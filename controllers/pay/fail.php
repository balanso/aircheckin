<?
$order_text = '';
if (!empty($_GET['orderId'])) {
  $sber_order_data = $sber_client->execute('/payment/rest/getOrderStatusExtended.do', [
    'orderId' => $_GET['orderId'],
  ]);

  if (!empty($sber_order_data['orderNumber'])) {
    $order_id = $db->getOne("SELECT order_id FROM sber_order_id WHERE sber_order_id=?s", $sber_order_data['orderNumber']);
  }

  if ($order_id) {
    $order_text = ' договора №' . $order_id;
  }
}

load_tpl('/views/header.tpl');
load_tpl('/views/pay/pay_fail.tpl', ['order_text'=>$order_text]);
load_tpl('/views/footer.tpl');
