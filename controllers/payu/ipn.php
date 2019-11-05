<?
// file_put_contents(__DIR__ . '/ipn', date('d.m.Y H:i') . ' ' . print_r(var_export($_POST, TRUE) . "\n", true), FILE_APPEND);

if (isset($_POST['HASH'])) {
  $result = $payu->handleIpnRequest();

  $sig_params = $_POST;
  $sig_str = '';
  unset($sig_params['HASH']);

  foreach ($sig_params as $key => $value) {
    if (is_array($value)) {
      $sig_params[$key] = $value[0];
    }

    $sig_str .= strlen($sig_params[$key]);
    $sig_str .= $sig_params[$key];
  }

  $payu_config = get_config('payu');
  $sig = hash_hmac('md5', $sig_str, $payu_config['secret']);

  if ($_POST['HASH'] == $sig) {
    if (isset($_POST['ORDERNO']) && isset($_POST['PAYMETHOD']) && isset($_POST['ORDERSTATUS'])
      && $_POST['PAYMETHOD'] == 'Payout' && $_POST['ORDERSTATUS'] == 'COMPLETE') {

      $request_owner_id = $db->getOne("SELECT owner_id FROM payu_requests WHERE id=?i", $_POST['REFNOEXT']);

      if ($request_owner_id) {
        $orders_id = $db->getAll("SELECT id FROM orders WHERE status=?i AND apart_id IN (SELECT id FROM aparts WHERE owner_id=?i)", ORDER_STATUS['wait_payout_answer'], $request_owner_id);
        $ids = [];
        foreach ($orders_id as $key => $order) {
          $ids[] = $order['id'];
        }

        if (!empty($ids)) {
          $db->query("UPDATE orders SET status=?i WHERE id IN (?a)", ORDER_STATUS['order_completed_paid'], $ids);
          add_order_status_history($ids, ORDER_STATUS['order_completed_paid']);
        }

        $db->query("UPDATE payu_requests SET response_recieved_at=?i WHERE id=?i", time(), $_POST['ORDERNO']);
      }
    }

    echo $result;
    exit();

  } else {
    echo ('Hash check failed');
  }
} else {
  echo ('Hash not found');
}
