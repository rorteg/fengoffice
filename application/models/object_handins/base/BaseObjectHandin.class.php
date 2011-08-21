<?php

  /**
  * BaseObjectHandin class
  * Written on Tue, 23 Mar 2008 16:53:08 -0300
  *
  * @author Marcos Saiz <marcos.saiz@fengoffice.com>
  */
  abstract class BaseObjectHandin extends DataObject {
  
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
    * Return value of 'title' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getTitle() {
      return $this->getColumnValue('title');
    } //  getTitle()
    
    /**
    * Set value of 'title' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setTitle($value) {
      return $this->setColumnValue('title', $value);
    } // setProjectId() 
    
    /**
    * Return value of 'text' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getText() {
      return $this->getColumnValue('text');
    } //  gettext()
    
    /**
    * Set value of 'text' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setText($value) {
      return $this->setColumnValue('text', $value);
    } // setProjectId() 

      
    /**
    * Return value of 'responsible_user_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getResponsibleUserId() {
      return $this->getColumnValue('responsible_user_id');
    } // getresponsible_user_id()
    
    /**
    * Set value of 'responsible_user_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setResponsibleUserId($value) {
      return $this->setColumnValue('responsible_user_id', $value);
    } // setId()  

      
    /**
    * Return value of 'responsible_company_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getResponsibleCompanyId() {
      return $this->getColumnValue('responsible_company_id');
    } // getresponsible_user_id()
    
    /**
    * Set value of 'responsible_company_id' field
    *
    * @access public   
    * @param integer $value
    * @return void
    */
    function setResponsibleCompanyId($value) {
      return $this->setColumnValue('responsible_company_id', $value);
    } // setId()     
     
    /**
    * Return value of 'objectid' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getObjectId() {
      return $this->getColumnValue('rel_object_id');
    } // getobject_id()
    
    /**
    * Set value of 'object_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setObjectId($value) {
      return $this->setColumnValue('rel_object_id', $value);
    } // setId() 
       
     
    /**
    * Return value of 'objectmanager' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getObjectManager() {
      return $this->getColumnValue('rel_object_manager');
    } // getobject_manager()
    
    /**
    * Set value of 'object_manager' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setObjectManager($value) {
      return $this->setColumnValue('rel_object_manager', $value);
    } // setmanager() 
 
    /**
    * Return value of 'order' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getOrder() {
      return $this->getColumnValue('order');
    } // getorder()
    
    /**
    * Set value of 'order' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setOrder($value) {
      return $this->setColumnValue('order', $value);
    } // setorder() 
     
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return ObjectHandins
    */
    function manager() {
      if(!($this->manager instanceof ObjectHandins )) $this->manager =  ObjectHandins::instance();
      return $this->manager;
    } // manager
  
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
    
  } // BaseObjectHandin

?>