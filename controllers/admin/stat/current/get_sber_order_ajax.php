<?
require_once ROOT . '/lib/sberbank.php';
check_user_access('admin');

$_POST['order_id'] = '32a8503d-af87-7055-9e3c-b9c704b2b916';

if (!empty($_POST['order_id'])) {
  $order_id = $_POST['order_id'];

  try {
    $sber_order = $sber_client->execute('/payment/rest/getOrderStatusExtended.do', [
      'orderId' => $order_id,
    ]);
  } catch (Exception $e) {
    exit(json_err($e->getMessage()));
  }

  if (isset($sber_order['amount']) && $sber_order['amount'] > 0) {
  	$amount = substr($sber_order['amount'], 0, -2);
    exit(json_msg(['sum' => $amount]));
  } else exit(json_err('Не получена сумма от API сбербанка'));

} else {
  exit(json_err('Не получен ID заказа'));
}
