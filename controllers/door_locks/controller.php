<?
header('Access-Control-Allow-Origin: ' . CLIENT_SITE_URL);
require_once ROOT . '/lib/shorty/shorty.php';

if (!empty($_POST['short_link'])) {
  $link        = $_POST['link'];
  $shorty      = new Shorty();
  $shorty_link = $shorty->get($link);
  json_answer($shorty_link);
} else {
	echo 0;
}
