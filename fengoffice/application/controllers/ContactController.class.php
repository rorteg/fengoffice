<?php

/**
 * Contact controller
 *
 * @version 1.0
 * @author Marcos Saiz <marcos.saiz@fengoffice.com>
 */
class ContactController extends ApplicationController {

	
	
	/**
	 * Construct the ContactController
	 *
	 * @access public
	 * @param void
	 * @return ContactController
	 */
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website');
	} // __construct

	
	function init() {
		require_javascript("og/ContactManager.js");
		ajx_current("panel", "contacts", null, null, true);
		ajx_replace(true);
	}
	
	/**
	 * 
	 * @author Ignacio Vazquez - elpepe.uy@gmail.com
	 */
	function list_companies() {
		ajx_current("empty");
		$context = active_context();
		
		//$contacts = Contacts::instance()->getContentObjects($context, ObjectTypes::instance()->findById(Contacts::instance()->getObjectTypeId()))->objects;
		$contacts = Contacts::instance()->listing();
		$defaultCompany = Contacts::instance()->findById(1);
		if ($defaultCompany instanceof Contact)  $contacts[] = $defaultCompany ;
		$companies = array();
		foreach ($contacts as $contactObj ) {
			if ($contactObj instanceof Contact ) { 
				if ($contactObj->isCompany()) {
					$companies[]  = array (
						"name"  => $contactObj->getObjectName(),
						"value" => $contactObj->getId() 
					);
				}
			}
		}
		ajx_extra_data(array("companies"=>$companies));
	}
	
	// ---------------------------------------------------
	//  USERS
	// ---------------------------------------------------
	
	
	/**
	 * Creates a system user, receiving a Contact id
	 * @deprecated by this->add_user
	 */
	function create_user(){
		
		$contact = Contacts::findById(get_id());
		if(!($contact instanceof Contact)) {
			flash_error(lang('contact dnx'));
			ajx_current("empty");
			return;
		} // if
		
		if(!can_manage_security(logged_user())){
			flash_error(lang('no permissions'));
			ajx_current("empty");
			return;
		} // if
		
		$this->redirectTo('contact','add',array('company_id' => $contact->getCompanyId(), 'contact_id' => $contact->getId()));
		
	}
	
	
	/**
	 * Show user card
	 *
	 * @access public
	 * @param void
	 * @return null
	 * @deprecated
	 */
	function card_user() {
		$this->redirectTo('contact','card');
	} 
	
	
    /**
	 * Add user
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function add_user() {
		$max_users = config_option('max_users');
		if ($max_users && (Contacts::count() >= $max_users)) {
			flash_error(lang('maximum number of users reached error'));
			ajx_current("empty");
			return;
		}
		$company = Contacts::findById(get_id('company_id'));
		if (!($company instanceof Contact)) {
			$company = owner_company();
		}

		if (!can_manage_security(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
		
		$user = new Contact();
		
		$user_data = array_var($_POST, 'user');
		// Populate form fields
		if (!is_array($user_data)) {
			//if it is a new user
			$contact_id = get_id('contact_id');
			$contact = Contacts::findById($contact_id);
			
			if ($contact instanceof Contact) {
				
				if (!is_valid_email($contact->getEmailAddress())){
					ajx_current("empty");
					flash_error(lang("contact email is required to create user"));
					return false;
				}	
			
				//if it will be created from a contact
				$user_data = array(
					'username' => $this->generateUserNameFromContact($contact),
					'display_name' => $contact->getFirstname() . $contact->getSurname(),
					'email' => $contact->getEmailAddress('personal'),
					'contact_id' => $contact->getId(),
					'password_generator' => 'random',
					'type' => 'Executive',
					'can_manage_time' => true,
				); // array
				tpl_assign('ask_email', false);
			} else {
				// if it is new, and created from admin interface
				$user_data = array(
                                  'password_generator' => 'random',
                                  'company_id' => $company->getId(),
                                  'timezone' => $company->getTimezone(),
				  'create_contact' => true ,
				  'send_email_notification' => true ,
				  'type' => 'Executive',
				  'can_manage_time' => true,
				); // array
				tpl_assign('ask_email', true);
			}
			
			// System permissions
			tpl_assign('system_permissions', new SystemPermission());
			
			// Module permissions
			$module_permissions_info = array();
			$all_modules = TabPanels::findAll(array("conditions" => "`enabled` = 1", "order" => "ordering"));
			$all_modules_info = array();
			foreach ($all_modules as $module) {
				$all_modules_info[] = array('id' => $module->getId(), 'name' => lang($module->getTitle()), 'ot' => $module->getObjectTypeId());
			}
			tpl_assign('module_permissions_info', $module_permissions_info);
			tpl_assign('all_modules_info', $all_modules_info);
			
			// Member permissions
			$parameters = permission_form_parameters(0);
			tpl_assign('permission_parameters', $parameters);
			
			// Permission Groups
			$groups = PermissionGroups::getNonPersonalSameLevelPermissionsGroups('`parent_id`,`id` ASC');
			tpl_assign('groups', $groups);
			$roles= SystemPermissions::getAllRolesPermissions();
			tpl_assign('roles', $roles);
			$tabs= TabPanelPermissions::getAllRolesModules();
			tpl_assign('tabs_allowed', $tabs);
			
			
		} // if

		
		tpl_assign('user', $user);
		tpl_assign('company', $company);
		tpl_assign('user_data', $user_data);
		
		//Submit User
		if (is_array(array_var($_POST, 'user'))) {
			if (!array_var($user_data, 'createPersonalProject')) {
				$user_data['personal_project'] = 0;
			}
			try {
				Contacts::validateUser($user_data);
				
				DB::beginWork();
				$user = $this->createUser($user_data, array_var($_POST,'permissions'));
				
				DB::commit();	
				flash_success(lang('success add user', $user->getObjectName()));
				ajx_current("back");
			} catch(Exception $e) {
				DB::rollback();
				ajx_current("empty");
				flash_error($e->getMessage());
			} // try

		} // if

	} // add_user
	
	
	private function generateUserNameFromContact($contact) {
		$uname = "";
		if ($contact->getSurname() == "") {
			$uname = $contact->getFirstName();
		} else if ($contact->getFirstname() == "") {
			$uname = $contact->getSurname();
		} else {
			$uname = substr_utf($contact->getFirstname(), 0, 1) . $contact->getSurname();
		}
		$uname = strtolower(trim(str_replace(" ", "", $uname)));
		if ($uname == "") {
			$uname = strtolower(str_replace(" ", "_", lang("new user")));
		}
		$base = $uname;
		for ($i=2; Contacts::getByUsername($uname) instanceof Contact; $i++) {
			$uname = $base . $i;
		}
		return $uname;
	}
	
	
	/**
	 * List user preferences
	 *
	 */
	function list_user_categories(){	
		tpl_assign('config_categories', ContactConfigCategories::getAll());	
	} //list_preferences

	
	/**
	 * List user preferences
	 *
	 */
	function update_user_preferences(){
		$category = ContactConfigCategories::findById(get_id());
		if(!($category instanceof ContactConfigCategory)) {
			flash_error(lang('config category dnx'));
			$this->redirectToReferer(get_url('contact','card'));
		} // if

		if($category->isEmpty()) {
			flash_error(lang('config category is empty'));
			$this->redirectToReferer(get_url('contact','card'));
		} // if

		$options = $category->getContactOptions(false);
		$categories = ContactConfigCategories::getAll(false);

		tpl_assign('category', $category);
		tpl_assign('options', $options);
		tpl_assign('config_categories', $categories);

		$submited_values = array_var($_POST, 'options');                
		if(is_array($submited_values)) {
			try{                            
				DB::beginWork();
				foreach($options as $option) {
					// update cache if available
					if (GlobalCache::isAvailable()) {
						GlobalCache::delete('user_config_option_'.logged_user()->getId().'_'.$option->getName());
					}
                                        if($option->getName() == "reminders_events"){
                                            $array_value = array_var($submited_values, $option->getName());
                                            $new_value = array_var($array_value,"reminder_type") . "," . array_var($array_value,"reminder_duration") . "," . array_var($array_value,"reminder_duration_type");
                                        }else{
                                            $new_value = array_var($submited_values, $option->getName());
                                        }
					
					if(is_null($new_value) || ($new_value == $option->getContactValue(logged_user()->getId()))) continue;
	
					$option->setContactValue($new_value, logged_user()->getId());
					$option->save();
					evt_add("user preference changed", array('name' => $option->getName(), 'value' => $new_value));
				} // foreach
				DB::commit();
				flash_success(lang('success update config value', $category->getDisplayName()));
				ajx_current("back");
			}
			catch (Exception $ex){
				DB::rollback();
				flash_success(lang('error update config value', $category->getDisplayName()));
			}
		} // if
	} //list_preferences
	
	
	/**
	 * Creates an user (called from add_user). Does no transaction and throws Exceptions
	 * so should be called inside a transaction and inside a try-catch 
	 *
	 * @param User $user
	 * @param array $user_data
	 * @param boolean $is_admin
	 * @param string $permissionsString
	 * @param string $personalProjectName
	 * @return User $user
	 */
	function createUser($user_data, $permissionsString) {
		return create_user($user_data, $permissionsString);
	}
	
	
	
	// ---------------------------------------------------
	//  CONTACTS
	// ---------------------------------------------------
	
	
	function list_users() {
		$this->setTemplate(get_template_path("json"));
		ajx_current("empty");
		$usr_data = array();
		$users = Contacts::findAll(array("conditions"=>"is_company = 0"));
		if ($users) {
			foreach ($users as $usr) {
				$usr_data[] = array(
					"id" => $usr->getId(),
					"name" => $usr->getObjectName()
				);
			}
		}
		$extra = array();
		$extra['users'] = $usr_data;
		ajx_extra_data($extra);
	}
	
	
	/**
	 * Lists all contacts and clients
	 *
	 */
	function list_all() {
		ajx_current("empty");
		
		// Get all variables from request
		$start = array_var($_GET,'start', 0);
		$limit = array_var($_GET,'limit', config_option('files_per_page'));
		$page = 1;
		if ($start > 0){
			$page = ($start / $limit) + 1;
		}
		$order = array_var($_GET,'sort');
		$order_dir = array_var($_GET,'dir');
		$action = array_var($_GET,'action');
		$attributes = array(
			"ids" => explode(',', array_var($_GET, 'ids')),
			"types" => explode(',', array_var($_GET, 'types')),
			"accountId" => array_var($_GET, 'account_id'),
			"viewType" => array_var($_GET, 'view_type'),
		);
		
		//Resolve actions to perform
		$actionMessage = array();
		if (isset($action)) {
			$actionMessage = $this->resolveAction($action, $attributes);
			if ($actionMessage["errorCode"] == 0) {
				flash_success($actionMessage["errorMessage"]);
			} else {
				flash_error($actionMessage["errorMessage"]);
			}
		} 
		
				
		$extra_conditions = "";
		if ($attributes['viewType'] == 'contacts') {
			$extra_conditions = 'AND `is_company` = 0';
		} else if ($attributes['viewType'] == 'companies') {
			$extra_conditions = 'AND `is_company` = 1';
		}
		$extra_conditions.= " AND disabled = 0 " ;
		
		switch ($order){
			case 'updatedOn':
				$order = '`updated_on`';
				break;
			case 'createdOn':
				$order = '`created_on`';
				break;
			case 'name':
				$order = ' concat(surname, first_name) ';
				break;
			default:
				$order = '`name`';
				break;
		}
		if (!$order_dir){
			switch ($order){
				case 'name': $order_dir = 'ASC'; break;
				default: $order_dir = 'DESC';
			}
		}

		$context = active_context();
		if (context_type() == 'mixed') {
			// There are members selected
			//$content_objects = Contacts::getContentObjects($context, ObjectTypes::findById(Contacts::instance()->getObjectTypeId()), $order, $order_dir, $extra_conditions, null, false,false, $start, $limit);
			$content_objects = Contacts::instance()->listing(array(
				"order" => $order,
				"order_dir" => $order_dir,
				"extra_conditions" => $extra_conditions,
				"start" =>$start,
				"limit" => $limit
			));
				
			
		}else{
			// Estoy parado en 'All'. Filtro solo por permisos TODO: Fix this !
			$conditions = "archived_on = '0000-00-00 00:00:00' AND trashed_on = '0000-00-00 00:00:00' $extra_conditions";
			$content_objects = new stdClass();
			$content_objects->objects = Contacts::instance()->findAll(array(
				"conditions" => $conditions,
				"order"=> "$order $order_dir",
				"offset" => $start,
				"limit" => $limit			
			));
			$content_objects->total =  Contacts::instance()->count(array("conditions" => $conditions));
			
			foreach ( $content_objects->objects as $k => $contact ) { /* @var $contact Contact */
				if (Plugins::instance()->isActivePlugin("core_dimensions")) {
					$m = array_var(Members::instance()->findByObjectId($contact->getId(), Dimensions::findByCode("feng_persons")->getId()),0);
					if ($m instanceof Member) {
						$mid = $m->getId();
						if (! ContactMemberPermissions::instance()->contactCanReadMember(logged_user()->getPermissionGroupId(),$mid, logged_user())) {
							unset($content_objects->objects[$k]);
							$content_objects->total--;
						}
					}
				}
			}
			$content_objects->objects = array_values($content_objects->objects) ;
			
		}
		// Prepare response object
		$object = $this->newPrepareObject($content_objects->objects, $content_objects->total, $start, $attributes);
		ajx_extra_data($object);
    	tpl_assign("listing", $object);

	}
	
	
	/**
	 * Resolve action to perform
	 *
	 * @param string $action
	 * @param array $attributes
	 * @return string $message
	 */
	private function resolveAction($action, $attributes){
		
		$resultMessage = "";
		$resultCode = 0;
		switch ($action){
			case "delete":
				$succ = 0; $err = 0;
				for($i = 0; $i < count($attributes["ids"]); $i++){
					$id = $attributes["ids"][$i];
					$type = $attributes["types"][$i];
					
					$contact = Contacts::findById($id);
					if (isset($contact) && $contact->canDelete(logged_user())){
						try{
							DB::beginWork();
							$contact->trash();
							DB::commit();
							ApplicationLogs::createLog($contact,ApplicationLogs::ACTION_TRASH);
							$succ++;
						} catch(Exception $e){
							DB::rollback();
							$err++;
						}
					} else {
						$err++;
					}
				}; // for
				if ($err > 0) {
					$resultCode = 2;
					$resultMessage = lang("error delete objects", $err) . ($succ > 0 ? lang("success delete objects", $succ) : "");
				} else {
					$resultMessage = lang("success delete objects", $succ);
					if ($succ > 0) ObjectController::reloadPersonsDimension();
				}
				break;
			case "archive":
				$succ = 0; $err = 0;
				for($i = 0; $i < count($attributes["ids"]); $i++){
					$id = $attributes["ids"][$i];
					$type = $attributes["types"][$i];
					$contact = Contacts::findById($id);
					if (isset($contact) && $contact->canEdit(logged_user())){
						try{
							DB::beginWork();
							$contact->archive();
							DB::commit();
							ApplicationLogs::createLog($contact, ApplicationLogs::ACTION_ARCHIVE);
							$succ++;
						} catch(Exception $e){
							DB::rollback();
							$err++;
						}
					} else {
						$err++;
					}
				}; // for
				if ($err > 0) {
					$resultCode = 2;
					$resultMessage = lang("error archive objects", $err) . ($succ > 0 ? lang("success archive objects", $succ) : "");
				} else {
					$resultMessage = lang("success archive objects", $succ);
					if ($succ > 0) ObjectController::reloadPersonsDimension();
				}
				break;
			default:
				$resultMessage = lang("unimplemented action" . ": '" . $action . "'");// if 
				$resultCode = 2;	
				break;		
		} // switch
		return array("errorMessage" => $resultMessage, "errorCode" => $resultCode);
	}
	
	
	
	
	
	/**
	 * Prepares return object for a list of emails and messages
	 *
	 * @param array $totMsg
	 * @param integer $start
	 * @param integer $limit
	 * @return array
	 */
	private function newPrepareObject($objects, $count, $start = 0, $attributes = null)
	{
		$object = array(
			"totalCount" => $count,
			"start" => $start,
			"contacts" => array()
		);
		for ($i = 0; $i < count($objects); $i++){
			if (isset($objects[$i])){
				$c= $objects[$i];
					
				if ($c instanceof Contact && !$c->isCompany()){						
					$company = $c->getCompany();
					$companyName = '';
					if (!is_null($company))
						$companyName= $company->getObjectName();
					
					$personal_emails = $c->getContactEmails('personal');	
					$w_address = $c->getAddress('work');
					$h_address = $c->getAddress('home');
									
					$object["contacts"][] = array(
						"id" => $i,
						"ix" => $i,
						"object_id" => $c->getId(),
						"ot_id" => $c->getObjectTypeId(),
						"type" => 'contact',
						"name" => $c->getReverseDisplayName(),
						"email" => $c->getEmailAddress('personal',true),
						"companyId" => $c->getCompanyId(),
						"companyName" => $companyName,
						"website" => $c->getWebpage('personal') ? cleanUrl($c->getWebpageUrl('personal'), false) : '',
						"jobTitle" => $c->getJobTitle(),
                        "department" => $c->getDepartment(),
						"email2" => !is_null($personal_emails) && isset($personal_emails[0]) ? $personal_emails[0]->getEmailAddress() : '',
						"email3" => !is_null($personal_emails) && isset($personal_emails[1]) ? $personal_emails[1]->getEmailAddress() : '',
						"workWebsite" => $c->getWebpage('work') ? cleanUrl($c->getWebpageUrl('work'), false) : '',
						"workAddress" => $w_address ? $c->getFullAddress($w_address) : '',
						"workPhone1" => $c->getPhone('work',true) ? cleanUrl($c->getPhoneNumber('work',true), false) : '',
						"workPhone2" => $c->getPhone('work') ? cleanUrl($c->getPhoneNumber('work'), false) : '',
						"homeWebsite" => $c->getWebpage('personal') ? cleanUrl($c->getWebpageUrl('personal'), false) : '',
						"homeAddress" => $h_address ? $c->getFullAddress($h_address) : '',
						"homePhone1" => $c->getPhone('home',true) ? cleanUrl($c->getPhoneNumber('home',true), false) : '',
						"homePhone2" => $c->getPhone('home') ? cleanUrl($c->getPhoneNumber('home'), false) : '',
						"mobilePhone" =>$c->getPhone('mobile') ? cleanUrl($c->getPhoneNumber('mobile'), false) : '',
						"createdOn" => $c->getCreatedOn() instanceof DateTimeValue ? ($c->getCreatedOn()->isToday() ? format_time($c->getCreatedOn()) : format_datetime($c->getCreatedOn())) : '',
						"createdOn_today" => $c->getCreatedOn() instanceof DateTimeValue ? $c->getCreatedOn()->isToday() : 0,
						"createdBy" => $c->getCreatedByDisplayName(),
						"createdById" => $c->getCreatedById(),
						"updatedOn" => $c->getUpdatedOn() instanceof DateTimeValue ? ($c->getUpdatedOn()->isToday() ? format_time($c->getUpdatedOn()) : format_datetime($c->getUpdatedOn())) : '',
						"updatedOn_today" => $c->getUpdatedOn() instanceof DateTimeValue ? $c->getUpdatedOn()->isToday() : 0,
						"updatedBy" => $c->getUpdatedByDisplayName(),
						"updatedById" => $c->getUpdatedById(),
						"memPath" => json_encode($c->getMembersToDisplayPath()),
					);
				} else if ($c instanceof Contact){					
					
					$w_address = $c->getAddress('work');
					$object["contacts"][] = array(
						"id" => $i,
						"ix" => $i,
						"object_id" => $c->getId(),
						"ot_id" => $c->getObjectTypeId(),
						"type" => 'company',
						'name' => $c->getObjectName(),
						'email' => $c->getEmailAddress(),
						'website' => $c->getWebpage('work') ? cleanUrl($c->getWebpageUrl('work'), false) : '',
						'workPhone1' => $c->getPhone('work',true) ? cleanUrl($c->getPhoneNumber('work',true), false) : '',
                        'workPhone2' => $c->getPhone('fax',true) ? cleanUrl($c->getPhoneNumber('fax',true), false) : '',
                        'workAddress' => $w_address ? $c->getFullAddress($w_address) : '',
						"companyId" => $c->getId(),
						"companyName" => $c->getObjectName(),
						"jobTitle" => '',
                        "department" => lang('company'),
						"email2" => '',
						"email3" => '',
						"workWebsite" => $c->getWebpage('work') ? cleanUrl($c->getWebpageUrl('work'), false) : '',
						"homeWebsite" => '',
						"homeAddress" => '',
						"homePhone1" => '',
						"homePhone2" => '',
						"mobilePhone" =>'',
						"createdOn" => $c->getCreatedOn() instanceof DateTimeValue ? ($c->getCreatedOn()->isToday() ? format_time($c->getCreatedOn()) : format_datetime($c->getCreatedOn())) : '',
						"createdOn_today" => $c->getCreatedOn() instanceof DateTimeValue ? $c->getCreatedOn()->isToday() : 0,
						"createdBy" => $c->getCreatedByDisplayName(),
						"createdById" => $c->getCreatedById(),
						"updatedOn" => $c->getUpdatedOn() instanceof DateTimeValue ? ($c->getUpdatedOn()->isToday() ? format_time($c->getUpdatedOn()) : format_datetime($c->getUpdatedOn())) : '',
						"updatedOn_today" => $c->getUpdatedOn() instanceof DateTimeValue ? $c->getUpdatedOn()->isToday() : 0,
						"updatedBy" => $c->getUpdatedByDisplayName(),
						"updatedById" => $c->getUpdatedById(),
						"memPath" => json_encode($c->getMembersToDisplayPath()),
					);
				}
    		}
		}
		return $object;
	}

	
	/**
	 * View single contact card, determines which card to show if the contact is a company or not
	 *
	 * @access public
	 * @param void
	 * @return null
	 * @deprecated
	 */
	function view() {
		$contact = Contacts::findById(get_id());
		if ($contact->getIsCompany())
			$this->company_card();
		else 
			$this->card();
	}
	
	
	/**
	 * View single contact
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function card() {
		$id = get_id();
		$contact = Contacts::findById($id);
		if(!$contact || !$contact->canView(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
		
		$this->setTemplate('card');
		
		tpl_assign('contact', $contact);
        
        $context = active_context();

		$obj_type_types = array('content_object');
		if (array_var($_GET, 'include_comments')) $obj_type_types[] = 'comment';
		/*
		$pagination = Objects::getObjects($context, null, 1, null, null, false, false, null, null, null, $obj_type_types);
		$result = $pagination->objects; 
		$total_items = $pagination->total ;
		 
		if(!$result) $result = array();

		$info = array();
		
		foreach ($result as $obj  ) {
			$info_elem =  $obj->getArrayInfo();
			
			$instance = Objects::instance()->findObject($info_elem['object_id']);
			$info_elem['url'] = $instance->getViewUrl();
			
			if ($instance instanceof  Contact ) {
				if( $instance->isCompany() ) {
					$info_elem['icon'] = 'ico-company';
					$info_elem['type'] = 'company';
				}
			}
			$info_elem['isRead'] = $instance->getIsRead(logged_user()->getId()) ;
			$info_elem['manager'] = get_class($instance->manager()) ;
			
			$info[] = $info_elem;
			
		}
        
        tpl_assign('feeds', $info);
        */ // Performance Killer
        
		ajx_extra_data(array("title" => $contact->getObjectName(), 'icon'=>'ico-contact'));
		ajx_set_no_toolbar(true);
		
		if (!$contact->isTrashed()){
			if($contact->canEdit(logged_user())) {
				add_page_action(lang('edit contact'), $contact->getEditUrl(), 'ico-edit', null, null, true);
			}
		}
		if ($contact->canDelete(logged_user())) {
			if ($contact->isTrashed()) {
				add_page_action(lang('restore from trash'), "javascript:if(confirm(lang('confirm restore objects'))) og.openLink('" . $contact->getUntrashUrl() ."');", 'ico-restore',null, null, true);
				add_page_action(lang('delete permanently'), "javascript:if(confirm(lang('confirm delete permanently'))) og.openLink('" . $contact->getDeletePermanentlyUrl() ."');", 'ico-delete',null, null, true);
			} else {
				if ($contact->getUserType() ) {
					
					if ($contact->hasReferences()) {
						// user-contacts, dont send them to trash, disable them
						add_page_action(lang('disable'), "javascript:if(confirm(lang('confirm disable user'))) og.openLink('" . $contact->getDisableUrl() ."',{callback:function(){og.customDashboard('contact','init',{},true)}});", 'ico-trash',null, null, true);
					}else {
						// user-contacts, dont send them to trash, disable them
						add_page_action(lang('delete'), "javascript:if(confirm(lang('confirm delete user'))) og.openLink('" . $contact->getDeleteUrl() ."',{callback:function(){og.customDashboard('contact','init',{},true)}});", 'ico-trash',null, null, true);
						add_page_action(lang('disable'), "javascript:if(confirm(lang('confirm disable user'))) og.openLink('" . $contact->getDisableUrl() ."',{callback:function(){og.customDashboard('contact','init',{},true)}});", 'ico-trash',null, null, true);

					}
				}else{
					// Non user contacts, move them to trash
					add_page_action(lang('move to trash'), "javascript:if(confirm(lang('confirm move to trash'))) og.openLink('" . $contact->getTrashUrl() ."');", 'ico-trash',null, null, true);
				}
			}
		} // if
		if (!$contact->isTrashed()) {
			if (can_manage_security(logged_user())) {
				if (!$contact->isUser()){
					add_page_action(lang('create user from contact'), $contact->getCreateUserUrl() , 'ico-user');
				}
			}
			if (!$contact->isUser() && $contact->canEdit(logged_user())) {
				if (!$contact->isArchived()) {
					add_page_action(lang('archive'), "javascript:if(confirm(lang('confirm archive object'))) og.openLink('" . $contact->getArchiveUrl() ."');", 'ico-archive-obj');
				} else {
					add_page_action(lang('unarchive'), "javascript:if(confirm(lang('confirm unarchive object'))) og.openLink('" . $contact->getUnarchiveUrl() ."');", 'ico-unarchive-obj');
				}
			}
		}

		
		if ($contact->isUser()   ){
			if ($contact->canChangePassword(logged_user())) {
				add_page_action(lang('change password'), $contact->getEditPasswordUrl(), 'ico-password', null, null, true);
			}
			if($contact->getId() == logged_user()->getId()){
				add_page_action(lang('edit preferences'), $contact->getEditPreferencesUrl(), 'ico-administration', null, null, true);
			}
			if($contact->canUpdatePermissions(logged_user())) {
				add_page_action(lang('permissions'), $contact->getUpdatePermissionsUrl(), 'ico-permissions', null, null, true);
			} 
		}
    
   		tpl_assign('company', $contact->getCompany());
		ApplicationReadLogs::createLog($contact, ApplicationReadLogs::ACTION_READ);
	} // view
	
	
	/**
	 * Add contact
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function add() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$this->setTemplate('edit_contact');
		//$this->setTemplate('add_contact');
		
		if (array_var($_GET, 'is_user') || array_var(array_var($_POST, 'contact'),'user')) {
			if (!can_manage_security(logged_user())) {
				flash_error(lang('no access permissions'));
				ajx_current("empty");
				return;
			} 
		} else {
			$notAllowedMember = '';
			if(!Contact::canAdd(logged_user(), active_context(), $notAllowedMember)) {
				if (str_starts_with($notAllowedMember, '-- req dim --')) flash_error(lang('must choose at least one member of', str_replace_first('-- req dim --', '', $notAllowedMember, $in)));
				else flash_error(lang('no context permissions to add',lang("contacts"), $notAllowedMember));
				ajx_current("empty");
				return;
			}
		}
		
		
		$contact = new Contact();		
		$im_types = ImTypes::findAll(array('order' => '`id`'));
		$contact_data = array_var($_POST, 'contact');
		if(!array_var($contact_data,'company_id')){
			$contact_data['company_id'] = get_id('company_id');
			$contact_data['timezone'] = logged_user()->getTimezone();
		}
		$redirect_to = get_url('contact');
		
		// Create contact from mail content, when writing an email...
		$contact_email = array_var($_GET, 'ce');
		if ($contact_email) $contact_data['email'] = $contact_email;
		if (array_var($_GET, 'div_id')) {
			$contact_data['new_contact_from_mail_div_id'] = array_var($_GET, 'div_id');
			$contact_data['hf_contacts'] = array_var($_GET, 'hf_contacts');
		}
                if($contact_email){
                    tpl_assign('contact_mail', true);
                }else{
                    tpl_assign('contact_mail', false);
                }
		tpl_assign('contact', $contact);
		tpl_assign('contact_data', $contact_data);
		tpl_assign('im_types', $im_types);

		// Submit
		if(is_array(array_var($_POST, 'contact'))) {
			ajx_current("empty");
			try {
				DB::beginWork();
				$contact_data['email']= trim ($contact_data['email']);
				
				Contacts::validate($contact_data);
				$newCompany = false;
				if (array_var($contact_data, 'isNewCompany') == 'true' && is_array(array_var($_POST, 'company'))){
					$company_data = array_var($_POST, 'company');
					$company = new Contact();
					$company->setFromAttributes($company_data);
					$company->setIsCompany(true);
					$company->setObjectName();
					$company->save();
					
					if($company_data['address'] != "")
						$company->addAddress($company_data['address'], $company_data['city'], $company_data['state'], $company_data['country'], $company_data['zipcode'], 'work', true);
					if($company_data['phone_number'] != "") $company->addPhone($company_data['phone_number'], 'work', true);
					if($company_data['fax_number'] != "") $company->addPhone($company_data['fax_number'], 'fax', true);
					if($company_data['homepage'] != "") $company->addWebpage($company_data['homepage'], 'work');
					if($company_data['email'] != "") $company->addEmail($company_data['email'], 'work', true);
					
					
					ApplicationLogs::createLog($company, ApplicationLogs::ACTION_ADD);
					$newCompany = true;
				}
				
				$contact_data['birthday'] = getDateValue($contact_data["birthday"]);
                                if(isset($contact_data['specify_username'])){
                                    if($contact_data['user']['username'] != ""){
                                        $contact_data['name'] = $contact_data['user']['username'];
                                    }else{
                                        $contact_data['name'] = $contact_data['first_name']." ".$contact_data['surname'];
                                    }
                                }else{
                                    $contact_data['name'] = $contact_data['first_name']." ".$contact_data['surname'];
                                }
				$contact->setFromAttributes($contact_data);

				if($newCompany)
					$contact->setCompanyId($company->getId());
				$contact->save();
					
				//Home form
				if($contact_data['h_address'] != "")
                                    $contact->addAddress($contact_data['h_address'], $contact_data['h_city'], $contact_data['h_state'], $contact_data['h_country'], $contact_data['h_zipcode'], 'home');
				if($contact_data['h_phone_number'] != "") $contact->addPhone($contact_data['h_phone_number'], 'home', true);
				if($contact_data['h_phone_number2'] != "") $contact->addPhone($contact_data['h_phone_number2'], 'home');
				if($contact_data['h_mobile_number'] != "") $contact->addPhone($contact_data['h_mobile_number'], 'mobile');
				if($contact_data['h_fax_number'] != "") $contact->addPhone($contact_data['h_fax_number'], 'fax');
				if($contact_data['h_pager_number'] != "") $contact->addPhone($contact_data['h_pager_number'], 'pager');
				if($contact_data['h_web_page'] != "") $contact->addWebpage($contact_data['h_web_page'], 'personal');
				
				//Work form
				if($contact_data['w_address'] != "")
                                    $contact->addAddress($contact_data['w_address'], $contact_data['w_city'], $contact_data['w_state'], $contact_data['w_country'], $contact_data['w_zipcode'], 'work');
				if($contact_data['w_phone_number'] != "") $contact->addPhone($contact_data['w_phone_number'], 'work', true);
				if($contact_data['w_phone_number2'] != "") $contact->addPhone($contact_data['w_phone_number2'], 'work');
				if($contact_data['w_assistant_number'] != "") $contact->addPhone($contact_data['w_assistant_number'], 'assistant');
				if($contact_data['w_callback_number'] != "") $contact->addPhone($contact_data['w_callback_number'], 'callback');
				if($contact_data['w_fax_number'] != "") $contact->addPhone($contact_data['w_fax_number'], 'fax', true);
				if($contact_data['w_web_page'] != "") $contact->addWebpage($contact_data['w_web_page'], 'work');
				
				//Other form
				if($contact_data['o_address'] != "")
                                    $contact->addAddress($contact_data['o_address'], $contact_data['o_city'], $contact_data['o_state'], $contact_data['o_country'], $contact_data['o_zipcode'], 'other');
				if($contact_data['o_phone_number'] != "") $contact->addPhone($contact_data['o_phone_number'], 'other', true);
				if($contact_data['o_phone_number2'] != "") $contact->addPhone($contact_data['o_phone_number2'], 'other');
				//if($contact_data['o_fax_number'] != "") $contact->addPhone($contact_data['o_fax_number'], 'fax');
				if($contact_data['o_web_page'] != "") $contact->addWebpage($contact_data['o_web_page'], 'other');
				
				//Emails and instant messaging form
				if($contact_data['email'] != "") $contact->addEmail($contact_data['email'], 'personal', true);
				if($contact_data['email2'] != "") $contact->addEmail($contact_data['email2'], 'personal');
				if($contact_data['email3'] != "") $contact->addEmail($contact_data['email3'], 'personal');
				
				//link it!
				$object_controller = new ObjectController();
				$member_ids = json_decode(array_var($_POST, 'members'));
				
				if($newCompany)
					$object_controller->add_to_members($company, $member_ids);
				$object_controller->add_to_members($contact, $member_ids);
				$object_controller->link_to_new_object($contact);
				$object_controller->add_subscribers($contact);
				$object_controller->add_custom_properties($contact);
				
				foreach($im_types as $im_type) {
					$value = trim(array_var($contact_data, 'im_' . $im_type->getId()));
					if($value <> '') {

						$contact_im_value = new ContactImValue();

						$contact_im_value->setContactId($contact->getId());
						$contact_im_value->setImTypeId($im_type->getId());
						$contact_im_value->setValue($value);
						$contact_im_value->setIsMain(array_var($contact_data, 'default_im') == $im_type->getId());

						$contact_im_value->save();
					} // if
				} // foreach
				
				ApplicationLogs::createLog($contact, ApplicationLogs::ACTION_ADD);
				
				//NEW ! User data in the same form 
				$user = array_var(array_var($_POST, 'contact'),'user');
				$user['username'] = str_replace(" ","",strtolower($contact_data['name'])) ;
				$this->createUserFromContactForm($user, $contact->getId(), $contact_data['email']);
				
				
				DB::commit();
				
				if (isset($contact_data['new_contact_from_mail_div_id'])) {
					$combo_val = trim($contact->getFirstName() . ' ' . $contact->getSurname() . ' <' . $contact->getEmailAddress('personal') . '>');
					evt_add("contact added from mail", array("div_id" => $contact_data['new_contact_from_mail_div_id'], "combo_val" => $combo_val, "hf_contacts" => $contact_data['hf_contacts']));
				}
				flash_success(lang('success add contact', $contact->getObjectName()));
				ajx_current("back");

				// Error...
			} catch(Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
			} // try

		} // if
	} // add

	
	function check_existing_email() {
		ajx_current("empty");
		$email = array_var($_REQUEST, 'email');
                $id_contact = array_var($_REQUEST, 'id_contact');
		$contact = Contacts::getByEmailCheck($email,$id_contact);
                
                if($contact){
                    if ($contact->getUserType() != 0) {
                            ajx_extra_data(array(
                                    "contact" => array(
                                        "name" => $contact->getFirstName(),
                                        "email" => $contact->getEmailAddress(),
                                        "id" => $contact->getEmailAddress(),
                                        "status" => true
                            )));
                    }else{
                            ajx_extra_data(array(
                                    "contact" => array(
                                        "name" => $contact->getFirstName(),
                                        "email" => $contact->getEmailAddress(),
                                        "id" => $contact->getObjectId(),
                                        "status" => false
                            )));
                    }
                }else{
                    ajx_extra_data(array(
                            "contact" => array(
                                "status" => false
                    )));
                }
	}
	
	
	/**
	 * Edit specific contact
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function edit() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$this->setTemplate('edit_contact');		
		
		$contact = Contacts::findById(get_id());
		if(!($contact instanceof Contact)) {
			flash_error(lang('contact dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$contact->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$im_types = ImTypes::findAll(array('order' => '`id`'));
		$personal_emails = $contact->getContactEmails('personal');
		
		$contact_data = array_var($_POST, 'contact');
		// Populate form fields
		if(!is_array($contact_data)) {
			$contact_data = array(
          	'first_name' => $contact->getFirstName(),
          	'surname' => $contact->getSurname(), 
                'username' => $contact->getUsername(),
          	'department' => $contact->getDepartment(),
          	'job_title' => $contact->getJobTitle(),
                'email' => $contact->getEmailAddress(),
                'email2' => !is_null($personal_emails) && isset($personal_emails[0]) ? $personal_emails[0]->getEmailAddress() : '',
                'email3' => !is_null($personal_emails) && isset($personal_emails[1])? $personal_emails[1]->getEmailAddress() : '',

                'w_web_page'=> $contact->getWebpageUrl('work'), 
                'birthday'=> $contact->getBirthday(),	 
                'w_phone_number'=> $contact->getPhoneNumber('work', true), 
                'w_phone_number2'=> $contact->getPhoneNumber('work'), 
                'w_fax_number'=> $contact->getPhoneNumber('fax', true), 
                'w_assistant_number'=> $contact->getPhoneNumber('assistant'), 
                'w_callback_number'=> $contact->getPhoneNumber('callback'), 

                'h_web_page'=> $contact->getWebpageUrl('personal'), 
                'h_phone_number'=> $contact->getPhoneNumber('home', true), 
                'h_phone_number2'=> $contact->getPhoneNumber('home'),
                'h_fax_number'=> $contact->getPhoneNumber('fax'), 
                'h_mobile_number'=> $contact->getPhoneNumber('mobile'),   
                'h_pager_number'=> $contact->getPhoneNumber('pager'),

                'o_web_page'=> $contact->getWebpageUrl('other'),
                'o_phone_number'=> $contact->getPhoneNumber('other',true), 
                'o_phone_number2'=> $contact->getPhoneNumber('other'), 

                'picture_file' => $contact->getPictureFile(),
          	'timezone' => $contact->getTimezone(),
          	'company_id' => $contact->getCompanyId(),
      	    ); // array
			
      	    $w_address = $contact->getAddress('work');
			if($w_address){
				$contact_data['w_address'] = $w_address->getStreet();
				$contact_data['w_city'] = $w_address->getCity();
				$contact_data['w_state'] = $w_address->getState();
				$contact_data['w_zipcode'] = $w_address->getZipCode();
				$contact_data['w_country'] = $w_address->getCountry();
			}
			
			$h_address = $contact->getAddress('home');
			if($h_address){
				$contact_data['h_address'] = $h_address->getStreet();
				$contact_data['h_city'] = $h_address->getCity();
				$contact_data['h_state'] = $h_address->getState();
				$contact_data['h_zipcode'] = $h_address->getZipCode();
				$contact_data['h_country'] = $h_address->getCountry();
			}
			
			$o_address = $contact->getAddress('other');
			if($o_address){
				$contact_data['o_address'] = $o_address->getStreet();
				$contact_data['o_city'] = $o_address->getCity();
				$contact_data['o_state'] = $o_address->getState();
				$contact_data['o_zipcode'] = $o_address->getZipCode();
				$contact_data['o_country'] = $o_address->getCountry();
			}

      	    if(is_array($im_types)) {
      	    	foreach($im_types as $im_type) {
      	    		$contact_data['im_' . $im_type->getId()] = $contact->getImValue($im_type);
      	    	} // foreach
      	    } // if

      	    $default_im = $contact->getMainImType();
      	    $contact_data['default_im'] = $default_im instanceof ImType ? $default_im->getId() : '';
		} // if
                
                tpl_assign('isEdit', array_var($_GET, 'isEdit',false));
		tpl_assign('contact', $contact);
		tpl_assign('contact_data', $contact_data);
		tpl_assign('im_types', $im_types);
		
		
		//Contact Submit
		if(is_array(array_var($_POST, 'contact'))) {
			//	MANAGE CONCURRENCE WHILE EDITING
			/* FIXME or REMOVEME			
			$upd = array_var($_POST, 'updatedon');
			if ($upd && $contact->getUpdatedOn()->getTimestamp() > $upd && !array_var($_POST,'merge-changes') == 'true')
			{
				ajx_current('empty');
				evt_add("handle edit concurrence", array(
					"updatedon" => $contact->getUpdatedOn()->getTimestamp(),
					"genid" => array_var($_POST,'genid')
				));
				return;
			}
			if (array_var($_POST,'merge-changes') == 'true')
			{					
				$this->setTemplate('card');
				$new_contact = Contacts::findById($contact->getId());
				ajx_set_panel(lang ('tab name',array('name'=>$new_contact->getObjectName())));
				ajx_extra_data(array("title" => $new_contact->getObjectName(), 'icon'=>'ico-contact'));
				ajx_set_no_toolbar(true);
				return;
			}
			*/
			
			try {
				DB::beginWork();
				$contact_data['email']= trim ($contact_data['email']);
				Contacts::validate($contact_data, get_id());
				$newCompany = false;
				if (array_var($contact_data, 'isNewCompany') == 'true' && is_array(array_var($_POST, 'company'))){
					$company_data = array_var($_POST, 'company');

					Contacts::validate($company_data);
					
					$company = new Contact();
					$company->setFromAttributes($company_data);
					$company->setIsCompany(true);
					$company->setObjectName();
					$company->save();
					
					if($company_data['address'] != "")
						$company->addAddress($company_data['address'], $company_data['city'], $company_data['state'], $company_data['country'], $company_data['zipcode'], 'work', true);
					if($company_data['phone_number'] != "") $company->addPhone($company_data['phone_number'], 'work', true);
					if($company_data['fax_number'] != "") $company->addPhone($company_data['fax_number'], 'fax', true);
					if($company_data['homepage'] != "") $company->addWebpage($company_data['homepage'], 'work');
					if($company_data['email'] != "") $company->addEmail($company_data['email'], 'work' , true);
					
					ApplicationLogs::createLog($company,ApplicationLogs::ACTION_ADD);
					$newCompany = true;

				}
				
				$contact_data['birthday'] = getDateValue($contact_data["birthday"]);
                                if(isset($contact_data['specify_username'])){
                                    if($contact_data['user']['username'] != ""){
                                        $contact_data['name'] = $contact_data['user']['username'];
                                    }else{
                                        $contact_data['name'] = $contact_data['first_name']." ".$contact_data['surname'];
                                    }
                                }else{
                                    $contact_data['name'] = $contact_data['first_name']." ".$contact_data['surname'];
                                }
				
				$contact->setFromAttributes($contact_data);
				
				if($newCompany) {
					$contact->setCompanyId($company->getId());
				}
						
				//telephones
			
				$mainPone = $contact->getPhone('work', true);
				if($mainPone){
					$mainPone->editNumber($contact_data['w_phone_number']);
				}else{
					if($contact_data['w_phone_number'] != "") $contact->addPhone($contact_data['w_phone_number'], 'work', true);
				}
				
				$pone2 = $contact->getPhone('work');
				if($pone2){
						$pone2->editNumber($contact_data['w_phone_number2']);
				}else{
					if($contact_data['w_phone_number2'] != "") $contact->addPhone($contact_data['w_phone_number2'], 'work');
				}

				$faxPhone =  $contact->getPhone('fax',true);
				if($faxPhone){
						$faxPhone->editNumber($contact_data['w_fax_number']);
				}else{
					if($contact_data['w_fax_number'] != "") $contact->addPhone($contact_data['w_fax_number'], 'fax', true);
				}
				
				$assistantPhone =  $contact->getPhone('assistant');
				if($assistantPhone){
						$assistantPhone->editNumber($contact_data['w_assistant_number']);
				}else{
					if($contact_data['w_assistant_number'] != "") $contact->addPhone($contact_data['w_assistant_number'], 'assistant');
				}
				
				$callbackPhone =  $contact->getPhone('callback');
				if($callbackPhone){
						$callbackPhone->editNumber($contact_data['w_callback_number']);
				}else{
					if($contact_data['w_callback_number'] != "") $contact->addPhone($contact_data['w_callback_number'], 'callback');
				}
				
				$o_phone = $contact->getPhone('other',true);
				if($o_phone){
						$o_phone->editNumber($contact_data['o_phone_number']);
				}else{
					if($contact_data['o_phone_number'] != "") $contact->addPhone($contact_data['o_phone_number'], 'other', true);
				}
				
				$o_pone2 = $contact->getPhone('other');
				if($o_pone2){
						$o_pone2->editNumber($contact_data['o_phone_number2']);
				}else{
					if($contact_data['o_phone_number2'] != "") $contact->addPhone($contact_data['o_phone_number2'], 'other');
				}

				$h_phone = $contact->getPhone('home', true);
				if($h_phone){
						$h_phone->editNumber($contact_data['h_phone_number']);
				}else{
					if($contact_data['h_phone_number'] != "") $contact->addPhone($contact_data['h_phone_number'], 'home', true);
				}
				
				$h_phone2 = $contact->getPhone('home');
				if($h_phone2){
						$h_phone2->editNumber($contact_data['h_phone_number2']);
				}else{
					if($contact_data['h_phone_number2'] != "") $contact->addPhone($contact_data['h_phone_number2'], 'home');
				}

				$h_faxPhone =  $contact->getPhone('fax');
				if($h_faxPhone){
						$h_faxPhone->editNumber($contact_data['h_fax_number']);
				}else{
					if($contact_data['h_fax_number'] != "") $contact->addPhone($contact_data['h_fax_number'], 'fax');
				}
				
				$h_mobilePhone =  $contact->getPhone('mobile');
				if($h_mobilePhone){
						$h_mobilePhone->editNumber($contact_data['h_mobile_number']);
				}else{
					if($contact_data['h_mobile_number'] != "") $contact->addPhone($contact_data['h_mobile_number'], 'mobile');
				}
				
				$h_pagerPhone =  $contact->getPhone('pager');
				if($h_pagerPhone){
						$h_pagerPhone->editNumber($contact_data['h_pager_number']);
				}else{
					if($contact_data['h_pager_number'] != "") $contact->addPhone($contact_data['h_pager_number'], 'pager');
				}
				
				//Emails 
				
				$mail = $contact->getEmail('personal',true);
				if($mail){
						$mail->editEmailAddress($contact_data['email']);
				}else{ 
						if($contact_data['email'] != "") $contact->addEmail($contact_data['email'], 'personal' , true);
				}
				
				$mail2 = !is_null($personal_emails) && isset($personal_emails[0])? $personal_emails[0] : null;
				if($mail2){
						$mail2->editEmailAddress($contact_data['email2']);
				}else{ 
						if($contact_data['email2'] != "") $contact->addEmail($contact_data['email2'], 'personal');
				}
				
				$mail3 = !is_null($personal_emails) && isset($personal_emails[1])? $personal_emails[1] : null;
				if($mail3){
						$mail3->editEmailAddress($contact_data['email3']);
				}else{ 
						if($contact_data['email3'] != "") $contact->addEmail($contact_data['email3'], 'personal');
				}
				
				//Addresses
				
				$w_address = $contact->getAddress('work');
				if($w_address){
					$w_address->edit($contact_data['w_address'], $contact_data['w_city'], $contact_data['w_state'], $contact_data['w_country'], $contact_data['w_zipcode'],2,0);
				}else{
					if($contact_data['w_address'] != "" || $contact_data['w_city'] != "" || $contact_data['w_state'] != "" || $contact_data['w_country'] != "" || $contact_data['w_zipcode'] != "")
						$contact->addAddress($contact_data['w_address'], $contact_data['w_city'], $contact_data['w_state'], $contact_data['w_country'], $contact_data['w_zipcode'], 'work');
				}

				$h_address = $contact->getAddress('home');
				if($h_address){
					$h_address->edit($contact_data['h_address'], $contact_data['h_city'], $contact_data['h_state'], $contact_data['h_country'], $contact_data['h_zipcode'],1,0);
				}else{
					if($contact_data['h_address'] != "" || $contact_data['h_city'] != "" || $contact_data['h_state'] != "" || $contact_data['h_country'] != "" || $contact_data['h_zipcode'] != "")
						$contact->addAddress($contact_data['h_address'], $contact_data['h_city'], $contact_data['h_state'], $contact_data['h_country'], $contact_data['h_zipcode'], 'home');
				}
				
				$o_address = $contact->getAddress('other');
				if($o_address){
					$o_address->edit($contact_data['o_address'], $contact_data['o_city'], $contact_data['o_state'], $contact_data['o_country'], $contact_data['o_zipcode'],3,0);
				}else{
					if($contact_data['o_address'] != "" || $contact_data['o_city'] != "" || $contact_data['o_state'] != "" || $contact_data['o_country'] != "" || $contact_data['o_zipcode'] != "")
						$contact->addAddress($contact_data['o_address'], $contact_data['o_city'], $contact_data['o_state'], $contact_data['o_country'], $contact_data['o_zipcode'], 'other');
				}
				
				//Webpages
				
				$w_homepage = $contact->getWebpage('work');
				if($w_homepage){
						$w_homepage->editWebpageURL($contact_data['w_web_page']);
				}else{
						if($contact_data['w_web_page'] != "") $contact->addWebpage($contact_data['w_web_page'], 'work');
				}

				$h_homepage = $contact->getWebpage('personal');
				if($h_homepage){
						$h_homepage->editWebpageURL($contact_data['h_web_page']);
				}else{
						if($contact_data['h_web_page'] != "") $contact->addWebpage($contact_data['h_web_page'], 'personal');
				}
				
				$o_homepage = $contact->getWebpage('other');
				if($o_homepage){
						$o_homepage->editWebpageURL($contact_data['o_web_page']);
				}else{
						if($contact_data['o_web_page'] != "") $contact->addWebpage($contact_data['o_web_page'], 'other');
				}				

				$contact->setObjectName();
				$contact->save();
				$contact->clearImValues();

				foreach($im_types as $im_type) {
					$value = trim(array_var($contact_data, 'im_' . $im_type->getId()));
					if($value <> '') {

						$contact_im_value = new ContactImValue();

						$contact_im_value->setContactId($contact->getId());
						$contact_im_value->setImTypeId($im_type->getId());
						$contact_im_value->setValue($value);
						$contact_im_value->setIsMain(array_var($contact_data, 'default_im') == $im_type->getId());

						$contact_im_value->save();
					} // if
				} // foreach

				$member_ids = json_decode(array_var($_POST, 'members'));
				$object_controller = new ObjectController();
				if (count($member_ids)){
					$object_controller->add_to_members($contact, $member_ids);
				}
				if ($newCompany) $object_controller->add_to_members($company, $member_ids);
                                $object_controller->link_to_new_object($contact);
				$object_controller->add_subscribers($contact);
				$object_controller->add_custom_properties($contact);
				
				ApplicationLogs::createLog($contact, ApplicationLogs::ACTION_EDIT );
                                
                                // User settings
                                $user = array_var(array_var($_POST, 'contact'),'user');
                                if($user){                                    
                                    $user['username'] = str_replace(" ","",strtolower($name));
                                    $this->createUserFromContactForm($user, $contact->getId(), $contact->getEmailAddress());

                                    // Reload contact again due to 'createUserFromContactForm' changes
                                    Hook::fire("after_contact_quick_add", Contacts::instance()->findById($contact->getId()), $ret);
                                }
                                
				DB::commit();
				
	     		flash_success(lang('success edit contact', $contact->getObjectName()));
				ajx_current("back");

			} catch(Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
		  		ajx_current("empty");
			} // try
		} // if
	} // edit

	
	/**
	 * Edit contact picture
	 *
	 * @param void
	 * @return null
	 */
	function edit_picture() {
		
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$contact = Contacts::findById(get_id());
		if(!($contact instanceof Contact)) {
			flash_error(lang('contact dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$contact->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$redirect_to = array_var($_GET, 'redirect_to');
		if((trim($redirect_to)) == '' || !is_valid_url($redirect_to)) {
			$redirect_to = $contact->getUpdatePictureUrl();
		} // if
		tpl_assign('redirect_to', $redirect_to);

		$picture = array_var($_FILES, 'new_picture');
		tpl_assign('contact', $contact);

		if(is_array($picture)) {

			try {

				if(!isset($picture['name']) || !isset($picture['type']) || !isset($picture['size']) || !isset($picture['tmp_name']) || !is_readable($picture['tmp_name'])) {
					throw new InvalidUploadError($picture, lang('error upload file'));
				} // if

				$valid_types = array('image/jpg', 'image/jpeg', 'image/pjpeg', 'image/gif', 'image/png','image/x-png');
				$max_width   = config_option('max_avatar_width', 50);
				$max_height  = config_option('max_avatar_height', 50);
				if(!in_array($picture['type'], $valid_types) || !($image = getimagesize($picture['tmp_name']))) {
					throw new InvalidUploadError($picture, lang('invalid upload type', 'JPG, GIF, PNG'));
				} // if

				$old_file = $contact->getPicturePath();
				DB::beginWork();

				if(!$contact->setPicture($picture['tmp_name'], $picture['type'], $max_width, $max_height)) {
					throw new InvalidUploadError($avatar, lang('error edit picture'));
				} // if

				ApplicationLogs::createLog($contact, ApplicationLogs::ACTION_EDIT);
				DB::commit();

				if(is_file($old_file)) {
					@unlink($old_file);
				} // if

				flash_success(lang('success edit picture'));
				
				ajx_current("back");
			} catch(Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			} // try
		} // if
	} // edit_picture

	
	/**
	 * Delete picture
	 *
	 * @param void
	 * @return null
	 */
	function delete_picture() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$contact = Contacts::findById(get_id());
		if(!($contact instanceof Contact)) {
			flash_error(lang('contact dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$contact->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$redirect_to = array_var($_GET, 'redirect_to');
		if((trim($redirect_to)) == '' || !is_valid_url($redirect_to)) {
			$redirect_to = $contact->getUpdatePictureUrl();
		} // if
		tpl_assign('redirect_to', $redirect_to);

		if(!$contact->hasPicture()) {
			flash_error(lang('picture dnx'));
			ajx_current("empty");
			return;
		} // if

		try {
			DB::beginWork();
			$contact->deletePicture();
			$contact->save();
			ApplicationLogs::createLog($contact, ApplicationLogs::ACTION_EDIT);

			DB::commit();

			flash_success(lang('success delete picture'));
			ajx_current("back");
		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error delete picture'));
			ajx_current("empty");
		} // try

	} // delete_picture

	
	/**
	 * Delete specific contact
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function delete() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$contact = Contacts::findById(get_id());
		if(!($contact instanceof Contact)) {
			flash_error(lang('contact dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$contact->canDelete(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		try {

			DB::beginWork();
			$contact->trash();
			ApplicationLogs::createLog($contact, ApplicationLogs::ACTION_TRASH );
			
			

			DB::commit();

			flash_success(lang('success delete contact', $contact->getObjectName()));
			ajx_current("back");
		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error delete contact'));
			ajx_current("empty");
		} // try
	} // delete
	
	
	function import_from_csv_file() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		@set_time_limit(0);
		ini_set('auto_detect_line_endings', '1');
		if (isset($_GET['from_menu']) && $_GET['from_menu'] == 1) unset($_SESSION['history_back']);
		if (isset($_SESSION['history_back'])) {
			unset($_SESSION['history_back']);
			ajx_current("start");
		} else {
			
			if(!Contact::canAdd(logged_user(), active_context())) {
				flash_error(lang('no access permissions'));
				ajx_current("empty");
				return;
			} 
	
			$this->setTemplate('csv_import');			
			
			$type = array_var($_GET, 'type', array_var($_SESSION, 'import_type', 'contact')); //type of import (contact - company)
			if (!isset($_SESSION['import_type']) || ($type != $_SESSION['import_type'] && $type != ''))
				$_SESSION['import_type'] = $type;
			tpl_assign('import_type', $type);
			
			$filedata = array_var($_FILES, 'csv_file');
			if (is_array($filedata) && !is_array(array_var($_POST, 'select_contact'))) {
				
				$filename = $filedata['tmp_name'].'.csv';
				copy($filedata['tmp_name'], $filename);
				
				$first_record_has_names = array_var($_POST, 'first_record_has_names', false);
				$delimiter = array_var($_POST, 'delimiter', '');
				if ($delimiter == '') $delimiter = $this->searchForDelimiter($filename);
				
				$_SESSION['delimiter'] = $delimiter;
				$_SESSION['csv_import_filename'] = $filename;
				$_SESSION['first_record_has_names'] = $first_record_has_names;
				
				$titles = $this->read_csv_file($filename, $delimiter, true);
				
				tpl_assign('titles', $titles);
			}
			
			if (array_var($_GET, 'calling_back', false)) {
				$filename = $_SESSION['csv_import_filename'];
				$delimiter = $_SESSION['delimiter'];
				$first_record_has_names = $_SESSION['first_record_has_names'];
				
				$titles = $this->read_csv_file($filename, $delimiter, true);

				unset($_GET['calling_back']);
				tpl_assign('titles', $titles);
			}
			
			if (is_array(array_var($_POST, 'select_contact')) || is_array(array_var($_POST, 'select_company'))) {
				
				$type = $_SESSION['import_type'];
				$filename = $_SESSION['csv_import_filename'];
				$delimiter = $_SESSION['delimiter'];
				$first_record_has_names = $_SESSION['first_record_has_names'];
				
				$registers = $this->read_csv_file($filename, $delimiter);
				
				$import_result = array('import_ok' => array(), 'import_fail' => array());

				$i = $first_record_has_names ? 1 : 0;
				while ($i < count($registers)) {
					try {
						DB::beginWork();
						if ($type == 'contact') {
							$contact_data = $this->buildContactData(array_var($_POST, 'select_contact'), array_var($_POST, 'check_contact'), $registers[$i]);
							$contact_data['import_status'] = '('.lang('updated').')';
							$fname = DB::escape(array_var($contact_data, "first_name"));
							$lname = DB::escape(array_var($contact_data, "surname"));
							$email_cond = array_var($contact_data, "email") != '' ? " OR email_address = '".array_var($contact_data, "email")."'" : "";
							$contact = Contacts::findOne(array(
								"conditions" => "first_name = ".$fname." AND surname = ".$lname." $email_cond",
								'join' => array(
                                    'table' => ContactEmails::instance()->getTableName(),
                                    'jt_field' => 'contact_id',
                                    'e_field' => 'object_id',
							)));
							$log_action = ApplicationLogs::ACTION_EDIT;
							if (!$contact) {
								$contact = new Contact();
								$contact_data['import_status'] = '('.lang('new').')';
								$log_action = ApplicationLogs::ACTION_ADD;
								$can_import = $contact->canAdd(logged_user(), active_context());
								
							} else {
								$can_import = $contact->canEdit(logged_user());
							}
							if ($can_import) {
								$comp_name = DB::escape(array_var($contact_data, "company_id"));
								if ($comp_name != '') {
									$company = Contacts::findOne(array("conditions" => "first_name = $comp_name AND is_company = 1"));
									if ($company) {
										$contact_data['company_id'] = $company->getId();
									} 
									$contact_data['import_status'] .= " " . lang("company") . " $comp_name";
								} else {
									$contact_data['company_id'] = 0;
								}
								$contact_data['name'] = $contact_data['first_name']." ".$contact_data['surname'];
								$contact->setFromAttributes($contact_data);
								$contact->save();

								$contact->addEmail(array_var($contact_data, "email"),'personal');

								ApplicationLogs::createLog($contact, null, $log_action);
								$import_result['import_ok'][] = $contact_data;
							} else {
								throw new Exception(lang('no access permissions'));
							}
							
						}else if ($type == 'company') {
							$contact_data = $this->buildCompanyData(array_var($_POST, 'select_company'), array_var($_POST, 'check_company'), $registers[$i]);
							$contact_data['import_status'] = '('.lang('updated').')';
							$comp_name = DB::escape(array_var($contact_data, "first_name"));
							$company = Contacts::findOne(array("conditions" => "first_name = $comp_name AND is_company = 1"));
							$log_action = ApplicationLogs::ACTION_EDIT;
							if (!$company) {
								$company = new Contact();
								$contact_data['import_status'] = '('.lang('new').')';
								$log_action = ApplicationLogs::ACTION_ADD;
								$can_import = $company->canAdd(logged_user(), active_context());
								
							} else {
								$can_import = $company->canEdit(logged_user());
							}
							if ($can_import) {
								$contact_data['name'] = $contact_data['first_name'];
								$contact_data['is_company'] = 1;
								$company->setFromAttributes($contact_data);
								$company->save();
								ApplicationLogs::createLog($company, null, $log_action);
								
								$import_result['import_ok'][] = $contact_data;
							} else {
								throw new Exception(lang('no access permissions'));
							}
						}

						DB::commit();						
						
					} catch (Exception $e) {
						DB::rollback();
						$contact_data['fail_message'] = substr_utf($e->getMessage(), strpos_utf($e->getMessage(), "\r\n"));
						$import_result['import_fail'][] = $contact_data;
					}		
					$i++;
				}
				unlink($_SESSION['csv_import_filename']);
				unset($_SESSION['csv_import_filename']);
				unset($_SESSION['delimiter']);
				unset($_SESSION['first_record_has_names']);
				unset($_SESSION['import_type']);
				
				$_SESSION['history_back'] = true;
				tpl_assign('import_result', $import_result);
			}
		}
	} // import_from_csv_file

		
	function read_csv_file($filename, $delimiter, $only_first_record = false) {
		
		$handle = fopen($filename, 'rb');
		if (!$handle) {
			flash_error(lang('file not exists'));
			ajx_current("empty");
			return;
		}
		
		if ($only_first_record) {
			$result = fgetcsv($handle, null, $delimiter);
			$aux = array();
			foreach ($result as $title) $aux[] = mb_convert_encoding($title, "UTF-8", detect_encoding($title));
			$result = $aux;			
		} else {
			
			$result = array();
			while ($fields = fgetcsv($handle, null, $delimiter)) {
				$aux = array();
				foreach ($fields as $field) $aux[] = mb_convert_encoding($field, "UTF-8", detect_encoding($field));
				$result[] = $aux;
			}
		}

		fclose($handle);
		return $result;
	} //read_csv_file
	
	
	private function searchForDelimiter($filename) {
		$delimiterCount = array(',' => 0, ';' => 0);
		
		$handle = fopen($filename, 'rb');
		$str = fgets($handle);
		fclose($handle);
		
		$del = null;
		foreach($delimiterCount as $k => $v) {
			$exploded = explode($k, $str);
			$delimiterCount[$k] = count($exploded);
			if ($del == null || $delimiterCount[$k] > $delimiterCount[$del]) $del = $k;
		}
		return $del;
	}
	
	
	function export_to_csv_file() {
		$this->setTemplate('csv_export');
		$ids = array_var($_GET, 'ids');
		$type = array_var($_GET, 'type', array_var($_SESSION, 'import_type', 'contact')); //type of import (contact - company)		
		tpl_assign('import_type', $type);
		if (!isset($_SESSION['import_type']) || ($type != $_SESSION['import_type'] && $type != '')){
			$_SESSION['import_type'] = $type;
		}
                
		$checked_fields = ($type == 'contact') ? array_var($_POST, 'check_contact') : array_var($_POST, 'check_company');
		if (is_array($checked_fields)) {
			$titles = '';
			$imp_type = array_var($_SESSION, 'import_type', 'contact');
			if ($imp_type == 'contact') {
				$field_names = Contacts::getContactFieldNames();
				
				foreach($checked_fields as $k => $v) {
					if (isset($field_names["contact[$k]"]) && $v == 'checked')
						$titles .= $field_names["contact[$k]"] . ',';
				}
				$titles = substr_utf($titles, 0, strlen_utf($titles)-1) . "\n";
			}else{
                                $field_names = Contacts::getCompanyFieldNames();
				
				foreach($checked_fields as $k => $v) {
					if (isset($field_names["company[$k]"]) && $v == 'checked')
						$titles .= $field_names["company[$k]"] . ',';
				}
				$titles = substr_utf($titles, 0, strlen_utf($titles)-1) . "\n";
                        }
				
			$filename = rand().'.tmp';
			$handle = fopen(ROOT.'/tmp/'.$filename, 'wb');
			fwrite($handle, $titles);
			
			$ids_sql = ($ids)? " AND id IN (".$ids.") " : "";
			if (array_var($_SESSION, 'import_type', 'contact') == 'contact') {
                                $conditions .= ($conditions == "" ? "" : " AND ") . "`archived_by_id` = 0" . ($conditions ? " AND $conditions" : "");
                                $conditions .= $ids_sql;
				$contacts = Contacts::instance()->getAllowedContacts($conditions);
				foreach ($contacts as $contact) {
					fwrite($handle, $this->build_csv_from_contact($contact, $checked_fields) . "\n");
				}
			}else{
                                $conditions .= ($conditions == "" ? "" : " AND ") . "`archived_by_id` = 0" . ($conditions ? " AND $conditions" : "");
                                $conditions .=$ids_sql;
				$companies = Contacts::getVisibleCompanies(logged_user(), $conditions);
				foreach ($companies as $company) {
					fwrite($handle, $this->build_csv_from_company($company, $checked_fields) . "\n");
				}
                        }
			
			fclose($handle);

			$_SESSION['contact_export_filename'] = $filename;
			flash_success(($imp_type == 'contact' ? lang('success export contacts') : lang('success export companies')));
		} else {
			unset($_SESSION['contact_export_filename']);
			return;
		}
	}
	
	
	function download_exported_file() {
		$filename = array_var($_SESSION, 'contact_export_filename', '');
		if ($filename != '') {
			$path = ROOT.'/tmp/'.$filename;
			$size = filesize($path);
                        
			if (isset($_SESSION['fname'])) {
				$name = $_SESSION['fname'];
				unset($_SESSION['fname']);
			}
			else $name = (array_var($_SESSION, 'import_type', 'contact') == 'contact' ? 'contacts.csv' : 'companies.csv');
			
			unset($_SESSION['contact_export_filename']);
			unset($_SESSION['import_type']);
			download_file($path, 'text/csv', $name, $size, true);
			unlink($path);
			die();			
		} else $this->setTemplate('csv_export');
	}
	
	
	private function build_csv_field($text, $last = false) {
		if ($text instanceof DateTimeValue) {
			$text = $text->format("Y-m-d");
		}
		if (strpos($text, ",") !== FALSE) {
			$str = "'$text'";
		} else $str = $text;
		if (!$last) {
			$str .= ",";
		}
		return $str;
	}
	
	
	function build_csv_from_contact(Contact $contact, $checked) {
		$str = '';
                
		if (isset($checked['first_name']) && $checked['first_name'] == 'checked') $str .= self::build_csv_field($contact->getFirstName());
		if (isset($checked['surname']) && $checked['surname'] == 'checked') $str .= self::build_csv_field($contact->getSurname());
		if (isset($checked['email']) && $checked['email'] == 'checked') $str .= self::build_csv_field($contact->getEmailAddress('personal'));
		if (isset($checked['company_id']) && $checked['company_id'] == 'checked') $str .= self::build_csv_field($contact->getCompany() ? $contact->getCompany()->getObjectName() : "");
		
		if (isset($checked['w_web_page']) && $checked['w_web_page'] == 'checked') $str .= self::build_csv_field($contact->getWebPageUrl('work'));
		$work_address = $contact->getAddress('work');
		if ($work_address){
			if (isset($checked['w_address']) && $checked['w_address'] == 'checked') $str .= self::build_csv_field($work_address->getStreet());
			if (isset($checked['w_city']) && $checked['w_city'] == 'checked') $str .= self::build_csv_field($work_address->getStreet());
			if (isset($checked['w_state']) && $checked['w_state'] == 'checked') $str .= self::build_csv_field($work_address->getState());
			if (isset($checked['w_zipcode']) && $checked['w_zipcode'] == 'checked') $str .= self::build_csv_field($work_address->getZipcode());
			if (isset($checked['w_country']) && $checked['w_country'] == 'checked') $str .= self::build_csv_field($work_address->getCountryName());
		}
		
		if (isset($checked['w_phone_number']) && $checked['w_phone_number'] == 'checked') $str .= self::build_csv_field($contact->getPhoneNumber('work',true));
		if (isset($checked['w_phone_number2']) && $checked['w_phone_number2'] == 'checked') $str .= self::build_csv_field($contact->getPhoneNumber('work'));
		if (isset($checked['w_fax_number']) && $checked['w_fax_number'] == 'checked') $str .= self::build_csv_field($contact->getPhoneNumber('fax',true));
		if (isset($checked['w_assistant_number']) && $checked['w_assistant_number'] == 'checked') $str .= self::build_csv_field($contact->getPhoneNumber('assistant'));
		if (isset($checked['w_callback_number']) && $checked['w_callback_number'] == 'checked') $str .= self::build_csv_field($contact->getPhoneNumber('callback'));
		
		if (isset($checked['h_web_page']) && $checked['h_web_page'] == 'checked') $str .= self::build_csv_field($contact->getWebPageUrl('home'));
		$home_address = $contact->getAddress('home');
		if ($home_address){
			if (isset($checked['h_address']) && $checked['h_address'] == 'checked') $str .= self::build_csv_field($home_address->getStreet());
			if (isset($checked['h_city']) && $checked['h_city'] == 'checked') $str .= self::build_csv_field($home_address->getCity());
			if (isset($checked['h_state']) && $checked['h_state'] == 'checked') $str .= self::build_csv_field($home_address->getState());
			if (isset($checked['h_zipcode']) && $checked['h_zipcode'] == 'checked') $str .= self::build_csv_field($home_address->getZipcode());
			if (isset($checked['h_country']) && $checked['h_country'] == 'checked') $str .= self::build_csv_field($home_address->getCountryName());
		}
		if (isset($checked['h_phone_number']) && $checked['h_phone_number'] == 'checked') $str .= self::build_csv_field($contact->getPhoneNumber('home',true));
		if (isset($checked['h_phone_number2']) && $checked['h_phone_number2'] == 'checked') $str .= self::build_csv_field($contact->getPhoneNumber('home'));
		if (isset($checked['h_fax_number']) && $checked['h_fax_number'] == 'checked') $str .= self::build_csv_field($contact->getPhoneNumber('fax'));
		if (isset($checked['h_mobile_number']) && $checked['h_mobile_number'] == 'checked') $str .= self::build_csv_field($contact->getPhoneNumber('mobile'));
		if (isset($checked['h_pager_number']) && $checked['h_pager_number'] == 'checked') $str .= self::build_csv_field($contact->getPhoneNumber('pager'));
		
		if (isset($checked['o_web_page']) && $checked['o_web_page'] == 'checked') $str .= self::build_csv_field($contact->getWebPageUrl('other'));
		$other_address = $contact->getAddress('other');
		if ($other_address){
			if (isset($checked['o_address']) && $checked['o_address'] == 'checked') $str .= self::build_csv_field($other_address->getStreet());
			if (isset($checked['o_city']) && $checked['o_city'] == 'checked') $str .= self::build_csv_field($other_address->getCity());
			if (isset($checked['o_state']) && $checked['o_state'] == 'checked') $str .= self::build_csv_field($other_address->getState());
			if (isset($checked['o_zipcode']) && $checked['o_zipcode'] == 'checked') $str .= self::build_csv_field($other_address->getZipcode());
			if (isset($checked['o_country']) && $checked['o_country'] == 'checked') $str .= self::build_csv_field($other_address->getCountryName());
		}
		if (isset($checked['o_phone_number']) && $checked['o_phone_number'] == 'checked') $str .= self::build_csv_field($contact->getPhoneNumber('other',true));
		if (isset($checked['o_phone_number2']) && $checked['o_phone_number2'] == 'checked') $str .= self::build_csv_field($contact->getPhoneNumber('other'));
		
		if (isset($checked['o_birthday']) && $checked['o_birthday'] == 'checked') $str .= self::build_csv_field($contact->getBirthday());
		
		$personal_emails = $contact->getContactEmails('personal');
		if (isset($checked['email2']) && $checked['email2'] == 'checked' && !is_null($personal_emails) && isset($personal_emails[0])) 
			$str .= self::build_csv_field($personal_emails[0]);
		if (isset($checked['email3']) && $checked['email3'] == 'checked' && !is_null($personal_emails) && isset($personal_emails[1])) 
			$str .= self::build_csv_field($personal_emails[1]);
		if (isset($checked['job_title']) && $checked['job_title'] == 'checked') $str .= self::build_csv_field($contact->getJobTitle());
		if (isset($checked['department']) && $checked['department'] == 'checked') $str .= self::build_csv_field($contact->getDepartment());
		
		$str = str_replace(array(chr(13).chr(10), chr(13), chr(10)), ' ', $str); //remove line breaks
		
		return $str;
	}
	
	
	function import_from_vcard() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		@set_time_limit(0);
		ini_set('auto_detect_line_endings', '1');
		if (isset($_GET['from_menu']) && $_GET['from_menu'] == 1) unset($_SESSION['go_back']);
		if (isset($_SESSION['go_back'])) {
			unset($_SESSION['go_back']);
			ajx_current("start");
		} else {
                
                    if(!Contact::canAdd(logged_user(), active_context())) {
                            flash_error(lang('no access permissions'));
                            ajx_current("empty");
                            return;
                    } 

                    $this->setTemplate('vcard_import');
                    tpl_assign('import_type', 'contact');                

                    $filedata = array_var($_FILES, 'vcard_file');
                    if (is_array($filedata)) {
                            $filename = ROOT.'/tmp/'.logged_user()->getId().'temp.vcf';
                            copy($filedata['tmp_name'], $filename);
                            $result = $this->read_vcard_file($filename);
                            unlink($filename);
                            $import_result = array('import_ok' => array(), 'import_fail' => array());

                            foreach ($result as $contact_data) {
                                    try {
                                            DB::beginWork();
                                            if (isset($contact_data['photo_tmp_filename'])) {
                                                $file_id = FileRepository::addFile($contact_data['photo_tmp_filename'], array('public' => true));
                                                $contact_data['picture_file'] = $file_id;
                                                unlink($contact_data['photo_tmp_filename']);
                                                unset($contact_data['photo_tmp_filename']);
                                            }
                                            if (isset($contact_data['company_name'])) {
                                                $company = Contacts::findOne(array("conditions" => "`first_name` = '".mysql_real_escape_string($contact_data['company_name'])."'"));
                                                if ($company == null) {                                                        
                                                        $company = new Contact();
                                                        $company->setObjectName($contact_data['company_name']);
                                                        $company->setIsCompany(1);
                                                        $company->save();                                                        
                                                        ApplicationLogs::createLog($company, null, ApplicationLogs::ACTION_ADD);
                                                }
                                                $contact_data['company_id'] = $company->getObjectId();
                                                unset($contact_data['company_name']);
                                            }

                                            $contact_data['import_status'] = '('.lang('updated').')';
                                            $fname = DB::escape(array_var($contact_data, "first_name"));
                                            $lname = DB::escape(array_var($contact_data, "surname"));
                                            $email_cond = array_var($contact_data, "email") != '' ? " OR email_address = '".array_var($contact_data, "email")."'" : "";
                                            $contact = Contacts::findOne(array(
                                                "conditions" => "first_name = ".$fname." AND surname = ".$lname." $email_cond",
                                                'join' => array(
                                                        'table' => ContactEmails::instance()->getTableName(),
                                                        'jt_field' => 'contact_id',
                                                        'e_field' => 'object_id',
                                                )));                                                        
                                            $log_action = ApplicationLogs::ACTION_EDIT;
                                            if (!$contact) {
                                                    $contact = new Contact();
                                                    $contact_data['import_status'] = '('.lang('new').')';
                                                    $log_action = ApplicationLogs::ACTION_ADD;
                                                    $can_import = active_project() != null ? $contact->canAdd(logged_user(), active_project()) : can_manage_contacts(logged_user());
                                            } else {
                                                    $can_import = $contact->canEdit(logged_user());
                                            }

                                            if ($can_import) {
                                                    $comp_name = DB::escape(array_var($contact_data, "company_id"));
                                                    if ($comp_name != '') {
                                                            $company = Contacts::findOne(array("conditions" => "first_name = $comp_name AND is_company = 1"));
                                                            if ($company) {
                                                                    $contact_data['company_id'] = $company->getId();
                                                            } 
                                                            $contact_data['import_status'] .= " " . lang("company") . " $comp_name";
                                                    } else {
                                                            $contact_data['company_id'] = 0;
                                                    }
                                                    $contact_data['name'] = $contact_data['first_name']." ".$contact_data['surname'];
                                                    $contact->setFromAttributes($contact_data);
                                                    $contact->save();

                                                    $contact->addEmail(array_var($contact_data, "email"),'personal');

                                                    ApplicationLogs::createLog($contact, null, $log_action);
                                                    $import_result['import_ok'][] = $contact_data;
                                            } else {
                                                    throw new Exception(lang('no access permissions'));
                                            }
                                            DB::commit();					
                                    } catch (Exception $e) {
                                            DB::rollback();
                                            $fail_msg = substr_utf($e->getMessage(), strpos_utf($e->getMessage(), "\r\n"));
                                            $import_result['import_fail'][] = array('first_name' => $fname, 'surname' => $lname, 'email' => $contact_data['email'], 'import_status' => $contact_data['import_status'], 'fail_message' => $fail_msg);
                                    }
                            }
                            $_SESSION['go_back'] = true;
                            tpl_assign('import_result', $import_result);
                        }
                    }
                        
	}

	
	private function read_vcard_file($filename, $only_first_record = false) {
            $handle = fopen($filename, 'rb');
            if (!$handle) {
                flash_error(lang('file not exists'));
                ajx_current("empty");
                return;
            }
            // parse VCard blocks
            $in_block = true;
            $results = array();
            while (($line = fgets($handle)) !== false) {
                if (preg_match('/^BEGIN:VCARD/', $line)) {
                    // START OF CONTACT
                    $in_block = true;
                    $block_data = array();
                } else if (preg_match('/^END:VCARD/', $line)) {
                    // END OF CONTACT
                    $in_block = false;
                    if (isset($photo_data))
                    if (isset($photo_data)) {
                        $filename = ROOT."/tmp/".rand().".$photo_type";
                        $f_handle = fopen($filename, "wb");
                        fwrite($f_handle, base64_decode($photo_data));
                        fclose($f_handle);
                        $block_data['photo_tmp_filename'] = $filename;
                    }
                    unset($photo_data);
                    unset($photo_enc);
                    unset($photo_type);

                    $results[] = $block_data;
                    if ($only_first_record && count($results) > 0) return $results;
                } else if (preg_match('/^N(:|;charset=[-a-zA-Z0-9.]+:)([^;]*);([^;]*)/i', $line, $matches)) {
                    // NAME
                    $block_data["first_name"] = trim($matches[count($matches)-1]);
                    $block_data["surname"] = trim($matches[count($matches)-2]);
                } else if (preg_match('/^ORG(:|;charset=[-a-zA-Z0-9.]+:)([^;]*)/i', $line, $matches)) {
                    // ORGANIZATION
                    $block_data["company_name"] = str_replace(array("\r\n", "\n", "\r", "\t", '\r\n', '\n', '\r', '\t'), " ", trim($matches[2]));
                } else if (preg_match('/^NOTE(:|;charset=[-a-zA-Z0-9.]+:)([^;]*)/i', $line, $matches)) {
                    // NOTES
                    $block_data["notes"] = trim($matches[count($matches)-1]);
                } else if (preg_match('/EMAIL;type=(PREF,)?INTERNET(,PREF)?(;type=(HOME|WORK))?(;type=PREF)?:([-a-zA-Z0-9_.]+@[-a-zA-Z0-9.]+)/i', $line, $matches)) {
                    // EMAIL
                    $email = trim($matches[count($matches)-1]);
                    if (!isset($block_data["email"])) 
                            $block_data["email"] = $email;
                    else if (!isset($block_data["email2"])) 
                            $block_data["email2"] = $email;
                    else if (!isset($block_data["email3"])) 
                            $block_data["email3"] = $email;

                } else if (preg_match('/URL(;type=(HOME|WORK))?.*?:(.+)/i', $line, $matches)) {
                    // WEB URL
                    $url = trim($matches[3]);
                    $url = str_replace(array("\r\n", "\n", "\r", "\t", '\r\n', '\n', '\r', '\t'), " ", $url);
                    if ($matches[2] == "HOME") {
                            $block_data['h_web_page'] = $url;
                    } else if ($matches[2] == "WORK") {
                            $block_data['w_web_page'] = $url;
                    } else {
                            $block_data['o_web_page'] = $url;
                    }
                } else if (preg_match('/TEL(;type=(HOME|WORK|CELL|FAX)[,A-Z]*)?.*:(.+)/i', $line, $matches)) {
                    // PHONE
                    $phone = trim($matches[3]);
                    $phone = str_replace(array("\r\n", "\n", "\r", "\t", '\r\n', '\n', '\r', '\t'), " ", $phone);
                    if ($matches[2] == "HOME") {
                        $block_data["h_phone_number"] = $phone;
                    } else if ($matches[2] == "CELL") {
                        $block_data["h_mobile_number"] = $phone;
                    } else if ($matches[2] == "WORK") {
                        $block_data["w_phone_number"] = $phone;
                    } else if ($matches[2] == "FAX") {
                        $block_data["w_fax_number"] = $phone;
                    } else {
                        $block_data["o_phone_number"] = $phone;
                    }
                    } else if (preg_match('/ADR;type=(HOME|WORK|[A-Z0-9]*)[,A-Z]*(:|;charset=[-a-zA-Z0-9.]+:|;type=pref:);;([^;]*);([^;]*);([^;]*);([^;]*);([^;]*)/i', $line, $matches)) {
                    // ADDRESS
                    // $matches is
                    // [1] <-- street
                    // [2] <-- city
                    // [3] <-- state
                    // [4] <-- zip
                    // [5] <-- country
                    $addr = array_slice($matches, count($matches)-5);
                    foreach ($addr as $k=>$v) $addr[$k] = str_replace(array("\r\n", "\n", "\r", "\t", '\r\n', '\n', '\r', '\t'), " ", trim($v));
                    if ($matches[1] == "HOME") {
                        $block_data["h_address"] = $addr[0];
                        $block_data["h_city"] = $addr[1];
                        $block_data["h_state"] = $addr[2];
                        $block_data["h_zipcode"] = $addr[3];
                        $block_data["h_country"] = CountryCodes::getCountryCodeByName($addr[4]);
                    } else if ($matches[1] == "WORK") {
                        $block_data["w_address"] = $addr[0];
                        $block_data["w_city"] = $addr[1];
                        $block_data["w_state"] = $addr[2];
                        $block_data["w_zipcode"] = $addr[3];
                        $block_data["w_country"] = CountryCodes::getCountryCodeByName($addr[4]);
                    } else {
                        $block_data["o_address"] = $addr[0];
                        $block_data["o_city"] = $addr[1];
                        $block_data["o_state"] = $addr[2];
                        $block_data["o_zipcode"] = $addr[3];
                        $block_data["o_country"] = CountryCodes::getCountryCodeByName($addr[4]);
                    }
                } else if (preg_match('/^BDAY[;value=date]*:([0-9]+)-([0-9]+)-([0-9]+)/i', $line, $matches)) {
                    // BIRTHDAY
                    // $matches[1]  <-- year     $matches[2]  <-- month    $matches[3]  <-- day
                    $block_data["o_birthday"] = $matches[1] . '-' . $matches[2] . '-' . $matches[3] . "00:00:00";

                } else if (preg_match('/TITLE(:|;charset=[-a-zA-Z0-9.]+:)(.*)/i', $line, $matches)) {
                    // JOB TITLE
                    $block_data["job_title"] = str_replace(array("\r\n", "\n", "\r", "\t", '\r\n', '\n', '\r', '\t'), " ", trim($matches[2]));

                } else if (preg_match('/PHOTO(;ENCODING=(b|BASE64)?(;TYPE=([-a-zA-Z.]+))|;VALUE=uri):(.*)/i', $line, $matches)) {

                    foreach ($matches as $k => $v) {
                            if (str_starts_with(strtoupper($v), ";ENCODING")) $enc_idx = $k+1;
                            if (str_starts_with(strtoupper($v), ";TYPE")) $type_idx = $k+1;
                            if (str_starts_with(strtoupper($v), ";VALUE=uri")) $uri_idx = $k+1;
                    }
                    if (isset($enc_idx) && isset($type_idx)) {
                            $photo_enc = $matches[$enc_idx];
                            $photo_type = $matches[$type_idx];
                            $photo_data = str_replace(array("\r\n", "\n", "\r", "\t"), "", trim($matches[count($matches)-1]));
                    } else if (isset($uri_idx)) {
                            $uri = trim($matches[count($matches)-1]);            		
                            $photo_type = substr($uri, strrpos($uri, "."));
                            $data = file_get_contents(urldecode($uri));
                            $filename = ROOT."/tmp/".rand().".$photo_type";
                            $f_handle = fopen($filename, "wb");
                            fwrite($f_handle, $data);
                            fclose($f_handle);
                            $block_data['photo_tmp_filename'] = $filename;
                    }
                } else {
                    if (isset($photo_data) && isset($enc_idx) && isset($type_idx)) {
                            $photo_data .= str_replace(array("\r\n", "\n", "\r", "\t"), "", trim($line));
                    }
                }
                unset($matches);
            }
            fclose($handle);        
            return $results;
    } // read_vcard_file
    
    
    private function build_vcard($contacts) {
    	$vcard = "";

    	foreach($contacts as $contact) {
    		$vcard .= "BEGIN:VCARD\nVERSION:3.0\n";
    		
    		$vcard .= "N:" . $contact->getSurname() . ";" . $contact->getFirstname() . "\n";
    		$vcard .= "FN:" . $contact->getFirstname() . " " . $contact->getSurname() . "\n";
    		if ($contact->getCompany() instanceof Contact)
    			$vcard .= "ORG:" . $contact->getCompany()->getObjectName() . "\n";
    		if ($contact->getJobTitle())
    			$vcard .= "TITLE:" . $contact->getJobTitle() . "\n";
    		if ($contact->getBirthday() instanceof DateTimeValue)
    			$vcard .= "BDAY:" . $contact->getBirthday()->format("Y-m-d") . "\n";
    		$haddress = $contact->getAddress('home');
    		if ($haddress)
    			$vcard .= "ADR;TYPE=HOME:;;" . $haddress->getStreet() .";". $haddress->getCity() .";". $haddress->getState() .";". $haddress->getZipcode() .";". $haddress->getCountryName() . "\n";
    		$waddress = $contact->getAddress('work');
    		if ($waddress)
    			$vcard .= "ADR;TYPE=WORK:;;" . $waddress->getStreet() .";". $waddress->getCity() .";". $waddress->getState() .";". $waddress->getZipcode() .";". $waddress->getCountryName() . "\n";
    		$oaddress = $contact->getAddress('other');
    		if ($oaddress)
    			$vcard .= "ADR;TYPE=INTL:;;" . $oaddress->getStreet() .";". $oaddress->getCity() .";". $oaddress->getState() .";". $oaddress->getZipcode() .";". $oaddress->getCountryName() . "\n";
    		if ($contact->getPhoneNumber('home',true))
    			$vcard .= "TEL;TYPE=HOME,VOICE:" . $contact->getPhoneNumber('home',true) . "\n";
    		if ($contact->getPhoneNumber('work',true))
    			$vcard .= "TEL;TYPE=WORK,VOICE:" . $contact->getPhoneNumber('work',true) . "\n";
    		if ($contact->getPhoneNumber('other',true))
    			$vcard .= "TEL;TYPE=VOICE:" . $contact->getPhoneNumber('other',true) . "\n";
    		if ($contact->getPhoneNumber('fax'))
    			$vcard .= "TEL;TYPE=HOME,FAX:" . $contact->getPhoneNumber('fax') . "\n";
    		if ($contact->getPhoneNumber('fax', true))
    			$vcard .= "TEL;TYPE=WORK,FAX:" . $contact->getPhoneNumber('fax', true) . "\n";
    		if ($contact->getPhoneNumber('mobile'))
    			$vcard .= "TEL;TYPE=CELL,VOICE:" . $contact->getPhoneNumber('mobile') . "\n";
    		if ($contact->getWebpageUrl('personal'))
    			$vcard .= "URL;TYPE=HOME:" . $contact->getWebpageUrl('personal') . "\n";
    		if ($contact->getWebpageUrl('work'))
    			$vcard .= "URL;TYPE=WORK:" . $contact->getWebpageUrl('work') . "\n";
    		if ($contact->getWebpageUrl('other'))
    			$vcard .= "URL:" . $contact->getWebpageUrl('other') . "\n";
    		if ($contact->getEmailAddress('personal'))
    			$vcard .= "EMAIL;TYPE=PREF,INTERNET:" . $contact->getEmailAddress() . "\n";
    		$personal_emails = $contact->getContactEmails('personal');
    		if (!is_null($personal_emails) && isset($personal_emails[0]))
    			$vcard .= "EMAIL;TYPE=INTERNET:" . $personal_emails[0] . "\n";
    		if (!is_null($personal_emails) && isset($personal_emails[1]))
    			$vcard .= "EMAIL;TYPE=INTERNET:" . $personal_emails[1] . "\n";
			if ($contact->hasPicture()) {
    			$data = FileRepository::getFileContent($contact->getPictureFile());
    			$chunklen = 62;
    			$pre = "PHOTO;ENCODING=BASE64;TYPE=PNG:";
    			$b64 = base64_encode($data);
    			$enc_data = substr($b64, 0, $chunklen + 1 - strlen($pre)) . "\n ";
    			$enc_data .= chunk_split(substr($b64, $chunklen + 1 - strlen($pre)), $chunklen, "\n ");
    			$vcard .= $pre . $enc_data . "\n";
    		}

			$vcard .= "END:VCARD\n";
    	}
    	return $vcard;
    }
    
    function export_to_vcard() {
    	$ids = array_var($_GET, 'ids');
    	$contacts = array();
   		$contacts = Contacts::instance()->getAllowedContacts(" id IN (".$ids.")");
    	if (count($contacts) == 0) {
			flash_error(lang("you must select the contacts from the grid"));
			ajx_current("empty");
			return;
		}
		
    	$data = self::build_vcard($contacts);
    	$name = (count($contacts) == 1 ? $contacts[0]->getObjectName() : "contacts") . ".vcf";

    	download_contents($data, 'text/x-vcard', $name, strlen($data), true);
    	die();
    }
    

    function export_to_vcard_all() {
      $contacts_all = Contacts::instance()->getAllowedContacts();
      $user = logged_user();
      if (count($contacts_all) == 0) {
        flash_error(lang("you must select the contacts from the grid"));
        ajx_current("empty");
        return;
      }

      $data = self::build_vcard($contacts_all);
      $name = "contacts_all_".$user->getUsername().".vcf";

      download_contents($data, 'text/x-vcard', $name, strlen($data), true);
      die();
    }
	
	
	function buildContactData($position, $checked, $fields) {
		$contact_data = array();
		if (isset($checked['first_name']) && $checked['first_name']) $contact_data['first_name'] = array_var($fields, $position['first_name']);
		if (isset($checked['surname']) && $checked['surname']) $contact_data['surname'] = array_var($fields, $position['surname']);
		if (isset($checked['email']) && $checked['email']) $contact_data['email'] = array_var($fields, $position['email']);
		if (isset($checked['company_id']) && $checked['company_id']) $contact_data['company_id'] = array_var($fields, $position['company_id']);
		
		if (isset($checked['w_web_page']) && $checked['w_web_page']) $contact_data['w_web_page'] = array_var($fields, $position['w_web_page']);
		if (isset($checked['w_address']) && $checked['w_address']) $contact_data['w_address'] = array_var($fields, $position['w_address']);
		if (isset($checked['w_city']) && $checked['w_city']) $contact_data['w_city'] = array_var($fields, $position['w_city']);
		if (isset($checked['w_state']) && $checked['w_state']) $contact_data['w_state'] = array_var($fields, $position['w_state']);
		if (isset($checked['w_zipcode']) && $checked['w_zipcode']) $contact_data['w_zipcode'] = array_var($fields, $position['w_zipcode']);
		if (isset($checked['w_country']) && $checked['w_country']) $contact_data['w_country'] = CountryCodes::getCountryCodeByName(array_var($fields, $position['w_country']));
		if (isset($checked['w_phone_number']) && $checked['w_phone_number']) $contact_data['w_phone_number'] = array_var($fields, $position['w_phone_number']);
		if (isset($checked['w_phone_number2']) && $checked['w_phone_number2']) $contact_data['w_phone_number2'] = array_var($fields, $position['w_phone_number2']);
		if (isset($checked['w_fax_number']) && $checked['w_fax_number']) $contact_data['w_fax_number'] = array_var($fields, $position['w_fax_number']);
		if (isset($checked['w_assistant_number']) && $checked['w_assistant_number']) $contact_data['w_assistant_number'] = array_var($fields, $position['w_assistant_number']);
		if (isset($checked['w_callback_number']) && $checked['w_callback_number']) $contact_data['w_callback_number'] = array_var($fields, $position['w_callback_number']);
		
		if (isset($checked['h_web_page']) && $checked['h_web_page']) $contact_data['h_web_page'] = array_var($fields, $position['h_web_page']);
		if (isset($checked['h_address']) && $checked['h_address']) $contact_data['h_address'] = array_var($fields, $position['h_address']);
		if (isset($checked['h_city']) && $checked['h_city']) $contact_data['h_city'] = array_var($fields, $position['h_city']);
		if (isset($checked['h_state']) && $checked['h_state']) $contact_data['h_state'] = array_var($fields, $position['h_state']);
		if (isset($checked['h_zipcode']) && $checked['h_zipcode']) $contact_data['h_zipcode'] = array_var($fields, $position['h_zipcode']);
		if (isset($checked['h_country']) && $checked['h_country']) $contact_data['h_country'] = CountryCodes::getCountryCodeByName(array_var($fields, $position['h_country']));
		if (isset($checked['h_phone_number']) && $checked['h_phone_number']) $contact_data['h_phone_number'] = array_var($fields, $position['h_phone_number']);
		if (isset($checked['h_phone_number2']) && $checked['h_phone_number2']) $contact_data['h_phone_number2'] = array_var($fields, $position['h_phone_number2']);
		if (isset($checked['h_fax_number']) && $checked['h_fax_number']) $contact_data['h_fax_number'] = array_var($fields, $position['h_fax_number']);
		if (isset($checked['h_mobile_number']) && $checked['h_mobile_number']) $contact_data['h_mobile_number'] = array_var($fields, $position['h_mobile_number']);
		if (isset($checked['h_pager_number']) && $checked['h_pager_number']) $contact_data['h_pager_number'] = array_var($fields, $position['h_pager_number']);
		
		if (isset($checked['o_web_page']) && $checked['o_web_page']) $contact_data['o_web_page'] = array_var($fields, $position['o_web_page']);
		if (isset($checked['o_address']) && $checked['o_address']) $contact_data['o_address'] = array_var($fields, $position['o_address']);
		if (isset($checked['o_city']) && $checked['o_city']) $contact_data['o_city'] = array_var($fields, $position['o_city']);
		if (isset($checked['o_state']) && $checked['o_state']) $contact_data['o_state'] = array_var($fields, $position['o_state']);
		if (isset($checked['o_zipcode']) && $checked['o_zipcode']) $contact_data['o_zipcode'] = array_var($fields, $position['o_zipcode']);
		if (isset($checked['o_country']) && $checked['o_country']) $contact_data['o_country'] = CountryCodes::getCountryCodeByName(array_var($fields, $position['o_country']));
		if (isset($checked['o_phone_number']) && $checked['o_phone_number']) $contact_data['o_phone_number'] = array_var($fields, $position['o_phone_number']);
		if (isset($checked['o_phone_number2']) && $checked['o_phone_number2']) $contact_data['o_phone_number2'] = array_var($fields, $position['o_phone_number2']);
		if (isset($checked['o_fax_number']) && $checked['o_fax_number']) $contact_data['o_fax_number'] = array_var($fields, $position['o_fax_number']);
		if (isset($checked['o_birthday']) && $checked['o_birthday']) $contact_data['o_birthday'] = array_var($fields, $position['o_birthday']);
		if (isset($checked['email2']) && $checked['email2']) $contact_data['email2'] = array_var($fields, $position['email2']);
		if (isset($checked['email3']) && $checked['email3']) $contact_data['email3'] = array_var($fields, $position['email3']);
		if (isset($checked['job_title']) && $checked['job_title']) $contact_data['job_title'] = array_var($fields, $position['job_title']);
		if (isset($checked['department']) && $checked['department']) $contact_data['department'] = array_var($fields, $position['department']);
		if (isset($checked['middlename']) && $checked['middlename']) $contact_data['middlename'] = array_var($fields, $position['middlename']);
		if (isset($checked['notes']) && $checked['notes']) $contact_data['notes'] = array_var($fields, $position['notes']);
		          
		$contact_data['is_private'] = false;
		$contact_data['timezone'] = logged_user()->getTimezone();

		return $contact_data;
	} // buildContactData
	
	
	// ---------------------------------------------------
	//  COMPANIES
	// ---------------------------------------------------	
	
	
	function company_card() {
		$this->setTemplate("view_company");
		$company = Contacts::findById(get_id());
		if(!($company instanceof Contact)) {
			flash_error(lang('company dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$company->canView(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		ajx_set_no_toolbar(true);
		ajx_extra_data(array("title" => $company->getObjectName(), 'icon'=>'ico-company'));
		tpl_assign('company', $company);
		
		ApplicationReadLogs::createLog($company, ApplicationReadLogs::ACTION_READ);
	} // card

	
	private function getCompanyDataFromContactData($contact_data) {
		$comp = array();
		$comp['name'] = array_var($contact_data, 'company_id');
		$comp['email'] = array_var($contact_data, 'email');
		$comp['homepage'] = array_var($contact_data, 'w_web_page');
		$comp['address'] = array_var($contact_data, 'w_address');
		$comp['address2'] = '';
		$comp['city'] = array_var($contact_data, 'w_city');
		$comp['state'] = array_var($contact_data, 'w_state');
		$comp['zipcode'] = array_var($contact_data, 'w_zipcode');
		$comp['country'] = array_var($contact_data, 'w_country');
		$comp['phone_number'] = array_var($contact_data, 'w_phone_number');
		$comp['fax_number'] = array_var($contact_data, 'w_fax_number');
		$comp['timezone'] = logged_user()->getTimezone();
		return $comp;
	}
	
	
	function buildCompanyData($position, $checked, $fields) {
		$contact_data = array();
		if (isset($checked['first_name']) && $checked['first_name']) $contact_data['first_name'] = array_var($fields, $position['first_name']);
		if (isset($checked['email']) && $checked['email']) $contact_data['email'] = array_var($fields, $position['email']);
		if (isset($checked['homepage']) && $checked['homepage']) $contact_data['homepage'] = array_var($fields, $position['homepage']);
		if (isset($checked['address']) && $checked['address']) $contact_data['address'] = array_var($fields, $position['address']);
		if (isset($checked['address2']) && $checked['address2']) $contact_data['address2'] = array_var($fields, $position['address2']);
		if (isset($checked['city']) && $checked['city']) $contact_data['city'] = array_var($fields, $position['city']);
		if (isset($checked['state']) && $checked['state']) $contact_data['state'] = array_var($fields, $position['state']);
		if (isset($checked['zipcode']) && $checked['zipcode']) $contact_data['zipcode'] = array_var($fields, $position['zipcode']);
		if (isset($checked['country']) && $checked['country']) $contact_data['country'] = CountryCodes::getCountryCodeByName(array_var($fields, $position['country']));
		if (isset($checked['phone_number']) && $checked['phone_number']) $contact_data['phone_number'] = array_var($fields, $position['phone_number']);
		if (isset($checked['fax_number']) && $checked['fax_number']) $contact_data['fax_number'] = array_var($fields, $position['fax_number']);
		if (isset($checked['notes']) && $checked['notes']) $contact_data['notes'] = array_var($fields, $position['notes']);
		$contact_data['timezone'] = logged_user()->getTimezone();
		
		return $contact_data;
	}
	
	
	function build_csv_from_company(Contact $company, $checked) {
		$str = '';
		
		if (isset($checked['first_name']) && $checked['first_name'] == 'checked') $str .= self::build_csv_field($company->getObjectName());
		
		$address = $company->getAddress('work', true);
		if ($address){
			if (isset($checked['address']) && $checked['address'] == 'checked') $str .= self::build_csv_field($address->getStreet());
			if (isset($checked['city']) && $checked['city'] == 'checked') $str .= self::build_csv_field($address->getCity());
			if (isset($checked['state']) && $checked['state'] == 'checked') $str .= self::build_csv_field($address->getState());
			if (isset($checked['zipcode']) && $checked['zipcode'] == 'checked') $str .= self::build_csv_field($address->getZipcode());
			if (isset($checked['country']) && $checked['country'] == 'checked') $str .= self::build_csv_field($address->getCountryName());
		}
		if (isset($checked['phone_number']) && $checked['phone_number'] == 'checked') $str .= self::build_csv_field($company->getPhoneNumber('work', true));
		if (isset($checked['fax_number']) && $checked['fax_number'] == 'checked') $str .= self::build_csv_field($company->getPhoneNumber('fax', true));
		if (isset($checked['email']) && $checked['email'] == 'checked') $str .= self::build_csv_field($company->getEmailAddress());
		if (isset($checked['homepage']) && $checked['homepage'] == 'checked') $str .= self::build_csv_field($company->getWebpageUrl('work'));
		
		$str = str_replace(array(chr(13).chr(10), chr(13), chr(10)), ' ', $str); //remove line breaks
		
		return $str;
	}
	
	
	/**
	 * Edit company
	 *
	 * @param void
	 * @return null
	 */
	function edit_company() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$this->setTemplate('add_company');

		$company = Contacts::findById(get_id());
		
		if(!($company instanceof Contact)) {
			flash_error(lang('client dnx'));
			ajx_current("empty");
			return;
		} // if
		
		if(!$company->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$company_data = array_var($_POST, 'company');
		
		if(!is_array($company_data)) {
			$address = $company->getAddress('work');
			$street = "";
			$city = "";
			$state = "";
			$zipcode = "";
			if($address){
				$street = $address->getStreet();
				$city = $address->getCity();
				$state = $address->getState();
				$zipcode = $address->getZipCode();
				$country = $address->getCountry();
			}
			
			$company_data = array(
                          'first_name' => $company->getFirstName(),
                          'timezone' => $company->getTimezone(),
                          'email' => $company->getEmailAddress(),
			  'phone_number' => $company->getPhoneNumber('work',true),
			  'fax_number' => $company->getPhoneNumber('fax',true),
                          'homepage' => $company->getWebpageURL('work'),
                          'address' => $street,
                          'city' => $city,
                          'state' => $state,
                          'zipcode' => $zipcode,
			  'country'=> $country,
			); // array
		} // if

		tpl_assign('company', $company);
		tpl_assign('company_data', $company_data);

		if(is_array(array_var($_POST, 'company'))) {
			try {
				Contacts::validate($company_data, $_REQUEST['id']);
				DB::beginWork();
				
				$company->setFromAttributes($company_data);
				
				$mainPone = $company->getPhone('work', true);
				if($mainPone){
						$mainPone->editNumber($company_data['phone_number']);
				}else{
					if($company_data['phone_number'] != "") $company->addPhone($company_data['phone_number'], 'work', true);
				}
				
				$faxPhone = $company->getPhone('fax', true);
				if($faxPhone){
						$faxPhone->editNumber($company_data['fax_number']);
				}else{
					if($company_data['fax_number'] != "") $company->addPhone($company_data['fax_number'], 'fax', true);
				}
				
				$mail = $company->getEmail();
				if($mail){
						$mail->editEmailAddress($company_data['email']);
				}else{ 
						if($company_data['email'] != "") $company->addEmail($company_data['email'], 'work' , true);
				}
				
				$homepage = $company->getWebpage('work');
				if($homepage){
						$homepage->editWebpageURL($company_data['homepage']);
				}else{
						if($company_data['homepage'] != "") $company->addWebpage($company_data['homepage'], 'work');
				}
				
				$address = $company->getAddress('work');
				if($address){
						$address->edit($company_data['address'], $company_data['city'], $company_data['state'], $company_data['country'], $company_data['zipcode'],2,1);
				}else{$a = 1;
						if($company_data['address'] != "" || $company_data['city'] != "" || $company_data['state'] != "" || $company_data['country'] != "" || $company_data['zipcode'] != "")
							$company->addAddress($company_data['address'], $company_data['city'], $company_data['state'], $company_data['country'], $company_data['zipcode'], 'work', true);
				}	
				
				
				
				
				$company->setObjectName();
				$company->save();
				$member_ids = json_decode(array_var($_POST, 'members'));
				
				$object_controller = new ObjectController();
				if (!$company->isOwnerCompany()){
					$object_controller->add_to_members($company, $member_ids);
			    	$object_controller->link_to_new_object($company);
					$object_controller->add_subscribers($company);
					$object_controller->add_custom_properties($company);
				}
				
				ApplicationLogs::createLog($company, ApplicationLogs::ACTION_EDIT);
				DB::commit();

				flash_success(lang('success edit client', $company->getObjectName()));
				ajx_current("back");

			} catch(Exception $e) {
				DB::rollback();
				ajx_current("empty");
				flash_error($e->getMessage());
			} // try
		}
	} // edit_company

	
	/**
	 * View specific company
	 *
	 * @param void
	 * @return null
	 */
	function view_company() {
		$this->redirectTo('contact','company_card', array('id' => get_id()));
	} // view_company
	

	/**
	 * Add company
	 *
	 * @param void
	 * @return null
	 */
	function add_company() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$notAllowedMember = '';				
		if(!Contact::canAdd(logged_user(),active_context(),$notAllowedMember)) {
			if (str_starts_with($notAllowedMember, '-- req dim --')) flash_error(lang('must choose at least one member of', str_replace_first('-- req dim --', '', $notAllowedMember, $in)));
			else flash_error(lang('no context permissions to add',lang("contacts"), $notAllowedMember));
			ajx_current("empty");
			return;
		} // if
		
		$company = new Contact();
		$company->setIsCompany(1);
		$company_data = array_var($_POST, 'company');

		if(!is_array($company_data)) {
			$company_data = array(
				'timezone' => logged_user()->getTimezone(),
			); // array
		} // if
		tpl_assign('company', $company);
		tpl_assign('company_data', $company_data);
	
		if (is_array(array_var($_POST, 'company'))) {
                    
			$company->setFromAttributes($company_data);
			$company->setObjectName();

	

			try {
				Contacts::validate($company_data); 
				DB::beginWork();
				$company->save();
				if($company_data['address'] != "")
				$company->addAddress($company_data['address'], $company_data['city'], $company_data['state'], $company_data['country'], $company_data['zipcode'], 'work', true);
				if($company_data['phone_number'] != "") $company->addPhone($company_data['phone_number'], 'work', true);
				if($company_data['fax_number'] != "") $company->addPhone($company_data['fax_number'], 'fax', true);
				if($company_data['homepage'] != "") $company->addWebpage($company_data['homepage'], 'work');
				if($company_data['email'] != "") $company->addEmail($company_data['email'], 'work' , true);
				
				$object_controller = new ObjectController();
				$object_controller->add_subscribers($company);

				$member_ids = json_decode(array_var($_POST, 'members'));
				if (!is_null($member_ids))
					$object_controller->add_to_members($company, $member_ids);
                                $object_controller->link_to_new_object($company);
				$object_controller->add_custom_properties($company);
				
				ApplicationLogs::createLog($company, ApplicationLogs::ACTION_ADD);

				DB::commit();

				flash_success(lang('success add client', $company->getObjectName()));
				evt_add("company added", array("id" => $company->getObjectId(), "name" => $company->getObjectName()));
				ajx_current("back");
			} catch(Exception $e) {
				DB::rollback();
				ajx_current("empty");
				flash_error($e->getMessage());
			} // try
		} // if
	} // add_company

	
	/**
	 * Show and process edit company logo form
	 *
	 * @param void
	 * @return null
	 */
	function edit_logo() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$company = Contacts::findById(get_id());
		if(!($company instanceof Contact)) {
			flash_error(lang('company dnx'));
			ajx_current("empty");
			return;
		} // if
		if (!$company->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if)

		tpl_assign('company', $company);

		$logo = array_var($_FILES, 'new_logo');
		if(is_array($logo)) {
			try {
				if(!isset($logo['name']) || !isset($logo['type']) || !isset($logo['size']) || !isset($logo['tmp_name']) || !is_readable($logo['tmp_name'])) {
					throw new InvalidUploadError($logo, lang('error upload file'));
				} // if

				$valid_types = array('image/jpg', 'image/jpeg', 'image/pjpeg', 'image/gif', 'image/png','image/x-png');
				$max_width   = config_option('max_logo_width', 50);
				$max_height  = config_option('max_logo_height', 50);

				if(!in_array($logo['type'], $valid_types) || !($image = getimagesize($logo['tmp_name']))) {
					throw new InvalidUploadError($logo, lang('invalid upload type', 'JPG, GIF, PNG'));
				} // if

				$old_file = $company->getLogoPath();

				DB::beginWork();

				if(!$company->setLogo($logo['tmp_name'], $logo['type'], $max_width, $max_height, true)) {
					throw new InvalidUploadError($avatar, lang('error edit company logo'));
				} // if

				evt_add("logo changed");
				ApplicationLogs::createLog($company, ApplicationLogs::ACTION_EDIT);

				DB::commit();

				if(is_file($old_file)) {
					@unlink($old_file);
				} // uf

				flash_success(lang('success edit company logo'));
				ajx_current("back");
			} catch(Exception $e) {
				ajx_current("empty");
				DB::rollback();
				flash_error($e->getMessage());
			} // try
		} // if
	} // edit_logo

	
	/**
	 * Delete company logo
	 *
	 * @param void
	 * @return null
	 */
	function delete_logo() {
		if(!logged_user()->isAdministrator()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$company = Contacts::findById(get_id());
		if(!($company instanceof Contact)) {
			flash_error(lang('company dnx'));
			ajx_current("empty");
			return;
		} // if

		try {
			DB::beginWork();
			$company->deleteLogo();
			$company->save();
			ApplicationLogs::createLog($company, ApplicationLogs::ACTION_EDIT);
			DB::commit();

			flash_success(lang('success delete company logo'));
			ajx_current("back");
		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error delete company logo'));
			ajx_current("empty");
		} // try
	} // delete_logo
	
	
	function get_company_data(){
		ajx_current("empty");
		$id = array_var($_GET, 'id');
		$company = Contacts::findById($id);
		
		if ($company){
			$address = $company->getAddress('work');
			$street = "";
			$city = "";
			$state = "";
			$zipcode = "";
			$country = "";
			if($address){
				$street = $address->getStreet();
				$city = $address->getCity();
				$state = $address->getState();
				$zipcode = $address->getZipCode();
				$country = $address->getCountry();
			}
			ajx_extra_data(array(
				"id" => $company->getObjectId(),
				"address" => $street,
				"state" => $state,
				"city" => $city,
				"country" => $country,
				"zipcode" => $zipcode,
				"webpage" => $company->getWebpageURL('work'),
				"phoneNumber" => $company->getPhoneNumber('work', true),
				"faxNumber" => $company->getPhoneNumber('fax', true)
			));
		} else {
			ajx_extra_data(array(
				"id" => 0
			));
		}
	}
	
	private function createUserFromContactForm ($user, $contactId, $email) {
		$createUser = false ;
                $createPass = false ; 
                
		if ( array_var ($user, 'create-user')) {
			$createUser = true ;
                        if ( array_var ($user, 'create-password')) {
                            $createPass = true ;    
                            $password =  array_var($user, 'password') ;
                            $password_a =  array_var($user, 'password_a') ;                            
                        }
			$type =  array_var($user, 'type') ;
			$username =  array_var($user, 'username') ;
		}
		if ($createUser){			                       
        	if ($createPass){
            	$userData = array(  'contact_id' => $contactId,
                                    'username' => $username,
                                    'email' => $email,
                                    'password' => $password,
                                    'password_a' => $password_a,
                                    'type' => $type,
                                    'password_generator' => 'specify',
                                    'send_email_notification' => true);
            }else{
                $userData = array(	'contact_id' => $contactId,
									'username' => $username,
									'email' => $email,
									'type' => $type,
									'password_generator' => 'link',
									'send_email_notification' => true);
            }
			$valid =  Contacts::validateUser($contactId);
			create_user($userData, '');
		}
		
	}

	/**
	 * @author Ignacio Vazquez <elpepe.uy at gmail dot com>
	 * Handle quick add submit
	 */
	function quick_add() {
		if (array_var($_GET, 'current') == 'overview-panel') {
			ajx_current("reload");	
		}else {
			ajx_current("empty");
		}
		
		//---------- REQUEST PARAMS -------------- 
		//		$_POST = Array (
		//			[member] => Array (
		//				[name] => pepe 333
		//				[dimension_id] => 1
		//				[parent_member_id] => 0
		//				[dimension_id] => 19
		//			)
		//			[contact] => Array (
		//				[email] => slkdjflksjdflksdf@kldsjflkdf.com
		//				[user] => Array (
		//					[create-user]=>on
		//					[type] => 25
		//					[first_name] =>  
		// 					[surname] => 						
		//		)
		//----------------------------------------
		
		//alert_r($_POST['contact']);
		// Init variables

		$max_users = config_option('max_users');
		if ($max_users && (Contacts::count() >= $max_users)) {
			flash_error(lang('maximum number of users reached error'));
			ajx_current("empty");
			return;
		}

		if (!can_manage_security(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		
		$email = trim(array_var(array_var($_POST, 'contact'),'email')) ;
		$member = array_var($_POST, 'member');
		$name = array_var($member, 'name');
		$firstName = trim(array_var(array_var($_POST, 'contact'),'first_name'));
		$surname = trim(array_var(array_var($_POST, 'contact'),'surname'));
		$parentMemberId = array_var($member, 'parent_member_id');
		$objectType = ObjectTypes::findById(array_var($member, 'object_type_id'))->getName(); // 'person', 'company'
		$dimensionId =  array_var($member, 'dimension_id'); 		
		$company = array_var(array_var(array_var($_POST, 'contact'),'user'),'company_id');
                
		// Create new instance of Contact and set the basic fields
		$contact = new Contact();
		$contact->setObjectName($name);
		if ($firstName) {
			$contact->setFirstName($firstName);
		}else{
			$contact->setFirstName($name);	
		}
		
		if ($surname) {
			$contact->setSurname($surname);
		}
                $contact->setCompanyId($company);
		$contact->setIsCompany($objectType == "company");
		if ($parentMemberId){
			if ( $companyId = Members::findById($parentMemberId)->getObjectId()) {
				$contact->setCompanyId($companyId);
			}
		}
		
		
		// Save Contact
		try {
			DB::beginWork();
			$contact->save();
			if ($email && is_valid_email($email)) {
				if (!Contacts::validateUniqueEmail($email)) {
					DB::rollback();
					flash_error(lang("email address must be unique"));
					return false;
				}else{
					if (!array_var (array_var(array_var($_POST, 'contact'),'user'), 'create-user')) {
						$contact->addEmail($email, 'personal', true);
					}
					flash_success(lang("success add contact", $contact->getObjectName()));
				}
			}
			
			// User settings
			$user = array_var(array_var($_POST, 'contact'),'user');
			$user['username'] = str_replace(" ","",strtolower($name)) ;
			$this->createUserFromContactForm($user, $contact->getId(), $email);
			
			// Reload contact again due to 'createUserFromContactForm' changes
			Hook::fire("after_contact_quick_add", Contacts::instance()->findById($contact->getId()), $ret);
			
			DB::commit();
			
		}catch (Exception $e){
			DB::rollback();
			flash_error($e->getMessage());
		}
		
		
		// Reload 
		evt_add("reload dimension tree", $dimensionId);
	}
        
        function quick_config_filter_activity(){     
            $this->setLayout('empty');
            $submited_values = array_var($_POST, 'filter');
            $members = array_var($_GET, 'members');
            tpl_assign('members', array_var($_GET, 'members'));
            
            $filters_default = ContactConfigOptions::getFilterActivity();
            
            $filters = ContactConfigOptionValues::getFilterActivityMember($filters_default->getId(),$members);
            if(!$filters){
                $filters = ContactConfigOptions::getFilterActivity();
                $filter_value = $filters->getDefaultValue();
                tpl_assign('id', $filters->getId());
            }else{
                $filter_value = $filters->getValue();
            }
            $filters_def = explode(",",$filter_value);            
            if($filters_def[0] == 1){
                tpl_assign('checked_dimension_yes', 'checked="checked"');
            }else{
                tpl_assign('checked_dimension_no', 'checked="checked"');
            }
            if($filters_def[1] == 1){
                tpl_assign('checked_timeslot_yes', 'checked="checked"');
            }else{
                tpl_assign('checked_timeslot_no', 'checked="checked"');
            }
            tpl_assign('show', $filters_def[2]);            
            if($filters_def[3] == 1){
                tpl_assign('checked_view_downloads_yes', 'checked="checked"');
            }else{
                tpl_assign('checked_view_downloads_no', 'checked="checked"');
            }            
            if(is_array($submited_values)) {
                    $members = array_var($submited_values,"members");
                    $filters_default = ContactConfigOptions::getFilterActivity();
                    $filters = ContactConfigOptionValues::getFilterActivityMember($filters_default->getId(),$members);  
                    // update cache if available
                    if (GlobalCache::isAvailable()) {
                            GlobalCache::delete('user_config_option_'.logged_user()->getId().'_'.$filters_default->getName()."_".$members);
                    }                 
                    
                    $new_value = array_var($submited_values,"dimension") . "," . array_var($submited_values,"timeslot") . "," . array_var($submited_values,"show"). "," . array_var($submited_values,"view_downloads");
                    
                    if(!$filters){
                        $filter_opt = new ContactConfigOptionValue();
                        $filter_opt->setOptionId($filters_default->getId());
                        $filter_opt->setContactId(logged_user()->getId());
                        $filter_opt->setValue($new_value);
                        $filter_opt->setMemberId($members);
                        $filter_opt->save();
                    }else{
                        $filters->setValue($new_value);
                        $filters->save();
                    }
                    
                    evt_add("user preference changed", array('name' => $filters_default->getName()."_".$members, 'value' => $new_value));
                    redirect_to("index.php?c=dashboard&a=main_dashboard");
            }
            
        }
} 