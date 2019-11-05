	<div class="row"><div class="col-sm-12"><h4>Список апартаментов</h4></div></div><hr>

	<table id="stat" class="table table-hover table-striped table-sm table-bordered">

			<a href="/admin/aparts/add" class="btn btn-secondary">Добавить новый</a><br><br>
		<div class="row">
		</div>
		<thead>
			<tr>
				<th>Название</th>
				<th>Адрес</th>
				<th>Собственник</th>
			</tr>
		</thead>
		<tbody>
		</tbody>
		<tfoot>
		</tfoot>
	</table>

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
			"ajax": "/admin/aparts/list_ajax",
			"columns": [
			{ "data": "name" },
			{ "data": "address" },
			{ "data": "owner" }
			],
		});


		$('#stat').on( 'click', 'tbody tr', function () {
			var id = $(this).find('span.apart_id').data('id');
			location.href = '/admin/aparts/edit/'+id;
		});
	});
</script>

