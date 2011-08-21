/**
 *  ContactManager
 */
og.ContactManager = function() {
	var actions;
	this.viewType = "all";
	this.doNotRemove = true;
	this.needRefresh = false;
	
	if (!og.ContactManager.store) {
		og.ContactManager.store = new Ext.data.Store({
	        proxy: new og.GooProxy({
	            url: og.getUrl('contact', 'list_all')
	        }),
	        reader: new Ext.data.JsonReader({
	            root: 'contacts',
	            totalProperty: 'totalCount',
	            id: 'id',
	            fields: [
	                'object_id', 'type', 'name', 'companyId', 'companyName', 'email', 'website', 'jobTitle', 'createdBy', 'createdById', 'createdOn', 'createdOn_today', 'role', 'tags',
	                'department', 'email2', 'email3', 'workWebsite', 'workAddress', 'workPhone1', 'workPhone2', 
	                'homeWebsite', 'homeAddress', 'homePhone1', 'homePhone2', 'mobilePhone','wsIds','workspaceColors','updatedBy','updatedById', 'updatedOn', 'updatedOn_today', 'ix'
	            ]
	        }),
	        remoteSort: true,
			listeners: {
				'load': function(result) {
					var d = this.reader.jsonData;
					var ws = og.clean(Ext.getCmp('workspace-panel').getActiveWorkspace().name);
					var tag = og.clean(Ext.getCmp('tag-panel').getSelectedTag());
					if (d.totalCount == 0) {
						if (tag) {
							this.fireEvent('messageToShow', lang("no objects with tag message", lang("contacts"), ws, tag));
						} else {
							this.fireEvent('messageToShow', lang("no objects message", lang("contacts"), ws));
						}
					} else if (d.contacts.length == 0) {
						this.fireEvent('messageToShow', lang("no more objects message", lang("contacts")));
					} else {
						this.fireEvent('messageToShow', "");
					}
					og.showWsPaths();
					cm.setHidden(cm.getIndexById('role'), Ext.getCmp('workspace-panel').getActiveWorkspace().id == 0);
					Ext.getCmp('contact-manager').getView().focusRow(og.lastSelectedRow.contacts+1);
				}
			}
	    });
	    og.ContactManager.store.setDefaultSort('name', 'asc');
	}
	this.store = og.ContactManager.store;
	this.store.addListener({messageToShow: {fn: this.showMessage, scope: this}});
    
    //--------------------------------------------
    // Renderers
    //--------------------------------------------

	function renderDragHandle(value, p, r) {
		return '<div class="img-grid-drag" title="' + lang('click to drag') + '" onmousedown="var sm = Ext.getCmp(\'contact-manager\').getSelectionModel();if (!sm.isSelected('+r.data.ix+')) sm.clearSelections();sm.selectRow('+r.data.ix+', true);"></div>';
	}
	
    function renderContactName(value, p, r) {
    	var name = lang('n/a');
		if (r.data.type == 'company'){
			name = String.format(
					'<a style="font-size:120%" href="{1}" onclick="og.openLink(\'{1}\');return false;" title="{2}">{0}</a>',
					og.clean(value), og.getUrl('company', 'view_client', {id: r.data.object_id}), og.clean(r.data.name));
		}
		else{
			name = String.format(
					'<a style="font-size:120%" href="{1}" onclick="og.openLink(\'{1}\');return false;" title="{2}">{0}</a>',
					og.clean(value), og.getUrl('contact', 'card', {id: r.data.object_id}), og.clean(r.data.name));
			
			if(r.data.companyId != null && r.data.companyId != 0 && r.data.companyName.trim()!=''){
				name += String.format(
					' (<a style="font-size:80%" href="{1}" onclick="og.openLink(\'{1}\');return false;" title="{2}">{0}</a>)',
					og.clean(r.data.companyName), og.getUrl('company', 'view_client', {id: r.data.companyId}), og.clean(r.data.companyName));
			} //end else
		}
		return name;
    }
    
    function renderWsCrumbs(value, p, r) {
    	return String.format('<span class="project-replace">{0}</span>&nbsp;', value);
    }
    
    function renderCompany(value, p, r) {
    	return String.format('<a href="{1}" onclick="og.openLink(\'{1}\', null);return false;">{0}</a>', og.clean(value), og.getUrl('company', 'card', {id: r.data.companyId}));
    }
    
    function renderEmail(value, p, r) {
    	if (!value || value == '') {
    		return "";
    	}
		if (og.loggedUserHasEmailAccounts) {
    		var url = og.getUrl('mail', 'add_mail', {to: og.clean(r.data.name.replace("'","")) + ' <' + escape(og.clean(value)) + '>'});
    		return String.format('<a href="#" title="' + lang('write an email to contact', r.data.name) + '" onclick="og.openLink(\'' + url + '\');">{0}</a>', og.clean(value));
    	} else {
    		return String.format('<a target="_self" href="mailto:{0}">{0}</a>', og.clean(value));
	    }
    }
    
    function renderWebsite(value, p, r) {
    	return String.format('<a href="" onclick="window.open(\'{0}\'); return false">{0}</a>', og.clean(value));
    }
    	
	function renderIcon(value, p, r) {
		var classes = "db-ico ico-unknown ico-" + r.data.type;
		return String.format('<div class="{0}" title="{1}"/>', classes, lang(r.data.type));
	}
	
	function renderDateUpdated(value, p, r) {
		if (!value) {
			return "";
		}
		var userString = String.format('<a href="{1}" onclick="og.openLink(\'{1}\');return false;">{0}</a>', r.data.updatedBy, og.getUrl('user', 'card', {id: r.data.updatedById}));
	
		var now = new Date();
		var dateString = '';
		if (!r.data.updatedOn_today) {
			return lang('last updated by on', userString, value);
		} else {
			return lang('last updated by at', userString, value);
		}
	}
	
	function renderDateCreated(value, p, r) {
		if (!value) {
			return "";
		}
		var userString = String.format('<a href="{1}" onclick="og.openLink(\'{1}\');return false;">{0}</a>', r.data.createdBy, og.getUrl('user', 'card', {id: r.data.createdById}));
	
		var now = new Date();
		var dateString = '';
		if (!r.data.createdOn_today) {
			return lang('last updated by on', userString, value);
		} else {
			return lang('last updated by at', userString, value);
		}
	}
	
	function getSelectedIds() {
		var selections = sm.getSelections();
		if (selections.length <= 0) {
			return '';
		} else {
			var ret = '';
			for (var i=0; i < selections.length; i++) {
				ret += "," + selections[i].data.object_id;
//				ret += "," + selections[i].id;
			}
			og.lastSelectedRow.contacts = selections[selections.length-1].data.ix;
			return ret.substring(1);
		}
	}
	this.getSelectedIds = getSelectedIds;
	
	function getSelectedTypes() {
		var selections = sm.getSelections();
		if (selections.length <= 0) {
			return '';
		} else {
			var ret = '';
			for (var i=0; i < selections.length; i++) {
				ret += "," + selections[i].data.type;
			}	
			return ret.substring(1);
		}
	}
	this.getSelectedTypes = getSelectedTypes;
	
	function getFirstSelectedType() {
		if (sm.hasSelection()) {
			return sm.getSelected().data.type;
		}
		return '';
	}
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
				actions.tag.setDisabled(true);
				actions.delContact.setDisabled(true);
				actions.editContact.setDisabled(true);
				actions.assignContact.setDisabled(true);
				actions.archive.setDisabled(true);
			} else {
				actions.editContact.setDisabled(sm.getCount() != 1);
				if(getFirstSelectedType() == 'contact')
					actions.assignContact.setDisabled(sm.getCount() != 1);
				actions.tag.setDisabled(false);
				actions.delContact.setDisabled(false);
				actions.archive.setDisabled(false);
			}
		});
    var cm = new Ext.grid.ColumnModel([
		sm,
		{
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
			id: 'name',
			header: lang("name"),
			dataIndex: 'name',
			width: 150,
			renderer: renderContactName,
			sortable:true
        },{
			id: 'workspaces',
			header: lang("workspaces"),
			dataIndex: 'wsIds',
			width: 70,
			renderer: renderWsCrumbs,
			sortable:true
        },
		{
			id: 'role',
			header: lang("role"),
			dataIndex: 'role',
			width: 60,
			renderer: og.clean,
			sortable:false
        },{
			id: 'email',
			header: lang("email"),
			dataIndex: 'email',
			width: 120,
			renderer: renderEmail,
			sortable:true
		},{
			id: 'tags',
			header: lang("tags"),
			dataIndex: 'tags',
			hidden: true,
			width: 120
        },{
			id: 'department',
			header: lang("department"),
			dataIndex: 'department',
			width: 120,
			hidden: true,
			renderer: og.clean
        },{
			id: 'email2',
			header: lang("email2"),
			dataIndex: 'email2',
			width: 120,
			hidden: true,
			renderer: renderEmail,
			sortable:true
        },{
			id: 'email3',
			header: lang("email3"),
			dataIndex: 'email3',
			width: 120,
			hidden: true,
			renderer: renderEmail,
			sortable:true
        },{
			id: 'workWebsite',
			header: lang("workWebsite"),
			dataIndex: 'workWebsite',
			width: 120,
			hidden: true,
			renderer: renderWebsite
        },{
			id: 'workPhone1',
			header: lang("workPhone1"),
			dataIndex: 'workPhone1',
			width: 120,
			hidden: true,
			renderer: og.clean
        },{
			id: 'workPhone2',
			header: lang("workPhone2"),
			dataIndex: 'workPhone2',
			width: 120,
			hidden: true,
			renderer: og.clean
        },{
			id: 'workAddress',
			header: lang("workAddress"),
			dataIndex: 'workAddress',
			width: 120,
			hidden: true,
			renderer: og.clean
        },{
			id: 'homeWebsite',
			header: lang("homeWebsite"),
			dataIndex: 'homeWebsite',
			width: 120,
			hidden: true,
			renderer: renderWebsite
        },{
			id: 'homePhone1',
			header: lang("homePhone1"),
			dataIndex: 'homePhone1',
			width: 120,
			hidden: true,
			renderer: og.clean
        },{
			id: 'homePhone2',
			header: lang("homePhone2"),
			dataIndex: 'homePhone2',
			width: 120,
			hidden: true,
			renderer: og.clean
        },{
			id: 'homeAddress',
			header: lang("homeAddress"),
			dataIndex: 'homeAddress',
			width: 120,
			hidden: true,
			renderer: og.clean
        },{
			id: 'mobilePhone',
			header: lang("mobilePhone"),
			dataIndex: 'mobilePhone',
			width: 120,
			hidden: true,
			renderer: og.clean
        },{
			id: 'updated',
			header: lang("last updated by"),
			dataIndex: 'updatedOn',
			width: 120,
			hidden: true,
			renderer: renderDateUpdated,
			sortable: true
        },{
			id: 'created',
			header: lang("created by"),
			dataIndex: 'createdOn',
			width: 120,
			hidden: true,
			renderer: renderDateCreated,
			sortable: true
		}]);
    cm.defaultSortable = false;

	viewActions = {
			all: new Ext.Action({
				text: lang('view all'),
				handler: function() {
					this.viewType = "all";
					this.load();
				},
				scope: this
			}),
			contacts: new Ext.Action({
				text: lang('contacts'),
				iconCls: "ico-contacts",
				handler: function() {
					this.viewType = "contacts";
					this.load();
				},
				scope: this
			}),
			companies: new Ext.Action({
				text: lang('companies'),
				iconCls: "ico-company",
				handler: function() {
					this.viewType = "companies";
					this.load();
				},
				scope: this
			})
	}	
	actions = {
		newContact: new Ext.Action({
			text: lang('new'),
            tooltip: lang('create contact or client company'),
            iconCls: 'ico-new',
			menu: {items: [
				{text: lang('contact'), iconCls: 'ico-contact', handler: function() {
					var url = og.getUrl('contact', 'add');
					og.openLink(url);
				}},
				{text: lang('company'), iconCls: 'ico-company', handler: function() {
					var url = og.getUrl('company', 'add_client');
					//var url = og.getUrl('contact', 'generate_client_from_wsname');
					og.openLink(url);
				}}				
			]}
		}),
		delContact: new Ext.Action({
			text: lang('move to trash'),
            tooltip: lang('move selected objects to trash'),
            iconCls: 'ico-trash',
			disabled: true,
			handler: function() {
				if (confirm(lang('confirm move to trash'))) {
					this.load({
						action: 'delete',
						ids: getSelectedIds(),
						types: getSelectedTypes()
					});
					this.getSelectionModel().clearSelections();
				}
			},
			scope: this
		}),
		editContact: new Ext.Action({
			text: lang('edit'),
            tooltip: lang('edit selected object'),
            iconCls: 'ico-edit',
			disabled: true,
			handler: function() {
				var url = '';
				if (getFirstSelectedType() == 'contact')
					url = og.getUrl('contact', 'edit', {id:getFirstSelectedId()});
				else
					url = og.getUrl('company', 'edit_client', {id:getFirstSelectedId()});
				og.openLink(url, null);
			},
			scope: this
		}),
		archive: new Ext.Action({
			text: lang('archive'),
            tooltip: lang('archive selected object'),
            iconCls: 'ico-archive-obj',
			disabled: true,
			handler: function() {
				if (confirm(lang('confirm archive selected objects'))) {
					this.load({
						action: 'archive',
						ids: getSelectedIds(),
						types: getSelectedTypes()
					});
					this.getSelectionModel().clearSelections();
				}
			},
			scope: this
		}),
		assignContact: new Ext.Action({
			text: lang('assign roles'),
            tooltip: lang('assign contact role on workspace'),
            iconCls: 'ico-workspaces',
			disabled: true,
			handler: function() {
				var url = og.getUrl('contact', 'assign_to_project', {id:getFirstSelectedId()});
				og.openLink(url, null);
			},
			scope: this
		}),
		refresh: new Ext.Action({
			text: lang('refresh'),
            tooltip: lang('refresh desc'),
            iconCls: 'ico-refresh',
			handler: function() {
				og.ContactManager.store.reload();
			},
			scope: this
		}),
		view: new Ext.Action({
			text: lang('view'),
            iconCls: 'ico-view_options',
			disabled: false,
			menu: {items: [
				viewActions.all,
				'-',
				viewActions.contacts,
				viewActions.companies
			]}
		}),
		tag: new Ext.Action({
			text: lang('tag'),
	        tooltip: lang('tag selected objects'),
	        iconCls: 'ico-tag',
			disabled: true,
			menu: new og.TagMenu({
				listeners: {
					'tagselect': {
						fn: function(tag) {
							this.load({
								action: 'tag',
								ids: getSelectedIds(),
								types: getSelectedTypes(),
								tagTag: tag
							});
						},
						scope: this
					},'tagdelete': {
						fn: function(tag) {
							this.load({
								action: 'untag',
								ids: getSelectedIds(),
								types: getSelectedTypes(),
								tagTag: tag
							});
						},
						scope: this
					}
				}
			})
		}),
		imp_exp: new Ext.Action({
			text: lang('import/export'),
            tooltip: lang('contact import - export'),
            menu: { items: [
            	new Ext.Action({
		            text: lang('contacts'),
		            iconCls: 'ico-contact',
		            menu: { items: [
		            	new Ext.Action({
		            		text: lang('import'), 
		            		iconCls: 'ico-upload', 
		            		menu: { items: [
		            			{ text: lang('from csv'), iconCls: 'ico-text', handler: function() {
										var url = og.getUrl('contact', 'import_from_csv_file', {type:'contact', from_menu:1});
										og.openLink(url);
									}
								},
								{ text: lang('from vcard'), iconCls: 'ico-account', handler: function() {
										var url = og.getUrl('contact', 'import_from_vcard', {type:'contact', from_menu:1});
										og.openLink(url);
									}
								}
							]}
		            	}),
		            	new Ext.Action({
		            		text: lang('export'),
		            		iconCls: 'ico-download',
		            		menu: { items: [
		            			{ text: lang('to csv'), iconCls: 'ico-text', handler: function() {
										var url = og.getUrl('contact', 'export_to_csv_file', {type:'contact'});
										og.openLink(url);
									}
								},
								{ text: lang('to vcard'), iconCls: 'ico-account', handler: function() {
										var ids = getSelectedIds();
										if (ids != '') {
											var url = og.getUrl('contact', 'export_to_vcard', {ids:getSelectedIds(), types:getSelectedTypes()});
											location.href = url;
										} else og.err(lang("you must select the contacts from the grid"));
									}
								},
								{ text: lang('to vcard all'), iconCls: 'ico-account', handler: function() {										
										var url = og.getUrl('contact', 'export_to_vcard_all');
										location.href = url;										
									}
								}
		            		]}
		            	})
					]}
				}),
				new Ext.Action({
					text: lang('companies'),
					iconCls: 'ico-company',
		            menu: { items: [
						{ text: lang('import'), iconCls: 'ico-upload', handler: function() {
							var url = og.getUrl('contact', 'import_from_csv_file', {type:'company', from_menu:1});
							og.openLink(url);
						}},
						{ text: lang('export'), iconCls: 'ico-download', handler: function() {
							var url = og.getUrl('contact', 'export_to_csv_file', {type:'company'});
							og.openLink(url);
						}}
					]}
				})
			]}
		})
    };
    
	var tbar = [];
	if (!og.loggedUser.isGuest) {
		tbar.push(actions.newContact);
		tbar.push('-');
		tbar.push(actions.editContact);
		tbar.push(actions.tag);
		tbar.push(actions.archive);
		tbar.push(actions.delContact);
		tbar.push('-');
		tbar.push(actions.assignContact);
	}
	tbar.push(actions.view);
	if (!og.loggedUser.isGuest) {
		tbar.push('-');
		tbar.push(actions.imp_exp);
	}
	
	og.ContactManager.superclass.constructor.call(this, {
        store: this.store,
		layout: 'fit',
        cm: cm,
        enableDrag: true,
		ddGroup: 'WorkspaceDD',
		stateful: og.preferences['rememberGUIState'],
        closable: true,
		stripeRows: true,
		id: 'contact-manager',
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
		tbar: tbar,
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
};

Ext.extend(og.ContactManager, Ext.grid.GridPanel, {
	load: function(params) {
		if (!params) params = {};
		if (typeof params.start == 'undefined') {
			var start = (this.getBottomToolbar().getPageData().activePage - 1) * og.config['files_per_page'];
		} else {
			var start = 0;
		}
		Ext.apply(this.store.baseParams, {
			tag: Ext.getCmp('tag-panel').getSelectedTag(),
			view_type: this.viewType,
			active_project: Ext.getCmp('workspace-panel').getActiveWorkspace().id
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
	
	showMessage: function(text) {
		this.innerMessage.innerHTML = text;
	},
	
	reset: function() {
		this.load({start:0});
        this.getSelectionModel().clearSelections();                   
	},
	
	moveObjects: function(ws) {
		og.moveToWsOrMantainWs(this.id, ws);
	},
	
	moveObjectsToWsOrMantainWs: function(mantain, ws) {
		this.load({
			action: 'move',
			ids: this.getSelectedIds(),
			types: this.getSelectedTypes(),
			moveTo: ws,
			mantainWs: mantain
		});
	},
	
	trashObjects: function() {
		if (confirm(lang('confirm move to trash'))) {
			this.load({
				action: 'delete',
				ids: this.getSelectedIds(),
				types: this.getSelectedTypes()
			});
			this.getSelectionModel().clearSelections();
		}
	},

	archiveObjects: function() {
		if (confirm(lang('confirm archive selected objects'))) {
			this.load({
				action: 'archive',
				ids: this.getSelectedIds(),
				types: this.getSelectedTypes()
			});
			this.getSelectionModel().clearSelections();
		}
	},

	removeTags: function() {
		this.load({
			action: 'untag',
			ids: this.getSelectedIds(),
			types: this.getSelectedTypes()
		});
	},
	
	tagObjects: function(tag) {
		this.load({
			action: 'tag',
			ids: this.getSelectedIds(),
			types: this.getSelectedTypes(),
			tagTag: tag
		});
	}
});

Ext.reg("contacts", og.ContactManager);