<?php

// Functions that check permissions
// Recomendation: Before changing this, talk with marcos.saiz@fengoffice.com

  	define('ACCESS_LEVEL_READ', 1);
  	define('ACCESS_LEVEL_WRITE', 2);
  	  	
  	/**
  	 * Returns whether a user can manage security.
  	 * If groups are checked, one true permission makes the function return true.
  	 *
  	 * @param User $user
  	 * @param boolean $include_groups states whether groups should be checked for permissions
  	 * @return boolean
  	 */
  	function can_manage_security(User $user, $include_groups = true){
  		if ($user->isGuest()) return false;
  		if ($user->getCanManageSecurity()){
  			return true;
  		} else if ($include_groups){	
			$group_ids = GroupUsers::getGroupsCSVsByUser($user->getId());
			if($group_ids!=''){
	  			$gr = Groups::findOne(array('conditions' => array('id in ('.$group_ids.') AND can_manage_security = true ')));
	  			return $gr instanceof Group ;
			}
  		}
  		return false;
  	}
  	
	/**
  	 * Returns whether a user can manage contacts.
  	 * If groups are checked, one true permission makes the function return true.
  	 *
  	 * @param User $user
  	 * @param boolean $include_groups states whether groups should be checked for permissions
  	 * @return boolean
  	 */
  	function can_manage_contacts(User $user, $include_groups = true){
  		if ($user->isGuest()) return false;
  		if ($user->getCanManageContacts()){
  			return true;
  		}
  		if ($include_groups){
  			$user_ids = $user->getId();  			
			$group_ids = GroupUsers::getGroupsCSVsByUser($user_ids);
			if($group_ids!=''){
	  			$gr = Groups::findOne(array('conditions' => array('id in ('.$group_ids.') AND can_manage_contacts = true ')));
	  			return $gr instanceof Group ;
			}
  		}
  		return false;
  	}
  	
  	
	/**
  	 * Returns whether a user can manage time.
  	 * If groups are checked, one true permission makes the function return true.
  	 *
  	 * @param User $user
  	 * @param boolean $include_groups states whether groups should be checked for permissions
  	 * @return boolean
  	 */
  	function can_manage_time(User $user, $include_groups = true){
  		if ($user->isGuest()) return false;
  		if ($user->getCanManageTime()){
  			return true;
  		}
  		if ($include_groups){
  			$user_ids = $user->getId();  			
			$group_ids = GroupUsers::getGroupsCSVsByUser($user_ids);
			if($group_ids!=''){
	  			$gr = Groups::findOne(array('conditions' => array('id in ('.$group_ids.') AND can_manage_time = true ')));
	  			return $gr instanceof Group ;
			}
  		}
  		return false;
  	}
  	
  	/**
  	 * Returns whether a user can add mail accounts.
  	 * If groups are checked, one true permission makes the function return true.
  	 *
  	 * @param User $user
  	 * @param boolean $include_groups states whether groups should be checked for permissions
  	 * @return boolean
  	 */
  	function can_add_mail_accounts(User $user, $include_groups = true){
  		if ($user->isGuest()) return false;
  		if ($user->getCanAddMailAccounts()){
  			return true;
  		}
  		if ($include_groups){
  			$user_ids = $user->getId();  			
			$group_ids = GroupUsers::getGroupsCSVsByUser($user_ids);
			if($group_ids!=''){
	  			$gr = Groups::findOne(array('conditions' => array('id in ('.$group_ids.') AND `can_add_mail_accounts` = true ')));
	  			return $gr instanceof Group ;
			}
  		}
  		return false;
  	}
  	
  	function can_manage_templates(User $user, $include_groups = true) {
  		if ($user->isGuest()) return false;
  		if ($user->getCanManageTemplates()) {
  			return true;
  		}
  		if ($include_groups) {
  			$user_ids = $user->getId();
			$group_ids = GroupUsers::getGroupsCSVsByUser($user_ids);
			if($group_ids!=''){
	  			$gr = Groups::findOne(array('conditions' => array('id in ('.$group_ids.') AND can_manage_templates = true ')));
	  			return $gr instanceof Group ;
			}
  		}
  		return false;
  	}
  	
  	function can_manage_reports(User $user, $include_groups = true) {
  		if ($user->isGuest()) return false;
  		if ($user->getCanManageReports()) {
  			return true;
  		}
  		if ($include_groups) {
  			$user_ids = $user->getId();
			$group_ids = GroupUsers::getGroupsCSVsByUser($user_ids);
			if($group_ids!=''){
	  			$gr = Groups::findOne(array('conditions' => array('id in ('.$group_ids.') AND can_manage_reports = true ')));
	  			return $gr instanceof Group ;
			}
  		}
  		return false;
  	}
  	
  	/**
  	 * Returns whether a user can manage configuration.
  	 * If groups are checked, one true permission makes the function return true.
  	 *
  	 * @param User $user
  	 * @param boolean $include_groups states whether groups should be checked for permissions
  	 * @return boolean
  	 */
  	function can_manage_configuration(User $user, $include_groups = true){
  		if ($user->isGuest()) return false;
  		if ($user->getCanManageConfiguration()){
  			return true;
  		}
  		if ($include_groups){
  			$user_ids = $user->getId();  			
			$group_ids = GroupUsers::getGroupsCSVsByUser($user_ids);
			if($group_ids!=''){
	  			$gr = Groups::findOne(array('conditions' => array('id in ('.$group_ids.') AND can_manage_configuration = true ')));
	  			return $gr instanceof Group ;
			}
  		}
  		return false;
  	}
  	
  	/**
  	 * Returns whether a user can manage workspaces.
  	 * If groups are checked, one true permission makes the function return true.
  	 *
  	 * @param User $user
  	 * @param boolean $include_groups states whether groups should be checked for permissions
  	 * @return boolean
  	 */
  	function can_manage_workspaces(User $user, $include_groups = true){
  		if ($user->isGuest()) return false;
  		if ($user->getCanManageWorkspaces()){
  			return true;
  		}
  		if ($include_groups){
  			$user_ids = $user->getId();  			
			$group_ids = GroupUsers::getGroupsCSVsByUser($user_ids);
			if($group_ids!=''){
	  			$gr = Groups::findOne(array('conditions' => array('id in ('.$group_ids.') AND can_manage_workspaces = true ')));
	  			return $gr instanceof Group ;
			}
  		}
  		return false;
  	}
  	
  	/**
  	 * Returns whether a user can edit company data.
  	 * If groups are checked, one true permission makes the function return true.
  	 *
  	 * @param User $user
  	 * @param boolean $include_groups states whether groups should be checked for permissions
  	 * @return boolean
  	 */
  	function can_edit_company_data(User $user, $include_groups = true){
  		if ($user->isGuest()) return false;
  		if ($user->getCanEditCompanyData()){
  			return true;
  		}
  		if ($include_groups){
  			$user_ids = $user->getId();  			
			$group_ids = GroupUsers::getGroupsCSVsByUser($user_ids);
			if($group_ids!=''){
	  			$gr = Groups::findOne(array('conditions' => array('id in ('.$group_ids.') AND can_edit_company_data = true ')));
	  			return $gr instanceof Group ;
			}
  		}
  		return false;
  	}
  	
	/**
	 * Returns the field name that has to be checked for the given access level
	 *
	 * @param ObjectUserPermission $perm
	 * @param unknown_type $access_level
	 */
	function access_level_field_name($access_level){
		switch ($access_level){
			case ACCESS_LEVEL_READ: return "can_read"; break;
			case ACCESS_LEVEL_WRITE: return "can_write"; break;			
		}
		throw new Exception('Invalid ACCESS LEVEL in permission helper',-1);
	}  	
	
	/**
	 * Returns the field name that has to be checked for the given object type
	 *
	 * @param ApplicationDataObject $object
	 * @param ProjectPermission $proj_perm
	 * @return unknown
	 */
	function manager_class_field_name($manager_class,$access_level){
		if ($manager_class != ''){
			switch ($manager_class){
				case 'ProjectEvents' : 
					if ($access_level == ACCESS_LEVEL_WRITE)
						return 'can_write_events';
					else if ($access_level == ACCESS_LEVEL_READ)
						return 'can_read_events';
					else return false;
					break;
				case 'ProjectFiles' :  
					if ($access_level == ACCESS_LEVEL_WRITE)
						return 'can_write_files';
					else if ($access_level == ACCESS_LEVEL_READ)
						return 'can_read_files';
					else return false;
					break;
				case 'ProjectMessages' :  
					if ($access_level == ACCESS_LEVEL_WRITE)
						return 'can_write_messages';
					else if ($access_level == ACCESS_LEVEL_READ)
						return 'can_read_messages';
					else return false;
					break;
				case 'ProjectMilestones' :  
					if ($access_level == ACCESS_LEVEL_WRITE)
						return 'can_write_milestones';
					else if ($access_level == ACCESS_LEVEL_READ)
						return 'can_read_milestones';
					else return false;
					break;
				case 'ProjectTasks' :  
					if ($access_level == ACCESS_LEVEL_WRITE)
						return 'can_write_tasks';
					else if ($access_level == ACCESS_LEVEL_READ)
						return 'can_read_tasks';
					else return false;
					break;
				case 'ProjectWebpages' :  
					if ($access_level == ACCESS_LEVEL_WRITE)
						return 'can_write_weblinks';
					else if ($access_level == ACCESS_LEVEL_READ)
						return 'can_read_weblinks';
					else return false;
					break;
				case 'MailContents' :  
					if ($access_level == ACCESS_LEVEL_WRITE)
						return 'can_write_mails';
					else if ($access_level == ACCESS_LEVEL_READ)
						return 'can_read_mails';
					else return false;
					break;
				case 'Companies' : 
				case 'Contacts' :  
					if ($access_level == ACCESS_LEVEL_WRITE)
						return 'can_write_contacts';
					else if ($access_level == ACCESS_LEVEL_READ)
						return 'can_read_contacts';
					else return false;
					break;
			}
		}
		throw new Exception('Invalid MANAGER in permission helper',-1);
	}
  	
	/**
  	 * Enter description here...
  	 * assumes manager has one field as PK
  	 *
  	 * @param DataManager $manager
  	 * @param $access_level ACCESS_LEVEL_XX objects that defines which permission is being checked
  	 * @param string $project_id string that will be compared to the project id while searching project_user table
  	 * @param int $user_id user whose permissions are being checked
  	 * @return unknown
  	 */
	function permissions_sql_for_listings (DataManager $manager, $access_level, User $user, $project_id = '`project_id`', $table_alias = null){
		if(! ($manager instanceof DataManager)){
			throw new Exception("Invalid manager '$manager' in permissions helper", -1);
			return '';
		}
		$user_id = $user->getId();
		$oup_tablename = ObjectUserPermissions::instance()->getTableName(true);
		$wo_tablename = WorkspaceObjects::instance()->getTableName(true);
		$users_table_name =  Users::instance()->getTableName(true);
		$pu_table_name = ProjectUsers::instance()->getTableName(true);
		
		if ($user->isGuest() && $access_level == ACCESS_LEVEL_WRITE) return 'false';
		
		if (isset($table_alias) && $table_alias && $table_alias!='')
			$object_table_name = $table_alias;
		else
			$object_table_name = $manager->getTableName();
		if (!is_numeric($project_id))
			$project_id = "$object_table_name.$project_id";
		
		$object_id_field = $manager->getPkColumns();
		$object_id = $object_table_name . '.' . $object_id_field;
		$object_manager = get_class($manager);
		$access_level_text = access_level_field_name($access_level);
		$item_class = $manager->getItemClass();
		$is_project_data_object = (new $item_class) instanceof ProjectDataObject  ;
		
		// permissions for contacts
		if ($manager instanceof Contacts && can_manage_contacts($user)) {
			return 'true';
		}
		if ($manager instanceof Companies && can_manage_contacts($user)) {
			return 'true';
		}
		// permissions for file revisions
		if ($manager instanceof ProjectFileRevisions) {
			$pfTableName = "`" . TABLE_PREFIX . "project_files`";
			return "$object_table_name.`file_id` IN (SELECT `id` FROM $pfTableName WHERE " . permissions_sql_for_listings(ProjectFiles::instance(), $access_level, $user) . ")";
		}
		// permissions for projects
		if ($manager instanceof Projects) {
			$pcTableName = "`" . TABLE_PREFIX . 'project_users`';
			return "$object_table_name.`id` IN (SELECT `project_id` FROM $pcTableName `pc` WHERE `user_id` = $user_id)";
		}
		// permissions for users
		if ($manager instanceof Users) {
			if (logged_user()->isMemberOfOwnerCompany()) return "true";
			else return "$object_table_name.`company_id` = ".owner_company()->getId() ." OR $object_table_name.`company_id` = ". logged_user()->getCompanyId();
		}
		
		$can_manage_object = manager_class_field_name($object_manager, $access_level);
		
		// user is creator
		$str = " ( `created_by_id` = $user_id) ";
		// element belongs to personal project
		/*if($is_project_data_object) // TODO: type of element belongs to a project
			if (!in_array('project_id', $manager->getColumns())) {
				$str .= "\n OR ( EXISTS(SELECT * FROM $users_table_name `xx_u`, $wo_tablename `xx_wo`
				WHERE `xx_u`.`id` = $user_id
					AND `xx_u`.`personal_project_id` = `xx_wo`.`workspace_id`
					AND `xx_wo`.`object_id` = $object_id 
					AND `xx_wo`.`object_manager` = '$object_manager' )) ";
			} else {
				$str .= "\n OR ( $project_id = (SELECT `personal_project_id` FROM $users_table_name `xx_u` WHERE `xx_u`.`id` = $user_id)) ";
			}
		*/
		// user or group has specific permissions over object
		$group_ids = $user->getGroupsCSV();
		$all_ids = '(' . $user_id . ($group_ids != '' ? ',' . $group_ids : '' ) . ')';
		$str .= "\n OR ( EXISTS ( SELECT * FROM $oup_tablename `xx_oup` 
				WHERE `xx_oup`.`rel_object_id` = $object_id 
					AND `xx_oup`.`rel_object_manager` = '$object_manager' 
					AND `xx_oup`.`user_id` IN $all_ids 
					AND `xx_oup`.$access_level_text = true) )" ;
		if($is_project_data_object){ // TODO: type of element belongs to a project
			if (!in_array('project_id', $manager->getColumns())) {
				$str .= "\n OR ( EXISTS ( SELECT * FROM $pu_table_name `xx_pu`, $wo_tablename `xx_wo` 
				WHERE `xx_pu`.`user_id` IN $all_ids 
					AND `xx_pu`.`project_id` = `xx_wo`.`workspace_id`
					AND `xx_wo`.`object_id` = $object_id 
					AND `xx_wo`.`object_manager` = '$object_manager'
					AND `xx_pu`.$can_manage_object = true ) ) ";
			} else {
				$str .= "\n OR ( EXISTS ( SELECT * FROM $pu_table_name `xx_pu` 
				WHERE `xx_pu`.`user_id` IN $all_ids 
					AND `xx_pu`.`project_id` = $project_id 
					AND `xx_pu`.$can_manage_object = true ) ) ";
			}
		}
		
		// check account permissions in case of emails
		if ($manager instanceof MailContents) {
			$maccTableName = MailAccountUsers::instance()->getTableName(true);
			$str .= "\n OR EXISTS(SELECT `id` FROM $maccTableName WHERE `account_id` = $object_table_name.`account_id` AND `user_id` = $user_id)";
			if (user_config_option('view deleted accounts emails', null, $user_id)) {
				$str .= "\n OR ((SELECT count(*) FROM `" . TABLE_PREFIX . "mail_accounts` WHERE `id` = $object_table_name.`account_id`) = 0) AND `created_by_id` = $user_id";
			}
		}
		
		$hookargs = array(
			'manager' => $manager,
			'access_level' => $access_level,
			'user' => $user,
			'project_id' => $project_id,
			'table_alias' => $table_alias
		);
		Hook::fire('permissions_sql', $hookargs, $str);
		return ' (' . $str . ') ';
	}	
	
	
	/**
	 * Return true is $user can add an $object. False otherwise.
	 *
	 * @param User $user
	 * @param Project $project
	 * @param string $object_type
	 * @return boolean
	 */
	function can_add(User $user, Project $project, $object_manager){
		if ($user->isGuest()) return false;
		try {
			if (!$project instanceof Project) return false;
			$user_id = $user->getId();
			$proj_perm = ProjectUsers::findOne(array('conditions' => array('user_id = ? AND project_id = ? ',  $user_id , $project->getId())));
			if ($proj_perm && can_manage_type($object_manager,$proj_perm, ACCESS_LEVEL_WRITE)){
				return true; // if user has permissions over type of object in the project
			}
			$group_ids = GroupUsers::getGroupsCSVsByUser($user_id);
			if($group_ids && $group_ids!= ''){ //user belongs to at least one group
				$proj_perms = ProjectUsers::findAll(array('conditions' => array('project_id = '.$project->getId().' AND user_id in ('. $group_ids .')')));
				if($proj_perms){
					foreach ($proj_perms as $perm){
						if( can_manage_type($object_manager,$perm, ACCESS_LEVEL_WRITE)) return true; // if any group has permissions over type of object in the project
					}	
				}
			}
		}
		catch(Exception $e) {
				tpl_assign('error', $e);
				return false;
		}
		return false;
	}
	
	/**
	 * Return true is $user can add read $object_type objects in the project. False otherwise.
	 *
	 * @param User $user
	 * @param Project $project
	 * @param string $object_type
	 * @return boolean
	 */
	function can_read_type(User $user, Project $project, $object_manager){
		try {
			if (!$project instanceof Project) return false;
			$user_id = $user->getId();
			$proj_perm = ProjectUsers::findOne(array('conditions' => array('user_id = ? AND project_id = ? ',  $user_id , $project->getId())));
			if ($proj_perm && can_manage_type($object_manager,$proj_perm, ACCESS_LEVEL_READ)){
				return true; // if user has permissions over type of object in the project
			}
			$group_ids = GroupUsers::getGroupsCSVsByUser($user_id);
			if($group_ids && $group_ids!= ''){ //user belongs to at least one group
				$proj_perms = ProjectUsers::findAll(array('conditions' => array('project_id = '.$project->getId().' AND user_id in ('. $group_ids .')')));
				if($proj_perms){
					foreach ($proj_perms as $perm){
						if( can_manage_type($object_manager, $perm, ACCESS_LEVEL_READ)) return true; // if any group has permissions over type of object in the project
					}	
				}
			}
		}
		catch(Exception $e) {
				tpl_assign('error', $e);
				return false;
		}
		return false;
	}
	
	/**
	 * Return true is $user can read an $object. False otherwise.
	 *
	 * @param User $user
	 * @param ApplicationDataObject $object
	 * @return unknown
	 */
	function can_read(User $user, ApplicationDataObject $object){
		return can_access($user, $object, ACCESS_LEVEL_READ);
	}
	
	/**
	 * Return true is $user can write an $object. False otherwise.
	 *
	 * @param User $user
	 * @param ApplicationDataObject $object
	 * @return unknown
	 */
	function can_write(User $user, ApplicationDataObject $object){
		if ($user->isGuest()) return false;
		return can_access($user, $object, ACCESS_LEVEL_WRITE);
	}
	
	/**
	 * Return true is $user can delete an $object. False otherwise.
	 *
	 * @param User $user
	 * @param ApplicationDataObject $object
	 * @return unknown
	 */
	function can_delete(User $user, ApplicationDataObject $object){
		if ($user->isGuest()) return false;
		return can_access($user, $object, ACCESS_LEVEL_WRITE);
	}
	
	/**
	 * Return true is $user has $access_level (R/W) over $object
	 *
	 * @param User $user
	 * @param ApplicationDataObject $object
	 * @param int $access_level // 1 = read ; 2 = write
	 * @return unknown
	 */
	function can_access(User $user, ApplicationDataObject $object, $access_level){
		try {
			if (!$object instanceof ApplicationDataObject) {
				throw new Exception(lang('object dnx'));
			}
			$hookargs = array(
				"user" => $user,
				"object" => $object,
				"access_level" => $access_level
			);
			$ret = null;
			Hook::fire('can_access', $hookargs, $ret);
			if (is_bool($ret)) {
				return $ret;
			}
			if ($object instanceof Comment) {
				return can_access($user, $object->getObject(), $access_level);
			}
			if ($user->isGuest() && $access_level == ACCESS_LEVEL_WRITE) return false;
			if ($object instanceof ProjectFileRevision) {
				return can_access($user, $object->getFile(), $access_level);
			}
			if ($object->columnExists('project_id')) {
				$user_id = $user->getId();
				if(!$object instanceof ProjectContact && $object->getCreatedById() == $user_id)
					return true; // the user is the creator of the object
				if($object instanceof ProjectDataObject && $object->getProject() instanceof Project && $object->getProject()->getId() == $user->getPersonalProjectId() )
					return true; // The object belongs to the user's personal project
				$perms = ObjectUserPermissions::getAllPermissionsByObject($object, $user->getId());		
				if ($perms && is_array($perms)) //if the permissions for the user in the object are specially set
					return has_access_level($perms[0],$access_level); 
				$group_ids = GroupUsers::getGroupsCSVsByUser($user_id);
				if($group_ids && $group_ids!= ''){ //user belongs to at least one group
					$perms = ObjectUserPermissions::getAllPermissionsByObject($object, $group_ids);			
					if($perms){
						foreach ($perms as $perm){
							if ( has_access_level($perm,$access_level))
								return true; //there is one group permission that allows the user to access
						}				
					}
				}
				if($object instanceof ProjectDataObject && $object->getProject()){
					//if the object has a project assigned to it
					$proj_perm = ProjectUsers::findOne(array('conditions' => array('user_id = ? AND project_id = ? ',  $user_id , $object->getProject()->getId())));
					if ($proj_perm && can_manage_type(get_class($object->manager()),$proj_perm,$access_level)){
						return true; // if user has permissions over type of object in the project
					}
					if($group_ids && $group_ids!= ''){ //user belongs to at least one group
						$proj_perms = ProjectUsers::findAll(array('conditions' => array('project_id = '.$object->getProject()->getId().' AND user_id in ('. $group_ids .')')));
						if($proj_perms){
							foreach ($proj_perms as $perm){
								if( can_manage_type(get_class($object->manager()),$perm,$access_level)) return true; // if any group has permissions over type of object in the project
							}	
						}
					}
				}
			} else {
				// handle object in multiple workspaces
				$user_id = $user->getId();
				if($object->getCreatedById() == $user_id) {
					return true; // the user is the creator of the object
				}
				
				if ($object instanceof MailContent) {
					$acc = MailAccounts::findById($object->getAccountId());
					if (!$acc instanceof MailAccount) {
						$wss = $object->getWorkspaces();
						foreach ($wss as $ws){
							if ($ws && $ws->canView($user)){
								return true; // if user has permissions over type of object in the project
							}
						}
						return false; // it's an email with no account and not created by the user, and the user does not have permissions in the workspaces
					} else if (($access_level == ACCESS_LEVEL_READ && $acc->canView($user)) || ($access_level == ACCESS_LEVEL_WRITE && $acc->canDelete($user))){
						return true;
					}else{
						$wss = $object->getWorkspaces();
						if (count($wss)>0){
							foreach ($wss as $ws){
								if ($ws && $ws->canView($user) && $acc->canView($user)){
									return true; // if user has permissions over type of object in the project
								}
							}
						}else if($acc->canView($user)){
							return true;							
						}
						
					}
				}
				
				$perms = ObjectUserPermissions::getAllPermissionsByObject($object, $user->getId());		
				if ($perms && is_array($perms)) { //if the permissions for the user in the object are specially set
					return has_access_level($perms[0],$access_level);
				} 
				$group_ids = GroupUsers::getGroupsCSVsByUser($user_id);
				if($group_ids && $group_ids!= '') { //user belongs to at least one group
					$perms = ObjectUserPermissions::getAllPermissionsByObject($object, $group_ids);			
					if($perms) {
						foreach ($perms as $perm){
							if ( has_access_level($perm,$access_level)) {
								return true; //there is one group permission that allows the user to access
							}
						}				
					}
				}
				if($object instanceof ProjectDataObject){
					$ws = $object->getWorkspaces();
					foreach ($ws as $w) {
						// if the object has a project assigned to it
						$proj_perm = ProjectUsers::findOne(array('conditions' => array('user_id = ? AND project_id = ? ',  $user_id , $w->getId())));
						if ($proj_perm && can_manage_type(get_class($object->manager()), $proj_perm, $access_level)){
							return true; // if user has permissions over type of object in the project
						}
						if ($group_ids && $group_ids!= '') { //user belongs to at least one group
							$proj_perms = ProjectUsers::findAll(array('conditions' => array('project_id = '.$w->getId().' AND user_id in ('. $group_ids .')')));
							if($proj_perms) {
								foreach ($proj_perms as $perm) {
									if( can_manage_type(get_class($object->manager()),$perm,$access_level)) return true; // if any group has permissions over type of object in the project
								}	
							}
						}
					}
				}
			}		
		}catch(Exception $e) {			
			tpl_assign('error', $e);
			return false;
		}		
		return false;
	}
	/**
	 * Check whether an ObjectUserPermission
	 *
	 * @param ObjectUserPermission $perm
	 * @param unknown_type $access_level
	 */
	function has_access_level(ObjectUserPermission $perm, $access_level){
		switch ($access_level){
			case ACCESS_LEVEL_READ: return $perm->hasReadPermission(); break;
			case ACCESS_LEVEL_WRITE: return $perm->hasWritePermission(); break;			
		}
		return false;
	}
	
	
	/**
	 * Determines whether a ProjectUser object allows access to an object
	 *
	 * @param ApplicationDataObject $object
	 * @param ProjectPermission $proj_perm
	 * @return unknown
	 */
	function can_manage_type($object_type, $proj_perm, $access_level){
		if ($proj_perm){
			switch ($object_type){
				case 'ProjectEvents' : 
					if ($access_level == ACCESS_LEVEL_WRITE)
						return $proj_perm->getCanWriteEvents();
					else if ($access_level == ACCESS_LEVEL_READ)
						return $proj_perm->getCanReadEvents();
					else return false;
					break;
				case 'ProjectFiles' :  
					if ($access_level == ACCESS_LEVEL_WRITE)
						return $proj_perm->getCanWriteFiles();
					else if ($access_level == ACCESS_LEVEL_READ)
						return $proj_perm->getCanReadFiles();
					else return false;
					break;
				case 'ProjectMessages' :  
					if ($access_level == ACCESS_LEVEL_WRITE)
						return $proj_perm->getCanWriteMessages();
					else if ($access_level == ACCESS_LEVEL_READ)
						return $proj_perm->getCanReadMessages();
					else return false;
					break;
				case 'ProjectMilestones' :  
					if ($access_level == ACCESS_LEVEL_WRITE)
						return $proj_perm->getCanWriteMilestones();
					else if ($access_level == ACCESS_LEVEL_READ)
						return $proj_perm->getCanReadMilestones();
					else return false;
					break;
				case 'ProjectTasks' :  
					if ($access_level == ACCESS_LEVEL_WRITE)
						return $proj_perm->getCanWriteTasks();
					else if ($access_level == ACCESS_LEVEL_READ)
						return $proj_perm->getCanReadTasks();
					else return false;
					break;
				case 'ProjectWebpages' :  
					if ($access_level == ACCESS_LEVEL_WRITE)
						return $proj_perm->getCanWriteWeblinks();
					else if ($access_level == ACCESS_LEVEL_READ)
						return $proj_perm->getCanReadWeblinks();
					else return false;
					break;
				case 'MailContents' :  
					if ($access_level == ACCESS_LEVEL_WRITE)
						return $proj_perm->getCanWriteMails();
					else if ($access_level == ACCESS_LEVEL_READ)
						return $proj_perm->getCanReadMails();
					else return false;
					break;
				case 'Companies':
				case 'Contacts' :  
					if ($access_level == ACCESS_LEVEL_WRITE)
						return $proj_perm->getCanWriteContacts();
					else if ($access_level == ACCESS_LEVEL_READ)
						return $proj_perm->getCanReadContacts();
					else return false;
					break;
			}
		}
		return false;
	}
	
	/**
	 * Tells whether a user can assign a task to another user or company in a workspace.
	 * 
	 * @param $user User to which to check permissions
	 * @param $workspace
	 * @param $assignee
	 * @return boolean
	 */
	function can_assign_task(User $user, Project $workspace, $assignee) {
		if (!$assignee instanceof User && !$assignee instanceof Company) return true;
		if ($assignee instanceof Company) {
			$company = $assignee;
		} else {
			if ($assignee->getId() == $user->getId()) return true; // alow user to assign to himself
			$company = $assignee->getCompany();
		}
		$is_owner = $company->getId() == Companies::getOwnerCompany()->getId();
		$permissions = ProjectUsers::getByUserAndProject($workspace, $user);
		if ($permissions instanceof ProjectUser) {
			if ($is_owner) {
				if ($permissions->getCanAssignToOwners()) return true;
			} else {
				if ($permissions->getCanAssignToOther()) return true;
			}
		}
		$groups = GroupUsers::getGroupsByUser($user->getId());		
		if (is_array($groups) && count($groups) > 0) { //user belongs to at least one group
			foreach ($groups as $group) {
				$permissions = ProjectUsers::getByUserAndProject($workspace, $group);
				if ($permissions instanceof ProjectUser) {
					if ($is_owner) {
							if ($permissions->getCanAssignToOwners()) return true;
						} else {
							if ($permissions->getCanAssignToOther()) return true;
						}
					}
				}
		}
		return false;
	}
	
	/**
	 * Returns true if user can assign the task or an error string if not.
	 * @param $user
	 * @param $task
	 * @param $company_id
	 * @param $user_id
	 * @return mixed
	 */
	function can_assign_task_to_company_user(User $user, ProjectTask $task, $company_id, $user_id) {
		if ($company_id != 0) {
			$workspace = $task->getProject();
			if ($user_id != 0) {
				$assignee = Users::findById($user_id);
				if (!$assignee instanceof User) {
					return lang('error assign task user dnx');
				} else if (!can_assign_task($user, $workspace, $assignee)) {
					return lang('error assign task permissions user');
				}
			} else {
				$company = Companies::findById($company_id);
				if (!$company instanceof Company) {
					return lang('error assign task company dnx');
				} else if (!can_assign_task($user, $workspace, $company)) {
					return lang('error assign task permissions company');
				}
			}
		}
		return true;
	}
	
?>