<?php

  /**
  * BaseProjectContact class
  *
  * @author Carlos Palma <chonwil@gmail.com>
  */
  abstract class BaseProjectContact extends ProjectDataObject {
  
  	protected $objectTypeIdentifier = 'ro';
  
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
    * Return value of 'contact_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getContactId() {
      return $this->getColumnValue('contact_id');
    } // getContactId()
    
    /**
    * Set value of 'contact_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setContactId($value) {
      return $this->setColumnValue('contact_id', $value);
    } // setContactId() 
    
    /**
    * Return value of 'role' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getRole() {
      return $this->getColumnValue('role');
    } // getRole()
    
    /**
    * Set value of 'role' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setRole($value) {
      return $this->setColumnValue('role', $value);
    } // setRole() 
    
    
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return ProjectContacts
    */
    function manager() {
      if(!($this->manager instanceof ProjectContacts)) $this->manager = ProjectContacts::instance();
      return $this->manager;
    } // manager
  
  } // BaseProjectContact 

?>