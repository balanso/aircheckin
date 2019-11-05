<?
$tpl_data = [];

if (!empty($_POST)) {
  if (!empty($_POST['login']) && !empty($_POST['password'])) {

  	$exist_user = get_user_by_login($_POST['login']);

  	if ($exist_user) {
  		$tpl_data = ['message'=>'Пользователь с таким login уже существует'];
  	} else {
  		$password = create_password_hash($_POST['password']);
  		$user = add_user([
        'login'=>$_POST['login'],
        'password'=>$password,
      ]);
  		$tpl_data = ['message'=>"Пользователь {$user['login']} зарегистрирован, ID {$user['id']}"];
  	}
  }
}

load_tpl('/views/admin/template/header.tpl');
load_tpl('/views/admin/add_user.tpl', $tpl_data);
load_tpl('/views/admin/template/footer.tpl');
