<?php

/**
 * Timeslot class
 *
 * @author Carlos Palma <chonwil@gmail.com>
 */
class Timeslot extends BaseTimeslot {

	/**
	 * Timeslot # for specific object
	 *
	 * @var integer
	 */
	protected $timeslot_num = null;
	
	protected $assigned_user = null;
	
	protected $project = null;
	
	protected $object = null;
	
	/**
	 * Return object connected with this action
	 *
	 * @access public
	 * @param void
	 * @return ProjectDataObject
	 */
	function getObject() {
		if(is_null($this->object)) {
			$this->object = get_object_by_manager_and_id($this->getObjectId(), $this->getObjectManager());
		}
		return $this->object;
	} // getObject

	/**
	 * Return project object
	 *
	 * @param void
	 * @return Project
	 */
	function getProject() {
		if(is_null($this->project)) {
			$object = $this->getObject();
			if($object instanceof ProjectDataObject) {
				$project = $object->getproject();
				$this->project = $project instanceof Project ? $project : null;
			} // if
		} // if
		return $this->project;
	} // getProject
	
	function getWorkspaces($wsIds = null) {
		$object = $this->getObject();
		if($this->getObject() instanceof ProjectDataobject)
			return $object->getWorkspaces($wsIds);
		else
			return null;
	} // getProject

	/**
	 * Return project ID
	 *
	 * @param void
	 * @return integer
	 */
	function getProjectId() {
		$project = $this->getProject();
		return $project instanceof Project ? $project->getId() : null;
	} // getProjectId

	/**
	 * Return timeslot #
	 *
	 * @param void
	 * @return integer
	 */
	function getTimeslotNum() {
		if(is_null($this->timeslot_num)) {
			$object = $this->getObject();
			$this->timeslot_num = $object instanceof ProjectDataObject ? $object->getTimeslotNum($this) : 0;
		} // if
		return $this->timeslot_num;
	} // getTimeslotNum

	/**
    * Return user assigned to this timeslot
    *
    * @access public
    * @param void
    * @return User
    */
    function getUser() {
      if(is_null($this->assigned_user)) {
        $this->assigned_user = Users::findById($this->getUserId());
      } // 
      return $this->assigned_user;
    } // getUser
    
    function isOpen() {
    	return $this->getEndTime() == null;
    }
	
    function getMinutes(){
    	if (!$this->getStartTime())
    		return 0;
    		
    	$endTime = $this->getEndTime();
    	if (!$endTime)
    		$endTime = $this->isPaused() ? $this->getPausedOn() : DateTimeValueLib::now();
    	$timeDiff = DateTimeValueLib::get_time_difference($this->getStartTime()->getTimestamp(),$endTime->getTimestamp(), $this->getSubtract());
    	
    	return $timeDiff['days'] * 1440 + $timeDiff['hours'] * 60 + $timeDiff['minutes'];
    }

    function getSeconds(){
    	if (!$this->getStartTime())
    		return 0;
    		
    	$endTime = $this->getEndTime();
    	if (!$endTime)
    		if ($this->getPausedOn())
    			$endTime = $this->getPausedOn();
    		else
    			$endTime = DateTimeValueLib::now();
    	$timeDiff = DateTimeValueLib::get_time_difference($this->getStartTime()->getTimestamp(),$endTime->getTimestamp(), $this->getSubtract());
    	
    	return $timeDiff['days'] * 86400 + $timeDiff['hours'] * 3600  + $timeDiff['minutes']* 60 + $timeDiff['seconds'];
    }

    function getSecondsSincePause(){
    	if (!$this->getPausedOn())
    		return 0;
    		
    	$endTime = DateTimeValueLib::now();
    	$timeDiff = DateTimeValueLib::get_time_difference($this->getPausedOn()->getTimestamp(),$endTime->getTimestamp());
    	
    	return $timeDiff['days'] * 86400 + $timeDiff['hours'] * 3600  + $timeDiff['minutes']* 60 + $timeDiff['seconds'];
    }
    
    function isPaused(){
    	return $this->getPausedOn() != null;
    }
    
    function pause(){
    	if ($this->isPaused())
    		throw new Error('Timeslot is already paused');
    	$dt = DateTimeValueLib::now();
		$this->setPausedOn($dt);
    }
    
    function resume(){
    	if (!$this->isPaused())
    		throw new Error('Timeslot is not paused');
    	$dt = DateTimeValueLib::now();
    	$timeToSubtract = $dt->getTimestamp() - $this->getPausedOn()->getTimestamp();
		$this->setPausedOn(null);
		$this->setSubtract($this->getSubtract() + $timeToSubtract);
    }
    
    function close($description = null){
    	if ($this->isPaused()) {
    		$this->setEndTime($this->getPausedOn());
    	} else {
    	  	$dt = DateTimeValueLib::now();
			$this->setEndTime($dt);
    	}
    	
    	//Set billing info
		if ($this->getObject() instanceof ProjectDataObject && $this->getObject()->getProject() instanceof Project){
			$hours = $this->getMinutes() / 60;
	    	$user = $this->getUser();
			$billing_category_id = $user->getDefaultBillingId();
			$project = $this->getObject()->getProject();
			$this->setBillingId($billing_category_id);
			$hourly_billing = $project->getBillingAmount($billing_category_id);
			$this->setHourlyBilling($hourly_billing);
			$this->setFixedBilling(round($hourly_billing * $hours, 2));
			$this->setIsFixedBilling(false);
		}
		
		if ($description)
			$this->setDescription($description);
    }
    
    
	/**
	 * Return user who completed this timeslot
	 *
	 * @access public
	 * @param void
	 * @return User
	 */
	function getCompletedBy() {
		return $this->getUser();
	} // getCompletedBy
    
    // ---------------------------------------------------
	//  URLs
	// ---------------------------------------------------

	/**
	 * Return tag URL
	 *
	 * @param void
	 * @return string
	 */
	function getViewUrl() {
		$object = $this->getObject();
		return $object instanceof ProjectDataObject ? $object->getObjectUrl() . '#timeslot' . $this->getId() : '';
	} // getViewUrl

	/**
	 * Return add timeslot URL for specific object
	 *
	 * @param ProjectDataObject $object
	 * @return string
	 */
	static function getOpenUrl(ProjectDataObject $object) {
		return get_url('timeslot', 'open', array(
        'object_id' => $object->getObjectId(),
        'object_manager' => get_class($object->manager())
		)); // get_url
	} // getAddUrl
	
	static function getAddTimespanUrl(ProjectDataObject $object) {
		return get_url('timeslot', 'add_timespan', array(
        'object_id' => $object->getObjectId(),
        'object_manager' => get_class($object->manager())
		)); // get_url
	} // getAddUrl
	
	/**
	 * Return close timeslot URL for specific object
	 *
	 * @param ProjectDataObject $object
	 * @return string
	 */
	function getCloseUrl() {
		return get_url('timeslot', 'close', array(
		'id' => $this->getId()
		)); // get_url
	} // getCloseUrl
	
	/**
	 * Return pause timeslot URL for specific object
	 *
	 * @param ProjectDataObject $object
	 * @return string
	 */
	function getPauseUrl() {
		return get_url('timeslot', 'pause', array(
		'id' => $this->getId()
		)); // get_url
	} // getPauseUrl
	
	/**
	 * Return resume timeslot URL for specific object
	 *
	 * @param ProjectDataObject $object
	 * @return string
	 */
	function getResumeUrl() {
		return get_url('timeslot', 'resume', array(
		'id' => $this->getId()
		)); // get_url
	} // getResumeUrl

	/**
	 * Return edit URL
	 *
	 * @param void
	 * @return string
	 */
	function getEditUrl() {
		return get_url('timeslot', 'edit', array('id' => $this->getId()));
	} // getEditUrl

	/**
	 * Return delete URL
	 *
	 * @param void
	 * @return string
	 */
	function getDeleteUrl() {
		return get_url('timeslot', 'delete', array('id' => $this->getId(), 'active_project' => $this->getProjectId()));
	} // getDeleteUrl

	// ---------------------------------------------------
	//  Permissions
	// ---------------------------------------------------

	/**
	 * Can $user view this object
	 *
	 * @param User $user
	 * @return boolean
	 */
	function canView(User $user) {
		return can_read($user,$this);
	} // canView

	/**
	 * Empty implementation of static method.
	 *
	 * Add tag permissions are done through ProjectDataObject::canTimeslot() method. This
	 * will return timeslot permissions for specified object
	 *
	 * @param User $user
	 * @param Project $project
	 * @return boolean
	 */
	function canAdd(User $user, Project $project) {		
		return can_add($user,$project,get_class(Timeslots::instance()));
	} // canAdd

	/**
	 * Empty implementation of static method. Update tag permissions are check by the taggable
	 * object, not tag itself
	 *
	 * @param User $user
	 * @return boolean
	 */
	function canEdit(User $user) {
		return ($user->getId() == $this->getUserId() || $user->isAdministrator());
	} // canEdit

	/**
	 * Empty implementation of static method. Update tag permissions are check by the taggable
	 * object, not tag itself
	 *
	 * @param User $user
	 * @return boolean
	 */
	function canDelete(User $user) {
		return ($user->getId() == $this->getUserId() || $user->isAdministrator());
	} // canDelete

	// ---------------------------------------------------
	//  System
	// ---------------------------------------------------

	/**
	 * Validate before save
	 *
	 * @param array $error
	 * @return null
	 */
	function validate(&$errors) {
		
	} // validate

	/**
	 * Save the object
	 *
	 * @param void
	 * @return boolean
	 */
	function save() {
		$is_new = $this->isNew();
		$saved = parent::save();
		if($saved) {
			$object = $this->getObject();
			if($object instanceof ProjectDataObject) {
				if($is_new) {
					$object->onAddTimeslot($this);
				} else {
					$object->onEditTimeslot($this);
				} // if
			} // if
		} // if
		return $saved;
	} // save

	/**
	 * Delete timeslot
	 *
	 * @param void
	 * @return null
	 */
	function delete() {
		$deleted = parent::delete();
		if($deleted) {
			$object = $this->getObject();
			if($object instanceof ProjectDataObject) {
				$object->onDeleteTimeslot($this);
			} // if
		} // if
		return $deleted;
	} // delete

	// ---------------------------------------------------
	//  ApplicationDataObject implementation
	// ---------------------------------------------------

	/**
	 * Return object name
	 *
	 * @param void
	 * @return string
	 */
	function getObjectName() {
		$object = $this->getObject();
		return $object instanceof ProjectDataObject ? 
			lang('timeslot on object', $object->getObjectName()) : $this->getObjectTypeName();
	} // getObjectName

	/**
	 * Return object type name
	 *
	 * @param void
	 * @return string
	 */
	function getObjectTypeName() {
		return lang('timeslot');
	} // getObjectTypeName

	/**
	 * Return view tag URL
	 *
	 * @param void
	 * @return string
	 */
	function getObjectUrl() {
		return $this->getViewUrl();
	} // getObjectUrl

	function getArrayInfo($return_billing = false) {
		$task_name = '';
		$project_id = 0;
		
		if ($this->getObjectManager() == 'Projects')
			$project_id = $this->getObjectId();
		else if ($this->getObjectManager() == 'ProjectTasks'){
			$project_id = $this->getObject()->getProjectId();
			$task_name = '';
		}
			
		$user = $this->getUser();
		if ($user instanceof User) {
			$displayname = $user->getDisplayName();
		} else {
			$displayname = lang("n/a");
		}
		
		$lastUpdated = '';
		$lastUpdatedBy = '';
		if ($this->getUpdatedOn()->getTimestamp() != $this->getCreatedOn()->getTimestamp()) {
			$lastUpdated = $this->getUpdatedOn()->format(user_config_option('date format'));
			$lastUpdatedBy = $this->getUpdatedByDisplayName();
		}
		
		$result = array(
			'id' => $this->getId(),
			'date' => $this->getStartTime()->getTimestamp(),
			'time' => $this->getSeconds(),
			'pid' => $project_id,
			'uid' => $this->getUserId(),
			'uname' => $displayname,
			'lastupdated' => $lastUpdated,
			'lastupdatedby' => $lastUpdatedBy
		);
		if ($return_billing) {
			$result['hourlybilling'] = $this->getHourlyBilling();
			$result['totalbilling'] = $this->getFixedBilling();
		}
		
		if ($this->getDescription() != '')
			$result['desc'] = $this->getDescription();
			
		if ($task_name != '')
			$result['tn'] = $task_name;
		
		return $result;
	}
} // Timeslot

?>