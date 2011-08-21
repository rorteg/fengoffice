<?php
	require_javascript('og/modules/addMessageForm.js');
	require_javascript('og/tasks/main.js');
	require_javascript('og/tasks/addTask.js');
	require_javascript("og/ObjectPicker.js");
	$genid = gen_id();
	$object = $task;
	$all = true;
	if (active_project()!= null)
		$all = false;	
	if ($task->isNew()) {
		$project = Projects::findById(array_var($task_data, 'project_id'));
		if (isset($from_email) && $from_email instanceof MailContent) {
			$email_workspaces = $from_email->getWorkspaces();
			// pick the most specific workspace
			$email_max = 0;
			foreach ($email_workspaces as $email_workspace) {
				if ($email_workspace->getDepth() >= $email_max) {
					$email_max = $email_workspace->getDepth();
					$project = $email_workspace;
				}
			}
		}
	} else {
		$project = $task->getProject();
	}
	$co_type = array_var($task_data, 'object_subtype');
?>
<script>
og.checkSubmitAddTask = function(genid) {
	var dd = Ext.getCmp(genid + 'due_date').getValue();
	var sd = Ext.getCmp(genid + 'start_date').getValue();
	if (sd && dd && dd < sd) {
		alert(lang('warning start date greater than due date'));
		return false;
	}
	return true;
};
</script>
<form id="<?php echo $genid ?>submit-edit-form" style='height:100%;background-color:white' class="internalForm" action="<?php echo $task->isNew() ? get_url('task', 'add_task', array("copyId" => array_var($task_data, 'copyId'))) : $task->getEditListUrl() ?>" method="post" onsubmit="return og.checkSubmitAddTask('<?php echo $genid ?>')">

<div class="task">
<div class="coInputHeader">
	<div class="coInputHeaderUpperRow">
	<div class="coInputTitle"><table style="width:535px">
	<tr><td><?php
		if ($task->isNew()) {
			if (array_var($task_data, 'is_template', false)) {
				echo lang('new task template');
			} else if (isset($base_task) && $base_task instanceof ProjectTask) {
				echo lang('new task from template');
			} else {
				echo lang('new task list');
			}
		} else if ($task->getIsTemplate()) {
			echo lang('edit task template');
		} else {
			echo lang('edit task list');
		}
	?>
	</td><td style="text-align:right"><?php echo submit_button($task->isNew() ? (array_var($task_data, 'is_template', false) ? lang('save template') : lang('add task list')) : lang('save changes'),'s',array('style'=>'margin-top:0px;margin-left:10px', 'tabindex' => '10')) ?></td></tr></table>
	</div>
	
	</div>
	<div>
		<?php echo label_tag(lang('name'), $genid . 'taskListFormName', true) ?>
    	<?php echo text_field('task[title]', array_var($task_data, 'title'), 
    		array('class' => 'title', 'id' => $genid . 'taskListFormName', 'tabindex' => '1')) ?>
    </div>
	
	<?php $categories = array(); Hook::fire('object_edit_categories', $object, $categories); ?>
	
	<div style="padding-top:5px">
	<?php if ($all) { ?>
			<a href="#" class="option" style="font-weight:bold" onclick="og.toggleAndBolden('<?php echo $genid ?>add_task_select_workspace_div',this)"><?php echo lang('workspace') ?></a> - 
		<?php } else {?>
			<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_task_select_workspace_div',this)"><?php echo lang('workspace') ?></a> -
		<?php }?> 
		<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_task_tags_div', this)"><?php echo lang('tags') ?></a> - 
		<a href="#" class="option" style="font-weight:bold" onclick="og.toggleAndBolden('<?php echo $genid ?>add_task_more_div', this)"><?php echo lang('task data') ?></a> -  
		<?php /*<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_task_handins_div', this)"><?php echo lang('handins') ?></a> - */ ?>
		<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>task_repeat_options_div',this)"><?php echo lang('repeating task') ?></a>  -
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
	<input id="<?php echo $genid?>updated-on-hidden" type="hidden" name="updatedon" value="<?php echo $task->isNew() ? '': $task->getUpdatedOn()->getTimestamp() ?>">
	<input id="<?php echo $genid?>merge-changes-hidden" type="hidden" name="merge-changes" value="" >
	<input id="<?php echo $genid?>genid" type="hidden" name="genid" value="<?php echo $genid ?>" >
		<?php 
			$show_help_option = user_config_option('show_context_help'); 
			if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_add_task_context_help', true, logged_user()->getId()))) {?>
			<div id="tasksPanelContextHelp" class="contextHelpStyle">
				<?php render_context_help($this, 'chelp add new task','add_task'); ?>
			</div>
		<?php }?>
		

	<?php if ($all) { ?>
			<div id="<?php echo $genid ?>add_task_select_workspace_div" style="display:block"> 
	<?php } else {?>
			<div id="<?php echo $genid ?>add_task_select_workspace_div" style="display:none">
	<?php }?>
	<fieldset>
	 	<?php 
			$show_help_option = user_config_option('show_context_help'); 
			if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_add_task_workspace_context_help', true, logged_user()->getId()))) {?>
			<div id="tasksPanelContextHelp" class="contextHelpStyle">
				<?php render_context_help($this, 'chelp add new task workspace','add_task_workspace'); ?>
			</div>
		<?php }?>
	<legend><?php echo lang('workspace') ?></legend>
		<?php if (isset($email_workspaces) && is_array($email_workspaces)) { ?>
		<div>
			<label><?php echo lang('suggested workspaces')?>:</label>
			<ul>
			<?php foreach ($email_workspaces as $email_workspace) { ?>
				<li><a href="#" class="link-ico ico-color<?php echo $email_workspace->getColor() ?>" onclick="og.WorkspaceSelected('<?php echo $genid ?>wsSel', {id:'<?php echo $email_workspace->getId()?>',color:'<?php echo $email_workspace->getColor()?>',name:'<?php echo $email_workspace->getName()?>'})"><?php echo $email_workspace->getPath() ?></a></li>
			<?php } ?>
			</ul>
			<br />
		</div>
		<?php } ?>
		<?php echo '<div style="float:left;">' .select_project2('ws_ids', $project instanceof Project ? $project->getId() : 0, $genid) .'</div>'?>

		<?php if (!$task->isNew()) { ?>
			<div style="float:left; padding:5px;"><?php echo checkbox_field('task[apply_ws_subtasks]', array_var($task_data, 'apply_ws_subtasks', false), array("id" => "$genid-checkapplyws")) ?><label class="checkbox" for="<?php echo "$genid-checkapplyws" ?>"><?php echo lang('apply workspace to subtasks') ?></label></div>
		<?php } ?>
		<div style="clear:both"></div>
	</fieldset>
	</div>

	<div id="<?php echo $genid ?>add_task_tags_div" style="display:none">
	<fieldset>
		<?php 
			$show_help_option = user_config_option('show_context_help'); 
			if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_add_task_tags_context_help', true, logged_user()->getId()))) {?>
			<div id="tasksPanelContextHelp" class="contextHelpStyle">
				<?php render_context_help($this, 'chelp add new task tag','add_task_tags'); ?>
			</div>
		<?php }?>
	<legend><?php echo lang('tags') ?></legend>
		<?php echo autocomplete_tags_field("task[tags]", array_var($task_data, 'tags'), null, 30); ?>
	</fieldset>
	</div>

	
	<div id="<?php echo $genid ?>add_task_more_div" style="display:block">
  	<fieldset>
    <legend><?php echo lang('task data') ?></legend>
    
	    <label><?php echo lang('milestone') ?>: <span class="desc">(<?php echo lang('assign milestone task list desc') ?>)</span></label>
	    
	    <div style="float:left;" id="<?php $genid ?>add_task_more_div_milestone_combo" >
    		<?php echo select_milestone('task[milestone_id]', $project, array_var($task_data, 'milestone_id'), array('id' => $genid . 'taskListFormMilestone', 'tabindex' => '40')) ?>    		
    	</div>
    	<?php if (!$task->isNew()) { ?>
			<div style="float:left; padding:5px;"><?php echo checkbox_field('task[apply_milestone_subtasks]', array_var($task_data, 'apply_milestone_subtasks', false), array("id" => "$genid-checkapplymi")) ?><label class="checkbox" for="<?php echo "$genid-checkapplymi" ?>"><?php echo lang('apply milestone to subtasks') ?></label></div>
		<?php } ?>
    	<div style="clear:both"></div>
    	<div style="padding-top:4px">
    		<script>
    		og.pickParentTask = function(before) {
    			og.ObjectPicker.show(function (objs) {
    				if (objs && objs.length > 0) {
    					var obj = objs[0].data;
    					if (obj.type != 'task') {
    						og.msg(lang("error"), lang("object type not supported"), 4, "err");
    					} else {
    						og.addParentTask(this, obj);
    					}
    				}
    			}, before, {
    				types: {
    					'ProjectTasks': true
    				}
    			});
    		};

    		og.addParentTask = function(before, obj) {
    			var parent = before.parentNode;
    			var count = parent.getElementsByTagName('input').length;
    			var div = document.createElement('div');
    			div.className = "og-add-template-object ico-" + obj.type + (count % 2 ? " odd" : "");
    			div.innerHTML =
    				'<input type="hidden" name="task[parent_id]" value="' + obj.object_id + '" />' +
    				'<span class="name">' + og.clean(obj.name) + '</span>' +
    				'<a href="#" onclick="og.removeParentTask(this.parentNode)" class="removeDiv" style="display: block;">'+lang('remove')+'</div>';
    			bef = document.getElementById('<?php echo $genid?>parent_before');
    			label = document.getElementById('no-task-selected<?php echo $genid?>');
    			label.style.display = 'none';
        		bef.style.display = 'none';
    			parent.insertBefore(div, before);
    		};

    		og.removeParentTask = function(div) {
    			var parent = div.parentNode;
    			parent.removeChild(div);
    			bef = document.getElementById('<?php echo $genid?>parent_before');
    			label = document.getElementById('no-task-selected<?php echo $genid?>');
    			bef.style.display = 'inline';
    			label.style.display = 'inline';
    			
    		};
    		</script>
    		<?php echo label_tag(lang('parent task'), $genid . 'addTaskTaskList') ?>
    		<?php if (isset($task_data['parent_id'])&& $task_data['parent_id'] == 0) {?>
    			    			    			
    			<span id="no-task-selected<?php echo $genid?>"><?php echo lang('none')?></span>
    			<a style="margin-left: 10px" id="<?php echo $genid ?>parent_before" href="#" onclick="og.pickParentTask(this)"><?php echo lang('set parent task') ?></a>
    			
    		<?php }else{ //echo select_task_list('task[parent_id]', $project, array_var($task_data, 'parent_id'), false, array('id' => $genid . 'addTaskTaskList', 'tabindex' => '50')) ?>
 				<?php $parentTask = ProjectTasks::findById($task_data['parent_id']);
 				if ($parentTask instanceof ProjectTask){?>
 				<span style="display: none;" id="no-task-selected<?php echo $genid?>"><?php echo lang('none')?></span>
    			<a style="display: none;margin-left: 10px" id="<?php echo $genid ?>parent_before" href="#" onclick="og.pickParentTask(this)"><?php echo lang('set parent task') ?></a> 
				<div class="og-add-template-object ico-task">
					<input type="hidden" name="task[parent_id]" value="<?php echo $parentTask->getId() ?>" />
    				<span style="float:left" class="name"> <?php echo $parentTask->getTitle() ?> </span>
    				<a style="float:left" href="#" onclick="og.removeParentTask(this.parentNode)" class="remove" style="display: block;"><?php echo lang('remove')?> </a> 
    			</div>
    		<?php }
 				}?>
    	</div>
    	
    	<div style="padding-top:4px">	
    	<?php /*echo label_tag(lang('dates'))*/ ?>
    	<table><tbody><tr><td style="padding-right: 10px">
    	<?php echo label_tag(lang('start date')) ?>
    	</td><td>
		<?php echo pick_date_widget2('task_start_date', array_var($task_data, 'start_date'), $genid, 60, true, $genid.'start_date') ?>
		</td></tr><tr><td style="padding-right: 10px">
		<?php echo label_tag(lang('due date')) ?>
    	</td><td>
		<?php echo pick_date_widget2('task_due_date', array_var($task_data, 'due_date'), $genid, 70, true, $genid.'due_date') ?>
		</td></tr></tbody></table>
		</div>
		
		<div id='<?php echo $genid ?>add_task_time_div' style="padding-top:6px">
		<?php echo label_tag(lang('time estimate')) ?>
      	<?php $totalTime = array_var($task_data, 'time_estimate', 0); 
      		$minutes = $totalTime % 60;
			$hours = ($totalTime - $minutes) / 60;
      		?>
      		<table>
		<tr>
			<td align="right"><?php echo lang("hours") ?>:&nbsp;</td>
			<td align='left'><?php echo text_field("task[time_estimate_hours]", $hours, array('style' => 'width:30px', 'tabindex' => '80')) ?></td>
			<td align="right" style="padding-left:10px"><?php echo lang("minutes") ?>:&nbsp;</td>
			<td align='left'><select name="task[time_estimate_minutes]" size="1" tabindex="85">
			<?php
				$minutes = ($totalTime % 60);
				$minuteOptions = array(0,5,10,15,20,30,45);
				for($i = 0; $i < 7; $i++) {
					echo "<option value=\"" . $minuteOptions[$i] . "\"";
					if($minutes == $minuteOptions[$i]) echo ' selected="selected"';
					echo ">" . $minuteOptions[$i] . "</option>\n";
				}
			?></select>
			</td>
		</tr></table>
 	</div>
		
		<div style="padding-top:4px">
		<?php echo label_tag(lang('task priority')) ?>
		<?php echo select_task_priority('task[priority]', array_var($task_data, 'priority', ProjectTasks::PRIORITY_NORMAL), array('tabindex' => '90')) ?>
		</div>
		
		<div style="padding-top:4px">
		<?php $task_types = ProjectCoTypes::getObjectTypesByManager('ProjectTasks');
			if (count($task_types) > 0) {
				echo label_tag(lang('object type'));
				echo select_object_type('task[object_subtype]', $task_types, array_var($task_data, 'object_subtype', config_option('default task co type')), array('tabindex' => '95', 'onchange' => "og.onChangeObjectCoType('$genid', 'ProjectTasks', ".($object->isNew() ? "0" : $object->getId()).", this.value)"));
			}
		?>
		</div>
  	</fieldset>
  	</div>

<?php if($task->isNew()) { ?>
	<?php if (isset($base_task) && $base_task instanceof ProjectTask && $base_task->getIsTemplate()) { ?>
		<input type="hidden" name="task[from_template_id]" value="<?php echo $base_task->getId() ?>" />
	<?php } ?>
<?php } // if ?>
  	
	<div id="<?php echo $genid ?>task_repeat_options_div" style="display:none">
		<fieldset>
		<?php 
			$show_help_option = user_config_option('show_context_help'); 
			if ($show_help_option == 'always' || ($show_help_option == 'until_close')&& user_config_option('show_add_event_repeat_options_context_help', true, logged_user()->getId())) {?>
			<div id="addEventPanelContextHelp" class="contextHelpStyle">
				<?php render_context_help($this, 'chelp add event repeat options','add_event_repeat_options'); ?>
			</div>
		<?php }?>
			<legend><?php echo lang('repeating task')?></legend>
		<?php
			$occ = array_var($task_data, 'occ'); 
			$rsel1 = array_var($task_data, 'rsel1', true); 
			$rsel2 = array_var($task_data, 'rsel2', ''); 
			$rsel3 = array_var($task_data, 'rsel3', ''); 
			$rnum = array_var($task_data, 'rnum', ''); 
			$rend = array_var($task_data, 'rend', '');
			// calculate what is visible given the repeating options
			$hide = '';
			if((!isset($occ)) OR $occ == 1 OR $occ=="") $hide = "display: none;";
		?>
		<table border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td align="left" valign="top" style="padding-bottom:6px">
					<table><tr><td><?php echo lang('CAL_REPEAT')?> 
						<select name="task[occurance]" onChange="og.changeTaskRepeat()" tabindex="93">
							<option value="1" id="<?php echo $genid ?>today"<?php if(isset($occ) && $occ == 1) echo ' selected="selected"'?>><?php echo lang('CAL_ONLY_TODAY')?></option>
							<option value="2" id="<?php echo $genid ?>daily"<?php if(isset($occ) && $occ == 2) echo ' selected="selected"'?>><?php echo lang('CAL_DAILY_EVENT')?></option>
							<option value="3" id="<?php echo $genid ?>weekly"<?php if(isset($occ) && $occ == 3) echo ' selected="selected"'?>><?php echo lang('CAL_WEEKLY_EVENT')?></option>
							<option value="4" id="<?php echo $genid ?>monthly"<?php if(isset($occ) && $occ == 4) echo ' selected="selected"'?>><?php echo lang('CAL_MONTHLY_EVENT') ?></option>
							<option value="5" id="<?php echo $genid ?>yearly"<?php if(isset($occ) && $occ == 5) echo  ' selected="selected"'?>><?php echo lang('CAL_YEARLY_EVENT') ?></option>
						</select>
					</td></tr></table>
				</td>
			</tr><tr>
				<td>
					<div id="<?php echo $genid ?>repeat_options" style="width: 400px; align: center; text-align: left; <?php echo $hide ?>">
						<div>
							<?php echo lang('CAL_EVERY') . " " .text_field('task[occurance_jump]', array_var($task_data, 'rjump', '1'), array('class' => 'title','size' => '2', 'id' => $genid.'occ_jump', 'tabindex' => '94', 'maxlength' => '100', 'style'=>'width:25px')) ?>
							<span id="<?php echo $genid ?>word"></span>
						</div>
						<script type="text/javascript">
							og.selectRepeatMode = function(mode) {
								var id = '';
								if (mode == 1) id = 'repeat_opt_forever';
								else if (mode == 2) id = 'repeat_opt_times';
								else if (mode == 3) id = 'repeat_opt_until';
								if (id != '') {
									el = document.getElementById('<?php echo $genid ?>'+id);
									if (el) el.checked = true;
								} 
							}
						</script>
						<table>
							<tr><td colspan="2" style="vertical-align:middle; height: 22px;">
								<?php echo radio_field('task[repeat_option]', $rsel1, array('id' => $genid.'repeat_opt_forever','value' => '1', 'style' => 'vertical-align:middle', 'tabindex' => '95')) ."&nbsp;". lang('CAL_REPEAT_FOREVER')?>
							</td></tr>
							<tr><td colspan="2" style="vertical-align:middle">
								<?php echo radio_field('task[repeat_option]', $rsel2, array('id' => $genid.'repeat_opt_times','value' => '2', 'style' => 'vertical-align:middle', 'tabindex' => '96')) ."&nbsp;". lang('CAL_REPEAT');
								echo "&nbsp;" . text_field('task[repeat_num]', $rnum, array('size' => '3', 'id' => $genid.'repeat_num', 'maxlength' => '3', 'style'=>'width:25px', 'tabindex' => '97', 'onchange' => 'og.selectRepeatMode(2);')) ."&nbsp;". lang('CAL_TIMES') ?>
							</td></tr>
							<tr><td style="vertical-align:middle"><?php echo radio_field('task[repeat_option]', $rsel3,array('id' => $genid.'repeat_opt_until','value' => '3', 'style' => 'vertical-align:middle', 'tabindex' => '98')) ."&nbsp;". lang('CAL_REPEAT_UNTIL');?></td>
								<td style="padding-left:8px;"><?php echo pick_date_widget2('task[repeat_end]', $rend, $genid, 99);?>
							</td></tr>
						</table>
						<script type="text/javascript">
							var els = document.getElementsByName('task[repeat_end]');
							for (i=0; i<els.length; i++) {
								els[i].onchange = function() {
									og.selectRepeatMode(3);
								}
							}
						</script>
						<div style="padding-top: 4px;">
							<?php echo lang('repeat by') . ' ' ?>
							<select name="task[repeat_by]" tabindex="100">
								<option value="start_date" id="<?php echo $genid ?>rep_by_start_date"<?php if (array_var($task_data, 'repeat_by') == 'start_date') echo ' selected="selected"'?>><?php echo lang('field ProjectTasks start_date')?></option>
								<option value="due_date" id="<?php echo $genid ?>rep_by_due_date"<?php if (array_var($task_data, 'repeat_by') == 'due_date') echo ' selected="selected"'?>><?php echo lang('field ProjectTasks due_date')?></option>
							</select>
						</div>
					</div>
				</td>
			</tr>
		</table>
		</fieldset>
	</div>
  
	<div id="<?php echo $genid ?>add_reminders_div" style="display:none">
		<fieldset>
		<?php 
			$show_help_option = user_config_option('show_context_help'); 
			if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_add_task_reminders_context_help', true, logged_user()->getId()))) {?>
			<div id="tasksPanelContextHelp" class="contextHelpStyle">
				<?php render_context_help($this, 'chelp add new task reminders','add_task_reminders'); ?>
			</div>
		<?php }?>
		<legend><?php echo lang('object reminders') ?></legend>
		<label><?php echo lang("due date")?>:</label>
		<div id="<?php echo $genid ?>add_reminders_content">
			<?php echo render_add_reminders($object, 'due_date', array(
				'type' => 'reminder_email',
				'duration' => 1,
				'duration_type' => 1440,
				'for_subscribers' => true,
			)); ?>
		</div>
		</fieldset>
	</div>
	
	<div id='<?php echo $genid ?>add_custom_properties_div' style="display:none">
	<fieldset>
	<?php 
			$show_help_option = user_config_option('show_context_help'); 
			if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_add_task_custom_properties_context_help', true, logged_user()->getId()))) {?>
			<div id="tasksPanelContextHelp" class="contextHelpStyle">
				<?php render_context_help($this, 'chelp add new task custom properties','add_task_custom_properties'); ?>
			</div>
		<?php }?>
    <legend><?php echo lang('custom properties') ?></legend>
    	<div id="<?php echo $genid ?>not_required_custom_properties_container">
	    	<div id="<?php echo $genid ?>not_required_custom_properties">
	      	<?php echo render_object_custom_properties($object, 'ProjectTasks', false, $co_type) ?><br/><br/>
	      	</div><br />
	    </div>
      <?php echo render_add_custom_properties($object); ?>
  	</fieldset>
 	</div>
  
    <div id="<?php echo $genid ?>add_subscribers_div" style="display:none">
		<fieldset>
		<?php 
			$show_help_option = user_config_option('show_context_help'); 
			if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_add_task_subscribers_context_help', true, logged_user()->getId()))) {?>
			<div id="tasksPanelContextHelp" class="contextHelpStyle">
				<?php render_context_help($this, 'chelp add new task subscribers','add_task_subscribers'); ?>
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
	wsTree.previousValue = <?php echo $project instanceof Project ? $project->getId() : 0 ?>;
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
			Ext.get('<?php $genid ?>add_task_more_div_milestone_combo').load({
				url: og.getUrl('milestone', 'render_add_milestone', {
					workspaces: wsid,
					genid: '<?php echo $genid ?>'
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
			if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_add_task_linked_objects_context_help', true, logged_user()->getId()))) {?>
			<div id="tasksPanelContextHelp" class="contextHelpStyle">
				<?php render_context_help($this, 'chelp add new task linked object','add_task_linked_objects'); ?>
			</div>
		<?php }?>
		<legend><?php echo lang('linked objects') ?></legend>
		<?php
			$pre_linked_objects = null;
			if (isset($from_email) && $from_email instanceof MailContent) {
				$pre_linked_objects = array($from_email);
				$attachments = $from_email->getLinkedObjects();
				foreach ($attachments as $att) {
					if ($att instanceof ProjectFile) {
						$pre_linked_objects[] = $att;
					}
				}
			}
			echo render_object_link_form($object, $pre_linked_objects)
		?>
	</fieldset>	
	</div>
	<?php } // if ?>
		
   	
	<div><?php echo label_tag(lang('description'), $genid . 'taskListFormDescription') ?>
	<?php echo textarea_field('task[text]', array_var($task_data, 'text'), array('class' => 'huge', 'id' => $genid . 'taskListFormDescription', 'tabindex' => '140')) ?>
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
		<?php $defaultNotifyValue = user_config_option('can notify from quick add'); ?>
		<label><?php echo lang('assign to') ?>:</label> 
		<table>
			<tr>
				<td>
					<input type="hidden" id="<?php echo $genid ?>taskFormAssignedTo" name="task[assigned_to]"></input>
					<div id="<?php echo $genid ?>assignto_div" class="assignto_div">
						<div id="<?php echo $genid ?>assignto_container_div"></div>
					</div>
				</td>
				<td style="padding-left:10px">
					<div  id="<?php echo $genid ?>taskFormSendNotificationDiv" style="display:none">
						<?php echo checkbox_field('task[send_notification]', array_var($task_data, 'send_notification'), array('id' => $genid . 'taskFormSendNotification')) ?>
						<label for="<?php echo $genid ?>taskFormSendNotification" class="checkbox"><?php echo lang('send task assigned to notification') ?></label>
					</div>
					<?php if (!$task->isNew()) { ?>
						<?php echo checkbox_field('task[apply_assignee_subtasks]', array_var($task_data, 'apply_assignee_subtasks'), array('id' => $genid . 'taskFormApplyAssignee')) ?>
						<label for="<?php echo $genid ?>taskFormApplyAssignee" class="checkbox"><?php echo lang('apply assignee to subtasks') ?></label>
					<?php } ?>
					<a href="Javascript:;" class="ico-add" onclick="og.openLink(og.getUrl('user', 'add', {refresh_assignees: true}), {caller:'administration'});" style="padding: 2px 0px 0px 18px;">
						<?php echo lang('add user');?>
					</a>
				</td>
			</tr>
		</table>
		
	</div>
	
	<div id="<?php echo $genid ?>required_custom_properties_container">
		<div id="<?php echo $genid ?>required_custom_properties">
			<?php tpl_assign('startTi', 15000) ?>
			<?php echo render_object_custom_properties($object, 'ProjectTasks', true, $co_type) ?>
		</div><br/>
	</div>
	<?php echo input_field("task[is_template]", array_var($task_data, 'is_template', false), array("type" => "hidden", 'tabindex' => '160')); ?>
  <?php echo submit_button($task->isNew() ? (array_var($task_data, 'is_template', false) ? lang('save template') : lang('add task list')) : lang('save changes'), 's', array('tabindex' => '20000')) ?>
</div>
</div>
</form>

<script>
	var wsSelector = Ext.get('<?php echo $genid ?>wsSel');
	var prevWsValue = -1;
	var assigned_user = '<?php echo array_var($task_data, 'assigned_to', 0) ?>';
	
	og.drawNotificationsInnerHtml = function(companies) {
		var htmlStr = '';
		var script = "";
		htmlStr += '<div id="<?php echo $genid ?>notify_companies"></div>';
		script += 'var div = Ext.getDom(\'<?php echo $genid ?>notify_companies\');';
		script += 'div.notify_companies = {};';
		script += 'var cos = div.notify_companies;';
		if (companies != null) {
			for (i = 0; i < companies.length; i++) {
				comp_id = companies[i].id;
				comp_name = companies[i].name;
				script += 'cos.company_' + comp_id + ' = {id:\'<?php echo $genid ?>notifyCompany' + comp_id + '\', checkbox_id : \'notifyCompany' + comp_id + '\',users : []};';
					
				htmlStr += '<div class="companyDetails">';
				htmlStr += '<div class="companyName">';
				
				htmlStr += '<input type="checkbox" class="checkbox" name="task[notify_company_'+comp_id+']" id="<?php echo $genid ?>notifyCompany'+comp_id+'" onclick="App.modules.addMessageForm.emailNotifyClickCompany('+comp_id+',\'<?php echo $genid ?>\',\'notify_companies\', \'notification\')"></input>'; 
				htmlStr += '<label for="<?php echo $genid ?>notifyCompany'+comp_id+'" class="checkbox">'+og.clean(comp_name)+'</label>';
				
				htmlStr += '</div>';
				htmlStr += '<div class="companyMembers">';
				htmlStr += '<ul>';
				
				for (j = 0; j < companies[i].users.length; j++) {
					usr = companies[i].users[j];
					htmlStr += '<li><input type="checkbox" class="checkbox" name="task[notify_user_'+usr.id+']" id="<?php echo $genid ?>notifyUser'+usr.id+'" onclick="App.modules.addMessageForm.emailNotifyClickUser('+comp_id+','+usr.id+',\'<?php echo $genid ?>\',\'notify_companies\', \'notification\')"></input>'; 
					htmlStr += '<label for="<?php echo $genid ?>notifyUser'+usr.id+'" class="checkbox">'+og.clean(usr.name)+'</label>';
					script += 'cos.company_' + comp_id + '.users.push({ id:'+usr.id+', checkbox_id : \'notifyUser' + usr.id + '\'});';
				}
				htmlStr += '</ul>';
				htmlStr += '</div>';
				htmlStr += '</div>';
			}
		}
		Ext.lib.Event.onAvailable('<?php echo $genid ?>notify_companies', function() {
			eval(script);
		});
		return htmlStr;
	}
	
	og.drawAssignedToSelectBox = function(companies) {
		usersStore = ogTasks.buildAssignedToComboStore(companies);
		var assignCombo = new Ext.form.ComboBox({
			renderTo:'<?php echo $genid ?>assignto_container_div',
			name: 'taskFormAssignedToCombo',
			id: '<?php echo $genid ?>taskFormAssignedToCombo',
			value: assigned_user,
			store: usersStore,
			displayField:'text',
	        typeAhead: true,
	        mode: 'local',
	        triggerAction: 'all',
	        selectOnFocus:true,
	        width:160,
	        tabIndex: '150',
	        valueField: 'value',
	        emptyText: (lang('select user or group') + '...'),
	        valueNotFoundText: ''
		});
		assignCombo.on('select', og.onAssignToComboSelect);

		assignedto = document.getElementById('<?php echo $genid ?>taskFormAssignedTo');
		if (assignedto){
			assignedto.value = assigned_user;
			og.addTaskUserChanged('<?php echo $genid ?>', '<?php echo logged_user()->getId() ?>');
		}
	}
	
	og.onAssignToComboSelect = function() {
		combo = Ext.getCmp('<?php echo $genid ?>taskFormAssignedToCombo');
		assignedto = document.getElementById('<?php echo $genid ?>taskFormAssignedTo');
		if (assignedto) assignedto.value = combo.getValue();
		assigned_user = combo.getValue();
		
		og.addTaskUserChanged('<?php echo $genid ?>', '<?php echo logged_user()->getId() ?>');
	}

	og.addTaskUserChanged = function(genid, logged_user_id){
		var ddUser = document.getElementById(genid + 'taskFormAssignedTo');
		var chk = document.getElementById(genid + 'taskFormSendNotification');
		if (ddUser && chk){
			var values = ddUser.value.split(':');
			var user = values[1];
			var nV = <?php echo $defaultNotifyValue?>;
			chk.checked = (user > 0 && nV != 0 && user != logged_user_id);
			document.getElementById(genid + 'taskFormSendNotificationDiv').style.display = user > 0 ? 'block':'none';
		}
	}

	var companies;
	og.drawUserLists = function(success, data) {
		companies = data.companies;
	
		var assign_div = Ext.get('<?php echo $genid ?>assignto_container_div');
		if (assign_div != null) {
			assign_div.remove();
			assign_div = Ext.get('<?php echo $genid ?>assignto_div');
			if (assign_div != null) {
				assign_div.insertHtml('beforeEnd', '<div id="<?php echo $genid ?>assignto_container_div"></div>');
				og.drawAssignedToSelectBox(companies);
			}
		}

		var inv_div = Ext.get('<?php echo $genid ?>inv_companies_div');
		if (inv_div != null) inv_div.remove();
		inv_div = Ext.get('emailNotification');

		if (inv_div != null) {
			inv_div.insertHtml('beforeEnd', '<div id="<?php echo $genid ?>inv_companies_div">' + og.drawNotificationsInnerHtml(companies) + '</div>');	
			inv_div.repaint();
		}
	}
	
	var wsVal;
	og.redrawUserLists = function(){
		wsVal = Ext.get('<?php echo $genid ?>wsSelValue').getValue();
		
		if (wsVal != prevWsValue) {
			og.openLink(og.getUrl('task', 'allowed_users_to_assign', {ws_id:wsVal}), {callback:og.drawUserLists});
			prevWsValue = wsVal;
		}
	}
	wsSelector.addListener('click', og.redrawUserLists);
	og.redrawUserLists();
	
	og.changeTaskRepeat = function() {
		document.getElementById("<?php echo $genid ?>repeat_options").style.display = 'none';
		var word = '';
		var opt_display = '';
		if(document.getElementById("<?php echo $genid ?>daily").selected){
			word = '<?php echo escape_single_quotes(lang("days"))?>';
		} else if(document.getElementById("<?php echo $genid ?>weekly").selected){
			word = '<?php echo escape_single_quotes(lang("weeks"))?>';
		} else if(document.getElementById("<?php echo $genid ?>monthly").selected){
			word = '<?php echo escape_single_quotes(lang("months"))?>';
		} else if(document.getElementById("<?php echo $genid ?>yearly").selected){
			word = '<?php echo escape_single_quotes(lang("years"))?>';
		} else opt_display = 'none';
		
		document.getElementById("<?php echo $genid ?>word").innerHTML = word;
		document.getElementById("<?php echo $genid ?>repeat_options").style.display = opt_display;		
	}
	og.changeTaskRepeat();

	Ext.get('<?php echo $genid ?>taskListFormName').focus();
</script>