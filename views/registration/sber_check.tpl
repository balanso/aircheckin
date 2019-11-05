<!-- Modal -->
<div class="modal fade" id="<?=$modal_id?>" tabindex="-1" role="dialog" aria-labelledby="<?=$modal_id?>" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title"><?=$modal_title?> №<?=$data['doc_num']?></h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Закрыть">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					Номер Чека: <?=$data['doc_num']?><br>
					Номер ККТ : <?=$data['kkt_num']?><br>
					Номер ФН : <?=$data['fn_num']?><br>
					Фискальный признак : <?=$data['doc_sign']?><br>
					Статус : <?=$data['type_description']?><br>
					Сумма: <?=$data['price']?>₽<br>
					Услуга: <?=$modal_item?><br>
					Дата создания чека: <?=date('d.m.Y H:i', $data['created_at'])?><br><br>
					<a href="https://check.ofd.ru/rec/<?=$data['inn']?>/<?=$data['kkt_num']?>/<?=$data['fn_num']?>/<?=$data['doc_num']?>/<?=$data['doc_sign']?>">Открыть чек OFD</a>
				</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
			</div>
		</div>
	</div>
</div>

<script>
	$('#<?=$modal_button_id?>').on('click', function(e) {
		e.preventDefault();
		$('#<?=$modal_id?>').modal();
	});
</script>