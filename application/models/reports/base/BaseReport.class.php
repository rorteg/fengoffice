<?php

  /**
  * BaseReport class
  *
  * @author Pablo Kamil <pablokam@gmail.com>
  */
  abstract class BaseReport extends ApplicationDataObject {
  
  	protected $objectTypeIdentifier = 're';
  
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
    * Return value of 'name' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getName() {
      return $this->getColumnValue('name');
    } // getName()
    
    /**
    * Set value of 'name' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setName($value) {
      return $this->setColumnValue('name', $value);
    } // setName() 
    
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
    * Return value of 'object_type' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getObjectType() {
      return $this->getColumnValue('object_type');
    } // getObjectType()
    
    /**
    * Set value of 'object_type' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setObjectType($value) {
      return $this->setColumnValue('object_type', $value);
    } // setObjectType() 
    
    /**
    * Return value of 'order_by' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getOrderBy() {
      return $this->getColumnValue('order_by');
    } // getOrderBy()
    
    /**
    * Set value of 'order_by' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setOrderBy($value) {
      return $this->setColumnValue('order_by', $value);
    } // setOrderBy() 
    
    /**
    * Return value of 'is_order_by_asc' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getIsOrderByAsc() {
      return $this->getColumnValue('is_order_by_asc');
    } // getIsOrderByAsc()
    
    /**
    * Set value of 'is_order_by_asc' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setIsOrderByAsc($value) {
      return $this->setColumnValue('is_order_by_asc', $value);
    } // setIsOrderByAsc() 
   
     /**
    * Return value of 'workspace' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getWorkspace() {
      return $this->getColumnValue('workspace');
    } // getId()
    
    /**
    * Set value of 'workspace' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setWorkspace($value) {
      return $this->setColumnValue('workspace', $value);
    } // setId() 
    
     /**
    * Return value of 'tags' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getTags() {
      return $this->getColumnValue('tags');
    } // getName()
    
    /**
    * Set value of 'tags' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setTags($value) {
      return $this->setColumnValue('tags', $value);
    } // setName() 
    
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return Report 
    */
    function manager() {
      if(!($this->manager instanceof Reports )) $this->manager =  Reports::instance();
      return $this->manager;
    } // manager
  
  } // BaseReport

?>