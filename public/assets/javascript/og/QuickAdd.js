/**
 *  QuickAdd
 *
 */
og.QuickAdd = function(config) {
	og.QuickAdd.superclass.constructor.call(this, Ext.applyIf(config || {}, {
		text: lang('new'),
        tooltip: lang('create an object'),
        iconCls: 'ico-quick-add',
		menu: {items: [
			{id: 'quick-contact', text: lang('contact'), iconCls: 'ico-contact', handler: function() {
				var url = og.getUrl('contact', 'add');
				og.openLink(url/*, {caller: 'contacts-panel'}*/);
			}, hidden: !og.config['enable_contacts_module']},
			{id: 'quick-company', text: lang('company'), iconCls: 'ico-company', handler: function() {
				var url = og.getUrl('company', 'add_client');
				og.openLink(url/*, {caller: 'contacts-panel'}*/);
			}, hidden: !og.config['enable_contacts_module']},
			{id: 'quick-event', text: lang('event'), iconCls: 'ico-event', handler: function() {
				var url = og.getUrl('event', 'add');
				og.openLink(url/*, {caller: 'calendar-panel'}*/);
			}, hidden: !og.config['enable_calendar_module']},
			{id: 'quick-task', text: lang('task'), iconCls: 'ico-task', handler: function() {
				var url = og.getUrl('task', 'add_task');
				og.openLink(url/*, {caller: 'tasks-panel'}*/);
			}, hidden: !og.config['enable_tasks_module']},
			{id: 'quick-milestone', text: lang('milestone'), iconCls: 'ico-milestone', handler: function() {
				var url = og.getUrl('milestone', 'add');
				og.openLink(url/*, {caller: 'tasks-panel'}*/);
			}, hidden: !og.config['enable_tasks_module']},
			{id: 'quick-weblink', text: lang('webpage'), iconCls: 'ico-webpage', handler: function() {
				var url = og.getUrl('webpage', 'add');
				og.openLink(url/*, {caller: 'webpages-panel'}*/);
			}, hidden: !og.config['enable_weblinks_module']},
			{id: 'quick-note', text: lang('message'), iconCls: 'ico-message', handler: function() {
				var url = og.getUrl('message', 'add');
				og.openLink(url/*, {caller: 'messages-panel'}*/);
			}, hidden: !og.config['enable_notes_module']},
			{id: 'quick-document', text: lang('document'), iconCls: 'ico-doc', handler: function() {
				var url = og.getUrl('files', 'add_document');
				og.openLink(url/*, {caller: 'documents-panel'}*/);
			}, hidden: !og.config['enable_documents_module']},
//			{id: 'quick-spreadsheet', text: lang('spreadsheet'), iconCls: 'ico-sprd', handler: function() {
//				var url = og.getUrl('files', 'add_spreadsheet');
//				og.openLink(url/*, {caller: 'documents-panel'}*/);
//			}, hidden: !og.config['enable_documents_module']},
			{id: 'quick-presentation', text: lang('presentation'), iconCls: 'ico-prsn', handler: function() {
				var url = og.getUrl('files', 'add_presentation');
				og.openLink(url/*, {caller: 'documents-panel'}*/);
			}, hidden: !og.config['enable_documents_module']},
			{id: 'quick-file', text: lang('upload file'), iconCls: 'ico-upload', handler: function() {
				var url = og.getUrl('files', 'add_file');
				og.openLink(url/*, {caller: 'documents-panel'}*/);
			}, hidden: !og.config['enable_documents_module']},
			{id: 'quick-email', text: lang('email'), iconCls: 'ico-email', handler: function() {
				var url = og.getUrl('mail', 'add_mail');
				og.openLink(url/*, {caller: 'mails-panel'}*/);
			}, hidden: !og.config['enable_email_module']}
		]}
	}));
	
	// ENABLE / DISABLE MODULES	
	var module_buttons = {
		'notes': ['quick-note'],
		'email': ['quick-email'],
		'contacts': ['quick-contact', 'quick-company'],
		'calendar': ['quick-event'],
		'documents': ['quick-document', 'quick-presentation', 'quick-file'/*,  'quick-spreadsheet'*/],
		'tasks': ['quick-task', 'quick-milestone'],
		'weblinks': ['quick-weblink'],
		'time': [],
		'reporting': ['report']
	};
	og.eventManager.addListener('config option changed', function(option) {
		if (option.name.substring(0, 7) == 'enable_' && option.name.substring(option.name.length - 7) == '_module') {
			var module = option.name.substring(7, option.name.length - 7);
			var buttons = module_buttons[module] || [];
			for (var i=0; i < buttons.length; i++) {
				var btn = this.menu.items.get(buttons[i]);
				if (btn) btn.setVisible(option.value);
			}
		}
	}, this);
};

Ext.extend(og.QuickAdd, Ext.Button, {});