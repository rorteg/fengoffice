<?php
 
/**
 * ProjectTask class
 * Generated on Sat, 04 Mar 2006 12:50:11 +0100 by DataObject generation tool
 *
 * @author Ilija Studen <ilija.studen@gmail.com>
 * Modif: Marcos Saiz <marcos.saiz@gmail.com> 24/3/08
 */
class ProjectTask extends BaseProjectTask {
	 
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
	 * Array of searchable columns
	 *
	 * @var array
	 */
	protected $searchable_columns = array('text','title');

	/**
	 * Project task is commentable object
	 *
	 * @var boolean
	 */
	protected $is_commentable = true;
	
	protected $allow_timeslots = true;

	/**
	 * Cached task array
	 *
	 * @var array
	 */
	private $all_tasks;

	/**
	 * Cached open task array
	 *
	 * @var array
	 */
	private $open_tasks;

	/**
	 * Cached completed task array
	 *
	 * @var array
	 */
	private $completed_tasks;

	/**
	 * Cached number of open tasks
	 *
	 * @var integer
	 */
	private $count_all_tasks;

	/**
	 * Cached number of open tasks in this list
	 *
	 * @var integer
	 */
	private $count_open_tasks = null;

	/**
	 * Cached number of completed tasks in this list
	 *
	 * @var integer
	 */
	private $count_completed_tasks = null;

	/**
	 * Cached array of related forms
	 *
	 * @var array
	 */
	private $related_forms;

	/**
	 * Cached completed by reference
	 *
	 * @var User
	 */
	private $completed_by;

	private $milestone;
	
	private $id = null;
	
	function getId() {
		if ($this->id == null)
			$this->id = parent::getId();
		return $this->id;
	}
	
	function setId($value) {
		parent::setId($value);
		$this->id = $value;
	}
	
	function getMilestone(){
		if ($this->getMilestoneId() > 0 && !$this->milestone){
			$this->milestone = ProjectMilestones::findById($this->getMilestoneId());
		}
		return $this->milestone;
	}
	
	/**
	 * Return parent task that this task belongs to
	 *
	 * @param void
	 * @return ProjectTask
	 */
	function getParent() {
		if ($this->getParentId()==0) return null;
		$parent = ProjectTasks::findById($this->getParentId());
		return $parent instanceof ProjectTask  ? $parent : null;
	} // getParent
	
	/**
	 * Return the user that last assigned the task
	 *
	 * @access public
	 * @param void
	 * @return User
	 */
	function getAssignedBy() {
		return Users::findById($this->getAssignedById());
	} // getAssignedBy()

	/**
	 * Set the user that last assigned the task
	 *
	 * @access public
	 * @param User $value
	 * @return boolean
	 */
	function setAssignedBy($user) {
		$this->setAssignedById($user->getId());
	}

	/**
	 * Return owner user or company
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
	} // getAssignedTo
	
	function isAssigned() {
		return $this->getAssignedToCompanyId() > 0 || $this->getAssignedToUserId() > 0;
	} // getAssignedTo

	/**
	 * Return owner comapny
	 *
	 * @access public
	 * @param void
	 * @return Company
	 */
	function getAssignedToCompany() {
		return Companies::findById($this->getAssignedToCompanyId());
	} // getAssignedToCompany

	/**
	 * Return owner user
	 *
	 * @access public
	 * @param void
	 * @return User
	 */
	function getAssignedToUser() {
		return Users::findById($this->getAssignedToUserId());
	} // getAssignedToUser

	/**
	 * Returns true if this task was not completed
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function isOpen() {
		return !$this->isCompleted();
	} // isOpen

	/**
	 * Returns true if this task is completed
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function isCompleted() {
		return $this->getCompletedOn() instanceof DateTimeValue;
	} // isCompleted

	/**
	 * Check if this task is late
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function isLate() {
		if($this->isCompleted()) return false;
		if(!$this->getDueDate() instanceof DateTimeValue) return false;
		return !$this->isToday() && ($this->getDueDate()->getTimestamp() < DateTimeValueLib::now()->add('h', logged_user()->getTimezone())->getTimestamp());
	} // isLate
	
	/**
	 * Check if this task is today
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function isToday() {
		$now = DateTimeValueLib::now()->add('h', logged_user()->getTimezone())->getTimestamp();
		$due = $this->getDueDate();

		// getDueDate and similar functions can return NULL
		if(!($due instanceof DateTimeValue)) return false;

		return $now->getDay() == $due->getDay() &&
		$now->getMonth() == $due->getMonth() &&
		$now->getYear() == $due->getYear();
	} // isToday
	
	/**
	 * Return number of days that this task is late for
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getLateInDays() {
		if (!$this->getDueDate() instanceof DateTimeValue) return 0;
		$due_date_start = $this->getDueDate()->beginningOfDay();
		$today = DateTimeValueLib::now();
		$today = $today->add('h', logged_user()->getTimezone())->beginningOfDay();
		
		return floor(abs($due_date_start->getTimestamp() - $today->getTimestamp()) / 86400);
	} // getLateInDays
	
	/**
	 * Returns value of is private flag inehrited from parent task list
	 *
	 * @param void
	 * @return boolean
	 */
	function isPrivate() {
		return $this->getIsPrivate();
	} // isPrivate

	// ---------------------------------------------------
	//  Permissions
	// ---------------------------------------------------

	/**
	 * Check if user have task management permissions for project this list belongs to
	 *
	 * @param User $user
	 * @return boolean
	 */
	function canManage(User $user) {
		return can_write($user,$this);
	} // canManage

	/**
	 * Return true if $user can view this task lists
	 *
	 * @param User $user
	 * @return boolean
	 */
	function canView(User $user) {
		return can_read($user,$this);
	} // canView


	/**
	 * Check if user can add task lists in specific project
	 *
	 * @param User $user
	 * @param Project $project
	 * @return boolean
	 */
	function canAdd(User $user, Project $project) {
		return can_add($user,$project,get_class(ProjectTasks::instance()));
	} // canAdd
	
	/**
	 * Private function to check whether a task is asigned to user or company user
	 *
	 * @param User $user
	 * @return unknown
	 */
	private function isAsignedToUserOrCompany(User $user){
				// Additional check - is this task assigned to this user or its company
		if($this->getAssignedTo() instanceof User) {
			if($user->getId() == $this->getAssignedTo()->getObjectId()) return true;
		} elseif($this->getAssignedTo() instanceof Company) {
			if($user->getCompanyId() == $this->getAssignedTo()->getObjectId()) return true;
		} // if
		return false;
	}
	/**
	 * Check if specific user can update this task
	 *
	 * @access public
	 * @param User $user
	 * @return boolean
	 */
	function canEdit(User $user) {
		if(can_write($user,$this)) {
			return true;
		} // if
		$task_list = $this->getParent();
		return $task_list instanceof ProjectTask ? $task_list->canEdit($user) : false;
	} // canEdit
	
	function canAddTimeslot($user) {
		return $this->canChangeStatus($user);
	}
	
	/**
	 * Check if specific user can change task status
	 *
	 * @access public
	 * @param User $user
	 * @return boolean
	 */
	function canChangeStatus(User $user) {
		return ($this->canEdit($user) || $this->isAsignedToUserOrCompany($user));
	} // canChangeStatus

	/**
	 * Check if specific user can delete this task
	 *
	 * @access public
	 * @param User $user
	 * @return boolean
	 */
	function canDelete(User $user) {
		if (can_delete($user,$this))
			return true;
		$task_list = $this->getParent();
		return $task_list instanceof ProjectTask ? $task_list->canDelete($user) : false;
	} // canDelete

	/**
	 * Check if user can reorder tasks in this list
	 *
	 * @param User $user
	 * @return boolean
	 */
	function canReorderTasks(User $user) {
		return can_write($user,$this);
	} // canReorderTasks


	/**
	 * Check if specific user can add task to this list
	 *
	 * @param User $user
	 * @return boolean
	 */
	function canAddSubTask(User $user) {
		return can_write($user,$this);
	} // canAddTask
	// ---------------------------------------------------
	//  Operations
	// ---------------------------------------------------

	/**
	 * Complete this task and subtasks and check if we need to complete the parent
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function completeTask() {
		$this->setCompletedOn(DateTimeValueLib::now());
		$this->setCompletedById(logged_user()->getId());
		$this->save();
	
		$timeslots = $this->getTimeslots();
		if ($timeslots){
			foreach ($timeslots as $timeslot){
				if ($timeslot->isOpen())
					$timeslot->close();
					$timeslot->save();
			}
		}

		/*
		 * if this is run then when the user wants to reopen a task
		 * he will have to manually reopen the subtasks
		$tasks = $this->getOpenSubTasks();
		foreach ($tasks as $task) {
			$task->completeTask();
		}*/
		
		/*
		 * this is done in the controller
		$task_list = $this->getParent();
		if(($task_list instanceof ProjectTask) && $task_list->isOpen()) {
			$open_tasks = $task_list->getOpenSubTasks();
			if(empty($open_tasks)) $task_list->complete(DateTimeValueLib::now(), logged_user());
		} // if*/
		ApplicationLogs::createLog($this, $this->getWorkspaces(), ApplicationLogs::ACTION_CLOSE);
	} // completeTask

	/**
	 * Open this task and check if we need to reopen list again
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function openTask() {
		$this->setCompletedOn(null);
		$this->setCompletedById(0);
		$this->save();

		/*
		 * this is done in the controller
		$task_list = $this->getParent();
		if(($task_list instanceof ProjectTask) && $task_list->isCompleted()) {
			$open_tasks = $task_list->getOpenSubTasks();
			if(!empty($open_tasks)) $task_list->open();
		} // if*/
		ApplicationLogs::createLog($this, $this->getWorkspaces(), ApplicationLogs::ACTION_OPEN);
	} // openTask

	function getRemainingDays(){
		if (is_null($this->getDueDate()))
			return null;
		else{
			$due = $this->getDueDate();
			$date = DateTimeValueLib::now()->add('h', logged_user()->getTimezone())->getTimestamp();
			$nowDays = floor($date/(60*60*24));
			$dueDays = floor($due->getTimestamp()/(60*60*24));
			return $dueDays - $nowDays;
		}
	}
	
	function cloneTask($copy_status = false) {
		$new_task = new ProjectTask();
				
		$new_task->setParentId($this->getParentId());
		$new_task->setTitle($this->getTitle());
		$new_task->setText($this->getText());
		$new_task->setAssignedToCompanyId($this->getAssignedToCompanyId());
		$new_task->setAssignedToUserId($this->getAssignedToUserId());
		$new_task->setAssignedOn($this->getAssignedOn());
		$new_task->setAssignedById($this->getAssignedById());
		$new_task->setTimeEstimate($this->getTimeEstimate());
		$new_task->setStartedOn($this->getStartedOn());
		$new_task->setStartedById($this->getStartedById());
		$new_task->setPriority(($this->getPriority()));
		$new_task->setState($this->getState());
		$new_task->setOrder($this->getOrder());
		$new_task->setMilestoneId($this->getMilestoneId());
		$new_task->setIsPrivate($this->getIsPrivate());
		$new_task->setIsTemplate($this->getIsTemplate());
		$new_task->setFromTemplateId($this->getFromTemplateId());
		if ($this->getDueDate() instanceof DateTimeValue )
			$new_task->setDueDate(new DateTimeValue($this->getDueDate()->getTimestamp()));
		if ($this->getStartDate() instanceof DateTimeValue )
			$new_task->setStartDate(new DateTimeValue($this->getStartDate()->getTimestamp()));
		if ($copy_status) {
			$new_task->setCompletedById($this->getCompletedById());
			$new_task->setCompletedOn($this->getCompletedOn());
		}
		
		$new_task->save();
		$new_task->setTagsFromCSV(implode(",", $this->getTagNames()));
		
		foreach ($this->getWorkspaces() as $ws) {
			$new_task->addToWorkspace($ws);
		}
		if (is_array($this->getAllLinkedObjects())) {
			foreach ($this->getAllLinkedObjects() as $lo) {
				$new_task->linkObject($lo);
			}
		}
		
		$sub_tasks = $this->getAllSubTasks();
		foreach ($sub_tasks as $st) {
			if ($st->getParentId() == $this->getId()) {
				$new_st = $st->cloneTask($copy_status);
				if ($copy_status) {
					$new_st->setCompletedById($st->getCompletedById());
					$new_st->setCompletedOn($st->getCompletedOn());
					$new_st->save();
				}
				$new_task->attachTask($new_st);
			}
		}
		foreach ($this->getAllComments() as $com) {
			$new_com = new Comment();
			$new_com->setAuthorEmail($com->getAuthorEmail());
			$new_com->setAuthorName($com->getAuthorName());
			$new_com->setAuthorHomepage($com->getAuthorHomepage());
			$new_com->setCreatedById($com->getCreatedById());
			$new_com->setCreatedOn($com->getCreatedOn());
			$new_com->setUpdatedById($com->getUpdatedById());
			$new_com->setUpdatedOn($com->getUpdatedOn());
			$new_com->setIsAnonymous($com->getIsAnonymous());
			$new_com->setIsPrivate($com->getIsPrivate());
			$new_com->setText($com->getText());
			$new_com->setRelObjectId($new_task->getId());
			$new_com->setRelObjectManager("ProjectTasks");
			
			$new_com->save();
		}
		$_POST['subscribers'] = array();
		foreach ($this->getSubscribers() as $sub) {
			$_POST['subscribers']["user_" . $sub->getId()] = "checked";
		}
		$obj_controller = new ObjectController();
		$obj_controller->add_subscribers($new_task);
		
		foreach($this->getCustomProperties() as $prop) {
			$new_prop = new ObjectProperty();
			$new_prop->setRelObjectId($new_task->getId());
			$new_prop->setRelObjectManager($prop->getRelObjectManager());
			$new_prop->setPropertyName($prop->getPropertyName());
			$new_prop->setPropertyValue($prop->getPropertyValue());
			$new_prop->save();
		}
		
		$custom_props = CustomProperties::getAllCustomPropertiesByObjectType("ProjectTasks");
		foreach ($custom_props as $c_prop) {
			$values = CustomPropertyValues::getCustomPropertyValues($this->getId(), $c_prop->getId());
			if (is_array($values)) {
				foreach ($values as $val) {
					$cp = new CustomPropertyValue();
					$cp->setObjectId($new_task->getId());
					$cp->setCustomPropertyId($val->getCustomPropertyId());
					$cp->setValue($val->getValue());
					$cp->save();
				}
			}
		}
		
		$reminders = ObjectReminders::getByObject($this);
		foreach($reminders as $reminder) {
			$copy_reminder = new ObjectReminder();
			$copy_reminder->setContext($reminder->getContext());
			$reminder_date = $new_task->getColumnValue($reminder->getContext());
			if ($reminder_date instanceof DateTimeValue) {
				$reminder_date = new DateTimeValue($reminder_date->getTimestamp());
				$reminder_date->add('m', -$reminder->getMinutesBefore());
			}
			$copy_reminder->setDate($reminder_date);
			$copy_reminder->setMinutesBefore($reminder->getMinutesBefore());
			$copy_reminder->setObject($new_task);
			$copy_reminder->setType($reminder->getType());
			$copy_reminder->setUserId($reminder->getUserId());
			$copy_reminder->save();
		}
		
		return $new_task;
	}
	
	// ---------------------------------------------------
	//  TaskList Operations
	// ---------------------------------------------------

	/**
	 * Add subtask to this list
	 *
	 * @param string $text
	 * @param User $assigned_to_user
	 * @param Company $assigned_to_company
	 * @return ProjectTask
	 * @throws DAOValidationError
	 */
	function addSubTask($text, $assigned_to_user = null, $assigned_to_company = null) {
		$task = new ProjectTask();
		$task->setText($text);

		if($assigned_to_user instanceof User) {
			$task->setAssignedToUserId($assigned_to_user->getId());
			$task->setAssignedToCompanyId($assigned_to_user->getCompanyId());
		} elseif($assigned_to_company instanceof Company) {
			$task->setAssignedToCompanyId($assigned_to_company->getId());
		} // if

		$this->attachTask($task); // this one will save task
		return $task;
	} // addTask

	/**
	 * Attach subtask to thistask
	 *
	 * @param ProjectTask $task
	 * @return null
	 */
	function attachTask(ProjectTask $task) {
		if($task->getParentId() == $this->getId()) return;

		$task->setParentId($this->getId());
		$task->save();

		if($this->isCompleted()) $this->open();
	} // attachTask

	/**
	 * Detach subtask from this task
	 *
	 * @param ProjectTask $task
	 * @param ProjectTaskList $attach_to If you wish you can detach and attach task to
	 *   other list with one save query
	 * @return null
	 */
	function detachTask(ProjectTask $task, $attach_to = null) {
		if($task->getParentId() <> $this->getId()) return;

		if($attach_to instanceof ProjectTask) {
			$attach_to->attachTask($task);
		} else {
			$task->setParentId(0);
			$task->save();
		} // if

		$close = true;
		$open_tasks = $this->getOpenSubTasks();
		if(is_array($open_tasks)) {
			foreach($open_tasks as $open_task) {
				if($open_task->getId() <> $task->getId()) $close = false;
			} // if
		} // if

		if($close) $this->complete(DateTimeValueLib::now(), logged_user());
	} // detachTask

	/**
	 * Complete this task lists
	 *
	 * @access public
	 * @param DateTimeValue $on Completed on
	 * @param User $by Completed by
	 * @return null
	 */
	function complete(DateTimeValue $on, $by) {
		$by_id = $by instanceof User ? $by->getId() : 0;
		$this->setCompletedOn($on);
		$this->setCompletedById($by_id);
		$this->save();
		ApplicationLogs::createLog($this, $this->getWorkspaces(), ApplicationLogs::ACTION_CLOSE);
	} // complete

	/**
	 * Open this list
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function open() {
		$this->setCompletedOn(NULL);
		$this->setCompletedById(0);
		$this->save();
		ApplicationLogs::createLog($this, $this->getWorkspaces(), ApplicationLogs::ACTION_OPEN);
	} // open

	// ---------------------------------------------------
	//  Related object
	// ---------------------------------------------------

	/**
	 * Return all tasks from this list
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getSubTasks($include_trashed = true) {
		if(is_null($this->all_tasks)) {
			$this->all_tasks = ProjectTasks::findAll(array(
          'conditions' => '`parent_id` = ' . DB::escape($this->getId()),
          'order' => '`order`, `created_on`',
			'include_trashed' => $include_trashed
          )); // findAll
          if (is_null($this->all_tasks)) $this->all_tasks = array();
		} // if

		return $this->all_tasks;
	} // getTasks

	/**
	 * Return all tasks from this list
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getAllSubTasks($include_trashed = true) {
		if(is_null($this->all_tasks)) {
			$this->all_tasks = ProjectTasks::findAll(array(
          'conditions' => '`parent_id` = ' . DB::escape($this->getId()),
          'order' => '`order`, `created_on`',
			'include_trashed' => $include_trashed
          )); // findAll
          if (is_null($this->all_tasks)) $this->all_tasks = array();
		} // if
		
		$tasks = $this->all_tasks;
		$result = $tasks;
		
		for ($i = 0; $i < count($tasks); $i++){
			$tsubtasks = $tasks[$i]->getAllSubTasks($include_trashed);
			for ($j = 0; $j < count($tsubtasks); $j++)
				$result[] = $tsubtasks[$j];
		}
		
		return $result;
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
          'conditions' => '`parent_id` = ' . DB::escape($this->getId()) . ' AND `completed_on` = ' . DB::escape(EMPTY_DATETIME),
          'order' => '`order`, `created_on`'
          )); // findAll
		} // if

		return $this->open_tasks;
	} // getOpenTasks

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
          'conditions' => '`parent_id` = ' . DB::escape($this->getId()) . ' AND `completed_on` > ' . DB::escape(EMPTY_DATETIME),
          'order' => '`completed_on` DESC'
          )); // findAll
		} // if

		return $this->completed_tasks;
	} // getCompletedTasks

	/**
	 * Return number of all tasks in this list
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function countAllSubTasks() {
		if(is_null($this->count_all_tasks)) {
			if(is_array($this->all_tasks)) {
				$this->count_all_tasks = count($this->all_tasks);
			} else {
				$this->count_all_tasks = ProjectTasks::count('`parent_id` = ' . DB::escape($this->getId()));
			} // if
		} // if
		return $this->count_all_tasks;
	} // countAllTasks

	/**
	 * Return number of open tasks
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function countOpenSubTasks() {
		if(is_null($this->count_open_tasks)) {
			if(is_array($this->open_tasks)) {
				$this->count_open_tasks = count($this->open_tasks);
			} else {
				$this->count_open_tasks = ProjectTasks::count('`parent_id` = ' . DB::escape($this->getId()) . ' AND `completed_on` = ' . DB::escape(EMPTY_DATETIME));
			} // if
		} // if
		return $this->count_open_tasks;
	} // countOpenTasks

	/**
	 * Return number of completed tasks
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function countCompletedSubTasks() {
		if(is_null($this->count_completed_tasks)) {
			if(is_array($this->completed_tasks)) {
				$this->count_completed_tasks = count($this->completed_tasks);
			} else {
				$this->count_completed_tasks = ProjectTasks::count('`parent_id` = ' . DB::escape($this->getId()) . ' AND `completed_on` > ' . DB::escape(EMPTY_DATETIME));
			} // if
		} // if
		return $this->count_completed_tasks;
	} // countCompletedTasks

	/**
	 * Get project forms that are in relation with this task list
	 *
	 * @param void
	 * @return array
	 */
	function getRelatedForms() {
		if(is_null($this->related_forms)) {
			$this->related_forms = ProjectForms::findAll(array(
          'conditions' => '`action` = ' . DB::escape(ProjectForm::ADD_TASK_ACTION) . ' AND `in_object_id` = ' . DB::escape($this->getId()),
          'order' => '`order`'
          )); // findAll
		} // if
		return $this->related_forms;
	} // getRelatedForms

	/**
	 * Return user who completed this task
	 *
	 * @access public
	 * @param void
	 * @return User
	 */
	function getCompletedBy() {
		if(!($this->completed_by instanceof User)) {
			$this->completed_by = Users::findById($this->getCompletedById());
		} // if
		return $this->completed_by;
	} // getCompletedBy

	/**
	 * Return the name of who completed this task
	 *
	 * @access public
	 * @param void
	 * @return User
	 */
	function getCompletedByName() {
		if ($this->isCompleted()){
			if(!($this->completed_by instanceof User)) {
				$this->completed_by = Users::findById($this->getCompletedById());
			} // if
			if ($this->completed_by instanceof User) {
				return $this->completed_by->getDisplayName();
			} else {
				return '';
			}
		} else return '';
	} // getCompletedBy
	

	/**
	 * Return all handins for this task, NOT the ones associated with its subtasks
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getAllTaskHandins(){
		return ObjectHandins::getAllHandinsByObject($this);
	} //getAllTaskHandins


	/**
	 * Return all pending handins for this task, NOT the ones associated with its subtasks
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getPendingTaskHandins(){
		return ObjectHandins::getPendingHandinsByObject($this);
	} //getPendingTaskHandins

	// ---------------------------------------------------
	//  URLs
	// ---------------------------------------------------

	/**
	 * Return edit task URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getEditUrl() {
		return get_url('task', 'edit_task', array('id' => $this->getId()));
	} // getEditUrl

	/**
	 * Return edit list URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getEditListUrl() {
		return get_url('task', 'edit_task', array('id' => $this->getId()));
	} // getEditUrl

	/**
	 * Return delete task URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getDeleteUrl() {
		return get_url('task', 'delete_task', array('id' => $this->getId()));
	} // getDeleteUrl

	/**
	 * Return delete task list URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getDeleteListUrl() {
		return get_url('task', 'delete_task', array('id' => $this->getId()));
	} // getDeleteUrl

	/**
	 * Return comete task URL
	 *
	 * @access public
	 * @param string $redirect_to Redirect to this URL (referer will be used if this URL is not provided)
	 * @return string
	 */
	function getCompleteUrl($redirect_to = null) {
		$params = array(
        'id' => $this->getId()
		); // array

		if(trim($redirect_to)) {
			$params['redirect_to'] = $redirect_to;
		} // if

		return get_url('task', 'complete_task', $params);
	} // getCompleteUrl

	/**
	 * Return open task URL
	 *
	 * @access public
	 * @param string $redirect_to Redirect to this URL (referer will be used if this URL is not provided)
	 * @return string
	 */
	function getOpenUrl($redirect_to = null) {
		$params = array(
        'id' => $this->getId()
		); // array

		if(trim($redirect_to)) {
			$params['redirect_to'] = $redirect_to;
		} // if

		return get_url('task', 'open_task', $params);
	} // getOpenUrl


	/**
	 * Return add task url
	 *
	 * @param boolean $redirect_to_list Redirect back to the list when task is added. If false
	 *   after submission user will be redirected to projects tasks page
	 * @return string
	 */
	function getAddTaskUrl($redirect_to_list = true) {
		$attributes = array('id' => $this->getId());
		if($redirect_to_list) {
			$attributes['back_to_list'] = true;
		} // if
		return get_url('task', 'add_task', $attributes);
	} // getAddTaskUrl

	/**
	 * Return reorder tasks URL
	 *
	 * @param boolean $redirect_to_list
	 * @return string
	 */
	function getReorderTasksUrl($redirect_to_list = true) {
		$attributes = array('task_list_id' => $this->getId());
		if($redirect_to_list) {
			$attributes['back_to_list'] = true;
		} // if
		return get_url('task', 'reorder_tasks', $attributes);
	} // getReorderTasksUrl
	 
	/**
	 * Return view list URL
	 *
	 * @param void
	 * @return string
	 */
	function getViewUrl() {
		return get_url('task', 'view_task', array('id' => $this->getId()));
	} // getViewUrl
	
	/**
	 * Return print URL
	 *
	 * @param void
	 * @return string
	 */
	function getPrintUrl() {
		return get_url('task', 'print_task', array('id' => $this->getId()));
	} // getViewUrl

	/**
	 * This function will return URL of this specific list on project tasks page
	 *
	 * @param void
	 * @return string
	 */
	function getOverviewUrl() {
		$project = $this->getProject();
		if($project instanceof Project) {
			return $project->getTasksUrl() . '#taskList' . $this->getId();
		} // if
		return '';
	} // getOverviewUrl

	// ---------------------------------------------------
	//  System
	// ---------------------------------------------------

	/**
	 * Validate before save
	 *
	 * @access public
	 * @param array $errors
	 * @return null
	 */
	function validate(&$errors) {
		if(!$this->validatePresenceOf('title')) $errors[] = lang('task title required');
	} // validate

	 
	/**
	 * Delete this task lists
	 *
	 * @access public
	 * @param boolean $delete_childs
	 * @return boolean
	 */
	function delete($delete_children = true) {
		if($delete_children)  {
			$children = $this->getSubTasks();
			foreach($children as $child)
				$child->delete(true);
			$this->deleteHandins();
		}
		$related_forms = $this->getRelatedForms();
		if(is_array($related_forms)) {
			foreach($related_forms as $related_form) {
				$related_form->setInObjectId(0);
				$related_form->save();
			} // foreach
		} // if
		$task_list = $this->getParent();
		if($task_list instanceof ProjectTask) $task_list->detachTask($this);
		return parent::delete();
	} // delete
	
	function trash($trash_children = true, $trashDate = null) {
		if (is_null($trashDate))
			$trashDate = DateTimeValueLib::now();
		if($trash_children)  {
			$children = $this->getAllSubTasks();
			foreach($children as $child) {
				$child->trash(true,$trashDate);
				ApplicationLogs::createLog($child, $child->getWorkspaces(), ApplicationLogs::ACTION_TRASH);
			}
		}
		return parent::trash($trashDate);
	} // delete
	
	function archive($archive_children = true, $archiveDate = null) {
		if (is_null($archiveDate))
			$archiveDate = DateTimeValueLib::now();
		if($archive_children)  {
			$children = $this->getAllSubTasks();
			foreach($children as $child) {
				$child->archive(true,$archiveDate);
				ApplicationLogs::createLog($child, $child->getWorkspaces(), ApplicationLogs::ACTION_ARCHIVE);
			}
		}
		return parent::archive($archiveDate);
	} // delete

	/**
	 * Save this list
	 *
	 * @param void
	 * @return boolean
	 */
	function save() {
		if (!$this->isNew()) {
			$old_me = ProjectTasks::findById($this->getId(), true);
			if (!$old_me instanceof ProjectTask) return; // TODO: check this!!!
			/* This was added cause deleting some tasks was giving an error, couldn't reproduce it again, but this solved it */
		}
		if ($this->isNew() ||
				$this->getAssignedToCompanyId() != $old_me->getAssignedToCompanyId() ||
				$this->getAssignedToUserId() != $old_me->getAssignedToUserId()) {
			$this->setAssignedBy(logged_user());
			$this->setAssignedOn(DateTimeValueLib::now());
		}
		
		$due_date_changed = false;
		if (!$this->isNew()) {
			$old_due_date = $old_me->getDueDate();
			$due_date = $this->getDueDate();
			if ($due_date instanceof DateTimeValue) {
				if (!$old_due_date instanceof DateTimeValue || $old_due_date->getTimestamp() != $due_date->getTimestamp()) {
					$due_date_changed = true;
				}
			} else {
				if ($old_due_date instanceof DateTimeValue) {
					$due_date_changed = true;
				}
			}
		}
		parent::save();
		
		if ($due_date_changed) {
			$id = $this->getId();
			$sql = "UPDATE `".TABLE_PREFIX."object_reminders` SET
				`date` = date_sub((SELECT `due_date` FROM `".TABLE_PREFIX."project_tasks` WHERE `id` = $id),
					interval `minutes_before` minute) WHERE
					`object_manager` = 'ProjectTasks' AND `object_id` = $id;";
			DB::execute($sql);
		}

		$tasks = $this->getSubTasks();
		if(is_array($tasks)) {
			$task_ids = array();
			foreach($tasks as $task) {
				$task_ids[] = $task->getId();
			} // if

			if(count($task_ids) > 0) {
				ApplicationLogs::setIsPrivateForType($this->isPrivate(), 'ProjectTasks', $task_ids);
			} // if
		} // if

		return true;
	} // save

	function unarchive($unarchive_children = true){
		$archiveTime = $this->getArchivedOn();
		parent::unarchive();
		if ($unarchive_children){
			$children = $this->getAllSubTasks();
			foreach($children as $child)
				if ($child->isArchived() && $child->getArchivedOn()->getTimestamp() == $archiveTime->getTimestamp())
					$child->unarchive(false);
		}
	}

	function untrash($untrash_children = true){
		$deleteTime = $this->getTrashedOn();
		parent::untrash();
		if ($untrash_children){
			$children = $this->getAllSubTasks();
			foreach($children as $child)
				if ($child->isTrashed() && $child->getTrashedOn()->getTimestamp() == $deleteTime->getTimestamp())
					$child->untrash(false);
		}

		if ($this->hasOpenTimeslots()){
			$openTimeslots = $this->getOpenTimeslots();
			foreach ($openTimeslots as $timeslot){
				if (!$timeslot->isPaused()){
					$timeslot->setPausedOn($deleteTime);
					$timeslot->resume();
					$timeslot->save();
				}
			}
		}
	}

	/**
	 * Drop all tasks that are in this list
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function deleteSubTasks() {
		return ProjectTasks::delete(DB::escapeField('parent_id') . ' = ' . DB::escape($this->getId()));
	} // deleteTasks

	/**
	 * Drop all tasks that are in this list
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function deleteHandins() {
		$q=DB::escapeField('rel_object_id') . ' = ' . DB::escape($this->getId()) . ' AND ' .
		DB::escapeField('rel_object_manager') . ' = ' . DB::escape(get_class($this->manager()));
		return ObjectHandins::delete($q);
	} // deleteTasks

	// ---------------------------------------------------
	//  ApplicationDataObject implementation
	// ---------------------------------------------------

	/**
	 * Return object name
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getObjectName($charLimit = 0) {
		$name = $this->getTitle();
		if (!$name) {
			$name = $this->getText();
		}
		if ($charLimit > 0 && strlen_utf($name) > $charLimit)
			return substr_utf($name, 0, $charLimit) . '...';
		else
			return $name;
	} // getObjectName
	

	/**
	 * Return object type name
	 *
	 * @param void
	 * @return string
	 */
	function getObjectTypeName() {
		return 'task';
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
	
	/**
	 * Return object for task listing
	 *
	 * @return unknown
	 */
	function getDashboardObject(){
    	if($this->getUpdatedById() > 0 && $this->getUpdatedBy() instanceof User){
    		$updated_by_id = $this->getUpdatedBy()->getObjectId();
    		$updated_by_name = $this->getUpdatedByDisplayName();
			$updated_on = $this->getObjectUpdateTime() instanceof DateTimeValue ? ($this->getObjectUpdateTime()->isToday() ? format_time($this->getObjectUpdateTime()) : format_datetime($this->getObjectUpdateTime())) : lang('n/a');	
    	}else {
    		if($this->getCreatedBy())
    			$updated_by_id = $this->getCreatedBy()->getId();
    		else
    			$updated_by_id = lang('n/a');
    		$updated_by_name = $this->getCreatedByDisplayName();
			$updated_on = $this->getObjectCreationTime() instanceof DateTimeValue ? ($this->getObjectCreationTime()->isToday() ? format_time($this->getObjectCreationTime()) : format_datetime($this->getObjectCreationTime())) : lang('n/a');
    	}
    	if ($this instanceof ProjectTask)
    		$parent_id = $this->getParentId();
    	else 
    		$parent_id = $this->getId();
   	
		$deletedOn = $this->getTrashedOn() instanceof DateTimeValue ? ($this->getTrashedOn()->isToday() ? format_time($this->getTrashedOn()) : format_datetime($this->getTrashedOn(), 'M j')) : lang('n/a');
		if ($this->getTrashedById() > 0)
			$deletedBy = Users::findById($this->getTrashedById());
    	if (isset($deletedBy) && $deletedBy instanceof User) {
    		$deletedBy = $deletedBy->getDisplayName();
    	} else {
    		$deletedBy = lang("n/a");
    	}
    	
		$archivedOn = $this->getArchivedOn() instanceof DateTimeValue ? ($this->getArchivedOn()->isToday() ? format_time($this->getArchivedOn()) : format_datetime($this->getArchivedOn(), 'M j')) : lang('n/a');
		if ($this->getArchivedById() > 0)
			$archivedBy = Users::findById($this->getArchivedById());
    	if (isset($archivedBy) && $archivedBy instanceof User) {
    		$archivedBy = $archivedBy->getDisplayName();
    	} else {
    		$archivedBy = lang("n/a");
    	}
    		
    	return array(
				"id" => $this->getObjectTypeName() . $this->getId(),
				"object_id" => $this->getId(),
				"name" => $this->getObjectName(),
				"type" => $this->getObjectTypeName(),
				"tags" => project_object_tags($this),
				"createdBy" => $this->getCreatedByDisplayName(),
				"createdById" => $this->getCreatedById(),
    			"dateCreated" => $this->getObjectCreationTime() instanceof DateTimeValue ? ($this->getObjectCreationTime()->isToday() ? format_time($this->getObjectCreationTime()) : format_datetime($this->getObjectCreationTime())) : lang('n/a'),
				"updatedBy" => $updated_by_name,
				"updatedById" => $updated_by_id,
				"dateUpdated" => $updated_on,
				"wsIds" => $this->getWorkspacesIdsCSV(logged_user()->getWorkspacesQuery()),
				"url" => $this->getObjectUrl(),
				"parentId" => $parent_id,
				"status" => "Pending",
				"manager" => get_class($this->manager()),
    			"deletedById" => $this->getTrashedById(),
    			"deletedBy" => $deletedBy,
    			"dateDeleted" => $deletedOn,
    			"archivedById" => $this->getArchivedById(),
    			"archivedBy" => $archivedBy,
    			"dateArchived" => $archivedOn,
    			"isRead" => $this->getIsRead(logged_user()->getId())
			);
    }

    /**
	 * Returns true if the task has a subtask with id $id.
	 * 
	 * @param integer $id id to look for
	 * @return boolean
	 */
	function hasChild($id) {
		foreach ($this->getSubTasks() as $sub) {
			if ($sub->getId() == $id || $sub->hasChild($id)) {
				return true;
			}
		}
		return false;
	}
	
	
	/**
	 * Begin task templates
	 */
	function getAssignTemplateToWSUrl(){
		return get_url('administration','assign_task_template_to_ws',array('id'=> $this->getId()));
	}
	/**
	 * End task templates
	 */
	
	function getArrayInfo(){
		$result = array(
			'id' => $this->getId(),
			't' => $this->getTitle(),
			'wsid' => $this->getWorkspacesIdsCSV(),
			'c' => $this->getCreatedOn() instanceof DateTimeValue ? $this->getCreatedOn()->getTimestamp() : 0,
			'cid' => $this->getCreatedById(),
			'isread' => $this->getIsRead(logged_user()->getId()),
			'otype' => $this->getObjectSubtype()
			);
		
		if ($this->isCompleted())
			$result['s'] = 1;
		else $result['s'] = $this->getState();
			
		if ($this->getParentId() > 0)
			$result['pid'] = $this->getParentId();
		
		if ($this->getPriority() != 200)
			$result['pr'] = $this->getPriority();
		
		if ($this->getMilestoneId() > 0)
			$result['mid'] = $this->getMilestoneId();
			
		if ($this->getAssignedToUserId() > 0 || $this->getAssignedToCompanyId() > 0)
			$result['atid'] = $this->getAssignedToCompanyId() . ':' . $this->getAssignedToUserId();
			
		if ($this->getCompletedById() > 0){
			$result['cbid'] = $this->getCompletedById();
			$result['con'] = $this->getCompletedOn()->getTimestamp();
		}
			
		if ($this->getDueDate())
			$result['dd'] = $this->getDueDate()->getTimestamp();
		if ($this->getStartDate())
			$result['sd'] = $this->getStartDate()->getTimestamp();
		
		$result['tz'] = logged_user()->getTimezone() * 3600;
		
		$ot = $this->getOpenTimeslots();
		
		if ($ot){
			$users = array();
			$time = array();
			$paused = array();
			foreach ($ot as $t){
				$time[] = $t->getSeconds();
				$users[] = $t->getUserId();
				$paused[] = $t->isPaused()?1:0;
				if ($t->isPaused() && $t->getUserId() == logged_user()->getId())
					$result['wpt'] = $t->getPausedOn()->getTimestamp();
			}
			$result['wt'] = $time;
			$result['wid'] = $users;
			$result['wp'] = $paused;
		}
		
		$tags = $this->getTagNames();
		if ($tags)
			$result['tags'] = $tags;
		
		if ($this->isRepetitive())
			$result['rep'] = 1;

		return $result;
	}
	
	function isRepetitive() {
		return ($this->getRepeatForever() > 0 || $this->getRepeatNum() > 0 || 
			($this->getRepeatEnd() instanceof DateTimeValue && $this->getRepeatEnd()->toMySQL() != EMPTY_DATETIME) );
	}
	
	function getOpenTimeslots(){
		if (is_null($this->timeslots)){
			return Timeslots::getOpenTimeslotsByObject($this);
		} else {
			$result = array();
			for ($i = 0; $i < count($this->timeslots); $i++)
				if ($this->timeslots[$i]->isOpen())
					$result[] = $this->timeslots[$i];
			return $result;
		}
	}
	
	/**
	 * Notifies the user of comments and due date of this task
	 *
	 * @param User $user
	 */
	function subscribeUser($user) {
		parent::subscribeUser($user);
	}
	
	/**
	 * Stops notifying user of comments and due date
	 *
	 * @param unknown_type $user
	 */
	function unsubscribeUser($user) {
		parent::unsubscribeUser($user);
		//ObjectReminders::clearByObject($this);
	}
	
	/**
	 * Set the task's project
	 * @param $project
	 */
	function setProject($project) {
		$this->removeFromAllWorkspaces();
		$this->addToWorkspace($project);
		$this->project = null;
	}
	
	/**
	 * Get task's project's id
	 */
	function getProjectId() {
		$project = $this->getProject();
		if ($project instanceof Project) return $project->getId();
		return 0;
	}
	
} // ProjectTask

?>