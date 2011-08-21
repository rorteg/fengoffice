<?php

  /**
  * BaseUserPassword class
  *
  * @author Pablo Kamil <pablokam@gmail.com>
  */
  abstract class BaseUserPassword extends DataObject {
  	
  	var $password_temp = '';
  
  
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
    * Return value of 'password' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getPassword() {
      return $this->getColumnValue('password');
    } // getPassword()
    
    /**
    * Set value of 'password' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setPassword($value) {
      return $this->setColumnValue('password', $value);
    } // setPassword() 
    
       
    /**
    * Return value of 'password_date' field
    *
    * @access public
    * @param void
    * @return DateTimeValue 
    */
    function getPasswordDate() {
      return $this->getColumnValue('password_date');
    } // getPasswordDate()
    
    /**
    * Set value of 'password_date' field
    *
    * @access public   
    * @param DateTimeValue $value
    * @return boolean
    */
    function setPasswordDate($value) {
      return $this->setColumnValue('password_date', $value);
    } // setPasswordDate()     
 
    
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return UserPasswords 
    */
    function manager() {
      if(!($this->manager instanceof UserPasswords)) $this->manager = UserPasswords::instance();
      return $this->manager;
    } // manager
    
  
  } // UserPassword 

?>