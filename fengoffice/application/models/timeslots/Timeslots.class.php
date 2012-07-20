<?php

/**
 * class Timeslots
 *
 * @author Carlos Palma <chonwil@gmail.com>
 */
class Timeslots extends BaseTimeslots {

	public function __construct() {
		parent::__construct ();
		$this->object_type_name = 'timeslot';
	}
	
	/**
	 * Return object timeslots
	 *
	 * @param ContentDataObject $object
	 * @return array
	 */
	static function getTimeslotsByObject(ContentDataObject $object, $user = null) {
		$userCondition = '';
		if ($user)
			$userCondition = ' AND `contact_id` = '. $user->getId();

		return self::findAll(array(
          'conditions' => array('`rel_object_id` = ?' . $userCondition, $object->getObjectId()),
          'order' => '`e`.`start_time`'
          ));
	}
	
	
	static function getOpenTimeslotByObject(ContentDataObject $object, $user = null) {
		$userCondition = '';
		if ($user)
			$userCondition = ' AND `user_id` = '. $user->getId();

		return self::findOne(array(
          'conditions' => array('`rel_object_id` = ? AND `end_time`= ? ' . $userCondition, $object->getObjectId(), EMPTY_DATETIME), 
          'order' => '`e`.`start_time`'
          ));
	}
	
	
	static function getOpenTimeslotsByObject(ContentDataObject $object) {
		return self::findAll(array(
          'conditions' => array('`rel_object_id` = ? AND `end_time`= ? ', $object->getObjectId(), EMPTY_DATETIME), 
          'order' => 'start_time'
          ));
	}

	/**
	 * Return number of timeslots for specific object
	 *
	 * @param ContentDataObject $object
	 * @return integer
	 */
	static function countTimeslotsByObject(ContentDataObject $object, $user = null) {
		$userCondition = '';
		if ($user)
			$userCondition = ' AND `contact_id` = '. $user->getId();

		return self::count(array('`rel_object_id` = ? ' . $userCondition, $object->getObjectId()));
	} // countTimeslotsByObject

	/**
	 * Drop timeslots by object
	 *
	 * @param ContentDataObject
	 * @return boolean
	 */
	static function dropTimeslotsByObject(ContentDataObject $object) {
		$timeslots = self::findAll(array('conditions' => array('`rel_object_id` = ?', $object->getObjectId())));
		foreach ($timeslots as $timeslot) {
			$timeslot->delete();
		}
	} // dropTimeslotsByObject

	/**
	 * Returns timeslots based on the set query parameters
	 *
	 * @param User $user
	 * @param string $workspacesCSV
	 * @param DateTimeValue $start_date
	 * @param DateTimeValue $end_date
	 * @param string $object_id
	 * @param array $group_by
	 * @param array $order_by
	 * @return array
	 */
	static function getTaskTimeslots($context, $members = null, $user = null, $start_date = null, $end_date = null, $object_id = 0, $group_by = null, $order_by = null, $limit = 0, $offset = 0, $timeslot_type = 0){
		
		$commonConditions = "";
		if ($start_date)
			$commonConditions .= DB::prepareString(' AND `e`.`start_time` >= ? ', array($start_date));
		if ($end_date)
			$commonConditions .= DB::prepareString(' AND (`e`.`paused_on` <> 0 OR `e`.`end_time` <> 0 AND `e`.`end_time` <= ?) ', array($end_date));
			
		//User condition
		$commonConditions .= $user ? ' AND `e`.`contact_id` = '. $user->getId() : '';
		
		//Object condition
		$commonConditions .= $object_id > 0 ? ' AND `e`.`rel_object_id` = ' . $object_id : '';
		
		switch($timeslot_type){
			case 0: //Task timeslots
				$conditions = " AND `e`.`rel_object_id` IN (SELECT `obj`.`id` FROM `" . TABLE_PREFIX . "objects` `obj` WHERE `obj`.`trashed_on` = 0 AND `obj`.`archived_on` = 0)";
				break;
			case 1: //Time timeslots
				$conditions = " AND `e`.`rel_object_id` = 0";
				break;
			case 2: //All timeslots
				$conditions = " AND (`e`.`rel_object_id` = 0 OR `e`.`rel_object_id` IN (SELECT `obj`.`id` FROM `" . TABLE_PREFIX . "objects` `obj` WHERE `obj`.`trashed_on` = 0 AND `obj`.`archived_on` = 0))";
				break;
			default:
				throw new Error("Timeslot type not recognised: " . $timeslot_type);
		}
		
		$conditions .= $commonConditions;		
		$join_params = null;
		
		$order_by[] = 'start_time';
		$result = self::instance()->listing(array(
			'order' => $order_by,
			'extra_conditions' => $conditions,
		));
		
		return $result->objects;
	}
	
	/**
	 * This function sets the selected billing values for all timeslots which lack any type of billing values (value set to 0). 
	 * This function is used when users start to use billing in the system.
	 * 
	 * @return unknown_type
	 */
	static function updateBillingValues() {
		$timeslots = Timeslots::findAll(array(
			'conditions' => '`end_time` > 0 AND billing_id = 0 AND is_fixed_billing = 0',
			'join' => array(
				'table' => Objects::instance()->getTableName(true),
				'jt_field' => 'id',
				'e_field' => 'rel_object_id'
			)
		));
		
		$users = Contacts::getAllUsers();
		$usArray = array();
		foreach ($users as $u){
			$usArray[$u->getId()] = $u;
		}
		$pbidCache = array();
		$count = 0;
		
		$categories_cache = array();
		
		foreach ($timeslots as $ts){
			/* @var $ts Timeslot */
		    $user = $usArray[$ts->getContactId()];
		    if ($user instanceof Contact){
				$billing_category_id = $user->getDefaultBillingId();
				if ($billing_category_id > 0){
					
					$hours = $ts->getMinutes() / 60;
					
					$billing_category = array_var($categories_cache, $billing_category_id);
					if (!$billing_category instanceof BillingCategory) {
						$billing_category = BillingCategories::findById($billing_category_id);
						$categories_cache[$billing_category_id] = $billing_category;
					}
					
					if ($billing_category instanceof BillingCategory){
						$hourly_billing = $billing_category->getDefaultValue();
						$ts->setBillingId($billing_category_id);
						$ts->setHourlyBilling($hourly_billing);
						$ts->setFixedBilling(round($hourly_billing * $hours, 2));
						$ts->setIsFixedBilling(false);
						
						$ts->save();
						$count ++;
					}
					
				}
			} else {
				$ts->setIsFixedBilling(true);
				$ts->save();
			}
		}
		return $count;
	}
	
	static function getTimeslotsByUserWorkspacesAndDate(DateTimeValue $start_date, DateTimeValue $end_date, $object_manager, $user = null, $workspacesCSV = null, $object_id = 0){
		return array(); //FIXME or REMOVEME
		$userCondition = '';
		if ($user)
			$userCondition = ' AND `contact_id` = '. $user->getId();
		
		$projectCondition = '';
		if ($workspacesCSV && $object_manager == 'ProjectTasks')
			$projectCondition = ' AND (SELECT count(*) FROM `'. TABLE_PREFIX . 'project_tasks` as `pt`, `' . TABLE_PREFIX . 'workspace_objects` AS `wo` WHERE `pt`.`id` = `rel_object_id` AND `pt`.`trashed_on` = 0 AND ' .
			"`wo`.`object_manager` = 'ProjectTasks' AND `wo`.`object_id` = `object_id` AND `wo`.`workspace_id` IN (" . $workspacesCSV . ')) > 0';
			
		/* TODO: handle permissions with permissions_sql_for_listings */
		$permissions = "";
		if ($object_manager == 'ProjectTasks') {
			$permissions = ' AND (SELECT count(*) FROM `'. TABLE_PREFIX . 'project_tasks` as `pt`, `' . TABLE_PREFIX . 'workspace_objects` AS `wo` WHERE `pt`.`id` = `rel_object_id` AND `pt`.`trashed_on` = 0 AND ' .
			"`wo`.`object_manager` = 'ProjectTasks' AND `wo`.`object_id` = `object_id` AND `wo`.`workspace_id` IN (" . logged_user()->getWorkspacesQuery() . ')) > 0';
		}
			
		$objectCondition = '';
		if ($object_id > 0)
			$objectCondition = ' AND `rel_object_id` = ' . $object_id;
		
		return self::findAll(array(
          'conditions' => array('`start_time` > ? and `end_time` < ?' . $userCondition . $projectCondition . $permissions . $objectCondition, $start_date, $end_date),
          'order' => '`start_time`'
        ));
	
	}

	static function getGeneralTimeslots($context, $user = null, $offset = 0, $limit = 20) {
			
		$user_sql = "";
		if ($user instanceof Contact) {
			$user_sql = " AND contact_id = " . $user->getId();
		}
		
		//$result = Timeslots::getContentObjects($context, ObjectTypes::findById(Timeslots::instance()->getObjectTypeId()), array('start_time', 'rel_object_id'), 'DESC', " AND rel_object_id = 0" . $user_sql, null, null, null, $offset, $limit);
		$result = Timeslots::instance()->listing(array(
			"order" => array('start_time', 'rel_object_id'),
			"order_dir" => "DESC",
		 	"extra_conditions" => " AND rel_object_id = 0" . $user_sql,
			"start" => $offset,
			"limit" => $limit			
		));
		return $result;
	}
	
	
} // Timeslots

?>