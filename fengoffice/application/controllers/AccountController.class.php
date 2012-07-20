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
		ajx_set_no_toolbar(true);
		
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
		ajx_set_panel("");
		$user = Contacts::findById(get_id());
		if(!($user instanceof Contact && $user->isUser()) || $user->getDisabled()) {
			flash_error(lang('user dnx'));
			ajx_current("empty");
			return;
		} // if

		
		$company = $user->getCompany();
		/*if(!($company instanceof Contact)) {
			flash_error(lang('company dnx'));
			ajx_current("empty");
			return;
		} // if
		*/

		if(!$user->canUpdateProfile(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$redirect_to = array_var($_GET, 'redirect_to');
		if((trim($redirect_to)) == '' || !is_valid_url($redirect_to)) {
			$redirect_to = $user->getCardUserUrl();
		} // if
		tpl_assign('redirect_to', null);

		$user_data = array_var($_POST, 'user');
		if(!is_array($user_data)) {
			$user_data = array(
	          'username'      => $user->getUsername(),
	          'email'         => $user->getEmailAddress(),
	          'display_name'  => $user->getObjectName(),
	          'timezone'      => $user->getTimezone(),
	          'company_id'    => $user->getCompanyId(),
	          'is_admin'      => $user->isAdministrator(),
			  'type'          => $user->getUserType(),
			); // array

		} // if

		tpl_assign('user', $user);
		tpl_assign('company', $company);
		tpl_assign('user_data', $user_data);
		tpl_assign('billing_categories', BillingCategories::findAll());
		// Permission Groups
		$groups = PermissionGroups::getNonPersonalSameLevelPermissionsGroups('`parent_id`,`id` ASC');
		tpl_assign('groups', $groups);
		$roles= SystemPermissions::getAllRolesPermissions();
		tpl_assign('roles', $roles);
		$tabs= TabPanelPermissions::getAllRolesModules();
		tpl_assign('tabs_allowed', $tabs);
		// Submit user
		if(is_array(array_var($_POST, 'user'))) {
			$company_id = array_var($user_data,'company_id');
			if($company_id && !(Contacts::findById($company_id) instanceof Contact)){
				ajx_current("empty");
				flash_error(lang("company dnx"));
				return ;
			}
			try {
				DB::beginWork();

				$user->setUserType(array_var($user_data,'type'));
				$user->setTimezone(array_var($user_data,'timezone'));
				$user->setDefaultBillingId(array_var($user_data,'default_billing_id'));
				$user->setUpdatedOn(DateTimeValueLib::now());
				
				if (logged_user()->isAdministrator()){
					//if ($user->getId() != 2) { // System admin cannot change it's company (from Feng 2.0 onwards administrador has id = 2)
					//	$user->setCompanyId(array_var($user_data,'company_id'));
					//}
					
					$user->setUsername(array_var($user_data,'username'));
				} else {
					$user->setCompanyId(array_var($user_data,'company_id'));
				}
				if(!isset($_POST['sys_perm'])){
					$rol_permissions=SystemPermissions::getRolePermissions(array_var($user_data, 'type'));
					$_POST['sys_perm']=array();
					$not_rol_permissions=SystemPermissions::getNotRolePermissions(array_var($user_data, 'type'));
					
					foreach ($not_rol_permissions as $npr){
						$_POST['sys_perm'][$npr]=0;
					}
					foreach($rol_permissions as $pr){
						$_POST['sys_perm'][$pr]=1;
					}
					
					
				}
				if(!isset($_POST['mod_perm'])){
					$tabs_permissions=TabPanelPermissions::getRoleModules(array_var($user_data, 'type'));
					$_POST['mod_perm']=array();
					foreach($tabs_permissions as $pr){
						$_POST['mod_perm'][$pr]=1;
					}
				}
				$user->save();
				
				$autotimezone = array_var($user_data, 'autodetect_time_zone', null);
				if ($autotimezone !== null) {
					set_user_config_option('autodetect_time_zone', $autotimezone, $user->getId());
				}
				
				$object_controller = new ObjectController();
			  	$object_controller->add_custom_properties($user);
			  
				$ret = null;
				Hook::fire('after_edit_profile', $user, $ret);
				$pg_id = $user->getPermissionGroupId();
				save_permissions($pg_id, $user->isGuest());
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
		$user = Contacts::findById(get_id());
		if(!($user instanceof Contact && $user->isUser()) || $user->getDisabled()) {
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
			$redirect_to = $user->getCardUserUrl();
		} // if
		tpl_assign('redirect_to', null);

		$password_data = array_var($_POST, 'password');
		tpl_assign('user', $user);

		if(is_array($password_data)) {
			$old_password = array_var($password_data, 'old_password');
			$new_password = array_var($password_data, 'new_password');
			$new_password_again = array_var($password_data, 'new_password_again');

			try {
				if(!logged_user()->isAdminGroup()) {
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
				
				$user_password = new ContactPassword();
				$user_password->setContactId(get_id());
				$user_password->password_temp = $new_password;
				$user_password->setPasswordDate(DateTimeValueLib::now());
				$user_password->setPassword(cp_encrypt($new_password, $user_password->getPasswordDate()->getTimestamp()));
				$user_password->save();

				$user->setPassword($new_password);
				$user->setUpdatedOn(DateTimeValueLib::now());
				$user->save();
				
				if ($user->getId() == logged_user()->getId()) {
					CompanyWebsite::instance()->logUserIn($user, Cookie::getValue("remember", 0));
				}

				ApplicationLogs::createLog($user, ApplicationLogs::ACTION_EDIT);
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
		$user = Contacts::findById(get_id());
		if(!($user instanceof Contact && $user->isUser()) || $user->getDisabled()) {
			flash_error(lang('user dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$user->canUpdatePermissions(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$redirect_to = array_var($_GET, 'redirect_to');
		if((trim($redirect_to)) == '' || !is_valid_url($redirect_to)) {
			$redirect_to = $user->getCardUserUrl();
		} // if
		
		$sys_permissions_data = array_var($_POST, 'sys_perm');
		
		if(!is_array($sys_permissions_data)) {
			$pg_id = $user->getPermissionGroupId();
			$parameters = permission_form_parameters($pg_id);
			
			// Module Permissions
			$module_permissions = TabPanelPermissions::findAll(array("conditions" => "`permission_group_id` = $pg_id"));
			$module_permissions_info = array();
			foreach ($module_permissions as $mp) {
				$module_permissions_info[$mp->getTabPanelId()] = 1;
			}
			$all_modules = TabPanels::findAll(array("conditions" => "`enabled` = 1", "order" => "ordering"));
			$all_modules_info = array();
			foreach ($all_modules as $module) {
				$all_modules_info[] = array('id' => $module->getId(), 'name' => lang($module->getTitle()), 'ot' => $module->getObjectTypeId());
			}
			
			// System Permissions
			$system_permissions = SystemPermissions::findById($pg_id);
			
			tpl_assign('module_permissions_info', $module_permissions_info);
			tpl_assign('all_modules_info', $all_modules_info);
			tpl_assign('system_permissions', $system_permissions);
			
			tpl_assign('permission_parameters', $parameters);
			
			$more_permissions = array();
			Hook::fire('add_user_permissions', $pg_id, $more_permissions);
			tpl_assign('more_permissions', $more_permissions);
			
			
			// Permission Groups
			$groups = PermissionGroups::getNonPersonalSameLevelPermissionsGroups('`parent_id`,`id` ASC');
			tpl_assign('groups', $groups);
			$roles= SystemPermissions::getAllRolesPermissions();
			tpl_assign('roles', $roles);
			$tabs= TabPanelPermissions::getAllRolesModules();
			tpl_assign('tabs_allowed', $tabs);
			tpl_assign('guest_groups', PermissionGroups::instance()->getGuestPermissionGroups());
		}
		
		
		tpl_assign('user', $user);
		tpl_assign('redirect_to', $redirect_to);

		if(array_var($_POST, 'submitted') == 'submitted') {
			$user_data = array_var($_POST, 'user');
			if (!is_array($user_data)) $user_data = array();
			try{
				DB::beginWork();
				$pg_id = $user->getPermissionGroupId();
				
				$user->setUserType(array_var($user_data, 'type'));
				$user->save();
				save_permissions($pg_id, $user->isGuest());
				
				DB::commit();
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
		$user = Contacts::findById(get_id());
		if (!($user instanceof Contact && $user->isUser()) || $user->getDisabled()) {
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

				ApplicationLogs::createLog($user, ApplicationLogs::ACTION_EDIT);
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
		$user = Contacts::findById(get_id());
		if(!($user instanceof Contact && $user->isUser()) || $user->getDisabled()) {
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
			ApplicationLogs::createLog($user, ApplicationLogs::ACTION_EDIT);

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
	
	function disable() {
		$user = Contacts::findById(get_id());
		if (!($user instanceof Contact && $user->isUser())) {
			flash_error(lang('user dnx'));
			ajx_current("empty");
			return;
		}
		
		if (!$user->canDelete(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		
		try {
			DB::beginWork();
			$user->disable(false);
			ApplicationLogs::createLog($user, ApplicationLogs::ACTION_TRASH);
			$ret = null ; 
			Hook::fire("user_disabled", $user, $ret );
			DB::commit();
			
			if(array_var($_GET,'current')=="administration") {
				ajx_current("reload");
			}else{
				ajx_current("empty");
			}
			
			flash_success('success disable user');
			
		} catch (Exception $e) {
			flash_error($e->getMessage());
			DB::rollback();
			ajx_current("empty");
		}
	}
	
	
	function delete_user() {
		$user = Contacts::findById(get_id());
		if (!($user instanceof Contact && $user->isUser())) {
			flash_error(lang('user dnx'));
			ajx_current("empty");
			return;
		}
		
		if (!$user->canDelete(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		
		try {
			DB::beginWork();
			$user->disable();
			ApplicationLogs::createLog($user, ApplicationLogs::ACTION_TRASH);
			$ret = null ; 
			Hook::fire("user_disabled", $user, $ret );
			DB::commit();
			flash_success('success delete user');
			
			if(array_var($_GET,'current')=="administration") {
				ajx_current("reload");
			}else{
				ajx_current("empty");
			}
			
		} catch (Exception $e) {
			flash_error($e->getMessage());
			DB::rollback();
			ajx_current("empty");
		}
	}
	
	function restore_user() {
		$user = Contacts::findById(get_id());
		if (!($user instanceof Contact && $user->isUser())) {
			flash_error(lang('user dnx'));
			ajx_current("empty");
			return;
		}
		
		if (!$user->canDelete(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		
		try {
			DB::beginWork();
			$user->setDisabled(false);
			$user->unarchive();
			ApplicationLogs::createLog($user, ApplicationLogs::ACTION_UNTRASH);
			$ret = null ; 
			Hook::fire("user_restored", $user, $ret );			
			DB::commit();
			flash_success('success delete user');
			ajx_current("reload");
			
		} catch (Exception $e) {
			flash_error($e->getMessage());
			DB::rollback();
			ajx_current("empty");
		}
	}
	
	
	function set_timezone() {
		$tz = array_var($_REQUEST, 'tz');
		if ($tz != logged_user()->getTimezone()) {
			logged_user()->setTimezone($tz);
			logged_user()->save();
		}
		ajx_current("empty");
	}

} // AccountController

?>