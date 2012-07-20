/**
 *  
 * This module holds the rendering logic for the add new task div
 *
 * @author Carlos Palma <chonwil@gmail.com>
 */
 
 //************************************
//*		Draw add new task form
//************************************

ogTasks.drawAddNewTaskForm = function(group_id, parent_id, level){
	var topToolbar = Ext.getCmp('tasksPanelTopToolbarObject');
	var bottomToolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
	var filters = bottomToolbar.getFilters();
	var displayCriteria = bottomToolbar.getDisplayCriteria();
	var drawOptions = topToolbar.getDrawOptions();
	
	if (parent_id > 0)
		var parentTask = ogTasks.getTask(parent_id);
	
	if (displayCriteria.group_by == 'milestone' && group_id != 'unclassified'){
		var milestone_id = group_id;
	} else if (parentTask && parentTask.milestoneId > 0){
		var milestone_id = parentTask.milestoneId;
	} else if (filters.filter == 'milestone') {
		var milestone_id = Ext.getCmp('ogTasksFilterMilestonesCombo').getValue();
	} else {
		var milestone_id = 0;
	}
	
	var assignedToValue = null;
	if (displayCriteria.group_by == 'assigned_to' && group_id != 'unclassified'){
		assignedToValue = group_id;
	} else if(parentTask && parentTask.assignedToId){
		assignedToValue = parentTask.assignedToId;
	} else if (filters.filter == 'assigned_to') {
		assignedToValue = filters.fval;
	}
	
	var member_id = null;
	if (displayCriteria.group_by.indexOf('dimension_') == 0) {
		var dim_id = displayCriteria.group_by.replace('dimension_', '');
		member_id = group_id;
	}
	
	
	var priority = 200;
	if (displayCriteria.group_by == 'priority' && group_id != 'unclassified'){
		priority = group_id;
	}
	
	if (parent_id > 0)
		var containerName = 'ogTasksPanelTask' + parent_id + 'G' + group_id;
	else
		var containerName = 'ogTasksPanelGroup' + group_id;
	
	this.drawTaskForm(containerName, {
		parentId: parent_id,
		milestoneId: milestone_id,
		member_id: member_id,
		title: '',
		description: '',
		priority: priority,
		dueDate: '',
                startDate: '',
		assignedTo: assignedToValue,
		taskId: 0,
                time_estimated: 0,
                multiAssignment: 0,
		isEdit: false                
	});
        if(og.config.wysiwyg_tasks){
            loadCKeditor(0);
        }
}

ogTasks.drawEditTaskForm = function(task_id, group_id){
	var task = this.getTask(task_id);
	var containerName = 'ogTasksPanelTask' + task.id + 'G' + group_id;
	if (task){
		this.drawTaskForm(containerName, {
			title: task.title,
			description: task.description,
			priority: task.priority,
			members: task.members,
			dueDate: task.dueDate,
                        startDate: task.startDate,
			assignedTo: task.assignedToId,
			taskId: task_id,
                        time_estimated: task.TimeEstimate,
                        multiAssignment: task.multiAssignment,
			isEdit: true
		});
                if(og.config.wysiwyg_tasks){
                    loadCKeditor(task_id);
                }
	}
}


//submit the form when the user press enter
ogTasks.checkEnterPress = function (e,id)
{
	var characterCode;
	if (e && e.which) {
		characterCode = e.which;
	} else {
		characterCode = e.keyCode;
	}
	if (characterCode == 13) {
		ogTasks.SubmitNewTask(id,true);
		return false;
	}
	return true;
}

ogTasks.drawTaskForm = function(container_id, data){
	this.hideAddNewTaskForm();
        
	var bottomToolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
	var topToolbar = Ext.getCmp('tasksPanelTopToolbarObject');
	var drawOptions = topToolbar.getDrawOptions();
	var padding = (15/* * level*/) - 1;
	
	var html = "<div style='margin-left:" + padding + "px' class='ogTasksAddTaskForm'>";
	
	if (data.member_id && bottomToolbar.getDisplayCriteria().group_by.indexOf('dimension_') == 0) {
		html += "<input type='hidden' id='ogTasksPanelATMemberId' value='" + data.member_id + "'>";
	}
	
	if (data.parentId > 0){
		var parentTask = ogTasks.getTask(data.parentId);
		html += "<input type='hidden' id='ogTasksPanelATParentId' value='" + data.parentId + "'>";
	}
	html += "<b>" + lang('title') + ":</b><br/>";
        //html += "<input id='ogTasksPanelATTitle' type='text' class='title' name='task[name]' tabIndex=1000 value='' onkeypress='return ogTasks.checkEnterPress(event,"+ data.taskId +");' maxlength='255' size='255'   />";
	html += "<input id='ogTasksPanelATTitle' type='text' class='title' name='task[name]' tabIndex=1000 value='' maxlength='255' size='255'   />";
		
	//First column
	html += "<table style='width:100%; margin-top:7px'><tr><td>";
        if(og.config.wysiwyg_tasks){
            html += "<div id='ogTasksPanelATDesc'><b>" + lang('description') + ":</b><br/>";
            html += "<textarea id='" + og.genid + "ckeditor" + data.taskId + "' cols='40' rows='10' name='task[text]' class='short ckeditor' tabIndex=1100 style='height:50px'>" + data.description + "</textarea></div>";
        }else{
            html += "<div id='ogTasksPanelATDesc'><b>" + lang('description') + ":</b><br/>";
            html += "<textarea id='ogTasksPanelATDescCtl' cols='40' rows='10' name='task[text]' class='short' tabIndex=1100 style='height:50px'>" + data.description + "</textarea></div>";
        }
	
	var chkIsVisible = data.assignedTo && data.assignedTo != '0';
	var chkIsChecked = chkIsVisible && ogTasks.userPreferences.defaultNotifyValue && data.assignedTo != this.currentUser.id;	
	
	html += "<div id='ogTasksPanelATContext' style='padding-top:5px;padding-bottom: 10px; display:none'><table><tr><td style='width:120px;'><b>" + lang('context') + ":&nbsp;</b></td><td><input type=\"hidden\" id=\"ogTasksPanelMembers\" name=\"members\" value=\"\"/><div id='ogTasksPanelContextSelector'>";

	html += og.popupMemberChooserHtml('', ogTasks.tasks_object_type_id, "ogTasksPanelMembers", data.members, true);
	html += "</div></td>";
	//if (data.isEdit && data.subtasksCount>0) html += "<td style=\"padding-left:15px\"><label for=\"ogTasksPanelApplyWS\"><input style=\"width:14px;\" type=\"checkbox\" name=\"task[apply_ws_subtasks]\" id=\"ogTasksPanelApplyWS\" />&nbsp;" + lang('apply workspace to subtasks') + "</label></td>";
	html += "</tr></table></div>";
	html += "<div id='ogTasksPanelATMilestone' style='padding-top:5px; display:none'><table><tr><td style='width:120px;'><b>" + lang('milestone') + ":&nbsp;</b></td><td><div id='ogTasksPanelMilestoneSelector'></div></td>";
	//html += "<div id='ogTasksPanelATMilestone' style='padding-top:5px;" + (data.isEdit? '': 'display:none') + "'><table><tr><td style='width:120px;'><b>" + lang('milestone') + ":&nbsp;</b></td><td><div id='ogTasksPanelMilestoneSelector'></div></td>";
	//if (data.isEdit && data.subtasksCount>0) html += "<td style=\"padding-left:15px\"><label for=\"ogTasksPanelApplyMI\"><input style=\"width:14px;\" type=\"checkbox\" name=\"task[apply_milestone_subtasks]\" id=\"ogTasksPanelApplyMI\" />&nbsp;" + lang('apply milestone to subtasks') + "</label></td>";
	html += "</tr></table></div>";
	if (data.milestoneId) {
		html += "<input type='hidden' name='task_milestone_id' value='"+data.milestoneId+"'/>";
	}
	html += "<div id='ogTasksPanelATObjectType' style='padding-top:5px;'><table><tr><td style='width:120px;'><b>" + lang('object type') + ":&nbsp;</b></td><td><input id='ogTasksPanelObjectTypeSelector' style='min-width:120px;max-width:300px' type='text' value='" + (data.otype ? data.otype : og.defaultTaskType) + "' name='task[object_subtype]'/></td></tr></table></div>";
        
        if(og.config.multi_assignment){
            if(typeof window.loadMultiAssignmentHtml == 'function'){
                html += loadMultiAssignmentHtml(data.taskId);
            }            
        }
        
	//Second column
	html += "</td><td style='padding-left:10px; margin-right:10px;width:375px;'>";
	  
	// <ASSIGN_TO COMBO>
	html += "<table><tr><td><div id='ogTasksPanelATAssigned' style='padding-top:5px;'><table><tr><td style='width:130px; vertical-align:middle;'><b>" + lang('assigned to') + ":&nbsp;</b></td><td><span id='ogTasksPanelATAssignedCont'></span></td></tr></table>";
	html += '<div  id="ogTasksPanelATNotifyDiv"><label for="ogTasksPanelATNotify"><input style="width:14px;" type="checkbox" name="task[notify]" id="ogTasksPanelATNotify" ' + (chkIsChecked? 'checked':'') + '/>&nbsp;' + lang('send notification') + '</label></div>';
	//if (data.isEdit && data.subtasksCount>0) html += '<label for="ogTasksPanelApplyAssignee"><input style="width:14px;" type="checkbox" name="task[apply_assignee_subtasks]" id="ogTasksPanelApplyAssignee" />&nbsp;' + lang('apply assignee to subtasks') + '</label>';
	html += '</div><td></tr></table>'; 
	// </ASSIGN_TO COMBO>
	
	html += "<table id='ogTasksPanelATDates' style='padding-top:5px;'>";
	html += "<tr><td style='width:130px; vertical-align:middle;'><b>" + lang('start date') + ":</b>&nbsp;</td>";
        var time_picker_html_start = og.config.use_time_in_task_dates ? "<div style='float:left;margin-left:10px;' id='ogTasksPanelATStartTime'></div>" : "";
	html += "<td><div style='float:left;' id='ogTasksPanelATStartDate'></div>"+time_picker_html_start+"</td></tr>";
	html += "<tr><td colspan='2' style='height:5px;'></td></tr>";
	html += "<tr><td style='width:130px; vertical-align:middle;'><b>" + lang('due date') + ":</b>&nbsp;</td>";
	var time_picker_html_duetime = og.config.use_time_in_task_dates ? "<div style='float:left;margin-left:10px;' id='ogTasksPanelATDueTime'></div>" : "";
	html += "<td><div style='float:left;' id='ogTasksPanelATDueDate'></div>"+time_picker_html_duetime+"</td></tr></table>";
        
        if (drawOptions.show_time_estimates){
                var totalTime = data.time_estimated; 
                var minutes = totalTime % 60;
                var hours = (totalTime - minutes) / 60;
		html += "<div id='ogTasksPanelATTime' style='padding-top:5px;'><table><tr><td style='width:130px; vertical-align:middle;'><b>" + lang('time estimate') + ":</b></td><td>";
		html += "<input type='text' id='ogTasksPanelATHours' style='width:25px' tabIndex=1250  name='task[time_estimate_hours]' value='" + hours + "'/>&nbsp;" + lang('hours') + "</td>";
                html += "<td>&nbsp;<select name='task[time_estimate_minutes]' id='ogTasksPanelATMinutes' size='1' tabindex='1250'>";
                var minuteOptions = new Array(0,5,10,15,20,25,30,35,40,45,50,55);
                for(var i = 0; i < 12; i++) {
                        html += "<option value=\"" + minuteOptions[i] + "\"";
                        if(minutes == minuteOptions[i]) html +=' selected="selected"';
                        html += ">" + minuteOptions[i] + "</option>\n";
                }
                html += "</select>&nbsp;" + lang('minutes') + "</td></tr></table></div>";
	}
	
	html += "<div id='ogTasksPanelATPriority' style='padding-top:5px;'><table><tr><td style='width:130px; vertical-align:middle;'><b>" + lang('priority') + ":&nbsp;</b></td>";
	html += "<td><span id='ogTasksPanelATPriorityCont'></span></td></tr></table></div>";
	
	html += "</td></tr><tr><td style='padding-top:15px'>";
	
	// No more options... show 'all options' link
	//if (!data.isEdit)
	//	html += "<a href='#' class='internalLink' onclick='ogTasks.addNewTaskShowMore()' id='ogTasksPanelATShowMore'><b>" + lang('more options') + "...</b></a>";
	html += "<a href='#' class='internalLink' onclick='ogTasks.TaskFormShowAll(" + data.taskId + ")' id='ogTasksPanelATShowAll'><b>" + lang('all options') + "...</b></a>";
	html += "</td><td style='text-align:right; padding-right:30px;'>";	
	
	//Buttons
        if(og.config.multi_assignment == 1){
            if(typeof window.loadMultiAssignmentHtml == 'function'){
                if(data.multiAssignment){
                    html += "<button onclick='og.TaskMultiAssignment();return false;' tabIndex=1600 type='submit' class='submit'>" + (data.isEdit? lang('save changes') : lang('add task')) + "</button>&nbsp;&nbsp;<button class='submit' tabIndex=1700 onclick='ogTasks.hideAddNewTaskForm();return false;'>" + lang('cancel') + "</button>";
                }else{
                    html += "<button onclick='ogTasks.SubmitNewTask(" + data.taskId + ", true);return false;' tabIndex=1600 type='submit' class='submit'>" + (data.isEdit? lang('save changes') : lang('add task')) + "</button>&nbsp;&nbsp;<button class='submit' tabIndex=1700 onclick='ogTasks.hideAddNewTaskForm();return false;'>" + lang('cancel') + "</button>";
                }
            }else{
                html += "<button onclick='ogTasks.SubmitNewTask(" + data.taskId + ", true);return false;' tabIndex=1600 type='submit' class='submit'>" + (data.isEdit? lang('save changes') : lang('add task')) + "</button>&nbsp;&nbsp;<button class='submit' tabIndex=1700 onclick='ogTasks.hideAddNewTaskForm();return false;'>" + lang('cancel') + "</button>";
            }
        }else{
            html += "<button onclick='ogTasks.SubmitNewTask(" + data.taskId + ", true);return false;' tabIndex=1600 type='submit' class='submit'>" + (data.isEdit? lang('save changes') : lang('add task')) + "</button>&nbsp;&nbsp;<button class='submit' tabIndex=1700 onclick='ogTasks.hideAddNewTaskForm();return false;'>" + lang('cancel') + "</button>";
        }
	
	html += "</td></table>";	
	html += '</div>';
	
	var div = document.createElement('div');
	div.className = 'ogTasksTaskRow';
	div.id = 'ogTasksPanelAT';
	div.innerHTML = html;
	
	var container = document.getElementById(container_id);
	var next = container.nextSibling;
	if (next)
		container.parentNode.insertBefore(div, next);
	else
		container.appendChild(div);
	
	//Create Ext components
	var object_subtypes = ogTasks.ObjectSubtypes;
	var co_types = [];
	for (var i=0; i < object_subtypes.length; i++) {
		co_types.push([object_subtypes[i].id, og.clean(object_subtypes[i].name)]);
	}
	new Ext.form.ComboBox({
		store: new Ext.data.SimpleStore({
       		fields: ["value", "text"],
       		data: co_types
		}),
		id: 'ogTasksPanelObjectTypeSelector',
		valueField: 'value',
		displayField:'text',
                typeAhead: true,
                mode: 'local',
                triggerAction: 'all',
                selectOnFocus:true,
                width:140,
                valueNotFoundText: '',
                applyTo: "ogTasksPanelObjectTypeSelector"
   	});
	if (co_types.length == 0) {
   		document.getElementById('ogTasksPanelATObjectType').style.display = 'none';
   	}

   	var milestoneCombo = bottomToolbar.filterMilestonesCombo.cloneConfig({
		name: 'task[milestone_id]',
		renderTo: 'ogTasksPanelMilestoneSelector',
		id: 'ogTasksPanelATMilestoneCombo',
		hidden: true,
		width: 200,
		value: data.milestoneId,
		tabIndex:1220
	});
	ogTasks.selectedMilestone = data.milestoneId;
	og.openLink(og.getUrl('milestone', 'get_assignable_milestones'), {callback:ogTasks.drawMilestonesCombo});
	
	ogTasks.assignedTo = data.assignedTo ? data.assignedTo : 0;
	og.openLink(og.getUrl('task', 'allowed_users_to_assign'), {callback:ogTasks.drawAssignedToCombo});
	
	document.getElementById('ogTasksPanelATTitle').value = data.title;
	document.getElementById('ogTasksPanelATTitle').focus();
	
	if (data.startDate){
		var date = new Date(data.startDate * 1000);
		date = new Date(Date.parse(date.toUTCString().slice(0, -4)));
		var sd = date.dateFormat(og.preferences['date_format']);
                var starttime = date.dateFormat(og.preferences['time_format_use_24'] == 1 ? 'G:i' : 'g:i A');
	} else sd = '';
	var DtStart = new og.DateField({
		renderTo:'ogTasksPanelATStartDate',
		id:'ogTasksPanelATStartDateCmp',
		style:'width:100px',
		emptyText: og.preferences.date_format_tip,
		tabIndex:1300,
		value: sd
	});
	if (data.dueDate){
		var date = new Date(data.dueDate * 1000);
		date = new Date(Date.parse(date.toUTCString().slice(0, -4)));
		var dd = date.dateFormat(og.preferences['date_format']);
		var duetime = date.dateFormat(og.preferences['time_format_use_24'] == 1 ? 'G:i' : 'g:i A');
	} else dd = '';
	var DtDue = new og.DateField({
		renderTo:'ogTasksPanelATDueDate',
		id:'ogTasksPanelATDueDateCmp',
		style:'width:100px',
		tabIndex:1400,
		value: dd,
		emptyText: og.preferences.date_format_tip,
		listeners: {
			'change': {
				fn: function(due, val, old) {
					if (this.getValue() && this.getValue() > due.getValue()) {
						alert(lang("warning start date greater than due date"));
					}
				},
				scope: DtStart
			}
		}
	});
	DtStart.on('change', function(start, val, old) {
		if (this.getValue() && this.getValue() < start.getValue()) {
			alert(lang("warning start date greater than due date"));
		}
	},
	DtDue);

        if (og.config.use_time_in_task_dates) {
                if(starttime == undefined){
                    var start_time = new Date(og.config.work_day_start_time * 1000);
                    start_time = new Date(Date.parse(start_time.toUTCString().slice(0, -4)));
                    starttime = start_time.dateFormat(og.preferences['time_format_use_24'] == 1 ? 'G:i' : 'g:i A');
                }
		var startTime = new Ext.form.TimeField({
			renderTo:'ogTasksPanelATStartTime',
			id: 'ogTasksPanelATStartTimeCmp',
			width: 80,
                        format: og.config.time_format_use_24_duetime,
			tabIndex: 1420,
			emptyText: 'hh:mm',
			value: starttime
		});
                
                if(duetime == undefined){
                    var date_time = new Date(og.config.work_day_end_time * 1000);
                    date_time = new Date(Date.parse(date_time.toUTCString().slice(0, -4)));
                    duetime = date_time.dateFormat(og.preferences['time_format_use_24'] == 1 ? 'G:i' : 'g:i A');
                }
		var dueTime = new Ext.form.TimeField({
			renderTo:'ogTasksPanelATDueTime',
			id: 'ogTasksPanelATDueTimeCmp',
			width: 80,
                        format: og.config.time_format_use_24_duetime,
			tabIndex: 1420,
			emptyText: 'hh:mm',
			value: duetime
		});
	}
	
	var priorityCombo = bottomToolbar.filterPriorityCombo.cloneConfig({
		name: 'task[priority]',
		renderTo: 'ogTasksPanelATPriorityCont',
		id: 'ogTasksPanelATPriorityCombo',
		hidden: false,
		width: 120,
		value: data.priority,
		tabIndex:1500
	});
}

ogTasks.addNewTaskShowMore = function(){

	document.getElementById('ogTasksPanelATShowMore').style.display = 'none';
	document.getElementById('ogTasksPanelATShowAll').style.display = 'inline';
	
	document.getElementById('ogTasksPanelATDesc').style.display = 'block';
	
	if (document.getElementById('ogTasksPanelATDates'))
		document.getElementById('ogTasksPanelATDates').style.display = 'block';
	
	if (document.getElementById('ogTasksPanelATPriority'))
		document.getElementById('ogTasksPanelATPriority').style.display = 'block';
		
	document.getElementById('ogTasksPanelATAssigned').style.visibility = 'visible';
	document.getElementById('ogTasksPanelATContext').style.display = 'block';
	document.getElementById('ogTasksPanelATMilestone').style.display = 'block';
	if (ogTasks.ObjectSubtypes && ogTasks.ObjectSubtypes.length > 0) {
		document.getElementById('ogTasksPanelATObjectType').style.display = 'block';
	} else {
		document.getElementById('ogTasksPanelATObjectType').style.display = 'none';
	}
	document.getElementById('ogTasksPanelATDesc').focus(); 
}

ogTasks.TaskFormShowAll = function(task_id){
	var params = this.GetNewTaskParameters(false,task_id);
	if (task_id)
		og.openLink(og.getUrl('task', 'edit_task', {id:task_id}), {'post' : params});
	else
		og.openLink(og.getUrl('task', 'add_task'), {'post' : params});
}

ogTasks.hideAddNewTaskForm = function(){
	var oldForm = document.getElementById('ogTasksPanelAT');
	if (oldForm)
		oldForm.parentNode.removeChild(oldForm);
}

ogTasks.GetNewTaskParameters = function(wrapWith,task_id){
	var parameters = [];

	//Conditional fields
	var parentField = document.getElementById('ogTasksPanelATParentId');
	if (parentField)
		parameters["parent_id"] = parentField.value;
	
	var hoursPanel = document.getElementById('ogTasksPanelATHours');
	if (hoursPanel)
		parameters["hours"] = hoursPanel.value;
            
        var minutePanel = document.getElementById('ogTasksPanelATMinutes');
	if (minutePanel)
		parameters["minutes"] = minutePanel.value;
	
	var startPanel = Ext.getCmp('ogTasksPanelATStartDateCmp');
	if (startPanel && startPanel.getValue() != ''){
		parameters["task_start_date"] = startPanel.getValue().format(og.preferences['date_format']);
                var startTimePanel = Ext.getCmp('ogTasksPanelATStartTimeCmp');
		if (startTimePanel && startTimePanel.getValue() != '') {
			parameters["task_start_time"] = startTimePanel.getValue();
		}
        }
	
	var duePanel = Ext.getCmp('ogTasksPanelATDueDateCmp');
	if (duePanel && duePanel.getValue() != '') {
		parameters["task_due_date"] = duePanel.getValue().format(og.preferences['date_format']);
		var dueTimePanel = Ext.getCmp('ogTasksPanelATDueTimeCmp');
		if (dueTimePanel && dueTimePanel.getValue() != '') {
			parameters["task_due_time"] = dueTimePanel.getValue();
		}
	}
		
	var notify = document.getElementById('ogTasksPanelATNotify');
	if (notify && notify.style.display != 'none' && notify.checked)
		parameters["notify"] = true;
	else
		parameters["notify"] = false;
            
        if(og.config.wysiwyg_tasks){
            var editor = og.getCkEditorInstance(og.genid + 'ckeditor' + task_id);
            parameters["text"] = editor.getData();
        }else{
            var description = document.getElementById('ogTasksPanelATDescCtl');
            if (description)
		parameters["text"] = description.value;
        }
	
	var applyMI = document.getElementById('ogTasksPanelApplyMI');
	parameters["apply_milestone_subtasks"] = applyMI && applyMI.checked ? "checked" : "";
	
	var applyWS = document.getElementById('ogTasksPanelApplyWS');
	parameters["apply_ws_subtasks"] = applyWS && applyWS.checked ? "checked" : "";
	
	var applyAT = document.getElementById('ogTasksPanelApplyAssignee');
	parameters["apply_assignee_subtasks"] = applyAT && applyAT.checked ? "checked" : "";
	
	var milestones_combo = Ext.getCmp('ogTasksPanelATMilestoneCombo');
	var milestone_id = milestones_combo ? milestones_combo.getValue() : (milestone_hf = document.getElementById('task_milestone_id') ? milestone_hf.value : null);
	if (milestone_id) parameters["milestone_id"] = milestone_id;
	
	//Always visible
	parameters["assigned_to_contact_id"] = Ext.getCmp('ogTasksPanelATUserCompanyCombo').getValue();
	parameters["priority"] = Ext.getCmp('ogTasksPanelATPriorityCombo').getValue();
	parameters["name"] = document.getElementById('ogTasksPanelATTitle').value;
	parameters["object_subtype"] = Ext.getCmp('ogTasksPanelObjectTypeSelector').getValue();
	
	//parameters["members"] = document.getElementById('ogTasksPanelMembers').value;
	if (member_input = document.getElementById('ogTasksPanelATMemberId')) {
		parameters["member_id"] = member_input.value;
	}
        
        //multi_assignment
        if(og.config.multi_assignment == 1){
            if(typeof window.loadMultiAssignmentHtml == 'function'){                
                var applyChange = document.getElementById(og.genid + 'multi_assignment_aplly_change');
                if (applyChange)
                        parameters["multi_assignment_aplly_change"] = applyChange.value;

                var multi_assignment = {};
                var assigned_to_contact_id = new Array();
                var name = new Array();
                var time_estimate_hours = new Array();
                var time_estimate_minutes = new Array();
                var pos = 1;
                var line = 0;
                $("#" + og.genid + "multi_assignment :input").each(function(){
                    if(pos == 1){
                        assigned_to_contact_id[line] = $(this).val();
                    }else if(pos == 3){
                        name[line] = $(this).val();
                    }else if(pos == 4){
                        time_estimate_hours[line] = $(this).val();
                    }else if(pos == 5){
                        time_estimate_minutes[line] = $(this).val();
                    }

                    if(pos == 5){
                        pos = 1;
                        line++;
                    }else{
                        pos++;    
                    }
                });

                for (i=0; i < line; i++) {
                    multi_assignment[i] = {
                                                assigned_to_contact_id : assigned_to_contact_id[i],
                                                name : name[i],
                                                time_estimate_hours : time_estimate_hours[i],
                                                time_estimate_minutes : time_estimate_minutes[i]
                                            };
                }
            }
        }
        
	if (wrapWith) {
		var params2 = [];
		for (var i in parameters) {
			if (parameters[i] || parameters[i] === 0) {
				params2[wrapWith + "[" + i + "]"] = parameters[i];
			}
		}
                if(og.config.multi_assignment == 1){
                    if(typeof window.loadMultiAssignmentHtml == 'function'){ 
                        params2["multi_assignment"] = Ext.util.JSON.encode(multi_assignment);
                    }
                }
		return params2;
	} else {
                if(og.config.multi_assignment == 1){
                    if(typeof window.loadMultiAssignmentHtml == 'function'){ 
                        parameters["multi_assignment"] = Ext.util.JSON.encode(multi_assignment);
                    }
                }
		return parameters;
	}
}

ogTasks.SubmitNewTask = function(task_id,view_popup){
	var parameters = this.GetNewTaskParameters('task',task_id);
        var url = '';
	if (task_id > 0) {
                if(view_popup){
                        var related = og.checkRelated("task",task_id);
                        if(related){
                            this.dialog = new og.TaskPopUp("edit",task_id);
                            this.dialog.setTitle(lang('tasks related'));	                                
                            this.dialog.show();
                            return false;
                        }else{
                            url = og.getUrl('task', 'quick_edit_task', {id:task_id});
                        }
                } else {
                        var opt = $("#" + og.genid + "type_related").val();
                        parameters["type_related"] = opt;
                        url = og.getUrl('task', 'quick_edit_task', {id:task_id});
                }
		
	} else {
		url = og.getUrl('task', 'quick_add_task');
	}

	og.openLink(url, {
		method: 'POST',
		post: parameters,
		callback: function(success, data) {
			if (success && ! data.errorCode) {
				var task = this.getTask(data.task.id);
				if (!task){
					var task = new ogTasksTask();
					task.setFromTdata(data.task);
					if (data.task.s) {
						task.statusOnCreate = data.task.s;
					}
					task.isCreatedClientSide = true;
					this.Tasks[this.Tasks.length] = task;
					var parent = this.getTask(task.parentId);
					if (parent){
						task.parent = parent;
						parent.subtasks[parent.subtasks.length] = task;
					}
                                        
                                        if (data.subtasks) {
                                                for (i=0; i<data.subtasks.length; i++) {
                                                        var task = new ogTasksTask();
                                                        task.setFromTdata(data.subtasks[i]);
                                                        if (data.subtasks[i].s) {
                                                                task.statusOnCreate = data.subtasks[i].s;
                                                        }
                                                        task.isCreatedClientSide = true;
                                                        this.Tasks[this.Tasks.length] = task;
                                                        var parent = this.getTask(task.parentId);
                                                        if (parent){
                                                                task.parent = parent;
                                                                parent.subtasks[parent.subtasks.length] = task;
                                                        }
                                                }
                                        }
                                        
				} else {
					task.setFromTdata(data.task);
				}
				
				if (data.subtasks) {
					for (i=0; i<data.subtasks.length; i++) {
						var subtask = this.getTask(data.subtasks[i].id);
						if (subtask) {
							subtask.setFromTdata(data.subtasks[i]);
						}
					}
				}
				this.redrawGroups = false;
				this.draw();
				this.redrawGroups = true;
			} else {
				if (!data.errorMessage || data.errorMessage == '') {
					og.err(lang("error adding task"));
				}
			}
		},
		scope: this
	});
}


ogTasks.buildAssignedToComboStore = function(companies, only_me) {
	var usersStore = [];
	var comp_array = [];
	var cantU = 0;
	var cantC = 0;
	
	if (!only_me) {
		usersStore[cantU++] = ['0', lang('dont assign')];
		usersStore[cantU++] = ['0', '--'];
	}
	
	if (companies) {
		for (i=0; i<companies.length; i++) {
			comp = companies[i];
			if (!only_me && comp.id > 0) {
				comp_array[cantC++] = [comp.id, og.clean(comp.name)];
			}
			for (j=0; j<comp.users.length; j++) {
				usr = comp.users[j];
				if (!only_me) {
					usersStore[cantU++] = [usr.id, og.clean(usr.name)];
				}
				if (usr.isCurrent) usersStore.unshift([usr.id, lang('me')]);
			}
		}
	}        
	usersStore = usersStore.concat(comp_array);
		
	return usersStore;
}

ogTasks.buildMilestonesComboStore = function(ms) {
	var milestonesData = [[0,"--" + lang('none') + "--"]];
    for (i in ms){
    	if (ms[i].id)
    		milestonesData[milestonesData.length] = [ms[i].id, ms[i].t];
    }
	return milestonesData;
}

ogTasks.drawAssignedToCombo = function(success, data) {
	var only_me = data.only_me ? data.only_me : null;
	var usersStore = ogTasks.buildAssignedToComboStore(data.companies, only_me);
	var prev_combo = Ext.get('ogTasksPanelATUserCompanyCombo');
	if (prev_combo) prev_combo.remove();
		
	var namesCombo = new Ext.form.ComboBox({
		name: 'task[assigned_to]',
		renderTo: 'ogTasksPanelATAssignedCont',
		id: 'ogTasksPanelATUserCompanyCombo',
		store: usersStore,
		hidden: false,
		width: 210,
		displayField:'text',
        typeAhead: true,
        mode: 'local',
        triggerAction: 'all',
        selectOnFocus:true,
        value: ogTasks.assignedTo,
		emptyText: (lang('select user or group') + '...'),
	    valueNotFoundText: '',
		tabIndex:1200,
		listeners: {
			'select':function(combo, record){
				var checkbox = document.getElementById('ogTasksPanelATNotify');
				if (checkbox){
					var checkboxDiv = document.getElementById('ogTasksPanelATNotifyDiv');
					var user = ogTasks.getUser(record.data.value);
					if (user && record.data.value != '-1' && record.data.value != '0'){
						checkboxDiv.style.display = 'block';
						var currentUser = ogTasks.currentUser;
						if (ogTasks.userPreferences.defaultNotifyValue == 1) {
							checkbox.checked = (record.data.value != (currentUser.id));
						} else {
							checkbox.checked = false;
						}
						ogTasks.assignedTo = combo.getValue();
					} else {
						checkboxDiv.style.display = 'none';
						checkbox.checked = false;
					}
				}
			}
		}
	});
}

ogTasks.drawMilestonesCombo = function(success, data) {
	var bottomToolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
	mStore = ogTasks.buildMilestonesComboStore(data.milestones);
	prev_combo = Ext.get('ogTasksPanelATMilestoneCombo');
	if (prev_combo) {
		m_val = prev_combo.getValue();
		var found = false;
		for (i in mStore) {
			if (mStore[i][1] == m_val) {
				ogTasks.selectedMilestone = mStore[i][0];
				found = true;
				break;
			}
		}
		if (!found) ogTasks.selectedMilestone = 0;
		prev_combo.remove();
	}

	var milestoneCombo = bottomToolbar.filterMilestonesCombo.cloneConfig({
		name: 'task[milestone_id]',
		renderTo: 'ogTasksPanelMilestoneSelector',
		id: 'ogTasksPanelATMilestoneCombo',
		store: mStore,
		hidden: false,
		width: 200,
		value: ogTasks.selectedMilestone,
		tabIndex:1220
	});
}
