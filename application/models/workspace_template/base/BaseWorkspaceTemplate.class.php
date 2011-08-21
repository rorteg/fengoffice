<?php

/**
 * BaseWorkspaceTemplate class
 *
 * @author Ignacio de Soto
 */
abstract class BaseWorkspaceTemplate extends DataObject {

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
	 * Return value of 'Template_id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getTemplateId() {
		return $this->getColumnValue('template_id');
	} // getTemplateId()

	/**
	 * Set value of 'Template_id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setTemplateId($value) {
		return $this->setColumnValue('template_id', $value);
	} // setTemplateId()

	/**
	 * Return value of 'include_subws' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getInludeSubWs() {
		return $this->getColumnValue('include_subws');
	} // getInludeSubWs()

	/**
	 * Set value of 'include_subws' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setInludeSubWs($value) {
		return $this->setColumnValue('include_subws', $value);
	} // setInludeSubWs()

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
	 * @return WorkspaceTemplates
	 */
	function manager() {
		if(!($this->manager instanceof WorkspaceTemplates)) $this->manager = WorkspaceTemplates::instance();
		return $this->manager;
	} // manager

} // BaseGroupUser

?>