<?
if (!empty($action)) {
  if ($action == 'get_token') {
    include_once __DIR__ . '/get_token.php';
  } else {

    if (isset($_GET['token'])) {
      $token = $_GET['token'];
    } else {
      $token = getBearerToken();
    }

    if ($token) {
      $user = get_user_by_token($token);

      if (!$user) {
        json_answer('error', 'Access denied, token user not found');
      }

      if ($user['access_level'] < 10) {
        json_answer('error', 'Access denied, account access level < 10');
      } else {
      }

    } else {
      json_answer('error', 'Token not found');
    }
  }

  $owner_id = $db->getOne("SELECT id FROM owners WHERE user_id=?i", $user['id']);
  if (empty($owner_id)) {
    json_answer('error', 'Owner with user_id = ' . $user['id'] . ' not found');
  }

  switch ($action) {
    case 'get_user':
      include __DIR__ . '/get_user.php';
      break;

    case 'get_orders':
      include_once __DIR__ . '/get_orders.php';
      break;

    case 'transfer_orders_profit':
      include_once __DIR__ . '/transfer_orders_profit.php';
      break;

    case 'add_card':
      include_once __DIR__ . '/add_card.php';
      break;

    default:
      if (is_file(__DIR__ . "/$action.php")) {
        include_once __DIR__ . "/$action.php";
      } else {
        json_answer('error', 'Action not found');
      }

      break;

  }
} else {
  json_answer('error', 'Action not found');
}
