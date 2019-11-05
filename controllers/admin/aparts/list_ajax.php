<?
check_user_access('admin');

$aparts = $db->getAll("SELECT * FROM aparts ORDER BY id DESC");

foreach ($aparts as $key => $ap) {
	$owner_name = $db->getOne("SELECT name FROM owners WHERE id=?i", $ap['owner_id']);

	$out_aps[] = [
		'name'=>'<span class="apart_id" data-id="'.$ap['id'].'">'.$ap['name'].'</span>',
		'address'=>$ap['address'],
		'owner'=>$owner_name,
	];
}

if (empty($out_aps)) {
  exit(json_encode(['status' => 'error', 'message' => 'Не найдены договора на указанные даты']));
} else {
  exit(json_encode(['data' => $out_aps]));
}

// echo json_encode(['status'=>'error', 'message'=>'Не получены даты поиска']);
