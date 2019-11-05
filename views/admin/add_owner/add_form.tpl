<div class="row"><div class="col-sm-12"><h4>Регистрация собственника</h4></div></div><hr>
<div class="row">
	<div class="col col-md-8">
			<form action="/admin/add_owner" method="POST">
				<h6>Заполните данные собственника</h6>
				<div class="form-group">
					<input type="text" class="form-control" id="input_fio" aria-describedby="fio" name="fio" placeholder="ФИО" required>
				</div>
				<div class="form-group row">
				  <div class="col-sm-12">
				    <input class="form-control input_phone" required id="input_phone" name="phone" placeholder="Телефон +7" type="tel" value=""/>
				  </div>
				</div>
				<div class="form-group">
					<input type="email" class="form-control" id="input_email" aria-describedby="email" name="email" placeholder="Почта @" required>
				</div>
				<button type="submit" class="btn btn-warning">Зарегистрировать</button>
			</form>
			<?if (isset($message)) {?>
				<div class="row">
					<div class="col">
						<p><?=$message?></p>
					</div>
				</div>
			<?}?>
		</div>
	</div>
