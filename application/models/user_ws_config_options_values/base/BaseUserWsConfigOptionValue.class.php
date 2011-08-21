<?php

  /**
  * BaseConfigOption class
  *
  * @author Marcos Saiz <marcos.saiz@fengoffice.com>
  */
  abstract class BaseUserWsConfigOptionValue extends DataObject {
  
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
    function getOptionId() {
      return $this->getColumnValue('option_id');
    } // getId()
    
    /**
    * Set value of 'id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setOptionId($value) {
      return $this->setColumnValue('option_id', $value);
    } // setId() 
  
    /**
    * Return value of 'workspace_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getWorkspaceId() {
      return $this->getColumnValue('workspace_id');
    } // setWorkspaceId()
    
    /**
    * Set value of 'workspace_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setWorkspaceId($value) {
      return $this->setColumnValue('workspace_id', $value);
    } // setWorkspaceId() 
  
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
    * Return value of 'value' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getValue() {
      return $this->getColumnValue('value');
    } // getValue()
    
    /**
    * Set value of 'value' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setValue($value) {
      return $this->setColumnValue('value', $value);
    } // setValue() 
        
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return  UserWsConfigOptionValues 
    */
    function manager() {
      if(!($this->manager instanceof UserWsConfigOptionValues )) $this->manager =  UserWsConfigOptionValues::instance();
      return $this->manager;
    } // manager
  
  } // BaseConfigOption 

?>