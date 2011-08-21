<?php

/**
 * ProjectEvents, generated on Tue, 04 Jul 2006 06:46:08 +0200 by
 * DataObject generation tool
 *
 * @author Marcos Saiz <marcos.saiz@gmail.com>
 */
class ProjectEvents extends BaseProjectEvents {

	const ORDER_BY_NAME = 'name';
	const ORDER_BY_POSTTIME = 'dateCreated';
	const ORDER_BY_MODIFYTIME = 'dateUpdated';

	public static function getWorkspaceString($ids = '?') {
		return " `id` IN (SELECT `object_id` FROM `" . TABLE_PREFIX . "workspace_objects` WHERE `object_manager` = 'ProjectEvents' AND `workspace_id` IN ($ids)) ";
	}

	/**
	 * Returns all events for the given date, tag and considers the active project
	 *
	 * @param DateTimeValue $date
	 * @param String $tags
	 * @return unknown
	 */
	static function getDayProjectEvents(DateTimeValue $date, $tags = '', $project = null, $user = -1, $inv_state = '-1', $archived = false){
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

		$permissions = ' AND ( ' . permissions_sql_for_listings(ProjectEvents::instance(),ACCESS_LEVEL_READ, logged_user()) .')';

		if ($project instanceof Project ){
			$pids = $project->getAllSubWorkspacesQuery(!$archived);
			$wsstring = " AND " . self::getWorkspaceString($pids);
		} else {
			$wsstring = "";
		}
		
		if (isset($tags) && $tags && $tags!='') {
			$tag_str = " AND EXISTS (SELECT * FROM `" . TABLE_PREFIX . "tags` `t` WHERE `tag` IN (" . DB::escape($tags) . ") AND  `" . TABLE_PREFIX . "project_events`.`id` = `t`.`rel_object_id` AND `t`.`rel_object_manager` = 'ProjectEvents') ";
		} else {
			$tag_str = "";
		}
		
		$first_d = $day;
		while($first_d > 7) $first_d -= 7;
		$week_of_first_day = date("W", mktime(0,0,0, $month, $first_d, $year));
		
		if ($archived) $archived_cond = " AND `archived_by_id` <> 0";
		else $archived_cond = " AND `archived_by_id` = 0";

		$conditions = "	(
				-- 
				-- THIS RETURNS EVENTS ON THE ACTUAL DAY IT'S SET FOR (ONE TIME EVENTS)
				-- 
				(
					`repeat_h` = 0 
					AND
					(
						`duration` > `start` AND (`start` >= '$start_date_str' AND `start` < '$nextday_date_str' OR `duration` <= '$nextday_date_str' AND `duration` > '$start_date_str' OR `start` < '$start_date_str' AND `duration` > '$nextday_date_str')
						OR 
						`type_id` = 2 AND `start` >= '$start_date_str' AND `start` < '$nextday_date_str'
					)
				) 
				-- 
				-- THIS RETURNS REGULAR REPEATING EVENTS - DAILY, WEEKLY, MONTHLY, OR YEARLY.
				-- 
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
				-- 
				-- THIS RETURNS EVENTS SET TO BE A CERTAIN DAY OF THE WEEK IN A CERTAIN WEEK EVERY CERTAIN AMOUNT OF MONTHS
				-- 
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
					MOD( PERIOD_DIFF(DATE_FORMAT(`start`, '%Y%m'), DATE_FORMAT('$start_date_str', '%Y%m')), `repeat_mjump`) = 0
				)
			)
			$wsstring
			$permissions
			$tag_str $archived_cond";

			$result_events = self::findAll(array(
				'conditions' => $conditions,
				'order' => '`start`',
			));

			// Find invitations for events and logged user
			if (is_array($result_events) && count($result_events)) {
				ProjectEvents::addInvitations($result_events, $user);
				if (!($user == null && $inv_state == null)) {
					foreach ($result_events as $k => $event) {
						$conditions = '`event_id` = ' . $event->getId();
						if ($user != -1) $conditions .= ' AND `user_id` = ' . $user;
						$inv = EventInvitations::findAll(array ('conditions' => $conditions));
						if (!is_array($inv)) {
							if ($inv == null || (trim($inv_state) != '-1' && !strstr($inv_state, ''.$inv->getInvitationState()) && $inv->getUserId() == logged_user()->getId())) {
								unset($result_events[$k]);
							}
						} else {
							if (count($inv) > 0){
								foreach ($inv as $key => $v) {
									if ($v == null || (trim($inv_state) != '-1' && !strstr($inv_state, ''.$v->getInvitationState()) && $v->getUserId() == logged_user()->getId())) {
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

		//permission check

		$permissions = ' AND ( ' . permissions_sql_for_listings(ProjectEvents::instance(),ACCESS_LEVEL_READ, logged_user()) .')';

		if ($project instanceof Project ){
			$pids = $project->getAllSubWorkspacesQuery(!$archived);
			$wsstring = " AND " . self::getWorkspaceString($pids);
		} else {
			$wsstring = "";
		}
		if (isset($tags) && $tags && $tags!='') {
			$tag_str = " AND EXISTS (SELECT * FROM `" . TABLE_PREFIX . "tags` `t` WHERE `tag` IN (" . DB::escape($tags) . ") AND `" . TABLE_PREFIX . "project_events`.`id` = `t`.`rel_object_id` AND `t`.`rel_object_manager` = 'ProjectEvents') ";
		} else {
			$tag_str= "";
		}
		
		$invited = " AND `id` IN (SELECT `event_id` FROM `" . TABLE_PREFIX . "event_invitations` WHERE `user_id` = ".logged_user()->getId().")";
		
		if ($archived) $archived_cond = " AND `archived_by_id` <> 0";
		else $archived_cond = " AND `archived_by_id` = 0";

		$tz_hm = "'" . floor(logged_user()->getTimezone()) . ":" . (abs(logged_user()->getTimezone()) % 1)*60 . "'";

		$s_date = new DateTimeValue($start_date->getTimestamp() - logged_user()->getTimezone() * 3600);
		$e_date = new DateTimeValue($end_date->getTimestamp() - logged_user()->getTimezone() * 3600);
		$e_date->add("d", 1);

		$start_date_str = $s_date->format("Y-m-d H:i:s");
		$end_date_str = $e_date->format("Y-m-d H:i:s");
		
		$first_d = $start_day;
		while($first_d > 7) $first_d -= 7;
		$week_of_first_day = date("W", mktime(0,0,0, $start_month, $first_d, $start_year));

		$conditions = "	((
				-- 
				-- THIS RETURNS EVENTS ON THE ACTUAL DAY IT'S SET FOR (ONE TIME EVENTS)
				-- 
				(
					`repeat_h` = 0 
					AND `duration` >= '$start_date_str' 
					AND `start` < '$end_date_str' 
				) 
				-- 
				-- THIS RETURNS REGULAR REPEATING EVENTS - DAILY, WEEKLY, MONTHLY, OR YEARLY.
				-- 
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
				-- 
				-- THIS RETURNS EVENTS SET TO BE A CERTAIN DAY OF THE WEEK IN A CERTAIN WEEK EVERY CERTAIN AMOUNT OF MONTHS
				-- 
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
					MOD( PERIOD_DIFF(DATE_FORMAT(`start`, '%Y%m'), DATE_FORMAT('$start_date_str', '%Y%m')), `repeat_mjump`) = 0
				)				
			)
			$wsstring
			$permissions $invited
			$tag_str $archived_cond )";

		$result_events = self::findAll(array(
			'conditions' => $conditions,
			'order' => '`start`',
		));

		// Find invitations for events and logged user
		ProjectEvents::addInvitations($result_events);

		return $result_events;
	}

	static function addInvitations($result_events, $user_id = -1) {
		if ($user_id == -1) $user_id = logged_user()->getId();
		if (isset($result_events) && is_array($result_events) && count($result_events)) {
			foreach ($result_events as $event) {
				$inv = EventInvitations::findById(array('event_id' => $event->getId(), 'user_id' => $user_id));
				if ($inv != null) {
					$event->addInvitation($inv);
				}
			}
		}
	}

	/**
	 * Reaturn all calendar Events
	 *
	 * @param Project $project
	 * @return array
	 */
	static function getAllEventsByProject($project = null, $archived = false, $inc_sub = true, $user = null) {
		if ($project instanceof Project) {
			if ($inc_sub) {
				$pids = $project->getAllSubWorkspacesQuery(true);
			} else {
				$pids = $project->getId();
			}
			$wsstring = " AND " . self::getWorkspaceString($pids);
		} else {
			$wsstring = "";
		}
		if ($user instanceof User) {
			$permissions = " AND " . permissions_sql_for_listings(self::instance(), ACCESS_LEVEL_READ, $user);
		} else {
			$permissions = "";
		}
		if ($archived) {
			$archived_cond = " `archived_by_id` <> 0";
		} else {
			$archived_cond = " `archived_by_id` = 0";
		}
		
		$cond_str = $archived_cond . $wsstring . $permissions;
		$result_events = self::findAll(array(
			'conditions' => array($cond_str)
		)); // findAll

		// Find invitations for events and logged user
		ProjectEvents::addInvitations($result_events, $user instanceof User ? $user->getId() : 0);

		return $result_events;
	} // getAllEventsByProject
	 
} // ProjectEvents

?>