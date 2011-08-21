og.TagPanel = function(config) {
	if (!config) config = {};
	this.tree = new og.TagTree(config.tagtree);
	
	var tbar = [];
	tbar.push({
		iconCls: 'ico-workspace-refresh',
		tooltip: lang('refresh desc'),
		handler: function() {
			this.loadTags();
		},
		scope: this.tree
	});
	tbar.push({
		iconCls: 'ico-sort-count',
		tooltip: lang('sort tags'),
		id: 'sort',
		menu: {
			items: [{
				iconCls: 'ico-sort-alphabetical',
				text: lang('sort tags alphabetically'),
				handler: function() {
					this.tree.loadTags(og.getUrl('tag', 'list_tags', {order: 'name'}));
					this.getTopToolbar().items.get('sort').setIconClass('ico-sort-alphabetical');
				},
				scope: this
			},{
				iconCls: 'ico-sort-count',
				text: lang('sort tags by count'),
				handler: function() {
					this.tree.loadTags(og.getUrl('tag', 'list_tags', {order: 'count'}));
					this.getTopToolbar().items.get('sort').setIconClass('ico-sort-count');
				},
				scope: this
			}]
		}
	});
	if (og.loggedUser.isAdmin) {
		tbar.push({
			iconCls: 'ico-rename',
			tooltip: lang('rename tag'),
			id: 'rename',
			handler: function() {
				Ext.Msg.prompt(lang('rename tag'), lang('enter a new name for the tag') + ':',
					function(btn, text) {
						if (btn == 'ok') {
							if (text == '') {
								alert(lang("you must enter a name"));
							} else {
								this.renameTag(this.getSelectedTag(), text);
							}
						}
					},
					this);
			},
			scope: this.tree
		});
		tbar.push({
			iconCls: 'ico-delete',
			tooltip: lang('delete tag'),
			id: 'delete',
			handler: function() {
				if (confirm(lang('confirm delete tag', this.getSelectedTag()))) {
					this.deleteTag(this.getSelectedTag());
				}
			},
			scope: this.tree
		});
	}
	Ext.applyIf(config, {
		split: true,
		height: 200,
		iconCls: 'ico-tags',
		title: lang('tags'),
		region: 'south',
		border: false,
		style: 'border-top-width: 1px',
		bodyBorder: false,
		collapsible: true,
		collapsed: false,
		collapsedTitle:  lang('tags'),
		plugins: new Ext.ux.collapsedPanelTitlePlugin(),
		layout: 'fit',
		items: [this.tree],
		tbar: tbar
	});
	og.TagPanel.superclass.constructor.call(this, config);
	
	this.tree = this.findById('tag-panel');
	
	this.tree.getSelectionModel().on({
		'selectionchange' : function(sm, node) {
			var rename = this.getTopToolbar().items.get('rename');
			if (rename)
				rename.setDisabled(!node || node == this.tree.tags);
		},
		scope:this
	});
};

Ext.ux.collapsedPanelTitlePlugin = function ()
{
    this.init = function(p) {
        if (p.collapsible)
        {
            var r = p.region;
            if ((r == 'north') || (r == 'south'))
            {
                p.on ('render', function()
                    {
                        var ct = p.ownerCt;
                        ct.on ('afterlayout', function()
                            {
                                if (ct.layout[r].collapsedEl)
                                {
                                    var leftPadding = ( p.collapsedIcon ? 24 : 4 ); 
                                    p.collapsedTitleEl = ct.layout[r].collapsedEl.createChild ({
                                        tag: 'div',
                                        style: 'color : #15428B; font-weight: bold; padding-left:' + leftPadding + 'px; margin-left:4px; margin-top:2px; background: url(' + p.collapsedIcon + ') no-repeat left center;',
                                        html: p.collapsedTitle
                                    });
                                }
                            }, false, {single:true});
                        p.on ('collapse', function()
                            {
                                if (ct.layout[r].collapsedEl && !p.collapsedTitleEl)
                                {
                                    var leftPadding = ( p.collapsedIcon ? 24 : 4 ); 
                                    p.collapsedTitleEl = ct.layout[r].collapsedEl.createChild ({
                                        tag: 'span',
                                        style: 'color : #15428B; padding-left:' + leftPadding + 'px; margin-left:4px; margin-top:2px; background: url(' + p.collapsedIcon + ') no-repeat left center;',
                                        html: p.collapsedTitle
                                    });
                                }
                            }, false, {single:true});
                    });
            }
        }
    };
}

Ext.extend(og.TagPanel, Ext.Panel, {});

og.TagTree = function(config) {
	if (!config) config = {};

	Ext.applyIf(config, {
		id: 'tag-panel',
		/**/ddGroup: 'WorkspaceDD',
		enableDrop: true,/**/
		autoScroll: true,
		autoLoadTags: false,
		rootVisible: false,
		lines: false,
		border: false,
		bodyBorder: false,
		root: new Ext.tree.TreeNode(lang('tags')),
		collapseFirst: false,
		tbar: [{
			xtype: 'textfield',
			id: 'tag-filter',
			width: 200,
			emptyText:lang('filter tags'),
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
	if (!config.listeners) config.listeners = {};
	Ext.apply(config.listeners, {
		beforenodedrop: function(e) {
			if (e.data.grid) {
				if (e.target.tag.name == '') {
					og.confirmRemoveTags(e.data.grid.id);
				} else {
					e.data.grid.tagObjects(e.target.tag.name);
				}
			}
			return false;
		}
    });
	og.TagTree.superclass.constructor.call(this, config);

	this.tags = this.root.appendChild(
		new Ext.tree.TreeNode({
			text: lang('all'),
			expanded: true,
			listeners: {
				click: function() {
					this.unselect();
					this.select();
				}
			}
		})
	);
	this.tags.tag = {name: ""};

	this.getSelectionModel().on({
		'selectionchange' : function(sm, node) {
			if (node && !this.pauseEvents) {
				this.fireEvent('tagselect', node.tag);
			}
			var tf = this.getTopToolbar().items.get('tag-filter');
			tf.setValue("");
			this.filterTree("");
			if (node) {
				node.expand();
				node.ensureVisible();
			}
		},
		scope:this
	});

	this.addEvents({tagselect: true});
	
	og.eventManager.addListener('tag added', this.addTag, this);
	og.eventManager.addListener('tag deleted', this.removeTag, this);
	
	if (this.autoLoadTags) {
		this.loadTags();
	}
};

Ext.extend(og.TagTree, Ext.tree.TreePanel, {
	
	getNode: function(tagname) {
		if (!tagname) return this.tags;
		return this.getNodeById(this.nameToId(tagname));
	},

	removeTag: function(tag) {
		var node = this.getNode(tag.name);
		if (node) {
			node.unselect();
			Ext.fly(node.ui.elNode).ghost('l', {
				callback: node.remove, scope: node, duration: .4
			});
		}
	},

	addTag : function(tag){
		var exists = this.getNode(tag.name);
		if (exists) {
			return;
		}
		var text = og.clean(tag.name);
		if (tag.count) {
			text += ' <span style="color:#777">(' + tag.count + ')</span>';
		}
		var config =  {
			iconCls: 'ico-tag',
			leaf: true,
			cls: 'tag-item',
			text: text,
			id: this.nameToId(tag.name),
			listeners: {
				click: function() {
					this.unselect();
					this.select();
				}
			}
		};
		var node = new Ext.tree.TreeNode(config);
		node.tag = tag;
		this.tags.appendChild(node);
		/*Ext.fly(node.ui.elNode).slideIn('l', {
			callback: Ext.emptyFn, scope: this, duration: .4
		});*/
		return node;
	},
	
	addTags: function(tags) {
		var index = 0;
        var me = this;
        var do_add_tags = function() {
                for (var i=0; i < 50 && index < tags.length; i++) {
                        me.addTag(tags[index++]);
                }
                if (index < tags.length) {
                        setTimeout(do_add_tags, 1000);
                }
        };
        do_add_tags();
	},
	
	getSelectedTag: function() {
		var s = this.getSelectionModel().getSelectedNode();
		if (s) {
			return this.getSelectionModel().getSelectedNode().tag.name;
		} else {
			return '';
		}
	},
	
	getTags: function() {
		var tags = [];
		var node = this.tags.firstChild;
		while (node) {
			tags.push(node.tag);
			node = node.nextSibling;
		}
		return tags;
	},
	
	select: function(id) {
		if (!id) {
			this.tags.select();
		} else {
			var node = this.getNode(id);
			if (node) {
				node.select();
			}
		}
	},
	
	hasTag: function(tagname) {
		return this.getNode(tagname);
	},
	
	loadTags: function(url, config) {
		if (!config) config = {};
		if (this.loadTagsFrom) {
			this.removeAll();
			var tags = Ext.getCmp(this.loadTagsFrom).getTags();
			this.addTags(tags);
			this.tags.expand();
			this.fireEvent('loadtags', tags);
		} else {
			if (!url) {
				url = og.getUrl('tag', 'list_tags');
			}
			og.openLink(url, {
				callback: function(success, data) {
					if (success && data.tags) {
						var selected = this.getSelectedTag();
						this.removeAll();
						this.addTags(data.tags);
						
						this.tags.expand();
						
						if (this.hasTag(selected)) {
							this.pauseEvents = true;
							this.select(selected);
							this.pauseEvents = false;
						} else {
							this.pauseEvents = true;
							this.tags.select();
							this.pauseEvents = false;
						}
						if (config.callback) {
							config.callback.call(config.scope);
						}
						this.fireEvent('loadtags', data.tags);
					}
				},
				scope: this
			});
		}
	},
	
	removeAll: function() {
		var node = this.tags.firstChild;
		while (node) {
			var aux = node;
			node = node.nextSibling;
			aux.remove();
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
		if (f) {
			n.getUI().show();
		} else {
			n.getUI().hide();
		}
		return f;
	},
	
	filterTree: function(text) {
		if (text == this.getTopToolbar().items.get('tag-filter').emptyText) {
			text = "";
		}
		this.expandAll();
		var re = new RegExp(Ext.escapeRe(text.toLowerCase()), 'i');
		this.filterNode(this.tags, re);
		this.tags.getUI().show();
	},
	
	isSelected: function(tagname) {
		return this.getSelectedTag() == tagname;
	},
	
	renameTag: function(tagname, newTagname) {
		if (!this.hasTag(newTagname) || confirm(lang('confirm merge tags', tagname, newTagname))) {
			this.loadTags(og.getUrl('tag', 'rename_tag', {tag: tagname, new_tag: newTagname}), {
				callback: function() {
					this.fireEvent('tagselect', this.tags.tag.name);
				},
				scope: this
			});
		}
	},
	/**
	 * Removes invalid characters from a tag name
	 * so that the result can be used as a node id.
	 */
	nameToId: function(name) {
		var id = "_";
		for (var i=0; i < name.length; i++) {
			id += name.charCodeAt(i) + "_";
		}
		return id;
	},
	
	deleteTag: function(name) {
		this.loadTags(og.getUrl('tag', 'delete_tag_by_name', {tag: name}), {
			callback: function() {
				this.fireEvent('tagselect', this.tags.tag.name);
			},
			scope: this
		});
	}
});

Ext.reg('tagpanel', og.TagPanel);
Ext.reg('tagtree', og.TagTree);
