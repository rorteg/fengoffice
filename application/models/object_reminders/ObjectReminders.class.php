<?php

/**
 * ObjectReminders
 *
 * @author Ignacio de Soto <ignacio.desoto@fengoffice.com>
 */
class ObjectReminders extends BaseObjectReminders {

	/**
	 * Returns reminders that a user has for a specific object.
	 *
	 * @param ProjectDataObject $object
	 * @param User $user
	 */
	function getAllRemindersByObjectAndUser($object, $user, $context = null, $include_subscriber_reminders = false) {
		if (isset($context)) {
			$extra = ' AND `context` = ' . DB::escape($context);
		} else {
			$extra = "";
		}
		if ($include_subscriber_reminders) {
			$usercond = '(`user_id` = ? OR `user_id` = 0)';
		} else {
			$usercond = '`user_id` = ?';
		}
		$reminders = ObjectReminders::findAll(array(
        	'conditions' => array("`object_id` = ? AND `object_manager` = ? AND $usercond" . $extra,
					$object->getId(),
        			get_class($object->manager()),
        			$user->getId()
		)));
		return $reminders;
	}
	
	/**
	 * Returns reminders for an object
	 * @param $object
	 * @return unknown_type
	 */
	function getByObject($object) {
		return self::findAll(array(
			'conditions' => array("`object_id` = ? AND `object_manager` = ?",
				$object->getId(),
				get_class($object->manager())
		)));
	}
	
	function getDueReminders($type = null) {
		if (isset($type)) {
			$extra = ' AND `type` = ' . DB::escape($type);
		} else {
			$extra = "";
		}
		return ObjectReminders::findAll(array(
			'conditions' => array(
				"`date` > '0000-00-00 00:00:00' AND `date` < ?" . $extra, DateTimeValueLib::now(),
			),
			'limit' => config_option('cron reminder limit', 100)
		));
	}
	
	function getNextDueReminders($type = null, DateTimeValue $start, DateTimeValue $end) {
		if (isset($type)) {
			$extra = ' AND `type` = ' . DB::escape($type);
		} else {
			$extra = "";
		}
		// get reminders that will not be shown in time if shown after $end time
		$reminders = ObjectReminders::findAll(array(
			'conditions' => array(
				"`date` > ? AND `date` < ? AND ADDDATE(`date`, INTERVAL `minutes_before` MINUTE) < ?" . $extra, $start, $end, $end,
			),
			'limit' => config_option('cron reminder limit', 100)
		));
		
		return $reminders;
	}
	
	/**
	 * Return array of users that have reminders for an object
	 *
	 * @param ProjectDataObject $object
	 * @return array
	 */
	static function getUsersByObject(ProjectDataObject $object) {
		$users = array();
		$reminders = ObjectReminders::findAll(array(
        	'conditions' => '`object_id` = ' . DB::escape($object->getId()) .
        		' AND `object_manager` = ' . DB::escape(get_class($object->manager()))
		)); // findAll
		if(is_array($reminders)) {
			foreach($reminders as $reminder) {
				$user = $reminder->getUser();
				if($user instanceof User) $users[] = $user;
			} // foreach
		} // if
		return count($users) ? $users : null;
	} // getUsersByObject

	/**
	 * Return array of objects that $user has reminders for
	 *
	 * @param User $user
	 * @return array
	 */
	static function getObjectsByUser(User $user) {
		$objects = array();
		$reminders = ObjectReminders::findAll(array(
        	'conditions' => '`user_id` = ' . DB::escape($user->getId())
		)); // findAll
		if(is_array($Reminders)) {
			foreach($Reminders as $Reminder) {
				$object = $Reminder->getObject();
				if($object instanceof ProjectDataObject) $objects[] = $object;
			} // foreach
		} // if
		return $objects;
	} // getObjectsByUser

	/**
	 * Clear reminders by object
	 *
	 * @param ProjectDataObject $object
	 * @return boolean
	 */
	static function clearByObject(ProjectDataObject $object) {
		return ObjectReminders::delete(
      		'`object_id` = ' . DB::escape($object->getId()) .
      		' AND `object_manager` = ' . DB::escape(get_class($object->manager()))
		);
	} // clearByObject

	static function clearByObjectAndUser(ProjectDataObject $object, User $user, $include_subscribers = false) {
		if ($include_subscribers) {
			$usercond = '(`user_id` = '. DB::escape($user->getId()) .' OR `user_id` = 0)';
		} else {
			$usercond = '`user_id` = '. DB::escape($user->getId());
		}
		return ObjectReminders::delete(
      		'`object_id` = ' . DB::escape($object->getId()) .
      		' AND `object_manager` = ' . DB::escape(get_class($object->manager())) .
			" AND $usercond"
		);
	}
	
	/**
	 * Clear Reminders by user
	 *
	 * @param User $user
	 * @return boolean
	 */
	static function clearByUser(User $user) {
		return ObjectReminders::delete('`user_id` = ' . DB::escape($user->getId()));
	} // clearByUser

} // ObjectReminders

?>