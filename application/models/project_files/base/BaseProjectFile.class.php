<?php

  /**
  * BaseProjectFile class
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  abstract class BaseProjectFile extends ProjectDataObject {
  
  	protected $objectTypeIdentifier = 'd';
  
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
    * Return value of 'filename' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getFilename() {
      return $this->getColumnValue('filename');
    } // getFilename()
    
    /**
    * Set value of 'filename' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setFilename($value) {
      return $this->setColumnValue('filename', $value);
    } // setFilename() 
    
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
    * Return value of 'is_locked' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getIsLocked() {
      return $this->getColumnValue('is_locked');
    } // getIsLocked()
    
    /**
    * Set value of 'is_locked' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setIsLocked($value) {
      return $this->setColumnValue('is_locked', $value);
    } // setIsLocked() 
    
    /**
    * Return value of 'is_visible' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getIsVisible() {
      return $this->getColumnValue('is_visible');
    } // getIsVisible()
    
    /**
    * Set value of 'is_visible' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setIsVisible($value) {
      return $this->setColumnValue('is_visible', $value);
    } // setIsVisible() 
    
    /**
    * Return value of 'expiration_time' field
    *
    * @access public
    * @param void
    * @return DateTimeValue 
    */
    function getExpirationTime() {
      return $this->getColumnValue('expiration_time');
    } // getExpirationTime()
    
    /**
    * Set value of 'expiration_time' field
    *
    * @access public   
    * @param DateTimeValue $value
    * @return boolean
    */
    function setExpirationTime($value) {
      return $this->setColumnValue('expiration_time', $value);
    } // setExpirationTime() 
    
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

    /**
     * Return value of 'checked_out_on' field
     *
     * @access public
     * @param void
     * @return DateTimeValue
     */
    function getCheckedOutOn() {
    	return $this->getColumnValue('checked_out_on');
    } // getCheckedOutOn()

    /**
     * Set value of 'checked_out_on' field
     *
     * @access public
     * @param DateTimeValue $value
     * @return boolean
     */
    function setCheckedOutOn($value) {
    	return $this->setColumnValue('checked_out_on', $value);
    } // setCheckedOutOn()

    /**
     * Return value of 'checked_out_by_id' field
     *
     * @access public
     * @param void
     * @return integer
     */
    function getCheckedOutById() {
    	return $this->getColumnValue('checked_out_by_id');
    } // getCheckedOutById()

    /**
     * Set value of 'checked_out_by_id' field
     *
     * @access public
     * @param integer $value
     * @return boolean
     */
    function setCheckedOutById($value) {
    	return $this->setColumnValue('checked_out_by_id', $value);
    } // setCheckedOutById()
    
    /**
    * Set value of 'was_auto_checked_out' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setWasAutoCheckedAuto($value) {
      return $this->setColumnValue('was_auto_checked_out', $value);
    } //  setWasAutoCheckedAuto() 
    
    /**
    * Return value of 'was_auto_checked_out' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function  getWasAutoCheckedAuto() {
      return $this->getColumnValue('was_auto_checked_out');
    } //  getWasAutoCheckedAuto()

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
    * Return value of 'type' field, contains an id of an email if the file is an attachment
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getType() {
      return $this->getColumnValue('type');
    } // getType()
    
    /**
    * Set value of 'type' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setType($value) {
      return $this->setColumnValue('type', $value);
    } // setType() 
    
    /**
    * Return value of 'url' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getUrl() {
      return $this->getColumnValue('url');
    } // getUrl()
    
    /**
    * Set value of 'url' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setUrl($value) {
      return $this->setColumnValue('url', $value);
    } // setUrl() 
    
    /**
    * Return value of 'mail_id' field, contains an id of an email if the file is an attachment
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getMailId() {
      return $this->getColumnValue('mail_id');
    } // getMailId()
    
    /**
    * Set value of 'mail_id' field (id of an email if the file is an attachment)
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setMailId($value) {
      return $this->setColumnValue('mail_id', $value);
    } // setMailId()

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
    * @return ProjectFiles 
    */
    function manager() {
      if(!($this->manager instanceof ProjectFiles)) $this->manager = ProjectFiles::instance();
      return $this->manager;
    } // manager
    
  
  } // BaseProjectFile 
   
    
?>