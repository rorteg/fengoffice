INSERT INTO `<?php echo $table_prefix ?>dimensions` (`code`, `name`, `is_root`, `is_manageable`, `allows_multiple_selection`, `defines_permissions`, `is_system`,`default_order`, `options` ) VALUES
 ('workspaces', 'Workspaces', 1, 1, 0, 1, 1,-10,'{"defaultAjax":{"controller":"dashboard", "action": "main_dashboard"}, "quickAdd":true,"showInPaths":true}'),
 ('tags', 'Tags', 1, 1, 0, 0, 1,-9,'{"defaultAjax":{"controller":"dashboard", "action": "init_overview"},"quickAdd":true,"showInPaths":true}');

INSERT INTO `<?php echo $table_prefix ?>dimension_object_types` (`dimension_id`, `object_type_id`, `is_root`,`options` ) VALUES
 ((SELECT `id` FROM `<?php echo $table_prefix ?>dimensions` WHERE `code`='workspaces'), (SELECT `id` FROM `<?php echo $table_prefix ?>object_types` WHERE `name`='workspace'), 1, '{"defaultAjax":{"controller":"dashboard", "action": "main_dashboard"}}'),
 ((SELECT `id` FROM `<?php echo $table_prefix ?>dimensions` WHERE `code`='tags'), (SELECT `id` FROM `<?php echo $table_prefix ?>object_types` WHERE `name`='tag'), 1 ,'');

INSERT INTO `<?php echo $table_prefix ?>dimension_object_type_hierarchies` (`dimension_id`, `parent_object_type_id`, `child_object_type_id`) VALUES
 ((SELECT `id` FROM `<?php echo $table_prefix ?>dimensions` WHERE `code`='workspaces'), (SELECT `id` FROM `<?php echo $table_prefix ?>object_types` WHERE `name`='workspace'), (SELECT `id` FROM `<?php echo $table_prefix ?>object_types` WHERE `name`='workspace'));

INSERT INTO `<?php echo $table_prefix ?>dimension_object_type_contents` (`dimension_id`,`dimension_object_type_id`,`content_object_type_id`, `is_required`, `is_multiple`)
 SELECT 
 	(SELECT `id` FROM `<?php echo $table_prefix ?>dimensions` WHERE `code`='workspaces'),
 	(SELECT `id` FROM `<?php echo $table_prefix ?>object_types` WHERE `name`='workspace'),
 	`id`, 0, 1
 FROM `<?php echo $table_prefix ?>object_types` 
 WHERE `type` IN ('content_object', 'comment', 'located')
 ON DUPLICATE KEY UPDATE dimension_id=dimension_id;

INSERT INTO `<?php echo $table_prefix ?>dimension_object_type_contents` (`dimension_id`,`dimension_object_type_id`,`content_object_type_id`, `is_required`, `is_multiple`)
 SELECT 
 	(SELECT `id` FROM `<?php echo $table_prefix ?>dimensions` WHERE `code`='tags'),
 	(SELECT `id` FROM `<?php echo $table_prefix ?>object_types` WHERE `name`='tag'),
 	`id`, 0, 1
 FROM `<?php echo $table_prefix ?>object_types` 
 WHERE `type` IN ('content_object', 'comment', 'located')
 ON DUPLICATE KEY UPDATE dimension_id=dimension_id;

INSERT INTO `<?php echo $table_prefix ?>contact_dimension_permissions` (`permission_group_id`, `dimension_id`, `permission_type`) VALUES
 (1, (SELECT `id` FROM `<?php echo $table_prefix ?>dimensions` WHERE `code`='workspaces'), 'allow all');


UPDATE `<?php echo $table_prefix ?>contact_config_options` 
 SET default_value = concat(default_value,',', (SELECT `id` FROM `<?php echo $table_prefix ?>dimensions` WHERE `code`='workspaces') ) 
 WHERE name='root_dimensions';


INSERT INTO `<?php echo $table_prefix ?>contact_dimension_permissions` (permission_group_id, dimension_id, permission_type)
  SELECT DISTINCT(permission_group_id), (SELECT id FROM `<?php echo $table_prefix ?>dimensions` WHERE code = 'workspaces'), 'allow all'
  FROM <?php echo $table_prefix ?>contacts WHERE user_type IN (SELECT id FROM `<?php echo $table_prefix ?>permission_groups` WHERE name IN ('Super Administrator', 'Administrator'))
ON duplicate key UPDATE dimension_id = dimension_id;

UPDATE `<?php echo $table_prefix ?>tab_panels` SET default_action = 'main_dashboard', initial_action = 'main_dashboard' WHERE id = 'overview-panel' ;

UPDATE <?php echo $table_prefix ?>widgets SET default_section = 'none' WHERE name = 'people' AND NOT EXISTS (SELECT id from <?php echo $table_prefix ?>plugins WHERE name = 'crpm');

INSERT INTO <?php echo $table_prefix ?>widgets(name, title, plugin_id, default_section,default_order) VALUES
 ('ws_description', 'workspace description', (SELECT id from <?php echo $table_prefix ?>plugins WHERE name = 'workspaces'), 'top', -100),
 ('workspaces', 'workspaces', (SELECT id from <?php echo $table_prefix ?>plugins WHERE name = 'workspaces'), 'right', 1)
ON DUPLICATE KEY update name = name ;

