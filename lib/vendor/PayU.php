<?php

class PayU
{
  const LU_URL               = 'https://secure.payu.ru/order/lu.php';
  const TOKEN_PAYMENT_URL    = 'https://secure.payu.ru/order/tokens/';
  const IDN_URL              = 'https://secure.payu.ru/order/idn.php';
  const IRN_URL              = 'https://secure.payu.ru/order/irn.php';
  const PAYOUT_LINK_CARD_URL = 'https://secure.payu.ru/order/pwa/service.php/UTF/NewPayoutCard';
  const PAYOUT_URL           = 'https://secure.payu.ru/order/prepaid/NewCardPayout';
  const IOS_URL              = 'https://secure.payu.ru/order/ios.php';

  /**
   * Идентификатор мерчанта.
   *
   * @var string
   */
  public $merchantId;
  /**
   * Имя мерчанта.
   *
   * @var string
   */
  public $merchantName;
  /**
   * Секретный ключ.
   *
   * @var string
   */
  public $secretKey;
  /**
   * Режим отладки платежа.
   *
   * @var bool
   */
  public $debug;

  /**
   * @param string $merchantId
   * @param string $merchantName
   * @param string $secretKey
   * @param bool $debug
   */
  public function __construct($merchantId, $merchantName, $secretKey, $debug = false)
  {
    $this->merchantId   = $merchantId;
    $this->merchantName = $merchantName;
    $this->secretKey    = $secretKey;
    $this->debug        = $debug;
  }

  public function getBalance()
  {
    $params = [
      'merchant'  => $this->merchantId,
      'timestamp' => time(),
    ];

    $sig_params = $params;
    unset($sig_params['timestamp']);
    ksort($sig_params);
    $sig_params          = implode('', $sig_params) . $params['timestamp'];
    $params['signature'] = hash_hmac('sha256', $sig_params, $this->secretKey);
    $params_string       = http_build_query($params);
    $balance             = file_get_contents("https://secure.payu.ru/api/pwa/v1/balance?{$params_string}");

    if ($balance) {
      $balance = json_decode($balance, true);

      if (isset($balance['meta']['status']) && $balance['meta']['status']['code'] == 0) {
        if (isset($balance['balances'][$this->merchantId])) {
          $current_balance = $balance['balances'][$this->merchantId];
          $payu_balance    = round($current_balance['Balance']);
          return $payu_balance;
        }
      }
    }

    return false;
  }

  /**
   * Генерация данных для формы оплаты.
   *
   * @param array $data данные платежа
   * @param string $backref URL на который вернется пользователь после оплаты
   * @param string $tokenType если платеж используется для привязки карты, то указываем тип токена (PAY_ON_TIME или PAY_BY_CLICK)
   * @return array данные формы
   */
  public function initLiveUpdateFormData(array $data, $backref, $tokenType = null)
  {
    $data['MERCHANT'] = $this->merchantName;
    $data['DEBUG']    = $this->debug ? 'TRUE' : 'FALSE';
    $data['BACK_REF'] = $backref;

    if ($tokenType) {
      $data['LU_ENABLE_TOKEN'] = '1';
      $data['LU_TOKEN_TYPE']   = $tokenType;
    }

    $data['ORDER_HASH'] = $this->hashLiveUpdateFormData($data);

    return $data;

  }

  /**
   * Платеж с помощью токена.
   *
   * @param array $data данные платежа
   * @param string $token токен привязанной карты
   * @return array результат запроса
   */
  public function createTokenPayment(array $data, $token)
  {
    $data['MERCHANT'] = $this->merchantName;
    $data['REF_NO']   = $token;
    $data['METHOD']   = 'TOKEN_NEWSALE';

    $data['SIGN'] = $this->hashTokenPayment($data);

    $result = $this->sendPostRequest(self::TOKEN_PAYMENT_URL, $data);
    $result = json_decode($result, true);

    return $result;
  }

  /**
   * Выполнение IDN запроса.
   *
   * @param array $data данные IDN запроса
   * @return string результат запроса
   */
  public function sendIdnRequest(array $data)
  {
    $uns_data             = array();
    $uns_data['MERCHANT'] = $this->merchantName;

    $uns_data = array_merge($uns_data, $data);

    $uns_data['ORDER_HASH'] = $this->hashTokenPayment($uns_data, false);

    $result = $this->sendPostRequest(self::IDN_URL, $uns_data);

    return $result;
  }

  /**
   * Выполнение IRN запроса.
   *
   * @param array $data данные IRN запроса
   * @return string результат запроса
   */
  public function sendIrnRequest(array $data)
  {
    $uns_data             = array();
    $uns_data['MERCHANT'] = $this->merchantName;

    $uns_data = array_merge($uns_data, $data);

    $uns_data['ORDER_HASH'] = $this->hashTokenPayment($uns_data, false);

    $result = $this->sendPostRequest(self::IRN_URL, $uns_data);

    return $result;
  }

  /**
   * Генерация данных для формы привязки карты (вывод средств).
   *
   * @param array $data данные запроса
   * @param string $backUrl URL на который вернется пользователь после привязки карты
   * @return array данные формы
   */
  public function initPayoutLinkCardFormData($data, $backUrl)
  {
    $data['MerchID'] = $this->merchantId;
    $data['BackURL'] = $backUrl;

    $data['Signature'] = $this->hashPayoutData($data);

    return $data;
  }

  /**
   * Запрос вывода средств.
   *
   * @param array $data данные платежа
   * @param string $token токен привязанной карты
   * @return array результат запроса
   */
  public function sendPayoutRequest(array $data, $token)
  {
    $data['merchantCode'] = $this->merchantId;
    $data['payin']        = '1';
    $data['token']        = $token;

    $data['signature'] = $this->hashPayoutData($data);

    $result = $this->sendPostRequest(self::PAYOUT_URL, $data);
    $result = json_decode($result, true);

    return $result;
  }

  /**
   * Обработка IPN запроса.
   *
   * @return string строка ответа на IPN запрос
   */
  public function handleIpnRequest()
  {
    $ipnPid  = isset($_POST['IPN_PID']) ? $_POST['IPN_PID'] : '';
    $ipnName = isset($_POST['IPN_PNAME']) ? $_POST['IPN_PNAME'] : '';
    $ipnDate = isset($_POST['IPN_DATE']) ? $_POST['IPN_DATE'] : '';

    $date = date('YmdHis');
    $hash =
    strlen($ipnPid[0]) . $ipnPid[0] .
    strlen($ipnName[0]) . $ipnName[0] .
    strlen($ipnDate) . $ipnDate .
    strlen($date) . $date;
    $hash = hash_hmac('md5', $hash, $this->secretKey);

    $result = '<EPAYMENT>' . $date . '|' . $hash . '</EPAYMENT>';

    return $result;
  }

  /**
   * @param integer $refNo
   * @return string
   */
  public function sendIosRequest($refNo)
  {
    $data = array(
      'MERCHANT' => $this->merchantName,
      'REFNOEXT' => $refNo,
    );
    $data['HASH'] = $this->hashLiveUpdateFormData($data);

    $result = $this->sendPostRequest(self::IOS_URL, $data);

    return $result;
  }

  /**
   * Генерация контрольной суммы для LU запроса.
   *
   * @param array $data
   * @return string
   */
  protected function hashLiveUpdateFormData(array $data)
  {
    $hash = strlen($data['MERCHANT']) . $data['MERCHANT'];
    unset($data['MERCHANT']);

    $hash .= $this->hasher($data);

    return hash_hmac('md5', $hash, $this->secretKey);
  }

  /**
   * Рекурсивная для массивов
   */
  public function hasher($data)
  {
    $ignoredKeys = array(
      'AUTOMODE',
      'BACK_REF',
      'DEBUG',
      'BILL_FNAME',
      'BILL_LNAME',
      'BILL_EMAIL',
      'BILL_PHONE',
      'BILL_ADDRESS',
      'BILL_CITY',
      'DELIVERY_FNAME',
      'DELIVERY_LNAME',
      'DELIVERY_PHONE',
      'DELIVERY_ADDRESS',
      'DELIVERY_CITY',
      'LU_ENABLE_TOKEN',
      'LU_TOKEN_TYPE',
      'LANGUAGE',
    );

    $hash = '';
    foreach ($data as $dataKey => $dataValue) {
      if (is_array($dataValue)) {
        $hash .= $this->hasher($dataValue);
      } else {
        if (!in_array($dataKey, $ignoredKeys, true)) {
          $hash .= strlen($dataValue) . $dataValue;
        }

      }
    }
    return $hash;
  }

  /**
   * Генерация контрольной суммы для платежа с помощью токена.
   *
   * @param array $data
   * @return string
   */
  protected function hashTokenPayment(array $data, $sort = true)
  {
    if ($sort) {
      ksort($data);
    }

    $hash = '';
    foreach ($data as $dataValue) {
      $hash .= strlen($dataValue) . $dataValue;
    }
    $hash = hash_hmac('md5', $hash, $this->secretKey);

    return $hash;
  }

  /**
   * Генерация контрольной суммы для запросов типа Payout
   *
   * @param array $data
   * @return string
   */
  protected function hashPayoutData(array $data)
  {
    ksort($data);

    $hash = implode($data) . $this->secretKey;
    $hash = md5($hash);

    return $hash;
  }

  /**
   * @param string $url
   * @param array $data
   * @return string
   */
  protected function sendPostRequest($url, array $data)
  {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $result = curl_exec($ch);
    if ($result === false) {
      echo 'CURL error: ' . curl_error($ch);
    }

    curl_close($ch);

    return $result;
  }
}
