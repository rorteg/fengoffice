<?php

  abstract class BaseSharedObject extends DataObject {
  
    // -------------------------------------------------------
    //  Access methods
    // -------------------------------------------------------

    /**
    * Return value of 'object_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getObjectId() {
      return $this->getColumnValue('object_id');
    } // getObjectId()
    
    /**
    * Set value of 'object_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setObjectId($value) {
      return $this->setColumnValue('object_id', $value);
    } // setObjectId() 
    
    /**
     * Return value of 'object_manager' field
     *
     * @access public
     * @param void
     * @return string
     */
    function getObjectManager() {
    	return $this->getColumnValue('object_manager');
    } // getObjectManager()

    /**
     * Set value of 'object_manager' field
     *
     * @access public
     * @param string $value
     * @return boolean
     */
    function setObjectManager($value) {
    	return $this->setColumnValue('object_manager', $value);
    } // setFolderName()
    
       /**
    * Return value of 'user_id' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getUserId() {
      return $this->getColumnValue('user_id');
    } // getCheckFolder()
    
    /**
    * Set value of 'user_id' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setUserId($value) {
      return $this->setColumnValue('user_id', $value);
    } // setCheckFolder() 

    
       /**
    * Return value of 'created_on' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getCreatedOn() {
      return $this->getColumnValue('created_on');
    } // getCreatedOn()
    
    /**
    * Set value of 'created_on' field
    *
    * @access public   
    * @param boolean $value
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
    * @param boolean $value
    * @return boolean
    */
    function setCreatedById($value) {
      return $this->setColumnValue('created_by_id', $value);
    } // setCreatedById() 
    
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return SharedObjects
    */
    function manager() {
      if(!($this->manager instanceof SharedObjects)) $this->manager = SharedObjects::instance();
      return $this->manager;
    } // manager
  
  } // BaseSharedObject

?>