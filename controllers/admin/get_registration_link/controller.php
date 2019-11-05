<?
check_user_access('admin');

global $db;
$aparts           = $db->getAll("SELECT id, name, price FROM aparts");
$marketing_agents = $db->getAll("SELECT * FROM marketing_agents");
$service_agents   = $db->getAll("SELECT * FROM service_agents");
$payment_methods  = $db->getAll("SELECT * FROM payment_methods");
// $cleaners         = $db->getAll("SELECT * FROM cleaners");

load_tpl('/views/admin/template/header.tpl');
load_tpl('/views/admin/get_registration_link/main.tpl', [
	'aparts'           => $aparts,
	'marketing_agents' => $marketing_agents,
	'service_agents'   => $service_agents,
	'payment_methods'  => $payment_methods,
	/*'cleaners'         => $cleaners*/]);
load_tpl('/views/admin/get_registration_link/footer.tpl');
load_tpl('/views/admin/template/footer.tpl');
