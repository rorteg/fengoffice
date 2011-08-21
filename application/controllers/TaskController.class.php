<?php

/**
 * Controller for handling task list and task related requests
 *
 * @version 1.0
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class TaskController extends ApplicationController {

	/**
	 * Construct the MilestoneController
	 *
	 * @access public
	 * @param void
	 * @return MilestoneController
	 */
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website');
	} // __construct

	private function task_item(ProjectTask $task) {
		$isCurrentProject = active_project() instanceof Project && $task->getProjectId() == active_project()->getId();

		return array(
			"id" => $task->getId(),
			"title" => clean($task->getObjectName()),
			"parent" => $task->getParentId(),
			"milestone" => $task->getMilestoneId(),
			"assignedTo" => $task->getAssignedTo()? $task->getAssignedToName():'',
			"workspaces" => ($isCurrentProject? '' : $task->getWorkspacesNamesCSV(logged_user()->getWorkspacesQuery())),
			"workspaceids" => ($isCurrentProject? '' : $task->getWorkspacesIdsCSV(logged_user()->getWorkspacesQuery())),
			"workspacecolors" => ($isCurrentProject? '' : $task->getWorkspaceColorsCSV(logged_user()->getWorkspacesQuery())),
			"completed" => $task->isCompleted(),
			"completedBy" => $task->getCompletedByName(),
			"isLate" => $task->isLate(),
			"daysLate" => $task->getLateInDays(),
			"priority" => $task->getPriority(),
			"duedate" => ($task->getDueDate() ? $task->getDueDate()->getTimestamp() : '0'),
			"order" => $task->getOrder()
		);
	}

	function assign(){
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		ajx_current("empty");
		$task_id = array_var($_POST, 'taskId');
		$task = ProjectTasks::findById($task_id);
		if(!($task instanceof ProjectTask)) {
			flash_error(lang('task list dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$task->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$assigned_to = explode(':', array_var($_POST, 'assigned_to', ''));
		try{
			DB::beginWork();
				
			$company_id = array_var($assigned_to, 0, 0);
			$user_id = array_var($assigned_to, 1, 0);
			if ($company_id < 0) $company_id = 0;
			if ($user_id < 0) $user_id = 0;
				
			$can_assign = can_assign_task_to_company_user(logged_user(), $task, $company_id, $user_id);
			if ($can_assign !== true) {
				flash_error($can_assign);
				return;
			}
				
			$task->setAssignedToCompanyId($company_id);
			$task->setAssignedToUserId($user_id);
				
			$task->save();
				
			DB::commit();
			ajx_extra_data(array("id" => $task_id));
		} catch(Exception $e) {
			DB::rollback();
			flash_error($e->getMessage());
		} // try
	}

	function quick_add_task() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		ajx_current("empty");
		$task = new ProjectTask();
		$task_data = array_var($_POST, 'task');
		$parent_id = array_var($task_data, 'parent_id', 0);
		$parent = ProjectTasks::findById($parent_id);
		$project = Projects::findById(array_var($task_data, 'project_id', 0));
		if (!$project instanceof Project) {
			if ($parent instanceof ProjectTask){
				$project = $parent->getProject();
			} else {
				$milestone_id = array_var($task_data,'milestone_id',null);
				if($milestone_id){
					$milestone = ProjectMilestones::findById($milestone_id);
					if($milestone) $project =$milestone->getProject();
				}
				if(!$project instanceof Project) $project = active_or_personal_project();
			}
		}

		if(!ProjectTask::canAdd(logged_user(), $project)) {
			flash_error(lang('no access permissions'));
			return;
		} // if

		if (is_array($task_data)) {
			$task_data['due_date'] = getDateValue(array_var($task_data, 'task_due_date'));
			$task_data['start_date'] = getDateValue(array_var($task_data, 'task_start_date'));
				
			$task->setFromAttributes($task_data);
			//$task->setOrder(ProjectTasks::maxOrder(array_var($task_data, "parent_id", 0), array_var($task_data, "milestone_id", 0)));
			// Set assigned to
			$assigned_to = explode(':', array_var($task_data, 'assigned_to', ''));
			$company_id = array_var($assigned_to, 0, 0);
			$user_id = array_var($assigned_to, 1, 0);
			$can_assign = can_assign_task_to_company_user(logged_user(), $task, $company_id, $user_id);
			if ($can_assign !== true) {
				flash_error($can_assign);
				return;
			}
			$task->setAssignedToCompanyId($company_id);
			$task->setAssignedToUserId($user_id);
			$task->setIsPrivate(false); // Not used, but defined as not null.
				
			if (array_var($task_data,'is_completed',false) == 'true'){
				$task->setCompletedOn(DateTimeValueLib::now());
				$task->setCompletedById(logged_user()->getId());
			}
				
			try {
				DB::beginWork();
				$task->save();
				$task->setProject($project);
				$task->setTagsFromCSV(array_var($task_data, 'tags'));

				//Add new work timeslot for this task
				if (array_var($task_data,'hours') != '' && array_var($task_data,'hours') > 0){
					$hours = array_var($task_data, 'hours');
					$hours = - $hours;
						
					$timeslot = new Timeslot();
					$dt = DateTimeValueLib::now();
					$dt2 = DateTimeValueLib::now();
					$timeslot->setEndTime($dt);
					$dt2 = $dt2->add('h', $hours);
					$timeslot->setStartTime($dt2);
					$timeslot->setUserId(logged_user()->getId());
					$timeslot->setObjectManager("ProjectTasks");
					$timeslot->setObjectId($task->getId());
					$timeslot->save();
				}

				// subscribe
				$task->subscribeUser(logged_user());

				ApplicationLogs::createLog($task, $task->getWorkspaces(), ApplicationLogs::ACTION_ADD);
				$assignee = $task->getAssignedToUser();
				if ($assignee instanceof User) {
					$task->subscribeUser($assignee);
				}
				
			    // create default reminder
			    $reminder = new ObjectReminder();
				$reminder->setMinutesBefore(1440);
				$reminder->setType("reminder_email");
				$reminder->setContext("due_date");
				$reminder->setObject($task);
				$reminder->setUserId(0);
				$date = $task->getDueDate();
				
				if(!isset($minutes))$minutes=0;
				
				if ($date instanceof DateTimeValue) {
					$rdate = new DateTimeValue($date->getTimestamp() - $minutes * 60);
					$reminder->setDate($rdate);
				}
				$reminder->save();
				
				DB::commit();

				// notify asignee
				if(array_var($task_data, 'notify') == 'true') {
					try {
						Notifier::taskAssigned($task);
					} catch(Exception $e) {
					} // try
				}
				ajx_extra_data(array("task" => $task->getArrayInfo()));
				flash_success(lang('success add task', $task->getTitle()));
				
				//Addition for goback action (Wizard) 
				if(isset($_SESSION['wizard_task']))
				{
					evt_add("back_to_wizard");
					unset($_SESSION['wizard_task']);
				}
				
			} catch(Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
			} // try
		} // if
	}

	function quick_edit_task() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		ajx_current("empty");

		$task = ProjectTasks::findById(get_id());
		if(!($task instanceof ProjectTask)) {
			flash_error(lang('task list dnx'));
			return;
		} // if

		if(!$task->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			return;
		} // if

		$task_data = array_var($_POST, 'task');

		if (is_array($task_data)) {
			$task_data['due_date'] = getDateValue(array_var($task_data, 'task_due_date'));
			$task_data['start_date'] = getDateValue(array_var($task_data, 'task_start_date'));
			$old_milestone_id = $task->getMilestoneId();
				
			$task->setFromAttributes($task_data);
			$project = Projects::findById(array_var($task_data, 'project_id', 0));

			//$task->setOrder(ProjectTasks::maxOrder(array_var($task_data, "parent_id", 0), array_var($task_data, "milestone_id", 0)));
			// Set assigned to
			$assigned_to = explode(':', array_var($task_data, 'assigned_to', ''));
				
			$company_id = array_var($assigned_to, 0, 0);
			$user_id = array_var($assigned_to, 1, 0);
			$can_assign = can_assign_task_to_company_user(logged_user(), $task, $company_id, $user_id);
			if ($can_assign !== true) {
				flash_error($can_assign);
				return;
			}
			$task->setAssignedToCompanyId($company_id);
			$task->setAssignedToUserId($user_id);
			//$task->setIsPrivate(false); // Not used, but defined as not null.
				
			/*if (array_var($task_data,'is_completed',false) == 'true'){
				$task->setCompletedOn(DateTimeValueLib::now());
				$task->setCompletedById(logged_user()->getId());
				}*/
			if (array_var($_GET, 'dont_mark_as_read')) {
				$is_read = $task->getIsRead(logged_user()->getId());
			}
			try {
				DB::beginWork();
				$task->save();
				
				if (array_var($_GET, 'dont_mark_as_read') && !$is_read) {
					$task->setIsRead(logged_user()->getId(), false);
				}					
				
				if ($project instanceof Project && $task->canAdd(logged_user(),$project)) {
					$task->setProject($project);
				}
				$task->setTagsFromCSV(array_var($task_data, 'tags'));
				
				// apply values to subtasks
				$subtasks = $task->getAllSubTasks();
				$project = $task->getProject();
				$milestone_id = $task->getMilestoneId();
				
				//Check for milestone workspace restrictions, update the task's workspace if milestone changed
				if ($milestone_id > 0 && $old_milestone_id != $milestone_id){
					$milestone = ProjectMilestones::findById($milestone_id);
					$milestoneWs = $milestone->getProject();
					if ($milestoneWs->getId() != $project->getId() && !$milestoneWs->isParentOf($project)){
						$project = $milestoneWs;
						if ($task->canAdd(logged_user(),$project)) {
							$task->setProject($project);
						} else {
							throw new Exception(lang('no access permissions'));
						}
					}
				}
				$apply_ws = array_var($task_data, 'apply_ws_subtasks', '') == "checked";
				$apply_ms = array_var($task_data, 'apply_milestone_subtasks', '') == "checked";
				$apply_at = array_var($task_data, 'apply_assignee_subtasks', '') == "checked";
				$modified_subtasks = array();
				foreach ($subtasks as $sub) {
					$modified = false;
					if ($apply_at || !$sub->getAssignedTo() instanceof ApplicationDataObject) {
						$sub->setAssignedToCompanyId($company_id);
						$sub->setAssignedToUserId($user_id);
						$modified = true;
					}
					if ($apply_ws) {
						$sub->setProject($project);
						$modified = true;
					}
					if ($apply_ms) {
						$sub->setMilestoneId($milestone_id);
						$modified = true;
					}
					if ($modified) {
						$sub->save();
						$modified_subtasks[] = $sub;
					}
				}

				//Add new work timeslot for this task
				if (array_var($task_data,'hours') != '' && array_var($task_data,'hours') > 0){
					$hours = array_var($task_data, 'hours');
						
					if (strpos($hours,',') && !strpos($hours,'.')) {
						$hours = str_replace(',','.',$hours);
					}
						
					$timeslot = new Timeslot();
					$dt = DateTimeValueLib::now();
					$dt2 = DateTimeValueLib::now();
					$timeslot->setEndTime($dt);
					$dt2 = $dt2->add('h', -$hours);
					$timeslot->setStartTime($dt2);
					$timeslot->setUserId(logged_user()->getId());
					$timeslot->setObjectManager("ProjectTasks");
					$timeslot->setObjectId($task->getId());
					$timeslot->save();
				}
				ApplicationLogs::createLog($task, $task->getWorkspaces(), ApplicationLogs::ACTION_EDIT);
				$assignee = $task->getAssignedToUser();
				if ($assignee instanceof User) {
					$task->subscribeUser($assignee);
				}
				DB::commit();

				// notify asignee
				if(array_var($task_data, 'notify') == 'true') {
					try {
						Notifier::taskAssigned($task);
					} catch(Exception $e) {
					} // try
				}
				$task->getTagNames(true); //Forces reload of task tags to update changes
				$subt_info = array();
				foreach ($modified_subtasks as $sub) {
					$subt_info[] = $sub->getArrayInfo();
				}
				ajx_extra_data(array("task" => $task->getArrayInfo(), 'subtasks' => $subt_info));
				flash_success(lang('success edit task', $task->getTitle()));
			} catch(Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
			} // try
		} // if
	}

	function multi_task_action(){
		ajx_current("empty");
		$ids = explode(',', array_var($_POST, 'ids'));
		$action = array_var($_POST, 'action');
		$options = array_var($_POST, 'options');

		if (!is_array($ids) || trim(array_var($_POST, 'ids')) == '' || count($ids) <= 0){
			flash_error(lang('no items selected'));
			return;
		}

		$tasks = ProjectTasks::findAll(array('conditions' => 'id in (' . implode(',',$ids) . ')'));
		$tasksToReturn = array();
		$showSuccessMessage = true;
		try{
			DB::beginWork();
			foreach($tasks as $k=>$task){
				switch ($action){
					case 'tag':
						if ($task->canEdit(logged_user())){
							$tag = $options;
							Tags::addObjectTag($tag, $task);
							ApplicationLogs::createLog($task, $task->getWorkspaces(), ApplicationLogs::ACTION_TAG,false,null,true,$tag);
							$tasksToReturn[] = $task->getArrayInfo();
						}
						break;
					case 'untag':
						if ($task->canEdit(logged_user())){
							$tag = $options;
							if ($tag != ''){
								$task->deleteTag($tag);
							}else{
								$task->clearTags();
							}
							//ApplicationLogs::createLog($task, $task->getWorkspaces(), ApplicationLogs::ACTION_TAG,false,null,true,$tag);
							$tasksToReturn[] = $task->getArrayInfo();
						}
						break;
					case 'complete':
						if ($task->canEdit(logged_user())){
							$task->completeTask();
							$tasksToReturn[] = $task->getArrayInfo();
						}
						break;
					case 'delete':
						if ($task->canDelete(logged_user())){
							$tasksToReturn[] = array('id' => $task->getId());
							$subtasks = $task->getAllSubtasks();
							$task->trash();							
							ApplicationLogs::createLog($task, $task->getWorkspaces(), ApplicationLogs::ACTION_TRASH);
							foreach ($subtasks as $st){
								ApplicationLogs::createLog($st, $st->getWorkspaces(), ApplicationLogs::ACTION_TRASH);
							}
						}
						break;
					case 'archive':
						if ($task->canEdit(logged_user())){
							$tasksToReturn[] = $task->getArrayInfo();
							$subtasks = $task->getAllSubtasks();
							$task->archive();
							ApplicationLogs::createLog($task, $task->getWorkspaces(), ApplicationLogs::ACTION_ARCHIVE);
							foreach ($subtasks as $st){
								ApplicationLogs::createLog($st, $st->getWorkspaces(), ApplicationLogs::ACTION_ARCHIVE);
							}
						}
						break;
					case 'start_work':
						if ($task->canEdit(logged_user())){
							$task->addTimeslot(logged_user());
							ApplicationLogs::createLog($task, $task->getWorkspaces(), ApplicationLogs::ACTION_EDIT);
								
							$tasksToReturn[] = $task->getArrayInfo();
							$showSuccessMessage = false;
						}
						break;
					case 'close_work':
						if ($task->canEdit(logged_user())){
							$task->closeTimeslots(logged_user(),array_var($_POST, 'options'));
							ApplicationLogs::createLog($task, $task->getWorkspaces(), ApplicationLogs::ACTION_EDIT);
								
							$tasksToReturn[] = $task->getArrayInfo();
							$showSuccessMessage = false;
						}
						break;
					case 'pause_work':
						if ($task->canEdit(logged_user())){
							$task->pauseTimeslots(logged_user());
							$tasksToReturn[] = $task->getArrayInfo();
							$showSuccessMessage = false;
						}
						break;
					case 'resume_work':
						if ($task->canEdit(logged_user())){
							$task->resumeTimeslots(logged_user());
							$tasksToReturn[] = $task->getArrayInfo();
							$showSuccessMessage = false;
						}
						break;
					case 'markasread':
						$task->setIsRead(logged_user()->getId(),true);
						$tasksToReturn[] = $task->getArrayInfo();
						$showSuccessMessage = false;
						break;
					case 'markasunread':
						$task->setIsRead(logged_user()->getId(),false);
						$tasksToReturn[] = $task->getArrayInfo();
						$showSuccessMessage = false;
						break;
					default:
						DB::rollback();
						flash_error(lang('invalid action'));
						return;
				} // end switch
			} // end foreach
			DB::commit();
			if (count($tasksToReturn) < count($tasks)) {
				flash_error(lang('tasks updated') . '. ' . lang('some tasks could not be updated due to permission restrictions'));
			} else if ($showSuccessMessage) {
				flash_success(lang('tasks updated'));
			}
				
			ajx_extra_data(array('tasks' => $tasksToReturn));
		} catch(Exception $e){
			DB::rollback();
			flash_error($e->getMessage());
		}

	}

	function new_list_tasks(){
		//load config options into cache for better performance
		load_user_config_options_by_category_name('task panel');
		 
		// get query parameters, save user preferences if necessary
		$status = array_var($_GET,'status',null);
		if (is_null($status) || $status == '') {
			$status = user_config_option('task panel status',2);
		} else
		if (user_config_option('task panel status') != $status) {
			set_user_config_option('task panel status', $status, logged_user()->getId());
		}

		$previous_filter = user_config_option('task panel filter','no_filter');
		$filter = array_var($_GET,'filter');
		if (is_null($filter) || $filter == '') {
			$filter = user_config_option('task panel filter','no_filter');
		} else
		if (user_config_option('task panel filter') != $filter) {
			set_user_config_option('task panel filter', $filter, logged_user()->getId());
		}

		if ($filter != 'no_filter'){
			$filter_value = array_var($_GET,'fval');
			if (is_null($filter_value) || $filter_value == '') {
				$filter_value = user_config_option('task panel filter value',logged_user()->getCompanyId() . ':' . logged_user()->getId());
				set_user_config_option('task panel filter value', $filter_value, logged_user()->getId());
				$filter = $previous_filter;
				set_user_config_option('task panel filter', $filter, logged_user()->getId());
			} else
			if (user_config_option('task panel filter value') != $filter_value) {
				set_user_config_option('task panel filter value', $filter_value, logged_user()->getId());
			}
		}
		$isJson = array_var($_GET,'isJson',false);
		if ($isJson) ajx_current("empty");

		$project = active_project();
		$tag = active_tag();

		$template_condition = "`is_template` = 0 ";

		//Get the task query conditions
		$task_filter_condition = "";
		switch($filter){
			case 'assigned_to':
				$assigned_to = explode(':', $filter_value);
				$assigned_to_user = array_var($assigned_to,1,0);
				$assigned_to_company = array_var($assigned_to,0,0);
				if ($assigned_to_user > 0) {
					$task_filter_condition = " AND (`assigned_to_user_id` = " . $assigned_to_user
							. " OR (`assigned_to_company_id` = " . $assigned_to_company . " AND `assigned_to_user_id` = 0)) ";
				}
				else
				if ($assigned_to_company > 0) {
					$task_filter_condition = " AND  `assigned_to_company_id` = " . $assigned_to_company . " AND `assigned_to_user_id` = 0";
				} else {
					if ($assigned_to_company == -1 && $assigned_to_user == -1)
					$task_filter_condition = "  AND `assigned_to_company_id` = 0 AND `assigned_to_user_id` = 0 ";
				}
				break;
			case 'assigned_by':
				if ($filter_value != 0) {
					$task_filter_condition = " AND  `assigned_by_id` = " . $filter_value . " ";
				}
				break;
			case 'created_by':
				if ($filter_value != 0) {
					$task_filter_condition = " AND  `created_by_id` = " . $filter_value . " ";
				}
				break;
			case 'completed_by':
				if ($filter_value != 0) {
					$task_filter_condition = " AND  `completed_by_id` = " . $filter_value . " ";
				}
				break;
			case 'milestone':
				$task_filter_condition = " AND  `milestone_id` = " . $filter_value . " ";
				break;
			case 'priority':
				$task_filter_condition = " AND  `priority` = " . $filter_value . " ";
				break;
			case 'subtype':
				if ($filter_value != 0) {
					$task_filter_condition = " AND  `object_subtype` = " . $filter_value . " ";
				}
				break;
			case 'no_filter':
				$task_filter_condition = "";
				break;
			default:
				flash_error(lang('task filter criteria not recognised', $filter));
		}

		if ($project instanceof Project) {
			$pids = $project->getAllSubWorkspacesQuery(true);
			$projectstr = " AND " . ProjectTasks::getWorkspaceString($pids);
		} else {
			$pids = "";
			$projectstr = "";
		}
		$permissions = " AND " . permissions_sql_for_listings(ProjectTasks::instance(), ACCESS_LEVEL_READ, logged_user());
		

		$task_status_condition = "";
		switch($status){
			case 0: // Incomplete tasks
				$task_status_condition = " AND `completed_on` = " . DB::escape(EMPTY_DATETIME);
				break;
			case 1: // Complete tasks
				$task_status_condition = " AND `completed_on` > " . DB::escape(EMPTY_DATETIME);
				break;
			case 10: // Active tasks
				$now=date('Y-m-j 00:00:00');
				$task_status_condition = " AND `completed_on` = " . DB::escape(EMPTY_DATETIME) . " AND `start_date` <= '$now'";
				break;
			case 11: // Overdue tasks
				$now=date('Y-m-j 00:00:00');
				$task_status_condition = " AND `completed_on` = " . DB::escape(EMPTY_DATETIME) . " AND `due_date` < '$now'";
				break;
			case 12: // Today tasks
				$now=date('Y-m-j 00:00:00');
				$task_status_condition = " AND `completed_on` = " . DB::escape(EMPTY_DATETIME) . " AND `due_date` = '$now'";
				break;
			case 13: // Today + Overdue tasks
				$now=date('Y-m-j 00:00:00');
				$task_status_condition = " AND `completed_on` = " . DB::escape(EMPTY_DATETIME) . " AND `due_date` <= '$now'";
				break;
			case 14: // Today + Overdue tasks
				$now=date('Y-m-j 00:00:00');
				$task_status_condition = " AND `completed_on` = " . DB::escape(EMPTY_DATETIME) . " AND `due_date` <= '$now'";
				break;
			case 20: // Actives task by current user
			$now=date('Y-m-j 00:00:00');
				$task_status_condition = " AND `completed_on` = " . DB::escape(EMPTY_DATETIME) . " AND `start_date` <= '$now' AND `assigned_to_user_id` = " . logged_user()->getId();
				break;
			case 21: // Subscribed tasks by current user
				$res20=DB::execute("SELECT object_id FROM ". TABLE_PREFIX . "object_subscriptions WHERE `object_manager` LIKE 'ProjectTasks' AND `user_id` = " . logged_user()->getId());
				$subs_rows=$res20->fetchAll($res20);
				foreach($subs_rows as $row) $subs[]=$row['object_id'];
				unset($res20,$subs_rows,$row);
				$now=date('Y-m-j 00:00:00');
				$task_status_condition = " AND `completed_on` = " . DB::escape(EMPTY_DATETIME) . " AND `id` IN(" . implode(',',$subs) . ")";
				break;				
			case 2: // All tasks
				break;
			default:
				throw new Exception('Task status "' . $status . '" not recognised');
		}

		if (!$tag) {
			$tagstr = "";
		} else {
			$tagstr = " AND (select count(*) from " . TABLE_PREFIX . "tags where " .
			TABLE_PREFIX . "project_tasks.id = " . TABLE_PREFIX . "tags.rel_object_id and " .
			TABLE_PREFIX . "tags.tag = ".DB::escape($tag)." and " . TABLE_PREFIX . "tags.rel_object_manager ='ProjectTasks' ) > 0 ";
		}

		$conditions = $template_condition . $task_filter_condition . $task_status_condition . $permissions . $tagstr . $projectstr . " AND `trashed_by_id` = 0 AND `archived_by_id` = 0";

		//Now get the tasks
		$tasks = ProjectTasks::findAll(array('conditions' => $conditions, 'order' => 'created_on DESC', 'limit' => user_config_option('task_display_limit') > 0 ? user_config_option('task_display_limit') + 1 : null));
		ProjectTasks::populateData($tasks);
		//Find all internal milestones for these tasks
		$internalMilestones = ProjectMilestones::getProjectMilestones(active_or_personal_project(), null, 'DESC', "", null, null, null, ($status == 0), false);
		ProjectMilestones::populateData($internalMilestones);
		
		//Find all external milestones for these tasks
		$milestone_ids = array();
		if($tasks){
			foreach ($tasks as $task){
				if ($task->getMilestoneId() != 0) {
					$milestone_ids[$task->getMilestoneId()]	= $task->getMilestoneId();
				}
			}
		}

		$milestone_ids_condition = '';
		if (count($milestone_ids) > 0){
			$milestone_ids_condition = ' OR id in (' . implode(',',$milestone_ids) . ')';
		}

		if ($status == 0) {
			$pendingstr = " AND `completed_on` = " . DB::escape(EMPTY_DATETIME) . " ";
		} else {
			$pendingstr = "";
		}
		
		if (!$tag) {
			$tagstr = "";
		} else {
			$tagstr = " AND (select count(*) from " . TABLE_PREFIX . "tags where " .
			TABLE_PREFIX . "project_milestones.id = " . TABLE_PREFIX . "tags.rel_object_id and " .
			TABLE_PREFIX . "tags.tag = ".DB::escape($tag)." and " . TABLE_PREFIX . "tags.rel_object_manager ='ProjectMilestones' ) > 0 ";
		}
		$projectstr = " AND (" . ProjectMilestones::getWorkspaceString($pids) . $milestone_ids_condition . ")";
		$archivedstr = " AND `archived_by_id` = 0 ";
		$milestone_conditions = " `is_template` = false " . $archivedstr . $projectstr . $pendingstr;
		$externalMilestonesTemp = ProjectMilestones::findAll(array('conditions' => $milestone_conditions));
		$externalMilestones = array();
		if($externalMilestonesTemp){
			foreach ($externalMilestonesTemp as $em){
				$found = false;
				if($internalMilestones){
					foreach ($internalMilestones as $im){
						if ($im->getId() == $em->getId()){
							$found = true;
							break;
						}
					}
				}
				if (!$found) {
					$externalMilestones[] = $em;
				}
			}
		}
		ProjectMilestones::populateData($externalMilestones);

		//Get Users Info
		if (logged_user()->isMemberOfOwnerCompany()) {
			$users = Users::getAll();
			$allUsers = array();
		} else {
			$users = logged_user()->getAssignableUsers();
			$allUsers = Users::getAll();
		}
		//Get Companies Info
		if (logged_user()->isMemberOfOwnerCompany()) {
			$companies = Companies::getCompaniesWithUsers();
		} else {
			$companies = logged_user()->getAssignableCompanies();
		}

		if (!$isJson){
			if(active_project() instanceof Project) {
				$task_templates = WorkspaceTemplates::getTemplatesByWorkspace(active_project()->getId());
			} else {
				$task_templates = array();
			}
			tpl_assign('project_templates', $task_templates);
			tpl_assign('all_templates', COTemplates::findAll());
			if (user_config_option('task_display_limit') > 0 && count($tasks) > user_config_option('task_display_limit')) {
				tpl_assign('displayTooManyTasks', true);
				array_pop($tasks);
			}
			tpl_assign('tasks', $tasks);
				
			tpl_assign('object_subtypes', ProjectCoTypes::getObjectTypesByManager('ProjectTasks'));
			tpl_assign('internalMilestones', $internalMilestones);
			tpl_assign('externalMilestones', $externalMilestones);
			tpl_assign('users', $users);
			tpl_assign('allUsers', $allUsers);
			tpl_assign('companies', $companies);
			tpl_assign('userPreferences', array(
				'filterValue' => isset($filter_value)?$filter_value:'',
				'filter' => $filter,
				'status' => $status,
				'showWorkspaces' => user_config_option('tasksShowWorkspaces',1),
				'showTime' => user_config_option('tasksShowTime',0),
				'showDates' => user_config_option('tasksShowDates',0),
				'showTags' => user_config_option('tasksShowTags',0),
				'showEmptyMilestones' => user_config_option('tasksShowEmptyMilestones',0),
				'groupBy' => user_config_option('tasksGroupBy','milestone'),
				'orderBy' => user_config_option('tasksOrderBy','priority'),
				'defaultNotifyValue' => user_config_option('can notify from quick add')
			));
			ajx_set_no_toolbar(true);
		}
	}

	/**
	 * View task page
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function view_task() {
		$task_list = ProjectTasks::findById(get_id());
		$this->addHelper('textile');
		$this->setTemplate('view_list');

		if(!($task_list instanceof ProjectTask)) {
			flash_error(lang('task list dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$task_list->canView(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		//read object for this user
		$task_list->setIsRead(logged_user()->getId(),true);
		
		tpl_assign('task_list', $task_list);

		/*if(active_project()){
			$open=active_project()->getOpenTasks();
			$comp=active_project()->getCompletedTasks();
			}
			else{
			$projects=active_projects();
			foreach ($projects as $p){
			$open[] = $p->getOpenTasks();
			$comp[] = $p->getCompletedTasks();
			}
			}*/
		$this->addHelper('textile');
		ajx_extra_data(array("title" => $task_list->getTitle(), 'icon'=>'ico-task'));
		ajx_set_no_toolbar(true);
		
		ApplicationReadLogs::createLog($task_list, $task_list->getWorkspaces(), ApplicationReadLogs::ACTION_READ);
	} // view_task

	function print_task() {
		$this->setLayout("html");
		$task = ProjectTasks::findById(get_id());

		if(!($task instanceof ProjectTask)) {
			flash_error(lang('task list dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$task->canView(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		tpl_assign('task', $task);
		$this->setTemplate('print_task');
	} // print_task

	/**
	 * Add new task
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function add_task() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$project = active_or_personal_project();
		if(!ProjectTask::canAdd(logged_user(), $project)) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$task = new ProjectTask();
		$task_data = array_var($_POST, 'task');
		$default_assigned_to = logged_user()->getId().":".logged_user()->getCompanyId();
		if(!is_array($task_data)) {
			$task_data = array(
				'milestone_id' => array_var($_POST, 'milestone_id',0),
				'project_id' => array_var($_POST, 'project_id',active_or_personal_project()->getId()),
				'title' => array_var($_POST, 'title', ''),
				'assigned_to' => array_var($_POST, 'assigned_to', $default_assigned_to),
				'parent_id' => array_var($_POST, 'parent_id', 0),
				'priority' => array_var($_POST, 'priority', ProjectTasks::PRIORITY_NORMAL),
				'text' => array_var($_POST, 'text', ''),
				'start_date' => getDateValue(array_var($_POST, 'task_start_date', '')),
				'due_date' => getDateValue(array_var($_POST, 'task_due_date', '')),
				'is_template' => array_var($_POST, "is_template", array_var($_GET, "is_template", false)),
				'tags' => array_var($_POST, "tags", ''),
				'object_subtype' => array_var($_POST, "object_subtype", config_option('default task co type')),
				'send_notification' => array_var($_POST, 'notify') && array_var($_POST, 'notify') == 'true'
			); // array
			$from_email = array_var($_GET, 'from_email');
			$email = MailContents::findById($from_email);
			if ($email instanceof MailContent) {
				$task_data['title'] = $email->getSubject();
				$task_data['text'] = lang('create task from email description', $email->getSubject(), $email->getFrom(), $email->getTextBody());
				$task_data['tags'] = implode(", ", $email->getTagNames());
				tpl_assign('from_email', $email);
			}
		} // if
		
		if (array_var($_GET, 'replace')) {
			ajx_replace(true);
		}

		tpl_assign('task_data', $task_data);
		tpl_assign('task', $task);

		if (is_array(array_var($_POST, 'task'))) {
			$proj = Projects::findById(array_var($task_data, 'project_id'));
			if ($proj instanceof Project) {
				$project = $proj;
			}
			// order
			$task->setOrder(ProjectTasks::maxOrder(array_var($task_data, "parent_id", 0), array_var($task_data, "milestone_id", 0)));
				
			$task_data['due_date'] = getDateValue(array_var($_POST, 'task_due_date'));
			$task_data['start_date'] = getDateValue(array_var($_POST, 'task_start_date'));
			try {
				$err_msg = $this->setRepeatOptions($task_data);
				if ($err_msg) {
					flash_error($err_msg);
					ajx_current("empty");
					return;
				}
				
				$task->setFromAttributes($task_data);

				$totalMinutes = (array_var($task_data, 'time_estimate_hours',0) * 60) +
						(array_var($task_data, 'time_estimate_minutes',0));
				$task->setTimeEstimate($totalMinutes);

				$task->setIsPrivate(false); // Not used, but defined as not null.
				// Set assigned to
				$assigned_to = explode(':', array_var($task_data, 'assigned_to', ''));
				$company_id = array_var($assigned_to, 0, 0);
				$user_id = array_var($assigned_to, 1, 0);
				$can_assign = can_assign_task_to_company_user(logged_user(), $task, $company_id, $user_id);
				if ($can_assign !== true) {
					flash_error($can_assign);
					ajx_current("empty");
					return;
				}
				$task->setAssignedToCompanyId($company_id);
				$task->setAssignedToUserId($user_id);

				$id = array_var($_GET, 'id', 0);
				$parent = ProjectTasks::findById($id);
				if ($parent instanceof ProjectTask) {
					$task->setParentId($id);
					if ($parent->getIsTemplate()) {
						$task->setIsTemplate(true);
					}
				}

				if ($task->getParentId() > 0 && $task->hasChild($task->getParentId())) {
					flash_error(lang('task child of child error'));
					ajx_current("empty");
					return;
				}

				//Add handins
				$handins = array();
				for($i = 0; $i < 4; $i++) {
					if(isset($task_data["handin$i"]) && is_array($task_data["handin$i"]) && (trim(array_var($task_data["handin$i"], 'title')) <> '')) {
						$assigned_to = explode(':', array_var($task_data["handin$i"], 'assigned_to', ''));
						$handins[] = array(
							'title' => array_var($task_data["handin$i"], 'title'),
							'responsible_company_id' => array_var($assigned_to, 0, 0),
							'responsible_user_id' => array_var($assigned_to, 1, 0)
						); // array
					} // if
				} // for


				DB::beginWork();
				$task->save();
				//$task->setProject($project);
				//echo 'pepe'; DB::rollback(); die();
				$task->setTagsFromCSV(array_var($task_data, 'tags'));
					
				foreach($handins as $handin_data) {
					$handin = new ObjectHandin();
					$handin->setFromAttributes($handin_data);
					$handin->setObjectId($task->getId());
					$handin->setObjectManager(get_class($task->manager()));
					$handin->save();
				} // foreach*/

				if (array_var($_GET, 'copyId', 0) > 0) {
					// copy remaining stuff from the task with id copyId
					$toCopy = ProjectTasks::findById(array_var($_GET, 'copyId'));
					if ($toCopy instanceof ProjectTask) {
						ProjectTasks::copySubTasks($toCopy, $task, array_var($task_data, 'is_template', false));
					}
				}
				
				//Link objects
				$object_controller = new ObjectController();
				if ($parent instanceof ProjectTask) {
					// task is being added as subtask of another, so place in same workspace
					$task->addToWorkspace($parent->getProject());
				} else {
					$object_controller->add_to_workspaces($task);
				}
				$object_controller->link_to_new_object($task);
				$object_controller->add_subscribers($task);
				$object_controller->add_custom_properties($task);
				$object_controller->add_reminders($task);

				ApplicationLogs::createLog($task, $task->getWorkspaces(), ApplicationLogs::ACTION_ADD);

				DB::commit();

				// notify asignee
				if(array_var($task_data, 'send_notification') == 'checked') {
					try {
						Notifier::taskAssigned($task);
					} catch(Exception $e) {
						evt_add("debug", $e->getMessage());
					} // try
				}

				if ($task->getIsTemplate()) {
					flash_success(lang('success add template', $task->getTitle()));
				} else {
					flash_success(lang('success add task list', $task->getTitle()));
				}
				if (array_var($task_data, 'inputtype') != 'taskview') {
					ajx_current("back");
				} else {
					ajx_current("reload");
				}

			} catch(Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			} // try
		} // if
	} // add_task
	
	/**
	 * Copy task
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function copy_task() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$project = active_or_personal_project();
		if(!ProjectTask::canAdd(logged_user(), $project)) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$id = get_id();
		$task = ProjectTasks::findById($id);
		if (!$task instanceof ProjectTask) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$task_data = array(
			'milestone_id' => $task->getMilestoneId(),
			'title' => $task->getIsTemplate() ? $task->getTitle() : lang("copy of", $task->getTitle()),
			'assigned_to' => $task->getAssignedToCompanyId() . ":" . $task->getAssignedToUserId(),
			'parent_id' => $task->getParentId(),
			'priority' => $task->getPriority(),
			'tags' => implode(",", $task->getTagNames()),
			'project_id' => $task->getProjectId(),
			'time_estimate' => $task->getTimeEstimate(),
			'text' => $task->getText(),
			'copyId' => $task->getId(),
		); // array
		if ($task->getStartDate() instanceof DateTimeValue) {
			$task_data['start_date'] = $task->getStartDate()->getTimestamp();
		}
		if ($task->getDueDate() instanceof DateTimeValue) {
			$task_data['due_date'] = $task->getDueDate()->getTimestamp();
		}

		$newtask = new ProjectTask();
		tpl_assign('task_data', $task_data);
		tpl_assign('task', $newtask);
		tpl_assign('base_task', $task);
		$this->setTemplate("add_task");
	} // copy_task


	/**
	 * Edit task
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function edit_task() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$this->setTemplate('add_task');

		$task = ProjectTasks::findById(get_id());
		if(!($task instanceof ProjectTask)) {
			flash_error(lang('task list dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$task->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$task_data = array_var($_POST, 'task');
		if(!is_array($task_data)) {
			$this->getRepeatOptions($task, $occ, $rsel1, $rsel2, $rsel3, $rnum, $rend, $rjump);
				
			$tag_names = $task->getTagNames();
			$task_data = array(
				'title' => array_var($_POST, 'title', $task->getTitle()),
				'text' => $task->getText(),
				'milestone_id' => array_var($_POST, 'milestone_id',$task->getMilestoneId()),
				'due_date' => getDateValue(array_var($_POST, 'task_due_date'), $task->getDueDate()),
				'start_date' => getDateValue(array_var($_POST, 'task_start_date', $task->getStartDate())),
				'parent_id' => $task->getParentId(),
				'project_id' => array_var($_POST, 'project_id',$task->getProjectId()),
				'tags' => is_array($tag_names) && count($tag_names) ? implode(', ', $tag_names) : '',
				'is_private' => $task->isPrivate(),
				'assigned_to' => array_var($_POST, 'assigned_to', $task->getAssignedToCompanyId() . ':' . $task->getAssignedToUserId()),
				'priority' => array_var($_POST, 'priority', $task->getPriority()),
				'send_notification' => array_var($_POST, 'notify') == 'true',
				'time_estimate' => $task->getTimeEstimate(),
				'forever' => $task->getRepeatForever(),
				'rend' => $rend,
				'rnum' => $rnum,
				'rjump' => $rjump,
				'rsel1' => $rsel1,
				'rsel2' => $rsel2,
				'rsel3' => $rsel3,
				'occ' => $occ,
				'repeat_by' => $task->getRepeatBy(),
				'object_subtype' => array_var($_POST, "object_subtype", ($task->getObjectSubtype() != 0 ? $task->getObjectSubtype() : config_option('default task co type'))),
			); // array
			$handins = ObjectHandins::getAllHandinsByObject($task);
			$id = 0;
			if($handins){
				foreach($handins as $handin){
					$task_data['handin'.$id] =array(
		              'title' => $handin->getTitle(),
		              'assigned_to' => $handin->getResponsibleCompanyId() . ':' . $handin->getResponsibleUserId()
					); // array
					$id=$id +1;
					if($id>3) break;
				} // foreach
			} // if
		} // if

		tpl_assign('task', $task);
		tpl_assign('task_data', $task_data);

		if(is_array(array_var($_POST, 'task'))) {
			
			//MANAGE CONCURRENCE WHILE EDITING
			$upd = array_var($_POST, 'updatedon');
			if ($upd && $task->getUpdatedOn()->getTimestamp() > $upd && !array_var($_POST,'merge-changes') == 'true')
			{
				ajx_current('empty');
				evt_add("handle edit concurrence", array(
					"updatedon" => $task->getUpdatedOn()->getTimestamp(),
					"genid" => array_var($_POST,'genid')
				));
				return;
			}
			if (array_var($_POST,'merge-changes') == 'true'){					
				$this->setTemplate('view_list');
				$edited_task = ProjectTasks::findById($task->getId());
				ajx_set_no_toolbar(true);
				ajx_set_panel(lang ('tab name',array('name'=>$edited_task->getTitle())));
				tpl_assign('task_list', $edited_task);
				ajx_extra_data(array("title" => $edited_task->getTitle(), 'icon'=>'ico-task'));				
				return;
			}
			
			$old_owner = $task->getAssignedTo();
			if (array_var($task_data, 'parent_id') == $task->getId()) {
				flash_error(lang("task own parent error"));
				ajx_current("empty");
				return;
			}
			$old_is_private = $task->isPrivate();
			$old_project_id = $task->getProjectId();
			$project_id = array_var($_POST, 'ws_ids', 0);
			if ($old_project_id != $project_id) {
				$newProject = Projects::findById($project_id);
				if (!$newProject instanceof Project || !$task->canAdd(logged_user(), $newProject)) {
					flash_error(lang('no access permissions'));
					ajx_current("empty");
					return;
				}
			}
				
			$task_data['due_date'] = getDateValue(array_var($_POST, 'task_due_date'));
			$task_data['start_date'] = getDateValue(array_var($_POST, 'task_start_date'));
				
			try {
				$err_msg = $this->setRepeatOptions($task_data);
				if ($err_msg) {
					flash_error($err_msg);
					ajx_current("empty");
					return;
				}
				
				
				if (!isset($task_data['parent_id'])) {
					$task_data['parent_id'] = 0;	
				}
				
				$was_template = $task->getIsTemplate();
				$task->setFromAttributes($task_data);
				$task->setIsTemplate($was_template); // is_template value must not be changed from ui
				
				// Set assigned to
				$assigned_to = explode(':', array_var($task_data, 'assigned_to', ''));
				$company_id = array_var($assigned_to, 0, 0);
				$user_id = array_var($assigned_to, 1, 0);
				$can_assign = can_assign_task_to_company_user(logged_user(), $task, $company_id, $user_id);
				if ($can_assign !== true) {
					flash_error($can_assign);
					return;
				}
				$task->setAssignedToCompanyId($company_id);
				$task->setAssignedToUserId($user_id);
				if(!logged_user()->isMemberOfOwnerCompany()) $task->setIsPrivate($old_is_private);

				$totalMinutes = (array_var($task_data, 'time_estimate_hours') * 60) +
						(array_var($task_data, 'time_estimate_minutes'));
				$task->setTimeEstimate($totalMinutes);

				//Add handins
				$handins = array();
				for($i = 0; $i < 4; $i++) {
					if(isset($task_data["handin$i"]) && is_array($task_data["handin$i"]) && (trim(array_var($task_data["handin$i"], 'title')) <> '')) {
						$assigned_to = explode(':', array_var($task_data["handin$i"], 'assigned_to', ''));
						$handins[] = array(
	              'title' => array_var($task_data["handin$i"], 'title'),
	              'responsible_company_id' => array_var($assigned_to, 0, 0),
	              'responsible_user_id' => array_var($assigned_to, 1, 0)
						); // array
					} // if
				} // for

				if ($task->getParentId() > 0 && $task->hasChild($task->getParentId())) {
					flash_error(lang('task child of child error'));
					ajx_current("empty");
					return;
				}

				DB::beginWork();
				$task->save();
				$task->setTagsFromCSV(array_var($task_data, 'tags'));

				$object_controller = new ObjectController();
				$object_controller->add_to_workspaces($task, !$task->getIsTemplate());
				$object_controller->link_to_new_object($task);
				$object_controller->add_subscribers($task);
				$object_controller->add_custom_properties($task);
				$object_controller->add_reminders($task);
				
				// apply values to subtasks
				$subtasks = $task->getAllSubTasks();
				$project = $task->getProject();
				$milestone_id = $task->getMilestoneId();
				$apply_ws = array_var($task_data, 'apply_ws_subtasks') == "checked";
				$apply_ms = array_var($task_data, 'apply_milestone_subtasks') == "checked";
				$apply_at = array_var($task_data, 'apply_assignee_subtasks', '') == "checked";
				foreach ($subtasks as $sub) {
					$modified = false;
					if ($apply_at || !$sub->getAssignedTo() instanceof ApplicationDataObject) {
						$sub->setAssignedToCompanyId($company_id);
						$sub->setAssignedToUserId($user_id);
						$modified = true;
					}
					if ($apply_ws) {
						$sub->setProject($project);
						$modified = true;
					}
					if ($apply_ms) {
						$sub->setMilestoneId($milestone_id);
						$modified = true;
					}
					if ($modified) {
						$sub->save();
					}
				}

				$task->resetIsRead();
				
				ApplicationLogs::createLog($task, $task->getWorkspaces(), ApplicationLogs::ACTION_EDIT);

				DB::commit();

				try {
					if(array_var($task_data, 'send_notification') == 'checked') {
						$new_owner = $task->getAssignedTo();
						if($new_owner instanceof User) {
							Notifier::taskAssigned($task);
						} // if
					} // if
				} catch(Exception $e) {

				} // try

				flash_success(lang('success edit task list', $task->getTitle()));
				ajx_current("back");

			} catch(Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			} // try
		} // if
	} // edit_task

	/**
	 * Delete task
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function delete_task() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		ajx_current("empty");
		$project = active_or_personal_project();
		$task = ProjectTasks::findById(get_id());
		if (!($task instanceof ProjectTask)) {
			flash_error(lang('task dnx'));
			return;
		} // if

		if (!$task->canDelete(logged_user())) {
			flash_error(lang('no access permissions'));
			return;
		} // if

		try {
			DB::beginWork();
			$is_template = $task->getIsTemplate();
			$task->trash();
			ApplicationLogs::createLog($task, $task->getWorkspaces(), ApplicationLogs::ACTION_TRASH);
			DB::commit();

			if ($is_template) {
				flash_success(lang('success delete template', $task->getTitle()));
			} else {
				flash_success(lang('success delete task list', $task->getTitle()));
			}
			if (array_var($_GET, 'quick', false)) {
				ajx_current('empty');
			} else if (array_var($_GET, 'taskview', false)){
				ajx_current('reload');
			} else {
				ajx_current('back');
			}
		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error delete task list'));
		} // try
	} // delete_task


	// ---------------------------------------------------
	//  Tasks
	// ---------------------------------------------------

	private function getNextRepetitionDates($task, &$new_st_date, &$new_due_date) {
		$new_due_date = null;
		$new_st_date = null;

		if ($task->getStartDate() instanceof DateTimeValue ) {
			$new_st_date = new DateTimeValue($task->getStartDate()->getTimestamp());
		}
		if ($task->getDueDate() instanceof DateTimeValue ) {
			$new_due_date = new DateTimeValue($task->getDueDate()->getTimestamp());
		}
		if ($task->getRepeatD() > 0) {
			if ($new_st_date instanceof DateTimeValue) {
				$new_st_date = $new_st_date->add('d', $task->getRepeatD());
			}
			if ($new_due_date instanceof DateTimeValue) {
				$new_due_date = $new_due_date->add('d', $task->getRepeatD());
			}
		} else if ($task->getRepeatM() > 0) {
			if ($new_st_date instanceof DateTimeValue) {
				$new_st_date = $new_st_date->add('M', $task->getRepeatM());
			}
			if ($new_due_date instanceof DateTimeValue) {
				$new_due_date = $new_due_date->add('M', $task->getRepeatM());
			}
		} else if ($task->getRepeatY() > 0) {
			if ($new_st_date instanceof DateTimeValue) {
				$new_st_date = $new_st_date->add('y', $task->getRepeatY());
			}
			if ($new_due_date instanceof DateTimeValue) {
				$new_due_date = $new_due_date->add('y', $task->getRepeatY());
			}
		}
	}

	function generate_new_repetitive_instance() {
		ajx_current("empty");
		$task = ProjectTasks::findById(get_id());
		if (!($task instanceof ProjectTask)) {
			flash_error(lang('task dnx'));
			return;
		} // if

		if (!$task->isRepetitive()) {
			flash_error(lang('task not repetitive'));
			return;
		}

		$this->getNextRepetitionDates($task, $new_st_date, $new_due_date);
			
		// if this is the last task of the repetetition, do not generate a new instance
		if ($task->getRepeatNum() > 0) {
			$task->setRepeatNum($task->getRepeatNum() - 1);
			if ($task->getRepeatNum() == 0) {
				flash_error(lang('task cannot be instantiated more times'));
				return;
			}
		}
		if ($task->getRepeatEnd() instanceof DateTimeValue) {
			if ($task->getRepeatBy() == 'start_date' && $new_st_date > $task->getRepeatEnd() ||
			$task->getRepeatBy() == 'due_date' && $new_due_date > $task->getRepeatEnd() ) {
				flash_error(lang('task cannot be instantiated more times'));
				return;
			}
		}
		try {
			DB::beginWork();
			$new_task = $task->cloneTask();
			$new_task->save();
				
			// set next values for repetetive task
			if ($task->getStartDate() instanceof DateTimeValue ) $task->setStartDate($new_st_date);
			if ($task->getDueDate() instanceof DateTimeValue ) $task->setDueDate($new_due_date);

			$task->save();
			DB::commit();
			flash_success(lang("new task repetition generated"));
			ajx_current("reload");
		} catch (Exception $e) {
			DB::rollback();
			flash_error($e->getMessage());
		}
	}

	/**
	 * Complete task
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function complete_task() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		ajx_current("empty");
		$task = ProjectTasks::findById(get_id());
		if(!($task instanceof ProjectTask)) {
			flash_error(lang('task dnx'));
			return;
		} // if

		if(!$task->canChangeStatus(logged_user())) {
			flash_error(lang('no access permissions'));
			return;
		} // if

		try {
			DB::beginWork();
			// if task is repetitive, generate a complete instance of this task and modify repeat values
			if ($task->isRepetitive()) {
				$complete_last_task = false;
				// calculate next repetition date
				$this->getNextRepetitionDates($task, $new_st_date, $new_due_date);

				// if this is the last task of the repetetition, complete it, do not generate a new instance
				if ($task->getRepeatNum() > 0) {
					$task->setRepeatNum($task->getRepeatNum() - 1);
					if ($task->getRepeatNum() == 0)
					$complete_last_task = true;
				}
				if (!$complete_last_task && $task->getRepeatEnd() instanceof DateTimeValue) {
					if ($task->getRepeatBy() == 'start_date' && $new_st_date > $task->getRepeatEnd() ||
					$task->getRepeatBy() == 'due_date' && $new_due_date > $task->getRepeatEnd() ) {
						$complete_last_task = true;
					}
				}
				if (!$complete_last_task) {
					// generate completed task
					$new_task = $task->cloneTask(true);
					$new_task->completeTask();
						
					// set next values for repetetive task
					if ($task->getStartDate() instanceof DateTimeValue ) $task->setStartDate($new_st_date);
					if ($task->getDueDate() instanceof DateTimeValue ) $task->setDueDate($new_due_date);

					foreach ($task->getAllSubTasks() as $subt) {
						$subt->setCompletedById(0);
						$subt->setCompletedOn(EMPTY_DATETIME);
						$subt->save();
					}
					$task->save();
				} else {
					// if this is the last repetition, complete this
					$task->completeTask();
				}
			} else {
				$task->completeTask();
			}
			/*$completed_tasks = array(); //Completes the parents if all subtasks are complete
			 $parent = $task->getParent();
			 while ($parent instanceof ProjectTask && $parent->countOpenSubTasks() <= 0) {
				$parent->completeTask();
				$completed_tasks[] = $parent->getId();
				$milestone = ProjectMilestones::findById($parent->getMilestoneId());
				if ($milestone instanceof ProjectMilestones && $milestone->countOpenTasks() <= 0) {
				$milestone->setCompletedOn(DateTimeValueLib::now());
				ajx_extra_data(array("completedMilestone" => $milestone->getId()));
				}
				$parent = $parent->getParent();
				}
				ajx_extra_data(array("completedTasks" => $completed_tasks));*/
				
			//Already called in completeTask
			//ApplicationLogs::createLog($task, $task->getProject(), ApplicationLogs::ACTION_CLOSE);
			DB::commit();
			flash_success(lang('success complete task'));
				
			$redirect_to = array_var($_GET, 'redirect_to', false);
			if (array_var($_GET, 'quick', false) && !$task->isRepetitive()) {
				ajx_current("empty");
				ajx_extra_data(array("task" => $task->getArrayInfo()));
			} else {
				ajx_current("reload");
			}
		} catch(Exception $e) {
			DB::rollback();
			flash_error($e->getMessage());
		} // try
	} // complete_task

	/**
	 * Reopen completed task
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function open_task() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		ajx_current("empty");
		$task = ProjectTasks::findById(get_id());
		if(!($task instanceof ProjectTask)) {
			flash_error(lang('task dnx'));
			return;
		} // if

		if(!$task->canChangeStatus(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		try {
			DB::beginWork();
			$task->openTask();
				
			/*$opened_tasks = array();
			 $parent = $task->getParent();
			 while ($parent instanceof ProjectTask && $parent->isCompleted()) {
				$parent->openTask();
				$opened_tasks[] = $parent->getId();
				$milestone = ProjectMilestones::findById($parent->getMilestoneId());
				if ($milestone instanceof ProjectMilestones && $milestone->isCompleted()) {
				$milestone->setCompletedOn(EMPTY_DATETIME);
				ajx_extra_data(array("openedMilestone" => $milestone->getId()));
				}
				$parent = $parent->getParent();
				}
				ajx_extra_data(array("openedTasks" => $opened_tasks));*/
				
			//Already called in openTask
			//ApplicationLogs::createLog($task, $task->getProject(), ApplicationLogs::ACTION_OPEN);
			DB::commit();
				
			flash_success(lang('success open task'));
				
			$redirect_to = array_var($_GET, 'redirect_to', false);
			if (array_var($_GET, 'quick', false)) {
				ajx_current("empty");
				ajx_extra_data(array("task" => $task->getArrayInfo()));
			} else {
				ajx_current("reload");
			}
		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error open task'));
		} // try
	} // open_task

	private function getTasksAndMilestones($page, $objects_per_page, $tag=null, $order=null, $order_dir=null, $parent_task_id = null, $project = null, $tasksAndOrMilestones = 'both'){
		if(!$parent_task_id || !is_numeric($parent_task_id))
		$parent_task_id = 0;
		$parent_string = " AND parent_id = $parent_task_id ";
		$queries = ObjectController::getDashboardObjectQueries($project, $tag);
		if ($tasksAndOrMilestones == 'both') {
			$query = $queries['ProjectTasks'] . $parent_string . " UNION " . $queries['ProjectMilestones'];
		} else if ($tasksAndOrMilestones == 'tasks') {
			$query = $queries['ProjectTasks'] . $parent_string;
		} else {
			$query = $queries['ProjectMilestones'];
		}
		if ($order) {
			$query .= " order by " . $order . " ";
			if ($order_dir) {
				$query .= " " . $order_dir . " ";
			}
		} else {
			$query .= " order by last_update desc ";
		}
		if ($page && $objects_per_page) {
			$start = ($page-1) * $objects_per_page;
			$query .=  " limit " . $start . "," . $objects_per_page. " ";
		} else if ($objects_per_page) {
			$query .= " limit " . $objects_per_page;
		}

		$res = DB::execute($query);
		$objects = array();
		if (!$res) return $objects;
		$rows = $res->fetchAll();
		if (!$rows) return $objects;
		$i=1;
		foreach ($rows as $row) {
			$manager= $row['object_manager_value'];
			$id = $row['oid'];
			if ($id && $manager) {
				$obj = get_object_by_manager_and_id($id, $manager);
				if ($obj->canView(logged_user())) {
					$dash_object = $obj->getDashboardObject();
					//	$dash_object['id'] = $i++;
					$objects[] = $dash_object;
				}
			} //if($id && $manager)
		}//foreach
		return $objects;
	} //getTasksAndMilestones

	/**
	 * Counts dashboard objects
	 *
	 * @return unknown
	 */
	private function countTasksAndMilestones($tag=null, $project=null) {
		$queries = ObjectController::getDashboardObjectQueries($project,$tag,true);
		$query = $queries['ProjectTasks'] . " UNION " . $queries['ProjectMilestones'];
		$ret = 0;
		$res1 = DB::execute($query);
		if ($res1) {
			$rows = $res1->fetchAll();
			if ($rows) {
				foreach ($rows as $row) {
					if (isset($row['quantity'])) {
						$ret += $row['quantity'];
					}
				}//foreach
			}
		}
		return $ret;
	}

	/**
	 * Create a new template
	 *
	 */
	function new_template() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$project = active_or_personal_project();
		if(!ProjectTask::canAdd(logged_user(), $project)) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$id = get_id();
		$task = ProjectTasks::findById($id);
		if (!$task instanceof ProjectTask) {
			$task_data = array('is_template' => true);
		} else {
			$task_data = array(
				'milestone_id' => $task->getMilestoneId(),
				'title' => $task->getTitle(),
				'assigned_to' => $task->getAssignedToCompanyId() . ":" . $task->getAssignedToUserId(),
				'parent_id' => $task->getParentId(),
				'priority' => $task->getPriority(),
				'tags' => implode(",", $task->getTagNames()),
				'project_id' => $task->getProjectId(),
				'time_estimate' => $task->getTimeEstimate(),
				'text' => $task->getText(),
				'is_template' => true,
				'copyId' => $task->getId(),
			); // array
			if ($task->getStartDate() instanceof DateTimeValue) {
				$task_data['start_date'] = $task->getStartDate()->getTimestamp();
			}
			if ($task->getDueDate() instanceof DateTimeValue) {
				$task_data['due_date'] = $task->getDueDate()->getTimestamp();
			}
		}

		$task = new ProjectTask();
		tpl_assign('task_data', $task_data);
		tpl_assign('task', $task);
		$this->setTemplate("add_task");
	} // new_template

	/**
	 * View a message in a printer-friendly format.
	 *
	 */
	function print_view_all() {
		$this->setLayout("html");
		$this->view_tasks();
	} // print_view

	function allowed_users_to_assign() {
		$wspace_id = array_var($_GET, "ws_id");
		$comp_array = allowed_users_to_assign($wspace_id);
		$object = array(
			"totalCount" => count($comp_array),
			"start" => 0,
			"companies" => array()
		);
		$object['companies'] = $comp_array;

		ajx_extra_data($object);
		ajx_current("empty");
	} // allowed_users_to_assign

	function change_start_due_date() {
		$task = ProjectTasks::findById(get_id());
		if(!$task->canEdit(logged_user())){
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return ;
		}
	  
		$tochange = array_var($_GET, 'tochange', '');
	  
		if (($tochange == 'both' || $tochange == 'due') && $task->getDueDate() instanceof DateTimeValue ) {
			$year = array_var($_GET, 'year', $task->getDueDate()->getYear());
			$month = array_var($_GET, 'month', $task->getDueDate()->getMonth());
			$day = array_var($_GET, 'day', $task->getDueDate()->getDay());
			$new_date = new DateTimeValue(mktime(0, 0, 0, $month, $day, $year));
			$task->setDueDate($new_date);
		}
		if (($tochange == 'both' || $tochange == 'start') && $task->getStartDate() instanceof DateTimeValue ) {
			$year = array_var($_GET, 'year', $task->getStartDate()->getYear());
			$month = array_var($_GET, 'month', $task->getStartDate()->getMonth());
			$day = array_var($_GET, 'day', $task->getStartDate()->getDay());
			$new_date = new DateTimeValue(mktime(0, 0, 0, $month, $day, $year));
			$task->setStartDate($new_date);
		}
		
		try {
			DB::beginWork();
			$task->save();
			DB::commit();
	  	} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error change date'));
		} // try
		ajx_current("empty");
	}

	private function getRepeatOptions($task, &$occ, &$rsel1, &$rsel2, &$rsel3, &$rnum, &$rend, &$rjump) {
		//Repeating options
		$rsel1 = false;
		$rsel2 = false;
		$rsel3 = false;
		$rend = null;
		$rnum = null;
		$occ = 1;
		if($task->getRepeatD() > 0) {
			$occ = 2;
			$rjump = $task->getRepeatD();
		}
		if($task->getRepeatD() > 0 AND $task->getRepeatD()%7 == 0) {
			$occ = 3;
			$rjump = $task->getRepeatD() / 7;
		}
		if($task->getRepeatM() > 0) {
			$occ = 4;
			$rjump = $task->getRepeatM();
		}
		if($task->getRepeatY() > 0) {
			$occ = 5;
			$rjump = $task->getRepeatY();
		}
		if($task->getRepeatEnd()) $rend = $task->getRepeatEnd();
		if($task->getRepeatNum() > 0) $rnum = $task->getRepeatNum();
		if(!isset($rjump) || !is_numeric($rjump)) $rjump = 1;
		// decide which repeat type it is
		if($task->getRepeatForever()) $rsel1 = true; //forever
		else if(isset($rnum) AND $rnum > 0) $rsel2 = true; //repeat n-times
		else if(isset($rend) AND $rend instanceof DateTimeValue) $rsel3 = true; //repeat until
		else $rsel1 = true; // default
	}

	private function setRepeatOptions(&$task_data) {
		// repeat options
		$repeat_d = 0;
		$repeat_m = 0;
		$repeat_y = 0;
		$repeat_h = 0;
		$rend = '';
		$forever = 0;
		$jump = array_var($task_data, 'occurance_jump');

		if(array_var($task_data, 'repeat_option') == 1) $forever = 1;
		elseif(array_var($task_data, 'repeat_option') == 2) $rnum = array_var($task_data, 'repeat_num');
		elseif(array_var($task_data, 'repeat_option') == 3) $rend = getDateValue(array_var($task_data, 'repeat_end'));
		// verify the options above are valid
		if (isset($rnum) && $rnum) {
			if(!is_numeric($rnum) || $rnum < 1 || $rnum > 1000) throw new Exception(lang('repeat x times must be a valid number between 1 and 1000'));
		} else $rnum = 0;

		if (isset($jump) && $jump) {
			if(!is_numeric($jump) || $jump < 1 || $jump > 1000) throw new Exception(lang('repeat period must be a valid number between 1 and 1000'));
		} else {
			$occurrance = array_var($task_data, 'occurance');
			if ($occurrance && $occurrance != 1)
				return lang('repeat period must be a valid number between 1 and 1000');
		}

		// check for repeating options
		// 1=repeat once, 2=repeat daily, 3=weekly, 4=monthy, 5=yearly, 6=holiday repeating
		$oend = null;
		switch(array_var($task_data, 'occurance')){
			case "1":
				$forever = 0;
				$task_data['repeat_d'] = 0;
				$task_data['repeat_m'] = 0;
				$task_data['repeat_y'] = 0;
				$task_data['repeat_by'] = '';
				break;
			case "2":
				$task_data['repeat_d'] = $jump;
				if(isset($forever) && $forever == 1) $oend = null;
				else $oend = $rend;
				break;
			case "3":
				$task_data['repeat_d'] = 7 * $jump;
				if(isset($forever) && $forever == 1) $oend = null;
				else $oend = $rend;
				break;
			case "4":
				$task_data['repeat_m'] = $jump;
				if(isset($forever) && $forever == 1) $oend = null;
				else $oend = $rend;
				break;
			case "5":
				$task_data['repeat_y'] = $jump;
				if(isset($forever) && $forever == 1) $oend = null;
				else $oend = $rend;
				break;
			default: break;
		}
		$task_data['repeat_num'] = $rnum;
		$task_data['repeat_forever'] = $forever;
		$task_data['repeat_end'] =  $oend;

		if ($task_data['repeat_num'] || $task_data['repeat_forever'] || $task_data['repeat_end']) {
			if ($task_data['repeat_by'] == 'start_date' && !$task_data['start_date'] instanceof DateTimeValue ) {
				return lang('to repeat by start date you must specify task start date');
			}
			if ($task_data['repeat_by'] == 'due_date' && !$task_data['due_date'] instanceof DateTimeValue ) {
				return lang('to repeat by due date you must specify task due date');
			}
		}
		return null;
	}


} // TaskController

?>