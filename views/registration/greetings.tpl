<div class="container" id="ac_registration_greetings">
	<div class="row text-center border-wrap">
		<div class="col-12">
			<h3>Ура!</h3>
			<p class="text-center text-lg">Здравствуйте, пройдите регистрацию, на основании внесённых данных сформируется личный кабинет с договором и кнопкой оплаты. Ключи можно будет получить на основании паспорта, в день заезда по адресу <a href="https://www.google.ru/maps/place/%D0%A5%D0%BE%D0%B4%D1%8B%D0%BD%D1%81%D0%BA%D0%B8%D0%B9+%D0%B1-%D1%80,+2,+%D0%9C%D0%BE%D1%81%D0%BA%D0%B2%D0%B0,+%D0%A0%D0%BE%D1%81%D1%81%D0%B8%D1%8F,+125167/@55.7888816,37.5356504,17z/data=!3m1!4b1!4m5!3m4!1s0x46b549964e037c9b:0x98eff2c1a700d4e9!8m2!3d55.7888786!4d37.5378444" target="_blank">г. Москва, м. ЦСКА, Ходынский бульвар дом 2 - Блок А</a><br>С уважением, Aeroapart Team.<br><br><b>Апартамент <?=$apart_name?></b><br>с <?=$date_in?> до <?=$date_out?><br>
				депозит <?=$deposit?>₽, тариф <?=$tarif_total?>₽<br><br>
				<button class="btn btn-warning" id="start_registration">Начать регистрацию</a></button>
				<?
				if (isset($order_timeout) && $order_timeout > time()) {
					$timeout = $order_timeout - time();

					echo '<p class="text-center mt-4 greetings-timer">Бронь <span data-timeout="' . $timeout . '" id="order_greetings_timeout_display">' . date('i:s', $timeout) . '</span></p>';
				}
				?>
			</div>
		</div>

		<?if (!empty($apart_images)) {?>
			<div class="row mt-3">
				<div class="row">
					<?
					foreach ($apart_images as $key => $image) {?>
						<div class="col-lg-3 col-md-4 col-xs-6 thumb">
							<a class="thumbnail" href="#" data-image-id="" data-toggle="modal" data-title="<?='Апартамент ' . $apart_name?>"
								data-image="<?=WEB_ROOT . '/public/img/aparts/' . $apart_id . '/' . $image?>"
								data-target="#image-gallery">
								<img class="img-thumbnail"
								src="<?=WEB_ROOT . '/public/img/aparts/' . $apart_id . '/small/' . $image?>"
								alt="<?='Апартамент ' . $apart_name?>">
							</a>
						</div>
						<?}?>
					</div>


					<div class="modal fade" id="image-gallery" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
						<div class="modal-dialog modal-lg">
							<div class="modal-content">
								<div class="modal-header">
									<h4 class="modal-title" id="image-gallery-title"></h4>
									<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Закрыть</span>
									</button>
								</div>
								<div class="modal-body">
									<img id="image-gallery-image" class="img-responsive col-md-12" src="">
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-secondary float-left" id="show-previous-image"><i class="fa fa-arrow-left"></i>
									</button>

									<button type="button" id="show-next-image" class="btn btn-secondary float-right"><i class="fa fa-arrow-right"></i>
									</button>
								</div>
							</div>
						</div>

					</div>
				</div>
				<?}?>

				<script>
					$(function () {
						$('#start_registration').click(function () {
							$('#ac_registration_greetings').fadeOut(300, function () {
								$("#ac_registration_sms_check").fadeIn(300, function () {
									$([document.documentElement, document.body]).animate({
										scrollTop: $("#ac_registration_sms_check").offset().top
									}, 500);
								});
							});
						});

		//Галерея
		let modalId = $('#image-gallery');

		loadGallery(true, 'a.thumbnail');

		      //This function disables buttons when needed
		      function disableButtons(counter_max, counter_current) {
		      	$('#show-previous-image, #show-next-image')
		      	.show();
		      	if (counter_max === counter_current) {
		      		$('#show-next-image')
		      		.hide();
		      	} else if (counter_current === 1) {
		      		$('#show-previous-image')
		      		.hide();
		      	}
		      }

		      /**
		       *
		       * @param setIDs        Sets IDs when DOM is loaded. If using a PHP counter, set to false.
		       * @param setClickAttr  Sets the attribute for the click handler.
		       */

		       function loadGallery(setIDs, setClickAttr) {
		       	let current_image,
		       	selector,
		       	counter = 0;

		       	$('#show-next-image, #show-previous-image')
		       	.click(function () {
		       		if ($(this)
		       			.attr('id') === 'show-previous-image') {
		       			current_image--;
		       	} else {
		       		current_image++;
		       	}

		       	selector = $('[data-image-id="' + current_image + '"]');
		       	updateGallery(selector);
		       });

		       	function updateGallery(selector) {
		       		let $sel = selector;
		       		current_image = $sel.data('image-id');
		       		$('#image-gallery-title')
		       		.text($sel.data('title'));
		       		$('#image-gallery-image')
		       		.attr('src', $sel.data('image'));
		       		disableButtons(counter, $sel.data('image-id'));
		       	}

		       	if (setIDs == true) {
		       		$('[data-image-id]')
		       		.each(function () {
		       			counter++;
		       			$(this)
		       			.attr('data-image-id', counter);
		       		});
		       	}
		       	$(setClickAttr)
		       	.on('click', function () {
		       		updateGallery($(this));
		       	});
		       }
		     });

// Таймер регистрации
function start_registration_timer(duration, display) {
	var timer = duration, minutes, seconds;
	setInterval(function () {
		minutes = parseInt(timer / 60, 10);
		seconds = parseInt(timer % 60, 10);

		minutes = minutes < 10 ? "0" + minutes : minutes;
		seconds = seconds < 10 ? "0" + seconds : seconds;

		display.textContent = minutes + ":" + seconds;

		if (--timer < 0) {
			timer = duration;
			location.reload();
		}
	}, 1000);
}

var as_timeout_display_greetings = $('#order_greetings_timeout_display');
if (as_timeout_display_greetings.length > 0) {
	var as_timeout = as_timeout_display_greetings.data('timeout')
	start_registration_timer(as_timeout, as_timeout_display_greetings[0]);
}
</script>

</div>