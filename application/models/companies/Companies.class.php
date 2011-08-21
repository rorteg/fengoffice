<?php

/**
 * Companies, generated on Sat, 25 Feb 2006 17:37:12 +0100 by
 * DataObject generation tool
 *
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class Companies extends BaseCompanies {

	public static function getWorkspaceString($ids = '?') {
		return " `id` IN (SELECT `object_id` FROM `" . TABLE_PREFIX . "workspace_objects` WHERE `object_manager` = 'Companies' AND `workspace_id` IN ($ids)) ";
	}
	 
	/**
	 * Return all registered companies
	 *
	 * @param void
	 * @return array
	 */
	static function getAll() {
		return Companies::findAll(array(
        'order' => '`client_of_id`'
        )); // findAll
	} // getAll

	static function getVisibleCompanies(User $user, $additional_conditions = null){
		if (can_manage_contacts($user)){
			if ($additional_conditions) return self::findAll(array('conditions' => $additional_conditions));
			else return self::getAll();
		} else {
			return self::getCompaniesByProjects($user->getWorkspacesQuery(true), $additional_conditions);
		}
	}

	/**
	 * Return all companies that are on specific projects, determined by a CVS list of project ids.
	 *
	 * @access public
	 * @param string $projects_csv CSV list of projects
	 * @param string $additional_conditions Additional SQL conditions
	 * @param bool $include_owner Include the owner company
	 * @return array Array of Companies
	 */
	static function getCompaniesByProjects($projects_csv, $additional_conditions = null, $include_owner = true) {
		$companies = array();
		$companies_table = self::instance()->getTableName(true);
		$project_objects_table =  WorkspaceObjects::instance()->getTableName(true);

		// Restrict result only on owner company
		$ownerCond = '';
		if (!$include_owner){
			$owner_id = owner_company()->getId();
			$ownerCond = "$companies_table.`client_of_id` = '$owner_id' AND ";
		}
		$wsCond = self::getWorkspaceString($projects_csv);
		 
		$conditions = $ownerCond != '' ? "$ownerCond AND $wsCond" : $wsCond;
		if (trim($additional_conditions) <> '') $conditions .= " AND ($additional_conditions)";

		return self::findAll(array(
			'conditions' => $conditions,
			'order' => '`name`'
		));
	} // getCompaniesByProjects

	/**
	 * Return all companies that have system users
	 *
	 * @param void
	 * @return array
	 */
	static function getCompaniesWithUsers() {
		$user_table =  Users::instance()->getTableName();
		$companies_table =  Companies::instance()->getTableName();
		return Companies::findAll(array(
	        'conditions' => array("EXISTS (SELECT `id` FROM $user_table WHERE $user_table.`company_id` = $companies_table.`id` )"),
	        'order' => '`client_of_id`'
        )); // findAll
	} // getCompaniesWithUsers

	/**
	 * Return owner company
	 *
	 * @access public
	 * @param void
	 * @return Company
	 */
	static function getOwnerCompany() {
		
		$owner_company = null;
		if (GlobalCache::isAvailable()) {
			$owner_company = GlobalCache::get('owner_company', $success);
			if ($success && $owner_company instanceof Company) {
				return $owner_company;
			}
		}
		
		$owner_company = Companies::findOne(array(
        	'conditions' => array('`client_of_id` = ?', 0),
			'include_trashed' => true,
		)); // findOne
		
		if (GlobalCache::isAvailable()) {
			GlobalCache::update('owner_company', $owner_company);
		}
		
		return $owner_company;
	} // getOwnerCompany

	/**
	 * Return company clients
	 *
	 * @param Company $company
	 * @return array
	 */
	static function getCompanyClients(Company $company) {
		return Companies::findAll(array(
        	'conditions' => array('`client_of_id` = ?', $company->getId()),
        	'order' => '`name`'
        )); // array
	} // getCompanyClients


	static function getCompanyFieldNames() {
		return array('company[name]' => lang('name'),
			'company[address]' => lang('address'),
			'company[address2]' => lang('address2'),
			'company[city]' => lang('city'),
			'company[state]' => lang('state'),
			'company[zipcode]' => lang('zipcode'),
			'company[country]' => lang('country'),
			'company[phone_number]' => lang('phone'),
			'company[fax_number]' => lang('fax'),
			'company[email]' => lang('email address'),
			'company[homepage]' => lang('homepage'),
			'company[notes]' => lang('notes'),
		);
	}
	
	
	/**
	 * Returns an array of: (name, email)
	 * from companies that the logged user can access. 
	 * @return array
	 */
	function getCompanyEmailAddresses() {
		$permissions = permissions_sql_for_listings(Companies::instance(), ACCESS_LEVEL_READ, logged_user());
		$sql = "SELECT `name`, `email` FROM `" . TABLE_PREFIX . "companies` WHERE " .
			"`trashed_by_id` = 0 AND $permissions AND `email` <> ''";
		$all = DB::executeAll($sql);
		if (is_array($all)) return $all;
		return array();
	}
} // Companies

?>