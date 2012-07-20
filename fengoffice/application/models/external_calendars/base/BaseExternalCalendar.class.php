<?php

/**
 * BaseExternalCalendar class
 * Generado el 22/2/2012
 * @author Andres Botta <andres@iugo.com.uy>
 */
abstract class BaseExternalCalendar extends DataObject {

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
	 * @return integer
	 */
	function setId($value) {
		return $this->setColumnValue('id', $value);
	} // setId()
        
	/**
	 * Return value of 'ext_cal_user_id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getExtCalUserId() {
		return $this->getColumnValue('ext_cal_user_id');
	} // getExtCalUserId()

	/**
	 * Set value of 'ext_cal_user_id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return integer
	 */
	function setExtCalUserId($value) {
		return $this->setColumnValue('ext_cal_user_id', $value);
	} // setExtCalUserId()
	 
	/**
	 * Return value of 'calendar_user' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getCalendarUser() {
		return $this->getColumnValue('calendar_user');
	} // getCalendarUser()

	/**
	 * Set value of 'calendar_user' field
	 *
	 * @access public
	 * @param string $value
	 * @return string
	 */
	function setCalendarUser($value) {
		return $this->setColumnValue('calendar_user', $value);
	} // setCalendarUser()
        
        /**
	 * Return value of 'calendar_visibility' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getCalendarVisibility() {
		return $this->getColumnValue('calendar_visibility');
	} // getCalendarVisibility()

	/**
	 * Set value of 'calendar_visibility' field
	 *
	 * @access public
	 * @param string $value
	 * @return string
	 */
	function setCalendarVisibility($value) {
		return $this->setColumnValue('calendar_visibility', $value);
	} // setCalendarVisibility()
        
        /**
	 * Return value of 'calendar_name' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getCalendarName() {
		return $this->getColumnValue('calendar_name');
	} // getCalendarName()

	/**
	 * Set value of 'calendar_name' field
	 *
	 * @access public
	 * @param string $value
	 * @return string
	 */
	function setCalendarName($value) {
		return $this->setColumnValue('calendar_name', $value);
	} // setCalendarName()
        
        /**
	 * Return value of 'id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getCalendarFeng() {
		return $this->getColumnValue('calendar_feng');
	} // getCalendarFeng()

	/**
	 * Set value of 'calendar_feng' field
	 *
	 * @access public
	 * @param integer $value
	 * @return integer
	 */
	function setCalendarFeng($value) {
		return $this->setColumnValue('calendar_feng', $value);
	} // setCalendarFeng()
        
        /**
        * Return manager instance
        *
        * @access protected
        * @param void
        * @return ExternalCalendars 
        */
        function manager() {
          if(!($this->manager instanceof ExternalCalendars)) $this->manager = ExternalCalendars::instance();
          return $this->manager;
        } // manager
} 
?>