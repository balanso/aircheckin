<?
check_user_access('admin');


$statuses = [ORDER_STATUS['pay_complete_sber'], ORDER_STATUS['pay_complete_custom'], ORDER_STATUS['ready_for_money_transfer']];

$orders = $db->getAll(
  "SELECT orders.*,marketing_agents.name as marketing_agent_name, service_agents.name as service_agent_name
			FROM orders
			JOIN marketing_agents ON orders.marketing_agent_id = marketing_agents.id
			JOIN service_agents ON orders.service_agent_id = service_agents.id
			WHERE date_out >= ?i AND status IN (?a) ORDER BY id DESC"
, time()-MONTH, $statuses);

$aparts = $db->getAll("SELECT name,id FROM aparts");

load_tpl('/views/admin/stat/apart/main.tpl', ['orders' => $orders, 'aparts'=>$aparts]);
