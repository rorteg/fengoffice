<?php
  require_javascript('og/modules/addMessageForm.js'); 
  $genid = gen_id();
  $object = $milestone;
  $all = true;
	if (active_project()!= null)
		$all = false;
  if ($milestone->isNew()) {
  	$project = active_or_personal_project();
  } else {
  	$project = $milestone->getProject();
  }
  
?>
<form style='height:100%;background-color:white' class="internalForm" action="<?php echo $milestone->isNew() ? get_url('milestone', 'add', array("copyId" => array_var($milestone_data, 'copyId'))) : $milestone->getEditUrl() ?>" method="post">

<div class="milestone">
<div class="coInputHeader">
	<div class="coInputHeaderUpperRow">
	<div class="coInputTitle"><table style="width:535px">
	<tr><td><?php
		if ($milestone->isNew()) {
			if (array_var($milestone_data, 'is_template', false)) {
				echo lang('new milestone template');
			} else if (isset($milestone_task ) && $milestone_task instanceof ProjectTask) {
				echo lang('new milestone from template');
			} else {
				echo lang('new milestone');
			}
		} else {
			echo lang('edit milestone');
		}
	?>
	</td><td style="text-align:right"><?php echo submit_button($milestone->isNew() ? (array_var($milestone_data, 'is_template', false) ? lang('save template') : lang('add milestone')) : lang('save changes'),'s',array('style'=>'margin-top:0px;margin-left:10px', 'tabindex' => '5')) ?></td></tr></table>
	</div>
	
	</div>
	<div>
	<?php echo label_tag(lang('name'), $genid. 'milestoneFormName', true) ?>
	<?php echo text_field('milestone[name]', array_var($milestone_data, 'name'), 
		array('class' => 'title', 'id' => $genid .'milestoneFormName', 'tabindex' => '1')) ?>
	</div>
	
	<?php $categories = array(); Hook::fire('object_edit_categories', $object, $categories); ?>
	
	<div style="padding-top:5px">
		<?php if ($all) { ?>
			<a href="#" class="option" style="font-weight:bold" onclick="og.toggleAndBolden('<?php echo $genid ?>add_milestone_select_workspace_div',this)"><?php echo lang('workspace') ?></a> - 
		<?php } else {?>
			<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_milestone_select_workspace_div',this)"><?php echo lang('workspace') ?></a> -
		<?php }?> 
		<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_milestone_tags_div', this)"><?php echo lang('tags') ?></a> - 
		<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_milestone_description_div', this)"><?php echo lang('description') ?></a> - 
		<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_milestone_options_div', this)"><?php echo lang('options') ?></a> -
		<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_reminders_div',this)"><?php echo lang('object reminders') ?></a>  - 
		<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_custom_properties_div', this)"><?php echo lang('custom properties') ?></a> -
		<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_subscribers_div',this)"><?php echo lang('object subscribers') ?></a>
		<?php if($object->isNew() || $object->canLinkObject(logged_user(), $project)) { ?> - 
			<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_linked_objects_div',this)"><?php echo lang('linked objects') ?></a>
		<?php } ?>
		<?php foreach ($categories as $category) { ?>
			- <a href="#" class="option" <?php if ($category['visible']) echo 'style="font-weight: bold"'; ?> onclick="og.toggleAndBolden('<?php echo $genid . $category['name'] ?>', this)"><?php echo lang($category['name'])?></a>
		<?php } ?>
	</div>
</div>
<div class="coInputSeparator"></div>
<div class="coInputMainBlock">
	<input id="<?php echo $genid?>updated-on-hidden" type="hidden" name="updatedon" value="<?php echo $milestone->isNew() ? '' : $milestone->getUpdatedOn()->getTimestamp() ?>">
		<?php 
			$show_help_option = user_config_option('show_context_help'); 
			if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_add_milestone_context_help', true, logged_user()->getId()))) {?>
			<div id="milestonePanelContextHelp" class="contextHelpStyle">
				<?php render_context_help($this, 'chelp add milestone','add_milestone'); ?>
			</div>
		<?php }?>
	
	<?php if ($milestone->isNew() && isset($base_milestone) && $base_milestone instanceof ProjectMilestone && $base_milestone->getIsTemplate()) { ?>
		<input type="hidden" name="milestone[from_template_id]" value="<?php echo $base_milestone->getId() ?>" />
	<?php } ?>
	
	<?php if ($all) { ?>
			<div id="<?php echo $genid ?>add_milestone_select_workspace_div" style="display:block"> 
	<?php } else {?>
			<div id="<?php echo $genid ?>add_milestone_select_workspace_div" style="display:none">
	<?php }?>
	<fieldset>
	<?php 
			$show_help_option = user_config_option('show_context_help'); 
			if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_add_milestone_workspace_context_help', true, logged_user()->getId()))) {?>
			<div id="milestonePanelContextHelp" class="contextHelpStyle">
				<?php render_context_help($this, 'chelp add milestone workspace','add_milestone_workspace'); ?>
			</div>
		<?php }?>
	<legend><?php echo lang('workspace') ?></legend>
		<?php if (!$milestone->isNew()) {?>
			<div class="desc" style="margin-bottom:5px"><?php echo lang('add milestone change workspace warning')?></div>
		<?php } ?>
		<?php echo select_project2('ws_ids', $milestone->getProjectId(), $genid) ?>
	</fieldset>
	</div>

	<div id="<?php echo $genid ?>add_milestone_tags_div" style="display:none">
	<fieldset>
	<?php 
			$show_help_option = user_config_option('show_context_help'); 
			if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_add_milestone_tags_context_help', true, logged_user()->getId()))) {?>
			<div id="milestonePanelContextHelp" class="contextHelpStyle">
				<?php render_context_help($this, 'chelp add milestone tags', 'add_milestone_tags'); ?>
			</div>
		<?php }?>
	<legend><?php echo lang('tags') ?></legend>
		<?php echo autocomplete_tags_field("milestone[tags]", array_var($milestone_data, 'tags'), null, 10); ?>
		
	</fieldset>
	</div>
	
	<div id="<?php echo $genid ?>add_milestone_description_div" style="display:none">
	<fieldset>
		<?php 
			$show_help_option = user_config_option('show_context_help'); 
						if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_add_milestone_description_context_help', true, logged_user()->getId()))) {?>
			<div id="milestonePanelContextHelp" class="contextHelpStyle">
				<?php render_context_help($this, 'chelp add milestone description','add_milestone_description'); ?>
			</div>
		<?php }?>
	<legend><?php echo lang('description') ?></legend>
		<?php echo textarea_field('milestone[description]', array_var($milestone_data, 'description'), array('class' => 'long', 'id' => $genid . 'milestoneFormDesc', 'tabindex' => '20')) ?>
	</fieldset>
	</div>
  
	<div id="<?php echo $genid ?>add_milestone_options_div" style="display:none">
	<fieldset>
	<legend><?php echo lang('options') ?></legend>
	<?php if(logged_user()->isMemberOfOwnerCompany()) { ?>
	<!-- 
		<div class="objectOptions">
		<div class="optionLabel"><label><?php echo lang('private milestone') ?>: <span class="desc">(<?php echo lang('private milestone desc') ?>)</span></label></div>
		<div class="optionControl"><?php echo yes_no_widget('milestone[is_private]', $genid . 'milestoneFormIsPrivate', array_var($milestone_data, 'is_private'), lang('yes'), lang('no'), 30) ?></div>
		</div>
	 -->
	<?php } // if ?>
		<div class="objectOption">
		<div class="optionLabel"><?php echo label_tag(lang('assign to'), $genid . 'milestoneFormAssignedTo') ?></div>
		<div class="optionControl"><?php echo assign_to_select_box('milestone[assigned_to]', active_or_personal_project(), array_var($milestone_data, 'assigned_to'), array('id' => $genid . 'milestoneFormAssignedTo', 'tabindex' => '40')) ?></div>
		<div class="optionControl"><?php echo checkbox_field('milestone[send_notification]', array_var($milestone_data, 'send_notification', true), array('id' => $genid . 'milestoneFormSendNotification', 'tabindex' => '45')) ?> 
		<label for="<?php echo $genid ?>milestoneFormSendNotification" class="checkbox"><?php echo lang('send milestone assigned to notification') ?></label></div>
		</div>
		<div class="objectOption">
		<div class="optionLabel"><?php echo label_tag(lang('urgent milestone'), $genid . 'milestoneFormIsUrgent') ?></div>
		<div class="optionControl"><?php echo checkbox_field('milestone[is_urgent]', array_var($milestone_data, 'is_urgent', false), array('id' => $genid . 'milestoneFormIsUrgent', 'tabindex' => '45')) ?> </div>
		</div>
	</fieldset>
	</div>

	<div id="<?php echo $genid ?>add_reminders_div" style="display:none">
		<fieldset>
		<?php 
			$show_help_option = user_config_option('show_context_help'); 
						if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_add_milestone_reminders_context_help', true, logged_user()->getId()))) {?>
			<div id="milestonePanelContextHelp" class="contextHelpStyle">
				<?php render_context_help($this, 'chelp add milestone reminders','add_milestone_reminders'); ?>
			</div>
		<?php }?>
		<legend><?php echo lang('object reminders') ?></legend>
		<label><?php echo lang("due date")?>:</label>
		<div id="<?php echo $genid ?>add_reminders_content">
			<?php echo render_add_reminders($object, 'due_date', array(
				'type' => 'reminder_email',
				'duration' => 1,
				'duration_type' => 1440
			)); ?>
		</div>
		</fieldset>
	</div>
	
	<div id='<?php echo $genid ?>add_custom_properties_div' style="display:none">
	<fieldset>
	<?php 
			$show_help_option = user_config_option('show_context_help'); 
			if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_add_milestone_custom_properties_context_help', true, logged_user()->getId()))) {?>
			<div id="milestonePanelContextHelp" class="contextHelpStyle">
				<?php render_context_help($this, 'chelp add milestone custom properties','add_milestone_custom_properties'); ?>
			</div>
		<?php }?>
	<legend><?php echo lang('custom properties') ?></legend>
		<?php echo render_object_custom_properties($object, 'ProjectMilestones', false) ?><br/><br/>
		<?php echo render_add_custom_properties($object); ?>
	</fieldset>
	</div>
	
	<div id="<?php echo $genid ?>add_subscribers_div" style="display:none">
		<fieldset>
		<?php 
			$show_help_option = user_config_option('show_context_help'); 
			if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_add_milestone_subscribers_context_help', true, logged_user()->getId()))) {?>
			<div id="milestonePanelContextHelp" class="contextHelpStyle">
				<?php render_context_help($this, 'chelp add milestone subscribers','add_milestone_subscribers'); ?>
			</div>
		<?php }?>
		<legend><?php echo lang('object subscribers') ?></legend>
		<div id="<?php echo $genid ?>add_subscribers_content">
			<?php echo render_add_subscribers($object, $genid); ?>
		</div>
		</fieldset>
	</div>
	
	<script>
	var wsTree = Ext.get('<?php echo $genid ?>wsSel');
	wsTree.previousValue = <?php echo $milestone->getProjectId() ?>;
	wsTree.on("click", function(ws) {
		var uids = App.modules.addMessageForm.getCheckedUsers('<?php echo $genid ?>');
		var wsid = Ext.get('<?php echo $genid ?>wsSelValue').getValue();
		if (wsid != this.previousValue) {
			this.previousValue = wsid;
			Ext.get('<?php echo $genid ?>add_subscribers_content').load({
				url: og.getUrl('object', 'render_add_subscribers', {
					workspaces: wsid,
					users: uids,
					genid: '<?php echo $genid ?>',
					object_type: '<?php echo get_class($object->manager()) ?>'
				}),
				scripts: true
			});
		}
	}, wsTree);
	</script>

	<?php if($object->isNew() || $object->canLinkObject(logged_user(), $project)) { ?>
	<div style="display:none" id="<?php echo $genid ?>add_linked_objects_div">
	<fieldset>
		<?php 
			$show_help_option = user_config_option('show_context_help'); 
			if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_add_milestone_linked_object_context_help', true, logged_user()->getId()))) {?>
			<div id="milestonePanelContextHelp" class="contextHelpStyle">
				<?php render_context_help($this, 'chelp add milestone linked object','add_milestone_linked_object'); ?>
			</div>
		<?php }?>
		<legend><?php echo lang('linked objects') ?></legend>
		<?php echo render_object_link_form($object) ?>
	</fieldset>	
	</div>
	<?php } // if ?>
	
	<div>
	<?php echo label_tag(lang('due date'), null, true) ?>
	<?php echo pick_date_widget2('milestone[due_date_value]', array_var($milestone_data, 'due_date'),$genid, 90) ?>
	</div>

	<?php echo input_field("milestone[is_template]", array_var($milestone_data, 'is_template', false), array("type" => "hidden")); ?>

	<?php foreach ($categories as $category) { ?>
	<div <?php if (!$category['visible']) echo 'style="display:none"' ?> id="<?php echo $genid . $category['name'] ?>">
	<fieldset>
		<legend><?php echo lang($category['name'])?><?php if ($category['required']) echo ' <span class="label_required">*</span>'; ?></legend>
		<?php echo $category['content'] ?>
	</fieldset>
	</div>
	<?php } ?>
	
	<div>
		<?php echo render_object_custom_properties($object, 'ProjectMilestones', true) ?>
	</div><br/>

	<?php echo submit_button($milestone->isNew() ? (array_var($milestone_data, 'is_template', false) ? lang('save template') : lang('add milestone')) : lang('save changes'), 's', array('tabindex' => '20000')) ?>
</div>
</div>
</form>

<script>
	Ext.get('<?php echo $genid ?>milestoneFormName').focus();
</script>