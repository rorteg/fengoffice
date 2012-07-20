<?php
	require_javascript("og/ObjectPicker.js");
	require_javascript("og/modules/addTemplate.js");
	require_javascript("og/DateField.js");
	
	
	$workspaces = active_projects();
	$genid = gen_id();
	$object = $cotemplate;
?>
<form  style='height:100%;background-color:white' class="internalForm" action="<?php echo get_url('template', 'instantiate', array('id' => $id))?>" method="post" enctype="multipart/form-data" onsubmit="return og.handleMemberChooserSubmit('<?php echo $genid; ?>', <?php echo $cotemplate->manager()->getObjectTypeId() ?>);">

<div class="template">
<div class="coInputHeader">
<div class="coInputMainBlock">	
		
	<?php if (isset ($workspaces) && count($workspaces) > 0) { ?>
	<div id="<?php echo $genid ?>add_template_select_workspace_div">
	<fieldset>
		<legend><?php echo lang('template context')?></legend>
		<?php
			if ($cotemplate->isNew()) {
				render_dimension_trees($cotemplate->manager()->getObjectTypeId(), $genid, null, array('select_current_context' => true)); 
			}else {
				render_dimension_trees($cotemplate->manager()->getObjectTypeId(), $genid, $cotemplate->getMemberIds()); 
			} 
		?>		
	</fieldset>
	</div>
	<?php } ?>
	
	<?php echo submit_button(lang('save changes'),'s',
		array('style'=>'margin-top:0px', 'tabindex' => '3')) ?>
</div>
</div>
</div>
</form>

<script>


	var memberChoosers = Ext.getCmp('<?php echo "$genid-member-chooser-panel-".$cotemplate->manager()->getObjectTypeId()?>').items;
	if (memberChoosers) {
		memberChoosers.each(function(item, index, length) {
			item.on('all trees updated', function() {
				var dimensionMembers = {};
				memberChoosers.each(function(it, ix, l) {
					dim_id = this.dimensionId;
					dimensionMembers[dim_id] = [];
					var checked = it.getChecked("id");
					for (var j = 0 ; j < checked.length ; j++ ) {
						dimensionMembers[dim_id].push(checked[j]);
					}
				});
				og.contextManager.lastCheckedMembers[<?php echo $cotemplate->manager()->getObjectTypeId() ?>] = {};
				og.contextManager.lastCheckedMembers[<?php echo $cotemplate->manager()->getObjectTypeId() ?>] = dimensionMembers ;
			});
		});
	}
	
</script>