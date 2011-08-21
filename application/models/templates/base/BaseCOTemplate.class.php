<?php

/**
 * BaseCOTemplate class
 *
 * @author Ignacio de Soto <ignacio.desoto@gmail.com>
 */
abstract class BaseCOTemplate extends ProjectDataObject {

	protected $objectTypeIdentifier = 'te';
	
	// -------------------------------------------------------
	//  Access methods
	// -------------------------------------------------------

	/**
	 * Return value of 'id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getId() {
		return $this->getColumnValue('id');
	} // getId()

	/**
	 * Set value of 'id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setId($value) {
		return $this->setColumnValue('id', $value);
	} // setId()


	/**
	 * Return value of 'name' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getName() {
		return $this->getColumnValue('name');
	} // getName()

	/**
	 * Set value of 'name' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setName($value) {
		return $this->setColumnValue('name', $value);
	} // setName()

	/**
	 * Return value of 'description' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getDescription() {
		return $this->getColumnValue('description');
	} // getDescription()

	/**
	 * Set value of 'description' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setDescription($value) {
		return $this->setColumnValue('description', $value);
	} // setDescription()

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
	 * Return value of 'updated_on' field
	 *
	 * @access public
	 * @param void
	 * @return DateTimeValue
	 */
	function getUpdatedOn() {
		return $this->getColumnValue('updated_on');
	} // getUpdatedOn()

	/**
	 * Set value of 'updated_on' field
	 *
	 * @access public
	 * @param DateTimeValue $value
	 * @return boolean
	 */
	function setUpdatedOn($value) {
		return $this->setColumnValue('updated_on', $value);
	} // setUpdatedOn()

	/**
	 * Return value of 'updated_by_id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getUpdatedById() {
		return $this->getColumnValue('updated_by_id');
	} // getUpdatedById()

	/**
	 * Set value of 'updated_by_id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setUpdatedById($value) {
		return $this->setColumnValue('updated_by_id', $value);
	} // setUpdatedById()


	/**
	 * Return manager instance
	 *
	 * @access protected
	 * @param void
	 * @return Templates
	 */
	function manager() {
		if(!($this->manager instanceof COTemplates)) $this->manager = COTemplates::instance();
		return $this->manager;
	} // manager

} // BaseCOTemplate

?>