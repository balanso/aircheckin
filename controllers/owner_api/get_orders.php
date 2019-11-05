<?
if (!empty($_POST['date_out_from']) && !empty($_POST['date_out_to'])) {
  $query_add = $db->parse("date_out BETWEEN ?s AND ?s", $_POST['date_out_from'], $_POST['date_out_to']);
} else {
  $time      = time() - 6*MONTH;
  $query_add = "date_out >= " . $time;
}

if (!empty($_POST['date_in_from']) && !empty($_POST['date_in_to'])) {
  $query_add .= $db->parse(" AND date_in BETWEEN ?s AND ?s", $_POST['date_in_from'], $_POST['date_in_to']);
}

if (!empty($_POST['apart_id'])) {
  $query_add .= $db->parse(' AND apart_id=?i', $_POST['apart_id']);
}

$statuses = [
  ORDER_STATUS['pay_complete_sber'],
  ORDER_STATUS['pay_complete_custom'],
  ORDER_STATUS['ready_for_money_transfer'],
  ORDER_STATUS['order_completed_paid'],
];

$orders = $db->getAll(
  "SELECT orders.*,marketing_agents.name as marketing_agent_name, service_agents.name as service_agent_name
  FROM orders
  JOIN marketing_agents ON orders.marketing_agent_id = marketing_agents.id
  JOIN service_agents ON orders.service_agent_id = service_agents.id
  WHERE $query_add AND status IN (?a)
    AND apart_id IN (SELECT id FROM aparts WHERE owner_id={$owner_id})
  ORDER BY id DESC"
  , $statuses);

foreach ($orders as $key => $order) {
  $cleanings_cost = $order['cleanings'] * $order['cleaning_cost'];
  $marketing_cost = $order['tarif_total'] / 100 * $order['marketing_agent_percent'];
  $service_cost   = $order['tarif_total'] / 100 * $order['service_agent_percent'];
  $tarif_total    = $order['tarif_total'];
  $profit         = $tarif_total - $service_cost - $marketing_cost - $cleanings_cost;

  $output[$key] = [
    'id'                      => $order['id'],
    'date_in'                 => $order['date_in'],
    'date_out'                => $order['date_out'],
    'cleanings_num'           => $order['cleanings'],
    'cleanings_cost'          => rub_to_kop($cleanings_cost),
    'marketing_agent'         => $order['marketing_agent_name'],
    'marketing_agent_percent' => $order['marketing_agent_percent'],
    'marketing_agent_cost'    => rub_to_kop($marketing_cost),
    'service_agent'           => $order['service_agent_name'],
    'service_agent_percent'   => $order['service_agent_percent'],
    'service_agent_cost'      => rub_to_kop($service_cost),
    'tarif_total'             => rub_to_kop($tarif_total),
    'deposit'                 => rub_to_kop($order['deposit']),
    'status'                  => $order['status'],
    'status_updated_at'       => $order['status_updated_at'],
    'profit'                  => rub_to_kop($profit),
  ];
}

if (!empty($output)) {
  json_answer($output);
} else {
  json_answer('error', 'Orders not found');
}
