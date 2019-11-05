<div class="container" id="ac_registration_form_container" style="display: none">
  <div class="offset-lg-2 col-lg-8 border-wrap">

    <form onsubmit="return false;" id="ac_registration_form">
      <h4 class="mb-4 text-center">
        Регистрация договора о присоединении к сервису
        <br/>
        <small>
          с
          <?=$date_in?>
          до
          <?=$date_out?> в апартаменты по адресу: <?=$data['apart_address']?> - <?=$data['apart_name']?>
        </small>
      </h4>
      <?
      if (isset($order_timeout) && $order_timeout > time()) {
        $timeout = $order_timeout - time();

        echo '<p class="text-center"><b>Ссылка на регистрацию действительна ещё <span data-timeout="' . $timeout . '" id="order_form_timeout_display">' . date('i:s', $timeout) . '</span></b></p>';
      }
      ?>

      <div class="form-group row">
        <div class="col-sm-12">
          <label for="input_name">Фамилия<small> / Family</small></label>
          <input class="form-control" required id="input_last_name" name="last_name" placeholder="" value="" type="text"/>
        </div>
      </div>
      <div class="form-group row">
        <div class="col-sm-12">
          <label for="input_first_name">Имя<small> / Name</small></label>
          <input class="form-control" required id="input_first_name" name="first_name" placeholder="" value="" type="text"/>
        </div>
      </div>
      <div class="form-group row">
        <div class="col-sm-12">
          <label for="input_second_name">Отчество<small> / Surname</small></label>
          <input class="form-control" required id="input_second_name" name="second_name" placeholder="" value="" type="text"/>
        </div>
      </div>
      <div class="form-group row">
        <div class="col-sm-12">
          <label for="input_birthdate">Дата рождения<small> / Date of birth</small></label>
          <input class="form-control" required id="input_birthdate" name="birthdate" placeholder="01.01.1990" value="" type="tel" pattern="[0-9]{2}.[0-9]{2}.[0-9]{2,4}"/>
        </div>
      </div>
      <div class="form-group row">
        <div class="col-sm-12">
         <label for="input_nationality">Гражданство<small> / Nationality</small></label>
         <select id="input_nationality" name="nationality" class="form-control custom-select" required>
           <option value="afghan">Afghan</option>
           <option value="albanian">Albanian</option>
           <option value="algerian">Algerian</option>
           <option value="american">American</option>
           <option value="andorran">Andorran</option>
           <option value="angolan">Angolan</option>
           <option value="antiguans">Antiguans</option>
           <option value="argentinean">Argentinean</option>
           <option value="armenian">Armenian</option>
           <option value="australian">Australian</option>
           <option value="austrian">Austrian</option>
           <option value="azerbaijani">Azerbaijani</option>
           <option value="bahamian">Bahamian</option>
           <option value="bahraini">Bahraini</option>
           <option value="bangladeshi">Bangladeshi</option>
           <option value="barbadian">Barbadian</option>
           <option value="barbudans">Barbudans</option>
           <option value="batswana">Batswana</option>
           <option value="belarusian">Belarusian</option>
           <option value="belgian">Belgian</option>
           <option value="belizean">Belizean</option>
           <option value="beninese">Beninese</option>
           <option value="bhutanese">Bhutanese</option>
           <option value="bolivian">Bolivian</option>
           <option value="bosnian">Bosnian</option>
           <option value="brazilian">Brazilian</option>
           <option value="british">British</option>
           <option value="bruneian">Bruneian</option>
           <option value="bulgarian">Bulgarian</option>
           <option value="burkinabe">Burkinabe</option>
           <option value="burmese">Burmese</option>
           <option value="burundian">Burundian</option>
           <option value="cambodian">Cambodian</option>
           <option value="cameroonian">Cameroonian</option>
           <option value="canadian">Canadian</option>
           <option value="cape verdean">Cape Verdean</option>
           <option value="central african">Central African</option>
           <option value="chadian">Chadian</option>
           <option value="chilean">Chilean</option>
           <option value="chinese">Chinese</option>
           <option value="colombian">Colombian</option>
           <option value="comoran">Comoran</option>
           <option value="congolese">Congolese</option>
           <option value="costa rican">Costa Rican</option>
           <option value="croatian">Croatian</option>
           <option value="cuban">Cuban</option>
           <option value="cypriot">Cypriot</option>
           <option value="czech">Czech</option>
           <option value="danish">Danish</option>
           <option value="djibouti">Djibouti</option>
           <option value="dominican">Dominican</option>
           <option value="dutch">Dutch</option>
           <option value="east timorese">East Timorese</option>
           <option value="ecuadorean">Ecuadorean</option>
           <option value="egyptian">Egyptian</option>
           <option value="emirian">Emirian</option>
           <option value="equatorial guinean">Equatorial Guinean</option>
           <option value="eritrean">Eritrean</option>
           <option value="estonian">Estonian</option>
           <option value="ethiopian">Ethiopian</option>
           <option value="fijian">Fijian</option>
           <option value="filipino">Filipino</option>
           <option value="finnish">Finnish</option>
           <option value="french">French</option>
           <option value="gabonese">Gabonese</option>
           <option value="gambian">Gambian</option>
           <option value="georgian">Georgian</option>
           <option value="german">German</option>
           <option value="ghanaian">Ghanaian</option>
           <option value="greek">Greek</option>
           <option value="grenadian">Grenadian</option>
           <option value="guatemalan">Guatemalan</option>
           <option value="guinea-bissauan">Guinea-Bissauan</option>
           <option value="guinean">Guinean</option>
           <option value="guyanese">Guyanese</option>
           <option value="haitian">Haitian</option>
           <option value="herzegovinian">Herzegovinian</option>
           <option value="honduran">Honduran</option>
           <option value="hungarian">Hungarian</option>
           <option value="icelander">Icelander</option>
           <option value="indian">Indian</option>
           <option value="indonesian">Indonesian</option>
           <option value="iranian">Iranian</option>
           <option value="iraqi">Iraqi</option>
           <option value="irish">Irish</option>
           <option value="israeli">Israeli</option>
           <option value="italian">Italian</option>
           <option value="ivorian">Ivorian</option>
           <option value="jamaican">Jamaican</option>
           <option value="japanese">Japanese</option>
           <option value="jordanian">Jordanian</option>
           <option value="kazakhstani">Kazakhstani</option>
           <option value="kenyan">Kenyan</option>
           <option value="kittian and nevisian">Kittian and Nevisian</option>
           <option value="kuwaiti">Kuwaiti</option>
           <option value="kyrgyz">Kyrgyz</option>
           <option value="laotian">Laotian</option>
           <option value="latvian">Latvian</option>
           <option value="lebanese">Lebanese</option>
           <option value="liberian">Liberian</option>
           <option value="libyan">Libyan</option>
           <option value="liechtensteiner">Liechtensteiner</option>
           <option value="lithuanian">Lithuanian</option>
           <option value="luxembourger">Luxembourger</option>
           <option value="macedonian">Macedonian</option>
           <option value="malagasy">Malagasy</option>
           <option value="malawian">Malawian</option>
           <option value="malaysian">Malaysian</option>
           <option value="maldivan">Maldivan</option>
           <option value="malian">Malian</option>
           <option value="maltese">Maltese</option>
           <option value="marshallese">Marshallese</option>
           <option value="mauritanian">Mauritanian</option>
           <option value="mauritian">Mauritian</option>
           <option value="mexican">Mexican</option>
           <option value="micronesian">Micronesian</option>
           <option value="moldovan">Moldovan</option>
           <option value="monacan">Monacan</option>
           <option value="mongolian">Mongolian</option>
           <option value="moroccan">Moroccan</option>
           <option value="mosotho">Mosotho</option>
           <option value="motswana">Motswana</option>
           <option value="mozambican">Mozambican</option>
           <option value="namibian">Namibian</option>
           <option value="nauruan">Nauruan</option>
           <option value="nepalese">Nepalese</option>
           <option value="new zealander">New Zealander</option>
           <option value="ni-vanuatu">Ni-Vanuatu</option>
           <option value="nicaraguan">Nicaraguan</option>
           <option value="nigerien">Nigerien</option>
           <option value="north korean">North Korean</option>
           <option value="northern irish">Northern Irish</option>
           <option value="norwegian">Norwegian</option>
           <option value="omani">Omani</option>
           <option value="pakistani">Pakistani</option>
           <option value="palauan">Palauan</option>
           <option value="panamanian">Panamanian</option>
           <option value="papua new guinean">Papua New Guinean</option>
           <option value="paraguayan">Paraguayan</option>
           <option value="peruvian">Peruvian</option>
           <option value="polish">Polish</option>
           <option value="portuguese">Portuguese</option>
           <option value="qatari">Qatari</option>
           <option value="romanian">Romanian</option>
           <option value="russian" selected>Россия<small> / Russian</small></option>
           <option value="rwandan">Rwandan</option>
           <option value="saint lucian">Saint Lucian</option>
           <option value="salvadoran">Salvadoran</option>
           <option value="samoan">Samoan</option>
           <option value="san marinese">San Marinese</option>
           <option value="sao tomean">Sao Tomean</option>
           <option value="saudi">Saudi</option>
           <option value="scottish">Scottish</option>
           <option value="senegalese">Senegalese</option>
           <option value="serbian">Serbian</option>
           <option value="seychellois">Seychellois</option>
           <option value="sierra leonean">Sierra Leonean</option>
           <option value="singaporean">Singaporean</option>
           <option value="slovakian">Slovakian</option>
           <option value="slovenian">Slovenian</option>
           <option value="solomon islander">Solomon Islander</option>
           <option value="somali">Somali</option>
           <option value="south african">South African</option>
           <option value="south korean">South Korean</option>
           <option value="spanish">Spanish</option>
           <option value="sri lankan">Sri Lankan</option>
           <option value="sudanese">Sudanese</option>
           <option value="surinamer">Surinamer</option>
           <option value="swazi">Swazi</option>
           <option value="swedish">Swedish</option>
           <option value="swiss">Swiss</option>
           <option value="syrian">Syrian</option>
           <option value="taiwanese">Taiwanese</option>
           <option value="tajik">Tajik</option>
           <option value="tanzanian">Tanzanian</option>
           <option value="thai">Thai</option>
           <option value="togolese">Togolese</option>
           <option value="tongan">Tongan</option>
           <option value="trinidadian or tobagonian">Trinidadian or Tobagonian</option>
           <option value="tunisian">Tunisian</option>
           <option value="turkish">Turkish</option>
           <option value="tuvaluan">Tuvaluan</option>
           <option value="ugandan">Ugandan</option>
           <option value="ukrainian">Ukrainian</option>
           <option value="uruguayan">Uruguayan</option>
           <option value="uzbekistani">Uzbekistani</option>
           <option value="venezuelan">Venezuelan</option>
           <option value="vietnamese">Vietnamese</option>
           <option value="welsh">Welsh</option>
           <option value="yemenite">Yemenite</option>
           <option value="zambian">Zambian</option>
           <option value="zimbabwean">Zimbabwean</option>
         </select>
       </div>
     </div>
     <div class="form-group row">
      <div class="col-sm-12">
       <label for="input_passport_type">Тип паспорта<small> / Passport type</small></label>
       <select id="input_passport_type" name="passport_type" class="custom-select form-control" required>
         <option value="rus" selected>Паспорт РФ / Russian passport</option>
         <option value="int">Загран. паспорт / International passport</option>
       </select>
     </div>
   </div>
   <div class="form-group row">
    <div class="col-sm-12">
      <label for="input_passport_number">Серия и номер паспорта<small> / Passport number</small></label>
      <input class="form-control" required id="input_passport_number" name="passport_number" placeholder="0000 111222" type="text" value=""/>
    </div>
  </div>
<!--   <div class="form-group row">
    <div class="col-sm-12">
      <label for="input_phone">Телефон<small> / Phone</small></label>
      <input class="form-control input_phone" readonly="true" required id="input_phone" name="phone"  type="tel" value=""/>
    </div>
  </div> -->
  <div class="form-group row">
    <div class="col-sm-12">
      <label for="input_email">Эл. почта<small> / Email</small></label>
      <input class="form-control" required id="input_email" name="email" placeholder="your-e@mail.ru" type="email" value=""/>
    </div>
  </div>
  <div class="form-group">
    <label for="input_comment">Особые пожелания<small> / Special wishes</small></label>
    <textarea class="form-control" name="comment" id="input_comment" placeholder="Например, укажите номер автомобиля для пропуска" rows="2"></textarea>
  </div>
  <div class="form-check">
    <input class="form-check-input form-check-input-lg" type="checkbox" id="input_rules_accept" required>
    <label class="form-check-label" for="input_rules_accept">
      С <a href="https://aeroapart.ru/rules" target="_blank">правилами</a> ознакомлен(а)
    </label>
  </div>
  <hr>
  <div class="form-group row">
    <div class="col-sm-12 text-center">
      <button class="btn btn-warning" id="registration_btn">Зарегистрироваться
      </button>
    </div>
  </div>
  <input required id="input_link" name="link" type="hidden" value="<?=$link?>">
</form>

</div>
</div>

<script>
  $(function () {

    var input_birthdate = document.getElementById('input_birthdate');
    if (input_birthdate != undefined) {
      vanillaTextMask.maskInput({
        inputElement: input_birthdate,
        mask: [/[0123]/, /[0-9]/, '.', /[01]/, /[0-9]/, '.', /[12]/, /[09]/, /[0-9]/, /[0-9]/]
      })
    }

/*    vanillaTextMask.maskInput({
      inputElement: document.querySelector('.input_phone'),
      guide: false,
      mask: [/[+0-9]/, /[0-9]/, /[0-9]/, /[0-9]/, /[0-9]/, /[0-9]/, /[0-9]/, /[0-9]/, /[0-9]/, /[0-9]/, /[0-9]/, /[0-9]/]
    })*/

    $('#input_name1, #input_name2,#input_name3,#input_passport').change(function () {
      $(this).val($(this).val().replace(/[^a-zа-я0-9\s-]/gui, ''));
    });

    $('#input_nationality').change(function () {
      if ($(this).val() != 'russian') {
        $('#input_passport_type').val('int');
      } else {
        $('#input_passport_type').val('rus');
      }
        // $('#input_passport_type').selectpicker('refresh');
        $('#input_passport_number').focus();
      })

    registration_btn = $('#registration_btn');
    registration_btn.click(function () {
      var form = $('#ac_registration_form');

      if (form[0].checkValidity()) {
        $('.ac-loading-text').text('Формируем договор');

        setTimeout(function () {
          $('.ac-loading-text').text('Надуваем воздушные шары!');
        }, 12000);

        $('#ac-body').fadeOut(300, function () {
          $('.ac-loading').fadeIn();
        });

        $.ajax({
          type: "POST",
          url: AC_BASE_AJAX_URL + '/registration/registration_ajax',
          data: form.serialize(),
          dataType: 'json',
        })
        .done(function (data) {
            $('.ac-loading-text').text('Готово!');
            setTimeout(function () {
              $('.ac-loading').fadeOut(500, function () {
                $("#ac-body").html(data.html).fadeIn(300, function() {
                  $([document.documentElement, document.body]).animate({
                    scrollTop: $("#ac_registration_success").offset().top
                  }, 500);
                });
              });
            }, 500);
          })
        .fail(function (data) {
            alert('При создании договора возникла какая-то ошибка, передайте пожалуйста данную информацию администратору, спасибо.');
            location.reload();
          });
      }
    });

    var as_timeout_display_form = $('#order_form_timeout_display');
    if (as_timeout_display_form.length > 0) {
      var as_timeout = as_timeout_display_form.data('timeout')
      start_registration_timer(as_timeout, as_timeout_display_form[0]);
    }
  });
</script>