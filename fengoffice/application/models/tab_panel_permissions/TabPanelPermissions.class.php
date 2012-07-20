<?php

  /**
  * TabPanelPermissions
  *
  * @author Alvaro Torterola <alvarotm01@gmail.com>
  */
  class TabPanelPermissions extends BaseTabPanelPermissions {

     function clearByPermissionGroup($pg_id) {
     	self::delete("`permission_group_id` = $pg_id");
     }
     
     
    function isModuleEnabled($tab_panel_id, $pg_ids){
     	$tab_permission = self::findOne(array('conditions'=>"`tab_panel_id` = '$tab_panel_id' AND `permission_group_id`
     					  IN ($pg_ids)"));
     	
     	if (!is_null($tab_permission))return true;
     	return false;
     }
   	function getRoleModules($rol){
     	$tab_permission = self::findAll(array('conditions'=>"`permission_group_id`=$rol"));
     	$tabs=array();
     	foreach($tab_permission as $tab){
     		$tabs[]=$tab->getColumnValue('tab_panel_id');
     	}
     	return $tabs;
    }
    function getAllRolesModules(){
    	$groups=PermissionGroups::getNonPersonalPermissionGroups('`parent_id`,`id` ASC');
  		$roles_permissions=array();
  		$tabs=array();
  		foreach($groups as $group){
  			$tabs[$group->getId()]=array();
  			$tabs[$group->getId()]=self::getRoleModules($group->getId());
  		}
  		return $tabs;
    }
  		
  } // TabPanelPermissions 

?>