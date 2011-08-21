<?php

  /**
  * BaseWorkspaceBilling class
  *
  * @author Carlos Palma <chonwil@gmail.com>
  */
  abstract class BaseWorkspaceBilling extends DataObject {
  
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
    * Return value of 'billing_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getBillingId() {
      return $this->getColumnValue('billing_id');
    } // getBillingId()
    
    /**
    * Set value of 'billing_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setBillingId($value) {
      return $this->setColumnValue('billing_id', $value);
    } // setBillingId() 
    
        
    /**
    * Return value of 'value' field
    *
    * @access public
    * @param void
    * @return float 
    */
    function getValue() {
      return $this->getColumnValue('value');
    } // getValue()   
    
    /**
    * Set value of 'value' field
    *
    * @access public   
    * @param float $value
    * @return boolean
    */
    function setValue($value) {
      return $this->setColumnValue('value', $value);
    } // setValue() 
    
   
    
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
    * @return WorkspaceBillings 
    */
    function manager() {
      if(!($this->manager instanceof WorkspaceBillings)) $this->manager = WorkspaceBillings::instance();
      return $this->manager;
    } // manager
  
  } // BaseWorkspaceBilling

?>