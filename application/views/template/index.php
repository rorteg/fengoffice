<?php
set_page_title(lang('templates'));
add_page_action(lang('new template'), get_url('template', 'add'), 'ico-add');
$genid = gen_id();
?>


<div class="adminClients" style="height: 100%; background-color: white">
<div class="adminHeader">
<div class="adminTitle"><?php echo lang('templates') ?></div>
</div>
<div class="adminSeparator"></div>
<div class="adminMainBlock"><?php if(isset($templates) && is_array($templates) && count($templates)) { ?>
<table style="min-width: 400px; margin-top: 10px;"
	id="<?php echo $genid ?>-ws">
	<tr>
		<th><?php echo lang('template') ?></th>
		<th style="min-width: 150px"><?php echo lang('workspaces') ?></th>
		<th><?php echo lang('actions') ?></th>
	</tr>
	<?php
	$isAlt = true;
	foreach($templates as $cotemplate) {
		$isAlt = !$isAlt; ?>
	<tr class="<?php echo $isAlt? 'altRow' : ''?>">
		<td><a class="internalLink ico-template bg-ico"
			href="<?php echo $cotemplate->getViewUrl() ?>"><?php echo clean($cotemplate->getName()) ?></a></td>
		<td style="text-align: center"><?php
		$workspaces = $cotemplate->getWorkspaces();
		$project_ids = array();
		foreach ($workspaces as $workspace)
		$project_ids[] = $workspace->getId();
		?> <span class="project-replace"><?php echo implode(',',$project_ids) ?></span>
		</td>
		<?php
		$options = array();
		if ($cotemplate->canEdit(logged_user())) {
			$options[] = '<a class="internalLink" href="' . $cotemplate->getEditUrl() .'&popup=true">' . lang('edit') . '</a>';
		}
		if(can_manage_workspaces(logged_user())) {
			$options[] = '<a class="internalLink" href="' . $cotemplate->getAssignTemplateToWSUrl() . '">' . lang('assign to workspace') . '</a>';
		} // if
		if($cotemplate->canDelete(logged_user())) {
			$options[] = '<a class="internalLink" href="' . $cotemplate->getDeleteUrl() .'&popup=true" onclick="return confirm(\'' . escape_single_quotes(lang('confirm delete template')) . '\')">' . lang('delete template') . '</a>';
		}
		?>
		<td style="font-size: 80%;"><?php echo implode(' | ', $options) ?></td>
	</tr>
	<?php } // foreach ?>
</table>
<?php } else { ?> <?php echo lang('no templates') ?><br>
<?php } // if ?> <br>
<a href="<?php echo get_url("template", "add") ?>"
	class="internalLink ico-add bg-ico"><?php echo lang("new template") ?></a>
</div>
</div>

<script>
og.showWsPaths('<?php echo $genid ?>-ws');
</script>
