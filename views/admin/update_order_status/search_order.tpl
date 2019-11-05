<div class="row"><div class="col-sm-12"><h4>Смена статуса договора</h4></div></div><hr>
<div class="row mb-4">
	<div class="col-md-8">
		<form name="change_status_form" action="/admin/update_order_status" method="POST">
			<div class="form-group row">
				<div class="col-sm-12">
					<input class="form-control" required id="input_order_id" name="order_id" placeholder="Номер договора" value="<?=$order_id?>" type="number"/>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-sm-12">
					<button class="btn btn-warning">Найти договор
					</button>
				</div>
			</div>
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