<?php
require_javascript('og/modules/addMessageForm.js');
set_page_title($webpage->isNew() ? lang('add webpage') : lang('edit webpage'));
if ($webpage->isNew()) {
	$project = active_or_personal_project();
} else {
	$project = $webpage->getProject();
}
$genid = gen_id();
$object = $webpage;
$all = true;
if (active_project()!= null)
	$all = false;
?>

<form id="<?php echo $genid ?>submit-edit-form" style='height: 100%; background-color: white' class="internalForm"
	action="<?php echo $webpage->isNew() ? get_url('webpage', 'add') : $webpage->getEditUrl() ?>"
	method="post">

<div class="webpage">
<div class="coInputHeader">
<div class="coInputHeaderUpperRow">
<div class="coInputTitle">
<table style="width: 535px">
	<tr>
		<td><?php echo $webpage->isNew() ? lang('new webpage') : lang('edit webpage') ?>
		</td>
		<td style="text-align: right"><?php echo submit_button($webpage->isNew() ? lang('add webpage') : lang('save changes'),'s',array('style'=>'margin-top:0px;margin-left:10px', 'tabindex' => '20')) ?></td>
	</tr>
</table>
</div>

</div>
<div><?php echo label_tag(lang('title'), 'webpageFormTitle', true) ?> <?php echo text_field('webpage[title]', array_var($webpage_data, 'title'), array('class' => 'title', 'tabindex' => '1', 'id' => 'webpageFormTitle')) ?>
</div>

<?php $categories = array(); Hook::fire('object_edit_categories', $object, $categories); ?>

<div style="padding-top: 5px">
<?php if ($all) { ?>
	<a href="#" class="option" style="font-weight:bold" onclick="og.toggleAndBolden('<?php echo $genid ?>add_webpage_select_workspace_div',this)"><?php echo lang('workspace') ?></a>
<?php } else {?>
	<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_webpage_select_workspace_div',this)"><?php echo lang('workspace') ?></a>
<?php }?>
- <a href="#" class="option" tabindex=0 onclick="og.toggleAndBolden('<?php echo $genid?>add_webpage_tags_div', this)"><?php echo lang('tags') ?></a>
- <a href="#" class="option" tabindex=0 onclick="og.toggleAndBolden('<?php echo $genid?>add_webpage_description_div', this)"><?php echo lang('description') ?></a>
- <a href="#" class="option" tabindex=0 onclick="og.toggleAndBolden('<?php echo $genid?>add_custom_properties_div', this)"><?php echo lang('custom properties') ?></a>
- <a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_subscribers_div',this)"><?php echo lang('object subscribers') ?></a>
<?php if($object->isNew() || $object->canLinkObject(logged_user(), $project)) { ?>
	- <a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_linked_objects_div',this)"><?php echo lang('linked objects') ?></a>
<?php } ?>
<?php foreach ($categories as $category) { ?>
	- <a href="#" class="option" <?php if ($category['visible']) echo 'style="font-weight: bold"'; ?> onclick="og.toggleAndBolden('<?php echo $genid . $category['name'] ?>', this)"><?php echo lang($category['name'])?></a>
<?php } ?></div>
</div>
<div class="coInputSeparator"></div>
<div class="coInputMainBlock">

	<input id="<?php echo $genid?>updated-on-hidden" type="hidden" name="updatedon" value="<?php echo $webpage->isNew()? '' : $webpage->getUpdatedOn()->getTimestamp() ?>">
	<input id="<?php echo $genid?>merge-changes-hidden" type="hidden" name="merge-changes" value="" >
	<input id="<?php echo $genid?>genid" type="hidden" name="genid" value="<?php echo $genid ?>" >
	<?php 
$show_help_option = user_config_option('show_context_help');
if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_add_webpage_context_help', true, logged_user()->getId()))) {?>
<div id="webpagePanelContextHelp"
	class="contextHelpStyle"><?php render_context_help($this, 'chelp add webpage','add_webpage'); ?>
</div>
<?php }?>
<?php if ($all) { ?>
			<div id="<?php echo $genid ?>add_webpage_select_workspace_div" style="display:block"> 
<?php } else {?>
			<div id="<?php echo $genid ?>add_webpage_select_workspace_div" style="display:none">
<?php }?>
<fieldset><?php 
$show_help_option = user_config_option('show_context_help');
if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_add_webpage_workspace_context_help', true, logged_user()->getId()))) {?>
<div id="webpagePanelContextHelp"
	class="contextHelpStyle"><?php render_context_help($this, 'chelp add webpage workspace','add_webpage_workspace'); ?>
</div>
<?php }?> <legend><?php echo lang('workspace') ?></legend>
	<?php if ($object->isNew()) {
			echo select_workspaces('ws_ids', null, array($project), $genid.'ws_ids');
		} else {
			echo select_workspaces('ws_ids', null, $object->getWorkspaces(), $genid.'ws_ids');
		} ?>
</fieldset>
</div>

<div id="<?php echo $genid?>add_webpage_tags_div" style="display: none">
<fieldset><?php 
$show_help_option = user_config_option('show_context_help');
if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_add_webpage_tags_context_help', true, logged_user()->getId()))) {?>
<div id="webpagePanelContextHelp"
	class="contextHelpStyle"><?php render_context_help($this, 'chelp add webpage tags','add_webpage_tags'); ?>
</div>
<?php }?> <legend> <?php echo lang('tags') ?></legend> <?php echo autocomplete_tags_field("webpage[tags]", array_var($webpage_data, 'tags'), null, 30); ?>
</fieldset>
</div>

<div id="<?php echo $genid?>add_webpage_description_div" style="display: none">
<fieldset><?php 
$show_help_option = user_config_option('show_context_help');
if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_add_webpage_description_context_help', true, logged_user()->getId()))) {?>
<div id="webpagePanelContextHelp"
	class="contextHelpStyle"><?php render_context_help($this, 'chelp add webpage description','add_webpage_description'); ?>
</div>
<?php }?> <legend> <?php echo label_tag(lang('description'), 'webpageFormDesc') ?>
</legend> <?php echo textarea_field('webpage[description]', array_var($webpage_data, 'description'), array('class' => 'long', 'id' => 'webpageFormDesc', 'tabindex' => '40')) ?>
</fieldset>
</div>

<div id='<?php echo $genid?>add_custom_properties_div' style="display: none">
<fieldset><?php 
$show_help_option = user_config_option('show_context_help');
if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_add_webpage_custom_properties_context_help', true, logged_user()->getId()))) {?>
<div id="webpagePanelContextHelp"
	class="contextHelpStyle"><?php render_context_help($this, 'chelp add webpage custom properties','add_webpage_custom_properties'); ?>
</div>
<?php }?> <legend><?php echo lang('custom properties') ?></legend> <?php echo render_object_custom_properties($object, 'ProjectWebPages', false) ?><br />
<br />
<?php echo render_add_custom_properties($object); ?></fieldset>
</div>


<div id="<?php echo $genid ?>add_subscribers_div" style="display: none">
<fieldset><?php 
$show_help_option = user_config_option('show_context_help');
if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_add_webpage_subscribers_context_help', true, logged_user()->getId()))) {?>
<div id="webpagePanelContextHelp"
	class="contextHelpStyle"><?php render_context_help($this, 'chelp add webpage subscribers','add_webpage_subscribers'); ?>
</div>
<?php }?> <legend><?php echo lang('object subscribers') ?></legend>
<div id="<?php echo $genid ?>add_subscribers_content"><?php echo render_add_subscribers($object, $genid); ?>
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
<div style="display: none"
	id="<?php echo $genid ?>add_linked_objects_div">
<fieldset><?php 
$show_help_option = user_config_option('show_context_help');
if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_add_webpage_linked_objects_context_help', true, logged_user()->getId()))) {?>
<div id="webpagePanelContextHelp"
	class="contextHelpStyle"><?php render_context_help($this, 'chelp add webpage linked objects','add_webpage_linked_objects'); ?>
</div>
<?php }?> <legend><?php echo lang('linked objects') ?></legend> <?php echo render_object_link_form($object) ?>
</fieldset>
</div>
<?php } // if ?>

<div><?php echo label_tag(lang('url'), 'webpageFormURL', true) ?> <?php echo text_field('webpage[url]', array_var($webpage_data, 'url'), array('class' => 'title', 'tabindex' => '50', 'id' => 'webpageFormURL')) ?>
</div>

<?php foreach ($categories as $category) { ?>
<div <?php if (!$category['visible']) echo 'style="display:none"' ?>
	id="<?php echo $genid . $category['name'] ?>">
<fieldset><legend><?php echo lang($category['name'])?><?php if ($category['required']) echo ' <span class="label_required">*</span>'; ?></legend>
<?php echo $category['content'] ?></fieldset>
</div>
<?php } ?>

<div><?php echo render_object_custom_properties($object, 'ProjectWebPages', true) ?>
</div>
<br />

<?php echo submit_button($webpage->isNew() ? lang('add webpage') : lang('save changes'), 's',
array('tabindex' => '20000')) ?></div>
</div>
</form>

<script>
	Ext.get('webpageFormTitle').focus();
</script>
