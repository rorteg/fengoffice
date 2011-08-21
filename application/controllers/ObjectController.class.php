<?php

/**
 * Controller that is responsible for handling objects linking related requests
 *
 * @version 1.0
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class ObjectController extends ApplicationController {

	function index(){
		$this->setLayout('html');

	}
	/**
	 * Construct the ObjectController
	 *
	 * @access public
	 * @param void
	 * @return ObjectController
	 */
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website');
	} // __construct

	function add_subscribers(ProjectDataObject $object) {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$log_info = "";
		$subscribers = array_var($_POST, 'subscribers');
		$object->clearSubscriptions();
		if (is_array($subscribers)) {
			foreach ($subscribers as $key => $checked) {
				$user_id = substr($key, 5);
				if ($checked == "checked") {
					$user = Users::findById($user_id);
					if ($user instanceof User) {
						$object->subscribeUser($user);
						$log_info .= ($log_info == "" ? "" : ",") . $user->getId();
					}
				}
			}
			if ($log_info != "") {
				ApplicationLogs::createLog($object, $object->getWorkspaces(), ApplicationLogs::ACTION_SUBSCRIBE, false, true, true, $log_info);
			}
		}
	}
	
	function redraw_subscribers_list() {
		$object = get_object_by_manager_and_id(array_var($_GET, 'id'), array_var($_GET, 'man'));
		if (!$object) {
			ajx_current("empty");
			return;
		}
		tpl_assign('object', $object);
		$this->setLayout("html");
		$this->setTemplate("list_subscribers");
	}
	
	function add_subscribers_list() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$genid = array_var($_GET,'genid');
		$obj_id = array_var($_GET,'obj_id');
		$obj_manager = array_var($_GET,'manager');
		$object = get_object_by_manager_and_id($obj_id,$obj_manager);
		
		if (!isset($genid)) {
			$genid = gen_id();
		}
		$subscriberIds = array();
		if ($object->isNew()) {
			$subscriberIds[] = logged_user()->getId();
		} else {
			foreach ($object->getSubscribers() as $u) {
				$subscriberIds[] = $u->getId();
			}
		}
		if (!isset($workspaces)) {
			if ($object->isNew()) {
				$workspaces = array(active_or_personal_project());
			} else {
				$workspaces = $object->getWorkspaces();
			}
		}
		tpl_assign('object', $object);
		tpl_assign('type', get_class($object->manager()));
		tpl_assign('workspaces', $workspaces);
		tpl_assign('subscriberIds', $subscriberIds);
		tpl_assign('genid', $genid);
		//echo tpl_fetch(get_template_path('add_subscribers_list', 'object'));
	}
	
	function add_subscribers_from_object_view() {
		ajx_current("empty");
		$objectId = array_var($_GET, 'object_id');
		$managerName = array_var($_GET, 'object_manager');
		$object = get_object_by_manager_and_id($objectId,$managerName);
		$old_users = $object->getSubscriberIds();
		$this->add_subscribers($object);
		$users = $object->getSubscriberIds();
		$new = array();
		foreach ($users as $user) {
			if (!in_array($user, $old_users)) {
				$new[] = $user;
			}
		}
		ApplicationLogs::createLog($object, $object->getWorkspaces(), ApplicationLogs::ACTION_SUBSCRIBE, false, false, true, implode(",", $new));
		
		flash_success(lang('subscription modified successfully'));
	}
	
	function init_trash() {
		require_javascript("og/TrashCan.js");
		ajx_current("panel", "trashcan", null, null, true);
		ajx_replace(true);
	}
	
	function init_archivedobjs() {
		require_javascript("og/ArchivedObjects.js");
		ajx_current("panel", "archivedobjects", null, null, true);
		ajx_replace(true);
	}

	function render_add_subscribers() {
		$ws_ids = array_var($_GET, 'workspaces', '');
		$uids = array_var($_GET, 'users', '');
		$genid = array_var($_GET, 'genid', '');
		$type = array_var($_GET, 'object_type', '');
		$workspaces = Projects::findByCSVIds($ws_ids);
		$subscriberIds = explode(",", $uids);

		tpl_assign('type', $type);
		tpl_assign('workspaces', $workspaces);
		tpl_assign('subscriberIds', $subscriberIds);
		tpl_assign('genid', $genid);
		$this->setLayout("html");
		$this->setTemplate("add_subscribers");
	}
	
	function add_to_workspaces($object, $force_ws = true) {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$object->removeFromWorkspaces(logged_user()->getWorkspacesQuery());
		$ids = array_var($_POST, "ws_ids", "");
		$enteredWS = Projects::findByCSVIds($ids);
		$validWS = array();
		foreach ($enteredWS as $ws) {
			if ($object->canAdd(logged_user(), $ws)) {
				$validWS[] = $ws;
			}
		}
		if (empty($validWS) && $force_ws) {
			throw new Exception(lang('must choose at least one workspace error'));
		}
		foreach ($validWS as $w) {
			$object->addToWorkspace($w);
		}
		return $validWS;
	}
	
	/**
	 * Adds the custom properties of an object into the database.
	 * 
	 * @param $object
	 * @return unknown_type
	 */
	function add_custom_properties($object) {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$obj_custom_properties = array_var($_POST, 'object_custom_properties');
		
		$customProps = CustomProperties::getAllCustomPropertiesByObjectType(get_class($object->manager()));
		//Sets all boolean custom properties to 0. If any boolean properties are returned, they are subsequently set to 1.
		foreach($customProps as $cp){
			if($cp->getType() == 'boolean'){
				$custom_property_value = CustomPropertyValues::getCustomPropertyValue($object->getId(), $cp->getId());
				if(!$custom_property_value instanceof CustomPropertyValue){
					$custom_property_value = new CustomPropertyValue();
				}
				$custom_property_value->setObjectId($object->getId());
				$custom_property_value->setCustomPropertyId($cp->getId());
				$custom_property_value->setValue(0);
				$custom_property_value->save();
			}
		}
		if (is_array($obj_custom_properties)){
			
			foreach($obj_custom_properties as $id => $value){
				//Get the custom property
				$custom_property = null;
				foreach ($customProps as $cp){
					if ($cp->getId() == $id){
						$custom_property = $cp;
						break;
					}
				}
				
				if ($custom_property instanceof CustomProperty){
					// save dates in standard format "Y-m-d H:i:s", because the column type is string
					if ($custom_property->getType() == 'date') {
						if(is_array($value)){
							$newValues = array();
							foreach ($value as $val) {
								$dtv = DateTimeValueLib::dateFromFormatAndString(user_config_option('date_format'), $val);
								$newValues[] = $dtv->format("Y-m-d H:i:s");
							}
							$value = $newValues;
						} else {
							$dtv = DateTimeValueLib::dateFromFormatAndString(user_config_option('date_format'), $value);
							$value = $dtv->format("Y-m-d H:i:s");
						}
					}
					
					//Save multiple values
					if(is_array($value)){
						CustomPropertyValues::deleteCustomPropertyValues($object->getId(), $id);
						foreach($value as &$val){
							if (is_array($val)) {
								// CP type == table
								$str_val = '';
								foreach ($val as $col_val) {
									$col_val = str_replace("|", "\|", $col_val);
									$str_val .= ($str_val == '' ? '' : '|') . $col_val;
								}
								$val = $str_val;
							}
							if($val != ''){
								if(strpos($val, ',')) {
									$val = str_replace(',', '|', $val);
								}
								
								$custom_property_value = new CustomPropertyValue();
								$custom_property_value->setObjectId($object->getId());
								$custom_property_value->setCustomPropertyId($id);
								$custom_property_value->setValue($val);
								$custom_property_value->save();
							}
						}
					}else{
						if($custom_property->getType() == 'boolean'){
							$value = isset($value);
						}
						$cpv = CustomPropertyValues::getCustomPropertyValue($object->getId(), $id);
						if($cpv instanceof CustomPropertyValue){
							$custom_property_value = $cpv;
						} else 
							$custom_property_value = new CustomPropertyValue();
						$custom_property_value->setObjectId($object->getId());
						$custom_property_value->setCustomPropertyId($id);
						$custom_property_value->setValue($value);
						$custom_property_value->save();
					}
					
					//Add to searchable objects
					if ($object->isSearchable() && 
						($custom_property->getType() == 'text' || $custom_property->getType() == 'list' || $custom_property->getType() == 'numeric')){
						$name = $custom_property->getName();
						$searchable_object = SearchableObjects::findOne(array("conditions" => "`rel_object_manager` = '".get_class($object->manager())."' AND `rel_object_id` = ".$object->getId()." AND `column_name` = '$name'"));
						if (!$searchable_object)
							$searchable_object = new SearchableObject();
						
						if (is_array($value))
							$value = implode(', ', $value);
							
						$searchable_object->setRelObjectManager(get_class($object->manager()));
						$searchable_object->setRelObjectId($object->getId());
						$searchable_object->setColumnName($name);
						$searchable_object->setContent($value);
						$searchable_object->setProjectId(0);
						$searchable_object->setIsPrivate(false);
						
						$searchable_object->save();
					}
				}
			}
		}

		//Save the key - value pair custom properties (custom properties table)
		$object->clearObjectProperties();
		$names = array_var($_POST, 'custom_prop_names');
		$values = array_var($_POST, 'custom_prop_values');
		if (!is_array($names)) return;
		for ($i=0; $i < count($names); $i++) {
			$name = trim($names[$i]);
			$value = trim($values[$i]);
			if ($name != '' && $value != '') {
				$property = new ObjectProperty();
				$property->setObject($object);
				$property->setPropertyName($name);
				$property->setPropertyValue($value);
				$property->save();
				if ($object->isSearchable()) {
					$object->addPropertyToSearchableObject($property);
				}
			}
		}
	}

	function add_reminders($object) {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$popup_count = 0;
		$object->clearReminders(logged_user(), true);
		$typesC = array_var($_POST, 'reminder_type');
		if (!is_array($typesC)) return;
		$durationsC = array_var($_POST, 'reminder_duration');
		$duration_typesC = array_var($_POST, 'reminder_duration_type');
		$subscribersC = array_var($_POST, 'reminder_subscribers');
		foreach ($typesC as $context => $types) {
			$durations = $durationsC[$context];
			$duration_types = $duration_typesC[$context];
			$subscribers = $subscribersC[$context];
			for ($i=0; $i < count($types); $i++) {
				$type = $types[$i];
				$duration = $durations[$i];
				$duration_type = $duration_types[$i];
				$minutes = $duration * $duration_type;
				$reminder = new ObjectReminder();
				$reminder->setMinutesBefore($minutes);
				$reminder->setType($type);
				$reminder->setContext($context);
				$reminder->setObject($object);
				if (isset($subscribers[$i])) {
					$reminder->setUserId(0);
				} else {
					$reminder->setUser(logged_user());
				}
				$date = $object->getColumnValue($context);
				if ($date instanceof DateTimeValue) {
					$rdate = new DateTimeValue($date->getTimestamp() - $minutes * 60);
					$reminder->setDate($rdate);
				}
				$reminder->save();
				$popup_count = $popup_count + ($type == 'reminder_popup' ? 1 : 0);
			}
		}
		
		if (count($popup_count) > 0 && GlobalCache::isAvailable()) {
			$user_ids = Users::findAll(array('id' => true));
			foreach ($user_ids as $uid)	GlobalCache::update('check_for_popup_reminders_'.$uid, 1);
		}
	}

	// ---------------------------------------------------
	//  Link / Unlink
	// ---------------------------------------------------

	function redraw_linked_object_list() {
		$object = get_object_by_manager_and_id(array_var($_GET, 'id'), array_var($_GET, 'man'));
		if (!$object) {
			ajx_current("empty");
			return;
		}

		tpl_assign('linked_objects_object', $object);
		tpl_assign('shortDisplay', false);
		tpl_assign('enableAdding', true);
		tpl_assign('linked_objects', $object->getLinkedObjects());
		$this->setLayout("html");
		$this->setTemplate("list_linked_objects");
	}
	
	function link_object() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		ajx_current("empty");
		$manager_class = array_var($_GET, 'manager');
		$object_id = get_id('object_id');
			
		$object = get_object_by_manager_and_id($object_id, $manager_class);
		if(!($object instanceof ApplicationDataObject)) {
			flash_error(lang('no access permissions'));
			return;
		} // if
		if(!($object->canLinkObject(logged_user()))){
			flash_error(lang('no access permissions'));
			return;
		} // if
		$str_obj = array_var($_GET, 'objects');
		if ($str_obj == null) return;
		try {
			$err_message_list = '';
			DB::beginWork();
			$split = explode(",", $str_obj);
			$succ = 0; $err = 0; $permission_err = false; $object_dnx_err = false;
			foreach ($split as $objid) {
				$parts = explode(":", $objid);
				if ($parts[1] == $object_id && $parts[0] == $manager_class){
					$err++;
					$err_message_list .= ' - ' . lang('error cannot link object to self') . "\n";
					continue;
				}
				$rel_object = get_object_by_manager_and_id($parts[1], $parts[0]);
				if (!($rel_object instanceof ApplicationDataObject)) {
					$err++;
					if (!$object_dnx_err)
						$err_message_list .= ' - ' . lang('object dnx') . "\n";
					$object_dnx_err = true;
					continue;
				} // if
				if (!($rel_object->canLinkObject(logged_user()))) {
					$err++;
					if (!$permission_err)
						$err_message_list .= ' - ' . lang('no access permissions') . "\n";
					$permission_err = true;
					continue;
				} // if
				try {
					$object->linkObject($rel_object);
					if ($object instanceof ProjectDataObject) {
						ApplicationLogs::createLog($object, $object->getWorkspaces(), ApplicationLogs::ACTION_LINK, false, null, true, $objid);
					}
					if ($rel_object instanceof ProjectDataObject) {
						ApplicationLogs::createLog($rel_object, $rel_object->getWorkspaces(), ApplicationLogs::ACTION_LINK, false, null, true, get_class($object->manager()) . ':' . $object->getId());
					}
					$succ++;
				} catch(Exception $e){
					$err++;
				}
			}
			DB::commit();
			$message = "";
			if ($err > 0) {
				$message .= lang("error link object", $err) . "\n" . $err_message_list;
			}
			if ($succ > 0) {
				$message .= lang("success link objects", $succ) . "\n";
			}
			if ($succ == 0 && $err > 0) {
				flash_error($message);
				ajx_current("empty");
			} else if ($succ > 0) {
				flash_success($message);
				if (array_var($_GET, 'reload')) {
					ajx_current("reload");
				}
			}
		} catch (Exception $e) {
			DB::rollback();
			flash_error($e->getMessage());
			ajx_current("empty");
		}
	}

	/**
	 * Function called from other controllers when creating a new object an linking objects to it
	 *
	 * @param void
	 * @return null
	 */
	function link_to_new_object($the_object){
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		if (!$the_object->isNew() && !$the_object->canLinkObject(logged_user())) {
			flash_error(lang("user cannot link objects"));
			return;
		}
		$objects = array_var($_POST, 'linked_objects');
		$the_object->clearLinkedObjects();
		if (is_array($objects)) {
			$err = 0;
			foreach ($objects as $objid) {
				$split = explode(":", $objid);
				if(count($split) == 2){
					if (!class_exists($split[0], true)) continue;
					$object = get_object_by_manager_and_id($split[1], $split[0]);
				}else if (count($split) == 3 && $split[2] == 'isName'){
					$object = ProjectFiles::getByFilename($split[1]);
				} else continue;
				
				if ($object->canLinkObject(logged_user())) {
					$the_object->linkObject($object);
					if ($the_object instanceof ProjectDataObject)
						ApplicationLogs::createLog($the_object, $the_object->getWorkspaces(), ApplicationLogs::ACTION_LINK,false,null,true,get_class($object->manager()).':'.$object->getId());
					if ($object instanceof ProjectDataObject)
						ApplicationLogs::createLog($object, $object->getWorkspaces(), ApplicationLogs::ACTION_LINK,false,null,true,get_class($the_object->manager()).':'.$the_object->getId());
				} else {
					$err++;
				}
			}
			if ($err > 0) {
				flash_error(lang('some objects could not be linked', $err));
			}
		}
	}

	/**
	 * Link object to the object
	 *
	 * @param void
	 * @return null
	 */
	function link_to_object() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$manager_class = array_var($_GET, 'manager');
		$object_id = get_id('object_id');
			
		$object = get_object_by_manager_and_id($object_id, $manager_class);
		if(!($object instanceof ProjectDataObject)) {
			flash_error(lang('no access permissions'));
			//$this->redirectToReferer(get_url('dashboard'));
			ajx_current("empty");
			return;
		} // if
			
		$already_linked_objects = $object->getLinkedObjects();
		$already_linked_objects_ids = null;
		if(is_array($already_linked_objects)) {
			$already_linked_objects_ids = array();
			foreach($already_linked_objects as $already_linked_object) {
				$already_linked_objects_ids[] = $already_linked_object->getId();
			} // foreach
		} // if
			
		$link_data = array_var($_POST, 'link');
		if(!is_array($link_data)) {
			$link_data = array('what' => 'existing_object');
		} // if
			
		tpl_assign('link_to_object', $object);
		tpl_assign('link_data', $link_data);
		tpl_assign('already_linked_objects_ids', $already_linked_objects_ids);
			
		if(is_array(array_var($_POST, 'link'))) {

			if(array_var($link_data, 'what') == 'existing_object') {
				$link_data_info  = explode('::',array_var($link_data, 'object_id'));
				echo $link_data_info [0] . '-' . $link_data_info [1];
				$object2 = get_object_by_manager_and_id($link_data_info[0],$link_data_info[1]);
				if(!($object2 instanceof ProjectDataObject )) {
					flash_error(lang('no object to link'));
					$this->redirectToUrl($object->getLinkedObjectsUrl());
				} // if
				$linked_objects[] = $object2;
			} elseif(array_var($link_data, 'what') == 'new_object') {
				try {
					$linked_objects = ProjectFiles::handleHelperUploads(active_project());
				} catch(Exception $e) {
					flash_error(lang('error upload file'));
					$this->redirectToUrl($object->getLinkedObjectsUrl());
				} // try
			} // if

			if(!is_array($linked_objects) || !count($linked_objects)) {
				flash_error(lang('no objects to link'));
				$this->redirectToUrl($object->getLinkedObjectsUrl());
			} // if

			try {
				DB::beginWork();
					
				$counter = 0;
				foreach($linked_objects as $linked_object) {
					$object->linkObject($linked_object);
					if ($object instanceof ProjectDataObject)
						ApplicationLogs::createLog($object, $object->getWorkspaces(), ApplicationLogs::ACTION_LINK,false,null,true,get_class($linked_object->manager()).':'.$linked_object->getId());
					if ($linked_object instanceof ProjectDataObject)
						ApplicationLogs::createLog($linked_object, $linked_object->getWorkspaces(), ApplicationLogs::ACTION_LINK,false,null,true,get_class($object->manager()).':'.$object->getId());
					$counter++;
				} // foreach
					
				DB::commit();
				flash_success(lang('success link objects', $counter));
				$this->redirectToUrl($object->getObjectUrl());
			} catch(Exception $e) {
				DB::rollback();
					
				if(array_var($link_data, 'what') == 'new_object' && count($linked_objects)) {
					foreach($linked_objects as $linked_object) {
						$linked_object->delete();
					} // foreach
				} // if
				flash_error($e->getMessage());
				ajx_current("empty");
			} // try
		} // if
	} // link_to_object

	/**
	 * Unlink object from related object
	 *
	 * @param void
	 * @return null
	 */
	function unlink_from_object() { // ex detach_from_object() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$manager_class = array_var($_GET, 'manager');
		$object_id = get_id('object_id');
		$object1 = get_object_by_manager_and_id($object_id, $manager_class);
		
		$dont_reload = array_var($_GET, 'dont_reload');
		if (array_var($_GET, 'rel_objects')) {
			$objects_to_unlink = explode(",", array_var($_GET, 'rel_objects'));
		} else {
			$objects_to_unlink = array(array_var($_GET, 'rel_object_manager') .":". get_id('rel_object_id'));
		}
		try {
			DB::beginWork();
			$err = 0; $succ = 0;
			foreach ($objects_to_unlink as $obj) {
				$obj_id = explode(":", $obj);
				
				$rel_object_manager = $obj_id[0];
				$rel_object_id = $obj_id[1];
					
				$object2 = get_object_by_manager_and_id($rel_object_id, $rel_object_manager);
				if(!($object1 instanceof ApplicationDataObject)|| !($object2 instanceof ApplicationDataObject)) {
					flash_error(lang('object not found'));
					ajx_current("empty");
					return;
				} // if
					
				$linked_object = LinkedObjects::findById(array(
					'rel_object_manager' => $manager_class,
					'rel_object_id' => $object_id,
					'object_id' => $rel_object_id,
					'object_manager' => $rel_object_manager,
				)); // findById
				if(!($linked_object instanceof LinkedObject ))
				{ //search for reverse link
					$linked_object = LinkedObjects::findById(array(
						'rel_object_manager' => $rel_object_manager,
						'rel_object_id' => $rel_object_id,
						'object_id' => $object_id,
						'object_manager' => $manager_class,
					)); // findById
				}
		
				if(!($linked_object instanceof LinkedObject )) {
					$err++;
					continue;
				} // if
				
				$linked_object->delete();
	
				if ($object1 instanceof ProjectDataObject)
					ApplicationLogs::createLog($object1, $object1->getWorkspaces(), ApplicationLogs::ACTION_UNLINK,false,null,true,get_class($object2->manager()).':'.$object2->getId());
				if ($object2 instanceof ProjectDataObject)
					ApplicationLogs::createLog($object2, $object2->getWorkspaces(), ApplicationLogs::ACTION_UNLINK,false,null,true,get_class($object1->manager()).':'.$object1->getId());
				
				$succ++;
			}
			DB::commit();
			$message = "";
			if ($err > 0) {
				$message .= lang("error unlink object", $err) . "\n";
			}
			if ($succ > 0) {
				$message .= lang("success unlink object", $succ) . "\n";
			}
			if ($succ == 0 && $err > 0) {
				flash_error($message);
			} else if ($succ > 0) {
				flash_success($message);
			}
			
			flash_success(lang('success unlink object'));
			
			if ($dont_reload) ajx_current("empty");
			else ajx_current("reload");
		} catch(Exception $e) {
			flash_error(lang('error unlink object'));
			DB::rollback();
			ajx_current("empty");
		} // try
	} // unlink_from_object


	/**
	 * Show property list
	 *
	 * @param
	 * @return ObjectProperties
	 */
	function view_properties()
	{
		$manager_class = array_var($_GET, 'manager');
		$object_id = get_id('object_id');
		$obj = get_object_by_manager_and_id ($object_id, $manager_class);

		if (!($obj instanceof ProjectDataObject ))
		{
			flash_error(lang('object dnx'));
			ajx_current("empty");
			return;
		}
		$properties = ObjectProperties::getAllPropertiesByObject($obj);
		if(!($properties instanceof ObjectProperties ))
		{
			flash_error(lang('properties dnx'));
			ajx_current("empty");
			return;
		}
		tpl_assign('properties', $properties);
	} // view_properties
	
	function show_all_linked_objects() {
				
		require_javascript("og/LinkedObjectsManager.js");
		ajx_current("panel", "linkedobject", null, array(
			'linked_object' => array_var($_GET, 'linked_object'),
			'linked_manager' => array_var($_GET, 'linked_manager'),
			'filter_manager' => array_var($_GET, 'filter_manager', ''),
			'linked_object_name' => array_var($_GET, 'linked_object_name'),
			'linked_object_ico' => array_var($_GET, 'linked_object_ico'),
		));
		ajx_replace(true);
	}	

	/**
	 * Update, delete and add new properties
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function update_properties() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$this->setTemplate('add_properties');

		$manager_class = array_var($_GET, 'manager');
		$object_id = get_id('object_id');
		$obj = get_object_by_manager_and_id ($object_id, $manager_class);
		if(!($obj instanceof ProjectDataObject )) {
			flash_error(lang('object dnx'));
			ajx_current("empty");
			return;
		} // if

		if(! logged_user()->getCanManageProperties()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$new_properties = array_var($_POST, 'new_properties');
		$update_properties = array_var($_POST, 'update_properties');
		$delete_properties = array_var($_POST, 'delete_properties');
		if(is_array(array_var($_POST, 'new_properties')) || is_array(array_var($_POST, 'update_properties')) || is_array(array_var($_POST, 'delete_handins'))) {

			try {
				DB::beginWork();
				//add new properties
				foreach ($new_properties as $prop) {
					$property = new ObjectProperty();
					$property->setFromAttributes($prop);
					$property->setRelObjectId($object_id);
					$property->setRelObjectManager($manager_class);
					$property->save();
				}
				foreach ($update_properties as $prop) {
					$property = ObjectProperties::getProperty(array_var($prop,'id')); //ObjectProperties::getPropertyByName($obj, array_var($prop,'name'));
					$property->setPropertyValue(array_var($prop,'value'));
					$property->save();
				}
				foreach ($delete_properties as $prop)
				{
					$property = ObjectProperties::getProperty(array_var($prop,'id')); //ObjectProperties::getPropertyByName($obj, array_var($prop,'name'));
					$prop->delete();
				}
				tpl_assign('properties',ObjectProperties::getAllPropertiesByObject($obj));
				ApplicationLogs::createLog($obj, $obj->getWorkspaces(), ApplicationLogs::ACTION_EDIT);
				DB::commit();
					
				flash_success(lang('success add properties'));
				$this->redirectToReferer($obj->getObjectUrl());
			} catch(Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			} //
		} // if
	} // update_properties

	/**
	 * Show handins list
	 *
	 * @param
	 * @return ObjectHandins
	 */
	function view_handins()
	{
		$manager_class = array_var($_GET, 'manager');
		$object_id = get_id('object_id');
		$obj = get_object_by_manager_and_id ($object_id, $manager_class);

		if (!($obj instanceof ProjectDataObject ))
		{
			flash_error(lang('object dnx'));
			ajx_current("empty");
			return;
		}
		$handins = ObjectHandins::getAllHandinsByObject($obj);
		if(!($handins instanceof ObjectHandins))
		{
			flash_error(lang('handins dnx'));
			ajx_current("empty");
			return;
		}
		tpl_assign('handins', $handins);
	} // view_handins

	/**
	 * Update, delete and add new handins
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function update_handins() {
		$this->setTemplate('add_handins');

		$manager_class = array_var($_GET, 'manager');
		$object_id = get_id('object_id');
		$obj = get_object_by_manager_and_id ($object_id, $manager_class);
		if(!($obj instanceof ProjectDataObject )) {
			flash_error(lang('object dnx'));
			ajx_current("empty");
			return;
		} // if

		if($obj->canEdit()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$new_handins = array_var($_POST, 'new_handins');
		$update_handins = array_var($_POST, 'update_handins');
		$delete_handins = array_var($_POST, 'delete_handins');
		if(is_array(array_var($_POST, 'new_handins')) || is_array(array_var($_POST, 'update_handins')) || is_array(array_var($_POST, 'delete_handins'))) {

			try {
				DB::beginWork();
				//add new handins
				foreach ($new_handins as $handin) {
					$handin = new ObjectHandin();
					$handin->setFromAttributes($handin);
					$handin->setRelObjectId($object_id);
					$handin->setRelObjectManager($manager_class);
					$handin->save();
				}
				foreach ($update_handins as $handin) {
					$handin = ObjectHandins::getHandin(array_var($handin,'id'));
					$handin->setFromAttributes($handin);
					$handin->save();
				}
				foreach ($delete_handins as $handin)
				{
					$handin = ObjectHandins::getHandin(array_var($handin,'id'));
					$handin->delete();
				}
				tpl_assign('handins',ObjectHandins::getAllHandinsByObject($obj));
				ApplicationLogs::createLog($obj, $obj->getWorkspaces(), ApplicationLogs::ACTION_EDIT);
				DB::commit();
					
				flash_success(lang('success add handins'));
				$this->redirectToReferer($obj->getObjectUrl());
			} catch(Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			} //
		} // if
	} // update_handins

	/**
	 * Returns array of queries that will return Dashboard Objects
	 *
	 * @param string $proj_ids
	 * @param string $tag
	 * @param boolean $count if false the query will return objects, if true it will return object count
	 */
	static function getDashboardObjectQueries($project = null, $tag = null, $count = false, $trashed = false, $linkedObject = null, $order = 'updatedOn', $filterName = '', $archived = false, $filterManager = ''){
		if ($trashed && $trashed !== 'all') $order = 'trashedOn';
		else if ($archived) $order = 'archivedOn';
		switch ($order){
			case 'dateCreated':
				$order_crit_companies = '`created_on`';
				$order_crit_contacts = '`created_on`';
				$order_crit_file_revisions = '`created_on`';
				$order_crit_calendar = '`created_on`';
				$order_crit_tasks = '`created_on`';
				$order_crit_milestones = '`created_on`';
				$order_crit_webpages = '`created_on`';
				$order_crit_files = '`created_on`';
				$order_crit_emails = '`received_date`';
				$order_crit_comments = '`created_on`';
				$order_crit_messages = '`created_on`';
				$order_crit_workspaces = '`created_on`';
				break;
			case 'trashedOn':
				$order_crit_companies = '`trashed_on`';
				$order_crit_contacts = '`trashed_on`';
				$order_crit_file_revisions = '`trashed_on`';
				$order_crit_calendar = '`trashed_on`';
				$order_crit_tasks = '`trashed_on`';
				$order_crit_milestones = '`trashed_on`';
				$order_crit_webpages = '`trashed_on`';
				$order_crit_files = '`trashed_on`';
				$order_crit_emails = '`trashed_on`';
				$order_crit_comments = '`trashed_on`';
				$order_crit_messages = '`trashed_on`';
				$order_crit_workspaces = '`updated_on`';
				break;
			case 'archivedOn':
				$order_crit_companies = '`archived_on`';
				$order_crit_contacts = '`archived_on`';
				$order_crit_file_revisions = '`updated_on`';
				$order_crit_calendar = '`archived_on`';
				$order_crit_tasks = '`archived_on`';
				$order_crit_milestones = '`archived_on`';
				$order_crit_webpages = '`archived_on`';
				$order_crit_files = '`archived_on`';
				$order_crit_emails = '`archived_on`';
				$order_crit_comments = '`updated_on`';
				$order_crit_messages = '`archived_on`';
				$order_crit_workspaces = '`completed_on`';
				break;
			case 'name':
				$order_crit_companies = '`name`';
				$order_crit_contacts = "TRIM(CONCAT(' ', `lastname`, `firstname`, `middlename`))";
				$order_crit_file_revisions = "'zzzzzzzzzzzzzz'"; //Revisar
				$order_crit_calendar = '`subject`';
				$order_crit_tasks = '`title`';
				$order_crit_milestones = '`name`';
				$order_crit_webpages = '`title`';
				$order_crit_files = '`filename`';
				$order_crit_emails = '`subject`';
				$order_crit_comments = '`text`';
				$order_crit_messages = '`title`';
				$order_crit_workspaces = '`name`';
				break;
			default:
				$order_crit_companies = '`updated_on`';
				$order_crit_contacts = '`updated_on`';
				$order_crit_file_revisions = '`updated_on`';
				$order_crit_calendar = '`updated_on`';
				$order_crit_tasks = '`updated_on`';
				$order_crit_milestones = '`updated_on`';
				$order_crit_webpages = '`updated_on`';
				$order_crit_files = '`updated_on`';
				$order_crit_emails = '`received_date`';
				$order_crit_comments = '`updated_on`';
				$order_crit_messages = '`updated_on`';
				$order_crit_workspaces = '`updated_on`';
				break;
		}
		if ($project instanceof Project) {
			$proj_ids = $project->getAllSubWorkspacesQuery(true);
			$proj_cond_companies = Companies::getWorkspaceString($proj_ids);
			$proj_cond_messages = ProjectMessages::getWorkspaceString($proj_ids);
			$proj_cond_documents = ProjectFiles::getWorkspaceString($proj_ids);
			$proj_cond_emails = MailContents::getWorkspaceString($proj_ids);
			$proj_cond_events = ProjectEvents::getWorkspaceString($proj_ids);
			$proj_cond_tasks = ProjectTasks::getWorkspaceString($proj_ids);
			$proj_cond_charts = ProjectCharts::getWorkspaceString($proj_ids);
			$proj_cond_milestones = ProjectMilestones::getWorkspaceString($proj_ids);
			$proj_cond_weblinks = ProjectWebpages::getWorkspaceString($proj_ids);
			$proj_cond_contacts = Contacts::getWorkspaceString($proj_ids);
		} else {
			$proj_cond_companies = "true";
			$proj_cond_messages = "true";
			$proj_cond_documents = "true";
			$proj_cond_emails = "true";
			$proj_cond_events = "true";
			$proj_cond_tasks = "true";
			$proj_cond_charts = "true";
			$proj_cond_milestones = "true";
			$proj_cond_weblinks = "true";
			$proj_cond_contacts = "true";
		}			
		
		
		if ($trashed) {
			if ($trashed === 'all') $trashed_cond = '`trashed_on` >= ' . DB::escape(EMPTY_DATETIME);
			else $trashed_cond = '`trashed_on` > ' . DB::escape(EMPTY_DATETIME);
			$archived_cond = '1 = 1'; // Show all objects in trash
			$comments_arch_cond = "1 = 1";
		} else {
			$trashed_cond = '`trashed_on` = ' . DB::escape(EMPTY_DATETIME);
			if ($archived) {
				$archived_cond = "`archived_by_id` > 0";
				$comments_arch_cond = "1 = 0"; // Don't show comments in archived objects listings
			} else {
				$archived_cond = "`archived_by_id` = 0";
				$comments_arch_cond = "1 = 1";
			}
		}
		
		if(isset($tag) && $tag && $tag!='') {
			$tag_str = " AND EXISTS (SELECT * FROM `" . TABLE_PREFIX . "tags` `t` WHERE `tag`= " . DB::escape($tag) . " AND `co`.`id` = `t`.`rel_object_id` AND `t`.`rel_object_manager` = `object_manager_value`) ";
		} else {
			$tag_str = ' ';
		}
		
		if ($linkedObject instanceof ProjectDataObject) {
			$link_id = $linkedObject->getId();
			$link_mgr = get_class($linkedObject->manager());
			$link_str = " AND EXISTS (SELECT * FROM `" . TABLE_PREFIX . "linked_objects` `t` WHERE
			(`t`.`object_id`=".DB::escape($link_id)." AND `t`.object_manager = ".DB::escape($link_mgr). " AND `co`.`id` = `t`.`rel_object_id` AND `t`.`rel_object_manager` = `object_manager_value`) OR
			(`t`.`rel_object_id`=".DB::escape($link_id)." AND `t`.rel_object_manager = ".DB::escape($link_mgr). " AND `co`.`id` = `t`.`object_id` AND `t`.`object_manager` = `object_manager_value`)) ";
		} else {
			$link_str= ' ';
		}
		
		$tag_str .= $link_str;

		$res = array();
		/** If the name of the query ends with Comments it is assumed to be a list of Comments **/
			$cfn = '';
			if ($filterName!=''){
				$cfn = " AND text LIKE '%". $filterName ."%'";				
			}		
		// Notes
		if (module_enabled('notes')) {
			$fn = '';
	
			if ($filterName!=''){
				$fn = " AND title LIKE '%". $filterName ."%'";				
			}
			$permissions = ' AND ( ' . permissions_sql_for_listings(ProjectMessages::instance(), ACCESS_LEVEL_READ, logged_user(), '`project_id`', '`co`') .')';
			if ($filterManager == '' || $filterManager == "ProjectMessages")
			$res['ProjectMessages']  = "SELECT  'ProjectMessages' AS `object_manager_value`, `id` AS `oid`, $order_crit_messages AS `order_value` FROM `" .
			TABLE_PREFIX . "project_messages` `co` WHERE " . $trashed_cond ." AND $archived_cond AND ".$proj_cond_messages . str_replace('= `object_manager_value`', "= 'ProjectMessages'", $tag_str) . $permissions . $fn;
			if ($filterManager == '' || $filterManager == "Comments")
			$res['ProjectMessagesComments'] = "SELECT  'Comments' AS `object_manager_value`, `id` AS `oid`, $order_crit_comments AS `order_value` FROM `" .
			TABLE_PREFIX . "comments` WHERE $trashed_cond AND `rel_object_manager` = 'ProjectMessages' AND `rel_object_id` IN (SELECT `co`.`id` FROM `" .
			TABLE_PREFIX . "project_messages` `co` WHERE `trashed_by_id` = 0 AND $comments_arch_cond AND " . $proj_cond_messages . str_replace('= `object_manager_value`', "= 'ProjectMessages'", $tag_str) . $permissions . $cfn . ")";
			
		}
		
		// Events
		if (module_enabled("calendar")) {
			$fn = '';
			if ($filterName!=''){
				$fn = " AND subject LIKE '%". $filterName ."%'";
			}
			
			$permissions = ' AND ( ' . permissions_sql_for_listings(ProjectEvents::instance(), ACCESS_LEVEL_READ, logged_user(), '`project_id`', '`co`') .')';
			if ($filterManager == '' || $filterManager == "ProjectEvents")
			$res['ProjectEvents'] = "SELECT  'ProjectEvents' AS `object_manager_value`, `id` AS `oid`, $order_crit_calendar AS `order_value` FROM `" .
			TABLE_PREFIX . "project_events` `co` WHERE  " . $trashed_cond ." AND $archived_cond AND ".$proj_cond_events . str_replace('= `object_manager_value`', "= 'ProjectEvents'", $tag_str) . $permissions . $fn;
			if ($filterManager == '' || $filterManager == "Comments")
			$res['ProjectEventsComments'] = "SELECT  'Comments' AS `object_manager_value`, `id` AS `oid`, $order_crit_comments AS `order_value` FROM `" .
			TABLE_PREFIX . "comments` WHERE $trashed_cond AND `rel_object_manager` = 'ProjectEvents' AND `rel_object_id` IN (SELECT `co`.`id` FROM `" .
			TABLE_PREFIX . "project_events` `co` WHERE `trashed_by_id` = 0 AND $comments_arch_cond AND " . $proj_cond_events . str_replace('= `object_manager_value`', "= 'ProjectEvents'", $tag_str) . $permissions . $cfn .")";
		}

		// Documents
		if (module_enabled("documents")) {
			$fn = '';
			if ($filterName!=''){
				$fn = " AND filename LIKE '%". $filterName ."%'";
			}
			$permissions = ' AND ( ' . permissions_sql_for_listings(ProjectFiles::instance(), ACCESS_LEVEL_READ, logged_user(), '`project_id`', '`co`') .')';
			$typestring = array_var($_GET, "typestring");
			if ($typestring) {
				$typecond = " AND  ((SELECT count(*) FROM `" . TABLE_PREFIX . "project_file_revisions` `pfr` WHERE `" .
					"pfr`.`type_string` LIKE ".DB::escape($typestring)." AND `".
					"co`.`id` = `pfr`.`file_id`) > 0)";
			} else {
				$typecond = "";
			}
			if ($filterManager == '' || $filterManager == "ProjectFiles")
			$res['ProjectFiles'] = "SELECT  'ProjectFiles' AS `object_manager_value`, `id` as `oid`, $order_crit_files AS `order_value` FROM `" .
			TABLE_PREFIX . "project_files` `co` WHERE " . $trashed_cond ." AND $archived_cond AND ".$proj_cond_documents . str_replace('= `object_manager_value`', "= 'ProjectFiles'", $tag_str) . $permissions . $typecond . $fn;
			if ($filterManager == '' || $filterManager == "Comments")
			$res['ProjectFilesComments'] = "SELECT  'Comments' AS `object_manager_value`, `id` AS `oid`, $order_crit_comments AS `order_value` FROM `" .
			TABLE_PREFIX . "comments` WHERE $trashed_cond AND `rel_object_manager` = 'ProjectFiles' AND `rel_object_id` IN (SELECT `co`.`id` FROM `" .
			TABLE_PREFIX . "project_files` `co` WHERE `trashed_by_id` = 0 AND $comments_arch_cond AND " . $proj_cond_documents . str_replace('= `object_manager_value`', "= 'ProjectFiles'", $tag_str) . $permissions . $cfn .")";

			if ($trashed) {
				$file_rev_docs = "SELECT `id` FROM `" . TABLE_PREFIX . "project_files` `co` WHERE `trashed_by_id` = 0 AND " . $proj_cond_documents . str_replace('= `object_manager_value`', "= 'ProjectFiles'", $tag_str) . $permissions . $typecond;
				$res['FileRevisions'] = "SELECT 'ProjectFileRevisions' AS `object_manager_value`, `id` AS `oid`, $order_crit_file_revisions AS `order_value` FROM `" .
				TABLE_PREFIX . "project_file_revisions` `co` WHERE $trashed_cond AND `file_id` IN (" . $file_rev_docs . ")";
			}
		}

		// Tasks and Milestones
		if (module_enabled("tasks")) {
			$fn = '';
			if ($filterName!=''){
				$fn = " AND title LIKE '%". $filterName ."%'";
			}
			$completed = ($trashed || $archived) ? '': 'AND `completed_on` = ' . DB::escape(EMPTY_DATETIME);
			$permissions = ' AND ( ' . permissions_sql_for_listings(ProjectTasks::instance(), ACCESS_LEVEL_READ, logged_user(), '`project_id`', '`co`') .')';
			if ($filterManager == '' || $filterManager == "ProjectTasks")
			$res['ProjectTasks'] = "SELECT  'ProjectTasks' AS `object_manager_value`, `id` AS `oid`, $order_crit_tasks AS `order_value` FROM `" .
			TABLE_PREFIX . "project_tasks` `co` WHERE `is_template` = false $completed AND " . $trashed_cond ." AND $archived_cond AND `is_template` = false AND ".$proj_cond_tasks . str_replace('= `object_manager_value`', "= 'ProjectTasks'", $tag_str) . $permissions . $fn;
			if ($filterManager == '' || $filterManager == "Comments")
			$res['ProjectTasksComments'] = "SELECT  'Comments' AS `object_manager_value`, `id` AS `oid`, $order_crit_comments AS `order_value` FROM `" .
			TABLE_PREFIX . "comments` WHERE $trashed_cond AND `rel_object_manager` = 'ProjectTasks' AND `rel_object_id` IN (SELECT `co`.`id` FROM `" .
			TABLE_PREFIX . "project_tasks` `co` WHERE `trashed_by_id` = 0 AND $comments_arch_cond AND `is_template` = false AND " . $proj_cond_tasks . str_replace('= `object_manager_value`', "= 'ProjectTasks'", $tag_str) . $permissions . $cfn .")";

			$fn = '';
			if ($filterName!=''){
				$fn = " AND name LIKE '%". $filterName ."%'";
			}
			$permissions = ' AND ( ' . permissions_sql_for_listings(ProjectMilestones::instance(), ACCESS_LEVEL_READ, logged_user(), '`project_id`', '`co`') .')';
			if ($filterManager == '' || $filterManager == "ProjectMilestones")
			$res['ProjectMilestones'] = "SELECT  'ProjectMilestones' AS `object_manager_value`, `id` AS `oid`, $order_crit_milestones AS `order_value` FROM `" .
			TABLE_PREFIX . "project_milestones` `co` WHERE " . $trashed_cond ." AND $archived_cond AND `is_template` = false AND ".$proj_cond_milestones . str_replace('= `object_manager_value`', "= 'ProjectMilestones'", $tag_str) . $permissions . $fn;
			if ($filterManager == '' || $filterManager == "Comments")
			$res['ProjectMilestonesComments'] = "SELECT  'Comments' AS `object_manager_value`, `id` AS `oid`, $order_crit_comments AS `order_value` FROM `" .
			TABLE_PREFIX . "comments` WHERE $trashed_cond AND `rel_object_manager` = 'ProjectMilestones' AND `rel_object_id` IN (SELECT `co`.`id` FROM `" .
			TABLE_PREFIX . "project_milestones` `co` WHERE `trashed_by_id` = 0 AND $comments_arch_cond AND `is_template` = false AND " . $proj_cond_milestones . str_replace('= `object_manager_value`', "= 'ProjectMilestones'", $tag_str) . $permissions . $cfn .")";
		}

		// Weblinks
		if (module_enabled("weblinks")) {
			$fn = '';
			if ($filterName!=''){
				$fn = " AND title LIKE '%". $filterName ."%'";
			}
			
			$permissions = ' AND ( ' . permissions_sql_for_listings(ProjectWebpages::instance(), ACCESS_LEVEL_READ, logged_user(), '`project_id`', '`co`') .')';
			if ($filterManager == '' || $filterManager == "ProjectWebpages")
			$res['ProjectWebPages'] = "SELECT  'ProjectWebPages' AS `object_manager_value`, `id` AS `oid`, $order_crit_webpages AS `order_value` FROM `" .
			TABLE_PREFIX . "project_webpages` `co` WHERE " . $trashed_cond ." AND $archived_cond AND ".$proj_cond_weblinks . str_replace('= `object_manager_value`', "= 'ProjectWebpages'", $tag_str) . $permissions . $fn;
			if ($filterManager == '' || $filterManager == "Comments")
			$res['ProjectWebPagesComments'] = "SELECT  'Comments' AS `object_manager_value`, `id` AS `oid`, $order_crit_comments AS `order_value` FROM `" .
			TABLE_PREFIX . "comments` WHERE $trashed_cond AND `rel_object_manager` = 'ProjectWebpages' AND `rel_object_id` IN (SELECT `co`.`id` FROM `" .
			TABLE_PREFIX . "project_webpages` `co` WHERE " . $trashed_cond ." AND $comments_arch_cond AND ".$proj_cond_weblinks . str_replace('= `object_manager_value`', "= 'ProjectWebpages'", $tag_str) . $permissions . $cfn . ")";
		}

		// Email
		if (module_enabled("email")) {
			$fn = '';
			if ($filterName!=''){
				$fn = " AND subject LIKE '%". $filterName ."%'";
			}
			$permissions = ' AND ( ' . permissions_sql_for_listings(MailContents::instance(), ACCESS_LEVEL_READ, logged_user(), $project instanceof Project ? $project->getId() : 0, '`co`') .')';
			if ($filterManager == '' || $filterManager == "MailContents")
			$res['MailContents'] = "SELECT  'MailContents' AS `object_manager_value`, `id` AS `oid`, $order_crit_emails AS `order_value` FROM `" .
			TABLE_PREFIX . "mail_contents` `co` WHERE (" . $trashed_cond ." AND $archived_cond AND `is_deleted` = 0 AND ".$proj_cond_emails . str_replace('= `object_manager_value`', "= 'MailContents'", $tag_str) . $permissions .") $fn" ;
			if ($filterManager == '' || $filterManager == "Comments")
			$res['MailContentsComments'] = "SELECT  'Comments' AS `object_manager_value`, `id` AS `oid`, $order_crit_comments AS `order_value` FROM `" .
			TABLE_PREFIX . "comments` WHERE $trashed_cond AND `rel_object_manager` = 'MailContents' AND `rel_object_id` IN (SELECT `co`.`id` FROM `" .
			TABLE_PREFIX . "mail_contents` `co` WHERE `trashed_by_id` = 0 AND $comments_arch_cond AND " . $proj_cond_emails . str_replace('= `object_manager_value`', "= 'MailContents'", $tag_str) . $permissions . $cfn . ")";
		}
		
		// Conacts and Companies
		if (module_enabled("contacts")) {
			$fn = '';
			$fn2 = '';
			if ($filterName!=''){
				$fn = " AND firstname LIKE '%". $filterName ."%'";
				$fn2 = " AND name LIKE '%". $filterName ."%'";
			}
				
			
			// companies
			$permissions = ' AND ( ' . permissions_sql_for_listings(Companies::instance(), ACCESS_LEVEL_READ, logged_user(), '`project_id`', '`co`') .')';
			if ($filterManager == '' || $filterManager == "Companies")
			$res['Companies'] = "SELECT  'Companies' AS `object_manager_value`, `id` as `oid`, $order_crit_companies AS `order_value` FROM `" .
			TABLE_PREFIX . "companies` `co` WHERE " . $trashed_cond ." AND $archived_cond AND ".$proj_cond_companies . str_replace('= `object_manager_value`', "= 'Companies'", $tag_str) . $permissions . $fn2;
			$res['CompaniesComments'] = "SELECT  'Comments' AS `object_manager_value`, `id` AS `oid`, $order_crit_comments AS `order_value` FROM `" .
			TABLE_PREFIX . "comments` WHERE $trashed_cond AND `rel_object_manager` = 'Companies' AND `rel_object_id` IN (SELECT `co`.`id` FROM `" .
			TABLE_PREFIX . "companies` `co` WHERE `trashed_by_id` = 0 AND $comments_arch_cond AND " . $proj_cond_documents . str_replace('= `object_manager_value`', "= 'Companies'", $tag_str) . $permissions . $cfn .")";
			
			
			// contacts
			$permissions = ' AND ( ' . permissions_sql_for_listings(Contacts::instance(), ACCESS_LEVEL_READ, logged_user(), '`project_id`', '`co`') . ')';
			if ($filterManager == '' || $filterManager == "Contacts")
			$res['Contacts'] = "SELECT 'Contacts' AS `object_manager_value`, `id` AS `oid`, $order_crit_contacts AS `order_value` FROM `" .
			TABLE_PREFIX . "contacts` `co` WHERE $trashed_cond AND $archived_cond AND $proj_cond_contacts " .
			str_replace('= `object_manager_value`', "= 'Contacts'", $tag_str) . $permissions . $fn;
			$res['ContactsComments'] = "SELECT  'Comments' AS `object_manager_value`, `id` AS `oid`, $order_crit_comments AS `order_value` FROM `" .
			TABLE_PREFIX . "comments` WHERE $trashed_cond AND `rel_object_manager` = 'Contacts' AND `rel_object_id` IN (SELECT `co`.`id` FROM `" .
			TABLE_PREFIX . "contacts` `co` WHERE `trashed_by_id` = 0 AND $comments_arch_cond AND " . $proj_cond_documents . str_replace('= `object_manager_value`', "= 'Contacts'", $tag_str) . $permissions . $cfn .")";
		}
		
		// Workspaces (only for archived objects view)
		if ($archived) {
			if ($filterManager == '' || $filterManager == "Projects")
			$res['Projects'] = "SELECT  'Projects' AS `object_manager_value`, `id` AS `oid`, $order_crit_workspaces AS `order_value` FROM `" .
			TABLE_PREFIX . "projects` `co` WHERE `completed_on` <> ".DB::escape(EMPTY_DATETIME)." AND `id` IN (".logged_user()->getWorkspacesQuery().")";
		}
		
		if($count){
			foreach ($res as $p => $q){
				$res[$p] ="SELECT count(*) AS `quantity`, '$p' AS `objectName` FROM ( $q ) `table_alias`";
			}
		}
		
		return $res;
	}

	/**
	 *  Returns al objects that will be found on the dashboard.
	 * 	Functions used only in ObjectController
	 *
	 * @param int $page
	 * @param int $objects_per_page
	 * @param string $order
	 * @param string $order_dir can be asc or desc
	 */
	function getDashboardObjects($page, $objects_per_page, $tag=null, $order=null, $order_dir=null, $types = null, $project = null, $trashed = false, $linkedObject = null, $filterName = "", $archived = false, $filterManager = ''){
		///TODO: this method is horrible on performance and should not be here!!!!
		$queries = $this->getDashboardObjectQueries($project, $tag, false, $trashed,$linkedObject, $order, $filterName, $archived, $filterManager);
		if (!$order_dir){
			switch ($order){
				case 'name': $order_dir = 'ASC'; break;
				default: $order_dir = 'DESC';
			}
		}
		if(isset($types) && $types){
			$query = '';
			foreach ($types as $type) {
				if ($query == '')
					$query = $queries[$type];
				else
					$query .= " \n UNION \n" . $queries[$type];
			}
		} //if $type
		else {
			$query = '';
			foreach ($queries as $q){
				if($query == '')
					$query = $q;
				else
					$query .= " \n UNION \n" . $q;
			}

		}
		if($order){
			$query .= " ORDER BY `order_value` ";
			if($order_dir)
				$query .= " " . mysql_real_escape_string($order_dir) . " ";
		}
		else
		$query .= " ORDER BY `order_value` DESC ";
		if($page && $objects_per_page){
			$start=($page-1) * $objects_per_page ;
			$query .=  " LIMIT " . $start . "," . $objects_per_page. " ";
		}
		elseif($objects_per_page)
			$query .= " LIMIT " . $objects_per_page;
			
		$res = DB::execute($query);
		$objects = array();
		if(!$res)  return $objects;
		$rows=$res->fetchAll();
		if(!$rows)  return $objects;
		$index=0;
		foreach ($rows as $row){
			$manager= $row['object_manager_value'];
			$id = $row['oid'];
			if($id && $manager){
				$obj=get_object_by_manager_and_id($id,$manager);
				if($obj->canView(logged_user())){
					$objects[] = $obj;			
				}
			}//if($id && $manager)
		}//foreach
		ProjectDataObjects::populateData($objects);
		$dash_objects = array();
		foreach ($objects as $obj){
			//Logger::log('DASH');
			$dash_object= $obj->getDashboardObject();
			$dash_object['ix'] = $index++;
			$dash_objects[] = $dash_object;	
		}
		return $dash_objects;
	}//getDashboardobjects
		
	/**
	 * Counts dashboard objects
	 *
	 * @return unknown
	 */
	function countDashboardObjects($tag = null, $types = null, $project = null, $trashed = false, $linkedObject = null, $filterName = '', $archived = false, $filterManager = ''){
		if (defined('INFINITE_PAGING') && INFINITE_PAGING) return 10000000;
		///TODO: this method is also horrible in performance and should not be here!!!!
		$queries = $this->getDashboardObjectQueries($project, $tag, true, $trashed, $linkedObject, null, $filterName, $archived, $filterManager);
		if (isset($types) && $types) {
			$query = '';
			foreach ($types as $type) {
				if ($query == '')
					$query = $queries[$type];
				else
					$query .= " \n UNION \n" . $queries[$type];
			}
		} //if $type
		else {
			$query = '';
			foreach ($queries as $q){
				if($query == '')
					$query = $q;
				else
					$query .= " \n UNION \n" . $q;
			}
		}
		$ret = 0;
		//echo $query;die();
		$res = DB::execute($query);
		if(!$res)  return $ret;
		$rows=$res->fetchAll();
		if(!$rows) return  $ret;
		foreach ($rows as $row){
			if(isset($row['quantity']))
			$ret += $row['quantity'];
		}//foreach
		return $ret;
	}
	
	function mark_as_read() {
		ajx_current('empty');
		$csvids = array_var($_GET, 'ids');
		$ids = explode(",", $csvids);
		$this->do_mark_as_read_unread_objects($ids, true, user_config_option('show_emails_as_conversations', true, logged_user()->getId()));
	}
	
	function mark_as_unread() {
		ajx_current('empty');
		$csvids = array_var($_GET, 'ids');
		$ids = explode(",", $csvids);
		$this->do_mark_as_read_unread_objects($ids, false, user_config_option('show_emails_as_conversations', true, logged_user()->getId()));
	}

	function list_objects() {
		
		/* get query parameters */
		$filesPerPage = config_option('files_per_page');
		$start = array_var($_GET,'start') ? (integer)array_var($_GET,'start') : 0;
		$limit = array_var($_GET,'limit') ? array_var($_GET,'limit') : $filesPerPage;

		$order = array_var($_GET,'sort');
		$orderdir = array_var($_GET,'dir');
		$page = (integer) ($start / $limit) + 1;
		$hide_private = !logged_user()->isMemberOfOwnerCompany();
		$tag = array_var($_GET,'tag');
		$typeCSV = array_var($_GET, 'type');
		$types = null;
		if ($typeCSV) {
			$types = explode(",", $typeCSV);
		}
		$objid = array_var($_GET,'linkedobject');
		$mangr = array_var($_GET,'linkedmanager');
		$filterManager = array_var($_GET,'filtermanager','');
		$show_all_linked_objects = ($objid && $objid != 0 && $mangr && $mangr != '') ? true : false; //if we want to show linked objects		
					
		$user = array_var($_GET,'user');
		$trashed = array_var($_GET, 'trashed', false);
		$archived = array_var($_GET, 'archived', false);

		/* if there's an action to execute, do so */
		if (!$show_all_linked_objects){
			$linkedObject = null;
			if (array_var($_GET, 'action') == 'delete') {
				$ids = explode(',', array_var($_GET, 'objects'));
				list($succ, $err) = $this->do_delete_objects($ids);
				if ($err > 0) {
					flash_error(lang('error delete objects', $err));
				} else {
					flash_success(lang('success delete objects', $succ));
				}
			} else if (array_var($_GET, 'action') == 'delete_permanently') {
				$ids = explode(',', array_var($_GET, 'objects'));
				list($succ, $err) = $this->do_delete_objects($ids, null, true);
				if ($err > 0) {
					flash_error(lang('error delete objects', $err));
				}
				if ($succ > 0) {
					flash_success(lang('success delete objects', $succ));
				}
			}else if (array_var($_GET, 'action') == 'markasread') {
				$ids = explode(',', array_var($_GET, 'objects'));
				list($succ, $err) = $this->do_mark_as_read_unread_objects($ids, true);
				
			}else if (array_var($_GET, 'action') == 'markasunread') {
				$ids = explode(',', array_var($_GET, 'objects'));
				list($succ, $err) = $this->do_mark_as_read_unread_objects($ids, false);
				
			}else if (array_var($_GET, 'action') == 'empty_trash_can') {
	
				$Allitems = $this->getDashboardObjects(null,null,null,null,null,null,active_project(),true);
				
				$ids = array();
				for ($i=0;$i<count($Allitems);$i++){
					$id = $Allitems[$i]['object_id'];
					$manager = $Allitems[$i]['manager'];
					$ids[]= $manager.':'.$id;
				}
				list($succ, $err) = $this->do_delete_objects($ids, null, true);		
				if ($err > 0) {
					flash_error(lang('error delete objects', $err));
				}
				if ($succ > 0) {
					flash_success(lang('success delete objects', $succ));
				}
			} else if (array_var($_GET, 'action') == 'archive') {
				$ids = explode(',', array_var($_GET, 'objects'));
				list($succ, $err) = $this->do_archive_unarchive_objects($ids, null, 'archive');
				if ($err > 0) {
					flash_error(lang('error archive objects', $err));
				} else {
					flash_success(lang('success archive objects', $succ));
				}
			} else if (array_var($_GET, 'action') == 'unarchive') {
				$ids = explode(',', array_var($_GET, 'objects'));
				list($succ, $err) = $this->do_archive_unarchive_objects($ids, null, 'unarchive');
				if ($err > 0) {
					flash_error(lang('error unarchive objects', $err));
				} else {
					flash_success(lang('success unarchive objects', $succ));
				}
			}
			else if (array_var($_GET, 'action') == 'unclassify') {
				$ids = explode(',', array_var($_GET, 'objects'));
				$err = 0;
				$succ = 0;
				foreach ($ids as $id) {
					$split = explode(":", $id);
					$type = $split[0];
					if ($type == 'MailContents') {
						$email = MailContents::findById($split[1]);
						if (isset($email) && !$email->isDeleted() && $email->canEdit(logged_user())){
							if (MailController::do_unclassify($email)) $succ++;
							else $err++;
						} else $err++;
					}
				}
				if ($err > 0) {
					flash_error(lang('error unclassify emails', $err));
				} else {
					flash_success(lang('success unclassify emails', $succ));
				}
			}
			else if (array_var($_GET, 'action') == 'tag') {
				$ids = explode(',', array_var($_GET, 'objects'));
				$tagTag = array_var($_GET, 'tagTag');
				list($succ, $err) = $this->do_tag_object($tagTag, $ids);
				if ($err > 0) {
					flash_error(lang('error tag objects', $err));
				} else {
					flash_success(lang('success tag objects', $succ));
				}
			}
			else if (array_var($_GET, 'action') == 'untag') {
				$ids = explode(',', array_var($_GET, 'objects'));
				list($succ, $err) = $this->do_untag_object(array_var($_GET, 'tagTag'), $ids);
				if ($err > 0) {
					flash_error(lang('error untag objects', $err));
				} else {
					flash_success(lang('success untag objects', $succ));
				}
			} else if (array_var($_GET, 'action') == 'restore') {
				$ids = explode(',', array_var($_GET, 'objects'));
				$success = 0; $error = 0;
				foreach ($ids as $id) {
					$split = explode(":", $id);
					$obj = get_object_by_manager_and_id($split[1], $split[0]);
					if ($obj->canDelete(logged_user())) {
						try {
							$obj->untrash();
							ApplicationLogs::createLog($obj, $obj->getWorkspaces(), ApplicationLogs::ACTION_UNTRASH);
							if ($obj->getObjectManagerName() == 'ProjectTasks'){							
								$subtasks = $obj->getAllSubtasks();
								foreach ($subtasks as $st){
									ApplicationLogs::createLog($st, $st->getWorkspaces(), ApplicationLogs::ACTION_UNTRASH);
									$success++;
								}							
							}
							$success++;
						} catch (Exception $e) {
							$error++;
						}
					} else {
						$error++;
					}
				}
				if ($success > 0) {
					flash_success(lang("success untrash objects", $success));
				}
				if ($error > 0) {
					flash_error(lang("error untrash objects", $error));
				}
			} else if (array_var($_GET, 'action') == 'move') {
				$wsid = array_var($_GET, "moveTo");
				$destination = Projects::findById($wsid);
				if (!$destination instanceof Project) {
					$resultMessage = lang('project dnx');
					$resultCode = 1;
				} else if (!can_add(logged_user(), $destination, 'ProjectMessages')) {
					$resultMessage = lang('no access permissions');
					$resultCode = 1;
				} else {
					$ids = explode(',', array_var($_GET, 'objects'));
					$count = 0;
					DB::beginWork();
					foreach ($ids as $id) {
						$split = explode(":", $id);
						$type = $split[0];
						$obj = get_object_by_manager_and_id($split[1], $type);
						$mantainWs = array_var($_GET, "mantainWs");
						if ($type != 'Projects' && $obj->canEdit(logged_user())) {
							if ($type == 'MailContents') {
								$email = MailContents::findById($split[1]);
								$conversation = MailContents::getMailsFromConversation($email);
								foreach ($conversation as $conv_email) {
									$count += MailController::addEmailToWorkspace($conv_email->getId(), $destination, $mantainWs);
									if (array_var($_GET, 'classify_atts') && $conv_email->getHasAttachments()) {
										MailUtilities::parseMail($conv_email->getContent(), $decoded, $parsedEmail, $warnings);
										$classification_data = array();
										for ($j=0; $j < count(array_var($parsedEmail, "Attachments", array())); $j++) {
											$classification_data["att_".$j] = true;		
										}
										$tags = implode(",", $conv_email->getTagNames());
										MailController::classifyFile($classification_data, $conv_email, $parsedEmail, array($destination), $mantainWs, $tags);
									}								
								}
								$count++;
							} else {
								if (!$mantainWs || $type == 'ProjectTasks' || $type == 'ProjectMilestones') {
									$removed = "";
									$ws = $obj->getWorkspaces();
									foreach ($ws as $w) {
										if (can_add(logged_user(), $w, $type)) {
											$obj->removeFromWorkspace($w);
											$removed .= $w->getId() . ",";
										}
									}
									$removed = substr($removed, 0, -1);
									$log_action = ApplicationLogs::ACTION_MOVE;
									$log_data = ($removed == "" ? "" : "from:$removed;") . "to:$wsid";
								} else {
									$log_action = ApplicationLogs::ACTION_COPY;
									$log_data = "to:$wsid";
								}
								$obj->addToWorkspace($destination);
								ApplicationLogs::createLog($obj, $obj->getWorkspaces(), $log_action, false, null, true, $log_data);
								$count++;
							}
						}
					}
					if ($count > 0) {
						$reload = true;
						DB::commit();
						flash_success(lang("success move objects", $count));
					} else {
						DB::rollback();
					}
				}
			}
		}else{
			$linkedObject = get_object_by_manager_and_id($objid,$mangr);				
		}
		$filterName = array_var($_GET,'name');
		$result = null;
		
		/* perform queries according to type*/
		$project = active_project();
		if (!$show_all_linked_objects){
			$total_items = $this->countDashboardObjects($tag, $types, $project, $trashed, $linkedObject, $filterName, $archived, $filterManager);
		}else{
			$total_items = (defined('INFINITE_PAGING') && INFINITE_PAGING) ? 10000000 : count($linkedObject->getAllLinkedObjects());			
		}
		if ($total_items < ($page - 1) * $limit){
			$page = 1;
			$start = 0;
		}
		if (!$show_all_linked_objects){
			$result = $this->getDashboardObjects($page, $filesPerPage, $tag, $order, $orderdir, $types, $project, $trashed, $linkedObject, $filterName, $archived, $filterManager);
		}else{
			$result = LinkedObjects::getLinkedObjectsWithPaging($page, $order, $orderdir, $objid,$mangr, $filesPerPage);
		}
		if(!$result) $result = array();
				
		/* prepare response object */
		$listing = array(
			"totalCount" => $total_items,
			"start" => $start,
			"objects" => $result
		);
		ajx_extra_data($listing);
		tpl_assign("listing", $listing);
		
		if (isset($reload) && $reload) ajx_current("reload");
		else ajx_current("empty");
	}

	function do_tag_object($tag, $ids, $manager = null) {
		$err = $succ = 0;
		foreach ($ids as $id) {
			if (trim($id) != '') {
				try {
					if($manager){
						$obj = get_object_by_manager_and_id($id, $manager);
						Tags::addObjectTag($tag, $obj, $obj->getProject());
					}
					else{ //call from dashboard, format is manager:id
						$split = explode(":", $id);
						$obj = get_object_by_manager_and_id($split[1], $split[0]);
						Tags::addObjectTag($tag, $obj, $obj->getProject());
					}
					if ($obj instanceof ProjectDataObject){
						if ($obj->isSearchable())
						$obj->addTagsToSearchableObject();
						ApplicationLogs::createLog($obj, $obj->getWorkspaces(), ApplicationLogs::ACTION_TAG,false,null,true,$tag);
					}
					$succ++;
				} catch (Exception $e) {
					$err ++;
				}
			}
		}
		return array($succ, $err);
	}
	
	function do_untag_object($tag, $ids, $manager = null) {
		$err = $succ = 0;
		foreach ($ids as $id) {
			if (trim($id) != '') {
				try {
					if($manager){
						$obj = get_object_by_manager_and_id($id, $manager);
						if ($tag) {
							$obj->deleteTag($tag);
						} else {
							$obj->clearTags();
						}
					}
					else{ //call from dashboard, format is manager:id
						$split = explode(":", $id);
						$obj = get_object_by_manager_and_id($split[1], $split[0]);
						if ($tag) {
							$obj->deleteTag($tag);
						} else {
							$obj->clearTags();
						}
							
					}
					$succ++;
				} catch (Exception $e) {
					$err ++;
				}
			}
		}
		return array($succ, $err);
	}
	
	function view(){
		$id = array_var($_GET,'id');
		$manager = array_var($_GET,'manager');
		$obj = get_object_by_manager_and_id($id,$manager);
		if(!($obj instanceof DataObject )) {
			flash_error(lang('object dnx'));
			ajx_current("empty");
			return;
		} // if

		if(! $obj->canView( logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
			
		redirect_to($obj->getObjectUrl(),true);
	}

	function do_delete_objects($ids, $manager=null, $permanent = false) {
		$err = 0; // count errors
		$succ = 0; // count files deleted
		foreach ($ids as $id) {
			try {
				if (trim($id)!=''){
					if ($manager){
						$obj = get_object_by_manager_and_id($id, $manager);
						if ($obj->canDelete(logged_user())) {
							if ($permanent) {
								if ($obj instanceof MailContent) {
									$obj->delete(false);
								} else {
									if ($obj->getObjectManagerName()== 'ProjectTasks') {
										$subtasks = $obj->getAllSubtasks();
										foreach ($subtasks as $st){
											ApplicationLogs::createLog($st, $st->getWorkspaces(), ApplicationLogs::ACTION_DELETE);							
										}
									}
									$obj->delete();
								}
								ApplicationLogs::createLog($obj, $obj->getWorkspaces(), ApplicationLogs::ACTION_DELETE);
								$succ++;
							} else if ($obj->isTrashable()) {
								if ($obj->getObjectManagerName()== 'ProjectTasks') {
									$subtasks = $obj->getAllSubtasks();
									foreach ($subtasks as $st){
										ApplicationLogs::createLog($st, $st->getWorkspaces(), ApplicationLogs::ACTION_TRASH);	
										$succ++;						
									}
								}								
								$obj->trash();
								ApplicationLogs::createLog($obj, $obj->getWorkspaces(), ApplicationLogs::ACTION_TRASH);
								$succ++;
							}
						}
					} else { //call from dashboard, format is manager:id
						$split = explode(":", $id);
						$obj = get_object_by_manager_and_id($split[1], $split[0]);
						if (!$obj instanceof ApplicationDataObject) {
							break;
						}
						if ($obj->canDelete(logged_user())) {
							if ($permanent) {
								if ($obj instanceof MailContent) {
									$obj->delete(false);
								} else {
									if ($obj->getObjectManagerName()== 'ProjectTasks') {
										$subtasks = $obj->getAllSubtasks();
										foreach ($subtasks as $st){
											ApplicationLogs::createLog($st, $st->getWorkspaces(), ApplicationLogs::ACTION_DELETE);							
										}
									}
									$obj->delete();
								}
								ApplicationLogs::createLog($obj, $obj->getWorkspaces(), ApplicationLogs::ACTION_DELETE);
								$succ++;
							} else if ($obj->isTrashable()) {
								if ($obj->getObjectManagerName()== 'ProjectTasks') {
									$subtasks = $obj->getAllSubtasks();
									foreach ($subtasks as $st){
										ApplicationLogs::createLog($st, $st->getWorkspaces(), ApplicationLogs::ACTION_TRASH);	
										$succ++;						
									}
								}
								$obj->trash();
								ApplicationLogs::createLog($obj, $obj->getWorkspaces(), ApplicationLogs::ACTION_TRASH);
								$succ++;
							}
						}
					}
				}
			} catch(Exception $e) {
				$err ++;
			} // try
		}
		return array($succ, $err);
	}
	
	function do_archive_unarchive_objects($ids, $manager=null, $action='archive') {
		$err = 0; // count errors
		$succ = 0;
		foreach ($ids as $id) {
			try {
				if (trim($id)!=''){
					if ($manager){
						$obj = get_object_by_manager_and_id($id, $manager);
						if ($obj->canEdit(logged_user())) {
							if ($action == 'archive') {
								if ($obj->getObjectManagerName()== 'ProjectTasks') {
										$subtasks = $obj->getAllSubtasks();
										foreach ($subtasks as $st){
											ApplicationLogs::createLog($st, $st->getWorkspaces(), ApplicationLogs::ACTION_ARCHIVE);	
											$succ++;						
										}
								}								
								$obj->archive();
								$succ++;
								ApplicationLogs::createLog($obj, $obj->getWorkspaces(), ApplicationLogs::ACTION_ARCHIVE);
							} else if ($action == 'unarchive') {
								if ($obj->getObjectManagerName()== 'ProjectTasks') {
										$subtasks = $obj->getAllSubtasks();
										foreach ($subtasks as $st){
											ApplicationLogs::createLog($st, $st->getWorkspaces(), ApplicationLogs::ACTION_UNARCHIVE);
											$succ++;							
										}
								}
								$obj->unarchive();
								$succ++;
								ApplicationLogs::createLog($obj, $obj->getWorkspaces(), ApplicationLogs::ACTION_UNARCHIVE);
							}
						} else {
							$err ++;
						}
					} else { //call from dashboard, format is manager:id
						$split = explode(":", $id);
						$obj = get_object_by_manager_and_id($split[1], $split[0]);
						if (!$obj instanceof ApplicationDataObject) {
							$err ++;
							continue;
						}
						if ($obj->canEdit(logged_user())) {
							$workspaces = $obj instanceof Project ? null : $obj->getWorkspaces();
							if ($action == 'archive') {
								if ($obj->getObjectManagerName()== 'ProjectTasks') {
										$subtasks = $obj->getAllSubtasks();
										foreach ($subtasks as $st){
											ApplicationLogs::createLog($st, $st->getWorkspaces(), ApplicationLogs::ACTION_ARCHIVE);
											$succ++;							
										}
								}
								$obj->archive();
								$succ++;
								ApplicationLogs::createLog($obj, $workspaces, ApplicationLogs::ACTION_ARCHIVE);
							} else if ($action == 'unarchive') {
								if ($obj->getObjectManagerName()== 'ProjectTasks') {
										$subtasks = $obj->getAllSubtasks();
										foreach ($subtasks as $st){
											ApplicationLogs::createLog($st, $st->getWorkspaces(), ApplicationLogs::ACTION_UNARCHIVE);
											$succ++;							
										}
								}
								$obj->unarchive();
								$succ++;
								ApplicationLogs::createLog($obj, $workspaces, ApplicationLogs::ACTION_UNARCHIVE);
							}
						} else {
							$err ++;
						}
					}
				}
			} catch(Exception $e) {
				$err ++;
			} // try
		}
		return array($succ, $err);
	}

	function do_mark_as_read_unread_objects($ids, $read, $mark_conversation = false) {
		$err = 0; // count errors
		$succ = 0; // count updated objects
		foreach ($ids as $str_id) {
			try {
				if (trim($str_id) != ''){
					$exploded = explode(":", $str_id);
					$manager = array_var($exploded, 0);
					$id = array_var($exploded, 1);
					if ($manager) {
						$obj = get_object_by_manager_and_id($id, $manager);
						if ($obj) {
							$obj->setIsRead(logged_user()->getId(), $read);
							if ($obj instanceof MailContent && $mark_conversation) {
								$emails_in_conversation = MailContents::getMailsFromConversation($obj);
								foreach ($emails_in_conversation as $email) {
									$email->setIsRead(logged_user()->getId(), $read);
								}
							}
						}
					}
					$succ++;
				}
			} catch(Exception $e) {
				$err ++;
			} // try
		}
		return array($succ, $err);
	}
	
	function move() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		ajx_current("empty");
		$ids = array_var($_GET, 'ids');
		if (!$ids) return;
		$wsid = array_var($_GET, 'ws');
		$keep = array_var($_GET, 'keep', 1) == 1;
		$atts = array_var($_GET, 'atts', 0) == 1;
		$workspace = Projects::findById($wsid);
		if (!$workspace instanceof Project) {
			flash_error(lang('project dnx'));
			return;
		}
		$id_list = explode(",", $ids);
		$err = 0;
		$succ = 0;
		foreach ($id_list as $cid) {
			list($manager, $id) = explode(":", $cid);
			if (isset($maganer) && $maganer == 'Projects') continue;
			try {
				$obj = get_object_by_manager_and_id($id, $manager);
				if ($obj instanceof ProjectDataObject && $obj->canEdit(logged_user())) {
					if ($obj instanceof MailContent) {
						$conversation = MailContents::getMailsFromConversation($obj);
						$count = 0;
						foreach ($conversation as $conv_email) {
							$count += MailController::addEmailToWorkspace($conv_email->getId(), $workspace, $keep);
							if (array_var($_GET, 'atts') && $conv_email->getHasAttachments()) {
								MailUtilities::parseMail($conv_email->getContent(), $decoded, $parsedEmail, $warnings);
								$classification_data = array();
								for ($j=0; $j < count(array_var($parsedEmail, "Attachments", array())); $j++) {
									$classification_data["att_".$j] = true;		
								}
								$tags = implode(",", $conv_email->getTagNames());
								MailController::classifyFile($classification_data, $conv_email, $parsedEmail, array($workspace), $keep, $tags);
							}
						}
						$succ++;
					} else {
						$remain = 0;
						if (!$keep || $obj instanceof ProjectTask || $obj instanceof ProjectMilestone) { // Tasks and Milestones can have only 1 workspace
							$removed = "";
							$ws = $obj->getWorkspaces();
							foreach ($ws as $w) {
								if (can_add(logged_user(), $w, get_class($obj->manager()))) {
									$obj->removeFromWorkspace($w);
									$removed .= $w->getId() . ",";
								} else {
									$remain++;
								}
							}
							$removed = substr($removed, 0, -1);
							$log_action = ApplicationLogs::ACTION_MOVE;
							$log_data = ($removed == "" ? "" : "from:$removed;") . "to:$wsid";
						} else {
							$log_action = ApplicationLogs::ACTION_COPY;
							$log_data = "to:$wsid";
						}
						if ($remain > 0 && ($obj instanceof ProjectTask || $obj instanceof ProjectMilestone)) {
							$err++;
						} else {
							$obj->addToWorkspace($workspace);
							ApplicationLogs::createLog($obj, $obj->getWorkspaces(), $log_action, false, null, true, $log_data);
							$succ++;
						}
					}
				} else {
					$err++;
				}
			} catch (Exception $e) {
				$err++;
			}
		}
		if ($err > 0) {
			flash_error(lang("error move objects", $err));
		} else {
			flash_success(lang("success move objects", $succ));
		}
	}

	function view_history(){
		$id = array_var($_GET,'id');
		$manager = array_var($_GET,'manager');
		$obj = get_object_by_manager_and_id($id,$manager);
		if(!($obj instanceof ApplicationDataObject )) {
			flash_error(lang('object dnx'));
			ajx_current("empty");
			return;
		} // if
		if(! $obj->canView( logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if		
		if ($obj instanceof User && (logged_user()->getId() != $id && !logged_user()->isAdministrator())){
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
		$logs = ApplicationLogs::getObjectLogs($obj);
		$logs_read = ApplicationReadLogs::getObjectLogs($obj);
		
		tpl_assign('user_id', get_id());
		tpl_assign('object',$obj);
		tpl_assign('logs',$logs);
		tpl_assign('logs_read',$logs_read);
	}

	// ---------------------------------------------------
	//  Subscriptions
	// ---------------------------------------------------

	/**
	 * Subscribe to object
	 *
	 * @param void
	 * @return null
	 */
	function subscribe() {
		ajx_current("reload");

		$id = array_var($_GET,'id');
		$manager = array_var($_GET,'manager');
		$object = get_object_by_manager_and_id($id, $manager);
		if(!($object instanceof ApplicationDataObject)) {
			flash_error(lang('message dnx'));
			return;
		} // if

		if(!$object->canView(logged_user())) {
			flash_error(lang('no access permissions'));
			return ;
		} // if

		try {
			$object->subscribeUser(logged_user());
			ApplicationLogs::createLog($object, $object->getWorkspaces(), ApplicationLogs::ACTION_SUBSCRIBE, false, true, true, logged_user()->getId());
			flash_success(lang('success subscribe to object'));
		} catch (Exception $e) {
			flash_error(lang('error subscribe to object'));
		}
	} // subscribe

	/**
	 * Unsubscribe from object
	 *
	 * @param void
	 * @return null
	 */
	function unsubscribe() {
		ajx_current("reload");

		$id = array_var($_GET,'id');
		$manager = array_var($_GET,'manager');
		$object = get_object_by_manager_and_id($id, $manager);
		if(!($object instanceof ApplicationDataObject)) {
			flash_error(lang('message dnx'));
			return;
		} // if

		if(!$object->canView(logged_user())) {
			flash_error(lang('no access permissions'));
			return;
		} // if

		try {
			$object->unsubscribeUser(logged_user());
			ApplicationLogs::createLog($object, $object->getWorkspaces(), ApplicationLogs::ACTION_UNSUBSCRIBE, false, null, true, logged_user()->getId());
			flash_success(lang('success unsubscribe to object'));
		} catch (Exception $e) {
			flash_error(lang('error unsubscribe to object'));
		}
	} // unsubscribe

	function send_reminders() {
		ajx_current("empty");
		try {
			$sent = Notifier::sendReminders();
			flash_success("success sending reminders", $sent);
		} catch (Exception $e) {
			flash_error($e->getMessage());
		}
	}

	/**
	 * Properties are sent as POST name:values
	 *
	 */
	function save_properties() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		ajx_current("empty");
		$id = array_var($_GET,'id');
		$manager = array_var($_GET,'manager');
		$object = get_object_by_manager_and_id($id, $manager);
		if (!$object->canEdit(logged_user())) {
			//flash_error(lang('no access permissions'));
			return ;
		}
		try {
			$count = 0;
			foreach ($_POST as $n => $v) {
				$object->setProperty($n, $v);
				$count++;
			}
		} catch (Exception $e) {

		}
	}

	function untrash() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$manager_class = array_var($_GET, 'manager');
		$object_id = get_id('object_id');
		$object = get_object_by_manager_and_id($object_id, $manager_class);
		if ($object instanceof ApplicationDataObject && $object->canDelete(logged_user())) {
			try {
				DB::beginWork();
				$object->untrash();
				$ws = $object->getWorkspaces();
				ApplicationLogs::createLog($object, $ws, ApplicationLogs::ACTION_UNTRASH);
				DB::commit();
				flash_success(lang("success untrash object"));
			} catch (Exception $e) {
				flash_error(lang("error untrash object"));
				DB::rollback();
			}
		} else {
			flash_error(lang("no access permissions"));
		}
		ajx_current("back");
	}

	function delete_permanently() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$manager_class = array_var($_GET, 'manager');
		$object_id = get_id('object_id');
		$object = get_object_by_manager_and_id($object_id, $manager_class);
		if ($object instanceof ProjectDataObject && $object->canDelete(logged_user())) {
			try {
				DB::beginWork();
				$ws = $object->getWorkspaces();
				$object->delete();
				ApplicationLogs::createLog($object, $ws, ApplicationLogs::ACTION_DELETE);
				flash_success(lang("success delete object"));
				DB::commit();
			} catch (Exception $e) {
				DB::rollback();
				flash_error(lang("error delete object"));
			}
		} else {
			flash_error(lang("no access permissions"));
		}
		ajx_current("back");
	}

	function trash() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		ajx_current("empty");
		$csvids = array_var($_GET, 'ids');
		if (!$csvids && array_var($_GET, 'manager') && array_var($_GET, 'object_id')) {
			$csvids = array_var($_GET, 'manager') . ":" . array_var($_GET, 'object_id');
			ajx_current("back");
		}
		$ids = explode(",", $csvids);
		$count = 0;
		$err = 0;
		foreach ($ids as $id) {
			try {
				$parts = explode(":", $id);
				$object = get_object_by_manager_and_id($parts[1], $parts[0]);
				if ($object instanceof ProjectDataObject && $object->canDelete(logged_user())) {
					$object->trash();
					ApplicationLogs::createLog($object, $object->getWorkspaces(), ApplicationLogs::ACTION_TRASH);
					$count++;
				} else {
					$err++;
				}
			} catch (Exception $e) {
				$err++;
			}
		}
		if ($err > 0) {
			flash_error(lang("error trash objects", $err));
		} else {
			flash_success(lang("success trash objects", $count));
			if (array_var($_GET, 'manager') == "MailContents" || array_var($_GET, 'current') == 'mails-panel') {
				//ajx_add("mails-containerpanel", "reload");
			}
		}
	}
	
	function tag() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		ajx_current("empty");
		$csvids = array_var($_GET, 'ids');
		$tag = trim(array_var($_GET, 'tag', ''));
		if (!$tag) {
			flash_error(lang("no tag specified"));
		}
		$ids = explode(",", $csvids);
		$count = 0;
		$err = 0;
		foreach ($ids as $id) {
			try {
				$parts = explode(":", $id);
				$object = get_object_by_manager_and_id($parts[1], $parts[0]);
				if ($object instanceof ProjectDataObject && $object->canEdit(logged_user())) {
					if ($object instanceof MailContent && user_config_option('show_emails_as_conversations', true, logged_user()->getId())) {
						$emails = MailContents::getMailsFromConversation($object);
						foreach ($emails as $email) {
							if (!$email->hasTag($tag)) {
								$email->addTag($tag);
								ApplicationLogs::createLog($email, $email->getWorkspaces(), ApplicationLogs::ACTION_TAG);
							}
						}
					} else {
						if (!$object->hasTag($tag)) {
							$object->addTag($tag);
							ApplicationLogs::createLog($object, $object->getWorkspaces(), ApplicationLogs::ACTION_TAG);
						}
					}
					$count++;
				} else {
					$err++;
				}
			} catch (Exception $e) {
				$err++;
			}
		}
		if ($count > 0) evt_add('tag added', array("name" => $tag));
		if ($err > 0) {
			flash_error(lang("error tag objects", $err));
		} else {
			flash_success(lang("success tag objects", $count));
		}
	}
	
	function untag() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		ajx_current("empty");
		$csvids = array_var($_GET, 'ids');
		$tag = trim(array_var($_GET, 'tag', ''));
		$all = array_var($_GET, 'all');
		if (!$tag && !$all) {
			flash_error(lang("no tag specified"));
			return;
		}
		$ids = explode(",", $csvids);
		$count = 0;
		$err = 0;
		foreach ($ids as $id) {
			try {
				$parts = explode(":", $id);
				$object = get_object_by_manager_and_id($parts[1], $parts[0]);
				if ($object instanceof ProjectDataObject && $object->canEdit(logged_user())) {
					if ($object instanceof MailContent && user_config_option('show_emails_as_conversations', true, logged_user()->getId())) {
						$emails = MailContents::getMailsFromConversation($object);
						foreach ($emails as $email) {
							if ($all) {
								$email->clearTags();
							} else {
								$email->deleteTag($tag);
							}
							ApplicationLogs::createLog($email, $email->getWorkspaces(), ApplicationLogs::ACTION_UNTAG);
						}
					} else {
						if ($all) {
							$object->clearTags();
						} else {
							$object->deleteTag($tag);
						}
						ApplicationLogs::createLog($object, $object->getWorkspaces(), ApplicationLogs::ACTION_UNTAG);
					}
					$count++;
				} else {
					$err++;
				}
			} catch (Exception $e) {
				$err++;
			}
		}
		if ($err > 0) {
			flash_error(lang("error untag objects", $err));
		} else {
			flash_success(lang("success untag objects", $count));
		}
	}

	/**
	 * Clears old objects in trash according to config option days_on_trash
	 *
	 */
	function purge_trash() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		ajx_current("empty");
		try {
			$deleted = Trash::purge_trash();
			flash_success("success purging trash", $deleted);
		} catch (Exception $e) {
			flash_error($e->getMessage());
		}
	}
	
	function archive() {
		ajx_current("empty");
		$csvids = array_var($_GET, 'ids');
		if (!$csvids && array_var($_GET, 'manager') && array_var($_GET, 'object_id')) {
			$csvids = array_var($_GET, 'manager') . ":" . array_var($_GET, 'object_id');
			ajx_current("back");
		}
		$ids = explode(",", $csvids);
		$count = 0;
		$err = 0;
		foreach ($ids as $id) {
			try {
				$parts = explode(":", $id);
				$object = get_object_by_manager_and_id($parts[1], $parts[0]);
				if ($object instanceof ProjectDataObject && $object->canEdit(logged_user())) {
					$object->archive();
					ApplicationLogs::createLog($object, $object->getWorkspaces(), ApplicationLogs::ACTION_ARCHIVE);
					$count++;
				} else {
					$err++;
				}
			} catch (Exception $e) {
				$err++;
			}
		}
		if ($err > 0) {
			flash_error(lang("error archive objects", $err));
		} else {
			flash_success(lang("success archive objects", $count));
		}
	}
	
	function unarchive() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$manager_class = array_var($_GET, 'manager');
		$object_id = get_id('object_id');
		$object = get_object_by_manager_and_id($object_id, $manager_class);
		if ($object instanceof ApplicationDataObject && $object->canEdit(logged_user())) {
			try {
				DB::beginWork();
				if ($object->getObjectManagerName()== 'ProjectTasks') {
					$subtasks = $object->getAllSubtasks();						
				}				
				$object->unarchive();
				$ws = $object->getWorkspaces();				
				ApplicationLogs::createLog($object, $ws, ApplicationLogs::ACTION_UNARCHIVE);
				if (isset ($subtasks)){
					foreach ($subtasks as $st){
						ApplicationLogs::createLog($st, $st->getWorkspaces(), ApplicationLogs::ACTION_UNARCHIVE);							
					}
				}
				DB::commit();
				flash_success(lang("success unarchive objects", 1));
			} catch (Exception $e) {
				DB::rollback();
				flash_error(lang("error unarchive objects", 1));
			}
		} else {
			flash_error(lang("no access permissions"));
		}
		ajx_current("back");
	}
	

	function popup_reminders() {
		ajx_current("empty");
		
		// if no new popup reminders don't make useless queries
		if (GlobalCache::isAvailable()) {
			$check = GlobalCache::get('check_for_popup_reminders_'.logged_user()->getId(), $success);
			if ($success && $check == 0) return;
		}
		
		$reminders = ObjectReminders::getDueReminders("reminder_popup");
/*		
		// look for reminders to send in the next 15 minutes that will not be shown in time, if found then show them now.
		$next_reminders = ObjectReminders::getNextDueReminders("reminder_popup", DateTimeValueLib::now(), new DateTimeValue(time() + 15*60));
		$reminders = array_merge($reminders, $next_reminders);
*/		
		$popups = array();
		foreach ($reminders as $reminder) {
			$object = $reminder->getObject();
			$context = $reminder->getContext();
			$type = $object->getObjectTypeName();
			$date = $object->getColumnValue($reminder->getContext());
			if (!$date instanceof DateTimeValue) continue;
			if ($object->isTrashed()) {
				$reminder->delete();
				continue;
			}
			// convert time to the user's locale
			$timezone = logged_user()->getTimezone();
			if ($date->getTimestamp() + 5*60 < DateTimeValueLib::now()->getTimestamp()) {
				// don't show popups older than 5 minutes
				$reminder->delete();
				continue;
			}
			if ($reminder->getUserId() == 0) {
				if (!$object->isSubscriber(logged_user())) {
					// reminder for subscribers and user is not subscriber
					continue;
				}
			} else if ($reminder->getUserId() != logged_user()->getId()) {
				continue;
			}
			if ($context == "due_date" && $object instanceof ProjectTask) {
				if ($object->isCompleted()) {
					// don't show popups for completed tasks
					$reminder->delete();
					continue;
				}
			}
			$url = $object->getViewUrl();
			$link = '<a href="#" onclick="og.openLink(\''.$url.'\');return false;">'.clean($object->getObjectName()).'</a>';
			evt_add("popup", array(
				'title' => lang("$context $type reminder"),
				'message' => lang("$context $type reminder desc", $link, format_datetime($date)),
				'type' => 'reminder',
				'sound' => 'info'
				));
				if ($reminder->getUserId() == 0) {
					// reminder is for all subscribers, so change it for one reminder per user (except logged_user)
					// otherwise if deleted it won't notify other subscribers and if not deleted it will keep notifying
					// logged user
					$subscribers = $object->getSubscribers();
					foreach ($subscribers as $subscriber) {
						if ($subscriber->getId() != logged_user()->getId()) {
							$new = new ObjectReminder();
							$new->setContext($reminder->getContext());
							$new->setDate($reminder->getDate());
							$new->setMinutesBefore($reminder->getMinutesBefore());
							$new->setObject($object);
							$new->setUser($subscriber);
							$new->setType($reminder->getType());
							$new->save();
						}
					}
				}
				$reminder->delete();
		}
		
		// popup reminders already checked for logged user
		if (GlobalCache::isAvailable()) {
			$today_next_reminders = ObjectReminders::findAll(array(
				'conditions' => array(
					"`date` > ? AND `date` < ?", DateTimeValueLib::now(), DateTimeValueLib::now()->endOfDay(),
				),
				'limit' => config_option('cron reminder limit', 100)
			));
			
			if (count($today_next_reminders) == 0) {
				GlobalCache::update('check_for_popup_reminders_'.logged_user()->getId(), 0);
			}
		}
	}

	function share() {
		$id = array_var($_GET, 'object_id');
		$manager = array_var($_GET, 'manager');
		$obj = get_object_by_manager_and_id($id, $manager);

		if(!($obj instanceof DataObject )) {
			flash_error(lang('object dnx'));
			ajx_current("empty");
			return;
		} // if
			
		$contacts = Contacts::getAll();
		$allEmails = array();
		$emailAndComp = array();
		foreach ($contacts as $contact) {
			if (trim($contact->getEmail()) != "") {
				$emailStr = str_replace(",", " ", $contact->getFirstname() . ' ' . $contact->getLastname() . ' <' . $contact->getEmail() . '>');
				$allEmails[] = $emailStr;
				if ($contact->getCompany()) $emailAndComp[$emailStr] = $contact->getCompany()->getId();
			}
		}
			
		$companies = Companies::getAll();
		$allCompanies = array();
		foreach ($companies as $comp) {
			$allCompanies[$comp->getId()] = $comp->getName();
		}
			
		$actuallySharing = array();
		$users = SharedObjects::getUsersSharing($id, $manager);
		foreach ($users as $u) {
			$user = Users::findById($u->getUserId());
			if ($user) $actuallySharing[] = array('name' => $user->getDisplayName(), 'email' => $user->getEmail(), 'company' => $user->getCompany()->getName());
		}
			
		tpl_assign('allEmails', $allEmails);
		tpl_assign('allCompanies', $allCompanies);
		tpl_assign('emailAndComp', $emailAndComp);
		tpl_assign('actuallySharing', $actuallySharing);
		tpl_assign('object', $obj);
	}

	function do_share() {
		$share_data = array_var($_POST, 'share_data');
		if (is_array($share_data)) {
			$obj = get_object_by_manager_and_id(array_var($share_data, 'object_id'), array_var($share_data, 'object_manager'));

			$emails = array_var($_POST, 'emails');
			$companies = array_var($_POST, 'companiesId');

			if (!is_array($emails) || !count($emails)) {
				flash_error(lang('must specify recipients'));
				ajx_current("empty");
				return;
			}

			$people = array();
			foreach($emails as $k => $email) { // Retrieve users to notify
				$lt_pos = strpos_utf($email, '<');
				if ($lt_pos !== FALSE) { // only email address
					$email = substr_utf($email, $lt_pos + 1);
					$email = str_replace('>', '', $email);
				}
				if (trim($email) != '') {
					$user = Users::findOne(array('conditions' => "`email` = '" . $email . "'"));
					if (!($user instanceof User)) { // User not exists -> create one with minimum permissions
						try {
							DB::beginWork();
							$user = $this->createMinimumUser($email, $companies[$k]);
							DB::commit();
						} catch (Exception $e) {
							DB::rollback();
						}
					}
					if ($user instanceof User) {
						$people[] = $user;
						$canWrite = array_var($share_data, 'allow_edit');
	
						if ($canWrite && !$obj->canEdit($user) || !$obj->canView($user)) {
							$this->setObjUserPermission($user, $obj, $canWrite);
						}
						$this->saveSharedObject($obj, $user);
					}
				}
			}
			Notifier::shareObject($obj, $people);

			flash_success(lang("success sharing object"));
			ajx_current("back");
		}
	}

	function createMinimumUser($email, $compId) {
		$contact = Contacts::getByEmail($email);
		$posArr = strpos_utf($email, '@') === FALSE ? null : strpos($email, '@');
		$user_data = array(
			'username' => $email,
			'display_name' => $posArr != null ? substr_utf($email, 0, $posArr) : $email,
			'email' => $email,
			'contact_id' => isset($contact) ? $contact->getId() : null,
			'password_generator' => 'random',
			'timezone' => isset($contact) ? $contact->getTimezone() : 0,
			'create_contact' => !isset($contact),
			'company_id' => $compId,
			'send_email_notification' => true,
		); // array

		$user = null;
		$user = create_user($user_data, false, '');

		return $user;
	}

	function setObjUserPermission($user, $obj, $canWrite) {
		$obj_perm = ObjectUserPermissions::findOne(array('conditions' => "rel_object_id = ".$obj->getId()." AND rel_object_manager = '".$obj->getObjectManagerName()."' AND user_id = ".$user->getId()));
		if ($obj_perm) $obj_perm->setColumnValue('can_write', $canWrite);
		else {
			$obj_perm = new ObjectUserPermission();
			$obj_perm->setFromAttributes(array(
				'rel_object_id' => $obj->getId(),
				'rel_object_manager' => $obj->getObjectManagerName(),
				'user_id' => $user->getId(),
				'can_read' => 1,
				'can_write' => $canWrite
			));
		}
		try {
			DB::beginWork();
			$obj_perm->save();
			DB::commit();
		} catch (Exception $e) {
			DB::rollback();
			flash_error($e->getMessage());
			ajx_current("empty");
		}
	}

	function saveSharedObject($object, $user) {
		$ou = SharedObjects::findOne(array('conditions' => "object_id = ".$object->getId()." AND object_manager = '". $object->getObjectManagerName()."' AND user_id = ".$user->getId()));
		if (!$ou) {
			try {
				DB::beginWork();
				$ou = new SharedObject();
				$ou->setObjectId($object->getId());
				$ou->setObjectManager($object->getObjectManagerName());
				$ou->setUserId($user->getId());
				$ou->setCreatedOn(DateTimeValueLib::now());
				$ou->setCreatedById(logged_user()->getId());
				$ou->save();
				DB::commit();
			} catch (Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			}
		}
	}
	
	function get_co_types() {
		$object_type = array_var($_GET, 'object_type', '');
		if($object_type != ''){
			$types = ProjectCoTypes::findAll(array("conditions" => "`object_manager` = '".mysql_real_escape_string($object_type)."'"));
			$co_types = array();
			foreach($types as $type){
				$t = array();
				$t['id'] = $type->getId();
				$t['name'] = $type->getName();
				$co_types[] = $t;
			}
			ajx_current("empty");
			ajx_extra_data(array("co_types" => $co_types));
		}
	}
	
	function re_render_custom_properties() {
		$managerClass = array_var($_GET, 'manager');
		eval('$manager = ' . $managerClass . "::instance();");
		if (!$manager) {
			ajx_current("empty");
			return;
		}
		
		$object = $manager->findById(array_var($_GET, 'id'));
		if (!$object) {
			// if id == 0 object is new, then a dummy object is created to render the properties.
			$object = new ProjectMessage();
		}
		
		$html = render_object_custom_properties($object, $managerClass, array_var($_GET, 'req'), array_var($_GET, 'co_type'));
		
		$scripts = array(); $initag = "<script>"; $endtag = "</script>";
		$pos = strpos($html, $initag);
		while ($pos !== FALSE) {
			$end_pos = strpos($html, $endtag, $pos);
			if ($end_pos === FALSE) break;
			$ini = $pos + strlen($initag);
			$sc = substr($html, $ini, $end_pos - $ini);
			if (!str_starts_with(trim($sc), "og.addTableCustomPropertyRow")) {// do not add repeated functions
				$scripts[] = $sc;
			}
			$pos = strpos($html, $initag, $end_pos);
		}
		foreach ($scripts as $sc) {
			$html = str_replace("$initag$sc$endtag", "", $html);
		}

		ajx_current("empty");
		ajx_extra_data(array("html" => $html, 'scripts' => implode("", $scripts)));
	}
}
?>