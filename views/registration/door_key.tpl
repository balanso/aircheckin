<? if (isset($door_key)) {
	if ($door_key == 'enabled') {
		load_tpl('/views/registration/door_key_enabled.tpl', ['short_link'=>$short_link]);
	}

	if ($door_key == 'disabled') {
		load_tpl('/views/registration/door_key_disabled.tpl');
	}
}?>
