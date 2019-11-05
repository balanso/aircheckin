<?
header('Access-Control-Allow-Origin: ' . CLIENT_SITE_URL);
require_once ROOT . '/lib/shorty/shorty.php';

if (!empty($_POST['short_link'])) {
  $link        = $_POST['short_link'];
  $shorty      = new Shorty();
  $shorty_link = $shorty->get($link);

  if ($shorty_link) {
    preg_match('/pre_order#(\d+)#/ui', $shorty_link, $link_data);
    if (!empty($link_data[1])) {
      $pre_order_id = $link_data[1];

      $db = get_db_connect();
      $door_key = $db->getRow("SELECT * FROM door_keys WHERE id IN (SELECT door_key_id FROM aparts WHERE id IN (SELECT apart_id FROM pre_orders WHERE id=?i))", $pre_order_id);

      if (!empty($door_key)) {
        @file_get_contents("https://onoffcloud.ru:2443/api/set?apikey={$door_key['key']}&state=open5");
      	// file_get_contents("https://onoffcloud.ru:2443/api/set?apikey={$door_key['key']}&state=open5");
/*        if (!empty($answer)) {
          $answer = json_decode($answer);
        }*/


        json_answer('Pretty good!');
      }
    }
  }
}
