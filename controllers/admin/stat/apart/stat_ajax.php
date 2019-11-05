<?
check_user_access('admin');

$query_add = '';
$apart_text = '';

if (!empty($_POST['date_from_ts']) && !empty($_POST['date_to_ts'])) {
  $query_add = $db->parse("date_in BETWEEN ?s AND ?s", $_POST['date_from_ts'], $_POST['date_to_ts']);
  $dates_text = 'с ' . date('d.m', $_POST['date_from_ts']) . ' по ' . date('d.m', $_POST['date_to_ts']);
} else {
  $time = time()-MONTH;
  $query_add = "date_out >= ".$time;
  $dates_text = 'за 30 дней';
}

if (!empty($_POST['apart_id'])) {
  $query_add .= $db->parse(' AND apart_id=?i', $_POST['apart_id']);
  if (!empty($_POST['apart_name'])) {
    $apart_text = ' по апартаменту ' . $_POST['apart_name'];
  }
}

// $statuses = [ORDER_STATUS['pay_complete_sber'], ORDER_STATUS['pay_complete_custom'], ORDER_STATUS['ready_for_money_transfer']];
$orders = $db->getAll(
  "SELECT orders.*,marketing_agents.name as marketing_agent_name, service_agents.name as service_agent_name
  FROM orders
  JOIN marketing_agents ON orders.marketing_agent_id = marketing_agents.id
  JOIN service_agents ON orders.service_agent_id = service_agents.id
  WHERE $query_add ORDER BY date_in DESC");

if (!empty($orders)) {
  $total = ['tarif' => 0, 'marketing' => 0, 'service' => 0, 'cleanings' => 0, 'profit' => 0];
  // debug($orders);
  $table_rows = '';
  $text       = "Статистика договоров $dates_text$apart_text\n\n";
  foreach ($orders as $key => $order) {
    $date_in        = date('d.m', $order['date_in']);
    $date_out       = date('d.m', $order['date_out']);
    $cleanings_cost = $order['cleanings'] * $order['cleaning_cost'];
    $marketing_cost = $order['tarif_total'] / 100 * $order['marketing_agent_percent'];
    $service_cost   = $order['tarif_total'] / 100 * $order['service_agent_percent'];
    $profit         = $order['tarif_total'] - $service_cost - $marketing_cost - $cleanings_cost;
    $table_rows .= "<tr>
    <td>№<span class=\"order_id\">{$order['id']}</span> {$date_in} - {$date_out}</td>
    <td>{$order['tarif_total']}₽</td>
    <td>{$order['marketing_agent_name']} {$order['marketing_agent_percent']}₽</td>
    <td>{$order['service_agent_name']} {$service_cost}₽</td>
    <td>{$cleanings_cost}₽</td>
    <td>{$profit}₽</td>
    </tr>";

    $text .= "Договор №{$order['id']} {$date_in} - {$date_out}
Выручка {$order['tarif_total']}₽
Маркетинг - {$order['marketing_agent_name']} {$marketing_cost}₽
Сервис - {$order['service_agent_name']} {$service_cost}₽
Уборка {$cleanings_cost}₽
Прибыль {$profit}₽\n\n";

    $total['tarif'] += $order['tarif_total'];
    $total['marketing'] += $marketing_cost;
    $total['service'] += $service_cost;
    $total['cleanings'] += $cleanings_cost;
    $total['profit'] += $profit;
  }

  $text .= "Итоговая выручка: {$total['profit']}₽";

  $total['orders_count'] = count($orders);
  $total_row             = "<tr>
    <td>{$total['orders_count']} шт.</td>
    <td>{$total['tarif']}₽</td>
    <td>{$total['marketing']}₽</td>
    <td>{$total['service']}₽</td>
    <td>{$total['cleanings']}₽</td>
    <td>{$total['profit']}₽</td></tr>";
}

if (empty($orders)) {
  exit(json_encode(['status' => 'error', 'message' => 'Не найдены договора на указанные даты']));
} else {
  exit(json_encode(['orders' => $table_rows, 'total' => $total_row, 'text' => $text]));
}

// echo json_encode(['status'=>'error', 'message'=>'Не получены даты поиска']);
