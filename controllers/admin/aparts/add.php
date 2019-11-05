<?
require_once ROOT . '/lib/messaging.php';

$tpl_data = [];
$checked  = true;

load_tpl('/views/admin/template/header.tpl');

if (!empty($_POST)) {
  $required_fields = ['email', 'fio', 'phone'];
  foreach ($required_fields as $key => $value) {
    if (empty($_POST[$value])) {
      $tpl_data['empty_field'] = $value;
      $checked  = false;
    }
  }

  if ($checked) {
    $password = generate_password(10);
    $hash     = create_password_hash($password);
    $user     = add_user([
      'login'    => uniqid(),
      'email'    => $_POST['email'],
      'phone'    => $_POST['phone'],
      'name'     => $_POST['fio'],
      'password' => $hash,
    ]);

    if (!empty($user)) {
      $login =  'id' . $user['id'];
      $owner = add_owner(['user_id' => $user['id'], 'name'=>$user['name']]);
      $db->query("UPDATE users SET login=?s, access_level=10 WHERE id=?i", $login, $user['id']);

      $message_template = load_tpl('/views/admin/add_owner/mail_template.tpl', ['user_login'=>$login, 'user_password'=>$password, 'apk_download_link'=>OWNERS_APK_DOWNLOAD_LINK], true);

      send_mail($user['email'], 'Создан личный кабинет', $message_template);
      load_tpl('/views/admin/add_owner/add_success.tpl', ['owner_name'=>$user['name'], 'login'=>$login, 'password'=>$password, 'email'=>$user['email']]);

    } else {
      $tpl_data = ['message' => "Не удалось создать пользователя для владельца"];
      load_tpl('/views/admin/add_owner/add_form.tpl', $tpl_data);
    }
  } else {
    $tpl_data['message'] = 'Не заполнено поле '.$tpl_data['empty_field'];
    load_tpl('/views/admin/add_owner/add_form.tpl', $tpl_data);
  }
} else {
  load_tpl('/views/admin/add_owner/add_form.tpl', $tpl_data);
}

load_tpl('/views/admin/template/footer.tpl');
