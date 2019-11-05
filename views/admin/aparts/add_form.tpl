<?
$owner_select_options = '';
if (!empty($owners)) {
	foreach ($owners as $key => $owner) {
		$owner_select_options .= "<option value=\"{$owner['id']}\">{$owner['name']}</option>";
	}
}
?>
<link href="<?=WEB_ROOT?>/public/css/dropzone.css" type="text/css" rel="stylesheet" />

<div class="row"><div class="col-sm-12"><h4>Добавление апартамента</h4></div></div><hr>
<div class="row">
	<div class="col col-md-8">

			<form action="/admin/aparts/add" method="POST" class="">
				<!-- <h6>Заполните данные апартамента</h6> -->
				<div class="form-group">
					<label>Название комнаты</label>
					<input type="text" class="form-control" id="input_name" aria-describedby="name" name="name" placeholder="Название" required>
				</div>
				<div class="form-group">
					<label>Адрес</label>
					<input type="text" class="form-control" id="input_address" name="address" placeholder="Адрес апартамента" required>
				</div>
				<div class="form-group">
					<label>Wi-Fi название сети</label>
					<input type="text" class="form-control" id="input_wifi_name" name="wifi_name" placeholder="Название сети" required>
				</div>
				<div class="form-group">
					<label>Wi-Fi пароль</label>
					<input type="text" class="form-control" id="input_wifi_pass" name="wifi_pass" placeholder="Пароль" required>
				</div>
				<div class="form-group">
					<label>Владелец</label>
					<select id="input_owner_id" name="owner_id" class="custom-select form-control" required>
						<option selected value=""></option>
						<?=$owner_select_options?>
					</select>
				</div>
				<div class="form-group">
					<label>ID в PMS</label>
					<input type="text" class="form-control" id="input_pms_id" name="pms_id" placeholder="ID в PMS" required>
				</div>
				<div class="form-group">
					<div class="dropzone"></div>
				</div>
				<button type="submit" class="btn btn-warning">Добавить</button>
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


		<script src="<?=WEB_ROOT?>/public/js/lib/dropzone.js"></script>
		<script type="text/javascript">
			Dropzone.autoDiscover = false;
			$(".dropzone").dropzone({
				addRemoveLinks: true,
				url: "/admin/aparts/image_handler_ajax?action=upload",
				dictDefaultMessage: 'Перетащите фотографии объекта для загрузки или клик на текст',
				dictRemoveFile: 'Удалить',

				init: function() {
					thisDropzone = this;
					$.get('/admin/aparts/image_handler_ajax?action=get', function(data) {
						$.each(data, function(key,value){
							var mockFile = { name: value.name, size: value.size };
							thisDropzone.options.addedfile.call(thisDropzone, mockFile);
							thisDropzone.options.thumbnail.call(thisDropzone, mockFile, "/public/img/aparts/temp/"+value.name);

							thisDropzone.createThumbnailFromUrl(mockFile, "/public/img/aparts/temp/"+value.name);
						});

					});
				},

				removedfile: function(file) {
					var name = file.name;
					$.ajax({
						type: 'POST',
						url: '/admin/aparts/image_handler_ajax?action=remove',
						data: {name: name},
						sucess: function(data){
							console.log('success: ' + data);
						}
					});
					var _ref;
					return (_ref = file.previewElement) != null ? _ref.parentNode.removeChild(file.previewElement) : void 0;
				}
			});
		</script>