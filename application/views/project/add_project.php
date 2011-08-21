<?php
	require_javascript("og/modules/addProjectForm.js");
	require_javascript('og/modules/updatePermissionsForm.js');
  	if(!$project->isNew() && $project->canEdit(logged_user())) {
  		if ($project->getCompletedById() == 0)
			add_page_action(lang('archive'), "javascript:if(confirm(lang('confirm archive object'))) og.openLink('" . $project->getCompleteUrl() ."');", 'ico-archive-obj');
		else
			add_page_action(lang('unarchive'), "javascript:if(confirm(lang('confirm unarchive object'))) og.openLink('" . $project->getOpenUrl() ."');", 'ico-unarchive-obj');
  	} // if
	if(!$project->isNew() && $project->canDelete(logged_user())) {
  		add_page_action(lang('delete'),  $project->getDeleteUrl() , 'ico-delete');
  	} // if
  	
  	$genid = gen_id();
  	$object = $project;
?>
<form style="height:100%;background-color:white" class="internalForm" action="<?php echo $project->isNew() ? get_url('project', 'add') : $project->getEditUrl()?>" method="post" onsubmit="og.ogPermPrepareSendData('<?php echo $genid ?>');return true;">

    <script>
		var genid = '<?php echo $genid ?>';
    </script>
    
<div class="adminAddProject">
  <div class="adminHeader">
  	<div class="adminHeaderUpperRow">
  		<div class="adminTitle"><table style="width:535px"><tr><td>
  			<?php echo $project->isNew() ? lang('new workspace') : lang('edit workspace') ?>
  		</td><td>
  			<?php echo submit_button($project->isNew() ? lang('add workspace') : lang('save changes'), 's', array('id'=>$genid.'submit_btn1', 'style'=>'margin-top:0px;margin-left:10px', 'tabindex' => '5')) ?>
  			<div id="<?php echo $genid ?>load1" class="loading-indicator" style="display: none;"><?php echo lang('loading') ?></div>
  		</td></tr></table></div>
  	</div>
  	<div>
    <?php echo label_tag(lang('name'), 'projectFormName', true) ?>
    <?php echo text_field('project[name]', array_var($project_data, 'name'), 
    	array('class' => 'title', 'id' => 'projectFormName', 'tabindex' => '1')) ?>
    	<input type="hidden" name="wizard" value="<?php if(isset($_REQUEST['wizard'])) echo $_REQUEST['wizard'] ?>" />
    </div>
  
  	<?php $categories = array(); Hook::fire('object_edit_categories', $object, $categories); ?>
  	<?php $cps = CustomProperties::countHiddenCustomPropertiesByObjectType('Projects'); ?>
  
  	<div style="padding-top:5px">
		<a href="#" class="option" onclick="og.ToggleTrap('trap1', 'fs1');og.toggleAndBolden('<?php echo $genid ?>workspace_description',this)"><?php echo lang('description') ?></a>
		<?php if ($project->canChangePermissions(logged_user())) { ?>
			 - <a href="#" id="<?php echo $genid ?>perm_togg" class="option" onclick="og.ToggleTrap('trap1', 'fs1');og.toggleAndBolden('<?php echo $genid ?>workspace_permissions',this)"><?php echo lang('edit permissions') ?></a>
		<?php } ?>
		<?php  if ($billing_amounts && count($billing_amounts) > 0) {  ?>
			 - <a href="#" class="option" onclick="og.ToggleTrap('trap1', 'fs1');og.toggleAndBolden('<?php echo $genid ?>workspace_billing',this)"><?php echo lang('billing') ?></a>
		<?php } ?>
		<?php  if (can_manage_contacts(logged_user())) {  ?>
			 - <a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>project_contacts',this)"><?php echo lang('workspace contacts') ?></a>
		<?php } ?>
		<?php foreach ($categories as $category) { ?>
			- <a href="#" class="option" <?php if ($category['visible']) echo 'style="font-weight: bold"'; ?> onclick="og.toggleAndBolden('<?php echo $genid . $category['name'] ?>', this)"><?php echo lang($category['name'])?></a>
		<?php } ?>
		<?php if ($cps > 0) { ?>
			- <a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_custom_properties_div',this)"><?php echo lang('custom properties') ?></a>
		<?php } ?> 
	</div>
  
  </div>
  <div class="adminSeparator"></div>
  <div class="adminMainBlock">

		<?php 
			$show_help_option = user_config_option('show_context_help'); 
			if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_add_workspace_context_help', true, logged_user()->getId()))) {?>
			<div id="projectPanelContextHelp" class="contextHelpStyle">
				<?php render_context_help($this, 'chelp add workspace','add_workspace'); ?>
			</div>
		<?php }?>
		
	<div id="<?php echo $genid ?>workspace_description" style="display:none">
	<fieldset>
	<?php 
			$show_help_option = user_config_option('show_context_help'); 
			if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_add_workspace_context_help', true, logged_user()->getId()))) {?>
			<div id="tasksPanelContextHelp" class="contextHelpStyle">
				<?php render_context_help($this, 'chelp add workspace description','add_workspace'); ?>
			</div>
		<?php }?>	
	<legend><?php echo lang('description') ?></legend>
		<?php echo textarea_field('project[description]', array_var($project_data, 'description'), array('id' => 'projectFormDescription', 'tabindex' => '10')) ?>
		<?php echo label_tag(lang('show project desciption in overview')) ?>
		<?php echo yes_no_widget('project[show_description_in_overview]', 'projectFormShowDescriptionInOverview', array_var($project_data, 'show_description_in_overview', true), lang('yes'), lang('no'), 20) ?>
	</fieldset>
	</div>

	<!-- permissions -->
	<?php if ($project->canChangePermissions(logged_user())) { ?>
		<?php $field_tab = 30; ?>
		<div id="<?php echo $genid ?>workspace_permissions" style="display:none">
		<fieldset>
		<legend><?php echo lang('edit permissions') ?></legend>
		<?php tpl_assign('genid', $genid);
		$this->includeTemplate(get_template_path('workspace_permissions_control', 'project')); ?>
		</fieldset>
		</div>
	<?php } // if ?>
	<!-- /permissions -->
	
	
	<?php 
	$current = $project->isNew()? 0: $project->getId();
	if ($billing_amounts && count($billing_amounts) > 0) { ?>
	<div id="<?php echo $genid ?>workspace_billing" style="display:none">
	<fieldset>	
	<legend><?php echo lang('billing') ?></legend>
	<table>
	<tr>
		<th><?php echo lang('category') ?></th>
		<th><?php echo lang('hourly rates') ?></th>
		<th><?php echo lang('origin') ?></th>
		<th><?php echo lang('default hourly rates') ?></th>
	</tr>
	<?php 
	$isAlt = true;
	foreach ($billing_amounts as $billing_row) { 
	$isAlt = !$isAlt; ?>
	<tr class="<?php echo $isAlt? 'altRow' : '' ?>"><td style="padding:5px;padding-right:15px;font-weight:bold">
		<?php echo $billing_row['category']->getName() ?></td>
	<td style="padding:5px;padding-right:15px;">
<?php 
	$is_project_billing = $billing_row['origin'] == $current && !$project->isNew();
	if ($is_project_billing) {
			echo text_field('project[billing][' . $billing_row['category']->getId() . '][value]', $billing_row['value'], array('style'=>'width:100px'));
	} else {?>
		<span id="<?php echo $genid . $billing_row['category']->getId() ?>bv">
		$<?php echo $billing_row['value'] ?>&nbsp;&nbsp;(<?php echo lang('edit') ?>)</a></span>
		<span id="<?php echo $genid . $billing_row['category']->getId() ?>bvedit" style="display:none">
			<?php echo text_field('project[billing][' . $billing_row['category']->getId() . '][value]', $billing_row['value'], 
				array('style'=>'width:100px', 'id' => $genid . $billing_row['category']->getId() . 'text')) ?>
		</span>
		<?php } //if ?>
		<input type='hidden' id='<?php echo $genid . $billing_row['category']->getId() ?>edclick' name="project[billing][<?php echo $billing_row['category']->getId() ?>][update]" value='<?php echo $is_project_billing? 1:0 ?>'/>
		</td>
	<td style="padding:5px;padding-right:15px;"><?php switch($billing_row['origin']) {
		case 'default': echo lang('default hourly rates'); break;
		case $current: echo $project->isNew()?  lang('defined in a parent workspace'):lang('defined in the current workspace'); break;
		default: echo lang('defined in a parent workspace'); break;
}?></td>
	<td style="padding:5px;">$<?php echo $billing_row['category']->getDefaultValue()?></td></tr>
<?php } //foreach ?>
	</table>
	</fieldset>
	</div>
	<?php } ?>
		
	<?php if (Project::canAdd(logged_user()) && isset ($projects) && count($projects) > 0) { ?>
	<fieldset>
	<legend><?php echo lang('parent workspace') ?></legend>
		<?php // echo select_project('project[parent_id]', $projects, $project->isNew()?active_project()?active_project()->getId():0:$project->getParentId(), null, true) ?>
		<?php 
			if (!$project->isNew() && $project->getParentWorkspace() instanceof Project && !logged_user()->isProjectUser($project->getParentWorkspace())){?>
		<div class="tasksPanelWarning ico-warning32" style="font-size:10px;color:#666;background-repeat:no-repeat;padding-left:40px;max-width:600px;border:1px solid #E3AD00;background-color:#FFF690;background-position:4px 4px;">
			<div style="font-weight:bold;width:99%;text-align:center;padding:4px;color:#AF8300;"><?php echo lang('cannot change parent workspace') ?><br/><?php echo lang('cannot change parent workspace description') ?></div>
		</div>
			<?php } else echo select_project2('project[parent_id]', ($project->isNew())? (active_project()?active_project()->getId():0):$project->getParentId(), $genid, true) ?>
	</fieldset>
	<div id="trap1"><fieldset id="fs1" style="height:0px;border:0px;padding:0px;display:none"><span style="color:#FFFFFF;"></span></fieldset></div>
	<?php } ?>
	
	
	<?php if (can_manage_contacts(logged_user())) { ?>
	<div id="<?php echo $genid ?>project_contacts" style="display:none">
	<fieldset>
	<legend><?php echo lang('workspace contacts') ?></legend>
		<div id="<?php echo $genid?>contacts">
			<table id="<?php echo $genid?>contactsTable" class="contactsTable">
			<?php 
			$has_sme = isset($subject_matter_experts) && is_array($subject_matter_experts) && count($subject_matter_experts) > 0;
			if ($has_sme){?>
				<?php $class = null;
				$c = -1;
				foreach ($subject_matter_experts as $sme) {
					if($c++ % 2 == 0)
						$class = 'altrow';?>
				<tr id="<?php echo $genid . 'contacts' . $sme->getId() ?>" class="<?php echo $class ?>">
					<td id="<?php echo $genid . 'contacts_name_cell' . $sme->getId()  ?>" class="contact_name"><?php echo clean($sme->getDisplayName())?></td>
					<td class="contact_data"><?php echo clean($sme->getJobTitle() . ($sme->getJobTitle() != ''? ', ' : '') . ($sme->getCompany() instanceof Company?$sme->getCompany()->getName() : ''));?></td>
					<td class="contact_role">
						<input type="hidden" name="project[contacts][<?php echo $sme->getId()  ?>][role]" value="<?php echo clean($sme->getRole($project)->getRole()) ?>"/>
						<?php echo clean($sme->getRole($project)->getRole());?></td>
					<td class="actions">
						<input type="hidden" name="project[contacts][<?php echo $sme->getId()  ?>][contact_id]" value="<?php echo $sme->getId() ?>"/>
						<a href="#" class="coViewAction ico-delete" onclick="og.sme.removeFromsme('<?php echo $genid?>', '<?php echo $sme->getId() ?>');return false;" title="<?php echo lang('remove')?>"><?php echo lang('remove')?></a>
					</td>
				</tr>
				<?php } // foreach?>
			<?php } //if?>
			</table>
			<div id="<?php echo $genid?>noContacts" style="<?php echo $has_sme? 'display:none':''?>">
				<?php echo lang('no contacts to display')?>
			</div>
		</div>
		<div id="<?php echo $genid?>addsmeControl" class="asc">
			<div id="<?php echo $genid?>header" class="header">
				<div id="<?php echo $genid?>title" class="title"><?php echo lang('add new contact')?></div>
				<div id="<?php echo $genid?>search" class="search">
					<?php echo lang('search contact')?>:&nbsp;
					<input type='text' style="color:#888" value="<?php echo lang('search')?>..." onfocus="if (value == '<?php echo escape_single_quotes(lang('search'))?>...') {style.color='#333'; value = ''}"  onblur="if (value == '') {style.color='#888';value = '<?php echo escape_single_quotes(lang('search'))?>...'}" onkeypress="return og.sme.searchTO(event,'<?php echo $genid?>', 'contact', 'search');" id="<?php echo $genid?>searchField"/>
				</div>
			</div>
			<div id="<?php echo $genid?>body" class="body"></div>
			<div id="<?php echo $genid?>searching" class="searching loading-indicator" style="display:none;color:#666"><?php echo lang('searching')?></div>
		</div>
	</fieldset>
	</div>
	<?php } ?>
	
	
    <?php echo label_tag(lang('workspace color')) ?>
		<input type="hidden" id="workspace_color" name="project[color]" value="<?php echo $project->isNew()?0:$project->getColor() ?>" />
		<div>
			<script>
			og.wsColorChoose = function(obj, color) {
				var elements = document.getElementsByName("wsColor-<?php echo $genid ?>");
				for (var i = 0; i < elements.length; i++){
					var p = elements[i];
					if (p.className) {
						if (p.className.indexOf('ico-color'+color+' ') >= 0) {
							p.className = 'ico-color' + color + ' ws-color-chooser-selected';
							document.getElementById('workspace_color').value = color;
						} else {
							p.className = p.className.replace(/ws-color-chooser-selected/ig, 'ws-color-chooser');
						}
					}
				}
			}
			</script>
			<table><tr><td><img class="ico-color0 ws-color-chooser" name="wsColor-<?php echo $genid ?>" onclick="og.wsColorChoose(this, 0)" src="<?php echo EMPTY_IMAGE ?>"/></td>
				<td>	
    		<?php for ($i=1; $i <= 12; $i++) {
				$class = "ico-color$i " . ($project->getColor() != $i?"ws-color-chooser":"ws-color-chooser-selected");
				echo "<img src=\"".EMPTY_IMAGE."\" name=\"wsColor-$genid\" class=\"$class\" onclick=\"og.wsColorChoose(this, $i)\" />";
			} ?></td></tr><tr><td></td><td>	
    		<?php for ($i=13; $i <= 24; $i++) {
				$class = "ico-color$i " . ($project->getColor() != $i?"ws-color-chooser":"ws-color-chooser-selected");
				echo "<img src=\"".EMPTY_IMAGE."\" name=\"wsColor-$genid\" class=\"$class\" onclick=\"og.wsColorChoose(this, $i)\" />";
			} ?></td></tr></table>
		</div>
		
	<?php foreach ($categories as $category) { ?>
	<div <?php if (!$category['visible']) echo 'style="display:none"' ?> id="<?php echo $genid . $category['name'] ?>">
	<fieldset>
		<legend><?php echo lang($category['name'])?><?php if ($category['required']) echo ' <span class="label_required">*</span>'; ?></legend>
		<?php echo $category['content'] ?>
	</fieldset>
	</div>
	<?php } ?>
	
	<?php if ($cps > 0) { ?>
	<div id='<?php echo $genid ?>add_custom_properties_div' style="display:none">
		<fieldset>
			<legend><?php echo lang('custom properties') ?></legend>
			<?php echo render_object_custom_properties($object, 'Projects', false) ?>
		</fieldset>
	</div>
	<?php } ?>
	
	<div>
		<?php echo render_object_custom_properties($object, 'Projects', true) ?>
	</div><br/>
	
	<div>
	<?php echo submit_button($project->isNew() ? lang('add workspace') : lang('save changes'), 's', array('id'=>$genid.'submit_btn2', 'tabindex' => '250')) ?>
	</div>
</div>
</div>
</form>

<script>
	og.addWsWsSelectorClicked = function() {
		var wsVal = document.getElementById(genid + 'wsSelValue').value;
		if (wsVal != og.addWsPrevWsValue) {
			if (wsVal != 0 && og.addWsPrevWsValue != -1) {
				var overwrite = confirm(lang('confirm inherit permissions'));
				if (overwrite) {
					document.getElementById(genid + 'load1').style.display = 'block';
					document.getElementById(genid + 'submit_btn1').disabled = true;
					document.getElementById(genid + 'submit_btn2').disabled = true;
					og.openLink(og.getUrl('project', 'get_ws_permissions', {ws_id: wsVal}), {
						callback: function(success, data) {
							document.getElementById(genid + 'load1').style.display = 'none';
							document.getElementById(genid + 'submit_btn1').disabled = false;
							document.getElementById(genid + 'submit_btn2').disabled = false;
							if (success && data.permissions) {
								og.ogLoadPermissionsFromArray('<?php echo $genid ?>', data.permissions<?php if ($project->isNew()) echo ", true"; ?>);
							}
						}
					});
					if (document.getElementById(genid + 'workspace_permissions').style.display == 'none') {
						og.toggleAndBolden(genid + 'workspace_permissions', document.getElementById(genid + 'perm_togg'));
					}
				}
			}
			og.addWsPrevWsValue = wsVal;
		}
	}

	var ws_sel = Ext.get(genid + 'wsSel');
	og.addWsPrevWsValue = -1;
	if (ws_sel) {
		ws_sel.addListener('click', og.addWsWsSelectorClicked);
		og.addWsWsSelectorClicked();
	}
	
	Ext.get('projectFormName').focus();
</script>
