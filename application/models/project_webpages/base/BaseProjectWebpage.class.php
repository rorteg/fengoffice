<?php

  /**
  * BaseProjectWebpage class
  *
  * @author Carlos Palma <chonwil@gmail.com>
  */
  abstract class BaseProjectWebpage extends ProjectDataObject {
  
  	protected $objectTypeIdentifier = 'wp';
  
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
    } // getWebpageId()
    
    /**
    * Set value of 'id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setId($value) {
      return $this->setColumnValue('id', $value);
    } // setWebpageId() 
    
    /**
    * Return value of 'url' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getUrl() {
      return $this->getColumnValue('url');
    } // getUrl()
    
    /**
    * Set value of 'url' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setUrl($value) {
      return $this->setColumnValue('url', $value);
    } // setUrl()
    
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
	} // getupdatedOn()

	/**
	 * Set value of 'updated_on' field
	 *
	 * @access public
	 * @param DateTimeValue $value
	 * @return boolean
	 */
	function setUpdatedOn($value) {
		return $this->setColumnValue('updated_on', $value);
	} // setupdatedOn()

	/**
	 * Return value of 'updated_by_id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getUpdatedById() {
		return $this->getColumnValue('updated_by_id');
	} // getupdatedById()

	/**
	 * Set value of 'updated_by_id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setUpdatedById($value) {
		return $this->setColumnValue('updated_by_id', $value);
	} // setupdatedById()
    
	
	/**
	* Return value of 'is_private' field
	*
	* @access public
	* @param void
	* @return boolean 
	*/
	function getIsPrivate() {
	  return $this->getColumnValue('is_private');
	} // getIsPrivate()

	/**
	* Set value of 'is_private' field
	*
	* @access public   
	* @param int $value
	* @return boolean
	*/
	function setIsPrivate($value) {
	  return $this->setColumnValue('is_private', $value);
	} // setIsPrivate()	
	
    
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return ProjectWebpages
    */
    function manager() {
      if(!($this->manager instanceof ProjectWebpages)) $this->manager = ProjectWebpages::instance();
      return $this->manager;
    } // manager

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
    
    
  } // BaseProjectWebpage 

?>