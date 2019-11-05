<?
check_user_access('admin');

require ROOT . '/lib/exa_pms.php';

if (isset($_REQUEST['order_id'])) {
  $data = exa_get_order_by_key($_REQUEST['order_id']);

  // debug($data);

  if (isset($data['order'])) {
    $order = $data['order'];

    $order_data = [
      'date_from'     => $order['DateFrom'],
      'date_to'       => $order['DateTo'],
      'tarif_per_day' => $data['living']['Price'],
      'tarif_total'   => $order['Price'],
      'apart_name'    => $data['items']['Name'],
      'apart_id'      => $data['items']['ID'],
      'guests'        => $data['living']['Count'],
    ];

    $answer = ['status' => 'ok', 'data' => $order_data];

  } else {

    if (isset($data['error']) && isset($data['message'])) {
      $answer = ['status' => 'error', 'message' => $data['message']];
    } else {
      $answer = ['status' => 'error', 'message' => 'Неизвестная ошибка! ' . print_r($data, true)];
    }
  }

  exit(json_encode($answer));
} else {
  $answer = ['status' => 'error', 'message' => 'Не указан ID заказа PMS'];
  exit(json_encode($answer));
}
