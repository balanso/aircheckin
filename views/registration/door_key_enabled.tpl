<div class="col-12 mb-3"><button type="button" class="btn btn-warning" id="open_door"><i class="fa fa-lock" aria-hidden="true"></i> Открыть дверь на 5 секунд</button></div>

<script>
	$('#open_door').click(function() {
		$('#open_door').attr('disabled', 'disabled');

		setTimeout(function() {
			$('#open_door').html('<i class="fa fa-lock" aria-hidden="true"></i> Открыть дверь на 5 секунд').removeAttr('disabled');
		}, 7000);


		$.ajax({
			type: "POST",
			url: AC_BASE_AJAX_URL + '/door_locks/open_door_ajax',
			data: {short_link:'<?=$short_link?>'},
			dataType: "json",
			success: function (msg) {
				console.log(msg);
				if (msg['status'] == 'ok') {
/*					$('#open_door').html('<i class="fa fa-lock-open" aria-hidden="true"></i> Дверь открыта, входите');
					setTimeout(function() {
						$('#open_door').html('<i class="fa fa-lock" aria-hidden="true"></i> Открыть дверь на 5 секунд').removeAttr('disabled');
					}, 7000);*/
				} else {
					alert('Не удалось открыть дверь, обратитесь к администратору');
				}
			}
		});
	});
</script>