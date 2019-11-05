<div class="container text-center">
  <h4 class="">
    Спасибо, оплата
    <?=$order_text?>
    прошла успешно!
  </h4>
  <hr>
  <?if (!empty($tarif_pay_url)) {?>
<!--     <div class="col-12">
      <a href="<?=$tarif_pay_url?>" class="btn btn-success mb-3" target="_blank">Оплатить тариф</a>
    </div> -->
  <?}?>
  <?if (!empty($order_file_name)) {?>
  <div class="col-12">
    <a href="<?=WEB_ROOT?>/public/orders/<?=$order_file_name?>" download class="btn btn-warning" target="_blank">Скачать договор</a>
  </div>
  <?}?>

  <!--   <p>
    	Здравствуйте, для выбора тарифа укажите интересующие вас даты заезда и выезда
    </p> -->
</div>
