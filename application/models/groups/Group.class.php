<?php

/**
 * group class
 *
 * @author Marcos Saiz <marcos.saiz@gmail.com>
 */


class Group extends BaseGroup {

	const CONST_MINIMUM_GROUP_ID = 10000000;
	const CONST_ADMIN_GROUP_ID = 10000000;

	/**
	 * REturn true if the actual group is the Administrators group (which cannot be deleted)
	 *
	 * @return boolean
	 */
	function isAdministratorGroup(){
		return Group::CONST_ADMIN_GROUP_ID == $this->getId();
	} //isAdministratorGroup
	/**
	* Return array of all group members
	*
	* @access public
	* @param void
	* @return array
	*/
	function getUsers($group_id) {
		return GroupUsers::getUsersByGroup($group_id);
	} // getUsers

	/**
	 * Return number of group users
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function countUsers() {
		return GroupUsers::count('`group_id` = ' . DB::escape($this->getId()));
	} // countUsers

	function setAllPermissions($value) {
		$this->setCanEditCompanyData($value);
		$this->setCanManageConfiguration($value);
		$this->setCanManageSecurity($value);
		$this->setCanManageWorkspaces($value);
		$this->setCanManageContacts($value);
		$this->setCanManageTemplates($value);
		$this->setCanManageReports($value);
		$this->setCanManageTime($value);
		$this->setCanAddMailAccounts($value);
	}

	function getAllPermissions($user_permissions = null) {
		if (is_null($user_permissions) && !$this->isNew())
		$user_permissions = ProjectUsers::findAll(array('conditions' => 'user_id = '. $this->getId()) );
		$result = array();
		if (is_array($user_permissions)){
			foreach ($user_permissions as $perm){
				$chkArray = array();
				$chkArray[0] = ($perm->getCanAssignToOwners() ? 1 : 0);
				$chkArray[1] = ($perm->getCanAssignToOther() ? 1 : 0);
					
				$radioArray = array();
				$radioArray[0] = ($perm->getCanWriteMessages() ? 2 : ($perm->getCanReadMessages()? 1 : 0));
				$radioArray[1] = ($perm->getCanWriteTasks() ? 2 : ($perm->getCanReadTasks()? 1 : 0));
				$radioArray[2] = ($perm->getCanWriteMilestones() ? 2 : ($perm->getCanReadMilestones()? 1 : 0));
				$radioArray[3] = ($perm->getCanWriteMails() ? 2 : ($perm->getCanReadMails()? 1 : 0));
				$radioArray[4] = ($perm->getCanWriteComments() ? 2 : ($perm->getCanReadComments()? 1 : 0));
				$radioArray[5] = ($perm->getCanWriteContacts() ? 2 : ($perm->getCanReadContacts()? 1 : 0));
				$radioArray[6] = ($perm->getCanWriteWeblinks() ? 2 : ($perm->getCanReadWeblinks()? 1 : 0));
				$radioArray[7] = ($perm->getCanWriteFiles() ? 2 : ($perm->getCanReadFiles()? 1 : 0));
				$radioArray[8] = ($perm->getCanWriteEvents() ? 2 : ($perm->getCanReadEvents()? 1 : 0));
					
				$result[] = array("wsid" => $perm->getProjectId(), "pc" => $chkArray, "pr" => $radioArray);
			}
		}
			
		return $result;
	}

	function getProjectPermission(Project $project, $permission, $default = false) {
		static $valid_permissions = null;
		if(is_null($valid_permissions)) {
			$valid_permissions = ProjectUsers::getPermissionColumns();
		} // if

		if(!in_array($permission, $valid_permissions)) {
			return $default;
		} // if

		$project_user = ProjectUsers::findById(array(
			'project_id' => $project->getId(),
			'user_id' => $this->getId()
		)); // findById


		if(!($project_user instanceof ProjectUser)) {
			return $default;
		} // if
		$getter = 'get' . Inflector::camelize($permission);
		return $project_user->$getter();
	} // getProjectPermission


	/**
	* Check if specific user can update this group
	*
	* @access public
	* @param User $user
	* @return boolean
	*/
	function canEdit(User $user) {
		return $user->isAccountOwner() || $user->isAdministrator() || $user->isMemberOf(owner_company());
	} // canEdit

	/**
	 * Check if specific user can delete this group
	 *
	 * @access public
	 * @param User $user
	 * @return boolean
	 */
	function canDelete(User $user) {
		return ($user->isAccountOwner() || $user->isAdministrator()) && !$this->isAdministratorGroup() ;
	} // canDelete

	/**
	 * Returns true if specific user can add group
	 *
	 * @access public
	 * @param User $user
	 * @return boolean
	 */
	function canAdd(User $user, Company $company) {
		return ($user->isAdministrator() || $user->isMemberOf($company));
			
		//return $user->isAccountOwner() || $user->isAdministrator($this);
	} // canAddClient

	/**
	 * Check if this user can add new account to this group
	 *
	 * @access public
	 * @param User $user
	 * @return boolean
	 */
	function canAddUser(User $user) {
		return User::canEdit($user);
	} // canAddUser

	/**
	* Return view group URL
	*
	* @access public
	* @param void
	* @return string
	*/
	function getViewUrl() {
		return get_url('group', 'view_group', array( 'id' => $this->getId()));
	} // getViewUrl

	/**
	 * Edit group url
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function getEditUrl() {
		return get_url('group', 'edit_group', array('id' => $this->getId()));
	} // getEditUrl

	/**
	 * Return delete group URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getDeleteGroupUrl() {
		return get_url('group', 'delete', array('id' => $this->getId()));
	} // getDeleteClientUrl

	/**
	* Return add user URL
	*
	* @access public
	* @param void
	* @return string
	*/
	function getAddUserUrl() {
		return get_url('group', 'add_user', array('id' => $this->getId()));
	} // getAddUserUrl

	/**
	 * Delete this group and all related data
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 * @throws Error
	 */
	function delete() {
		if ($this->isAdministratorGroup()  ) {
			throw new Error(lang('error delete group'));
			return false;
		} // if
		ProjectUsers::clearByUser($this);
		return parent::delete();
	} // delete

	function isGuest() {
		return false;
	}

} // group

?>