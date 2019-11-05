<div class="row"><div class="col-sm-12"><h4>Собственники</h4></div></div><hr>
<?php
foreach ($css_files as $file): ?>
	<link type="text/css" rel="stylesheet" href="<?php echo $file; ?>" />
<?php endforeach;?>
<style>
	</style>

	<?php echo $output; ?>
<?php foreach ($js_files as $file): ?>
	<script src="<?php echo $file; ?>"></script>
	<?php endforeach;
?>
