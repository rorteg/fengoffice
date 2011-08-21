/**
 *  LinkedObjectManager
 *
 */
og.LinkedObjectManager = function(config) {
	if (!config.filter_manager)
		config.filter_manager = '';
	Ext.apply(this, config);
	
	var actions, moreActions;
	
	var objId = config.linked_object;
	var objManager = config.linked_manager;
	var objName = config.linked_object_name;
	
	if (objName.length > 20){		
		objName = objName.substring(0,17) + "...";
	}
	var objIco = config.linked_object_ico;
	
	this.doNotRemove = true;
	this.needRefresh = false;
	
	if (!og.LinkedObjectManager.store) {
		og.LinkedObjectManager.store = new Ext.data.Store({
			proxy: new og.GooProxy({
				url: og.getUrl('object', 'list_objects')
			}),
			reader: new Ext.data.JsonReader({
				root: 'objects',
				totalProperty: 'totalCount',
				id: 'id',
				fields: [
					'name', 'object_id', 'type', 'tags', 
					'createdBy', 'createdById', 'dateCreated',
					'updatedBy', 'updatedById',	'dateUpdated',
					'icon', 'wsIds', 'manager', 'mimeType', 'url', 'ix'
				]
			}),
			remoteSort: true,
			listeners: {
				'load': function() {
					var d = this.reader.jsonData;
					var ws = og.clean(Ext.getCmp('workspace-panel').getActiveWorkspace().name);
					var tag = og.clean(Ext.getCmp('tag-panel').getSelectedTag());
					if (d.totalCount == 0) {
						if (tag) {
							this.fireEvent('messageToShow', lang("no objects with tag message", lang("objects"), ws, tag));
						} else {
							this.fireEvent('messageToShow', lang("no objects message", lang("objects"), ws));
						}
					} else if (d.objects.length == 0) {
						this.fireEvent('messageToShow', lang("no more objects message", lang("objects")));
					} else {
						this.fireEvent('messageToShow', "");
					}
					og.showWsPaths();
					Ext.getCmp('linked-objects-manager').getView().focusRow(og.lastSelectedRow.linkedobjs+1);
				}
			}
		});
		og.LinkedObjectManager.store.setDefaultSort('dateUpdated', 'desc');
	}
	this.store = og.LinkedObjectManager.store;
	this.store.addListener({messageToShow: {fn: this.showMessage, scope: this}});

	function renderDragHandle(value, p, r) {
		return '<div class="img-grid-drag" title="' + lang('click to drag') + '" onmousedown="Ext.getCmp(\'linked-objects-manager\').getSelectionModel().selectRow('+r.data.ix+', true);"></div>';
	}

	function renderName(value, p, r) {
		var projectsString = String.format('<span class="project-replace">{0}</span>&nbsp;', r.data.wsIds);

		var viewUrl = r.data.url;
		
		var actions = '';
		var actionStyle= ' style="font-size:90%;color:#777777;padding-top:3px;padding-left:18px;background-repeat:no-repeat" ';
		if (r.data.type == 'webpage') {
			viewUrl = og.getUrl('webpage', 'view', {id:r.data.object_id});
			actions += String.format('<a class="list-action ico-open-link" href="#" onclick="window.open(\'{0}\')" title="{1}" ' + actionStyle + '> </a>',
				r.data.url, lang('open link in new window', value));
		}
		actions = '<span>' + actions + '</span>';
	
		if (value.trim() == "") {
			var cleanvalue = lang("n/a");
		} else {
			var cleanvalue = og.clean(value);
		}
		var name = String.format('<a style="font-size:120%" href="{1}" onclick="og.openLink(\'{1}\');return false;">{0}</a>', cleanvalue, viewUrl);
		
		return projectsString + name + actions;
	}

	function renderType(value, p, r){
		return String.format('<i>' + lang(value) + '</i>')
	}
	
	function renderIcon(value, p, r) {
		var classes = "db-ico ico-unknown ico-" + r.data.type;
		if (r.data.mimeType) {
			var path = r.data.mimeType.replace(/\//ig, "-").split("-");
			var acc = "";
			for (var i=0; i < path.length; i++) {
				acc += path[i];
				classes += " ico-" + acc;
				acc += "-";
			}
		}
		return String.format('<div class="{0}" title="{1}"/>', classes, lang(r.data.type));
	}
	function renderUser(value, p, r) {
		return String.format('<a href="{1}" onclick="og.openLink(\'{1}\');return false;">{0}</a>', og.clean(value), og.getUrl('user', 'card', {id: r.data.updatedById}));
	}

	function renderAuthor(value, p, r) {
		return String.format('<a href="{1}" onclick="og.openLink(\'{1}\');return false;">{0}</a>', og.clean(value), og.getUrl('user', 'card', {id: r.data.createdById}));
	}

	function renderDate(value, p, r) {
		if (!value) {
			return "";
		}
		return value;
	}

	function getSelectedIds() {
		var selections = sm.getSelections();
		if (selections.length <= 0) {
			return '';
		} else {
			var ret = '';
			for (var i=0; i < selections.length; i++) {
				ret += "," + selections[i].data.manager + ":" + selections[i].data.object_id;
			}
			og.lastSelectedRow.linkedobjs = selections[selections.length-1].data.ix;
			return ret.substring(1);
		}
	}
	
	this.getSelectedIds = getSelectedIds;
	
	function getFirstSelectedId() {
		if (sm.hasSelection()) {
			return sm.getSelected().data.object_id;
		}
		return '';
	}

	var sm = new Ext.grid.CheckboxSelectionModel();
	sm.on('selectionchange',
		function() {
			if (sm.getCount() <= 0) {
				actions.unlink.setDisabled(true);
			} else {
				actions.unlink.setDisabled(false);
			}
		});
	var cm = new Ext.grid.ColumnModel([
		sm,{
			id: 'draghandle',
			header: '&nbsp;',
			width: 18,
        	renderer: renderDragHandle,
        	fixed:true,
        	resizable: false,
        	hideable:false,
        	menuDisabled: true
		},{
        	id: 'icon',
        	header: '&nbsp;',
        	dataIndex: 'icon',
        	width: 28,
        	renderer: renderIcon,
        	fixed:true,
        	resizable: false,
        	hideable:false,
        	menuDisabled: true
        },{
			id: 'type',
			header: lang('type'),
			dataIndex: 'type',
			width: 80,
        	renderer: renderType,
        	fixed:false,
        	resizable: true,
        	hideable:true,
        	menuDisabled: true
		},{
			id: 'name',
			header: lang("name"),
			dataIndex: 'name',
			width: 300,
			renderer: renderName,
			sortable:true
        },{
        	id: 'user',
        	header: lang('user'),
        	dataIndex: 'updatedBy',
        	width: 120,
        	renderer: renderUser
        },{
			id: 'tags',
			header: lang("tags"),
			dataIndex: 'tags',
			width: 120,
			hidden: true
        },{
			id: 'updatedOn',
			header: lang("last update"),
			dataIndex: 'dateUpdated',
			width: 80,
			renderer: renderDate,
			sortable:true
        },{
			id: 'createdOn',
			header: lang("created on"),
			dataIndex: 'dateCreated',
			width: 80,
			hidden: true,
			renderer: renderDate,
			sortable:true
		},{
			id: 'author',
			header: lang("author"),
			dataIndex: 'createdBy',
			width: 120,
			renderer: renderAuthor,
			hidden: true
		}]);
	cm.defaultSortable = false;

	actions = {
		parent: new Ext.Action({
			id: 'parent',
			text: objName,
            tooltip: lang('change parent'),
            iconCls: objIco,
			disabled: false,
			handler: function() {
			
				og.ObjectPicker.show(function (data) {
					if (data) {
						
						object_id = data[0].data.object_id;
						object_manager = data[0].data.manager;
							
						switch(object_manager){
						case "Contacts":
							object_ico = "ico-contact";
							break;
						case "ProjectMessages":
							object_ico = "ico-message";
							break;
						case "ProjectEvents":
							object_ico = "ico-event";
							break;
						case "ProjectTasks":
							object_ico = "ico-task";
							break;
						case "ProjectWebpages":
							object_ico = "ico-webpage";
							break;
						case "ProjectFiles":
							object_ico = "ico-file";
							break;
						case "ProjectMilestones":
							object_ico = "ico-milestone";
							break;
						default:
							object_ico = "ico-unknown";
						break;
						}
						
						object_name = data[0].data.name;
						
					}
					og.openLink(og.getUrl('object','show_all_linked_objects',{
						linked_object:object_id,
						linked_manager:object_manager,
						linked_object_ico:object_ico,
						linked_object_name:object_name
					}));			
				});			
								
			},
			scope: this
		}),

		unlink: new Ext.Action({
			text: lang('unlink'),
			iconCls: 'ico-delete',
            tooltip: lang('unlink object'),
        	disabled: true,
        	handler: function() {
				if (confirm(lang('confirm unlink objects'))) {
					var selections = sm.getSelections();
					if (selections.length > 0) {
						og.openLink(og.getUrl('object', 'unlink_from_object', {
							manager: this.linked_manager,
							object_id: this.linked_object,
							rel_objects: getSelectedIds()
						}));
					}
					this.getSelectionModel().clearSelections();
				}
			},
			scope: this
		}),
		link: new Ext.Action({
			text: lang('link'),
            tooltip: lang('link object'),
            iconCls: 'ico-add',
			disabled: false,
			handler: function() {
				var lo = this.linked_object;
				var lm = this.linked_manager;
				og.ObjectPicker.show(function (data) {
					if (data) {
						var objects = '';
						for (var i=0; i < data.length; i++) {
							if (objects != '') objects += ',';
							objects += data[i].data.manager + ':' + data[i].data.object_id;
						}
						og.openLink(og.getUrl('object','link_object',{object_id:lo,manager:lm,objects:objects, reload:1}));
					}
				});
			},
			scope: this
		})
    };
    
	og.LinkedObjectManager.superclass.constructor.call(this, {
		enableDrag: true,
		ddGroup : 'WorkspaceDD',
		store: this.store,
		layout: 'fit',
		autoExpandColumn: 'name',
		cm: cm,
		stripeRows: true,
		closable: true,
		id: 'linked-objects-manager',
		bbar: new og.CurrentPagingToolbar({
			pageSize: og.config['files_per_page'],
			store: this.store,
			displayInfo: true,
			displayMsg: lang('displaying objects of'),
			emptyMsg: lang("no objects to display")
		}),
		viewConfig: {
			forceFit: true
		},
		sm: sm,
		tbar:[
		    actions.parent,
			'-',
			actions.unlink,
			actions.link
		],
		listeners: {
			'render': {
				fn: function() {
					this.innerMessage = document.createElement('div');
					this.innerMessage.className = 'inner-message';
					var msg = this.innerMessage;
					var elem = Ext.get(this.getEl());
					var scroller = elem.select('.x-grid3-scroller');
					scroller.each(function() {
						this.dom.appendChild(msg);
					});
				},
				scope: this
			}
		}
	});

	var tagevid = og.eventManager.addListener("tag changed", function(tag) {
		if (!this.ownerCt) {
			og.eventManager.removeListener(tagevid);
			return;
		}
		if (this.ownerCt.active) {
			this.load({start:0});
		} else {
    		this.needRefresh = true;
    	}
	}, this);
	
	var wschevid = og.eventManager.addListener("workspace changed", function() {
		if (!this.ownerCt) {
			og.eventManager.removeListener(wschevid);
			return;
		}
		if (this.ownerCt.active) {
			this.load({start:0});
		} else {
    		this.needRefresh = true;
    	}
	}, this);
};

Ext.extend(og.LinkedObjectManager, Ext.grid.GridPanel, {
	load: function(params) {
		if (!params) params = {};
		if (typeof params.start == 'undefined') {
			var start = (this.getBottomToolbar().getPageData().activePage - 1) * og.config['files_per_page'];
		} else {
			var start = 0;
		}
		Ext.apply(this.store.baseParams, {
			tag: Ext.getCmp('tag-panel').getSelectedTag(),
			//active_project: Ext.getCmp('workspace-panel').getActiveWorkspace().id,
			active_project: 0,
			linkedobject: this.linked_object,
			linkedmanager: this.linked_manager,
			filtermanager: this.filter_manager
		});
		this.store.load({
			params: Ext.applyIf(params, {
				start: start,
				limit: og.config['files_per_page']
			})
		});
		this.needRefresh = false;
	},
	
	activate: function() {
		if (this.needRefresh) {
			this.load({start: 0});
		}
	},
	
	reset: function() {
		this.load({start:0});
	},
	
	moveObjects: function(ws) {
		if (ws == 0) {
			var selections = this.getSelectionModel().getSelections();
			var amail = false;
			for (i=0; i<selections.length; i++) {
				if (selections[i].data.manager == 'MailContents') {
					amail = true;
					break;
				}
			}
			if (amail) {
				og.confirmMoveToAllWs(this.id, lang('confirm unclassify emails'));
			}
		} else {
			var selections = this.getSelectionModel().getSelections();
			var allItemsAreTasksOrMilestones = true;
			for (i=0; i<selections.length; i++) {
				if (selections[i].data.manager != 'ProjectTasks' && selections[i].data.manager != 'ProjectMilestones') {
					allItemsAreTasksOrMilestones = false;
					break;
				}
			}
			// Tasks and events does not keep ws, only move
			if (allItemsAreTasksOrMilestones) {
				this.moveObjectsToWsOrMantainWs(false, ws);
			} else {
				og.moveToWsOrMantainWs(this.id, ws);
			}
		}
	},
	
	moveObjectsToWsOrMantainWs: function(mantain, ws) {
		var selections = this.getSelectionModel().getSelections();
		var amail = false;
		for (i=0; i<selections.length; i++) {
			if (selections[i].data.manager == 'MailContents') {
				amail = true;
				break;
			}
		}
		if (amail) {
			og.askToClassifyUnclassifiedAttachs(this.id, mantain, ws);
		} else {
			this.load({
				action: 'move',
				objects: this.getSelectedIds(),
				moveTo: ws,
				mantainWs: mantain
			});
		}
	},
	
	moveObjectsClassifyingEmails: function(mantain, ws, classifyatts) {
		this.load({
			action: 'move',
			objects: this.getSelectedIds(),
			moveTo: ws,
			mantainWs: mantain,
			classify_atts: classifyatts
		});
	},
	
	tagObjects: function(tag) {
		this.load({
			action: 'tag',
			objects: this.getSelectedIds(),
			tagTag: tag
		});
	},
	
	removeTags: function() {
		this.load({
			action: 'untag',
			objects: this.getSelectedIds()
		});
	},
	
	trashObjects: function() {
		if (confirm(lang('confirm move to trash'))) {
			this.load({
				action: 'delete',
				objects: this.getSelectedIds()
			});
			this.getSelectionModel().clearSelections();
		}
	},
	
	archiveObjects: function() {
		if (confirm(lang('confirm archive selected objects'))) {
			this.load({
				action: 'archive',
				objects: this.getSelectedIds()
			});
			this.getSelectionModel().clearSelections();
		}
	},	
	
	showMessage: function(text) {
		if (this.innerMessage) {
			this.innerMessage.innerHTML = text;
		}
	},
	
	newConfig: function(config) {
		Ext.apply(this, config);
		var parent = this.getTopToolbar().items.get('parent');
		var oName = config.linked_object_name;
		
		if (oName.length > 20){
			oName = oName.substring(0,17) + "...";
		}

		parent.setText(oName);
		parent.setIconClass(config.linked_object_ico);
	}
});

Ext.reg("linkedobject", og.LinkedObjectManager);