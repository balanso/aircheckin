<div class="container text-center" id="ac_registration_sms_check" style="display: none">
  <form id="phone_check_form">
    <h5 class="text-center">Проверка номера телефона</h5>
    <p>Введите номер телефона, мы отправим на него SMS с кодом проверки</p>
    <div class="form-group row center-block">
      <div class="col-sm-12">
        <div class="input-group">
          <input class="form-control input_phone" id="input_phone" name="phone_view" type="tel"/>
          <input id="input_phone_hidden" name="phone" type="hidden"/>
        </div>
      </div>
    </div>
    <div class="form-group row d-none div_input_check">
      <div class="col-md-3 center-block">
        <div class="input-group">
          <input class="form-control" id="input_code" min="1000" max="9999" name="code" placeholder="Проверочный код" type="number"/>
        </div>
      </div>
    </div>
    <input type="hidden" name="short_link" value="<?=$link?>"/>
    <button type="submit" id="send_code_button" class="btn btn-warning mb-1">Отправить проверочный код</button>
    <button type="submit" id="check_code_button" class="btn btn-warning mb-1" style="display: none">Подтвердить код</button>
    <br/>
    <div id="small_action_buttons">
      <a href="#" id="have_code" class="small text-secondary text-underline">
        <u>У меня уже есть код</u>
      </a>
    </div>
  </form>
</div>
<script>
  $(function () {
    var sms_sended_timestamp = 0;
    var code_input_shown = false;
    var sms_timer;

    function enable_send_button() {
      $('#send_code_button').attr('disabled', false).text('Отправить прочерочный код');
    }

    function set_send_sms_timeout() {
      var current_time = Math.round(new Date().getTime() / 1000);
      var wait_time = 60;

      if (sms_sended_timestamp + wait_time > current_time) {
        wait_time = sms_sended_timestamp + wait_time - current_time;

        $('#send_code_button').attr('disabled', true);
        var container = $("#send_code_button");
        var message = "Повторная отправка через ";

        container.html(message + wait_time + ' сек');
        sms_timer = setInterval(function () {
          if (--wait_time && wait_time > 0) {
            container.html(message + wait_time + ' сек');
          } else {
            clearInterval(sms_timer);
            enable_send_button();
          }
        }, 1000);

      } else {
        // enable_send_button();
        return true;
      }


    }

    function show_check_code_input() {
      // clearInterval(sms_timer);
      // enable_send_button();
      $('.div_input_check').hide().removeClass('d-none').show('fast');
      $('#send_code_button').hide();
      $('#check_code_button').show();
      $('#small_action_buttons').html('<a href="#" id="send_again" class="small text-secondary text-underline"><u>Отправить SMS ещё раз</u></a>');
      $('#input_code').focus();
      code_input_shown = true;
    }

    function hide_check_code_input() {
      // clearInterval(sms_timer);
      // enable_send_button();
      $('.div_input_check').hide('fast');
      $('#check_code_button').hide();
      $('#send_code_button').show();
      $('#input_phone_check').attr('readonly', false);
      $('#small_action_buttons').html('<a href="#" id="have_code" class="small text-secondary text-underline"><u>У меня уже есть код</u></a>');
      code_input_shown = false;
    }

    function send_code() {
      $.ajax({
        type: "POST",
        url: AC_BASE_AJAX_URL + '/registration/send_auth_sms_ajax',
        data: $('#phone_check_form').serialize(),
        dataType: "json",
        success: function (msg) {
          console.log(msg);
          if (msg['status'] == 'ok') {
            $('#input_phone_check').attr('readonly', true);
            show_check_code_input();
            sms_sended_timestamp = Math.round(new Date().getTime() / 1000);
            set_send_sms_timeout();
            $('#input_code').focus();
          } else {
            alert('Не удалось отправить SMS: ' + msg['message']);
            sms_sended_timestamp = Math.round(new Date().getTime() / 1000);
            set_send_sms_timeout();
          }
        },
        error: function(msg) {
          alert('Не удалось отправить SMS, обратитесь к администратору');
        }
      });
    }

    $('#input_code').on('keyup', function() {
      if ($(this).val().length == 4) {
        check_code();
      }
    });

    function check_code() {
      $.ajax({
        type: "POST",
        url: AC_BASE_AJAX_URL + '/registration/check_auth_sms_ajax',
        data: $('#phone_check_form').serialize(),
        dataType: "json",
        success: function (msg) {
          {
            if (msg['status'] == 'ok') {
              $('#ac_registration_sms_check').fadeOut(300, function () {
                load_tenant_data();
                $("#ac_registration_form_container").fadeIn(300, function () {
                  $([document.documentElement, document.body]).animate({
                    scrollTop: $("#ac_registration_form_container").offset().top
                  }, 500);
                });
              });
            } else {
              $('#input_code').val('').addClass('is-invalid');
              $('#check_code_button').attr('disabled', false);
              console.log(msg);
            }
          }
        }
      });
    }

    $('body').on('click', '#have_code', function () {
      show_check_code_input()
    });

    $('body').on('click', '#send_again', function () {
      hide_check_code_input();
    });

    $('#input_code').on('focus focusout', function () {
      if ($('#input_code').hasClass('is-invalid')) {
        $('#input_code').removeClass('is-invalid')
      }
    });

    $('#send_code_button').click(function (e) {
      if ($("#phone_check_form")[0].checkValidity()) {
        e.preventDefault();
        $(this).attr('disabled', true);
        send_code();
      }
    });

    $('#check_code_button').click(function (e) {
      if ($("#phone_check_form")[0].checkValidity()) {
        e.preventDefault();
        $(this).attr('disabled', true);
        check_code();
      }
    });

    function load_tenant_data() {
      $.ajax({
        type: "POST",
        url: AC_BASE_AJAX_URL + '/registration/load_tenant_data_ajax',
        data: $('#ac_registration_form').serialize(),
        dataType: "json",
        success: function (msg) {
          console.log(msg);
          if (msg['status'] == 'ok') {
            $('#input_first_name').val(msg.data.first_name);
            $('#input_second_name').val(msg.data.second_name);
            $('#input_last_name').val(msg.data.last_name);
            $('#input_birthdate').val(msg.data.birthdate);
            $('#input_passport_type').val(msg.data.passport_type);
            $('#input_passport_number').val(msg.data.passport_number);
            $('#input_email').val(msg.data.email);


          } else {
            location.reload();
            alert(msg['message']);
            console.log(msg);
          }
        },
        error: function(msg) {
          alert('Не удалось загрузить данные арендатора, обратитесь к администратору!');
        }
      });
    }
  });
</script>

<script src="<?=WEB_ROOT?>/public/js/input_phone_formatter.js?v2"></script>
