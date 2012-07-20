
INSERT INTO `<?php echo $table_prefix ?>config_options` (`category_name`, `name`, `value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
 ('mailing', 'user_email_fetch_count', '10', 'IntegerConfigHandler', 0, 0, 'How many emails to fetch when checking for email'),
 ('mailing', 'sent_mails_sync', '0', 'BoolConfigHandler', 0, 0, 'imap email accounts synchronization possibility'),
 ('mailing', 'check_spam_in_subject', '0', 'BoolConfigHandler', 0, 0, '')
 ON DUPLICATE KEY UPDATE name=name;

INSERT INTO <?php echo $table_prefix ?>contact_config_options (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
 ('mails panel', 'view deleted accounts emails', '1', 'BoolConfigHandler', '0', '0', NULL),
 ('mails panel', 'block_email_images', '1', 'BoolConfigHandler', '0', '0', NULL),
 ('mails panel', 'draft_autosave_timeout', '60', 'IntegerConfigHandler', '0', '100', NULL),
 ('mails panel', 'attach_docs_content', '1', 'BoolConfigHandler', '0', '0', NULL),
 ('mails panel', 'email_polling', '0', 'IntegerConfigHandler', '1', '0', NULL),
 ('mails panel', 'show_unread_on_title', '0', 'BoolConfigHandler', '1', '0', NULL),
 ('mails panel', 'max_spam_level', '0', 'IntegerConfigHandler', '0', '100', NULL),
 ('mails panel', 'create_contacts_from_email_recipients', '0', 'BoolConfigHandler', '0', '101', NULL),
 ('mails panel', 'mail_drag_drop_prompt', 'prompt', 'MailDragDropPromptConfigHandler', '0', '102', NULL),
 ('mails panel', 'show_emails_as_conversations', '0', 'BoolConfigHandler', '0', '0', NULL),
 ('mails panel', 'mails account filter', '', 'StringConfigHandler', '1', '0', NULL),
 ('mails panel', 'mails classification filter', 'all', 'StringConfigHandler', '1', '0', NULL),
 ('mails panel', 'mails read filter', 'all', 'StringConfigHandler', '1', '0', NULL),
 ('mails panel', 'hide_quoted_text_in_emails', '1', 'BoolConfigHandler', 0, 110, NULL),
 ('mails panel', 'mail_account_err_check_interval', '300', 'IntegerConfigHandler', 0, 120, NULL),
 ('mails panel', 'classify_mail_with_conversation', '1', 'BoolConfigHandler', 0, 130, NULL)
ON DUPLICATE KEY UPDATE name=name;

INSERT INTO `<?php echo $table_prefix ?>contact_config_categories` (`name`, `is_system`, `type`, `category_order`) VALUES 
 ('mails panel', 0, 0, 5)
ON DUPLICATE KEY UPDATE name=name;

INSERT INTO `<?php echo $table_prefix ?>administration_tools` (`name`, `controller`, `action`, `order`) VALUES
 ('mass_mailer', 'administration', 'tool_mass_mailer', 2)
ON DUPLICATE KEY UPDATE name=name;

INSERT INTO `<?php echo $table_prefix ?>dimension_object_type_contents` (`dimension_id`,`dimension_object_type_id`,`content_object_type_id`, `is_required`, `is_multiple`)
   SELECT dimension_id, object_type_id, (SELECT id FROM <?php echo $table_prefix ?>object_types WHERE name = 'mail' ),0, 1 
   FROM <?php echo $table_prefix ?>dimension_object_types;

INSERT INTO `<?php echo $table_prefix ?>tab_panel_permissions` (permission_group_id, tab_panel_id)  (
  SELECT id, 'mails-panel' FROM <?php echo $table_prefix ?>permission_groups
  WHERE name IN ('Super Administrator','Administrator', 'Manager', 'Executive' ,'Account Owner')
) ON DUPLICATE KEY UPDATE tab_panel_id = tab_panel_id;

UPDATE <?php echo $table_prefix ?>system_permissions SET can_add_mail_accounts = 1
WHERE permission_group_id IN (
  SELECT id FROM <?php echo $table_prefix ?>permission_groups
  WHERE name IN ('Super Administrator','Administrator', 'Manager', 'Executive' ,'Account Owner')
);

INSERT INTO `<?php echo $table_prefix ?>cron_events` (name, recursive, delay, is_system, enabled) VALUES ('check_mail', 1, 10, 0, 1) ON DUPLICATE KEY UPDATE name=name;
