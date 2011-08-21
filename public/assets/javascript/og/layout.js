Ext.onReady(function(){
	Ext.get("loading").hide();
		
	// fix cursor not showing on message boxs
	Ext.MessageBox.getDialog().on("show", function(d) {
		var div = Ext.get(d.el);
		div.setStyle("overflow", "auto");
		var text = div.select(".ext-mb-textarea", true);
		if (!text.item(0))
			text = div.select(".ext-mb-text", true);
		if (text.item(0))
			text.item(0).dom.select();
	});

	if (og.preferences['rememberGUIState']) {
		Ext.state.Manager.setProvider(new og.HttpProvider({
			saveUrl: og.getUrl('gui', 'save_state'),
			readUrl: og.getUrl('gui', 'read_state'),
			autoRead: false
		}));
		Ext.state.Manager.getProvider().initState(og.initialGUIState);
	}
	
	Ext.QuickTips.init();

	// SETUP PANEL LAYOUT
	og.panels = {};
	var panels = [
		og.panels.overview = new og.ContentPanel({
			title: langhtml('overview'),
			id: 'overview-panel',
			iconCls: 'ico-overview',
			refreshOnWorkspaceChange: true,
			refreshOnTagChange: true,
			defaultContent: {
				type: "url",
				data: og.getUrl('dashboard','index')
			},
			initialContent: {
				type: "url",
				data: og.initialURL
			}
		}),
		og.panels.notes = new og.ContentPanel({
			title: lang('messages'),
			id: 'messages-panel',
			iconCls: 'ico-messages-layout',
			refreshOnWorkspaceChange: true,
			defaultContent: {
				type: 'url',
				data: og.getUrl('message', 'init')
			}
		}),
		og.panels.email = new og.ContentPanel({
			title: lang('email tab'),
			id: 'mails-panel',
			iconCls: 'ico-emails-layout',
			refreshOnWorkspaceChange: true,
			defaultContent: {
				type: 'url',
				data: og.getUrl('mail', 'init')
			}
		}),
		og.panels.contacts = new og.ContentPanel({
			title: lang('contacts'),
			id: 'contacts-panel',
			iconCls: 'ico-contacts-layout',
			refreshOnWorkspaceChange: true,
			defaultContent: {
				type: 'url',
				data: og.getUrl('contact', 'init')
			}
		}),
		og.panels.calendar = new og.ContentPanel({
			title: lang('calendar'),
			id: 'calendar-panel',
			iconCls: 'ico-calendar-layout',
			refreshOnWorkspaceChange: true,
			refreshOnTagChange: true,
			defaultContent: {
				type: 'url',
				data: og.getUrl('event', 'view_calendar')
			}
		}),
		og.panels.documents = new og.ContentPanel({
			title: lang('documents'),
			id: 'documents-panel',
			iconCls: 'ico-documents-layout',
			refreshOnWorkspaceChange: true,
			defaultContent: {
				type: 'url',
				data: og.getUrl('files', 'init')
			}
		}),
		og.panels.tasks = new og.ContentPanel({
			title: lang('tasks'),
			id: 'tasks-panel',
			iconCls: 'ico-tasks-layout',
			refreshOnWorkspaceChange: true,
			defaultContent: {
				type: "url",
				data: og.getUrl('task','new_list_tasks')
			}
		}),
		og.panels.weblinks = new og.ContentPanel({
			title: lang('web pages'),
			id: 'webpages-panel',
			iconCls: 'ico-webpages-layout',
			refreshOnWorkspaceChange: true,
			defaultContent: {
				type: 'url',
				data: og.getUrl('webpage', 'init')
			}
		}),
		og.panels.time = new og.ContentPanel({
			title: lang('time'),
			id: 'time-panel',
			iconCls: 'ico-time-layout',
			refreshOnWorkspaceChange: true,
			defaultContent: {
				type: "url",
				data: og.getUrl('time','index')
			}
		}),
		og.panels.reporting = new og.ContentPanel({
			title: lang('reporting'),
			id: 'reporting-panel',
			iconCls: 'ico-reporting-layout',
			//refreshOnWorkspaceChange: true,
			defaultContent: {
				type: "url",
				data: og.getUrl('reporting','index')
			}
		})
	];
	var tab_panel = new Ext.TabPanel({
		id: 'tabs-panel',
		region:'center',
		activeTab: 0,
		enableTabScroll: true,
		items: panels,
		listeners: {
			'render': function() {
				// hide disabled modules
				if (!og.config['enable_notes_module']) if (panel = Ext.get('tabs-panel__messages-panel')) panel.setDisplayed(false);
				if (!og.config['enable_email_module']) if (panel = Ext.get('tabs-panel__mails-panel')) panel.setDisplayed(false);
				if (!og.config['enable_contacts_module']) if (panel = Ext.get('tabs-panel__contacts-panel')) panel.setDisplayed(false);
				if (!og.config['enable_calendar_module']) if (panel = Ext.get('tabs-panel__calendar-panel')) panel.setDisplayed(false);
				if (!og.config['enable_documents_module']) if (panel = Ext.get('tabs-panel__documents-panel')) panel.setDisplayed(false);
				if (!og.config['enable_tasks_module']) if (panel = Ext.get('tabs-panel__tasks-panel')) panel.setDisplayed(false);
				if (!og.config['enable_weblinks_module']) if (panel = Ext.get('tabs-panel__webpages-panel')) panel.setDisplayed(false);
				if (!og.config['enable_time_module']) if (panel = Ext.get('tabs-panel__time-panel')) panel.setDisplayed(false);
				if (!og.config['enable_reporting_module']) if (panel = Ext.get('tabs-panel__reporting-panel')) panel.setDisplayed(false);
			}
		}
	});

	// ENABLE / DISABLE MODULES
	var module_tabs = {
		'notes': ['tabs-panel__messages-panel'],
		'email': ['tabs-panel__mails-panel'],
		'contacts': ['tabs-panel__contacts-panel'],
		'calendar': ['tabs-panel__calendar-panel'],
		'documents': ['tabs-panel__documents-panel'],
		'tasks': ['tabs-panel__tasks-panel'],
		'weblinks': ['tabs-panel__webpages-panel'],
		'time': ['tabs-panel__time-panel'],
		'reporting': ['tabs-panel__reporting-panel']
	};
	og.eventManager.addListener('config option changed', function(option) {
		if (option.name.substring(0, 7) == 'enable_' && option.name.substring(option.name.length - 7) == '_module') {
			var module = option.name.substring(7, option.name.length - 7);
			var tabs = module_tabs[module] || [];
			for (var i=0; i < tabs.length; i++) {
				Ext.get(tabs[i]).setDisplayed(option.value);
			}
		}
	});
	
	// BUILD VIEWPORT
	var viewport = new Ext.Viewport({
		layout: 'border',
		stateful: og.preferences['rememberGUIState'],
		items: [
			new Ext.BoxComponent({
				region: 'north',
				el: 'header'
			}),
			new Ext.BoxComponent({
				region: 'south',
				el: 'footer'
			}),
			/*helpPanel = new og.HelpPanel({
				region: 'east',
				collapsible: true,
				collapsed: true,
				split: true,
				width: 225,
				minSize: 175,
				maxSize: 400,
				id: 'help-panel',
				title: lang('help'),
				iconCls: 'ico-help'
			 }),*/
			 {
				region: 'west',
				id: 'menu-panel',
				title: lang('workspaces'),
				iconCls: 'ico-workspaces',
				split: true,
				width: 200,
				bodyBorder: false,
				minSize: 175,
				maxSize: 400,
				collapsible: true,
				margins: '0 0 0 0',
				layout: 'border',
				stateful: og.preferences['rememberGUIState'],
				items: [{
					id: 'workspaces-tree',
					xtype: 'wspanel',
					wstree: {
						listeners: {
							workspaceselect: function(ws) {
								og.eventManager.fireEvent('workspace changed', ws);
								og.updateWsCrumbs(ws);
							}
						},
						autoLoadWorkspaces: true
					},
					listeners: {
						render: function() {
							this.getTopToolbar().setHeight(25);
						}
					}
				},{
					xtype: 'tagpanel',
					tagtree: {
						listeners: {
							tagselect: function(tag) {
								og.eventManager.fireEvent('tag changed', tag);
								og.updateWsCrumbsTag(tag);
							}
						},
						autoLoadTags: true
					}
				}]
			},
			Ext.getCmp('tabs-panel')
		 ]
	});
	
    og.captureLinks();

    if (og.preferences['email_polling'] > 0) {
    	function updateUnreadCount() {
    		og.openLink(og.getUrl('mail', 'get_unread_count'), {
    			onSuccess: function(d) {
    				if (typeof d.unreadCount != 'undefined') {
    					og.updateUnreadEmail(d.unreadCount);
    				}
    			},
    			hideLoading: true,
    			hideErrors: true,
    			preventPanelLoad: true
    		});
    	}
    	updateUnreadCount();
    	setInterval(updateUnreadCount, Math.max(og.preferences['email_polling'], 5000));
    }
    
    if (og.hasNewVersions) {
    	og.msg(lang('new version notification title'), og.hasNewVersions, 0);
    }
});