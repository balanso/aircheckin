<?
check_user_access('admin');

if (!empty($_REQUEST['order_id'])) {
	$order_id = $_REQUEST['order_id'];

	$order = $db->getRow("
		SELECT orders.*, aparts.name AS apart_name, aparts.address AS apart_address
			FROM orders
			JOIN aparts ON orders.apart_id = aparts.id WHERE orders.id=?i"
		,$order_id
	);


	$sber_orders = $db->getAll("SELECT * FROM sber_orders WHERE order_id=?i", $order_id);

	foreach ($sber_orders as $key => $sber_order) {
		$sber_orders[$key]['operations'] = $db->getAll("SELECT * FROM sber_operations WHERE sber_order_id=?i", $sber_order['id']);
	}

	$order_statuses = $db->getAll("SELECT * FROM order_statuses");
	foreach ($order_statuses as $key => $status) {
		$statuses[$status['id']] = $status;
	}

	$order['status_history'] = $db->getAll("SELECT * FROM order_status_history WHERE order_id=?i", $order_id);
	$order['payments_data'] = $sber_orders;
	$order['statuses'] = $statuses;

	// debug($order);


	exit(json_html('/views/admin/stat/apart_new/order_data.tpl',
	  ['order' => $order]));
}