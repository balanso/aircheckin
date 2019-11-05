<?
require_once ROOT . '/vendor/autoload.php';
require_once ROOT . '/lib/user.php';
require_once ROOT . '/lib/db.php';

function curl_get_file_contents($URL)
{
  $c = curl_init();
  curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($c, CURLOPT_URL, $URL);
  $contents = curl_exec($c);
  curl_close($c);

  if ($contents) return $contents;
  else return FALSE;
}


//Функция округления цены до нужной точности
function rounding_price($price, $precision = 0.01){
    # $price - принимает цену, которую необходимо округлить
    # $precision - указывает точность до которой необходимо выполнить округление
    #     0.01 - округление цены до копеек (минимально-возможная цена)
    #     0.05 - округление цены до 5 копеек
    #     0.10 - округление цены до 10 копеек
    #     0.50 - округление цены до 50 копеек
    #     1.00 - округление цены до 1 рубля (копейки будут откинуты)
  $price = intval(round($price * 100, 3));
        // В переменной $price, в round второй аргумент указывает сколько знаков
        // (не считая копеек) стоит игнорировать математическое округление
        // То есть в строке $price = intval(round($price * 100, 3)) имеет значение 3
        // значит 5 знаков (3 указанных, и 2 знака копеек) после запятой
        // не будут использоваться для математического округления
        // 16,49 999  - будет преобразовано в 16,49 (при 0,01) или 16,45 (при 0,05) или 16,40 (при 0,10)
        // 16,49 9994 - будет преобразовано в 16,49 (при 0,01) или 16,45 (при 0,05) или 16,40 (при 0,10)
        // 16,49 9995 - будет преобразовано в 16,50
        // 16,49 9999 - будет преобразовано в 16,50
  $precision = round($precision, 2);
  return round(floor(floor($price / $precision / 100)) * $precision);
}

function rub_to_kop($rub_num) {
  return rounding_price($rub_num * 100);
}

function kop_to_rub($kop_num) {
  return rounding_price($kop_num / 100);
}

/**
 * @param $date string
 * @param $format string
 */
function str_date_to_format($date, $format)
{
  $ts = strtotime($date);

  return date($format, $ts);
}

function format_phone7($phone, $for_search = false)
{

  $phone = preg_replace("/[^0-9+]/", "", $phone);

  if (!empty($phone) && strlen($phone) > 5) {
    $phone_start = substr($phone, 0, 1);

    switch ($phone_start) {
      case 9:
      if (strlen($phone) == 10) {
        $formatted_phone = '7' . $phone;
      }

      break;
      case 8:
      if (strlen($phone) == 11) {
        $formatted_phone = '7' . substr($phone, 1);
      }

      break;
      case 7:
      if (strlen($phone) == 11) {
        $formatted_phone = ($phone);
      }

      break;

      break;
      case '+':
      if (strlen($phone) == 12 && substr($phone, 1, 1) == 7) {
        $formatted_phone = '7' . substr($phone, 2);
      }
      break;

      default:
      if (strlen($phone) == 10) {
        $formatted_phone = '7' . $phone;
      }
    }

    if (!empty($formatted_phone)) {
      if ($for_search) {
        if (substr($formatted_phone, 0, 2) == '+7') {
          $trim_num = 2;
        } elseif (substr($formatted_phone, 0, 1) == 8) {
          $trim_num = 1;
        } elseif (substr($formatted_phone, 0, 1) == 7) {
          $trim_num = 1;
        } else {
          $trim_num = 0;
        }
        return substr($formatted_phone, $trim_num);
      } else {
        return $formatted_phone;
      }
    } else return false;
  }

  return $phone;
}

function format_phone_search($phone)
{
  return substr($phone, -10);

}

/**
 * @param $data
 */
function debug($data = null)
{
  if (!empty($data)) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
    echo PHP_EOL;
  }

  return true;
}

function is_cookie_enabled()
{
  if (!empty($_COOKIE['visitor_id'])) {
    return true;
  }

  return false;
}

/**
 * @param $url
 */
function go($url = '/')
{
  header('Location: ' . $url);
  exit();
}

/* status = error, warning, ok*/
/* data = array of data */
/**
 * @param $status
 * @param array $data
 */
function json_answer($status, $data = null)
{
  if (is_string($status)) {
    if (!$data) {
      $out['status'] = 'ok';
      $out['message'] = $status;
    } else {
      $out['status'] = $status;
    }
  } elseif (is_array($status)) {
    $out['status'] = 'ok';
    $out['data']   = $status;
  }

  if (is_string($data)) {
    $out['message'] = $data;
  } elseif (!empty($data)) {
    $out['data'] = $data;
  }

  $out['answer_timestamp'] = time();

  exit(json_encode($out));
}

/**
 * @param $msg
 */
function json_err($msg)
{
  if (is_array($msg)) {
    $out = array_merge(['type' => 'error'], $msg);
    return json_encode($out);
  } else {
    return json_encode(['type' => 'error', 'data' => $msg]);
  }
}

/**
 * @param $msg
 */
function json_msg($msg)
{
  if (is_array($msg)) {
    $out = array_merge(['status' => 'ok', 'type' => 'message'], $msg);
    return json_encode($out);
  } else {
    return json_encode(['status' => 'ok', 'type' => 'message', 'data' => $msg]);
  }
}

/**
 * @param $form_name
 * @param $data
 * @param $timeout
 */
function check_form_spam($form_name, $data, $timeout = 60)
{
  $db = get_db_connect();

  $sender = [
    'ip'         => $_SERVER["REMOTE_ADDR"],
    'user_agent' => $_SERVER["HTTP_USER_AGENT"],
    'form_name'  => $form_name,
    'form_data'  => json_encode($data),
    'date_sended' => time(),
  ];

  $last_send = $db->getOne(
    "SELECT date_sended FROM sended_forms WHERE form_name=?s AND ip=?s AND user_agent=?s ORDER by id DESC"
    , $form_name, $sender['ip'], $sender['user_agent']);

  if (!$last_send || ($last_send && $last_send + $timeout < time())) {
    $db->query("INSERT INTO sended_forms SET ?u", $sender);
    return true;
  } else {
    return false;
  }
}

/**
 * @param mixed $path
 * @param array $data
 */
function load_tpl($path, $data = [], $to_var = false)
{
  global $db;
  global $user;

  if (!empty($data)) {
    extract($data);
  }

  if (is_array($path)) {
    foreach ($path as $key => $value) {
      $value   = ROOT . '/' . $value;
      $files[] = $value;
    }
  } else {
    $path    = ROOT . '/' . $path;
    $files[] = $path;
  }

  if (!empty($files)) {
    if ($to_var) {
      ob_start();
    }

    foreach ($files as $key => $value) {
      if (is_file($value)) {
        if ($to_var) {
          include $value;
        } else {
          include_once $value;
        }
      } else {
        echo 'Не найден файл ' . $value;
      }
    }

    if ($to_var) {
      return ob_get_clean();
    }
  }

  return true;
}

/**
 * @param $title
 * @param null $text
 */
function json_html_page($title = null, $text = null)
{
  $answer = load_tpl(
    ['/views/page.tpl'],
    ['title' => $title, 'text' => $text], true);

  return json_encode(['status' => 'ok', 'html' => $answer], JSON_HEX_QUOT | JSON_HEX_TAG);
}

/**
 * @param array $files
 * @param array $data
 */
function json_html($files = [], $data = [])
{
  $answer = load_tpl($files, $data, true);
  return json_encode(['status' => 'ok', 'html' => $answer], JSON_HEX_QUOT | JSON_HEX_TAG);
}
/**
 * @param $string
 * @param $encoding
 */
function mb_ucfirst($string, $encoding = 'utf8')
{
  $strlen    = mb_strlen($string, $encoding);
  $firstChar = mb_substr($string, 0, 1, $encoding);
  $then      = mb_substr($string, 1, $strlen - 1, $encoding);
  return mb_strtoupper($firstChar, $encoding) . $then;
}
