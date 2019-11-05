<?
header('Access-Control-Allow-Origin: ' . CLIENT_SITE_URL);
require_once ROOT . '/lib/shorty/shorty.php';

if (!empty($_POST) && isset($_POST['short_link'])) {
  $link        = $_POST['short_link'];
  $shorty      = new Shorty();
  $shorty_link = $shorty->get($link);

  if ($shorty_link) {
    preg_match('/pre_order#(\d+)#/ui', $shorty_link, $link_data);
    if (!empty($link_data[1])) {
      $pre_order_id = $link_data[1];
    }
  }

  if (!isset($pre_order_id) || empty($pre_order_id)) {
    json_answer('error', 'Pre order not found');
  }

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
        $db->query("UPDATE pre_orders SET phone=?s WHERE id=?i", $phone, $pre_order_id);
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
} else json_answer('error', 'POST data or short link not recieved');
