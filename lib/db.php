<?
require_once ROOT . '/vendor/autoload.php';
$db = get_db_connect();

function get_db_connect()
{
  global $db;

  $db_config = get_config('db');

  if (!empty($db)) {
    return $db;
  } else {
    $db = new SafeMySQL($db_config);
    return $db;
  }
}

/**
 * @param $order_id
 * @param $status_id
 */
function add_order_status_history($order_id, $status_id, $description = '')
{
  $db = get_db_connect();
  $time = time();

  if (is_array($order_id)) {
  	foreach ($order_id as $key => $value) {
  		$insert_values[] = $db->parse("(?i,?i,?i,?s)", $value, $status_id, $time, $description);

  		// $insert_values[] = [$value, $status_id, $time];
  	}

  	$insert_values = implode(',', $insert_values);
  	$db->query("INSERT INTO order_status_history (order_id, status_id, created_at, description) VALUES $insert_values");
  } else {
    $db->query("INSERT INTO order_status_history SET status_id=?i, order_id=?i, created_at=?i, description=?s", $status_id, $order_id, time(), $description);
  }

}
