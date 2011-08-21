<?php

  /**
  * BaseGroupUser class
  *
  * @author Marcos Saiz <marcos.saiz@gmail.com>
  */
  abstract class BaseGroupUser extends DataObject {
  
    // -------------------------------------------------------
    //  Access methods
    // -------------------------------------------------------
    
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
    * Return value of 'group_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getGroupId() {
      return $this->getColumnValue('group_id');
    } // getGroupId()
    
    /**
    * Set value of 'group_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setGroupId($value) {
      return $this->setColumnValue('group_id', $value);
    } // setGroupId() 
    
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
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return GroupUser 
    */
    function manager() {
      if(!($this->manager instanceof GroupUsers)) $this->manager = GroupUsers::instance();
      return $this->manager;
    } // manager
  
  } // BaseGroupUser 

?>