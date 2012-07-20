<?php
	require_javascript('og/modules/addTaskForm.js');
	require_javascript('og/tasks/main.js');
	require_javascript('og/tasks/addTask.js');
	require_javascript("og/ObjectPicker.js");
        require_javascript('og/tasks/TaskPopUp.js');
	$genid = gen_id();
	$co_type = array_var($task_data, 'object_subtype');
	
	if (config_option('use tasks dependencies')) {
		require_javascript('og/tasks/task_dependencies.js');
	}
	
	$visible_cps = CustomProperties::countVisibleCustomPropertiesByObjectType($task->getObjectTypeId());
        
        $loc = user_config_option('localization');
	if (strlen($loc) > 2) $loc = substr($loc, 0, 2);
?>
<script>
og.genid = '<?php echo $genid?>';
og.config.multi_assignment = '<?php echo config_option('multi_assignment') && Plugins::instance()->isActivePlugin('crpm') ? '1' : '0' ?>';
</script>
<form id="<?php echo $genid ?>submit-edit-form" style='height:100%;background-color:white' class="add-task" action="<?php echo $task->isNew() ? get_url('task', 'add_task', array("copyId" => array_var($task_data, 'copyId'))) : $task->getEditListUrl() ?>" method="post" onsubmit="return App.modules.addTaskForm.checkSubmitAddTask('<?php echo $genid; ?>','<?php echo $task->manager()->getObjectTypeId()?>') && og.handleMemberChooserSubmit('<?php echo $genid; ?>', <?php echo $task->manager()->getObjectTypeId() ?>) && og.setDescription() <?php if (array_var($task_data, 'multi_assignment') && Plugins::instance()->isActivePlugin('crpm')) { echo "&& og.TaskMultiAssignment()";}?>;">

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
		<?php echo label_tag(lang('name'),'ogTasksPanelATTitle', true ) ?>
                <?php echo text_field('task[name]', array_var($task_data, 'name'), 
    		array('class' => 'title', 'id' => 'ogTasksPanelATTitle', 'tabindex' => '1',"size"=>"255", "maxlength"=>"255")) ?>
    </div>
	
	<?php $categories = array(); Hook::fire('object_edit_categories', $task, $categories); ?>
	
	<div style="padding-top:5px">
		<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_task_select_context_div',this)" ><?php echo lang('context') ?></a> -
		<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_task_more_div', this)" style="font-weight:bold" ><?php echo lang('task data') ?></a> -  
		<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>task_repeat_options_div',this)"><?php echo lang('repeating task') ?></a>  -
		<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_reminders_div',this)"><?php echo lang('object reminders') ?></a>  -
		<a href="#" class="option <?php echo $visible_cps>0 ? 'bold' : ''?>" onclick="og.toggleAndBolden('<?php echo $genid ?>add_custom_properties_div', this)"><?php echo lang('custom properties') ?></a> -
		<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_subscribers_div',this)"><?php echo lang('object subscribers') ?></a>
		<?php if($task->isNew() || $task->canLinkObject(logged_user())) { ?> - 
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
        <input id="<?php echo $genid?>view_related" type="hidden" name="view_related" value="<?php echo (isset($task_related) ? $task_related : "")?>" />
        <input id="<?php echo $genid?>type_related" type="hidden" name="type_related" value="only" />
        <input id="<?php echo $genid?>multi_assignment_aplly_change" type="hidden" name="task[multi_assignment_aplly_change]" value="" />
        <input id="<?php echo $genid?>view_add" type="hidden" name="view_add" value="true" />
	
	<div id="<?php echo $genid ?>add_task_select_context_div" style="display:none">
	<fieldset>
		<legend><?php echo lang('context')?></legend>
		<?php
			if ($task->isNew()) {
				render_dimension_trees($task->manager()->getObjectTypeId(), $genid, null, array('select_current_context' => true));
			} else {
				render_dimension_trees($task->manager()->getObjectTypeId(), $genid, $task->getMemberIds());
			} 
		?>
	</fieldset>
	</div>

	
	<div id="<?php echo $genid ?>add_task_more_div" style="display:block">
  	<fieldset>
    <legend><?php echo lang('task data') ?></legend>
    
	    <label><?php echo lang('milestone') ?>: <span class="desc">(<?php echo lang('assign milestone task list desc') ?>)</span></label>
	    
	    <div style="float:left;" id="<?php $genid ?>add_task_more_div_milestone_combo" >
    		<?php  echo select_milestone('task[milestone_id]', null, array_var($task_data, 'milestone_id'), array('id' => $genid . 'taskListFormMilestone', 'tabindex' => '40')) ?>    		
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
    				types: ['task'],
    				selected_type: 'task'
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
    			
    		<?php }else{
 				$parentTask = ProjectTasks::findById($task_data['parent_id']);
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
			<div style="float:left;"><?php echo pick_date_widget2('task_start_date', array_var($task_data, 'start_date'), $genid, 60, true, $genid.'start_date') ?></div>
			<?php if (config_option('use_time_in_task_dates')) { ?>
			<div style="float:left;margin-left:10px;"><?php echo pick_time_widget2('task_start_time', $task->getUseStartTime() ? array_var($task_data, 'start_date') : user_config_option('work_day_start_time'), $genid, 65) ?></div>
			<?php } ?>
		</td></tr><tr><td style="padding-right: 10px">
		<?php echo label_tag(lang('due date')) ?>
    	</td><td>
    		<div style="float:left;"><?php echo pick_date_widget2('task_due_date', array_var($task_data, 'due_date'), $genid, 70, true, $genid.'due_date'); ?></div>
    		<?php if (config_option('use_time_in_task_dates')) { ?>
    		<div style="float:left;margin-left:10px;"><?php echo pick_time_widget2('task_due_time', $task->getUseDueTime() ? array_var($task_data, 'due_date') : user_config_option('work_day_end_time'), $genid, 75); ?></div>
    		<?php } ?>
    		<div class="clear"></div>
		</td></tr></tbody></table>
		</div>
		
		<div id='<?php echo $genid ?>add_task_time_div' style="padding-top:6px">
		<?php echo label_tag(lang('time estimate')) ?>
                <?php 
                        $totalTime = array_var($task_data, 'time_estimate', 0); 
                        $minutes = $totalTime % 60;
			$hours = ($totalTime - $minutes) / 60;
      		?>
      		<table>
		<tr>
			<td align="right"><?php echo lang("hours") ?>:&nbsp;</td>
			<td align='left'><?php echo text_field("task[time_estimate_hours]", $hours, array('id' => 'ogTasksPanelATHours', 'style' => 'width:30px', 'tabindex' => '80')) ?></td>
			<td align="right" style="padding-left:10px"><?php echo lang("minutes") ?>:&nbsp;</td>
			<td align='left'><select name="task[time_estimate_minutes]" size="1" tabindex="85" id="ogTasksPanelATMinutes">
			<?php
				$minutes = ($totalTime % 60);
				$minuteOptions = array(0,5,10,15,20,25,30,35,40,45,50,55);
				for($i = 0; $i < 12; $i++) {
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
		
                <?php if(array_var($task_data, 'time_estimate') == 0){?>
		<div style="padding-top:4px">
		<?php echo label_tag(lang('percent completed')) ?>
		<?php echo input_field('task[percent_completed]', array_var($task_data, 'percent_completed', 0), array('tabindex' => '100', 'class' => 'short')) ?>
		</div>
		<?php }?>
                
		<?php if (config_option('use tasks dependencies')) { ?>
		<div style="padding-top:4px">
		<?php echo label_tag(lang('previous tasks')) ?>
		<?php 	
			if (!$task->isNew())
				$previous_tasks = ProjectTaskDependencies::findAll(array('conditions' => 'task_id = '.$task->getId()));
			else $previous_tasks = array();
		?>
			<div>
			<?php if (count($previous_tasks) == 0) { ?>
				<span id="<?php echo $genid?>no_previous_selected"><?php echo lang('none') ?></span>
				<script>if (!og.previousTasks) og.previousTasks = []; og.previousTasksIdx = og.previousTasks.length;</script>
			<?php } else {
				$k=0; ?>
				<script>
					og.previousTasks=[];
					og.previousTasksIdx = '<?php echo count($previous_tasks)?>';
				</script>
				<input type="hidden" name="task[clean_dep]" value="1" />
				<?php 
				foreach ($previous_tasks as $task_dep) {
					$task = ProjectTasks::findById($task_dep->getPreviousTaskId());
				?>
					<div class="og-add-template-object ico-task">
						<input type="hidden" name="task[previous]['<?php echo $k?>']" value="<?php echo $task->getId()?>" />
						<span class="name"><?php echo clean($task->getTitle()) ?></span>
						<a href="#" onclick="og.removePreviousTask(this.parentNode, '<?php echo $genid?>', '<?php echo $k?>')" class="removeDiv" style="display: block;"><?php echo lang('remove') ?></a>
					</div>
					<script>
						var obj={id:'<?php echo $task_dep->getPreviousTaskId() ?>'};
						og.previousTasks[og.previousTasks.length] = obj;
					</script>
				<?php $k++;
				}
			} ?>
			</div><a class="coViewAction ico-add" id="<?php echo $genid?>previous_before" href="#" onclick="og.pickPreviousTask(this, '<?php echo $genid?>')"><?php echo lang('add previous task') ?></a>
		
		</div>
		<?php } ?>
		
		<div style="padding-top:4px">
		<?php $task_types = ProjectCoTypes::getObjectTypesByManager('ProjectTasks');
			if (count($task_types) > 0) {
				echo label_tag(lang('object type'));
				echo select_object_type('task[object_subtype]', $task_types, array_var($task_data, 'object_subtype', config_option('default task co type')), array('tabindex' => '95', 'onchange' => "og.onChangeObjectCoType('$genid', '".$task->getObjectTypeId()."', ".($task->isNew() ? "0" : $task->getId()).", this.value)"));
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
			<legend><?php echo lang('repeating task')?></legend>
		<?php
                        if(!$task->isCompleted()){
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
			</tr>
                        <tr>
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
                                                        
                                                        og.viewDays = function(view) {
                                                            var btn = Ext.get('<?php echo $genid ?>repeat_days');
                                                            if(view){
                                                                btn.dom.style.display = 'block';
                                                            }else{
                                                                btn.dom.style.display = 'none';
                                                            }
                                                        }
						</script>
						<table>
							<tr><td colspan="2" style="vertical-align:middle; height: 22px;">
								<?php echo radio_field('task[repeat_option]', $rsel1, array('id' => $genid.'repeat_opt_forever','value' => '1', 'style' => 'vertical-align:middle', 'tabindex' => '95', 'onclick' => 'og.viewDays(false)')) ."&nbsp;". lang('CAL_REPEAT_FOREVER')?>
							</td></tr>
							<tr><td colspan="2" style="vertical-align:middle">
								<?php echo radio_field('task[repeat_option]', $rsel2, array('id' => $genid.'repeat_opt_times','value' => '2', 'style' => 'vertical-align:middle', 'tabindex' => '96', 'onclick' => 'og.viewDays(true)')) ."&nbsp;". lang('CAL_REPEAT');
								echo "&nbsp;" . text_field('task[repeat_num]', $rnum, array('size' => '3', 'id' => $genid.'repeat_num', 'maxlength' => '3', 'style'=>'width:25px', 'tabindex' => '97', 'onchange' => 'og.selectRepeatMode(2);')) ."&nbsp;". lang('CAL_TIMES') ?>
							</td></tr>
							<tr><td style="vertical-align:middle"><?php echo radio_field('task[repeat_option]', $rsel3,array('id' => $genid.'repeat_opt_until','value' => '3', 'style' => 'vertical-align:middle', 'tabindex' => '98', 'onclick' => 'og.viewDays(false)')) ."&nbsp;". lang('CAL_REPEAT_UNTIL');?></td>
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
                        
                        <tr id="<?php echo $genid ?>repeat_days" style="display: none;">
                            <td>
                                <table>
                                    <tr>
                                        <td>
                                            <input class="checkbox" type="checkbox" value="1" name="task[repeat_saturdays]"/>
                                            <?php echo lang('repeat on saturdays')?>                                                
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input class="checkbox" type="checkbox" value="1" name="task[repeat_sundays]"/>
                                            <?php echo lang('repeat on sundays')?>                                                
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input class="checkbox" type="checkbox" value="1" name="task[working_days]"/>
                                            <?php echo lang('repeat working days')?>                                                
                                        </td>
                                    </tr>
                                </table>
                            </td>
			</tr>
		</table>
                <?php }else{ echo lang('option repetitive task completed');}?>
		</fieldset>
	</div>
  
	<div id="<?php echo $genid ?>add_reminders_div" style="display:none">
		<fieldset>
		<legend><?php echo lang('object reminders') ?></legend>
		<label><?php echo lang("due date")?>:</label>
		<div id="<?php echo $genid ?>add_reminders_content">
			<?php echo render_add_reminders($task, 'due_date', array(
				'type' => 'reminder_email',
				'duration' => 1,
				'duration_type' => 1440,
				'for_subscribers' => true,
			)); ?>
		</div>
		</fieldset>
	</div>
	
	<div id="<?php echo $genid ?>add_custom_properties_div" style="<?php echo ($visible_cps > 0 ? "" : "display:none") ?>">
	<fieldset>
		<legend><?php echo lang('custom properties') ?></legend>
    	<div id="<?php echo $genid ?>not_required_custom_properties_container">
	    	<div id="<?php echo $genid ?>not_required_custom_properties">
	      	<?php echo render_object_custom_properties($task, false, $co_type) ?>
	      	</div>
	    </div>
      <?php //echo render_add_custom_properties($task); ?>
  	</fieldset>
 	</div>
  
    <div id="<?php echo $genid ?>add_subscribers_div" style="display:none">
		<fieldset>
		<legend><?php echo lang('object subscribers') ?></legend>
		<div id="<?php echo $genid ?>add_subscribers_content">
			<?php echo render_add_subscribers($task, $genid); ?>
		</div>
		</fieldset>
	</div>
	
	<?php if($task->isNew() || $task->canLinkObject(logged_user())) { ?>
	<div style="display:none" id="<?php echo $genid ?>add_linked_objects_div">
	<fieldset>
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
			echo render_object_link_form($task, $pre_linked_objects)
		
		?>
	</fieldset>	
	</div>
	<?php } // if ?>
		
   	
	<div>
                <?php 
                    if(config_option("wysiwyg_tasks")){
                        if(array_var($task_data, 'type_content') == "text"){
                            $ckEditorContent = nl2br(htmlspecialchars(array_var($task_data, 'text')));
                        }else{
                            $ckEditorContent = purify_html(nl2br(array_var($task_data, 'text')));
                        }                        
                ?>
		<?php echo label_tag(lang('description'), $genid . 'taskListFormDescription') ?>
                <div id="<?php echo $genid ?>ckcontainer" style="height: 100%">
                    <textarea cols="80" id="<?php echo $genid ?>ckeditor" name="task[text]" rows="10"><?php echo clean($ckEditorContent) ?></textarea>
                </div>
                <script>
                    var h = document.getElementById("<?php echo $genid ?>ckcontainer").offsetHeight;
                    var editor = CKEDITOR.replace('<?php echo $genid ?>ckeditor', {
                        uiColor: '#BBCCEA',
                        height: h,
                        enterMode: CKEDITOR.ENTER_BR,
                        shiftEnterMode: CKEDITOR.ENTER_BR,
                        disableNativeSpellChecker: false,
                        language: '<?php echo $loc ?>',
                        customConfig: '',
                        toolbar: [
                                        ['FontSize','-','Bold','Italic','Underline','-', 'SpellChecker', 'Scayt','-',
                                        //'NumberedList','BulletedList','-',
                                        'TextColor','BGColor','-',
                                        'JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock']
                                ],
                        on: {
                                instanceReady: function(ev) {
                                        og.adjustCkEditorArea('<?php echo $genid ?>');
                                        editor.resetDirty();
                                }
                            },
                        entities_additional : '#39,#336,#337,#368,#369'
                    });

                    og.setDescription = function() {
                            var form = Ext.getDom('<?php echo $genid ?>submit-edit-form');
                            if (form.preventDoubleSubmit) return false;

                            setTimeout(function() {
                                    form.preventDoubleSubmit = false;
                            }, 2000);

                            var editor = og.getCkEditorInstance('<?php echo $genid ?>ckeditor');
                            form['task[text]'].value = editor.getData();

                            return true;
                    };
                </script>
                <?php }else{?>
                <div>
                        <?php 
                            if(array_var($task_data, 'type_content') == "text"){
                                $content_text = array_var($task_data, 'text');
                            }else{
                                $content_text = html_to_text(html_entity_decode(nl2br(array_var($task_data, 'text')), null, "UTF-8"));
                            }   
                        ?>
                        <?php echo label_tag(lang('description'), $genid . 'taskListFormDescription') ?>
                        <?php echo textarea_field('task[text]', $content_text, array('class' => 'huge', 'id' => $genid . 'taskListFormDescription', 'tabindex' => '140')) ?>
                </div>
                <script>
                    og.setDescription = function() {
                            return true;
                    };
                </script>
                <?php }?>
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
		<table><tr><td>
			<input type="hidden" id="<?php echo $genid ?>taskFormAssignedTo" name="task[assigned_to_contact_id]" value="<?php echo array_var($task_data, 'assigned_to_contact_id')?>"></input>
			<div id="<?php echo $genid ?>assignto_container_div"></div>
			
		</td><td style="padding-left:10px"><div  id="<?php echo $genid ?>taskFormSendNotificationDiv" style="display:none">
			<?php echo checkbox_field('task[send_notification]', array_var($task_data, 'send_notification'), array('id' => $genid . 'taskFormSendNotification')) ?>
			<label for="<?php echo $genid ?>taskFormSendNotification" class="checkbox"><?php echo lang('send task assigned to notification') ?></label>
		</div>
		<?php if (!$task->isNew()) { ?>
			<?php echo checkbox_field('task[apply_assignee_subtasks]', array_var($task_data, 'apply_assignee_subtasks'), array('id' => $genid . 'taskFormApplyAssignee')) ?>
			<label for="<?php echo $genid ?>taskFormApplyAssignee" class="checkbox"><?php echo lang('apply assignee to subtasks') ?></label>
		<?php } ?>
		</td></tr></table>
		
	</div>
        
        <?php 
            if(config_option('multi_assignment') && Plugins::instance()->isActivePlugin('crpm')){
                if($task->isNew()){
                    $null = null;
                    Hook::fire('draw_html', $genid, $null);
                }                
                require_javascript('multi_assignment.js', 'crpm');
            }
        ?>
        
	<?php echo input_field("task[is_template]", array_var($task_data, 'is_template', false), array("type" => "hidden", 'tabindex' => '160')); ?>
        <?php echo submit_button($task->isNew() ? (array_var($task_data, 'is_template', false) ? lang('save template') : lang('add task list')) : lang('save changes'), 's', array('tabindex' => '20000')) ?>
</div>
</div>
</form>

<script>

	var assigned_user = '<?php echo array_var($task_data, 'assigned_to_contact_id', 0) ?>';
	var start = true;
	
	og.drawAssignedToSelectBox = function(companies, only_me) {
		usersStore = ogTasks.buildAssignedToComboStore(companies, only_me);
		var assignCombo = new Ext.form.ComboBox({
			renderTo:'<?php echo $genid ?>assignto_container_div',
			name: 'taskFormAssignedToCombo',
			id: '<?php echo $genid ?>taskFormAssignedToCombo',
			value: assigned_user,
			store: usersStore,
			displayField:'text',
	        typeAhead: true,
	        mode: 'local',
	        cls: 'assigned-to-combo',
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
			var user = ddUser.value;
			var nV = <?php echo $defaultNotifyValue?>;
			chk.checked = (user > 0 && nV != 0 && user != logged_user_id);
			var user_obj = ogTasks.getUser(user); // check if selected user is a user or a company
			document.getElementById(genid + 'taskFormSendNotificationDiv').style.display = (user > 0 && user_obj) ? 'block':'none';
		}
	}
	

	og.redrawUserLists = function(context){
		if (!og.redrawingUserList) {
			og.redrawingUserList = true ;
			var prev_value = 0;
			var combo = Ext.getCmp('<?php echo $genid ?>taskFormAssignedToCombo');
			if (combo) {
				combo.collapse();
				combo.disable();
				prev_value = combo.getValue();
			}
			
			parameters = context ? {context: context} : {};
			og.openLink(og.getUrl('task', 'allowed_users_to_assign', parameters), {callback: function(success, data){
				companies = data.companies;
				only_me = data.only_me ? data.only_me : null;
				if (combo) {
					combo.reset();
					combo.store.removeAll();
					combo.store.loadData(ogTasks.buildAssignedToComboStore(companies, only_me));
					combo.setValue(prev_value);
					combo.enable();
				} else {
					og.drawAssignedToSelectBox(companies, only_me);
				}
				og.redrawingUserList = false ;
			}});
			setTimeout(function() { 
				og.redrawingUserList = false
			}, 1500);
		}		
	}
	
	og.redrawUserLists(og.contextManager.plainContext());
        
        <?php if(!$task->isCompleted()){?>
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
        <?php }?>

	var memberChoosers = Ext.getCmp('<?php echo "$genid-member-chooser-panel-".$task->manager()->getObjectTypeId()?>').items;
	
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

				var milestone_el = document.getElementById('<?php echo $genid ?>taskListFormMilestone');
				var actual_value = milestone_el ? milestone_el.value : 0;
				Ext.get('<?php $genid ?>add_task_more_div_milestone_combo').load({
					url: og.getUrl('milestone', 'render_add_milestone', {
						context: Ext.util.JSON.encode(dimensionMembers),
						genid: '<?php echo $genid ?>',
						selected: actual_value
					}),
					scripts: true
				});

				var uids = App.modules.addMessageForm.getCheckedUsers('<?php echo $genid ?>');

				Ext.get('<?php echo $genid ?>add_subscribers_content').load({
					url: og.getUrl('object', 'render_add_subscribers', {
						context: Ext.util.JSON.encode(dimensionMembers),
						users: uids,
						genid: '<?php echo $genid ?>',
						otype: '<?php echo $task->manager()->getObjectTypeId()?>'
					}),
					scripts: true
				});
				og.redrawUserLists(Ext.util.JSON.encode(dimensionMembers));

			});
		});
	}

	Ext.get('ogTasksPanelATTitle').focus();
        
        Ext.extend(og.TaskPopUp, Ext.Window, {
                accept: function() {
                        var opt = $("#<?php echo $genid?>type_related").val();
                        if(opt == "pending"){
                            var url = og.getUrl('task', 'edit_task', {id : <?php echo $pending_task_id ?>, replace : true});
                            og.openLink(url, {
                                    method: 'POST',
                                    scope: this
                            });
                        }                        
                        this.close();
                }
        });
                
        $(document).ready(function() {
            if($("#<?php echo $genid?>view_related").val()){
                <?php if($task->isCompleted()){ ?>
                        this.dialog = new og.TaskPopUp('task_complete','');
                <?php }else{?>
                        this.dialog = new og.TaskPopUp('','');
                <?php }?>
                this.dialog.setTitle(lang('tasks related'));
                this.dialog.show();      
            }
        });
        
        function selectRelated(val){
                $("#<?php echo $genid?>type_related").val(val);
        }
        
        
        <?php if ($task->isNew()){ ?> 
            COUNT_LINE = 1;
            <?php 
                    if (count($multi_assignment) > 0) {
                        foreach($multi_assignment as $assignment){ ?>
                            addMultiAssignment('<?php echo $genid ?>','<?php echo $assignment['assigned_to_contact_id'] ?>' , '<?php echo $assignment['name'] ?>', '<?php echo $assignment['time_estimate_hours'] ?>', '<?php echo $assignment['time_estimate_minutes'] ?>');		
            <?php       }
                    }//foreach ?>
        <?php }//if ?>
</script>