<option value="" selected hidden>Выберите апартамент</option>
<?foreach ($aparts as $key => $value) {?>
		<option data-pms-id="<?=$value['pms_id']?>" data-tarif="<?=$value['tarif']?>" value="<?=$value['id']?>"><?=$value['name']?></option>
<?}?>