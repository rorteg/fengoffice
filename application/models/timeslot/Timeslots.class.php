<?php

/**
 * class Timeslots
 *
 * @author Carlos Palma <chonwil@gmail.com>
 */
class Timeslots extends BaseTimeslots {

	/**
	 * Return object timeslots
	 *
	 * @param ProjectDataObject $object
	 * @return array
	 */
	static function getTimeslotsByObject(ProjectDataObject $object, $user = null) {
		$userCondition = '';
		if ($user)
			$userCondition = ' and `user_id` = '. $user->getId();

		return self::findAll(array(
          'conditions' => array('`object_id` = ? AND `object_manager` = ?' . $userCondition, $object->getObjectId(), get_class($object->manager())),
          'order' => '`start_time`'
          )); // array
	} // getTimeslotsByObject
	
	
	static function getOpenTimeslotByObject(ProjectDataObject $object, $user = null) {
		$userCondition = '';
		if ($user)
			$userCondition = ' and `user_id` = '. $user->getId();

		return self::findOne(array(
          'conditions' => array('`object_id` = ? AND `object_manager` = ? AND `end_time`= ? ' . $userCondition, $object->getObjectId(), get_class($object->manager()), EMPTY_DATETIME), 
          'order' => '`start_time`'
          )); // array
	} // getTimeslotsByObject
	
	
	static function getOpenTimeslotsByObject(ProjectDataObject $object) {
		return self::findAll(array(
          'conditions' => array('`object_id` = ? AND `object_manager` = ? AND `end_time`= ? ', $object->getObjectId(), get_class($object->manager()), EMPTY_DATETIME), 
          'order' => '`start_time`'
          )); // array
	} // getTimeslotsByObject

	/**
	 * Return number of timeslots for specific object
	 *
	 * @param ProjectDataObject $object
	 * @return integer
	 */
	static function countTimeslotsByObject(ProjectDataObject $object, $user = null) {
		$userCondition = '';
		if ($user)
		$userCondition = ' and `user_id` = '. $user->getId();

		return self::count(array('`object_id` = ? AND `object_manager` = ?' . $userCondition, $object->getObjectId(), get_class($object->manager())));
	} // countTimeslotsByObject

	/**
	 * Drop timeslots by object
	 *
	 * @param ProjectDataObject
	 * @return boolean
	 */
	static function dropTimeslotsByObject(ProjectDataObject $object) {
		return self::delete(array('`object_manager` = ? AND `object_id` = ?', get_class($object->manager()), $object->getObjectId()));
	} // dropTimeslotsByObject

	/**
	 * Returns timeslots based on the set query parameters
	 *
	 * @param User $user
	 * @param string $workspacesCSV
	 * @param DateTimeValue $start_date
	 * @param DateTimeValue $end_date
	 * @param string $object_manager
	 * @param string $object_id
	 * @param array $group_by
	 * @param array $order_by
	 * @return array
	 */
	static function getTaskTimeslots($workspace = null, $user = null, $workspacesCSV = null, $start_date = null, $end_date = null, $object_id = 0, $group_by = null, $order_by = null, $limit = 0, $offset = 0, $timeslot_type = 0, $custom_conditions = null, $object_subtype = null){
		$wslevels = 0;
		foreach ($group_by as $gb)
			if ($gb == "project_id")
				$wslevels++;
		
		$wsDepth = 0;
		if ($workspace instanceof Project)
			$wsDepth = $workspace->getDepth();
		
		$wslevels = min(array($wslevels, 10 - $wsDepth));
		if ($wslevels < 0) $wslevels = 0;
		
		$select = "SELECT `ts`.*";
		for ($i = 0; $i < $wslevels; $i++)
			$select .= ", `ws" . $i . "`.`name` AS `wsName" . $i . "`, `ws" . $i . "`.`id` AS `wsId" . $i . "`";
			
		$preFrom = " FROM ";
		for ($i = 0; $i < $wslevels; $i++)
			$preFrom .= "(";
		$postFrom = "";
		for ($i = 0; $i < $wslevels; $i++)
			$postFrom .= ") LEFT OUTER JOIN `".TABLE_PREFIX."projects` AS `ws" . $i . "` ON `pr`.`p" . ($wsDepth + $i + 1) . "` = `ws" . $i . "`.`id`";
		
		$commonConditions = "";
		$commonConditions2 = "";
		if ($start_date){
			$commonConditions .= DB::prepareString(' AND `ts`.`start_time` >= ? ', array($start_date));
			$commonConditions2 .= $commonConditions;
		}
		if ($end_date){
			if ($timeslot_type == '1' || $timeslot_type == '2'){//this is for the General timeslots queries only
				$commonConditions2 .= DB::prepareString(' AND (`ts`.`paused_on` <> 0 OR `ts`.`end_time` <> 0 ) AND `ts`.`start_time` < ? ' , array($end_date)); //this is for the General timeslots queries only
			}
			$commonConditions .= DB::prepareString(' AND (`ts`.`paused_on` <> 0 OR `ts`.`end_time` <> 0) AND `ts`.`end_time` < ? ', array($end_date));
			//$commonConditions .= DB::prepareString(' AND (`ts`.`paused_on` <> 0 OR `ts`.`end_time` <> 0) AND `ts`.`end_time` > 0 AND `ts`.`end_time` < ? ', array($end_date));  -- another fix reported by a user, but we have to test it yet		
		}
		
		//User condition
		$user_condition = $user? ' AND `ts`.`user_id` = '. $user->getId() : '';
		$commonConditions .= $user_condition;
		$commonConditions2 .= $user_condition;
		
		//Object condition
		$commonConditions .= $object_id > 0 ? ' AND `ts`.`object_manager` = "ProjectTasks" AND `ts`.`object_id` = ' . $object_id : ''; //Only applies to tasks
		
		$sql = '';

		//Custom properties conditions
		$custom_cond ='';
		$custom = false; 		
		if(count($custom_conditions) > 0){		
				$custom = true;			
				foreach($custom_conditions as $condCp){		
										
					//array_var($condCp, 'custom_property_id');
					$cp = CustomProperties::getCustomProperty(array_var($condCp, 'custom_property_id'));
					
					//$skip_condition = false;
					$dateFormat = 'm/d/Y';
					if(isset($params[array_var($condCp, 'id')."_".$cp->getName()])){
						$value = $params[array_var($condCp, 'id')."_".$cp->getName()];
						if ($cp->getType() == 'date')
						$dateFormat = user_config_option('date_format');
					}else{
						$value = array_var($condCp, 'value');
					}
							
					
					$custom_cond .= ' AND `pt`.id IN ( SELECT object_id as id FROM '.TABLE_PREFIX.'custom_property_values cpv WHERE ';
					$custom_cond .= ' cpv.custom_property_id = '.array_var($condCp, 'custom_property_id');				

					if(array_var($condCp, 'condition') == 'like' || array_var($condCp, 'condition') == 'not like'){
						$value = '%'.$value.'%';
					}
					if ($cp->getType() == 'date') {
						$dtValue = DateTimeValueLib::dateFromFormatAndString($dateFormat, $value);
						$value = $dtValue->format('Y-m-d H:i:s');
					}
					if(array_var($condCp, 'condition') != '%'){
						if ($cp->getType() == 'numeric') {
							$custom_cond .= ' AND cpv.value '.array_var($condCp, 'condition').' '.mysql_real_escape_string($value);
						}else{
							$custom_cond .= ' AND cpv.value '.array_var($condCp, 'condition').' "'.mysql_real_escape_string($value).'"';
						}
					}else{
						$custom_cond .= ' AND cpv.value like "%'.mysql_real_escape_string($value).'"';
					}
					$custom_cond .= ')';
				
				}									
		}
		
		switch($timeslot_type){
			case 0: //Task timeslots
				$from = "`" . TABLE_PREFIX . "timeslots` AS `ts`, `" . TABLE_PREFIX . "project_tasks` AS `pt`, `" . TABLE_PREFIX ."projects` AS `pr`, `" . TABLE_PREFIX ."workspace_objects` AS `wo`";
				$conditions = " WHERE `ts`.`object_manager` = 'ProjectTasks'  AND `pt`.`id` = `ts`.`object_id` AND `pt`.`trashed_on` = " . DB::escape(EMPTY_DATETIME) . " AND `pt`.`archived_by_id` = 0 AND `wo`.`object_manager` = 'ProjectTasks' AND `wo`.`object_id` = `ts`.`object_id` AND `wo`.`workspace_id` = `pr`.`id`";
				//Project condition
				$conditions .= $workspacesCSV ? ' AND `pr`.`id` IN (' . $workspacesCSV . ')' : '';
				if ($custom) {
					$commonConditions .= $custom_cond;
				}
				if ($object_subtype) $conditions .= " AND `pt`.`object_subtype`=$object_subtype";
				
				$sql = $select . $preFrom . $from . $postFrom . $conditions . $commonConditions;
				break;
			case 1: //Time timeslots
				$from = "`" . TABLE_PREFIX . "timeslots` AS `ts`, `" . TABLE_PREFIX ."projects` AS `pr`";
				$conditions = " WHERE `ts`.`object_manager` = 'Projects'";
				$conditions .= $workspacesCSV ? ' AND `ts`.`object_id` IN (' . $workspacesCSV . ") AND `ts`.`object_id` = `pr`.`id`" : " AND `ts`.`object_id` = `pr`.`id`";

				$sql = $select . $preFrom . $from . $postFrom . $conditions . $commonConditions2;
				break;
			case 2: //All timeslots
				$from1 = "`" . TABLE_PREFIX . "timeslots` AS `ts`, `" . TABLE_PREFIX . "project_tasks` AS `pt`, `" . TABLE_PREFIX ."projects` AS `pr`, `" . TABLE_PREFIX ."workspace_objects` AS `wo`";
				$from2 = "`" . TABLE_PREFIX . "timeslots` AS `ts`, `" . TABLE_PREFIX ."projects` AS `pr`";
				
				$conditions1 = " WHERE `ts`.`object_manager` = 'ProjectTasks'  AND `pt`.`id` = `ts`.`object_id` AND `pt`.`trashed_on` = " . DB::escape(EMPTY_DATETIME) . " AND `pt`.`archived_by_id` = 0 AND `wo`.`object_manager` = 'ProjectTasks' AND `wo`.`object_id` = `ts`.`object_id` AND `wo`.`workspace_id` = `pr`.`id`";
				//Project condition
				$conditions1 .= $workspacesCSV ? ' AND `pr`.`id` IN (' . $workspacesCSV . ')' : '';
				if ($object_subtype) $conditions1 .= " AND `pt`.`object_subtype`=$object_subtype";
				
				$conditions2 = " WHERE `object_manager` = 'Projects'";
				$conditions2 .= $workspacesCSV ? ' AND `ts`.`object_id` IN (' . $workspacesCSV . ") AND `ts`.`object_id` = `pr`.`id`" : " AND `ts`.`object_id` = `pr`.`id`";

				$sql = $select . $preFrom . $from1 . $postFrom . $conditions1 . $commonConditions . $custom_cond . ' UNION ' . $select . $preFrom . $from2 . $postFrom . $conditions2 . $commonConditions2;
				break;
			default:
				throw new Error("Timeslot type not recognised: " . $timeslot_type);
		}
		
		//Group by
		$wsCount = 0;
		$sql .= ' ORDER BY ';
		if (is_array($group_by)){
			foreach ($group_by as $gb){
				switch($gb){
					case 'project_id':
						$sql.= "`wsName" . $wsCount . "` ASC, ";
						$wsCount++;
						break;
					case 'id':
					case 'priority':
					case 'milestone_id':
					case 'state':
						if ($timeslot_type == 0)
							$sql.= "`pt`.`$gb` ASC, "; 
						break;
					default:
						if (is_string($gb) && trim($gb) != '')  $sql.= "`$gb` ASC, "; break;
				}
			}
		}
		
		//Order by
		if (is_array($order_by)){
			foreach ($order_by as $ob){
				if (is_string($ob) && trim($ob) != '')  $sql.= "`$ob` ASC, ";
			}
		}
		
		$sql .= " `start_time`";
		if ($limit > 0 && $offset > 0)
			$sql .= " LIMIT $offset, $limit";

		$timeslots = array();
		$rows = DB::executeAll($sql);
		if(is_array($rows)) {
			foreach($rows as $row) {
				$tsRow = array("ts" => Timeslots::instance()->loadFromRow($row));
				for ($i = 0; $i < $wslevels; $i++)
					$tsRow["wsId".$i] = $row["wsId" . $i];
				$timeslots[] = $tsRow;
			} // foreach
		} // if
		
    	return count($timeslots) ? $timeslots : null;
	}
	
	/**
	 * This function sets the selected billing values for all timeslots which lack any type of billing values (value set to 0). 
	 * This function is used when users start to use billing in the system.
	 * 
	 * @return unknown_type
	 */
	static function updateBillingValues(){
		$timeslots = Timeslots::findAll(array('conditions' => '`end_time` > 0 AND billing_id = 0 AND is_fixed_billing = 0 AND (object_manager = \'ProjectTasks\' OR object_manager = \'Projects\')', 
			'limit' => 500));
		
		$users = Users::findAll();
		$usArray = array();
		foreach ($users as $u){
			$usArray[$u->getId()] = $u;
		}
		$pbidCache = array();
		$count = 0;
		foreach ($timeslots as $ts){
		    $user = $usArray[$ts->getUserId()];
		    if (isset($user) && $user){
				$billing_category_id = $user->getDefaultBillingId();
				if ($billing_category_id > 0){
					$object = $ts->getObject();
					//Set billing info
					if (($object instanceof ProjectDataObject && $object->getProject() instanceof Project) || ($object instanceof Project)){
						$hours = $ts->getMinutes() / 60;
						if ($object instanceof Project)
							$project = $object;
						else
							$project = $object->getProject();
						
						$ts->setBillingId($billing_category_id);
						if (!isset($pbidCache[$project->getId()]))
							$pbidCache[$project->getId()] = array();
							
						if (isset($pbidCache[$project->getId()][$billing_category_id]))
							$hourly_billing = $pbidCache[$project->getId()][$billing_category_id];
						else{
							$hourly_billing = $project->getBillingAmount($billing_category_id);
							$pbidCache[$project->getId()][$billing_category_id] = $hourly_billing;
						}
						
						$ts->setHourlyBilling($hourly_billing);
						$ts->setFixedBilling(round($hourly_billing * $hours,2));
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
		$userCondition = '';
		if ($user)
			$userCondition = ' AND `user_id` = '. $user->getId();
		
		$projectCondition = '';
		if ($workspacesCSV && $object_manager == 'ProjectTasks')
			$projectCondition = ' AND (SELECT count(*) FROM `'. TABLE_PREFIX . 'project_tasks` as `pt`, `' . TABLE_PREFIX . 'workspace_objects` AS `wo` WHERE `pt`.`id` = `object_id` AND `pt`.`trashed_on` = ' . DB::escape(EMPTY_DATETIME) . ' AND ' .
			"`wo`.`object_manager` = 'ProjectTasks' AND `wo`.`object_id` = `object_id` AND `wo`.`workspace_id` IN (" . $workspacesCSV . ')) > 0';
			
		/* TODO: handle permissions with permissions_sql_for_listings */
		$permissions = "";
		if ($object_manager == 'ProjectTasks') {
			$permissions = ' AND (SELECT count(*) FROM `'. TABLE_PREFIX . 'project_tasks` as `pt`, `' . TABLE_PREFIX . 'workspace_objects` AS `wo` WHERE `pt`.`id` = `object_id` AND `pt`.`trashed_on` = ' . DB::escape(EMPTY_DATETIME) . ' AND ' .
			"`wo`.`object_manager` = 'ProjectTasks' AND `wo`.`object_id` = `object_id` AND `wo`.`workspace_id` IN (" . logged_user()->getWorkspacesQuery() . ')) > 0';
		}
			
		$objectCondition = '';
		if ($object_id > 0)
			$objectCondition = ' AND `object_id` = ' . $object_id;
		
		return self::findAll(array(
          'conditions' => array('`object_manager` = ? and `start_time` > ? and `end_time` < ?' . $userCondition . $projectCondition . $permissions . $objectCondition, $object_manager, $start_date, $end_date),
          'order' => '`start_time`'
          )); // array
	
	}

	static function getAllProjectTimeslots($project) {
		return Timeslots::findAll(array(
			'conditions' => "object_manager = 'Projects' AND `object_id` = " . $project->getId() 
		));
	}
	
	static function getProjectTimeslots($allowedWorkspaceIdsCSV = null, $user = null, $project = null, $offset = 0, $limit = 20) {
		$project_sql = "";
		if ($allowedWorkspaceIdsCSV != null) {
			$project_sql .= " AND `object_id` IN ($allowedWorkspaceIdsCSV)";
		}
		if ($project instanceof Project) {
			$pids = $project->getAllSubWorkspacesQuery();
			$project_sql .= " AND `object_id` IN ($pids)";
		}
			
		$user_sql = "";
		if ($user instanceof User) {
			$user_sql = " AND user_id = " . $user->getId();
		}
		
		return Timeslots::findAll(array(
			'conditions' => "object_manager = 'Projects'" . $project_sql . $user_sql, 
			'order' => 'start_time DESC, id DESC',
			'offset' => $offset,
			'limit' => $limit));
	}
	
	static function countProjectTimeslots($allowedWorkspaceIdsCSV = null, $user = null, $project = null) {
		$project_sql = "";
		if ($allowedWorkspaceIdsCSV != null) {
			$project_sql .= " AND `object_id` IN ($allowedWorkspaceIdsCSV)";
		}
		if ($project instanceof Project) {
			$pids = $project->getAllSubWorkspacesQuery();
			$project_sql .= " AND `object_id` IN ($pids)";
		}
			
		$user_sql = "";
		if ($user instanceof User) {
			$user_sql = " AND user_id = " . $user->getId();
		}
		
		return Timeslots::count("object_manager = 'Projects'" . $project_sql . $user_sql);
	}
} // Comments

?>