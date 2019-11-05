<br><br><button type="button" id="send_sms_to_tenant" class="btn btn-warning">Отправить SMS со ссылкой<br>
<small>на <?=$phone?></small></button>

<script>
$('#send_sms_to_tenant').on('click', function() {
	$.ajax({
	  type: "POST",
	  url: AC_BASE_AJAX_URL + '/admin/get_registration_link/send_sms_link_to_tenant_ajax',
	  data: {url: "<?=$url?>", phone: "<?=$phone?>"},
	  dataType: "json",
	  success: function (msg) {
	    console.log(msg);
	    if (msg['status'] == 'ok') {
	    	alert('SMS отправлено на номер <?=$phone?>');
	    } else {
	      alert(msg['message']);
	      console.log(msg);
	    }
	  }
	});
});
	</script>
