<?
require_once ROOT . '/lib/messaging.php';
// file_put_contents(__DIR__ . '/input_payout_token', date('d.m.Y H:i') . ' ' . print_r($_REQUEST, true), FILE_APPEND);

if (isset($_POST['Signature'])) {
  $config = get_config('payu');
  $sig_params = $_POST;
  unset($sig_params['Signature']);
  ksort($sig_params);
  $sig_params = implode('', $sig_params) . $config['secret'];
  $sig        = md5($sig_params);

  if ($sig == $_POST['Signature']) {
  	$log->info('Signature checked!', $_POST);

  	if (!empty($_POST['RequestID'])) {
  	  $owner_id = $db->getOne("SELECT owner_id FROM payu_requests WHERE id=?i AND response_recieved_at IS NULL", $_POST['RequestID']);

      $user = $db->getRow("SELECT users.* FROM users WHERE users.id IN (SELECT user_id FROM owners WHERE owners.id=?i)", $owner_id);

      send_tg("Собственник {$user['name']} {$user['login']} привязал новую карту {$_POST['CardMask']}");

  	  if ($owner_id) {
  	    $card_mask = str_replace('-', '', $_POST['CardMask']);
  	    $db->query("UPDATE owners SET card_number=?s, payu_token=?s WHERE id=?i", $card_mask, $_POST['Token'], $owner_id);
  	    $db->query("UPDATE payu_requests SET response_recieved_at=?i WHERE id=?i", time(), $_POST['RequestID']);
  	  } else {
  	  	$log->debug('Active request is not found', $_POST);
  	  	exit('Active request is not found');
  	  }
  	}
  } else {
  	$log->debug('Signature error!', $_POST);
  	exit('Signature not checked!');
  }
}

echo 'OK';
exit();
