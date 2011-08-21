<?php

/**
 * Milestone controller
 *
 * @package Taus.application
 * @subpackage controller
 * @version 1.0
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class MilestoneController extends ApplicationController {

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
	
	function view_milestones() {
		ajx_current("empty");
		$project = active_project();
		$tag = active_tag();
		$assigned_by = array_var($_GET, 'assigned_by', '');
		$assigned_to = array_var($_GET, 'assigned_to', '');
		$status = array_var($_GET, 'status', "pending");
		
//		$assigned_to = explode(':', $assigned_to);
		$to_company = array_var($assigned_to, 0, null);
		$to_user = array_var($assigned_to, 1, null);
		$assigned_by = explode(':', $assigned_by);
		$by_company = array_var($assigned_by, 0, null);
		$by_user = array_var($assigned_by, 1, null);
		
		$milestones = ProjectMilestones::getProjectMilestones($project, null, 'ASC', $tag, $to_company, $to_user, $by_user, $status == 'pending');

		$milestones_bottom_complete = array();
		$ms = array();
		foreach ($milestones as $milestone) {
			if (!$milestone->isCompleted()) {
				$milestones_bottom_complete[] = $milestone;
				$ms[] = $this->milestone_item($milestone);
			}
		}
		foreach ($milestones as $milestone) {
			if ($milestone->isCompleted()) {
				$milestones_bottom_complete[] = $milestone;
				$ms[] = $this->milestone_item($milestone);
			}
		}
		
		ajx_extra_data(array("milestones" => $ms));
		
		tpl_assign('milestones', $milestones_bottom_complete);
		tpl_assign('project', $project);
	}
	
	function quick_add_milestone() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		ajx_current("empty");
		$milestone = new ProjectMilestone();
		$milestone_data = array_var($_POST, 'milestone');
		$project = active_or_personal_project();
		if(!ProjectMilestone::canAdd(logged_user(), $project)) {
			flash_error(lang('no access permissions'));
			return;
		} // if
		
		if (is_array($milestone_data)) {
			try {
				$milestone->setFromAttributes($milestone_data);
				$now = DateTimeValueLib::now();
				$milestone->setDueDate(DateTimeValueLib::make(0, 0, 0, array_var($milestone_data, 'due_date_month', $now->getMonth()), array_var($milestone_data, 'due_date_day', $now->getDay()), array_var($milestone_data, 'due_date_year', $now->getYear())));
				// Set assigned to
				$assigned_to = explode(':', array_var($milestone_data, 'assigned_to', ''));
				$milestone->setAssignedToCompanyId(array_var($assigned_to, 0, 0));
				$milestone->setAssignedToUserId(array_var($assigned_to, 1, 0));			
				$milestone->setIsPrivate(false); // Not used, but defined as not null.
				$urgent = array_var($milestone_data, 'is_urgent') == 'checked';
				$milestone->setIsUrgent($urgent);
				DB::beginWork();
				$milestone->save();
				$milestone->setProject($project);

				ApplicationLogs::createLog($milestone, $milestone->getWorkspaces(), ApplicationLogs::ACTION_ADD);
				DB::commit();

				ajx_extra_data(array("milestone" => $this->milestone_item($milestone)));
				flash_success(lang('success add milestone', $milestone->getName()));
			} catch(Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
			} // try
		} // if
	}
	
	private function milestone_item(ProjectMilestone $milestone) {
		return array(
			"id" => $milestone->getId(),
			"title" => $milestone->getName(),
			"assignedTo" => $milestone->getAssignedToName(),
			"workspaces" => $milestone->getProject()->getName(),
			"completed" => $milestone->isCompleted(),
			"completedBy" => $milestone->getCompletedByName(),
			"isLate" => $milestone->isLate(),
			"daysLate" => $milestone->getLateInDays(),
			"duedate" => $milestone->getDueDate()->getTimestamp(),
			"urgent" => $milestone->getIsUrgent()
		);
	}
	
//
//	/**
//	 * List all milestones in specific (this) project
//	 *
//	 * @access public
//	 * @param void
//	 * @return null
//	 */
//	function index() {
//		$this->addHelper('textile');
//		$project = active_or_personal_project();
//
//		tpl_assign('late_milestones', $project->getLateMilestones());
//		tpl_assign('today_milestones', $project->getTodayMilestones());
//		tpl_assign('upcoming_milestones', $project->getUpcomingMilestones());
//		tpl_assign('completed_milestones', $project->getCompletedMilestones());
//
//	} // index

	/**
	 * Show view milestone page
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function view() {
		$this->addHelper('textile');

		$milestone = ProjectMilestones::findById(get_id());
		if(!($milestone instanceof ProjectMilestone)) {
			flash_error(lang('milestone dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$milestone->canView(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		ajx_extra_data(array("title" => $milestone->getName(), "urgent" => $milestone->getIsUrgent() ,'icon'=>'ico-milestone'));
		ajx_set_no_toolbar(true);
		tpl_assign('milestone', $milestone);
		
		ApplicationReadLogs::createLog($milestone, $milestone->getWorkspaces(), ApplicationReadLogs::ACTION_READ);
	} // view

	/**
	 * Show and process add milestone form
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function add() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$this->setTemplate('add_milestone');

		if(!ProjectMilestone::canAdd(logged_user(), active_or_personal_project())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$milestone_data = array_var($_POST, 'milestone');
		$now = DateTimeValueLib::now();
		$due_date = DateTimeValueLib::make(0, 0, 0, array_var($_GET, 'due_month', $now->getMonth()), array_var($_GET, 'due_day', $now->getDay()), array_var($_GET, 'due_year', $now->getYear()));
		if(!is_array($milestone_data)) {
			$milestone_data = array(
				'due_date' => $due_date,
				'name' => array_var($_GET, 'name', ''),
				'assigned_to' => array_var($_GET, 'assigned_to', '0:0'),
				'is_template' => array_var($_GET, "is_template", false)
			); // array
		} // if
		$milestone = new ProjectMilestone();
		tpl_assign('milestone_data', $milestone_data);
		tpl_assign('milestone', $milestone);

		if (is_array(array_var($_POST, 'milestone'))) {
			$milestone_data['due_date'] = getDateValue(array_var($milestone_data, 'due_date_value'),DateTimeValueLib::now()->beginningOfDay());
			
			$assigned_to = explode(':', array_var($milestone_data, 'assigned_to', ''));
			$milestone->setIsPrivate(false); //Mandatory to set
			$milestone->setFromAttributes($milestone_data);
			$urgent = array_var($milestone_data, 'is_urgent') == 'checked';
			$milestone->setIsUrgent($urgent);
			if(!logged_user()->isMemberOfOwnerCompany()) $milestone->setIsPrivate(false);

			$project = Projects::findById(array_var($_POST, 'ws_ids', 0));
			if (!$project instanceof Project && !ProjectMilestone::canAdd(logged_user(), $project)) {
				flash_error(lang('no access permissions'));
				ajx_current("empty");
				return;
			} // if
			$milestone->setAssignedToCompanyId(array_var($assigned_to, 0, 0));
			$milestone->setAssignedToUserId(array_var($assigned_to, 1, 0));

			try {
				DB::beginWork();

				$milestone->save();
				$milestone->setTagsFromCSV(array_var($milestone_data, 'tags'));
			    $object_controller = new ObjectController();
			    $object_controller->add_to_workspaces($milestone);
				$object_controller->link_to_new_object($milestone);
				$object_controller->add_subscribers($milestone);
				$object_controller->add_custom_properties($milestone);
				$object_controller->add_reminders($milestone);
			    
				if (array_var($_GET, 'copyId', 0) > 0) {
					// copy remaining stuff from the milestone with id copyId
					$toCopy = ProjectMilestones::findById(array_var($_GET, 'copyId'));
					if ($toCopy instanceof ProjectMilestone) {
						ProjectMilestones::copyTasks($toCopy, $milestone, array_var($milestone_data, 'is_template', false));
					}
				}
				
				ApplicationLogs::createLog($milestone, $milestone->getWorkspaces(), ApplicationLogs::ACTION_ADD);
				
				DB::commit();

				// Send notification
				try {
					if(!$milestone->getIsTemplate() && array_var($milestone_data, 'send_notification') == 'checked') {
						Notifier::milestoneAssigned($milestone); // send notification
					} // if
				} catch(Exception $e) {

				} // try

				if ($milestone->getIsTemplate()) {
					flash_success(lang('success add template', $milestone->getName()));
				} else {
					flash_success(lang('success add milestone', $milestone->getName()));
				}
				ajx_current("back");

			} catch(Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			} // try
		} // if
	} // add

	/**
	 * Show and process edit milestone form
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function edit() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$this->setTemplate('add_milestone');

		$milestone = ProjectMilestones::findById(get_id());
		if(!($milestone instanceof ProjectMilestone)) {
			flash_error(lang('milestone dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$milestone->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}

		$milestone_data = array_var($_POST, 'milestone');
		if(!is_array($milestone_data)) {
			$tag_names = $milestone->getTagNames();
			$milestone_data = array(
          'name'        => $milestone->getName(),
          'due_date'    => $milestone->getDueDate(),
          'description' => $milestone->getDescription(),
          'assigned_to' => $milestone->getAssignedToCompanyId() . ':' . $milestone->getAssignedToUserId(),
          'tags'        => is_array($tag_names) ? implode(', ', $tag_names) : '',
          'is_private'  => $milestone->isPrivate(),
          'is_urgent' 	=> $milestone->getIsUrgent()
			); // array
		} // if

		tpl_assign('milestone_data', $milestone_data);
		tpl_assign('milestone', $milestone);

		if(is_array(array_var($_POST, 'milestone'))) {
			if (array_var($milestone_data, 'due_date_value') != ''){
				$milestone_data['due_date'] = getDateValue(array_var($milestone_data, 'due_date_value'));
			} else {
				$now = DateTimeValueLib::now();
				$milestone_data['due_date'] = DateTimeValueLib::make(0, 0, 0, $now->getMonth(), $now->getDay(), $now->getYear());
			}
			
			$old_owner = $milestone->getAssignedTo(); // remember the old owner
			$assigned_to = explode(':', array_var($milestone_data, 'assigned_to', ''));

			$old_is_private  = $milestone->isPrivate();
			$milestone->setFromAttributes($milestone_data);
			$urgent = array_var($milestone_data, 'is_urgent') == 'checked';
			$milestone->setIsUrgent($urgent);
			if(!logged_user()->isMemberOfOwnerCompany()) $milestone->setIsPrivate($old_is_private);

			$old_project_id = $milestone->getProjectId();
			$project_id = array_var($_POST, 'ws_ids');
			if ($old_project_id != $project_id) {
				$newProject = Projects::findById($project_id);
				if(!$milestone->canAdd(logged_user(),$newProject)) {
					flash_error(lang('no access permissions'));
					ajx_current("empty");
					return;
				} // if
				$milestone->move_inconsistent_tasks($newProject);
			}
			
			$milestone->setAssignedToCompanyId(array_var($assigned_to, 0, 0));
			$milestone->setAssignedToUserId(array_var($assigned_to, 1, 0));

			try {
				DB::beginWork();
				$milestone->save();
				$milestone->setTagsFromCSV(array_var($milestone_data, 'tags'));

				$object_controller = new ObjectController();
				$object_controller->add_to_workspaces($milestone);
			    $object_controller->link_to_new_object($milestone);
				$object_controller->add_subscribers($milestone);
				$object_controller->add_custom_properties($milestone);
				$object_controller->add_reminders($milestone);
				
				ApplicationLogs::createLog($milestone, $milestone->getWorkspaces(), ApplicationLogs::ACTION_EDIT);
				DB::commit();

				// If owner is changed send notification but don't break submission
				try {
					$new_owner = $milestone->getAssignedTo();
					if(array_var($milestone_data, 'send_notification') == 'checked') {
						if($old_owner instanceof User) {
							// We have a new owner and it is different than old owner
							if($new_owner instanceof User && $new_owner->getId() <> $old_owner->getId()) Notifier::milestoneAssigned($milestone);
						} else {
							// We have new owner
							if($new_owner instanceof User) Notifier::milestoneAssigned($milestone);
						} // if
					} // if
				} catch(Exception $e) {

				} // try

				flash_success(lang('success edit milestone', $milestone->getName()));
				ajx_current("back");

			} catch(Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			} // try
		} // if
	} // edit

	/**
	 * Delete single milestone
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function delete() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		ajx_current("empty");
		$milestone = ProjectMilestones::findById(get_id());
		if(!($milestone instanceof ProjectMilestone)) {
			flash_error(lang('milestone dnx'));
			return;
		} // if

		if(!$milestone->canDelete(logged_user())) {
			flash_error(lang('no access permissions'));
			return;
		} // if

		try {
			DB::beginWork();
			$milestone->trash();
			ApplicationLogs::createLog($milestone, $milestone->getWorkspaces(), ApplicationLogs::ACTION_TRASH);
			DB::commit();

			if ($is_template) {
				flash_success(lang('success delete template', $milestone->getName()));
			} else {
				flash_success(lang('success deleted milestone', $milestone->getName()));
			}
			if (array_var($_GET, 'quick', false)) {
				ajx_current('empty');
			} else {
				ajx_current('back');
			}
		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error delete milestone'));
		} // try
	} // delete

	/**
	 * Complete specific milestone
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function complete() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		ajx_current("empty");
		$milestone = ProjectMilestones::findById(get_id());
		if(!($milestone instanceof ProjectMilestone)) {
			flash_error(lang('milestone dnx'));
			return;
		} // if

		if(!$milestone->canChangeStatus(logged_user())) {
			flash_error(lang('no access permissions'));
			return;
		} // if

		try {

			$milestone->setCompletedOn(DateTimeValueLib::now());
			$milestone->setCompletedById(logged_user()->getId());

			DB::beginWork();
			$milestone->save();
			ApplicationLogs::createLog($milestone, $milestone->getWorkspaces(), ApplicationLogs::ACTION_CLOSE);
			DB::commit();

			flash_success(lang('success complete milestone', $milestone->getName()));
			$redirect_to = array_var($_GET, 'redirect_to', false);
			if (array_var($_GET, 'quick', false)) {
				ajx_current("empty");
			} else {
				ajx_current("reload");
			}
		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error complete milestone'));
		} // try

	} // complete

	/**
	 * Open specific milestone
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function open() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		ajx_current("empty");
		$milestone = ProjectMilestones::findById(get_id());
		if(!($milestone instanceof ProjectMilestone)) {
			flash_error(lang('milestone dnx'));
			return;
		} // if

		if(!$milestone->canChangeStatus(logged_user())) {
			flash_error(lang('no access permissions'));
			return;
		} // if

		try {

			$milestone->setCompletedOn(null);
			$milestone->setCompletedById(0);

			DB::beginWork();
			$milestone->save();
			ApplicationLogs::createLog($milestone, $milestone->getWorkspaces(), ApplicationLogs::ACTION_OPEN);
			DB::commit();

			flash_success(lang('success open milestone', $milestone->getName()));
			$redirect_to = array_var($_GET, 'redirect_to', false);
			if (array_var($_GET, 'quick', false)) {
				ajx_current("empty");
			} else {
				ajx_current("reload");
			}
		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error open milestone'));
		} // try

	} // open

	/**
	 * Copy milestone
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function copy_milestone() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$project = active_or_personal_project();
		if(!ProjectMilestone::canAdd(logged_user(), $project)) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
		
		$id = get_id();
		$milestone = ProjectMilestones::findById($id);
		if (!$milestone instanceof ProjectMilestone) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$milestone_data = array(
			'name' => $milestone->getIsTemplate() ? $milestone->getName() : lang("copy of", $milestone->getName()),
			'assigned_to' => $milestone->getAssignedToCompanyId() . ":" . $milestone->getAssignedToUserId(),
			'tags' => implode(",", $milestone->getTagNames()),
			'project_id' => $milestone->getProjectId(),
			'description' => $milestone->getDescription(),
			'copyId' => $milestone->getId(),
		); // array
		if ($milestone->getDueDate() instanceof DateTimeValue) {
			$milestone_data['due_date'] = $milestone->getDueDate()->getTimestamp();
		}

		$newmilestone = new ProjectMilestone();
		tpl_assign('milestone_data', $milestone_data);
		tpl_assign('milestone', $newmilestone);
		tpl_assign('base_milestone', $milestone);
		$this->setTemplate("add_milestone");
	} // copy_milestone
	
		
	/**
	 * Create a new milestone template
	 *
	 */
	function new_template() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$project = active_or_personal_project();
		if(!ProjectMilestone::canAdd(logged_user(), $project)) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
		
		$id = get_id();
		$milestone = ProjectMilestones::findById($id);
		if (!$milestone instanceof ProjectMilestone) {
			$milestone_data = array('is_template' => true);
		} else {
			$milestone_data = array(
				'name' => $milestone->getName(),
				'assigned_to' => $milestone->getAssignedToCompanyId() . ":" . $milestone->getAssignedToUserId(),
				'tags' => implode(",", $milestone->getTagNames()),
				'project_id' => $milestone->getProjectId(),
				'description' => $milestone->getDescription(),
				'copyId' => $milestone->getId(),
				'is_template' => true,
			); // array
			if ($milestone->getDueDate() instanceof DateTimeValue) {
				$milestone_data['due_date'] = $milestone->getDueDate()->getTimestamp();
			}
		}

		$milestone = new ProjectMilestone();
		tpl_assign('milestone_data', $milestone_data);
		tpl_assign('milestone', $milestone);
		$this->setTemplate("add_milestone");
	} // new_template
	
	function change_due_date() {
		$milestone = ProjectMilestones::findById(get_id());
		if(!$milestone->canEdit(logged_user())){	    	
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return ;
	    }
	    
	    $year = array_var($_GET, 'year', $milestone->getDueDate()->getYear());
	    $month = array_var($_GET, 'month', $milestone->getDueDate()->getMonth());
	    $day = array_var($_GET, 'day', $milestone->getDueDate()->getDay());
	    try {
	    	DB::beginWork();
	    	$milestone->setDueDate(new DateTimeValue(mktime(0, 0, 0, $month, $day, $year)));
	    	$milestone->save();
	    	DB::commit();
		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error change due date milestone'));
		} // try
	    ajx_current("empty");
	}
	
	function render_add_milestone() {
		$ws_ids = array_var($_GET, 'workspaces', '');
		$genid = array_var($_GET, 'genid', '');				
		$workspaces = Projects::findByCSVIds($ws_ids);
		tpl_assign('workspaces', $workspaces);
		tpl_assign('genid', $genid);
		$this->setLayout("html");
		$this->setTemplate("add_select_milestone");	
	}
	
	/**
	 * Returns the milestones included in the present workspace and all of its parents. This is because tasks from a particular workspace
	 * can only be assigned to milestones from that workspace and from any of its parents.
	 */
	function get_workspace_milestones() {
		ajx_current("empty");
		$ws_id = array_var($_GET, 'ws_id');
		$workspace = Projects::findById($ws_id);
		if ($workspace instanceof Project) {
			$milestones = $workspace->getOpenMilestones();
			$ms = array();
			foreach ($milestones as $milestone) {
				$ms[] = array(
					'id' => $milestone->getId(),
					'name' => $milestone->getName(),
				);
			}
			ajx_extra_data(array('milestones' => $ms));
		} else {
			ajx_extra_data(array('milestones' => array()));
		}
	}
	
} // MilestoneController

?>