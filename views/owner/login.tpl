<div class="container">
  <div class="row mb-3">
    <div class="col col-sm-12 col-md-2">
      <img src="<?=CLIENT_SITE_URL?>/assets/img/logo_header.svg">
    </div>
  </div>
  <div class="row text-center">
    <div class="col offset-md-3 col-md-6">
  	<h2>Личный кабинет собственника</h2>
      <form action="/owner/login" method="POST">
        <div class="form-group">
          <input type="text" class="form-control form-control-lg" required id="input_login" aria-describedby="login" name="login" placeholder="Логин">
        </div>
        <div class="form-group">
          <input type="password" name="password" required class="form-control form-control-lg" id="input_password" placeholder="Пароль">
        </div>
        <input type="hidden" name="referer" class="form-control" id="input_referer" value="<?=$referer?>">
        <button type="submit" class="btn btn-warning btn-lg">Войти в личный кабинет</button>
      </form>
      <? if (isset($message) && !empty($message)) {?>
        <div class="row text-center">
          <div class="col">
            <p><?=$message?></p>
          </div>
        </div>
      <? } ?>
    </div>
  </div>
</div>