<div class="modal-header">
  <h5 class="modal-title" id="order_modal_title">Договор №<?=$order['id']?> от <?=date('d.m H:i', $order['date_created'])?></h5>
  <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
<div class="modal-body">
  Апартамент <?=$order['apart_name']?> <?=$order['apart_address']?><br>
  Проживание с <?=str_date_to_format($order['date_in'], 'd.m H:i')?> по <?=str_date_to_format($order['date_out'], 'd.m H:i')?><br>
  Кол-во гостей: <?=$order['guests']?><br>
  Кол-во уборок: <?=$order['cleanings']?> по <?=$order['cleaning_cost']?>₽<br>
  Статус: <?=$order['statuses'][$order['status']]['description']?>

  <br><br>
  <b><?=$order['name']?></b><br>
  <?=$order['phone']?> <?=$order['email']?><br>
  Паспорт <?=$order['passport_number']?><br>
  Дата рождения <?=$order['birthdate']?>


  <?
if (isset($order['payments_data'])) {
  foreach ($order['payments_data'] as $key => $payment_data) {
    $operations_text = [];

    if ($payment_data['type'] == 't') {
      $type = 'Тариф';
    } elseif ($payment_data['type'] == 'd') {
      $type = 'Депозит';
    }

    $pay_orders[$key]['id']     = $payment_data['sberbank_order_id'];
    $pay_orders[$key]['title']  = "<a href=\"\" onClick=\"return false;\" id=\"js_show_operations_history\" data-id=\"$key\">$type</a> {$payment_data['amount']}₽";
    $pay_orders[$key]['status'] = "создан";
    $pay_orders[$key]['amount'] = $payment_data['amount'];

    $operations_text[] = date('d.m H:i', $payment_data['created_at']) . ' создан заказ';

    if (!empty($payment_data['operations'])) {
      foreach ($payment_data['operations'] as $k => $op) {
        switch ($op['type']) {
          case '1':
            $operations_text[]          = date('d.m H:i', $op['created_at']) . " принят платёж (депозит) {$op['amount']}₽";
            $pay_orders[$key]['status'] = 'принят платёж';
            break;
          case '2':
            $operations_text[]          = date('d.m H:i', $op['created_at']) . " подтверждён платёж (расчёт) {$op['amount']}₽";
            $pay_orders[$key]['status'] = 'произведён расчёт';
            break;
          case '3':
            $operations_text[]          = date('d.m H:i', $op['created_at']) . " возврат {$op['amount']}₽";
            $pay_orders[$key]['status'] = 'произведён возврат';
            break;

          default:
            break;
        }
      }
    }
    $pay_orders[$key]['operations_text'] = implode('<br>', $operations_text);
  }

  if (!empty($pay_orders)) {
    $payments_text   = '';
    $operations_text = '';

    foreach ($pay_orders as $key => $pay_order) {
      $payments_text .= $pay_order['title'] . ' - ' . $pay_order['status'];

      if (!empty($pay_order['operations_text'])) {
        $payments_text .= '<br>';
        if ($pay_order['status'] == 'принят платёж') {
          $pay_complete_text = '<br>
            <div class="input-group" id="js_pay_input' . $pay_order['id'] . '">
            <div class="input-group-prepend">
            <span class="input-group-text">Расчёт на сумму</span>
            </div>
            <input type="number col-sm-1" value="' . $pay_order['amount'] . '" id="input_sum' . $pay_order['id'] . '" class="form-control">

            <div class="input-group-append">
            <button class="btn btn-outline-success js_pay_complete" data-order-id="' . $order['id'] . '"data-for="order_sum' . $order['id'] . '" data-id="' . $pay_order['id'] . '" type="button">Сделать</button>
            </div>
            </div>
            ';
        } else {
          $pay_complete_text = '';
        }
        $payments_text .= '
          <div class="operations_text" data-id="' . $key . '" style="display: none">' . $pay_order['operations_text'] . $pay_complete_text . '<br>

          </div>
          ';
      }
    }
  }

}

if (!empty($payments_text)) {?>
    <br><br><b>Данные по оплатам</b><br>
    <?=$payments_text?>
    <?}?>


    <br><b><a href="#" id="js_show_status_history">История статусов</a></b><br>
    <div class="status_history" style="display: none">
      <?
$text = '';
foreach ($order['status_history'] as $key => $item) {
  $text .= date('d.m H:i', $item['created_at']) . ' ';
  $text .= $order['statuses'][$item['status_id']]['description'];
  if (!empty($item['description'])) {
    $text .= '<br><small>' . $item['description'].'</small>';
  }
  $text .= '<br>';

}

echo $text;
?>
    </div>
  </div>