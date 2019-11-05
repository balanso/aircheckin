<div class="container">
	<div class="row text-center">
		<div class="col offset-md-3 col-md-6">
			<h3>Авторизация</h3>
			<form action="/user/login" method="POST">
				<div class="form-group">
					<input type="text" class="form-control" required id="input_login" aria-describedby="login" name="login" placeholder="Логин">
				</div>
				<div class="form-group">
					<input type="password" name="password" required class="form-control" id="input_password" placeholder="Пароль">
				</div>
				<input type="hidden" name="referer" class="form-control" id="input_referer" value="<?=$referer?>">
				<button type="submit" class="btn btn-warning">Войти</button>
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
