/**
 *  MailManager
 *
 */
og.MailManager = function() {

	var actions, moreActions, selectActions, accountActions, markactions;
	this.accountId = og.emailFilters.account;
	this.readType = og.emailFilters.read;
	this.classifType = og.emailFilters.classif;
	this.viewType = "all";
	this.stateType = "received";
	this.doNotRemove = true;
	this.needRefresh = false;
	this.maxrowidx = 0;

	var fields = [
		'object_id', 'type', 'accountId', 'accountName', 'hasAttachment', 'subject', 'text', 'date',
		'projectId', 'projectName', 'userId', 'userName', 'tags', 'workspaceColors','isRead','from',
		'from_email','isDraft','isSent','folder','to', 'ix', 'conv_total', 'conv_unread', 'conv_hasatt'
	];
	this.Record = Ext.data.Record.create(fields);
	
	if (!og.MailManager.store) {
		og.MailManager.store = new Ext.data.Store({
			proxy: new og.GooProxy({
				url: og.getUrl('mail', 'list_all'),
				timeout: 0//Ext.Ajax.timeout
			}),
			reader: new Ext.data.JsonReader({
				root: 'messages',
				totalProperty: 'totalCount',
				id: 'id',
				fields: fields
			}),
			remoteSort: true,
			listeners: {
				'load': function(store, rs) {
					var d = this.reader.jsonData;
					var ws = og.clean(Ext.getCmp('workspace-panel').getActiveWorkspace().name);
					var tag = og.clean(Ext.getCmp('tag-panel').getSelectedTag());
					if (d.totalCount === 0) {
						if (tag) {
							this.fireEvent('messageToShow', lang("no objects with tag message", lang("emails"), ws, tag));
						} else {
							this.fireEvent('messageToShow', lang("no objects message", lang("emails"), ws));
						}
					} else if (d.messages.length == 0) {
						this.fireEvent('messageToShow', lang("no more objects message", lang("emails")));
					} else {
						this.fireEvent('messageToShow', "");
					}
					og.showWsPaths();
					//Ext.getCmp('mails-manager').getView().focusRow(og.lastSelectedRow.mails+1);
					if (typeof d.unreadCount != 'undefined') {
						og.updateUnreadEmail(d.unreadCount);
					}
					
					var manager = Ext.getCmp('mails-manager');
					var view = manager.getView();
					for (i=0; i<manager.maxrowidx; i++) {
						var el = view.getRow(i);
						if (el) el.innerHTML = el.innerHTML.replace('x-grid3-td-draghandle "', 'x-grid3-td-draghandle " onmousedown="var sm = Ext.getCmp(\'mails-manager\').getSelectionModel();if (!sm.isSelected('+i+')) {sm.clearSelections();} sm.selectRow('+i+', true);"');
					}
				}
			}
		});
		og.MailManager.store.setDefaultSort('date', 'desc');
	}
	this.store = og.MailManager.store;
	this.store.addListener({messageToShow: {fn: this.showMessage, scope: this}});
	
	function renderName(value, p, r) {
		var name = '';
		var classes = "";
		if (!r.data.isRead) classes += " bold";
		var strAction = 'view';
		
		if (r.data.isDraft) {
			strDraft = "<span style='font-size:80%;color:red'>"+lang('draft')+"&nbsp;</style>";			
			strAction = 'edit_mail';
		}
		else { strDraft = ''; }
		
		var subject = value && og.clean(value.trim()) || '<i>' + lang("no subject") + '</i>';
		var conv_str = r.data.conv_total > 1 ? " <span class='db-ico ico-comment' style='margin-left:3px;padding-left: 18px;'><span style='font-size:80%'>(" + (r.data.conv_unread > 0 ? '<b style="font-size:130%">' + r.data.conv_unread + '</b>/' : '') + r.data.conv_total + ")</span></span>" : "";
		
		var js = 'var r = og.MailManager.store.getById(\'' + r.id + '\'); r.data.isRead = true;og.openLink(\'{1}\');r.commit();og.showWsPaths();return false;';
		name = String.format(
				'{4}<a style="font-size:120%;" class="{3}" href="#" onclick="' + js + '" title="{2}">{0}</a>',
				subject + conv_str, og.getUrl('mail', strAction, {id: r.data.object_id}), og.clean(r.data.text),classes,strDraft);
				
		if (r.data.isSent) {
			name = String.format('<span class="db-ico ico-sent" style="padding-left:18px" title="{1}">{0}</span>',name,lang("mail sent"));
		}
		
		var projectstring = String.format('<span class="project-replace">{0}</span>&nbsp;', r.data.projectId);
		
		var text = '';
		if (r.data.text != ''){
			text = '&nbsp;-&nbsp;<span style="color:#888888;white-space:nowrap">';
			text += og.clean(r.data.text) + "</span></i>";
		}
		
		return projectstring + name + text;
	}

	function renderFrom(value, p, r){
		var strAction = 'view';
		var classes = "";
		
		if (r.data.isDraft) strAction = 'edit_mail';
		if (!r.data.isRead) classes += ' bold';

		var draw_to = (r.data.isSent || r.data.isDraft) && Ext.getCmp('mails-manager').stateType != 'sent';
		if (!r.data.to) r.data.to = "";
		var to_cut = r.data.to.length > 70 ? og.clean(r.data.to.substring(0, 67) + "...") : og.clean(r.data.to);
		var sender = (draw_to ? to_cut : og.clean(value.trim())) || '<i>' + lang("no sender") + '</i>';
		var title = draw_to ? og.clean(r.data.to) : og.clean(r.data.from_email);
		
		var js = 'var r = og.MailManager.store.getById(\'' + r.id + '\'); r.data.isRead = true;og.openLink(\'{1}\');r.commit();og.showWsPaths();return false;';
		name = String.format(
				'<a style="font-size:120%;" class="{3}" href="#" onclick="' + js + '" title="{2}">{0}</a>',
				sender, og.getUrl('mail', strAction, {id: r.data.object_id}), title, classes);
		return name;
	}
	
	function renderDragHandle(value, p, r) {
		Ext.getCmp('mails-manager').maxrowidx = r.data.ix;
		return '<div class="img-grid-drag" title="' + lang('click to drag') + '" onmousedown="var sm = Ext.getCmp(\'mails-manager\').getSelectionModel();if (!sm.isSelected('+r.data.ix+')) sm.clearSelections();sm.selectRow('+r.data.ix+', true);"></div>';
	}
	
	function renderIcon(value, p, r) {
		if (r.data.projectId.length > 0)
			return '<div class="db-ico ico-email"></div>';
		else
			return String.format('<a href="#" onclick="og.openLink(\'{0}\')" title={1}><div class="db-ico ico-classify"></div></a>', og.getUrl('mail', 'classify', {id: r.data.object_id}), lang('classify'));
	}

	function renderAttachment(value, p, r){
		if (value)
			return '<div class="db-ico ico-attachment"></div>';
		else
			return '';
	}
	
	function renderIsRead(value, p, r){
		var js = 'var r = og.MailManager.store.getById(\'' + r.id + '\'); r.data.isRead = !r.data.isRead;og.openLink(og.getUrl(\'object\', \'' + (value ? 'mark_as_unread' : 'mark_as_read') + '\', {ids:\'MailContents:' + r.data.object_id + '\'}));og.showWsPaths();r.commit();';
		return String.format(
				'<div title="{0}" class="db-ico {2}" onclick="{1}"></div>',
				value ? lang('mark as unread') : lang('mark as read'), js, value ? 'ico-read' : 'ico-unread'
		);
	}

	function renderAccount(value, p, r) {
		return String.format('<a href="#" onclick="og.eventManager.fireEvent(\'mail account select\',[\'{1}\', \'{0}\'])">{0}</a>', og.clean(value), r.data.accountId);
	}
	
	function renderTo(value, p, r) {
		var classes = "";
		var strAction = 'view';
		
		if (r.data.isDraft) strAction = 'edit_mail';
		if (!r.data.isRead) classes += ' bold';
		
		var receiver = value && og.clean(value.trim()) || '<i>' + lang("no recipient") + '</i>';

		name = String.format(
				'<a style="font-size:120%;" class="{3}" href="#" onclick="og.openLink(\'{1}\');og.showWsPaths();return false;" title="{2}">{0}</a>',
				receiver, og.getUrl('mail', strAction, {id: r.data.object_id}), og.clean(value), classes);
		return name;
	}
	
	function renderFolder(value, p, r) {
		if (r.data.folder != 'undefined')
			return r.data.folder;
		else
			return '';
	}
	
	function renderDate(value, p, r) {
		if (!value) {
			return "";
		}
		return value;
	}
	
	function renderActions(value, p, r) {
		var actions = '';
		var actionStyle= ' style="font-size:105%;padding-top:2px;padding-bottom:3px;padding-left:16px;background-repeat:no-repeat;" '; 
		
		actions += String.format(
			'<a class="list-action ico-reply" href="#" onclick="og.openLink(og.getUrl(\'mail\', \'reply_mail\', {id:{0}}))" title="{1}" ' + actionStyle + '>&nbsp;</a>',
			r.data.object_id, lang('reply mail'));

		actions += String.format(
			'<a class="list-action ico-reply-all" href="#" onclick="og.openLink(og.getUrl(\'mail\', \'reply_mail\', {id:{0}, all:1}))" title="{1}" ' + actionStyle + '>&nbsp;</a>',
			r.data.object_id, lang('reply to all mail'));

		actions += String.format(
			'<a class="list-action ico-forward" href="#" onclick="og.openLink(og.getUrl(\'mail\', \'forward_mail\', {id:{0}}))" title="{1}" ' + actionStyle + '>&nbsp;</a>',
			r.data.object_id, lang('forward mail'));
		
		actions += String.format(
			'<a class="list-action ico-delete" href="#" onclick="og.openLink(og.getUrl(\'mail\', \'delete\', {id:{0}}))" title="{1}" ' + actionStyle + '>&nbsp;</a>',
			r.data.object_id, lang('delete'));
		
		if (actions != '')
			actions = '<span>' + actions + '</span>';
			
		return actions;
	}

	function getSelectedIds() {
		var selections = sm.getSelections();
		if (selections.length <= 0) {
			return '';
		} else {
			var ret = '';
			for (var i=0; i < selections.length; i++) {
				ret += "," + selections[i].data.object_id;
			}
			og.lastSelectedRow.mails = selections[selections.length-1].data.ix;
			return ret.substring(1);
		}
	}
	this.getSelectedIds = getSelectedIds;
	
	function selectionHasAttachments() {
		var selections = sm.getSelections();
		if (selections.length <= 0) {
			return false;
		} else {
			for (var i=0; i < selections.length; i++) {
				if (selections[i].data.hasAttachment || selections[i].data.conv_hasatt) return true;
			}	
			return false;
		}
	}
	this.selectionHasAttachments = selectionHasAttachments;
	
	function getSelectedReadTypes() {
		var selections = sm.getSelections();
		if (selections.length <= 0) {
			return '';
		} else {
			var read = false;
			var unread = false;
			for (var i=0; i < selections.length; i++) {
				if (selections[i].data.isRead) read = true;
				if (!selections[i].data.isRead) unread = true;
				if (read && unread) return 'all';
			}	
			if (read) return 'read';
			else return 'unread';
		}
	}
	
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
				actions.del.setDisabled(true);
				actions.archive.setDisabled(true);
				markactions.markAsRead.setDisabled(true);				
				markactions.markAsUnread.setDisabled(true);
				markactions.markAsSpam.setDisabled(true);				
				markactions.markAsHam.setDisabled(true);
			} else {
				actions.tag.setDisabled(false);
				actions.del.setDisabled(false);
				actions.archive.setDisabled(false);
				
				var selTypes = getSelectedTypes();
				if (/message/.test(selTypes)){
					markactions.markAsRead.setDisabled(true);
					markactions.markAsUnread.setDisabled(true);
					markactions.markAsSpam.setDisabled(true);				
					markactions.markAsHam.setDisabled(true);
				}else {								
					markactions.markAsRead.setDisabled(false);
					markactions.markAsUnread.setDisabled(false);
					markactions.markAsSpam.setDisabled(false);				
					markactions.markAsHam.setDisabled(false);
					var selReadTypes = getSelectedReadTypes();
					
					if (selReadTypes == 'read') markactions.markAsRead.setDisabled(true);
					else if (selReadTypes == 'unread') markactions.markAsUnread.setDisabled(true);	
				}
				
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
        	fixed:true,
        	resizable: false,
        	hideable:false,
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
			id: 'hasAttachment',
			header: '&nbsp;',
			dataIndex: 'hasAttachment',
			width: 24,
        	renderer: renderAttachment,
        	fixed:true,
        	resizable: false,
        	hideable:false,
        	menuDisabled: true
		},{
			id: 'from',
			header: lang("from"),
			dataIndex: 'from',
			width: 120,
			renderer: renderFrom
        },{
			id: 'to',
			header: lang("to"),
			dataIndex: 'to',
			width: 200,
			hidden: true,
			sortable: false,
			renderer: renderTo
        },{
			id: 'subject',
			header: lang("subject"),
			dataIndex: 'subject',
			width: 250,
			renderer: renderName
        },{
			id: 'account',
			header: lang("account"),
			dataIndex: 'accountName',
			width: 60,
			renderer: renderAccount
        },{
			id: 'tags',
			header: lang("tags"),
			dataIndex: 'tags',
			sortable: false,
			hidden: true,
			width: 60
        },{
			id: 'date',
			header: lang("date"),
			dataIndex: 'date',
			width: 60,
			renderer: renderDate
        },{
			id: 'folder',
			header: lang("folder"),
			dataIndex: 'folderName',
			width: 60,
			sortable: true,
			hidden: true,
			renderer: renderFolder
        },{
			id: 'actions',
			header: lang("actions"),
			width: 60,
			renderer: renderActions,
			sortable: false
		}]);
	cm.defaultSortable = true;

	moreActions = {};
	
	filterReadUnread = {
		all: new Ext.Action({
			text: lang('view all'),
			handler: function() {
				this.reloadFiltering("all", null, null);
				Ext.getCmp('mails-manager').getTopToolbar().items.get('tb-item-read-unread').setText(lang('view by state'))
			},
			scope: this
		}),
		read: new Ext.Action({
			text: lang('read'),
			handler: function() {
				this.reloadFiltering("read", null, null);
				Ext.getCmp('mails-manager').getTopToolbar().items.get('tb-item-read-unread').setText(lang('read'))
			},
			scope: this
		}),
		unread: new Ext.Action({
			text: lang('unread'),
			handler: function() {
				this.reloadFiltering("unread", null, null);
				Ext.getCmp('mails-manager').getTopToolbar().items.get('tb-item-read-unread').setText(lang('unread'))
			},
			scope: this
		})
	};
	
	filterClassification = {
		all: new Ext.Action({
			text: lang('view all'),
			handler: function() {
				this.reloadFiltering(null, null, null, 'all');
				Ext.getCmp('mails-manager').getTopToolbar().items.get('tb-item-classification').setText(lang('view by classification'))
			},
			scope: this
		}),
		classified: new Ext.Action({
			text: lang('classified'),
			handler: function() {
				this.reloadFiltering(null, null, null, "classified");
				Ext.getCmp('mails-manager').getTopToolbar().items.get('tb-item-classification').setText(lang('classified'))
			},
			scope: this
		}),
		unclassified: new Ext.Action({
			text: lang('unclassified'),
			handler: function() {
				this.reloadFiltering(null, null, null, "unclassified");
				Ext.getCmp('mails-manager').getTopToolbar().items.get('tb-item-classification').setText(lang('unclassified'))
			},
			scope: this
		})
	};
	
	filterAccounts = {};
	
	markactions = {
		markAsRead: new Ext.Action({
			text: lang('mark read'),
            tooltip: lang('mark read'),
            iconCls: 'ico-mail-mark-read',
			disabled: true,
			handler: function() {
				var sm = this.getSelectionModel();
				var sel = sm.getSelections();
				var ids = "";
				for (var i=0; i < sel.length; i++) {
					if (ids) ids += ",";
					ids += "MailContents:" + sel[i].id;
					sel[i].set('isRead', true);
					sel[i].commit();
				}
				if (ids) og.openLink(og.getUrl('object', 'mark_as_read', {ids:ids}));
				sm.clearSelections();
			},
			scope: this
		}),
		markAsUnread: new Ext.Action({
			text: lang('mark unread'),
            tooltip: lang('mark unread'),
            iconCls: 'ico-mail-mark-unread',
			disabled: true,
			handler: function() {
				var sm = this.getSelectionModel();
				var sel = sm.getSelections();
				var ids = "";
				for (var i=0; i < sel.length; i++) {
					if (ids) ids += ",";
					ids += "MailContents:" + sel[i].id;
					sel[i].set('isRead', false);
					sel[i].commit();
				}
				if (ids) og.openLink(og.getUrl('object', 'mark_as_unread', {ids:ids}));
				sm.clearSelections();
			},
			scope: this
		}),
		markAsSpam: new Ext.Action({
			text: lang('mark spam'),
            tooltip: lang('mark spam'),
            iconCls: 'ico-mail-mark-spam',
			disabled: true,
			handler: function() {
				var sm = this.getSelectionModel();
				var sel = sm.getSelections();
				var ids = "";
				for (var i=0; i < sel.length; i++) {
					if (ids) ids += ",";
					ids += "MailContents:" + sel[i].id;
					this.store.remove(sel[i]);
				}
				if (ids) og.openLink(og.getUrl('mail', 'mark_as_spam', {ids:ids}));
				sm.clearSelections();
			},
			scope: this
		}),
		markAsHam: new Ext.Action({
			text: lang('mark ham'),
            tooltip: lang('mark ham'),
            iconCls: 'ico-mail-mark-ham',
			disabled: true,
			hidden: true,
			handler: function() {
				var sm = this.getSelectionModel();
				var sel = sm.getSelections();
				var ids = "";
				for (var i=0; i < sel.length; i++) {
					if (ids) ids += ",";
					ids += "MailContents:" + sel[i].id;
					this.store.remove(sel[i]);
				}
				if (ids) og.openLink(og.getUrl('mail', 'mark_as_ham', {ids:ids}));
				sm.clearSelections();
			},
			scope: this
		})
	};
		
	accountActions = {
		addAccount: new Ext.Action({
			text: lang('add mail account'),
			handler: function(e) {
				var url = og.getUrl('mail', 'add_account');
				og.openLink(url);
			}
		}),
		editAccount: new Ext.Action({
			text: lang('edit account'),
            tooltip: lang('edit email account'),
			disabled: false,
			menu: new og.EmailAccountMenu({
				listeners: {
					'accountselect': {
						fn: function(account) {
							var url = og.getUrl('mail', 'edit_account', {id: account});
							og.openLink(url);
						},
						scope: this
					}
				}
			},{},"edit")
		})
	};
	
	selectActions = {
		selectAll: new Ext.Action({
			text: lang('all'),
			handler: function(e) {
				sm.selectAll();
			}
		}),
		selectNone: new Ext.Action({
			text: lang('none'),
			handler: function(e) {
				sm.clearSelections();
			}
		}),
		selectRead: new Ext.Action({
			text: lang('read'),
			handler: function(e) {
				sm.selectAll();
				var selections = sm.getSelections();
				for (var i=0; i < selections.length; i++) {
					if (!selections[i].data.isRead) sm.deselectRow(i, false);
				}	
			}
		}),
		selectUnread: new Ext.Action({
			text: lang('unread'),
			handler: function(e) {
				sm.selectAll();
				var selections = sm.getSelections();
				for (var i=0; i < selections.length; i++) {
					if (selections[i].data.isRead) sm.deselectRow(i, false);
				}	
			}
		}),
		selectClassified: new Ext.Action({
			text: lang('classified'),
			handler: function(e) {
				sm.selectAll();
				var selections = sm.getSelections();
				for (var i=0; i < selections.length; i++) {
					if (!selections[i].data.projectId.length > 0) sm.deselectRow(i, false);
				}	
			}
		}),
		selectUnclassified: new Ext.Action({
			text: lang('unclassified'),
			handler: function(e) {
				sm.selectAll();
				var selections = sm.getSelections();
				for (var i=0; i < selections.length; i++) {
					if (selections[i].data.projectId.length > 0) sm.deselectRow(i, false);
				}
			}
		})
	};
	
	actions = {
		newCO: new Ext.Action({
			text: lang('new'),
            tooltip: lang('create an email'),
            iconCls: 'ico-new',
            handler: function() {
            	var url = og.getUrl('mail', 'add_mail');
            	og.openLink(url);
            }
		}),
		accounts: new Ext.Action({
			text: lang('accounts'),
            tooltip: lang('account options'),
            iconCls: 'ico-administration',
			disabled: false,
			menu: {items: [
				accountActions.addAccount,
				accountActions.editAccount
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
							var sm = this.getSelectionModel();
							var sel = sm.getSelections();
							var ids = "";
							for (var i=0; i < sel.length; i++) {
								if (ids) ids += ",";
								ids += "MailContents:" + sel[i].id;
								var oldtags = sel[i].get('tags');
								if ((", " + oldtags + ", ").indexOf(", " + tag + ", ") < 0) {
									if (oldtags) oldtags += ", ";
									sel[i].set('tags', oldtags + tag);
									sel[i].commit();
								}
							}
							if (ids) og.openLink(og.getUrl('object', 'tag', {ids:ids, tag:tag}));
						},
						scope: this
					},
					'tagdelete': {
						fn: function(tag, all) {
							var sm = this.getSelectionModel();
							var sel = sm.getSelections();
							var ids = "";
							for (var i=0; i < sel.length; i++) {
								if (ids) ids += ",";
								ids += "MailContents:" + sel[i].id;
								if (!all && Ext.getCmp('tag-panel').isSelected(tag) || all && Ext.getCmp('tag-panel').getSelectedTag()) {
									this.store.remove(sel[i]);
								} else if (all) {
									sel[i].set('tags', '');
									sel[i].commit();
								} else {
									var oldtags = ", " + sel[i].get('tags') + ", ";
									if (oldtags.indexOf(", " + tag + ", ") >= 0) {
										oldtags = oldtags.replace(", " + tag + ", ", ", ");
										oldtags = oldtags.replace(/^, *|, *$/g, '');
										sel[i].set('tags', oldtags);
										sel[i].commit();
									}
								}
							}
							if (ids) {
								if (all) {
									og.openLink(og.getUrl('object', 'untag', {ids:ids, all:1}));
								} else {
									og.openLink(og.getUrl('object', 'untag', {ids:ids, tag:tag}));
								}
							}
						},
						scope: this
					}
				}
			})
		}),
		del: new Ext.Action({
			text: lang('move to trash'),
            tooltip: lang('move selected objects to trash'),
            iconCls: 'ico-trash',
			disabled: true,
			handler: function() {
				if (confirm(lang('confirm move to trash'))) {
					var sm = this.getSelectionModel();
					var sel = sm.getSelections();
					var ids = "";
					for (var i=0; i < sel.length; i++) {
						if (ids) ids += ",";
						ids += "MailContents:" + sel[i].id;
					//	this.store.remove(sel[i]);
					}
					if (ids) og.openLink(og.getUrl('object', 'trash', {ids:ids}),{callback:function(){Ext.getCmp('mails-manager').load()}});
				}
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
					var sm = this.getSelectionModel();
					var sel = sm.getSelections();
					var ids = "";
					for (var i=0; i < sel.length; i++) {
						if (ids) ids += ",";
						ids += "MailContents:" + sel[i].id;
					//	this.store.remove(sel[i]);
					}
					if (ids) og.openLink(og.getUrl('object', 'archive', {ids:ids}),{callback:function(){Ext.getCmp('mails-manager').load()}});
				}
			},
			scope: this
		}),
		markAs: new Ext.Action({
			text: lang('mark as'),
			tooltip: lang('mark as desc'),
			menu: [
				markactions.markAsRead,
				markactions.markAsUnread,
				markactions.markAsSpam,
				markactions.markAsHam
			]
		}),
		checkMails: new Ext.Action({
			text: lang('check mails'),
			iconCls: 'ico-check_mails',
			handler: function() {
				this.load({
					action: "checkmail"
				});
			},
			scope: this
		}),
		sendOutbox: new Ext.Action({
			text: lang('send outbox'),
			tooltip: lang('send outbox title'),
			iconCls: 'ico-sent',
			handler: function() {
				og.msg(lang('success'), lang('sending outbox mails'));
				og.openLink(og.getUrl('mail', 'send_outbox_mails', {}), {hideLoading:1});
			},
			id: 'send_outbox_btn',
			hidden: true,
			scope: this
		}), 
		inbox_email: new Ext.Action({
	        text: lang('inbox'),
	        toggleGroup : 'filter_option',
	        enableToggle: true,
	        pressed: true,
	        id: 'inbox_btn',
	        handler: function(item, event) {
        		if(!item.pressed){
        			item.toggle(true,true);
        		}
			},
	        toggleHandler: function(item, pressed) {
       			if(pressed){
					this.store.removeAll();
       				this.stateType = "received";	
					this.viewType = "all";
					if (obox_btn = Ext.getCmp('send_outbox_btn')) obox_btn.hide();
					markactions.markAsSpam.show();
					markactions.markAsHam.hide();
        			cm.setHidden(cm.getIndexById('from'), false);
					cm.setHidden(cm.getIndexById('to'), true);
        			this.store.baseParams = {
					      read_type: this.readType,
					      view_type: this.viewType,
					      state_type : this.stateType,
					      classif_type: this.classifType,
					      tag: Ext.getCmp('tag-panel').getSelectedTag(),
						  active_project: Ext.getCmp('workspace-panel').getActiveWorkspace().id,
						  account_id: this.accountId
					    };
					this.load({start:0});						
       			}
			},			
			scope: this
    	}),
		sent_email: new Ext.Action({
	        text: lang('sent'),
	        toggleGroup : 'filter_option',
	        enableToggle: true,
	        pressed: false,
	        id: 'sent_btn',
	        handler: function(item, event) {
        		if(!item.pressed){
        			item.toggle(true,true);
        		}
			},
	        toggleHandler: function(item, pressed) {
        		if(pressed){
					this.store.removeAll();
					this.stateType = "sent";
					this.viewType = "all";
					if (obox_btn = Ext.getCmp('send_outbox_btn')) obox_btn.hide();
					markactions.markAsSpam.show();
					markactions.markAsHam.hide();
					cm.setHidden(cm.getIndexById('from'), true);
					cm.setHidden(cm.getIndexById('to'), false);
					this.store.baseParams = {
					      read_type: this.readType,
					      view_type: this.viewType,
					      state_type : this.stateType,
					      classif_type: this.classifType,
					      tag: Ext.getCmp('tag-panel').getSelectedTag(),
						  active_project: Ext.getCmp('workspace-panel').getActiveWorkspace().id,
						  account_id: this.accountId
					    };
					this.load({start:0});
        		}
			},
			scope: this
    	}),
    	
		draft_email: new Ext.Action({
	        text: lang('draft'),
	        toggleGroup : 'filter_option',
	        enableToggle: true,
	        pressed: false,
	        id: 'draft_btn',
	        handler: function(item, event) {
        		if(!item.pressed){
        			item.toggle(true,true);
        		}
			},
			toggleHandler: function(item, pressed) {
				if(pressed){
					this.store.removeAll();
					this.stateType = "draft";
					this.viewType = "all";
					if (obox_btn = Ext.getCmp('send_outbox_btn')) obox_btn.hide();
					markactions.markAsSpam.show();
					markactions.markAsHam.hide();
        			cm.setHidden(cm.getIndexById('from'), true);
					cm.setHidden(cm.getIndexById('to'), false);
        			this.store.baseParams = {
					      read_type: this.readType,
					      view_type: this.viewType,
					      state_type : this.stateType,
					      classif_type: this.classifType,
					      tag: Ext.getCmp('tag-panel').getSelectedTag(),
						  active_project: Ext.getCmp('workspace-panel').getActiveWorkspace().id,
						  account_id: this.accountId
					    };
					this.load({start:0});
        		} 
			},
			scope: this
    	}),
	
		junk_email: new Ext.Action({
	        text: lang('junk'),
	        toggleGroup : 'filter_option',
	        enableToggle: true,
	        pressed: false,
	        id: 'junk_btn',
	        handler: function(item, event) {
        		if(!item.pressed){
        			item.toggle(true,true);
        		}
			},
			toggleHandler: function(item, pressed) {
				if(pressed){
					this.store.removeAll();
					this.stateType = "junk";
					this.viewType = "all";
					if (obox_btn = Ext.getCmp('send_outbox_btn')) obox_btn.hide();
					markactions.markAsSpam.hide();
					markactions.markAsHam.show();
        			cm.setHidden(cm.getIndexById('from'), false);
					cm.setHidden(cm.getIndexById('to'), true);
        			this.store.baseParams = {
					      read_type: this.readType,
					      view_type: this.viewType,
					      state_type : this.stateType,
					      classif_type: this.classifType,
					      tag: Ext.getCmp('tag-panel').getSelectedTag(),
						  active_project: Ext.getCmp('workspace-panel').getActiveWorkspace().id,
						  account_id: this.accountId
					    };
					this.load({start:0});
        		} 
			},
			scope: this
    	}),
	
		out_email: new Ext.Action({
	        text: lang('outbox'),
	        toggleGroup : 'filter_option',
	        enableToggle: true,
	        pressed: false,
	        id: 'outbox_btn',
	        handler: function(item, event) {
        		if(!item.pressed){
        			item.toggle(true,true);
        		}
			},
			toggleHandler: function(item, pressed) {
				if(pressed){
					this.store.removeAll();
					this.stateType = "outbox";
					this.viewType = "all";
					if (obox_btn = Ext.getCmp('send_outbox_btn')) obox_btn.show();
					markactions.markAsSpam.show();
					markactions.markAsHam.hide();
        			cm.setHidden(cm.getIndexById('from'), true);
					cm.setHidden(cm.getIndexById('to'), false);
        			this.store.baseParams = {
					      read_type: this.readType,
					      view_type: this.viewType,
					      state_type : this.stateType,
					      classif_type: this.classifType,
					      tag: Ext.getCmp('tag-panel').getSelectedTag(),
						  active_project: Ext.getCmp('workspace-panel').getActiveWorkspace().id,
						  account_id: this.accountId
					    };
					this.load({start:0});
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
		viewReadUnread: new Ext.Action({
			text: this.readType == 'read' ? lang('read') : (this.readType == 'unread' ? lang('unread') : lang('view by state')),
            iconCls: 'ico-mail-mark-read',
			disabled: false,
			id: 'tb-item-read-unread',
			menu: {items: [
				filterReadUnread.all,
				'-',
				filterReadUnread.read,
				filterReadUnread.unread
			]}
		}),
		viewByAccount: new Ext.Action({
			text: this.accountId == 0 ? lang('view by account') : og.emailFilters.accountName,
            iconCls: 'ico-account',
			disabled: false,
			id: 'tb-item-byaccount',
			menu: new og.EmailAccountMenu({
				listeners: {
					'accountselect': {
						fn: function(account, name) {
							this.accountId = account;
							this.load();
							if (account == 0) {
								name = lang('view by account');
							}
							Ext.getCmp('mails-manager').getTopToolbar().items.get('tb-item-byaccount').setText(name);
						},
						scope: this
					}
				}
			},[{name: lang('view all'), email:'', id: '', separator:true}],"view")
		}),
		viewByClassification: new Ext.Action({
			text: this.classifType == 'classified' ? lang('classified') : (this.classifType == 'unclassified' ? lang('unclassified') : lang('view by classification')),
            iconCls: 'ico-classify',
			disabled: false,
			id: 'tb-item-classification',
			menu: {items: [
				filterClassification.all,
				'-',
				filterClassification.classified,
				filterClassification.unclassified
			]}			
		}),
		select: new Ext.Action({
			text: lang('select'),
            tooltip: lang('select'),
            iconCls: 'ico-select',
			disabled: false,
			menu: {items: [
				selectActions.selectAll,
				selectActions.selectNone,
				'-',
				selectActions.selectRead,
				selectActions.selectUnread,
				'-',
				selectActions.selectClassified,
				selectActions.selectUnclassified
			]}
		})
    };
	
	var mas = og.eventManager.addListener("mail account select", function(account) {
		this.accountId = account[0];
		this.load();
		Ext.getCmp('mails-manager').getTopToolbar().items.get('tb-item-byaccount').setText(account[1]);
	}, this);
	
	this.actionRep = actions;

	var top1 = [];
	if (!og.loggedUser.isGuest) {
		top1.push(actions.newCO);
		top1.push('-');
		top1.push(actions.tag);
		top1.push(actions.archive);
		top1.push(actions.del);		
		top1.push('-');
	}
	top1.push(actions.markAs);
	if (!og.loggedUser.isGuest) {
		top1.push('-');
		top1.push(actions.checkMails);
		top1.push(actions.accounts);
		top1.push(actions.sendOutbox);
	}
	
	this.topTbar1 = new Ext.Toolbar({
		style: 'border:0px none;',
		items: top1
	});
	
	this.topTbar2 = new Ext.Toolbar({
		style: 'border:0px none',
		items: [
			actions.select,
			'-',
			actions.inbox_email,
			actions.sent_email,
			actions.draft_email,
			actions.junk_email,
			actions.out_email,
			'-',
			lang('filter')+': ',
			actions.viewReadUnread,
			actions.viewByClassification,
			actions.viewByAccount
		]
	});
		    
	og.MailManager.superclass.constructor.call(this, {
		store: this.store,
		layout: 'fit',
		cm: cm,
		enableDrag: true,
		ddGroup: 'WorkspaceDD',
		stateful: og.preferences['rememberGUIState'],
		border: false,
		bodyBorder: false,
		stripeRows: true,
		closable: true,
		loadMask: false,
		id: 'mails-manager',
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
		tbar: this.topTbar2,
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
	og.eventManager.addListener('reload mails panel', this.load, this);
	
	function toggleButtons(inb, sent, dra) {
		Ext.getCmp('inbox_btn').toggle(inb);
		Ext.getCmp('sent_btn').toggle(sent);
		Ext.getCmp('draft_btn').toggle(dra);
	}
	
	var tagevid = og.eventManager.addListener("tag changed", function(tag) {
		if (!this.ownerCt) {
			og.eventManager.removeListener(tagevid);
			return;
		}
		if (this.ownerCt.ownerCt.active) {// ownerCt is MailManagerPanel, must ask his ownerCt to see if tab is active
			this.needRefresh = false;
			this.load({start:0});
		} else {
    		this.needRefresh = true;
    	}
	}, this);
	
	// Send emails in background
	var send_ev = og.eventManager.addListener("must send mails", function(data) {
		og.openLink(og.getUrl('mail', 'send_outbox_mails', {acc_id: data.account}), {hideLoading:1});
	}, this);
	
	var new_mailsent_ev = og.eventManager.addListener("mail sent", function(data) {
		if (this.stateType == "outbox") {
			var view = Ext.getCmp('mails-manager').getView();
	        var sto = og.MailManager.store;
	        var idx = sto.indexOfId(data.id);
	        if (idx == -1) return;
	        var sto_row = sto.getAt(idx);
	        if (sto_row) {
	        	sto.remove(sto_row);
	        }
		}
		og.msg(lang('success'), lang('mail sent msg'), 2);
	}, this);
	
	var mailssent_ev = og.eventManager.addListener("mails sent", function(data) {
		this.load();
	}, this);
	
	// auto refresh emails
	var me = this;
	this.emailRefreshInterval = setInterval(function() {
		if (og.fengHasBrowserFocus) og.skipEmailPolling = 0;
		else {
			if (og.skipEmailPolling > 0) og.skipEmailPolling--;
			else (og.skipEmailPolling = 5);
		}		
		if (og.fengHasBrowserFocus || og.skipEmailPolling == 0) {
			var p = me.getBottomToolbar().getPageData().activePage;
			if (Ext.getCmp('tabs-panel').getActiveTab().id == 'mails-panel' && p == 1) {
				me.needRefresh = false;
				og.MailManager.store.load();
			} else {
				me.needRefresh = true;
			}
		}
	}, 60000);
	/*poll to see if an error has happened while checking mail*/
	if (og.preferences.email_check_acc_errors > 0) {
		this.accountErrCheckInterval = setInterval(function() {
			if (Ext.getCmp('tabs-panel').getActiveTab().id == 'mails-panel') {
				og.openLink(og.getUrl('mail', 'check_account_errors'), {hideLoading:true});
			}
		}, og.preferences.email_check_acc_errors * 1000);
	}
};


Ext.extend(og.MailManager, Ext.grid.GridPanel, {
	load: function(params) {
		if (!params) params = {};
		var start;
		if (typeof params.start == 'undefined') {
			start = (this.getBottomToolbar().getPageData().activePage - 1) * og.config['files_per_page'];
		} else {
			start = 0;
		}
		this.store.baseParams = {
	      read_type: this.readType,
	      view_type: this.viewType,
	      state_type : this.stateType,
	      classif_type: this.classifType,
	      tag: Ext.getCmp('tag-panel').getSelectedTag(),
		  active_project: Ext.getCmp('workspace-panel').getActiveWorkspace().id,
		  account_id: this.accountId
	    };
		this.store.load({
			params: Ext.apply(params, {
				start: start,
				limit: og.config['files_per_page']				
			})
		});
		this.store.baseParams.action = "";
	},
	
	activate: function() {
		if (this.needRefresh) {
			this.load({start:0});
		}
	},
	
	reset: function() {
		this.load({start:0});
	},
	
	showMessage: function(text) {
		this.innerMessage.innerHTML = text;
	},
	
	moveObjectsToAllWs: function() {
		this.load({
			action: 'unclassify',
			ids: this.getSelectedIds(),
			types: this.getSelectedTypes()
		});
	},
	
	moveObjects: function(ws) {
		var selections = this.getSelectionModel().getSelections();
		var unclassified = true;
		for (var i=0; i < selections.length; i++) {
			if (selections[i].data.projectId.length > 0) {
				unclassified = false;
				break;
			}
		}
		if (unclassified) {
			// if all emails are unclassified don't prompt for keeping workspaces
			this.moveObjectsToWsOrMantainWs(1, ws);
		} else {
			// prompt
			og.moveToWsOrMantainWs(this.id, ws);
		}
	},
	
	moveObjectsToWsOrMantainWs: function(mantain, ws) {
		if (mantain && !ws) return; // dragging to "All" and keeping workspaces
		if (this.selectionHasAttachments() && ws) {
			og.askToClassifyUnclassifiedAttachs('mails-manager', mantain, ws);
		} else {
			this.moveObjectsClassifyingEmails(mantain, ws, 0);
		}
	},
	
	moveObjectsClassifyingEmails: function(mantain, ws, classifyatts) {
		var sm = this.getSelectionModel();
		var sel = sm.getSelections();
		var ids = "";
		for (var i=0; i < sel.length; i++) {
			if (ids) ids += ",";
			ids += "MailContents:" + sel[i].id;
			if (mantain) {
				var pids = (', ' + sel[i].get('projectId') + ', ');
				pids = pids.replace(', ' + ws + ', ', ', ').substring(2, pids.length - 2);
				if (pids) pids += ', ';
			} else {
				var pids = "";
			}
			if (ws) pids += ws;
			var wp = Ext.getCmp('workspace-panel');
			var active = wp.getActiveWorkspace().id;
			if (!mantain && active != ws && !wp.isSubWorkspace(ws, active)) {
				// if the email was moved and the new workspace is not a subworkspace of the selected workspace => remove the email
				this.store.remove(sel[i]);
			} else {
				sel[i].set('projectId', pids);
				//sel[i].commit();
			}
		}
		og.showWsPaths();
		if (ids) {
			if (!ws) {
				// unclassify
				og.openLink(og.getUrl('mail', 'unclassify_many', {ids:ids}));
			} else {
				og.openLink(og.getUrl('object', 'move', {ids:ids, ws:ws, keep: (mantain ? '1' : '0'), atts:(classifyatts ? '1' : '0')}));
			}
		}
		sm.clearSelections();
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

	tagObjects: function(tag) {
		this.load({
			action: 'tag',
			ids: this.getSelectedIds(),
			types: this.getSelectedTypes(),
			tagTag: tag
		});
	},

	removeTags: function() {
		this.load({
			action: 'untag',
			ids: this.getSelectedIds(),
			types: this.getSelectedTypes()
		});
	},
	
	getFirstToolbar: function() {
		return this.topTbar1;
	},
	
	reloadFiltering: function(readType, viewType, stateType, classifType) {
		if (readType) this.readType = readType;
		if (viewType) this.viewType = viewType;
		if (stateType) this.stateType = stateType;
		if (classifType) this.classifType = classifType;
		
		this.store.baseParams = {
			read_type: this.readType,
			view_type: this.viewType,
			state_type : this.stateType,
			classif_type : this.classifType,
			tag: Ext.getCmp('tag-panel').getSelectedTag(),
			active_project: Ext.getCmp('workspace-panel').getActiveWorkspace().id
		};
		this.load();
	}
});

Ext.reg("mails", og.MailManager);


/************************************************
Container for MailManager, adds a new toolbar
*************************************************/
og.MailManagerPanel = function() {
	this.doNotRemove = true;
	this.needRefresh = false;
	
	this.manager = new og.MailManager();

	og.MailManagerPanel.superclass.constructor.call(this, {
		layout: 'fit',
		border: false,
		bodyBorder: false,
		tbar: this.manager.getFirstToolbar(),
		items: [this.manager],
		closable: true
	});
}

Ext.extend(og.MailManagerPanel, Ext.Panel, {
	load: function(params) {
		this.manager.load(params);
	},
	activate: function() {
		this.manager.activate();
	},	
	reset: function() {
		this.manager.reset();
	},	
	showMessage: function(text) {
		this.manager.showMessage(text);
	}
});

Ext.reg("mails-containerpanel", og.MailManagerPanel);