	<div class="row"><div class="col-sm-12"><h4>Добавить пользователя</h4></div></div><hr>
	<div class="row">
		<div class="col col-md-8">
			<form action="/admin/add_user" method="POST">
				<div class="form-group">
					<input type="text" class="form-control" id="input_login" aria-describedby="login" name="login" placeholder="Логин" required>
				</div>
				<div class="form-group">
					<input type="password" name="password" class="form-control" id="input_password" placeholder="Пароль" required>
				</div>
				<button type="submit" class="btn btn-warning">Добавить пользователя</button>
			</form>
			<? if (isset($message)) {?>
				<div class="row text-center">
					<div class="col">
						<p><?=$message?></p>
					</div>
				</div>
			<? } ?>
		</div>
	</div>
