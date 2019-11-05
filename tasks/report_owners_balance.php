<?
require_once __DIR__ . '/../config.php';
require_once ROOT . '/vendor/autoload.php';
require_once ROOT . '/lib/messaging.php';
require_once ROOT . '/lib/vendor/PayU.php';

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

// Logger
$formatter = new LineFormatter("[%datetime%] %channel% %level_name%: %message% %context% %extra%\n", "H:i:s");
$log       = new Logger('router');
$handler   = new StreamHandler(__DIR__ . '/../logs/tasks_' . date('y.m.d') . '.log', DEBUG ? Logger::DEBUG : Logger::WARNING);
$handler->setFormatter($formatter);
$log->pushHandler($handler);
$log->info('Запущена задача make_orders_ready_for_pay');

require_once ROOT . '/lib/db.php';
require_once ROOT . '/lib/common.php';

$db                   = get_db_connect();
$message              = "<b>Баланс собственников на " . date('d.m.y') . "</b>";
$balance_items        = $db->getAll("SELECT * FROM owners_balance WHERE payed_out=0");
$total_owners_balance = 0;
$total_checkout_sum   = 0;

$conf = get_config('payu');
$comission_percent = $conf['comission_percent'];
$min_comission = $conf['min_comission'];

if (!empty($balance_items)) {
  foreach ($balance_items as $key => $item) {
    $total_owners_balance += $item['sum'];
  }

  // $total_owners_balance = kop_to_rub($total_owners_balance);
  // Pay U забирает 1,2% при переводе, минималка 45р
  if ($total_owners_balance * 100 / (100-$comission_percent) < $min_comission) {
    $checkout_need_sum = $total_owners_balance + $min_comission;
  } else {
    $checkout_need_sum = $total_owners_balance * 100 / (100-$comission_percent);
  }

  $total_owners_balance_rub = kop_to_rub($total_owners_balance);
  $checkout_need_sum_rub        = kop_to_rub($checkout_need_sum);

  $checkout_wait_sum = $checkout_need_sum_rub;

  $orders_url = "https://test.aircheckin.ru/admin/orders?status=20";
  $message .= "\nНа балансе собственников {$total_owners_balance_rub}₽ <a href=\"{$orders_url}\">[Подробнее]</a>";
  $message .= "\nДля выведения из PayU необходимо {$checkout_need_sum_rub}₽";
} else {
  $message .= "\nДоступно 0₽";
  $checkout_wait_sum = 0;
}

//скрипт запускается в 09:00, нужно найти расчёты старшепрошлого дня 23:59 (12 часов)
$report_orders_time = time() - 9 * 60 * 60 + 60; // 9 часов и 1 минута.
$wait_orders        = $db->getAll("SELECT * FROM orders WHERE status=11 OR status=12 AND status_updated_at < ?i", $report_orders_time);

if (!empty($wait_orders)) {
  $total_profit = 0;
  $orders       = [];

  foreach ($wait_orders as $key => $order) {
    $cleanings_cost = $order['cleanings'] * $order['cleaning_cost'];
    $marketing_cost = $order['tarif_total'] / 100 * $order['marketing_agent_percent'];
    $service_cost   = $order['tarif_total'] / 100 * $order['service_agent_percent'];
    $profit         = $order['tarif_total'] - $service_cost - $marketing_cost - $cleanings_cost;
    $total_profit += $profit;
  }

  // Pay U забирает 1,2% при переводе, минималка 45р
  if ($total_profit * 100 / (100-$comission_percent) < kop_to_rub($min_comission)) {
    $total_profit += kop_to_rub($min_comission);
  } else {
    $total_profit = $total_profit * 100 / (100-$comission_percent);
  }

  $total_profit_wait = rounding_price($total_profit);
  $orders_count      = count($wait_orders);
// $message .= "\nДоговоров готовых к выплате: $orders_count на $total_profit₽";
  $char = $total_profit_wait >= 0 ? '+' : '';

  $orders_url = WEB_ROOT . "/admin/orders?status[]=11&status[]=12&status_updated_at_less={$report_orders_time}";
  $message .= "\nОжидается $char{$total_profit_wait}₽ в 12:00 <a href=\"{$orders_url}\">[Подробнее]</a>";
  $checkout_wait_sum += $total_profit_wait;
  $message .= "\nИтого {$checkout_wait_sum}₽";
} else {
  $message .= "\nНовой прибыли не ожидается";
}

$payu_conf = get_config('payu');
$payu      = new PayU($payu_conf['merchant'], '', $payu_conf['secret']);
$balance   = $payu->getBalance();

if ($balance || $balance === 0) {
  $db->query("INSERT INTO payu_balance SET balance=?i, created_at=?i", $balance, time());

  if ($balance < $checkout_wait_sum) {
    $message .= "\n<b>Внимание!</b> В PayU не достаточно средств для выплаты, на счёте {$balance}₽";
  } else {
    $message .= "\nНа счёте в PayU {$balance}₽";
    $message .= "\n<b>Все системы в норме</b>, Россия - чемпион!";
  }
} else {
  $message .= "\n<b>Внимание!</b> Не удалось получить баланс PayU, необходима ручная проверка";
}

send_tg($message);
