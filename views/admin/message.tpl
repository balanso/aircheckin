<? if (isset($title)) {?>
	<div class="row"><div class="col-sm-12"><h4><?=$title?></h4></div></div><hr>
<? } ?>

<?if (isset($message)) {?>
	<div class="row">
		<div class="col">
			<p><?=$message?></p>
		</div>
	</div>
<?}?>