<?
require __DIR__ . '/checkout.php';

$card = [
  'update_button' => '<a class="btn btn-success btn-sm" href="/owner/add_card">Добавить карту для выплат</a>',
  'checkout_message' => '',
];

$checkout['message'] = '';

$user             = get_user_by_cookie();

if ($user['access_level'] != '10') {
	exit('Access denied, <a href="/owner/logout">Make logout and login again</a>');
}
$owner            = $db->getRow("SELECT * FROM owners WHERE user_id=?i", $user['id']);

if (isset($param1) && !empty($param1)) {
  if ($param1 == 'card_update' && isset($_GET['Result'])) {
    if (stristr($_GET['Result'], 'OK')) {
      $card['message'] = 'Запрос принят, мы добавим добавим вашу карту в течении 5 минут';
    } else {
      $card['message'] = 'Не удалсь добавить карту, попробуйте ещё раз';
    }
  }

  if ($param1 == 'checkout') {
  	$checkout['run'] = true;
  	$checkout['message'] = checkout_owner_balance($owner);
  }
}

$owner['balance'] = $db->getOne("SELECT SUM(owners_balance.sum) FROM owners_balance WHERE owner_id=?i AND payed_out=0", $owner['id']) ?? 0;


if ($owner['balance'] > 0) {
	$owner['balance'] = kop_to_rub($owner['balance']);
  $checkout_button_params = 'class="btn btn-warning btn-sm" href="/owner/cabinet/checkout"';
} else {
  $checkout_button_params = 'class="btn btn-secondary btn-sm disabled" href="#"';
}

if (!empty($owner['card_number'])) {
  $card['number']          = $owner['card_number'];
  $card['update_button']   = '<a class="btn btn-warning btn-sm" href="/owner/add_card">Изменить карту</a>';
  $card['checkout_button'] = "<a {$checkout_button_params}>Перевести на карту</a>";
} else {
  $card['checkout_button'] = "<a {$checkout_button_params}>Перевести на карту</a>";
}

//Получаем список договоров для отчёта
$statuses = [
  ORDER_STATUS['pay_complete_sber'],
  ORDER_STATUS['pay_complete_custom'],
  ORDER_STATUS['ready_for_money_transfer'],
  ORDER_STATUS['order_completed_paid'],
];

$orders_per_page = 25;
if (isset($_GET['orders_page'])) {
	$offset = $_GET['orders_page'] * 25;
} else {
	$offset = 0;
}

$orders = $db->getAll(
  "SELECT orders.*,marketing_agents.name as marketing_agent_name, service_agents.name as service_agent_name
  FROM orders
  JOIN marketing_agents ON orders.marketing_agent_id = marketing_agents.id
  JOIN service_agents ON orders.service_agent_id = service_agents.id
  WHERE status IN (?a)
    AND apart_id IN (SELECT id FROM aparts WHERE owner_id={$owner['id']})
  ORDER BY id DESC LIMIT {$orders_per_page} OFFSET {$offset}"
  , $statuses);

foreach ($orders as $key => $order) {
  $cleanings_cost = $order['cleanings'] * $order['cleaning_cost'];
  $marketing_cost = $order['tarif_total'] / 100 * $order['marketing_agent_percent'];
  $service_cost   = $order['tarif_total'] / 100 * $order['service_agent_percent'];
  $tarif_total    = $order['tarif_total'];
  $profit         = $tarif_total - $service_cost - $marketing_cost - $cleanings_cost;



  $orders[$key] = [
    'id'                      => $order['id'],
    'date_created'            => date('d.m.y', $order['date_created']),
    'date_in'                 => date('d.m.y', $order['date_in']),
    'date_out'                => date('d.m.y', $order['date_out']),
    'cleanings_num'           => $order['cleanings'],
    'cleanings_cost'          => $cleanings_cost,
    'marketing_agent'         => $order['marketing_agent_name'],
    'marketing_agent_percent' => $order['marketing_agent_percent'],
    'marketing_agent_cost'    => $marketing_cost,
    'service_agent'           => $order['service_agent_name'],
    'service_agent_percent'   => $order['service_agent_percent'],
    'service_agent_cost'      => $service_cost,
    'tarif_total'             => $tarif_total,
    'deposit'                 => rub_to_kop($order['deposit']),
    'status'                  => $order['status'],
    'status_updated_at'       => $order['status_updated_at'],
    'profit'                  => $profit,
  ];

  	$orders[$key]['cleaning_cost'] = $cleanings_cost > 0 ? $cleanings_cost : 0;
  	$orders[$key]['marketing_agent_cost'] = $marketing_cost > 0 ? $marketing_cost : 0;


  $orders[$key]['expenses'] = '';
}


$orders_template = [];
foreach ($orders as $key => $order) {;
	$orders_template[$order['date_created']][] = $order;
}

$tpl_data = ['owner' => $owner, 'user' => $user, 'card' => $card, 'orders' => $orders_template, 'checkout'=>$checkout];
load_tpl('/views/owner/header.tpl');
load_tpl('/views/owner/cabinet.tpl', $tpl_data);
load_tpl('/views/owner/footer.tpl');
