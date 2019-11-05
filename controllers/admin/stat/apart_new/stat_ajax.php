<?
check_user_access('admin');

$where = [];
$where_query = '';
$out_orders = [];

if (!empty($_POST['date_from_ts']) && !empty($_POST['date_to_ts'])) {
  $where[] = $db->parse('date_in BETWEEN ?s AND ?s', $_POST['date_from_ts'], $_POST['date_to_ts']);
} else {
  $where[] = $db->parse('date_created BETWEEN ?s AND ?s', time()-MONTH, time());
}

if (!empty($_POST['apart_id'])) {
  $where[] = $db->parse('apart_id=?i', $_POST['apart_id']);

  if (!empty($_POST['apart_name'])) {
    $apart_text = ' по апартаменту ' . $_POST['apart_name'];
  }
}

if (!empty($where)) {
  $where_query = 'WHERE ' . implode(' AND ', $where);
}

$orders = $db->getAll(
  "SELECT orders.*,marketing_agents.name as marketing_agent_name, service_agents.name as service_agent_name
  FROM orders
  JOIN marketing_agents ON orders.marketing_agent_id = marketing_agents.id
  JOIN service_agents ON orders.service_agent_id = service_agents.id
  $where_query ORDER BY date_in DESC");

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

  $cleanings_cost                   = $order['cleanings'] * $order['cleaning_cost'];
  $marketing_cost                   = $order['tarif_total'] / 100 * $order['marketing_agent_percent'];
  $service_cost                     = $order['tarif_total'] / 100 * $order['service_agent_percent'];
  $owner_profit                     = $order['tarif_total'] - $service_cost - $marketing_cost - $cleanings_cost;
  $out_orders[$key]['revenue']      = $order['tarif_total'] . '₽';
  $out_orders[$key]['owner_profit'] = $owner_profit . '₽';

/*  $out_orders['tarif_total'] = $order['tarif_total'];
$out_orders['profit'] = $profit;*/

  $out_orders[$key]['order'] = "№<span class=\"order_id\">{$order['id']}</span> от " . date('d.m', $order['date_created']) . "<br>
  c " . $out_orders[$key]['date_in'] . " по " . $out_orders[$key]['date_out'];
  $out_orders[$key]['guest']  = $order['name'] . "<br>{$order['phone']} {$order['email']}";
  $out_orders[$key]['status'] = $status_id_desc[$order['status']];
}

if (empty($out_orders)) {
  exit(json_encode(['status' => 'error', 'message' => 'Не найдены договора на указанные даты']));
} else {
  exit(json_encode(['data' => $out_orders]));
}

// echo json_encode(['status'=>'error', 'message'=>'Не получены даты поиска']);
