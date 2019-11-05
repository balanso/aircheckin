<?
$tpl_data = [];
if (!check_user_access('owner', false)) {
  include __DIR__ . '/login.php';
  exit();
}

if (!empty($action)) {
  if (is_file(__DIR__ . '/' . $action . '.php')) {
    include __DIR__ . '/' . $action . '.php';
  } else {
    include __DIR__ . '/login.php';
  }
} else {
	go('/owner/cabinet');
}
