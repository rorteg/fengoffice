/**
 *  TimeManager
 *
 */

var ogTimeManager = {};
ogTimeManager.Tasks = [];
ogTimeManager.Timeslots = [];
ogTimeManager.Users = [];
ogTimeManager.Companies = [];
ogTimeEvents = {};

ogTimeTimeslot = function(){
	this.id;
	this.date;
	this.time;
	this.workspaceId;
	this.userId;
	this.userName;
	this.lastUpdated;
	this.lastUpdatedBy;
	this.hourlyBilling;
	this.totalBilling;

	this.description = '';
	this.taskName;
}

ogTimeTimeslot.prototype.setFromTdata = function(tdata){
	this.id = tdata.id;
	this.date = tdata.date;
	this.time = tdata.time;
	this.workspaceId = tdata.pid;
	this.userId = tdata.uid;
	this.userName = tdata.uname;
	this.lastUpdated = tdata.lastupdated;
	this.lastUpdatedBy = tdata.lastupdatedby;
	this.hourlyBilling = tdata.hourlybilling || 0;
	this.totalBilling = tdata.totalbilling || 0;
	
	if (tdata.desc)	this.description = tdata.desc; else this.description = '';
	if (tdata.tn)	this.taskName = tdata.tn; else this.taskName = null;
}



//************************************
//*		Data loading
//************************************

ogTimeManager.loadDataFromHF = function(genid){
	var result = [];
	result['tasks'] = Ext.util.JSON.decode(document.getElementById(genid + 'hfTasks').value);
	result['users'] = Ext.util.JSON.decode(document.getElementById(genid + 'hfUsers').value);
	result['timeslots'] = Ext.util.JSON.decode(document.getElementById(genid + 'hfTimeslots').value);
	result['companies'] = Ext.util.JSON.decode(document.getElementById(genid + 'hfCompanies').value);
	this.genid = genid;
	
	return this.loadData(result);
}


ogTimeManager.loadData = function(data){
	var i;
	this.Tasks = [];
	for (i in data['tasks']){
		var tdata = data['tasks'][i];
		if (tdata.id){
			var task = new ogTasksTask();
			task.setFromTdata(tdata);
			if (tdata.s)
				task.statusOnCreate = tdata.s;
			this.Tasks[this.Tasks.length] = task;
		}
	}
	
	this.Users = [];
	for (i in data['users']){
		var udata = data['users'][i];
		if (udata.id){
			var user =  new ogTasksUser(udata.id,udata.name,udata.cid);
			this.Users[this.Users.length] = user;
			if (udata.isCurrent)
				this.currentUser = user;
		}
	}
	
	this.Timeslots = [];
	for (i in data['timeslots']){
		var tdata = data['timeslots'][i];
		if (tdata.id){
			var timeslot =  new ogTimeTimeslot();
			timeslot.setFromTdata(tdata);
			this.Timeslots[this.Timeslots.length] = timeslot;
		}
	}
	
	this.Companies = [];
	for (i in data['companies']){
		var cdata = data['companies'][i];
		if (cdata.id)
			this.Companies[this.Companies.length] = new ogTasksCompany(cdata.id,cdata.name);
	}
}



//************************************
//*		Methods
//************************************



ogTimeManager.GetNewTimeslotParameters = function(genid){
	var parameters = [];
	parameters["timeslot[date]"] = Ext.getCmp(genid + "timeslot[date]Cmp").getValue().format(og.preferences['date_format']);
	parameters["timeslot[project_id]"] = document.getElementById(genid + 'wsSelValue').value;
	parameters["timeslot[hours]"] = document.getElementById(genid + 'tsHours').value;
	parameters["timeslot[description]"] = document.getElementById(genid + 'tsDesc').value;
	var userSel = document.getElementById(genid + 'tsUser');
	if (userSel){
		parameters["timeslot[user_id]"] = userSel.value;
	}
	parameters["timeslot[id]"] = document.getElementById(genid + 'tsId').value;
	
	return parameters;
}

ogTimeManager.insertTimeslot = function(timeslot, genid){
	for (var i = 0; i < this.Timeslots.length; i++){
		if (this.Timeslots[i].date <= timeslot.date){
			this.Timeslots.splice(i,0,timeslot);
			this.drawTimespans(genid);
			return;
		}
	}
	this.Timeslots[this.Timeslots.length] = timeslot;
	this.drawTimespans(genid);
}

ogTimeManager.SubmitNewTimeslot = function(genid){
	var parameters = this.GetNewTimeslotParameters(genid);
	var isEdit = document.getElementById(this.genid + 'TMTimespanSubmitEdit').style.display == 'block';
	var action = 'add_project_timeslot';
	if (isEdit)
		action = 'edit_project_timeslot';

	og.openLink(og.getUrl('time', action), {
		method: 'POST',
		post: parameters,
		callback: function(success, data) {
			if (success && !data.errorCode) {
				var timeslot = new ogTimeTimeslot();
				timeslot.setFromTdata(data.timeslot);
				if (isEdit){
					this.deleteTimeslot(timeslot.id);
					this.CancelEdit();
				}
				document.getElementById(genid + 'tsDesc').value = '';
				document.getElementById(genid + 'tsHours').value = 0;
				this.insertTimeslot(timeslot, genid);
				
				og.showWsPaths(genid + 'TMTimespanTable');
			} else {
				if (!data.errorMessage || data.errorMessage == '')
					og.err(lang("error adding timeslot"));
			}
		},
		scope: this
	});
}

ogTimeManager.DeleteTimeslot = function(timeslotId){
	og.openLink(og.getUrl('time', 'delete_project_timeslot', {id:timeslotId}), {
		method: 'POST',
		callback: function(success, data) {
			if (success && !data.errorCode) {
				this.deleteTimeslot(data.timeslotId);
				this.drawTimespans(this.genid);
			} else {
				og.err(lang("error adding timeslot"));
			}
		},
		scope: this
	});
}

ogTimeManager.CancelEdit = function(){
	document.getElementById(this.genid + 'TMTimespanSubmitEdit').style.display = 'none';
	document.getElementById(this.genid + 'TMTimespanSubmitAdd').style.display = 'block';
	document.getElementById(this.genid + 'TMTimespanAddNew').className = 'TMTimespanAddNew';
	
	document.getElementById(this.genid + 'tsHours').value = '0';
	document.getElementById(this.genid + 'tsDesc').value = '';
	var datePick = Ext.getCmp(this.genid + 'timeslot[date]Cmp');
	if (datePick){
		datePick.setValue(new Date());
	}
}

ogTimeManager.EditTimeslot = function(timeslotId){
	var ts = this.getTimeslot(timeslotId);
	if (ts){
		document.getElementById(this.genid + 'TMTimespanSubmitEdit').style.display = 'block';
		document.getElementById(this.genid + 'TMTimespanSubmitAdd').style.display = 'none';
		document.getElementById(this.genid + 'TMTimespanAddNew').className = 'TMTimespanEdit';
		
		document.getElementById(this.genid + 'tsHours').value = (ts.time / 3600);
		document.getElementById(this.genid + 'tsDesc').value = ts.description;
		document.getElementById(this.genid + 'tsId').value = timeslotId;
		og.drawWorkspaceSelector(this.genid + "wsSel", ts.workspaceId, 'timeslot[project_id]', false);
		var userSel = document.getElementById(this.genid + 'tsUser');
		if (userSel && userSel.options){
			for (var i = 0; i < userSel.options.length; i++){
				if (userSel.options[i].value == ts.userId){
					userSel.selectedIndex = i;
					break;	
				}
			}
		}
		var datePick = Ext.getCmp(this.genid + 'timeslot[date]Cmp');
		if (datePick){
			datePick.setValue(new Date(ts.date * 1000));
		}
		document.getElementById(this.genid + 'tsHours').focus();
	}
}


ogTimeManager.deleteTimeslot = function(id){
	for (var i = 0; i < this.Timeslots.length; i++)
		if (this.Timeslots[i].id == id){
			this.Timeslots.splice(i,1);
			return;
		}
}

ogTimeManager.getTimeslot = function(id){
	for (var i = 0; i < this.Timeslots.length; i++)
		if (this.Timeslots[i].id == id)
			return this.Timeslots[i];
	return null;
}

ogTimeManager.getTask = function(id){
	for (var i = 0; i < this.Tasks.length; i++)
		if (this.Tasks[i].id == id)
			return this.Tasks[i];
	return null;
}

ogTimeManager.getUser = function(id){
	for (var i = 0; i < this.Users.length; i++)
		if (this.Users[i].id == id)
			return this.Users[i];
	return null;
}

ogTimeManager.getUserCompanyName = function(assigned_to){
	var split = assigned_to.split(':');
	var name = '';
	if (split[1] > 0){ //Look for user
		var user = this.getUser(split[1]);
		if (user)
			name = user.name;
	} else { //Look for company
		if (split[0] > 0){
			var company = this.getCompany(split[0]);
			if (company)
				name = company.name;
		}
	}
	return name;
}


ogTimeManager.mouseMovement = function(task_id, mouse_is_over){
	if (mouse_is_over){
		this.taskMouseOver(task_id);
		ogTimeEvents.lastTaskId = task_id;
	} else {
		ogTimeEvents.mouseOutTimeout = setTimeout('ogTimeManager.taskMouseOut(' + task_id + ')',20);
		ogTimeEvents.lastTaskId = null;
	}
}

ogTimeManager.taskMouseOver = function(task_id){
	var table = document.getElementById('ogTimePanelTaskTableT' + task_id);
	if (table)
		table.className = 'ogTasksTaskTableSelected';
	var actions = document.getElementById('ogTimePanelTaskActionsT' + task_id);
	if (actions)
		actions.style.visibility='visible';
}

ogTimeManager.taskMouseOut = function(task_id){
	if (!ogTimeEvents.lastTaskId || ogTimeEvents.lastTaskId != task_id){
		var table = document.getElementById('ogTimePanelTaskTableT' + task_id);
		if (table)
			table.className = 'ogTasksTaskTable';
		var actions = document.getElementById('ogTimePanelTaskActionsT' + task_id);
		if (actions)
			actions.style.visibility='hidden';
	}
}


ogTimeManager.executeAction = function(actionName, ids, options){
	
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
					/*if (actionName == 'close_work')
						ogTimeManager.removeTask(tdata.id);
					else {*/
						var task = ogTimeManager.getTask(tdata.id);
						task.setFromTdata(tdata);
					//}
				}
				this.drawTasks(this.genid);
			} else {
			
			}
		},
		scope: this
	});
}


ogTimeManager.removeTask = function(id){
	for (var i = 0; i < this.Tasks.length; i++)
		if (this.Tasks[i].id == id){
			this.Tasks.splice(i,1);
			return true;
		}
	return false;
}


ogTimeManager.closeTimeslot = function(tgId){
	var panel = document.getElementById('ogTimePanelCWD' + tgId);
	if (panel.style.display == 'block')
		panel.style.display = 'none';
	else {
		panel.style.display = 'block';
		document.getElementById('ogTimePanelCWDescription' + tgId).focus();
	}
}

ogTimeManager.ToggleCompleteStatus = function(task_id, status){
	var action = (status == 0)? 'complete_task' : 'open_task';
	
	og.openLink(og.getUrl('task', action, {id: task_id, quick: true}), {
		callback: function(success, data) {
			if (!success || data.errorCode) {
			} else {
				//Set task data
				var task = this.getTask(task_id);
				task.status = (status == 0)? 1 : 0;
				task.completedById = this.currentUser.id;
				var today = new Date();
				today = today.clearTime();
				task.completedOn = (today.format('U'));
				
				this.drawTasks(this.genid);
			}
		},
		scope: this
	});
}


ogTimeManager.getCompany = function(id){
	for (var i = 0; i < this.Companies.length; i++)
		if (this.Companies[i].id == id)
			return this.Companies[i];
	return null;
}