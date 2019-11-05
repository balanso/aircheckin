<?php
require_once ROOT . '/lib/common.php';
/**
 * Shorty: A simple URL shortener.
 *
 * @copyright Copyright (c) 2011, Mike Cao <mike@mikecao.com>
 * @license   MIT, http://www.opensource.org/licenses/mit-license.php
 */
class Shorty
{
  /**
   * Default characters to use for shortening.
   *
   * @var string
   */
  private $chars = 'XPzSI6v5DqLuBtVWQARy2mfwkC14F8HUTOG0aJiYpNrl9Zxgbd3Khsno7jMeEc';

  /**
   * Salt for id encoding.
   *
   * @var string
   */
  private $salt = 'HjccbzxtvgbjyDctktycrjujLj,hfQW';

  /**
   * Length of number padding.
   */
  private $padding = 15;

  /**
   * Hostname
   */
  private $hostname = '';

  /**
   * @var string
   */

  /**
   * PDO database connection.
   *
   * @var object
   */
  private $connection = null;

  /**
   * Whitelist of IPs allowed to save URLs.
   * If the list is empty, then any IP is allowed.
   *
   * @var array
   */
  private $whitelist = array();

  /**
   * Constructor
   *
   * @param string $hostname Hostname
   * @param object $db Database connection
   */

  public $url_id = 0;

  public function __construct()
  {
    $this->hostname = stripos($_SERVER['SERVER_PROTOCOL'], 'https') === true ? 'https://' : 'http://' . $_SERVER['HTTP_HOST'];
    $this->db       = get_db_connect();
  }

  /**
   * Gets the character set for encoding.
   *
   * @return string Set of characters
   */
  public function get_chars()
  {
    return $this->chars;
  }

  /**
   * Sets the character set for encoding.
   *
   * @param string $chars Set of characters
   */
  public function set_chars($chars)
  {
    if (!is_string($chars) || empty($chars)) {
      throw new Exception('Invalid input.');
    }
    $this->chars = $chars;
  }

  /**
   * Gets the salt string for encoding.
   *
   * @return string Salt
   */
  public function get_salt()
  {
    return $this->salt;
  }

  /**
   * Sets the salt string for encoding.
   *
   * @param string $salt Salt string
   */
  public function set_salt($salt)
  {
    $this->salt = $salt;
  }

  /**
   * Gets the padding length.
   *
   * @return int Padding length
   */
  public function get_padding()
  {
    return $this->padding;
  }

  /**
   * Sets the padding length.
   *
   * @param int $padding Padding length
   */
  public function set_padding($padding)
  {
    $this->padding = $padding;
  }

  /**
   * Converts an id to an encoded string.
   *
   * @param int $n Number to encode
   * @return string Encoded string
   */
  public function encode($n)
  {
    $k = 0;

    if ($this->padding > 0 && !empty($this->salt)) {
      $k = self::get_seed($n, $this->salt, $this->padding);
      $n = (int) ($k . $n);
    }

    return self::num_to_alpha($n, $this->chars);
  }

  /**
   * Converts an encoded string into a number.
   *
   * @param string $s String to decode
   * @return int Decoded number
   */
  public function decode($s)
  {
    $n = self::alpha_to_num($s, $this->chars);

    return (!empty($this->salt)) ? substr($n, $this->padding) : $n;
  }

  /**
   * Gets a number for padding based on a salt.
   *
   * @param int $n Number to pad
   * @param string $salt Salt string
   * @param int $padding Padding length
   * @return int Number for padding
   */
  public static function get_seed($n, $salt, $padding)
  {
    $hash = md5($n . $salt);
    $dec  = hexdec(substr($hash, 0, $padding));
    $num  = $dec % pow(10, $padding);
    if ($num == 0) {
      $num = 1;
    }

    $num = str_pad($num, $padding, '0');

    return $num;
  }

  /**
   * Converts a number to an alpha-numeric string.
   *
   * @param int $num Number to convert
   * @param string $s String of characters for conversion
   * @return string Alpha-numeric string
   */
  public static function num_to_alpha($n, $s)
  {
    $b = strlen($s);
    $m = $n % $b;

    if ($n - $m == 0) {
      return substr($s, $n, 1);
    }

    $a = '';

    while ($m > 0 || $n > 0) {
      $a = substr($s, $m, 1) . $a;
      $n = ($n - $m) / $b;
      $m = $n % $b;
    }

    return $a;
  }

  /**
   * Converts an alpha numeric string to a number.
   *
   * @param string $a Alpha-numeric string to convert
   * @param string $s String of characters for conversion
   * @return int Converted number
   */
  public static function alpha_to_num($a, $s)
  {
    $b = strlen($s);
    $l = strlen($a);

    for ($n = 0, $i = 0; $i < $l; $i++) {
      $n += strpos($s, substr($a, $i, 1)) * pow($b, $l - $i - 1);
    }

    return $n;
  }

  /**
   * Looks up a URL in the database by id.
   *
   * @param string $id URL id
   * @return array URL record
   */
  public function fetch($id)
  {
    return $this->db->getRow('SELECT * FROM urls WHERE id = ?s', $id);
  }

  /**
   * Attempts to locate a URL in the database.
   *
   * @param string $url URL
   * @return array URL record
   */
  public function find($url)
  {
    $statement = $this->db->getRow(
      'SELECT * FROM urls WHERE url = ?s', $url
    );

    return $statement;
  }

  /**
   * Stores a URL in the database.
   *
   * @param string $url URL to store
   * @return int Insert id
   */
  public function store($url)
  {
    $datetime = time();

    $statement = $this->db->query(
      'INSERT INTO urls (url, created) VALUES (?s,?s)',
      $url, $datetime);

    return $this->db->insertId();
  }

  /**
   * Updates statistics for a URL.
   *
   * @param int $id URL id
   */
  public function update($id)
  {
    $datetime = date('Y-m-d H:i:s');

    $statement = $this->db->query(
      'UPDATE urls SET hits = hits + 1, accessed = ?s WHERE id = ?i'
      , $datetime, $id);
  }

  /**
   * Sends a redirect to a URL.
   *
   * @param string $url URL
   */
  public function redirect($url)
  {
    header("Location: $url", true, 301);
    exit();
  }

  /**
   * Sends a 404 response.
   */
  public function not_found()
  {
    header('HTTP/1.1 404 Not Found');
  }

  /**
   * Sends an error message.
   *
   * @param string $message Error message
   */
  public function error($message)
  {
    exit("<h1>$message</h1>");
  }

  /**
   * @param $action
   * @param $url
   */
  public function add($url)
  {
    $url    = urldecode($url);
    $result = $this->find($url);

    // Not found, so save it
    if (empty($result)) {
      $id           = $this->store($url);
      $url          = $this->encode($id);
      $this->url_id = $id;
    } else {
      $url          = $this->encode($result['id']);
      $this->url_id = $result['id'];
    }

    return $url;
  }

  /**
   * @param $url
   */
  public function get($url)
  {
    if (preg_match('/^([a-zA-Z0-9]+)$/', $url, $matches)) {
      $id     = self::decode($matches[1]);
      $result = $this->fetch($id);

      if (!empty($result)) {
        $this->update($id);

        // $this->redirect($result['url']);
        return ($result['url']);
      } else {
        return false;
      }
    }
  }

}
