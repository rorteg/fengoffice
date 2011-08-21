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
	function view_group() {
		$this->setTemplate('view_group');

		if(!can_manage_security(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return ;
		} // if

		$group = Groups::findById(get_id());
		if(!($group instanceof Group)) {
			flash_error(lang('group dnx'));
			$this->redirectTo('administration');
		} // if
		tpl_assign('group', $group);
	} // view_group
	
	/**
	 * Add group
	 *
	 * @param void
	 * @return null
	 */
	function add_group() {
		$this->setTemplate('add_group');

		if(!can_manage_security(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return ;
		} // if

		if (logged_user()->isAdministrator()) {
			$projects = Projects::getAll();
		} else {
			$projects = null;
		}
		
		$group = new Group();
		$group_data = array_var($_POST, 'group');
		tpl_assign('group', $group);
		tpl_assign('group_data', $group_data);
		tpl_assign('projects', $projects);

		if(is_array($group_data)) {
			$group->setFromAttributes($group_data);
			try {
				DB::beginWork();
				$group->save();
				//set permissions
				$permissionsString = array_var($_POST,'permissions');
				if ($permissionsString && $permissionsString != ''){
					$permissions = json_decode($permissionsString);
				}
			  	if(is_array($permissions) && count($permissions) > 0) {
			  		//Clear old modified permissions
			  		$ids = array();
			  		foreach($permissions as $perm)
			  			$ids[] = $perm->wsid;
			  			
			  		ProjectUsers::clearByUser($group,implode(',',$ids));
			  		
			  		//Add new permissions
			  		//TODO - Make batch update of these permissions
			  		foreach($permissions as $perm){
			  			if(ProjectUser::hasAnyPermissions($perm->pr,$perm->pc)){			  				
				  			$relation = new ProjectUser();
					  		$relation->setProjectId($perm->wsid);
					  		$relation->setUserId($group->getId());
				  			
					  		$relation->setCheckboxPermissions($perm->pc);
					  		$relation->setRadioPermissions($perm->pr);
					  		$relation->save();
			  			} //endif
			  			//else if the user has no permissions at all, he is not a project_user. ProjectUser is not created
			  		} //end foreach
				} // if
				
				$group->save();
				if (array_var($_POST, 'user')) {
					foreach (array_var($_POST, 'user') as $user_id => $val){
						if ($val=='checked' && is_numeric($user_id) && (Users::findById($user_id) instanceof  User)) {
							$gu = new GroupUser();
							$gu->setGroupId($group->getId());
							$gu->setUserId($user_id);
							$gu->save();
						}
					}
				}
				ApplicationLogs::createLog($group, null, ApplicationLogs::ACTION_ADD);
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
	function edit_group() {
		$this->setTemplate('add_group');

		if(!can_manage_security(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return ;
		} // if

		$group = Groups::findById(get_id());
		if(!($group instanceof Group)) {
			flash_error(lang('group dnx'));
			$this->redirectTo('administration', 'groups');
		} // if
		
		if (logged_user()->isAdministrator()) {
			$projects = Projects::getAll();
		} else {
			$projects = null;
		}
		
		$permissions = ProjectUsers::getNameTextArray();

		$group_data = array_var($_POST, 'group');
		if(!is_array($group_data)) {
			$group_data = array(
	          'name' => $group->getName(),
	          'can_edit_company_data' => $group->getCanEditCompanyData(),
	          'can_manage_security' => $group->getCanManageSecurity(),
	          'can_manage_workspaces' => $group->getCanManageWorkspaces(),
	          'can_manage_configuration' => $group->getCanManageConfiguration(),
	          'can_manage_contacts' => $group->getCanManageContacts(),
			  'can_manage_templates' => $group->getCanManageTemplates(),
			  'can_manage_reports' => $group->getCanManageReports(),
			  'can_manage_time' => $group->getCanManageTime(),
			  'can_add_mail_accounts' => $group->getCanAddMailAccounts(),
			); // array			
		} // if
		$users = GroupUsers::getUsersByGroup($group->getId());
		if($users)
			foreach ($users as $usr)
				$group_data['user['.$usr->getId().']'] = true;
		tpl_assign('group', $group);
		tpl_assign('group_data', $group_data);
		tpl_assign('permissions', $permissions);
		tpl_assign('projects', $projects);
		
		if (is_array(array_var($_POST, 'group'))) {
			$group->setFromAttributes($group_data);
			if(array_var($group_data, "can_edit_company_data") != 'checked') $group->setCanEditCompanyData(false);
			if(array_var($group_data, "can_manage_security") != 'checked') $group->setCanManageSecurity(false);
			if(array_var($group_data, "can_manage_configuration") != 'checked') $group->setCanManageConfiguration(false);
			if(array_var($group_data, "can_manage_workspaces") != 'checked') $group->setCanManageWorkspaces(false);
			if(array_var($group_data, "can_manage_contacts") != 'checked') $group->setCanManageContacts(false);
			if(array_var($group_data, "can_manage_templates") != 'checked') $group->setCanManageTemplates(false);
			if(array_var($group_data, "can_manage_reports") != 'checked') $group->setCanManageReports(false);
			if(array_var($group_data, "can_manage_time") != 'checked') $group->setCanManageTime(false);
			if(array_var($group_data, "can_add_mail_accounts") != 'checked') $group->setCanAddMailAccounts(false);
			try {
				DB::beginWork();
				//set permissions
				$permissionsString = array_var($_POST,'permissions');
				if ($permissionsString && $permissionsString != ''){
					$permissions = json_decode($permissionsString);
				}
			  	if(is_array($permissions) && count($permissions) > 0) {
			  		//Clear old modified permissions
			  		$ids = array();
			  		foreach($permissions as $perm)
			  			$ids[] = $perm->wsid;
			  			
			  		ProjectUsers::clearByUser($group,implode(',',$ids));
			  		
			  		//Add new permissions
			  		//TODO - Make batch update of these permissions
			  		foreach($permissions as $perm){
			  			if(ProjectUser::hasAnyPermissions($perm->pr,$perm->pc)){			  				
				  			$relation = new ProjectUser();
					  		$relation->setProjectId($perm->wsid);
					  		$relation->setUserId($group->getId());
				  			
					  		$relation->setCheckboxPermissions($perm->pc);
					  		$relation->setRadioPermissions($perm->pr);
					  		$relation->save();
			  			} //endif
			  			//else if the user has no permissions at all, he is not a project_user. ProjectUser is not created
			  		} //end foreach
				} // if
				
				$group->save();
				GroupUsers::clearByGroup($group);
				if (array_var($_POST, 'user')){
					foreach (array_var($_POST, 'user') as $user_id => $val){
						if ($val == 'checked' && is_numeric($user_id) && (Users::findById($user_id) instanceof User)) {
							$gu = new GroupUser();
							$gu->setGroupId($group->getId());
							$gu->setUserId($user_id);
							$gu->save();
						}
					}
				}				
				ApplicationLogs::createLog($group, null, ApplicationLogs::ACTION_EDIT);
				DB::commit();

				flash_success(lang('success edit group', $group->getName()));
				ajx_current("back");

			} catch(Exception $e) {
				DB::rollback();
				tpl_assign('error', $e);
			} // try
		} // if
	} // edit_group

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
		} // if

		$group = Groups::findById(get_id());
		if(!($group instanceof Group)) {
			flash_error(lang('group dnx'));
			ajx_current("empty");
			return ;
		} // if

		try {
			DB::beginWork();
			$group->delete();
			ApplicationLogs::createLog($group, null, ApplicationLogs::ACTION_DELETE);
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