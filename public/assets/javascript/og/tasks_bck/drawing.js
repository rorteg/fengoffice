/**
 * drawing.js
 *
 * This module holds the rendering logic for groups and tasks
 *
 * @author Carlos Palma <chonwil@gmail.com>
 */

//************************************
//*		<RX : dragging
//************************************

var rx__dd = 1000;
rx__TasksD = Ext.extend(Ext.dd.DDProxy, {
startDrag: function(x, y) {
	var dragEl = Ext.get(this.getDragEl());
	var el = Ext.get(this.getEl());
	
	if (!Ext.isIE) dragEl.applyStyles({'border':'1px solid gray;','border-width':'1px 1px 1px 6px','width':'auto','height':'auto','cursor':'move'});
	else dragEl.setWidth('auto');
	var task = ogTasks.getTask(this.config.dragData.i_t);	
	dragEl.update(task.title);
	dragEl.addClass(el.dom.className + ' RX__tasks_dd-proxy'); 
},
onDragOver: function(e, targetId) {
    var target = Ext.get(targetId);
	if(targetId.indexOf(rx__TasksDrag.idGroup)>=0) /* group */ {
        this.lastTargetId = targetId;		
		this.lastGroupTargetId = targetId;
        target.addClass('RX__tasks_dd-over');
	}else if(targetId.indexOf(rx__TasksDrag.idTask)>=0) /* task */ {
        this.lastTargetId = targetId;				
        target.addClass('RX__tasks_dd-over');
	}else{
		//XXX: mark wrong target, check other options
	}
},
onDragOut: function(e, targetId) {
    var target = Ext.get(targetId);
	if(targetId.indexOf(rx__TasksDrag.idGroup)>=0) /* group */ {
        this.lastTargetId = ''; //targetId;		
        target.removeClass('RX__tasks_dd-over');
	}else if(targetId.indexOf(rx__TasksDrag.idTask)>=0) /* task */ {
        this.lastTargetId = this.lastGroupTargetId;				
        target.removeClass('RX__tasks_dd-over');
	}else{
		//XXX: mark wrong target, check other options
	}
},
endDrag: function() {
    var dragEl = Ext.get(this.getDragEl());
    var el = Ext.get(this.getEl());
	if(this.lastGroupTargetId) 
		Ext.get(this.lastGroupTargetId).removeClass('RX__tasks_dd-over');
	if(this.lastTargetId) 
		Ext.get(this.lastTargetId).removeClass('RX__tasks_dd-over');
		
	var targetId = this.lastTargetId;
	rx__TasksDrag.d = rx__TasksDrag.haveExtDD[this.lastGroupTargetId];
	rx__TasksDrag.p = rx__TasksDrag.haveExtDD[this.lastTargetId];
	rx__TasksDrag.t = this.config.dragData.i_t;
	rx__TasksDrag.g = this.config.dragData.i_g;
	this.lastTargetId = null;
	this.lastGroupTargetId = null;
	
	var doProcess = false;
	if (targetId) {
		if(targetId.indexOf(rx__TasksDrag.idGroup)>=0) /* group */ {
			doProcess = true;
			rx__TasksDrag.p = false;
		}else if(targetId.indexOf(rx__TasksDrag.idTask)>=0) /* task */ {
			doProcess = true;
		}else{
			//XXX: mark wrong target
		}
	}
	
	if(doProcess) {
		rx__TasksDrag.process();
		/*/ alert('From '+rx__TasksDrag.g+'.'+rx__TasksDrag.t+' to '+rx__TasksDrag.d+'.'+rx__TasksDrag.p+' ('+rx__TasksDrag.displayCriteria.group_by+')'); /* */
		//alert(dump(ogTasks.Groups));
	}else{
		//alert(targetId);
	}
}
});

function dump(arr,level) {
	var dumped_text = "";
	if(!level) level = 0;
	
	//The padding given at the beginning of the line.
	var level_padding = "";
	for(var j=0;j<level+1;j++) level_padding += "    ";
	
	if(typeof(arr) == 'object') { //Array/Hashes/Objects
		 for(var item in arr) {
			  var value = arr[item];
			 
			  if(typeof(value) == 'object') { //If it is an array,
				   dumped_text += level_padding + "'" + item + "' ...\n";
				   dumped_text += dump(value,level+1);
			  } else {
	 			   dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
			  }
		 }
	} else { //Stings/Chars/Numbers etc.
	 dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
	}
	return dumped_text;
} 

var rx__TasksDrag = {
	t: false,
	g: false,
	d: false,
	p: false,
	// (g::t)-->(d::p)
	displayCriteria: '',
	allowDrag: false,
	state: 'no',
	haveExtDD: {},
	full_redraw: false,

	classGroup: 'ogTasksGroup', //'ogTasksGroupHeader',
	idGroup: 'ogTasksPanelGroupCont', // 'ogTasksPanelGroup',
	classTask: 'ogTasksTaskTable',
	idTask: 'ogTasksPanelTaskTable',
	ddGroup: 'WorkspaceDD', // group
	dzClass: 'rx__hasDZ',
	
	initialize: function() {
		this.haveExtDD = {};
	},
	prepareExt: function(t,g,id) {
		if(this.haveExtDD[id]) return;
		Ext.get(id).dd = new rx__TasksD(id, rx__TasksDrag.ddGroup, { scope: this, dragData: {i_t:t, i_g: g} });
		new Ext.dd.DropZone(id, {ddGroup: rx__TasksDrag.ddGroup});
		this.haveExtDD[id] = t; // true
		this.prepareDrops();
	},
	prepareDrops: function() {
		Ext.select('.'+rx__TasksDrag.classGroup).each( function(el) {
			if(el.hasClass(rx__TasksDrag.dzClass)) return;
			el.addClass(rx__TasksDrag.dzClass);
			id = el.dom.id;
			new Ext.dd.DropZone(id, {ddGroup: rx__TasksDrag.ddGroup});
			d = id.substr(rx__TasksDrag.idGroup.length, 66);
			rx__TasksDrag.haveExtDD[id] = d;
		} );
		Ext.select('.'+rx__TasksDrag.classTask).each( function(el) {
			if(el.hasClass(rx__TasksDrag.dzClass)) return;
			el.addClass(rx__TasksDrag.dzClass);
			id = el.dom.id;
			new Ext.dd.DropZone(id, {ddGroup: rx__TasksDrag.ddGroup});
			d = new String( id.substr(rx__TasksDrag.idTask.length, 66) );
			d = d.substr(1,d.indexOf('G')-1); // format: T{task_id}G{group_id}
			rx__TasksDrag.haveExtDD[id] = d;
		} );
	},
	prepareDrop: function(d,id) {
		if(this.haveExtDD[id]) return;
		/*Ext.get(id).dd =*/ new Ext.dd.DropZone(id, {ddGroup: rx__TasksDrag.ddGroup});
		this.haveExtDD[id] = d;
	},
	parametersFromTask: function(task) {
		var parameters = [];
		/* // optional //
		parameters["parent_id"] = parentField.value;
		parameters["hours"] = hoursPanel.value;
		parameters["task_start_date"] = startPanel.getValue().format(lang('date format'));
		parameters["task_due_date"] = duePanel.getValue().format(lang('date format'));
		parameters["notify"] = true;
		parameters["text"] = description.value;		*/
		
		// mandatory
		parameters["assigned_to"] = task.assignedToId;
		parameters["milestone_id"] = task.milestoneId;
		parameters["priority"] = task.priority;
		parameters["title"] = task.title;
		parameters["project_id"] = task.workspaceIds;
		parameters["tags"] = task.tags;
		
		// add dates to parameters
		if (task.dueDate) {
			var d1 = new Date();
			d1.setTime(task.dueDate * 1000);
			parameters["task_due_date"] = d1.format(og.preferences['date_format']);
		}
		if (task.startDate) {
			var d2 = new Date();
			d2.setTime(task.startDate * 1000);
			parameters["task_start_date"] = d2.format(og.preferences['date_format']);
		}
		
		return parameters;
	},
	quickEdit: function(task_id, parameters) {
		// wrap
		var params2 = [];
		for (var i in parameters)
			if (parameters[i] || parameters[i] === 0)
				params2["task[" + i + "]"] = parameters[i];
		/*alert('Updating (quick edit) #'+this.t+' with '+dump(params2));/**/
		/*return params2;		*/
		if (parameters['milestone_id'] > 0){
			var milestone = ogTasks.getMilestone(parameters['milestone_id']);
			var task = ogTasks.getTask(task_id);
			
			if (this.displayCriteria.group_by == 'milestone' && milestone.id != task.milestoneId){//Milestone changed
				if (milestone.workspaceIds != task.workspaceIds)
					if(!og.IsWorkspaceParentOf(milestone.workspaceIds, task.workspaceIds)){
						if (!confirm(lang('task milestone workspace inconsistency')))
							return;
					}
			}
			
			// Workspace changed -> milestone control, assigned control
			if (this.displayCriteria.group_by == 'workspace' && milestone.workspaceIds != parameters['project_id']) { 
				if (!og.IsWorkspaceParentOf(milestone.workspaceIds, parameters['project_id'])) {
					if (!confirm(lang('task milestone does not belong to workspace'))) {
						return;
					} else {
						params2["task[milestone_id]"] = 0;
					}
				}
				/*if (!og.canAssignTask(parameters['project_id'], task.assignedToId)) {
					if (!confirm(lang('task cant be assigned to current user'))) {
						return;
					} else {
						params2["task[assigned_to]"] = 0;
					}
				}*/
				alert(task.assignedToId);
			}
		}

		
		parameters = params2;
		var url = og.getUrl('task', 'quick_edit_task', {id:task_id, dont_mark_as_read:1});
	
		og.openLink(url, {
			method: 'POST',
			post: parameters,
			callback: function(success, data) {
				if (success && ! data.errorCode) {
					var task = ogTasks.getTask(data.task.id);
					if (!task){
						var task = new ogTasksTask();
						task.setFromTdata(data.task);
						if (data.task.s)
							task.statusOnCreate = data.task.s;
						task.isCreatedClientSide = true;
						ogTasks.Tasks[ogTasks.Tasks.length] = task;
						var parent = ogTasks.getTask(task.parentId);
						if (parent){
							task.parent = parent;
							parent.subtasks[parent.subtasks.length] = task;
						}
					} else {
						task.setFromTdata(data.task);
						var parent = ogTasks.getTask(task.parentId);
						if (parent){
							task.parent = parent;
							parent.subtasks[parent.subtasks.length] = task;
						}
					}
					
					if (data.subtasks && data.subtasks.length > 0)
						ogTasks.setSubtasksFromData(task, data.subtasks);
					
					if(!rx__TasksDrag.full_redraw) ogTasks.redrawGroups = false;
					else rx__TasksDrag.full_redraw = true;
					ogTasks.draw();
					ogTasks.redrawGroups = true;
					rx__TasksDrag.haveExtDD = {};
				} else {
					if (!data.errorMessage || data.errorMessage == '')
						og.err(lang("error adding task"));
				}
			},
			scope: ogTasks
		});
		
	},
	process: function() {
		var task = ogTasks.getTask(this.t);
		this.p = parseInt(this.p);
		
		// non-edits
		if (this.g == this.d && !this.p) {
			// task is being dragged from group #G to group #G
			if (task.parentId != 0) {
				// however, the intention might be to un-attach the task from its parent (!)
				this.p = 0;
			}else
				return;
		}
		if (task.parentId == this.d && task.parentId) // is the task being dragged as a subtask o its own parent?
			return;

		// check for unwanted cycles - #t cannot be a predecessor of #p 
		var ti = this.p; var tiQ={};
		while(ti!=0 && !tiQ[ti]) {
			/*alert('Checking if #'+ti+' is '+this.t);*/
			if(ti==this.t) return;
			var tt = ogTasks.getTask(ti);
			if(!tt) break;
			tiQ[ti]=1; // loop protection - mark visited vertices
			ti = tt.parentId;
		}
		
		// unattach from current parent
		if(task.parentId) {
			// delete task #t from the list of its parent subtasks 
			var parent = ogTasks.getTask(task.parentId);
			for(var i=parent.subtasks.length; i-->0;) 
				if(parent.subtasks[i].id == this.t)
				{
					parent.subtasks.splice(i,1);
					break;
				}
			// change task #t parent to #0
			for (var i = 0; i < ogTasks.Tasks.length; i++)
				if (ogTasks.Tasks[i].id == this.t) {
					ogTasks.Tasks[i].parentId = 0;
					ogTasks.Tasks[i].parent = null;
					break;
				}

		}
		
		var parameters = this.parametersFromTask(task);
		
		// special edits
		switch(this.displayCriteria.group_by) {
			case 'status': ogTasks.ToggleCompleteStatus(task.id, 1-this.d); return; break;
			default:
		}

		parameters['parent_id'] = this.p?this.p:0;
		parameters['apply_ws_subtasks'] = "checked";
		parameters['apply_milestone_subtasks'] = "checked";
	
		var group = ogTasks.getGroup(this.d);
		var group_not_empty = group && group.group_tasks && group.group_tasks.length>0;
		
		// change
		switch (this.displayCriteria.group_by){
			case 'milestone':	parameters["milestone_id"] = this.d!='unclassified'? ogTasks.getMilestone(this.d).id : 0; break;
			case 'priority':	parameters["priority"] = this.d!='unclassified'? parseInt(this.d) : 200; /*100,200,300*/ break;
			case 'assigned_to':	parameters["assigned_to"] = this.d; /* 1:1 */ break;
			case 'due_date' : 	if(group_not_empty) parameters["task_due_date"] = group.group_tasks[0].dueDate; break;
			case 'start_date' : if(group_not_empty) parameters["task_start_date"] = group.group_tasks[0].startDate; break;
			case 'created_on' : if(group_not_empty) parameters["created_on"] = group.group_tasks[0].createdOn; break;
			case 'completed_on':if(group_not_empty) parameters["completed_on"] = group.group_tasks[0].completedOn.toString().format(lang('date format')); break;
			case 'created_by' :	parameters["created_by"] = this.d; /* ? */ break;
			case 'status' : 	parameters["status"] = this.d; /* done previously, special request */ break;
			case 'completed_by':parameters["completed_by"] = this.d; /* ? */ break;
			case 'subtype':parameters["object_subtype"] = this.d; /* ? */ break;
			case 'tag':			{
				if(this.d=='unclassified') /* remove single tag */ {
					//var untag = ogTasks.Groups[this.g].group_id; // group id (=tag name) from group index
					var untag = new String(this.g);
					s = new String(task.tags);
					if(!task.tags) s='';
					i = s.indexOf(untag);
					if(i>=0) s = s.substr(0,i)+s.substr(i+1+untag.length);
					parameters['tags'] = s;
				}else /* add single tag */ {
					if(!task.tags || task.tags=='null')
						parameters['tags'] = this.d;
					else parameters['tags'] += ', '+this.d;
				}
			} break;
			case 'workspace':	parameters["project_id"] = this.d; /* ? */ break;
			default:
		}
		
		rx__TasksDrag.full_redraw = true;
		task_id = this.t;
		this.quickEdit(task_id, parameters);
		
	},
	onDragStart: function(t,g,id) {
		return false;
		/*if(this.state!='no') return false;
		this.t=t;
		this.g=g;
		this.state = 'md';
		return false;*/
	},
	last_oDO_e: null,
	markCursor: function(e,d) {
		if(this.last_oDO_e)
			this.last_oDO_e.style.cursor = 'auto';
		if(e)
			e.style.cursor = (d==this.g?'not-allowed':'crosshair')+' !important';
		this.last_oDO_e = e;
	},
	onDragOver: function(e,d) {
		if(this.state!='md') return false;
		if(this.last_oDO_e==e) return false;
			else this.markCursor(e,d);
		return false;
	},
	onDrop: function(d) {
		if(this.state!='md') return false;
		this.markCursor(null,d);
		this.d=d;
		this.state = 'no';
		//alert('From '+this.g+'.'+this.t+' to '+this.d);
		return false;
	},
	showHandle: function(id,v) {
		if(!rx__TasksDrag.allowDrag || og.loggedUser.isGuest) return;
		var o = document.getElementById('RX__ogTasksPanelDrag'+id);
		var ine = Ext.get('ogTasksPanelAT');
		if(ine) if(ine.isVisible()) v = false;
		if(o) o.style.visibility = v?'visible':'hidden';
	}
};


//************************************
//*		Main function
//************************************

ogTasks.draw = function(){
	var start = new Date(); 
	if (this.redrawGroups)
		this.Groups = [];
	for (var i = 0; i < this.Tasks.length; i++)
		this.Tasks[i].divInfo = [];

	var bottomToolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
	var topToolbar = Ext.getCmp('tasksPanelTopToolbarObject');
	var displayCriteria = bottomToolbar.getDisplayCriteria();
	var drawOptions = topToolbar.getDrawOptions();
	this.Groups = this.groupTasks(displayCriteria, this.Tasks);
	for (var i = 0; i < this.Groups.length; i++){
		this.Groups[i].group_tasks = this.orderTasks(displayCriteria, this.Groups[i].group_tasks);
	}
	
	// *** <RX ***
	rx__TasksDrag.displayCriteria = displayCriteria;
	rx__TasksDrag.allowDrag = false;
	if(displayCriteria.group_by=='milestone' || displayCriteria.group_by=='priority' 
	|| displayCriteria.group_by=='assigned_to' || displayCriteria.group_by=='status' || displayCriteria.group_by=='subtype' 
	|| displayCriteria.group_by=='tag' || displayCriteria.group_by=='workspace') rx__TasksDrag.allowDrag = true;
	// *** /RX ***
	
	//Drawing
	var sb = new StringBuffer();
	for (var i = 0; i < this.Groups.length; i++){
		if (i != (this.Groups.length-1) || this.Groups[i].group_tasks.length > 0) { //If there are no unclassified or unassigned tasks, do not show unassigned group
			if (ogTasks.userPreferences.showEmptyMilestones == 0 && displayCriteria.group_by == 'milestone' && this.Groups[i].group_tasks.length == 0) continue;
			sb.append(this.drawGroup(displayCriteria, drawOptions, this.Groups[i]));
		}
	}
	
	// *** <RX ***
	if(this.Groups.length==1 && this.Groups[0].group_tasks.length==0) { // there are no tasks to display
		sb.append('<div class="inner-message" style="text-align: center; color: gray; font-size: 14px;">'+lang('no tasks to display')+ '</div>'+
		'<div id="rx__no_tasks_info" style="text-align: center; "><a href="#" class="internalLink ogTasksGroupAction ico-add" '+
		'onClick="document.getElementById(\'rx__no_tasks_info\').style.display=\'none\'; document.getElementById(\'rx__hidden_group\').style.display=\'block\'; ogTasks.drawAddNewTaskForm(\'' + this.Groups[0].group_id + '\')" '+
		'title="' + lang('add task') + '">' + (lang('add task')) + '</a>'+
		'</div>');
		var rx__hidden_group = new String();
		rx__hidden_group = this.drawGroup(displayCriteria, drawOptions, this.Groups[0]);
		rx__hidden_group = '<div id="rx__hidden_group" style="display: none;">'+rx__hidden_group+'</div>';
		sb.append(rx__hidden_group);
	}
	// *** /RX ***
	
	var container = document.getElementById('tasksPanelContainer');
	sb.append("<div style='height:20px'></div>")
	container.innerHTML = sb.toString();
	og.showWsPaths('tasksPanelContainer');
}

ogTasks.toggleSubtasks = function(taskId, groupId){
	var subtasksDiv = document.getElementById('ogTasksPanelSubtasksT' + taskId + 'G' + groupId);
	var expander = document.getElementById('ogTasksPanelFixedExpanderT' + taskId + 'G' + groupId);
	var task = this.getTask(taskId);
	if (subtasksDiv){
		task.isExpanded = !task.isExpanded;
		subtasksDiv.style.display = (task.isExpanded)? 'block':'none';
		expander.className = "og-task-expander " + ((task.isExpanded)?'toggle_expanded':'toggle_collapsed');
	}
}



//************************************
//*		Draw group
//************************************

ogTasks.drawMilestoneCompleteBar = function(group){
	var html = '';
	var milestone = this.getMilestone(group.group_id);
	if (!milestone) return html;
	var complete = 0;
	var completedTasks = milestone.completedTasks;
	var totalTasks =  milestone.totalTasks;
	var tasks = this.flattenTasks(group.group_tasks);
	for (var i = 0; i < tasks.length; i++){
		var t = tasks[i];
		if (t.milestoneId == group.group_id){
			completedTasks += (t.status == 1 && (t.statusOnCreate == 0))? 1:0;
			completedTasks -= (t.status == 0 && (t.statusOnCreate == 1))? 1:0;
			totalTasks += (t.isCreatedClientSide)? 1:0;
		}
	}
	if (totalTasks > 0)
		complete = ((100 * completedTasks) / totalTasks);
	html += "<table><tr><td style='padding-left:15px;padding-top:5px'>" +
	"<table style='height:7px;width:50px'><tr><td style='height:7px;width:" + (complete) + "%;background-color:#6C2'></td><td style='width:" + (100 - complete) + "%;background-color:#DDD'></td></tr></table>" +
	"</td><td style='padding-left:3px;line-height:12px'><span style='font-size:8px;color:#AAA'>(" + completedTasks + '/' +  totalTasks + ")</span></td></tr></table>";

	return html;			
}

ogTasks.drawGroup = function(displayCriteria, drawOptions, group){
	var sb = new StringBuffer();
	
		// **** <RX : dragging **** //
	//sb.append('<script>rx__TasksDrag.prepareDrop(\"" + group.group_id + "\",this.id);</scr'+'ipt>');
	//rx__TasksDrag.haveExtDD['ogTasksPanelGroupCont'+group.group_id] = group.group_id;
	sb.append("<div id='ogTasksPanelGroupCont" + group.group_id + "' class='ogTasksGroup' style='display:" + ((this.existsSoloGroup() && !group.solo)? 'none':'block') + "'><div id='ogTasksPanelGroup" + group.group_id + "' class='ogTasksGroupHeader' onmouseover='ogTasks.mouseMovement(null,\"" + group.group_id + "\",true)' onmouseout='ogTasks.mouseMovement(null,\"" + group.group_id + "\", false)'>");
	sb.append("<table width='100%'><tr>");
	sb.append('<td style="width:20px"><div onclick="ogTasks.expandCollapseAllTasksGroup(\'' + group.group_id + '\')" class="og-task-expander toggle_expanded" id="ogTasksPanelGroupExpanderG' + group.group_id + '"></div></td>');
	sb.append('<td style="width:20px" title="'+lang('select all tasks')+'"><input style="width:14px;height:14px" type="checkbox" id="ogTasksPanelGroupChk' + group.group_id + '" ' + (group.isChecked?'checked':'') + ' onclick="ogTasks.GroupSelected(this,\'' + group.group_id + '\')"/></td>');
	
	sb.append("<td width='20px'><div class='db-ico " + group.group_icon + "'></div></td>");
	
	sb.append('<td>');
	switch (displayCriteria.group_by){
		case 'milestone':
			var milestone = this.getMilestone(group.group_id);
			if (milestone){
				if (milestone.isUrgent){
					sb.append("</td><td><div class='db-ico ico-urgent-milestone'></div></td><td>");
				}
				sb.append("<table><tr><td><div class='ogTasksGroupHeaderName'>");
				if (milestone.completedById){
					var user = this.getUser(milestone.completedById);
					var tooltip = '';
					if (user){
						var time = new Date(milestone.completedOn * 1000);
						var now = new Date();
						var timeFormatted = time.getYear() != now.getYear() ? time.dateFormat('M j, Y'): time.dateFormat('M j');
						tooltip = lang('completed by name on', og.clean(user.name), timeFormatted).replace(/'\''/g, '\\\'');
					}
					sb.append("<a href='#' style='text-decoration:line-through' class='internalLink' onclick='og.openLink(\"" + og.getUrl('milestone', 'view', {id: group.group_id}) + "\")' title='" + tooltip + "'>" + og.clean(group.group_name) + '</a></div></td>');
				}
				else
					sb.append("<a href='#' class='internalLink' onclick='og.openLink(\"" + og.getUrl('milestone', 'view', {id: group.group_id}) + "\")'>" + og.clean(group.group_name) + '</a></div></td>');
				
				if (drawOptions.show_workspaces){
					var ids = String(milestone.workspaceIds).split(',');
					var projectsString = "<td style='padding-left:10px'>";
					projectsString += '<span class="project-replace">' + ids.join(',') + '</span>&nbsp;';
					sb.append(projectsString + "</td>");
				}
			} else {
				sb.append("<table><tr><td><div class='ogTasksGroupHeaderName'>" + og.clean(group.group_name) + '</div></td>');
			}
			sb.append("</tr></table>");
			break;
		default:
			sb.append("<div class='ogTasksGroupHeaderName'>" + og.clean(group.group_name) + '</div>');
	}
	sb.append("</td><td align='right'>");
	if (displayCriteria.group_by == 'milestone' && this.getMilestone(group.group_id)){
		var milestone = this.getMilestone(group.group_id);
		sb.append("<table><tr>");
		if (drawOptions.show_dates){
			sb.append('<td><span style="padding-left:12px;color:#888;">');
			var date = new Date(milestone.dueDate * 1000);
			var now = new Date();
			var dateFormatted = date.getYear() != now.getYear() ? date.dateFormat('M j, Y'): date.dateFormat('M j');
			if (milestone.completedById > 0){
				sb.append('<span style="text-decoration:line-through">' +  lang('due') + ':&nbsp;' + dateFormatted + '</span>');
			} else {
				if ((date < now))
					sb.append('<span style="font-weight:bold;color:#F00">' + lang('due') + ':&nbsp;' + dateFormatted + '</span>');
				else
					sb.append(lang('due') + ':&nbsp;' + dateFormatted);
			}
			sb.append('</span></td>');
		}
		sb.append("<td><div id='ogTasksPanelCompleteBar" + group.group_id + "'>" + this.drawMilestoneCompleteBar(group) + "</div></td>");
		sb.append("<td><div class='ogTasksGroupHeaderActions' style='visibility:hidden;padding-left:15px' id='ogTasksPanelGroupActions" + group.group_id + "'>" + this.drawGroupActions(group) + '</div></td></tr></table>');
	} else
		sb.append("<div class='ogTasksGroupHeaderActions' style='visibility:hidden' id='ogTasksPanelGroupActions" + group.group_id + "'>" + this.drawGroupActions(group) + '</div>');
	sb.append('</td></tr></table></div>');
	
	sb.append("<div id='ogTasksPanelTaskRowsContainer" + group.group_id + "'>");
	//draw the group's tasks
	for (var i = 0; i < group.group_tasks.length; i++){
		if (i == og.noOfTasks){			//Draw expander if group has more than og.noOfTasks tasks
			sb.append("<div class='ogTasksTaskRow' style='display:" + (group.isExpanded? "none" : "inline") + "' id='ogTasksGroupExpandTasksTitle" + group.group_id + "'>");
			sb.append("<a href='#' class='internalLink' onclick='ogTasks.expandGroup(\"" + group.group_id + "\")'>" + lang('show more tasks number', (group.group_tasks.length - i)) + "</a>");
			sb.append("</div>");
			sb.append("<div id='ogTasksGroupExpandTasks" + group.group_id + "'>");
			if (group.isExpanded)
				for (var j = og.noOfTasks; j < group.group_tasks.length; j++)
					sb.append(this.drawTask(group.group_tasks[j], drawOptions, displayCriteria, group.group_id, 1));
			sb.append("</div>");
			break;
		}
		sb.append(this.drawTask(group.group_tasks[i], drawOptions, displayCriteria, group.group_id, 1));
	}
	sb.append("</div></div>");
	return sb.toString();
}

ogTasks.drawGroupActions = function(group){
	return '<a id="ogTasksPanelGroupSoloOn' + group.group_id + '" style="margin-right:15px;display:' + (group.solo? "none" : "inline") + '" href="#" class="internalLink" onClick="ogTasks.hideShowGroups(\'' + group.group_id + '\')" title="' + lang('hide other groups') + '">' + (lang('hide others')) + '</a>' +
	'<a id="ogTasksPanelGroupSoloOff' + group.group_id + '" style="display:' + (group.solo? "inline" : "none") + ';margin-right:15px;" href="#" class="internalLink" onClick="ogTasks.hideShowGroups(\'' + group.group_id + '\')" title="' + lang('show all groups') + '">' + (lang('show all')) + '</a>' +
	'<a href="#" class="internalLink ogTasksGroupAction ico-print" style="margin-right:15px;" onClick="ogTasks.printGroup(\'' + group.group_id + '\')" title="' + lang('print this group') + '">' + (lang('print')) + '</a>' +
	'<a href="#" class="internalLink ogTasksGroupAction ico-add" onClick="ogTasks.drawAddNewTaskForm(\'' + group.group_id + '\')" title="' + lang('add a new task to this group') + '">' + (lang('add task')) + '</a>';
}


ogTasks.hideShowGroups = function(group_id){
	var group = this.getGroup(group_id);
	if (group){
		var soloOn = document.getElementById('ogTasksPanelGroupSoloOn' + group_id);
		var soloOff = document.getElementById('ogTasksPanelGroupSoloOff' + group_id);
		group.solo = !group.solo;
		
		soloOn.style.display = group.solo ? 'none':'inline';
		soloOff.style.display= group.solo ? 'inline':'none';
		
		for (var i = 0; i < this.Groups.length; i++){
			if (this.Groups[i].group_id != group_id){
				var groupEl = document.getElementById('ogTasksPanelGroupCont' + this.Groups[i].group_id);
				if (groupEl)
					groupEl.style.display = group.solo ? 'none':'block';
			}
		}
		
		if (group.solo)
			this.expandGroup(group_id);
		else
			this.collapseGroup(group_id);
	}
}



ogTasks.expandGroup = function(group_id){
	var div = document.getElementById('ogTasksGroupExpandTasks' + group_id);
	var divLink = document.getElementById('ogTasksGroupExpandTasksTitle' + group_id);
	if (div){
		var group = this.getGroup(group_id);
		group.isExpanded = true;
		var html = '';
		var bottomToolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
		var topToolbar = Ext.getCmp('tasksPanelTopToolbarObject');
		var displayCriteria = bottomToolbar.getDisplayCriteria();
		var drawOptions = topToolbar.getDrawOptions();
		for (var i = og.noOfTasks; i < group.group_tasks.length; i++)
			html += this.drawTask(group.group_tasks[i], drawOptions, displayCriteria, group.group_id, 1);
		div.innerHTML = html;
		divLink.style.display = 'none';
		if (drawOptions.show_workspaces)
			og.showWsPaths('ogTasksGroupExpandTasks' + group_id);
	}
}



ogTasks.collapseGroup = function(group_id){
	var div = document.getElementById('ogTasksGroupExpandTasks' + group_id);
	var divLink = document.getElementById('ogTasksGroupExpandTasksTitle' + group_id);
	if (div){
		var group = this.getGroup(group_id);
		group.isExpanded = false;
		div.innerHTML = '';
		divLink.style.display = 'block';
	}
}

ogTasks.expandCollapseAllTasksGroup = function(group_id) {
	var group = this.getGroup(group_id);
	if (group){
		var expander = document.getElementById('ogTasksPanelGroupExpanderG' + group_id);
		if (group.alltasks_collapsed) {
			group.alltasks_collapsed = false;
			if (expander) expander.className = 'og-task-expander toggle_expanded';
			visibility = 'block';
		} else {
			group.alltasks_collapsed = true;
			if (expander) expander.className = 'og-task-expander toggle_collapsed';
			visibility = 'none';
		}
		
		var tasks_container = document.getElementById("ogTasksPanelTaskRowsContainer" +  group.group_id);
		if (tasks_container) tasks_container.style.display = visibility;
	}
}


ogTasks.drawAddTask = function(id_subtask, group_id, level){
	//Draw indentation
	var padding = (15 * (level + 1)) + 10;
	return '<div class="ogTasksTaskRow" style="padding-left:' + padding + 'px">' +
	'<div class="ogTasksAddTask ico-add">' +
	'<a href="#" class="internalLink"  onClick="ogTasks.drawAddNewTaskForm(\'' + group_id + '\', ' + id_subtask + ', ' + level + ')">' + ((id_subtask > 0)?lang('add subtask') : lang('add task')) + '</a>' +
	'</div></div>';
}



//************************************
//*		Draw task
//************************************

ogTasks.drawTask = function(task, drawOptions, displayCriteria, group_id, level){
	//Draw indentation
	var padding = 15 * level;
	var containerName = 'ogTasksPanelTask' + task.id + 'G' + group_id;
	task.divInfo[task.divInfo.length] = {group_id: group_id, drawOptions: drawOptions, displayCriteria: displayCriteria, group_id: group_id, level:level};

	// **** <RX : dragging **** //
	var rx__drag_h = '';
	var tgId = "T" + task.id + 'G' + group_id;
	if(rx__TasksDrag.allowDrag)
		rx__drag_h = "<div id='RX__ogTasksPanelDrag" + tgId + "' class='RX__tasks_og-drag ogTasksIcon' title='"+lang('click to drag task')+"' onmouseover='rx__TasksDrag.prepareExt("+task.id+", \"" + group_id + "\",this.id)' onmousedown='rx__TasksDrag.onDragStart("+task.id+", \"" + group_id + "\",this.id); return false;'></div>";

	var html = '<div style="padding-left:' + padding + 'px" id="' + containerName + '" class="RX__tasks_row" onmouseover="rx__TasksDrag.showHandle(\''+tgId+'\',1)"  onmouseout="rx__TasksDrag.showHandle(\''+tgId+'\',0)">' + rx__drag_h 
		 + this.drawTaskRow(task, drawOptions, displayCriteria, group_id, level) + '</div>';
	// **** /RX **** //
	
	if (task.subtasks.length > 0)
		html += this.drawSubtasks(task, drawOptions, displayCriteria, group_id, level);
	return html;
}

ogTasks.drawTaskRow = function(task, drawOptions, displayCriteria, group_id, level){
	var sb = new StringBuffer();
	var tgId = "T" + task.id + 'G' + group_id;
	sb.append('<table id="ogTasksPanelTaskTable' + tgId + '" class="ogTasksTaskTable' + (task.isChecked?'Selected':'') + '" onmouseover="ogTasks.mouseMovement(' + task.id + ',\'' + group_id + '\',true)" onmouseout="ogTasks.mouseMovement(' + task.id + ',\'' + group_id + '\',false)"><tr>');

	//Draw checkbox
	var priorityColor = "white";
	switch(task.priority){
		case 200: priorityColor = "#DAE3F0"; break;
		case 300: priorityColor = "#FF9088"; break;
		case 400: priorityColor = "#FF0000"; break;
		default: break;
	}
	sb.append('<td width=19 class="ogTasksCheckbox" style="background-color:' + priorityColor + '">');
	sb.append('<input style="width:14px;height:14px" type="checkbox" id="ogTasksPanelChk' + tgId + '" ' + (task.isChecked?'checked':'') + ' onclick="ogTasks.TaskSelected(this,' + task.id + ', \'' + group_id + '\')"/></td>'); 
	
	//Draw subtasks expander
	if (task.subtasks.length > 0){
		sb.append("<td width='16px' style='padding-top:3px'><div id='ogTasksPanelFixedExpander" + tgId + "' class='og-task-expander " + ((task.isExpanded)?'toggle_expanded':'toggle_collapsed') + "' onclick='ogTasks.toggleSubtasks(" + task.id +", \"" + group_id + "\")'></div></td>");
	}else{
		sb.append("<td width='16px'><div id='ogTasksPanelExpander" + tgId + "' style='visibility:hidden' class='og-task-expander ico-add ogTasksIcon' onClick='ogTasks.drawAddNewTaskForm(\"" + group_id + "\", " + task.id + "," + level +")' title='" + lang('add subtask') + "'></div></td>");
	}

	if (task.isRead){
		sb.append("<td style=\"width:16px\" id=\"ogTasksPanelMarkasTd" + task.id + "\"><div title=\"" + lang('mark as unread') + "\" id=\"readunreadtask" + task.id + "\" class=\"db-ico ico-read\" onclick=\"ogTasks.readTask(" + task.id + ","+task.isRead+")\" /></td>");		
	}else {
		sb.append("<td style=\"width:16px\" id=\"ogTasksPanelMarkasTd" + task.id + "\"><div title=\"" + lang('mark as read') + "\" id=\"readunreadtask" + task.id + "\" class=\"db-ico ico-unread\" onclick=\"ogTasks.readTask(" + task.id + ","+task.isRead+")\" /> </td>");
	}
	
	//Center td
	sb.append('<td align=left>');
	
	//Draw Workspaces
	if (drawOptions.show_workspaces){
		var ids = String(task.workspaceIds).split(',');
		var projectsString = "";
		var ids_to_show = new Array();
		for(var i = 0; i < ids.length; i++)
			if (!(displayCriteria.group_by == 'workspace' && group_id == ids[i]))
				ids_to_show.push(ids[i]);
		if (ids_to_show.length >= 1)
			projectsString += '<span class="project-replace">' + ids_to_show.join(',') + '</span>&nbsp;';
		sb.append(projectsString);
	}
	
	var taskName = '';
	//Draw the Assigned user
	if (task.assignedToId && (displayCriteria.group_by != 'assigned_to' || task.assignedToId != group_id)){
		taskName += '<b>' + og.clean(this.getUserCompanyName(task.assignedToId)) + '</b>:&nbsp;';
	}
	//Draw the task name
	taskName += og.clean(task.title);
	if (task.status > 0){
		var user = this.getUser(task.completedById);
		var tooltip = '';
		if (user){
			var time = new Date(task.completedOn * 1000);
			var now = new Date();
			var timeFormatted = time.getYear() != now.getYear() ? time.dateFormat('M j, Y'): time.dateFormat('M j');
			tooltip = lang('completed by name on', og.clean(user.name), timeFormatted).replace(/'\''/g, '\\\'');
		}
		taskName = "<span style='text-decoration:line-through' title='" + tooltip + "'>" + taskName + "</span>";
	}
	var viewUrl = og.getUrl('task', 'view_task', {id: task.id});
	sb.append('<a class="internalLink" href="' + viewUrl + '" onclick="og.openLink(\'' + viewUrl + '\');return false;" id="rx__dd'+(++rx__dd)+'">' + taskName + '</a>');
	
	//Draw repeat icon (if repetitive)
	if (task.repetitive > 0){
		sb.append('<span style="margin: 0px 8px; padding: 0px 0px 2px 12px;" class="ico-recurrent" title="'+ lang('repetitive task') +'">&nbsp;</span>');
	}
	//Draw tags
	if (drawOptions.show_tags)
		if (task.tags)
			sb.append('<span style="padding-left:18px;padding-top:4px;padding-bottom:2px;color:#888;font-size:10px;margin-left:10px" class="ico-tags ogTasksIcon"><i>' + og.clean(task.tags) + '</i></span>');
	
	sb.append('</td><td align=right><table style="height:100%"><tr>');
	
	//Draw task actions
	sb.append("<td><div id='ogTasksPanelTaskActions" + tgId + "' class='ogTaskActions'><table><tr>");
	var renderTo = "ogTasksPanelTaskActions" + tgId + "Assign";
	sb.append("<td style='padding-left:8px;'><a href='#' onclick='ogTasks.drawEditTaskForm(" + task.id + ", \"" + group_id + "\")'>");
	sb.append("<div class='ico-edit coViewAction' title='" + lang('edit') + "' style='cursor:pointer;height:16px;padding-top:0px'>" + lang('edit') + "</div></a></td>");
	sb.append("<td style='padding-left:8px;'><a href='#' onclick='ogTasks.ToggleCompleteStatus(" + task.id + ", " + task.status + ")'>");
	if (task.status > 0){
		sb.append("<div class='ico-reopen coViewAction' title='" + lang('reopen this task') + "' style='cursor:pointer;height:16px;padding-top:0px'>" + lang('reopen') + "</div></a></td>");
	} else {
		sb.append("<div class='ico-complete coViewAction' title='" + lang('complete this task') + "' style='cursor:pointer;height:16px;padding-top:0px'>" + lang('do complete') + "</div></a></td>");
	}
	sb.append("</tr></table></div></td>");
	
	//Draw dates
	if (drawOptions.show_dates && (task.startDate || task.dueDate)){
		sb.append('<td style="color:#888;font-size:10px;padding-left:6px;padding-right:3px">');
		if (task.status == 1)
			sb.append('<span style="text-decoration:line-through;">');
		else
			sb.append('<span>');
		
		if (task.startDate){
			var date = new Date(task.startDate * 1000);
			date = new Date(Date.parse(date.toUTCString().slice(0, -4)));
			var now = new Date();
			var dateFormatted = date.getYear() != now.getYear() ? date.dateFormat('M j, Y'): date.dateFormat('M j');
			sb.append(lang('start') + ':&nbsp;' + dateFormatted);
		}
		if (task.startDate && task.dueDate)
			sb.append('&nbsp;-&nbsp;');
		
		if (task.dueDate){
			var date = new Date((task.dueDate) * 1000);
			date = new Date(Date.parse(date.toUTCString().slice(0, -4)));
			var now = new Date();
			var dateFormatted = date.getYear() != now.getYear() ? date.dateFormat('M j, Y'): date.dateFormat('M j');
			var dueString = lang('due') + ':&nbsp;' + dateFormatted;
			if (task.status == 0){
				if (date < now)
					dueString = '<span style="font-weight:bold;color:#F00">' + dueString + '</span>';
			}
			sb.append(dueString);
		}
		sb.append('</span></td>');
	}
	
	//Draw time tracking
	if (drawOptions.show_time){
		if (task.workingOnIds){
			var ids = (task.workingOnIds + ' ').split(',');
			var userIsWorking = false;
			for (var i = 0; i < ids.length; i++)
				if (ids[i] == this.currentUser.id){
					userIsWorking = true;
					var pauses = (task.workingOnPauses + ' ').split(',');
					var userPaused = pauses[i] == 1;
				}
			sb.append("<td class='" + (userIsWorking?(userPaused?"ogTasksPausedTimeTd": "ogTasksActiveTimeTd") : "ogTasksTimeTd") + "'><table><tr>");
			if (userIsWorking){
				if (userPaused)
					sb.append("<td><a href='#' onclick='ogTasks.executeAction(\"resume_work\",[" + task.id + "])'><div class='ogTasksTimeClock ico-time-play' title='" + lang('resume_work') + "'></div></a></td>");
				else
					sb.append("<td><a href='#' onclick='ogTasks.executeAction(\"pause_work\",[" + task.id + "])'><div class='ogTasksTimeClock ico-time-pause' title='" + lang('pause_work') + "'></div></a></td>");
				
				sb.append("<td><a href='#' onclick='ogTasks.closeTimeslot(\"" + tgId + "\")'><div class='ogTasksTimeClock ico-time-stop' title='" + lang('close_work') + "'></div></a></td>");
			} else
				sb.append("<td><a href='#' onclick='ogTasks.executeAction(\"start_work\",[" + task.id + "])'><div class='ogTasksTimeClock ico-time' title='" + lang('start_work') + "'></div></a></td>");
			
			sb.append("<td style='white-space:nowrap'><b>");
			
			for (var i = 0; i < ids.length; i++){
				var user = this.getUser(ids[i]);
				if (user){
					sb.append("" + og.clean(user.name));
					if (i < ids.length - 1)
						sb.append(",");
					sb.append("&nbsp;");
				}
			}
			sb.append("</b>");
			if (userIsWorking){
				sb.append("<div id='ogTasksPanelCWD" + tgId + "' style='display:none'><table><tr><td>" + lang('description') + ":<br/><textarea tabIndex=10100 style='height:54px;width:220px;margin-right:8px' id='ogTasksPanelCWDescription" + tgId + "'></textarea></td></tr>");
				sb.append("<tr><td style='padding-bottom:5px'><button type='submit' tabIndex=10101 onclick='ogTasks.executeAction(\"close_work\",[" + task.id + "],document.getElementById(\"ogTasksPanelCWDescription" + tgId + "\").value);return false'>" + lang('close work') + "</button>&nbsp;&nbsp;<button tabIndex=10102 type='submit' onclick='ogTasks.closeTimeslot(\"" + tgId + "\");return false'>" + lang('cancel') + "</button></td></tr></table></div>");
			}
			sb.append("</td></tr></table>");
		}else{
			sb.append("<td class='ogTasksTimeTd'>");
			sb.append("<a href='#' onclick='ogTasks.executeAction(\"start_work\",[" + task.id + "])'><div class='ogTasksTimeClock ico-time' title='" + lang('start_work') + "'></div></a>");
		}
		sb.append("</td>");
	}
	
	sb.append('</tr></table></td></tr></table>');
		
	return sb.toString();
}



ogTasks.closeTimeslot = function(tgId){
	var panel = document.getElementById('ogTasksPanelCWD' + tgId);
	if (panel.style.display == 'block')
		panel.style.display = 'none';
	else {
		panel.style.display = 'block';
		document.getElementById('ogTasksPanelCWDescription' + tgId).focus();
	}
}

ogTasks.drawSubtasks = function(task, drawOptions, displayCriteria, group_id, level){
	var html = '<div style="display:' + ((task.isExpanded)?'block':'none') + '" id="ogTasksPanelSubtasksT' + task.id + 'G' + group_id + '">';
	var orderedTasks = this.orderTasks(displayCriteria, task.subtasks);
	for (var i = 0; i < orderedTasks.length; i++){
		html += this.drawTask(orderedTasks[i], drawOptions, displayCriteria, group_id, level + 1);
	}
	html += this.drawAddTask(task.id, group_id, level);
	html += '</div>';
	return html;
}


ogTasks.ToggleCompleteStatus = function(task_id, status){
	var action = (status == 0)? 'complete_task' : 'open_task';
	
	og.openLink(og.getUrl('task', action, {id: task_id, quick: true}), {
		callback: function(success, data) {
			if (!success || data.errorCode) {
			} else {
				//Set task data
				var task = ogTasks.getTask(task_id);
				task.setFromTdata(data.task);
				
				//Redraw task, or redraw whole panel
				var bottomToolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
				var displayCriteria = bottomToolbar.getDisplayCriteria();
				if (displayCriteria.group_by != 'status')
					this.UpdateTask(task.id);
				else
					this.draw();
			}
		},
		scope: this
	});
}
ogTasks.readTask = function(task_id,isUnRead){
	var task = ogTasks.getTask(task_id);
	if (!isUnRead){
		og.openLink(
			og.getUrl('task','multi_task_action'),
			{ method:'POST' ,	post:{ids:task_id, action:'markasread'},callback:function(success, data){
					if (!success || data.errorCode) {
					} else {
						var td = document.getElementById('ogTasksPanelMarkasTd' + task_id);
						td.innerHTML = "<div title=\"" + lang('mark as unread') + "\" id=\"readunreadtask" + task_id + "\" class=\"db-ico ico-read\" onclick=\"ogTasks.readTask(" + task_id + ",true)\" />";
						task.isRead = true;
					}
				}
			}
		);
	}else{
		og.openLink(
			og.getUrl('task','multi_task_action'),
			{ method:'POST' ,	post:{ids:task_id, action:'markasunread'},callback:function(success, data){
					if (!success || data.errorCode) {
					} else {								
						var td = document.getElementById('ogTasksPanelMarkasTd' + task_id);
						td.innerHTML = "<div title=\"" + lang('mark as read') + "\" id=\"readunreadtask" + task_id + "\" class=\"db-ico ico-unread\" onclick=\"ogTasks.readTask(" + task_id + ",false)\" />";
						task.isRead = false;
					}
				}
			}
		);
	}
}

ogTasks.UpdateTask = function(task_id){
	var task = ogTasks.getTask(task_id);
	for (var i = 0; i < task.divInfo.length; i++){
		var containerName = 'ogTasksPanelTask' + task.id + 'G' + task.divInfo[i].group_id;
		var div = document.getElementById(containerName);
		if (div){
			div.innerHTML = this.drawTaskRow(task, task.divInfo[i].drawOptions, task.divInfo[i].displayCriteria, task.divInfo[i].group_id, task.divInfo[i].level);
			if (task.divInfo[i].displayCriteria.group_by == 'milestone') { //Update milestone complete bar
				var div2 = document.getElementById('ogTasksPanelCompleteBar' + task.divInfo[i].group_id);
				div2.innerHTML = this.drawMilestoneCompleteBar(this.getGroup(task.divInfo[i].group_id));
			}
			og.showWsPaths(containerName);
		}
	}
}