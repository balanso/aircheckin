<?
check_user_access('admin');


if (!empty($_GET['tenant_id'])) {
  $orders = $db->getAll("SELECT
		orders.*,
		marketing_agents.name as marketing_agent_name,
		service_agents.name as service_agent_name
		FROM orders
		JOIN marketing_agents ON orders.marketing_agent_id = marketing_agents.id
		JOIN service_agents ON orders.service_agent_id = service_agents.id
		WHERE orders.id IN (SELECT order_id FROM tenant_orders WHERE tenant_orders.tenant_id=?i)
		ORDER BY id DESC", $_GET['tenant_id']);
} else {
  if (!empty($_POST['date_from_ts']) && !empty($_POST['date_to_ts'])) {
    $orders = $db->getAll(
      "SELECT orders.*,marketing_agents.name as marketing_agent_name, service_agents.name as service_agent_name
		FROM orders
		JOIN marketing_agents ON orders.marketing_agent_id = marketing_agents.id
		JOIN service_agents ON orders.service_agent_id = service_agents.id
		WHERE date_created BETWEEN ?s AND ?s ORDER BY id DESC"
      , $_POST['date_from_ts'], $_POST['date_to_ts']);

    // По дате создания?
  } else {
    $orders = $db->getAll(
      "SELECT orders.*,marketing_agents.name as marketing_agent_name, service_agents.name as service_agent_name
		FROM orders
		JOIN marketing_agents ON orders.marketing_agent_id = marketing_agents.id
		JOIN service_agents ON orders.service_agent_id = service_agents.id
		WHERE date_created >= ?i ORDER BY id DESC"
      , MONTH);
  }
}

$statuses = $db->getAll("SELECT * FROM order_statuses");

foreach ($statuses as $key => $status) {
  $status_id_desc[$status['id']] = $status['description'];
}

foreach ($orders as $key => $order) {
  $out_orders[$key]['name']         = $order['name'];
  $out_orders[$key]['phone']        = $order['phone'];
  $out_orders[$key]['email']        = $order['email'];
  $out_orders[$key]['date_in']      = date('d.m', $order['date_in']);
  $out_orders[$key]['date_out']     = date('d.m', $order['date_out']);
  $out_orders[$key]['date_created'] = date('d.m H:i', $order['date_created']);
  $out_orders[$key]['pay']          = "Тариф {$order['tarif_total']}₽<br>Депозит {$order['deposit']}₽";
  $out_orders[$key]['service']      = "{$order['service_agent_name']}<br>" . $order['tarif_total'] * $order['service_agent_percent'] / 100 . "₽";
  $out_orders[$key]['marketing']    = "{$order['marketing_agent_name']}<br>" . $order['tarif_total'] * $order['marketing_agent_percent'] / 100 . "₽";

  $out_orders[$key]['order'] = "№<span class=\"order_id\">{$order['id']}</span> от " . date('d.m', $order['date_created']) . "<br>
	c " . $out_orders[$key]['date_in'] . " по " . $out_orders[$key]['date_out'];
  $out_orders[$key]['guest'] = $order['name'] . "<br>{$order['phone']} {$order['email']}";
  $out_orders[$key]['status'] = $status_id_desc[$order['status']];
}

if (empty($out_orders)) {
  exit(json_encode(['status' => 'error', 'message' => 'Не найдены договора на указанные даты']));
} else {
  exit(json_encode(['data' => $out_orders]));
}

// echo json_encode(['status'=>'error', 'message'=>'Не получены даты поиска']);
