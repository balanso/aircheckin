<?
check_user_access('admin');
require_once ROOT . '/lib/messaging.php';

if (!empty($_POST['apart_id'])) {
  $owner_id = $db->getOne("SELECT owner_id FROM aparts WHERE id=?i", $_POST['apart_id']);

  if ($owner_id) {
    $owner = $db->getRow("SELECT * FROM owners WHERE id=?i", $owner_id);

    if ($owner) {
    	if (!empty($_POST['text'])) {
        send_tg($_POST['text'], $owner['tg_chat_id']);
    		exit(json_encode(['status'=>'success']));
    	} else {
    		exit(json_encode(['status' => 'error', 'message' => 'Не получен текст для отправки']));
    	}
    } else {
      exit(json_encode(['status' => 'error', 'message' => 'Не найден владелец апартамента по ID']));
    }
  } else {
    exit(json_encode(['status' => 'error', 'message' => 'Не получен ID владельца апартамента']));
  }

} else {
  exit(json_encode(['status' => 'error', 'message' => 'Не указан ID апартамента']));
}
