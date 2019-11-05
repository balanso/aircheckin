<div class="container" id="ac_registration_success">
  <div class="row text-center border-wrap">
    <div class="col-12 mb-2">
      <h3>Поздравляем!</h3>
      <h5>Договор №<?=$order['id']?> сформирован и отправлен вам на почту</h5>
    </div>
    <div class="col-12 mb-3">
      <a href="<?=WEB_ROOT?>/public/orders/<?=$order['file_name']?>" download class="btn btn-warning" target="_blank">Скачать договор</a>
    </div>
    <div class="col-12">
      <p>
        <?if ($order['status'] == 12) {
          ?>
        Мы получили оплату и ожидаем вашего прибытия!</strong>
        <?
      } else {
        ?>

        Для подтверждения вашего проживания мы ожидаем оплату<br><strong>депозита (залог)</strong> и <strong>тарифа (стоимость проживания)</strong>

        <?
      }
      ?>
    </p>
  </div>
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
