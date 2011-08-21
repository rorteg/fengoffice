<?php

/**
 * Users, generated on Sat, 25 Feb 2006 17:37:12 +0100 by
 * DataObject generation tool
 *
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class Users extends BaseUsers {

	/**
	 * Return all users
	 *
	 * @param void
	 * @return array
	 */
	function getAll() {
		return self::findAll();
	} // getAll
	
	/**
	 * Returns all the users visible by the requesting user
	 *
	 * @param User $user
	 * @return array
	 */
	static function getVisibleUsers(User $user) {
		if ($user->isMemberOfOwnerCompany()){
			return self::findAll(array('order' => 'concat(`display_name`, `username`)'));
		} else {
			return $user->getCompany()->getUsers();
		}
	} // getAll

	/**
	 * Return user by username
	 *
	 * @access public
	 * @param string $username
	 * @return User
	 */
	static function getByUsername($username) {
		return self::findOne(array(
        'conditions' => array('`username` = ?', $username)
		)); // array
	} // getByUsername

	/**
	 * Return user object by email
	 *
	 * @param string $email
	 * @return User
	 */
	static function getByEmail($email) {
		return self::findOne(array(
        'conditions' => array('`email` = ?', $email)
		)); // findOne
	} // getByEmail

	/**
	 * Return all users that was active in past $active_in minutes (defautl is 15 minutes)
	 *
	 * @access public
	 * @param integer $active_in
	 * @return array
	 */
	static function getWhoIsOnline($active_in = 15) {
		if((integer) $active_in < 1) $active_in = 15;

		$datetime = DateTimeValueLib::now();
		$datetime->advance(-1 * $active_in * 60);
		return Users::findAll(array(
        'conditions' => array('`last_activity` > ?', $datetime)
		)); // findAll
	} // getWhoIsOnline

	/**
	 * Return user by token
	 *
	 * @param string $token
	 * @return User
	 */
	static function getByToken($token) {
		return self::findOne(array(
        'conditions' => array('`token` = ?', $token)
		)); // findOne
	} // getByToken

	/**
	 * Check if specific token already exists in database
	 *
	 * @param string $token
	 * @return boolean
	 */
	static function tokenExists($token) {
		return self::count(array('`token` = ?', $token)) > 0;
	} // tokenExists

	/**
	 * Return users grouped by company
	 *
	 * @param void
	 * @return array
	 */
	static function getGroupedByCompany() {
		$companies = Companies::getCompaniesWithUsers();
		if(!is_array($companies) || !count($companies)) {
			return null;
		} // if

		$result = array();
		foreach($companies as $company) {
			$users = $company->getUsers();
			if(is_array($users) && count($users)) {
				$result[$company->getName()] = array(
            'details' => $company,
            'users' => $users,
				); // array
			} // if
		} // foreach

		return count($result) ? $result : null;
	} // getGroupedByCompany

	/**
	 * Return users grouped by company, from the project IDs
	 *
	 * @param void
	 * @return array
	 */
	static function getGroupedByCompanyFromProjectIds($project_ids) {
		// Get user ids for project and subprojects
		$project_users_table = ProjectUsers::instance()->getTableName(true);
		$sql = "SELECT DISTINCT user_id FROM $project_users_table WHERE (`project_id` in ( $project_ids ) ) ";
		$rows = DB::executeAll($sql);
		$user_csvs = '';
		if(is_array($rows)) {
			foreach($rows as $row) {
				$user_csvs .=',' . $row['user_id'];
			} // foreach
		} // if
		else
		return null;
		if($user_csvs ) //remove first comma
		$user_csvs = substr($user_csvs,1);
		$users = Users::findAll(array(
			 'conditions' => array('`id` in (' . $user_csvs  . ')'),
			 'order' => 'display_name'
		  )); // findAll
		  $result = array();
		  if($users){
		  	foreach($users as $user) {
		  		$comp_id = $user->getCompanyId();
		  		if(array_var($result, $comp_id, null)){
		  			$result[$comp_id][] = $user;
		  		}
		  		else {
		  			// the first one
		  			$result[$comp_id] = array($user);
		  		}
		  	} // foreach
		  }
		  return count($result) ? $result : null;
	} // getGroupedByCompany

	function getContactManagers() {
		$users = Users::findAll(array(
			'conditions' => array('`can_manage_contacts` = 1'),
			'order' => 'display_name',
		));
		if (!is_array($users)) $users = array();
		return $users;
	}
	
	/**
	 * It returns all the users that have the recived project as
	 * personal project. used to detect whether to delete or not a
	 * workspace.
	 * @param int $project_id 
	 * @return array
	 */
	function GetByPersonalProject($project_id)
	{
		if ($project_id != ""){
			$users = Users::findAll(array(
				'conditions' => array('`personal_project_id` = ' . $project_id)
			));
		}
		if (!is_array($users)) $users = array();
		return $users;
	}//getByPersonalProject
	
	function getExternalUsers() {
		return Users::findAll(array('conditions' => '`company_id` IN (SELECT `id` FROM `' . TABLE_PREFIX . 'companies` WHERE `client_of_id` <> 0)'));
	}
	
	function countExternalUsers() {
		return Users::count('`company_id` IN (SELECT `id` FROM `' . TABLE_PREFIX . 'companies` WHERE `client_of_id` <> 0)');
	}
	
	function getUserDisplayName($user_id) {
		$user = Users::findById($user_id);
		if ($user) {
			return $user->getDisplayName();
		} else {
			$log = ApplicationLogs::findOne(array('conditions' => "`rel_object_id` = '$user_id' AND `rel_object_manager` = 'Users' AND `action` = 'add'"));
			if ($log) return $log->getObjectName();
			else return lang('n/a');
		}
	}
	
	function findByIds($ids) {
		$esc = DB::escape($ids);
		return self::findAll(array('conditions' => "`id` IN ($esc)"));
	}
} // Users

?>