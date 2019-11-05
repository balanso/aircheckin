<?
check_user_access('admin');

if (!empty($action)) {
  if (is_file(__DIR__ . '/' . $action . '.php')) {
    include __DIR__ . '/' . $action . '.php';
  } elseif (is_file(__DIR__ . '/' . $action . '/controller.php')) {
  	include __DIR__ . '/' . $action . '/controller.php';
  } else {
    switch ($action) {
      case 'get_registration_link':
        include_once __DIR__ . '/get_registration_link/controller.php';
        break;

      case 'stat':
        include_once __DIR__ . '/stat/controller.php';
        break;

      case 'aparts':
        include_once __DIR__ . '/aparts/controller.php';
        break;

      case 'add_user':
        include_once __DIR__ . '/add_user.php';
        break;

      case 'add_owner':
        include_once __DIR__ . '/add_owner.php';
        break;

      default:
        include_once __DIR__ . '/main.php';
        break;
    }
  }
} else {
  include_once __DIR__ . '/main.php';
}
