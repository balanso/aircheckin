<?
$data = [
  'id' => $user['id'],
  'login' => $user['login'],
  'name' => $user['name'],
  'phone' => $user['phone'],
  'email' => $user['email'],
  'access_level' => $user['access_level'],
];

if (!empty($param1)) {
  if ($param1 == 'aparts') {
    $data['aparts'] = $db->getAll("SELECT * FROM aparts WHERE owner_id=?i", $owner_id);
  }

  if ($param1 == 'owner') {
    $data['owner'][]            = $db->getRow("SELECT owners.*, SUM(owners_balance.sum) AS balance FROM owners LEFT JOIN owners_balance ON owner_id=?i WHERE owners.id=?i", $owner_id, $owner_id);
// Убрать [] = лишний массив. + В приложухе убрать.

    $data['payout_allowed'] = 1;
    $payu_balance = $db->getOne("SELECT balance FROM payu_balance ORDER BY id DESC LIMIT 1");

    if ($payu_balance && isset($data['owner']['balance']) && $payu_balance < $data['owner']['balance']) {
      $data['payout_allowed'] = 0;
    }
  }
}

json_answer($data);
