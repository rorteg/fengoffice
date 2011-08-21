<?php

  /**
  * BaseProjectMilestone class
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  abstract class BaseProjectMilestone extends ProjectDataObject {
  
  	protected $objectTypeIdentifier = 'mi';
  
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
    * Return value of 'name' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getName() {
      return $this->getColumnValue('name');
    } // getName()
    
    /**
    * Set value of 'name' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setName($value) {
      return $this->setColumnValue('name', $value);
    } // setName() 
    
    /**
    * Return value of 'description' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getDescription() {
      return $this->getColumnValue('description');
    } // getDescription()
    
    /**
    * Set value of 'description' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setDescription($value) {
      return $this->setColumnValue('description', $value);
    } // setDescription() 
    
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
    
    /** Return value of 'is_urgent' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getIsUrgent() {
      return $this->getColumnValue('is_urgent');
    } // getIsUrgent()
    
    /**
    * Set value of 'is_urgent' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setIsUrgent($value) {
      return $this->setColumnValue('is_urgent', $value);
    } // setIsUrgent() 
    
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
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return ProjectMilestones 
    */
    function manager() {
      if(!($this->manager instanceof ProjectMilestones)) $this->manager = ProjectMilestones::instance();
      return $this->manager;
    } // manager
  
    
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
    
  } // BaseProjectMilestone 

?>