<?
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require_once ROOT . '/lib/sberbank.php';
require_once ROOT . '/lib/messaging.php';
require_once ROOT . '/lib/exa_pms.php';
require_once ROOT . '/lib/ofd.php';




// Logger
$formatter = new LineFormatter("[%datetime%] %channel% %level_name%: %message% %context% %extra%\n", "H:i:s");
$log       = new Logger('pay');
$handler   = new StreamHandler(ROOT . '/logs/pay_' . date('y.m.d') . '.log', DEBUG ? Logger::DEBUG : Logger::INFO);
$handler->setFormatter($formatter);
$log->pushHandler($handler);

$tg_error_message = '';

$log->info('Входящий запрос', $_REQUEST);

if ($action == 'success') {
  include_once __DIR__ . '/success.php';
  exit();
} elseif ($action == 'fail') {
  include_once __DIR__ . '/fail.php';
  exit();
}

$sber_config = get_config('sberbank');

//При операциях сбербанк присылает Callback на скрипт, происходит сверка достоверности данных по ключу.
if (check_data($_GET) || $sber_config['mode'] == 'test') {
  if (in_array($_GET['operation'], ['approved', 'deposited', 'reversed', 'refunded']) && $_GET['status'] == 1) {
    $log->info('Операция разрешена');

    //Получаем данные заказа из Callback, находим ID договора (order_id) в таблице sber_order_id по ID заказа в сбере (orderNumber)
    $sber_order = $sber_client->execute('/payment/rest/getOrderStatusExtended.do', [
      'orderId' => $_GET['mdOrder'],
    ]);
    $operation = $_GET['operation'];

    if (!empty($sber_order)) {
      $sber_order_data = $db->getRow("SELECT id, order_id, type FROM sber_orders WHERE sberbank_order_id=?s", $_GET['mdOrder']);

      if (!empty($sber_order_data['order_id'])) {
        $order = $db->getRow("SELECT * FROM orders WHERE id=?i", $sber_order_data['order_id']);

        if (!empty($order)) {
          $apart             = $db->getRow("SELECT id, name, owner_id FROM aparts WHERE id=?i", $order['apart_id']);
          $owner             = $db->getRow("SELECT * FROM owners WHERE id=?i", $apart['owner_id']);
          $date_in           = date('d.m.y H:i', $order['date_in']);
          $date_out          = date('d.m.y H:i', $order['date_out']);
          $time              = time();
          $operation_insert  = ['sber_order_id' => $sber_order_data['id'], 'created_at' => $time];
          $order_data_text   = '| Д№' . $order['id'] . ' | ' . $apart['name'] . ' | ' . $date_in . ' - ' . $date_out . ' | ' . $order['name'];
          $receipt_item_name = "Аренда апартамента {$apart['name']} с {$date_in} до {$date_out} по доровору №{$order['id']}";

          // Если получена оплата за тариф
          if ($sber_order_data['type'] == 't') {
            if ($operation == 'approved') {
              $operation_amount            = kop_to_rub($sber_order['paymentAmountInfo']['approvedAmount']);
              $order_update_data['status'] = ORDER_STATUS['pay_hold_sber'];
              $tg_text                     = '<b>Холдирование</b> тарифа ' . $operation_amount . 'р ' . $order_data_text;

              $operation_insert['type']   = 1;
              $operation_insert['amount'] = $operation_amount;
            }
            // Если операция deposited (Списание) то просто отправляем уведомление об операции в Telegram
            elseif ($operation == 'deposited') {
              $operation_amount            = kop_to_rub($sber_order['paymentAmountInfo']['depositedAmount']);
              $order_update_data['status'] = ORDER_STATUS['pay_complete_sber'];
              $tg_text                     = '<b>Расчёт</b> тарифа ' . $operation_amount . 'р ' . $order_data_text;

              $operation_insert['type']   = 2;
              $operation_insert['amount'] = $operation_amount;
              $receipt_type               = 'Income';

              // Интеграция с ExaPMS, добавляем расчёт в заказ
              if (isset($order['pms_id'])) {
                $exa_data = exa_get_order_by_key($order['pms_id']);

                if (isset($exa_data['order']['ID'])) {
                  $pay_add = exa_request([
                    'command' => 'OrderSuccessPay',
                    'summ'    => $operation_amount,
                    'id'      => $exa_data['order']['ID'],
                  ]);

                  if (isset($pay_add['error'])) {
                    $tg_error_message = "Ошибка добавления оплаты в заказ №{$order['pms_id']}: {$exa_data['message']}";
                  }
                } else {
                  $tg_error_message = 'Ошибка получения заказа PMS при оплате. ' . $exa_data['message'];
                }
              } else {
                $tg_error_message .= "Ошибка при оплате: в заказе ID {$order['id']} не указан PMS ID. Заказ PMS не отредактирован.";
              }

            } elseif ($operation == 'reversed') {
              $operation_amount            = kop_to_rub($sber_order['amount']);
              $order_update_data['status'] = ORDER_STATUS['nulled_return_sber'];
              $tg_text                     = '<b>Частичный расчёт</b> тарифа ' . $operation_amount . 'р ' . $order_data_text;

              $operation_insert['type']   = 4;
              $operation_insert['amount'] = $operation_amount;

            } elseif ($operation == 'refunded') {
              $operation_amount           = kop_to_rub($sber_order['paymentAmountInfo']['refundedAmount']);
              $operation_insert['amount'] = $operation_amount;
              $operation_insert['type']   = 6;
              $tg_text                    = '<b>Возврат</b> оплаты тарифа ' . $operation_amount . 'р ' . $order_data_text;

              $receipt_type = 'IncomeReturn';
            }

            if (!empty($tg_error_message)) {
              $tg_error_message .= "\nНомер договора: " . $order['id'];
              send_tg($tg_error_message, $owner['tg_chat_id']);
            }
          }

          // Если получена оплата за депозит
          elseif ($sber_order_data['type'] == 'd') {
            if ($operation == 'approved') {
              $operation_amount = kop_to_rub($sber_order['paymentAmountInfo']['approvedAmount']);
              $tg_text          = '<b>Холдирование</b> депозита ' . $operation_amount . 'р ' . $order_data_text;

              $operation_insert['type']   = 1;
              $operation_insert['amount'] = $operation_amount;
            }
            // Если операция deposited (Списание) то просто отправляем уведомление об операции в Telegram
            elseif ($operation == 'deposited') {
              $approved_amount  = kop_to_rub($sber_order['paymentAmountInfo']['approvedAmount']);
              $deposited_amount = kop_to_rub($sber_order['paymentAmountInfo']['depositedAmount']);

              $approved_amount_text = '';
              if ($approved_amount != $deposited_amount) {
                $approved_amount_text = 'от ' . $approved_amount . 'р ';
              }

              $tg_text = '<b>Расчёт</b> депозита ' . $deposited_amount . 'р ' . $approved_amount_text . $order_data_text;

              $operation_insert['type']   = 2;
              $operation_insert['amount'] = $deposited_amount;
              $receipt_type               = 'IncomePrepayment';

            } elseif ($operation == 'reversed') {
              $operation_amount = kop_to_rub($sber_order['amount']);
              $tg_text          = '<b>Частичный расчёт</b> депозита ' . $operation_amount . 'р ' . $order_data_text;

              $operation_insert['type']   = 4;
              $operation_insert['amount'] = $operation_amount;
            } elseif ($operation == 'refunded') {
              $operation_amount           = kop_to_rub($sber_order['paymentAmountInfo']['refundedAmount']);
              $tg_text                    = '<b>Возврат</b> оплаты депозита ' . $operation_amount . 'р ' . $order_data_text;
              $operation_insert['type']   = 6;
              $operation_insert['amount'] = $operation_amount;
              $receipt_type               = 'IncomeReturnPrepayment';
            }
          }

          // Обновляем статус договора
          if (isset($order_update_data['status'])) {
            $db->query("UPDATE orders SET status=?i, status_updated_at=?i  WHERE id=?i", $order_update_data['status'], time(), $order['id']);
            add_order_status_history($order['id'], $order_update_data['status']);

            $log->info("Обновляем статус договора ID {$order['id']}");
          }

          if (!empty($operation_insert)) {
            $log->info('Добавляем операцию sber_operations и обновляем статус sber_orders', $operation_insert);

            $db->query("INSERT INTO sber_operations SET ?u", $operation_insert);
            $db->query("UPDATE sber_orders SET status=?i WHERE id=?i", $operation_insert['type'], $operation_insert['sber_order_id']);

            // Генерируем чек и записываем данные в БД
            if (isset($receipt_type) && isset($operation_insert['amount'])) {
              $receipt = ofd_generate_send_receipt(
                $sber_order_data['id'],
                $receipt_type,
                $receipt_item_name,
                $operation_insert['amount'], $order['name'] . ' ' . $order['passport_number'],
                $order['email'],
                $order['phone']
              );

              if (!empty($receipt['ofd_receipt_id'])) {
                $log->info('Сгенерирован чек ID '.$receipt['receipt_id']);
                $update_result = ofd_update_check_data($receipt['receipt_id']);

                if ($update_result) {
                  $log->info('Обновлены данные чека');
                } else {
                  $log->error('Не удалось обновить данные чека');
                }
              }

              if ($receipt['status'] == 'success') {
                $tg_text .= "\nЧек отправлен гостю на " . $order['email'];
              } else {
                $tg_text .= "\nОшибка отправки чека, см. лог.\n";
                $log->info('Ошибка отправки чека', $receipt);
              }
            }
          }

        }
      } elseif (empty($sber_order_data['order_id'])) {
        $tg_text = 'Не найдена связка номера заказа сбербанка с номера договора.' . "\n";
        if ($operation == 'approved') {
          $order_amount = substr($sber_order['paymentAmountInfo']['approvedAmount'], 0, -2);
          $tg_text .= 'Сбербанк холдирование: ' . $order_amount . 'р';
        } elseif ($operation == 'deposited') {
          $order_amount = substr($sber_order['paymentAmountInfo']['depositedAmount'], 0, -2);
          $tg_text .= 'Сбербанк расчёт: ' . $order_amount . 'р';
        } elseif ($operation == 'reversed') {
          $order_amount = substr($sber_order['amount'], 0, -2);
          $tg_text .= 'Сбербанк возврат: ' . $order_amount . 'р';
        } else {
          $tg_text .= 'Сбербанк ' . $operation;
        }

        if (!empty($sber_order_id)) {
          $tg_text .= ', номер операции: ' . $sber_order_id;
        }
        if (!empty($sber_order['orderDescription'])) {
          $tg_text .= ', описание: ' . $sber_order['orderDescription'];
        }
      }

      if (!empty($tg_text)) {
        $log->info('Отправляем сообщение в ТГ');
        send_tg($tg_text, $owner['tg_chat_id']);
      }

    } else {
      $log->warning('Не найден заказ сбербанк в БД');
    }

  } else {
    $log->warning('Не известный статус или тип операции');
  }
} else {
  $log->warning('Не пройдена проверка данных');
  echo 'access denied';
}
