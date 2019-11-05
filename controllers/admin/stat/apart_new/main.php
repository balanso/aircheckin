<?

$data = [];

$table_rows = '';
$total      = ['tarif' => 0, 'marketing' => 0, 'service' => 0, 'cleanings' => 0, 'profit' => 0];
$total_row  = '';
$text       = '';

$statuses = [
  ORDER_STATUS['pay_complete_sber'],
  ORDER_STATUS['pay_complete_custom'],
  ORDER_STATUS['ready_for_money_transfer'],
];

$orders = $db->getAll(
  "SELECT orders.*,marketing_agents.name as marketing_agent_name, service_agents.name as service_agent_name
    FROM orders
    JOIN marketing_agents ON orders.marketing_agent_id = marketing_agents.id
    JOIN service_agents ON orders.service_agent_id = service_agents.id
    WHERE date_created BETWEEN ?i and ?i ORDER BY date_in DESC", time()-MONTH, time());

if (!empty($orders)) {
  $text = 'Статистика договоров за 30 дней' . "\n\n";
  foreach ($orders as $key => $order) {
    $date_in        = date('m.d', $order['date_in']);
    $date_out       = date('m.d', $order['date_out']);
    $cleanings_cost = $order['cleanings'] * $order['cleaning_cost'];
    $marketing_cost = $order['tarif_total'] / 100 * $order['marketing_agent_percent'];
    $service_cost   = $order['tarif_total'] / 100 * $order['service_agent_percent'];
    $profit         = $order['tarif_total'] - $service_cost - $marketing_cost - $cleanings_cost;

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
		<td></td>
		<td>{$total['tarif']}₽</td>
		<td>{$total['profit']}₽</td></tr>";

  $data['total_row'] = $total_row;
}

if (!empty($_GET['tenant_id'])) {
  $data['tenant_id'] = $_GET['tenant_id'];
}

$aparts               = $db->getAll("SELECT name,id FROM aparts");
$apart_select_options = '';
if (!empty($aparts)) {
  foreach ($aparts as $key => $apart) {
    $apart_select_options .= "<option value=\"{$apart['id']}\">{$apart['name']}</option>";
  }
}
$data['apart_select_options'] = $apart_select_options;




load_tpl('/views/admin/stat/apart_new/main.tpl', $data);