<?
check_user_access('admin');

$tenants = $db->getAll("SELECT * FROM tenants ORDER BY id ASC");

foreach ($tenants as $key => $ten) {
	$birthdate = date('d.m.Y', $ten['birthdate']);
	$passport = ($ten['passport_number'] > 0) ? $ten['passport_number'] : 'Не указан';

	$out_tens[] = [
		'name'=>'<span class="tenant_id" data-id="'.$ten['id'].'">'.$ten['name'].'</span>',
		'phone'=>$ten['phone'],
		'passport'=>$passport,
	];
}

if (empty($out_tens)) {
  exit(json_encode(['status' => 'error', 'message' => 'Не найдены договора на указанные даты']));
} else {
  exit(json_encode(['data' => $out_tens]));
}

// echo json_encode(['status'=>'error', 'message'=>'Не получены даты поиска']);
