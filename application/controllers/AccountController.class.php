<?php

/**
 * User account controller with all the parts related to it (profile update, private messages etc)
 *
 * @version 1.0
 * @author Ilija Studen <ilija.studen@gmail.com>, Marcos Saiz <marcos.saiz@fengoffice.com>
 */
class AccountController extends ApplicationController {

	/**
	 * Construct the AccountController
	 *
	 * @access public
	 * @param void
	 * @return AccountController
	 */
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website');
		if (array_var($_GET, 'current') != 'administration') {
			ajx_set_panel("account");
		}
	} // __construct

	/**
	 * Show account index page
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function index() {
		$this->setHelp("account");
		$this->setTemplate("card");
		$this->setControllerName("user");
		tpl_assign('user', logged_user());
		tpl_assign('user_id', get_id());
		
		ajx_set_no_toolbar(true);		
		$pids = null;
		if (active_project() instanceof Project) {
			$pids = active_project()->getAllSubWorkspacesQuery();
		}
		$logs = ApplicationLogs::getOverallLogs(false, false, $pids, 15, 0, get_id());

		tpl_assign('logs', $logs);
	} // index

	/**
	 * Edit logged user profile. 
	 * Called with different POST format from "administration/users/edit user profile " and from "profile/edit my profile" 
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function edit_profile() {
		$user = Users::findById(get_id());
		if(!($user instanceof User)) {
			flash_error(lang('user dnx'));
			ajx_current("empty");
			return;
		} // if

		$company = $user->getCompany();
		if(!($company instanceof Company)) {
			flash_error(lang('company dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$user->canUpdateProfile(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$redirect_to = array_var($_GET, 'redirect_to');
		if((trim($redirect_to)) == '' || !is_valid_url($redirect_to)) {
			$redirect_to = $user->getCardUrl();
		} // if
		tpl_assign('redirect_to', null);

		$user_data = array_var($_POST, 'user');
		if(!is_array($user_data)) {
			$user_data = array(
	          'username'      => $user->getUsername(),
	          'email'         => $user->getEmail(),
	          'display_name'  => $user->getDisplayName(),
	          'title'         => $user->getTitle(),
	          'timezone'      => $user->getTimezone(),
	          'auto_assign'   => $user->getAutoAssign(),
	          'company_id'    => $user->getCompanyId(),
	          'is_admin'      => $user->isAdministrator(),
			  'type'          => $user->getType(),
			); // array

		} // if

		tpl_assign('user', $user);
		tpl_assign('company', $company);
		tpl_assign('user_data', $user_data);
		tpl_assign('billing_categories', BillingCategories::findAll());

		if(is_array(array_var($_POST, 'user'))) {
			if(array_var($user_data,'company_id') && !(Companies::findById(array_var($user_data,'company_id')) instanceof Company )){
				ajx_current("empty");
				flash_error(lang("company dnx"));
				return ;
			}
			try {
				DB::beginWork();
				
				$user->setDisplayName(array_var($user_data,'display_name'));
				$user->setEmail(array_var($user_data,'email'));
				$user->setType(array_var($user_data,'type'));
				$user->setTimezone(array_var($user_data,'timezone'));
				$user->setTitle(array_var($user_data,'title'));
				$user->setUpdatedOn(DateTimeValueLib::now());
				if (logged_user()->isAdministrator()){
					if ($user->getId() != 1) { // System admin cannot change it's company
						$user->setCompanyId(array_var($user_data,'company_id'));
					}
					$user->setDefaultBillingId(array_var($user_data,'default_billing_id'));
					$user->setUsername(array_var($user_data,'username'));
					$project = Projects::findById(array_var($user_data, 'personal_project_id'));
					if ($project instanceof Project && $user->getPersonalProjectId() != $project->getId()) {
						$user->setPersonalProjectId($project->getId());
						$project_user = ProjectUsers::findById(array('project_id' => $project->getId(), 'user_id' => $user->getId()));
						if (!$project_user) {
							$project_user = new ProjectUser();
							$project_user->setUserId($user->getId());
							$project_user->setProjectId($project->getId());
						}
						$project_user->setAllPermissions(true);
						$project_user->save();
					}
				}

				$user->save();
							
				$autotimezone = array_var($user_data, 'autodetect_time_zone', null);
				if ($autotimezone !== null) {
					set_user_config_option('autodetect_time_zone', $autotimezone, $user->getId());
				}
				
				$object_controller = new ObjectController();
			  	$object_controller->add_custom_properties($user);
			  
				if ($user->getId() != 1) { //System admin cannot change its own admin status
					if ($user->getType() == 'admin') {
						if ($user->getCompanyId() != owner_company()->getId()) {
							// external users can't be admins => set as Normal 
							$user->setType('normal');
							$user->setAsAdministrator(false);
						} else {
							$user->setAsAdministrator(true);
						}
					} else {
						$user->setAsAdministrator(false);
					}
				}
								
				if (GlobalCache::isAvailable()) {	
					GlobalCache::delete('logged_user_'.$user->getId());
				}
				
				DB::commit();

				flash_success(lang('success update profile'));
				ajx_current("back");
				ajx_add("overview-panel", "reload");
			} catch(Exception $e) {
				DB::rollback();
				ajx_current("empty");
				flash_error($e->getMessage());
			} // try
		} // if
	} // edit_profile

	/**
	 * Edit logged user password
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function edit_password() {
		$user = Users::findById(get_id());
		if(!($user instanceof User)) {
			flash_error(lang('user dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$user->canUpdateProfile(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$redirect_to = array_var($_GET, 'redirect_to');
		if((trim($redirect_to)) == '' || !is_valid_url($redirect_to)) {
			$redirect_to = $user->getCardUrl();
		} // if
		tpl_assign('redirect_to', null);

		$password_data = array_var($_POST, 'password');
		tpl_assign('user', $user);

		if(is_array($password_data)) {
			$old_password = array_var($password_data, 'old_password');
			$new_password = array_var($password_data, 'new_password');
			$new_password_again = array_var($password_data, 'new_password_again');

			try {
				if(!logged_user()->isAdministrator()) {
					if(trim($old_password) == '') {
						throw new Error(lang('old password required'));
					} // if
					if(!$user->isValidPassword($old_password)) {
						throw new Error(lang('invalid old password'));
					} // if
				} // if

				if(trim($new_password) == '') {
					throw new Error(lang('password value required'));
				} // if
				if($new_password <> $new_password_again) {
					throw new Error(lang('passwords dont match'));
				} // if
				
				$user_password = new UserPassword();
				$user_password->setUserId(get_id());
				$user_password->password_temp = $new_password;
				$user_password->setPasswordDate(DateTimeValueLib::now());
				$user_password->setPassword(cp_encrypt($new_password, $user_password->getPasswordDate()->getTimestamp()));
				$user_password->save();

				$user->setPassword($new_password);
				$user->setUpdatedOn(DateTimeValueLib::now());
				$user->save();
											
				if (GlobalCache::isAvailable()) {	
					GlobalCache::delete('logged_user_'.$user->getId());
				}
				
				if ($user->getId() == logged_user()->getId()) {
					CompanyWebsite::instance()->logUserIn($user, Cookie::getValue("remember", 0));
				}

				ApplicationLogs::createLog($user, null, ApplicationLogs::ACTION_EDIT);
				flash_success(lang('success edit user', $user->getUsername()));
				ajx_current("back");

			} catch(Exception $e) {
				DB::rollback();
				ajx_current("empty");
				flash_error($e->getMessage());
			} // try
		} // if
	} // edit_password

	/**
	 * Show update permissions page
	 *
	 * @param void
	 * @return null
	 */
	function update_permissions() {
		$user = Users::findById(get_id());
		if(!($user instanceof User)) {
			flash_error(lang('user dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$user->canUpdatePermissions(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$company = $user->getCompany();
		if(!($company instanceof Company)) {
			flash_error(lang('company dnx'));
			ajx_current("empty");
			return;
		} // if

		if (logged_user()->isAdministrator()) {
			$projects = Projects::getAll();
		} else {
			$projects = null;
		}

		$permissions = ProjectUsers::getNameTextArray();

		$redirect_to = array_var($_GET, 'redirect_to');
		if((trim($redirect_to)) == '' || !is_valid_url($redirect_to)) {
			$redirect_to = $user->getCardUrl();
		} // if
		
		$user_data = array_var($_POST, 'user');
		if(!is_array($user_data)) {
			$user_data = array(
	          'can_edit_company_data' => $user->getCanEditCompanyData(),
	          'can_manage_security' => $user->getCanManageSecurity(),
	          'can_manage_workspaces' => $user->getCanManageWorkspaces(),
	          'can_manage_configuration' => $user->getCanManageConfiguration(),
	          'can_manage_contacts' => $user->getCanManageContacts(),
			  'can_manage_templates' => $user->getCanManageTemplates(),
			  'can_manage_reports' => $user->getCanManageReports(),
			  'can_manage_time' => $user->getCanManageTime(),
			  'can_add_mail_accounts' => $user->getCanAddMailAccounts(),
			); // array
			
			Hook::fire('add_user_permissions', $user, $user_data);
		} // if

		tpl_assign('user_data', $user_data);
		tpl_assign('user', $user);
		tpl_assign('company', $company);
		tpl_assign('projects', $projects);
		tpl_assign('permissions', $permissions);
		tpl_assign('redirect_to', $redirect_to);

		if(array_var($_POST, 'submitted') == 'submitted') {
			$user_data = array_var($_POST, 'user');
			if (!is_array($user_data)) $user_data = array();
			try{
				DB::beginWork();
				
				$permissionsString = array_var($_POST,'permissions');
				if ($permissionsString && $permissionsString != ''){
					$permissions = json_decode($permissionsString);
				}
			  	if(is_array($permissions) && count($permissions) > 0) {
			  		//Clear old modified permissions
			  		$ids = array();
			  		foreach($permissions as $perm) {
			  			$ids[] = $perm->wsid;
			  		}
			  			
			  		ProjectUsers::clearByUser($user, implode(',', $ids));
			  		
			  		//Add new permissions
			  		//TODO - Make batch update of these permissions
			  		foreach ($permissions as $perm) {
			  			if (ProjectUser::hasAnyPermissions($perm->pr,$perm->pc)) {			  				
				  			$relation = new ProjectUser();
					  		$relation->setProjectId($perm->wsid);
					  		$relation->setUserId($user->getId());
				  			
					  		$relation->setCheckboxPermissions($perm->pc, $user->isGuest() ? false : true);
					  		$relation->setRadioPermissions($perm->pr, $user->isGuest() ? false : true);
					  		$relation->save();
			  			} //endif
			  			//else if the user has no permissions at all, he is not a project_user. ProjectUser is not created
			  		} //end foreach
				} // if
				
				$user->setCanEditCompanyData(false);
				$user->setCanManageSecurity(false);
				$user->setCanManageConfiguration(false);
				$user->setCanManageWorkspaces(false);
				$user->setCanManageContacts(false);
				$user->setCanManageTemplates(false);
				$user->setCanManageReports(false);
				$user->setCanManageTime(false);
				$user->setCanAddMailAccounts(false);
				$other_permissions = array();
				Hook::fire('add_user_permissions', $user, $other_permissions);
				foreach ($other_permissions as $k => $v) {
					$user->setColumnValue($k, false);
				}
				$user->setFromAttributes($user_data);
				$user->setUpdatedOn(DateTimeValueLib::now());
				$user->save();
				DB::commit();
				
				// Delete old global cache so the new permissions are taken into account next time
				if (GlobalCache::isAvailable()) {
					GlobalCache::delete('logged_user_'.$user->getId());
				}				
	
				flash_success(lang('success user permissions updated'));
				ajx_current("back");
			} catch(Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			}
		} // if
	} // update_permissions

	/**
	 * Edit logged user avatar
	 *
	 * @param void
	 * @return null
	 */
	function edit_avatar() {
		$user = Users::findById(get_id());
		if (!($user instanceof User)) {
			flash_error(lang('user dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$user->canUpdateProfile(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$redirect_to = array_var($_GET, 'redirect_to');
		if((trim($redirect_to)) == '' || !is_valid_url($redirect_to)) {
			$redirect_to = $user->getUpdateAvatarUrl();
		} // if
		tpl_assign('redirect_to', $redirect_to);

		$avatar = array_var($_FILES, 'new_avatar');
		tpl_assign('user', $user);

		if(is_array($avatar)) {
			try {
				if(!isset($avatar['name']) || !isset($avatar['type']) || !isset($avatar['size']) || !isset($avatar['tmp_name']) || !is_readable($avatar['tmp_name'])) {
					throw new InvalidUploadError($avatar, lang('error upload file'));
				} // if

				$valid_types = array('image/jpg', 'image/jpeg', 'image/pjpeg', 'image/gif', 'image/png','image/x-png');
				$max_width   = config_option('max_avatar_width', 50);
				$max_height  = config_option('max_avatar_height', 50);

				if(!in_array($avatar['type'], $valid_types) || !($image = getimagesize($avatar['tmp_name']))) {
					throw new InvalidUploadError($avatar, lang('invalid upload type', 'JPG, GIF, PNG'));
				} // if

				$old_file = $user->getAvatarPath();
				DB::beginWork();

				$user->setUpdatedOn(DateTimeValueLib::now());
				if(!$user->setAvatar($avatar['tmp_name'], $avatar['type'], $max_width, $max_height)) {
					throw new InvalidUploadError($avatar, lang('error edit avatar'));
				} // if

				ApplicationLogs::createLog($user, null, ApplicationLogs::ACTION_EDIT);
				DB::commit();

				if(is_file($old_file)) {
					@unlink($old_file);
				} // if

				flash_success(lang('success edit avatar'));
				ajx_current("back");
			} catch(Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			} // try
		} // if
	} // edit_avatar

	/**
	 * Delete avatar
	 *
	 * @param void
	 * @return null
	 */
	function delete_avatar() {
		$user = Users::findById(get_id());
		if(!($user instanceof User)) {
			flash_error(lang('user dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$user->canUpdateProfile(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$redirect_to = array_var($_GET, 'redirect_to');
		if((trim($redirect_to)) == '' || !is_valid_url($redirect_to)) {
			$redirect_to = $user->getUpdateAvatarUrl();
		} // if
		tpl_assign('redirect_to', $redirect_to);

		if(!$user->hasAvatar()) {
			flash_error(lang('avatar dnx'));
			ajx_current("empty");
			return;
		} // if

		try {
			DB::beginWork();
			$user->setUpdatedOn(DateTimeValueLib::now());
			$user->deleteAvatar();
			$user->save();
			ApplicationLogs::createLog($user, null, ApplicationLogs::ACTION_EDIT);

			DB::commit();

			flash_success(lang('success delete avatar'));
			ajx_current("back");
		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error delete avatar'));
			ajx_current("empty");
		} // try

	} // delete_avatar
	
	function update_user_preference(){
		ajx_current("empty");
		$option_name = array_var($_GET,'name');
		$option_value = array_var($_GET,'value');
		if($option_name != ''){
			try{
				DB::beginWork();
				set_user_config_option($option_name,$option_value,logged_user()->getId());
				DB::commit();
			} catch(Exception $e){
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			}
		}
		
	}

} // AccountController

?>