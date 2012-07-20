<?php

/**
 * PermissionGroup class
 *
 * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
 */
class PermissionGroup extends BasePermissionGroup {
	
	function getUsers() {
		return Contacts::findAll(array("conditions" => "`id` IN ( SELECT `contact_id` FROM ".ContactPermissionGroups::instance()->getTableName(true)." 
			WHERE `permission_group_id` = ".$this->getId().")"));
	}
	
	function getViewUrl() {
		return get_url('group', 'view', array("id" => $this->getId()));
	}
	
	function getEditUrl() {
		return get_url('group', 'edit', array("id" => $this->getId()));
	}
	
	function getDeleteUrl() {
		return get_url('group', 'delete', array("id" => $this->getId()));
	}
	
	
	function delete() {
		// delete system permissions
		SystemPermissions::delete("`permission_group_id` = ".$this->getId());
		// delete member permissions
		ContactMemberPermissions::delete("`permission_group_id` = ".$this->getId());
		// delte dimension permissions
		ContactDimensionPermissions::delete("`permission_group_id` = ".$this->getId());
		// delete contact_permission_group entries
		ContactPermissionGroups::delete("`permission_group_id` = ".$this->getId());
		// delete tab panel permissions
		TabPanelPermissions::delete("`permission_group_id` = ".$this->getId());
		
		parent::delete();
	}
	
} // PermissionGroup

?>