/**
 *  TaskManager
 *
 */

og.TasksBottomToolbar = function(config) {
	Ext.applyIf(config,
		{
			id:"tasksPanelBottomToolbarObject",
			renderTo: "tasksPanelBottomToolbar",
			height: 28,
			style:"border:0px none"
		});
		
	og.TasksBottomToolbar.superclass.constructor.call(this, config);
	
    this.groupcombo = new Ext.form.ComboBox({
        store: new Ext.data.SimpleStore({
	        fields: ['value', 'text'],
	        data : [['nothing', '--' + lang('nothing (groups)') + '--']
	        	,['milestone', lang('milestone')]
	        	,['priority',lang('priority')]
	        	,['workspace', lang('workspace')]
	        	,['assigned_to', lang('assigned to')]
	        	,['due_date', lang('due date')]
	        	,['start_date', lang('start date')]
	        	,['created_on', lang('created on')]
	        	,['created_by', lang('created by')]
	        	,['completed_on', lang('completed on')]
	        	,['completed_by', lang('completed by')]
	        	,['status', lang('status')]
	        	,['tag', lang('tag')]
	        	,['subtype', lang('object type')]]
	    	}),
        displayField:'text',
        typeAhead: true,
        mode: 'local',
        triggerAction: 'all',
        selectOnFocus:true,
        width:120,
        valueField: 'value',
        listeners: {
        	'select' : function(combo, record) {
        		ogTasks.setAllCheckedValue(false);
        		ogTasks.setAllExpandedValue(false);
				ogTasks.draw();
        		var url = og.getUrl('account', 'update_user_preference', {name: 'tasksGroupBy', value:record.data.value});
				og.openLink(url,{hideLoading:true});
        	}
        }
    });
    this.groupcombo.setValue(ogTasks.userPreferences.groupBy);
	
    this.ordercombo = new Ext.form.ComboBox({
        store: new Ext.data.SimpleStore({
	        fields: ['value', 'text'],
	        data : [['priority',lang('priority')]
	        	,['workspace', lang('workspace')]
	        	,['name', lang('task name')]
	        	,['due_date', lang('due date')]
	        	,['created_on', lang('created on')]
	        	,['completed_on', lang('completed on')]
	        	,['assigned_to', lang('assigned to')]
	        	,['start_date', lang('start date')]]
	    	}),
        displayField:'text',
        typeAhead: true,
        mode: 'local',
        triggerAction: 'all',
        selectOnFocus:true,
        width:120,
        valueField: 'value',
        listeners: {
        	'select' : function(combo, record) {
				ogTasks.redrawGroups = false;
				ogTasks.draw();
				ogTasks.redrawGroups = true;
        		var url = og.getUrl('account', 'update_user_preference', {name: 'tasksOrderBy', value:record.data.value});
				og.openLink(url,{hideLoading:true});
        	}
        }
    });
    this.ordercombo.setValue(ogTasks.userPreferences.orderBy);
    
    this.filtercombo = new Ext.form.ComboBox({
        store: new Ext.data.SimpleStore({
	        fields: ['value', 'text'],
	        data : [['no_filter','--' + lang('no filter') + '--']
	        	,['created_by',lang('created by')]
	        	,['completed_by', lang('completed by')]
	        	,['assigned_to', lang('assigned to')]
	        	,['assigned_by', lang('assigned by')]
	        	,['milestone', lang('milestone')]
	        	,['priority', lang('priority')]
	        	,['subtype', lang('object type')]]
	    }),
        displayField:'text',
        typeAhead: true,
        mode: 'local',
        triggerAction: 'all',
        selectOnFocus:true,
        width:100,
        valueField: 'value',
        listeners: {
        	'select' : function(combo, record) {
        		switch(record.data.value){
        			case 'no_filter':
        				Ext.getCmp('ogTasksFilterNamesCombo').hide();
        				Ext.getCmp('ogTasksFilterNamesCompaniesCombo').hide();
        				Ext.getCmp('ogTasksFilterMilestonesCombo').hide();
        				Ext.getCmp('ogTasksFilterPriorityCombo').hide();
        				Ext.getCmp('ogTasksFilterSubtypeCombo').hide();
						var toolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
        				toolbar.load();
        				break;
        			case 'milestone':
        				Ext.getCmp('ogTasksFilterNamesCombo').hide();
        				Ext.getCmp('ogTasksFilterNamesCompaniesCombo').hide();
        				Ext.getCmp('ogTasksFilterMilestonesCombo').show();
        				Ext.getCmp('ogTasksFilterMilestonesCombo').setValue('');
        				Ext.getCmp('ogTasksFilterPriorityCombo').hide();
        				Ext.getCmp('ogTasksFilterSubtypeCombo').hide();
        				break;
        			case 'priority':
        				Ext.getCmp('ogTasksFilterNamesCombo').hide();
        				Ext.getCmp('ogTasksFilterNamesCompaniesCombo').hide();
        				Ext.getCmp('ogTasksFilterMilestonesCombo').hide();
        				Ext.getCmp('ogTasksFilterPriorityCombo').show();
        				Ext.getCmp('ogTasksFilterPriorityCombo').setValue('');
        				Ext.getCmp('ogTasksFilterSubtypeCombo').hide();
        				break;
        			case 'assigned_to':
        				Ext.getCmp('ogTasksFilterNamesCombo').hide();
        				Ext.getCmp('ogTasksFilterNamesCompaniesCombo').show();
        				Ext.getCmp('ogTasksFilterNamesCompaniesCombo').setValue('');
        				Ext.getCmp('ogTasksFilterMilestonesCombo').hide();
        				Ext.getCmp('ogTasksFilterPriorityCombo').hide();
        				Ext.getCmp('ogTasksFilterSubtypeCombo').hide();
        				break;
        			case 'subtype':
        				Ext.getCmp('ogTasksFilterNamesCombo').hide();
        				Ext.getCmp('ogTasksFilterNamesCompaniesCombo').hide();
        				Ext.getCmp('ogTasksFilterNamesCompaniesCombo').setValue('');
        				Ext.getCmp('ogTasksFilterMilestonesCombo').hide();
        				Ext.getCmp('ogTasksFilterPriorityCombo').hide();
        				Ext.getCmp('ogTasksFilterSubtypeCombo').show();
        				break;
        			default:
        				Ext.getCmp('ogTasksFilterNamesCombo').show();
        				Ext.getCmp('ogTasksFilterNamesCombo').setValue('');
        				Ext.getCmp('ogTasksFilterNamesCompaniesCombo').hide();
        				Ext.getCmp('ogTasksFilterMilestonesCombo').hide();
        				Ext.getCmp('ogTasksFilterPriorityCombo').hide();
        				Ext.getCmp('ogTasksFilterSubtypeCombo').hide();
        				break;
        		}
        	}
        }
    });
    this.filtercombo.setValue(ogTasks.userPreferences.filter);

    
    
    var currentUser = '';
    var usersArray = Ext.util.JSON.decode(document.getElementById(config.usersHfId).value);
    var companiesArray = Ext.util.JSON.decode(document.getElementById(config.companiesHfId).value);
    for (i in usersArray){
		if (usersArray[i].isCurrent)
			currentUser = usersArray[i].cid + ':' + usersArray[i].id;
	}
	var ucsData = [[currentUser, lang('me')],['0:0',lang('everyone')],['-1:-1', lang('unassigned')],['0:0','--']];
	for (i in companiesArray)
		if (companiesArray[i].id) ucsData[ucsData.length] = [(companiesArray[i].id + ':0'), companiesArray[i].name];
	ucsData[ucsData.length] = ['0:0','--'];
	ucsOtherUsers = [];
	for (i in usersArray){
		var companyName = '';
		var j;
		for(j in companiesArray)
			if (companiesArray[j] && companiesArray[j].id == usersArray[i].cid)
				companyName = companiesArray[j].name;
		if (usersArray[i] && usersArray[i].cid) 
			ucsOtherUsers[ucsOtherUsers.length] = [(usersArray[i].cid + ':' + usersArray[i].id), usersArray[i].name + ' : ' + companyName];
		if (usersArray[i].isCurrent)
			currentUser = usersArray[i].cid + ':' + usersArray[i].id;
	}
	ucsData = ucsData.concat(ogTasksOrderUsers(ucsOtherUsers));
    
    this.filterNamesCompaniesCombo = new Ext.form.ComboBox({
    	id: 'ogTasksFilterNamesCompaniesCombo',
        store: new Ext.data.SimpleStore({
	        fields: ['value', 'text'],
	        data : ucsData
	    }),
	    hidden: ogTasks.userPreferences.filter != 'assigned_to',
        displayField:'text',
        typeAhead: true,
        mode: 'local',
        triggerAction: 'all',
        selectOnFocus:true,
        width:140,
        valueField: 'value',
        emptyText: (lang('select user or group') + '...'),
        valueNotFoundText: '',
        listeners: {
        	'select' : function(combo, record) {
				var toolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
        		if (toolbar.filterNamesCompaniesCombo == this)
        			toolbar.load();
        		else{
        			if (this.initialConfig.isInternalSelector)
        				ogTasks.UserCompanySelected(this.initialConfig.controlName, record.data.value, this.initialConfig.taskId);
        		}
        	}
        }
    });
    this.filterNamesCompaniesCombo.setValue(ogTasks.userPreferences.filterValue);
    
    for (i in usersArray){
		if (usersArray[i].isCurrent)
			currentUser = usersArray[i].id;
	}
	var uData = [[currentUser, lang('me')],['0',lang('everyone')],['0','--']];
	uDOtherUsers = [];
	for (i in usersArray){
		if (usersArray[i] && !usersArray[i].isCurrent && usersArray[i].id)
			uDOtherUsers[uDOtherUsers.length] = [usersArray[i].id, usersArray[i].name];
	}
	uData = uData.concat(ogTasksOrderUsers(uDOtherUsers));
    this.filterNamesCombo = new Ext.form.ComboBox({
    	id: 'ogTasksFilterNamesCombo',
        store: new Ext.data.SimpleStore({
	        fields: ['value', 'text'],
	        data : uData
	    }),
	    hidden: (ogTasks.userPreferences.filter == 'milestone' || ogTasks.userPreferences.filter == 'priority' || ogTasks.userPreferences.filter == 'assigned_to' || ogTasks.userPreferences.filter == 'subtype' || ogTasks.userPreferences.filter == 'no_filter'),
        displayField:'text',
        typeAhead: true,
        mode: 'local',
        triggerAction: 'all',
        selectOnFocus:true,
        width:140,
        valueField: 'value',
        emptyText: (lang('select user or group') + '...'),
        valueNotFoundText: '',
        listeners: {
        	'select' : function(combo, record) {
				var toolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
        		toolbar.load();
        	}
		}
	});
    this.filterNamesCombo.setValue(ogTasks.userPreferences.filterValue);
    
    this.filterPriorityCombo = new Ext.form.ComboBox({
    	id: 'ogTasksFilterPriorityCombo',
        store: new Ext.data.SimpleStore({
			fields: ['value', 'text'],
			data : [[100, lang('low')],[200, lang('normal')],[300, lang('high')],[400, lang('urgent')]]
	    }),
	    hidden: ogTasks.userPreferences.filter != 'priority',
        displayField:'text',
        typeAhead: true,
        mode: 'local',
        triggerAction: 'all',
        selectOnFocus:true,
        width:140,
        valueField: 'value',
        emptyText: (lang('select priority') + '...'),
        valueNotFoundText: '',
        listeners: {
        	'select' : function(combo, record) {
				var toolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
        		if (toolbar.filterPriorityCombo == this)
        			toolbar.load();
        	}
        }
    });
    this.filterPriorityCombo.setValue(ogTasks.userPreferences.filterValue);
    
    var subtypesArray = Ext.util.JSON.decode(document.getElementById(config.subtypesHfId).value);
    var subtypes_data = [[0, lang('all')]];
    for (i=0; i<subtypesArray.length; i++) {
    	var ost = subtypesArray[i];
    	subtypes_data[subtypes_data.length] = [ost.id, ost.name];
    }
    this.filterSubtypeCombo = new Ext.form.ComboBox({
    	id: 'ogTasksFilterSubtypeCombo',
        store: new Ext.data.SimpleStore({
			fields: ['value', 'text'],
			data : subtypes_data
	    }),
	    hidden: ogTasks.userPreferences.filter != 'subtype',
        displayField:'text',
        typeAhead: true,
        mode: 'local',
        triggerAction: 'all',
        selectOnFocus:true,
        width:140,
        valueField: 'value',
        emptyText: '...',
        valueNotFoundText: '',
        listeners: {
        	'select' : function(combo, record) {
				var toolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
        		if (toolbar.filterSubtypeCombo == this)
        			toolbar.load();
        	}
        }
    });
    this.filterSubtypeCombo.setValue(ogTasks.userPreferences.filterValue);
    
    
    var milestones = Ext.util.JSON.decode(document.getElementById(config.internalMilestonesHfId).value);
    milestones = milestones.concat(Ext.util.JSON.decode(document.getElementById(config.externalMilestonesHfId).value));
    milestonesData = [[0,"--" + lang('none') + "--"]];
    for (i in milestones){
    	if (milestones[i].id)
    		milestonesData[milestonesData.length] = [milestones[i].id, milestones[i].t];
    }
    this.filterMilestonesCombo = new Ext.form.ComboBox({
    	id: 'ogTasksFilterMilestonesCombo',
        store: new Ext.data.SimpleStore({
	        fields: ['value', 'text'],
	        data : milestonesData,
	        sortInfo: {field:'text',direction:'ASC'}
	    }),
	    hidden: (ogTasks.userPreferences.filter != 'milestone'),
        displayField:'text',
        typeAhead: true,
        mode: 'local',
        triggerAction: 'all',
        selectOnFocus:true,
        width:140,
        valueField: 'value',
        emptyText: (lang('select milestone') + '...'),
        valueNotFoundText: '',
        listeners: {
        	'select' : function(combo, record) {
				var toolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
        		if (toolbar.filterMilestonesCombo == this)
        			toolbar.load();
        	}
        }
    });
    this.filterMilestonesCombo.setValue(ogTasks.userPreferences.filterValue);
	
	
    this.statusCombo = new Ext.form.ComboBox({
    	id: 'ogTasksStatusCombo',
        store: new Ext.data.SimpleStore({
	        fields: ['value', 'text'],
	        data : [[2, lang('all')],[0, lang('pending')],[1, lang('complete')], [10, lang('active')], [11, lang('overdue')], [12, lang('today')], [13, lang('overdue')+"+"+lang('today')], [20, lang('my active')], [21, lang('my subscribed')] ]
	    }),
        displayField:'text',
        typeAhead: true,
        mode: 'local',
        triggerAction: 'all',
        selectOnFocus:true,
        width:120,
        valueField: 'value',
        listeners: {
        	'select' : function(combo, record) {
				var toolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
        		toolbar.load();
        	}
        }
    });
    this.statusCombo.setValue(ogTasks.userPreferences.status);
    this.add(lang('filter') + ':');
    this.add(this.filtercombo);
    this.add(this.filterNamesCombo);
    this.add(this.filterNamesCompaniesCombo);
    this.add(this.filterPriorityCombo);
    this.add(this.filterSubtypeCombo);
    this.add(this.filterMilestonesCombo);
    this.add('&nbsp;&nbsp;&nbsp;' + lang('status') + ':');
    this.add(this.statusCombo);
    
	this.add('&nbsp;&nbsp;&nbsp;' + lang('group by') + ':');
    this.add(this.groupcombo);
    this.add('&nbsp;&nbsp;&nbsp;' + lang('order by') + ':');
    this.add(this.ordercombo);
};

Ext.extend(og.TasksBottomToolbar, Ext.Toolbar, {
	load: function(params) {
		if (!params) params = {};
		Ext.apply(params,this.getFilters());
		og.openLink(og.getUrl('task','new_list_tasks',params));
	},
	getDisplayCriteria : function(){
		return {
			group_by : this.groupcombo.getValue(),
			order_by : this.ordercombo.getValue()
		}
	},
	getFilters : function(){
		var filterValue;
		switch(this.filtercombo.getValue()){
			case 'milestone':
				filterValue = this.filterMilestonesCombo.getValue();
				break;
			case 'priority':
				filterValue = this.filterPriorityCombo.getValue();
				break;
			case 'subtype':
				filterValue = this.filterSubtypeCombo.getValue();
				break;
			case 'assigned_to':
				filterValue = this.filterNamesCompaniesCombo.getValue();
				break;
			default:
				filterValue = this.filterNamesCombo.getValue();
				break;
		}
		
		return {
			status: this.statusCombo.getValue(),
			filter:this.filtercombo.getValue(),
			fval:filterValue
		}
	},
	cloneUserCompanyCombo : function(newId){
		var clone = this.filterNamesCompaniesCombo.cloneConfig({id:newId});
		
		return clone;
	}
	 
});

Ext.reg("TasksBottomToolbar", og.TasksBottomToolbar);