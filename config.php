<?
define('ROOT', __DIR__);
define('BASEPATH', ROOT);

define('TOKEN_SECRET', "-");
define('TG_BOT_TOKEN', '-');
define('ORDER_TIMEOUT_SEC', 30 * 60);
define('MINUTE', 60);
define('HOUR', MINUTE * 60);
define('DAY', HOUR * 24);
define('WEEK', DAY * 7);
define('MONTH', DAY * 30);
define('ADMIN_EMAIL', '-');

define('MODE', 'dev');
define('DEBUG', true);

switch (MODE) {
  case 'dev':
    $client_site_url     = 'http://dev-aeroapart.ru';
    $aircheckin_web_root = 'http://dev-aircheckin.ru';
    break;

  case 'test':
    $client_site_url     = 'https://aeroapart.ru';
    // $client_site_url     = 'https://test.autoapartlocks.tmweb.ru';
    $aircheckin_web_root = 'https://test.aircheckin.ru';
    break;

  case 'work':
    $client_site_url     = 'https://aeroapart.ru';
    $aircheckin_web_root = 'https://aeroapart.aircheckin.ru';
    break;

  default:
    break;
}

define('WEB_ROOT', $aircheckin_web_root);

define('CLIENT_SITE_URL', $client_site_url);
define('AIRCHECKIN_WEB_ROOT', $aircheckin_web_root);

define('REGISTRATION_LINK_ROOT_URL', CLIENT_SITE_URL . '/aircheckin');
define('OWNERS_APK_DOWNLOAD_LINK', 'https://play.google.com/store/apps/details?id=ru.csero.apartservice');
define('AIRCHECKIN_ROOT_AJAX_URL', AIRCHECKIN_WEB_ROOT);


define('ORDER_STATUS', [
  'registration_link' => 1,
  'temporary_registration_link' => 2,
  'registered' => 9,
  'pay_hold_sber' => 10,
  'pay_complete_sber' => 11,
  'pay_complete_custom' => 12,
  'ready_for_money_transfer' => 20,
  'nulled_registration_timeout' => 100,
  'nulled_return_sber' => 101,
  'wait_payout_answer' => 190,
  'order_completed_paid' => 200,
  'order_completed' => 201,
]);

/**
 * @param $name
 * @return mixed
 */
function get_config($name)
{
  $config = [];

  switch (MODE) {
    case 'dev':
      $config['db'] = array(
        'host' => 'localhost',
        'user' => '',
        'pass' => '',
        'db'   => 'aircheckin',
      );
      break;

    default:
      $config['db'] = array(
        'host' => 'localhost',
        'user' => '-',
        'pass' => '-',
        'db'   => 'admin_aeroapart',
      );
      break;
  }

  $config['payu'] = array(
    'merchant'=>'-',
    'secret'=>'-',
    'comission_percent'=>1.2,
    'min_comission'=>4500, //в копейках
  );

  $config['ofd'] = array(
    'ferma_login'    => '-',
    'ferma_password' => '-',
    'inn' => '-',
  );

  $config['sberbank'] = array(
    'user'          => '--api',
    'password'      => '-',
    'test_user'     => '-',
    'test_password' => '-',
    // 'mode'          => 'test',
    'mode'          => 'work',
    'success_url'   => AIRCHECKIN_WEB_ROOT . '/pay/success',
    'fail_url'      => AIRCHECKIN_WEB_ROOT . '/pay/fail',
  );

  if ($config['sberbank']['mode'] == 'test') {
    $config['sberbank']['pay_url'] = 'https://3dsec.sberbank.ru/payment/merchants/sbersafe_id/payment_ru.html?mdOrder=';
  } else {
    $config['sberbank']['pay_url'] = 'https://securepayments.sberbank.ru/payment/merchants/sbersafe_id/payment_ru.html?mdOrder=';
  }

  if (!empty($config[$name])) {
    return $config[$name];
  } else {
    return [];
  }
}
