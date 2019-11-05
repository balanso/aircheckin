<?
header('Access-Control-Allow-Origin: ' . CLIENT_SITE_URL);
require_once ROOT . '/lib/messaging.php';
if (isset($_POST['phone'])) {
  $phone       = preg_match('/\+7[\d]{10}/', $_POST['phone'], $matches);
  $phone       = $matches[0] ?? null;
  $sms_timeout = 60;

  if (check_form_spam('send_auth_sms', $phone, $sms_timeout)) {
    $check_code      = rand(1000, 9999);
    $expiration_date = time() + 60 * 10;
    $db->query("INSERT INTO sms_check_codes SET expiration_date=?i, code=?i, phone=?s",
      $expiration_date, $check_code, $phone);

    $res = send_sms($phone, 'Проверочный код: ' . $check_code);

    if ($res->status == 'OK') {json_answer('Code sended');
    } else {
      json_answer('error', 'sms status: ' . $res->status . ', code: ' . $res->status_code);
    }

  } else {
    json_answer('error', 'Повторите попытку через минуту');
  }
} else {
  json_answer('error', 'Phone not recieved');
}

exit();
