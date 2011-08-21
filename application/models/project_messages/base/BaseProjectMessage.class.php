<?php

  /**
  * BaseProjectMessage class
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  abstract class BaseProjectMessage extends ProjectDataObject {
  
  	protected $objectTypeIdentifier = 'me';
  
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
    * Return value of 'additional_text' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getAdditionalText() {
      return $this->getColumnValue('additional_text');
    } // getAdditionalText()
    
    /**
    * Set value of 'additional_text' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setAdditionalText($value) {
      return $this->setColumnValue('additional_text', $value);
    } // setAdditionalText() 
    
    /**
    * Return value of 'is_important' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getIsImportant() {
      return $this->getColumnValue('is_important');
    } // getIsImportant()
    
    /**
    * Set value of 'is_important' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setIsImportant($value) {
      return $this->setColumnValue('is_important', $value);
    } // setIsImportant() 
    
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
    * Return value of 'comments_enabled' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getCommentsEnabled() {
      return $this->getColumnValue('comments_enabled');
    } // getCommentsEnabled()
    
    /**
    * Set value of 'comments_enabled' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setCommentsEnabled($value) {
      return $this->setColumnValue('comments_enabled', $value);
    } // setCommentsEnabled() 
    
    /**
    * Return value of 'anonymous_comments_enabled' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getAnonymousCommentsEnabled() {
      return $this->getColumnValue('anonymous_comments_enabled');
    } // getAnonymousCommentsEnabled()
    
    /**
    * Set value of 'anonymous_comments_enabled' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setAnonymousCommentsEnabled($value) {
      return $this->setColumnValue('anonymous_comments_enabled', $value);
    } // setAnonymousCommentsEnabled() 
    
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
    
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return ProjectMessages 
    */
    function manager() {
      if(!($this->manager instanceof ProjectMessages)) $this->manager = ProjectMessages::instance();
      return $this->manager;
    } // manager
  
  } // BaseProjectMessage 

?>