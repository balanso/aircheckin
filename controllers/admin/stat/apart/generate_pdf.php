<?

if (!empty($_GET['apart_id']) && !empty($_GET['apart_name']) && !empty($_GET['date_from_ts']) && !empty($_GET['date_to_ts'])) {

  $_monthsList = array(
  "1"=>"январь","2"=>"февраль","3"=>"март",
  "4"=>"апрель","5"=>"май", "6"=>"июнь",
  "7"=>"июль","8"=>"август","9"=>"сентябрь",
  "10"=>"октябрь","11"=>"ноябрь","12"=>"декабрь");

  $owner = $db->getRow("SELECT * FROM owners WHERE id IN (SELECT owner_id FROM aparts WHERE id=?i)", $_GET['apart_id']);
  $user_created_at = $db->getOne("SELECT created_at FROM users WHERE id=?i", $owner['user_id']);

  $orders = $db->getAll(
    "SELECT orders.*,marketing_agents.name as marketing_agent_name, service_agents.name as service_agent_name
  FROM orders
  JOIN marketing_agents ON orders.marketing_agent_id = marketing_agents.id
  JOIN service_agents ON orders.service_agent_id = service_agents.id
  WHERE date_in BETWEEN ?s AND ?s AND apart_id=?i ORDER BY date_in DESC",
    $_GET['date_from_ts'], $_GET['date_to_ts'], $_GET['apart_id']);

  if (!empty($orders)) {
    $total = ['tarif' => 0, 'marketing' => 0, 'service' => 0, 'cleanings' => 0, 'owner_profit' => 0, 'cleanings_cost' => 0, 'cleanings_num' => 0, 'owner_profit' => 0];

    foreach ($orders as $key => $order) {
      $date_in        = date('d.m', $order['date_in']);
      $date_out       = date('d.m', $order['date_out']);
      $cleanings_cost = $order['cleanings'] * $order['cleaning_cost'];
      $marketing_cost = $order['tarif_total'] / 100 * $order['marketing_agent_percent'];
      $service_cost   = $order['tarif_total'] / 100 * $order['service_agent_percent'];
      $owner_profit         = $order['tarif_total'] - $service_cost - $marketing_cost - $cleanings_cost;

      $total['tarif'] += $order['tarif_total'];
      $total['marketing'] += $marketing_cost;
      $total['service'] += $service_cost;
      $total['cleanings_cost'] += $cleanings_cost;
      $total['cleanings_num'] += $order['cleanings'];
      $total['owner_profit'] += $owner_profit;
    }
  }

  $order_text = file_get_contents(ROOT . '/views/admin/stat/apart/pdf_report.tpl');
  $order_text = str_replace('$[\'web_root\']', WEB_ROOT, $order_text);
  $order_text = str_replace('$[\'apart_name\']', $_GET['apart_name'], $order_text);
  $order_text = str_replace('$[\'user_created_at\']', date('d.m.Y', $user_created_at), $order_text);

  $date       = date("Y-m-01", $_GET['date_from_ts']);
  $newdate    = strtotime('+1 month', strtotime($date));
  $order_text = str_replace('$[\'report_created_at\']', date('d.m.Y', $newdate), $order_text);

  $report_date_period = $_monthsList[date("n", $_GET['date_from_ts'])] . ' ' . date('Yг.');
  $order_text = str_replace('$[\'report_date\']', $report_date_period, $order_text);
  $order_text = str_replace('$[\'owner_name\']', $owner['name'], $order_text);


  $order_text = str_replace('$[\'total_orders_sum\']', $total['tarif'], $order_text);
  $order_text = str_replace('$[\'total_agent_profit\']', $total['service'], $order_text);
  $order_text = str_replace('$[\'total_cleanings_num\']', $total['cleanings_num'], $order_text);
  $order_text = str_replace('$[\'total_cleanings_price\']', $total['cleanings_cost'], $order_text);
  $order_text = str_replace('$[\'total_owner_profit\']', $total['owner_profit'], $order_text);

  $order_text = str_replace('$[\'owner_name\']', $owner['name'], $order_text);


  $mpdf       = new \Mpdf\Mpdf(['tempDir' => ROOT . '/public/orders/']);
  $mpdf->WriteHTML($order_text);
  $mpdf->Output('Отчёт по апартаменту Е152 за '.$_monthsList[date("n", $_GET['date_from_ts'])].' 2019.pdf', 'D');

}
