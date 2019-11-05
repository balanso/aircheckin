$(function () {
  $('#get_pay_link').click(function () {
    var form = $('#get_pay_link_form');
    if (form[0].checkValidity()) {
      $.ajax({
        type: "POST",
        url: '/get_pay_link/get_pay_link_ajax',
        data: form.serialize(),
        success: function (msg) {
          if (msg.search(/Error/i) != -1) {
            console.log(msg);
          } else {
            form.find("input, textarea").val('');
            $('#pay_link').attr('href', msg).text(msg);
            $('#modal_with_pay_link').modal('toggle');
          }
        }
      });
    } else {
      console.log("Invalid form!")
    }
  });

  $('#get_pay_link_form').submit(function () {
    return false;
  })

  $('#copy_link_btn').mousedown(function (e) {
    var text = document.getElementById('pay_link');
    var selection = window.getSelection();
    var range = document.createRange();
    range.selectNodeContents(text);
    selection.removeAllRanges();
    selection.addRange(range);
    document.execCommand('copy');
    selection.removeAllRanges();
    $('#modal_with_pay_link').modal('toggle');
  });

  $('.input-group-prepend').click(function () {
    $(this).next('input').focus();
  })
});