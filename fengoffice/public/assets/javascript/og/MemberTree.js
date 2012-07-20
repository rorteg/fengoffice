
// ***** tree  ***** //
og.MemberTree = function(config) {

	var tbar = [{
		xtype: 'textfield',
		id: config.id + '-textfilter',
		cls: "dimension-panel-textfilter" ,
		emptyText:lang('filter members'),
		listeners:{
			render: {
				fn: function(f){
					f.el.on('keyup', function(e) {
						this.filterTree(e.target.value);
					},
					this, {buffer: 350});
				},
				scope: this
			}
		}
	}];
	if (config.quickAdd) {
		tbar.push ({
	    	xtype: 'box',
	    	ctCls: 'member-quick-form-link', 
			autoEl: {
				id: 'quick-form-'+config.dimensionId,
				tag: 'a', 
				href: '#', 
				html: lang('add')+'+',
				onclick: "og.quickForm({ dimensionId: "+config.dimensionId+",type: 'member', treeId: '"+config.id+"', elId: 'quick-form-"+config.dimensionId+"'});"
			}
		});
	}
	
	
	Ext.applyIf(config, {
		region: 'center',
		id: config.id,
		loader: new og.MemberChooserTreeLoader({
    		dataUrl: 'index.php?c=dimension&a=initial_list_dimension_members_tree&ajax=true&dimension_id='+config.dimensionId+'&avoid_session=1',
    		ownerTree: this  
    	}),
		autoScroll: true,
		//rootVisible: false,
		root: {
        	text: lang('view all'),
        	id:0,
        	href: "#",
        	iconCls : 'root',
        	cls: 'root'
    	},
    	enableDrop: true,
    	ddGroup: 'MemberDD',
		collapseFirst: false,
		collapsible: true,
    	selModel: (config.multipleSelection)? new Ext.tree.MultiSelectionModel() : new Ext.tree.DefaultSelectionModel(),
    	dimensionId: config.dimensionId,
    	dimensionCode: config.dimensionCode, 
    	cls: config.dimensionCode,
    	reloadHidden: true, //To force tree reload when is hidden 
    	height: 210,
    	animate: false,
    	tools: [
	       {
	    	   id: 'toggle',
	    	   handler : function(e,t,p){
	    		   p.toggleCollapse();
	    	   }
	       }, 
    	   {
    		   id: 'close',
    		   handler: function(e,t,p){
	    	   		p.removeFromContext();
	    	   		og.contextManager.setDimensionVisibility('dimension-panel-' + config.dimensionId, false);
	    	   		
	    	   		var dim_ids = og.contextManager.getVisibleDimensions();
					og.openLink(og.getUrl('account', 'update_user_preference', {name:'root_dimensions', value:dim_ids.join(',')}), {hideLoading:true});
	       		}
    	    }

    	],  	
    	hideCollapseTool: true ,
    	expandMode: 'root', //all root,
    	tbar: tbar 
	});
	config.initialLoader = config.loader;
	if (!config.listeners) config.listeners = {};
	Ext.apply(config.listeners, {
		beforenodedrop: function(e) {
			if (!isNaN(e.target.id) && e.data.grid) {
				
				var has_relations = false;
				var ids = [];
				for (var i=0; i<e.data.selections.length; i++) {
					ids.push(e.data.selections[i].data.object_id);
					if (!has_relations) {
						var mpath = Ext.util.JSON.decode(e.data.selections[i].data.memPath);
						if (mpath && mpath[config.dimensionId]) has_relations = true;
					}
				}
				
				if (e.data.selections[0] && og.dimension_object_type_contents[config.dimensionId][e.target.object_type_id][e.data.selections[0].data.ot_id] &&
						og.dimension_object_type_contents[config.dimensionId][e.target.object_type_id][e.data.selections[0].data.ot_id].multiple) {
					
					if (og.preferences['drag_drop_prompt'] == 'prompt') {
						var rm_prev = has_relations ? (confirm(lang('do you want to mantain the current associations of this obj with members of', config.title)) ? "0" : "1") : "1";
					}else if (og.preferences['drag_drop_prompt'] == 'move') {
						var rm_prev = true ;
					}else if (og.preferences['drag_drop_prompt'] == 'keep') {
						var rm_prev = false ;
					}
					
					og.openLink(og.getUrl('member', 'add_objects_to_member'),{
						method: 'POST',
						post: {objects: Ext.util.JSON.encode(ids), member: e.target.id, remove_prev:rm_prev},
						callback: function(){
							e.data.grid.load();
						}
					});
				} else {
					og.openLink(og.getUrl('member', 'add_objects_to_member'),{
						method: 'POST',
						post: {objects: Ext.util.JSON.encode(ids), member: e.target.id},
						callback: function(){
							e.data.grid.load();
						}
					});
				}
			}
			return false;
		}
    });

	og.MemberTree.superclass.constructor.call(this, config);
	
	var self = this ; // To change scope inside callbacks	

	// ********** TREE EVENTS *********** //
	this.on({
		click: function(node, e){
			og.contextManager.currentDimension = self.dimensionId ;
			og.eventManager.fireEvent("member tree node click", node);
			var treeConf = node.attributes.loader.ownerTree.initialConfig ;
			if  (node.getDepth() == 0 ){
				// Fire 'all' selection for other trees 
				// Manage dashboard
				if ( treeConf.dimensionOptions.defaultAjax ){
					var controller =  treeConf.dimensionOptions.defaultAjax.controller ;
					var action =  treeConf.dimensionOptions.defaultAjax.action ;
					if ( controller && action ) {
						og.customDashboard(controller, action, {}, true);
					}
				}
			}else{
				// Member selection (not root)
				if ( node.options && node.options.defaultAjax && node.options.defaultAjax.controller && node.options.defaultAjax.action) {
					var reload = ( this.getSelectionModel() && this.getSelectionModel().getSelectedNode() && this.getSelectionModel().getSelectedNode().id  ==  node.id );
					og.customDashboard( node.options.defaultAjax.controller, node.options.defaultAjax.action, {id: node.object_id}, reload);
                                        this.clearFilter();
                                        if(this.dimensionCode == 'feng_persons'){//people
                                            og.core_dimensions.onContactClick(node.id);
                                        }else if(this.dimensionCode == 'workspaces'){//workspaces
                                            og.workspaces.onWorkspaceClick(node.id);
                                        }else if(this.dimensionCode == 'customer_project'){//crpm     
                                            og.projects.onProjectClick(node.id);
                                        }
                                        node.expand();
                                        $("#" + this.id + '-textfilter').val("");
                                }else{
					og.resetDashboard();
				}
			
			}
                        
		}
	});
	
	this.getSelectionModel().on({
		
		selectionchange : function(sm, selection) {
			if (selection && !this.pauseEvents) {
				og.contextManager.cleanActiveMembers(this.dimensionId) ;
				if ( ! this.isMultiple() ){
					//Single Selection
					var node = selection ; 
					if (node.getDepth()) {
						var member = node.attributes.id ;
						if(node.attributes.allow_childs) {
							$('#'+this.id + " .member-quick-form-link").show();
						}else{
							$('#'+this.id + " .member-quick-form-link").hide();
						}
					}else{
						$('#'+this.id + " .member-quick-form-link").show();
						var member = 0 ; 
					}
					if (!this.hidden) {
						og.contextManager.addActiveMember(member, this.dimensionId, node );
					}
					if ( this.filterOnChange ) {
						var trees = this.ownerCt.items;
						if (trees){
							this.suspendEvents();
							this.totalFilterTrees = 0 ;
							this.filteredTrees = 0 ;
							trees.each(function (item, index, length){
								if ( self.id != item.id  && (!item.hidden ||item.reloadHidden) && self.reloadDimensions.indexOf(item.dimensionId) != -1  ) {
									// Filter other Member Trees
									self.totalFilterTrees++;
									item.filterByMember(member ,function(){
										self.filteredTrees ++ ;
										if (self.filteredTrees == self.totalFilterTrees) {
											self.resumeEvents() ;
											og.eventManager.fireEvent('member trees updated',node);
										}
									}) ;
									
								}
							});
							if (this.totalFilterTrees == 0 ) {
								this.resumeEvents();
								og.eventManager.fireEvent('member trees updated',node);
								
							}
						}
					}
					
					var type =  node.attributes.object_type_id;
					og.contextManager.lastSelectedNode = node ;
					og.contextManager.lastSelectedDimension = this.dimensionId ;
					og.contextManager.lastSelectedMemberType = type; 
					
					og.eventManager.fireEvent('member changed', node);
					
				}else { 
					// Multiple Selection: (UNDER DEVELOPENT) 
					// Add to context
					for (var i = 0 ; i < selection.length ; i++) {
						var node = selection[i] ;
						if (node.getDepth()) {
							var member = node.attributes.id ;
						} else {
							var member = 0;
						}
						og.contextManager.addActiveMember(member, this.dimensionId, node );
					}
				}
			}
		},
		scope:this // Con esto this referencia al TreeNode. Sino al SelModel
	});
	
	this.init(function(){
		  self.selectRoot([0]) ; 
	}) ;
	
	// **************** TREE INIT **************** //
};

Ext.extend(og.MemberTree, Ext.tree.TreePanel, {

	// ******* ATTRIBUTES ******** //
	
	filterOnChange: true,
	
	filterTree: function(text) {

		if (text == this.getTopToolbar().items.get(this.id + '-textfilter').emptyText) {
			text = "";
		}
		if (text.trim() == '') {
			this.clearFilter();
		} else {
			var re = new RegExp(Ext.escapeRe(text.toLowerCase()), 'i');
			this.filterNode(this.getRootNode(), re);
			this.expandAll();
		}
	},
	
	filterNode: function(n, re) {
		
		var f = false;
		var c = n.firstChild;
		while (c) {
			f = this.filterNode(c, re) || f;
			c = c.nextSibling;
		}
		f = re.test(n.text.toLowerCase()) || f;
		if (!n.previousState) {
			// save the state before filtering
			n.previousState = n.expanded ? "e" :"c";
		}
		if (f) {
			n.getUI().show();
		} else {
			n.getUI().hide();
		}
		return f;
	},
	
	clearFilter: function(n) {
		if (!n) n = this.getRootNode();
		if (!n.previousState) return;
		var c = n.firstChild;
		while (c) {
			this.clearFilter(c);
			c = c.nextSibling;
		}
		n.getUI().show();
		if (n.previousState == "e") {
			n.expand(false, false);
		} else if (n.previousState == "c") {
			n.collapse(false, false);
		}
		n.previousState = null;
	},
	
	
	
	expandedNodes: function () {
		nodes = [];
		nodes = nodes.concat( this.root.expandedNodes() );
		return nodes ;
	},
	
	init: function ( callback  ) {
		switch (this.expandMode) {
			case "all":
				this.expandAll(callback);
				break;
			case "root":
				this.root.expand(0,0,callback) ;
				break;
			case "none": default : // Not expand ?
				break;
		}
	} ,

	// ******* METHODS ******** //
	
	isMultiple: function() {
		return ( this.getSelectionModel().constructor.toString().indexOf("Array") != -1 );
	},
	
	selectRoot: function() {
		selModel = this.getSelectionModel() ;
		selModel.suspendEvents();
		var node = this.getRootNode() ;
		selModel.select(node) ;
		if (!this.hidden) og.contextManager.addActiveMember(0, this.dimensionId, node );
		selModel.resumeEvents();

	},
	
	hide: function() {
		og.MemberTree.superclass.hide.call(this);
		og.contextManager.cleanActiveMembers(this.dimensionId);
	},
	show: function() {
		og.MemberTree.superclass.show.call(this);
		og.contextManager.cleanActiveMembers(this.dimensionId);
		this.selectRoot();
	}, 

	selectNodes: function(nids) {
		for (var i = 0 ; i < nids.length ; i++ ) {
			if ( nids[i] != "undefined" ) {
				if ( nids[i] != 0 ) {
					var node = this.getNodeById(nids[i]) ;
				}else{
					var node = this.getRootNode();
				}
				if (node) {
					selModel = this.getSelectionModel() ;
					selModel.suspendEvents();
					selModel.select(node) ;
					selModel.resumeEvents();
				}else{
					// If node not found in the new tree remove it from contextMAnager
					og.contextManager.cleanActiveMembers(this.dimensionId);
				}
			} 
		}
	
	},
	
	expandNodes: function (nids, callback) {
		
		for (var i = 0 ; i < nids.length ; i++ ) {
			if ( nids[i] != "undefined" ) {
				if ( nids[i] != 0 ) {
					var node = this.getNodeById(nids[i]) ;
					
				}else{
					var node = this.getRootNode();
				}
				if (node) {
					node.expand();
				}
			} 
		}
		
		
	},
	
	hideRoot: function () {
		this.addClass("root-hidden");
	},
	
	showRoot: function () {
		this.removeClass("root-hidden");
	},
	
	filterByMember: function(memberId, callback) {
		var tree = this ; //scope
		var expandedNodes = tree.expandedNodes() ;
		var selectedMembers = og.contextManager.getDimensionMembers(this.dimensionId) ;

		//tree.expandMode = "none";
		
		this.collapseAll() ;
		
		this.loader =  new og.MemberChooserTreeLoader({
			dataUrl: 'index.php?c=dimension&a=list_dimension_members_tree&ajax=true&dimension_id='+this.dimensionId+'&member_id='+memberId +'&avoid_session=1',	
			ownerTree: this
		});
		this.loader.load(this.getRootNode(), function() {
			
			tree.init(
				function() {
					if (tree.expandMode != "all"){
						// If not all nodes are exapnded, expand only needed
						tree.expandNodes(expandedNodes);
					}
					tree.selectNodes(selectedMembers); 
			        if( typeof callback == "function"){
			        	callback();
			        }
				} 
			);
		});
			
	},
	
	removeFromContext: function() {
		this.hide();
		this.collapse();
		this.getSelectionModel().select(this.getRootNode());
		var did = this.dimensionId;
		Ext.getCmp("dimension-selector-"+did).suspendEvents();
		Ext.getCmp("dimension-selector-"+did).setChecked(false);
		Ext.getCmp("dimension-selector-"+did).resumeEvents();
	}

});


// ***** EXTJS REGISTER COMPONENT ******* //
Ext.reg('member-tree', og.MemberTree);
