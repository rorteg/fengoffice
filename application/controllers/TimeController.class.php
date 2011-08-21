<?php

/**
 * Controller for handling time management
 *
 * @version 1.0
 * @author Carlos Palma <chonwil@gmail.com>
 */
class TimeController extends ApplicationController {

	/**
	 * Construct the TimeController
	 *
	 * @access public
	 * @param void
	 * @return TimeController
	 */
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website');
		if (!can_manage_time(logged_user(),true)) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
		}
	} // __construct
	
	function index() {
		if (!can_manage_time(logged_user(),true)) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$tasksUserId = array_var($_GET,'tu');
		if (is_null($tasksUserId)) {
			$tasksUserId = user_config_option('TM tasks user filter',logged_user()->getId());
		} else if (user_config_option('TM tasks user filter') != $tasksUserId) {
			set_user_config_option('TM tasks user filter', $tasksUserId, logged_user()->getId());
		}
				
		$timeslotsUserId = array_var($_GET,'tsu');
		if (is_null($timeslotsUserId)) {
			$timeslotsUserId = user_config_option('TM user filter',0);
		} else if (user_config_option('TM user filter') != $timeslotsUserId) {
			set_user_config_option('TM user filter', $timeslotsUserId, logged_user()->getId());
		}
				
		$showTimeType = array_var($_GET,'stt');
		if (is_null($showTimeType)) {
			$showTimeType = user_config_option('TM show time type',0);
		} else if (user_config_option('TM show time type') != $showTimeType) {
			set_user_config_option('TM show time type', $showTimeType, logged_user()->getId());
		}
		
		$start = array_var($_GET, 'start', 0);
		$limit = 20;
		
		$tasksUser = Users::findById($tasksUserId);
		$timeslotsUser = Users::findById($timeslotsUserId);	
		
		//Active tasks view
		$tasks = ProjectTasks::getOpenTimeslotTasks($tasksUser,logged_user());
		ProjectTasks::populateData($tasks);
		$tasks_array = array();
		
		//Timeslots view
		$total = 0;
		switch ($showTimeType){
			case 0: //Show only timeslots added through the time panel
				$timeslots = Timeslots::getProjectTimeslots(logged_user()->getWorkspacesQuery(), $timeslotsUser, active_project(), $start, $limit);
				$total = Timeslots::countProjectTimeslots(logged_user()->getWorkspacesQuery(), $timeslotsUser, active_project());
				break;
			case 1: //Show only timeslots added through the tasks panel / tasks
				throw new Error('not yet implemented' . $showTimeType);
				/*if (active_project() instanceof Project){
					$workspacesCSV = active_project()->getAllSubWorkspacesQuery(false,logged_user());
				} else {
					$workspacesCSV = logged_user()->getWorkspacesQuery();
				}
				$taskTimeslots = Timeslots::getTaskTimeslots(null, $timeslotsUser, $workspacesCSV, null , null, null, null,0,20);*/
				//break;
			case 2: //Show timeslots added through both the time and tasks panel / tasks
				throw new Error('not yet implemented' . $showTimeType);
				
				//break;
			default:
				throw new Error('Unrecognised TM show time type: ' . $showTimeType);
		}
		
		
		//Get Users Info
		if (logged_user()->isMemberOfOwnerCompany())
			$users = Users::getAll();
		else
			$users = logged_user()->getCompany()->getUsers();
		
		//Get Companies Info
		if (logged_user()->isMemberOfOwnerCompany())
			$companies = Companies::getCompaniesWithUsers();
		else
			$companies = array(logged_user()->getCompany());
			
		tpl_assign('timeslots', $timeslots);
		tpl_assign('tasks', $tasks);
		tpl_assign('users', $users);
		tpl_assign('start', $start);
		tpl_assign('limit', $limit);
		tpl_assign('total', $total);
		tpl_assign('companies', $companies);
		ajx_set_no_toolbar(true);
	}
	
	function add_project_timeslot(){
		if (!can_manage_time(logged_user(),true)) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		ajx_current("empty");
		$timeslot_data = array_var($_POST, 'timeslot');
		
		try {
			$hoursToAdd = array_var($timeslot_data, 'hours',0);
			if (strpos($hoursToAdd,',') && !strpos($hoursToAdd,'.'))
				$hoursToAdd = str_replace(',','.',$hoursToAdd);
			if (strpos($hoursToAdd,':') && !strpos($hoursToAdd,'.')) {
				$pos = strpos($hoursToAdd,':') + 1;
				$len = strlen($hoursToAdd) - $pos;
				$minutesToAdd = substr($hoursToAdd,$pos,$len);
				if( !strlen($minutesToAdd)<=2 || !strlen($minutesToAdd)>0){
					$minutesToAdd = substr($minutesToAdd,0,2);
				}
				$mins = $minutesToAdd / 60;
				$hours = substr($hoursToAdd, 0, $pos-1);
				$hoursToAdd = $hours + $mins;
			}
				
			if ($hoursToAdd <= 0){
				flash_error(lang('time has to be greater than 0'));
				return;
			}
			
			$startTime = getDateValue(array_var($timeslot_data, 'date'));
			$startTime = $startTime->add('h', 8 - logged_user()->getTimezone());
			$endTime = getDateValue(array_var($timeslot_data, 'date'));
			$endTime = $endTime->add('h', 8 - logged_user()->getTimezone() + $hoursToAdd);
			$timeslot_data['start_time'] = $startTime;
			$timeslot_data['end_time'] = $endTime;
			$timeslot_data['object_id'] = array_var($timeslot_data,'project_id');
			$timeslot_data['object_manager'] = 'Projects';
			$timeslot = new Timeslot();
		
			
			
			//Only admins can change timeslot user
			if (!array_var($timeslot_data,'user_id',false) || !logged_user()->isAdministrator())
				$timeslot_data['user_id'] = logged_user()->getId();
			$timeslot->setFromAttributes($timeslot_data);
			
			/* Billing */
			$user = Users::findById($timeslot_data['user_id']);
			$billing_category_id = $user->getDefaultBillingId();
			$project = Projects::findById(array_var($timeslot_data,'project_id'));
			$timeslot->setBillingId($billing_category_id);
			$hourly_billing = $project->getBillingAmount($billing_category_id);
			$timeslot->setHourlyBilling($hourly_billing);
			$timeslot->setFixedBilling($hourly_billing * $hoursToAdd);
			$timeslot->setIsFixedBilling(false);
			
			DB::beginWork();
			$timeslot->save();
			DB::commit();
			
			$show_billing = can_manage_security(logged_user()) && logged_user()->isAdministrator();
			ajx_extra_data(array("timeslot" => $timeslot->getArrayInfo($show_billing)));
		} catch(Exception $e) {
			DB::rollback();
			flash_error($e->getMessage());
		} // try
	}
	
	function edit_project_timeslot(){
		if (!can_manage_time(logged_user(),true)) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		ajx_current("empty");
		$timeslot_data = array_var($_POST, 'timeslot');
		$timeslot = Timeslots::findById(array_var($timeslot_data,'id',0));
	
		if (!$timeslot instanceof Timeslot){
			flash_error(lang('timeslot dnx'));
			return;
		}
		
		try {
			$hoursToAdd = array_var($timeslot_data, 'hours',0);
			if (strpos($hoursToAdd,',') && !strpos($hoursToAdd,'.'))
				$hoursToAdd = str_replace(',','.',$hoursToAdd);
			if (strpos($hoursToAdd,':') && !strpos($hoursToAdd,'.')) {
				$pos = strpos($hoursToAdd,':') + 1;
				$len = strlen($hoursToAdd) - $pos;
				$minutesToAdd = substr($hoursToAdd,$pos,$len);
				if( !strlen($minutesToAdd)<=2 || !strlen($minutesToAdd)>0){
					$minutesToAdd = substr($minutesToAdd,0,2);
				}
				$mins = $minutesToAdd / 60;
				$hours = substr($hoursToAdd, 0, $pos-1);
				$hoursToAdd = $hours + $mins;
			}

				
			if ($hoursToAdd <= 0){
				flash_error(lang('time has to be greater than 0'));
				return;
			}
			
			$startTime = getDateValue(array_var($timeslot_data, 'date'));
			$startTime = $startTime->add('h', 8 - logged_user()->getTimezone());
			$endTime = getDateValue(array_var($timeslot_data, 'date'));
			$endTime = $endTime->add('h', 8 - logged_user()->getTimezone() + $hoursToAdd);
			$timeslot_data['start_time'] = $startTime;
			$timeslot_data['end_time'] = $endTime;
			$timeslot_data['object_id'] = array_var($timeslot_data,'project_id');
			$timeslot_data['object_manager'] = 'Projects';
			
			//Only admins can change timeslot user
			if (array_var($timeslot_data,'user_id',false) && !logged_user()->isAdministrator())
				$timeslot_data['user_id'] = $timeslot->getUserId();
				
			$timeslot->setFromAttributes($timeslot_data);
			
			/* Billing */
			$user = Users::findById($timeslot_data['user_id']);
			$billing_category_id = $user->getDefaultBillingId();
			$project = Projects::findById(array_var($timeslot_data,'project_id'));
			$timeslot->setBillingId($billing_category_id);
			$hourly_billing = $project->getBillingAmount($billing_category_id);
			$timeslot->setHourlyBilling($hourly_billing);
			$timeslot->setFixedBilling($hourly_billing * $hoursToAdd);
			$timeslot->setIsFixedBilling(false);
			
			DB::beginWork();
			$timeslot->save();
			DB::commit();
			
			ajx_extra_data(array("timeslot" => $timeslot->getArrayInfo(true)));
		} catch(Exception $e) {
			DB::rollback();
			flash_error($e->getMessage());
		} // try
	}
	
	function delete_project_timeslot(){
		if (!can_manage_time(logged_user(),true)) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		ajx_current("empty");
		$timeslot = Timeslots::findById(get_id());
		
		if (!$timeslot instanceof Timeslot){
			flash_error(lang('timeslot dnx'));
			return;
		}
		
		if (!$timeslot->canDelete(logged_user())){
			flash_error(lang('no access permissions'));
			return;
		}
		
		try {
			DB::beginWork();
			$timeslot->delete();
			DB::commit();
			
			ajx_extra_data(array("timeslotId" => get_id()));
		} catch(Exception $e) {
			DB::rollback();
			flash_error($e->getMessage());
		} // try
	}

} // TimeController

?>