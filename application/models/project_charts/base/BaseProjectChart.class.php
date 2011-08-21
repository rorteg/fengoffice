<?php
/**
 * BaseProjectChart class
 *
 * @author Carlos Palma <chonwil@gmail.com>
 */
abstract class BaseProjectChart extends ProjectDataObject {

	protected $objectTypeIdentifier = 'ch';
	 
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
	 * Return value of 'type_id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getTypeId() {
		return $this->getColumnValue('type_id');
	} // getTypeId()

	/**
	 * Set value of 'type_id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setTypeId($value) {
		return $this->setColumnValue('type_id', $value);
	} // setTypeId()


	/**
	 * Return value of 'display_id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getDisplayId() {
		return $this->getColumnValue('display_id');
	} // getDisplayId()

	/**
	 * Set value of 'display_id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setDisplayId($value) {
		return $this->setColumnValue('display_id', $value);
	} // setDisplayId()

	/**
	 * Return value of 'title' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getTitle() {
		return $this->getColumnValue('title');
	} // getTitle()

	/**
	 * Set value of 'title' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setTitle($value) {
		return $this->setColumnValue('title', $value);
	} // setTitle()


	/**
	 * Return value of 'show_in_project' field
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function getShowInProject() {
		return $this->getColumnValue('show_in_project');
	} // getShowInProject()

	/**
	 * Set value of 'show_in_project' field
	 *
	 * @access public
	 * @param boolean $value
	 * @return boolean
	 */
	function setShowInProject($value) {
		return $this->setColumnValue('show_in_project', $value);
	} // setShowInProject()

	/**
	 * Return value of 'show_in_parents' field
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function getShowInParents() {
		return $this->getColumnValue('show_in_parents');
	} // getShowInParents()

	/**
	 * Set value of 'show_in_parents' field
	 *
	 * @access public
	 * @param boolean $value
	 * @return boolean
	 */
	function setShowInParents($value) {
		return $this->setColumnValue('show_in_parents', $value);
	} // setShowInParents()

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

	/** Return value of 'trashed_on' field
	 *
	 * @access public
	 * @param void
	 * @return DateTimeValue
	 */
	function getTrashedOn() {
		return $this->getColumnValue('trashed_on');
	} // getTrashedOn()

	/**
	 * Set value of 'trashed_on' field
	 *
	 * @access public
	 * @param DateTimeValue $value
	 * @return boolean
	 */
	function setTrashedOn($value) {
		return $this->setColumnValue('trashed_on', $value);
	} // setTrashedOn()

	/**
	 * Return value of 'trashed_by_id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getTrashedById() {
		return $this->getColumnValue('trashed_by_id');
	} // getTrashedById()

	/**
	 * Set value of 'trashed_by_id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setTrashedById($value) {
		return $this->setColumnValue('trashed_by_id', $value);
	} // setTrashedById()

    /**
    * Return value of 'archived_by_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getArchivedById() {
      return $this->getColumnValue('archived_by_id');
    } // getArchivedById()
    
    /**
    * Set value of 'archived_by_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setArchivedById($value) {
      return $this->setColumnValue('archived_by_id', $value);
    } // setArchivedById()
	
    /** Return value of 'archived_on' field
    *
    * @access public
    * @param void
    * @return DateTimeValue 
    */
    function getArchivedOn() {
      return $this->getColumnValue('archived_on');
    } // getArchivedOn()
    
    /**
    * Set value of 'archived_on' field
    *
    * @access public   
    * @param DateTimeValue $value
    * @return boolean
    */
    function setArchivedOn($value) {
      return $this->setColumnValue('archived_on', $value);
    } // setArchivedOn() 
    
	/**
	 * Return manager instance
	 *
	 * @access protected
	 * @param void
	 * @return ProjectCharts
	 */
	function manager() {
		if(!($this->manager instanceof ProjectCharts)) $this->manager = ProjectCharts::instance();
		return $this->manager;
	} // manager

} // BaseProjectChart
?>