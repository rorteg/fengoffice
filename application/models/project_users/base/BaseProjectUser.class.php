<?php

  /**
  * BaseProjectUser class
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  abstract class BaseProjectUser extends DataObject {
  
    // -------------------------------------------------------
    //  Access methods
    // -------------------------------------------------------
  
    /**
    * Return value of 'project_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getProjectId() {
      return $this->getColumnValue('project_id');
    } // getProjectId()
    
    /**
    * Set value of 'project_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setProjectId($value) {
      return $this->setColumnValue('project_id', $value);
    } // setProjectId() 
    
    /**
    * Return value of 'user_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getUserId() {
      return $this->getColumnValue('user_id');
    } // getUserId()
    
    /**
    * Set value of 'user_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setUserId($value) {
      return $this->setColumnValue('user_id', $value);
    } // setUserId() 
    
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
    * Return value of 'can_read_messages' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getCanReadMessages() {
      return $this->getColumnValue('can_read_messages');
    } // getCanReadMessages()   
    
    /**
    * Set value of 'can_read_messages' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setCanReadMessages($value) {
      return $this->setColumnValue('can_read_messages', $value);
    } // setCanReadMessages() 
    
   
    /**
    * Return value of 'can_read_tasks' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getCanReadTasks() {
      return $this->getColumnValue('can_read_tasks');
    } // getCanReadTasks()
    
    /**
    * Set value of 'can_read_tasks' field
    *
    * @access public   	
    * @param boolean $value
    * @return boolean
    */
    function setCanReadTasks($value) {
      return $this->setColumnValue('can_read_tasks', $value);
    } // setCanReadTasks() 
    
    /**
    * Return value of 'can_read_weblinks' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getCanReadWeblinks() {
      return $this->getColumnValue('can_read_weblinks');
    } // getCanReadWeblinks()
    
    /**
    * Set value of 'can_read_weblinks' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setCanReadWeblinks($value) {
      return $this->setColumnValue('can_read_weblinks', $value);
    } // setCanReadWeblinks() 
    
    /**
    * Return value of 'can_read_milestones' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getCanReadMilestones() {
      return $this->getColumnValue('can_read_milestones');
    } // getCanReadMilestones()
    
    /**
    * Set value of 'can_read_milestones' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setCanReadMilestones($value) {
      return $this->setColumnValue('can_read_milestones', $value);
    } // setCanReadMilestones() 
    
    /**
    * Return value of 'can_read_mails' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getCanReadMails() {
      return $this->getColumnValue('can_read_mails');
    } // getCanReadMails()
    
    /**
    * Set value of 'can_read_mails' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setCanReadMails($value) {
      return $this->setColumnValue('can_read_mails', $value);
    } // setCanReadMails() 
        
    /**
    * Return value of 'can_read_files' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getCanReadContacts() {
      return $this->getColumnValue('can_read_contacts');
    } // getCanReadContacts()
    
    /**
    * Set value of 'can_read_files' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setCanReadContacts($value) {
      return $this->setColumnValue('can_read_contacts', $value);
    } // setCanReadContacts()  
       
    /**
    * Return value of 'can_read_comments' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getCanReadComments() {
      return $this->getColumnValue('can_read_comments');
    } // getCanReadComments()
    
    /**
    * Set value of 'can_read_comments' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setCanReadComments($value) {
      return $this->setColumnValue('can_read_comments', $value);
    } // setCanReadComments()     
    /**
    * Return value of 'can_read_files' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getCanReadFiles() {
      return $this->getColumnValue('can_read_files');
    } // getCanReadFiles()
    
    /**
    * Set value of 'can_read_files' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setCanReadFiles($value) {
      return $this->setColumnValue('can_read_files', $value);
    } // setCanReadFiles() 
    
    
    /**
    * Return value of 'can_read_events' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getCanReadEvents() {
      return $this->getColumnValue('can_read_events');
    } // getCanReadEvents()
    
    /**
    * Set value of 'can_read_events' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setCanReadEvents($value) {
      return $this->setColumnValue('can_read_events', $value);
    } // getCanReadEvents() 
    
    
    
    /**
    * Return value of 'can_write_messages' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getCanWriteMessages() {
      return $this->getColumnValue('can_write_messages');
    } // getCanWriteMessages()   
    
    /**
    * Set value of 'can_write_messages' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setCanWriteMessages($value) {
      return $this->setColumnValue('can_write_messages', $value);
    } // setCanWriteMessages() 
    
   
    /**
    * Return value of 'can_write_tasks' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getCanWriteTasks() {
      return $this->getColumnValue('can_write_tasks');
    } // getCanWriteTasks()
    
    /**
    * Set value of 'can_write_tasks' field
    *
    * @access public   	
    * @param boolean $value
    * @return boolean
    */
    function setCanWriteTasks($value) {
      return $this->setColumnValue('can_write_tasks', $value);
    } // setCanWriteTasks() 
    
    /**
    * Return value of 'can_write_weblinks' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getCanWriteWeblinks() {
      return $this->getColumnValue('can_write_weblinks');
    } // getCanWriteWeblinks()
    
    /**
    * Set value of 'can_write_weblinks' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setCanWriteWeblinks($value) {
      return $this->setColumnValue('can_write_weblinks', $value);
    } // setCanWriteWeblinks() 
    
    /**
    * Return value of 'can_write_milestones' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getCanWriteMilestones() {
      return $this->getColumnValue('can_write_milestones');
    } // getCanWriteMilestones()
    
    /**
    * Set value of 'can_write_milestones' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setCanWriteMilestones($value) {
      return $this->setColumnValue('can_write_milestones', $value);
    } // setCanWriteMilestones() 
    
    /**
    * Return value of 'can_write_mails' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getCanWriteMails() {
      return $this->getColumnValue('can_write_mails');
    } // getCanWriteMails()
    
    /**
    * Set value of 'can_write_mails' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setCanWriteMails($value) {
      return $this->setColumnValue('can_write_mails', $value);
    } // setCanWriteMails() 
        
    /**
    * Return value of 'can_write_files' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getCanWriteContacts() {
      return $this->getColumnValue('can_write_contacts');
    } // getCanWriteContacts()
    
    /**
    * Set value of 'can_write_files' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setCanWriteContacts($value) {
      return $this->setColumnValue('can_write_contacts', $value);
    } // setCanWriteContacts()  
       
    /**
    * Return value of 'can_write_comments' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getCanWriteComments() {
      return $this->getColumnValue('can_write_comments');
    } // getCanWriteComments()
    
    /**
    * Set value of 'can_write_comments' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setCanWriteComments($value) {
      return $this->setColumnValue('can_write_comments', $value);
    } // setCanWriteComments()     
    /**
    * Return value of 'can_write_files' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getCanWriteFiles() {
      return $this->getColumnValue('can_write_files');
    } // getCanWriteFiles()
    
    /**
    * Set value of 'can_write_files' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setCanWriteFiles($value) {
      return $this->setColumnValue('can_write_files', $value);
    } // setCanWriteFiles() 
    
    
    /**
    * Return value of 'can_write_events' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getCanWriteEvents() {
      return $this->getColumnValue('can_write_events');
    } // getCanWriteEvents()
    
    /**
    * Set value of 'can_write_events' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setCanWriteEvents($value) {
      return $this->setColumnValue('can_write_events', $value);
    } // getCanWriteEvents() 
    
    /**
    * Return value of 'can_assign_to_owners' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getCanAssignToOwners() {
      return $this->getColumnValue('can_assign_to_owners');
    } // getCanAssignToOwners()
    
    /**
    * Set value of 'can_assign_to_owners' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setCanAssignToOwners($value) {
      return $this->setColumnValue('can_assign_to_owners', $value);
    } // setCanAssignToOwners() 
    
    /**
    * Return value of 'can_assign_to_other' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getCanAssignToOther() {
      return $this->getColumnValue('can_assign_to_other');
    } // getCanAssignToOther()
    
    /**
    * Set value of 'can_assign_to_other' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setCanAssignToOther($value) {
      return $this->setColumnValue('can_assign_to_other', $value);
    } // setCanAssignToOther() 
    
    
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return ProjectUsers 
    */
    function manager() {
      if(!($this->manager instanceof ProjectUsers)) $this->manager = ProjectUsers::instance();
      return $this->manager;
    } // manager
  
  } // BaseProjectUser 

?>