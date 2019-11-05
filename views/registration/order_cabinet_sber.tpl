<?
parse_str(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY), $queries);

foreach ($sber_orders as $key => $sber_order) {
  $payed = false;

  if ($sber_order['status'] == 1 || $sber_order['status'] == 2) {
    $payed = true;
  } elseif (isset($queries['orderId']) && isset($queries['status'])) {

    if ($sber_order['sberbank_order_id'] == $queries['orderId']) {

      if ($queries['status'] == 'fail') {
        $message_color = 'alert-danger';
        if ($sber_order['type'] == 't') {
          $message = 'Оплата тарифа не прошла, попробуйте ещё раз';
        } else {
          $message = 'Оплата депозита не прошла, попробуйте ещё раз';
        }
      } elseif ($queries['status'] == 'success') {
        $payed = true;
      }
    }
  }

  $out_sber_orders[$sber_order['type']] = [
    'payed'    => $payed,
    'pay_url'  => $sber_order['pay_url'],
    'amount'   => $sber_order['amount'],
    'type'     => $sber_order['type'],
    'status'   => $sber_order['status'],
    'order_id' => $sber_order['order_id'],
  ];

  if (!empty($sber_order['receipt'])) {
    $out_sber_orders[$sber_order['type']]['receipt'] = $sber_order['receipt'];
  }

}

$tarif_button         = '';
$tarif_message        = '';
$tarif_block          = '';
$tarif_button_message = '';
$tarif_button_url      = '#';
$tarif_modal_button_id = 'deposit_modal_button';

$deposit_button       = '';
$deposit_block        = '';
$deposit_button_url      = '#';
$deposit_modal_button_id = 'tarif_modal_button';


if (!empty($out_sber_orders['d'])) {
  $deposit_order = $out_sber_orders['d'];

  if ($deposit_order['payed']) {
    if (!empty($deposit_order['receipt'])) {
      $deposit_button_class    = 'btn-warning';
      $deposit_button_text     = '<i class="fa fa-book" style="color: black;" aria-hidden="true"></i> Чек оплаты депозита ' . $deposit_order['amount'] . '₽';

      if (!empty($deposit_order['receipt']['doc_sign'])) {
        $deposit_check_modal = load_tpl('/views/registration/sber_check.tpl', [
          'modal_id'        => 'deposit_check_modal',
          'modal_button_id' => $deposit_modal_button_id,
          'modal_title'     => "Чек оплаты депозита",
          'modal_item'      => 'оплата депозита по договору №' . $deposit_order['order_id'],
          'data'            => $deposit_order['receipt'],
        ], true);
      }

    } else {
      $deposit_button_class = 'btn-secondary disabled';
      $deposit_button_text  = '<i class="fa fa-check" style="color: lightgreen;" aria-hidden="true"></i> Депозит оплачен ' . $deposit_order['amount'] . '₽<br><small>Чек будет доступен после проверки</small>';
    }
  } else {
    $deposit_button_class = 'btn-success';
    $deposit_button_text  = 'Оплатить депозит ' . $deposit_order['amount'] . '₽';
    $deposit_button_url   = $deposit_order['pay_url'];
  }

  $deposit_block = '<div class="col-12 mb-3"><a href="' . $deposit_button_url . '" class="btn ' . $deposit_button_class . '" target="_blank" id="' . $deposit_modal_button_id . '">' . $deposit_button_text . '</a></div>';
}

if (!empty($out_sber_orders['t'])) {
  $tarif_order = $out_sber_orders['t'];

  if (isset($deposit_order) && !$deposit_order['payed']) {
    $tarif_button_class   = 'btn-secondary disabled';
    $tarif_button_text    = 'Оплатить тариф ' . $tarif_order['amount'] . '₽';
    $tarif_button_message = '<br><small>Доступно после оплаты депозита</small>';
  } else {

    if ($tarif_order['payed']) {
      if (!empty($tarif_order['receipt'])) {
        $tarif_button_class    = 'btn-warning';
        $tarif_button_text     = '<i class="fa fa-book" style="color: black;" aria-hidden="true"></i> Чек оплаты тарифа ' . $tarif_order['amount'] . '₽';

        if (!empty($tarif_order['receipt']['doc_sign'])) {
          $tarif_check_modal = load_tpl('/views/registration/sber_check.tpl', [
            'modal_id'        => 'tarif_check_modal',
            'modal_button_id' => $tarif_modal_button_id,
            'modal_title'     => "Чек оплаты тарифа",
            'modal_item'      => 'оплата тарифа по договору №' . $tarif_order['order_id'],
            'data'            => $tarif_order['receipt'],
          ], true);
        }

      } else {
        $tarif_button_class = 'btn-secondary disabled';
        $tarif_button_text  = '<i class="fa fa-check" style="color: lightgreen;" aria-hidden="true"></i> Тариф оплачен ' . $tarif_order['amount'] . '₽<br><small>Чек будет доступен после проверки</small>';
      }
    } else {
      $tarif_button_class = 'btn-success';
      $tarif_button_text  = 'Оплатить тариф ' . $tarif_order['amount'] . '₽';
      $tarif_button_url   = $tarif_order['pay_url'];
    }
  }

  $tarif_block = '<div class="col-12 mb-3"><a href="' . $tarif_button_url . '" class="btn ' . $tarif_button_class . '" target="_blank" id="' . $tarif_modal_button_id . '">' . $tarif_button_text . $tarif_button_message. '</a></div>';
}

$pay_message = '<div class="col-12"><p style="font-size: 1.2em;">Для подтверждения вашего проживания необходимо оплатить <strong>депозит</strong><br>и в день заезда или раньше оплатить <strong>тариф</strong></p></div>';
?>

<div class="container" id="ac_registration_success">
  <div class="row text-center border-wrap" style="position: relative;">
    <div class="col-12 mb-2">
      <h3>Поздравляем!</h3>
      <h5>Договор №<?=$order['id']?> сформирован и отправлен вам на почту</h5>
    </div>
    <div class="col-12 mb-3">
      <a href="<?=WEB_ROOT?>/public/orders/<?=$order['file_name']?>" download class="btn btn-warning" target="_blank">Скачать договор</a>
    </div>
    <?=$pay_message?>
    <?=$deposit_block?>
    <?=$tarif_block?>

    <? if (isset($door_key)) {
      if ($door_key == 'enabled') {
        load_tpl('/views/registration/door_key_enabled.tpl', ['short_link'=>$short_link]);
      }

      if ($door_key == 'disabled') {
        load_tpl('/views/registration/door_key_disabled.tpl');
      }
    }?>

  </div>

</div>

<?
if (!empty($deposit_check_modal)) {
  echo $deposit_check_modal;
}
if (!empty($tarif_check_modal)) {
  echo $tarif_check_modal;
}
?>