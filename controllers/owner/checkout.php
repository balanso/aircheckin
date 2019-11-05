<?
require_once ROOT . '/lib/vendor/PayU.php';
require_once ROOT . '/lib/messaging.php';
require_once ROOT . '/lib/db.php';

function checkout_owner_balance($owner)
{
  $db = get_db_connect();
  $user = $db->getRow("SELECT * FROM users WHERE id=?i", $owner['user_id']);
// Получаем договора со статусом 20 "Готов к переводу денег собственнику"
  $owner_balance_items = $db->getAll("SELECT * FROM owners_balance WHERE owner_id=?i AND payed_out=0", $owner['id']);
  $owner_balance       = 0;

  if (!empty($owner_balance_items)) {
    foreach ($owner_balance_items as $key => $item) {
      $owner_balance += $item['sum'];
      $order_ids_for_close[]         = $item['order_id'];
      $balance_items_ids_for_close[] = $item['id'];
    }

    if (is_numeric($owner_balance)) {

      $conf              = get_config('payu');
      $comission_percent = $conf['comission_percent'];
      $min_comission     = $conf['min_comission'];

      // Pay U забирает 1,2% при переводе, минималка 45.00р
      if ($owner_balance * 100 / (100 - $comission_percent) < $min_comission) {
        $payout_sum = $owner_balance + $min_comission;
      } else {
        $payout_sum = $owner_balance * 100 / (100 - $comission_percent);
      }

      $payout_sum = $payout_sum / 100;
      $payout_sum = round($payout_sum, 2);

      $payu_conf         = get_config('payu');
      $payu              = new PayU($payu_conf['merchant'], '', $payu_conf['secret']);
      $payu_balance      = $payu->getBalance();
      $owner_balance_rub = kop_to_rub($owner_balance);
      // $payu_balance = 100000;

      $name_array = explode(' ', $user['name']);
      if (count($name_array) == 3) {
        $first_name = $name_array[1];
        $last_name  = $name_array[0];
      } elseif (count($name_array) == 2) {
        $first_name = $name_array[1];
        $last_name  = $name_array[0];
      } else {
        $first_name = $name_array[0];
        $last_name  = 'Aeroapart';
      }

      if ($payu_balance && $payu_balance >= $payout_sum) {
        $db->query("INSERT INTO payu_requests SET owner_id=?i, created_at=?i, type=2", $owner['id'], time());
        $payu_request_id = $db->insertId();

        $data_arr = array(
          // данные Payout запроса
          'amount'            => $payout_sum,
          'currency'          => 'RUB',
          'clientCountryCode' => 'RU',
          'outerId'           => $payu_request_id,
          'desc'              => 'Вывод средств',
          'senderFirstName'   => 'Aircheckin',
          'senderLastName'    => 'Service',
          'senderEmail'       => 'booking@aeroapart.ru',
          'senderPhone'       => '+79233111155',
          'clientFirstName'   => $first_name,
          'clientLastName'    => $last_name,
          'clientEmail'       => $user['email'],
          'timestamp'         => time(),
        );

        // json_answer($data_arr);

        $result = $payu->sendPayoutRequest($data_arr, $owner['payu_token']);
        // $result[1]          = 'SUCCESS';
        $output['result']     = $result;
        $output['data_arr']   = $data_arr;
        $output['payout_sum'] = $payout_sum;

        // PayU принял запрос на выплату
        if (isset($result[1]) && $result[1] == 'SUCCESS') {
          // Обновляем баланс PayU
          $new_payu_balance = $payu_balance - $payout_sum;
          $db->query("INSERT INTO payu_balance SET balance=?i, created_at=?i", $new_payu_balance, time());

          // Ставим договорам статус "Ожидание выплаты от PayU"
          $db->query("UPDATE orders SET status=?i, status_updated_at=?i WHERE id IN (?a)", ORDER_STATUS['wait_payout_answer'], time(), $order_ids_for_close);
          $db->query("UPDATE owners_balance SET payed_out=1, created_at=?i WHERE id IN (?a)", time(), $balance_items_ids_for_close);

          // Добавляем историю статусов
          add_order_status_history($order_ids_for_close, ORDER_STATUS['wait_payout_answer']);

        /*  $log->info('Создан запрос выплаты собственнику ' . $user['login'] . ' в размере ' . $payout_sum . '₽');
          $log->info('Договора переведены в статус ожидания выплаты', $order_ids_for_close);
*/
          send_tg("Собственник {$first_name} {$last_name} id{$owner['user_id']} перевёл {$payout_sum}₽ с баланса на карту {$owner['card_number']}");

          $output['message'] = 'Создан запрос перевода ' . $owner_balance_rub . '₽ на карту ' . $owner['card_number'] . ', обработка запроса может занять до 10 минут.';
          return $output['message'];
        } else {
          send_tg("У собственника {$first_name} {$last_name} id{$owner['user_id']} возникли проблемы с выводом {$owner_balance_rub}₽ с баланса на карту {$owner['card_number']}\n" . print_r($result, true));
          return 'Мы зафиксировали ошибку #1, продолжаем тестирование сервиса и с вашего позволения переведём вам деньги в ручном режиме. Благодарим за помощь в тестировании!';
        }
      } else {
        send_tg("У собственника {$first_name} {$last_name} id{$owner['user_id']} возникли проблемы с выводом {$owner_balance_rub}₽ с баланса на карту {$owner['card_number']}\nНе хватает денег на счёте PayU!");
        return 'Мы зафиксировали ошибку #2, продолжаем тестирование сервиса и с вашего позволения переведём вам деньги в ручном режиме. Благодарим за помощь в тестировании!';
      }
    }
  } else {
    return 'Balance orders for payout not found';
  }
}
