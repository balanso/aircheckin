<?
require_once ROOT . '/lib/user.php';

if (isset($action) && !empty($action)) {
  if ($action == 'registration') {
    include_once ('registration.php');
  } elseif ($action == 'logout') {
    remove_user_cookies();
    go('/user/login');
  } else {
    include_once ('login.php');
  }
}
