<?
header('Access-Control-Allow-Origin: ' . CLIENT_SITE_URL);
require_once ROOT . '/lib/shorty/shorty.php';

if (!empty($_POST) && isset($_POST['link'])) {
  $link        = $_POST['link'];
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

  $pre_order = $db->getRow("SELECT * FROM pre_orders WHERE id=?i", $pre_order_id);

  if (!empty($pre_order['phone'])) {
    $f_phone = '%' . format_phone7($pre_order['phone'], true) . '%';
    $exist_tenant = $db->getRow("SELECT * FROM tenants WHERE phone LIKE ?s", $f_phone);

    if ($exist_tenant) {
      $name = explode(' ', $exist_tenant['name']);
      $exist_tenant['last_name'] = $name[0] ?? '';
      $exist_tenant['first_name'] = $name[1] ?? '';
      $exist_tenant['second_name'] = $name[2] ?? '';
      $exist_tenant['birthdate'] = date('d.m.Y', $exist_tenant['birthdate']);

      json_answer($exist_tenant);
    } else {
      json_answer('tenant not found');
    }
  } else {
    json_answer('error', 'Phone not found in pre order');
  }


} else {
  json_answer('error', 'POST data or short link not recieved');
}
