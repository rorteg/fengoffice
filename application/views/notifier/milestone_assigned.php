<div style="font-family: Verdana, Arial, sans-serif; font-size: 12px;">
	<a href="<?php echo str_replace('&amp;', '&', $milestone_assigned->getViewUrl()) ?>" target="_blank" style="font-size: 18px;"><?php echo lang('milestone assigned', $milestone_assigned->getName()) ?></a><br><br>

	<?php echo lang('workspace') ?>: <span style="<?php echo get_workspace_css_properties($milestone_assigned->getProject()->getColor()); ?>">
	<?php echo $milestone_assigned->getProject()->getName() ?></span><br><br>

	<?php if (isset($date)) {
		 	echo "<br>";
		 	echo lang('date') ?>: <?php echo $date ?><?php echo "<br>";
		}
	?>
	<br><br>

	<div style="color: #818283; font-style: italic; border-top: 2px solid #818283; padding-top: 2px; font-family: Verdana, Arial, sans-serif; font-size: 12px;">
	<?php echo lang('system notification email'); ?><br>
	<a href="<?php echo ROOT_URL; ?>" target="_blank" style="font-family: Verdana, Arial, sans-serif; font-size: 12px;"><?php echo ROOT_URL; ?></a>
	</div>

</div>