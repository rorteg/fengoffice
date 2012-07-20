<?php

/***************************************************************************
 *	Authors:
 *   - Reece Pegues
 *   - Feng Office Development Team
 * 	 - Sadysta (fengoffice.com/web/forums) - iCal Server
 *   - Ras2000 (fengoffice.com/web/forums) - Calendar starting on Mon or Sun 	 
 ***************************************************************************/

require_once ROOT.'/environment/classes/event/CalFormatUtilities.php';
/**
* Controller that is responsible for handling project events related requests
*
* @version 1.0
* @author Marcos Saiz <marcos.saiz@gmail.com>
* @adapted from Reece calendar <http://reececalendar.sourceforge.net/>.
* Acknowledgements at the bottom.
*/

class EventController extends ApplicationController {

	/**
	* Construct the EventController
	*
	* @access public
	* @param void
	* @return EventController
	*/
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website');
		$this->addHelper('calendar');
	} // __construct
     
	
	function init() {
		require_javascript("og/CalendarManager.js");
		ajx_current("panel", "events", null, null, true);
		ajx_replace(true);
	}
	
	/**
	* Show events index page (list recent events)
	*
	* @param void
	* @return null
	*/
	function index($view_type = null, $user_filter = null, $status_filter = null, $task_filter = null) {
		ajx_set_no_toolbar(true);
		ajx_replace(true);
				 
		$this->getActualDateToShow($day, $month, $year);
		
		if ($view_type == null)
			$this->getUserPreferences($view_type, $user_filter, $status_filter, $task_filter);
				  
		$this->setTemplate('calendar');
		$this->setViewVariables($view_type, $user_filter, $status_filter, $task_filter);
	}
	
	function registerInvitations($data, $event, $clear=true) {
		if ($clear) $event->clearInvitations();
		// Invitations
		$invitations = array_var($data, 'users_to_invite', array());
		foreach ($invitations as $id => $assist) {
			$conditions = array('event_id' => $event->getId(), 'contact_id' => $id);
			//insert only if not exists 
			if (EventInvitations::findById($conditions) == null) {
				$invitation = new EventInvitation();
				$invitation->setEventId($event->getId());
				$invitation->setContactId($id);
				$invitation->setInvitationState(logged_user() instanceof Contact && logged_user()->getId() == $id ? 1 : 0);
				$invitation->save();
				if (array_var($data, 'subscribe_invited', false) && is_array(array_var($_POST, 'subscribers'))) {
					$_POST['subscribers']['user_' . $id] = 'checked';
				}
			}
		}
		// Delete non checked invitations
		$previuos_invitations = EventInvitations::findAll(array('conditions' => '`event_id` = ' . $event->getId()));
		foreach ($previuos_invitations as $pinv) {
			if (!array_key_exists($pinv->getContactId(), $invitations)) $pinv->delete();
		}
	}
	
	function change_invitation_state($attendance = null, $event_id = null, $user_id = null) {
		$from_post_get = $attendance == null || $event_id == null;
		// Take variables from post
		if ($attendance == null) $attendance = array_var($_POST, 'event_attendance');
		if ($event_id == null) $event_id = array_var($_POST, 'event_id');
		if ($user_id == null) $user_id = array_var($_POST, 'user_id');
		
		// If post is empty, take variables from get
		if ($attendance == null) $attendance = array_var($_GET, 'at');
		if ($event_id == null) $event_id = array_var($_GET, 'e');
		if ($user_id == null) $user_id = array_var($_GET, 'u');
		
		if ($attendance == null || $event_id == null) {
			flash_error('Missing parameters');
			ajx_current("back");
		} else {
			$conditions = array('conditions' => "`event_id` = " . DB::escape($event_id) . " AND `contact_id` = ". DB::escape($user_id));
			$inv = EventInvitations::findOne($conditions);
			$conditions_all = array('conditions' => "`event_id` = " . DB::escape($event_id));
			$invs = EventInvitations::findAll($conditions_all);			
			if ($inv != null) {
				if ($inv->getContactId() != logged_user()->getId()) {
					flash_error(lang('no access permissions'));					
					self::view_calendar();
					return;
				}
				try {
					DB::beginWork();
					$inv->setInvitationState($attendance);
					$inv->save();
					DB::commit();
				} catch (Exception $e) {
					DB::rollback();
					flash_error($e->getMessage());
					ajx_current("empty");
					return;
				}
			}
			if ($from_post_get) {
				// Notify creator (only when invitation is accepted or declined)
				$event = ProjectEvents::findById(array('id' => $event_id));
				if ($inv->getInvitationState() == 1 || $inv->getInvitationState() == 2) {
					$user = Contacts::findById(array('id' => $user_id));
					session_commit();
					Notifier::notifEventAssistance($event, $inv, $user, $invs);
					if ($inv->getInvitationState() == 1) flash_success(lang('invitation accepted'));
					else flash_success(lang('invitation rejected'));
				} else {
					flash_success(lang('success edit event', $event instanceof ProjectEvent ? clean($event->getObjectName()) : ''));
				}
				if (array_var($_GET, 'at')) {
					self::view_calendar();
				} else {
					ajx_current("reload");
				}
			}
		}
	}
	
	
	function getData($event_data){
		// get the day
			if (array_var($event_data, 'start_value') != '') {
				$date_from_widget = array_var($event_data, 'start_value');
				$dtv = getDateValue($date_from_widget);
				$day = $dtv->getDay();
	       		$month = $dtv->getMonth();
	       		$year = $dtv->getYear();
				
			} else {
				$month = isset($event_data['month'])?$event_data['month']:date('n', DateTimeValueLib::now()->getTimestamp());
				$day = isset($event_data['day'])?$event_data['day']:date('j', DateTimeValueLib::now()->getTimestamp());
				$year = isset($event_data['year'])?$event_data['year']:date('Y', DateTimeValueLib::now()->getTimestamp());
			}
       		
			if (array_var($event_data, 'start_time') != '') {
				$this->parseTime(array_var($event_data, 'start_time'), $hour, $minute);
			} else {
				$hour = array_var($event_data, 'hour');
	       		$minute = array_var($event_data, 'minute');
				if(array_var($event_data, 'pm') == 1) $hour += 12;
			}
			if (array_var($event_data, 'type_id') == 2 && $hour == 24) $hour = 23;
			
			// repeat defaults
			$repeat_d = 0;
			$repeat_m = 0;
			$repeat_y = 0;
			$repeat_h = 0;
			$repeat_h_params = array('dow' => 0, 'wnum' => 0, 'mjump' => 0);
			$rend = '';		
			// get the options
			$forever = 0;
			$jump = array_var($event_data,'occurance_jump');
			
			if(array_var($event_data,'repeat_option') == 1) $forever = 1;
			elseif(array_var($event_data,'repeat_option') == 2) $rnum = array_var($event_data,'repeat_num');
			elseif(array_var($event_data,'repeat_option') == 3) $rend = getDateValue(array_var($event_data,'repeat_end'));
			// verify the options above are valid
			if(isset($rnum) && $rnum !="") {
				if(!is_numeric($rnum) || $rnum < 1 || $rnum > 1000) {
					throw new Exception(CAL_EVENT_COUNT_ERROR);
				}
			} else $rnum = 0;
			if($jump != ""){
				if(!is_numeric($jump) || $jump < 1 || $jump > 1000) {
					throw new Exception(CAL_REPEAT_EVERY_ERROR);
				}
			} else $jump = 1;
			
		
		    // check for repeating options
			// 1=repeat once, 2=repeat daily, 3=weekly, 4=monthy, 5=yearly, 6=holiday repeating
			$oend = null;
			switch(array_var($event_data,'occurance')){
				case "1":
					$forever = 0;
					$repeat_d = 0;
					$repeat_m = 0;
					$repeat_y = 0;
					$repeat_h = 0;
					break;
				case "2":
					$repeat_d = $jump;
					if(isset($forever) && $forever == 1) $oend = null;
					else $oend = $rend;
					break;
				case "3":
					$repeat_d = 7 * $jump;
					if(isset($forever) && $forever == 1) $oend = null;
					else $oend = $rend;
					break;
				case "4":
					$repeat_m = $jump;
					if(isset($forever) && $forever == 1) $oend = null;
					else $oend = $rend;
					break;
				case "5":
					$repeat_y = $jump;
					if(isset($forever) && $forever == 1) $oend = null;
					else $oend = $rend;
					break;
				case "6":
					$repeat_h = 1;
					$repeat_h_params = array(
						'dow' => array_var($event_data, 'repeat_dow'), 
						'wnum' => array_var($event_data, 'repeat_wnum'),
						'mjump' => array_var($event_data, 'repeat_mjump'),
					);
					break;
			}
			$repeat_number = $rnum;
			
		 	// get duration
			$durationhour = array_var($event_data,'durationhour');
			$durationmin = array_var($event_data,'durationmin');
			
			// get event type:  2=full day, 3=time/duratin not specified, 4=time not specified
			$typeofevent = array_var($event_data,'type_id');
			if(!is_numeric($typeofevent) OR ($typeofevent!=1 AND $typeofevent!=2 AND $typeofevent!=3)) $typeofevent = 1;

			if ($durationhour == 0 && $durationmin < 15 && $typeofevent != 2) {
				throw new Exception(lang('duration must be at least 15 minutes'));
			}
				
			// calculate timestamp and durationstamp
			$dt_start = new DateTimeValue(mktime($hour, $minute, 0, $month, $day, $year) - logged_user()->getTimezone() * 3600);
			$timestamp = $dt_start->format('Y-m-d H:i:s');
			$dt_duration = DateTimeValueLib::make($dt_start->getHour() + $durationhour, $dt_start->getMinute() + $durationmin, 0, $dt_start->getMonth(), $dt_start->getDay(), $dt_start->getYear());
			$durationstamp = $dt_duration->format('Y-m-d H:i:s');
			
			// organize the data expected by the query function
			$data = array();
			$data['repeat_num'] = $rnum;
			$data['repeat_h'] = $repeat_h;
			$data['repeat_dow'] = $repeat_h_params['dow'];
			$data['repeat_wnum'] = $repeat_h_params['wnum'];
			$data['repeat_mjump'] = $repeat_h_params['mjump'];
			$data['repeat_d'] = $repeat_d;
			$data['repeat_m'] = $repeat_m;
			$data['repeat_y'] = $repeat_y;
			$data['repeat_forever'] = $forever;
			$data['repeat_end'] =  $oend;
			$data['start'] = $timestamp;
			$data['name'] =  array_var($event_data,'name');
			$data['description'] =  array_var($event_data,'description');
			$data['type_id'] = $typeofevent;
			$data['duration'] = $durationstamp;
			
			$data['users_to_invite'] = array();
			// owner user always is invited and confirms assistance (only for popup quick add)
			if (array_var($_POST, 'popup')) $data['users_to_invite'][logged_user()->getId()] = 1; 

			$compstr = 'invite_user_';
			foreach ($event_data as $k => $v) {
				if (str_starts_with($k, $compstr) && ($v == 'checked' || $v == 'on')) {
					$data['users_to_invite'][substr($k, strlen($compstr))] = 0; // Pending Answer
				}
			}
			
			if (isset($event_data['confirmAttendance'])) {
				$data['confirmAttendance'] = array_var($event_data, 'confirmAttendance');
			}			
			
			if (isset($event_data['send_notification'])) {
				$data['send_notification'] = array_var($event_data,'send_notification') == 'checked';
			}
			if (isset($event_data['subscribe_invited'])) {
				$data['subscribe_invited'] = array_var($event_data,'subscribe_invited') == 'checked';
			}
			return $data;
	}
	
	function add() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		
		$notAllowedMember = '';
		if(!(ProjectEvent::canAdd(logged_user(), active_context(),$notAllowedMember ))){	    	
			if (str_starts_with($notAllowedMember, '-- req dim --')) flash_error(lang('must choose at least one member of', str_replace_first('-- req dim --', '', $notAllowedMember, $in)));
			else flash_error(lang('no context permissions to add',lang("events"), $notAllowedMember));
			ajx_current("empty");
			return ;
                }
	    
                $this->setTemplate('event');
		$event = new ProjectEvent();		
		$event_data = array_var($_POST, 'event');
				
		$event_name = array_var($_GET, 'name'); //if sent from pupup
		
		//var_dump($event_data) ;
		$month = isset($_GET['month'])?$_GET['month']:date('n', DateTimeValueLib::now()->getTimestamp() + logged_user()->getTimezone() * 3600);
		$day = isset($_GET['day'])?$_GET['day']:date('j', DateTimeValueLib::now()->getTimestamp() + logged_user()->getTimezone() * 3600);
		$year = isset($_GET['year'])?$_GET['year']:date('Y', DateTimeValueLib::now()->getTimestamp() + logged_user()->getTimezone() * 3600);
		
		$user_filter = isset($_GET['user_filter']) ? $_GET['user_filter'] : logged_user()->getId();
		
		if(!is_array($event_data)) {
			// if data sent from quickadd popup (via get) we se it, else default
			if (isset($_GET['start_time'])) $this->parseTime($_GET['start_time'], $hour, $minute);
			else {
				$hour = isset($_GET['hour']) ? $_GET['hour'] : date('G', DateTimeValueLib::now()->getTimestamp() + logged_user()->getTimezone() * 3600);
				$minute = isset($_GET['minute']) ? $_GET['minute'] : round((date('i') / 15), 0) * 15; //0,15,30 and 45 min
			}
			if(!user_config_option('time_format_use_24')) {
				if($hour >= 12){
					$pm = 1;
					$hour = $hour - 12;
				} else $pm = 0;
			}
			$event_data = array(
				'month' => isset($_GET['month']) ? $_GET['month'] : date('n', DateTimeValueLib::now()->getTimestamp() + logged_user()->getTimezone() * 3600),
				'year' => isset($_GET['year']) ? $_GET['year'] : date('Y', DateTimeValueLib::now()->getTimestamp() + logged_user()->getTimezone() * 3600),
				'day' => isset($_GET['day']) ? $_GET['day'] : date('j', DateTimeValueLib::now()->getTimestamp() + logged_user()->getTimezone() * 3600),
				'hour' => $hour,
				'minute' => $minute,
				'pm' => (isset($pm) ? $pm : 0),
				'typeofevent' => isset($_GET['type_id']) ? $_GET['type_id'] : 1,
				'name' => $event_name,
				'durationhour' => isset($_GET['durationhour']) ? $_GET['durationhour'] : 1,
				'durationmin' => isset($_GET['durationmin']) ? $_GET['durationmin'] : 0,
			); // array
		} // if
		
		tpl_assign('event', $event);
		tpl_assign('event_data', $event_data);
		
		if (is_array(array_var($_POST, 'event'))) {
			try {
				$data = $this->getData($event_data);

				$event->setFromAttributes($data);

				DB::beginWork();
				$event->save();

				$this->registerInvitations($data, $event);

				if (isset($data['confirmAttendance'])) {
					$this->change_invitation_state($data['confirmAttendance'], $event->getId(), $user_filter);
				}
				
				$is_silent = false;
				if (isset($data['send_notification']) && $data['send_notification']) {
					$users_to_inv = array();
					foreach ($data['users_to_invite'] as $us => $v) {
						if ($us != logged_user()->getId()) {
							$users_to_inv[] = Contacts::findById(array('id' => $us));
						}
					}
					Notifier::notifEvent($event, $users_to_inv, 'new', logged_user());
					$is_silent = true;
				}

				if (array_var($_POST, 'members')) {
					$member_ids = json_decode(array_var($_POST, 'members'));
				} else {
					$member_ids = array();
					$context = active_context();
					foreach ($context as $selection) {
						if ($selection instanceof Member) $member_ids[] = $selection->getId();
					}
				}
                                
                                ApplicationLogs::createLog($event, ApplicationLogs::ACTION_ADD, false, $is_silent);
                                
                                $object_controller = new ObjectController();
				$object_controller->add_to_members($event, $member_ids);
				$object_controller->add_subscribers($event);
				$object_controller->link_to_new_object($event);
				$object_controller->add_custom_properties($event);
				$object_controller->add_reminders($event);

                                if (array_var($_POST, 'popup', false)) {
                                        // create default reminder
                                        $def = explode(",",user_config_option("reminders_events"));
                                        $minutes = $def[2] * $def[1];
                                        $reminder = new ObjectReminder();
                                        $reminder->setMinutesBefore($minutes);
                                        $reminder->setType($def[0]);
                                        $reminder->setContext("start");
                                        $reminder->setObject($event);
                                        $reminder->setUserId(0);
                                        $date = $event->getStart();
                                        if ($date instanceof DateTimeValue) {
                                                $rdate = new DateTimeValue($date->getTimestamp() - $minutes * 60);
                                                $reminder->setDate($rdate);
                                        }
                                        $reminder->save();
                                }
                                
                                $opt_rep_day = array();
                                if(array_var($event_data, 'repeat_saturdays',false)){
                                    $opt_rep_day['saturday'] = true;
                                }
                                if(array_var($event_data, 'repeat_sundays',false)){
                                    $opt_rep_day['sunday'] = true;
                                }
                                
                                $this->repetitive_event($event, $opt_rep_day);
                                
				if (array_var($_POST, 'popup', false)) {
					$event->subscribeUser(logged_user());
					ajx_current("reload");
				} else {
					ajx_current("back");
				}
				DB::commit();

				flash_success(lang('success add event', clean($event->getObjectName())));
				ajx_add("overview-panel", "reload");
			} catch(Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			} // try

		}
	}
	
	function delete() {
                $options = array_var($_GET, 'options');
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		//check auth
		$event = ProjectEvents::findById(get_id());
		if ($event != null) {
		    if(!$event->canDelete(logged_user())){	    	
				flash_error(lang('no access permissions'));
				//$this->redirectTo('event');
				ajx_current("empty");
				return ;
		    }
		    $events = array($event);
		} else {
			$ev_ids = explode(',', array_var($_GET, 'ids', ''));
			if (!is_array($ev_ids) || count($ev_ids) == 0) {
				flash_error(lang('no objects selected'));
				ajx_current("empty");
				return ;
			}
			$events = array();
			foreach($ev_ids as $id) {
				$e = ProjectEvents::findById($id);
				if ($e instanceof ProjectEvent) $events[] = $e;
			}
		}
	    
                $this->getUserPreferences($view_type, $user_filter, $status_filter, $task_filter);
		$this->setTemplate($view_type);
		
		try {
			foreach ($events as $event) {
				$notifications = array();
				$invs = EventInvitations::findAll(array ('conditions' => 'event_id = ' . $event->getId()));
				if (is_array($invs)) {
					foreach ($invs as $inv) {
						if ($inv->getContactId() != logged_user()->getId()) 
							$notifications[] = Contacts::findById(array('id' => $inv->getContactId()));
					}
				} else {
					if ($invs->getContactId() != logged_user()->getId()) 
						$notifications[] = Contacts::findById(array('id' => $invs->getContactId()));
				}
				//Notifier::notifEvent($event, $notifications, 'deleted', logged_user());
				
				DB::beginWork();
				// delete event
				$event->trash();
                                
                                if($event->getSpecialID() != ""){
                                    $this->delete_event_calendar_extern($event);
                                    $event->setSpecialID("");
                                    $event->save();
                                }       
                                
                                if($options == "news" || $options == "all"){
                                    $this->repetitive_event_related($event,"delete",$options);
                                }
                                
				ApplicationLogs::createLog($event, ApplicationLogs::ACTION_TRASH);
				DB::commit();
			}
			flash_success(lang('success delete event', ''));
			ajx_current("reload");			
          	ajx_add("overview-panel", "reload");
			          	
		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error delete event'));
			ajx_current("empty");
		} // try
	}
	
	function archive() {
                $options = array_var($_GET, 'options');
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		//check auth
		$event = ProjectEvents::findById(get_id());
		if ($event != null) {
		    if(!$event->canDelete(logged_user())){	    	
				flash_error(lang('no access permissions'));
				ajx_current("empty");
				return ;
		    }
		    $events = array($event);
		} else {
			$ev_ids = explode(',', array_var($_GET, 'ids', ''));
			if (!is_array($ev_ids) || count($ev_ids) == 0) {
				flash_error(lang('no objects selected'));
				ajx_current("empty");
				return ;
			}
			$events = array();
			foreach($ev_ids as $id) {
				$e = ProjectEvents::findById($id);
				if ($e instanceof ProjectEvent) $events[] = $e;
			}
		}
	    
                $this->getUserPreferences($view_type, $user_filter, $status_filter, $task_filter);
		$this->setTemplate($view_type);
		
		try {
			$succ = 0;
			foreach ($events as $event) {
				DB::beginWork();
				$event->archive();
                                if($options == "news" || $options == "all"){
                                    $this->repetitive_event_related($event,"archive",$options);
                                }
				ApplicationLogs::createLog($event, ApplicationLogs::ACTION_ARCHIVE);
				DB::commit();
				$succ++;
			}
			flash_success(lang('success archive objects', $succ));
			ajx_current("reload");			
          	ajx_add("overview-panel", "reload");
			          	
		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error archive objects'));
			ajx_current("empty");
		} // try
	}
	
	function viewdate($view_type = null, $user_filter = null, $status_filter = null, $task_filter = null){
			
		tpl_assign('cal_action','viewdate');
		ajx_set_no_toolbar(true);
		
		$this->getActualDateToShow($day, $month, $year);
		
	    if ($view_type == null)
	        $this->getUserPreferences($view_type, $user_filter, $status_filter, $task_filter);
		
		$this->setTemplate('viewdate');
		$this->setViewVariables($view_type, $user_filter, $status_filter, $task_filter);
	}
	
	function viewweek($view_type = null, $user_filter = null, $status_filter = null, $task_filter = null){
		tpl_assign('cal_action','viewdate');
		ajx_set_no_toolbar(true);
		
		$this->getActualDateToShow($day, $month, $year);
		
	    if ($view_type == null)
	    	$this->getUserPreferences($view_type, $user_filter, $status_filter, $task_filter);
	    
	    $this->setTemplate('viewweek');
		$this->setViewVariables($view_type, $user_filter, $status_filter, $task_filter);
	}
	
	function viewweek5days($view_type = null, $user_filter = null, $status_filter = null, $task_filter = null){
		tpl_assign('cal_action','viewdate');
		ajx_set_no_toolbar(true);
		
		$this->getActualDateToShow($day, $month, $year);
		
	    if ($view_type == null)
	    	$this->getUserPreferences($view_type, $user_filter, $status_filter, $task_filter);
	    
	    $this->setTemplate('viewweek5days');
		$this->setViewVariables($view_type, $user_filter, $status_filter, $task_filter);
	}
	
	private function getActualDateToShow(&$day, &$month, &$year) {
		$day = isset($_GET['day']) ? $_GET['day'] : (isset($_SESSION['day']) ? $_SESSION['day'] : date('j', DateTimeValueLib::now()->getTimestamp() + logged_user()->getTimezone() * 3600));
		$month = isset($_GET['month']) ? $_GET['month'] : (isset($_SESSION['month']) ? $_SESSION['month'] : date('n', DateTimeValueLib::now()->getTimestamp() + logged_user()->getTimezone() * 3600));
	    $year = isset($_GET['year']) ? $_GET['year'] : (isset($_SESSION['year']) ? $_SESSION['year'] : date('Y', DateTimeValueLib::now()->getTimestamp() + logged_user()->getTimezone() * 3600));
	}
	
	function setViewVariables($view_type, $user_filter, $status_filter, $task_filter) {
		$context = active_context();
		$member_selected = false;
		foreach ($context as $selection) {
			if ($selection instanceof Member) {
				$member_selected = true;
				break;
			}
		}
		
		$users = allowed_users_in_context(ProjectEvents::instance()->getObjectTypeId(), $context, ACCESS_LEVEL_READ);
		$company_ids = array(-1);
		foreach ($users as $user) {
			if ($user->getCompanyId()) $company_ids[] = $user->getCompanyId();
		}
		$companies = Contacts::findAll(array("conditions" => "is_company = 1 AND object_id IN (".implode(",", $company_ids).")"));
		
		$usr = Contacts::findById($user_filter);
		$user_filter_comp = $usr != null ? $usr->getCompanyId() : 0;

		tpl_assign('users', $users);
		tpl_assign('companies', $companies);
		tpl_assign('userPreferences', array(
				'view_type' => $view_type,
				'user_filter' => $user_filter,
				'status_filter' => $status_filter,
				'task_filter' => $task_filter,
				'user_filter_comp' => $user_filter_comp
		));
	}
	
	function getUserPreferences(&$view_type = null, &$user_filter = null, &$status_filter = null, &$task_filter = null) {
		$view_type = array_var($_GET,'view_type');
		if (is_null($view_type) || $view_type == '') {
			$view_type = user_config_option('calendar view type', 'viewweek');
		}
		if (user_config_option('calendar view type', '') != $view_type)
			set_user_config_option('calendar view type', $view_type, logged_user()->getId());
		
		$user_filter = array_var($_GET,'user_filter');
		if (is_null($user_filter) || $user_filter == '') {
			$user_filter = user_config_option('calendar user filter', 0);
		}
		if ($user_filter == 0) $user_filter = logged_user()->getId(); 	
		if (user_config_option('calendar user filter', '') != $user_filter)
			set_user_config_option('calendar user filter', $user_filter, logged_user()->getId());
			
		$status_filter = array_var($_GET,'status_filter');
		if (is_null($status_filter)) {
			$status_filter = user_config_option('calendar status filter', ' 0 1 3');
		}
		if (user_config_option('calendar status filter', '') != $status_filter)
			set_user_config_option('calendar status filter', $status_filter, logged_user()->getId());
                
                $task_filter = array_var($_GET,'task_filter');
		if (is_null($task_filter) || $task_filter == '') {
			$task_filter = user_config_option('calendar task filter', "pending");
		}
		if (user_config_option('calendar task filter', '') != $task_filter)
			set_user_config_option('calendar task filter', $task_filter, logged_user()->getId());
	}
	
	function view_calendar() {
		$this->getUserPreferences($view_type, $user_filter, $status_filter , $task_filter);
		if($view_type == 'viewdate') $this->viewdate($view_type, $user_filter, $status_filter, $task_filter);
		else if($view_type == 'index') $this->index($view_type, $user_filter, $status_filter, $task_filter);
		else if($view_type == 'viewweek5days') $this->viewweek5days($view_type, $user_filter, $status_filter, $task_filter);
		else $this->viewweek($view_type, $user_filter, $status_filter, $task_filter);
	}
	
	
	function view(){
		//check auth
		$this->addHelper('textile');
		ajx_set_no_toolbar(true);
	    $event = ProjectEvents::findById(get_id());
	    if (isset($event) && $event != null) {
		    if(!$event->canView(logged_user())){
				flash_error(lang('no access permissions'));
				$this->redirectTo('event');
				return ;
		    }

		 	//read object for this user
			$event->setIsRead(logged_user()->getId(), true);
			
			tpl_assign('event', $event);
			tpl_assign('cal_action', 'view');	
			tpl_assign('view', array_var($_GET, 'view', 'month'));	
			ajx_extra_data(array("title" => $event->getObjectName(), 'icon'=>'ico-calendar'));
			
			ApplicationReadLogs::createLog($event, ApplicationReadLogs::ACTION_READ);
	    } else {
	    	flash_error(lang('event dnx'));
			ajx_current("empty");
			return ;
	    }
	}

	function cal_error($text){
		$output = "<center><span class='failure'>$text</span></center><br>";
		return $output;
	}
	
		
	function edit() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$this->setTemplate('event');
		$event = ProjectEvents::findById(get_id());
		
		$user_filter = isset($_GET['user_id']) ? $_GET['user_id'] : logged_user()->getId();
		
		$inv = EventInvitations::findById(array('event_id' => $event->getId(), 'contact_id' => $user_filter));
		if ($inv != null) {
			$event->addInvitation($inv);
		}
		
		if(!$event->canEdit(logged_user())){	    	
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return ;
                }
	    
		$event_data = array_var($_POST, 'event');
		if(!is_array($event_data)) {
			
			$setlastweek = false;
			$rsel1 = false;$rsel2=false; $rsel3=false;
			$forever = $event->getRepeatForever();
			$occ = 1;
			if($event->getRepeatD() > 0){ $occ = 2; $rjump = $event->getRepeatD();}
			if($event->getRepeatD() > 0 AND $event->getRepeatD()%7==0){ $occ = 3; $rjump = $event->getRepeatD()/7;}
			if($event->getRepeatM() > 0){ $occ = 4; $rjump = $event->getRepeatM();}
			if($event->getRepeatY() > 0){ $occ = 5; $rjump = $event->getRepeatY();}
			if($event->getRepeatH() > 0){ $occ = 6;}
			if($event->getRepeatH() == 2){ $setlastweek = true;}
			if($event->getRepeatEnd()) { $rend = $event->getRepeatEnd();}
			if($event->getRepeatNum() > 0) $rnum = $event->getRepeatNum();
			if(!isset($rjump) || !is_numeric($rjump)) $rjump = 1;
			// decide which repeat type it is
			if($forever) $rsel1 = true; //forever
			else if(isset($rnum) AND $rnum>0) $rsel2 = true; //repeat n-times
			else if(isset($rend) AND $rend instanceof DateTimeValue) $rsel3 = true; //repeat until
			
			//if(isset($rend) AND $rend=="9999-00-00") $rend = "";
			// organize the time and date data for the html select drop downs.
			$thetime = $event->getStart()->getTimestamp() + logged_user()->getTimezone()*3600;
			$durtime = $event->getDuration()->getTimestamp() + logged_user()->getTimezone()*3600 - $thetime;
			$hour = date('G', $thetime);
			// format time to 24-hour or 12-hour clock.
			if(!user_config_option('time_format_use_24')){
				if($hour >= 12){
					$pm = 1;
					$hour = $hour - 12;
				}else $pm = 0;
			}
				
			$event_data = array(
                          'description' => $event->getDescription(),
                          'name' => $event->getObjectName(),
                          'username' => $event->getCreatedByDisplayName(),
                          'typeofevent' => $event->getTypeId(),
                          'forever' => $event->getRepeatForever(),
                          'usetimeandduration' => ($event->getTypeId())==3?0:1,
                          'occ' => $occ,
                          'rjump' => $rjump,
                          'setlastweek' => $setlastweek,
                          'rend' => isset($rend)?$rend:NULL,
                          'rnum' => isset($rnum)?$rnum:NULL,
                          'rsel1' => $rsel1,
                          'rsel2' => $rsel2,
                          'rsel3' => $rsel3,
                          'thetime' => $event->getStart()->getTimestamp(),
			  'hour' => $hour,
			  'minute' => date('i', $thetime),
			  'month' => date('n', $thetime),
			  'year' => date('Y', $thetime),
			  'day' => date('j', $thetime),
			  'durtime' => ($event->getDuration()->getTimestamp() - $thetime),
			  'durationmin' => ($durtime / 60) % 60,
			  'durationhour' => ($durtime / 3600) % 24,
			  'durday' => floor($durtime / 86400),
			  'pm' => isset($pm) ? $pm : 0,
			  'repeat_dow' => $event->getRepeatDow(),
			  'repeat_wnum' => $event->getRepeatWnum(),
			  'repeat_mjump' => $event->getRepeatMjump(),
			); // array
		} // if
                
                //I find all those related to the task to find out if the original
                $event_related = ProjectEvents::findByRelated($event->getObjectId());
                if(!$event_related){
                    //is not the original as the original look plus other related
                    if($event->getOriginalEventId() != "0"){
                        $event_related = ProjectEvents::findByEventAndRelated($event->getObjectId(),$event->getOriginalEventId());
                    }
                }
                if($event_related){
                    tpl_assign('event_related', true);
                }else{
                    tpl_assign('event_related', false);
                }    
                
		tpl_assign('event_data', $event_data);
		tpl_assign('event', $event);

		if(is_array(array_var($_POST, 'event'))) {
			
			//	MANAGE CONCURRENCE WHILE EDITING
			/* FIXME or REMOVEME
			$upd = array_var($_POST, 'updatedon');
			if ($upd && $event->getUpdatedOn()->getTimestamp() > $upd && !array_var($_POST,'merge-changes') == 'true')
			{
				ajx_current('empty');
				evt_add("handle edit concurrence", array(
					"updatedon" => $event->getUpdatedOn()->getTimestamp(),
					"genid" => array_var($_POST,'genid')
				));
				return;
			}
			if (array_var($_POST,'merge-changes') == 'true')
			{					
				$this->setTemplate('view_event');
				$editedEvent = ProjectEvents::findById($event->getId());
				$this->view();
				ajx_set_panel(lang ('tab name',array('name'=>$editedEvent->getTitle())));
				ajx_extra_data(array("title" => $editedEvent->getTitle(), 'icon'=>'ico-event'));
				ajx_set_no_toolbar(true);
				ajx_set_panel(lang ('tab name',array('name'=>$editedEvent->getTitle())));
				return;
			}
			*/
			
			try {
				$data = $this->getData($event_data);
				// run the query to set the event data
                                $event->setFromAttributes($data);

                                $this->registerInvitations($data, $event, false);
				if (isset($data['confirmAttendance'])) {
                                    $this->change_invitation_state($data['confirmAttendance'], $event->getId(), $user_filter);
                                }
                                
                                $is_silent = false;
                                if (isset($data['send_notification']) && $data['send_notification']) {
                                                    $users_to_inv = array();
                                    foreach ($data['users_to_invite'] as $us => $v) {
                                            if ($us != logged_user()->getId()) {
                                                    $users_to_inv[] = Contacts::findById(array('id' => $us));
                                            }
                                    }
                                    Notifier::notifEvent($event, $users_to_inv, 'modified', logged_user());
                                    $is_silent = true;
                                }
				    
                                DB::beginWork();
                                $event->save();  

                                if($event->getSpecialID() != ""){
                                    $this->sync_calendar_extern($event);
                                }

                                $member_ids = json_decode(array_var($_POST, 'members'));

                                $object_controller = new ObjectController();
                                $object_controller->add_to_members($event, $member_ids);
                                $object_controller->add_subscribers($event);

                                $object_controller->link_to_new_object($event);
                                $object_controller->add_custom_properties($event);
                                $object_controller->add_reminders($event);

                                $event->resetIsRead();

                                ApplicationLogs::createLog($event, ApplicationLogs::ACTION_EDIT, false, $is_silent);
                                
                                $opt_rep_day = array();
                                if(array_var($event_data, 'repeat_saturdays',false)){
                                    $opt_rep_day['saturday'] = true;
                                }
                                if(array_var($event_data, 'repeat_sundays',false)){
                                    $opt_rep_day['sunday'] = true;
                                }
                                
                                $this->repetitive_event($event, $opt_rep_day);
                                
                                if($_POST['type_related'] == "all" || $_POST['type_related'] == "news"){
                                    $data['members'] = json_decode(array_var($_POST, 'members'));
                                    $this->repetitive_event_related($event,"edit",$_POST['type_related'],$data);
                                }   

                                DB::commit();
                                flash_success(lang('success edit event', clean($event->getObjectName())));

                                if (array_var($_POST, 'popup', false)) {
                                                ajx_current("reload");
                                } else {
                                        ajx_current("back");
                                }
                                ajx_add("overview-panel", "reload");          	
                    } catch(Exception $e) {
                            DB::rollback();
                                    flash_error($e->getMessage());
                                    ajx_current("empty");
                    } // try
		} // if
	} // edit
	
	/**
	 * Returns hour and minute in 24 hour format
	 *
	 * @param string $time_str
	 * @param int $hour
	 * @param int $minute
	 */
	function parseTime($time_str, &$hour, &$minute) {
		$exp = explode(':', $time_str);
		$hour = $exp[0];
		$minute = $exp[1];
		if (str_ends_with($time_str, 'M')) {
			$exp = explode(' ', $minute);
			$minute = $exp[0];
			if ($exp[1] == 'PM' && $hour < 12) {
				$hour = ($hour + 12) % 24;
			}
			if ($exp[1] == 'AM' && $hour == 12) {
				$hour = 0;
			}
		}
	}
	
	function allowed_users_view_events() {
		$comp_array = array();
		$actual_user_id = isset($_GET['user']) ? $_GET['user'] : logged_user()->getId();
		$evid = array_var($_GET, 'evid');
		
		$i = 0;
		$companies_tmp = Contacts::findAll(array("conditions" => "is_company = 1"));
		$companies = array("0" => array('id' => $i++, 'name' => lang('without company'), 'logo_url' => '#'));
		foreach ($companies_tmp as $comptmp) {
			$companies[$comptmp->getId()] = array(
				'id' => $i++,
				'name' => $comptmp->getObjectName(),
				'logo_url' => $comptmp->getPictureUrl()
			);
		}
		
		$context_plain = array_var($_GET, 'context');
		if (is_null($context_plain) || $context_plain == "") $context = active_context();
		else $context = build_context_array($context_plain);
		
		$users = allowed_users_in_context(ProjectEvents::instance()->getObjectTypeId(), $context, ACCESS_LEVEL_READ);
		
		foreach ($companies as $id => $comp) {
			if (is_array($users) && count($users) > 0) {
				$comp_data = array(
					'id' => $comp['id'],
					'object_id' => $id,
					'name' => $comp['name'],
					'logo_url' => $comp['logo_url'],
					'users' => array() 
				);
				foreach ($users as $user) {
					if ($user->getCompanyId() == $id) {
						$comp_data['users'][] = array(
							'id' => $user->getId(),
							'name' => $user->getObjectName(),
							'avatar_url' => $user->getPictureUrl(),
							'invited' => $evid == 0 ? ($user->getId() == $actual_user_id) : (EventInvitations::findOne(array('conditions' => "`event_id` = $evid and `contact_id` = ".$user->getId())) != null),
							'mail' => $user->getEmailAddress()
						);
					}
				}
				if (count($comp_data['users']) > 0) {
					$comp_array[] = $comp_data;
				}
			}
		}
		
		$object = array(
			"totalCount" => count($comp_array),
			"start" => 0,
			"companies" => $comp_array
		);

		ajx_extra_data($object);
		ajx_current("empty");
	}
	
	function icalendar_import() {
		@set_time_limit(0);
		if (isset($_GET['from_menu']) && $_GET['from_menu'] == 1) unset($_SESSION['history_back']);
		if (isset($_SESSION['history_back'])) {
			if ($_SESSION['history_back'] > 0) $_SESSION['history_back'] = $_SESSION['history_back'] - 1;
			if ($_SESSION['history_back'] == 0) unset($_SESSION['history_back']);
			ajx_current("back");
		} else {
			$ok = false;
			$this->setTemplate('cal_import');
				
			$filedata = array_var($_FILES, 'cal_file');
			if (is_array($filedata)) {
				
				$filename = $filedata['tmp_name'].'vcal';
				copy($filedata['tmp_name'], $filename);
				
				$events_data = CalFormatUtilities::decode_ical_file($filename);
				if (count($events_data)) {
					try {
						DB::beginWork();
						foreach ($events_data as $ev_data) {
							$event = new ProjectEvent();

							$event->setFromAttributes($ev_data);
							$event->save();

							ApplicationLogs::createLog($event, ApplicationLogs::ACTION_ADD);

							$conditions = array('event_id' => $event->getId(), 'contact_id' => logged_user()->getId());
							//insert only if not exists
							if (EventInvitations::findById($conditions) == null) {
								$invitation = new EventInvitation();
								$invitation->setEventId($event->getId());
								$invitation->setContactId(logged_user()->getId());
								$invitation->setInvitationState(1);
								$invitation->save();
							}

							//insert only if not exists
							if (ObjectSubscriptions::findBySubscriptions($event->getId()) == null) {
								$subscription = new ObjectSubscription();
								$subscription->setObjectId($event->getId());
								$subscription->setContactId(logged_user()->getId());
								$subscription->save();
							}

							$member_ids = array();
							$context = active_context();
							foreach ($context as $selection) {
								if ($selection instanceof Member) $member_ids[] = $selection->getId();
							}
							$object_controller = new ObjectController();
							$object_controller->add_to_members($event, $member_ids);
						}
						DB::commit();
						$ok = true;
						flash_success(lang('success import events', count($events_data)));
						$_SESSION['history_back'] = 1;
					} catch (Exception $e) {
						DB::rollback();
						flash_error($e->getMessage());
					}
				} else {
					flash_error(lang('no events to import'));
				}
				unset($filename);
				if (!$ok) ajx_current("empty");				
			}
			else if (array_var($_POST, 'atimportform', 0)) ajx_current("empty");
		}
	}
        
	function icalendar_export() {
		$this->setTemplate('cal_export');
		$calendar_name = array_var($_POST, 'calendar_name');			
		if ($calendar_name != '') {
			$from = getDateValue(array_var($_POST, 'from_date'));
			$to = getDateValue(array_var($_POST, 'to_date'));
			
			$events = ProjectEvents::getRangeProjectEvents($from, $to);
			
			$buffer = CalFormatUtilities::generateICalInfo($events, $calendar_name);
			
			$filename = rand().'.tmp';
			$handle = fopen(ROOT.'/tmp/'.$filename, 'wb');
			fwrite($handle, $buffer);
			fclose($handle);
			
			$_SESSION['calendar_export_filename'] = $filename;
			$_SESSION['calendar_name'] = $calendar_name;
			flash_success(lang('success export calendar', count($events)));
			ajx_current("empty");
		} else {
			unset($_SESSION['calendar_export_filename']);
			unset($_SESSION['calendar_name']);
			return;
		}
	}
	
	function download_exported_file() {
		$filename = array_var($_SESSION, 'calendar_export_filename', '');
		$calendar_name = array_var($_SESSION, 'calendar_name', '');
		if ($filename != '') {
			$path = ROOT.'/tmp/'.$filename;
			$size = filesize($path);
			
			unset($_SESSION['calendar_export_filename']);
			download_file($path, 'text/ics', $calendar_name.'_events.ics', $size, false);
			unlink($path);
			die();
		} else $this->setTemplate('cal_export');
	}
	
	function generate_ical_export_url() {
		/*FIXME!! $ws = active_project();
		if ($ws == null) {
			$cal_name = logged_user()->getObjectName();
			$ws_ids = 0;
		} else {
			$cal_name = Projects::findById($ws->getId())->getName();
			if (isset($_GET['inc_subws']) && $_GET['inc_subws'] == 'true') {
				$ws_ids = $ws->getAllSubWorkspacesQuery(true, logged_user(), ProjectContacts::instance()->getTableName(true).".`can_read_events` = 1");
			} else {
				$ws_ids = $ws->getId();
			}			
		}
		$token = logged_user()->getToken();
		$url = ROOT_URL . "/" . PUBLIC_FOLDER . "/tools/ical_export.php?cal=$ws_ids&n=$cal_name&t=$token";
		
		$obj = array("url" => $url);
		ajx_extra_data($obj);*/
		ajx_current("empty");		
	}
	
	function change_duration() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$event = ProjectEvents::findById(get_id());
		if(!$event->canEdit(logged_user())){	    	
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return ;
	    }
	    
	    $hours = array_var($_GET, 'hours', -99);
	    $mins = array_var($_GET, 'mins', -99);
	    if ($hours == -99 || $mins == -99) {
	    	ajx_current("empty");
	    	return;
	    }
	    
	    $duration = new DateTimeValue($event->getDuration()->getTimestamp());
	    $duration->add('h', $hours);
	    $duration->add('m', $mins);
	    
	    DB::beginWork();
	    $event->setDuration($duration->format("Y-m-d H:i:s"));
	    $event->save();
            
            if($event->getSpecialID() != ""){
                $this->sync_calendar_extern($event);
            }
	    DB::commit();
	    
	    ajx_extra_data($this->get_updated_event_data($event));
	    if ($event->isRepetitive()) ajx_current("reload");
	    else ajx_current("empty");
	}
	
	function move_event() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$event = ProjectEvents::findById(get_id());
		if(!$event->canEdit(logged_user())){	    	
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
	    }
	    $is_read = $event->getIsRead(logged_user()->getId());
		
	    $year = array_var($_GET, 'year', $event->getStart()->getYear());
	    $month = array_var($_GET, 'month', $event->getStart()->getMonth());
	    $day = array_var($_GET, 'day', $event->getStart()->getDay());
	    $hour = array_var($_GET, 'hour', 0);
	    $min = array_var($_GET, 'min', 0);
	    
	    if ($hour == -1) $hour = format_date($event->getStart(), 'H', logged_user()->getTimezone() );
	    if ($min == -1) $min = format_date($event->getStart(), 'i', logged_user()->getTimezone() );
	    
		if ($event->isRepetitive()) {
			$orig_date = DateTimeValueLib::dateFromFormatAndString('Y-m-d H:i:s', array_var($_GET, 'orig_date'));
			$diff = DateTimeValueLib::get_time_difference($orig_date->getTimestamp(), mktime($hour, $min, 0, $month, $day, $year));
		    $new_start = new DateTimeValue($event->getStart()->getTimestamp());
		    $new_start->add('d', $diff['days']);
		    $new_start->add('h', $diff['hours']);
		    $new_start->add('m', $diff['minutes']);
		    
		    if ($event->getRepeatH()) {
		    	$event->setRepeatDow(date("w", mktime($hour, $min, 0, $month, $day, $year))+1);
		    	$wnum = 0;
		    	$tmp_day = $new_start->getDay();
		    	while ($tmp_day > 0) {
		    		$tmp_day -= 7;
		    		$wnum++;
		    	}
		    	$event->setRepeatWnum($wnum);
		    }
	    } else {
		    $new_start = new DateTimeValue(mktime($hour, $min, 0, $month, $day, $year) - logged_user()->getTimezone() * 3600);
	    }

	    $diff = DateTimeValueLib::get_time_difference($event->getStart()->getTimestamp(), $event->getDuration()->getTimestamp());
	    $new_duration = new DateTimeValue($new_start->getTimestamp());
	    $new_duration->add('d', $diff['days']);
	    $new_duration->add('h', $diff['hours']);
	    $new_duration->add('m', $diff['minutes']);
	    
	    // see if we have to reload
		$os = format_date($event->getStart(), 'd', logged_user()->getTimezone() );
		$od = format_date($event->getDuration(), 'd', logged_user()->getTimezone() );
		$ohm = format_date($event->getDuration(), 'H:i', logged_user()->getTimezone() );
		$nd = format_date($new_duration, 'd', logged_user()->getTimezone() );
		$nhm = format_date($new_duration, 'H:i', logged_user()->getTimezone() );
		$different_days = ($os != $od && $ohm != '00:00') || ($day != $nd && $nhm != '00:00');
	    
        DB::beginWork();
	    $event->setStart($new_start->format("Y-m-d H:i:s"));
	    $event->setDuration($new_duration->format("Y-m-d H:i:s"));
	    $event->save();
            
            if (!$is_read) {
                    $event->setIsRead(logged_user()->getId(), false);
            }
            
            if($event->getSpecialID() != ""){
                $this->sync_calendar_extern($event);
            }
            
	    DB::commit();
    
	    ajx_extra_data($this->get_updated_event_data($event));
	    if ($different_days || $event->isRepetitive()) ajx_current("reload");
	    else ajx_current("empty");
	}
	
	private function get_updated_event_data($event) {
		$new_start = new DateTimeValue($event->getStart()->getTimestamp() + logged_user()->getTimezone() * 3600);
	    $new_duration = new DateTimeValue($event->getDuration()->getTimestamp() + logged_user()->getTimezone() * 3600);
	    $ev_data = array (
	    	'start' => $new_start->format(user_config_option('time_format_use_24') ? "G:i" : "g:i A"),
	    	'end' => $new_duration->format(user_config_option('time_format_use_24') ? "G:i" : "g:i A"),
	    	'' => clean($event->getObjectName()),
	    );
	    return array("ev_data" => $ev_data);
	}
	
	public function markasread(){
			$ev_ids = explode(',', array_var($_GET, 'ids', ''));
			if (!is_array($ev_ids) || count($ev_ids) == 0){
				flash_error(lang('no objects selected'));
				ajx_current("empty");
				return ;
			}
			$events = array();
			foreach($ev_ids as $id) {
				$event = ProjectEvents::findById($id);
				$event->setIsRead(logged_user()->getId(),true);
			}
			ajx_current("reload");
	}
	
	public function markasunread(){
			$ev_ids = explode(',', array_var($_GET, 'ids', ''));
			if (!is_array($ev_ids) || count($ev_ids) == 0){
				flash_error(lang('no objects selected'));
				ajx_current("empty");
				return ;
			}
			$events = array();
			foreach($ev_ids as $id) {
				$event = ProjectEvents::findById($id);
				$event->setIsRead(logged_user()->getId(),false);
			}
			ajx_current("reload");
	}
        
        function calendar_sinchronization() {
                
                $user = ExternalCalendarUsers::findByContactId();
                $user_data = array();
                
                if($user){
                    $external_calendars = array();
                    $calFeed = array();
                    require_once 'Zend/Loader.php';

                    Zend_Loader::loadClass('Zend_Gdata');
                    Zend_Loader::loadClass('Zend_Gdata_AuthSub');
                    Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
                    Zend_Loader::loadClass('Zend_Gdata_Calendar');
                    
                    $user_cal = $user->getAuthUser();
                    $pass = $user->getAuthPass();
                    $service = Zend_Gdata_Calendar::AUTH_SERVICE_NAME;
                    
                    try
                    {                                        
                        $client = Zend_Gdata_ClientLogin::getHttpClient($user_cal,$pass,$service);
                        $gdataCal = new Zend_Gdata_Calendar($client); 

                        $calFeed = $gdataCal->getCalendarListFeed();   
                    }
                    catch(Exception $e)
                    {
                            flash_error(lang('check your account'));
                    }
                    
                    if(count($calFeed) > 0){
                        foreach ($calFeed as $calF){
                            $cal_src = explode("/",$calF->content->src);
                            array_pop($cal_src);
                            $calendar_visibility = end($cal_src);
                            array_pop($cal_src);
                            $calendar_user = end($cal_src);

                            $sql = "SELECT ec.* FROM `".TABLE_PREFIX."external_calendars` ec,`".TABLE_PREFIX."external_calendar_users` ecu 
                                    WHERE ec.calendar_user = '".$calendar_user."' AND ecu.contact_id = ".logged_user()->getId()."";
                            $calendar_feng = DB::executeOne($sql);
                            $sel = 0;
                            if($calendar_feng){
                                $sel = 1;
                            }
                            $external_calendars[] = array('user' => $calendar_user, 'title' => $calF->title->text , 'sel' => $sel);                        

                            $calendar_google[] = $calendar_user;
                        }
                    }
                    
                    $view_calendars = array();
                    $calendars = ExternalCalendars::findByExtCalUserId($user->getId());       
                    foreach ($calendars as $ext_calendar){
                        if(in_array($ext_calendar->getCalendarUser(), $calendar_google)){
                            $view_calendars[] = $ext_calendar;
                        }else{
                            $ext_calendar->delete();
                        }
                    }
                    tpl_assign('calendars', $view_calendars);
                    
                    $members_ids = explode(",", $user->getRelatedTo());
                    foreach($members_ids as $members_id){
                        $members[] = $members_id;
                    }
                    $user_data['id'] = $user->getId();
                    $user_data['auth_user'] = $user->getAuthUser();
                    $user_data['auth_pass'] = $user->getAuthPass();
                    $user_data['related_to'] = $members;
                    $user_data['sync'] = $user->getSync(); 
                    
                    tpl_assign('user', $user_data);
                    tpl_assign('external_calendars', $external_calendars);
                }else{
                    tpl_assign('external_calendars', array());
                }
                
                $cal_data = array();
                if(get_id('cal_id')){
                    $edit_calendar = ExternalCalendars::findById(get_id('cal_id'));
                    
                    $cal_data['id'] = $edit_calendar->getId();
                    
                    $cal_data['calendar_link'] = "https://www.google.com/calendar/feeds/".$edit_calendar->getCalendarUser()."/".$edit_calendar->getCalendarVisibility()."/basic";
                    $cal_data['calendar_name'] = $edit_calendar->getCalendarName();
                    tpl_assign('cal_data', $cal_data);
                }
	}
        
        function add_calendar_user() {
                ajx_current("empty");
                if($_POST){                    
                    if (!array_var($_POST, 'auth_user')) {
                            flash_error(lang('must enter a account gmail'));
                            ajx_current("empty");
                            return;
                    }
                    $user_email = ExternalCalendarUsers::findByEmail(array_var($_POST, 'auth_user'));
                    if($user_email) {
                            flash_error(lang('account has already'));
                            ajx_current("empty");
                            return;
                    } 
                    if (!array_var($_POST, 'auth_pass')) {
                            flash_error(lang('must enter the password gmail'));
                            ajx_current("empty");
                            return;
                    }
                    
                    $sync = 0;
                    if(array_var($_POST, 'sync')){
                        $sync = 1;
                    }
                    
                    $member_ids = json_decode(array_var($_POST, 'related_to'));
                    $members = "";
                    foreach($member_ids as $member_id){
                        $members .= $member_id.",";
                    }
                    $members = rtrim($members, ",");
                    
                    $user_cal = ExternalCalendarUsers::findById(get_id('cal_user_id'));
                    if($user_cal){
                        $user_cal->setAuthUser(array_var($_POST, 'auth_user'));
                        $user_cal->setAuthPass(array_var($_POST, 'auth_pass'));
                        $user_cal->setRelatedTo($members);
                        $user_cal->setSync($sync);
                        $user_cal->save();
                        
                        flash_success(lang('success edit account gmail'));
                        
                    }else{
                        $user_cal = new ExternalCalendarUser();
                        $user_cal->setAuthUser(array_var($_POST, 'auth_user'));
                        $user_cal->setAuthPass(array_var($_POST, 'auth_pass'));
                        $user_cal->setContactId(logged_user()->getId());
                        $user_cal->setRelatedTo($members);
                        $user_cal->setType("google");
                        $user_cal->setSync($sync);
                        $user_cal->save();
                        
                        flash_success(lang('success add account gmail'));
                    }                   
                    ajx_current("reload");
                }         
	}
        
        function add_calendar() {
                ajx_current("empty");
                if($_POST){
                    if (!array_var($_POST, 'calendar_name')) {
                            flash_error(lang('must enter a calendar name'));
                            ajx_current("empty");
                            return;
                    }
                    if (!array_var($_POST, 'calendar_link')) {
                            flash_error(lang('must enter an calendar link'));
                            ajx_current("empty");
                            return;
                    }
                    
                    $link = explode("/",array_var($_POST, 'calendar_link'));
                    array_pop($link); 
                    $calendar_visibility = end($link);
                    array_pop($link);                     
                    $calendar_user = end($link);
                    
                    $calendar = ExternalCalendars::findById(get_id('cal_id'));
                    if($calendar){
                        $calendar->setCalendarUser($calendar_user);
                        $calendar->setCalendarVisibility($calendar_visibility);
                        $calendar->setCalendarName(array_var($_POST, 'calendar_name'));
                        $calendar->save();
                        
                        flash_success(lang('success edit calendar'));                        
                    }else{
                        $calendar = new ExternalCalendar();
                        $calendar->setCalendarUser($calendar_user);
                        $calendar->setCalendarVisibility($calendar_visibility);
                        $calendar->setCalendarName(array_var($_POST, 'calendar_name'));
                        $calendar->setExtCalUserId(array_var($_POST, 'ext_cal_user_id'));
                        $calendar->save();
                        
                        flash_success(lang('success add calendar'));
                    }                   
                    ajx_current("reload");
                }         
	}
        
        function delete_calendar() {
                ajx_current("empty");
                                    
                $deleteCalendar = array_var($_GET, 'deleteCalendar', false);
                
                require_once 'Zend/Loader.php';

                Zend_Loader::loadClass('Zend_Gdata');
                Zend_Loader::loadClass('Zend_Gdata_AuthSub');
                Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
                Zend_Loader::loadClass('Zend_Gdata_Calendar');
                
                $users = ExternalCalendarUsers::findByContactId();
                
                $user = $users->getAuthUser();
                $pass = $users->getAuthPass();
                $service = Zend_Gdata_Calendar::AUTH_SERVICE_NAME;             
                
                $calendar = ExternalCalendars::findById(get_id('cal_id'));
                $events = ProjectEvents::findByExtCalId($calendar->getId());
                
                $calendar_user = $calendar->getCalendarUser();
                $calendar_visibility = 'private';
                if($calendar){
                    if($calendar->delete()){
                        if($events){
                            foreach($events as $event){                            
                                $event->trash();
                                
                                $event->setSpecialID("");
                                $event->setExtCalId(0);
                                $event->save();
                            }                        

                            if($deleteCalendar){
                                try
                                {
                                        $client = Zend_Gdata_ClientLogin::getHttpClient($user,$pass,$service);
                                        $gdataCal = new Zend_Gdata_Calendar($client); 

                                        $gdataCal = new Zend_Gdata_Calendar($client);
                                        $query = $gdataCal->newEventQuery();
                                        $query->setUser($calendar_user);
                                        $query->setVisibility($calendar_visibility);

                                        $event_list = $gdataCal->getCalendarEventFeed($query);
                                        foreach ($event_list as $event)
                                        {
                                            $event->delete();
                                        }   
                                }
                                catch(Exception $e)
                                {
                                        flash_error(lang('could not connect to calendar'));
                                        ajx_current("empty");
                                }
                            }
                        }
                        
                        flash_success(lang('success delete calendar'));
                        ajx_current("reload");
                    }         
                } 
	}
        
        function sync_calendar_extern($event){
            require_once 'Zend/Loader.php';

            Zend_Loader::loadClass('Zend_Gdata');
            Zend_Loader::loadClass('Zend_Gdata_AuthSub');
            Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
            Zend_Loader::loadClass('Zend_Gdata_Calendar');

            $users = ExternalCalendarUsers::findByContactId();
            if (!$users instanceof ExternalCalendarUser) return;
            $calendar = ExternalCalendars::findById($event->getExtCalId());
            if (!$calendar instanceof ExternalCalendar) return;

            $user = $users->getAuthUser();
            $pass = $users->getAuthPass();
            $service = Zend_Gdata_Calendar::AUTH_SERVICE_NAME;

            $client = Zend_Gdata_ClientLogin::getHttpClient($user,$pass,$service);

            $event_id = 'http://www.google.com/calendar/feeds/'.$calendar->getCalendarUser().'/private/full/'.$event->getSpecialID();

            $gcal = new Zend_Gdata_Calendar($client);

            $edit_event = $gcal->getCalendarEventEntry($event_id);
            $edit_event->title = $gcal->newTitle($event->getObjectName()); 
            $edit_event->content = $gcal->newContent($event->getDescription());

            $star_time = explode(" ",$event->getStart()->format("Y-m-d H:i:s"));
            $end_time = explode(" ",$event->getDuration()->format("Y-m-d H:i:s"));

            if($event->getTypeId() == 2){
                $when = $gcal->newWhen();
                $when->startTime = $star_time[0];
                $when->endTime = $end_time[0];
                $edit_event->when = array($when);
            }else{                                    
                $when = $gcal->newWhen();
                $when->startTime = $star_time[0]."T".$star_time[1].".000-00:00";
                $when->endTime = $end_time[0]."T".$end_time[1].".000-00:00";
                $edit_event->when = array($when);
            }

            $edit_event->save();  
        }
        
        function delete_event_calendar_extern($event){
            ajx_current("empty");
            require_once 'Zend/Loader.php';

            Zend_Loader::loadClass('Zend_Gdata');
            Zend_Loader::loadClass('Zend_Gdata_AuthSub');
            Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
            Zend_Loader::loadClass('Zend_Gdata_Calendar');

            $users = ExternalCalendarUsers::findByContactId();
            $calendar = ExternalCalendars::findById($event->getExtCalId());

            $user = $users->getAuthUser();
            $pass = $users->getAuthPass();
            $service = Zend_Gdata_Calendar::AUTH_SERVICE_NAME;

            $client = Zend_Gdata_ClientLogin::getHttpClient($user,$pass,$service);

            $event_id = 'http://www.google.com/calendar/feeds/'.$calendar->getCalendarUser().'/private/full/'.$event->getSpecialID();

            $gcal = new Zend_Gdata_Calendar($client);

            $edit_event = $gcal->getCalendarEventEntry($event_id);
            $edit_event->delete(); 
        }
        
        function import_calendars(){
            ajx_current("empty");
            if(array_var($_POST, 'e_calendars')){
                $users = ExternalCalendarUsers::findByContactId();
                $sync = false;
                foreach (array_var($_POST, 'e_calendars') as $cal_title => $cal_user){
                    $sql = "SELECT ec.* FROM `".TABLE_PREFIX."external_calendars` ec,`".TABLE_PREFIX."external_calendar_users` ecu 
                            WHERE ec.calendar_user = '".$cal_user."' AND ecu.contact_id = ".logged_user()->getId()."";
                    $calendar_feng = DB::executeOne($sql);  
                    
                    if(!$calendar_feng){
                        $calendar = new ExternalCalendar();
                        $calendar->setCalendarUser($cal_user);
                        $calendar->setCalendarVisibility("private");
                        $calendar->setCalendarName($cal_title);
                        $calendar->setExtCalUserId($users->getId());
                        if($cal_title == lang('feng calendar')){
                            $calendar->setCalendarFeng(1);
                        }
                        $calendar->save();
                        
                        $sync = true;
                    }
                }
//                if($sync){
//                    $this->import_google_calendar();
//                }
                $this->import_google_calendar();
                flash_success(lang('success import calendar'));
                ajx_current("reload");
            }
        }
        
        function import_google_calendar() {      
                ajx_current("empty");
                $users = ExternalCalendarUsers::findByContactId();  
                if($users){
                    $calendars = ExternalCalendars::findByExtCalUserId($users->getId());

                    require_once 'Zend/Loader.php';

                    Zend_Loader::loadClass('Zend_Gdata');
                    Zend_Loader::loadClass('Zend_Gdata_AuthSub');
                    Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
                    Zend_Loader::loadClass('Zend_Gdata_Calendar');

                    $user = $users->getAuthUser();
                    $pass = $users->getAuthPass();
                    $service = Zend_Gdata_Calendar::AUTH_SERVICE_NAME;

                    try
                    {
                            $client = Zend_Gdata_ClientLogin::getHttpClient($user,$pass,$service);                                                       
                            $gdataCal = new Zend_Gdata_Calendar($client);

                            //update or insert events for calendars                        
                            foreach ($calendars as $calendar){

                                //check the deleted calendars
                                $delete_calendar = false;
                                $calFeed = $gdataCal->getCalendarListFeed();        
                                foreach ($calFeed as $calF){
                                    $cal_src = explode("/",$calF->content->src);
                                    array_pop($cal_src);
                                    $calendar_visibility = end($cal_src);
                                    array_pop($cal_src);
                                    $calendar_user = end($cal_src); 

                                    if($calendar_user == $calendar->getCalendarUser()){
                                        $delete_calendar = true;
                                    }
                                }

                                if($delete_calendar){
                                    $calendar_user = $calendar->getCalendarUser();
                                    $calendar_visibility = $calendar->getCalendarVisibility();

                                    $query = $gdataCal->newEventQuery();
                                    $query->setUser($calendar_user);
                                    $query->setVisibility($calendar_visibility);
                                    $query->setSingleEvents(true);
                                    $query->setProjection('full');
                                    // execute and get results
                                    $event_list = $gdataCal->getCalendarEventFeed($query);

                                    $array_events_google = array();
                                    foreach ($event_list as $event){
                                        $event_id = explode("/",$event->id->text);
                                        $special_id = end($event_id); 
                                        $event_name = lang("untitle event");
                                        if($event->title->text != ""){
                                            $event_name = $event->title->text;
                                        }
                                        $array_events_google[] = $special_id;
                                        $new_event = ProjectEvents::findBySpecialId($special_id);
                                        if($new_event){
                                            if(strtotime(ProjectEvents::date_google_to_sql($event->updated)) > $new_event->getUpdateSync()->getTimestamp()){
                                                $start = strtotime(ProjectEvents::date_google_to_sql($event->when[0]->startTime));
                                                $fin = strtotime(ProjectEvents::date_google_to_sql($event->when[0]->endTime));
                                                if(($fin - $start) == 86400){
                                                    $new_event->setStart(date("Y-m-d H:i:s",$start));
                                                    $new_event->setDuration(date("Y-m-d H:i:s",$start));
                                                    $new_event->setTypeId(2);
                                                }elseif(($fin - $start) > 86400){                                                
                                                    $t_s = explode(' ', date("Y-m-d H:i:s",$start));
                                                    $t_f = explode(' ', date("Y-m-d H:i:s",$fin));

                                                    $date_s = new DateTimeValue(strtotime($t_s[0]."00:00:00") - logged_user()->getTimezone() * 3600);
                                                    $date_f = new DateTimeValue(strtotime($t_f[0]."23:59:59 -1 day") - logged_user()->getTimezone() * 3600);

                                                    $new_event->setStart(date("Y-m-d H:i:s",$date_s->getTimestamp()));
                                                    $new_event->setDuration(date("Y-m-d H:i:s",$date_f->getTimestamp()));
                                                    $new_event->setTypeId(2);
                                                }else{
                                                    $new_event->setStart(ProjectEvents::date_google_to_sql($event->when[0]->startTime));
                                                    $new_event->setDuration(ProjectEvents::date_google_to_sql($event->when[0]->endTime));
                                                }

                                                $new_event->setObjectName($event_name);
                                                $new_event->setDescription($event->content->text);
                                                $new_event->setUpdateSync(ProjectEvents::date_google_to_sql($event->updated));
                                                $new_event->setExtCalId($calendar->getId());
                                                $new_event->save(); 
                                            }
                                        }else{
                                            $new_event = new ProjectEvent();
                                            
                                            $start = strtotime(ProjectEvents::date_google_to_sql($event->when[0]->startTime));
                                            $fin = strtotime(ProjectEvents::date_google_to_sql($event->when[0]->endTime));
                                            if(($fin - $start) == 86400){
                                                $new_event->setStart(date("Y-m-d H:i:s",$start));
                                                $new_event->setDuration(date("Y-m-d H:i:s",$start));
                                                $new_event->setTypeId(2);
                                            }elseif(($fin - $start) > 86400){
                                                $t_s = explode(' ', date("Y-m-d H:i:s",$start));
                                                $t_f = explode(' ', date("Y-m-d H:i:s",$fin));
                                                
                                                $date_s = new DateTimeValue(strtotime($t_s[0]."00:00:00") - logged_user()->getTimezone() * 3600);
                                                $date_f = new DateTimeValue(strtotime($t_f[0]."23:59:59 -1 day") - logged_user()->getTimezone() * 3600);
                                                
                                                $new_event->setStart(date("Y-m-d H:i:s",$date_s->getTimestamp()));
                                                $new_event->setDuration(date("Y-m-d H:i:s",$date_f->getTimestamp()));
                                                $new_event->setTypeId(2);
                                            }else{
                                                $new_event->setStart(ProjectEvents::date_google_to_sql($event->when[0]->startTime));
                                                $new_event->setDuration(ProjectEvents::date_google_to_sql($event->when[0]->endTime));
                                                $new_event->setTypeId(1);
                                            }
                                            
                                            $new_event->setObjectName($event_name);
                                            $new_event->setDescription($event->content->text);
                                            $new_event->setSpecialID($special_id);
                                            $new_event->setUpdateSync(ProjectEvents::date_google_to_sql($event->updated));
                                            $new_event->setExtCalId($calendar->getId());                                            
                                            $new_event->save(); 
                                            
                                            $conditions = array('event_id' => $new_event->getId(), 'contact_id' => logged_user()->getId());
                                            //insert only if not exists 
                                            if (EventInvitations::findById($conditions) == null) { 
                                                $invitation = new EventInvitation();
                                                $invitation->setEventId($new_event->getId());
                                                $invitation->setContactId(logged_user()->getId());
                                                $invitation->setInvitationState(1);
                                                $invitation->save();
                                            }
                                            
                                            //insert only if not exists 
                                            if (ObjectSubscriptions::findBySubscriptions($new_event->getId()) == null) { 
                                                $subscription = new ObjectSubscription();
                                                $subscription->setObjectId($new_event->getId());
                                                $subscription->setContactId(logged_user()->getId());
                                                $subscription->save();
                                            }

                                            if($users->getRelatedTo()){
                                                $member = array();
                                                $member_ids = explode(",",$users->getRelatedTo());
                                                foreach ($member_ids as $member_id){
                                                    $member[] = $member_id;
                                                }
                                                $object_controller = new ObjectController();
                                                $object_controller->add_to_members($new_event, $member); 
                                            }else{
                                                $member_ids = array();
                                                $context = active_context();
                                                foreach ($context as $selection) {
                                                    if ($selection instanceof Member) $member_ids[] = $selection->getId();
                                                }		        
                                                $object_controller = new ObjectController();
                                                $object_controller->add_to_members($new_event, $member_ids); 
                                            }                                            
                                        }           
                                    }// foreach event list 

                                    //check the deleted events
                                    $events_delete = ProjectEvents::findByExtCalId($calendar->getId());
                                    if($events_delete){
                                        foreach($events_delete as $event_delete){  
                                            if(!in_array($event_delete->getSpecialID(), $array_events_google)){
                                                $event_delete->trash();

                                                $event_delete->setSpecialID("");
                                                $event_delete->setExtCalId(0);
                                                $event_delete->save();    
                                            }                                        
                                        }  
                                    }
                                }else{                
                                    $events = ProjectEvents::findByExtCalId($calendar->getId());
                                    if($calendar->delete()){
                                        if($events){
                                            foreach($events as $event){                            
                                                $event->trash();

                                                $event->setSpecialID("");
                                                $event->setExtCalId(0);
                                                $event->save();
                                            }  
                                        }
                                    }
                                }
                            }//foreach calendars
                    }
                    catch(Exception $e)
                    {
                            flash_error(lang('could not connect to calendar'));
                            ajx_current("empty");
                    }
                }
	}
        
        function export_google_calendar() {
		ajx_current("empty");
                
                require_once 'Zend/Loader.php';

                Zend_Loader::loadClass('Zend_Gdata');
                Zend_Loader::loadClass('Zend_Gdata_AuthSub');
                Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
                Zend_Loader::loadClass('Zend_Gdata_Calendar');
                
                $users = ExternalCalendarUsers::findByContactId();
                if($users){
                    if($users->getSync() == 1){
                        $sql = "SELECT ec.* FROM `".TABLE_PREFIX."external_calendars` ec,`".TABLE_PREFIX."external_calendar_users` ecu 
                                WHERE ec.calendar_feng = 1 AND ecu.contact_id = ".logged_user()->getId();
                        $calendar_feng = DB::executeOne($sql);
                        $events = ProjectEvents::findNoSync();

                        $user = $users->getAuthUser();
                        $pass = $users->getAuthPass();
                        $service = Zend_Gdata_Calendar::AUTH_SERVICE_NAME;

                        try
                        {
                                $client = Zend_Gdata_ClientLogin::getHttpClient($user,$pass,$service);  
                                $gdataCal = new Zend_Gdata_Calendar($client);

                                if ($calendar_feng){
                                    foreach ($events as $event){
                                        $calendarUrl = 'http://www.google.com/calendar/feeds/'.$calendar_feng['calendar_user'].'/private/full';

                                        $newEvent = $gdataCal->newEventEntry();
                                        $newEvent->title = $gdataCal->newTitle($event->getObjectName());
                                        $newEvent->content = $gdataCal->newContent($event->getDescription());

                                        $star_time = explode(" ",$event->getStart()->format("Y-m-d H:i:s"));
                                        $end_time = explode(" ",$event->getDuration()->format("Y-m-d H:i:s"));

                                        if($event->getTypeId() == 2){
                                            $when = $gdataCal->newWhen();
                                            $when->startTime = $star_time[0];
                                            $when->endTime = $end_time[0];
                                            $newEvent->when = array($when);
                                        }else{                                    
                                            $when = $gdataCal->newWhen();
                                            $when->startTime = $star_time[0]."T".$star_time[1].".000-00:00";
                                            $when->endTime = $end_time[0]."T".$end_time[1].".000-00:00";
                                            $newEvent->when = array($when);
                                        }

                                        // insert event
                                        $createdEvent = $gdataCal->insertEvent($newEvent, $calendarUrl);

                                        $event_id = explode("/",$createdEvent->id->text);
                                        $special_id = end($event_id); 
                                        $event->setSpecialID($special_id);
                                        $event->setUpdateSync(ProjectEvents::date_google_to_sql($createdEvent->updated));
                                        $event->setExtCalId($calendar_feng['id']);
                                        $event->save();
                                    }                             
                                }else{
                                    $appCalUrl = '';
                                    $calFeed = $gdataCal->getCalendarListFeed();        
                                    foreach ($calFeed as $calF){
                                        if($calF->title->text == lang('feng calendar')){
                                            $appCalUrl = $calF->content->src;
                                            $t_calendario = $calF->title->text;
                                        }
                                    }    

                                    if($appCalUrl != ""){
                                        $title_cal = $t_calendario;
                                    }else{
                                        $appCal = $gdataCal -> newListEntry();
                                        $appCal -> title = $gdataCal-> newTitle(lang('feng calendar'));                         
                                        $own_cal = "http://www.google.com/calendar/feeds/default/owncalendars/full";                        
                                        $new_cal = $gdataCal->insertEvent($appCal, $own_cal);

                                        $title_cal = $new_cal->title->text;
                                        $appCalUrl = $new_cal->content->src;                                
                                    }               

                                    $cal_src = explode("/",$appCalUrl);
                                    array_pop($cal_src);
                                    $calendar_visibility = end($cal_src);
                                    array_pop($cal_src);
                                    $calendar_user = end($cal_src);                            

                                    $calendar = new ExternalCalendar();
                                    $calendar->setCalendarUser($calendar_user);
                                    $calendar->setCalendarVisibility($calendar_visibility);
                                    $calendar->setCalendarName($title_cal);
                                    $calendar->setExtCalUserId($users->getId());
                                    $calendar->setCalendarFeng(1);
                                    $calendar->save();

                                    foreach ($events as $event){                               
                                        $calendarUrl = 'http://www.google.com/calendar/feeds/'.$calendar->getCalendarUser().'/private/full';

                                        $newEvent = $gdataCal->newEventEntry();

                                        $newEvent->title = $gdataCal->newTitle($event->getObjectName());
                                        $newEvent->content = $gdataCal->newContent($event->getDescription());

                                        $star_time = explode(" ",$event->getStart()->format("Y-m-d H:i:s"));
                                        $end_time = explode(" ",$event->getDuration()->format("Y-m-d H:i:s"));

                                        if($event->getTypeId() == 2){
                                            $when = $gdataCal->newWhen();
                                            $when->startTime = $star_time[0];
                                            $when->endTime = $end_time[0];
                                            $newEvent->when = array($when);
                                        }else{                                    
                                            $when = $gdataCal->newWhen();
                                            $when->startTime = $star_time[0]."T".$star_time[1].".000-00:00";
                                            $when->endTime = $end_time[0]."T".$end_time[1].".000-00:00";
                                            $newEvent->when = array($when);
                                        }

                                        // insert event
                                        $createdEvent = $gdataCal->insertEvent($newEvent, $calendarUrl);

                                        $event_id = explode("/",$createdEvent->id->text);
                                        $special_id = end($event_id); 
                                        $event->setSpecialID($special_id);
                                        $event->setUpdateSync(ProjectEvents::date_google_to_sql($createdEvent->updated));
                                        $event->setExtCalId($calendar->getId());
                                        $event->save();
                                    } 
                                }
                                flash_success(lang('success add sync'));
                                ajx_current("reload");
                        }
                        catch(Exception $e)
                        {
                                // prevent Google username and password from being displayed
                                // if a problem occurs
                                flash_error(lang('could not connect to calendar'));
                                ajx_current("empty");
                        }
                    }
                }
	}
        
        function repetitive_event($event,$opt_rep_day){
            if($event->isRepetitive()){
                if ($event->getRepeatNum() > 0) {
                    $event->setRepeatNum($event->getRepeatNum() - 1);
                    while($event->getRepeatNum() > 0){
                        $this->getNextRepetitionDates($event, $opt_rep_day, $new_st_date, $new_due_date);
                        $event->setRepeatNum($event->getRepeatNum() - 1);
                        // generate completed task
                        $event->cloneEvent($new_st_date,$new_due_date);
                        // set next values for repetetive task
                        if ($event->getStart() instanceof DateTimeValue ) $event->setStart($new_st_date);
                        if ($event->getDuration() instanceof DateTimeValue ) $event->setDuration($new_due_date);
                    }
                }elseif ($event->getRepeatForever() == 0){
                    $event_end = $event->getRepeatEnd();
                    $new_st_date = "";
                    $new_due_date = "";
                    while($new_st_date <= $event_end || $new_due_date <= $event_end){
                        $this->getNextRepetitionDates($event, $opt_rep_day, $new_st_date, $new_due_date);
                        // generate completed task
                        $event->cloneEvent($new_st_date,$new_due_date);
                        // set next values for repetetive task
                        if ($event->getStart() instanceof DateTimeValue ) $event->setStart($new_st_date);
                        if ($event->getDuration() instanceof DateTimeValue ) $event->setDuration($new_due_date);
                    }                    
                }
                $event->setRepeatEnd(EMPTY_DATETIME);
                $event->setRepeatNum(0);
                $event->setRepeatD(0);
                $event->setRepeatM(0);
                $event->setRepeatY(0);
                $event->setRepeatH(0);
                $event->setRepeatDow(0);
                $event->setRepeatWnum(0);
                $event->setRepeatMjump(0);
                $event->save();
            }
        }
        
        private function getNextRepetitionDates($event, $opt_rep_day, &$new_st_date, &$new_due_date) {
		$new_due_date = null;
		$new_st_date = null;

		if ($event->getStart() instanceof DateTimeValue ) {
			$new_st_date = new DateTimeValue($event->getStart()->getTimestamp());
		}
		if ($event->getDuration() instanceof DateTimeValue ) {
			$new_due_date = new DateTimeValue($event->getDuration()->getTimestamp());
		}
		if ($event->getRepeatD() > 0) {
			if ($new_st_date instanceof DateTimeValue) {
				$new_st_date = $new_st_date->add('d', $event->getRepeatD());
			}
			if ($new_due_date instanceof DateTimeValue) {
				$new_due_date = $new_due_date->add('d', $event->getRepeatD());
			}
		} else if ($event->getRepeatM() > 0) {
			if ($new_st_date instanceof DateTimeValue) {
				$new_st_date = $new_st_date->add('M', $event->getRepeatM());
			}
			if ($new_due_date instanceof DateTimeValue) {
				$new_due_date = $new_due_date->add('M', $event->getRepeatM());
			}
		} else if ($event->getRepeatY() > 0) {
			if ($new_st_date instanceof DateTimeValue) {
				$new_st_date = $new_st_date->add('y', $event->getRepeatY());
			}
			if ($new_due_date instanceof DateTimeValue) {
				$new_due_date = $new_due_date->add('y', $event->getRepeatY());
			}
		}
                
                $this->correct_days_event_repetitive($new_st_date,$opt_rep_day['saturday'],$opt_rep_day['sunday']);
                $this->correct_days_event_repetitive($new_due_datev, $opt_rep_day['saturday'], $opt_rep_day['sunday']);
	}
        
        function repetitive_event_related($event,$action,$type_related = "",$event_data = array()){
            //I find all those related to the event to find out if the original
            $event_related = ProjectEvents::findByRelated($event->getObjectId());
            if(!$event_related){
                //is not the original as the original look plus other related
                if($event->getOriginalEventId() != "0"){
                    $event_related = ProjectEvents::findByEventAndRelated($event->getObjectId(),$event->getOriginalEventId());
                }
            }            
            if($event_related){
                switch($action){
                        case "edit":
                                foreach ($event_related as $e_rel){
                                    if($type_related == "news"){
                                        if($event->getStart() <= $e_rel->getStart()){
                                            $this->repetitive_event_related_edit($e_rel,$event_data);
                                        }
                                    }else{
                                        $this->repetitive_event_related_edit($e_rel,$event_data);
                                    }                                    
                                }
                        break;
                        case "delete":
                                $delete_event = array();
                                foreach ($event_related as $e_rel){
                                    $event_rel = Objects::findObject($e_rel->getId());   
                                    if($type_related == "news"){
                                        if($event->getStart() <= $e_rel->getStart()){
                                            $delete_event[] = $e_rel->getId();                                                                             
                                            $event_rel->trash(); 
                                        }
                                    }else{
                                        $delete_event[] = $e_rel->getId();                                                                             
                                        $event_rel->trash(); 
                                    }                                                                        
                                }
                                return $delete_event;
                        break;
                        case "archive":
                                $archive_event = array();
                                foreach ($event_related as $e_rel){
                                    $event_rel = Objects::findObject($e_rel->getId());                                    
                                    if($type_related == "news"){
                                        if($event->getStart() <= $e_rel->getStart()){
                                            $archive_event[] = $e_rel->getId();                                                                            
                                            $e_rel->archive();  
                                        }
                                    }else{
                                        $archive_event[] = $e_rel->getId();                                                                            
                                        $e_rel->archive();
                                    }
                                }
                                return $archive_event;
                        break;
                }
            }
            
        }
        
        function repetitive_event_related_edit($event,$data){
            // run the query to set the event data
            $event->setFromAttributes($data);

            $this->registerInvitations($data, $event, false);
            if (isset($data['confirmAttendance'])) {
                $this->change_invitation_state($data['confirmAttendance'], $event->getId(), $user_filter);
            }
            DB::beginWork();
            $event->save();  

            if($event->getSpecialID() != ""){
                $this->sync_calendar_extern($event);
            }

            $object_controller = new ObjectController();
            $object_controller->add_to_members($event, array_var($task_data, 'members'));
            $object_controller->add_subscribers($event);

            $object_controller->link_to_new_object($event);
            $object_controller->add_custom_properties($event);
            $object_controller->add_reminders($event);

            $event->resetIsRead();

            ApplicationLogs::createLog($event, ApplicationLogs::ACTION_EDIT);
        }
        
        function correct_days_event_repetitive($date, $repeat_saturday = false, $repeat_sunday = false){
            if($date != ""){
                $working_days = explode(",",config_option("working_days"));
                if($repeat_saturday) $working_days[] = 6;
                if($repeat_sunday) $working_days[] = 0;
                if(!in_array(date("w",  $date->getTimestamp()), $working_days)){
                    $date = $date->add('d', 1);
                    $this->correct_days_event_repetitive($date);
                }
            }
            return $date;
        }
        
        function check_related_event(){
            ajx_current("empty");
            //I find all those related to the task to find out if the original
            $event_related = ProjectEvents::findByRelated(array_var($_REQUEST, 'related_id'));
            if(!$event_related){
                $event_related = ProjectEvents::findById(array_var($_REQUEST, 'related_id'));
                //is not the original as the original look plus other related
                if($event_related->getOriginalEventId() != "0"){
                    ajx_extra_data(array("status" => true));
                }else{
                    ajx_extra_data(array("status" => false));
                }                
            }else{
                ajx_extra_data(array("status" => true));
            }
        }
	
} // EventController

/***************************************************************************
 *           Parts of the code for this class were extracted from
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/
/*
	Code is from:
	Copyright (c) Reece Pegues
	sitetheory.com

    Reece PHP Calendar is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or 
	any later version if you wish.

    You should have received a copy of the GNU General Public License
    along with this file; if not, write to the Free Software
    Foundation Inc, 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
	
*/
?>
