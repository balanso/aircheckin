<?
require_once __DIR__ . '/../config.php';
require_once ROOT . '/vendor/autoload.php';

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

$db                 = get_db_connect();
$ready_orders_id    = [];
$ready_for_pay_time = time() - 12 * 60 * 60 + 60; // 12 часов + 1 минута.
                                                  //скрипт запускается в 12:00, нужно найти договора прошедшие статус "расчёт" раньше прошлого дня 23:59

$ready_orders = $db->getAll("
  SELECT order_id
  FROM order_status_history
  WHERE order_id IN (
    SELECT id FROM orders WHERE moved_to_balance=0)
    AND status_id IN (?i, ?i) AND created_at < ?i
      ", ORDER_STATUS['pay_complete_sber'], ORDER_STATUS['pay_complete_custom'], $ready_for_pay_time);

if (!empty($ready_orders)) {
  foreach ($ready_orders as $key => $value) {
    $ready_orders_id[] = $value['order_id'];
  }

  if (!empty($ready_orders_id)) {
    $time         = time();
    $ready_orders = $db->getAll("
    SELECT orders.*, aparts.owner_id FROM orders JOIN aparts ON aparts.id=apart_id WHERE orders.id IN (?a)
    ", $ready_orders_id);

    if (!empty($ready_orders)) {
      foreach ($ready_orders as $key => $order) {
        $cleanings_cost = $order['cleanings'] * $order['cleaning_cost'];
        $marketing_cost = $order['tarif_total'] / 100 * $order['marketing_agent_percent'];
        $service_cost   = $order['tarif_total'] / 100 * $order['service_agent_percent'];
        $tarif_total    = $order['tarif_total'];
        $profit         = $tarif_total - $service_cost - $marketing_cost - $cleanings_cost;

        $balance_update[] = [
          'order_id' => $order['id'],
          'owner_id' => $order['owner_id'],
          'sum'      => rub_to_kop($profit),
        ];

        $update_order_ids[] = $order['id'];
      }


      if (!empty($update_order_ids) && !empty($balance_update)) {
        $db->query("UPDATE orders SET moved_to_balance=1, status=?i WHERE id IN (?a)", ORDER_STATUS['ready_for_money_transfer'], $update_order_ids);
        add_order_status_history($update_order_ids, ORDER_STATUS['ready_for_money_transfer']);


        $query = [];
        foreach ($balance_update as $key => $val) {
          $query[] = $db->parse("(?i,?i,?i,?i)", $val['order_id'], $val['owner_id'], $val['sum'], $time);
        }

        if (!empty($query)) {
          $query = implode(',',$query);
          $db->query("INSERT INTO owners_balance (`order_id`, `owner_id`, `sum`, `created_at`) VALUES $query");
        }

      }
    }
  }
}
