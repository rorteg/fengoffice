<?php

/**
 * Tag class
 * Generated on Wed, 05 Apr 2006 06:44:54 +0200 by DataObject generation tool
 *
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class Tag extends BaseTag {

	/**
	 * Return object connected with this action
	 *
	 * @access public
	 * @param void
	 * @return ApplicationDataObject
	 */
	function getObject() {
		return get_object_by_manager_and_id($this->getRelObjectId(), $this->getRelObjectManager());
	} // getObject

	/**
	 * Return tag URL
	 *
	 * @param void
	 * @return string
	 */
	function getViewUrl() {
		return get_url('object', 'list_objects', array('tag' => $this->getTag(), 'active_project' => active_project()?active_project()->getId():0));
	} // getViewUrl

	// ---------------------------------------------------
	//  Permissions
	// ---------------------------------------------------

	/**
	 * Can $user view this object
	 *
	 * @param User $user
	 * @return boolean
	 */
	function canView(User $user) {
		return true;
	} // canView

	/**
	 * Empty implementation of static method. Update tag permissions are check by the taggable
	 * object, not tag itself
	 *
	 * @param User $user
	 * @param Project $project
	 * @return boolean
	 */
	function canAdd(User $user, Project $project) {
		return false;
	} // canAdd

	/**
	 * Empty implementation of static method. Update tag permissions are check by the taggable
	 * object, not tag itself
	 *
	 * @param User $user
	 * @return boolean
	 */
	function canEdit(User $user) {
		return false;
	} // canEdit

	/**
	 * Empty implementation of static method. Update tag permissions are check by the taggable
	 * object, not tag itself
	 *
	 * @param User $user
	 * @return boolean
	 */
	function canDelete(User $user) {
		return false;
	} // canDelete

	// ---------------------------------------------------
	//  ApplicationDataObject implementation
	// ---------------------------------------------------

	/**
	 * Return object type name
	 *
	 * @param void
	 * @return string
	 */
	function getObjectTypeName() {
		return 'tag';
	} // getObjectTypeName

	/**
	 * Return view tag URL
	 *
	 * @param void
	 * @return string
	 */
	function getObjectUrl() {
		return $this->getViewUrl();
	} // getObjectUrl

	function __toString() {
        return $this->getTag();
    }
} // Tag

?>