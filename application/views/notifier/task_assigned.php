<div style="font-family: Verdana, Arial, sans-serif; font-size: 12px;">
	<a href="<?php echo str_replace('&amp;', '&', $task_assigned->getViewUrl()) ?>" style="font-size: 18px;"><?php echo lang('task assigned', $task_assigned->getTitle()) ?></a><br><br>
	
	<?php echo lang('workspace') ?>: <span style="<?php echo get_workspace_css_properties($task_assigned->getProject()->getColor()); ?>">
	<?php echo $task_assigned->getProject()->getName() ?></span><br><br>
	
	<?php if ($task_assigned->getMilestone() instanceof Milestone) {
		echo lang('milestone') . ': ' . $task_assigned->getMilestone()->getName();?>
		<br><br>
	<?php } ?>
	
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