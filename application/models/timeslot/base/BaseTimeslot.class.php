<?php

  /**
  * BaseTimeslot class
  *
  * @author Carlos Palma <chonwil@gmail.com>
  */
  abstract class BaseTimeslot extends ApplicationDataObject {
  
  	protected $objectTypeIdentifier = 'ts';
  
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
    * Return value of 'start_time' field
    *
    * @access public
    * @param void
    * @return DateTimeValue 
    */
    function getStartTime() {
      return $this->getColumnValue('start_time');
    } // getStartTime()
    
    /**
    * Set value of 'start_time' field
    *
    * @access public   
    * @param DateTimeValue $value
    * @return boolean
    */
    function setStartTime($value) {
      return $this->setColumnValue('start_time', $value);
    } // setStartTime() 
    
    /**
    * Return value of 'end_time' field
    *
    * @access public
    * @param void
    * @return DateTimeValue 
    */
    function getEndTime() {
      return $this->getColumnValue('end_time');
    } // getEndTime()
    
    /**
    * Set value of 'end_time' field
    *
    * @access public   
    * @param DateTimeValue $value
    * @return boolean
    */
    function setEndTime($value) {
      return $this->setColumnValue('end_time', $value);
    } // setEndTime() 
    
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
    * Return value of 'paused_on' field
    *
    * @access public
    * @param void
    * @return DateTimeValue 
    */
    function getPausedOn() {
      return $this->getColumnValue('paused_on');
    } // getPausedOn()
    
    /**
    * Set value of 'paused_on' field
    *
    * @access public   
    * @param DateTimeValue $value
    * @return boolean
    */
    function setPausedOn($value) {
      return $this->setColumnValue('paused_on', $value);
    } // setPausedOn() 
    
    /**
    * Return value of 'subtract' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getSubtract() {
      return $this->getColumnValue('subtract');
    } // getUserId()
    
    /**
    * Set value of 'subtract' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setSubtract($value) {
      return $this->setColumnValue('subtract', $value);
    } // setUserId() 
    
    
    /**
    * Return value of 'fixed_billing' field
    *
    * @access public
    * @param void
    * @return float 
    */
    function getFixedBilling() {
      return $this->getColumnValue('fixed_billing');
    } // getFixedBilling()
    
    /**
    * Set value of 'fixed_billing' field
    *
    * @access public   
    * @param float $value
    * @return boolean
    */
    function setFixedBilling($value) {
      return $this->setColumnValue('fixed_billing', $value);
    } // setFixedBilling() 
    
    
    /**
    * Return value of 'hourly_billing' field
    *
    * @access public
    * @param void
    * @return float 
    */
    function getHourlyBilling() {
      return $this->getColumnValue('hourly_billing');
    } // getHourlyBilling()
    
    /**
    * Set value of 'hourly_billing' field
    *
    * @access public   
    * @param float $value
    * @return boolean
    */
    function setHourlyBilling($value) {
      return $this->setColumnValue('hourly_billing', $value);
    } // setHourlyBilling() 
    
    /**
    * Return value of 'is_fixed_billing' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getIsFixedBilling() {
      return $this->getColumnValue('is_fixed_billing');
    } // getIsFixedBilling()
    
    /**
    * Set value of 'is_fixed_billing' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setIsFixedBilling($value) {
      return $this->setColumnValue('is_fixed_billing', $value);
    } // setIsFixedBilling() 
    
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
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return Timeslots 
    */
    function manager() {
      if(!($this->manager instanceof Timeslots)) $this->manager = Timeslots::instance();
      return $this->manager;
    } // manager
  
  } // BaseComment 

?>