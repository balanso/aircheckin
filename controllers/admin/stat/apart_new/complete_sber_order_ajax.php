<?
require_once ROOT . '/lib/sberbank.php';
check_user_access('admin');

/*$_POST['order_id'] = '32a8503d-af87-7055-9e3c-b9c704b2b916';
$_POST['sum'] = '60';*/

if (!empty($_POST['order_id'])) {
  $order_id = $_POST['order_id'];

  try {
    $sber_order = $sber_client->execute('/payment/rest/getOrderStatusExtended.do', [
      'orderId' => $order_id,
    ]);
  } catch (Exception $e) {
    exit(json_err($e->getMessage()));
  }

  // Расчёт!
  if ($sber_order['paymentAmountInfo']['paymentState'] == 'APPROVED') {
    if (isset($_POST['sum']) && $_POST['sum'] > 0) {
      try {
        $sber_deposit = $sber_client->execute('/payment/rest/deposit.do', [
          'orderId' => $order_id,
          'amount'  => $_POST['sum'] . '00',
        ]);

        exit(json_msg('Расчёт произведён! Данные карточки обновятся в течении 30 секунд.'));
      } catch (Exception $e) {
        exit(json_err($e->getMessage()));
      }
    } else exit(json_err('Не указана сумма депозита'));

  } else {
    exit(json_err('Статус заказа должен быть "принят платёж"'));
  }
} else {
  exit(json_err('Не указан ID заказа'));
}
