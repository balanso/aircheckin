<?
check_user_access('admin');

require_once ROOT . '/lib/messaging.php';

if (isset($_POST['phone']) && isset($_POST['url'])) {
  $phone = preg_match('/\+7[\d]{10}/', $_POST['phone'], $matches);
  $phone = $matches[0] ?? null;

  $res = send_sms($phone, 'Ссылка на регистрацию: ' . $_POST['url']);

  if ($res->status == 'OK') {
  	json_answer('Code sended');
  } else {
    json_answer('error', 'sms status: ' . $res->status . ', code: ' . $res->status_code);
  }
}
