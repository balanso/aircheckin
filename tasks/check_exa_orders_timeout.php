<?
require_once __DIR__ . '/../config.php';
require_once ROOT . '/lib/messaging.php';
require_once ROOT . '/lib/exa_pms.php';
require_once ROOT . '/lib/db.php';

global $db;

$timeout_orders = $db->getAll("SELECT id, pms_id FROM pre_orders WHERE status=3 AND timeout<?s", time());

foreach ($timeout_orders as $key => $order) {

  if (!empty($order['pms_id'])) {
    $exa_data              = exa_get_order_by_key($order['pms_id']);
    $exa_edit_order_params = [
      'id'    => $exa_data['order']['ID'],
      'state' => 3,
    ];

    $result = exa_edit_order($exa_edit_order_params);
  }

// status 4 = аннулирован по истечению таймаута регистрации
  $db->query("UPDATE pre_orders SET status=100 WHERE id=?i", $order['id']);
  file_put_contents(__DIR__ . '/check_exa_orders_timeout.log', date('d.m.Y H:i') . ' бронь аннулирована: ' . print_r(['pms_id' => $order['pms_id'], 'pre_order_id' => $order['id'], 'result' => print_r($result, true)], true), FILE_APPEND);
}
