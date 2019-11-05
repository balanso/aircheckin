<?

$data = [];
if (!empty($_GET['tenant_id'])) {
	$data['tenant_id'] = $_GET['tenant_id'];
}

load_tpl('/views/admin/stat/current/main.tpl', $data);