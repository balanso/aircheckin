<?
require_once ROOT . '/lib/vendor/PayU.php';
$open_form = false;

// Первый - цифровой код ТСН, получаемый в PayU
// Второй - текстовое имя
$payu = new PayU('13117', 'retfdgre', ']99|w4?E6&2X4]Q#G4^T');

$name_array = explode(' ', $user['name']);
if (count($name_array) == 3) {
  $first_name = $name_array[1];
  $last_name  = $name_array[0];
} elseif (count($name_array) == 2) {
  $first_name = $name_array[1];
  $last_name  = $name_array[0];
} else {
  $first_name = $name_array[0];
  $last_name  = '';
}

$db->query("INSERT INTO payu_requests SET owner_id=?i, created_at=?i, type=1", $owner_id, time());
$request_id = $db->insertId();

$formData = $payu->initPayoutLinkCardFormData(array(
  'RequestID'   => $request_id,
  'Email'       => $user['email'] ?? '',
  'FirstName'   => $first_name ?? '',
  'LastName'    => $last_name ?? '',
  'Description' => 'Добавление карты пользователя для осуществления выплат по договору',
  'CardOwnerId' => $user['id'],
  'Timestamp'   => time(),
), WEB_ROOT . '/payu/result');

$open_form = true;
?>

<?if ($open_form) {?>
  <form action="<?php echo PayU::PAYOUT_LINK_CARD_URL; ?>" id="payu" method="post">
      <?php foreach ($formData as $formDataKey => $formDataValue): ?>
          <input type="hidden" name="<?php echo $formDataKey; ?>" value="<?php echo $formDataValue; ?>">
      <?php endforeach;?>
  </form>
  <script type="text/javascript">
      document.getElementById('payu').submit();
  </script>
<?} else {
  echo "Не удалось открыть форму добавления карты, обратитесь в тех. поддержку aircheckin";
}?>
