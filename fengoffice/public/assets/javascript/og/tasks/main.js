/**
 * main.js
 *
 * This module holds the structure information for all elements used in the ordering and grouping algorithms,
 * and holds the code for ordering and grouping tasks.
 *
 * @author Carlos Palma <chonwil@gmail.com>
 */

ogTasks.Tasks = [];
ogTasks.Users = [];
ogTasks.Companies = [];
ogTasks.Milestones = [];

ogTasks.Groups = [];

ogTasks.redrawGroups = true;

//ogTasks.prevWsValue = -1; //Used to view if ws selector changed its value, to refresh the assingedto combo
ogTasks.assignedTo = '-1'; //Used to init the assignedto combo when it is refreshed
ogTasks.selectedMilestone = 0;

//************************************
//*		Structure definitions
//************************************

ogTasksTask = function(){
	this.id;
	this.title;
        this.description;
	this.createdOn;
	this.createdBy;
	this.status = 0;
	this.statusOnCreate = 0;
	this.parentId = 0;
	this.priority = 200;
	this.milestoneId;
	this.assignedToId;
	this.dueDate;
	this.startDate;
	this.workingOnIds;
	this.workingOnTimes;
	this.workingOnPauses;
	this.pauseTime;
	this.isAdditional = false;
	this.isRead = true;
	this.completedById;
	this.completedOn;
	this.repetitive = false;
	this.otype;
	this.percentCompleted = 0;
	this.members;
	this.depCount;
	this.memPath;
	this.useDueTime = false;
	this.useStartTime = false;
	
	this.createdByName;
	this.assignedToName;
	this.milestoneName;
	this.workspacePaths;
	
	this.subtasks = [];
	this.parent;
	
	this.divInfo = [];
	this.isChecked = false;
	this.isExpanded = false;
	this.isCreatedClientSide = false;
}

ogTasksTask.prototype.flatten = function(){
	var result = [this];
	if (this.subtasks.length > 0)
		for (var i = 0; i < this.subtasks.length; i++)
			result = result.concat(this.subtasks[i].flatten());
	return result;
}

ogTasksTask.prototype.setFromTdata = function(tdata){
	this.id = tdata.id;
	this.title = tdata.t;
        this.description = tdata.desc;
	this.createdOn = tdata.c;
	this.createdBy = tdata.cid;
		
	var dummyDate = new Date();

	if (tdata.s) this.status = tdata.s; else this.status = 0;
	if (tdata.pid) this.parentId = tdata.pid; else this.parentId = 0;
	if (tdata.pr) this.priority = tdata.pr; else this.priority = 200;
	if (tdata.mid) this.milestoneId = tdata.mid; else this.milestoneId = null;
	if (tdata.atid) this.assignedToId = tdata.atid; else this.assignedToId = null;
	if (tdata.dd) this.dueDate = tdata.dd; else this.dueDate = null;
	if (tdata.sd) this.startDate = tdata.sd; else this.startDate = null;
	if (tdata.wid) this.workingOnIds = tdata.wid; else this.workingOnIds = null;
	if (tdata.wt) this.workingOnTimes = tdata.wt; else this.workingOnTimes = null;
	if (tdata.wp) this.workingOnPauses = tdata.wp; else this.workingOnPauses = null;
	if (tdata.wpt) this.pauseTime = tdata.wpt; else this.pauseTime = null;
	if (tdata.cbid) this.completedById = tdata.cbid; else this.completedById = null;
	if (tdata.con) this.completedOn = tdata.con; else this.completedOn = null;
	if (tdata.rep) this.repetitive = true;
	if (tdata.isread) this.isRead = true; else this.isRead = false;
	if (tdata.otype) this.otype = tdata.otype; else this.otype = null;
	if (tdata.percentCompleted) this.percentCompleted = tdata.percentCompleted; else this.percentCompleted = 0;
	if (tdata.members) this.members = tdata.members; else this.members = [];
	//if (tdata.estimatedTime) this.estimatedTime =  Math.round( tdata.estimatedTime * 10  / 60 ) / 10; else this.estimatedTime = '' ;
	if (tdata.estimatedTime) this.estimatedTime = tdata.estimatedTime ; else this.estimatedTime = '' ;
        if (tdata.TimeEstimate) this.TimeEstimate = tdata.TimeEstimate ; else this.TimeEstimate = 0 ;
	if (tdata.depCount) this.depCount = tdata.depCount; else this.depCount = null;
	if (tdata.memPath) this.memPath = tdata.memPath; else this.memPath = [];
	if (tdata.udt) this.useDueTime = tdata.udt;
	if (tdata.ust) this.useStartTime = tdata.ust;
        if (tdata.multiAssignment) this.multiAssignment = tdata.multiAssignment;
}

ogTasksMilestone = function(id, title, dueDate, totalTasks, completedTasks, isInternal, isUrgent){
	this.id = id;
	this.title = title;
	
	var dummyDate = new Date();
	this.dueDate = dueDate;
	
	this.completedTasks = completedTasks;
	this.totalTasks = totalTasks;
	this.isInternal = isInternal;
	this.isUrgent = isUrgent;
	this.completedById;
}

ogTasksCompany = function(id, name){
	this.id = id;
	this.name = name;
}

ogTasksUser = function(id, name, companyId){
	this.id = id;
	this.name = name;
	this.companyId = companyId;
}

ogTasksObjectSubtype = function(id, name){
	this.id = id;
	this.name = name;
}

ogTasksDependencyCount = function(id, count, dependants){
	this.id = id;
	this.count = count;
	this.dependants = dependants;
}

//************************************
//*		Data loading
//************************************

ogTasks.loadDataFromHF = function(){
	var result = [];
	var tasksString = document.getElementById('hfTasks').value;
	result['tasks'] = Ext.util.JSON.decode(tasksString);
	result['internalMilestones'] = Ext.util.JSON.decode(document.getElementById('hfIMilestones').value);
	result['externalMilestones'] = Ext.util.JSON.decode(document.getElementById('hfEMilestones').value);
	result['users'] = Ext.util.JSON.decode(document.getElementById('hfUsers').value);
	result['allUsers'] = Ext.util.JSON.decode(document.getElementById('hfAllUsers').value);
	result['companies'] = Ext.util.JSON.decode(document.getElementById('hfCompanies').value);
	result['objectSubtypes'] = Ext.util.JSON.decode(document.getElementById('hfObjectSubtypes').value);
	result['dependencyCount'] = Ext.util.JSON.decode(document.getElementById('hfDependencyCount').value);
	
	return ogTasks.loadData(result);
}


ogTasks.loadData = function(data){
	var i;
	this.Tasks = [];
	for (i in data['tasks']){
		var tdata = data['tasks'][i];
		if (tdata.id){
			var task = new ogTasksTask();
			task.setFromTdata(tdata);
			if (tdata.s)
				task.statusOnCreate = tdata.s;
			this.Tasks[ogTasks.Tasks.length] = task;
		}
	}
	
	//Set task parent information
	for (var i = 0; i < this.Tasks.length; i++){
		if (this.Tasks[i].parentId > 0){
			var parent = this.getTask(this.Tasks[i].parentId);
			if (parent){
				this.Tasks[i].parent = parent;
				parent.subtasks[parent.subtasks.length] = this.Tasks[i];
			}
		}
	}
	
	this.Users = [];
	for (i in data['users']){
		var udata = data['users'][i];
		if (udata.id){
			var user =  new ogTasksUser(udata.id,udata.name,udata.cid);
			this.Users[ogTasks.Users.length] = user;
			if (udata.isCurrent)
				this.currentUser = user;
		}
	}
	
	this.AllUsers = [];
	for (i in data['allUsers']){
		var udata = data['allUsers'][i];
		if (udata.id){
			var user =  new ogTasksUser(udata.id,udata.name,udata.cid);
			this.AllUsers[ogTasks.AllUsers.length] = user;
		}
	}

	this.Companies = [];
	for (i in data['companies']){
		var cdata = data['companies'][i];
		if (cdata.id)
			this.Companies[ogTasks.Companies.length] = new ogTasksCompany(cdata.id,cdata.name);
	}
	
	this.Milestones = [];
	for (i in data['internalMilestones']){
		var mdata = data['internalMilestones'][i];
		if (mdata.id){
			with (mdata) {
				var milestone = new ogTasksMilestone(id,t,dd,tnum,tc,true,is_urgent);
			}
			if (mdata.compId) milestone.completedById = mdata.compId;
			if (mdata.compOn) milestone.completedOn = mdata.compOn;
			this.Milestones[ogTasks.Milestones.length] = milestone;
		}
	}
	for (i in data['externalMilestones']){
		var mdata = data['externalMilestones'][i];
		if (mdata.id){
			with (mdata) {
				var milestone = new ogTasksMilestone(id,t,dd,tnum,tc,false,is_urgent);
			}
			if (mdata.compId) milestone.completedById = mdata.compId;
			if (mdata.compOn) milestone.completedOn = mdata.compOn;
			this.Milestones[ogTasks.Milestones.length] = milestone;
		}
	}
	
	this.ObjectSubtypes = [];
	for (i in data['objectSubtypes']){
		var otdata = data['objectSubtypes'][i];
		if (otdata.id){
			var ot =  new ogTasksObjectSubtype(otdata.id,otdata.name);
			this.ObjectSubtypes[ogTasks.ObjectSubtypes.length] = ot;
		}
	}
	
	this.DependencyCount = [];
	for (i in data['dependencyCount']){
		var dcdata = data['dependencyCount'][i];
		if (dcdata.id){
			var dc =  new ogTasksDependencyCount(dcdata.id, dcdata.count, dcdata.dependants);
			this.DependencyCount[ogTasks.DependencyCount.length] = dc;
		}
	}
}



//************************************
//*		Grouping algorithms
//************************************

ogTasks.getBottomParent = function(task){
	if (task.parent)
		return this.getBottomParent(task.parent);
	else
		return task;
}

ogTasks.getGroupData = function(displayCriteria, groups,tasks){
	var groupData = [];
	var i;
	var td = this.getTimeDistances();
	for (var i = 0; i < groups.length; i++){
		var groupId = groups[i];
		var name;
		switch (displayCriteria.group_by){
			case 'assigned_to': name = lang('unassigned'); break;
			case 'nothing': name = lang('tasks'); break;
			case 'completed_by':
			case 'completed_on':
				name = lang('pending'); break;
			case 'due_date': name = lang('no due date');break;
			case 'start_date': name = lang('no start date');break;
			default:
				name = lang('ungrouped');
		}
		var icon = '';
		var id = i;
		var urgent = false;
		if (groupId != 'unclassified'){
			switch(displayCriteria.group_by){
				case 'milestone':
					icon = 'ico-milestone';
					var milestone = this.getMilestone(groupId);
					if (milestone){
						name = milestone.title; 
						urgent = milestone.isUrgent;
					}
					break;
				case 'priority' : 
					switch(groupId){
						case 100: name = lang('low'); icon = 'ico-task-low-priority'; break;
						case 200: name = lang('normal'); icon = 'ico-task'; break;
						case 300: name = lang('high'); icon = 'ico-task-high-priority'; break;
						case 400: name = lang('urgent'); icon = 'ico-task-high-priority'; break;
						default:
					} break;
				case 'workspace' : 
					icon = 'ico-color' + og.getWorkspaceColor(groupId);
					name = og.getFullWorkspacePath(groupId, true);
					break;
				case 'assigned_to' : 
					var contact = this.getUser(groupId);
					if (contact){
						var company_name = "";
						if (contact.companyId > 0) {
							company_name = " : " + this.getCompany(contact.companyId).name;
							icon = 'ico-user';
						} else {
							icon = 'ico-company';
						}
						name = contact.name + company_name;
					} else {
						var comp = this.getCompany(groupId);
						if (comp){
							name = comp.name;
							icon = 'ico-company';
						} else {
							name = lang('company not found', groupId);
							icon = 'ico-unknown';
						}
					}
					
					break;
				case 'due_date' : name = td[groupId]; break;
				case 'start_date' : name = td[groupId]; break;
				case 'created_on' : name = td[groupId]; break;
				case 'completed_on' : name = td[groupId]; break;
				case 'created_by' : 
				case 'completed_by' : 
					var user = this.getUser(groupId);
					if (user) name = user.name;
					icon = 'ico-user'; 
					break;
				case 'status' :
					if (groupId == 0){
						icon = 'ico-delete';
						name = lang('incomplete');
					} else {
						icon = 'ico-complete';
						name = lang('complete');
					}
					break;
				case 'subtype' : name = this.getObjectSubtype(groupId) ? this.getObjectSubtype(groupId).name : lang('ungrouped') ; break;
				default:
					if (displayCriteria.group_by.indexOf('dimension_') == 0) {
						// Group by dimension
						var dim_id = displayCriteria.group_by.replace('dimension_', '');
						if (og.dimensions[dim_id] && og.dimensions[dim_id][groupId]) {
							name = og.dimensions[dim_id][groupId].name;
							icon = og.dimensions[dim_id][groupId].ico;
						}
					}
			}
		}
		var solo = false;
		var expanded = false;
		if (!this.redrawGroups){
			var group = this.getGroup(groupId);
			if (group){
				solo = group.solo;
				expanded = group.isExpanded;
			}
		}
		
		if (!tasks[i])
			tasks[i] = [];
		
		groupData[groupData.length] = {
			group_name: name,
			group_id: groupId,
			group_icon: icon,
			group_tasks: tasks[i],
			solo: solo,
			isExpanded: expanded,
			isChecked: false,
			isUrgent: urgent
		}
	}
	return groupData;
}

ogTasks.groupTasks = function(displayCriteria, tasksContainer){
	var groups = [];
	var tasks = [];
	groups[0] = 'unclassified';
	tasks[0] = [];
	if (!this.redrawGroups) {
		for (var i = 0; i < this.Groups.length - 1; i++) {
			groups[i+1] = this.Groups[i].group_id;
		}
	}
	
	for (var i = 0; i < tasksContainer.length; i++){
		var task = tasksContainer[i];
		if (!task.parent){
			var this_task_groups = [];
			var group = null;
			switch(displayCriteria.group_by){
				case 'milestone': group = (task.milestoneId?(this.getMilestone(task.milestoneId)?task.milestoneId:null):null); break;
				case 'priority' : group = (task.priority?task.priority:200); break;
				case 'assigned_to' : group = (task.assignedToId?task.assignedToId:null); break;
				case 'due_date' : group = (task.dueDate?this.getTimeDistance(task.dueDate):null); break;
				case 'start_date' : group = (task.startDate?this.getTimeDistance(task.startDate):null); break;
				case 'created_on' : group = this.getTimeDistance(task.createdOn); break;
				case 'completed_on' : group = (task.completedOn? this.getTimeDistance(task.completedOn):null); break;
				case 'created_by' : group = task.createdBy; break;
				case 'status' : group = task.status; break;
				case 'completed_by' : group = (task.completedById?task.completedById:null); break;
				case 'subtype' : group = task.otype; break;
				default:
					if (displayCriteria.group_by.indexOf('dimension_') == 0) {
						// Group by dimension
						var dim_id = displayCriteria.group_by.replace('dimension_', '');
						for (k=0; k<task.members.length; k++) {
							for (j=0; j<og.dimensions[dim_id].length; j++) {
								if (og.dimensions[dim_id][task.members[k]]) {
									group = task.members[k];
									this_task_groups.push(group);
									break;
								}
							}
						}
						if (this_task_groups.length == 0) {
							this_task_groups.push(groups[0]);
						}
					}
			}
			
			if (displayCriteria.group_by.indexOf('dimension_') != 0) {
				this_task_groups.push(group);
			}
			for (var x=0; x<this_task_groups.length; x++) {
				group = this_task_groups[x];
				if (group || group == 0){
					if (groups.indexOf(group) < 0){
						groups[groups.length] = group;
						tasks[tasks.length] = [];
					}
					if (!tasks[groups.indexOf(group)])
						 tasks[groups.indexOf(group)] = [];
					var tasksArray = tasks[groups.indexOf(group)]; 
					tasksArray[tasksArray.length]= task;
				} else {
					tasks[0][tasks[0].length] = task;
				}
			}
		}
	}
	if (displayCriteria.group_by == 'milestone'){ 			//Show all milestones
	
		var bottomToolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
		var filters = bottomToolbar.getFilters();
		
		for(var i = 0; i < this.Milestones.length; i++) {
			if (groups.indexOf(this.Milestones[i].id) < 0){
				if (filters.filter != 'milestone' || filters.fval == this.Milestones[i].id){
					groups[groups.length] = this.Milestones[i].id;
					tasks[tasks.length] = [];
				}
			}
		}
	}
	var groupData = this.getGroupData(displayCriteria,groups,tasks);
	return this.orderGroups(displayCriteria, groupData);
}



//************************************
//*		Ordering Algorithms
//************************************

ogTasks.orderGroups = function(displayCriteria,groups){
	//Take the unclassified tasks to the bottom
	var unclassifiedGroup = groups[0];
	for (var i = 1; i < groups.length; i++) {
		groups[i-1] = groups[i];
	}
	groups[groups.length - 1] = unclassifiedGroup;
	
	//Order the rest
	switch(displayCriteria.group_by){
		case 'created_by' :
		case 'completed_by' :
			for (var i = 0; i < groups.length - 2; i++) {
				for (var j = i+1; j < groups.length - 1; j++) {
					if (groups[i].group_name.toUpperCase() > groups[j].group_name.toUpperCase()){
						var aux = groups[i];
						groups[i] = groups[j];
						groups[j] = aux;
					}
				}
			}
			break;
		case 'milestone':
			for (var i = 0; i < groups.length-1; i++) {
				groups[i].duedate = this.getMilestone(groups[i].group_id).dueDate;
			}
			for (var i = 0; i < groups.length - 2; i++) {
				for (var j = i+1; j < groups.length - 1; j++) {
					if (groups[i].duedate > groups[j].duedate){
						var aux = groups[i];
						groups[i] = groups[j];
						groups[j] = aux;
					}
				}
			}
			break;
		case 'assigned_to' :
		case 'due_date' :
		case 'start_date' :
		case 'status' :
			for (var i = 0; i < groups.length - 2; i++) {
				for (var j = i+1; j < groups.length - 1; j++) {
					if (groups[i].group_id > groups[j].group_id){
						var aux = groups[i];
						groups[i] = groups[j];
						groups[j] = aux;
					}
				}
			}
			break;
		case 'created_on' :
		case 'completed_on' :
		case 'priority' :
			for (var i = 0; i < groups.length - 2; i++) {
				for (var j = i+1; j < groups.length - 1; j++) {
					if (groups[i].group_id < groups[j].group_id){
						var aux = groups[i];
						groups[i] = groups[j];
						groups[j] = aux;
					}
				}
			}
			break;
		default:
			if (displayCriteria.group_by.indexOf('dimension_') == 0) {
				for (var i = 0; i < groups.length - 2; i++) {
					for (var j = i+1; j < groups.length - 1; j++) {
						if (groups[i].group_name.toUpperCase() > groups[j].group_name.toUpperCase()){
							var aux = groups[i];
							groups[i] = groups[j];
							groups[j] = aux;
						}
					}
				}
			}
	}
	return groups;
}

ogTasks.orderTasks = function(displayCriteria, tasks){
	for (var i = 0; i < tasks.length - 1; i++){
		for (var j = i+1; j < tasks.length; j++){
			var swap = false;
			var resolveByName = false;
			switch(displayCriteria.order_by){
				case 'priority' : 
					if (tasks[i].priority < tasks[j].priority) {
						swap = true;
					} else {
						if (tasks[i].priority == tasks[j].priority){
							//take into account the due date
							swap = (tasks[i].dueDate && tasks[j].dueDate && tasks[i].dueDate > tasks[j].dueDate) || (tasks[j].dueDate && !tasks[i].dueDate);
							if (!swap)
								resolveByName = (tasks[i].dueDate && tasks[j].dueDate && tasks[i].dueDate == tasks[j].dueDate) || (!tasks[j].dueDate && !tasks[i].dueDate) ;
						} else
							swap = false;
					}
					break;
				case 'name' : 
					swap = tasks[i].title.toUpperCase() > tasks[j].title.toUpperCase();
					break;
				case 'due_date' : 
					swap = (tasks[i].dueDate && tasks[j].dueDate && tasks[i].dueDate > tasks[j].dueDate) || (tasks[j].dueDate && !tasks[i].dueDate) ;
					if (!swap)
						resolveByName = (tasks[i].dueDate && tasks[j].dueDate && tasks[i].dueDate == tasks[j].dueDate) || (!tasks[j].dueDate && !tasks[i].dueDate) ;
					break;
				case 'created_on' : 
					swap = (tasks[i].createdOn && tasks[j].createdOn && tasks[i].createdOn > tasks[j].createdOn) || (tasks[j].createdOn && !tasks[i].createdOn) ;
					break;
				case 'completed_on' : 
					swap = tasks[i].completedOn < tasks[j].completedOn;
					break;
				case 'assigned_to' : //TODO: Correct this sorting method
					swap = (tasks[i].assignedToId && tasks[j].assignedToId && tasks[i].assignedToId < tasks[j].assignedToId) || (tasks[j].assignedToId && !tasks[i].assignedToId);
					if (!swap)
						resolveByName = (tasks[i].assignedToId && tasks[j].assignedToId && tasks[i].assignedToId == tasks[j].assignedToId) || (!tasks[j].assignedToId && !tasks[i].assignedToId);
					break;
				case 'start_date' : 
					swap = (tasks[i].startDate && tasks[j].startDate && tasks[i].startDate > tasks[j].startDate) || (tasks[j].startDate && !tasks[i].startDate) ;
					if (!swap)
						resolveByName = (tasks[i].startDate && tasks[j].startDate && tasks[i].startDate == tasks[j].startDate) || (!tasks[j].startDate && !tasks[i].startDate) ;
					break;
                                case 'percent_completed' : 
					swap = tasks[j].percentCompleted > tasks[i].percentCompleted;
					break;
				default:
			}
			if (!swap && resolveByName){
				swap = tasks[i].title.toUpperCase() > tasks[j].title.toUpperCase();
			}
			if (swap){
				var aux = tasks[i];
				tasks[i] = tasks[j];
				tasks[j] = aux;
			}
		}
	}
	return tasks;
}


ogTasks.TaskSelected = function(checkbox, task_id, group_id){
	var task = this.getTask(task_id);
	task.isChecked = checkbox.checked;
	var topToolbar = Ext.getCmp('tasksPanelTopToolbarObject');
	topToolbar.updateCheckedStatus();
}


ogTasks.GroupSelected = function(checkbox, group_id){
	this.expandGroup(group_id);
	var group = this.getGroup(group_id);
	var tasks = [];
	for(var i = 0; i < group.group_tasks.length; i++)
		tasks = tasks.concat(group.group_tasks[i].flatten());

	for (var i = 0; i < tasks.length; i++){
		tasks[i].isChecked = checkbox.checked;
		var tgId = "T" + tasks[i].id + 'G' + group_id;
		var chkTask = document.getElementById('ogTasksPanelChk' + tgId);
		chkTask.checked = checkbox.checked;
		var table = document.getElementById('ogTasksPanelTaskTable' + tgId);
		if (table)
			table.className = checkbox.checked ? 'ogTasksTaskTableSelected' : 'ogTasksTaskTable';
	}
	var topToolbar = Ext.getCmp('tasksPanelTopToolbarObject');
	topToolbar.updateCheckedStatus();
}


//************************************
//*		Helpers
//************************************


ogTasks.executeAction = function(actionName, ids, options){
	if (!ids)
		var ids = this.getSelectedIds();
	
	og.openLink(og.getUrl('task', 'multi_task_action'), {
		method: 'POST',
		post: {
			"ids": ids.join(','),
			"action" : actionName,
			"options": options
		},
		callback: function(success, data) {
			if (success && ! data.errorCode) {
				for (var i = 0; i < data.tasks.length; i++){
					var tdata = data.tasks[i];
					if (actionName == 'delete' || actionName == 'archive'){
						var task = this.getTask(tdata.id);
						if (task){
							var tasksToRemove = task.flatten();
							for (var j = 0; j < tasksToRemove.length; j++)
								this.removeTask(tasksToRemove[j].id);
							if (task.parent)
								for (var j = 0; j < task.parent.subtasks.length; j++)
									if (task.parent.subtasks[j].id == task.id)
										task.parent.subtasks.splice(j,1);
						}
					} else {
						var task = ogTasks.getTask(tdata.id);
						task.setFromTdata(tdata);
					}
				}
				this.redrawGroups = false;
				this.draw();
				this.redrawGroups = true;
				var topToolbar = Ext.getCmp('tasksPanelTopToolbarObject');
				topToolbar.updateCheckedStatus();
			} else {
			
			}
		},
		scope: this
	});
}

ogTasks.setAllCheckedValue = function(checked){
	for (var i = 0; i < this.Tasks.length; i++){
		this.Tasks[i].isChecked = checked;
	}
}

ogTasks.getSelectedIds = function(){
	var result = [];
	for (var i = 0; i < this.Tasks.length; i++) {
		if (this.Tasks[i].isChecked) {
			result[result.length] = this.Tasks[i].id;
		}
	}
	return result;
}

ogTasks.setAllExpandedValue = function(expanded){
	for (var i = 0; i < this.Tasks.length; i++){
		this.Tasks[i].isExpanded = expanded;
	}
}

ogTasks.getUserCompanyName = function(assigned_to){
	var user = this.getUser(assigned_to, true);
	if (user) {
		return user.name;
	} else {
		user = this.getCompany(assigned_to);
		if (user) {
			return user.name;
		}
	}
	return "";
}

ogTasks.getTask = function(id){
	for (var i = 0; i < this.Tasks.length; i++) {
		if (this.Tasks[i].id == id) {
			return this.Tasks[i];
		}
	}
	return null;
}

ogTasks.removeTask = function(id){
	for (var i = 0; i < this.Tasks.length; i++) {
		if (this.Tasks[i].id == id){
			if (this.Tasks[i].milestoneId > 0) {
				var mstone = ogTasks.getMilestone(this.Tasks[i].milestoneId);
				if (mstone && !this.Tasks[i].isCreatedClientSide) {
					mstone.totalTasks -= 1;
					mstone.completedTasks -= (this.Tasks[i].status == 0 && (this.Tasks[i].statusOnCreate == 1))? 1:0 ? 1 : 0;
				}
			}
			this.Tasks.splice(i,1);
			return true;
		}
	}
	return false;
}
ogTasks.redrawTask = function(id){
	for (var i = 0; i < this.Tasks.length; i++) {
		if (this.Tasks[i].id == id){
			if (this.Tasks[i].milestoneId > 0) {
				var mstone = ogTasks.getMilestone(this.Tasks[i].milestoneId);
				if (mstone && !this.Tasks[i].isCreatedClientSide) {
					mstone.totalTasks -= 1;
					mstone.completedTasks -= (this.Tasks[i].status == 0 && (this.Tasks[i].statusOnCreate == 1))? 1:0 ? 1 : 0;
				}
			}
			this.Tasks.splice(i,1);
			return true;
		}
	}
	return false;
}
ogTasks.getMilestone = function(id){
	for (var i = 0; i < this.Milestones.length; i++) {
		if (this.Milestones[i].id == id) {
			return this.Milestones[i];
		}
	}
	return null;
}

ogTasks.getUser = function(id, lookInAll){
	if (lookInAll) {
		for (var i = 0; i < this.AllUsers.length; i++) {
			if (this.AllUsers[i].id == id) {
				return this.AllUsers[i];
			}
		}
	} else {
		for (var i = 0; i < this.Users.length; i++) {
			if (this.Users[i].id == id) {
				return this.Users[i];
			}
		}
	}
	return null;
}

ogTasks.getCompany = function(id){
	for (var i = 0; i < this.Companies.length; i++) {
		if (this.Companies[i].id == id) {
			return this.Companies[i];
		}
	}
	return null;
}

ogTasks.getGroup = function(id){
	for (var i = 0; i < this.Groups.length; i++) {
		if (this.Groups[i].group_id == id) {
			return this.Groups[i];
		}
	}
	return null;
}

ogTasks.getObjectSubtype = function(id){
	for (var i = 0; i < this.ObjectSubtypes.length; i++) {
		if (this.ObjectSubtypes[i].id == id) {
			return this.ObjectSubtypes[i];
		}
	}
	return null;
}

ogTasks.getDependencyCount = function(id){
	for (var i = 0; i < this.DependencyCount.length; i++) {
		if (this.DependencyCount[i].id == id) {
			return this.DependencyCount[i];
		}
	}
	return null;
}

ogTasks.setSubtasksFromData = function(task, subtdata){
	for (var j = 0; j < task.subtasks.length; j++) {
		var subt = task.subtasks[j];
		for (var k = 0; k < subtdata.length; k++) {
			if (subtdata[k].id == subt.id) {
				subt.setFromTdata(subtdata[k]);
				break;
			}
		}
		ogTasks.setSubtasksFromData(subt, subtdata);
	}
}

/*
*	Returns the time distance values and labels
*/
ogTasks.getTimeDistances = function(){
	var result = [];
	result[3] = lang('earlier than one year');
	result[4] = lang('last year');
	result[5] = lang('last three months');
	result[6] = lang('last month');
	result[7] = lang('last two weeks');
	result[8] = lang('last week');
	result[9] = lang('yesterday');
	result[10] = lang('today');
	result[11] = lang('tomorrow');
	result[12] = lang('one week');
	result[13] = lang('two weeks');
	result[14] = lang('one month');
	result[15] = lang('three months');
	result[16] = lang('one year');
	result[17] = lang('later than one year');
	
	return result;
}

/*
*	Given a specific datetime, returns the time distance for the datetime
*/
ogTasks.getTimeDistance = function(timestamp){
	var tz = og.loggedUser.tz ? og.loggedUser.tz : 0;
	var date = new Date((timestamp - tz * 3600) * 1000);
	date.clearTime();
	var today = new Date();
	today.clearTime();
	var elapsed;
	if (today > date)
		elapsed = -(date.getElapsed(today));
	else
		elapsed = today.getElapsed(date);
	var day = 24*60*60*1000; //milliseconds in a day
	
	if (elapsed <  -(365 * day))
		return 3;
	if (elapsed < -(90 * day))
		return 4;
	if (elapsed < -(30 * day))
		return 5;
	if (elapsed < -(14 * day))
		return 6;
	if (elapsed < -(7 * day))
		return 7;
	if (elapsed < -(day))
		return 8;
	if (elapsed < 0)
		return 9;
	if (elapsed == 0)
		return 10;
	if (elapsed <= day)
		return 11;
	if (elapsed <= 7 * day)
		return 12;
	if (elapsed <= 14 * day)
		return 13;
	if (elapsed <= 30 * day)
		return 14;
	if (elapsed <= 90 * day)
		return 15;
	if (elapsed <= 365 * day)
		return 16;
	return 17;
}

//--------------------------------
//		Mouse movements
//--------------------------------

ogTasks.mouseMovement = function(task_id, group_id, mouse_is_over){
	if (og.loggedUser.isGuest) return;
	if (mouse_is_over){
		if (!task_id)
			this.groupMouseOver(group_id);
		else
			this.taskMouseOver(task_id, group_id);
		ogTaskEvents.lastTaskId = task_id;
		ogTaskEvents.lastGroupId = group_id;
		
		ogTaskEvents.showGroupHeader = group_id == ogTaskEvents.lastGroupId;
	} else {
		if (!task_id) {
			ogTaskEvents.mouseOutTimeout = setTimeout('ogTasks.groupMouseOut("' + group_id + '")',20);
		} else {
			ogTaskEvents.mouseOutTimeout = setTimeout('ogTasks.taskMouseOut(' + task_id + ',"' + group_id + '")',20);
		}
		ogTaskEvents.lastTaskId = null;
		ogTaskEvents.lastGroupId = null;
	}
}

ogTasks.groupMouseOver = function(group_id){
	var actions = document.getElementById('ogTasksPanelGroupActions' + group_id);
	if (actions) {
		actions.style.opacity = '1.0';
		actions.style.filter = 'alpha(opacity=100)';
	}
}

ogTasks.groupMouseOut = function(group_id){
	if (!ogTaskEvents.lastGroupId || ogTaskEvents.lastGroupId != group_id){
		var actions = document.getElementById('ogTasksPanelGroupActions' + group_id);
		if (actions) {
			actions.style.opacity = '0.35';
			actions.style.filter = 'alpha(opacity=35)';
		}
	}
}


ogTasks.taskMouseOver = function(task_id, group_id){
	var table = document.getElementById('ogTasksPanelTaskTableT' + task_id + 'G' + group_id);
	if (table)
		table.className = 'ogTasksTaskTableSelected';
	var expander = document.getElementById('ogTasksPanelExpanderT' + task_id + 'G' + group_id);
	if (expander)
		expander.style.visibility='visible';
	var actions = document.getElementById('ogTasksPanelTaskActionsT' + task_id + 'G' + group_id);
	if (actions)
		actions.style.visibility='visible';
	this.groupMouseOver(group_id);
}

ogTasks.taskMouseOut = function(task_id, group_id){
	if (!ogTaskEvents.lastTaskId || ogTaskEvents.lastTaskId != task_id){
		var table = document.getElementById('ogTasksPanelTaskTableT' + task_id + 'G' + group_id);
		var chk = document.getElementById('ogTasksPanelChkT' + task_id + 'G' + group_id);
		if (table && chk)
			if (!chk.checked)
				table.className = 'ogTasksTaskTable';
		var expander = document.getElementById('ogTasksPanelExpanderT' + task_id + 'G' + group_id);
		if (expander)
			expander.style.visibility='hidden';
		var actions = document.getElementById('ogTasksPanelTaskActionsT' + task_id + 'G' + group_id);
		if (actions)
			actions.style.visibility='hidden';
		this.groupMouseOut(group_id);
	}
}

ogTasks.flattenTasks = function(tasks){
	var result = [];
	for (var i = 0; i < tasks.length; i++) {
		result = result.concat(tasks[i].flatten());
	}
	return result;
}

ogTasks.existsSoloGroup = function(){
	for (var i = 0; i < this.Groups.length; i++) {
		if (this.Groups[i].solo) {
			return true;
		}
	}
	return false;
}

//Written for edit task view
og.addTaskUserChanged = function(genid, user_id){
	var ddUser = document.getElementById(genid + 'taskFormAssignedTo');
	var chk = document.getElementById(genid + 'taskFormSendNotification');
	if (ddUser && chk){
		var user = ddUser.value;
		chk.checked = (user > 0 && user != user_id);
		document.getElementById(genid + 'taskFormSendNotificationDiv').style.display = user > 0 ? 'block':'none';
	}
}