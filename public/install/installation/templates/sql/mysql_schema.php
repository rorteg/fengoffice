-- <?php echo $table_prefix ?> og_
-- <?php echo $default_charset ?> DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
-- <?php echo $default_collation ?> collate utf8_unicode_ci
-- <?php echo $engine ?> InnoDB
-- varchar cannot be larger than 256
-- blob/text cannot have default values
-- sql queries must finish with ;\n (line break inmediately after ;)

CREATE TABLE `<?php echo $table_prefix ?>administration_tools` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `name` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `controller` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `action` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `order` tinyint(3) unsigned NOT NULL default '0',
  `visible` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>application_logs` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `taken_by_id` int(10) unsigned default NULL,
  `rel_object_id` int(10) NOT NULL default '0',
  `object_name` text <?php echo $default_collation ?>,
  `rel_object_manager` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by_id` int(10) unsigned default NULL,
  `action` enum('upload','open','close','delete','edit','add','trash','untrash','subscribe','unsubscribe','tag','comment','link','unlink','login','logout','untag','archive','unarchive','move','copy','read','download','checkin','checkout') <?php echo $default_collation ?> default NULL,
  `is_private` tinyint(1) unsigned NOT NULL default '0',
  `is_silent` tinyint(1) unsigned NOT NULL default '0',
  `log_data` text <?php echo $default_collation ?>,
  PRIMARY KEY  (`id`),
  KEY `created_on` (`created_on`),
  KEY `by_object` USING BTREE (`rel_object_manager`,`rel_object_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>comments` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `rel_object_id` int(10) unsigned NOT NULL default '0',
  `rel_object_manager` varchar(30) <?php echo $default_collation ?> NOT NULL default '',
  `text` text <?php echo $default_collation ?>,
  `is_private` tinyint(1) unsigned NOT NULL default '0',
  `is_anonymous` tinyint(1) unsigned NOT NULL default '0',
  `author_name` varchar(50) <?php echo $default_collation ?> default NULL,
  `author_email` varchar(100) <?php echo $default_collation ?> default NULL,
  `author_homepage` varchar(100) <?php echo $default_collation ?> NOT NULL default '',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by_id` int(10) unsigned default NULL,
  `updated_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated_by_id` int(10) unsigned default NULL,
  `trashed_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `trashed_by_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`),
  KEY `object_id` (`rel_object_manager`, `rel_object_id`),
  KEY `created_on` (`created_on`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>companies` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `client_of_id` smallint(5) unsigned default NULL,
  `name` varchar(100) <?php echo $default_collation ?> default NULL,
  `email` varchar(100) <?php echo $default_collation ?> default NULL,
  `notes` text <?php echo $default_collation ?> ,
  `homepage` varchar(100) <?php echo $default_collation ?> default NULL,
  `address` varchar(100) <?php echo $default_collation ?> default NULL,
  `address2` varchar(100) <?php echo $default_collation ?> default NULL,
  `city` varchar(50) <?php echo $default_collation ?> default NULL,
  `state` varchar(50) <?php echo $default_collation ?> default NULL,
  `zipcode` varchar(30) <?php echo $default_collation ?> default NULL,
  `country` varchar(10) <?php echo $default_collation ?> default NULL,
  `phone_number` varchar(50) <?php echo $default_collation ?> default NULL,
  `fax_number` varchar(50) <?php echo $default_collation ?> default NULL,
  `logo_file` varchar(44) <?php echo $default_collation ?> default NULL,
  `timezone` float(3,1) NOT NULL default '0.0',
  `hide_welcome_info` tinyint(1) unsigned NOT NULL default '0',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by_id` int(10) unsigned default NULL,
  `updated_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated_by_id` int(10) unsigned default NULL,
  `trashed_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `trashed_by_id` int(10) unsigned default NULL,
  `archived_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `archived_by_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`),
  KEY `created_on` (`created_on`),
  KEY `client_of_id` (`client_of_id`),
  KEY `trashed_on` USING BTREE (`trashed_on`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>config_categories` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `name` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `is_system` tinyint(1) unsigned NOT NULL default '0',
  `category_order` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `order` (`category_order`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>config_options` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `category_name` varchar(30) <?php echo $default_collation ?> NOT NULL default '',
  `name` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `value` text <?php echo $default_collation ?>,
  `config_handler_class` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `is_system` tinyint(1) unsigned NOT NULL default '0',
  `option_order` smallint(5) unsigned NOT NULL default '0',
  `dev_comment` varchar(255) <?php echo $default_collation ?> default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `order` (`option_order`),
  KEY `category_id` (`category_name`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>file_repo` (
  `id` varchar(40) <?php echo $default_collation ?> NOT NULL default '',
  `content` longblob NOT NULL,
  `order` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `order` (`order`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>file_repo_attributes` (
  `id` varchar(40) <?php echo $default_collation ?> NOT NULL default '',
  `attribute` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `value` text <?php echo $default_collation ?> NOT NULL,
  PRIMARY KEY  (`id`,`attribute`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>file_types` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `extension` varchar(10) <?php echo $default_collation ?> NOT NULL default '',
  `icon` varchar(30) <?php echo $default_collation ?> NOT NULL default '',
  `is_searchable` tinyint(1) unsigned NOT NULL default '0',
  `is_image` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `extension` (`extension`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>groups` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` TEXT <?php echo $default_collation ?> NOT NULL,
  `created_on` DATETIME NOT NULL,
  `created_by_id` INTEGER UNSIGNED NOT NULL,
  `updated_on` DATETIME NOT NULL,
  `updated_by_id` INTEGER UNSIGNED NOT NULL,
  	`can_edit_company_data` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
	`can_manage_security` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
	`can_manage_workspaces` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
	`can_manage_configuration` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
	`can_manage_contacts` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
	`can_manage_templates` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
	`can_manage_reports` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
	`can_manage_time` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
	`can_add_mail_accounts` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE = <?php echo $engine ?> AUTO_INCREMENT = 10000000 <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>group_users` (
  `group_id` INTEGER UNSIGNED NOT NULL,
  `user_id` INTEGER UNSIGNED NOT NULL,
  `created_on` DATETIME NOT NULL,
  `created_by_id` INTEGER UNSIGNED NOT NULL,
  PRIMARY KEY(`group_id`, `user_id`),
  INDEX `USER`(`user_id`)
) ENGINE = <?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>linked_objects` (
  `rel_object_manager` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `rel_object_id` int(10) unsigned NOT NULL default '0',
  `object_id` int(10) unsigned NOT NULL default '0',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by_id` int(10) unsigned default NULL,
  `object_manager` varchar(50) <?php echo $default_collation ?> NOT NULL default '',  
  PRIMARY KEY(`rel_object_manager`,`rel_object_id`,`object_id`,`object_manager`),
  KEY `other_obj` USING BTREE (`object_manager`,`object_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>im_types` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `name` varchar(30) <?php echo $default_collation ?> NOT NULL default '',
  `icon` varchar(30) <?php echo $default_collation ?> NOT NULL default '',
  PRIMARY KEY (`id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>object_subscriptions` (
  `object_id` int(10) unsigned NOT NULL default '0',
  `object_manager` varchar(50) NOT NULL,
  `user_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`object_id`,`object_manager`,`user_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>object_reminders` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `object_id` int(10) unsigned NOT NULL default '0',
  `object_manager` varchar(50) NOT NULL,
  `user_id` int(10) unsigned NOT NULL default '0',
  `type` VARCHAR(40) NOT NULL default '',
  `context` varchar(40) NOT NULL default '',
  `minutes_before` int(10) default NULL,
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  INDEX ( `date` ),
  KEY `type_date` (`type`,`date`),
  KEY `object` USING BTREE (`object_manager`,`object_id`,`date`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>object_reminder_types` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` VARCHAR(40) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE  `<?php echo $table_prefix ?>object_handins` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `title` text,
  `text` text,
  `responsible_user_id` int(10) unsigned default NULL,
  `rel_object_id` int(10) unsigned NOT NULL default '0',
  `rel_object_manager` varchar(50) NOT NULL,
  `order` int(10) unsigned default '0',
  `completed_by_id` int(10) unsigned default NULL,
  `completed_on` datetime default NULL,
  `responsible_company_id` int(10) unsigned default NULL,
  PRIMARY KEY (`id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>object_user_permissions` (
  `rel_object_id` INTEGER UNSIGNED NOT NULL,
  `rel_object_manager` VARCHAR(50) NOT NULL,
  `user_id` INTEGER UNSIGNED NOT NULL,
  `can_read` TINYINT(1) UNSIGNED NOT NULL,
  `can_write` TINYINT(1) UNSIGNED NOT NULL,
  PRIMARY KEY(`rel_object_id`, `user_id`, `rel_object_manager`)
) ENGINE = <?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE  `<?php echo $table_prefix ?>object_properties` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `rel_object_id` int(10) unsigned NOT NULL,
  `rel_object_manager` varchar(50) NOT NULL,
  `name` text NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY  (`id`),
  INDEX `ObjectID` (`rel_object_id`,`rel_object_manager`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>project_companies` (
  `project_id` int(10) unsigned NOT NULL default '0',
  `company_id` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`project_id`,`company_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE  `<?php echo $table_prefix ?>project_events` (
  `id` int(11) NOT NULL auto_increment,
  `created_by_id` int(11) NOT NULL default '0',
  `updated_by_id` int(11) default NULL,
  `updated_on` datetime default NULL,
  `created_on` datetime default NULL,
  `trashed_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `trashed_by_id` int(10) unsigned default NULL,
  `archived_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `archived_by_id` int(10) unsigned default NULL,
  `start` datetime default NULL,
  `duration` datetime default NULL,
  `subject` varchar(255) <?php echo $default_collation ?> default NULL,
  `description` text <?php echo $default_collation ?>,
  `private` char(1) <?php echo $default_collation ?> NOT NULL default '0',
  `repeat_end` date default NULL,
  `repeat_forever` TINYINT(1) UNSIGNED NOT NULL,
  `repeat_num` mediumint(9) NOT NULL default '0',
  `repeat_d` smallint(6) NOT NULL default '0',
  `repeat_m` smallint(6) NOT NULL default '0',
  `repeat_y` smallint(6) NOT NULL default '0',
  `repeat_h` smallint(6) NOT NULL default '0',
  `repeat_dow` int(10) unsigned NOT NULL default '0',
  `repeat_wnum` int(10) unsigned NOT NULL default '0',
  `repeat_mjump` int(10) unsigned NOT NULL default '0',
  `type_id` int(11) NOT NULL default '0',
  `special_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `trashed_on` USING BTREE (`trashed_on`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>project_file_revisions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `file_id` int(10) unsigned NOT NULL default '0',
  `file_type_id` smallint(5) unsigned NOT NULL default '0',
  `repository_id` varchar(40) <?php echo $default_collation ?> NOT NULL default '',
  `thumb_filename` varchar(44) <?php echo $default_collation ?> default NULL,
  `revision_number` int(10) unsigned NOT NULL default '0',
  `comment` text <?php echo $default_collation ?>,
  `type_string` varchar(255) <?php echo $default_collation ?> NOT NULL default '',
  `filesize` int(10) unsigned NOT NULL default '0',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by_id` int(10) unsigned default NULL,
  `updated_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated_by_id` int(10) unsigned default NULL,
  `trashed_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `trashed_by_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`),
  KEY `file_id` (`file_id`),
  KEY `updated_on` (`updated_on`),
  KEY `revision_number` (`revision_number`),
  KEY `trashed_on` USING BTREE (`trashed_on`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>project_files` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `filename` varchar(100) <?php echo $default_collation ?> NOT NULL default '',
  `description` text <?php echo $default_collation ?>,
  `is_private` tinyint(1) unsigned NOT NULL default '0',
  `is_important` tinyint(1) unsigned NOT NULL default '0',
  `is_locked` tinyint(1) unsigned NOT NULL default '0',
  `is_visible` tinyint(1) unsigned NOT NULL default '0',
  `expiration_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `comments_enabled` tinyint(1) unsigned NOT NULL default '0',
  `anonymous_comments_enabled` tinyint(1) unsigned NOT NULL default '0',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by_id` int(10) unsigned default '0',
  `updated_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated_by_id` int(10) unsigned default '0',
  `trashed_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `trashed_by_id` int(10) unsigned default NULL,
  `checked_out_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `checked_out_by_id` int(10) unsigned DEFAULT 0,
  `archived_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `archived_by_id` int(10) unsigned default NULL,
  `was_auto_checked_out` tinyint(1) unsigned NOT NULL default '0',
  `type` int(1) NOT NULL DEFAULT 0,
  `url` varchar(255) NULL,
  `mail_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `trashed_on` USING BTREE (`trashed_on`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>project_forms` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `project_id` int(10) unsigned NOT NULL default '0',
  `name` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `description` text <?php echo $default_collation ?> NOT NULL,
  `success_message` text <?php echo $default_collation ?> NOT NULL,
  `action` enum('add_comment','add_task') <?php echo $default_collation ?> NOT NULL default 'add_comment',
  `in_object_id` int(10) unsigned NOT NULL default '0',
  `created_on` datetime default NULL,
  `created_by_id` int(10) unsigned NOT NULL default '0',
  `updated_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated_by_id` int(10) unsigned NOT NULL default '0',
  `trashed_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `trashed_by_id` int(10) unsigned default NULL,
  `is_visible` tinyint(1) unsigned NOT NULL default '0',
  `is_enabled` tinyint(1) unsigned NOT NULL default '0',
  `order` smallint(6) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `trashed_on` USING BTREE (`trashed_on`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>project_messages` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `milestone_id` int(10) unsigned NOT NULL default '0',
  `title` varchar(100) <?php echo $default_collation ?> default NULL,
  `text` text <?php echo $default_collation ?>,
  `additional_text` text <?php echo $default_collation ?>,
  `is_important` tinyint(1) unsigned NOT NULL default '0',
  `is_private` tinyint(1) unsigned NOT NULL default '0',
  `comments_enabled` tinyint(1) unsigned NOT NULL default '0',
  `anonymous_comments_enabled` tinyint(1) unsigned NOT NULL default '0',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by_id` int(10) unsigned default NULL,
  `updated_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated_by_id` int(10) unsigned default NULL,
  `trashed_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `trashed_by_id` int(10) unsigned default NULL,
  `archived_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `archived_by_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`),
  KEY `milestone_id` (`milestone_id`),
  KEY `created_on` (`created_on`),
  KEY `trashed_on` USING BTREE (`trashed_on`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>project_milestones` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) <?php echo $default_collation ?> default NULL,
  `description` text <?php echo $default_collation ?>,
  `due_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `assigned_to_company_id` smallint(10) NOT NULL default '0',
  `assigned_to_user_id` int(10) unsigned NOT NULL default '0',
  `is_private` tinyint(1) unsigned NOT NULL default '0',
  `is_urgent` BOOLEAN NOT NULL default '0',
  `completed_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `completed_by_id` int(10) unsigned default NULL,
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by_id` int(10) unsigned default NULL,
  `updated_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated_by_id` int(10) unsigned default NULL,
  `trashed_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `trashed_by_id` int(10) unsigned default NULL,
  `archived_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `archived_by_id` int(10) unsigned default NULL,
  `is_template` BOOLEAN NOT NULL default '0',
  `from_template_id` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `due_date` (`due_date`),
  KEY `completed_on` (`completed_on`),
  KEY `created_on` (`created_on`),
  KEY `trashed_on` USING BTREE (`trashed_on`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>project_tasks` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `parent_id` int(10) unsigned default NULL,
  `title` TEXT <?php echo $default_collation ?>,
  `text` text <?php echo $default_collation ?>,
  `due_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `start_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `assigned_to_company_id` smallint(5) unsigned default NULL,
  `assigned_to_user_id` int(10) unsigned default NULL,
  `assigned_on` datetime default NULL,
  `assigned_by_id` int(10) unsigned default NULL,
  `time_estimate` int(10) unsigned NOT NULL default '0',
  `completed_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `completed_by_id` int(10) unsigned default NULL,
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by_id` int(10) unsigned default NULL,
  `updated_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated_by_id` int(10) unsigned default NULL,
  `trashed_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `trashed_by_id` int(10) unsigned default NULL,   
  `archived_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `archived_by_id` int(10) unsigned default NULL,
  `started_on` DATETIME DEFAULT NULL,
  `started_by_id` INTEGER UNSIGNED NOT NULL,
  `priority` INTEGER UNSIGNED default 200,
  `state` INTEGER UNSIGNED,
  `order` int(10) unsigned  default '0',
  `milestone_id` INTEGER UNSIGNED,
  `is_private` BOOLEAN NOT NULL default '0',
  `is_template` BOOLEAN NOT NULL default '0',
  `from_template_id` int(10) NOT NULL default '0',
  `repeat_end` DATETIME NOT NULL default '0000-00-00 00:00:00',
  `repeat_forever` tinyint(1) NOT NULL,
  `repeat_num` int(10) unsigned NOT NULL default '0',
  `repeat_d` int(10) unsigned NOT NULL,
  `repeat_m` int(10) unsigned NOT NULL,
  `repeat_y` int(10) unsigned NOT NULL,
  `repeat_by` varchar(15) collate utf8_unicode_ci NOT NULL default '',
  `object_subtype` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `completed_on` (`completed_on`),
  KEY `created_on` (`created_on`),
  KEY `order` (`order`),
  KEY `trashed_on` USING BTREE (`trashed_on`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;



CREATE TABLE `<?php echo $table_prefix ?>project_users` (
  `project_id` int(10) unsigned NOT NULL default '0',
  `user_id` int(10) unsigned NOT NULL default '0',
  `created_on` datetime default NULL,
  `created_by_id` int(10) unsigned NOT NULL default '0',
  `can_read_messages` tinyint(1) unsigned default '0',
  `can_write_messages` tinyint(1) unsigned default '0',
  `can_read_tasks` tinyint(1) unsigned default '0',
  `can_write_tasks` tinyint(1) unsigned default '0',
  `can_read_milestones` tinyint(1) unsigned default '0',
  `can_write_milestones` tinyint(1) unsigned default '0',
  `can_read_files` tinyint(1) unsigned default '0',
  `can_write_files` tinyint(1) unsigned default '0',
  `can_read_events` tinyint(1) unsigned default '0',
  `can_write_events` tinyint(1) unsigned default '0',
  `can_read_weblinks` tinyint(1) unsigned default '0',
  `can_write_weblinks` tinyint(1) unsigned default '0',
  `can_read_mails` tinyint(1) unsigned default '0',
  `can_write_mails` tinyint(1) unsigned default '0',
  `can_read_contacts` tinyint(1) unsigned default '0',
  `can_write_contacts` tinyint(1) unsigned default '0',
  `can_read_comments` tinyint(1) unsigned default '0',
  `can_write_comments` tinyint(1) unsigned default '0',
  `can_assign_to_owners` tinyint(1) unsigned NOT NULL default '0',
  `can_assign_to_other` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`project_id`,`user_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>projects` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(50) <?php echo $default_collation ?> default NULL,
  `description` text <?php echo $default_collation ?>,
  `show_description_in_overview` tinyint(1) unsigned NOT NULL default '0',
  `completed_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `completed_by_id` int(11) default NULL,
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by_id` int(10) unsigned default NULL,
  `updated_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated_by_id` int(10) unsigned default NULL,
  `color` int(10) unsigned default 0,
  `parent_id` int(10) unsigned NOT NULL default 0,
  `p1` int(10) unsigned NOT NULL default '0',
  `p2` int(10) unsigned NOT NULL default '0',
  `p3` int(10) unsigned NOT NULL default '0',
  `p4` int(10) unsigned NOT NULL default '0',
  `p5` int(10) unsigned NOT NULL default '0',
  `p6` int(10) unsigned NOT NULL default '0',
  `p7` int(10) unsigned NOT NULL default '0',
  `p8` int(10) unsigned NOT NULL default '0',
  `p9` int(10) unsigned NOT NULL default '0',
  `p10` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `completed_on` (`completed_on`),
  KEY `name` USING BTREE (`name`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>searchable_objects` (
  `rel_object_manager` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `rel_object_id` int(10) unsigned NOT NULL default '0',
  `column_name` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `content` text <?php echo $default_collation ?> NOT NULL,
  `project_id` int(10) unsigned NOT NULL default '0',
  `is_private` tinyint(1) unsigned NOT NULL default '0',
  `user_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`rel_object_manager`,`rel_object_id`,`column_name`),
  KEY `project_id` (`project_id`),
  FULLTEXT KEY `content` (`content`)
) ENGINE=MyISAM <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>tags` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tag` varchar(30) <?php echo $default_collation ?> NOT NULL default '',
  `rel_object_id` int(10) unsigned NOT NULL default '0',
  `rel_object_manager` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `created_on` datetime default NULL,
  `created_by_id` int(10) unsigned NOT NULL default '0',
  `is_private` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `tag` (`tag`),
  KEY `object_id` (`rel_object_manager`, `rel_object_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>users` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `company_id` smallint(5) unsigned NOT NULL default '0',
  `personal_project_id` int(10) unsigned NOT NULL default '0',
  `username` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `email` varchar(100) <?php echo $default_collation ?> default NULL,
  `type` varchar(10) <?php echo $default_collation ?> default NULL DEFAULT 'normal',
  `token` varchar(40) <?php echo $default_collation ?> NOT NULL default '',
  `salt` varchar(13) <?php echo $default_collation ?> NOT NULL default '',
  `twister` varchar(10) <?php echo $default_collation ?> NOT NULL default '',
  `display_name` varchar(50) <?php echo $default_collation ?> default NULL,
  `title` varchar(30) <?php echo $default_collation ?> default NULL,
  `avatar_file` varchar(44) <?php echo $default_collation ?> default NULL,
  `timezone` float(3,1) NOT NULL default '0.0',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by_id` int(10) unsigned default NULL,
  `updated_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated_by_id` int(10) unsigned default NULL,
  `last_login` datetime NOT NULL default '0000-00-00 00:00:00',
  `last_visit` datetime NOT NULL default '0000-00-00 00:00:00',
  `last_activity` datetime NOT NULL default '0000-00-00 00:00:00',
  	`can_edit_company_data` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
	`can_manage_security` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
	`can_manage_workspaces` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
	`can_manage_configuration` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
	`can_manage_contacts` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
	`can_manage_templates` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0, 
	`can_manage_reports` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
	`can_manage_time` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
	`can_add_mail_accounts` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0, 
  `auto_assign` tinyint(1) unsigned NOT NULL default '0',
  `default_billing_id` int(10) unsigned default 0,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `last_visit` (`last_visit`),
  KEY `company_id` (`company_id`),
  KEY `last_login` (`last_login`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>contacts`(
	`id` int(10) unsigned NOT NULL auto_increment,
	`firstname` varchar(50) <?php echo $default_collation ?> default NULL,
	`lastname` varchar(50) <?php echo $default_collation ?> default NULL,
	`middlename` varchar(50) <?php echo $default_collation ?> default NULL,
	`department` varchar(50) <?php echo $default_collation ?> default NULL,
	`job_title` varchar(50) <?php echo $default_collation ?> default NULL,
	`company_id` int(10) <?php echo $default_collation ?> default NULL,
	`email` varchar(100) <?php echo $default_collation ?> default NULL,
	`email2` varchar(100) <?php echo $default_collation ?> default NULL,
	`email3` varchar(100) <?php echo $default_collation ?> default NULL,
	`w_web_page` text <?php echo $default_collation ?> ,
	`w_address` varchar(200) <?php echo $default_collation ?> default NULL,
	`w_city` varchar(50) <?php echo $default_collation ?> default NULL,
	`w_state` varchar(50) <?php echo $default_collation ?> default NULL,
	`w_zipcode` varchar(50) <?php echo $default_collation ?> default NULL,
	`w_country` varchar(50) <?php echo $default_collation ?> default NULL,
    `w_phone_number` varchar(50) <?php echo $default_collation ?> default NULL,
    `w_phone_number2` varchar(50) <?php echo $default_collation ?> default NULL,
    `w_fax_number` varchar(50) <?php echo $default_collation ?> default NULL,
    `w_assistant_number` varchar(50) <?php echo $default_collation ?> default NULL,
    `w_callback_number` varchar(50) <?php echo $default_collation ?> default NULL,
	`h_web_page` text <?php echo $default_collation ?> ,
	`h_address` varchar(200) <?php echo $default_collation ?> default NULL,
	`h_city` varchar(50) <?php echo $default_collation ?> default NULL,
	`h_state` varchar(50) <?php echo $default_collation ?> default NULL,
	`h_zipcode` varchar(50) <?php echo $default_collation ?> default NULL,
	`h_country` varchar(50) <?php echo $default_collation ?> default NULL,
    `h_phone_number` varchar(50) <?php echo $default_collation ?> default NULL,
    `h_phone_number2` varchar(50) <?php echo $default_collation ?> default NULL,
    `h_fax_number` varchar(50) <?php echo $default_collation ?> default NULL,
    `h_mobile_number` varchar(50) <?php echo $default_collation ?> default NULL,
    `h_pager_number` varchar(50) <?php echo $default_collation ?> default NULL,
	`o_web_page` text <?php echo $default_collation ?> ,
	`o_address` varchar(200) <?php echo $default_collation ?> default NULL,
	`o_city` varchar(50) <?php echo $default_collation ?> default NULL,
	`o_state` varchar(50) <?php echo $default_collation ?> default NULL,
	`o_zipcode` varchar(50) <?php echo $default_collation ?> default NULL,
	`o_country` varchar(50) <?php echo $default_collation ?> default NULL,
    `o_phone_number` varchar(50) <?php echo $default_collation ?> default NULL,
    `o_phone_number2` varchar(50) <?php echo $default_collation ?> default NULL,
    `o_fax_number` varchar(50) <?php echo $default_collation ?> default NULL,
    `o_birthday` datetime default NULL,
    `picture_file` varchar(44) <?php echo $default_collation ?> default NULL,
	`timezone` float(3,1) NOT NULL default '0.0',
	`notes` text <?php echo $default_collation ?> ,
	`user_id` int(10),
	`is_private` tinyint(1) unsigned NOT NULL default '0',	
    `created_on`  datetime NOT NULL default '0000-00-00 00:00:00',
	`created_by_id` int(10) unsigned NOT NULL default '0',
    `updated_on`  datetime NOT NULL default '0000-00-00 00:00:00',
	`updated_by_id` int(10) unsigned NOT NULL default '0',
	`trashed_on` datetime NOT NULL default '0000-00-00 00:00:00',
	`trashed_by_id` int(10) unsigned default NULL,
    `archived_on` datetime NOT NULL default '0000-00-00 00:00:00',
    `archived_by_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`),
  KEY user_id (`user_id`),
  KEY created_by_id (`created_by_id`),
  KEY company_id (`company_id`),
  KEY `trashed_on` USING BTREE (`trashed_on`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE  `<?php echo $table_prefix ?>contact_im_values` (
  `contact_id` int(10) unsigned NOT NULL default '0',
  `im_type_id` tinyint(3) unsigned NOT NULL default '0',
  `value` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `is_default` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`contact_id`,`im_type_id`),
  KEY `is_default` (`is_default`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE  `<?php echo $table_prefix ?>project_contacts` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `contact_id` int(10) unsigned NOT NULL default '0',
  `project_id` int(10) unsigned NOT NULL default '0',
  `role` varchar(255) <?php echo $default_collation ?> default '',
  PRIMARY KEY  (`id`),
  INDEX `contact_project_ids` (`contact_id`, `project_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE  `<?php echo $table_prefix ?>project_webpages` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `url` text <?php echo $default_collation ?>  ,
  `title` varchar(100) <?php echo $default_collation ?> default '',
  `description` text <?php echo $default_collation ?> ,
  `created_on` datetime default NULL,
  `created_by_id` int(10) unsigned NOT NULL default '0',	
  `updated_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated_by_id` int(10) unsigned default NULL,
  `trashed_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `trashed_by_id` int(10) unsigned default NULL,
  `is_private` tinyint(1) unsigned NOT NULL default '0',
  `archived_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `archived_by_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`),
  KEY `trashed_on` USING BTREE (`trashed_on`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE  `<?php echo $table_prefix ?>mail_contents` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `account_id` int(10) unsigned NOT NULL default '0',
  `uid` varchar(255) <?php echo $default_collation ?> NOT NULL default '',
  `from` varchar(100) <?php echo $default_collation ?> NOT NULL default '',
  `from_name` VARCHAR( 250 ) NULL,
  `sent_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `received_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `subject` varchar(255) <?php echo $default_collation ?> NOT NULL default '',
  `has_attachments` int(1) NOT NULL default '0',
  `size` int(10) NOT NULL default '0',
  `state` INT( 1 ) NOT NULL DEFAULT '0' COMMENT '0:nothing, 1:sent, 2:draft',
  `is_deleted` int(1) NOT NULL default '0',
  `is_shared` INT(1) NOT NULL default '0',
  `is_private` INT(1) NOT NULL default 0,
  `created_on` datetime default NULL,
  `created_by_id` int(10) unsigned NOT NULL default '0',
  `trashed_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `trashed_by_id` int(10) unsigned default NULL,
  `imap_folder_name` varchar(100) <?php echo $default_collation ?> NOT NULL default '',
  `account_email` varchar(100) <?php echo $default_collation ?> default '',
  `content_file_id` varchar(40) <?php echo $default_collation ?> NOT NULL default '',
  `archived_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `archived_by_id` int(10) unsigned default NULL,
  `message_id` varchar(255) <?php echo $default_collation ?> NOT NULL COMMENT 'Message-Id header',
  `in_reply_to_id` varchar(255) <?php echo $default_collation ?> NOT NULL COMMENT 'Message-Id header of the previous email in the conversation',
  `conversation_id` int(10) unsigned NOT NULL default '0',
  `sync` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `account_id` (`account_id`),
  KEY `sent_date` (`sent_date`),
  KEY `received_date` (`received_date`),
  KEY `uid` (`uid`),
  KEY `conversation_id` (`conversation_id`),
  KEY `message_id` (`message_id`),
  KEY `state` (`state`),
  KEY `trashed_on` USING BTREE (`trashed_on`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE  `<?php echo $table_prefix ?>mail_datas` (
  `id` int(10) unsigned NOT NULL,
  `to` text <?php echo $default_collation ?> NOT NULL,
  `cc` text <?php echo $default_collation ?> NOT NULL,
  `bcc` text <?php echo $default_collation ?> NOT NULL,
  `subject` text <?php echo $default_collation ?>,
  `content` text <?php echo $default_collation ?>,
  `body_plain` longtext <?php echo $default_collation ?>,
  `body_html` longtext <?php echo $default_collation ?>,
  PRIMARY KEY (`id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE  `<?php echo $table_prefix ?>mail_accounts` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(10) unsigned NOT NULL default '0',
  `name` varchar(40) <?php echo $default_collation ?> NOT NULL default '',
  `email` varchar(100) <?php echo $default_collation ?> default '',
  `email_addr` VARCHAR( 100 ) <?php echo $default_collation ?> NOT NULL default '',
  `password` varchar(40) <?php echo $default_collation ?> default '',
  `server` varchar(100) <?php echo $default_collation ?> NOT NULL default '',
  `is_imap` int(1) NOT NULL default '0',
  `incoming_ssl` int(1) NOT NULL default '0',
  `incoming_ssl_port` int default '995',
  `smtp_server` VARCHAR(100) <?php echo $default_collation ?> NOT NULL default '',
  `smtp_use_auth` INTEGER UNSIGNED NOT NULL default 0,
  `smtp_username` VARCHAR(100) <?php echo $default_collation ?>,
  `smtp_password` VARCHAR(100) <?php echo $default_collation ?>,
  `smtp_port` INTEGER UNSIGNED NOT NULL default 25,
  `del_from_server` INTEGER NOT NULL default 0,
  `outgoing_transport_type` VARCHAR(5) <?php echo $default_collation ?> NOT NULL default '',
  `last_checked` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `is_default` BOOLEAN NOT NULL default '0',
  `signature` text <?php echo $default_collation ?> NOT NULL,
  `workspace` INT(10) NOT NULL default 0,
  `sender_name` varchar(100) <?php echo $default_collation ?> NOT NULL default '',
  `last_error_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_error_msg` varchar(255) <?php echo $default_collation ?> NOT NULL,
  `sync_addr` varchar(100) <?php echo $default_collation ?> NOT NULL,
  `sync_pass` varchar(40) <?php echo $default_collation ?> NOT NULL,
  `sync_server` varchar(100) <?php echo $default_collation ?> NOT NULL,
  `sync_ssl` tinyint(1) NOT NULL default '0',
  `sync_ssl_port` int(11) NOT NULL default '993',
  `sync_folder` varchar(100) <?php echo $default_collation ?> NOT NULL,
  PRIMARY KEY  (`id`),
  INDEX `user_id` (`user_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE  `<?php echo $table_prefix ?>mail_account_imap_folder` (
  `account_id` int(10) unsigned NOT NULL default '0',
  `folder_name` varchar(100) <?php echo $default_collation ?> NOT NULL default '',
  `check_folder` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`account_id`,`folder_name`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

-- save gui state
CREATE TABLE  `<?php echo $table_prefix ?>guistate` (
  `user_id` int(10) unsigned NOT NULL default '1',
  `name` varchar(100) NOT NULL,
  `value` text NOT NULL
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>workspace_objects` (
  `workspace_id` int(10) unsigned NOT NULL default 0,
  `object_manager` varchar(50) NOT NULL default '',
  `object_id` int(10) unsigned NOT NULL default 0,
  `created_by_id` int(10) unsigned default NULL,
  `created_on` datetime default NULL,
  PRIMARY KEY  (`workspace_id`, `object_manager`, `object_id`),
  KEY `workspace_id` (`workspace_id`),
  KEY `object_manager` (`object_manager`, `object_id`),
  KEY `object_id` (`object_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE  `<?php echo $table_prefix ?>project_charts` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `type_id` int(10) unsigned default NULL,
  `display_id` int(10) unsigned default NULL,
  `title` varchar(100) <?php echo $default_collation ?> default NULL,
  `show_in_project` tinyint(1) unsigned NOT NULL default '1',
  `show_in_parents` tinyint(1) unsigned NOT NULL default '0',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by_id` int(10) unsigned default NULL,
  `updated_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated_by_id` int(10) unsigned default NULL,
  `trashed_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `trashed_by_id` int(10) unsigned default NULL,
  `archived_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `archived_by_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE  `<?php echo $table_prefix ?>project_chart_params` (
  `id` int(10) unsigned NOT NULL,
  `chart_id` int(10) unsigned NOT NULL,
  `value` varchar(80) NOT NULL,
  PRIMARY KEY  (`id`,`chart_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE  `<?php echo $table_prefix ?>timeslots` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `object_id` int(10) unsigned NOT NULL,
  `object_manager` varchar(50) <?php echo $default_collation ?> NOT NULL,
  `start_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `end_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `user_id` int(10) unsigned NOT NULL,
  `description` text <?php echo $default_collation ?> NOT NULL,
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by_id` int(10) unsigned NOT NULL,
  `updated_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated_by_id` int(10) unsigned NOT NULL,
  `paused_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `subtract` int(10) unsigned NOT NULL default '0',
  `fixed_billing` float NOT NULL default '0',
  `hourly_billing` float NOT NULL default '0',
  `is_fixed_billing` float NOT NULL default '0',
  `billing_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  INDEX `ObjectID` (`object_id`,`object_manager`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>read_objects` (
  `rel_object_manager` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `rel_object_id` int(10) unsigned NOT NULL default '0',
  `user_id` int(10) unsigned NOT NULL default '0',
  `is_read` int(1) NOT NULL default '0',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`rel_object_manager`,`rel_object_id`,`user_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>user_ws_config_categories` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `name` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `is_system` tinyint(1) unsigned NOT NULL default '0',
  `type` tinyint(3) unsigned NOT NULL default '0',
  `category_order` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `order` (`category_order`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>user_ws_config_options` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `category_name` varchar(30) <?php echo $default_collation ?> NOT NULL default '',
  `name` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `default_value` text <?php echo $default_collation ?>,
  `config_handler_class` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `is_system` tinyint(1) unsigned NOT NULL default '0',
  `option_order` smallint(5) unsigned NOT NULL default '0',
  `dev_comment` varchar(255) <?php echo $default_collation ?> default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `order` (`option_order`),
  KEY `category_id` (`category_name`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>user_ws_config_option_values` (
  `option_id` int(10) unsigned NOT NULL default '0',
  `user_id` int(10) unsigned NOT NULL default '0',
  `workspace_id` int(10) unsigned NOT NULL default '0',
  `value` text <?php echo $default_collation ?>,
  PRIMARY KEY  (`option_id`,`user_id`,`workspace_id`),
  KEY `option_id` (`option_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>event_invitations` (
  `event_id` int(10) unsigned NOT NULL default '0',
  `user_id` int(10) unsigned NOT NULL default '0',
  `invitation_state` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY (`event_id`, `user_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>templates` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) <?php echo $default_collation ?> NOT NULL default '',
  `description` text <?php echo $default_collation ?>,
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by_id` int(10) unsigned NOT NULL,
  `updated_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated_by_id` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  INDEX `name` (`name`),
  INDEX `updated_on` (`updated_on`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>template_objects` (
  `template_id` int(10) unsigned NOT NULL default '0',
  `object_manager` varchar(50) NOT NULL default '',
  `object_id` int(10) unsigned NOT NULL default 0,
  `created_by_id` int(10) unsigned default NULL,
  `created_on` datetime default NULL,
  PRIMARY KEY  (`template_id`, `object_manager`, `object_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>workspace_templates` (
  `workspace_id` int(10) unsigned NOT NULL default 0,
  `template_id` int(10) unsigned NOT NULL default 0,
  `include_subws` int(1) unsigned NOT NULL default 0,
  `created_by_id` int(10) unsigned default NULL,
  `created_on` datetime default NULL,
  PRIMARY KEY  (`workspace_id`, `template_id`),
  INDEX `workspace_id` (`workspace_id`),
  INDEX `object_id` (`template_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

-- GelSheet

CREATE TABLE  `<?php echo $table_prefix ?>gs_books` (
  `BookId` int(10) unsigned NOT NULL auto_increment,
  `BookName` varchar(45) <?php echo $default_collation ?> NOT NULL default '',
  `UserId` int(10) unsigned NOT NULL COMMENT 'Book Owner',
  PRIMARY KEY  (`BookId`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?> AUTO_INCREMENT=189809 COMMENT='System Workbooks';

CREATE TABLE  `<?php echo $table_prefix ?>gs_cells` (
  `SheetId` int(10) unsigned NOT NULL,
  `DataColumn` int(10) unsigned NOT NULL,
  `DataRow` int(10) unsigned NOT NULL,
  `CellFormula` varchar(255) <?php echo $default_collation ?> default NULL,
  `CellValue` text <?php echo $default_collation ?> NOT NULL,
  `FontStyleId` int(10) unsigned NOT NULL default '0',
  `LayoutStyleId` int(11) NOT NULL default '0',
  PRIMARY KEY  (`SheetId`,`DataColumn`,`DataRow`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?> COMMENT='Sheet data';

CREATE TABLE  `<?php echo $table_prefix ?>gs_columns` (
  `SheetId` int(11) NOT NULL,
  `ColumnIndex` int(11) NOT NULL,
  `ColumnSize` int(11) NOT NULL,
  `FontStyleId` int(11) NOT NULL,
  `LayerStyleId` int(11) NOT NULL,
  `LayoutStyleId` int(11) NOT NULL,
  PRIMARY KEY  (`SheetId`,`ColumnIndex`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE  `<?php echo $table_prefix ?>gs_fontstyles` (
  `FontStyleId` int(11) NOT NULL,
  `BookId` int(11) NOT NULL,
  `FontId` int(11) NOT NULL,
  `FontSize` decimal(8,1) NOT NULL default '10.0',
  `FontBold` tinyint(1) NOT NULL default '0',
  `FontItalic` tinyint(1) NOT NULL default '0',
  `FontUnderline` tinyint(1) NOT NULL default '0',
  `FontColor` varchar(7) <?php echo $default_collation ?> NOT NULL default '',
  `FontVAlign` int(11) NOT NULL default '0',                                    
  `FontHAlign` int(11) NOT NULL default '0',
  PRIMARY KEY  (`FontStyleId`,`BookId`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE  `<?php echo $table_prefix ?>gs_fonts` (
  `FontId` int(11) NOT NULL auto_increment,
  `FontName` varchar(63) <?php echo $default_collation ?> NOT NULL default '',
  PRIMARY KEY  (`FontId`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?> AUTO_INCREMENT=7;

CREATE TABLE  `<?php echo $table_prefix ?>gs_borderstyles` (
  `BorderStyleId` int(11) NOT NULL auto_increment,
  `BorderColor` varchar(7) <?php echo $default_collation ?>  default NULL,
  `BorderWidth` int(11) NOT NULL DEFAULT 0,
  `BorderStyle` varchar(64) DEFAULT NULL,
  `BookId` int(11) DEFAULT NULL, 
  PRIMARY KEY  (`BorderStyleId`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>gs_layoutstyles` (                          
  `LayoutStyleId` int(11) NOT NULL AUTO_INCREMENT,        
  `BorderLeftStyleId` int(11) DEFAULT NULL,               
  `BackgroundColor` varchar(7) <?php echo $default_collation ?> DEFAULT NULL,              
  `BorderRightStyleId` int(11) DEFAULT NULL,              
  `BorderTopStyleId` int(11) DEFAULT NULL,                
  `BorderBottomStyleId` int(11) DEFAULT NULL,             
  `BookId` int(11) DEFAULT NULL,                          
  PRIMARY KEY (`LayoutStyleId`)                           
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE  `<?php echo $table_prefix ?>gs_mergedcells` (
  `SheetId` int(11) NOT NULL,
  `MergedCellRow` int(11) NOT NULL,
  `MergedCellCol` int(11) NOT NULL,
  `MergedRows` int(11) default NULL,
  `MergedCols` int(11) default NULL
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE  `<?php echo $table_prefix ?>gs_rows` (
  `SheetId` int(11) NOT NULL,
  `RowIndex` int(11) NOT NULL,
  `RowSize` int(11) NOT NULL,
  `FontStyleId` int(11) NOT NULL,
  `LayerStyleId` int(11) NOT NULL,
  `LayoutStyleId` int(11) NOT NULL,
  PRIMARY KEY  (`SheetId`,`RowIndex`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE  `<?php echo $table_prefix ?>gs_sheets` (
  `SheetId` int(10) unsigned NOT NULL auto_increment,
  `BookId` int(10) unsigned NOT NULL,
  `SheetName` varchar(45) <?php echo $default_collation ?> NOT NULL default '',
  `SheetIndex` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`SheetId`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?> AUTO_INCREMENT=1142 COMMENT='Workbooks Sheets';

CREATE TABLE  `<?php echo $table_prefix ?>gs_userbooks` (
  `UserBookId` int(10) unsigned NOT NULL auto_increment,
  `UserId` int(10) unsigned NOT NULL,
  `BookId` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`UserBookId`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE  `<?php echo $table_prefix ?>gs_users` (
  `UserId` int(10) unsigned NOT NULL auto_increment,
  `UserName` varchar(45) <?php echo $default_collation ?> NOT NULL default '',
  `UserLastName` varchar(45) <?php echo $default_collation ?> NOT NULL default '',
  `UserNickname` varchar(45) <?php echo $default_collation ?> NOT NULL default '',
  `UserPassword` varchar(45) <?php echo $default_collation ?> NOT NULL default '',
  `LanguageId` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`UserId`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?> AUTO_INCREMENT=4 COMMENT='Sytem Users';

CREATE TABLE `<?php echo $table_prefix ?>cron_events` (
	`id` int(10) unsigned NOT NULL auto_increment,
	`name` varchar(45) <?php echo $default_collation ?> NOT NULL default '',
	`recursive` boolean NOT NULL default '1',
	`delay` int(10) unsigned NOT NULL default 0,
	`is_system` boolean NOT NULL default '0',
	`enabled` boolean NOT NULL default '1',
	`date` datetime NOT NULL default '0000-00-00 00:00:00',
	PRIMARY KEY (`id`),
	UNIQUE KEY `uk_name` (`name`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE  `<?php echo $table_prefix ?>billing_categories` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) <?php echo $default_collation ?> default '',
  `description` text <?php echo $default_collation ?>,
  `default_value` float NOT NULL default 0,
  `report_name` varchar(100) <?php echo $default_collation ?> default '',
  `created_on` datetime default NULL,
  `created_by_id` int(10) unsigned NOT NULL default '0',
  `updated_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated_by_id` int(10) unsigned default NULL,
 PRIMARY KEY  (`id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE  `<?php echo $table_prefix ?>workspace_billings` (
  `project_id` int(10) unsigned NOT NULL,
  `billing_id` int(10) unsigned NOT NULL,
  `value` float NOT NULL default 0,
  `created_on` datetime default NULL,
  `created_by_id` int(10) unsigned NOT NULL default '0',
  `updated_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated_by_id` int(10) unsigned default NULL,
 PRIMARY KEY  (`project_id`, `billing_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE  `<?php echo $table_prefix ?>shared_objects` (
  `object_id` INTEGER UNSIGNED NOT NULL,
  `object_manager` VARCHAR(45) NOT NULL,
  `user_id` INTEGER UNSIGNED NOT NULL,
  `created_on` DATETIME NOT NULL,
  `created_by_id` INTEGER UNSIGNED NOT NULL,
  PRIMARY KEY (`object_id`, `object_manager`, `user_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>user_passwords` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL,
  `password` varchar(40) NOT NULL,
  `password_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>custom_properties` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `object_type` varchar(255) <?php echo $default_collation ?> NOT NULL,
  `name` varchar(255) <?php echo $default_collation ?> NOT NULL,
  `type` varchar(255) <?php echo $default_collation ?> NOT NULL,
  `description` text <?php echo $default_collation ?> NOT NULL,
  `values` text <?php echo $default_collation ?> NOT NULL,
  `default_value` varchar(255) <?php echo $default_collation ?> NOT NULL,
  `is_required` tinyint(1) NOT NULL,
  `is_multiple_values` tinyint(1) NOT NULL,
  `property_order` int(10) NOT NULL,
  `visible_by_default` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX ( `object_type` )
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE IF NOT EXISTS `<?php echo $table_prefix ?>custom_property_values` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `object_id` int(10) NOT NULL,
  `custom_property_id` int(10) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX ( `object_id` ),
  INDEX ( `custom_property_id` )
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE `<?php echo $table_prefix ?>queued_emails` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `to` text <?php echo $default_collation ?>,
  `from` text <?php echo $default_collation ?>,
  `subject` text <?php echo $default_collation ?>,
  `body` text <?php echo $default_collation ?>,
  `timestamp` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;


CREATE TABLE IF NOT EXISTS `<?php echo $table_prefix ?>reports` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) <?php echo $default_collation ?> NOT NULL,
  `description` varchar(255) <?php echo $default_collation ?> NOT NULL,
  `object_type` varchar(255) <?php echo $default_collation ?> NOT NULL,
  `order_by` varchar(255) <?php echo $default_collation ?> NOT NULL,
  `is_order_by_asc` tinyint(1) <?php echo $default_collation ?> NOT NULL,
  `workspace` INTEGER <?php echo $default_collation ?> NOT NULL,
  `tags` varchar(45) <?php echo $default_collation ?> NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE IF NOT EXISTS `<?php echo $table_prefix ?>report_columns` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `report_id` int(10) NOT NULL,
  `custom_property_id` int(10) NOT NULL,
  `field_name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE IF NOT EXISTS `<?php echo $table_prefix ?>report_conditions` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `report_id` int(10) NOT NULL,
  `custom_property_id` int(10) NOT NULL,
  `field_name` varchar(255) <?php echo $default_collation ?> NOT NULL,
  `condition` varchar(255) <?php echo $default_collation ?> NOT NULL,
  `value` varchar(255) <?php echo $default_collation ?> NOT NULL,
  `is_parametrizable` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE IF NOT EXISTS `<?php echo $table_prefix ?>template_parameters` (
`id` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`template_id` INT( 10 ) NOT NULL ,
`name` VARCHAR( 255 ) <?php echo $default_collation ?> NOT NULL ,
`type` VARCHAR( 255 ) <?php echo $default_collation ?> NOT NULL
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE IF NOT EXISTS `<?php echo $table_prefix ?>template_object_properties` (
`template_id` INT( 10 ) NOT NULL ,
`object_id` INT( 10 ) NOT NULL ,
`object_manager` varchar(50) NOT NULL,
`property` VARCHAR( 255 ) <?php echo $default_collation ?> NOT NULL ,
`value` TEXT NOT NULL ,
PRIMARY KEY ( `template_id` , `object_id` ,`object_manager`, `property` )
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE IF NOT EXISTS `<?php echo $table_prefix ?>mail_account_users` (
 `id` INT(10) NOT NULL AUTO_INCREMENT,
 `account_id` INT(10) NOT NULL,
 `user_id` INT(10) NOT NULL,
 `can_edit` BOOLEAN NOT NULL default '0',
 `is_default` BOOLEAN NOT NULL default '0',
 `signature` text <?php echo $default_collation ?> NOT NULL,
 `sender_name` varchar(100) <?php echo $default_collation ?> NOT NULL default '',
 `last_error_state` int(1) unsigned NOT NULL default '0' COMMENT '0:no error,1:err unread, 2:err read',
 PRIMARY KEY (`id`),
 UNIQUE KEY `uk_useracc` (`account_id`, `user_id`),
 KEY `ix_account` (`account_id`),
 KEY `ix_user` (`user_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE IF NOT EXISTS `<?php echo $table_prefix ?>mail_conversations` (
 `id` INT(10) NOT NULL AUTO_INCREMENT,
 PRIMARY KEY (`id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE IF NOT EXISTS `<?php echo $table_prefix ?>project_co_types` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `object_manager` varchar(45) NOT NULL,
  `name` varchar(45) NOT NULL,
  `created_by_id` int(10) unsigned NOT NULL,
  `created_on` datetime NOT NULL,
  `updated_by_id` int(10) unsigned NOT NULL,
  `updated_on` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `object_manager` (`object_manager`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE IF NOT EXISTS `<?php echo $table_prefix ?>custom_properties_by_co_type` (
  `co_type_id` INTEGER UNSIGNED NOT NULL,
  `cp_id` INTEGER UNSIGNED NOT NULL,
  PRIMARY KEY (`co_type_id`, `cp_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE  `<?php echo $table_prefix ?>application_read_logs` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `taken_by_id` int(10) NOT NULL default '0',
  `rel_object_id` int(10) NOT NULL default '0',
  `rel_object_manager` varchar(50) <?php echo $default_collation ?> NOT NULL default '',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by_id` int(10) unsigned default NULL,
  `action` enum('read','download') <?php echo $default_collation ?> default NULL,
  PRIMARY KEY  (`id`),
  KEY `created_on` (`created_on`),
  KEY `object_key` (`rel_object_manager`, `rel_object_id`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;

CREATE TABLE  `<?php echo $table_prefix ?>administration_logs` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `title` varchar(50) NOT NULL default '',
  `log_data` text NOT NULL,
  `category` enum('system','security') NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `created_on` (`created_on`),
  KEY `category` (`category`)
) ENGINE=<?php echo $engine ?> <?php echo $default_charset ?>;
