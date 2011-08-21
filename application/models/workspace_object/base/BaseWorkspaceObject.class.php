<?php

/**
 * BaseWorkspaceObject class
 *
 * @author Ignacio de Soto
 */
abstract class BaseWorkspaceObject extends DataObject {

	// -------------------------------------------------------
	//  Access methods
	// -------------------------------------------------------

	/**
	 * Return value of 'workspace_id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getWorkspaceId() {
		return $this->getColumnValue('workspace_id');
	} // getWorkspaceId()

	/**
	 * Set value of 'workspace_id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setWorkspaceId($value) {
		return $this->setColumnValue('workspace_id', $value);
	} // setWorkspaceId()
	 
	/**
	 * Return value of 'object_manager' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getObjectManager() {
		return $this->getColumnValue('object_manager');
	} // getObjectManager()

	/**
	 * Set value of 'object_manager' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setObjectManager($value) {
		return $this->setColumnValue('object_manager', $value);
	} // setObjectManager()

	/**
	 * Return value of 'object_id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getObjectId() {
		return $this->getColumnValue('object_id');
	} // getObjectId()

	/**
	 * Set value of 'object_id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setObjectId($value) {
		return $this->setColumnValue('object_id', $value);
	} // setObjectId()

	/**
	 * Return value of 'created_on' field
	 *
	 * @access public
	 * @param void
	 * @return DateTimeValue
	 */
	function getCreatedOn() {
		return $this->getColumnValue('created_on');
	} // getCreatedOn()

	/**
	 * Set value of 'created_on' field
	 *
	 * @access public
	 * @param DateTimeValue $value
	 * @return boolean
	 */
	function setCreatedOn($value) {
		return $this->setColumnValue('created_on', $value);
	} // setCreatedOn()

	/**
	 * Return value of 'created_by_id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getCreatedById() {
		return $this->getColumnValue('created_by_id');
	} // getCreatedById()

	/**
	 * Set value of 'created_by_id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setCreatedById($value) {
		return $this->setColumnValue('created_by_id', $value);
	} // setCreatedById()


	/**
	 * Return manager instance
	 *
	 * @access protected
	 * @param void
	 * @return WorkspaceObjects
	 */
	function manager() {
		if(!($this->manager instanceof WorkspaceObjects)) $this->manager = WorkspaceObjects::instance();
		return $this->manager;
	} // manager

} // BaseGroupUser

?>