<?php

/**
 * ProjectTaskDependencies class
 * 
 * @author Alvaro Torterola <alvaro.torterola@fengoffice.com>
 */
class  ProjectTaskDependencies extends BaseProjectTaskDependencies {

	static function getDependenciesForTask($task_id) {
		return self::findAll(array('conditions' => '`task_id` = ' . $task_id . " AND 0 = (SELECT `trashed_by_id` FROM `".TABLE_PREFIX."objects` WHERE `id`=`previous_task_id`)"));
	}
	
	static function countPreviousTasks($task_id) {
		return self::count('`task_id` = ' . $task_id . " AND 0 = (SELECT `trashed_by_id` FROM `".TABLE_PREFIX."objects` WHERE `id`=`previous_task_id`)");
	}
	
	static function getDependantsForTask($task_id) {
		return self::findAll(array('conditions' => '`previous_task_id` = ' . $task_id . " AND 0 = (SELECT `trashed_by_id` FROM `".TABLE_PREFIX."objects` WHERE `id`=`task_id`)"));
	}

} // ProjectTaskDependencies

?>