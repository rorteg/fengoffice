<?php
	foreach ($projects as $project){
?>
	<div class="subwscrumbs">
		<a href="#" onclick="Ext.getCmp('workspace-panel').select(<?php echo $project->getId();?>);og.clearSubWsCrumbs()"><?php echo $project->getName()?></a>
	</div>
<?php } ?>