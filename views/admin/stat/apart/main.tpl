<?

$table_rows = '';
$total      = ['tarif' => 0, 'marketing' => 0, 'service' => 0, 'cleanings' => 0, 'profit' => 0];
$total_row  = '';
$text       = '';

if (!empty($orders)) {
  // debug($orders);
  $text = 'Статистика договоров за 30 дней' . "\n\n";
  foreach ($orders as $key => $order) {
    $date_in        = date('m.d', $order['date_in']);
    $date_out       = date('m.d', $order['date_out']);
    $cleanings_cost = $order['cleanings'] * $order['cleaning_cost'];
    $marketing_cost = $order['tarif_total'] / 100 * $order['marketing_agent_percent'];
    $service_cost   = $order['tarif_total'] / 100 * $order['service_agent_percent'];
    $profit         = $order['tarif_total'] - $service_cost - $marketing_cost - $cleanings_cost;
    $table_rows .= "<tr>
		<td>№{$order['id']} {$date_in} - {$date_out}</td>
		<td>{$order['tarif_total']}₽</td>
		<td>{$order['marketing_agent_name']} {$marketing_cost}₽</td>
		<td>{$order['service_agent_name']} {$service_cost}₽</td>
		<td>{$cleanings_cost}₽</td>
		<td>{$profit}₽</td>
		</tr>";

    $text .= "Договор №{$order['id']} {$date_in} - {$date_out}
Выручка {$order['tarif_total']}₽
Маркетинг - {$order['marketing_agent_name']} {$marketing_cost}₽
Сервис - {$order['service_agent_name']} {$service_cost}₽
Уборка {$cleanings_cost}₽
Прибыль {$profit}₽\n\n";

    $total['tarif'] += $order['tarif_total'];
    $total['marketing'] += $marketing_cost;
    $total['service'] += $service_cost;
    $total['cleanings'] += $cleanings_cost;
    $total['profit'] += $profit;
  }

  $text .= "Итоговая выручка: {$total['profit']}₽";

  $total['orders_count'] = count($orders);
  $total_row             = "<tr>
		<td>{$total['orders_count']} шт.</td>
		<td>{$total['tarif']}₽</td>
		<td>{$total['marketing']}₽</td>
		<td>{$total['service']}₽</td>
		<td>{$total['cleanings']}₽</td>
		<td>{$total['profit']}₽</td></tr>";
}

$apart_select_options = '';
if (!empty($aparts)) {
  foreach ($aparts as $key => $apart) {
    $apart_select_options .= "<option value=\"{$apart['id']}\">{$apart['name']}</option>";
  }
}

?>

	<div class="row"><div class="col-sm-12"><h4>Статистика по апартаментам</h4></div></div><hr>
	<table id="stat" class="table table-hover table-striped table-sm table-bordered">
		<div class="row">
			<div class="col col-sm-3">
				<select id="input_apart_id" name="apart_id" class="custom-select form-control" required>
					<option selected value="0">Все апартаменты</option>
					<?=$apart_select_options?>
				</select>
			</div>
			<div class="col col-sm-5">
				<input autocomplete="off" class="form-control mb-3 readonly-clear" required id="input_dates" name="dates" placeholder="Даты выезда" type="text" readonly/>
			</div>

		</div>
		<thead>
			<tr>
				<th colspan="2"></th>
				<th colspan="3">Расходы</th>
				<th colspan=""></th>
			</tr>
			<tr>
				<th>Договор</th>
				<th>Выручка</th>
				<th>Маркетинг</th>
				<th>Сервис</th>
				<th>Уборка</th>
				<th>Прибыль</th>
			</tr>
		</thead>
		<tbody>
			<?=$table_rows?>
		</tbody>
		<tfoot>
			<tr>
				<th colspan="6" class="">Итого</th>
			</tr>
			<?=$total_row?>
		</tfoot>
	</table>

	<textarea class="form-control" id="stat_text" rows="10"><?=$text?></textarea>
	<div class="col text-right mt-1">
	<!-- <button id="send_tg" class="btn btn-warning">Отправить владельцу в Telegram</button> -->
	<a href="/admin/orders" class="btn btn-warning">Редактирование договоров</a>
	<a href="?pdf" id="generate_pdf" class="btn btn-warning">Создать PDF отчёт</a>
</div>

</div>

<!-- Modal -->
<div class="modal fade" id="order_modal" tabindex="-1" role="dialog" aria-labelledby="order_modal" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div id="js_load_data">
				<div class="modal-header">
					<h5 class="modal-title" id="order_modal_title">Договор №</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Закрыть">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					Body
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
			</div>
		</div>
	</div>
</div>

<link rel="stylesheet" type="text/css" href="<?=WEB_ROOT?>/public/js/lib/datatables/datatables.min.css"/>

<script type="text/javascript" src="<?=WEB_ROOT?>/public/js/lib/datatables/datatables.min.js"></script>

<script type="text/javascript" src="<?=WEB_ROOT?>/public/js/lib/datepicker_orig.min.js"></script>
<link href="<?=WEB_ROOT?>/public/css/datepicker.min.css" rel="stylesheet" type="text/css">

<script type="text/javascript">
	$(document).ready(function() {
		var selected_dates;

		function load_orders() {
			var from_timestamp, to_timestamp, firstDate, secondDate;

			if (selected_dates != undefined && selected_dates.length > 1) {
				firstDate = new Date(selected_dates[0]);
				secondDate = new Date(selected_dates[1]);
				from_timestamp = firstDate.getTime() / 1000;
				to_timestamp = secondDate.getTime() / 1000;
			} else {
				from_timestamp = 0;
				to_timestamp = Math.floor(Date.now() / 1000);
			}

			var apart_id = $('#input_apart_id').val();
			var apart_name = $('#input_apart_id :selected').text();

			$('#generate_pdf').attr('href', '?pdf&date_from_ts=' + from_timestamp + '&date_to_ts=' + to_timestamp + '&apart_id='+apart_id+'&apart_name='+apart_name);

			$.ajax({
				type: "POST",
				url: '/admin/stat/apart/stat_ajax',
				data: {
					date_from_ts: from_timestamp,
					date_to_ts: to_timestamp,
					apart_id: apart_id,
					apart_name: apart_name
				},
				dataType: 'json',
				success: function (data) {
					if (data.status == 'error') {
						alert(data.message);
					} else {
						console.log(data);
						$('#stat tbody').html(data.orders);
						$('#stat tfoot').html(data.total);
						$('#stat_text').html(data.text);
					}
				},
				error: function (jqXHR, textStatus, errorThrown) {
					alert('Произошла ошибка, попробуйте позже! ' + jqXHR + ' ' + textStatus + ' ' + errorThrown);
					console.log(jqXHR);
					console.log(textStatus);
					console.log(errorThrown);
				}
			});

			return true;
		}

		var dp = $('#input_dates').datepicker({
			range: true,
			autoClose: true,
			multipleDatesSeparator: ' - ',
			onSelect: function onSelect(fd, date) {
				if (date.length > 1) {
					selected_dates = dp.data('datepicker').selectedDates;
					load_orders();
				}
			}
		});

		$('#input_apart_id').on('change', function() {
			load_orders();
		});

		$('#order_modal').on('click', '#js_show_operations_history', function() {
			$('.operations_text[data-id='+$(this).data('id')+']').toggle();
		})

		$('#stat_current').on( 'click', 'tbody tr', function () {
			var order_id = $(this).find('span.order_id').text();
			load_order_data(order_id);
		});

		function load_order_data(order_id) {
			$.ajax({
				type: "POST",
				url: '/admin/stat/current/load_order_data_ajax',
				data: {
					order_id: order_id,
				},
				dataType: 'json',
				success: function (data) {
					if (data.status != undefined && data.html != undefined) {
						$('#js_load_data').html(data.html);
						$('#order_modal').modal()
					} else {
						alert('Возникла неизвестная ошибка, см. консоль');
						console.log(data);
					}
					// console.log(data);
				},
				error: function (jqXHR, textStatus, errorThrown) {
					alert('Произошла ошибка, попробуйте позже! ' + jqXHR + ' ' + textStatus + ' ' + errorThrown);
					console.log(jqXHR);
					console.log(textStatus);
					console.log(errorThrown);
				}
			});

			return true;
		}

		$('#send_tg').on('click', function(){
			text = $('#stat_text').val();
			apart_id = $('#input_apart_id').val();
			$.ajax({
				type: "POST",
				url: '/admin/stat/apart/send_tg_ajax',
				data: {
					text: text,
					apart_id: apart_id
				},
				dataType: 'json',
				success: function (data) {
					if (data.status == 'error') {
						alert(data.message);
					} else {
						alert('Сообщение успешно отправлено')
					}
				},
				error: function (jqXHR, textStatus, errorThrown) {
					alert('Произошла ошибка, попробуйте позже! ' + jqXHR + ' ' + textStatus + ' ' + errorThrown);
					console.log(jqXHR);
					console.log(textStatus);
					console.log(errorThrown);
				}
			});
		})
	});
</script>