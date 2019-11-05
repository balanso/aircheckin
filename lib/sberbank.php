<?
require_once ROOT . '/vendor/autoload.php';
require_once ROOT . '/lib/common.php';
require_once ROOT . '/config.php';

use Voronkovich\SberbankAcquiring\Client;
use Voronkovich\SberbankAcquiring\Currency;

$sber_client = get_sber_client();
/**
 * @return mixed
 */
function get_sber_client()
{
  global $sber_client;
  $sber = get_config('sberbank');

  if (empty($sber_client)) {
    $sber_client = new Client([
      'userName' => ($sber['mode'] == 'test') ? $sber['test_user'] : $sber['user'],
      'password' => ($sber['mode'] == 'test') ? $sber['test_password'] : $sber['password'],
      'language' => 'ru',
      'currency' => Currency::RUB,
      'apiUri'   => ($sber['mode'] == 'test') ? Client::API_URI_TEST : Client::API_URI,
    ]);

  }

  return $sber_client;
}

/**
 * @param $order_id
 * @param $cost
 * @param $type
 * @return mixed
 */
function create_sber_link($order_id, $cost, $type = 'tarif', $back_url = '')
{
  $config      = get_config('sberbank');
  $sber_client = get_sber_client();
  $db          = get_db_connect();

  if ($type == 'tarif') {
    $order_type            = 't';
    $params['description'] = 'Договор аренды нежилого помещения (апартаментов) №' . $order_id;
  } elseif ($type == 'deposit') {
    $order_type            = 'd';
    $params['description'] = 'Депозит в счёт договора аренды нежилого помещения (апартаментов) №' . $order_id;
  }

  $params['orderNumber'] = $order_type . time();

  // You can pass additional parameters like a currency code and etc.
  $params['currency'] = Currency::RUB;

  if (empty($back_url)) {
    $params['failUrl']   = $config['fail_url'];
    $params['returnUrl'] = $config['success_url'];
  } else {
    $params['failUrl']   = $back_url . '&status=fail';
    $params['returnUrl'] = $back_url . '&status=success';
  }

  $params['amount']         = $cost . '00';
  $params['expirationDate'] = date("Y-m-d\TH:m:s", strtotime("+3 month"));

  $result = $sber_client->execute('/payment/rest/registerPreAuth.do', $params);

  if (!empty($result['formUrl']) && !empty($result['orderId'])) {
    $db->query(
      "INSERT INTO sber_orders SET order_id=?i, sberbank_order_id=?s, sberbank_order_number=?s, amount=?i, type=?s, created_at=?i, status=0"
      , $order_id, $result['orderId'], $params['orderNumber'], $cost, $order_type, time());

    return $result['formUrl'];
    // 'https://securepayments.sberbank.ru/payment/merchants/sbersafe_id/payment_ru.html?mdOrder='.$result['formUrl']
  } else {
    return false;
  }
}
/**
 * @param $data
 */
function check_data($data)
{
  if (!empty($data['checksum'])) {
    $checksum = $data['checksum'];
    unset($data['checksum']);
    ksort($data);

    foreach ($data as $key => $value) {
      $data_string[] = $key . ';' . $value;
    }

    $data_string = implode(';', $data_string) . ';';
    $key         = 'gdf0brp9mpon3pe0o94q0bsssi';
    $hmac        = hash_hmac('sha256', $data_string, $key);
    $hmac        = strtoupper($hmac);

    if ($hmac == $checksum) {
      return true;
    }
  }

  return false;
}
