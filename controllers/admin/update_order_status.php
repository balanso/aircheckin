<?
check_user_access('admin');

global $user;
$tpl_data = ['order_id'=>''];

if (!empty($_POST)) {
  if (isset($_POST['order_id'])) {
    if (isset($_POST['status_id'])) {
      $db->query("UPDATE orders SET status=?i WHERE id=?i", $_POST['status_id'], $_POST['order_id']);
      add_order_status_history($_POST['order_id'], $_POST['status_id'], "Изменил {$user['name']} {$user['email']}");

      $tpl_data['message'] = 'Статус договора №'.$_POST['order_id'].' изменён.';
      global $user;
      $log->info($user['email']. ' изменил статус договора №'.$_POST['order_id'].' на '.$_POST['status_id']);

      load_tpl('/views/admin/template/header.tpl');
      load_tpl('/views/admin/update_order_status/search_order.tpl', $tpl_data);
      load_tpl('/views/admin/template/footer.tpl');
      exit();
    }
    $tpl_data['order'] = $db->getRow("SELECT * FROM orders WHERE id=?i", $_POST['order_id']);

    if (!empty($tpl_data['order'])) {
      $tpl_data['statuses'] = $db->getAll("SELECT id, description FROM order_statuses WHERE id=?i", ORDER_STATUS['pay_complete_custom']);

      load_tpl('/views/admin/template/header.tpl');
      load_tpl('/views/admin/update_order_status/update_order_status.tpl', $tpl_data);
      load_tpl('/views/admin/template/footer.tpl');

      exit();
    } else {
      $tpl_data['message'] = 'Договор с таким номером не найден!';
    }
  }
}

load_tpl('/views/admin/template/header.tpl');
load_tpl('/views/admin/update_order_status/search_order.tpl', $tpl_data);
load_tpl('/views/admin/template/footer.tpl');