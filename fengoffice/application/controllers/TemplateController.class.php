<?php

class TemplateController extends ApplicationController {
	
	var $dbDateFormat = "Y-m-d" ;

	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website');
	}

	function index() {
		if (!can_manage_templates(logged_user())) {
			flash_error(lang("no access permissions"));
			ajx_current("empty");
			return;
		}
		
		$templates=COTemplates::instance()->findAll();
		tpl_assign('templates', $templates);
	}

	function add() {
		if (!can_manage_templates(logged_user())) {
			flash_error(lang("no access permissions"));
			ajx_current("empty");
			return;
		}
		$template = new COTemplate();
		$template_data = array_var($_POST, 'template');
		if (!is_array($template_data)) {
			$template_data = array(
				'name' => '',
				'description' => ''
				);
		} else {
			//No le agrego miembros
// 			$member_ids = json_decode(array_var($_POST, 'members'));
// 			$context = active_context();
// 			$member_ids = array();
// 			foreach ($context as $selection) {
// 				if ($selection instanceof Member) $member_ids[] = $selection->getId();
// 			}
// 			if(count($selected_members)==0){
// 				$member_ids=$object->getMemberIds();
// 			}
// 			$controller->add_to_members($copy, $selected_members);
			$cotemplate = new COTemplate();
			$cotemplate->setFromAttributes($template_data);
			$object_ids = array();
			try {
				DB::beginWork();
				$cotemplate->save();
				$objects = array_var($_POST, 'objects');
				foreach ($objects as $objid) {
					$object = Objects::findObject($objid);
					$oid = $cotemplate->addObject($object);
					$object_ids[$objid] = $oid;
// 					COTemplates::validateObjectContext($object, $member_ids);
				}
				
				$objectPropertyValues = array_var($_POST, 'propValues');
				$propValueParams = array_var($_POST, 'propValueParam');
				$propValueOperation = array_var($_POST, 'propValueOperation');
				$propValueAmount = array_var($_POST, 'propValueAmount');
				$propValueUnit = array_var($_POST, 'propValueUnit');
				if (is_array($objectPropertyValues)) {
					foreach($objectPropertyValues as $objInfo => $propertyValues){
						foreach($propertyValues as $property => $value){
							

							
							$split = explode(":", $objInfo);
							$object_id = $split[1];
							$templateObjPropValue = new TemplateObjectProperty();
							$templateObjPropValue->setTemplateId($cotemplate->getId());
							$templateObjPropValue->setObjectId($object_ids[$objInfo]);
							//$templateObjPropValue->setObjectManager($split[0]);
							$templateObjPropValue->setProperty($property);
							$propValue = '';
							if(isset($propValueParams[$objInfo][$property])){
								$param = $propValueParams[$objInfo][$property];
								$operation = $propValueOperation[$objInfo][$property];
								$amount = $propValueAmount[$objInfo][$property];
								$unit = $propValueUnit[$objInfo][$property];
								$propValue = '{'.$param.'}'.$operation.$amount.$unit;
							}else{
								if(is_array($value)){
									$propValue = $value[0];
								}else{
									$propValue = $value;
								}
							}
							$templateObjPropValue->setValue($propValue);
							$templateObjPropValue->save();
						}
					}
				}
				$parameters = array_var($_POST, 'parameters');
				if (is_array($parameters)) {
					foreach($parameters as $parameter){
						$newTemplateParameter = new TemplateParameter();
						$newTemplateParameter->setTemplateId($cotemplate->getId());
						$newTemplateParameter->setName($parameter['name']);
						$newTemplateParameter->setType($parameter['type']);
						$newTemplateParameter->save();
					}
				}
								
// 				$object_controller = new ObjectController();
// 				$object_controller->add_to_members($cotemplate, $member_ids);
				
				evt_add('reload tab panel', 'tasks-panel');
				
				DB::commit();
				ApplicationLogs::createLog($cotemplate, ApplicationLogs::ACTION_ADD);
				flash_success(lang("success add template"));
				if (array_var($_POST, "add_to")) {
					ajx_current("start");
				} else {
					ajx_current("back");
				}
			} catch (Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			}
		}
		$objects = array();
		if (array_var($_GET, 'id')) {
			/*	TODO: Feng 2 
		  	$object = Objects::findObject(array_var($_GET, 'id'));
			if ($object instanceof ProjectDataObject) {
				$objects[] = $object;
				tpl_assign('add_to', true);
			}
			*/
		}
		tpl_assign('objects', $objects);
		tpl_assign('cotemplate', $template);
		tpl_assign('template_data', $template_data);
	}

	function edit() {
		if (!can_manage_templates(logged_user())) {
			flash_error(lang("no access permissions"));
			ajx_current("empty");
			return;
		}
		$this->setTemplate('add');

		$cotemplate = COTemplates::findById(get_id());
		if(!($cotemplate instanceof COTemplate)) {
			flash_error(lang('template dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$cotemplate->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$template_data = array_var($_POST, 'template');
		$object_properties = array();
		if(!is_array($template_data)) {
			$template_data = array(
				'name' => $cotemplate->getObjectName(),
				'description' => $cotemplate->getDescription(),
			); // array
			foreach($cotemplate->getObjects() as $obj){
				$object_properties[$obj->getObjectId()] = TemplateObjectProperties::getPropertiesByTemplateObject(get_id(), $obj->getObjectId());
			}
		} else {
			$cotemplate->setFromAttributes($template_data);
			try {
				$member_ids = json_decode(array_var($_POST, 'members'));
				DB::beginWork();
				$cotemplate->save();
				$cotemplate->removeObjects();
				$objects = array_var($_POST, 'objects');
				foreach ($objects as $objid) {
					
					$object = Objects::findObject($objid);
					COTemplates::validateObjectContext($object, $member_ids);
					$oid = $cotemplate->addObject($object);
					$object_ids[$objid] = $oid;
				}

				TemplateObjectProperties::deletePropertiesByTemplate(get_id());
				$objectPropertyValues = array_var($_POST, 'propValues');
				$propValueParams = array_var($_POST, 'propValueParam');
				$propValueOperation = array_var($_POST, 'propValueOperation');
				$propValueAmount = array_var($_POST, 'propValueAmount');
				$propValueUnit = array_var($_POST, 'propValueUnit');
				if (is_array($objectPropertyValues)) {
					foreach($objectPropertyValues as $objInfo => $propertyValues){
						foreach($propertyValues as $property => $value){


										
							$split = explode(":", $objInfo);
							$object_id = $split[1];
							$templateObjPropValue = new TemplateObjectProperty();
							$templateObjPropValue->setTemplateId($cotemplate->getId());
							$templateObjPropValue->setObjectId($object_ids[$objInfo]);
							//$templateObjPropValue->setObjectManager($split[0]);
							$templateObjPropValue->setProperty($property);
							$propValue = '';
							if(isset($propValueParams[$objInfo][$property])){
								$param = $propValueParams[$objInfo][$property];
								$operation = $propValueOperation[$objInfo][$property];
								$amount = $propValueAmount[$objInfo][$property];
								$unit = $propValueUnit[$objInfo][$property];
								$propValue = '{'.$param.'}'.$operation.$amount.$unit;
							}else{
								if(is_array($value)){
									$propValue = $value[0];
								}else{
									$propValue = $value;
								}
							}
							$templateObjPropValue->setValue($propValue);
							$templateObjPropValue->save();
						}
					}
				}
				TemplateParameters::deleteParametersByTemplate(get_id());
				$parameters = array_var($_POST, 'parameters');
				if (is_array($parameters)) {
					foreach($parameters as $parameter){
						$newTemplateParameter = new TemplateParameter();
						$newTemplateParameter->setTemplateId($cotemplate->getId());
						$newTemplateParameter->setName($parameter['name']);
						$newTemplateParameter->setType($parameter['type']);
						$newTemplateParameter->save();
					}
				}
				
				$object_controller = new ObjectController();
				$object_controller->add_to_members($cotemplate, $member_ids);
				
				DB::commit();
				ApplicationLogs::createLog($cotemplate, ApplicationLogs::ACTION_EDIT);
				flash_success(lang("success edit template"));
				ajx_current("back");
			} catch (Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			}
		}
		tpl_assign('object_properties', $object_properties);
		tpl_assign('parameters', TemplateParameters::getParametersByTemplate(get_id()));
		tpl_assign('objects', $cotemplate->getObjects());
		tpl_assign('cotemplate', $cotemplate);
		tpl_assign('template_data', $template_data);
	}

	function view() {
		if (!can_manage_templates(logged_user())) {
			flash_error(lang("no access permissions"));
			ajx_current("empty");
			return;
		}
		$cotemplate = COTemplates::findById(get_id());
		if(!($cotemplate instanceof COTemplate)) {
			flash_error(lang('template dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$cotemplate->canView(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		tpl_assign('cotemplate', $cotemplate);
		ajx_set_no_toolbar(true);
		ApplicationReadLogs::createLog($cotemplate, ApplicationReadLogs::ACTION_READ);
		
	}

	function delete() {
		if (!can_manage_templates(logged_user())) {
			flash_error(lang("no access permissions"));
			ajx_current("empty");
			return;
		}
		ajx_current("empty");
		$cotemplate = COTemplates::findById(get_id());
		if(!($cotemplate instanceof COTemplate)) {
			flash_error(lang('template dnx'));
			return;
		} // if

		if(!$cotemplate->canDelete(logged_user())) {
			flash_error(lang('no access permissions'));
			return;
		} // if

		try {
			DB::beginWork();
			$cotemplate->delete();
			ApplicationLogs::createLog($cotemplate, ApplicationLogs::ACTION_DELETE);
			DB::commit();
			flash_success(lang('success delete template', $cotemplate->getObjectName()));
			if (array_var($_GET, 'popup', false)) {
				ajx_current("reload");
			} else {
				ajx_current("back");
			}
		} catch(Exception $e) {
			DB::rollback();
			flash_error($e->getMessage());
		} // try
	}

	function add_to() {
		if (!can_manage_templates(logged_user())) {
			flash_error(lang("no access permissions"));
			ajx_current("empty");
			return;
		}
		$manager = array_var($_GET, 'manager');
		$id = get_id();
		
		$object = Objects::findObject($id);
		$template_id = array_var($_GET, 'template');
		if ($template_id) {
			$template = COTemplates::findById($template_id);
			if ($template instanceof COTemplate) {
				try {
					DB::beginWork();
					$template->addObject($object);
					DB::commit();
					flash_success(lang('success add object to template'));
					ajx_current("start");
				} catch(Exception $e) {
					DB::rollback();
					flash_error($e->getMessage());
				}
			}
		}
		tpl_assign('templates', COTemplates::findAll());
		tpl_assign("object", $object);
	}

	function template_parameters(){
		$id = get_id();
		$parameters = TemplateParameters::getParametersByTemplate($id);
		ajx_current("empty");
		ajx_extra_data(array('parameters' => $parameters));
	}
	
	
	function get_context(){
		$id = get_id();
		$template = COTemplates::findById($id);
		$this->setTemplate('get_context');
		if(array_var($_POST, 'members')){
			$this->instantiate();
		}
		tpl_assign('cotemplate',$template);
		tpl_assign('id',$id);
	}
	
	
	function instantiate() {
		$selected_members = array();
		$id = get_id();
	
		
		$template = COTemplates::findById($id);
		if (!$template instanceof COTemplate) {
			flash_error(lang("template dnx"));
			ajx_current("empty");
			return;
		}
		$parameters = TemplateParameters::getParametersByTemplate($id);
		$parameterValues = array_var($_POST, 'parameterValues');
		if(count($parameters) > 0 && !isset($parameterValues)) {
			ajx_current("back");
			return;
		}
		if(array_var($_POST, 'members')){
			$selected_members = json_decode(array_var($_POST, 'members'));
		}else{
			$context = active_context();
			
			foreach ($context as $selection) {
				if ($selection instanceof Member) $selected_members[] = $selection->getId();
			}
		}
		
		
		$objects = $template->getObjects() ;
		$controller  = new ObjectController();
		if (count($selected_members > 0)) {
			$selected_members_instances = Members::findAll(array('conditions' => 'id IN ('.implode($selected_members).')'));
		} else {
			$selected_members_instances = array();
		}
		
		DB::beginWork();
		
		$active_context = active_context();
		
		foreach ($objects as $object) {
			if (!$object instanceof ContentDataObject) continue;
			// copy object
			$copy = $object->copy();
			if ($copy->columnExists('is_template')) {
				$copy->setColumnValue('is_template', false);
			}
			if ($copy instanceof ProjectTask) {
				// don't copy parent task and milestone
				$copy->setMilestoneId(0);
				$copy->setParentId(0);
			}
			$copy->save();
	/*		if (!can_write(logged_user(), $selected_members_instances, $copy->getObjectTypeId()) ) {
				flash_error(lang('no context permissions to add', $copy instanceof ProjectTask ? lang("tasks") : ($copy instanceof ProjectMilestone ? lang('milestones') : '')));
				DB::rollback();
				ajx_current("empty");
				return;
			}*/
			
			// Copy members from origial object, if it doesn't have then use active context members
		/*	$template_object_members = $object->getMemberIds();
			if (count($template_object_members) == 0) {
				$object_member_ids = active_context_members(false);
				if (count($object_member_ids) > 0) {
					$template_object_members = Members::findAll(array("id" => true, "conditions" => "id IN (".implode(",", $object_member_ids).")"));
				}
			}*/
			
			/* Set instantiated object members:
			 * foreach dimension:
			 * 		if no member is active then the instantiated object is put in the same members as the original for current dimension
			 * 		if a member is selected in current dimension then the instantiated object will be put in that member  
			 */
			$template_object_members = $object->getMembers();
			$object_members = array();
			foreach( $active_context as $selection ) {
				if ($selection instanceof Member) { // member selected
					$object_members[] = $selection->getId();
				} else if ($selection instanceof Dimension) { // no member selected
					foreach ($template_object_members as $tom) {
						if ($tom->getDimensionId() == $selection->getId()) {
							$object_members[] = $tom->getId();
						}
					}
				}
			}			
			
			
			$controller->add_to_members($copy, $object_members);
			// copy linked objects
			$copy->copyLinkedObjectsFrom($object);
			// copy subtasks if applicable
			if ($copy instanceof ProjectTask) {
				ProjectTasks::copySubTasks($object, $copy, false);
				foreach($copy->getOpenSubTasks(false) as $m_task){
					$controller->add_to_members($m_task, $object_members);
				}
				$manager = $copy->manager();
			} else if ($copy instanceof ProjectMilestone) {
				ProjectMilestones::copyTasks($object, $copy, false);
				foreach($copy->getTasks(false) as $m_task){
					$controller->add_to_members($m_task, $object_members);
				}
				$manager = $copy->manager();
			}
			// copy custom properties
			$copy->copyCustomPropertiesFrom($object);
			// set property values as defined in template
			$objProp = TemplateObjectProperties::getPropertiesByTemplateObject($id, $object->getId());
			foreach($objProp as $property) {
				
				$propName = $property->getProperty();
				$value = $property->getValue();
				
				if ($manager->getColumnType($propName) == DATA_TYPE_STRING || $manager->getColumnType($propName) == DATA_TYPE_INTEGER) {
					if (is_array($parameterValues)){
						foreach($parameterValues as $param => $val){
							if (strpos($value, '{'.$param.'}') !== FALSE) {
								$value = str_replace('{'.$param.'}', $val, $value);
							}
						}
					}
				} else if($manager->getColumnType($propName) == DATA_TYPE_DATE || $manager->getColumnType($propName) == DATA_TYPE_DATETIME) {
					$operator = '+';
					if (strpos($value, '+') === false) {
						$operator = '-';
					}
					$opPos = strpos($value, $operator);
					if ($opPos !== false) {
						// Is parametric
						$dateParam = substr($value, 1, strpos($value, '}') - 1);
						$date = $parameterValues[$dateParam];
						
						$dateUnit = substr($value, strlen($value) - 1); // d, w or m (for days, weeks or months)
						if($dateUnit == 'm') {
							$dateUnit = 'M'; // make month unit uppercase to call DateTimeValue::add with correct parameter
						}
						$dateNum = (int) substr($value, strpos($value,$operator), strlen($value) - 2);
						
						
						$date = DateTimeValueLib::dateFromFormatAndString(user_config_option('date_format'), $date);
						$date = new DateTimeValue($date->getTimestamp() - logged_user()->getTimezone()*3600);// set date to GMT 0
						$value = $date->add($dateUnit, $dateNum);
					}else{
						$value = DateTimeValueLib::dateFromFormatAndString(user_config_option('date_format'), $value);
					}
				}
				if($value != '') {
					if (!$copy->setColumnValue($propName, $value)){
						$copy->object->setColumnValue($propName, $value);
					}
					$copy->save();
				}
			}
			
			// subscribe assigned to
			if ($copy instanceof ProjectTask) {
				foreach($copy->getOpenSubTasks(false) as $m_task){
					if ($m_task->getAssignedTo() instanceof Contact) {
						$m_task->subscribeUser($copy->getAssignedTo());
					}
				}
				if ($copy->getAssignedTo() instanceof Contact) {
					$copy->subscribeUser($copy->getAssignedTo());
				}
			} else if ($copy instanceof ProjectMilestone) {
				foreach($copy->getTasks(false) as $m_task){
					if ($m_task->getAssignedTo() instanceof Contact) {
						$m_task->subscribeUser($copy->getAssignedTo());
					}
				}
			}
			
			// copy reminders
			$reminders = ObjectReminders::getByObject($object);
			foreach ($reminders as $reminder) {
				$copy_reminder = new ObjectReminder();
				$copy_reminder->setContext($reminder->getContext());
				$reminder_date = $copy->getColumnValue($reminder->getContext());
				if ($reminder_date instanceof DateTimeValue) {
					$reminder_date = new DateTimeValue($reminder_date->getTimestamp());
					$reminder_date->add('m', -$reminder->getMinutesBefore());
				}
				$copy_reminder->setDate($reminder_date);
				$copy_reminder->setMinutesBefore($reminder->getMinutesBefore());
				$copy_reminder->setObject($copy);
				$copy_reminder->setType($reminder->getType());
				$copy_reminder->setUserId($reminder->getUserId());
				$copy_reminder->save();
			}
		}
		
		DB::commit();
		if (is_array($parameters) && count($parameters) > 0){
			ajx_current("back");
		}else{
			if(!$choose_ctx){
				ajx_current("back");
			}else{
				ajx_current("reload");
			}
		}
	}
	
	

	function instantiate_parameters(){
		if(is_array(array_var($_POST, 'parameterValues'))){
			ajx_current("back");
			$this->instantiate();
		}else{
			$id = get_id();
			$parameters = TemplateParameters::getParametersByTemplate($id);
			$params = array();
			foreach($parameters as $parameter){
				$params[] = array('name' => $parameter->getName(), 'type' => $parameter->getType());
			}
			tpl_assign('id', $id);
			tpl_assign('parameters', $params);
		}
	}

	function assign_to_ws() {
		if (!can_manage_templates(logged_user())) {
			flash_error(lang("no access permissions"));
			ajx_current("empty");
			return;
		}
		$template_id = get_id();
		$cotemplate = COTemplates::findById($template_id);
		if (!$cotemplate instanceof COTemplate) {
			flash_error(lang("template dnx"));
			ajx_current("empty");
			return;
		}
		$selected = WorkspaceTemplates::getWorkspacesByTemplate($template_id);
		tpl_assign('workspaces', logged_user()->getWorkspaces());
		tpl_assign('selected', $selected);
		tpl_assign('cotemplate', $cotemplate);
		$checked = array_var($_POST, 'ws_ids');
		if ($checked != null) {
			try {
				DB::beginWork();
				WorkspaceTemplates::deleteByTemplate($template_id);
				$wss = Projects::findByCSVIds($checked);
				foreach ($wss as $ws){
					$obj = new WorkspaceTemplate();
					$obj->setWorkspaceId($ws->getId());
					$obj->setTemplateId($template_id);
					$obj->setInludeSubWs(false);
					$obj->save();
				}
				DB::commit();
				flash_success(lang('success assign workspaces'));
				ajx_current("back");
			} catch (Exception $exc){
				DB::rollback();
				flash_error(lang('error assign workspace') . $exc->getMessage());
				ajx_current("empty");
			}
		}
	}

	function get_object_properties(){
		$props = array();
		$type = "ProjectTasks";
		eval('$objectProperties = '.$type.'::getTemplateObjectProperties();');
		foreach($objectProperties as $property){
			$props[] = array('id' => $property['id'], 'name' => lang('field '.$type.' '.$property['id']), 'type' => $property['type']);
		}
		ajx_current("empty");
		ajx_extra_data(array('properties' => $props));
	}
}

?>