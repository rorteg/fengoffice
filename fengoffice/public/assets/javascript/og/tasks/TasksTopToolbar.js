/**
 *  TaskManager
 *
 */
 
og.TasksTopToolbar = function(config) {
	Ext.applyIf(config,{
			id: "tasksPanelTopToolbarObject",
			renderTo: "tasksPanelTopToolbar",
			height: 28,
			style:"border:0px none"
		});
		
	og.TasksTopToolbar.superclass.constructor.call(this, config);

	var allTemplates = [];
	var allTemplatesArray = Ext.util.JSON.decode(document.getElementById(config.allTemplatesHfId).value);
	if (allTemplatesArray && allTemplatesArray.length > 0){
		for (var i = 0; i < allTemplatesArray.length; i++){
			allTemplates[allTemplates.length] = {text: allTemplatesArray[i].t,
				iconCls: 'ico-template',
				handler: function() {
					var tid = this.id;
					og.openLink(og.getUrl('template', 'template_parameters', {id: this.id}), {
						callback: function(success, data) {
							if (success) {
								if(data.parameters.length == 0){
									var url = og.getUrl('template', 'instantiate', {id: tid});
									og.openLink(url);
								}else{
									og.openLink(og.getUrl('template', 'instantiate_parameters', {id: tid}));
								}
							}
						}
					});
				},
				scope: allTemplatesArray[i]
			};
		}
	}

	var menuItems = [
		{text: lang('new milestone'), iconCls: 'ico-milestone', handler: function() {
			var url = og.getUrl('milestone', 'add');
			og.openLink(url);
		}},
		{text: lang('new task'), iconCls: 'ico-task', handler: function() {
			var additionalParams = {};
			var toolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
			if (toolbar.filterNamesCompaniesCombo.isVisible()){
				var value = toolbar.filterNamesCompaniesCombo.getValue();
				if (value) 
					additionalParams.assigned_to = value;
			}
			var url = og.getUrl('task', 'add_task');
			og.openLink(url, {post:additionalParams});
		}}/*,
		{text: lang('new task time report'), iconCls: 'ico-reporting', handler: function() {
			var url = og.getUrl('reporting', 'total_task_times_p');
			og.openLink(url);
		}},
		'-'*/];

	var projectTemplates = [];
	var projectTemplatesArray = Ext.util.JSON.decode(document.getElementById(config.projectTemplatesHfId).value);
	if (projectTemplatesArray && projectTemplatesArray.length > 0){
		for (var i = 0; i < projectTemplatesArray.length; i++){
			projectTemplates[projectTemplates.length] = {text: projectTemplatesArray[i].t,
				iconCls: 'ico-template',
				handler: function() {
					var tid = this.id;
					og.openLink(og.getUrl('template', 'template_parameters', {id: this.id}), {
						callback: function(success, data) {
							if (success) {
								if (data.parameters.length == 0) {
									var url = og.getUrl('template', 'instantiate', {id: tid});
									og.openLink(url);
								} else {
									og.openLink(og.getUrl('template', 'instantiate_parameters', {id: tid}));
								}
							}
						}
					});
				},
				scope: projectTemplatesArray[i]
			};
		}
		projectTemplates[projectTemplates.length] = '-';
		menuItems = menuItems.concat(projectTemplates);
	}

	menuItems = menuItems.concat([{
		text: lang('all'),
		iconCls: 'ico-template',
		cls: 'scrollable-menu',
		menu: {
			items: allTemplates
		}}]);

	
	
	var butt = new Ext.Button({
		iconCls: 'ico-new',
		text: lang('new'),
		menu: {
			cls:'scrollable-menu',
			items: menuItems
		}
	});
	
	var markactions = {
		markAsRead: new Ext.Action({
			text: lang('mark as read'),
                        tooltip: lang('mark as read desc'),
                        iconCls: 'ico-mark-as-read',
			disabled: true,
			handler: function() {
				ogTasks.executeAction('markasread');
			},
			scope: this
		}),
		markAsUnread: new Ext.Action({
			text: lang('mark as unread'),
                        tooltip: lang('mark as unread desc'),
                        iconCls: 'ico-mark-as-read',
			disabled: true,
			handler: function() {
				ogTasks.executeAction('markasunread');
			},
			scope: this
		})
	};
	this.markactions = markactions;
	
	var actions = {
		del: new Ext.Action({
			text: lang('move to trash'),
                        tooltip: lang('move selected objects to trash'),
                        iconCls: 'ico-trash',
			disabled: true,
			handler: function() {
                            var ids = ogTasks.getSelectedIds()+'';
                            var arr_ids = ids.split(',')
                            for(var i = 0; i < arr_ids.length; i++){
                                var related = og.checkRelated("task",arr_ids[i]);
                                if(related){
                                    break;    
                                }                                
                            }
                            
                            if(related){
                                this.dialog = new og.TaskPopUp("delete",'');
                                this.dialog.setTitle(lang('tasks related'));	                                
                                this.dialog.show();
                            }else{
                                if (confirm(lang('confirm move to trash'))) {
                                        ogTasks.executeAction('delete');
                                }  
                            }
                            
			},
			scope: this
		}),
		complete: new Ext.Action({
			text: lang('do complete'),
                        tooltip: lang('complete selected tasks'),
                        iconCls: 'ico-complete',
			disabled: true,
			handler: function() {
                                var ids = ogTasks.getSelectedIds();
                                var related = false;
                                for(var i = 0; i < ids.length; i++){
                                    var task = ogTasks.getTask(ids[i]);
                                    for(var j = 0; j < task.subtasks.length; j++){
                                        if(task.subtasks[j].status == 0){
                                            related = true;
                                        }                                        
                                        if(related){
                                            break;    
                                        }
                                    }                             
                                }

                                if(related){
                                    this.dialog = new og.TaskCompletePopUp('');
                                    this.dialog.setTitle(lang('do complete'));	                                
                                    this.dialog.show();
                                }else{
                                    ogTasks.executeAction('complete');
                                }
			},
			scope: this
		}),
		markAs: new Ext.Action({
			text: lang('mark as'),
			tooltip: lang('mark as desc'),
			menu: [
				markactions.markAsRead,
				markactions.markAsUnread
			]
		}),
		archive: new Ext.Action({
			text: lang('archive'),
                        tooltip: lang('archive selected object'),
                        iconCls: 'ico-archive-obj',
			disabled: true,
			handler: function() {
                                this.dialog = new og.TaskPopUp("archive",'');
                                this.dialog.setTitle(lang('tasks related'));	                                
                                this.dialog.show();
//				if (confirm(lang('confirm archive selected objects'))) {
//					ogTasks.executeAction('archive');
//				}
			},
			scope: this
		})
	};
	this.actions = actions;
	
    

    
    
    //Add stuff to the toolbar
	if (!og.loggedUser.isGuest) {
		this.add(butt);
		this.addSeparator();
		this.add(actions.complete);
		this.add(actions.archive);
		this.add(actions.del);		
		this.addSeparator();
	}
	this.add(actions.markAs);
	this.addSeparator();
	
	this.displayOptions = {
			time: {
		        text: lang('time'),
				checked: (ogTasks.userPreferences.showTime == 1),
				checkHandler: function() {
					ogTasks.redrawGroups = false;
					ogTasks.draw();
					ogTasks.redrawGroups = true;
					var url = og.getUrl('account', 'update_user_preference', {name: 'tasksShowTime', value:(this.checked?1:0)});
					og.openLink(url,{hideLoading:true});
				}
			},
			dates: {
		        text: lang('dates'),
				checked: (ogTasks.userPreferences.showDates == 1),
				checkHandler: function() {
					ogTasks.redrawGroups = false;
					ogTasks.draw();
					ogTasks.redrawGroups = true;
					var url = og.getUrl('account', 'update_user_preference', {name: 'tasksShowDates', value:(this.checked?1:0)});
					og.openLink(url,{hideLoading:true});
				}
			},
			empty_milestones: {
		        text: lang('empty milestones'),
				checked: (ogTasks.userPreferences.showEmptyMilestones == 1),
				checkHandler: function() {
					ogTasks.userPreferences.showEmptyMilestones = 1 - ogTasks.userPreferences.showEmptyMilestones;
					ogTasks.redrawGroups = false;
					ogTasks.draw();
					ogTasks.redrawGroups = true;
					var url = og.getUrl('account', 'update_user_preference', {name: 'tasksShowEmptyMilestones', value:(this.checked?1:0)});
					og.openLink(url,{hideLoading:true});
				}
			},
                        time_estimates: {
		        text: lang('time estimates'),
				checked: (ogTasks.userPreferences.showTimeEstimates == 1),
				checkHandler: function() {
					ogTasks.redrawGroups = false;
					ogTasks.draw();
					ogTasks.redrawGroups = true;
					var url = og.getUrl('account', 'update_user_preference', {name: 'tasksShowTimeEstimates', value:(this.checked?1:0)});
					og.openLink(url,{hideLoading:true});
				}
			}
		};

	this.show_menu = new Ext.Action({
	       	iconCls: 'op-ico-details',
			text: lang('show'),
			menu: {items: [
				this.displayOptions.time,
				this.displayOptions.dates,
				this.displayOptions.empty_milestones,
                                this.displayOptions.time_estimates
			]}
		});
	this.add(this.show_menu);
	
    this.add('-');
    
    this.add(new Ext.Action({
      id: 'button-print',
      text: lang('print'),
      tooltip: lang('print all groups'),
      iconCls: 'ico-print',
      handler: function() {
	ogTasks.printAllGroups();
      },
      scope: this
    }));
    
    
    Ext.get('button-print').set({
    	id: "tasks_print_btn"
    });


    
    
    if (ogTasks.extraTopToolbarItems) {
    	for (i=0; i<ogTasks.extraTopToolbarItems.length; i++) {
    		this.add(ogTasks.extraTopToolbarItems[i]);
    	}
    }
};

function ogTasksLoadFilterValuesCombo(newValue){
	var combo = Ext.getCmp('ogTasksFilterValuesCombo');
}

function ogTasksOrderUsers(usersList){
	for (var i = 0; i < usersList.length - 1; i++)
		for (var j = i+1; j < usersList.length; j++)
			if (usersList[i][1].toUpperCase() > usersList[j][1].toUpperCase()){
				var aux = usersList[i];
				usersList[i] = usersList[j];
				usersList[j] = aux;
			}
	return usersList;
}

Ext.extend(og.TasksTopToolbar, Ext.Toolbar, {
	getDrawOptions : function(){
		return {
			show_time : this.show_menu.items[0].menu.items.items[0].checked,
			show_dates : this.show_menu.items[0].menu.items.items[1].checked,
			show_ms : this.show_menu.items[0].menu.items.items[2].checked,
                        show_time_estimates : this.show_menu.items[0].menu.items.items[3].checked
		}
	},
	updateCheckedStatus : function(){
		var checked = false;
		var allIncomplete = true, allUnread = true, allRead = true;
		for (var i = 0; i < ogTasks.Tasks.length; i++)
			if (ogTasks.Tasks[i].isChecked) {
				checked = true;
				if (ogTasks.Tasks[i].status == 1) {
					allIncomplete = false;
				}
				if (ogTasks.Tasks[i].isRead) {
					allUnread = false;
				} else {
					allRead = false;
				}
			}
		
		if (!checked){
			this.actions.del.disable();
			this.actions.complete.disable();
			this.actions.archive.disable();
			this.markactions.markAsRead.disable();
			this.markactions.markAsUnread.disable();
		} else {
			this.actions.del.enable();
			this.actions.archive.enable();
			if (allUnread) {
				this.markactions.markAsUnread.disable();
			} else {
				this.markactions.markAsUnread.enable();
			}
			if (allRead) {
				this.markactions.markAsRead.disable();
			} else {
				this.markactions.markAsRead.enable();
			}
			if (allIncomplete) {
				this.actions.complete.enable();
			} else {
				this.actions.complete.disable();
			}
				
		}
		
	}
});

Ext.reg("tasksTopToolbar", og.TasksTopToolbar);
