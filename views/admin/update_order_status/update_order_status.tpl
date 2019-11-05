	<div class="row"><div class="col-sm-12"><h4>Смена статуса договора</h4></div></div><hr>
	<div class="row">
		<div class="col col-md-6">
					<b>Договор №<?=$order['id']?></b> с <?=date('d.m', $order['date_in'])?> по <?=date('d.m', $order['date_out'])?> <br><?=$order['name']?> <?=$order['phone']?>
			<form name="change_status_form" action="/admin/update_order_status" method="POST">
				<div class="form-group row">
				  <div class="col-sm-12">
				    <input class="form-control" required readonly id="input_order_id" name="order_id" placeholder="Номер договора" value="<?=$order['id']?>" type="hidden"/>
				  </div>
				</div>
				<div class="form-group row">
					<div class="col-sm-12">
						<select name="status_id" required class="form-control custom-select">
							<?
							foreach ($statuses as $key => $value) {?>
								<option value="<?=$value['id']?>"><?=$value['description']?></option>
							</select>
							<?}?>
						</div>
					</div>
					<div class="form-group row">
						<div class="col-sm-12">
							<button class="btn btn-warning">Изменить статус
							</button>
						</div>
					</div>
				</form>
				</div>
			</div>
