og.ObjectPicker = function(config,object_id) {	
	if (!config) config = {};
	
	var Grid = function(config) {
		if (!config) config = {};
		this.store = new Ext.data.Store({
        	proxy: new Ext.data.HttpProxy(new Ext.data.Connection({
				method: 'GET',
            	url: og.getUrl('object', 'list_objects', {ajax: true, include_comments:true, ignore_context: true })
        	})),
        	reader: new Ext.data.JsonReader({
            	root: 'objects',
            	totalProperty: 'totalCount',
            	id: 'id',
            	fields: [
	                'name', 'object_id', 'type', 'ot_id', 'icon', 'object_id', 'mimeType',
	                'createdBy', 'createdById', 'dateCreated',
					'updatedBy', 'updatedById', 'dateUpdated'
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
		addFilter: function(filter, selected, config) {
			if (!config) config = {};
			var exists = this.getNodeById(filter.filter + filter.id);
			if (exists) {
				return;
			}
			var config = Ext.apply(config, {
				iconCls: filter.iconCls || 'ico-' + filter.id,
				leaf: true,
				text: filter.name,
				cls: selected ? 'x-tree-selected' : '',
				id: filter.id
			});
			var node = new Ext.tree.TreeNode(config);
			node.filter = filter;
			this.filters.appendChild(node);
			return node;
		},
		loadFilters: function(types, selected_type) {
			this.removeAll();
			
			for (var i=0; i<og.objPickerTypeFilters.length; i++) {
				var filter = og.objPickerTypeFilters[i];
				if (!types) {
					this.addFilter(filter, filter.type == selected_type);
				} else {
					for (var j=0; j<types.length; j++) {
						if (types[j] == filter.type) {
							this.addFilter(filter, filter.type == selected_type);
							break;
						}
					}
				}
			}
			this.filters.filter.type = selected_type ? selected_type : '';
			
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
		closeAction: 'close',
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
							var quickId = Ext.id();
							var picker = this;
							og.openLink(og.getUrl('files', 'quick_add_files', {genid: quickId, object_id: object_id}), {
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
							//this.loadFilters();
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
			{
				layout: 'border',
				split: true,
				width: 200,
				region: 'west',
				collapsible: true,
				title: lang('filter'),
				items: [{
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
					}
				]
			}
		]
	}));
	this.grid.on('rowdblclick', this.accept, this);
	this.addEvents({'objectselected': true});
}

Ext.extend(og.ObjectPicker, Ext.Window, {
	accept: function() {
		this.fireEvent('objectselected', this.grid.getSelected());
		this.close();
	},
	
	cancel: function() {
		this.close();
	},
	
	loadFilters: function(config) {
		if (!config) config = {};
		delete this.grid.store.baseParams.type;
		var typef = this.findById('typeFilter');
		typef.loadFilters(config.types, config.selected_type);
		this.grid.store.baseParams.type = typef.filters.filter.type;
	},
	filterName: function(value) {
		this.grid.store.baseParams.name = value;
	},
	load: function() {
		this.grid.store.baseParams.context = og.contextManager.plainContext();
		this.grid.load();
	}
});

og.ObjectPicker.show = function(callback, scope, config, object_id) {
    
	this.dialog = new og.ObjectPicker(config,object_id);
	
	if (!config) config = {};
	if (config.context) {
		this.dialog.grid.store.baseParams.context = config.context ;
	}
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