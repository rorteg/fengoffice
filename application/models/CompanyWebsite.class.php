<?php

/**
 * Company website class
 *
 * @version 1.0
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
final class CompanyWebsite {

	/** Name of the cookie / session var where we save session_id **/
	const USER_SESSION_ID_VAR = 'user_session_id';

	/**
	 * Owner company
	 *
	 * @var Company
	 */
	private $company;

	/**
	 * Logged user
	 *
	 * @var User
	 */
	private $logged_user;

	/**
	 * Selected project
	 *
	 * @var Project
	 */
	private $selected_project;

	/**
	 * Init company website environment
	 *
	 * @access public
	 * @param void
	 * @return null
	 * @throws Error
	 */
	function init() {
		if(isset($this) && ($this instanceof CompanyWebsite)) {
			$this->initCompany();
			$this->initLoggedUser();
			$this->initActiveProject();
		} else {
			CompanyWebsite::instance()->init();
		} // if
	} // init

	/**
	 * Init company based on subdomain
	 *
	 * @access public
	 * @param string
	 * @return null
	 * @throws Error
	 */
	private function initCompany() {
		$company = Companies::getOwnerCompany();
		if(!($company instanceof Company)) {
			throw new OwnerCompanyDnxError();
		} // if

		// check the cache if available
		$owner = null;
		if (GlobalCache::isAvailable()) {
			$owner = GlobalCache::get('owner_company_creator', $success);
		}
		if (!($owner instanceof User)) {
			$owner = $company->getCreatedBy();
			// Update cache if available
			if ($owner instanceof User && GlobalCache::isAvailable()) {
				GlobalCache::update('owner_company_creator', $owner);
			}
		}
		if(!($owner instanceof User)) {
			throw new AdministratorDnxError();
		} // if

		$this->setCompany($company);
	} // initCompany

	/**
	 * Init active project, if we have active_project $_GET var
	 *
	 * @access public
	 * @param void
	 * @return null
	 * @throws Error
	 */
	private function initActiveProject() {
		$project_id = array_var($_GET, 'active_project');
		if (empty($project_id)) {
			$this->setProject(null);
		} else {
			$user = logged_user();
			if (!($user instanceof User)) return;
			$do_find = true;
			// check the cache for the option value
			if (GlobalCache::isAvailable() && GlobalCache::key_exists('active_ws_'.$user->getId())) {					
				$active_ws = GlobalCache::get('active_ws_'.$user->getId(), $success);							
				if ($success && $active_ws != null) $do_find = ($active_ws->getId() != $project_id);				
			}
			if ($do_find) {
				$project = Projects::findById($project_id);
				if (GlobalCache::isAvailable()) {
					GlobalCache::update('active_ws_'.$user->getId(), $project);
				}
			} else $project = $active_ws;
			$this->setProject($project);
		} // if
	} // initActiveProject

	/**
	 * This function will use session ID from session or cookie and if presend log user
	 * with that ID. If not it will simply break.
	 *
	 * When this function uses session ID from cookie the whole process will be treated
	 * as new login and users last login time will be set to current time.
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	private function initLoggedUser() {
		$user_id       = Cookie::getValue('id');
		$twisted_token = Cookie::getValue('token');
		$cn = Cookie::getValue('cn');
		$remember      = (boolean) Cookie::getValue('remember', false);

		if(empty($user_id) || empty($twisted_token)) {
			return false; // we don't have a user
		} // if

		// check the cache if available
		$user = null;
		if (GlobalCache::isAvailable()) {
			$user = GlobalCache::get('logged_user_'.$user_id, $success);
		}
		if (!($user instanceof User)) {
			$user = Users::findById($user_id);
			// Update cache if available
			if ($user instanceof User && GlobalCache::isAvailable()) {
				GlobalCache::update('logged_user_'.$user->getId(), $user);
			}
		}
		if(!($user instanceof User)) {
			return false; // failed to find user
		} // if
		if(!$user->isValidToken($twisted_token)) {
			return false; // failed to validate token
		} // if
		if(!($cn == md5(array_var($_SERVER, 'HTTP_USER_AGENT', "")))) {
			return false; // failed to check user agent
		} // if
		
		$last_act = $user->getLastActivity();
		if ($last_act) {
			$session_expires = $last_act->advance(SESSION_LIFETIME, false);
		}
		if(!$last_act || $session_expires!=null && DateTimeValueLib::now()->getTimestamp() < $session_expires->getTimestamp()) {
			$this->setLoggedUser($user, $remember, true);
		} else {
			$this->logUserIn($user, $remember);
		} // if
		 
		//$this->selected_project = $user->getPersonalProject();
	} // initLoggedUser

	// ---------------------------------------------------
	//  Utils
	// ---------------------------------------------------

	/**
	 * Log user in
	 *
	 * @access public
	 * @param User $user
	 * @param boolean $remember
	 * @return null
	 */
	function logUserIn(User $user, $remember = false) {
		$user->setLastLogin(DateTimeValueLib::now());

		if(is_null($user->getLastActivity())) {
			$user->setLastVisit(DateTimeValueLib::now());
		} else {
			$user->setLastVisit($user->getLastActivity());
		} // if

		$this->setLoggedUser($user, $remember, true);
	} // logUserIn

	/**
	 * Log out user
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function logUserOut() {
		$this->logged_user = null;
		Cookie::unsetValue('id');
		Cookie::unsetValue('token');
		Cookie::unsetValue('remember');
	} // logUserOut

	// ---------------------------------------------------
	//  Getters and setters
	// ---------------------------------------------------

	/**
	 * Get company
	 *
	 * @access public
	 * @param null
	 * @return Company
	 */
	function getCompany() {
		return $this->company;
	} // getCompany

	/**
	 * Set company value
	 *
	 * @access public
	 * @param Company $value
	 * @return null
	 */
	function setCompany(Company $value) {
		$this->company = $value;
	} // setCompany

	/**
	 * Get logged_user
	 *
	 * @access public
	 * @param null
	 * @return User
	 */
	function getLoggedUser() {
		return $this->logged_user;
	} // getLoggedUser

	/**
	 * Set logged_user value
	 *
	 * @access public
	 * @param User $value
	 * @param boolean $remember Remember this user for 2 weeks (configurable)
	 * @param DateTimeValue $set_last_activity_time Set last activity time. This property is turned off in case of feed
	 *   login for instance
	 * @return null
	 * @throws DBQueryError
	 */
	function setLoggedUser(User $user, $remember = false, $set_last_activity_time = true, $set_cookies = true) {
		if($set_last_activity_time) {
			$last_activity_mod_timestamp = array_var($_SESSION, 'last_activity_mod_timestamp', null);
			if (!$last_activity_mod_timestamp || $last_activity_mod_timestamp < time() - 60 * 10) {
				
				$user->setLastActivity(DateTimeValueLib::now());
				
				// Disable updating user info
				$old_updated_on = $user->getUpdatedOn();
				$user->setUpdatedOn(DateTimeValueLib::now()); 
				$user->setUpdatedOn($old_updated_on);
				
				$user->save();
				$_SESSION['last_activity_mod_timestamp'] = time();
			}
		} // if

		if ($set_cookies) {
			$expiration = $remember ? REMEMBER_LOGIN_LIFETIME : SESSION_LIFETIME;
	
			Cookie::setValue('id', $user->getId(), $expiration);
			Cookie::setValue('token', $user->getTwistedToken(), $expiration);
			Cookie::setValue('cn', md5(array_var($_SERVER, 'HTTP_USER_AGENT', "")), $expiration);
			if($remember) {
				Cookie::setValue('remember', 1, $expiration);
			} else {
				Cookie::unsetValue('remember');
			} // if
		}

		$this->logged_user = $user;
	} // setLoggedUser

	/**
	 * Get project
	 *
	 * @access public
	 * @param null
	 * @return Project
	 */
	function getProject() {
		return $this->selected_project;
	} // getProject

	/**
	 * Set project value
	 *
	 * @access public
	 * @param Project $value
	 * @return null
	 */
	function setProject($value) {
		if(is_null($value) || ($value instanceof Project)) $this->selected_project = $value;
	} // setProject

	/**
	 * Return single CompanyWebsite instance
	 *
	 * @access public
	 * @param void
	 * @return CompanyWebsite
	 */
	static function instance() {
		static $instance;
		if(!($instance instanceof CompanyWebsite)) {
			$instance = new CompanyWebsite();
		} // if
		return $instance;
	} // instance

} // CompanyWebsite

?>