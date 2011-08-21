<?php

/**
 * ProjectMilestone class
 * Generated on Sat, 04 Mar 2006 12:50:11 +0100 by DataObject generation tool
 *
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class ProjectMilestone extends BaseProjectMilestone {

	/**
	 * This project object is taggable
	 *
	 * @var boolean
	 */
	protected $is_taggable = true;

	/**
	 * Message comments are searchable
	 *
	 * @var boolean
	 */
	protected $is_searchable = true;
	
	/**
	 * Message comments are searchable
	 *
	 * @var boolean
	 */
	protected $is_commentable = true;

	/**
	 * Array of searchable columns
	 *
	 * @var array
	 */
	protected $searchable_columns = array('name', 'description');

	/**
	 * Cached User object of person who completed this milestone
	 *
	 * @var User
	 */
	private $completed_by;
	
	/**
	 * Cache of open tasks
	 *
	 * @var array
	 */
	private $open_tasks;
	
	/**
	 * Cache of completed tasks
	 *
	 * @var array
	 */
	private $completed_tasks;
	
	/**
	 * Return if this milestone is completed
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function isCompleted() {
		if(is_null($this->getDueDate())) return false;
		return (boolean) $this->getCompletedOn();
	} // isCompleted

	/**
	 * Check if this milestone is late
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function isLate() {
		if($this->isCompleted()) return false;
		if(is_null($this->getDueDate())) return true;
		return !$this->isToday() && ($this->getDueDate()->getTimestamp() < time());
	} // isLate

	/**
	 * Check if this milestone is today
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function isToday() {
		$now = DateTimeValueLib::now();
		$due = $this->getDueDate();

		// getDueDate and similar functions can return NULL
		if(!($due instanceof DateTimeValue)) return false;

		return $now->getDay() == $due->getDay() &&
		$now->getMonth() == $due->getMonth() &&
		$now->getYear() == $due->getYear();
	} // isToday

	/**
	 * Return the name of the user that completed the milestone
	 *
	 */
	function getCompletedByName() {
		if (!$this->isCompleted()) {
			return '';
		} else {
			return $this->getCompletedBy()->getDisplayName();
		}
	}
	
	/**
	 * Check if this is upcoming milestone
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function isUpcoming() {
		return /*!$this->isCompleted() && */!$this->isToday() && ($this->getLeftInDays() > 0);
	} // isUpcoming

	/**
	 * Return number of days that this milestone is late for
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getLateInDays() {
		$due_date_start = $this->getDueDate()->beginningOfDay();
		return floor(abs($due_date_start->getTimestamp() - DateTimeValueLib::now()->getTimestamp()) / 86400);
	} // getLateInDays

	/**
	 * Return number of days that is left
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getLeftInDays() {
		$due_date_start = $this->getDueDate()->endOfDay();
		return floor(abs($due_date_start->getTimestamp() - DateTimeValueLib::now()->beginningOfDay()->getTimestamp()) / 86400);
	} // getLeftInDays

	/**
	 * Return difference between specific datetime and due date time in seconds
	 *
	 * @access public
	 * @param DateTime $diff_to
	 * @return integer
	 */
	private function getDueDateDiff(DateTimeValue $diff_to) {
		return $this->getDueDate()->getTimestamp() - $diff_to->getTimestamp();
	} // getDueDateDiff

	// ---------------------------------------------------
	//  Related object
	// ---------------------------------------------------

	
	/**
	 * Return all tasks connected with this milestone
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getTasks() {
		return ProjectTasks::findAll(array(
	        'conditions' => '`milestone_id` = ' . DB::escape($this->getId()). " AND `trashed_on` = " . DB::escape(EMPTY_DATETIME) . " AND ". permissions_sql_for_listings(new ProjectTasks(),ACCESS_LEVEL_READ,logged_user()),
	        'order' => 'created_on'
        )); // findAll
	} // getTasks

	/**
	 * Return open tasks
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getOpenSubTasks() {
		if(is_null($this->open_tasks)) {
			$this->open_tasks = ProjectTasks::findAll(array(
          'conditions' => '`milestone_id` = ' . DB::escape($this->getId()) . ' AND `trashed_on` = ' . DB::escape(EMPTY_DATETIME) . " AND `completed_on` = " . DB::escape(EMPTY_DATETIME) . " AND ". permissions_sql_for_listings(new ProjectTasks(),ACCESS_LEVEL_READ,logged_user()),
          'order' => '`order`, `created_on`' 
          )); // findAll
		} // if

		return $this->open_tasks;
	} // getOpenSubTasks

	/**
	 * Return completed tasks
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getCompletedSubTasks() {
		if(is_null($this->completed_tasks)) {
			$this->completed_tasks = ProjectTasks::findAll(array(
          'conditions' => '`milestone_id` = ' . DB::escape($this->getId()) . ' AND `trashed_on` = ' . DB::escape(EMPTY_DATETIME) . " AND `completed_on` > " . DB::escape(EMPTY_DATETIME) . " AND ". permissions_sql_for_listings(new ProjectTasks(),ACCESS_LEVEL_READ,logged_user()),
          'order' => '`completed_on` DESC'
          )); // findAll
		} // if

		return $this->completed_tasks;
	} // getCompletedTasks
	function countAllTasks() {
		return ProjectTasks::count('`milestone_id` = ' . DB::escape($this->getId()). " AND `trashed_on` = " . DB::escape(EMPTY_DATETIME) . " AND ". permissions_sql_for_listings(new ProjectTasks(),ACCESS_LEVEL_READ,logged_user()));
	} // countAllTasks
	
	function countOpenTasks() {
		return ProjectTasks::count('`milestone_id` = ' . DB::escape($this->getId()) . ' AND `trashed_on` = " . DB::escape(EMPTY_DATETIME) . " AND `completed_on` = ' . DB::escape(EMPTY_DATETIME). " AND ". permissions_sql_for_listings(new ProjectTasks(),ACCESS_LEVEL_READ,logged_user()));
	} // countAllTasks
	
	/**
	 * Returns true if there are task lists in this milestone
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function hasTasks() {
		return (boolean) ProjectTasks::count('`milestone_id` = ' . DB::escape($this->getId()). " AND `trashed_on` = " . DB::escape(EMPTY_DATETIME) . " AND ". permissions_sql_for_listings(new ProjectTasks(),ACCESS_LEVEL_READ,logged_user()));
	} // hasTasks

	/**
	 * Return all messages related with this message
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getMessages() {
		return ProjectMessages::findAll(array(
        'conditions' => '`milestone_id` = ' . DB::escape($this->getId()). " AND `trashed_on` = " . DB::escape(EMPTY_DATETIME) . " AND ". permissions_sql_for_listings(new ProjectMessages(),ACCESS_LEVEL_READ,logged_user()),
        'order' => 'created_on'
        )); // findAll
	} // getMessages

	/**
	 * Returns true if there is messages in this milestone
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function hasMessages() {
		return (boolean) ProjectMessages::count('`milestone_id` = ' . DB::escape($this->getId()) . " AND `trashed_on` = " . DB::escape(EMPTY_DATETIME) . " AND ". permissions_sql_for_listings(new ProjectMessages(),ACCESS_LEVEL_READ,logged_user()));
	} // hasMessages

	/**
	 * Return assigned to object. It can be User, Company or nobady (NULL)
	 *
	 * @access public
	 * @param void
	 * @return ApplicationDataObject
	 */
	function getAssignedTo() {
		if($this->getAssignedToUserId() > 0) {
			return $this->getAssignedToUser();
		} elseif($this->getAssignedToCompanyId() > 0) {
			return $this->getAssignedToCompany();
		} else {
			return null;
		} // if
	} // getAssignedTo
	
	function getAssignedToName() {
		$user = $this->getAssignedToUser();
		$company = $this->getAssignedToCompany();
		if ($user instanceof User) {
			return $user->getDisplayName();
		} else if ($company instanceof Company) {
			return $company->getName();
		} else {
			return lang("anyone");
		} // if
	} // getAssignedToName

	/**
	 * Return responsible company
	 *
	 * @access public
	 * @param void
	 * @return Company
	 */
	protected function getAssignedToCompany() {
		return Companies::findById($this->getAssignedToCompanyId());
	} // getAssignedToCompany

	/**
	 * Return responsible user
	 *
	 * @access public
	 * @param void
	 * @return User
	 */
	protected function getAssignedToUser() {
		return Users::findById($this->getAssignedToUserId());
	} // getAssignedToUser

	/**
	 * Return User object of person who completed this milestone
	 *
	 * @param void
	 * @return User
	 */
	function getCompletedBy() {
		if ($this->isCompleted()){
			if(is_null($this->completed_by)) $this->completed_by = Users::findById($this->getCompletedById());
			return $this->completed_by;
		} else return null;
	} // getCompletedBy

	// ---------------------------------------------------
	//  Permissions
	// ---------------------------------------------------

	/**
	 * Returns true if specific user has CAN_MANAGE_MILESTONES permission set to true
	 *
	 * @access public
	 * @param User $user
	 * @return boolean
	 */
	function canManage(User $user) {		
		return can_write($user,$this);
	} // canManage

	/**
	 * Returns true if $user can view this milestone
	 *
	 * @param User $user
	 * @return boolean
	 */
	function canView(User $user) {
		return can_read($user,$this);
	} // canView

	/**
	 * Check if specific user can add new milestones to specific project
	 *
	 * @access public
	 * @param User $user
	 * @param Project $project
	 * @return boolean
	 */
	function canAdd(User $user, Project $project) {
		return can_add($user,$project,get_class(ProjectMilestones::instance()));
	} // canAdd

	/**
	 * Check if specific user can edit this milestone
	 *
	 * @access public
	 * @param User $user
	 * @return boolean
	 */
	function canEdit(User $user) {
		return can_write($user,$this);
	} // canEdit

	/**
	 * Can chagne status of this milestone (completed / open)
	 *
	 * @access public
	 * @param User $user
	 * @return boolean
	 */
	function canChangeStatus(User $user) {
		return can_write($user,$this);
	} // canChangeStatus

	/**
	 * Check if specific user can delete this milestone
	 *
	 * @access public
	 * @param User $user
	 * @return boolean
	 */
	function canDelete(User $user) {
		return can_delete($user,$this);
	} // canDelete

	// ---------------------------------------------------
	//  URL
	// ---------------------------------------------------

	function getViewUrl() {
		return get_url('milestone', 'view', array('id' => $this->getId()));
	} // getViewUrl

	/**
	 * Return edit milestone URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getEditUrl() {
		return get_url('milestone', 'edit', array('id' => $this->getId()));
	} // getEditUrl

	/**
	 * Return delete milestone URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getDeleteUrl() {
		return get_url('milestone', 'delete', array('id' => $this->getId()));
	} // getDeleteUrl

	/**
	 * Return complete milestone url
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getCompleteUrl($redirect_to = '') {
		$params = array(
        	'id' => $this->getId()
		);
		if (trim($redirect_to) != '') {
			$params["redirect_to"] = $redirect_to;
		}
		return get_url('milestone', 'complete', $params);
	} // getCompleteUrl
	

	/**
	 * Return open milestone url
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getOpenUrl($redirect_to = '') {
		$params = array(
        	'id' => $this->getId()
		);
		if (trim($redirect_to) != '') {
			$params["redirect_to"] = $redirect_to;
		}
		return get_url('milestone', 'open', $params);
	} // getOpenUrl

	/**
	 * Return add message URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getAddMessageUrl() {
		return get_url('message', 'add', array('milestone_id' => $this->getId()));
	} // getAddMessageUrl

	/**
	 * Return add task list URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getAddTaskUrl() {
		return get_url('task', 'add_task', array('milestone_id' => $this->getId()));
	} // getAddTaskUrl

	// ---------------------------------------------------
	//  System functions
	// ---------------------------------------------------

	/**
	 * Validate before save
	 *
	 * @access public
	 * @param array $errors
	 * @return boolean
	 */
	function validate(&$errors) {
		if(!$this->validatePresenceOf('name')) $errors[] = lang('milestone name required');
		if(!$this->validatePresenceOf('due_date')) $errors[] = lang('milestone due date required');
	} // validate

	/**
	 * Delete this object and reset all relationship. This function will not delete any of related objec
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	
	function save() {
		parent::save();
		if ($this->getDueDate() instanceof DateTimeValue) {
			$id = $this->getId();
			$sql = "UPDATE `".TABLE_PREFIX."object_reminders` SET
				`date` = date_sub((SELECT `due_date` FROM `".TABLE_PREFIX."project_milestones` WHERE `id` = $id),
					interval `minutes_before` minute) WHERE
					`object_manager` = 'ProjectMilestones' AND `object_id` = $id;";
			DB::execute($sql);
		}
	}
	
	function delete() {
		$is_template = $this->getIsTemplate();
		if ($is_template) {
			$tasks = $this->getTasks();
			foreach ($tasks as $t) {
				$t->delete();
			}
		}
		try {
			DB::execute("UPDATE " . ProjectMessages::instance()->getTableName(true) . " SET `milestone_id` = '0' WHERE `milestone_id` = " . DB::escape($this->getId()));
			DB::execute("UPDATE " . ProjectTasks::instance()->getTableName(true) . " SET `milestone_id` = '0' WHERE `milestone_id` = " . DB::escape($this->getId()));
			return parent::delete();
		} catch(Exception $e) {
			throw $e;
		} // try

	} // delete

	/**
	 * Trash this object and reset all relationship. This function will not trash any of related objects
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function trash($trashDate = null) {
		$is_template = $this->getIsTemplate();
		if ($is_template) {
			$this->delete();
		} else {
			try {
				DB::execute("UPDATE " . ProjectMessages::instance()->getTableName(true) . " SET `milestone_id` = '0' WHERE `milestone_id` = " . DB::escape($this->getId()));
				DB::execute("UPDATE " . ProjectTasks::instance()->getTableName(true) . " SET `milestone_id` = '0' WHERE `milestone_id` = " . DB::escape($this->getId()));
				return parent::trash($trashDate);
			} catch(Exception $e) {
				throw $e;
			} // try
		}

	} // trash
	
	
	/**
	 * Moves the tasks that do not comply with the following rule: Tasks of a milestone must belong to its workspace or any of its subworkspaces.
	 * 
	 * @param Project $newWorkspace The new workspace
	 * @return unknown_type
	 */
	function move_inconsistent_tasks(Project $newWorkspace){
		$oldWorkspace = $this->getProject();
		$nwCSV = explode(',', $newWorkspace->getAllSubWorkspacesCSV(true));
		$owCSV = explode(',', $oldWorkspace->getAllSubWorkspacesCSV(true));
		
		$inconsistentWs = array();
		
		foreach ($owCSV as $ow){
			$found = false;
			foreach ($nwCSV as $nw){
				if ($ow == $nw){
					$found = true;
					break;
				}
			}
			if (!$found)
				$inconsistentWs[] = $ow;
		}
		if (count($inconsistentWs) > 0){
			try {
				DB::execute('UPDATE ' . WorkspaceObjects::instance()->getTableName(true) . ' SET workspace_id = ' . $newWorkspace->getId() . 
					' WHERE object_manager = \'ProjectTasks\' and object_id in (SELECT id from ' . ProjectTasks::instance()->getTableName(true) . 
					' WHERE milestone_id = ' . $this->getId() . ') and workspace_id in (' . implode(',',$inconsistentWs) . ')');
			} catch(Exception $e) {
				throw $e;
			} // try
		}
	}
	
	// ---------------------------------------------------
	//  ApplicationDataObject implementation
	// ---------------------------------------------------

	/**
	 * Return object type name
	 *
	 * @param void
	 * @return string
	 */
	function getObjectTypeName() {
		return 'milestone';
	} // getObjectTypeName

	/**
	 * Return object URl
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getObjectUrl() {
		return $this->getViewUrl();
	} // getObjectUrl

	function getTitle() {
		return $this->getName();
	}
	
	function getArrayInfo(){
		$tnum = ProjectTasks::count('milestone_id = ' . $this->getId() . " AND `trashed_on` = " . DB::escape(EMPTY_DATETIME));
		$tc = ProjectTasks::count('milestone_id = ' . $this->getId() . ' and completed_on > '.DB::escape(EMPTY_DATETIME).' AND `trashed_on` = ' . DB::escape(EMPTY_DATETIME));
		
		$result = array(
			'id' => $this->getId(),
			't' => $this->getTitle(),
			'wsid' => $this->getWorkspacesIdsCSV(),
			'tnum' => $tnum,
			'tc' => $tc,
			'dd' => $this->getDueDate()->getTimestamp());
		
		$tags = $this->getTagNames();
		if ($tags)
			$result['tags'] = $tags;
			
		if ($this->getCompletedById() > 0){
			$result['compId'] = $this->getCompletedById();
			$result['compOn'] = $this->getCompletedOn()->getTimestamp();
		}
		
		$result['is_urgent'] = $this->getIsUrgent();
		
		return $result;
	}
	
	/**
	 * Set the milestone's project
	 * @param $project
	 */
	function setProject($project) {
		$this->removeFromAllWorkspaces();
		$this->addToWorkspace($project);
	}
	
	/**
	 * Get milestone's project's id
	 */
	function getProjectId() {
		$project = $this->getProject();
		if ($project instanceof Project) return $project->getId();
		return 0;
	}
	
} // ProjectMilestone

?>