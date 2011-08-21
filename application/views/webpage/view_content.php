<b><?php echo lang("url") ?>: </b><a target="_blank" href="<?php echo $url ?>"><?php echo $url ?></a>
<?php if (isset($desc) && trim($desc) != "") { ?>
	<fieldset><legend><?php echo lang('description') ?></legend>
		<?php echo $desc ?>
	</fieldset>
<?php } ?>
