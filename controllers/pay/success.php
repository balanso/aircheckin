<?
$tpl_data = [];

if (!empty($_GET['orderId'])) {
  $sber_order = $db->getRow("SELECT order_id, type FROM sber_orders WHERE sberbank_order_id=?s", $_GET['orderId']);

// Если оплачен депозит то получаем данные для оплаты тарифа
  if ($sber_order['type'] == 'd') {
    $sber_orders = $db->getAll("SELECT type, sberbank_order_id FROM sber_orders WHERE order_id=?i", $sber_order['order_id']);

    foreach ($sber_orders as $key => $ord) {
      if ($ord['type'] == 't') {
        $tpl_data['tarif_pay_url'] = 'https://securepayments.sberbank.ru/payment/merchants/sbersafe_id/payment_ru.html?mdOrder=' . $ord['sberbank_order_id'];
      }
    }
  }

  $order = $db->getRow("SELECT * FROM orders WHERE id=?i", $sber_order['order_id']);
  if (!empty($order)) {
    $tpl_data['order_file_name'] = $order['file_name'];
  }

  $type      = $sber_order['type'];
  $type_text = '';

  if ($type == 't') {
    $type_text = 'тарифа';
  } elseif ($type == 'd') {
    $type_text = 'депозита';
  }

  if ($sber_order['order_id'] && !empty($type_text)) {
    $tpl_data['order_text'] = ' ' . $type_text . ' по договору №' . $sber_order['order_id'];
  }
}

load_tpl('/views/header.tpl');
load_tpl('/views/pay/pay_success.tpl', $tpl_data);
load_tpl('/views/footer.tpl');
