	<div class="row"><div class="col-sm-12"><h4>Арендаторы</h4></div></div><hr>
	<table id="stat" class="table table-hover table-striped table-sm table-bordered">
		<div class="row">
		</div>
		<thead>
			<tr>
				<th>Имя</th>
				<th>Телефон</th>
				<th>Паспорт</th>
			</tr>
		</thead>
		<tbody>
		</tbody>
		<tfoot>
		</tfoot>
	</table>

<!-- Modal -->
<div class="modal fade" id="order_modal" tabindex="-1" role="dialog" aria-labelledby="order_modal" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div id="js_load_data">
				<div class="modal-header">
					<h5 class="modal-title" id="order_modal_title">Арендатор </h5>
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

<link rel="stylesheet" type="text/css" href="<?=WEB_ROOT?>/public/js/lib/datatables/datatables.min.css"/>

<script type="text/javascript" src="<?=WEB_ROOT?>/public/js/lib/datatables/datatables.min.js"></script>

<script type="text/javascript" src="<?=WEB_ROOT?>/public/js/lib/datepicker_orig.min.js"></script>
<link href="<?=WEB_ROOT?>/public/css/datepicker.min.css" rel="stylesheet" type="text/css">

<script type="text/javascript">
	$(document).ready(function() {
		var datatable = $('#stat').DataTable( {
			"order": [[ 0, 'desc' ]],
			"language": {
				"url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Russian.json"
			},
			"ordering": false,
			"ajax": "/admin/stat/tenants/stat_ajax",
			"columns": [
			{ "data": "name" },
			{ "data": "phone" },
			{ "data": "passport" }
			],
		});

		function load_tenant_data(tenant_id) {
			$.ajax({
				type: "POST",
				url: '/admin/stat/tenants/load_tenant_data_ajax',
				data: {
					tenant_id: tenant_id,
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

		$('#stat').on( 'click', 'tbody tr', function () {
			var tenant_id = $(this).find('span.tenant_id').data('id');
			load_tenant_data(tenant_id);
		});

		$('#order_modal').on('click', '.js_pay_complete', function() {
			var sber_order_id = $(this).data('id');
			var order_id = $(this).data('order-id');
			var order_sum = $('#order_sum'+order_id).val();

			$.ajax({
				type: "POST",
				url: '/admin/stat/current/complete_sber_order_ajax',
				data: {
					order_id: sber_order_id,
					sum: order_sum
				},
				dataType: 'json',
				success: function (data) {
					if (data.status == 'ok') {
						alert(data.data);
						$('#js_deposit_input'+order_id).fadeOut();
						// load_order_data(order_id);
					} else {
						alert('Ошибка: '+data.data);
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

		});


		var dp = $('#input_dates').datepicker({
			range: true,
			autoClose: true,
			multipleDatesSeparator: ' - ',
			onSelect: function onSelect(fd, date) {
				if (date.length > 1) {
					var firstDate = new Date(date[0]);
					var secondDate = new Date(date[1]);
					console.log(firstDate);
					console.log(secondDate);

					$.ajax({
						type: "POST",
						url: '/admin/stat/tenants/stat_ajax',
						data: {
							date_from_ts: firstDate.getTime() / 1000,
							date_to_ts: secondDate.getTime() / 1000,
						},
						dataType: 'json',
						success: function (data) {
							console.log(data);
							if (data.status == 'error') {
								alert(data.message);
								// console.log(data.orders);
							} else {
								datatable.clear();
								datatable.rows.add(data.data);
								datatable.draw();
							}
						},
						error: function (jqXHR, textStatus, errorThrown) {
							alert('Произошла ошибка, попробуйте позже! ' + jqXHR + ' ' + textStatus + ' ' + errorThrown);
							console.log(jqXHR);
							console.log(textStatus);
							console.log(errorThrown);
						}
					});
				}
			},
		});

		$('#order_modal').on('click', '#js_show_operations_history', function() {
			$('.operations_text[data-id='+$(this).data('id')+']').toggle();
		})

		$('#order_modal').on('click', '#js_show_status_history', function() {
			$('.status_history').toggle();
		})
	});
</script>

