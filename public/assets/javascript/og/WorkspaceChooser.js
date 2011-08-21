og.WorkspaceChooserTree = function(config) {
	if (!config) config = {};
	var workspaces = config.workspaces;
	delete config.workspaces;
	this.wsField = Ext.getDom(config.field) || {};
	if (!this.wsField.value) this.wsField.value = "";

	Ext.applyIf(config, {
		autoScroll: true,
		rootVisible: false,
		loadWorkspacesFrom: false,
		lines: false,
		root: new Ext.tree.TreeNode(lang('workspaces')),	
		collapseFirst: false,
		tbar: [{
			xtype: 'textfield',
			width: 200,
			emptyText:lang('filter workspaces'),
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
		}]
	});
	
	og.WorkspaceChooserTree.superclass.constructor.call(this, config);

	this.workspaces = this.root.appendChild(
		new Ext.tree.TreeNode({
			id: "ws0",
			text: lang('all'),
			expanded: true,
			name: lang('all'),
			cls:'x-tree-noicon',
			listeners: {
				click: function() {
					this.unselect();
					this.select();
				}
			}
		})
	);
	this.workspaces.ws = {id: 0, n: lang('all')};
	
	if (workspaces || this.loadWorkspacesFrom) {
		if (this.loadWorkspacesFrom) {
			workspaces = Ext.getCmp(this.loadWorkspacesFrom).getWsList(0, true);
			for (var i=0; i < workspaces.length; i++) {
				workspaces[i].n = workspaces[i].name;
				workspaces[i].p = workspaces[i].parent;
				workspaces[i].rp = workspaces[i].realParent;
				workspaces[i].d = workspaces[i].depth;
				workspaces[i].c = workspaces[i].color;
			}
		}
		if (typeof workspaces == "string") {
			workspaces = Ext.util.JSON.decode(workspaces);
		}
		this.addWorkspaces(workspaces);
		var ids = this.wsField.value.split(",");
		for (var i=0; i < ids.length; i++) {
			var n = ids[i].trim();
			var node = this.getNodeById(this.nodeId(n));
			if (node) {
				node.ensureVisible();
				node.suspendEvents();
				node.ui.toggleCheck(true);
				node.ws.checked = true;
				node.resumeEvents();
			}
		}
	}

	this.getSelectionModel().on({
		'selectionchange' : function(sm, node) {
			if (node && !this.pauseEvents) {
				this.fireEvent("wsselected", node.ws);
				this.clearFilter();
				node.expand();
				node.ensureVisible();
			}
		},
		scope:this
	});
	
	this.addEvents({workspaceselect: true});
};

Ext.extend(og.WorkspaceChooserTree, Ext.tree.TreePanel, {
	removeWS: function(ws) {
		var node = this.getNodeById(this.nodeId(ws.id));
		if (node) {
			if (node.isSelected()) {
				this.workspaces.select();
			}
			Ext.fly(node.ui.elNode).ghost('l', {
				callback: node.remove, scope: node, duration: .4
			});
		}
	},
	
	updateWS : function(ws) {
		this.addWS(ws);
		og.updateWsCrumbs(ws);
	},

	addWS : function(ws) {
		var exists = this.getNodeById(this.nodeId(ws.id));
		if (exists) {
			exists.setText(ws.n);
			if (ws.p != exists.ws.p) {
				var selected = exists.isSelected();
				var parent = this.getNode(ws.p);
				if (parent) {
					parent.appendChild(exists);
					exists.ws.parent = parent.ws.id;
					if (selected) exists.select();
				}
			}
			return;
		}
		var config = {
			cls: 'x-tree-noicon',
			text: og.clean(ws.n),
			id: this.nodeId(ws.id),
			checked: false,
			listeners: {
				click: function() {
					this.unselect();
					this.select();
				},
				checkchange: {
					fn: function(node, checkedValue) {
						node.ws.checked = checkedValue;
						node.select();
						if (this.wsField) {
							var tids = this.wsField.value.split(",");
							var ids = [];
							for (var i=0,j=0; i < tids.length; i++) {
								var x = tids[i].trim();
								if (x && x != node.ws.id) {
									ids.push(x);
								}
							} 
							if (checkedValue) {
								ids.push(node.ws.id);
							}
							this.wsField.value = ids.join(",");
						}
						this.fireEvent("wschecked", node.ws);
					},
					scope: this
				}
			}
		};
		var node = new Ext.tree.TreeNode(config);
		node.ws = ws;
		node.ws.checked = false;
		var parent = this.getNodeById(this.nodeId(ws.p));
		if (!parent) parent = this.workspaces;
		var iter = parent.firstChild;
		while (iter && node.text.toLowerCase() > iter.text.toLowerCase()) {
			iter = iter.nextSibling;
		}
		parent.insertBefore(node, iter);
		return node;
	},
	
	uncheckAll: function(node) {
		if (!node) node = this.root;
		var child = node.firstChild;
		if (node.ui.isChecked()) node.ui.toggleCheck(false);
		while (child) {
			this.uncheckAll(child);
			child = child.nextSibling;
		}
	},
	
	getActiveWorkspace: function() {
		var s = this.getSelectionModel().getSelectedNode();
		if (s) {
			return this.getSelectionModel().getSelectedNode().ws;
		} else {
			return {id: 0, name: 'all'};
		}
	},
	
	getSelected: function() {
		return this.getActiveWorkspace();
	},
	
	nodeId: function(id) {
		return 'ws' + id;
	},
	
	addWorkspaces: function(workspaces) {
		//Orders the workspaces so as to add them in hierarchy
		
		var workspacesToAdd = new Array();
		var continueOrdering = true;
		while(continueOrdering)
		{
			continueOrdering = false;
			for (var i = 0; i < workspaces.length; i++){
				var add = false;
				var ws = workspaces[i];
				if (ws.p == 0)
					add = true;
				else for (var j = 0; j < workspacesToAdd.length; j++)
					if (workspacesToAdd[j].id == ws.p){
						add = true;
						break;
					}
				if (add){
					continueOrdering = true;
					workspacesToAdd[workspacesToAdd.length] = workspaces.splice(i,1)[0];
					i--;
				}
			}
		}
		
		for (var i=0; i < workspacesToAdd.length; i++) {
			this.addWS(workspacesToAdd[i]);
		}
		this.workspaces.expand();
	},
	
	select: function(id) {
		if (!id) {
			this.workspaces.ensureVisible();
			this.workspaces.select();
		} else {
			var node = this.getNodeById(this.nodeId(id));
			if (node) {
				node.ensureVisible();
				node.select();
			}
		}
	},
	
	getNode: function(id) {
		if (!id) {
			return this.workspaces;
		} else {
			var node = this.getNodeById(this.nodeId(id));
			if (node) {
				return node;
			}
		}
		return null;
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
			n.previousState = n.expanded?"expanded":"collapsed";
		}
		if (f) {
			n.getUI().show();
		} else {
			n.getUI().hide();
		}
		return f;
	},
	
	filterTree: function(text) {
		var re = new RegExp(Ext.escapeRe(text.toLowerCase()), 'i');
		this.filterNode(this.workspaces, re);
		this.workspaces.getUI().show();
		this.expandAll();
	},
	
	clearFilter: function(n) {
		if (!n) n = this.workspaces;
		if (!n.previousState) return;
		var c = n.firstChild;
		while (c) {
			this.clearFilter(c);
			c = c.nextSibling;
		}
		n.getUI().show();
		if (this.getSelectionModel().getSelectedNode().isAncestor(n)) {
			n.previousState = "expanded";
		}
		if (n.previousState == "expanded") {
			n.expand();
		} else if (n.previousState == "collapsed") {
			n.collapse();
		}
		n.previousState = null;
	},
	
	getValue: function() {
		return this.wsField.value;
	}
});

Ext.reg('wsctree', og.WorkspaceChooserTree);




