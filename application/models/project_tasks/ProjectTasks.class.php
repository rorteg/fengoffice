<?php

/**
 * ProjectTasks, generated on Sat, 04 Mar 2006 12:50:11 +0100 by
 * DataObject generation tool
 *
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class ProjectTasks extends BaseProjectTasks {

	const ORDER_BY_ORDER = 'order';
	const ORDER_BY_STARTDATE = 'startDate';
	const ORDER_BY_DUEDATE = 'dueDate';
	const PRIORITY_URGENT = 400;
	const PRIORITY_HIGH = 300;
	const PRIORITY_NORMAL = 200;
	const PRIORITY_LOW = 100;
	

	public static function getWorkspaceString($ids = '?', $table_alias = '') {
		if ($table_alias) $table_alias .= ".";
		return " $table_alias`id` IN (SELECT `object_id` FROM `" . TABLE_PREFIX . "workspace_objects` WHERE `object_manager` = 'ProjectTasks' AND `workspace_id` IN ($ids)) ";
	}
	
	/**
	 * Return tasks lists for the next two weeks which don't have due date and have not been completed.
	 *
	 * @param Project $project
	 * @return array
	 */	
	static function getPendingTasks(User $user, $project, $tag = null, $archived = false) {
		if ($project instanceof Project) {
			$project_ids = $project->getAllSubWorkspacesQuery(!$archived);
			$wsstring = ' AND ' . self::getWorkspaceString($project_ids);
		} else {
			$wsstring = "";
		}
		
		if ($archived) $archived_cond = " AND `archived_by_id` <> 0";
		else $archived_cond = " AND `archived_by_id` = 0";

		$permissions = ' AND ( ' . permissions_sql_for_listings(ProjectTasks::instance(),ACCESS_LEVEL_READ, logged_user(), 'project_id') .')';
		$tagStr = $tag? (" AND id in (SELECT rel_object_id from " . TABLE_PREFIX . "tags t WHERE tag='".$tag."' AND t.rel_object_manager='ProjectTasks')"):'';
	
		$objects = self::findAll(array(
				'conditions' => array('((`assigned_to_user_id` = ? AND `assigned_to_company_id` = ? ) ' .
					' OR (`assigned_to_user_id` = ? AND `assigned_to_company_id` = ?) ' .
					' OR (`assigned_to_user_id` = ? AND `assigned_to_company_id` = ?)) '.
					' AND `completed_on` = ? AND parent_id = ? AND (due_date > DATE(CURRENT_TIMESTAMP) OR due_date = \'00:00:00 00-00-0000\')' .
					' AND `is_template` = false ' .
					$wsstring .
					$archived_cond . $permissions . $tagStr, $user->getId(), $user->getCompanyId(),
					0, $user->getCompanyId(), 0, 0, EMPTY_DATETIME,0, EMPTY_DATETIME),
        			'order' => 'priority DESC, `created_on` DESC'
        			));
        return $objects;
	} // getAllFilesByProject
	
	/**
	 * Returns unworked pending tasks 
	 *
	 * @param Project $project
	 * @return array
	 */
	static function getUnworkedPendingTasks(User $user, $project, $tag = null, $archived = false, $start_date = null, $end_date = null) {		
		if ($project instanceof Project) {
			$project_ids = $project->getAllSubWorkspacesQuery(!$archived);
			$wsstring = ' AND ' . self::getWorkspaceString($project_ids);
		} else {
			$wsstring = "";
		}
		
		$created_on_limit = (($start_date == null) || ($end_date ==null)) ? "" : " AND `created_on` >= ? AND `created_on` <= ? ";
			
		if ($archived) $archived_cond = " AND `archived_by_id` <> 0";
		else $archived_cond = " AND `archived_by_id` = 0";

		$permissions = ' AND ( ' . permissions_sql_for_listings(ProjectTasks::instance(),ACCESS_LEVEL_READ, logged_user(), 'project_id') .')';
		$tagStr = $tag? (" AND id in (SELECT rel_object_id from " . TABLE_PREFIX . "tags t WHERE tag='".$tag."' AND t.rel_object_manager='ProjectTasks')"):'';

		$only_unworked = "AND id in (SELECT DISTINCT `" . TABLE_PREFIX . "project_tasks`.`id` FROM `" . TABLE_PREFIX . "project_tasks`,  `" . TABLE_PREFIX . "timeslots` WHERE  `" . TABLE_PREFIX . "project_tasks`.`id` != `" . TABLE_PREFIX . "timeslots`.`object_id`)";
		
		if ($created_on_limit == ""){
			$pendingTasks = self::findAll(array(
					'conditions' => array('((`assigned_to_user_id` = ? AND `assigned_to_company_id` = ? ) ' .
						' OR (`assigned_to_user_id` = ? AND `assigned_to_company_id` = ?) ' .
						' OR (`assigned_to_user_id` = ? AND `assigned_to_company_id` = ?)) '.
						' AND `completed_on` = ? AND parent_id = ? AND (due_date > DATE(CURRENT_TIMESTAMP) OR due_date = \'00:00:00 00-00-0000\')' .
						' AND `is_template` = false ' .					
						$wsstring .	$archived_cond . $permissions . $tagStr .$only_unworked, $user->getId(), $user->getCompanyId(),
						0, $user->getCompanyId(), 0, 0, EMPTY_DATETIME,0),
	        			'order' => 'priority DESC, `created_on` DESC'
	        			));
		}else{
			$pendingTasks = self::findAll(array(
				'conditions' => array('((`assigned_to_user_id` = ? AND `assigned_to_company_id` = ? ) ' .
					' OR (`assigned_to_user_id` = ? AND `assigned_to_company_id` = ?) ' .
					' OR (`assigned_to_user_id` = ? AND `assigned_to_company_id` = ?)) '.
					' AND `completed_on` = ? AND parent_id = ? AND (due_date > DATE(CURRENT_TIMESTAMP) OR due_date = \'00:00:00 00-00-0000\')' .
					' AND `is_template` = false ' .					
					$wsstring .
					$archived_cond . $permissions . $tagStr.
					$created_on_limit.$only_unworked, $user->getId(), $user->getCompanyId(),
					0, $user->getCompanyId(), 0, 0, EMPTY_DATETIME,0, $start_date, $end_date),
        			'order' => 'priority DESC, `created_on` DESC'
        			));
			
		}		
        return $pendingTasks;
	} 	
	

	/**
	 * Return tasks on which the user has an open timeslot
	 *
	 * @param User $user
	 * @param Project $project
	 * @return array
	 */
	static function getOpenTimeslotTasks(User $user, User $logged_user, $project = null, $tag = null, $assigned_to_company = null, $assigned_to_user = null, $archived = false) {
		if ($project) {
			$project_ids = $project->getAllSubWorkspacesQuery(!$archived);
			$wsstring = " AND " . self::getWorkspaceString($project_ids);
		} else {
			$wsstring = "";
		}

		$openTimeslot = " AND id in (SELECT object_id from " . TABLE_PREFIX . "timeslots t WHERE user_id="
		. $user->getId() . " AND t.object_manager='ProjectTasks' AND t.end_time='" . EMPTY_DATETIME . "')";

		$permissions = ' AND ( ' . permissions_sql_for_listings(ProjectTasks::instance(), ACCESS_LEVEL_READ, logged_user(), 'project_id') .')';
		$tagStr = $tag? (" AND id in (SELECT rel_object_id from " . TABLE_PREFIX . "tags t WHERE tag=".DB::escape($tag)." AND t.rel_object_manager = 'ProjectTasks')"):'';
	

		$assignedToStr = "";
		if ($assigned_to_company) {
			if ($assigned_to_company == -1)
				$assigned_to_company = 0;
			$assignedToStr .= " AND `assigned_to_company_id` = " . DB::escape($assigned_to_company) . " ";
		}
		if ($assigned_to_user) {
			if ($assigned_to_user == -1)
				$assigned_to_user = 0;
			$assignedToStr .= " AND `assigned_to_user_id` = " . DB::escape($assigned_to_user) . " ";
		}
		
		if ($archived) $archived_cond = "`archived_by_id` <> 0 ";
		else $archived_cond = "`archived_by_id` = 0 ";
		
		$objects = self::findAll(array(
  				'conditions' => array('`is_template` = false AND ' . $archived_cond . $wsstring . $permissions . $tagStr . $assignedToStr . $openTimeslot),
        			'order' => 'due_date ASC, `created_on` DESC'
        			));
        			return $objects;
	} // getAllFilesByProject

	/*
	 * Return tasks for the next two weeks
	 *
	 * @param Project $project
	 * @return array
	 */
	static function getTasksForTwoWeeks() {
		$user =  logged_user();

		$permissions = ' AND ( ' . permissions_sql_for_listings(ProjectTasks::instance(),ACCESS_LEVEL_READ, logged_user(), 'project_id') .')';
		$objects = self::findAll(array(
  				'conditions' => array('((`assigned_to_user_id` = ? AND `assigned_to_company_id` = ? ) ' .
			  		' OR (`assigned_to_user_id` = ? AND `assigned_to_company_id` = ?) '.
			  		' OR (`assigned_to_user_id` = ? AND `assigned_to_company_id` = ?)) '.
					' AND `is_template` = false ' .
			  		' AND `completed_on` = ? AND parent_id = ? ' . $permissions, $user->getId(), $user->getCompanyId(),
		0, $user->getCompanyId(), 0, 0, EMPTY_DATETIME,0),
        		'order' => '`created_on`'
        		));
        		return $objects;
	} // getAllFilesByProject

	/**
	 * Return day tasks this user has access to
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getDayTasksByUser(DateTimeValue $date, User $user, $project = null, $tag = null, $assigned_to_company = null, $assigned_to_user = null, $limit = null, $archived = false) {
		if ($project instanceof Project) {
			$project_ids = $project->getAllSubWorkspacesQuery(!$archived);
			$wsstring = " AND " . self::getWorkspaceString($project_ids);
		} else {
			$wsstring = "";
		}
		
		$date = $date->add('h', logged_user()->getTimezone());
		$from_date =   (new DateTimeValue($date->getTimestamp()));
		$from_date = $from_date->beginningOfDay();
		$to_date =  (new DateTimeValue($date->getTimestamp()));
		$to_date = $to_date->endOfDay();
		
		$assignedToStr = "";
		if ($assigned_to_company) {
			if ($assigned_to_company == -1)
				$assigned_to_company = 0;
			$assignedToStr .= " AND `assigned_to_company_id` = " . DB::escape($assigned_to_company) . " ";
		}
		if ($assigned_to_user) {
			if ($assigned_to_user == -1)
				$assigned_to_user = 0;
			$assignedToStr .= " AND `assigned_to_user_id` = " . DB::escape($assigned_to_user) . " ";
		}
		$permissions = ' AND ( ' . permissions_sql_for_listings(ProjectTasks::instance(),ACCESS_LEVEL_READ, logged_user(), 'project_id') .')';

		if ($archived) $archived_cond = "`archived_by_id` <> 0 ";
		else $archived_cond = "`archived_by_id` = 0 ";
		
		$tagStr = $tag? (" AND id in (SELECT rel_object_id from " . TABLE_PREFIX . "tags t WHERE tag=".DB::escape($tag)." AND t.rel_object_manager='ProjectTasks')"):'';
		if ($limit) {
		  $result = self::findAll(array(
            'conditions' => array('`is_template` = false AND `completed_on` = ? AND (`due_date` >= ? AND `due_date` < ?) AND ' . $archived_cond . $wsstring . $tagStr . $permissions . $assignedToStr, EMPTY_DATETIME, $from_date, $to_date),
    		'limit' => $limit
	    	)); // findAll
		} else {
			$result = self::findAll(array(
        	  'conditions' => array('`is_template` = false AND `completed_on` = ? AND (`due_date` >= ? AND `due_date` < ?) AND ' . $archived_cond . $wsstring . $tagStr . $permissions . $assignedToStr, EMPTY_DATETIME, $from_date, $to_date)
    		)); // findAll
		}
		return $result;
	} // getDayTasksByUser

	/**
	 * Return late tasks this user has access to
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getLateTasksByUser(User $user, $project = null, $tag = null, $assigned_to_company = null, $assigned_to_user = null, $limit = null, $archived = false) {
		if ($project instanceof Project) {
			$project_ids = $project->getAllSubWorkspacesQuery(!$archived);
			$wsstring = " AND " . self::getWorkspaceString($project_ids);
		} else {
			$wsstring = "";
		}

		$to_date = DateTimeValueLib::now()->add('h', logged_user()->getTimezone())->beginningOfDay();
		
		$assignedToStr = "";
		if ($assigned_to_company) {
			if ($assigned_to_company == -1)
				$assigned_to_company = 0;
			$assignedToStr .= " AND `assigned_to_company_id` = " . DB::escape($assigned_to_company) . " ";
		}
		if ($assigned_to_user) {
			if ($assigned_to_user == -1)
				$assigned_to_user = 0;
			$assignedToStr .= " AND `assigned_to_user_id` = " . DB::escape($assigned_to_user) . " ";
		}
			
		$permissions = ' AND ( ' . permissions_sql_for_listings(ProjectTasks::instance(),ACCESS_LEVEL_READ, logged_user(), 'project_id') .')';

		if ($archived) $archived_cond = "`archived_by_id` <> 0 ";
		else $archived_cond = "`archived_by_id` = 0 ";
		
		$tagStr = $tag? (" AND id in (SELECT rel_object_id from " . TABLE_PREFIX . "tags t WHERE tag=".DB::escape($tag)." AND t.rel_object_manager='ProjectTasks')"):'';
		if ($limit) {
			
			$result = self::findAll(array(
	        'conditions' => array('`is_template` = false AND `completed_on` = ? AND `due_date` > \'00:00:00 00-00-0000\' AND `due_date` < ? AND ' . $archived_cond . $wsstring . $tagStr . $permissions . $assignedToStr, EMPTY_DATETIME, $to_date),
	      	'order' => '`due_date` ASC',
	      	'limit' => $limit
	       )); // findAll
		} else {
			$result = self::findAll(array(
	        'conditions' => array('`is_template` = false AND `completed_on` = ? AND `due_date` > \'00:00:00 00-00-0000\' AND `due_date` < ? AND ' . $archived_cond . $wsstring . $tagStr . $permissions . $assignedToStr, EMPTY_DATETIME, $to_date),
	      	'order' => '`due_date` ASC'
	        )); // findAll
		}
       return $result;
	} // getLateTasksByUser
	
	/**
	 * Returns all task templates
	 *
	 */
	static function getAllTaskTemplates($only_parent_task_templates = false, $archived = false){
		if ($archived) $archived_cond = "AND `archived_by_id` <> 0";
		else $archived_cond = "AND `archived_by_id` = 0";
		
		$conditions = " `is_template` = true $archived_cond" ;
		if($only_parent_task_templates)
			$conditions .= "  and `parent_id` = 0  ";
		$order_by = "`title` ASC";
		$tasks = ProjectTasks::find(array(
				'conditions' => $conditions,
				'order' => $order_by
		));
		if (!is_array($tasks)) $tasks = array();
		return $tasks;
	}
	
	/**
	 * Returns workspace task templates
	 *
	 */
	static function getWorkspaceTaskTemplates($workspace_id, $archived = false){
		$table_name = new WorkspaceTemplate();
		$table_name = $table_name->getTableName(true);
		if ($archived) $archived_cond = "AND `archived_by_id` <> 0";
		else $archived_cond = "AND `archived_by_id` = 0";
		$conditions = " `is_template` = true AND `id` in (select `template_id` from " .  $table_name  . " where `workspace_id` = $workspace_id) $archived_cond";
		$order_by = "`title` ASC";
		$tasks = ProjectTasks::find(array(
				'conditions' => $conditions,
				'order' => $order_by
		));
		if (!is_array($tasks)) $tasks = array();
		return $tasks;
//		return ProjectTasks::getProjectTasks($workspace_id, null, 'ASC', 0, 0, null, null, null, null, null, null,true);
	}
	
	static function getProjectTasks($project = null, $order = null, $orderdir = 'ASC', $parent_id = null, $milestone_id = null, $tag = null, $assigned_to_company = null, $assigned_to_user = null, $assigned_by_user = null, $pending = false, $priority = "all", $is_template = false, $is_today = false, $is_late = false, $limit = null, $archived = false) {
		if ($order == self::ORDER_BY_STARTDATE) {
			$order_by = '`start_date` ' . $orderdir;
		} else if ($order == self::ORDER_BY_DUEDATE) {
			$order_by = '`due_date` ' . $orderdir;
		} else {
			// default
			$order_by = '`order` ' . $orderdir;
			
		} // if

		if ($project instanceof Project) {
			$pids = $project->getAllSubWorkspacesQuery(!$archived);
			$projectstr = " AND " . self::getWorkspaceString($pids);
		} else {
			$projectstr = "";
		}

		if ($parent_id === null) {
			$parentstr = "";
		} else {
			$parentstr = " AND `parent_id` = " . DB::escape($parent_id) . " ";
		}

		if ($milestone_id === null) {
			$milestonestr = "";
		} else {
			$milestonestr = " AND `milestone_id` = " . DB::escape($milestone_id) . " ";
		}

		if ($tag == '' || $tag == null) {
			$tagstr = "";
		} else {
			$tagstr = " AND (select count(*) from " . TABLE_PREFIX . "tags where " .
			TABLE_PREFIX . "project_tasks.id = " . TABLE_PREFIX . "tags.rel_object_id and " .
			TABLE_PREFIX . "tags.tag = ".DB::escape($tag)." and " . TABLE_PREFIX . "tags.rel_object_manager ='ProjectTasks' ) > 0 ";
		}

		$assignedToStr = "";
		if ($assigned_to_company) {
			if ($assigned_to_company == -1)
				$assigned_to_company = 0;
			$assignedToStr .= " AND `assigned_to_company_id` = " . DB::escape($assigned_to_company) . " ";
		}
		if ($assigned_to_user) {
			if ($assigned_to_user == -1)
				$assigned_to_user = 0;
			$assignedToStr .= " AND `assigned_to_user_id` = " . DB::escape($assigned_to_user) . " ";
		}

		$assignedByStr = "";
		if ($assigned_by_user) {
			$assignedByStr .= " AND (`created_by_id` = " . DB::escape($assigned_by_user) . " OR `updated_by_id` = " . DB::escape($assigned_by_user) . ") ";
		}

		if ($pending) {
			$pendingstr = " AND `completed_on` = " . DB::escape(EMPTY_DATETIME) . " ";
		} else {
			$pendingstr = "";
		}

		if (is_numeric($priority)) {
			$priostr = " AND `priority` = " . DB::escape($priority);
		} else {
			$priostr = "";
		}
		
		if ($archived) $archived_cond = " AND `archived_by_id` <> 0";
		else $archived_cond = " AND `archived_by_id` = 0";

		$permissionstr = ' AND ( ' . permissions_sql_for_listings(ProjectTasks::instance(), ACCESS_LEVEL_READ, logged_user()) . ') ';

		$otherConditions = $milestonestr . $parentstr . $projectstr . $tagstr . $assignedToStr . $assignedByStr . $pendingstr . $priostr . $permissionstr . $archived_cond;

		$conditions = array(' `is_template` = ' . DB::escape($is_template) . $otherConditions);

		$tasks = ProjectTasks::find(array(
				'conditions' => $conditions,
				'order' => $order_by,
				'limit' => $limit
		));
		if (!is_array($tasks)) $tasks = array();
		return $tasks;
	} // getProjectTasks

	static function paginateProjectTasks($project = null, $order = null, $orderdir = 'ASC', $page = null, $tasks_per_page = null, $group_by_order = false, $parent_id = null, $milestone_id = 0, $tag = null, $assigned_to_company = null, $assigned_to_user = null, $assigned_by_user = null, $pending = false, $archived = false) {
		if ($order == self::ORDER_BY_STARTDATE) {
			$order_by = '`start_date` ' . $orderdir;
		} else if ($order == self::ORDER_BY_DUEDATE) {
			$order_by = '`due_date` ' . $orderdir;
		} else {
			// default
			$order_by = '`order` ' . $orderdir;
		} // if

		if ((integer) $page < 1) {
			$page = 1;
		} // if
		if ((integer) $tasks_per_page < 1) {
			$tasks_per_page = 10;
		} // if

		if ($project instanceof Project) {
			$pids = $project->getAllSubWorkspacesQuery(!$archived);
			$projectstr = " AND " . self::getWorkspaceString($pids);
		} else {
			$projectstr = "";
		}

		if ($parent_id === null) {
			$parentstr = "";
		} else {
			$parentstr = " AND `parent_id` = " . DB::escape($parent_id) . " ";
		}

		if ($milestone_id > 0) {
			$milestonestr = " AND `milestone_id` = " . DB::escape($milestone_id) . " ";
		} else {
			$milestonestr = "";
		}

		if ($tag == '' || $tag == null) {
			$tagstr = "";
		} else {
			$tagstr = " AND (select count(*) from " . TABLE_PREFIX . "tags where " .
			TABLE_PREFIX . "project_tasks.id = " . TABLE_PREFIX . "tags.rel_object_id and " .
			TABLE_PREFIX . "tags.tag = ".DB::escape($tag)." and " . TABLE_PREFIX . "tags.rel_object_manager ='ProjectTasks' ) > 0 ";
		}

		$assignedToStr = "";
		if ($assigned_to_company) {
			$assignedToStr .= " AND `assigned_to_company_id` = " . DB::escape($assigned_to_company) . " ";
		}
		if ($assigned_to_user) {
			$assignedToStr .= " AND `assigned_to_user_id` = " . DB::escape($assigned_to_user) . " ";
		}

		$assignedByStr = "";
		if ($assigned_by_user) {
			$assignedByStr .= " AND (`created_by_id` = " . DB::escape($assigned_by_user) . " OR `updated_by_id` = " . DB::escape($assigned_by_user) . ") ";
		}

		if ($pending) {
			$pendingstr = " AND `completed_on` = " . DB::escape(EMPTY_DATETIME) . " ";
		} else {
			$pendingstr = "";
		}
		
		if ($archived) $archived_cond = "AND `archived_by_id` <> 0";
		else $archived_cond = "AND `archived_by_id` = 0";

		$permissionstr = ' AND ( ' . permissions_sql_for_listings(ProjectTasks::instance(), ACCESS_LEVEL_READ, logged_user()) . ') ';

		$otherConditions = $milestonestr . $parentstr . $projectstr . $tagstr . $assignedToStr . $assignedByStr . $pendingstr . $permissionstr . $archived_cond;

		$conditions = array(' `is_template` = false ' . $otherConditions);

		list($tasks, $pagination) = ProjectTasks::paginate(array(
				'conditions' => $conditions,
				'order' => $order_by
		), $tasks_per_page, $page);
		if (!is_array($tasks)) $tasks = array();
		return array($tasks, $pagination);
	} // paginateProjectTasks

	function maxOrder($parentId = null, $milestoneId = null) {
		$condition = "`trashed_on` = " . DB::escape(EMPTY_DATETIME) . " AND `is_template` = false AND `archived_by_id` = 0";
		if (is_numeric($parentId)) {
			$condition .= " AND ";
			$condition .= " `parent_id` = " . DB::escape($parentId);
		}
		if (is_numeric($milestoneId)) {
			$condition .= " AND ";
			$condition .= " `milestone_id` = " . DB::escape($milestoneId);
		}
		$res = DB::execute("SELECT max(`order`) as `max` FROM `" . TABLE_PREFIX . "project_tasks` " .
		" WHERE " . $condition);
		if ($res->numRows() < 1) {
			return 0;
		} else {
			$row = $res->fetchRow();
			return $row["max"] + 1;
		}
	}

	/**
	 * Return Day tasks this user have access on
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getRangeTasksByUser(DateTimeValue $date_start, DateTimeValue $date_end, $assignedUser, $tags = '', $project = null, $archived = false) {

		$from_date = new DateTimeValue($date_start->getTimestamp());
		$from_date = $from_date->beginningOfDay();
		$to_date = new DateTimeValue($date_end->getTimestamp());
		$to_date = $to_date->endOfDay();
			
		$permissions = ' AND ( ' . permissions_sql_for_listings(ProjectTasks::instance(),ACCESS_LEVEL_READ, logged_user(), 'project_id') .')';

		if ($project instanceof Project ) {
			$pids = $project->getAllSubWorkspacesQuery(!$archived);
			$wsstring = " AND " . self::getWorkspaceString($pids);
		} else {
			$wsstring = "";
		}
		
		if (isset($tags) && $tags && $tags!='') {
			$tag_str = " AND exists (SELECT * from " . TABLE_PREFIX . "tags t WHERE tag=".DB::escape($tags)." AND  ".TABLE_PREFIX."project_tasks.id=t.rel_object_id AND t.rel_object_manager='ProjectTasks') ";
		} else {
			$tag_str= "";
		}
		
		$assignedFilter = '';
		if ($assignedUser instanceof User) 
			$assignedFilter = ' AND (`assigned_to_user_id` = ' . $assignedUser->getId() . ' OR (`assigned_to_user_id` = 0 AND `assigned_to_company_id` = '. $assignedUser->getCompanyId() .')) ';
		
		$rep_condition = " (`repeat_forever` = 1 OR `repeat_num` > 0 OR (`repeat_end` > 0 AND `repeat_end` >= '".$from_date->toMySQL()."')) ";
		
		if ($archived) $archived_cond = " AND `archived_by_id` <> 0";
		else $archived_cond = " AND `archived_by_id` = 0";
		
		$result = self::findAll(array(
        'conditions' => array('`is_template` = false AND `completed_on` = ? AND ((`due_date` >= ? AND `due_date` < ?) OR (`start_date` >= ? AND `start_date` < ?) OR '.$rep_condition.') ' . $archived_cond . $assignedFilter . $permissions.$wsstring.$tag_str, EMPTY_DATETIME, $from_date, $to_date, $from_date, $to_date)
		)); // findAll
		
		return $result;
	} // getDayTasksByUser

	/**
	 * Returns an unsaved copy of the task. Copies everything except open/closed state,
	 * anything that needs the task to have an id (like tags, properties, subtask),
	 * administrative info like who created the task and when, etc.
	 *
	 * @param ProjectTask $task
	 * @return ProjectTask
	 */
	function createTaskCopy(ProjectTask $task) {
		$new = new ProjectTask();
		$new->setMilestoneId($task->getMilestoneId());
		$new->setParentId($task->getParentId());
		$new->setTitle($task->getTitle());
		$new->setAssignedToCompanyId($task->getAssignedToCompanyId());
		$new->setAssignedToUserId($task->getAssignedToUserId());
		$new->setPriority($task->getPriority());
		$new->setTimeEstimate($task->getTimeEstimate());
		$new->setText($task->getText());
		$new->setIsPrivate($task->getIsPrivate());
		$new->setOrder(ProjectTasks::maxOrder($new->getParentId(), $new->getMilestoneId()));
		$new->setStartDate($task->getStartDate());
		$new->setDueDate($task->getDueDate());
		return $new;
	}

	/**
	 * Copies subtasks from taskFrom to taskTo.
	 *
	 * @param ProjectTask $taskFrom
	 * @param ProjectTask $taskTo
	 */
	function copySubTasks(ProjectTask $taskFrom, ProjectTask $taskTo, $as_template = false) {
		foreach ($taskFrom->getSubTasks() as $sub) {
			$new = ProjectTasks::createTaskCopy($sub);
			$new->setIsTemplate($as_template);
			$new->setParentId($taskTo->getId());
			$new->setMilestoneId($taskTo->getMilestoneId());
			$new->setOrder(ProjectTasks::maxOrder($new->getParentId(), $new->getMilestoneId()));
			if ($sub->getIsTemplate()) {
				$new->setFromTemplateId($sub->getId());
			}
			$new->save();
			foreach ($taskTo->getWorkspaces() as $workspace) {
				if (ProjectTask::canAdd(logged_user(), $workspace)) {
					$new->addToWorkspace($workspace);
				}
			}
			$new->copyCustomPropertiesFrom($sub);
			$new->copyLinkedObjectsFrom($sub);
			$new->setTagsFromCSV(implode(",", $sub->getTagNames()));
			ProjectTasks::copySubTasks($sub, $new, $as_template);
		}
	}
} // ProjectTasks
?>