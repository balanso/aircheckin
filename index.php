<?
require_once __DIR__ . '/config.php';
require_once ROOT . '/vendor/autoload.php';
require_once ROOT . '/lib/db.php';
require_once ROOT . '/lib/common.php';

use Monolog\ErrorHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

if (DEBUG) {
  error_reporting(E_ALL);
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  ini_set('log_errors', true);
  ini_set('error_log', $_SERVER['DOCUMENT_ROOT'] . '/php_errors.log');
} else {
  error_reporting(E_ERROR);
  ini_set('display_errors', 0);
  ini_set('display_startup_errors', 0);
  ini_set('log_errors', true);
  ini_set('error_log', $_SERVER['DOCUMENT_ROOT'] . '/php_errors.log');
}

// Logger
$formatter = new LineFormatter("[%datetime%] %channel% %level_name%: %message% %context% %extra%\n", "H:i:s", null, false, true);
$log       = new Logger('router');
$handler   = new StreamHandler(__DIR__ . '/logs/info_' . date('d.m.y') . '.log', DEBUG ? Logger::DEBUG : Logger::WARNING);
$handler->setFormatter($formatter);
$log->pushHandler($handler);


// Error logger
$error_log = new Logger('router');
$handler   = new StreamHandler(__DIR__ . '/logs/error_' . date('d.m.y') . '.log', Logger::NOTICE);
$handler->setFormatter($formatter);
$error_log->pushHandler($handler);

if (DEBUG) {
  $error_log->pushHandler(new Monolog\Handler\StreamHandler("php://output", Monolog\Logger::ERROR));
}

ErrorHandler::register($error_log);

$router = new \Bramus\Router\Router();
$log->info('---------------- S T A R T E D ---------------');


$router->get('/', function () {
  go('/admin');
});

// переделать на POST, all для теста
$router->all('/{controller}/{script_name}_ajax', function ($controller, $script) {
  global $db, $log;
  if (is_file(__DIR__ . '/controllers/' . $controller . '/' . $script . '_ajax.php')) {
    $log = $log->withName($controller);
    include __DIR__ . '/controllers/' . $controller . '/' . $script . '_ajax.php';
  }
});

$router->all(
  '/{controller}/{action}(/[a-z0-9_-]+)?(/[a-z0-9_-]+)?',
  // '/{controller}/{action}',
  function ($controller, $action = '', $param1, $param2) {
  global $db, $log;

  if (is_file(__DIR__ . '/controllers/' . $controller . '/controller.php')) {
    $log = $log->withName($controller);
    include __DIR__ . '/controllers/' . $controller . '/controller.php';
  }
});

$router->get('/{controller}', function ($controller, $action = '') {
  global $db, $log;
  if (is_file(__DIR__ . '/controllers/' . $controller . '/controller.php')) {
    $log = $log->withName($controller);
    include __DIR__ . '/controllers/' . $controller . '/controller.php';
  }
});


// Run it!
$router->run();
