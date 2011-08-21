<?php

  /**
  * BaseGroup class
  *
  * @author Marcos Saiz <marcos.saiz@gmail.com>
  */
  abstract class BaseGroup extends ApplicationDataObject {
  
  	protected $objectTypeIdentifier = 'gp';
  
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
    * @param void
    * @return integer 
    */
    function setUpdatedById() {
      return $this->setColumnValue('updated_by_id');
    } // setUpdatedById()
    
    /**
    * Return value of 'can_edit_company_data' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function  getCanEditCompanyData() {
      return $this->getColumnValue('can_edit_company_data');
    } //  getCanEditCompanyData()   
    
    /**
    * Set value of 'can_edit_company_data' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setCanEditCompanyData($value) {
      return $this->setColumnValue('can_edit_company_data', $value);
    } //  setCanEditCompanyData() 
     
    /**
    * Return value of 'can_manage_Security' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getCanManageSecurity() {
      return $this->getColumnValue('can_manage_security');
    } // getCanManageSecurity()
    
    /**
    * Set value of 'can_manage_Security' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function setCanManageSecurity($value) {
      return $this->setColumnValue('can_manage_security', $value);
    } // getCanManageSecurity()
    
    /**
    * Return value of 'can_manage_Workspaces' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getCanManageWorkspaces() {
      return $this->getColumnValue('can_manage_workspaces');
    } // getCanManageWorkspaces()
    
    /**
    * Set value of 'can_manage_Workspaces' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setCanManageWorkspaces($value) {
      return $this->setColumnValue('can_manage_workspaces', $value);
    } // setCanManageWorkspaces() 
    
    /**
    * Return value of 'can_manage_Configuration' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getCanManageConfiguration() {
      return $this->getColumnValue('can_manage_configuration');
    } // getCanManageConfiguration()
    
    /**
    * Set value of 'can_manage_Configuration' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setCanManageConfiguration($value) {
      return $this->setColumnValue('can_manage_configuration', $value);
    } // setCanManageConfiguration() 
    
    /**
    * Return value of 'can_manage_time' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getCanManageTime() {
      return $this->getColumnValue('can_manage_time');
    } // getCanManageTime()
    
    /**
    * Set value of 'can_manage_time' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setCanManageTime($value) {
      return $this->setColumnValue('can_manage_time', $value);
    } // setCanManageTime() 
    
    /**
    * Return value of 'can_manage_contacts' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getCanManageContacts() {
      return $this->getColumnValue('can_manage_contacts');
    } // getCanManageContacts()
    
    /**
    * Set value of 'can_manage_contacts' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function setCanManageContacts($value) {
      return $this->setColumnValue('can_manage_contacts', $value);
    } // setCanManageContacts()
    
    /**
    * Return value of 'can_manage_templates' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getCanManageTemplates() {
      return $this->getColumnValue('can_manage_templates');
    } // getCanManageTemplates()
    
    /**
    * Set value of 'can_manage_templates' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function setCanManageTemplates($value) {
      return $this->setColumnValue('can_manage_templates', $value);
    } // setCanManageTemplates()
    
    /**
    * Return value of 'can_manage_reports' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getCanManageReports() {
      return $this->getColumnValue('can_manage_reports');
    } // getCanManageReports()
    
    /**
    * Set value of 'can_manage_reports' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function setCanManageReports($value) {
      return $this->setColumnValue('can_manage_reports', $value);
    } // setCanManageReports()

    	/**
	 * Return value of 'can_add_mail_accounts' field
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function getCanAddMailAccounts() {
		return $this->getColumnValue('can_add_mail_accounts');
	} // getCanAddMailAccounts()

	/**
	 * Set value of 'can_add_mail_accounts' field
	 *
	 * @access public
	 * @param boolean $value
	 * @return boolean
	 */
	function setCanAddMailAccounts($value) {
		return $this->setColumnValue('can_add_mail_accounts', $value);
	} // setCanAddMailAccounts()
    
    
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return Groups
    */
    function manager() {
      if(!($this->manager instanceof  Groups)) $this->manager =  Groups::instance();
      return $this->manager;
    } // manager
  
  } // BaseGroup 

?>