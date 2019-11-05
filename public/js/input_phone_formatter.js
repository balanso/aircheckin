$(function () {
  var telInput = document.querySelector("#input_phone");
  var telObject = window.intlTelInput(telInput, {
    dropdownContainer: document.body,
    formatOnDisplay: true,
    hiddenInput: '',
    separateDialCode: true,
    utilsScript: "",
    // onlyCountries: ['ru'],
    initialCountry: 'ru',
  });

  var phoneMask = [/[1-9]/, /\d/, /\d/, ' ', /\d/, /\d/, /\d/, '-', /\d/, /\d/, '-', /\d/, /\d/]

  // Assuming you have an input element in your HTML with the class .myInput

  var maskedInputController = vanillaTextMask.maskInput({
    inputElement: telInput,
    mask: phoneMask,
    showMask: false,
    guide: false,
  })

  var tel_input_jq = $('#input_phone');
  var input_phone_real = $('#input_phone_hidden');
  var deal_code_block = $('.iti__selected-dial-code').first();

  tel_input_jq.on('change', function () {
    var selected_deal_code = deal_code_block.text();
    var new_val = tel_input_jq.val();
    new_val = new_val.replace(/[^\d]/g, '');

    if (new_val != '') {
      input_phone_real.val(selected_deal_code + new_val);
    } else {
      input_phone_real.val('');
    }
  });

  telInput.addEventListener("countrychange", function() {
    var selected_deal_code = deal_code_block.text();
    var new_val = tel_input_jq.val();
    new_val = new_val.replace(/[^\d]/g, '');

    if (new_val != '') {
      input_phone_real.val(selected_deal_code + new_val);
    } else {
      input_phone_real.val('');
    }
  });
});