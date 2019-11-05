<div class="container">
	<div class="row text-center">
		<div class="col">
			<? if (isset($title)) { ?>
			<h3><?=$title?></h3>
			<? } ?>
			<? if (isset($text)) { ?>
				<p><?=$text?></p>
			<? } ?>
		</div>
	</div>
</div>
