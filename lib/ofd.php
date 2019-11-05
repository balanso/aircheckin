<?
require_once ROOT . '/vendor/autoload.php';
require_once ROOT . '/config.php';
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * @param $sber_order_id
 * @param $type
 * @param $title
 * @param $price
 * @param $reciever_name_doc
 * @param $reciever_email
 * @param $reciever_phone
 */
function ofd_get_ferma_auth_token()
{
  $ofd_config = get_config('ofd');
  $data       = [
    'Login'    => $ofd_config['ferma_login'],
    'Password' => $ofd_config['ferma_password'],
  ];

  $db         = get_db_connect();
  $last_token = $db->getRow("SELECT expiration_date, token FROM ofd_tokens WHERE type=1 ORDER BY id DESC");

  if ($last_token && $last_token['expiration_date'] > time() + 60) {
    $token = $last_token['token'];
  } else {
    $url = 'https://ferma.ofd.ru/api/Authorization/CreateAuthToken';
    // $url = 'https://ferma-test.ofd.ru/api/Authorization/CreateAuthToken';
    $content = json_encode($data);

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER,
      array("Content-type: application/json"));
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $content);

    $json_response = curl_exec($curl);
    $status        = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    curl_close($curl);

    $response = json_decode($json_response, true);

    if ($status == 200 && isset($response['Data']['AuthToken'])) {
      $token         = $response['Data']['AuthToken'];
      $expiration    = $response['Data']['ExpirationDateUtc'];
      $expiration_ts = strtotime($expiration);
      $db->query("INSERT INTO ofd_tokens SET token=?s, expiration_date=?i", $token, $expiration_ts);
      return ['status' => 'success', 'token' => $token];
    } else {
      return ['status' => 'failed', 'message' => 'Не получен токен', 'data' => print_r($response, true)];
    }
  }
}

/**
 * @param $sber_order_id
 * @param $type
 * @param $title
 * @param $price
 * @param $reciever_name_doc
 * @param $reciever_email
 * @param $reciever_phone
 * @return mixed
 */
function ofd_generate_send_receipt($sber_order_id, $type, $title, $price, $reciever_name_doc, $reciever_email, $reciever_phone)
{
  $auth = ofd_get_ferma_auth_token();
  $db   = get_db_connect();

  if (isset($auth['token'])) {
    $token = $auth['token'];
  } else {
    return $auth;
  }

  // Logger
  $formatter = new LineFormatter("[%datetime%] %channel% %level_name%: %message% %context% %extra%\n", "H:i:s");
  $log       = new Logger('pay');
  $handler   = new StreamHandler(ROOT . '/logs/ofd_' . date('y.m.d') . '.log', DEBUG ? Logger::DEBUG : Logger::INFO);
  $handler->setFormatter($formatter);
  $log->pushHandler($handler);

  $type_id = $db->getOne("SELECT id FROM ofd_receipt_types WHERE name=?s", $type);

  if (empty($type_id)) {
    return ['status' => 'failed', 'data' => 'Не найден тип чека "' . $type . '" в БД'];
  }

  $db->query("INSERT INTO ofd_receipts SET created_at=?i, type=?i, sber_order_id=?i", time(), $type_id, $sber_order_id);
  $receipt_id = $db->insertId();
  $invoice_id = 'a' . $receipt_id;
  $ofd_config = get_config('ofd');

  $receipt_data = array(
    'Request' => array(
      'Inn'             => $ofd_config['inn'],
      'Type'            => $type,
      'InvoiceId'       => $invoice_id,
      'CustomerReceipt' => array(
        'TaxationSystem'        => 'SimpleIn',
        'Email'                 => $reciever_email,
        'Phone'                 => $reciever_phone,
        'PaymentType'           => 1,
        "InstallmentPlace"      => null,
        "InstallmentAddress"    => null,
        "AutomaticDeviceNumber" => null,
        'PaymentAgentInfo'      => null,
        "CorrectionInfo"        => null,
        'ClientInfo'            => array(
          'Name' => $reciever_name_doc,
        ),
        'Items'                 => array(
          0 => array(
            'Label'                    => $title,
            'Price'                    => $price,
            'Quantity'                 => 1.0,
            'Amount'                   => 1.0,
            'Vat'                      => 'VatNo',
            'MarkingCodeStructured'    => null,
            'MarkingCode'              => null,
            'PaymentMethod'            => 3,
            'PaymentType'              => 4,
            'OriginCountryCode'        => '643',
            'CustomsDeclarationNumber' => null,
            'PaymentAgentInfo'         => null,
          ),
        ),
        'PaymentItems'          => null,
        'CustomUserProperty'    => null,
      ),
    ),
  );

  $url = 'https://ferma.ofd.ru/api/kkt/cloud/receipt?AuthToken=' . $token;
// $url = 'https://ferma-test.ofd.ru/api/kkt/cloud/receipt?AuthToken='.$token;
  $content = json_encode($receipt_data);

  $curl = curl_init($url);
  curl_setopt($curl, CURLOPT_HEADER, false);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_HTTPHEADER,
    array("Content-type: application/json"));
  curl_setopt($curl, CURLOPT_POST, true);
  curl_setopt($curl, CURLOPT_POSTFIELDS, $content);

  $json_response = curl_exec($curl);

  $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  curl_close($curl);
  $response = json_decode($json_response, true);

  $log->info('Запрос на создание чека', [$receipt_data, $response]);

  if (isset($response['Status']) && $response['Status'] == 'Success' && isset($response['Data']['ReceiptId'])) {
    $db->query("UPDATE ofd_receipts SET ofd_id=?s, price=?i WHERE id=?i", $response['Data']['ReceiptId'], $price, $receipt_id);
    return ['status' => 'success', 'receipt_id' => $receipt_id, 'ofd_receipt_id' => $response['Data']['ReceiptId']];
  } else {
    return ['status' => 'failed', 'message' => 'Не удалось сформировать чек', 'data' => $response];
  }
}

/**
 * @param $receipt_id
 * @return mixed
 */
function ofd_get_receipt_data($receipt_id)
{
  $auth       = ofd_get_ferma_auth_token();
  $ofd_config = get_config('ofd');

  if (isset($auth['token'])) {
    $token = $auth['token'];
  } else {
    return $auth;
  }

  $receipt_data = array(
    'Request' => array(
      'ReceiptId' => $receipt_id,
    ),
  );

  $url     = 'https://ferma.ofd.ru/api/kkt/cloud/status?AuthToken=' . $token;
  $content = json_encode($receipt_data);

  $curl = curl_init($url);
  curl_setopt($curl, CURLOPT_HEADER, false);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_HTTPHEADER,
    array("Content-type: application/json"));
  curl_setopt($curl, CURLOPT_POST, true);
  curl_setopt($curl, CURLOPT_POSTFIELDS, $content);

  $json_response = curl_exec($curl);

  $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  curl_close($curl);
  $response = json_decode($json_response, true);

  if (isset($response['Data']['Device']['DeviceId'])) {
    $inn          = $ofd_config['inn'];
    $kktregnumber = $response['Data']['Device']['RNM'];
    $fnnumber     = $response['Data']['Device']['FN'];
    $docnumber    = $response['Data']['Device']['FDN'];
    $docsign      = $response['Data']['Device']['FPD'];

    return ['status' => 'success', 'url' => "https://check.ofd.ru/rec/$inn/$kktregnumber/$fnnumber/$docnumber/$docsign", 'inn' => $inn, 'kkt_num' => $kktregnumber, 'fn_num' => $fnnumber, 'doc_num' => $docnumber, 'doc_sign' => $docsign];
  } else {
    return ['status' => 'failed', 'data' => $response, 'status' => $status];
  }
}

/**
 * @param $receipt_id
 */
function ofd_update_check_data($receipt_id)
{
  $db                     = get_db_connect();
  $receipt_data['status'] = '';
  $receipt = $db->getRow("SELECT * FROM ofd_receipts WHERE id=?i", $receipt_id);

  if (!empty($receipt)) {
    while ($receipt_data['status'] != 'success') {
      sleep(2);
      $receipt_data = ofd_get_receipt_data($receipt['ofd_id']);

      if ($receipt_data['status'] == 'success') {
        $db->query("UPDATE ofd_receipts SET doc_sign=?i, doc_num=?i, kkt_num=?i, fn_num=?i WHERE id=?i", $receipt_data['doc_sign'], $receipt_data['doc_num'], $receipt_data['kkt_num'], $receipt_data['fn_num'], $receipt['id']);
        return true;
      }

      $i++;
      if ($i > 5) {
        break;
      }
    }
  }

  return false;
}
