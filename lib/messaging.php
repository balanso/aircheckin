<?
require_once ROOT . '/vendor/autoload.php';
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * @param $msg
 */
function send_tg($msg, $chat_id = 0)
{

  if (empty($chat_id)) {
    $chat_id = -1001354558187;
  }

  $data = [
    'chat_id'    => $chat_id,
    'text'       => $msg,
    'parse_mode' => 'html',
  ];

  $response = file_get_contents("https://api.telegram.org/bot".TG_BOT_TOKEN."/sendMessage?" . http_build_query($data));
}

/**
 * @param $to
 * @param $subject
 * @param $msg
 * @param array $attachment ['path_to_file', 'file_name']
 */
function send_mail($to, $subject, $msg, $attachment = array())
{
                               //Admin mail
  $mail = new PHPMailer(true); // Passing `true` enables exceptions
  try {
                                                //Server settings
    $mail->SMTPDebug = 0;                       // Enable verbose debug output
    $mail->isSMTP();                            // Set mailer to use SMTP
    $mail->Host       = 'smtp.yandex.com';      // Specify main and backup SMTP servers
    $mail->SMTPAuth   = true;                   // Enable SMTP authentication
    $mail->Username   = 'booking@aeroapart.ru'; // SMTP username
    $mail->Password   = 'Q20899999';            // SMTP password
    $mail->SMTPSecure = 'tls';                  // Enable TLS encryption, `ssl` also accepted
    $mail->Port       = 587;                    // TCP port to connect to
    $mail->CharSet    = "UTF-8";

    //Recipients
    $mail->setFrom('booking@aeroapart.ru', 'AEROAPART.RU');
    $mail->isHTML(true); // Set email format to HTML

    $mail->addAddress($to);

    if (!empty($attachment)) {
      $mail->addAttachment($attachment['path'], $attachment['name']);
    }

    $mail->Subject = $subject;
    $mail->Body    = $msg;
    $mail->AltBody = $msg;
    $mail->send();
  } catch (Exception $e) {
    // echo 'Messagecouldnotbesent . MailerError:', $mail->ErrorInfo;
  }
}

/**
 * @param $number
 * @param $text
 * @return mixed
 */
function send_sms($number, $text)
{
  $ch = curl_init("https://sms.ru/sms/send");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_TIMEOUT, 30);
  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
    "api_id" => "AF08D047-1D9E-BE08-73E2-A7C94663FA65",
    "to"     => $number, // До 100 штук до раз
    "msg"    => $text,   // Если приходят крякозябры, то уберите iconv и оставьте только "Привет!",
    /*
    // Если вы хотите отправлять разные тексты на разные номера, воспользуйтесь этим кодом. В этом случае to и msg нужно убрать.
    "multi" => array( // до 100 штук за раз
    "79788388041"=> iconv("windows-1251", "utf-8", "Привет 1"), // Если приходят крякозябры, то уберите iconv и оставьте только "Привет!",
    "74993221627"=> iconv("windows-1251", "utf-8", "Привет 2")
    ),
     */
    "json"   => 1, // Для получения более развернутого ответа от сервера
  )));
  $body = curl_exec($ch);
  curl_close($ch);

  $json = json_decode($body);
  return $json;
}
