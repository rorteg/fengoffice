<?php

/**
 * BaseProjectTask class
 *
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
abstract class BaseProjectTask extends ProjectDataObject {
  
  	protected $objectTypeIdentifier = 'ta';

	// -------------------------------------------------------
	//  Access methods
	// -------------------------------------------------------

	/**
	 * Return value of 'id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getId() {
		return $this->getColumnValue('id');
	} // getId()

	/**
	 * Set value of 'id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setId($value) {
		return $this->setColumnValue('id', $value);
	} // setId()

	/**
	 * Return value of 'parent_id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getParentId() {
		return $this->getColumnValue('parent_id');
	} //  getParentId()

	/**
	 * Set value of 'parent_id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setParentId($value) {
		return $this->setColumnValue('parent_id', $value);
	} // setparentId()

	/**
	 * Return value of 'is_private' field
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function getIsPrivate() {
		return $this->getColumnValue('is_private');
	} // getIsPrivate()

	/**
	 * Set value of 'is_private' field
	 *
	 * @access public
	 * @param boolean $value
	 * @return boolean
	 */
	function setIsPrivate($value) {
		return $this->setColumnValue('is_private', $value);
	} // setIsPrivate()

	/**
	 * Return value of 'text' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getText() {
		return $this->getColumnValue('text');
	} // getText()

	/**
	 * Set value of 'text' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setText($value) {
		return $this->setColumnValue('text', $value);
	} // setText()

	/**
	 * Return value of 'assigned_to_company_id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getAssignedToCompanyId() {
		return $this->getColumnValue('assigned_to_company_id');
	} // getAssignedToCompanyId()

	/**
	 * Set value of 'assigned_to_company_id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setAssignedToCompanyId($value) {
		return $this->setColumnValue('assigned_to_company_id', $value);
	} // setAssignedToCompanyId()

	/**
	 * Return value of 'assigned_to_user_id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getAssignedToUserId() {
		return $this->getColumnValue('assigned_to_user_id');
	} // getAssignedToUserId()

	/**
	 * Set value of 'assigned_to_user_id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setAssignedToUserId($value) {
		return $this->setColumnValue('assigned_to_user_id', $value);
	} // setAssignedToUserId()

	/**
	 * Return value of 'completed_on' field
	 *
	 * @access public
	 * @param void
	 * @return DateTimeValue
	 */
	function getCompletedOn() {
		return $this->getColumnValue('completed_on');
	} // getCompletedOn()

	/**
	 * Set value of 'completed_on' field
	 *
	 * @access public
	 * @param DateTimeValue $value
	 * @return boolean
	 */
	function setCompletedOn($value) {
		return $this->setColumnValue('completed_on', $value);
	} // setCompletedOn()

	/**
	 * Return value of 'completed_by_id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getCompletedById() {
		return $this->getColumnValue('completed_by_id');
	} // getCompletedById()

	/**
	 * Set value of 'completed_by_id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setCompletedById($value) {
		return $this->setColumnValue('completed_by_id', $value);
	} // setCompletedById()

	/**
	 * Return value of 'due_date' field
	 *
	 * @access public
	 * @param void
	 * @return DateTimeValue
	 */
	function getDueDate() {
		return $this->getColumnValue('due_date');
	} // getDueDate()

	/**
	 * Set value of 'due_date' field
	 *
	 * @access public
	 * @param DateTimeValue $value
	 * @return boolean
	 */
	function setDueDate($value) {
		return $this->setColumnValue('due_date', $value);
	} // setDueDate()


	/**
	 * Return value of 'start_date' field
	 *
	 * @access public
	 * @param void
	 * @return DateTimeValue
	 */
	function getStartDate() {
		return $this->getColumnValue('start_date');
	} // getStartDate()

	/**
	 * Set value of 'start_date' field
	 *
	 * @access public
	 * @param DateTimeValue $value
	 * @return boolean
	 */
	function setStartDate($value) {
		return $this->setColumnValue('start_date', $value);
	} // setStartDate()

	/**
	 * Return value of 'created_on' field
	 *
	 * @access public
	 * @param void
	 * @return DateTimeValue
	 */
	function getCreatedOn() {
		return $this->getColumnValue('created_on');
	} // getCreatedOn()

	/**
	 * Set value of 'created_on' field
	 *
	 * @access public
	 * @param DateTimeValue $value
	 * @return boolean
	 */
	function setCreatedOn($value) {
		return $this->setColumnValue('created_on', $value);
	} // setCreatedOn()

	/**
	 * Return value of 'created_by_id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getCreatedById() {
		return $this->getColumnValue('created_by_id');
	} // getCreatedById()

	/**
	 * Set value of 'created_by_id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setCreatedById($value) {
		return $this->setColumnValue('created_by_id', $value);
	} // setCreatedById()

	/**
	 * Return value of 'updated_on' field
	 *
	 * @access public
	 * @param void
	 * @return DateTimeValue
	 */
	function getUpdatedOn() {
		return $this->getColumnValue('updated_on');
	} // getUpdatedOn()

	/**
	 * Set value of 'updated_on' field
	 *
	 * @access public
	 * @param DateTimeValue $value
	 * @return boolean
	 */
	function setUpdatedOn($value) {
		return $this->setColumnValue('updated_on', $value);
	} // setUpdatedOn()

	/**
	 * Return value of 'updated_by_id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getUpdatedById() {
		return $this->getColumnValue('updated_by_id');
	} // getUpdatedById()

	/**
	 * Set value of 'updated_by_id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setUpdatedById($value) {
		return $this->setColumnValue('updated_by_id', $value);
	} // setUpdatedById()

	/**
	 * Return value of 'order' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getOrder() {
		return $this->getColumnValue('order');
	} // getOrder()

	/**
	 * Set value of 'order' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setOrder($value) {
		return $this->setColumnValue('order', $value);
	} // setOrder()

	/**
	 * Return value of 'milestone_id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getMilestoneId() {
		return $this->getColumnValue('milestone_id');
	} // getMilestoneId()

	/**
	 * Set value of 'milestone_id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setMilestoneId($value) {
		return $this->setColumnValue('milestone_id', $value);
	} // setMilestoneId()

	/**
	 * Return value of 'assigned_on' field
	 *
	 * @access public
	 * @param void
	 * @return DateTimeValue
	 */
	function getAssignedOn() {
		return $this->getColumnValue('assigned_on');
	} // getAssignedOn()

	/**
	 * Set value of 'assigned_on' field.
	 *
	 * @access public
	 * @param DateTimeValue $value
	 * @return boolean
	 */
	function setAssignedOn($value) {
		$this->setColumnValue('assigned_on', $value);
	} // setAssignedOn()

	/**
	 * Return value of 'assigned_by_id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getAssignedById() {
		return $this->getColumnValue('assigned_by_id');
	} // getAssignedById()

	/**
	 * Set value of 'assigned_by_id' field.
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setAssignedById($value) {
		$this->setColumnValue('assigned_by_id', $value);
	} // setAssignedById()
	
	
	/**
	 * Return value of 'time_estimate' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getTimeEstimate() {
		return $this->getColumnValue('time_estimate');
	} // getTimeEstimate()

	/**
	 * Set value of 'time_estimate' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setTimeEstimate($value) {
		return $this->setColumnValue('time_estimate', $value);
	} // setTimeEstimate()
	
	
	
	/**
	 * Return manager instance
	 *
	 * @access protected
	 * @param void
	 * @return ProjectTasks
	 */
	function manager() {
		if(!($this->manager instanceof ProjectTasks)) $this->manager = ProjectTasks::instance();
		return $this->manager;
	} // manager


	/**
	 * Return value of 'priority' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getPriority() {
		return $this->getColumnValue('priority');
	} // getpriority()

	/**
	 * Set value of 'priority' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setPriority($value) {
		return $this->setColumnValue('priority', $value);
	} // setpriority()

	/**
	 * Return value of 'state' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getState() {
		return $this->getColumnValue('state');
	} // getState()

	/**
	 * Set value of 'State' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setState($value) {
		return $this->setColumnValue('state', $value);
	} // setState()

	/**
	 * Return value of 'started_on' field
	 *
	 * @access public
	 * @param void
	 * @return DateTimeValue
	 */
	function getStartedOn() {
		return $this->getColumnValue('started_on');
	} // getStartedOn()

	/**
	 * Set value of 'started_on' field
	 *
	 * @access public
	 * @param DateTimeValue $value
	 * @return boolean
	 */
	function setStartedOn($value) {
		return $this->setColumnValue('started_on', $value);
	} // setStartedOn()

	/**
	 * Return value of 'started_by_id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getStartedById() {
		return $this->getColumnValue('started_by_id');
	} // getStartedById()

	/**
	 * Set value of 'started_by_id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setStartedById($value) {
		return $this->setColumnValue('started_by_id', $value);
	} // setStartedById()

	/**
	 * Return value of 'title' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getTitle() {
		return $this->getColumnValue('title');
	} // getTitle()

	/**
	 * Set value of 'title' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setTitle($value) {
		return $this->setColumnValue('title', $value);
	} // setTitle()
	

	/**
	 * Return value of 'is_template' field
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function getIsTemplate() {
		return $this->getColumnValue('is_template');
	} // getIsTemplate()

	/**
	 * Set value of 'is_template' field
	 *
	 * @access public
	 * @param boolean $value
	 * @return boolean
	 */
	function setIsTemplate($value) {
		return $this->setColumnValue('is_template', $value);
	} // setIsTemplate()
	

	/**
	 * Return value of 'from_template_id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getFromTemplateId() {
		return $this->getColumnValue('from_template_id');
	} // getFromTemplateId()

	/**
	 * Set value of 'from_template_id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setFromTemplateId($value) {
		return $this->setColumnValue('from_template_id', $value);
	} // setFromTemplateId()

	/** Return value of 'trashed_on' field
    *
    * @access public
    * @param void
    * @return DateTimeValue 
    */
    function getTrashedOn() {
      return $this->getColumnValue('trashed_on');
    } // getTrashedOn()
    
    /**
    * Set value of 'trashed_on' field
    *
    * @access public   
    * @param DateTimeValue $value
    * @return boolean
    */
    function setTrashedOn($value) {
      return $this->setColumnValue('trashed_on', $value);
    } // setTrashedOn() 
    
    /**
    * Return value of 'trashed_by_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getTrashedById() {
      return $this->getColumnValue('trashed_by_id');
    } // getTrashedById()
    
    /**
    * Set value of 'trashed_by_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setTrashedById($value) {
      return $this->setColumnValue('trashed_by_id', $value);
    } // setTrashedById()
	
    /**
    * Return value of 'repeat_forever' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getRepeatForever() {
      return $this->getColumnValue('repeat_forever');
    } //  getForever()
    
    /**
    * Set value of 'repeat_forever' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function  setRepeatForever($value) {
      return $this->setColumnValue('repeat_forever', $value);
    } //  setForever()

    
    /**
    * Return value of 'repeat_end' field
    *
    * @access public
    * @param void
    * @return Date 
    */
    function getRepeatEnd() {
      return $this->getColumnValue('repeat_end');
    } //  getRepeatEnd()
    
    /**
    * Set value of 'repeat_end' field
    *
    * @access public   
    * @param Date $value
    * @return boolean
    */
    function  setRepeatEnd($value) {
      return $this->setColumnValue('repeat_end', $value);
    } //  setRepeatEnd() 
    
    /**
    * Set value of 'repeat_num' field
    *
    * @access public   
    * @param Integer $value
    * @return boolean
    */
    function  setRepeatNum($value) {
      return $this->setColumnValue('repeat_num', $value);
    } //  setRepeatNum() 
    
    /**
    * Return value of 'repeat_num' field
    *
    * @access public
    * @param void
    * @return  Integer 
    */
    function getRepeatNum() {
      return $this->getColumnValue('repeat_num');
    } //  getRepeatNum()
    
    /**
    * Set value of 'repeat_d' field
    *
    * @access public   
    * @param Integer $value
    * @return boolean
    */
    function  setRepeatD($value) {
      return $this->setColumnValue('repeat_d', $value);
    } //  setRepeatD() 
    
    /**
    * Return value of 'repeat_d' field
    *
    * @access public
    * @param void
    * @return  Integer 
    */
    function getRepeatD() {
      return $this->getColumnValue('repeat_d');
    } //  setRepeatD()
    /**
    * Set value of 'repeat_m' field
    *
    * @access public   
    * @param Integer $value
    * @return boolean
    */
    function  setRepeatM($value) {
      return $this->setColumnValue('repeat_m', $value);
    } //  getRepeatM() 
    
    /**
    * Return value of 'repeat_m' field
    *
    * @access public
    * @param void
    * @return  Integer 
    */
    function getRepeatM() {
      return $this->getColumnValue('repeat_m');
    } //  getRepeatM()
    /**
    * Set value of 'repeat_y' field
    *
    * @access public   
    * @param Integer $value
    * @return boolean
    */
    function  setRepeatY($value) {
      return $this->setColumnValue('repeat_y', $value);
    } //  setRepeatY() 
    
    /**
    * Return value of 'repeat_y' field
    *
    * @access public
    * @param void
    * @return  Integer 
    */
    function getRepeatY() {
      return $this->getColumnValue('repeat_y');
    } //  getRepeatY()
    
	/**
	 * Return value of 'repeat_by' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getRepeatBy() {
		return $this->getColumnValue('repeat_by');
	} // getRepeatBy()

	/**
	 * Set value of 'repeat_by' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setRepeatBy($value) {
		return $this->setColumnValue('repeat_by', $value);
	} // setRepeatBy()

    /**
    * Return value of 'archived_by_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getArchivedById() {
      return $this->getColumnValue('archived_by_id');
    } // getArchivedById()
    
    /**
    * Set value of 'archived_by_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setArchivedById($value) {
      return $this->setColumnValue('archived_by_id', $value);
    } // setArchivedById()
	
    /** Return value of 'archived_on' field
    *
    * @access public
    * @param void
    * @return DateTimeValue 
    */
    function getArchivedOn() {
      return $this->getColumnValue('archived_on');
    } // getArchivedOn()
    
    /**
    * Set value of 'archived_on' field
    *
    * @access public   
    * @param DateTimeValue $value
    * @return boolean
    */
    function setArchivedOn($value) {
      return $this->setColumnValue('archived_on', $value);
    } // setArchivedOn() 
    
    /**
    * Return value of 'object_subtype' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getObjectSubtype() {
      return $this->getColumnValue('object_subtype');
    } // getObjectSubtype()
    
    /**
    * Set value of 'object_subtype' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setObjectSubtype($value) {
      return $this->setColumnValue('object_subtype', $value);
    } // setObjectSubtype()
    
} // BaseProjectTask


?>