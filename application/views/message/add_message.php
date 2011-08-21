<?php
	require_javascript('og/modules/addMessageForm.js'); 
	$project = active_or_personal_project();
	$genid = gen_id();
	$object = $message;
	$all = true;
	if (active_project()!= null)
		$all = false;
?>
<form id="<?php echo $genid ?>submit-edit-form" style='height:100%;background-color:white' action="<?php echo $message->isNew() ? get_url('message', 'add') : $message->getEditUrl() ?>" method="post" enctype="multipart/form-data">
<div class="message">
<div class="coInputHeader">
<div class="coInputHeaderUpperRow">
	<div class="coInputTitle">
		<table style="width:535px"><tr><td>
			<?php echo $message->isNew() ? lang('new message') : lang('edit message') ?>
		</td><td style="text-align:right">
			<?php echo submit_button($message->isNew() ? lang('add message') : lang('save changes'),'s',array('style'=>'margin-top:0px;margin-left:10px', 'tabindex' => '5')) ?>
		</td></tr></table>
	</div>
	
	</div>
	<div>
	<?php echo label_tag(lang('title'), $genid . 'messageFormTitle', true) ?>
	<?php echo text_field('message[title]', array_var($message_data, 'title'), 
		array('id' => $genid . 'messageFormTitle', 'class' => 'title', 'tabindex' => '1')) ?>
	</div>
	
	<?php $categories = array(); Hook::fire('object_edit_categories', $object, $categories); ?>
	
	<div style="padding-top:5px">
		<?php if ($all) { ?>
			<a href="#" class="option" style="font-weight:bold" onclick="og.toggleAndBolden('<?php echo $genid ?>add_message_select_workspace_div',this)"><?php echo lang('workspace') ?></a> - 
		<?php } else {?>
			<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_message_select_workspace_div',this)"><?php echo lang('workspace') ?></a> -
		<?php }?> 
		<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_message_add_tags_div', this)"><?php echo lang('tags') ?></a> - 
		<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_message_options_div',this)"><?php echo lang('options') ?></a> -
		<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_custom_properties_div',this)"><?php echo lang('custom properties') ?></a> - 
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
	
	<input id="<?php echo $genid?>merge-changes-hidden" type="hidden" name="merge-changes" value="" >
	<input id="<?php echo $genid?>genid"                type="hidden" name="genid"         value="<?php echo $genid ?>" >
	<input id="<?php echo $genid?>updated-on-hidden"    type="hidden" name="updatedon"     value="<?php echo !$message->isNew() ? $message->getUpdatedOn()->getTimestamp() : '' ?>">
	
		<?php 
			$show_help_option = user_config_option('show_context_help'); 
			if ($show_help_option == 'always' || ($show_help_option == 'until_close')&& user_config_option('show_add_note_context_help', true, logged_user()->getId())) {?>
			<div id="addNotesPanelContextHelp" class="contextHelpStyle">
				<?php render_context_help($this, 'chelp add note','add_note'); ?>
			</div>
		<?php }?>

	<?php if ($all) { ?>
			<div id="<?php echo $genid ?>add_message_select_workspace_div" style="display:block"> 
	<?php } else {?>
			<div id="<?php echo $genid ?>add_message_select_workspace_div" style="display:none">
	<?php }?>
	<fieldset>
		<?php 
			$show_help_option = user_config_option('show_context_help'); 
						if ($show_help_option == 'always' || ($show_help_option == 'until_close')&& user_config_option('show_add_note_workspace_context_help', true, logged_user()->getId())) {?>
			<div id="addContactPanelContextHelp" class="contextHelpStyle">
				<?php render_context_help($this, 'chelp add note workspace','add_note_workspace'); ?>
			</div>
		<?php }?>
	<legend><?php echo lang('workspace')?></legend>
		<?php if ($message->isNew()) {
			echo select_workspaces('ws_ids', null, array($project), $genid.'ws_ids');
		} else {
			echo select_workspaces('ws_ids', null, $message->getWorkspaces(), $genid.'ws_ids');
		} ?>
	</fieldset>
	</div>
	
	<div id="<?php echo $genid ?>add_message_add_tags_div" style="display:none">
	<fieldset>
	<?php 
			$show_help_option = user_config_option('show_context_help'); 
			if ($show_help_option == 'always' || ($show_help_option == 'until_close')&& user_config_option('show_add_note_tags_context_help', true, logged_user()->getId())) {?>
			<div id="addNotesPanelContextHelp" class="contextHelpStyle">
				<?php render_context_help($this, 'chelp add note tags','add_note_tags'); ?>
			</div>
		<?php }?>
	<legend><?php echo lang('tags')?></legend>
		<?php echo autocomplete_tags_field("message[tags]", array_var($message_data, 'tags'), null, 20); ?>
	</fieldset>
	</div>

	<?php if(logged_user()->isMemberOfOwnerCompany()) { ?>
	<div id="<?php echo $genid ?>add_message_options_div" style="display:none">
	<fieldset>
	<legend><?php echo lang('options') ?></legend>
	    <?php /* <div class="objectOption">
			<div class="optionLabel"><label><?php echo lang('private message') ?>:</label></div>
			<div class="optionControl"><?php echo yes_no_widget('message[is_private]', $genid.'messageFormIsPrivate', array_var($message_data, 'is_private'), lang('yes'), lang('no')) ?></div>
			<div class="optionDesc"><?php echo lang('private message desc') ?></div>
		</div>
		
		<div class="objectOption">
			<div class="optionLabel"><label><?php echo lang('important message')?>:</label></div>
			<div class="optionControl"><?php echo yes_no_widget('message[is_important]', $genid.'messageFormIsImportant', array_var($message_data, 'is_important'), lang('yes'), lang('no')) ?></div>
			<div class="optionDesc"><?php echo lang('important message desc') ?></div>
		</div> */ ?>

		<div class="objectOption">
			<div class="optionLabel"><label><?php echo lang('enable comments') ?>:</label></div>
			<div class="optionControl"><?php echo yes_no_widget('message[comments_enabled]', $genid.'fileFormEnableComments', array_var($message_data, 'comments_enabled', true), lang('yes'), lang('no'), 35) ?></div>
			<div class="optionDesc"><?php echo lang('enable comments desc') ?></div>
		</div>

		<div class="objectOption">
			<div class="optionLabel"><label><?php echo lang('enable anonymous comments') ?>:</label></div>
			<div class="optionControl"><?php echo yes_no_widget('message[anonymous_comments_enabled]', $genid.'fileFormEnableAnonymousComments', array_var($message_data, 'anonymous_comments_enabled', false), lang('yes'), lang('no'), 40) ?></div>
			<div class="optionDesc"><?php echo lang('enable anonymous comments desc') ?></div>
		</div>
	</fieldset>
	</div>
	<?php } // if ?>

	<div id='<?php echo $genid ?>add_custom_properties_div' style="display:none">
	<fieldset>
		<?php 
			$show_help_option = user_config_option('show_context_help'); 
						if ($show_help_option == 'always' || ($show_help_option == 'until_close')&& user_config_option('show_add_note_custom_properties_context_help', true, logged_user()->getId())) {?>
			<div id="addNotesPanelContextHelp" class="contextHelpStyle">
				<?php render_context_help($this, 'chelp add note custom properties','add_note_custom_properties'); ?>
			</div>
		<?php }?>
		<legend><?php echo lang('custom properties') ?></legend>
		<?php echo render_object_custom_properties($message, 'ProjectMessages', false) ?>
		<?php echo render_add_custom_properties($object); ?>
	</fieldset>
	</div>

	<div id="<?php echo $genid ?>add_subscribers_div" style="display:none">
		<fieldset>
			<?php 
				$show_help_option = user_config_option('show_context_help'); 
							if ($show_help_option == 'always' || ($show_help_option == 'until_close')&& user_config_option('show_add_note_subscribers_context_help', true, logged_user()->getId())) {?>
				<div id="addNotesPanelContextHelp" class="contextHelpStyle">
			<?php render_context_help($this, 'chelp add note subscribers','add_note_subscribers'); ?>
			</div>
		<?php }?>
		<legend><?php echo lang('object subscribers') ?></legend>
		<div id="<?php echo $genid ?>add_subscribers_content">
			<?php echo render_add_subscribers($object, $genid); ?>
		</div>
		</fieldset>
	</div>
	
	<script>
	var wsch = Ext.getCmp('<?php echo $genid ?>ws_ids');
	wsch.on("wschecked", function(arguments) {
		if (!this.getValue().trim()) return;
		var uids = App.modules.addMessageForm.getCheckedUsers('<?php echo $genid ?>');
		Ext.get('<?php echo $genid ?>add_subscribers_content').load({
			url: og.getUrl('object', 'render_add_subscribers', {
				workspaces: this.getValue(),
				users: uids,
				genid: '<?php echo $genid ?>',
				object_type: '<?php echo get_class($object->manager()) ?>'
			}),
			scripts: true
		});
	}, wsch);
	</script>

	<?php if($object->isNew() || $object->canLinkObject(logged_user(), $project)) { ?>
	<div style="display:none" id="<?php echo $genid ?>add_linked_objects_div">
	<fieldset>
		<?php 
			$show_help_option = user_config_option('show_context_help'); 
			if ($show_help_option == 'always' || ($show_help_option == 'until_close')&& user_config_option('show_add_note_linked_object_context_help', true, logged_user()->getId())) {?>
			<div id="addNotesPanelContextHelp" class="contextHelpStyle">
				<?php render_context_help($this, 'chelp add note linked objects','add_note_linked_object'); ?>
			</div>
		<?php }?>
		<legend><?php echo lang('linked objects') ?></legend>
		<?php echo render_object_link_form($object) ?>
	</fieldset>	
	</div>
	<?php } // if ?>
	
	
	<div>
	<?php echo label_tag(lang('text'), 'messageFormText', false) ?>
	<?php echo editor_widget('message[text]', array_var($message_data, 'text'), 
		array('id' => $genid . 'messageFormText', 'tabindex' => '50')) ?>
	</div>
	
	<?php foreach ($categories as $category) { ?>
	<div <?php if (!$category['visible']) echo 'style="display:none"' ?> id="<?php echo $genid . $category['name'] ?>">
	<fieldset>
		<legend><?php echo lang($category['name'])?><?php if ($category['required']) echo ' <span class="label_required">*</span>'; ?></legend>
		<?php echo $category['content'] ?>
	</fieldset>
	</div>
	<?php } ?>

	<div>
	<?php if(!$message->isNew() && trim($message->getAdditionalText())) { ?>
		<label for="<?php echo $genid ?>messageFormAdditionalText"><?php echo lang('additional text') ?>:</label>
		<?php echo editor_widget('message[additional_text]', array_var($message_data, 'additional_text'), array('id' => $genid . 'messageFormAdditionalText', 'tabindex' => '25')) ?>
	<?php } ?>
	</div>
	
	<div>
		<?php echo render_object_custom_properties($message, 'ProjectMessages', true) ?>
	</div><br/>
	
	<?php echo submit_button($message->isNew() ? lang('add message') : lang('save changes'),'s',
		array('style'=>'margin-top:0px', 'tabindex' => '20000')) ?>
</div>
</div>
</form>

<script>
	Ext.get('<?php echo $genid ?>messageFormTitle').focus();	
</script>
