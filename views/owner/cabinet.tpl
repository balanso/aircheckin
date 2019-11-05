<div class="container">
	<div class="row text-left">
		<div class="col offset-md-3 col-md-6">
			<div class="text-center">
				<h3>Личный кабинет</h3>
			</div>
			<div><?=$user['name']?> (<?=$user['login']?>)</div>
			<?
			if (isset($card['number']) && !empty($card['number'])) {
				echo "<div>Привязанная карта: {$card['number']}";
			} else {
				echo "<div class='mb-1'>{$card['update_button']}</div>";
			}

			if (isset($card['message'])) {
				echo "<div><small>{$card['message']}</small></div>";
			}

			echo "<div>Баланс: {$owner['balance']} р. {$card['checkout_message']}</div>";

			echo "<div>{$card['checkout_button']} {$card['update_button']}</div>";
			?>
		</div>
	</div>
</div>
<div class="row mt-3">
	<div class="col offset-md-3 col-md-6">
		<table class="table table-sm">
			<tbody>
				<?
				$headers = [];
				foreach ($orders as $date => $orders) {
					echo '<th colspan="3">' . $date . '</th>';

					foreach ($orders as $key => $order) {
						echo "<tr data-toggle='collapse' data-target='.order{$order['id']}'>
						<td>Договор №{$order['id']}<br>{$order['date_in']} - {$order['date_out']}</td>
						<td style='color: green'>{$order['profit']} р.</td>
						</tr>
						<tr class='collapse order{$order['id']}'>
						<td colspan='3'>Выручка: {$order['tarif_total']} р.<br>
						Уборка: {$order['cleaning_cost']} р.<br>
						{$order['service_agent']} {$order['service_agent_cost']} р.</td>
						</tr>";
					}

				}
				?>
			</tbody>
		</table>

<div class="container">
		<div class="row">
			<?
			if (isset($_GET['orders_page'])) {
				$next_page = $_GET['orders_page'] + 1;
				if ($_GET['orders_page'] > 0) {
					echo '<a class="btn btn-warning" href="/owner/cabinet">В начало</a></li>';
				}
			} else {
				$next_page = 1;
			}
			?>
			<div class="col text-right">
				<a class="btn btn-warning" href="/owner/cabinet?orders_page=<?=$next_page?>">Прошлые договора</a>
			</div>
		</div>
	</div>
<hr>
<div class="mb-2">
	<a href="/owner/logout" class="">Выйти из аккаунта</a>
</div>
</div>
</div>

<!-- Modal -->
<? if (isset($checkout['run'])) {?>
	<div class="modal" id="payout_modal" tabindex="-1" role="dialog" data-show="true" aria-labelledby="payout_modal_title" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="payout_modal_title">Результат операции</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Закрыть">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<?=$checkout['message']?>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
				</div>
			</div>
		</div>
	</div>
	<script>
		$('.modal').modal('show');
	</script>
	<? } ?>