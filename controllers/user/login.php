<?
$content_path = '/views/user/login.tpl';
$tpl_data     = ['referer' => '', 'message' => ''];

if (!empty($_POST)) {
  if (!empty($_POST['login']) && !empty($_POST['password'])) {
    if (verify_password($_POST['login'], $_POST['password'])) {
      $user = get_user_by_login($_POST['login']);
      set_user_auth_cookie($user['id']);

      if (!empty($_POST['referer'])) {
        go($_POST['referer']);
      }

      $tpl_data['message'] = "Вход произведён";
    } else {
      $tpl_data['message'] = "Не верный логин или пароль";
    }
  }
} else {
  if (isset($_GET['referer'])) {
    $tpl_data['message'] = "Не достаточно прав доступа для {$_GET['referer']}";
    $tpl_data['referer'] = $_GET['referer'];

  } elseif ($user = get_user_by_cookie()) {
    $tpl_data['message'] = "Вы авторизованы под {$user['login']}";
  }
}

load_tpl('/views/header_full.tpl');
load_tpl('/views/user/login.tpl', $tpl_data);
load_tpl('/views/admin/template/footer.tpl');
