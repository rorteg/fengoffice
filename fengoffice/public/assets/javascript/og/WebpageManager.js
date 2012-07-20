/**
 *  WebpageManager
 */
og.WebpageManager = function() {
	var actions, markactions;
	this.doNotRemove = true;
	this.needRefresh = false;
	
	if (!og.WebpageManager.store) {
		og.WebpageManager.store = new Ext.data.Store({
	        proxy: new og.GooProxy({
	            url: og.getUrl('webpage', 'list_all')
	        }),
	        reader: new Ext.data.JsonReader({
	            root: 'webpages',
	            totalProperty: 'totalCount',
	            id: 'id',
	            fields: [
	                'name', 'description', 'url', 'updatedBy', 'updatedById',
	                'updatedOn', 'updatedOn_today', 'ix', 'isRead', 'memPath'
	            ]
	        }),
	        remoteSort: true,
			listeners: {
				'load': function() {
					var d = this.reader.jsonData;
					if (d.totalCount == 0) {
						this.fireEvent('messageToShow', lang("no objects message", lang("web pages")));
					} else if (d.webpages.length == 0) {
						this.fireEvent('messageToShow', lang("no more objects message", lang("web pages")));
					} else {
						this.fireEvent('messageToShow', "");
					}
                                        
                                        var sm = Ext.getCmp('webpage-manager').getSelectionModel();
                                        sm.clearSelections();
				}
			}
	    });
	    og.WebpageManager.store.setDefaultSort('updated', 'desc');
    }
    this.store = og.WebpageManager.store;
    this.store.addListener({messageToShow: {fn: this.showMessage, scope: this}});
    //--------------------------------------------
    // Renderers
    //--------------------------------------------

	function renderDragHandle(value, p, r, ix) {
		return '<div class="img-grid-drag" title="' + lang('click to drag') + '" onmousedown="var sm = Ext.getCmp(\'webpage-manager\').getSelectionModel();if (!sm.isSelected('+ix+')) sm.clearSelections();sm.selectRow('+ix+', true);"></div>';
	}
    
	var readClass = 'read-unread-' + Ext.id();
	
    function renderName(value, p, r) {
    	var classes = readClass + r.id;
		if (!r.data.isRead) classes += " bold";
		
		var name = String.format(
			'<a style="font-size:120%;" class="{3}" title="{2}" href="{1}" onclick="og.openLink(\'{1}\');return false;">{0}</a>',
			og.clean(value), og.getUrl('webpage', 'view', {id: r.id}), lang('view weblink'), classes);
		
		var actionStyle= ' style="font-size:90%;color:#777777;padding-top:3px;padding-left:18px;background-repeat:no-repeat" ';
		
		var actions = String.format('<a class="list-action ico-open-link" href="{0}" target="_blank" title="{1}" ' + actionStyle + '>&nbsp;</a>',
			r.data.url.replace(/\"/g, escape("\"")).replace(/\'/g, escape("'")), lang('open link in new window', og.clean(value)));
		actions = '<span>' + actions + '</span>';
			
		var text = '';
		if (r.data.description != ''){
			text = '&nbsp;-&nbsp;<span class="desc nobr">';
			text += og.clean(r.data.description) + "</span>";
		}
		
		var mem_path = "";
		var mpath = r.data.memPath ? Ext.util.JSON.decode(r.data.memPath) : "";
		if (mpath) mem_path = og.getCrumbHtml(mpath);
	    
		return mem_path + name + actions + text;
	}
    
    function renderIcon(value, p, r) {
		return '<div class="db-ico ico-webpage"></div>';
	}
    
    function renderIsRead(value, p, r){
    	var idr = Ext.id();
		var idu = Ext.id();
		var jsr = 'og.WebpageManager.store.getById(\'' + r.id + '\').data.isRead = true; Ext.select(\'.' + readClass + r.id + '\').removeClass(\'bold\'); Ext.get(\'' + idu + '\').setDisplayed(true); Ext.get(\'' + idr + '\').setDisplayed(false); og.openLink(og.getUrl(\'object\', \'mark_as_read\', {ids:' + r.id + '}));'; 
		var jsu = 'og.WebpageManager.store.getById(\'' + r.id + '\').data.isRead = false; Ext.select(\'.' + readClass + r.id + '\').addClass(\'bold\'); Ext.get(\'' + idr + '\').setDisplayed(true); Ext.get(\'' + idu + '\').setDisplayed(false); og.openLink(og.getUrl(\'object\', \'mark_as_unread\', {ids:' + r.id + '}));';
		return String.format(
			'<div id="{0}" title="{1}" class="db-ico ico-read" style="display:{2}" onclick="{3}"></div>' + 
			'<div id="{4}" title="{5}" class="db-ico ico-unread" style="display:{6}" onclick="{7}"></div>',
			idu, lang('mark as unread'), value ? 'block' : 'none', jsu, idr, lang('mark as read'), value ? 'none' : 'block', jsr
		);
	}
    
	function renderDateUpdated(value, p, r) {
		if (!value) {
			return "";
		}
		var userString = String.format('<a href="{1}" onclick="og.openLink(\'{1}\');return false;">{0}</a>', r.data.updatedBy, og.getUrl('contact', 'card', {id: r.data.updatedById}));

		var now = new Date();
		var dateString = '';
		if (!r.data.updatedOn_today) {
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
				ret += "," + selections[i].id;
			}
			og.lastSelectedRow.webpages = selections[selections.length-1].data.ix;
			return ret.substring(1);
		}
	}
	this.getSelectedIds = getSelectedIds;
	
	function getFirstSelectedId() {
		if (sm.hasSelection()) {
			return sm.getSelected().id;
		}
		return '';
	}

	var sm = new Ext.grid.CheckboxSelectionModel();
	sm.on('selectionchange',
		function() {
			var allUnread = true, allRead = true;
			var selections = sm.getSelections();
			for (var i=0; i < selections.length; i++) {
				if (selections[i].data.isRead){
					allUnread = false;
				} else {
					allRead = false;
				}
			}
			if (sm.getCount() <= 0) {
				actions.delWebpage.setDisabled(true);
				actions.editWebpage.setDisabled(true);
				markactions.markAsRead.setDisabled(true);
				markactions.markAsUnread.setDisabled(true);
				actions.archive.setDisabled(true);
			} else {
				actions.editWebpage.setDisabled(sm.getCount() != 1);
				actions.delWebpage.setDisabled(false);
				if (allUnread) {
					markactions.markAsUnread.setDisabled(true);
				} else {
					markactions.markAsUnread.setDisabled(false);
				}
				if (allRead) {
					markactions.markAsRead.setDisabled(true);
				} else {
					markactions.markAsRead.setDisabled(false);
				}
				actions.archive.setDisabled(false);
						
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
			dataIndex: 'type',
			width: 28,
        	renderer: renderIcon,
        	fixed: true,
        	resizable: false,
        	hideable:true,
        	menuDisabled: true
		},{
			id: 'isRead',
			header: '&nbsp;',
			dataIndex: 'isRead',
			width: 16,
        	renderer: renderIsRead,
        	fixed:true,
        	resizable: false,
        	hideable:false,
        	menuDisabled: true
		},{
			id: 'name',
			header: lang("name"),
			dataIndex: 'name',
			width: 300,
			sortable: true,
			renderer: renderName
        },{
        	id: 'updated',
			header: lang("last updated by"),
			dataIndex: 'updatedOn',
			width: 90,
			renderer: renderDateUpdated,
			sortable: true
        }]);
    cm.defaultSortable = false;
    
    markactions = {
		markAsRead: new Ext.Action({
			text: lang('mark as read'),
            tooltip: lang('mark as read desc'),
            iconCls: 'ico-mark-as-read',
			disabled: true,
			handler: function() {
				this.load({
					action: 'markasread',
					ids: getSelectedIds()				
				});
				this.getSelectionModel().clearSelections();
			},
			scope: this
		}),
		markAsUnread: new Ext.Action({
			text: lang('mark as unread'),
            tooltip: lang('mark as unread desc'),
            iconCls: 'ico-mark-as-unread',
			disabled: true,
			handler: function() {
				this.load({
					action: 'markasunread',
					ids: getSelectedIds()				
				});
				this.getSelectionModel().clearSelections();
			},
			scope: this
		})
    };
	
	actions = {
		newWebpage: new Ext.Action({
			text: lang('new'),
            tooltip: lang('add new webpage'),
            iconCls: 'ico-new',
            handler: function() {
				var url = og.getUrl('webpage', 'add');
				og.openLink(url, null);
			}
		}),
		delWebpage: new Ext.Action({
			text: lang('move to trash'),
            tooltip: lang('move selected objects to trash'),
            iconCls: 'ico-trash',
			disabled: true,
			handler: function() {
				if (confirm(lang('confirm move to trash'))) {
					this.load({
						action: 'delete',
						webpages: getSelectedIds()
					});
				}
			},
			scope: this
		}),
		editWebpage: new Ext.Action({
			text: lang('edit'),
            tooltip: lang('edit selected webpage'),
            iconCls: 'ico-edit',
			disabled: true,
			handler: function() {
				var url = og.getUrl('webpage', 'edit', {id:getFirstSelectedId()});
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
						webpages: getSelectedIds()
					});
					this.getSelectionModel().clearSelections();
				}
			},
			scope: this
		}),
		refresh: new Ext.Action({
			text: lang('refresh'),
            tooltip: lang('refresh desc'),
            iconCls: 'ico-refresh',
			handler: function() {
				this.store.reload();
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
		})
    };
    
	var tbar = [];
	if (!og.loggedUser.isGuest) {
		tbar.push(actions.newWebpage);
		tbar.push('-');
		tbar.push(actions.editWebpage);
		tbar.push(actions.archive);
		tbar.push(actions.delWebpage);		
		tbar.push('-');
	}
	tbar.push(actions.markAs);
	
	og.WebpageManager.superclass.constructor.call(this, {
        store: this.store,
		layout: 'fit',
        cm: cm,
        enableDrag: true,
		ddGroup: 'MemberDD',
		stateful: og.preferences['rememberGUIState'],
        closable: true,
		stripeRows: true,
		id: 'webpage-manager',
        bbar: new og.CurrentPagingToolbar({
            pageSize: og.config['files_per_page'],
            store: this.store,
            displayInfo: true,
            displayMsg: lang('displaying webpages of'),
            emptyMsg: lang("no webpages to display")
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


};

Ext.extend(og.WebpageManager, Ext.grid.GridPanel, {
	load: function(params) {
		if (!params) params = {};
		var start;
		if (typeof params.start == 'undefined') {
			start = (this.getBottomToolbar().getPageData().activePage - 1) * og.config['files_per_page'];
		} else {
			start = 0;
		}
		
		this.store.baseParams = {
			context: og.contextManager.plainContext()
		};
		
		this.store.load({
			params: Ext.apply(params, {
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
	
	showMessage: function(text) {
		this.innerMessage.innerHTML = text;
	},
	
	archiveObjects: function() {
		if (confirm(lang('confirm archive selected objects'))) {
			this.load({
				action: 'archive',
				webpages: this.getSelectedIds()
			});
			this.getSelectionModel().clearSelections();
		}
	},
	
	trashObjects: function() {
		if (confirm(lang('confirm move to trash'))) {
			this.load({
				action: 'delete',
				webpages: this.getSelectedIds()
			});
			this.getSelectionModel().clearSelections();
		}
	}
	
});

Ext.reg("webpages", og.WebpageManager);