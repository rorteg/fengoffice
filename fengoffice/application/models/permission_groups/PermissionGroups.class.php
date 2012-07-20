<?php

  /**
  * PermissionGroups
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  class PermissionGroups extends BasePermissionGroups {
    
    function getNonPersonalPermissionGroups($order = '`name` ASC') {
    	return self::findAll(array("conditions" => "`contact_id` = 0 AND `parent_id` != 0", "order" => $order));
    }
    function getNonPersonalSameLevelPermissionsGroups($order = '`name` ASC') {
    	return self::findAll(array("conditions" => "`contact_id` = 0 AND `parent_id` != 0 AND `id` >= ".logged_user()->getUserType(), "order" => $order));
    }
    function getParentId($group_id){
    	return self::findById($group_id)->getParentId();
    }
    
    function getGuestPermissionGroups() {
    	return self::findAll(array("conditions" => "parent_id IN (SELECT p.id FROM ".TABLE_PREFIX."permission_groups p WHERE p.name='GuestGroup')"));
    }
    
    static function getNonRolePermissionGroups() {
//    	$roles = "'Administrator','Collaborator Customer','CollaboratorGroup','Executive','ExecutiveGroup','External Collaborator','Guest','Guest Customer','GuestGroup','Internal Collaborator','Manager','Non-Exec Director','Super Administrator'";
//		return self::findAll(array("conditions" => "`contact_id` = 0 AND `name` NOT IN ($roles) AND parent_id=0"));
        return self::findAll(array("conditions" => "`type` = 'user_groups'"));
    }
    
  } // PermissionGroups 

?>