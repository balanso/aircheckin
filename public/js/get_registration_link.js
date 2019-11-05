$(function () {
  var input_marketing_agent_id = $('#input_marketing_agent_id');
  var input_tarif_total = $('#input_tarif_total');
  var input_tarif = $('#input_tarif');
  var input_apart_id = $('#input_apart_id');
  var input_payment_method_id = $('#input_payment_method_id');
  var input_deposit = $('#input_deposit');
  var input_pms_order_id = $('#input_pms_order_id');
  var input_service_agent_percent = $('#input_service_agent_percent');
  var form_group_other = $('#form_group_other');
  var form_group_apart_id = $('#form_group_apart_id')
  var form_group_pms_order_id = $('#form_group_pms_order_id');
  var selected_dates;
  $('#js_toggle_fields').hide();
  $('#input_service_agent_id').val(1);
  $('#input_service_agent_percent').val($('#input_service_agent_id option:selected').data('percent'));


  var start = new Date();
  start.setHours(14);
  start.setMinutes(0);
  var total_days = 0;
  var price = 0;
  var oneDay = 24 * 60 * 60 * 1000; // hours*minutes*seconds*milliseconds


  var input_order_dates = $('#input_order_dates');
  var dp = input_order_dates.datepicker({
    position: 'top left',
    hoursStep: 1,
    timepicker: true,
    startDate: start,
    range: true,
    autoClose: true,
    multipleDatesSeparator: ' - ',
    onSelect: function onSelect(fd, date) {
      if (date.length > 1) {
        var firstDate = new Date(date[0]);
        var secondDate = new Date(date[1]);
        var diffDays = Math.round(Math.abs((firstDate.getTime() - secondDate.getTime()) / (oneDay)));
        total_days = diffDays;
        selected_dates = dp.data('datepicker').selectedDates;

        // Если маркетинговый агент == бронирование по звонку то подгружаем свободные апарты в эти даты
        if (input_marketing_agent_id.val() == 0) {
          var dates_old_val = input_order_dates.val();
          input_order_dates.removeClass('readonly-clear').val('Ищем свободные апартаменты');
          input_tarif.val(0);
          input_tarif_total.val(0);
          form_group_other.fadeOut();
          form_group_apart_id.fadeOut();
          $.ajax({
            type: "POST",
            url: '/admin/get_registration_link/get_pms_items_for_date_ajax',
            data: {
              timestamp_in: firstDate.getTime() / 1000,
              timestamp_out: secondDate.getTime() / 1000,
              total_days: total_days,
            },
            dataType: 'json',
            success: function (data) {
              // Подставляем выбранные даты в поле дат
              input_order_dates.addClass('readonly-clear').val(dates_old_val);

              // Если пришёл ответ с апартами то вставляем HTML из ответа
              if (data.status == 'ok') {
                input_apart_id.html(data.html);

                // Показываем поле выбора апарта
                form_group_apart_id.fadeIn(function () {
                  $([document.documentElement, document.body]).animate({
                    scrollTop: $("#input_order_dates").offset().top
                  }, 500);
                });




                input_apart_id.one('change', function () {
                  var this_tarif = $(this).find(':selected').data('tarif');
                  input_tarif.val(this_tarif);
                  input_tarif_total.val(this_tarif * total_days);

                  form_group_other.fadeIn(300, function () {
                    $([document.documentElement, document.body]).animate({
                      scrollTop: form_group_other.offset().top
                    }, 500, function() {
                      input_deposit.focus();
                    });
                  });
                });
              } else if (data.status == 'warning') {
                alert(data.message);
                form_group_apart_id.fadeOut();
                form_group_other.fadeOut();
                $('#input_order_dates').val('');
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

        // $('#input_cleanings').val(Math.ceil(total_days / 5));
      }
    },
    // Выключаем даты которых нет?
    onRenderCell: function (date, cellType) {
      var curr_date = date.getDate();
      var curr_month = date.getMonth() + 1;
      var curr_year = date.getFullYear();
      var curr_date_full = curr_year + "-" + curr_month + "-" + curr_date;

      if (curr_date_full == '0') {
        return {
          classes: 'my-class',
          disabled: true
        }
      }
    }
  });

  input_apart_id.on('change', function () {
    if (selected_dates.length > 1) {
      tarif = input_apart_id.find('option:selected').data('tarif');
      input_tarif.val(tarif);
      input_tarif_total.val(total_days * tarif);
    }
  });

/*  input_marketing_agent_id.on('change', function () {
    percent = $('#input_service_agent_id option:selected').data('percent');
    if (percent && percent > 0) {
      $('#input_service_agent_percent').val(percent);
    } else {
      $('#input_service_agent_percent').val(0);
    }
  });*/



  // При смене маркетингового партнёра работаем с полями
  input_marketing_agent_id.on('change', function () {
    percent = $('#input_marketing_agent_id option:selected').data('percent');
    if (percent && percent > 0) {
      $('#input_marketing_agent_percent').val(percent);
    } else {
      $('#input_marketing_agent_percent').val(0);
    }

    // Если маркетинговый агент == бронирование по звонку то показываем форму брони
    if (input_marketing_agent_id.val() == 0) {
      input_pms_order_id.val(0);

      form_group_apart_id.hide();
      form_group_other.hide();
      form_group_pms_order_id.fadeOut(300,
        function () {
          if ($('#js_toggle_fields:hidden').length > 0) {
            $('#js_toggle_fields').fadeIn();
          }
        });
    } else {
      // AirBNB
      if (input_marketing_agent_id.val() == 3) {
        input_payment_method_id.val(3).attr('disabled', true).attr('readonly', true);
      } else {
        input_payment_method_id.removeAttr('disabled').removeAttr('readonly').val(1);
      }

      if (input_pms_order_id.val() == 0) {
        input_pms_order_id.val('');
      }

      $("#js_toggle_fields:visible").fadeOut(300, function () {
        form_group_pms_order_id.fadeIn(300, function () {
          input_pms_order_id.focus();
        });
      });

      input_pms_order_id.focus();
    }
  });

  input_payment_method_id.on('change', function () {
    if (input_payment_method_id.val() != 1) {
      input_service_agent_percent.val('11.2');
    } else {
      input_service_agent_percent.val('13.2');
    }
  });

  input_tarif.on('keyup', function () {
    price = $(this).val() * total_days
    if (selected_dates.length > 1) {
      $('#input_tarif_total').val(price);
    }
  });

  input_tarif_total.on('keyup', function () {
    price_per_day = Math.ceil($(this).val() / total_days)
    if (selected_dates.length > 1) {
      input_tarif.val(price_per_day);
    }
  });

  input_pms_order_id.on('keypress', function (e) {
    if (e.which == 13) {
      get_pms_order_button.click();
    }
  });

  var get_pms_order_button = $('#get_pms_order_data');
  get_pms_order_button.click(function (e) {
    e.preventDefault();
    pms_order_id = input_pms_order_id.val();

    if (pms_order_id && pms_order_id > 0) {
      var old_text = get_pms_order_button.text();
      get_pms_order_button.attr('disabled', 'disabled').html('<span class="spinner-border hide text-secondary spinner-border-sm" role="status" aria-hidden="true"></span> Ищем заказ...');
      $.ajax({
          type: "POST",
          url: '/admin/get_registration_link/get_pms_order_data_ajax',
          data: {
            order_id: pms_order_id
          },
          dataType: 'json',
        }).done(function (answer) {
          get_pms_order_button.removeAttr('disabled').text(old_text);
          console.log(answer);
          if (answer.status == 'ok') {
            var order = answer.data;
            date_from = new Date(order.date_from + ' 14:00');
            date_to = new Date(order.date_to + ' 11:00');

            dp.data('datepicker').selectDate(date_from);
            dp.data('datepicker').selectDate(date_to);
            selected_dates = dp.data('datepicker').selectedDates;
            dp.data('datepicker').destroy();

            var total_days = Math.round(Math.abs((date_from.getTime() - date_to.getTime()) / (oneDay)));
            input_apart_id.off('change');
            input_apart_id.val(input_apart_id.find('option:contains("' + order.apart_name + '")').val());
            input_apart_id.attr('readonly', true).attr('disabled', true);
            input_tarif.val(order.tarif_total / total_days);
            input_tarif_total.val(order.tarif_total);
            $('#input_guests').val(order.guests);
            // $('#input_cleanings').val(Math.ceil(total_days / 5));
            $('#input_order_dates').attr('readonly', 'readonly').removeClass('readonly-clear');
            input_marketing_agent_id.attr('readonly', true).attr('disabled', true);

            $('#js_toggle_fields, #form_group_apart_id, #form_group_other').fadeIn();
            $('#form_group_apart_id').fadeIn();
            $('#form_group_other').fadeIn(function () {
              input_deposit.focus();
            });


            $([document.documentElement, document.body]).animate({
              scrollTop: input_apart_id.offset().top
            }, 500);
            input_pms_order_id.attr('readonly', true);
            $('#get_pms_order_data').attr('disabled', 'disabled');
          } else {
            alert(answer.message);
          }
        })
        .fail(function (data) {
          alert('При получении списка апартаментов возникла ошибка, обратитесь к администратору для решения проблемы или попробуйте перезагрузить страницу.');
          console.log(data);
        });
    } else {
      alert('Введите номер заказа');
    }
  });

  var get_registration_link = $('#get_registration_link');
  get_registration_link.click(function () {
    var old_text = get_registration_link.text();

    var form = $('#get_registration_link_form');
    if (form[0].checkValidity()) {
      input_marketing_agent_id.removeAttr('disabled');
      input_apart_id.removeAttr('disabled');
      input_payment_method_id.removeAttr('disabled');
      input_marketing_agent_id.removeAttr('disabled');

      get_registration_link.attr('disabled', 'disabled').html('<span class="spinner-border hide text-secondary spinner-border-sm" role="status" aria-hidden="true"></span> Формируем ссылку...');
      $.ajax({
          type: "POST",
          url: '/admin/get_registration_link/get_registration_link_ajax',
          data: form.serialize(),
          dataType: 'json'
        })
        .done(function (answer) {
          get_registration_link.text(old_text).removeAttr('disabled');

          if (answer.status != 'ok') {
            alert(answer.message);
            console.log(answer.message);
          } else {
            if (dp.data('datepicker')) {
              dp.data('datepicker').destroy();
            }

            $('#ac-body').html(answer.html);
          }
        })
        .fail(function (answer) {
          alert('При создании ссылки возникла ошибка, обратитесь к администратору для решения проблемы или попробуйте перезагрузить страницу.');
          console.log(answer);
        });

    } else {
      console.log("Invalid form!")
    }
  });

  $('#get_registration_link_form').submit(function () {
    return false;
  })

  $('body').on('mousedown', '#copy_link_btn', function (e) {
    var text = document.getElementById('registration_link');
    var selection = window.getSelection();
    var range = document.createRange();
    range.selectNodeContents(text);
    selection.removeAllRanges();
    selection.addRange(range);
    document.execCommand('copy');
    selection.removeAllRanges();
    $('#modal_with_reg_link').modal('toggle');
  });
});