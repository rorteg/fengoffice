<?php

  /**
  * BaseObjectSubscription class
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  abstract class BaseObjectSubscription extends DataObject {
  
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
    } // setObjectManager() 
    
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
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return ObjectSubscriptions 
    */
    function manager() {
      if(!($this->manager instanceof ObjectSubscriptions)) $this->manager = ObjectSubscriptions::instance();
      return $this->manager;
    } // manager
  
  } // BaseObjectSubscription 

?>