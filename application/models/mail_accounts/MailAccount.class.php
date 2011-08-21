<?php

/**
 * MailAccount class
 * Generated on Wed, 15 Mar 2006 22:57:46 +0100 by DataObject generation tool
 *
 * @author Carlos Palma <chonwil@gmail.com>
 */
class MailAccount extends BaseMailAccount {

	private $owner;
	 
	/**
	 * Gets the account owner
	 *
	 * @return User
	 */
	function getOwner()
	{
		if (is_null($this->owner)){
			$this->owner = Users::findById($this->getUserId());
		}
		return $this->owner;
	}
	 
	/**
	 * Validate before save
	 *
	 * @access public
	 * @param array $errors
	 * @return null
	 */
	function validate(&$errors) {
		if(!$this->validatePresenceOf('name')) {
			$errors[] = lang('mail account name required');
		} // if
		if(!$this->validatePresenceOf('server')) {
			$errors[] = lang('mail account server required');
		} // if
		if(!$this->validatePresenceOf('password')) {
			$errors[] = lang('mail account password required');
		} // if
		if(!$this->validatePresenceOf('email')) {
			$errors[] = lang('mail account id required');
		} // if
	} // validate

	/* Return array of all emails
	 *
	 * @access public
	 * @param void
	 * @return one or more MailContents objects
	 */
	function getMailContents() {
		return MailContents::findAll(array(
        'conditions' => '`account_id` = ' . DB::escape($this->getId()),
      'order' => '`date` DESC'
      )); // findAll
	} // getMailContents

	function getUids($folder = null) {
		$sql = "SELECT `uid` FROM `" . MailContents::instance()->getTableName() .
				"` WHERE `account_id` = ". $this->getId();
		if (!is_null($folder)) 
			$sql .= " AND `imap_folder_name` = '$folder'";
		$rows = DB::executeAll($sql);
		$uids = array();
		if (isset($rows) && is_array($rows)) {
			foreach ($rows as $r) {
				$uids[] = $r['uid'];
			}
		}
		return $uids;
	}
	
	function getMaxUID($folder = null){
		$maxUID = 0;
		$sql = "SELECT `uid` FROM `" . MailContents::instance()->getTableName() .
				"` WHERE `account_id` = ". $this->getId();
		if (!is_null($folder)) 
			$sql .= " AND `imap_folder_name` = '$folder'";
		$sql .= " AND id = (SELECT max(id) FROM `". MailContents::instance()->getTableName() .
				"` WHERE `account_id` = ". $this->getId(). " AND `state` < 2)";
		$rows = DB::executeAll($sql);
		if (isset($rows)){
			$maxUID = $rows[0]['uid'];
		}
		return $maxUID;
	}
	
	// ---------------------------------------------------
	//  URLs
	// ---------------------------------------------------

	/**
	 * Return view mail URL of this mail
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getViewUrl() {
		return get_url('mail', 'view_account', $this->getId());
	} // getAccountUrl

	/**
	 * Return edit mail URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getEditUrl() {
		return get_url('mail', 'edit_account', $this->getId());
	} // getEditUrl

	/**
	 * Return add mail URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getAddUrl() {
		return get_url('mail', 'add_account');
	} // getEditUrl

	/**
	 * Return delete mail URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getDeleteUrl() {
		return get_url('mail', 'delete_account', $this->getId());
	} // getDeleteUrl


	// ---------------------------------------------------
	//  Permissions
	// ---------------------------------------------------

	/**
	 * Returns true if $user can access this account
	 *
	 * @param User $user
	 * @return boolean
	 */
	function canView(User $user) {
		$accountUser = MailAccountUsers::getByAccountAndUser($this, $user);
		return $accountUser instanceof MailAccountUser;
	} // canView

	/**
	 * Check if specific user can add accounts
	 *
	 * @access public
	 * @param User $user
	 * @param Project $project
	 * @return booelean
	 */
	function canAdd(User $user) {
		return can_add_mail_accounts($user);
	} // canAdd

	/**
	 * Check if specific user can edit this account
	 *
	 * @access public
	 * @param User $user
	 * @return boolean
	 */
	function canEdit(User $user) {
		return $this->canView($user);
	} // canEdit

	/**
	 * Check if specific user can delete this account
	 *
	 * @access public
	 * @param User $user
	 * @return boolean
	 */
	function canDelete(User $user) {
		$accountUser = MailAccountUsers::getByAccountAndUser($this, $user);
		return $accountUser instanceof MailAccountUser && $accountUser->getCanEdit() || can_manage_security(logged_user());
	} // canDelete

	// ---------------------------------------------------
	//  ApplicationDataObject implementation
	// ---------------------------------------------------

	/**
	 * Return object name
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getObjectName() {
		return $this->getName();
	} // getObjectName

	/**
	 * Return object type name
	 *
	 * @param void
	 * @return string
	 */
	function getObjectTypeName() {
		return 'mail account';
	} // getObjectTypeName

	/**
	 * Return object URl
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getObjectUrl() {
		return $this->getEditUrl();
	} // getObjectUrl

	
	function delete($deleteMails = false){
		MailAccountUsers::deleteByAccount($this);
		if ($deleteMails) {
			session_commit();
			
			LinkedObjects::delete(array("(`object_id` IN (SELECT `id` FROM `".TABLE_PREFIX."mail_contents` WHERE `account_id` = " . DB::escape($this->getId()).") and `object_manager` = 'MailContents') 
				or (`rel_object_id` IN (SELECT `id` FROM `".TABLE_PREFIX."mail_contents` WHERE `account_id` = " . DB::escape($this->getId()).") and `rel_object_manager` = 'MailContents')")); 
			
      		SearchableObjects::delete(array("`rel_object_manager` = 'MailContents' AND `rel_object_id` IN (SELECT `id` FROM `".TABLE_PREFIX."mail_contents` WHERE `account_id` = " . DB::escape($this->getId()).") "));
			ReadObjects::delete("`rel_object_manager` = 'MailContents' AND `rel_object_id` IN (SELECT `id` FROM `".TABLE_PREFIX."mail_contents` WHERE `account_id` = " . DB::escape($this->getId()).") ");
			
			$account_emails = MailContents::findAll(array('conditions' => '`account_id` = ' . DB::escape($this->getId()), 'include_trashed' => true));
			foreach ($account_emails as $email) {
				$email->delete();
			}
			//MailContents::delete('`account_id` = ' . DB::escape($this->getId()));
		}
		if ($this->getIsImap()) {
			MailAccountImapFolders::delete('account_id = ' . $this->getId());
		}
		parent::delete();
	}
	
	/**
	 * Return the workspace associated to this mail account
	 * @return Project
	 */
	function getWorkspace() {
		return Projects::findById($this->getWorkspaceId());
	}
	
	
	/**
	 * Return smtp username that should be used according to smtp_use_Auth settings  
	 *
	 * @return unknown
	 */
	function smtpUsername(){
		$auth_level = $this->getSmtpUseAuth(); // 0 is no authentication, 1 is same as pop, 2 is use smtp specific settings
		if ($auth_level  == 0)	{
			return null;
		}
		else if ($auth_level == 1)	{
			return $this->getEmail();
		}
		else if ($auth_level == 2)	{
			return $this->getSmtpUsername();
		}
	}
	
	/**
	 * Return smtp password that should be used according to smtp_use_Auth settings  
	 *
	 * @return unknown
	 */
	function smtpPassword(){
		$auth_level = $this->getSmtpUseAuth(); // 0 is no authentication, 1 is same as pop, 2 is use smtp specific settings
		if ($auth_level  == 0)	{
			return null;
		}
		else if ($auth_level == 1)	{
			return $this->getPassword();
		}
		else if ($auth_level == 2)	{
			return $this->getSmtpPassword();
		}
	}
	
	function getFromName() {
		$user_settings = MailAccountUsers::getByAccountAndUser($this, logged_user());
		if ($user_settings instanceof MailAccountUser && $user_settings->getSenderName()) {
			return $user_settings->getSenderName();
		} else if ($this->getSenderName()) {
			return $this->getSenderName();
		} else {
			return logged_user()->getDisplayName();
		}
	}
}
?>