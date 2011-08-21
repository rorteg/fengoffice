<?php

  /**
  * Report class
  *
  * @author Pablo Kamil <pablokam@gmail.com>
  */
  class Report extends BaseReport {
      
    /**
    * Construct the object
    *
    * @param void
    * @return null
    */
    function __construct() {
      parent::__construct();
    } // __construct
    
    /**
	 * Validate before save
	 *
	 * @access public
	 * @param array $errors
	 * @return null
	 */
	function validate(&$errors) {
		if(trim($this->getName()) == ''){
			$errors[] = lang('report name required');
		}
		if(trim($this->getObjectType()) == ''){
			$errors[] = lang('report object type required');
		}
	} // validate
	
	/**
	 * Check CAN_MANAGE_REPORTS permission
	 *
	 * @access public
	 * @param User $user
	 * @return boolean
	 */
	function canManage(User $user) {
		return can_manage_reports($user);
	} // canManage

	/**
	 * Returns true if $user can access this report
	 *
	 * @param User $user
	 * @return boolean
	 */
	function canView(User $user) {
		return can_read($user,$this);
	} // canView

	/**
	 * Check if specific user can add reports
	 *
	 * @access public
	 * @param User $user
	 * @param Project $project
	 * @return booelean
	 */
	function canAdd(User $user) {
		return $this->canManage($user);
	} // canAdd

	/**
	 * Check if specific user can edit this report
	 *
	 * @access public
	 * @param User $user
	 * @return boolean
	 */
	function canEdit(User $user) {
		return $this->canManage($user);
	} // canEdit

	/**
	 * Check if specific user can delete this report
	 *
	 * @access public
	 * @param User $user
	 * @return boolean
	 */
	function canDelete(User $user) {
		return $this->canManage($user);
	} // canDelete
    
   
  } // Report

?>