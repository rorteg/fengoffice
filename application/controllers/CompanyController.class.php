<?php

/**
 * Company controller
 *
 * @version 1.0
 * @author Ilija Studen <ilija.studen@gmail.com> , Marcos Saiz <marcos.saiz@fengoffice.com>
 */
class CompanyController extends ApplicationController {
	
	/**
	 * Construct the CompanyController
	 *
	 * @param void
	 * @return CompanyController
	 */
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website');
	} // __construct

	/**
	 * Show company card page
	 *
	 * @param void
	 * @return null
	 */
	function card() {
		$this->setTemplate("view_company");
		$company = Companies::findById(get_id());
		if(!($company instanceof Company)) {
			flash_error(lang('company dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!logged_user()->canSeeCompany($company)) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		ajx_set_no_toolbar(true);
		ajx_extra_data(array("title" => $company->getName(), 'icon'=>'ico-company'));
		tpl_assign('company', $company);
		
		ApplicationReadLogs::createLog($company, $company->getWorkspaces(), ApplicationReadLogs::ACTION_READ);
	} // card

	/**
	 * View specific company
	 *
	 * @param void
	 * @return null
	 */
	function view_client() {
		$this->redirectTo('company','card', array('id' => get_id()));
	} // view_client

	/**
	 * Edit owner company
	 *
	 * @param void
	 * @return null
	 */
	function edit() {
//		$this->setTemplate('add_company');
//
//		if(!logged_user()->isAdministrator(owner_company())) {
//			flash_error(lang('no access permissions'));
//			ajx_current("empty");
//			return;
//		} // if
//
//		// Owner company
//		$company = owner_company();
//
//		$company_data = array_var($_POST, 'company');
//		if(!is_array($company_data)) {
//			$company_data = array(
//          'name' => $company->getName(),
//          'timezone' => $company->getTimezone(),
//          'email' => $company->getEmail(),
//          'homepage' => $company->getHomepage(),
//          'address' => $company->getAddress(),
//          'address2' => $company->getAddress2(),
//          'city' => $company->getCity(),
//          'state' => $company->getState(),
//          'zipcode' => $company->getZipcode(),
//          'country' => $company->getCountry(),
//          'phone_number' => $company->getPhoneNumber(),
//          'fax_number' => $company->getFaxNumber()
//			); // array
//		} // if
//
//		tpl_assign('company', $company);
//		tpl_assign('company_data', $company_data);
//
//		if(is_array(array_var($_POST, 'company'))) {
//			$company->setFromAttributes($company_data);
//			$company->setClientOfId(0);
//			$company->setHomepage(array_var($company_data, 'homepage'));
//
//			try {
//				DB::beginWork();
//				$company->save();
//				ApplicationLogs::createLog($company, null, ApplicationLogs::ACTION_EDIT);
//				DB::commit();
//
//				flash_success(lang('success edit company', $company->getName()));
//				ajx_current("back");
//
//			} catch(Exception $e) {
//				DB::rollback();
//				ajx_current("empty");
//				flash_error($e->getMessage());
//			} // try
//		} // if

	} // edit

	/**
	 * Add client
	 *
	 * @param void
	 * @return null
	 */
	function add_client() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$this->setTemplate('add_company');

		if(!Company::canAdd(logged_user(),active_or_personal_project())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$company = new Company();
		$company_data = array_var($_POST, 'company');
		if(!is_array($company_data)) {
			$company_data = array(
				'timezone' => logged_user()->getTimezone(),
			); // array
		} // if
		tpl_assign('company', $company);
		tpl_assign('company_data', $company_data);

		if (is_array(array_var($_POST, 'company'))) {
			$company->setFromAttributes($company_data);
			$company->setClientOfId(owner_company()->getId());

			try {

				DB::beginWork();
				$company->save();
				$company->setTagsFromCSV(array_var($company_data, 'tags'));

				$object_controller = new ObjectController();
				$object_controller->add_to_workspaces($company, !can_manage_contacts(logged_user()));
			    $object_controller->link_to_new_object($company);
				$object_controller->add_subscribers($company);
				$object_controller->add_custom_properties($company);
				
				ApplicationLogs::createLog($company, $company->getWorkspaces(), ApplicationLogs::ACTION_ADD);
					
//				ApplicationLogs::createLog($company, null, ApplicationLogs::ACTION_ADD);
				DB::commit();

				flash_success(lang('success add client', $company->getName()));
				evt_add("company added", array("id" => $company->getId(), "name" => $company->getName()));
				ajx_current("back");
			} catch(Exception $e) {
				DB::rollback();
				ajx_current("empty");
				flash_error($e->getMessage());
			} // try
		} // if
	} // add_client

	/**
	 * Edit client
	 *
	 * @param void
	 * @return null
	 */
	function edit_client() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$this->setTemplate('add_company');

		$company = Companies::findById(get_id());
		
		if(!$company->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		if(!($company instanceof Company)) {
			flash_error(lang('client dnx'));
			ajx_current("empty");
			return;
		} // if

		$company_data = array_var($_POST, 'company');
		if(!is_array($company_data)) {
			$tag_names = $company->getTagNames();
			$company_data = array(
	          'name' => $company->getName(),
  			  'tags' => is_array($tag_names) ? implode(', ', $tag_names) : '',
	          'timezone' => $company->getTimezone(),
	          'email' => $company->getEmail(),
	          'homepage' => $company->getHomepage(),
	          'address' => $company->getAddress(),
	          'address2' => $company->getAddress2(),
	          'city' => $company->getCity(),
	          'state' => $company->getState(),
	          'zipcode' => $company->getZipcode(),
	          'country' => $company->getCountry(),
	          'phone_number' => $company->getPhoneNumber(),
	          'fax_number' => $company->getFaxNumber(),
			  'notes' => $company->getNotes(),
			); // array
		} // if

		tpl_assign('company', $company);
		tpl_assign('company_data', $company_data);

		if(is_array(array_var($_POST, 'company'))) {
			$company->setFromAttributes($company_data);
			$is_owner_company = false;
			if (owner_company()->getId() == $company->getId()) {
				$company->setClientOfId(0);
				$is_owner_company = true;
			} else {
				$company->setClientOfId(owner_company()->getId());
			}
			$company->setHomepage(array_var($company_data, 'homepage'));

			try {
				DB::beginWork();
				$company->save();
				$company->setTagsFromCSV(array_var($company_data, 'tags'));
				$object_controller = new ObjectController();
				$object_controller->add_to_workspaces($company, !can_manage_contacts(logged_user()));
			    $object_controller->link_to_new_object($company);
				$object_controller->add_subscribers($company);
				$object_controller->add_custom_properties($company);
				
				ApplicationLogs::createLog($company, $company->getWorkspaces(), ApplicationLogs::ACTION_EDIT);
				DB::commit();

				// Update global cache
				if ($is_owner_company && GlobalCache::isAvailable()) {
					GlobalCache::update('owner_company', $company);
				}
				
				flash_success(lang('success edit client', $company->getName()));
				ajx_current("back");

			} catch(Exception $e) {
				DB::rollback();
				ajx_current("empty");
				flash_error($e->getMessage());
			} // try
		} // if
	} // edit_client

	/**
	 * Delete client
	 *
	 * @param void
	 * @return null
	 */
	function delete_client() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$company = Companies::findById(get_id());
		if(!($company instanceof Company)) {
			flash_error(lang('client dnx'));
			ajx_current("empty");
			return;
		} // if
		if (!$company->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		try {
			DB::beginWork();
			$company->trash();
			$ws = $company->getWorkspaces();
			ApplicationLogs::createLog($company, $ws, ApplicationLogs::ACTION_TRASH);
			DB::commit();

			flash_success(lang('success delete client', $company->getName()));
			ajx_current("back");
		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error delete client'));
			ajx_current("empty");
		} // try
	} // delete_client

	/**
	 * Update company permissions
	 *
	 * @param void
	 * @return null
	 */
	function update_permissions() {
		if(!logged_user()->isAdministrator(owner_company())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$company = Companies::findById(get_id());
		if(!($company instanceof Company)) {
			flash_error(lang('company dnx'));
			ajx_current("empty");
			return;
		} // if

		if($company->isOwner()) {
			flash_error(lang('error owner company has all permissions'));
			ajx_current("empty");
			return;
		} // if

		$projects = Projects::getAll(Projects::ORDER_BY_NAME);
		if(!is_array($projects) || !count($projects)) {
			flash_error(lang('no projects in db'));
			ajx_current("empty");
			return;
		} // if

		tpl_assign('projects', $projects);
		tpl_assign('company', $company);

		if(array_var($_POST, 'submitted') == 'submitted') {
			$counter = 0;
			$logged_user = logged_user(); // reuse...

			ProjectCompanies::delete('company_id = ' . $company->getId());
			$wsids = array_var($_POST, 'ws_ids', '');
			$selected = Projects::findByCSVIds($wsids);
			$counter = 0;
			foreach ($selected as $ws) {
				$pc = new ProjectCompany();
				$pc->setCompanyId($company->getId());
				$pc->setProjectId($ws->getId());
				$pc->save();
				$counter++;
			}

			flash_success(lang('success update company permissions', $counter));
			ajx_current("back");
		} // if
	} // update_permissions

	/**
	 * Show and process edit company logo form
	 *
	 * @param void
	 * @return null
	 */
	function edit_logo() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$company = Companies::findById(get_id());
		if(!($company instanceof Company)) {
			flash_error(lang('company dnx'));
			ajx_current("empty");
			return;
		} // if
		if (!$company->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if)

		tpl_assign('company', $company);

		$logo = array_var($_FILES, 'new_logo');
		if(is_array($logo)) {
			try {
				if(!isset($logo['name']) || !isset($logo['type']) || !isset($logo['size']) || !isset($logo['tmp_name']) || !is_readable($logo['tmp_name'])) {
					throw new InvalidUploadError($logo, lang('error upload file'));
				} // if

				$valid_types = array('image/jpg', 'image/jpeg', 'image/pjpeg', 'image/gif', 'image/png','image/x-png');
				$max_width   = config_option('max_logo_width', 50);
				$max_height  = config_option('max_logo_height', 50);

				if(!in_array($logo['type'], $valid_types) || !($image = getimagesize($logo['tmp_name']))) {
					throw new InvalidUploadError($logo, lang('invalid upload type', 'JPG, GIF, PNG'));
				} // if

				$old_file = $company->getLogoPath();

				DB::beginWork();

				if(!$company->setLogo($logo['tmp_name'], $logo['type'], $max_width, $max_height, true)) {
					throw new InvalidUploadError($avatar, lang('error edit company logo'));
				} // if

				ApplicationLogs::createLog($company, $company->getWorkspaces(), ApplicationLogs::ACTION_EDIT);

				DB::commit();

				if(is_file($old_file)) {
					@unlink($old_file);
				} // uf

				flash_success(lang('success edit company logo'));
				ajx_current("back");
			} catch(Exception $e) {
				ajx_current("empty");
				DB::rollback();
				flash_error($e->getMessage());
			} // try
		} // if
	} // edit_logo

	/**
	 * Delete company logo
	 *
	 * @param void
	 * @return null
	 */
	function delete_logo() {
		if(!logged_user()->isAdministrator(owner_company())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$company = Companies::findById(get_id());
		if(!($company instanceof Company)) {
			flash_error(lang('company dnx'));
			ajx_current("empty");
			return;
		} // if

		try {
			DB::beginWork();
			$company->deleteLogo();
			$company->save();
			ApplicationLogs::createLog($company, $company->getWorkspaces(), ApplicationLogs::ACTION_EDIT);
			DB::commit();

			flash_success(lang('success delete company logo'));
			ajx_current("back");
		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error delete company logo'));
			ajx_current("empty");
		} // try
	} // delete_logo

	/**
	 * Hide welcome info message
	 *
	 * @param void
	 * @return null
	 */
	function hide_welcome_info() {
		if(!logged_user()->isAdministrator(owner_company())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		try {
			owner_company()->setHideWelcomeInfo(true);
			owner_company()->save();

			flash_success(lang('success hide welcome info'));
			ajx_current("reload");
		} catch(Exception $e) {
			flash_error(lang('error hide welcome info'));
			ajx_current("empty");
		} // try

	} // hide_welcome_info

	function check_company_name(){
		ajx_current("empty");
		$name = array_var($_GET, 'name');
		$company = Companies::findOne(array('conditions' => 'UPPER(name) = ' . strtoupper($name)));
		
		if ($company){
			ajx_extra_data(array(
				"id" => $company->getId(),
				"name" => $company->getName()
			));
		} else {
			ajx_extra_data(array(
				"id" => 0,
				"name" => $name
			));
		}
	}

	function get_company_data(){
		ajx_current("empty");
		$id = array_var($_GET, 'id');
		$company = Companies::findById($id);
		
		if ($company){
			ajx_extra_data(array(
				"id" => $company->getId(),
				"address" => $company->getAddress(),
				"state" => $company->getState(),
				"city" => $company->getCity(),
				"country" => $company->getCountry(),
				"zipcode" => $company->getZipcode(),
				"webpage" => $company->getHomepage(),
				"phoneNumber" => $company->getPhoneNumber(),
				"faxNumber" => $company->getFaxNumber()
			));
		} else {
			ajx_extra_data(array(
				"id" => 0
			));
		}
	}
	
	function search(){
		ajx_current('empty');
		if (!can_manage_contacts(logged_user())) {
			flash_error(lang("no access permissions"));
			return;
		}
		
		$search_for = array_var($_POST,'search_for',false);
		if ($search_for){
			$search_results = SearchableObjects::searchByType($search_for, null, 'Companies', true, 50);
			$companies = $search_results[0];
			if ($companies && count($companies) > 0){
				$result = array();
				foreach ($companies as $companyResult){
					$company = $companyResult['object'];
					$result[] = array(
						'name' => $company->getName(),
						'id' => $company->getId(),
						'phone' => $company->getPhoneNumber(),
						'email' => $company->getEmail(),
					);
				}
				ajx_extra_data(array("results" => $result));
			}
		}
	}
	
} // CompanyController

?>