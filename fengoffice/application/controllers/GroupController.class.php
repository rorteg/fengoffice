<?php

/**
 * Group controller
 *
 * @version 1.0
 * @author Marcos Saiz <marcos.saiz@gmail.com>
 */
class GroupController extends ApplicationController {

	/**
	 * Construct the GroupController
	 *
	 * @param void
	 * @return GroupController
	 */
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website'); 
	} // __construct
	
	/**
	 * View specific group
	 *
	 * @param void
	 * @return null
	 */
	function view() {
		if(!can_manage_security(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return ;
		}

		$group = PermissionGroups::findById(get_id());
		if(!($group instanceof PermissionGroup)) {
			flash_error(lang('group dnx'));
			$this->redirectTo('administration');
		}
		tpl_assign('group_users', $group->getUsers());
		tpl_assign('group', $group);
	}
	
	/**
	 * Add group
	 *
	 * @param void
	 * @return null
	 */
	function add() {

		if(!can_manage_security(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return ;
		} // if

		$group = new PermissionGroup();
		$group_data = array_var($_POST, 'group');
		
		if(!is_array($group_data)) {
			
			tpl_assign('group', $group);
			tpl_assign('group_data', $group_data);

			// System permissions
			tpl_assign('system_permissions', new SystemPermission());

			// Module permissions
			$module_permissions_info = array();
			$all_modules = TabPanels::findAll(array("conditions" => "`enabled` = 1", "order" => "ordering"));
			$all_modules_info = array();
			foreach ($all_modules as $module) {
				$all_modules_info[] = array('id' => $module->getId(), 'name' => lang($module->getTitle()), 'ot' => $module->getObjectTypeId());
			}
			tpl_assign('module_permissions_info', $module_permissions_info);
			tpl_assign('all_modules_info', $all_modules_info);
			
			// Member permissions
			$parameters = permission_form_parameters(0);
			tpl_assign('permission_parameters', $parameters);
			
			// users
			tpl_assign('groupUserIds', array());
			tpl_assign('users', Contacts::getAllUsers());
			
		} else {
			$group->setFromAttributes($group_data);
			try {
				DB::beginWork();
                                $group->setType('user_groups');
				$group->setContactId(0);
				$group->save();
				
				// set permissions
				$pg_id = $group->getId();
				save_permissions($pg_id);
				
				// save users
				if ($users = array_var($_POST, 'user')) {
					foreach ($users as $user_id => $val){
						if ($val=='checked' && is_numeric($user_id) && (Contacts::findById($user_id) instanceof Contact)) {
							$cpg = new ContactPermissionGroup();
							$cpg->setPermissionGroupId($pg_id);
							$cpg->setContactId($user_id);
							$cpg->save();
						}
					}
				}
				
				//ApplicationLogs::createLog($group, ApplicationLogs::ACTION_ADD);
				DB::commit();
				flash_success(lang('success add group', $group->getName()));
				ajx_current("back");

			} catch(Exception $e) {
				DB::rollback();
				tpl_assign('error', $e);
			} // try
		} // if
	} // add_group

	/**
	 * Edit group
	 *
	 * @param void
	 * @return null
	 */
	function edit() {
		$this->setTemplate('add');

		if(!can_manage_security(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return ;
		} // if

		$group = PermissionGroups::findById(get_id());
		if(!($group instanceof PermissionGroup)) {
			flash_error(lang('group dnx'));
			$this->redirectTo('administration', 'groups');
		} // if

		$group_data = array_var($_POST, 'group');
		if(!is_array($group_data)) {
			$pg_id = $group->getId();
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
			
			// users
			$group_users = array();
			$cpgs = ContactPermissionGroups::findAll(array("conditions" => "`permission_group_id` = $pg_id"));
			foreach($cpgs as $cpg) $group_users[] = $cpg->getContactId();
			tpl_assign('groupUserIds', $group_users);
			tpl_assign('users', Contacts::getAllUsers());
			
			tpl_assign('group', $group);
			tpl_assign('group_data', array('name' => $group->getName()));
		} else {
			try {
				$group->setFromAttributes($group_data);
				DB::beginWork();
				$group->save();
				
				// set permissions
				$pg_id = $group->getId();
				save_permissions($pg_id);
				
				// save users
				ContactPermissionGroups::delete("`permission_group_id` = $pg_id");
				if ($users = array_var($_POST, 'user')) {
					foreach ($users as $user_id => $val){
						if ($val=='checked' && is_numeric($user_id) && (Contacts::findById($user_id) instanceof Contact)) {
							$cpg = new ContactPermissionGroup();
							$cpg->setPermissionGroupId($pg_id);
							$cpg->setContactId($user_id);
							$cpg->save();
						}
					}
				}
				
				//ApplicationLogs::createLog($group, ApplicationLogs::ACTION_EDIT);
				DB::commit();
				flash_success(lang('success edit group', $group->getName()));
				ajx_current("back");

			} catch(Exception $e) {
				DB::rollback();
				tpl_assign('error', $e);
			}
	
		}
	} // edit

	/**
	 * Delete group
	 *
	 * @param void
	 * @return null
	 */
	function delete() {
		if(!can_manage_security(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}

		$group = PermissionGroups::findById(get_id());
		if(!($group instanceof PermissionGroup)) {
			flash_error(lang('group dnx'));
			ajx_current("empty");
			return ;
		}
		
		if ($group->getContactId() > 0) {
			flash_error(lang('cannot delete personal permissions'));
			ajx_current("empty");
			return ;
		}

		try {
			DB::beginWork();
			$group->delete();
			//ApplicationLogs::createLog($group, ApplicationLogs::ACTION_DELETE);
			DB::commit();

			flash_success(lang('success delete group', $group->getName()));
			ajx_current("reload");
		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error delete group'));
			ajx_current("empty");
		} // try
	} // delete_group

} // GroupController

?>