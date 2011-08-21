og.TagMenu = function(config, tags) {
	if (!config) config = {};
	
	og.TagMenu.superclass.constructor.call(this, Ext.apply(config, {
		cls: 'scrollable-menu',
		items: []
	}));

	this.addEvents({tagselect: true, tagdelete: true});
	this.tagnames = {};

	this.loadTags();
	if (tags) {
		this.addTags(tags);
	}
	
	if (Ext.isIE) { // Add scrollbar in IE
		this.getEl().child('ul.x-menu-list').addClass('iemenulist');
		this.getEl().child('ul.x-menu-list').setWidth(this.getEl().child('ul.x-menu-list').getWidth()+20);
	}

	Ext.getCmp('tag-panel').on('loadtags', this.loadTags, this);
	
	og.eventManager.addListener('tag added', this.addTag, this);
	og.eventManager.addListener('tag deleted', this.removeTag, this);
};

Ext.extend(og.TagMenu, Ext.menu.Menu, {

	removeTag: function(tag) {
		var item = this.tagnames[tag.name];
		if (item) {
			this.remove(item);
		}
	},

	addTag : function(tag){
		var exists = this.tagnames[tag.name];
		if (exists) {
			return;
		}
		var item = new Ext.menu.Item({
			text: og.clean(tag.name),
			handler: function() {
				this.fireEvent('tagselect', tag.name);
			},
			scope: this
		});
		var c = this.items.getCount();
		this.insert(c-3, item);
		this.tagnames[tag.name] = item;
		
		return item;
	},
	
	exists: function(tagname) {
		return this.tagnames[tagname];
	},
	
	addTags: function(tags) {
		for (var i=0; i < tags.length; i++) {
			this.addTag(tags[i]);
		}
	},
	loadDeleteTags: function() {
		var dm = this.items.get('delete').menu;
		dm.addItem(new Ext.menu.Item({
			text: lang('delete all tag'),
			handler: function() {
				this.fireEvent('tagdelete', '', true);							
			},
			scope: this,
			id: lang('delete all tags')
		}));
		dm.addItem(new Ext.menu.Item({
			text: lang('delete tag'),
			handler: function() {
				Ext.Msg.prompt(lang('delete tag'),
					lang('enter the desired tag'),
					function (btn, text) {
						if (btn == 'ok' && text) {
							this.fireEvent('tagdelete', text.replace(/^\s*|\s*$/g, ''));
						}
					},
					this	
				);
			},
			scope: this,
			id: lang('delete tag by name')
		}));
		dm.addSeparator();
		var tags = Ext.getCmp('tag-panel').getTags();
		for (var i=0; i < tags.length; i++){
			dm.addItem(new Ext.menu.Item({
				text : tags[i].name,
				handler : function (btn) {
					this.fireEvent('tagdelete', btn.text);
				},
				scope: this
			}));
		}
	},
	loadTags: function() {
		var tags = Ext.getCmp('tag-panel').getTags();
		this.removeAll();
		this.tagnames = {};
		this.addItem(new Ext.menu.Item({
			text: lang('add tag'),
			iconCls: 'ico-addtag',
			handler: function() {
				Ext.Msg.prompt(lang('add tag'),
					lang('enter the desired tag'),
					function (btn, text) {
						if (btn == 'ok' && text) {
							this.fireEvent('tagselect', text.replace(/^\s*|\s*$/g, ''));
						}
					},
					this	
				);
			},
			scope: this
		}));
		this.addSeparator();
		this.addItem(new Ext.menu.Item({
			id: 'delete',
	    	text: lang('delete tag'),
	    	menu: {
				cls: 'scrollable-menu',
				items: []
			},
			iconCls: 'ico-delete',
			scope: this
		}));
		this.addTags(tags);
		this.loadDeleteTags();
	}
});