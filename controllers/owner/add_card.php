<?
require_once ROOT . '/lib/vendor/PayU.php';

// Первый - цифровой код ТСН, получаемый в PayU
// Второй - текстовое имя
$payu = new PayU('13117', 'retfdgre', ']99|w4?E6&2X4]Q#G4^T');

$user = get_user_by_cookie();

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

$formData = $payu->initPayoutLinkCardFormData(array(
  'RequestID'   => 346,
  'Email'       => $user['email'] ?? '',
  'FirstName'   => $first_name ?? '',
  'LastName'    => $last_name ?? '',
  'Description' => 'Добавление карты пользователя для осуществления выплат по договору',
  'CardOwnerId' => $user['id'],
  'Timestamp'   => time(),
), WEB_ROOT . '/owner/cabinet/card_update');

?>

<div class="content">
<div class="row">
<div class="col">
    <form  id="payu" action="<?php echo PayU::PAYOUT_LINK_CARD_URL; ?>" id="payu" method="post">
        <?php foreach ($formData as $formDataKey => $formDataValue): ?>
            <input type="hidden" name="<?php echo $formDataKey; ?>" value="<?php echo $formDataValue; ?>">
        <?php endforeach; ?>
    </form>
    <script type="text/javascript">
        document.getElementById('payu').submit();
    </script>
</div></div></div>