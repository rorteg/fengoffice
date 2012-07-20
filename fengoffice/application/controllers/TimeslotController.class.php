<?php

/**
 * Handle all timeslot related requests
 *
 * @version 1.0
 * @author Carlos Palma <chonwil@gmail.com>
 */
class TimeslotController extends ApplicationController {

	/**
	 * Construct the TimeslotController
	 *
	 * @access public
	 * @param void
	 * @return TimeslotController
	 */
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website');
	} // __construct

	/**
	 * Open timeslot
	 *
	 * @param void
	 * @return null
	 */
	function open() {
		
		$this->setTemplate('add_timeslot');

		$object_id = get_id('object_id');

		$object = Objects::findObject($object_id);
		if(!($object instanceof ContentDataObject) || !($object->canAddTimeslot(logged_user()))) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}

		$timeslot = new Timeslot();
		$dt = DateTimeValueLib::now();
		$timeslot->setStartTime($dt);
		$timeslot->setContactId(logged_user()->getId());
		$timeslot->setRelObjectId($object_id);
		
		try{
			DB::beginWork();
			$timeslot->save();
			
			/*	dont add timeslots to members, members are taken from the related object
			$object_controller = new ObjectController();
			$object_controller->add_to_members($timeslot, $object->getMemberIds());
			*/
			ApplicationLogs::createLog($timeslot, ApplicationLogs::ACTION_OPEN);
			DB::commit();
			
			flash_success(lang('success open timeslot'));
			ajx_current("reload");
		} catch (Exception $e) {
			DB::rollback();
			ajx_current("empty");
			flash_error($e->getMessage());
		}
	} 
	
	function add_timespan() {
	
		$object_id = get_id('object_id');
		
		$object = Objects::findObject($object_id);
		if(!($object instanceof ContentDataObject) || !($object->canAddTimeslot(logged_user()))) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}

		$timeslot_data = array_var($_POST, 'timeslot');
		$hours = array_var($timeslot_data, 'hours');
                $minutes = array_var($timeslot_data, 'minutes');
		
		if (strpos($hours,',') && !strpos($hours,'.')) {
			$hours = str_replace(',','.',$hours);
		}
		
		if($minutes){
			$min = str_replace('.','',($minutes/6));
			$hours = $hours + ("0.".$min);
		}
		
		$timeslot = new Timeslot();
		$dt = DateTimeValueLib::now();
		$dt2 = DateTimeValueLib::now();
		$timeslot->setEndTime($dt);
		$dt2 = $dt2->add('h', -$hours);                
		$timeslot->setStartTime($dt2);
		$timeslot->setDescription(array_var($timeslot_data, 'description'));
		$timeslot->setContactId(array_var($timeslot_data, 'contact_id', logged_user()->getId()));
		$timeslot->setRelObjectId($object_id);
		
		$billing_category_id = logged_user()->getDefaultBillingId();
		$bc = BillingCategories::findById($billing_category_id);
		if ($bc instanceof BillingCategory) {
			$timeslot->setBillingId($billing_category_id);
			$hourly_billing = $bc->getDefaultValue();
			$timeslot->setHourlyBilling($hourly_billing);
			$timeslot->setFixedBilling($hourly_billing * $hoursToAdd);
			$timeslot->setIsFixedBilling(false);
		}
		
		try{
			DB::beginWork();
			$timeslot->save();
		/*	dont add timeslots to members, members are taken from the related object
			$object_controller = new ObjectController();
			$object_controller->add_to_members($timeslot, $object->getMemberIds());
		*/	
			ApplicationLogs::createLog($timeslot, ApplicationLogs::ACTION_OPEN);
			                        
			$task = ProjectTasks::findById($object_id);
			if($task->getTimeEstimate() > 0){
				$timeslots = $task->getTimeslots();
				if (count($timeslots) == 1){
					$task->setPercentCompleted(0);
				}
				$timeslot_percent = round(($hours * 100) / ($task->getTimeEstimate() / 60));
				$total_percentComplete = $timeslot_percent + $task->getPercentCompleted();
				if ($total_percentComplete < 0) $total_percentComplete = 0;
				$task->setPercentCompleted($total_percentComplete);
				$task->save();

				$this->notifier_work_estimate($task);
			}
			
			DB::commit();
			
			flash_success(lang('success create timeslot'));
			ajx_current("reload");
		} catch (Exception $e) {
			DB::rollback();
			ajx_current("empty");
			flash_error($e->getMessage());
		}
	} 
	
	/*
	 * Close timeslot
	 *
	 * @param void
	 * @return null
	 */
	function close() {
		
		$this->setTemplate('add_timeslot');

		$timeslot = Timeslots::findById(get_id());
		if(!($timeslot instanceof Timeslot)) {
			flash_error(lang('timeslot dnx'));
			ajx_current("empty");
			return;
		}

		$object = $timeslot->getRelObject();
		if(!($object instanceof ContentDataObject)) {
			flash_error(lang('object dnx'));
			ajx_current("empty");
			return;
		}
		
		if(!($object->canAddTimeslot(logged_user()))) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$timeslot_data = array_var($_POST, 'timeslot');
		$timeslot->close();
		$timeslot->setFromAttributes($timeslot_data);
		
		/* Billing */
/*		$billing_category_id = logged_user()->getDefaultBillingId();
		$project = $object->getProject();
		if ($billing_category_id) {
			$timeslot->setBillingId($billing_category_id);
			$hourly_billing = $project->getBillingAmount($billing_category_id);
			$timeslot->setHourlyBilling($hourly_billing);
			$timeslot->setFixedBilling($hourly_billing * $timeslot->getMinutes() / 60);
			$timeslot->setIsFixedBilling(false);
		}
*/
		try{
			DB::beginWork();
			if (array_var($_GET, 'cancel') && array_var($_GET, 'cancel') == 'true')
				$timeslot->delete();
			else
				$timeslot->save();
			ApplicationLogs::createLog($timeslot, ApplicationLogs::ACTION_CLOSE);
			DB::commit();
				
			if (array_var($_GET, 'cancel') && array_var($_GET, 'cancel') == 'true')
				flash_success(lang('success cancel timeslot'));
			else
				flash_success(lang('success close timeslot'));
				
			ajx_current("reload");
		} catch (Exception $e) {
			DB::rollback();
			ajx_current("empty");
			flash_error($e->getMessage());
		}
	} 
	
	function pause() {
		
		ajx_current("empty");

		$timeslot = Timeslots::findById(get_id());
		if(!($timeslot instanceof Timeslot)) {
			flash_error(lang('timeslot dnx'));
			return;
		}

		$object = $timeslot->getRelObject();
		if(!($object instanceof ContentDataObject)) {
			flash_error(lang('object dnx'));
			return;
		}
		
		if(!($object->canAddTimeslot(logged_user()))) {
			flash_error(lang('no access permissions'));
			return;
		}
		
		if(!($timeslot->canEdit(logged_user()))) {
			flash_error(lang('no access permissions'));
			return;
		}
		
		try{
			DB::beginWork();
			$timeslot->pause();
			$timeslot->save();
			DB::commit();
				
			flash_success(lang('success pause timeslot'));
			ajx_current("reload");
		} catch (Exception $e) {
			DB::rollback();
			flash_error($e->getMessage());
		}
	} 
	
	function resume() {
		
		ajx_current("empty");

		$timeslot = Timeslots::findById(get_id());
		if(!($timeslot instanceof Timeslot)) {
			flash_error(lang('timeslot dnx'));
			return;
		}

		$object = $timeslot->getRelObject();
		if(!($object instanceof ContentDataObject)) {
			flash_error(lang('object dnx'));
			return;
		}
		
		if(!($object->canAddTimeslot(logged_user()))) {
			flash_error(lang('no access permissions'));
			return;
		}
		
		if(!($timeslot->canEdit(logged_user()))) {
			flash_error(lang('no access permissions'));
			return;
		}
		
		try{
			DB::beginWork();
			$timeslot->resume();
			$timeslot->save();
			DB::commit();
				
			flash_success(lang('success pause timeslot'));
			ajx_current("reload");
		} catch (Exception $e) {
			DB::rollback();
			flash_error($e->getMessage());
		}
	} 

	/**
	 * Edit timeslot
	 *
	 * @param void
	 * @return null
	 */
	function edit() {
		
		$this->setTemplate('add_timeslot');
		
		$timeslot = Timeslots::findById(get_id());
		if(!($timeslot instanceof Timeslot)) {
			flash_error(lang('timeslot dnx'));
			ajx_current("empty");
			return;
		}

		$object = $timeslot->getRelObject();
		if(!($object instanceof ContentDataObject)) {
			flash_error(lang('object dnx'));
			ajx_current("empty");
			return;
		}
		
		if(!($object->canAddTimeslot(logged_user()))) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		
		if(!($timeslot->canEdit(logged_user()))) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		
		$timeslot_data = array_var($_POST, 'timeslot');
		if(!is_array($timeslot_data)) {
			$timeslot_data = array(
				'contact_id' => $timeslot->getContactId(),
				'description' => $timeslot->getDescription(),
          		'start_time' => $timeslot->getStartTime(),
          		'end_time' => $timeslot->getEndTime(),
          		'is_fixed_billing' => $timeslot->getIsFixedBilling(),
          		'hourly_billing' => $timeslot->getHourlyBilling(),
          		'fixed_billing' => $timeslot->getFixedBilling()
			);
		}

		tpl_assign('timeslot_form_object', $object);
		tpl_assign('timeslot', $timeslot);
		tpl_assign('timeslot_data', $timeslot_data);
		tpl_assign('show_billing', BillingCategories::count() > 0);
		
		if(is_array(array_var($_POST, 'timeslot'))) {
			try {
				$this->percent_complete_delete($timeslot);
				
				$timeslot->setContactId(array_var($timeslot_data, 'contact_id', logged_user()->getId()));
				$timeslot->setDescription(array_var($timeslot_data, 'description'));
       			
				$st = getDateValue(array_var($timeslot_data, 'start_value'),DateTimeValueLib::now());
				$st->setHour(array_var($timeslot_data, 'start_hour'));
				$st->setMinute(array_var($timeslot_data, 'start_minute'));
				
				$et = getDateValue(array_var($timeslot_data, 'end_value'),DateTimeValueLib::now());
				$et->setHour(array_var($timeslot_data, 'end_hour'));
				$et->setMinute(array_var($timeslot_data, 'end_minute'));
				
				$st = new DateTimeValue($st->getTimestamp() - logged_user()->getTimezone() * 3600);
				$et = new DateTimeValue($et->getTimestamp() - logged_user()->getTimezone() * 3600);
                                $timeslot->setStartTime($st);
				$timeslot->setEndTime($et);
				
				if ($timeslot->getStartTime() > $timeslot->getEndTime()){
					flash_error(lang('error start time after end time'));
					ajx_current("empty");
					return;
				}
				
				$seconds = array_var($timeslot_data,'subtract_seconds',0);
				$minutes = array_var($timeslot_data,'subtract_minutes',0);
				$hours = array_var($timeslot_data,'subtract_hours',0);
				
				$subtract = $seconds + 60 * $minutes + 3600 * $hours;
				if ($subtract < 0){
					flash_error(lang('pause time cannot be negative'));
					ajx_current("empty");
					return;
				}
				
				$testEndTime = new DateTimeValue($timeslot->getEndTime()->getTimestamp());
				
				$testEndTime->add('s',-$subtract);
				
				if ($timeslot->getStartTime() > $testEndTime){
					flash_error(lang('pause time cannot exceed timeslot time'));
					ajx_current("empty");
					return;
				}
				
				$timeslot->setSubtract($subtract);				
				
				if ($timeslot->getUser()->getDefaultBillingId()) {
					$timeslot->setIsFixedBilling(array_var($timeslot_data,'is_fixed_billing',false));
					$timeslot->setHourlyBilling(array_var($timeslot_data,'hourly_billing',0));
					if ($timeslot->getIsFixedBilling()){
						$timeslot->setFixedBilling(array_var($timeslot_data,'fixed_billing',0));
					} else {
						$timeslot->setFixedBilling($timeslot->getHourlyBilling() * $timeslot->getMinutes() / 60);
					}
					if ($timeslot->getBillingId() == 0 && ($timeslot->getHourlyBilling() > 0 || $timeslot->getFixedBilling() > 0)){
						$timeslot->setBillingId($timeslot->getUser()->getDefaultBillingId());
					}
				}
				
				DB::beginWork();
				$timeslot->save();
				
				$timeslot_time = ($timeslot->getEndTime()->getTimestamp() - ($timeslot->getStartTime()->getTimestamp() + $timeslot->getSubtract())) / 3600;
				$task = ProjectTasks::findById($timeslot->getRelObjectId());
				if($task->getTimeEstimate() > 0){
					$timeslot_percent = round(($timeslot_time * 100) / ($task->getTimeEstimate() / 60));
					$total_percentComplete = $timeslot_percent + $task->getPercentCompleted();
					if ($total_percentComplete < 0) $total_percentComplete = 0;
					$task->setPercentCompleted($total_percentComplete);
					$task->save();
				}
				
				$this->notifier_work_estimate($task);
				
				DB::commit();

				flash_success(lang('success edit timeslot'));
				ajx_current("back");
			} catch(Exception $e) {
				DB::rollback();
				Logger::log($e->getTraceAsString());
				flash_error(lang('error edit timeslot').": ".$e->getMessage());
				ajx_current("empty");
			}
		}
	} // edit

	/**
	 * Delete specific timeslot
	 *
	 * @param void
	 * @return null
	 */
	function delete() {
		
		$timeslot = Timeslots::findById(get_id());
		if(!($timeslot instanceof Timeslot)) {
			flash_error(lang('timeslot dnx'));
			ajx_current("empty");
			return;
		}

		$object = $timeslot->getRelObject();
		if(!($object instanceof ContentDataObject)) {
			flash_error(lang('object dnx'));
			ajx_current("empty");
			return;
		}

		if(trim($object->getObjectUrl())) $redirect_to = $object->getObjectUrl();

		if(!$timeslot->canDelete(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
                     
		try {
			$timeslot_delete = $timeslot;      
                        
			DB::beginWork();
			$timeslot->delete();
			ApplicationLogs::createLog($timeslot, ApplicationLogs::ACTION_DELETE);
			$object->onDeleteTimeslot($timeslot);
			DB::commit();
			
			$this->percent_complete_delete($timeslot_delete);
			
			flash_success(lang('success delete timeslot'));
			ajx_current("reload");
		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error delete timeslot'));
			ajx_current("empty");
		}

	} // delete
        
        function percent_complete_delete($time_slot){
            $timeslot_time = ($time_slot->getEndTime()->getTimestamp() - ($time_slot->getStartTime()->getTimestamp() + $time_slot->getSubtract())) / 3600;
            $task = ProjectTasks::findById($time_slot->getRelObjectId());
            if($task->getTimeEstimate() > 0){
                $timeslot_percent = round(($timeslot_time * 100) / ($task->getTimeEstimate() / 60));
                $total_percentComplete = $task->getPercentCompleted() - $timeslot_percent;
				if ($total_percentComplete < 0) $total_percentComplete = 0;
                $task->setPercentCompleted($total_percentComplete);
                $task->save();
            }            
        }
        
        function notifier_work_estimate($task){
            if($task->getPercentCompleted() > 100){                
                Notifier::workEstimate($task);
            }
        }

} // TimeslotController

?>