<?php

/**
 * BaseUser class
 *
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
abstract class BaseUser extends ApplicationDataObject {

	protected $objectTypeIdentifier = 'us';

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
	 * Return value of 'company_id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getCompanyId() {
		return $this->getColumnValue('company_id');
	} // getCompanyId()

	/**
	 * Set value of 'company_id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setCompanyId($value) {
		return $this->setColumnValue('company_id', $value);
	} // setCompanyId()

	/**
	 * Return value of 'username' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getUsername() {
		return $this->getColumnValue('username');
	} // getUsername()

	/**
	 * Set value of 'username' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setUsername($value) {
		return $this->setColumnValue('username', $value);
	} // setUsername()

	/**
	 * Return value of 'email' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getEmail() {
		return $this->getColumnValue('email');
	} // getEmail()

	/**
	 * Set value of 'email' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setEmail($value) {
		return $this->setColumnValue('email', $value);
	} // setEmail()

	/**
	 * Return value of 'token' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getToken() {
		return $this->getColumnValue('token');
	} // getToken()

	/**
	 * Set value of 'token' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setToken($value) {
		return $this->setColumnValue('token', $value);
	} // setToken()

	/**
	 * Return value of 'salt' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getSalt() {
		return $this->getColumnValue('salt');
	} // getSalt()

	/**
	 * Set value of 'salt' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setSalt($value) {
		return $this->setColumnValue('salt', $value);
	} // setSalt()

	/**
	 * Return value of 'twister' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getTwister() {
		return $this->getColumnValue('twister');
	} // getTwister()

	/**
	 * Set value of 'twister' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setTwister($value) {
		return $this->setColumnValue('twister', $value);
	} // setTwister()

	/**
	 * Return value of 'display_name' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getDisplayName() {
		return $this->getColumnValue('display_name');
	} // getDisplayName()

	/**
	 * Set value of 'display_name' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setDisplayName($value) {
		return $this->setColumnValue('display_name', $value);
	} // setDisplayName()

	/**
	 * Return value of 'title' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getTitle() {
		return $this->getColumnValue('title');
	} // getTitle()

	/**
	 * Set value of 'title' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setTitle($value) {
		return $this->setColumnValue('title', $value);
	} // setTitle()

	/**
	 * Return value of 'avatar_file' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getAvatarFile() {
		return $this->getColumnValue('avatar_file');
	} // getAvatarFile()

	/**
	 * Set value of 'avatar_file' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setAvatarFile($value) {
		return $this->setColumnValue('avatar_file', $value);
	} // setAvatarFile()

	/**
	 * Return value of 'timezone' field
	 *
	 * @access public
	 * @param void
	 * @return float
	 */
	function getTimezone() {
		return $this->getColumnValue('timezone');
	} // getTimezone()

	/**
	 * Set value of 'timezone' field
	 *
	 * @access public
	 * @param float $value
	 * @return boolean
	 */
	function setTimezone($value) {
		return $this->setColumnValue('timezone', $value);
	} // setTimezone()

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
	 * Return value of 'last_login' field
	 *
	 * @access public
	 * @param void
	 * @return DateTimeValue
	 */
	function getLastLogin() {
		return $this->getColumnValue('last_login');
	} // getLastLogin()

	/**
	 * Set value of 'last_login' field
	 *
	 * @access public
	 * @param DateTimeValue $value
	 * @return boolean
	 */
	function setLastLogin($value) {
		return $this->setColumnValue('last_login', $value);
	} // setLastLogin()

	/**
	 * Return value of 'last_visit' field
	 *
	 * @access public
	 * @param void
	 * @return DateTimeValue
	 */
	function getLastVisit() {
		return $this->getColumnValue('last_visit');
	} // getLastVisit()

	/**
	 * Set value of 'last_visit' field
	 *
	 * @access public
	 * @param DateTimeValue $value
	 * @return boolean
	 */
	function setLastVisit($value) {
		return $this->setColumnValue('last_visit', $value);
	} // setLastVisit()

	/**
	 * Return value of 'last_activity' field
	 *
	 * @access public
	 * @param void
	 * @return DateTimeValue
	 */
	function getLastActivity() {
		return $this->getColumnValue('last_activity');
	} // getLastActivity()

	/**
	 * Set value of 'last_activity' field
	 *
	 * @access public
	 * @param DateTimeValue $value
	 * @return boolean
	 */
	function setLastActivity($value) {
		return $this->setColumnValue('last_activity', $value);
	} // setLastActivity()
	 
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
	} // setCanManagetemplates()

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
	 * Return value of 'can_manage_reports' field
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function getCanManageReports() {
		return $this->getColumnValue('can_manage_reports');
	} // getCanManageConfiguration()

	/**
	 * Set value of 'can_manage_reports' field
	 *
	 * @access public
	 * @param boolean $value
	 * @return boolean
	 */
	function setCanManageReports($value) {
		return $this->setColumnValue('can_manage_reports', $value);
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
	 * Return value of 'auto_assign' field
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function getAutoAssign() {
		return $this->getColumnValue('auto_assign');
	} // getAutoAssign()

	/**
	 * Set value of 'auto_assign' field
	 *
	 * @access public
	 * @param boolean $value
	 * @return boolean
	 */
	function setAutoAssign($value) {
		return $this->setColumnValue('auto_assign', $value);
	} // setAutoAssign()

	/**
	 * Return the personal project's id
	 *
	 * @return integer
	 */
	function getPersonalProjectId() {
		return $this->getColumnValue('personal_project_id');
	}

	/**
	 * Set the personal project's id
	 *
	 * @param integer $value
	 */
	function setPersonalProjectId($value) {
		$this->setColumnValue('personal_project_id', $value);
	}

	/**
	 * Return value of 'default_billing_id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getDefaultBillingId() {
		return $this->getColumnValue('default_billing_id');
	} // getDefaultBillingId()

	/**
	 * Set value of 'company_id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setDefaultBillingId($value) {
		return $this->setColumnValue('default_billing_id', $value);
	} // setDefaultBillingId()

	function getType() {
		return $this->getColumnValue('type');
	}
	
	function setType($type) {
		return $this->setColumnValue('type', $type);
	}
	
	/**
	 * Return manager instance
	 *
	 * @access protected
	 * @param void
	 * @return Users
	 */
	function manager() {
		if(!($this->manager instanceof Users)) $this->manager = Users::instance();
		return $this->manager;
	} // manager

} // BaseUser

?>