<?
check_user_access('admin');

if (!empty($_REQUEST['tenant_id'])) {
	$tenant_id = $_REQUEST['tenant_id'];

	$tenant = $db->getRow("
		SELECT * FROM tenants WHERE id=?i", $tenant_id);

	$tenant['citizenship'] = empty($tenant['citizenship']) ? 'не указано' : $tenant['citizenship'];
	$tenant['birthdate'] = $tenant['birthdate'] > 0 ? date('d.m.Y', $tenant['birthdate']) : 'не указана';

	exit(json_html('/views/admin/stat/tenants/tenant_data.tpl',
	  ['tenant' => $tenant]));
}