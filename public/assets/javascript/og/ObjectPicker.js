og.ObjectPicker = function(config) {
	if (!config) config = {};
	var Grid = function(config) {
		if (!config) config = {};
		this.store = new Ext.data.Store({
        	proxy: new Ext.data.HttpProxy(new Ext.data.Connection({
				method: 'GET',
            	url: og.getUrl('object', 'list_objects', {ajax: true})
        	})),
        	reader: new Ext.data.JsonReader({
            	root: 'objects',
            	totalProperty: 'totalCount',
            	id: 'id',
            	fields: [
	                'name', 'object_id', 'type', 'tags', 'createdBy', 'createdById',
	                'dateCreated',
					'updatedBy', 'updatedById',
					'dateUpdated',
					'icon', 'project', 'projectId', 'manager', 'object_id', 'mimeType'
            	]
        	}),
        	remoteSort: true
    	});
    	this.store.setDefaultSort('dateUpdated', 'desc');

		function renderIcon(value, p, r) {
			var classes = "db-ico ico-unknown ico-" + r.data.type;
			if (r.data.mimeType) {
				var path = r.data.mimeType.replace(/\./ig, "_").replace(/\//ig, "-").split("-");
				var acc = "";
				for (var i=0; i < path.length; i++) {
					acc += path[i];
					classes += " ico-" + acc;
					acc += "-";
				}
			}
			return String.format('<div class="{0}" />', classes);
		}
        
		function renderDate(value, p, r) {
			if (!value) {
				return "";
			}
			return value;
		}

		var sm = new Ext.grid.RowSelectionModel();
		var cm = new Ext.grid.ColumnModel([{
	        	id: 'icon',
	        	header: '&nbsp;',
	        	dataIndex: 'icon',
	        	width: 28,
	        	renderer: renderIcon,
	        	sortable: false,
	        	fixed:true,
	        	resizable: false,
	        	hideable:false,
	        	menuDisabled: true
	        },{
				id: 'name',
				header: lang("name"),
				dataIndex: 'name',
				renderer: og.clean
				//,width: 120
	        },{
				id: 'type',
				header: lang('type'),
				dataIndex: 'type',
				width: 60,
				hidden: true,
				sortable: false
			},{
				id: 'project',
				header: lang("project"),
				dataIndex: 'project',
				width: 60,
				renderer: og.clean,
				sortable: false,
				hidden: true
	        },{
				id: 'tags',
				header: lang("tags"),
				dataIndex: 'tags',
				width: 60,
				sortable: false,
				hidden: true
	        },{
				id: 'last',
				header: lang("last update"),
				dataIndex: 'dateUpdated',
				width: 60,
				renderer: renderDate
	        },{
	        	id: 'user',
	        	header: lang('user'),
	        	dataIndex: 'updatedBy',
	        	width: 60,
	        	renderer: og.clean,
	        	sortable: false,
				hidden: true
	        },{
				id: 'created',
				header: lang("created on"),
				dataIndex: 'dateCreated',
				width: 60,
				renderer: renderDate,
				hidden: true
			},{
				id: 'author',
				header: lang("author"),
				dataIndex: 'createdBy',
				width: 60,
				renderer: og.clean,
				hidden: true
			}]);
	    cm.defaultSortable = true;
    
		Grid.superclass.constructor.call(this, Ext.apply(config, {
	        store: this.store,
			layout: 'fit',
	        cm: cm,
	        stripeRows: true,
	        loadMask: true,
	        bbar: new og.CurrentPagingToolbar({
	            pageSize: og.config['files_per_page'],
	            store: this.store,
	            displayInfo: true,
	            displayMsg: lang('displaying objects of'),
	            emptyMsg: lang("no objects to display")
	        }),
			viewConfig: {
	            forceFit:true
	        },
			sm: sm
	    }));
	}
	Ext.extend(Grid, Ext.grid.GridPanel, {
		getSelected: function() {
			return this.getSelectionModel().getSelections();
		},
		
		filterSelect: function(filter) {
			if (filter.filter == 'type') {
				this.type = filter.type;
				this.store.baseParams.type = this.type;
			} else if (filter.filter == 'tag') {
				this.tag = filter.name;
				this.store.baseParams.tag = this.tag;
			} else if (filter.filter == 'ws') {
				this.ws = filter.id;
				this.store.baseParams.active_project = this.ws;
			}
			this.load();
		},
		
		load: function(params) {
			Ext.apply(params, {
				start: 0,
				limit: og.config['files_per_page']
			});
			this.store.load({
				params: params
			});
		}
	});
	
	var TypeFilter = function(config) {
		TypeFilter.superclass.constructor.call(this, Ext.apply(config, {
			rootVisible: false,
			lines: false,
			root: new Ext.tree.TreeNode(lang('filter')),
			collapseFirst: false
		}));
	
		this.filters = this.root.appendChild(
			new Ext.tree.TreeNode({
				text: lang('all'),
				expanded: true
			})
		);
		this.filters.filter = {filter: 'type', id: 0, name: ''};		
		this.getSelectionModel().on({
			'selectionchange' : function(sm, node) {
				if (node && !this.pauseEvents) {
					this.fireEvent("filterselect", node.filter);
				}
			},
			scope:this
		});
		this.addEvents({filterselect: true});
	};
	Ext.extend(TypeFilter, Ext.tree.TreePanel, {
		addFilter: function(filter, config) {
			if (!config) config = {};
			var exists = this.getNodeById(filter.filter + (filter.id?filter.id:filter.name));
			if (exists) {
				return;
			}
			var config = Ext.apply(config, {
				iconCls: config.iconCls || 'ico-' + filter.filter,
				leaf: true,
				text: filter.name,
				cls: filter.type == config.selected_type ? 'x-tree-selected' : '',
				id: filter.filter + (filter.id?filter.id:filter.name)
			});
			var node = new Ext.tree.TreeNode(config);
			node.filter = filter;
			this.filters.appendChild(node);
			return node;
		},
		loadFilters: function(types, selected_type) {
			this.removeAll();
			if (types) {
				var csv = "";
				for (var k in types) {
					if (types[k]) {
						if (csv != "") csv += ",";
						csv += k;
					}
				}
				this.filters.filter.type = csv;
			} else {
				types = {
					'ProjectMessages':true,
					'MailContents':true,
					'ProjectEvents':true,
					'Contacts':true,
					'Companies':true,
					'ProjectFiles':true,
					'ProjectTasks':true,
					'ProjectMilestones':true,
					'ProjectWebPages':true
				}
				this.filters.filter.type = '';
			}
			// load types
			if (types['ProjectMessages']) {
				this.addFilter({
					id: 'messages',
					name: lang('messages'),
					type: 'ProjectMessages',
					filter: 'type'
				}, {iconCls: 'ico-message', selected_type: selected_type});
			}
			if (types['MailContents']) {
				this.addFilter({
					id: 'email',
					name: lang('email'),
					type: 'MailContents',
					filter: 'type'
				}, {iconCls: 'ico-email', selected_type: selected_type});
			}
			if (types['ProjectEvents']) {
				this.addFilter({
					id: 'calendar',
					name: lang('calendar'),
					type: 'ProjectEvents',
					filter: 'type'
				}, {iconCls: 'ico-calendar', selected_type: selected_type});
			}
			if (types['Contacts']) {
				this.addFilter({
					id: 'contacts',
					name: lang('contacts'),
					type: 'Contacts',
					filter: 'type'
				}, {iconCls: 'ico-contacts', selected_type: selected_type});
			}
			if (types['Companies']) {
				this.addFilter({
					id: 'companies',
					name: lang('companies'),
					type: 'Companies',
					filter: 'type'
				}, {iconCls: 'ico-companies', selected_type: selected_type});
			}
			if (types['ProjectFiles']) {
				this.addFilter({
					id: 'documents',
					name: lang('documents'),
					type: 'ProjectFiles',
					filter: 'type'
				}, {iconCls: 'ico-documents', selected_type: selected_type});
			}
			if (types['ProjectTasks']) {
				this.addFilter({
					id: 'tasks',
					name: lang('tasks'),
					type: 'ProjectTasks',
					filter: 'type'
				}, {iconCls: 'ico-tasks', selected_type: selected_type});
			}
			if (types['ProjectMilestones']) {
				this.addFilter({
					id: 'milestones',
					name: lang('milestones'),
					type: 'ProjectMilestones',
					filter: 'type'
				}, {iconCls: 'ico-milestone', selected_type: selected_type});
			}
			if (types['ProjectWebPages']) {
				this.addFilter({
					id: 'webpages',
					name: lang('web pages'),
					type: 'ProjectWebPages',
					filter: 'type'
				}, {iconCls: 'ico-webpages', selected_type: selected_type});
			}
			if (selected_type) {
				this.filters.filter.type = selected_type;
			}
			this.filters.expand();
			
			this.pauseEvents = true;
			this.filters.select();
			this.pauseEvents = false;
		},
		
		removeAll: function() {
			var node = this.filters.firstChild;
			while (node) {
				var aux = node;
				node = node.nextSibling;
				aux.remove();
			}
		}
	});
	
	Ext.reg('typefilter', TypeFilter);
	
	og.ObjectPicker.superclass.constructor.call(this, Ext.apply(config, {
		y: 50,
		width: 640,
		height: 480,
		id: 'object-picker',
		layout: 'border',
		modal: true,
		closeAction: 'hide',
		iconCls: 'op-ico',
		title: lang('select an object'),
		buttons: [{
			text: lang('ok'),
			handler: this.accept,
			scope: this
		},{
			text: lang('cancel'),
			handler: this.cancel,
			scope: this
		}],
		items: [
			{
				region: 'center',
				layout: 'fit',
				tbar: [
					/*{
						text: lang('view'),
			            tooltip: lang('view desc'),
			            iconCls: 'op-ico-view',
						menu: {items: [
							{text: lang('details'), iconCls: 'op-ico-details', handler: function() {
								alert('details');
							}},
							{text: lang('icons'), iconCls: 'op-ico-icons', handler: function() {
								alert('icons');
							}}
						]}
					},*/{
						text: lang('upload'),
			            tooltip: lang('quick upload desc'),
			            iconCls: 'ico-upload',
			            handler: function() {
							var tagf = this.findById('tagFilter');
							var seltag = tagf.getSelectedTag();
							
							var wsf = Ext.getCmp('workspace-panel');
							var selws = wsf.getActiveWorkspace().id;
							var quickId = Ext.id();
							var picker = this;
							og.openLink(og.getUrl('files', 'quick_add_files', {workspace: selws, tag: seltag, genid: quickId}), {
			        			preventPanelLoad: true,
								onSuccess: function(data) {								
				        			og.ExtendedDialog.show({				
				                		html: data.current.data,
				                		height: 300,
				                		width: 600,
				                		ok_fn: function() {
					        				og.doFileUpload(quickId, {
					        					callback: function() {
					        						form = document.getElementById(quickId + 'quickaddfile');
					        						og.ajaxSubmit(form, {
						    							callback: function(success, data) {
					        								if (success) {
					        									picker.grid.store.reload();
					        								}
						    							}
						    						});
					        					}
					        				});
					                		og.ExtendedDialog.hide();
				            			}
				                	});					        			
				                	return;
			        			}
			        		});						
						},
						scope: this
					},
					{
						text: lang('refresh'),
			            tooltip: lang('refresh desc'),
			            iconCls: 'op-ico-refresh',
						handler: function() {
							this.loadFilters();
							this.grid.store.reload();
						},
						scope: this
					},
					"-",
					{
						xtype : 'label',						
						text: lang('filter') + ': ',
			            iconCls: 'ico-search',
						scope: this
					},					
					{
						xtype: 'textfield',
						id: 'txtFilreByObjectName',
						fieldLabel: lang('name'),
						tooltip: lang('filtre name desc'),
						listeners:{
							render: {
								fn: function(f){
									f.el.on('keyup', function(e) {
										this.filterName(e.target.value);
										this.grid.store.reload();
									},
									this, {buffer: 350});
								},
								scope: this
							}
						},
						scope: this
					}
				],
				items: [
					this.grid = new Grid()
				]
			},
			//new Grid({region:'center'}),
			{
				layout: 'border',
				split: true,
				width: 200,
				region: 'west',
				collapsible: true,
				title: lang('filter'),
				items: [{
						xtype: 'wstree',
						id: 'wsFilter',
						region: 'north',
						autoScroll: true,
						loadWorkspacesFrom: 'workspace-panel',
						split: true,
						height: 120,
						listeners: {
							workspaceselect: {
								fn: function(ws) {
									this.filterSelect({
										filter: 'ws',
										name: ws.name,
										id: ws.id
									});
								},
								scope: this.grid
							}
						}
					},{
						xtype: 'typefilter',
						id: 'typeFilter',
						region: 'center',
						autoScroll: true,
						listeners: {
							filterselect: {
								fn: this.grid.filterSelect,
								scope: this.grid
							}
						}
					},{
						xtype: 'tagtree',
						id: 'tagFilter',
						region: 'south',
						autoScroll: true,
						loadTagsFrom: 'tag-panel',
						split: true,
						height: 115,
						listeners: {
							tagselect: {
								fn: function(tag) {
									this.filterSelect({
										filter: 'tag',
										name: tag
									});
								},
								scope: this.grid
							}
						}
					}
				]
			}
		]
	}));
	this.grid.on('rowdblclick', this.accept, this);
	//this.grid.load();
	this.addEvents({'objectselected': true});
}

Ext.extend(og.ObjectPicker, Ext.Window, {
	accept: function() {
		this.fireEvent('objectselected', this.grid.getSelected());
		this.hide();
	},
	
	cancel: function() {
		this.hide();
	},
	
	loadFilters: function(config) {
		if (!config) config = {};
		delete this.grid.store.baseParams.type;
		delete this.grid.store.baseParams.tag;
		delete this.grid.store.baseParams.active_project;
		var typef = this.findById('typeFilter');
		var tagf = this.findById('tagFilter');
		var wsf = this.findById('wsFilter');
		wsf.loadWorkspaces();
		tagf.loadTags();
		typef.loadFilters(config.types, config.selected_type);
		this.grid.store.baseParams.type = typef.filters.filter.type;
	},
	filterName: function(value) {
		this.grid.store.baseParams.name = value;
	},
	load: function() {
		this.grid.load();
	}
});

og.ObjectPicker.show = function(callback, scope, config) {
	if (!this.dialog) {
		this.dialog = new og.ObjectPicker();
	}
	
	if (!config) config = {};
	this.dialog.loadFilters(config);
	this.dialog.load();
	this.dialog.purgeListeners();
	this.dialog.on('objectselected', callback, scope, {single:true});
	this.dialog.on('hide', og.restoreFlashObjects);
	this.dialog.on('close', og.restoreFlashObjects);
	og.hideFlashObjects();
	this.dialog.show();
	var pos = this.dialog.getPosition();
	if (pos[0] < 0) pos[0] = 0;
	if (pos[1] < 0) pos[1] = 0;
	this.dialog.setPosition(pos[0], pos[1]);
}