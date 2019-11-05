<div class="modal-header">
  <h5 class="modal-title" id="order_modal_title"><?=$tenant['name']?></h5>
  <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
<div class="modal-body">
  Паспорт <?=$tenant['passport_type']?> <?=$tenant['passport_number']?><br>
  Гражданство <?=$tenant['citizenship']?><br>
  Дата рождения <?=$tenant['birthdate']?><br>
  Почта <?=$tenant['email']?><br>
  Телефон <?=$tenant['phone']?><br><br>

  <a href="/admin/stat/current?tenant_id=<?=$tenant['id']?>"><b>Договора арендатора</b></a><br>
  <a href="/admin/get_registration_link?phone=<?=urlencode($tenant['phone'])?>"><b>Создать ссылку на регистрацию</b></a><br>
  <br>
</div>