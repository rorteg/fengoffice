<?php

/**
 * ProjectMilestones, generated on Sat, 04 Mar 2006 12:50:11 +0100 by
 * DataObject generation tool
 *
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class ProjectMilestones extends BaseProjectMilestones {

	public static function getWorkspaceString($ids = '?') {
		if (is_array($ids)) {
			$ids = implode(',', $ids);
		}
		return " `id` IN (SELECT `object_id` FROM `" . TABLE_PREFIX . "workspace_objects` WHERE `object_manager` = 'ProjectMilestones' AND `workspace_id` IN ($ids)) ";
	}
	
	/**
	 * Return all late milestones in active projects of a specific company.
	 * This function will exclude milestones marked for today
	 *
	 * @param void
	 * @return array
	 */
	function getLateMilestonesByCompany(Company $company, $archived = false) {
		$due_date = DateTimeValueLib::now()->beginningOfDay();

		$projects = $company->getActiveProjects();
		if(!is_array($projects) || !count($projects)) return null;

		$project_ids = array();
		foreach($projects as $project) {
			$project_ids[] = $project->getId();
		} // foreach

		if ($archived) $archived_cond = "`archived_by_id` <> 0 AND ";
		else $archived_cond = "`archived_by_id` = 0 AND ";
		
		return self::findAll(array(
	        'conditions' => array('`is_template` = false AND due_date` < ? AND `completed_on` = ? AND ' . $archived_cond . self::getWorkspaceString($project_ids), $due_date, EMPTY_DATETIME),
    	    'order' => '`due_date`',
		)); // findAll
	} // getLateMilestonesByCompany

	/**
	 * Return milestones scheduled for today from projects related with specific company
	 *
	 * @param Company $company
	 * @return array
	 */
	function getTodayMilestonesByCompany(Company $company, $archived = false) {
		$from_date = DateTimeValueLib::now()->beginningOfDay();
		$to_date = DateTimeValueLib::now()->endOfDay();

		$projects = $company->getActiveProjects();
		if(!is_array($projects) || !count($projects)) return null;

		$project_ids = array();
		foreach($projects as $project) {
			$project_ids[] = $project->getId();
		} // foreach
		
		if ($archived) $archived_cond = "`archived_by_id` <> 0 AND ";
		else $archived_cond = "`archived_by_id` = 0 AND ";

		return self::findAll(array(
        	'conditions' => array('`is_template` = false AND `completed_on` = ? AND (`due_date` >= ? AND `due_date` < ?) AND ' . $archived_cond . self::getWorkspaceString($project_ids), EMPTY_DATETIME, $from_date, $to_date),
        	'order' => '`due_date`'
        )); // findAll
	} // getTodayMilestonesByCompany

	/**
	 * Return all milestones that are assigned to the user
	 *
	 * @param User $user
	 * @return array
	 */
	static function getActiveMilestonesByUser(User $user, $archived = false) {
		$projects = $user->getActiveProjects();
		if(!is_array($projects) || !count($projects)) {
			return null;
		} // if

		$project_ids = array();
		foreach($projects as $project) {
			$project_ids[] = $project->getId();
		} // foreach

		if ($archived) $archived_cond = "`archived_by_id` <> 0 AND ";
		else $archived_cond = "`archived_by_id` = 0 AND ";
		
		return self::findAll(array(
        	'conditions' => array('`is_template` = false AND (`assigned_to_user_id` = ? OR (`assigned_to_user_id` = ? AND `assigned_to_company_id` = ?)) AND ' . $archived_cond . self::getWorkspaceString($project_ids) . ' AND `completed_on` = ?', $user->getId(), 0, 0, EMPTY_DATETIME),
        	'order' => '`due_date`'
        )); // findAll
	} // getActiveMilestonesByUser

	/**
	 * Return active milestones that are assigned to the specific user and belongs to specific project
	 *
	 * @param User $user
	 * @param Project $project
	 * @return array
	 */
	static function getActiveMilestonesByUserAndProject(User $user, Project $project, $archived = false) {
		if ($archived) $archived_cond = "`archived_by_id` <> 0 AND ";
		else $archived_cond = "`archived_by_id` = 0 AND ";
		
		return self::findAll(array(
        	'conditions' => array('`is_template` = false AND (`assigned_to_user_id` = ? OR (`assigned_to_user_id` = ? AND `assigned_to_company_id` = ?)) AND ' . $archived_cond . self::getWorkspaceString($project->getId()) . ' AND `completed_on` = ?', $user->getId(), 0, 0, EMPTY_DATETIME),
        	'order' => '`due_date`'
        )); // findAll
	} // getActiveMilestonesByUserAndProject
	 
	/**
	 * Return late milestones from active projects this user have access on. Today milestones are excluded
	 *
	 * @param User $user
	 * @return array
	 */
	function getLateMilestonesByUser(User $user, $project = null, $tag = null,$limit = null, $archived = false) {
		$due_date = DateTimeValueLib::now()->beginningOfDay();

		if ($project instanceof Project) {
			$project_ids = $project->getAllSubWorkspacesQuery(!$archived);
			$wsstring = " AND " . self::getWorkspaceString($project_ids);
		} else {
			$wsstring = "";
		}
		
		if ($archived) $archived_cond = "`archived_by_id` <> 0 ";
		else $archived_cond = "`archived_by_id` = 0 ";

		$permissions = ' AND ( ' . permissions_sql_for_listings(ProjectMilestones::instance(),ACCESS_LEVEL_READ, logged_user(), 'project_id') .')';
		$tagStr = $tag? (" AND id in (SELECT rel_object_id from " . TABLE_PREFIX . "tags t WHERE tag=".DB::escape($tag)." AND t.rel_object_manager='ProjectMilestones')"):'';
		if ($limit) {
			return self::findAll(array(
				'conditions' => array('`is_template` = false AND `due_date` < ? AND `completed_on` = ? AND ' . $archived_cond . $wsstring . $tagStr . $permissions, $due_date, EMPTY_DATETIME),
				'order' => '`due_date`',
				'limit' => $limit
			)); // findAll
		} else {
			return self::findAll(array(
          'conditions' => array('`is_template` = false AND `due_date` < ? AND `completed_on` = ? AND ' . $archived_cond . self::getWorkspaceString($project_ids) . $tagStr . $permissions, $due_date, EMPTY_DATETIME),
          'order' => '`due_date`'
          )); // findAll
		}
	}

	/**
	 * Return today milestones from active projects this user have access on
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getTodayMilestonesByUser(User $user, $project = null, $tag = null, $limit = null, $archived = false) {
		$from_date = DateTimeValueLib::now()->add('h', logged_user()->getTimezone())->beginningOfDay();
		$to_date = DateTimeValueLib::now()->add('h', logged_user()->getTimezone())->endOfDay();

		if ($project instanceof Project) {
			$project_ids = $project->getAllSubWorkspacesQuery(!$archived);
			$wsstring = " AND " . self::getWorkspaceString($project_ids);
		} else {
			$wsstring = "";
		}
		
		if ($archived) $archived_cond = "`archived_by_id` <> 0 ";
		else $archived_cond = "`archived_by_id` = 0 ";

		$permissions = ' AND ( ' . permissions_sql_for_listings(ProjectMilestones::instance(), ACCESS_LEVEL_READ, logged_user(), 'project_id') .')';
		$tagStr = $tag? (" AND id in (SELECT rel_object_id from " . TABLE_PREFIX . "tags t WHERE tag=".DB::escape($tag)." AND t.rel_object_manager = 'ProjectMilestones')"):'';
		if ($limit) {
			return self::findAll(array(
				'conditions' => array('`is_template` = false AND `completed_on` = ? AND (`due_date` >= ? AND `due_date` < ?) AND ' . $archived_cond . $wsstring . $tagStr . $permissions, EMPTY_DATETIME, $from_date, $to_date),
				'limit' => $limit
			)); // findAll
		}else {
			return self::findAll(array(
				'conditions' => array('`is_template` = false AND `completed_on` = ? AND (`due_date` >= ? AND `due_date` < ?) AND ' . $archived_cond . self::getWorkspaceString($project_ids) . $permissions, EMPTY_DATETIME, $from_date, $to_date)
			)); // findAll
		}
	} // getTodayMilestonesByUser

	/**
	 * Return Day milestones from active projects this user have access on
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getDayMilestonesByUser(DateTimeValue $date,User $user, $archived = false) {
		//      $date = new DateTimeValue($date->getTimestamp());

		$date = $date->add('h', logged_user()->getTimezone());
		$from_date =   (new DateTimeValue($date->getTimestamp()));
		$from_date = $from_date->beginningOfDay();
		$to_date =  (new DateTimeValue($date->getTimestamp()));
		$to_date = $to_date->endOfDay();
		 
		$permissions = ' AND ( ' . permissions_sql_for_listings(ProjectMilestones::instance(),ACCESS_LEVEL_READ, logged_user(), 'project_id') .')';

		if ($archived) $archived_cond = "AND `archived_by_id` <> 0 ";
		else $archived_cond = "AND `archived_by_id` = 0 ";
		
		$result = self::findAll(array(
			'conditions' => array('`is_template` = false AND `completed_on` = ? AND (`due_date` >= ? AND `due_date` < ?) ' . $archived_cond . $permissions, EMPTY_DATETIME, $from_date, $to_date)
		)); // findAll
		return $result;
	} // getDayMilestonesByUser

	function getDayMilestonesByUserAndProject(DateTimeValue $date,User $user, $project = null, $archived = false) {
		if ($project instanceof Project) {
			$project_ids = $project->getAllSubWorkspacesQuery(!$archived);
			$wsstring = " AND " . self::getWorkspaceString($project_ids);
		} else {
			$wsstring = "";
		}
		 
		$from_date =   (new DateTimeValue($date->getTimestamp()));
		$from_date = $from_date->beginningOfDay();
		$to_date =  (new DateTimeValue($date->getTimestamp()));
		$to_date = $to_date->endOfDay();
		
		if ($archived) $archived_cond = "`archived_by_id` <> 0 AND ";
		else $archived_cond = "`archived_by_id` = 0 AND ";
		
		$permissions = ' AND ( ' . permissions_sql_for_listings(ProjectMilestones::instance(),ACCESS_LEVEL_READ, $user, 'project_id') .')';

		$result = self::findAll(array(
			'conditions' => array('`is_template` = false AND `completed_on` = ? AND (`due_date` >= ? AND `due_date` < ?) AND ' . $archived_cond . $wsstring . $permissions, EMPTY_DATETIME, $from_date, $to_date)
		)); // findAll
		return $result;
	} // getDayMilestonesByUser


	/**
	 * Return milestones in date range from active projects this user have access on
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getRangeMilestonesByUser(DateTimeValue $date_start, DateTimeValue $date_end, $assignedUser = null, $tag = '', $project = null, $archived = false){

		$from_date = new DateTimeValue($date_start->getTimestamp());
		$from_date = $from_date->beginningOfDay();
		$to_date = new DateTimeValue($date_end->getTimestamp());
		$to_date = $to_date->endOfDay();
		 
		$permissions = ' AND ( ' . permissions_sql_for_listings(ProjectMilestones::instance(),ACCESS_LEVEL_READ, logged_user(), 'project_id') .')';
		 
		if ($project instanceof Project ){
			$pids = $project->getAllSubWorkspacesQuery(!$archived);
			$wsstring = " AND " . self::getWorkspaceString($pids);
		} else {
			$wsstring = "";
		}
		if (isset($tag) && $tag && $tag!='') {
			$tag_str = " AND exists (SELECT * from " . TABLE_PREFIX . "tags t WHERE tag=".DB::escape($tag)." AND  ".TABLE_PREFIX."project_milestones.id = t.rel_object_id AND t.rel_object_manager = 'ProjectMilestones') ";
		} else {
			$tag_str= "";
		}
		 
		$assignedFilter = '';
		if ($assignedUser instanceof User) {
			$assignedFilter = ' AND (`assigned_to_user_id` = '.$assignedUser->getId().' OR 
				(`id` IN (SELECT milestone_id FROM '.TABLE_PREFIX.'project_tasks WHERE `trashed_on` = ' . DB::escape(EMPTY_DATETIME) . ' AND `milestone_id` > 0 AND `assigned_to_user_id` = ' . $assignedUser->getId() . ') OR 
				(`assigned_to_user_id` = 0 AND (`assigned_to_company_id` = '. $assignedUser->getCompanyId().' OR `assigned_to_company_id` = 0))))';
					}
		
		if ($archived) $archived_cond = "AND `archived_by_id` <> 0 ";
		else $archived_cond = "AND `archived_by_id` = 0 ";

		$result = self::findAll(array(
			'conditions' => array('`is_template` = false AND `completed_on` = ? AND (`due_date` >= ? AND `due_date` < ?) ' . $archived_cond . $assignedFilter . $permissions . $wsstring . $tag_str, EMPTY_DATETIME, $from_date, $to_date)
		)); // findAll

		return $result;
	} // getRangeMilestonesByUser

	static function getMilestonesRelevantToWorkspace($workspace) {
		if ($workspace instanceof Project) {
			$pids = $workspace->getAllSubWorkspacesQuery(true);
			$projectstr = " AND (" . self::getWorkspaceString($pids) . " OR " . self::getWorkspaceString($workspace->getParentIds()) . ")";
		} else {
			$projectstr = "";
		}
		$pendingstr = " AND `completed_on` = " . DB::escape(EMPTY_DATETIME) . " ";
		$permissionstr = ' AND ( ' . permissions_sql_for_listings(ProjectMilestones::instance(), ACCESS_LEVEL_READ, logged_user()) . ') ';
		
		$conditions = array(' `is_template` = ' . DB::escape(false) . " AND `archived_by_id` = 0 $projectstr $pendingstr $permissionstr");
		$milestones = ProjectMilestones::find(array(
				'conditions' => $conditions,
		));
		if (!is_array($milestones)) $milestones = array();
		return $milestones;
	}
	
	static function getProjectMilestones($project = null, $order = null, $orderdir = 'DESC', $tag = null, $assigned_to_company = null, $assigned_to_user = null, $assigned_by_user = null, $pending = false, $is_template = false, $archived = false) {
		// default
		$order_by = '`due_date` ASC';

		if ($project instanceof Project) {
			$pids = $project->getAllSubWorkspacesQuery(!$archived);
			$projectstr = " AND " . self::getWorkspaceString($pids);
		} else {
			$projectstr = "";
		}
		

		if ($tag == '' || $tag == null) {
			$tagstr = "";
		} else {
			$tagstr = " AND (select count(*) from " . TABLE_PREFIX . "tags where " .
			TABLE_PREFIX . "project_milestones.id = " . TABLE_PREFIX . "tags.rel_object_id and " .
			TABLE_PREFIX . "tags.tag = ".DB::escape($tag)." and " . TABLE_PREFIX . "tags.rel_object_manager ='ProjectMilestones' ) > 0 ";
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

		if ($pending) {
			$pendingstr = " AND `completed_on` = " . DB::escape(EMPTY_DATETIME) . " ";
		} else {
			$pendingstr = "";
		}
		
		if ($archived) $archived_cond = " AND `archived_by_id` <> 0";
		else $archived_cond = " AND `archived_by_id` = 0";

		$permissionstr = ' AND ( ' . permissions_sql_for_listings(ProjectMilestones::instance(), ACCESS_LEVEL_READ, logged_user()) . ') ';

		$otherConditions = $projectstr . $tagstr . $assignedToStr . $assignedByStr . $permissionstr . $pendingstr . $archived_cond;

		$conditions = array(' `is_template` = ' . DB::escape($is_template) . $otherConditions);

		$milestones = ProjectMilestones::find(array(
				'conditions' => $conditions,
				'order' => $order_by
		));
		if (!is_array($milestones)) $milestones = array();
		return $milestones;
	} // getProjectMilestones

	/**
	 * Returns an unsaved copy of the milestone. Copies everything except open/closed state,
	 * anything that needs the task to have an id (like tags, properties, tasks),
	 * administrative info like who created the milestone and when, etc.
	 *
	 * @param ProjectMilestone $milestone
	 * @return ProjectMilestone
	 */
	function createMilestoneCopy(ProjectMilestone $milestone) {
		$new = new ProjectMilestone();
		$new->setName($milestone->getName());
		$new->setDescription($milestone->getDescription());
		$new->setIsPrivate($milestone->getIsPrivate());
		$new->setIsUrgent($milestone->setIsUrgent());
		$new->setAssignedToCompanyId($milestone->getAssignedToCompanyId());
		$new->setAssignedToUserId($milestone->getAssignedToUserId());
		$new->setDueDate($milestone->getDueDate());
		return $new;
	}

	/**
	 * Copies tasks from milestoneFrom to milestoneTo.
	 *
	 * @param ProjectMilestone $milestoneFrom
	 * @param ProjectMilestone $milestoneTo
	 */
	function copyTasks(ProjectMilestone $milestoneFrom, ProjectMilestone $milestoneTo, $as_template = false) {
		foreach ($milestoneFrom->getTasks() as $sub) {
			if ($sub->getParentId() != 0) continue;
			$new = ProjectTasks::createTaskCopy($sub);
			$new->setIsTemplate($as_template);
			$new->setMilestoneId($milestoneTo->getId());
			if ($sub->getIsTemplate()) {
				$new->setFromTemplateId($sub->getId());
			}
			$new->save();
			foreach ($sub->getWorkspaces() as $workspace) {
				if (ProjectTask::canAdd(logged_user(), $workspace)) {
					$new->addToWorkspace($workspace);
				}
			}
			if (!$as_template && active_project() instanceof Project && ProjectTask::canAdd(logged_user(), active_project())) {
				$new->removeFromAllWorkspaces();
				$new->addToWorkspace(active_project());
			}
			$new->copyCustomPropertiesFrom($sub);
			$new->copyLinkedObjectsFrom($sub);
			$new->setTagsFromCSV(implode(",", $sub->getTagNames()));
			ProjectTasks::copySubTasks($sub, $new, $as_template);
		}
	}

} // ProjectMilestones

?>