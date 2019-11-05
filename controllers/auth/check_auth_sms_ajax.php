<?
header('Access-Control-Allow-Origin: ' . CLIENT_SITE_URL);
if (!empty($_POST['phone']) && !empty($_POST['code'])) {
  $phone = preg_match('/\+7[\d]{10}/', $_POST['phone'], $matches);
  $phone = $matches[0] ?? null;
  $code  = preg_match('/[\d]{4}/', $_POST['code'], $matches);
  $code  = $matches[0] ?? null;

  if (!empty($code) && !empty($phone)) {
    $check_code = $db->getOne(
      "SELECT code FROM sms_check_codes WHERE phone=?s AND expiration_date>?i ORDER BY id DESC",
      $phone, time());

    if ($check_code == $code) {
      json_answer('Check success');
    } else {
      json_answer('error', 'Не верный проверочный код, попробуйте ещё раз');
    }
  } else {
    json_answer('error', 'Empty code or phone');
  }
} else {
  json_answer('error', 'Empty code or phone');
}
