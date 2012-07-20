<?php
	require_javascript("og/CSVCombo.js");
	require_javascript("og/DateField.js");
	require_javascript('og/tasks/main.js');
	require_javascript('og/tasks/addTask.js');
	require_javascript('og/tasks/drawing.js');
	require_javascript('og/tasks/TasksTopToolbar.js');
	require_javascript('og/tasks/TasksBottomToolbar.js');
	require_javascript('og/tasks/print.js');
        require_javascript('og/tasks/TaskPopUp.js');

	if (config_option('use tasks dependencies')) {
		require_javascript('og/tasks/task_dependencies.js');
	}        

        if(config_option('multi_assignment') && Plugins::instance()->isActivePlugin('crpm')){
                require_javascript('multi_assignment.js', 'crpm');
        }
        
        $loc = user_config_option('localization');
	if (strlen($loc) > 2) $loc = substr($loc, 0, 2);

	$genid = gen_id();
	
	$all_templates_array = array();
	$project_templates_array = array();
	$templates_array = array();
	$project_templates_array = array();
	$tasks_array = array();
	$internal_milestones_array = array();
	$external_milestones_array = array();
	$users_array = array();
	$companies_array = array();
	$allUsers_array = array();
	$object_subtypes_array = array();
	
	
	if (isset($all_templates) && !is_null($all_templates)){
		foreach($all_templates as $template) {
			$all_templates_array[] = $template->getArrayInfo();
		}
	}
	
	if (isset($project_templates) && !is_null($project_templates)) {
		foreach($project_templates as $template) {
			$project_templates_array[] = $template->getArrayInfo();
		}
	}
	
	if (isset($tasks)) {
		$ids = array();
		foreach($tasks as $task) {
			$ids[] = $task->getId();
			$tasks_array[] = $task->getArrayInfo();
		}
				
		$read_objects = ReadObjects::getReadByObjectList($ids, logged_user()->getId());
		foreach($tasks_array as &$data) {
			$data['isread'] = isset($read_objects[$data['id']]);
		}
	}
	
	if (is_array($internalMilestones)) {
		foreach($internalMilestones as $milestone) {
			$internal_milestones_array[] = $milestone->getArrayInfo();
		}
	}
	
	if (is_array($externalMilestones)) {
		foreach($externalMilestones as $milestone) {
			$external_milestones_array[] = $milestone->getArrayInfo();
		}
	}
	
	foreach($users as $user) {
		$user_info = $user->getArrayInfo();
		if ($user->getId() == logged_user()->getId())
			$user_info['isCurrent'] = true;
		$users_array[] = $user_info;
	}
	
	foreach($allUsers as $usr) {
		$allUsers_array[] = $usr->getArrayInfo();
	}
	
	foreach($companies as $company) {
		$companies_array[] = $company->getArrayInfo();
	}
	
	foreach($object_subtypes as $ot) {
		$object_subtypes_array[] = $ot->getArrayInfo();
	}
	
	if (!isset($dependency_count)) $dependency_count = array();
?>

<script>
og.noOfTasks = '<?php echo user_config_option('noOfTasks') ?>';
og.genid = '<?php echo $genid?>';
og.config.wysiwyg_tasks = '<?php echo config_option('wysiwyg_tasks') ? true : false ?>';
og.config.use_tasks_dependencies = '<?php echo config_option('use tasks dependencies') ? "1" : "0" ?>';
og.config.time_format_use_24 = '<?php echo user_config_option('time_format_use_24') ? ' - G:i' : ' - g:i A' ?>';
og.config.time_format_use_24_duetime = '<?php echo user_config_option('time_format_use_24') ? 'G:i' : 'g:i A' ?>';
og.config.work_day_start_time = '<?php echo strtotime(user_config_option('work_day_start_time')) ?>';
og.config.work_day_end_time = '<?php echo strtotime(user_config_option('work_day_end_time')) ?>';
og.config.multi_assignment = '<?php echo config_option('multi_assignment') && Plugins::instance()->isActivePlugin('crpm') ? '1' : '0' ?>';
</script>

<div id="taskPanelHiddenFields">
	<input type="hidden" id="hfProjectTemplates" value="<?php echo clean(str_replace('"',"'", str_replace("'", "\'", json_encode($project_templates_array)))) ?>"/>
	<input type="hidden" id="hfAllTemplates" value="<?php echo clean(str_replace('"',"'", str_replace("'", "\'", json_encode($all_templates_array)))) ?>"/>
	<input type="hidden" id="hfTasks" value="<?php echo clean(str_replace('"',"'", str_replace("'", "\'", json_encode($tasks_array)))) ?>"/>
	<input type="hidden" id="hfIMilestones" value="<?php echo clean(str_replace('"',"'", str_replace("'", "\'", json_encode($internal_milestones_array)))) ?>"/>
	<input type="hidden" id="hfEMilestones" value="<?php echo clean(str_replace('"',"'", str_replace("'", "\'", json_encode($external_milestones_array)))) ?>"/>
	<input type="hidden" id="hfUsers" value="<?php echo clean(str_replace('"',"'", str_replace("'", "\'", json_encode($users_array)))) ?>"/>
	<input type="hidden" id="hfAllUsers" value="<?php echo clean(str_replace('"',"'", str_replace("'", "\'", json_encode($allUsers_array)))) ?>"/>
	<input type="hidden" id="hfCompanies" value="<?php echo clean(str_replace('"',"'", str_replace("'", "\'", json_encode($companies_array)))) ?>"/>
	<input type="hidden" id="hfUserPreferences" value="<?php echo clean(str_replace('"',"'", str_replace("'", "\'", json_encode($userPreferences)))) ?>"/>
	<input type="hidden" id="hfObjectSubtypes" value="<?php echo clean(str_replace('"',"'", str_replace("'", "\'", json_encode($object_subtypes_array)))) ?>"/>
	<input type="hidden" id="hfDependencyCount" value="<?php echo clean(str_replace('"',"'", str_replace("'", "\'", json_encode($dependency_count)))) ?>"/>
        <input id="<?php echo $genid?>type_related" type="hidden" name="type_related" value="only" />
        <input id="<?php echo $genid?>complete_task" type="hidden" name="complete_task" value="yes" />        
</div>

<div id="tasksPanel" class="ogContentPanel" style="background-color:white;background-color:#F0F0F0;height:100%;width:100%;">
    
	<div id="tasksPanelTopToolbar" class="x-panel-tbar" style="width:100%;height:30px;display:block;background-color:#F0F0F0;"></div>
	<div id="tasksPanelBottomToolbar" class="x-panel-tbar" style="width:100%;height:30px;display:block;background-color:#F0F0F0;border-bottom:1px solid #CCC;"></div>
	<div id="tasksPanelContent" style="background-color:white;padding:7px;padding-top:0px;overflow-y:scroll;position:relative;">
	<?php if (isset($displayTooManyTasks) && $displayTooManyTasks){ ?>
	<div class="tasksPanelWarning ico-warning32" style="font-size:10px;color:#666;background-repeat:no-repeat;padding-left:40px;max-width:920px; margin:20px;border:1px solid #E3AD00;background-color:#FFF690;background-position:4px 4px;">
		<div style="font-weight:bold;width:99%;text-align:center;padding:4px;color:#AF8300;"><?php echo lang('too many tasks to display', user_config_option('task_display_limit')) ?></div>
	</div>
	<?php } ?>
		<div id="tasksPanelContainer" style="background-color:white;padding:7px;padding-top:0px;">
	<?php if(!(isset($tasks) || $userPreferences['groupBy'] == 'milestone')) { ?>
			<div style="font-size:130%;width:100%;text-align:center;padding-top:10px;color:#777;"><?php echo lang('no tasks to display') ?></div>
	<?php } ?>
		</div>
	</div>
</div>

<script type="text/javascript">
	if (!ogTasks.tasks_object_type_id) ogTasks.tasks_object_type_id = '<?php echo ProjectTasks::instance()->getObjectTypeId() ?>';
	if (rx__TasksDrag)
		rx__TasksDrag.initialize();

	ogTasks.userPreferences = Ext.util.JSON.decode(document.getElementById('hfUserPreferences').value);

	var mili = 0;
	if (og.TasksTopToolbar == 'undefined') {
		mili = 500;
	}

	// to prevent js execution before the js files are received
	setTimeout(function () {
		var ogTasksTT = new og.TasksTopToolbar({
			projectTemplatesHfId:'hfProjectTemplates',
			allTemplatesHfId:'hfAllTemplates',
			renderTo:'tasksPanelTopToolbar'
			});
		var ogTasksBT = new og.TasksBottomToolbar({
			renderTo:'tasksPanelBottomToolbar',
			usersHfId:'hfUsers',
			companiesHfId:'hfCompanies',
			internalMilestonesHfId:'hfIMilestones',
			externalMilestonesHfId:'hfEMilestones',
			subtypesHfId:'hfObjectSubtypes'
			});
	
		og.defaultTaskType = '<?php echo config_option('default task co type') ?>';
		
		function resizeTasksPanel(e, id) {
			var tpc = document.getElementById('tasksPanelContent');
			if (tpc) {
				tpc.style.height = (document.getElementById('tasksPanel').clientHeight - 68) + 'px';
			} else {
				og.removeDomEventHandler(window, 'resize', id);
			}
		}
		if (Ext.isIE) {
			og.addDomEventHandler(document.getElementById('tasksPanelContent'), 'resize', resizeTasksPanel);
		} else {
			og.addDomEventHandler(window, 'resize', resizeTasksPanel);
		}
		resizeTasksPanel();
		ogTasks.loadDataFromHF();

	<?php if(isset($tasks) || $userPreferences['groupBy'] == 'milestone') {?>
		ogTasks.draw();
	<?php } ?>

	}, mili);
        
        Ext.extend(og.TaskPopUp, Ext.Window, {
                accept: function() {
                        var task_id = $("#related_task_id").val();
                        var action = $("#action_related").val();
                        var opt = $("#<?php echo $genid?>type_related").val();
                        if(action == "edit"){
                            ogTasks.SubmitNewTask(task_id, false);
                        }else{
                            ogTasks.executeAction(action,'',opt);
                        }
                        
                        this.close();
                }
        });
        
        function selectRelated(val){
            $("#<?php echo $genid?>type_related").val(val);
        }
        
        Ext.extend(og.TaskCompletePopUp, Ext.Window, {
                accept: function() {
                        var task_id = $("#complete_task_id").val();
                        var opt = $("#<?php echo $genid?>complete_task").val();
                        if(task_id != ""){
                            ogTasks.ToggleCompleteStatusOk(task_id, 0 ,opt);
                        }else{
                            ogTasks.executeAction('complete','',opt);
                        }  
                        this.close();
                }
        });
        
        function selectTaskCompletePopUp(val){
            $("#<?php echo $genid?>complete_task").val(val);
        }
        
        function loadCKeditor(id){
            var instance = CKEDITOR.instances['<?php echo $genid ?>ckeditor' + id];
            if(instance)
            {
                CKEDITOR.remove(instance);
            }
            var editor = CKEDITOR.replace('<?php echo $genid ?>ckeditor' + id, {
                uiColor: '#BBCCEA',
                height: '100px',
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
                                og.adjustCkEditorArea('<?php echo $genid ?>',id);
                                editor.resetDirty();
                        }
                    },
                entities_additional : '#39,#336,#337,#368,#369'
            });
        }
</script>

<?php 
	// to include additional templates in the tasks list
	$more_content_templates = array();
	Hook::fire("include_tasks_template", null, $more_content_templates);
	foreach ($more_content_templates as $ct) {
		$this->includeTemplate(get_template_path(array_var($ct, 'template'), array_var($ct, 'controller'), array_var($ct, 'plugin')));
	}
?>
