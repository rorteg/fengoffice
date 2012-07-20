<?php

/**
 * ProjectEvents, generated on Tue, 04 Jul 2006 06:46:08 +0200 by
 * DataObject generation tool
 *
 * @author Marcos Saiz <marcos.saiz@gmail.com>
 */
class ProjectEvents extends BaseProjectEvents {

	function __construct() {
		parent::__construct();
		$this->object_type_name = 'event';
	}
	
	const ORDER_BY_NAME = 'name';
	const ORDER_BY_POSTTIME = 'dateCreated';
	const ORDER_BY_MODIFYTIME = 'dateUpdated';
        
        function findBySpecialId($special_id) {
                return ProjectEvents::findOne(array('conditions' => array('`special_id` = ?', $special_id)));
        }
        
        function findByExtCalId($ext_cal_id) {
                return ProjectEvents::findAll(array('conditions' => array('`ext_cal_id` = ?', $ext_cal_id)));
        }
        
        function findNoSync($contact_id) {
                return ProjectEvents::findAll(array(
                    'conditions' => array('`special_id` = "" AND trashed_by_id = 0 AND trashed_on =\''.EMPTY_DATETIME.'\' AND contact_id = '.$contact_id),
                    'join' => array(
                            'table' => EventInvitations::instance()->getTableName(),
                            'jt_field' => 'event_id',
                            'e_field' => 'object_id',
                    )));
        }

	/**
	 * Returns all events for the given date, tag and considers the active project
	 *
	 * @param DateTimeValue $date
	 * @param String $tags
	 * @return unknown
	 */
	static function getDayProjectEvents(DateTimeValue $date, $context = null, $user = -1, $inv_state = '-1', $archived = false){
		$day = $date->getDay();
		$month = $date->getMonth();
		$year = $date->getYear();

		if (!is_numeric($day) OR !is_numeric($month) OR !is_numeric($year)) {
			return NULL;
		}

		$tz_hm = "'". floor(logged_user()->getTimezone()).":".(abs(logged_user()->getTimezone()) % 1)*60 ."'";

		$date = new DateTimeValue($date->getTimestamp() - logged_user()->getTimezone() * 3600);
		$next_date = new DateTimeValue($date->getTimestamp() + 24*3600);

		$start_date_str = $date->format("Y-m-d H:i:s");
		$nextday_date_str = $next_date->format("Y-m-d H:i:s");

		// fix any date issues
		$year = date("Y",mktime(0,0,1,$month, $day, $year));
		$month = date("m",mktime(0,0,1,$month, $day, $year));
		$day = date("d",mktime(0,0,1,$month, $day, $year));
		//permission check

		$first_d = $day;
		while($first_d > 7) $first_d -= 7;
		$week_of_first_day = date("W", mktime(0,0,0, $month, $first_d, $year));

		$conditions = "	AND (
				(
					`repeat_h` = 0 
					AND
					(
						`duration` > `start` AND (`start` >= '$start_date_str' AND `start` < '$nextday_date_str' OR `duration` <= '$nextday_date_str' AND `duration` > '$start_date_str' OR `start` < '$start_date_str' AND `duration` > '$nextday_date_str')
						OR 
						`type_id` = 2 AND `start` >= '$start_date_str' AND `start` < '$nextday_date_str'
					)
				) 
				OR 
				(
					`repeat_h` = 0 
					AND
					DATE(`start`) <= '$start_date_str' 
					AND
					(
						(
							MOD( DATEDIFF(ADDDATE(`start`, INTERVAL ".logged_user()->getTimezone()." HOUR), '$year-$month-$day') ,repeat_d) = 0
							AND
							(
								DATE_ADD(`start`, INTERVAL (`repeat_num`-1)*`repeat_d` DAY) >= '$start_date_str'
								OR
                                                        repeat_forever = 1
								OR
								repeat_end >= '$year-$month-$day'
						)
						)
						OR
						(
							MOD( PERIOD_DIFF(DATE_FORMAT(`start`,'%Y%m'),DATE_FORMAT('$start_date_str','%Y%m')) ,repeat_m) = 0
							AND 
							`start` <= '$start_date_str' AND DAY(`start`) = $day 
							AND	
							(
								DATE_ADD(`start`, INTERVAL (`repeat_num`-1)*`repeat_m` MONTH) >= '$start_date_str'
								OR
                                                        repeat_forever = 1
								OR
								repeat_end >= '$year-$month-$day'
						)
						)
						OR
						(
							MOD( (YEAR(DATE(`start`))-YEAR('$start_date_str')) ,repeat_y) = 0
							AND 
							`start` <= '$start_date_str' AND DAY(`start`) = $day AND MONTH(`start`) = $month 
							AND 
							(
								DATE_ADD(`start`, INTERVAL (`repeat_num`-1)*`repeat_y` YEAR) >= '$start_date_str'
								OR
                                                        repeat_forever = 1
								OR
								repeat_end >= '$year-$month-$day'
						)
					)		
				)
				)
				OR
				(
					DATE(`start`) <= '$start_date_str'
					AND
					`repeat_h` = 1 
					AND
					`repeat_dow` = DAYOFWEEK('$start_date_str') 
					AND
					`repeat_wnum` + $week_of_first_day - 1 = WEEK('$start_date_str', 3) 
					AND
					MOD( ABS(PERIOD_DIFF(DATE_FORMAT(`start`, '%Y%m'), DATE_FORMAT('$start_date_str', '%Y%m'))), `repeat_mjump`) = 0
				)
			)";
		
			$start = null ;
			$limit = null ;
			//$result_events = self::getContentObjects(active_context(), ObjectTypes::findById(self::instance()->getObjectTypeId()), '`start`', 'ASC', $conditions,null,false,false,$start, $limit)->objects;
			$result_events = self::instance()->listing(array(
				"order" => 	'start',
				"order_dir"=> 'ASC',
				"extra_conditions" => $conditions ,
				"start" => $start,
				"limit" => $limit		
				
			))->objects ;

			// Find invitations for events and logged user
			if (is_array($result_events) && count($result_events)) {
				ProjectEvents::addInvitations($result_events, $user);
				if (!($user == null && $inv_state == null)) {
					foreach ($result_events as $k => $event) {
						$conditions = '`event_id` = ' . $event->getId();
						if ($user != -1) $conditions .= ' AND `contact_id` = ' . $user;
						$inv = EventInvitations::findAll(array ('conditions' => $conditions));
						if (!is_array($inv)) {
							if ($inv == null || (trim($inv_state) != '-1' && !strstr($inv_state, ''.$inv->getInvitationState()) && $inv->getContactId() == logged_user()->getId())) {
								unset($result_events[$k]);
							}
						} else {
							if (count($inv) > 0){
								foreach ($inv as $key => $v) {
									if ($v == null || (trim($inv_state) != '-1' && !strstr($inv_state, ''.$v->getInvitationState()) && $v->getContactId() == logged_user()->getId())) {
										unset($result_events[$k]);
										break;
									}
								}
							} else unset($result_events[$k]);
						}
					}
				}
			}

			return $result_events;
	}



	/**
	 * Returns all events for the given range, tag and considers the active project
	 *
	 * @param DateTimeValue $date
	 * @param String $tags
	 * @return unknown
	 */
	static function getRangeProjectEvents(DateTimeValue $start_date, DateTimeValue $end_date,  $tags = '', $project = null, $archived = false){

		$start_year = date("Y",mktime(0,0,1,$start_date->getMonth(), $start_date->getDay(), $start_date->getYear()));
		$start_month = date("m",mktime(0,0,1,$start_date->getMonth(), $start_date->getDay(), $start_date->getYear()));
		$start_day = date("d",mktime(0,0,1,$start_date->getMonth(), $start_date->getDay(), $start_date->getYear()));

		$end_year = date("Y",mktime(0,0,1,$end_date->getMonth(), $end_date->getDay(), $end_date->getYear()));
		$end_month = date("m",mktime(0,0,1,$end_date->getMonth(), $end_date->getDay(), $end_date->getYear()));
		$end_day = date("d",mktime(0,0,1,$end_date->getMonth(), $end_date->getDay(), $end_date->getYear()));

		if(!is_numeric($start_day) OR !is_numeric($start_month) OR !is_numeric($start_year) OR !is_numeric($end_day) OR !is_numeric($end_month) OR !is_numeric($end_year)){
			return NULL;
		}

		$invited = " AND `id` IN (SELECT `event_id` FROM `" . TABLE_PREFIX . "event_invitations` WHERE `contact_id` = ".logged_user()->getId().")";
		
		$tz_hm = "'" . floor(logged_user()->getTimezone()) . ":" . (abs(logged_user()->getTimezone()) % 1)*60 . "'";

		$s_date = new DateTimeValue($start_date->getTimestamp() - logged_user()->getTimezone() * 3600);
		$e_date = new DateTimeValue($end_date->getTimestamp() - logged_user()->getTimezone() * 3600);
		$e_date->add("d", 1);

		$start_date_str = $s_date->format("Y-m-d H:i:s");
		$end_date_str = $e_date->format("Y-m-d H:i:s");
		
		$first_d = $start_day;
		while($first_d > 7) $first_d -= 7;
		$week_of_first_day = date("W", mktime(0,0,0, $start_month, $first_d, $start_year));

		$conditions = "	AND ((
				(
					`repeat_h` = 0 
					AND `duration` >= '$start_date_str' 
					AND `start` < '$end_date_str' 
				) 
				OR 
				(
					`repeat_h` = 0 
					AND
					DATE(`start`) < '$end_date_str'
					AND
					(							
						(
							DATE_ADD(`start`, INTERVAL (`repeat_num`-1)*`repeat_d` DAY) >= '$start_date_str' 
							OR
                                        repeat_forever = 1
							OR
							repeat_end >= '$start_year-$start_month-$start_day'
				)
				OR
				(
							DATE_ADD(`start`, INTERVAL (`repeat_num`-1)*`repeat_m` MONTH) >= '$start_date_str' 
							OR
							repeat_forever = 1
							OR
							repeat_end >= '$start_year-$start_month-$start_day'
						)
						OR
						(
							DATE_ADD(`start`, INTERVAL (`repeat_num`-1)*`repeat_y` YEAR) >= '$start_date_str' 
							OR
							repeat_forever = 1
							OR
							repeat_end >= '$start_year-$start_month-$start_day'
						)
					)		
				)
				OR
				(
					DATE(`start`) <= '$start_date_str'
					AND
					`repeat_h` = 1 
					AND
					`repeat_dow` = DAYOFWEEK('$start_date_str') 
					AND
					`repeat_wnum` + $week_of_first_day - 1 = WEEK('$start_date_str', 3) 
					AND
					MOD( ABS(PERIOD_DIFF(DATE_FORMAT(`start`, '%Y%m'), DATE_FORMAT('$start_date_str', '%Y%m'))), `repeat_mjump`) = 0					
				)				
			)
			$invited
		)";

		//$result_events = self::getContentObjects(active_context(), ObjectTypes::findById(self::instance()->getObjectTypeId()), '`start`', 'ASC', $conditions, null, false, false, $start, $limit);
		
		$result_events = self::instance()->listing(array(
			"order" => 	'start',
			"order_dir"=> 'ASC',
			"extra_conditions" => $conditions ,
			"start" => $start,
			"limit" => $limit		
			
		))->objects ;
		// Find invitations for events and logged user
		ProjectEvents::addInvitations($result_events);

		return $result_events;
	}

	static function addInvitations($result_events, $user_id = -1) {
		if ($user_id == -1) $user_id = logged_user()->getId();
		if (isset($result_events) && is_array($result_events) && count($result_events)) {
			foreach ($result_events as $event) {
				$inv = EventInvitations::findById(array('event_id' => $event->getId(), 'contact_id' => $user_id));
				if ($inv != null) {
					$event->addInvitation($inv);
				}
			}
		}
	}
        
        function import_google_calendar() {
                $users_cal = ExternalCalendarUsers::findAll(); 
                if(count($users_cal) > 0){
                    foreach ($users_cal as $users){
                        $contact = Contacts::findById($users->getContactId());
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
                                    $delete_calendar = true;
                                    $calFeed = $gdataCal->getCalendarListFeed();        
                                    foreach ($calFeed as $calF){
                                        $cal_src = explode("/",$calF->content->src);
                                        array_pop($cal_src);
                                        $calendar_visibility = end($cal_src);
                                        array_pop($cal_src);
                                        $calendar_user = end($cal_src);

                                        if($calendar_user == $calendar->getCalendarUser()){
                                            $delete_calendar = false;
                                        }
                                    }

                                    if(!$delete_calendar){
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
                                                if($new_event->getUpdateSync() instanceof DateTimeValue && strtotime(ProjectEvents::date_google_to_sql($event->updated)) > $new_event->getUpdateSync()->getTimestamp()){                                                	
                                                    $start = strtotime(ProjectEvents::date_google_to_sql($event->when[0]->startTime));
                                                    $fin = strtotime(ProjectEvents::date_google_to_sql($event->when[0]->endTime));
                                                    if(($fin - $start) == 86400){
                                                        $new_event->setStart(date("Y-m-d H:i:s",$start));
                                                        $new_event->setDuration(date("Y-m-d H:i:s",$start));
                                                        $new_event->setTypeId(2);
                                                    }elseif(($fin - $start) > 86400){                                                
                                                        $t_s = explode(' ', date("Y-m-d H:i:s",$start));
                                                        $t_f = explode(' ', date("Y-m-d H:i:s",$fin));

                                                        $date_s = new DateTimeValue(strtotime($t_s[0]."00:00:00") - $contact->getTimezone() * 3600);
                                                        $date_f = new DateTimeValue(strtotime($t_f[0]."23:59:59 -1 day") - $contact->getTimezone() * 3600);

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

                                                    $date_s = new DateTimeValue(strtotime($t_s[0]."00:00:00") - $contact->getTimezone() * 3600);
                                                    $date_f = new DateTimeValue(strtotime($t_f[0]."23:59:59 -1 day") - $contact->getTimezone() * 3600);

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

                                                $conditions = array('event_id' => $new_event->getId(), 'contact_id' => $contact->getId());
                                                //insert only if not exists 
                                                if (EventInvitations::findById($conditions) == null) { 
                                                    $invitation = new EventInvitation();
                                                    $invitation->setEventId($new_event->getId());
                                                    $invitation->setContactId($contact->getId());
                                                    $invitation->setInvitationState($contact instanceof Contact && $contact->getId() == $contact->getId() ? 1 : 0);
                                                    $invitation->save();
                                                }

                                                //insert only if not exists 
                                                if (ObjectSubscriptions::findBySubscriptions($new_event->getId(),$contact) == null) { 
                                                    $subscription = new ObjectSubscription();
                                                    $subscription->setObjectId($new_event->getId());
                                                    $subscription->setContactId($contact->getId());
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
                                                    if(count($context) > 0){
                                                        foreach ($context as $selection) {
                                                                if ($selection instanceof Member) $member_ids[] = $selection->getId();
                                                        }
                                                    }                                                
                                                    if (count($member_ids) == 0 && $contact instanceof Contact) {
                                                            $m = Members::findById($contact->getPersonalMemberId());
                                                            if (!$m instanceof Member) {
                                                                    $person_dim = Dimensions::findByCode('feng_persons');
                                                                    if ($person_dim instanceof Dimension) {
                                                                            $member_ids = Members::findAll(array(
                                                                                    'id' => true, 
                                                                                    'conditions' => array("object_id = ? AND dimension_id = ?", $contact->getId(), $person_dim->getId())
                                                                            ));
                                                                    }
                                                            } else {
                                                                    $member_ids[] = $m->getId();
                                                            }
                                                    }
                                                    $object_controller = new ObjectController();
                                                    $object_controller->add_to_members($new_event, $member_ids, $contact); 
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
                                //Logger::log($e->getMessage());
                        }
                    }
                }
	}
        
        function export_google_calendar() { 
                $users_cal = ExternalCalendarUsers::findAll();  
                if(count($users_cal) > 0){
                    foreach ($users_cal as $users){
                        if($users->getSync() == 1){
                            $contact = Contacts::findById($users->getContactId());
                            $sql = "SELECT ec.* FROM `".TABLE_PREFIX."external_calendars` ec,`".TABLE_PREFIX."external_calendar_users` ecu 
                                    WHERE ec.calendar_feng = 1 AND ecu.contact_id = ".$contact->getId();
                            $calendar_feng = DB::executeOne($sql);
                            $events = ProjectEvents::findNoSync($contact->getId());
                            
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
                            }
                            catch(Exception $e)
                            {
                                    //Logger::log($e->getMessage());
                            }
                        }
                    }
                }
	}
        
        function date_google_to_sql($str){
                $t = explode('T', $str);

                $date = $t[0];
                if(array_key_exists(1,$t)){
                    $time_ = $t[1];
                }else{
                    $time_ = "12:00:00.000-00:00";
                }

                $time = substr($time_, 0, 8);
                $signo = substr($time_, 12,1);
                $gtm = substr($time_, 13,2);

                $str = strtotime($date . ' ' . $time);

                if($signo == "-"){
                    $str = $str + ($gtm * 60 * 60);
                }else{
                    $str = $str - ($gtm * 60 * 60);
                }

                return (date("Y-m-d H:i:s",$str));
        }
        
        function findByRelated($event_id) {
                return ProjectEvents::findAll(array('conditions' => array('`original_event_id` = ?', $event_id)));
        }
        
        function findByEventAndRelated($event_id,$original_event_id) {
                return ProjectEvents::findAll(array('conditions' => array('(`original_event_id` = ? OR `object_id` = ?) AND `object_id` <> ?', $original_event_id,$original_event_id,$event_id)));
        }

} // ProjectEvents


 
