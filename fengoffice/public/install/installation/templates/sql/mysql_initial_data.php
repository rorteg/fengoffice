INSERT INTO `<?php echo $table_prefix ?>administration_tools` (`name`, `controller`, `action`, `order`) VALUES
	('test_mail_settings', 'administration', 'tool_test_email', 1);

INSERT INTO `<?php echo $table_prefix ?>config_categories` (`name`, `is_system`, `category_order`) VALUES
	('system', 1, 0),
	('general', 0, 1),
	('mailing', 0, 2),
	('passwords', 0, 4);


INSERT INTO `<?php echo $table_prefix ?>config_options` (`category_name`, `name`, `value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
	('system', 'project_logs_per_page', '10', 'IntegerConfigHandler', 1, 0, NULL),
	('system', 'messages_per_page', '5', 'IntegerConfigHandler', 1, 0, NULL),
	('system', 'max_avatar_width', '50', 'IntegerConfigHandler', 1, 0, NULL),
	('system', 'max_avatar_height', '50', 'IntegerConfigHandler', 1, 0, NULL),
	('system', 'logs_per_project', '5', 'IntegerConfigHandler', 1, 0, NULL),
	('system', 'max_logo_width', '50', 'IntegerConfigHandler', 1, 0, NULL),
	('system', 'max_logo_height', '50', 'IntegerConfigHandler', 1, 0, NULL),
	('system', 'files_per_page', '50', 'IntegerConfigHandler', 1, 0, NULL),
	('system', 'notification_from_address', '', 'StringConfigHandler', 1, 0, 'Address to use as from field in email notifications. If empty, users address is used'),
	('system', 'min_chars_for_match', '3', 'IntegerConfigHandler', 1, 0, 'If search criteria len is less than this, then use always LIKE'),
	('general', 'upgrade_last_check_datetime', '2006-09-02 13:46:47', 'DateTimeConfigHandler', 1, 0, 'Date and time of the last upgrade check'),
	('general', 'upgrade_last_check_new_version', '0', 'BoolConfigHandler', 1, 0, 'True if system checked for the new version and found it. This value is used to hightligh upgrade tab in the administration'),
	('general', 'file_storage_adapter', 'fs', 'FileStorageConfigHandler', 0, 0, 'What storage adapter should be used? fs or mysql'),
	('general', 'theme', 'default', 'ThemeConfigHandler', 0, 0, NULL),
	('general', 'days_on_trash', '30', 'IntegerConfigHandler', 0, 0, 'Days before a file is deleted from trash. 0 = Not deleted'),
	('mailing', 'exchange_compatible', '0', 'BoolConfigHandler', 0, 0, NULL),
	('mailing', 'mail_transport', 'mail()', 'MailTransportConfigHandler', 0, 0, 'Values: ''mail()'' - try to emulate mail() function, ''smtp'' - use SMTP connection'),
	('mailing', 'smtp_server', '', 'StringConfigHandler', 0, 0, ''),
	('mailing', 'smtp_port', '25', 'IntegerConfigHandler', 0, 0, NULL),
	('mailing', 'smtp_address', '', 'StringConfigHandler', 0, 0, ''),
	('mailing', 'smtp_authenticate', '0', 'BoolConfigHandler', 0, 0, 'Use SMTP authentication'),
	('mailing', 'smtp_username', '', 'StringConfigHandler', 0, 0, NULL),
	('mailing', 'smtp_password', '', 'PasswordConfigHandler', 0, 0, NULL),
	('mailing', 'smtp_secure_connection', 'no', 'SecureSmtpConnectionConfigHandler', 0, 0, 'Values: no, ssl, tls'),
	('mailing', 'show images in document notifications', '0', 'BoolConfigHandler', 0, 0, NULL),
	('passwords', 'min_password_length', '0', 'IntegerConfigHandler', 0, '1', NULL),
	('passwords', 'password_numbers', '0', 'IntegerConfigHandler', 0, '2', NULL),
	('passwords', 'password_uppercase_characters', '0', 'IntegerConfigHandler', 0, '3', NULL),
	('passwords', 'password_metacharacters', '0', 'IntegerConfigHandler', 0, '4', NULL),
	('passwords', 'password_expiration', '0', 'IntegerConfigHandler', 0, '5', NULL),
	('passwords', 'password_expiration_notification', '0', 'IntegerConfigHandler', 0, '6', NULL),
	('passwords', 'account_block', '0', 'BoolConfigHandler', 0, '7', NULL),
	('passwords', 'new_password_char_difference', '0', 'BoolConfigHandler', '0', '8', NULL),
	('passwords', 'validate_password_history', '0', 'BoolConfigHandler', '0', '9', NULL),
	('passwords', 'block_login_after_x_tries', '0', 'BoolConfigHandler', '0', '20', NULL),
	('general', 'checkout_notification_dialog', '0', 'BoolConfigHandler', '0', '0', NULL),
	('general', 'file_revision_comments_required', '0', 'BoolConfigHandler', '0', '0', NULL),
	('general', 'currency_code', '$', 'StringConfigHandler', '0', '0', NULL),
	('general', 'checkout_for_editing_online', '0', 'BoolConfigHandler', '0', '0', NULL),
	('general', 'show_feed_links', '0', 'BoolConfigHandler', '0', '0', NULL),
	('general', 'use_owner_company_logo_at_header', '1', 'BoolConfigHandler', '0', '0', NULL),
	('general', 'ask_administration_autentification', 0, 'BoolConfigHandler', 0, 0, NULL),
	('general', 'use tasks dependencies', 0, 'BoolConfigHandler', 0, 0, NULL),
        ('general', 'untitled_notes', '0', 'BoolConfigHandler', '0', '0', NULL),
        ('general', 'repeating_task', '0', 'BoolConfigHandler', '0', '0', NULL),
        ('general', 'working_days', '1,2,3,4,5', 'StringConfigHandler', '0', '0', NULL),
        ('general', 'wysiwyg_tasks', '0', 'BoolConfigHandler', '0', '0', NULL), 
        ('general', 'wysiwyg_messages', '0', 'BoolConfigHandler', '0', '0', NULL),
        ('general', 'wysiwyg_projects', '0', 'BoolConfigHandler', '0', '0', NULL),
        ('task panel', 'tasksShowTimeEstimates', '1', 'BoolConfigHandler', '1', '0', NULL);
	
INSERT INTO `<?php echo $table_prefix ?>file_types` (`extension`, `icon`, `is_searchable`, `is_image`) VALUES
	('zip', 'archive.png', 0, 0),
	('rar', 'archive.png', 0, 0),
	('bz', 'archive.png', 0, 0),
	('bz2', 'archive.png', 0, 0),
	('gz', 'archive.png', 0, 0),
	('ace', 'archive.png', 0, 0),
	('mp3', 'audio.png', 0, 0),
	('wma', 'audio.png', 0, 0),
	('ogg', 'audio.png', 0, 0),
	('doc', 'doc.png', 0, 0),
	('xls', 'xls.png', 0, 0),
	('docx', 'doc.png', 1, 0),
	('xlsx', 'xls.png', 0, 0),
	('gif', 'image.png', 0, 1),
	('jpg', 'image.png', 0, 1),
	('jpeg', 'image.png', 0, 1),
	('png', 'image.png', 0, 1),
	('mov', 'mov.png', 0, 0),
	('pdf', 'pdf.png', 1, 0),
	('psd', 'psd.png', 0, 0),
	('rm', 'rm.png', 0, 0),
	('svg', 'svg.png', 0, 0),
	('swf', 'swf.png', 0, 0),
	('avi', 'video.png', 0, 0),
	('mpeg', 'video.png', 0, 0),
	('mpg', 'video.png', 0, 0),
	('qt', 'mov.png', 0, 0),
	('vob', 'video.png', 0, 0),
	('txt', 'text.png', 1, 0),
	('html', 'html.png', 1, 0),
	('slim', 'ppt.png', 1, 0),
	('ppt', 'ppt.png', 0, 0),
	('webfile', 'webfile.png', 0, 0),
        ('odt', 'doc.png', '0', '0'),
        ('fodt', 'doc.png', '0', '0');

INSERT INTO `<?php echo $table_prefix ?>im_types` (`name`, `icon`) VALUES
	('ICQ', 'icq.gif'),
	('AIM', 'aim.gif'),
	('MSN', 'msn.gif'),
	('Yahoo!', 'yahoo.gif'),
	('Skype', 'skype.gif'),
	('Jabber', 'jabber.gif');


INSERT INTO `<?php echo $table_prefix ?>cron_events` (`name`, `recursive`, `delay`, `is_system`, `enabled`, `date`) VALUES
	('purge_trash', '1', '1440', '1', '1', '0000-00-00 00:00:00'),
	('send_reminders', '1', '10', '0', '1', '0000-00-00 00:00:00'),
	('send_password_expiration_reminders', '1', '1440', '1', '1', '0000-00-00 00:00:00'),
	('send_notifications_through_cron', '1', '1', '0', '0', '0000-00-00 00:00:00'),
	('delete_mails_from_server', '1', '1440', '1', '1', '0000-00-00 00:00:00'),
	('clear_tmp_folder', '1', '1440', '1', '1', '0000-00-00 00:00:00'),
	('check_upgrade', '1', '1440', '0', '1', '0000-00-00 00:00:00'),
        ('import_google_calendar', '1', '10', '0', '0', '0000-00-00 00:00:00'),
        ('export_google_calendar', '1', '10', '0', '0', '0000-00-00 00:00:00');
	
INSERT INTO `<?php echo $table_prefix ?>object_reminder_types` (`name`) VALUES
  ('reminder_email'),
  ('reminder_popup');
  
INSERT INTO `<?php echo $table_prefix ?>contact_config_categories` (`name`, `is_system`, `type`, `category_order`) VALUES 
	('general', 0, 0, 0),
	('task panel', 0, 0, 2),
	('calendar panel', 0, 0, 4),
	('context help', 1, 0, 5),
	('time panel', 1, 0, 3);
	
INSERT INTO `<?php echo $table_prefix ?>contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES 
 ('task panel', 'can notify from quick add', '1', 'BoolConfigHandler', 0, 0, 'Notification checkbox default value'),
 ('task panel', 'tasksShowWorkspaces', '1', 'BoolConfigHandler', 1, 0, ''),
 ('task panel', 'tasksShowTime', '1', 'BoolConfigHandler', 1, 0, ''),
 ('task panel', 'tasksShowDates', '1', 'BoolConfigHandler', 1, 0, ''),
 ('task panel', 'tasksShowTags', '1', 'BoolConfigHandler', 1, 0, ''),
 ('task panel', 'tasksShowEmptyMilestones', '1', 'BoolConfigHandler', 1, 0, ''),
 ('task panel', 'tasksGroupBy', 'milestone', 'StringConfigHandler', 1, 0, ''),
 ('task panel', 'tasksOrderBy', 'priority', 'StringConfigHandler', 1, 0, ''),
 ('task panel', 'task panel status', '1', 'IntegerConfigHandler', 1, 0, ''),
 ('task panel', 'task panel filter', 'assigned_to', 'StringConfigHandler', 1, 0, ''),
 ('task panel', 'task panel filter value', '0', 'UserCompanyConfigHandler', 1, 0, ''),
 ('task panel', 'noOfTasks', '15', 'IntegerConfigHandler', '0', '100', NULL),
 ('task panel', 'task_display_limit', '500', 'IntegerConfigHandler', '0', '200', NULL),
 ('general', 'localization', '', 'LocalizationConfigHandler', 0, 100, ''),
 ('general', 'search_engine', 'match', 'SearchEngineConfigHandler', 0, 700, ''),
 ('general', 'lastAccessedWorkspace', '0', 'IntegerConfigHandler', 1, 0, ''),
 ('general', 'work_day_start_time', '9:00', 'TimeConfigHandler', 0, 400, 'Work day start time'),
 ('general', 'work_day_end_time', '18:00', 'TimeConfigHandler', 0, 410, 'Work day end time'),
 ('general', 'time_format_use_24', '0', 'BoolConfigHandler', 0, 500, 'Use 24 hours time format'),
 ('general', 'date_format', 'd/m/Y', 'DateFormatConfigHandler', 0, 600, 'Date objects will be displayed using this format.'),
 ('general', 'descriptive_date_format', 'l, j F', 'StringConfigHandler', 0, 700, 'Descriptive dates will be displayed using this format.'),
 ('general', 'custom_report_tab', '5', 'StringConfigHandler', '1', '0', NULL),
 ('general', 'last_mail_format', 'html', 'StringConfigHandler', '1', '0', NULL),
 ('general', 'amount_objects_to_show', '5', 'IntegerConfigHandler', '0', '0', NULL),
 ('general', 'reset_password', '', 'StringConfigHandler', '1', '0', 'Used to store per-user tokens to validate password reset requests'),
 ('general', 'autodetect_time_zone', '1', 'BoolConfigHandler', '0', '0', NULL),
 ('general', 'detect_mime_type_from_extension', '0', 'BoolConfigHandler', '0', '0', NULL),
 ('general', 'root_dimensions', '', 'RootDimensionsConfigHandler', '0', '0', NULL), 
 ('general', 'show_object_direct_url',0,'BoolConfigHandler',0,0,NULL ),
 ('general', 'drag_drop_prompt','prompt','DragDropPromptConfigHandler',0,0,NULL ),
 ('calendar panel', 'calendar view type', 'viewweek', 'StringConfigHandler', 1, 0, ''),
 ('calendar panel', 'calendar user filter', '0', 'IntegerConfigHandler', 1, 0, ''),
 ('calendar panel', 'calendar status filter', '', 'StringConfigHandler', 1, 0, ''),
 ('calendar panel', 'start_monday', '', 'BoolConfigHandler', 0, 0, ''),
 ('calendar panel', 'show_week_numbers', '', 'BoolConfigHandler', 0, 0, ''),
 ('context help', 'show_tasks_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_account_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_active_tasks_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_general_timeslots_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_late_tasks_widget_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_pending_tasks_widget_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_documents_widget_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_active_tasks_widget_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_calendar_widget_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_messages_widget_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_dashboard_info_widget_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_comments_widget_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_emails_widget_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_reporting_panel_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_file_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_administration_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_member_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_contact_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_company_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_upload_file_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_upload_file_workspace_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_upload_file_tags_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_upload_file_description_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_upload_file_custom_properties_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_upload_file_subscribers_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_upload_file_linked_objects_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_note_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_note_workspace_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_note_tags_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_note_custom_properties_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_note_subscribers_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_note_linked_object_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_milestone_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_milestone_workspace_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_milestone_tags_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_milestone_description_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_milestone_reminders_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_milestone_custom_properties_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_milestone_linked_object_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_milestone_subscribers_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_workspace_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_print_report_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_task_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_task_workspace_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_task_tags_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_task_reminders_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_task_custom_properties_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_task_linked_objects_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_task_subscribers_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_list_task_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_time_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_webpage_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_webpage_workspace_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_webpage_tags_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_webpage_description_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_webpage_custom_properties_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_webpage_subscribers_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_webpage_linked_objects_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_event_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_event_workspace_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_event_tag_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_event_description_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_event_repeat_options_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_event_reminders_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_event_custom_properties_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_event_subscribers_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_event_linked_objects_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('context help', 'show_add_event_inivitation_context_help', '1', 'BoolConfigHandler', '1', '0', NULL),
 ('time panel', 'TM show time type', '0', 'IntegerConfigHandler', 1, 0, ''),
 ('time panel', 'TM report show time type', '0', 'IntegerConfigHandler', 1, 0, ''),
 ('time panel', 'TM user filter', '0', 'IntegerConfigHandler', 1, 0, ''),
 ('time panel', 'TM tasks user filter', '0', 'IntegerConfigHandler', 1, 0, ''),
 ('general', 'show_context_help', 'until_close', 'ShowContextHelpConfigHandler', '0', '0', NULL),
 ('dashboard', 'show charts widget', '1', 'BoolConfigHandler', 0, 600, ''),
 ('dashboard', 'show dashboard info widget', '1', 'BoolConfigHandler', 0, 900, ''),
 ('general', 'rememberGUIState', '1', 'RememberGUIConfigHandler', 0, 300, ''),
 ('calendar panel', 'calendar task filter', 'pending', 'StringConfigHandler', 1, 0, ''),
 ('task panel', 'close timeslot open', '1', 'BoolConfigHandler', 0, 0, ''),
 ('calendar panel', 'reminders_events', 'reminder_email,1,60', 'StringConfigHandler', '0', '0', NULL),
 ('dashboard', 'filters_dashboard', '0,0,10,0', 'StringConfigHandler', '0', '0', 'first position: entry to see the dimension, second position: view timeslot, third position: recent activities to show, fourth position: view views and downloads');
 

INSERT INTO `<?php echo $table_prefix ?>object_types` (`id`,`name`,`handler_class`,`table_name`,`type`,`icon`,`plugin_id`) VALUES
 (1,'workspace', 'Workspaces', 'workspaces', 'dimension_object', 'workspace', null),
 (2,'tag', '', '', 'dimension_group', 'tag', null),
 (3,'message', 'ProjectMessages', 'project_messages', 'content_object', 'message', null),
 (4,'weblink', 'ProjectWebpages', 'project_webpages', 'content_object', 'weblink', null),
 (5,'task', 'ProjectTasks', 'project_tasks', 'content_object', 'task', null),
 (6,'file', 'ProjectFiles', 'project_files', 'content_object', 'file', null),
 (8,'form', 'ProjectForms', 'project_forms', '', '', null),
 (9,'chart', 'ProjectCharts', 'project_charts', '', '', null),
 (10,'milestone', 'ProjectMilestones', 'project_milestones', 'content_object', 'milestone', null),
 (11,'event', 'ProjectEvents', 'project_events', 'content_object', 'event', null), 
 (12,'report', 'Reports', 'reports', 'located', 'reporting', null),
 (13,'template', 'COTemplates', 'templates', 'located', 'template', null),
 (14,'comment', 'Comments', 'comments', 'comment', 'comment', null), 
 (15,'billing', 'Billings', 'billings', '', '', null),
 (16,'contact', 'Contacts', 'contacts', 'content_object', 'contact', null),
 (17,'file revision', 'ProjectFileRevisions', 'file_revisions', 'content_object', 'file', null),
 (18,'timeslot', 'Timeslots', 'timeslots', 'located', 'time', null);

INSERT INTO `<?php echo $table_prefix ?>address_types` (`name`,`is_system`) VALUES
 ('home', 1),
 ('work', 1),
 ('other', 1);

INSERT INTO `<?php echo $table_prefix ?>telephone_types` (`name`,`is_system`) VALUES
 ('home', 1),
 ('work', 1),
 ('other', 1),
 ('assistant', 0),
 ('callback', 0),
 ('mobile', 1),
 ('pager', 0),
 ('fax', 0);

INSERT INTO `<?php echo $table_prefix ?>email_types` (`name`,`is_system`) VALUES
 ('user',1),
 ('personal', 1),
 ('work', 1),
 ('other', 1);
 
INSERT INTO `<?php echo $table_prefix ?>webpage_types` (`name`,`is_system`) VALUES
 ('personal', 1),
 ('work', 1),
 ('other', 1);


INSERT INTO `<?php echo $table_prefix ?>tab_panels` (`id`,`title`,`icon_cls`,`refresh_on_context_change`,`default_controller`,`default_action`,`initial_controller`,`initial_action`,`enabled`,`type`,`ordering`,`plugin_id`,`object_type_id`) VALUES 
 ('calendar-panel','calendar','ico-calendar',1,'event','view_calendar','','',0,'system',8,0,11),
 ('contacts-panel','contacts','ico-contacts',1,'contact','init','','',0,'system',7,0,16),
 ('documents-panel','documents','ico-documents',1,'files','init','','',1,'system',6,0,6),
 ('messages-panel','messages','ico-messages',1,'message','init','','',1,'system',5,0,3),
 ('overview-panel','overview','ico-overview',1,'dashboard','main_dashboard','dashboard','main_dashboard',1,'system',-100,0,0),
 ('reporting-panel','reporting','ico-reporting',1,'reporting','index','','',0,'system',9,0,12),
 ('tasks-panel','tasks','ico-tasks',1,'task','new_list_tasks','','',1,'system',3,0,5),
 ('time-panel','time','ico-time-layout',1,'time','index','','',0,'system',8,0,0),
 ('webpages-panel','web pages','ico-webpages',1,'webpage','init','','',0,'system',7,0,4);
 


INSERT INTO `<?php echo $table_prefix ?>permission_groups` (`name`, `contact_id`, `is_context`, `plugin_id`, `type`) VALUES
('Super Administrator',	0,	0,	NULL, 'roles'),
('Administrator',	0,	0,	NULL, 'roles'),
('Manager',	0,	0,	NULL, 'roles'),
('Executive',	0,	0,	NULL, 'roles'),
('Collaborator Customer',	0,	0,	NULL, 'roles'),
('Internal Collaborator',	0,	0,	NULL, 'roles'),
('External Collaborator',	0,	0,	NULL, 'roles'),
('ExecutiveGroup',	0,	0,	NULL, 'roles'),
('CollaboratorGroup',	0,	0,	NULL, 'roles'),
('GuestGroup',	0,	0,	NULL, 'roles'),
('Guest Customer',	0,	0,	NULL, 'roles'),
('Guest',	0,	0,	NULL, 'roles'),
('Non-Exec Director',	0,	0,	NULL, 'roles');

SET @exegroup := (SELECT pg.id FROM <?php echo $table_prefix ?>permission_groups pg WHERE pg.name = 'ExecutiveGroup');
SET @colgroup := (SELECT pg.id FROM <?php echo $table_prefix ?>permission_groups pg WHERE pg.name = 'CollaboratorGroup');
SET @guegroup := (SELECT pg.id FROM <?php echo $table_prefix ?>permission_groups pg WHERE pg.name = 'GuestGroup');
UPDATE `<?php echo $table_prefix ?>permission_groups` SET `parent_id` = (@exegroup) WHERE `name` IN ('Super Administrator','Administrator','Manager','Executive');
UPDATE `<?php echo $table_prefix ?>permission_groups` SET `parent_id` = (@colgroup) WHERE `name` IN ('Collaborator Customer','Internal Collaborator','External Collaborator');
UPDATE `<?php echo $table_prefix ?>permission_groups` SET `parent_id` = (@guegroup) WHERE `name` IN ('Guest Customer','Guest','Non-Exec Director');

INSERT INTO `<?php echo $table_prefix ?>tab_panel_permissions` (`permission_group_id`, `tab_panel_id`) VALUES 
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Super Administrator'),	'calendar-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Super Administrator'),	'documents-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Super Administrator'),	'mails-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Super Administrator'),	'messages-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Super Administrator'),	'overview-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Super Administrator'),	'tasks-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Super Administrator'),	'time-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Super Administrator'),	'webpages-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Super Administrator'),	'contacts-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Super Administrator'),	'reporting-panel'),

((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Administrator'),	'calendar-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Administrator'),	'documents-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Administrator'),	'mails-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Administrator'),	'messages-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Administrator'),	'overview-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Administrator'),	'tasks-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Administrator'),	'time-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Administrator'),	'webpages-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Administrator'),	'contacts-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Administrator'),	'reporting-panel'),

((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Manager'),	'calendar-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Manager'),	'documents-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Manager'),	'mails-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Manager'),	'messages-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Manager'),	'overview-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Manager'),	'tasks-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Manager'),	'time-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Manager'),	'webpages-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Manager'),	'contacts-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Manager'),	'reporting-panel'),

((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Executive'),	'calendar-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Executive'),	'documents-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Executive'),	'mails-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Executive'),	'messages-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Executive'),	'overview-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Executive'),	'tasks-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Executive'),	'time-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Executive'),	'webpages-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Executive'),	'contacts-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Executive'),	'reporting-panel'),

((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Collaborator Customer'),	'calendar-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Collaborator Customer'),	'documents-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Collaborator Customer'),	'messages-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Collaborator Customer'),	'overview-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Collaborator Customer'),	'tasks-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Collaborator Customer'),	'time-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Collaborator Customer'),	'webpages-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Collaborator Customer'),	'contacts-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Collaborator Customer'),	'reporting-panel'),

((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Internal Collaborator'),	'calendar-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Internal Collaborator'),	'documents-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Internal Collaborator'),	'messages-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Internal Collaborator'),	'overview-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Internal Collaborator'),	'tasks-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Internal Collaborator'),	'time-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Internal Collaborator'),	'webpages-panel'),

((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'External Collaborator'),	'calendar-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'External Collaborator'),	'documents-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'External Collaborator'),	'messages-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'External Collaborator'),	'overview-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'External Collaborator'),	'tasks-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'External Collaborator'),	'time-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'External Collaborator'),	'webpages-panel'),

((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Guest Customer'),	'overview-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Guest Customer'),	'calendar-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Guest Customer'),	'documents-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Guest Customer'),	'messages-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Guest Customer'),	'webpages-panel'),

((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Guest'),	'overview-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Guest'),	'calendar-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Guest'),	'messages-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Guest'),	'webpages-panel'),

((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Non-Exec Director'),	'calendar-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Non-Exec Director'),	'documents-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Non-Exec Director'),	'messages-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Non-Exec Director'),	'overview-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Non-Exec Director'),	'tasks-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Non-Exec Director'),	'time-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Non-Exec Director'),	'webpages-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Non-Exec Director'),	'contacts-panel'),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Non-Exec Director'),	'reporting-panel');


INSERT INTO `<?php echo $table_prefix ?>system_permissions` (`permission_group_id`, `can_manage_security`, `can_manage_configuration`, `can_manage_templates`, `can_manage_time`, `can_add_mail_accounts`, `can_manage_dimensions`, `can_manage_dimension_members`, `can_manage_tasks`, `can_task_assignee`, `can_manage_billing`, `can_view_billing`) VALUES
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Super Administrator'),	1,	1,	1,	1,	1,		1,	1,	1,	1,	1,	1),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Administrator'),	1,	1,	1,	1,	1,		1,	1,	1,	1,	1,	1),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Manager'),	1,	0,	1,	1,	1,		0,	1,	1,	1,	1,	1),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Executive'),	1,	0,	0,	0,	1,		0,	1,	0,	1,	0,	1),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Collaborator Customer'),	0,	0,	0,	0,	0,		0,	0,	0,	1,	0,	1),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Internal Collaborator'),	0,	0,	0,	0,	0,		0,	0,	0,	1,	0,	0),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'External Collaborator'),	0,	0,	0,	0,	0,		0,	0,	0,	1,	0,	0),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Guest Customer'),	0,	0,	0,	0,	0,		0,	0,	0,	0,	0,	0),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Guest'),	0,	0,	0,	0,	0,		0,	0,	0,	0,	0,	0),
((SELECT id FROM <?php echo $table_prefix ?>permission_groups WHERE name = 'Non-Exec Director'),	0,	0,	0,	0,	0,		0,	0,	0,	0,	0,	1);

INSERT INTO `<?php echo $table_prefix ?>widgets` (`name`,`title`,`plugin_id`,`path`,`default_options`,`default_section`,`default_order`) VALUES 
 ('overdue_upcoming','overdue and upcoming',0,'','','left',3),
 ('people','people',0,'','','right',-1),
 ('messages','notes',0,'','','right',1000),
 ('documents','documents',0,'','','right',1100),
 ('calendar','upcoming events milestones and tasks',0,'','','top',0);

INSERT INTO <?php echo $table_prefix ?>role_object_type_permissions (role_id, object_type_id, can_delete, can_write)
 SELECT p.id, o.id, 1, 1
 FROM `<?php echo $table_prefix ?>object_types` o JOIN `<?php echo $table_prefix ?>permission_groups` p
 WHERE o.`name` IN ('message','weblink','file','task','milestone','event','contact','mail','timeslot','report','comment')
 AND p.`name` IN ('Super Administrator','Administrator','Manager','Executive');

INSERT INTO <?php echo $table_prefix ?>role_object_type_permissions (role_id, object_type_id, can_delete, can_write)
 SELECT p.id, o.id, 0, 1
 FROM `<?php echo $table_prefix ?>object_types` o JOIN `<?php echo $table_prefix ?>permission_groups` p
 WHERE o.`name` IN ('message','weblink','file','task','milestone','event','contact','timeslot','report','comment')
 AND p.`name` IN ('Collaborator Customer');

INSERT INTO <?php echo $table_prefix ?>role_object_type_permissions (role_id, object_type_id, can_delete, can_write)
 SELECT p.id, o.id, 0, 1
 FROM `<?php echo $table_prefix ?>object_types` o JOIN `<?php echo $table_prefix ?>permission_groups` p
 WHERE o.`name` IN ('message','weblink','file','task','milestone','event','timeslot','comment')
 AND p.`name` IN ('Internal Collaborator','External Collaborator');

INSERT INTO <?php echo $table_prefix ?>role_object_type_permissions (role_id, object_type_id, can_delete, can_write)
 SELECT p.id, o.id, 0, 0
 FROM `<?php echo $table_prefix ?>object_types` o JOIN `<?php echo $table_prefix ?>permission_groups` p
 WHERE o.`name` IN ('message','weblink','file','event','comment')
 AND p.`name` IN ('Guest Customer');

INSERT INTO <?php echo $table_prefix ?>role_object_type_permissions (role_id, object_type_id, can_delete, can_write)
 SELECT p.id, o.id, 0, 0
 FROM `<?php echo $table_prefix ?>object_types` o JOIN `<?php echo $table_prefix ?>permission_groups` p
 WHERE o.`name` IN ('message','weblink','event','comment')
 AND p.`name` IN ('Guest');

INSERT INTO <?php echo $table_prefix ?>role_object_type_permissions (role_id, object_type_id, can_delete, can_write)
 SELECT p.id, o.id, 0, 0
 FROM `<?php echo $table_prefix ?>object_types` o JOIN `<?php echo $table_prefix ?>permission_groups` p
 WHERE o.`name` IN ('message','weblink','file','task','milestone','event','contact','timeslot','report','comment')
 AND p.`name` IN ('Non-Exec Director');

UPDATE <?php echo $table_prefix ?>role_object_type_permissions SET can_write = 1 WHERE object_type_id = (SELECT id FROM <?php echo $table_prefix ?>object_types WHERE name='comment');
