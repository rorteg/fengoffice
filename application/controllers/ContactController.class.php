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
	 * Creates a system user, receiving a Contact id
	 *
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
		
		$this->redirectTo('user','add',array('company_id' => $contact->getCompanyId(), 'contact_id' => $contact->getId()));
		
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
		$tag = array_var($_GET,'tag');
		$action = array_var($_GET,'action');
		$attributes = array(
			"ids" => explode(',', array_var($_GET, 'ids')),
			"types" => explode(',', array_var($_GET, 'types')),
			"tag" => array_var($_GET, 'tagTag'),
			"accountId" => array_var($_GET, 'account_id'),
			"viewType" => array_var($_GET, 'view_type'),
			"moveTo" => array_var($_GET, 'moveTo'),
			"mantainWs" => array_var($_GET, 'mantainWs'),
			"tagTag" => array_var($_GET, 'tagTag'),
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
		
		// Get all emails and companies to contacts
		$project = active_project();
		/*$contacts = $this->getContacts($tag, $attributes, $project);
		$companies = array();
		$companies = $this->getCompanies($tag, $attributes, $project);
		$union = $this->addContactsAndCompanies($contacts, $companies);*/
		
		$type = null;
		if ($attributes['viewType'] == 'contacts') {
			$type = 'Contacts';
		} else if ($attributes['viewType'] == 'companies') {
			$type = 'Companies';
		}

		$count = $this->countContactObjects($tag, $type, $project);
		if ($start > $count) {
			$start = 0;
			$page = 1;
		}

		if ($count > 0) {
			$union = $this->getContactObjects($page, $limit, $tag, $order, $order_dir, $type, $project);
		} else {
			$union = array();
		}
		ProjectDataObjects::populateData($union);

		// Prepare response object
		$object = $this->newPrepareObject($union, $count, $start, $attributes);
		ajx_extra_data($object);
    	tpl_assign("listing", $object);

	}
	
	
	private static function getContactQueries($project = null, $tag = null, $count = false, $order = null, $archived = false) {
		switch ($order){
			case 'updatedOn':
				$order_crit_companies = 'updated_on';
				$order_crit_contacts = 'updated_on';
				break;
			case 'createdOn':
				$order_crit_companies = 'created_on';
				$order_crit_contacts = 'created_on';
				break;
			case 'email':
			case 'email2':
			case 'email3':
				$order_crit_contacts = $order;
				$order_crit_companies = $order == 'email' ? 'email' : "' '";
				break;
			default:
				$order_crit_contacts = "TRIM(CONCAT(' ', `lastname`, `firstname`, `middlename`))";
				$order_crit_companies = 'name';
				break;
		}
		if ($project instanceof Project) {
    		$proj_ids = $project->getAllSubWorkspacesQuery(!$archived);
    		$proj_cond_companies = " AND " . Companies::getWorkspaceString($proj_ids);
			$proj_cond_contacts = " AND " . Contacts::getWorkspaceString($proj_ids);
    	} else {
    		$proj_cond_companies = "";
			$proj_cond_contacts = "";
    	}
    	
		if (isset($tag) && $tag && $tag!='') {
    		$tag_str = " AND EXISTS (SELECT * FROM `" . TABLE_PREFIX . "tags` `t` WHERE `tag` = ".DB::escape($tag)." AND `co`.`id` = `t`.`rel_object_id` AND `t`.`rel_object_manager` = `object_manager_value`) ";
    	} else {
    		$tag_str= ' ';
    	}
    	$res = array();
    	
    	if ($archived) $archived_cond = "AND `archived_by_id` <> 0";
		else $archived_cond = "AND `archived_by_id` = 0";
    	
		$permissions = ' AND ( ' . permissions_sql_for_listings(Companies::instance(), ACCESS_LEVEL_READ, logged_user(), '`project_id`', '`co`') .')';
		$res['Companies'] = "SELECT  $order_crit_companies AS `order_value`, 'Companies' AS `object_manager_value`, `id` as `oid` FROM `" . 
					TABLE_PREFIX . "companies` `co` WHERE `trashed_by_id` = 0 $archived_cond " .$proj_cond_companies . str_replace('= `object_manager_value`', "= 'Companies'", $tag_str) . $permissions;
					
		$permissions = ' AND ( ' . permissions_sql_for_listings(Contacts::instance(), ACCESS_LEVEL_READ, logged_user(), '`project_id`', '`co`') . ')';
		$res['Contacts'] = "SELECT $order_crit_contacts AS `order_value`, 'Contacts' AS `object_manager_value`, `id` AS `oid` FROM `" . 
				TABLE_PREFIX . "contacts` `co` WHERE `trashed_by_id` = 0 $archived_cond $proj_cond_contacts " . str_replace('= `object_manager_value`', "= 'Contacts'", $tag_str) . $permissions;

		if ($count) {
			foreach ($res as $p => $q) {
				$res[$p] ="SELECT count(*) AS `quantity`, '$p' AS `objectName` FROM ( $q ) `table_alias`";
			}
		}
		return $res;
	}
	
	function countContactObjects($tag = null, $type = null, $project = null) {
    	$queries = $this->getContactQueries($project, $tag, true);
		if(isset($type) && $type){
			$query = $queries[$type];
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

	private function getContactObjects($page, $objects_per_page, $tag=null, $order=null, $order_dir=null, $type = null, $project = null){

    	$queries = $this->getContactQueries($project, $tag, false, $order);
		if (!$order_dir){
			switch ($order){
				case 'name': $order_dir = 'ASC'; break;
				default: $order_dir = 'DESC';
			}
		}
		if (isset($type) && $type) {
			$query = $queries[$type];
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
		$query .= " ORDER BY order_value $order_dir ";
		if ($page && $objects_per_page) {
			$start=($page-1) * $objects_per_page ;
			$query .=  " LIMIT " . $start . "," . $objects_per_page. " ";
		} elseif($objects_per_page) {
			$query .= " LIMIT " . $objects_per_page;
		}

    	$res = DB::execute($query);
    	$objects = array();
    	if (!$res)  return $objects;
    	$rows = $res->fetchAll();
    	if (!$rows)  return $objects;
    	$i = 1;

    	foreach ($rows as $row) {
    		$manager= $row['object_manager_value'];
    		$id = $row['oid'];
    		if ($id && $manager) {
    			$obj = get_object_by_manager_and_id($id,$manager);    			
    			if ($obj->canView(logged_user())) {
    				$objects[] = $obj;
    			}
    		} //if($id && $manager)
    	}//foreach

    	return $objects;
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
					
					switch ($type){
						case "contact":
							$contact = Contacts::findById($id);
							if (isset($contact) && $contact->canDelete(logged_user())){
								try{
									DB::beginWork();
									$contact->trash();
									DB::commit();
									ApplicationLogs::createLog($contact, $contact->getWorkspaces(), ApplicationLogs::ACTION_TRASH);
									$succ++;
								} catch(Exception $e){
									DB::rollback();
									$err++;
								}
							} else {
								$err++;
							}
							break;
							
						case "company":
							$company = Companies::findById($id);
							if (isset($company)) {
								if ($company->canDelete(logged_user())) {
									try{
										DB::beginWork();
										$company->trash();									
										DB::commit();
										ApplicationLogs::createLog($company, $company->getWorkspaces(), ApplicationLogs::ACTION_TRASH);
										$succ++;
									} catch(Exception $e){
										DB::rollback();
										$err++;
									}
								} else {
									$err++;
								}
							};
							break;
							
						default:
							$err++;
							break;
					}; // switch
				}; // for
				if ($err > 0) {
					$resultCode = 2;
					$resultMessage = lang("error delete objects", $err) . "" . ($succ > 0 ? lang("success delete objects", $succ) : "");					
				} else {
					$resultMessage = lang("success delete objects", $succ);
				}
				break;
						
			case "tag":
				$tag = $attributes["tag"];
				for($i = 0; $i < count($attributes["ids"]); $i++){
					$id = $attributes["ids"][$i];
					$type = $attributes["types"][$i];
					switch ($type){
						case "contact":
							$contact = Contacts::findById($id);
							if (isset($contact) && $contact->canEdit(logged_user())){
								Tags::addObjectTag($tag, $contact);
								ApplicationLogs::createLog($contact, $contact->getWorkspaces(), ApplicationLogs::ACTION_TAG,false,null,true,$tag);
								$resultMessage = lang("success tag objects", '');
							};
							break;

						case "company":
							$company = Companies::findById($id);
							if (isset($company) && $company->canEdit(logged_user())){
								Tags::addObjectTag($tag, $company);
								ApplicationLogs::createLog($company, $company->getWorkspaces(), ApplicationLogs::ACTION_TAG,false,null,true,$tag);
								$resultMessage = lang("success tag objects", '');
							};
							break;

						default:
							$resultMessage = lang("unimplemented type" .": '" . $type . "'");// if
							$resultCode = 2;
							break;
					}; // switch
				}; // for
				break;
				
			case "untag":
				for($i = 0; $i < count($attributes["ids"]); $i++){
					$id = $attributes["ids"][$i];
					$type = $attributes["types"][$i];
					switch ($type){
						case "contact":
							$contact = Contacts::findById($id);
							if (isset($contact) && $contact->canEdit(logged_user())){
								$tag = $attributes['tagTag'];
								if ($tag != ''){
									$contact->deleteTag($tag);
								}else{
									$contact->clearTags();
								}								
								ApplicationLogs::createLog($contact, $contact->getWorkspaces(), ApplicationLogs::ACTION_EDIT, false, null, true);
								$resultMessage = lang("success untag objects", '');
							};
							break;

						case "company":
							$company = Companies::findById($id);
							if (isset($company) && $company->canEdit(logged_user())){
								$company->clearTags();
								ApplicationLogs::createLog($company, $company->getWorkspaces(), ApplicationLogs::ACTION_EDIT, false, null, true);
								$resultMessage = lang("success tag objects", '');
							};
							break;

						default:
							$resultMessage = lang("unimplemented type" .": '" . $type . "'");// if
							$resultCode = 2;
							break;
					}; // switch
				}; // for
				break;
				
			case "move":
				$wsid = $attributes["moveTo"];
				$destination = Projects::findById($wsid);
				if (!$destination instanceof Project) {
					$resultMessage = lang('project dnx');
					$resultCode = 1;
				} else {
					$count = 0;
					for($i = 0; $i < count($attributes["ids"]); $i++){
						$id = $attributes["ids"][$i];
						$type = $attributes["types"][$i];
						switch ($type){
							case "contact":
								if (!can_add(logged_user(), $destination, 'Contacts')) continue;
								$contact = Contacts::findById($id);
								if ($contact instanceof Contact && $contact->canEdit(logged_user())){
									if (!$attributes["mantainWs"]) {
										$removed = "";
										$ws = $contact->getWorkspaces(null, $destination);
										foreach ($ws as $w) {
											if (can_add(logged_user(), $w, 'Contacts')) {
												$contact->removeFromWorkspace($w);
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
									$contact->addToWorkspace($destination);
									ApplicationLogs::createLog($contact, $contact->getWorkspaces(), $log_action, false, null, true, $log_data);
									$count++;
								};
								break;
								
							case "company":
								if (!can_add(logged_user(), $destination, 'Companies')) continue;
								$company = Companies::findById($id);
								if ($company instanceof Company && $company->canEdit(logged_user())){
									if (!$attributes["mantainWs"]) {
										$removed = "";
										$ws = $company->getWorkspaces($destination);
										foreach ($ws as $w) {
											if (can_add(logged_user(), $w, 'Companies')) {
												$company->removeFromWorkspace($w);
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
									$company->addToWorkspace($destination);
									ApplicationLogs::createLog($company, $company->getWorkspaces(), $log_action, false, null, true, $log_data);
									$count++;
								};
								break;
	
							default:
								$resultMessage = lang("Unimplemented type: '" . $type . "'");// if
								$resultCode = 2;
								break;
						}; // switch
					}; // for
					$resultMessage = lang("success move objects", $count);
					$resultCode = 0;
				}
				break;
			case "archive":
				$succ = 0; $err = 0;
				for($i = 0; $i < count($attributes["ids"]); $i++){
					$id = $attributes["ids"][$i];
					$type = $attributes["types"][$i];
					
					switch ($type){
						case "contact":
							$contact = Contacts::findById($id);
							if (isset($contact) && $contact->canEdit(logged_user())){
								try{
									DB::beginWork();
									$contact->archive();
									DB::commit();
									ApplicationLogs::createLog($contact, $contact->getWorkspaces(), ApplicationLogs::ACTION_ARCHIVE);
									$succ++;
								} catch(Exception $e){
									DB::rollback();
									$err++;
								}
							} else {
								$err++;
							}
							break;
							
						case "company":
							$company = Companies::findById($id);
							if (isset($company)) {
								if ($company->canEdit(logged_user())) {
									try{
										DB::beginWork();
										$company->archive();									
										DB::commit();
										ApplicationLogs::createLog($company, $company->getWorkspaces(), ApplicationLogs::ACTION_ARCHIVE);
										$succ++;
									} catch(Exception $e){
										DB::rollback();
										$err++;
									}
								} else {
									$err++;
								}
							};
							break;
							
						default:
							$err++;
							break;
					}; // switch
				}; // for
				if ($err > 0) {
					$resultCode = 2;
					$resultMessage = lang("error archive objects", $err) . "<br />" . ($succ > 0 ? lang("success archive objects", $succ) : "");
				} else {
					$resultMessage = lang("success archive objects", $succ);
				}
				break;
			default:
				$resultMessage = lang("unimplemented action" . ": '" . $action . "'");// if 
				$resultCode = 2;	
				break;		
		} // switch
		return array("errorMessage" => $resultMessage, "errorCode" => $resultCode);
	}
	
	function addProjectContact($id, $destination, $mantainWs = true, $role = "") {
		$contact = Contacts::findById($id);
		$pc = ProjectContacts::getRole($contact, $destination);
		if (!ProjectContact::canAdd(logged_user(), $destination)) return 0;
		
		if (!$pc instanceof ProjectContact) {
			if (!$mantainWs) {
				$removed = "";
				$old_roles = $contact->getRoles();
				foreach ($old_roles as $role) {
					$role->delete();
					$removed .= $role->getProjectId() . ",";
				}
				$removed = substr($removed, 0, -1);
				$log_action = ApplicationLogs::ACTION_MOVE;
				$log_data = ($removed == "" ? "" : "from:$removed;") . "to:".$destination->getId();
			} else {
				$log_action = ApplicationLogs::ACTION_COPY;
				$log_data = "to:".$destination->getId();
			}
			$pc = new ProjectContact();
			$pc->setProjectId($destination->getId());
			$pc->setContactId($contact->getId());
			$pc->setRole($role);
			$pc->save();
			ApplicationLogs::createLog($contact, $contact->getWorkspaces(), $log_action, false, null, true, $log_data);
		}
		return 1;		
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
					
				if ($c instanceof Contact){						
					$roleName = "";
					$roleTags = "";
					$project = active_project();
					if ($project) {
						$role = $c->getRole($project);
						if ($role instanceof ProjectContact) {
							$roleName = $role->getRole();
						}
					}
					$company = $c->getCompany();
					$companyName = '';
					if (!is_null($company))
					$companyName= $company->getName();
					$object["contacts"][] = array(
						"id" => $i,
						"ix" => $i,
						"object_id" => $c->getId(),
						"type" => 'contact',
						"wsIds" => $c->getUserWorkspacesIdsCSV(logged_user()),
    					"workspaceColors" => $c->getUserWorkspaceColorsCSV(logged_user()),
						"name" => $c->getReverseDisplayName(),
						"email" => $c->getEmail(),
						"companyId" => $c->getCompanyId(),
						"companyName" => $companyName,
						"website" => $c->getHWebPage() ? cleanUrl($c->getHWebPage(), false) : '',
						"jobTitle" => $c->getJobTitle(),
				    	"role" => $roleName,
						"tags" => project_object_tags($c),
						"department" => $c->getDepartment(),
						"email2" => $c->getEmail2(),
						"email3" => $c->getEmail3(),
						"workWebsite" => $c->getWWebPage() ? cleanUrl($c->getWWebPage(), false) : '',
						"workAddress" => $c->getFullWorkAddress(),
						"workPhone1" => $c->getWPhoneNumber(),
						"workPhone2" => $c->getWPhoneNumber2(),
						"homeWebsite" => $c->getHWebPage() ? cleanUrl($c->getHWebPage(), false) : '',
						"homeAddress" => $c->getFullHomeAddress(),
						"homePhone1" => $c->getHPhoneNumber(),
						"homePhone2" => $c->getHPhoneNumber2(),
						"mobilePhone" =>$c->getHMobileNumber(),
						"createdOn" => $c->getCreatedOn() instanceof DateTimeValue ? ($c->getCreatedOn()->isToday() ? format_time($c->getCreatedOn()) : format_datetime($c->getCreatedOn())) : '',
						"createdOn_today" => $c->getCreatedOn() instanceof DateTimeValue ? $c->getCreatedOn()->isToday() : 0,
						"createdBy" => $c->getCreatedByDisplayName(),
						"createdById" => $c->getCreatedById(),
						"updatedOn" => $c->getUpdatedOn() instanceof DateTimeValue ? ($c->getUpdatedOn()->isToday() ? format_time($c->getUpdatedOn()) : format_datetime($c->getUpdatedOn())) : '',
						"updatedOn_today" => $c->getUpdatedOn() instanceof DateTimeValue ? $c->getUpdatedOn()->isToday() : 0,
						"updatedBy" => $c->getUpdatedByDisplayName(),
						"updatedById" => $c->getUpdatedById()
					);
				} else if ($c instanceof Company ){					
					$roleName = "";
					$roleTags = "";
					if (!is_null($c))
					$companyName= $c->getName();
					$object["contacts"][] = array(
						"id" => $i,
						"ix" => $i,
						"object_id" => $c->getId(),
						"type" => 'company',
						"wsIds" => $c->getUserWorkspacesIdsCSV(logged_user()),
    					"workspaceColors" => $c->getUserWorkspaceColorsCSV(logged_user()),
						'name' => $c->getName(),
						'email' => $c->getEmail(),
						'website' => $c->getHomepage(),
						'workPhone1' => $c->getPhoneNumber(),
          				'workPhone2' => $c->getFaxNumber(),
          				'workAddress' => $c->getAddress() . ' - ' . $c->getAddress2(),
						"companyId" => $c->getId(),
						"companyName" => $c->getName(),
						"jobTitle" => '',
				    	"role" => lang('company'),
						"tags" => project_object_tags($c),
						"department" => lang('company'),
						"email2" => '',
						"email3" => '',
						"workWebsite" => $c->getHomepage(),
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
						"updatedById" => $c->getUpdatedById()
					);
				}
    		}
		}
		return $object;
	}

	/**
	 * View single contact
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function view() {
		$this->card();
	} // view

	/**
	 * View single contact
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function card() {
		$contact = Contacts::findById(get_id());
		if(!$contact || !$contact->canView(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
		$roles = ProjectContacts::getRolesByContact($contact);
		if (isset($roles))
		{
			foreach ($roles as $role)
			{
				$tags[$role->getProjectId()] = $role->getTagNames();
			}
		}

		tpl_assign('contact', $contact);
		if(($uid = $contact->getUserId()) && ($usr = Users::findById($uid)))
			tpl_assign('user', $usr);
		if (isset($roles))
		tpl_assign('roles',$roles);
		if (isset($tags))
		tpl_assign('tags',$tags);
		ajx_extra_data(array("title" => $contact->getDisplayName(), 'icon'=>'ico-contact'));
		ajx_set_no_toolbar(true);
		
		ApplicationReadLogs::createLog($contact, $contact->getWorkspaces(), ApplicationReadLogs::ACTION_READ);
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
		if (active_project() instanceof Project) {
			tpl_assign('isAddProject',true);
		}
		$this->setTemplate('edit_contact');

		if(active_project() instanceof Project && !Contact::canAdd(logged_user(),active_project())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

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
		
		tpl_assign('contact', $contact);
		tpl_assign('contact_data', $contact_data);
		tpl_assign('im_types', $im_types);

		if(is_array(array_var($_POST, 'contact'))) {
			ajx_current("empty");
			try {
				DB::beginWork();
				
				$newCompany = false;
				if (array_var($contact_data, 'isNewCompany') == 'true' && is_array(array_var($_POST, 'company'))){
					$company_data = array_var($_POST, 'company');
					$company = new Company();
					$company->setFromAttributes($company_data);
					$company->setClientOfId(1);
					
					$company->save();
					ApplicationLogs::createLog($company, null, ApplicationLogs::ACTION_ADD);
					$newCompany = true;
					if(active_project() instanceof Project && $company->canAdd(logged_user(), active_project())) {
						$company->addToWorkspace(active_project());
					}else{
						$company->addToWorkspace(logged_user()->getPersonalProject());
					}
				}
				
				$contact_data['o_birthday'] = getDateValue($contact_data["o_birthday_value"]);
				
				$contact->setFromAttributes($contact_data);

				if($newCompany)
					$contact->setCompanyId($company->getId());
				$contact->setIsPrivate(false);
				$contact->save();
				$contact->setTagsFromCSV(array_var($contact_data, 'tags'));
				
				//link it!
			    $object_controller = new ObjectController();
			    $object_controller->add_to_workspaces($contact, !can_manage_contacts(logged_user()));
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
						$contact_im_value->setIsDefault(array_var($contact_data, 'default_im') == $im_type->getId());

						$contact_im_value->save();
					} // if
				} // foreach
				
				if(active_project() instanceof Project && trim(array_var($contact_data,'role', ''))) {
					$pc = new ProjectContact();
					$pc->setContactId($contact->getId());
					$pc->setProjectId(active_project()->getId());
					$pc->setRole(array_var($contact_data,'role'));
					$pc->save();
				}

				ApplicationLogs::createLog($contact, null, ApplicationLogs::ACTION_ADD);
				
				DB::commit();
				
				if (isset($contact_data['new_contact_from_mail_div_id'])) {
					$combo_val = trim($contact->getFirstname() . ' ' . $contact->getLastname() . ' <' . $contact->getEmail() . '>');
					evt_add("contact added from mail", array("div_id" => $contact_data['new_contact_from_mail_div_id'], "combo_val" => $combo_val, "hf_contacts" => $contact_data['hf_contacts']));
				}
				flash_success(lang('success add contact', $contact->getDisplayName()));
				ajx_current("back");

				// Error...
			} catch(Exception $e) {
				DB::rollback();
				//tpl_assign('error', $e);
				flash_error($e->getMessage());
			} // try

		} // if
	} // add

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
		
		if (active_project() instanceof Project) {
			tpl_assign('isAddProject',true);
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

		$im_types = ImTypes::findAll(array('order' => '`id`'));
		$active_project = active_project();
		$role = "" ;
		if($active_project){
			$pc = $contact->getRole(active_project());
			if ($pc instanceof ProjectContact) {
				$role = $pc->getRole();
			}
		}
		
		$contact_data = array_var($_POST, 'contact');
		if(!is_array($contact_data)) {
			$tag_names = $contact->getTagNames();
			$contact_data = array(
          	'firstname' => $contact->getFirstName(),
          	'lastname' => $contact->getLastName(),
			'middlename'=> $contact->getMiddleName(), 
          	'department' => $contact->getDepartment(),
          	'job_title' => $contact->getJobTitle(),
            'email' => $contact->getEmail(),
            'email2' => $contact->getEmail2(),
            'email3' => $contact->getEmail3(),
			'w_web_page'=> $contact->getWWebPage(), 
			'w_address'=> $contact->getWAddress(), 
			'w_city'=> $contact->getWCity(), 
			'w_state'=> $contact->getWState(), 
			'w_zipcode'=> $contact->getWZipcode(), 
			'w_country'=> $contact->getWCountry(), 
			'w_phone_number'=> $contact->getWPhoneNumber(), 
			'w_phone_number2'=> $contact->getWPhoneNumber2(), 
			'w_fax_number'=> $contact->getWFaxNumber(), 
			'w_assistant_number'=> $contact->getWAssistantNumber(), 
			'w_callback_number'=> $contact->getWCallbackNumber(), 

			'h_web_page'=> $contact->getHWebPage(), 
			'h_address'=> $contact->getHAddress(), 
			'h_city'=> $contact->getHCity(), 
			'h_state'=> $contact->getHState(), 
			'h_zipcode'=> $contact->getHZipcode(), 
			'h_country'=> $contact->getHCountry(), 
			'h_phone_number'=> $contact->getHPhoneNumber(), 
			'h_phone_number2'=> $contact->getHPhoneNumber2(), 
			'h_fax_number'=> $contact->getHFaxNumber(), 
			'h_mobile_number'=> $contact->getHMobileNumber(), 
			'h_pager_number'=> $contact->getHPagerNumber(), 

			'o_web_page'=> $contact->getOWebPage(), 
			'o_address'=> $contact->getOAddress(), 
			'o_city'=> $contact->getOCity(), 
			'o_state'=> $contact->getOState(), 
			'o_zipcode'=> $contact->getOZipcode(), 
			'o_country'=> $contact->getOCountry(), 
			'o_phone_number'=> $contact->getOPhoneNumber(), 
			'o_phone_number2'=> $contact->getOPhoneNumber2(), 
			'o_fax_number'=> $contact->getOFaxNumber(), 
			'o_birthday'=> $contact->getOBirthday(), 
          	'picture_file' => $contact->getPictureFile(),
          	'timezone' => $contact->getTimezone(),
          	'notes' => $contact->getNotes(),
          	'is_private' => $contact->getIsPrivate(),
          	'company_id' => $contact->getCompanyId(),
      	    'role' => $role,
      	    'tags' => is_array($tag_names) ? implode(', ', $tag_names) : '',
      	    
      	    ); // array

      	    if(is_array($im_types)) {
      	    	foreach($im_types as $im_type) {
      	    		$contact_data['im_' . $im_type->getId()] = $contact->getImValue($im_type);
      	    	} // forech
      	    } // if

      	    $default_im = $contact->getDefaultImType();
      	    $contact_data['default_im'] = $default_im instanceof ImType ? $default_im->getId() : '';
		} // if

		tpl_assign('contact', $contact);
		tpl_assign('contact_data', $contact_data);
		tpl_assign('im_types', $im_types);

		if(is_array(array_var($_POST, 'contact'))) {

			//	MANAGE CONCURRENCE WHILE EDITING			
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
				ajx_set_panel(lang ('tab name',array('name'=>$new_contact->getDisplayName())));
				ajx_extra_data(array("title" => $new_contact->getDisplayName(), 'icon'=>'ico-contact'));
				ajx_set_no_toolbar(true);
				//ajx_set_panel(lang ('tab name',array('name'=>$new_contact->getDisplayName())));
				return;
			}
			
			try {
				DB::beginWork();
				
				$newCompany = false;
				if (array_var($contact_data, 'isNewCompany') == 'true' && is_array(array_var($_POST, 'company'))){
					$company_data = array_var($_POST, 'company');
					$company = new Company();
					$company->setFromAttributes($company_data);
					$company->setClientOfId(1);
															
					$company->save();
					ApplicationLogs::createLog($company, null, ApplicationLogs::ACTION_ADD );
					$newCompany = true;
					
					if(active_project() instanceof Project && $company->canAdd(logged_user(), active_project())) {
						$company->addToWorkspace(active_project());
					}else{
						$company->addToWorkspace(logged_user()->getPersonalProject());
					}
				}
				
				$contact_data['o_birthday'] = getDateValue(array_var($contact_data, "o_birthday_value",''));
				
				$contact->setFromAttributes($contact_data);
				
				/*if (!is_null($contact->getOBirthday()) && $contact_data["o_birthday_year"] == 0){
					$contact->setOBirthday(null);
				} else if ($contact_data["o_birthday_year"] != 0) {
					$bday = new DateTimeValue(0);
					$bday->setYear($contact_data["o_birthday_year"]);
					$bday->setMonth($contact_data["o_birthday_month"]);
					$bday->setDay($contact_data["o_birthday_day"]);
					$contact->setOBirthday($bday);
				}*/

				if($newCompany)
					$contact->setCompanyId($company->getId());

				$contact->save();
				$contact->setTagsFromCSV(array_var($contact_data, 'tags'));
				$contact->clearImValues();

				foreach($im_types as $im_type) {
					$value = trim(array_var($contact_data, 'im_' . $im_type->getId()));
					if($value <> '') {

						$contact_im_value = new ContactImValue();

						$contact_im_value->setContactId($contact->getId());
						$contact_im_value->setImTypeId($im_type->getId());
						$contact_im_value->setValue($value);
						$contact_im_value->setIsDefault(array_var($contact_data, 'default_im') == $im_type->getId());

						$contact_im_value->save();
					} // if
				} // foreach

				$object_controller = new ObjectController();
				$object_controller->add_to_workspaces($contact, !can_manage_contacts(logged_user()));
			    $object_controller->link_to_new_object($contact);
				$object_controller->add_subscribers($contact);
				$object_controller->add_custom_properties($contact);
				
				ApplicationLogs::createLog($contact, null, ApplicationLogs::ACTION_EDIT );
				
				DB::commit();
				
				if (trim(array_var($contact_data, 'role', '')) != '' && active_project() instanceof Project) {
					if(!ProjectContact::canAdd(logged_user(), active_project())) {
						flash_error(lang('error contact added but not assigned', $contact->getDisplayName(), active_project()->getName()));
						ajx_current("back");
						return;
					} // if
					
					$pc = $contact->getRole(active_project());
					if (!$pc instanceof ProjectContact) {
						$pc = new ProjectContact();
						$pc->setContactId($contact->getId());
						$pc->setProjectId(active_project()->getId());
					}
					$pc->setRole(array_var($contact_data,'role'));
					$pc->save();
					//ApplicationLogs::createLog($contact, $contact->getWorkspaces(), ApplicationLogs::ACTION_ADD);

				}

				flash_success(lang('success edit contact', $contact->getDisplayName()));
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

				ApplicationLogs::createLog($contact, null, ApplicationLogs::ACTION_EDIT);
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
			ApplicationLogs::createLog($contact, $contact->getWorkspaces(), ApplicationLogs::ACTION_EDIT);

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
			ApplicationLogs::createLog($contact, null, ApplicationLogs::ACTION_TRASH );

			DB::commit();

			flash_success(lang('success delete contact', $contact->getDisplayName()));
			ajx_current("back");
		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error delete contact'));
			ajx_current("empty");
		} // try
	} // delete

	function assign_to_project() {
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
		
		$projects = active_projects();
		$contactRoles = ProjectContacts::getRolesByContact($contact);

		if(!$contact->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$contact_data = array_var($_POST, 'contact');
		$enterData = true;
		if(!is_array($contact_data)) {
			$enterData = false;
			foreach($projects as $project){
				$contact_data['pid_'.$project->getId()] = false;
				$contact_data['role_pid_'.$project->getId()] = '';
				 
				if($contactRoles){
					foreach($contactRoles as $cr){
						if ($project->getId() == $cr->getProjectId()){
							$contact_data['pid_'.$project->getId()] = true;
							$contact_data['role_pid_'.$project->getId()] = $cr->getRole();
						} // if
					} // foreach
				} // if
			} // foreach
		} // if

		if($enterData){
			try {
				DB::beginWork();
				$err = 0; $succ = 0;
				$workspaces = array();
				foreach($projects as $project) {
					$pc = ProjectContacts::getRole($contact, $project);
					if(!isset($contact_data['pid_'.$project->getId()])){
						if ($pc instanceof ProjectContact) {
							$pc->delete();
							$succ++;
						}
					} else {
						$role = $contact_data['role_pid_'.$project->getId()];
						if ($pc instanceof ProjectContact) {
							if ($pc->getRole() != $role){
								$pc->setRole($role);
								$pc->save();
								$succ++;
//								ApplicationLogs::createLog($contact, $project, ApplicationLogs::ACTION_EDIT);
							} //if
						} else {
							$pc = new ProjectContact();
							$pc->setProjectId($project->getId());
							$pc->setContactId($contact->getId());
							$pc->setRole($role);
							$pc->save();
							$succ++;
//							ApplicationLogs::createLog($contact, $project, ApplicationLogs::ACTION_EDIT);
						}//if else
					}//if else
				}//foreach
				if ($err == 0) {
					flash_success(lang('success edit contact', $contact->getDisplayName()));
					DB::commit();
					ApplicationLogs::createLog($contact, null, ApplicationLogs::ACTION_EDIT );
					ajx_current("back");
				} else {
					DB::rollback();
					flash_error(lang('failed to assign contact due to permissions', implode(", ", $workspaces)));
					ajx_current("empty");
				}
			} catch(Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			} // try
		} // if

		tpl_assign('contact', $contact);
		tpl_assign('contact_data', $contact_data);
		tpl_assign('projects', $projects);
	} // assign_to_project
	
	
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
			
			if(!Contact::canAdd(logged_user(), active_or_personal_project())) {
				flash_error(lang('no access permissions'));
				ajx_current("empty");
				return;
			} // if
	
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
							$fname = mysql_real_escape_string(array_var($contact_data, "firstname"));
							$lname = mysql_real_escape_string(array_var($contact_data, "lastname"));
							$email_cond = array_var($contact_data, "email") != '' ? " OR email = '".array_var($contact_data, "email")."'" : "";
							$contact = Contacts::findOne(array("conditions" => "firstname = '".$fname."' AND lastname = '".$lname."' $email_cond"));
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
								$comp_name = mysql_real_escape_string(array_var($contact_data, "company_id"));
								if ($comp_name != '') {
									$company = Companies::findOne(array("conditions" => "name = '$comp_name'"));
									if ($company) {
										$contact_data['company_id'] = $company->getId();
									} else {
										$company_data = self::getCompanyDataFromContactData($contact_data);
										$company = new Company();
										$company->setFromAttributes($company_data);
										if ($company->isOwner()) 
											$company->setClientOfId(0);
										else 
											$company->setClientOfId(owner_company()->getId());
										$company->save();
										ApplicationLogs::createLog($company, null, ApplicationLogs::ACTION_ADD);
										$company->setTagsFromCSV(array_var($_POST, 'tags'));
										if (active_project() instanceof Project) $company->addToWorkspace(active_project());
										$contact_data['company_id'] = $company->getId();
									}
									$contact_data['import_status'] .= " " . lang("company") . " $comp_name";
								} else {
									$contact_data['company_id'] = 0;
								}

								$contact->setFromAttributes($contact_data);
								$contact->save();
								ApplicationLogs::createLog($contact, null, $log_action);
								$contact->setTagsFromCSV(array_var($_POST, 'tags'));
							
								if(active_project() instanceof Project) {
									$pc = ProjectContacts::findOne(array("conditions" => "contact_id = ".$contact->getId()." AND project_id = ".active_project()->getId()));
									if (!$pc) {
										$pc = new ProjectContact();
										$pc->setContactId($contact->getId());
										$pc->setProjectId(active_project()->getId());
										$pc->setRole(array_var($contact_data,'role'));
										$pc->save();
									}
									$contact->addToWorkspace(active_project());
								}
								$import_result['import_ok'][] = $contact_data;
							} else {
								throw new Exception(lang('no access permissions'));
							}
							
						} else if ($type == 'company') {
							$contact_data = $this->buildCompanyData(array_var($_POST, 'select_company'), array_var($_POST, 'check_company'), $registers[$i]);
							$contact_data['import_status'] = '('.lang('updated').')';
							$comp_name = mysql_real_escape_string(array_var($contact_data, "name"));
							$company = Companies::findOne(array("conditions" => "name = '$comp_name'"));
							$log_action = ApplicationLogs::ACTION_EDIT;
							if (!$company) {
								$company = new Company();
								$contact_data['import_status'] = '('.lang('new').')';
								$log_action = ApplicationLogs::ACTION_ADD;
								$can_import = active_project() != null ? $company->canAdd(logged_user(), active_project()) : can_manage_contacts(logged_user()) || logged_user()->isAccountOwner() || logged_user()->isAdministrator();
							} else {
								$can_import = $company->canEdit(logged_user());
							}
							if ($can_import) {
								$company->setFromAttributes($contact_data);
								if ($company->isOwner()) 
									$company->setClientOfId(0);
								else 
									$company->setClientOfId(owner_company()->getId());
								$company->save();
								ApplicationLogs::createLog($company, null, $log_action);
								$company->setTagsFromCSV(array_var($_POST, 'tags'));
								if (active_project() instanceof Project) $company->addToWorkspace(active_project());
								
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
		$comp['notes'] = '';
		$comp['timezone'] = logged_user()->getTimezone();
		return $comp;
	}
	
	function buildCompanyData($position, $checked, $fields) {
		$contact_data = array();
		if (isset($checked['name']) && $checked['name']) $contact_data['name'] = array_var($fields, $position['name']);
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
	
	function buildContactData($position, $checked, $fields) {
		$contact_data = array();
		if (isset($checked['firstname']) && $checked['firstname']) $contact_data['firstname'] = array_var($fields, $position['firstname']);
		if (isset($checked['lastname']) && $checked['lastname']) $contact_data['lastname'] = array_var($fields, $position['lastname']);
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

	function export_to_csv_file() {
		$this->setTemplate('csv_export');
		
		$type = array_var($_GET, 'type', array_var($_SESSION, 'import_type', 'contact')); //type of import (contact - company)
		tpl_assign('import_type', $type);
		if (!isset($_SESSION['import_type']) || ($type != $_SESSION['import_type'] && $type != ''))
			$_SESSION['import_type'] = $type;
		
		if ($type == 'contact') $checked_fields = array_var($_POST, 'check_contact');
		else $checked_fields = array_var($_POST, 'check_company');
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
			} else {
				$field_names = Companies::getCompanyFieldNames();
				
				foreach($checked_fields as $k => $v) {
					if (isset($field_names["company[$k]"]) && $v == 'checked')
						$titles .= $field_names["company[$k]"] . ',';
				}
				$titles = substr_utf($titles, 0, strlen_utf($titles)-1) . "\n";
			}
			
			$filename = rand().'.tmp';
			$handle = fopen(ROOT.'/tmp/'.$filename, 'wb');
			fwrite($handle, $titles);
			
			$project = active_project();
			if ($project instanceof Project) {
				$pids = $project->getAllSubWorkspacesQuery(true);
			}
			$wsConditions = null;
			$tag_str = null;
			$tag = array_var($_GET, 'active_tag');

			if (array_var($_SESSION, 'import_type', 'contact') == 'contact') {
				if (isset($pids)) 
					$wsConditions = Contacts::getWorkspaceString($pids);
				if (isset($tag) && $tag && $tag!='')
		    		$tag_str = " EXISTS (SELECT * FROM `" . TABLE_PREFIX . "tags` `t` WHERE `tag` = ".DB::escape($tag)." AND `co`.`id` = `t`.`rel_object_id` AND `t`.`rel_object_manager` = 'Contacts') ";

		    	$conditions = $wsConditions ? ($wsConditions . ($tag_str ? " AND $tag_str" : '')) : $tag_str;
		    	$conditions .= ($conditions == "" ? "" : " AND ") . "`archived_by_id` = 0" . ($conditions ? " AND $conditions" : "");
				$contacts = Contacts::instance()->getAllowedContacts($conditions);
				foreach ($contacts as $contact) {
					fwrite($handle, $this->build_csv_from_contact($contact, $checked_fields) . "\n");
				}
			} else {
				if (isset($pids)) 
					$wsConditions = Companies::getWorkspaceString($pids);
				if (isset($tag) && $tag && $tag!='')
		    		$tag_str = " EXISTS (SELECT * FROM `" . TABLE_PREFIX . "tags` `t` WHERE `tag` = ".DB::escape($tag)." AND `".TABLE_PREFIX . "companies`.`id` = `t`.`rel_object_id` AND `t`.`rel_object_manager` = 'Companies') ";
					
		    	$conditions = $wsConditions ? ($wsConditions . ($tag_str ? " AND $tag_str" : '')) : $tag_str;
		    	$conditions .= ($conditions == "" ? "" : " AND ") . "`archived_by_id` = 0" . ($conditions ? " AND $conditions" : "");
				$companies = Companies::getVisibleCompanies(logged_user(), $conditions);
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
			download_file($path, 'text/csv', $name, $size, false);
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
		
		if (isset($checked['firstname']) && $checked['firstname'] == 'checked') $str .= self::build_csv_field($contact->getFirstname());
		if (isset($checked['lastname']) && $checked['lastname'] == 'checked') $str .= self::build_csv_field($contact->getLastname());
		if (isset($checked['email']) && $checked['email'] == 'checked') $str .= self::build_csv_field($contact->getEmail());
		if (isset($checked['company_id']) && $checked['company_id'] == 'checked') $str .= self::build_csv_field($contact->getCompany() ? $contact->getCompany()->getName() : "");
		
		if (isset($checked['w_web_page']) && $checked['w_web_page'] == 'checked') $str .= self::build_csv_field($contact->getWWebPage());
		if (isset($checked['w_address']) && $checked['w_address'] == 'checked') $str .= self::build_csv_field($contact->getWAddress());
		if (isset($checked['w_city']) && $checked['w_city'] == 'checked') $str .= self::build_csv_field($contact->getWCity());
		if (isset($checked['w_state']) && $checked['w_state'] == 'checked') $str .= self::build_csv_field($contact->getWState());
		if (isset($checked['w_zipcode']) && $checked['w_zipcode'] == 'checked') $str .= self::build_csv_field($contact->getWZipcode());
		if (isset($checked['w_country']) && $checked['w_country'] == 'checked') $str .= self::build_csv_field($contact->getWCountryName());
		if (isset($checked['w_phone_number']) && $checked['w_phone_number'] == 'checked') $str .= self::build_csv_field($contact->getWPhoneNumber());
		if (isset($checked['w_phone_number2']) && $checked['w_phone_number2'] == 'checked') $str .= self::build_csv_field($contact->getWPhoneNumber2());
		if (isset($checked['w_fax_number']) && $checked['w_fax_number'] == 'checked') $str .= self::build_csv_field($contact->getWFaxNumber());
		if (isset($checked['w_assistant_number']) && $checked['w_assistant_number'] == 'checked') $str .= self::build_csv_field($contact->getWAssistantNumber());
		if (isset($checked['w_callback_number']) && $checked['w_callback_number'] == 'checked') $str .= self::build_csv_field($contact->getWCallbackNumber());
		
		if (isset($checked['h_web_page']) && $checked['h_web_page'] == 'checked') $str .= self::build_csv_field($contact->getHWebPage());
		if (isset($checked['h_address']) && $checked['h_address'] == 'checked') $str .= self::build_csv_field($contact->getHAddress());
		if (isset($checked['h_city']) && $checked['h_city'] == 'checked') $str .= self::build_csv_field($contact->getHCity());
		if (isset($checked['h_state']) && $checked['h_state'] == 'checked') $str .= self::build_csv_field($contact->getHState());
		if (isset($checked['h_zipcode']) && $checked['h_zipcode'] == 'checked') $str .= self::build_csv_field($contact->getHZipcode());
		if (isset($checked['h_country']) && $checked['h_country'] == 'checked') $str .= self::build_csv_field($contact->getHCountryName());
		if (isset($checked['h_phone_number']) && $checked['h_phone_number'] == 'checked') $str .= self::build_csv_field($contact->getHPhoneNumber());
		if (isset($checked['h_phone_number2']) && $checked['h_phone_number2'] == 'checked') $str .= self::build_csv_field($contact->getHPhoneNumber2());
		if (isset($checked['h_fax_number']) && $checked['h_fax_number'] == 'checked') $str .= self::build_csv_field($contact->getHFaxNumber());
		if (isset($checked['h_mobile_number']) && $checked['h_mobile_number'] == 'checked') $str .= self::build_csv_field($contact->getHMobileNumber());
		if (isset($checked['h_pager_number']) && $checked['h_pager_number'] == 'checked') $str .= self::build_csv_field($contact->getHPagerNumber());
		
		if (isset($checked['o_web_page']) && $checked['o_web_page'] == 'checked') $str .= self::build_csv_field($contact->getOWebPage());
		if (isset($checked['o_address']) && $checked['o_address'] == 'checked') $str .= self::build_csv_field($contact->getOAddress());
		if (isset($checked['o_city']) && $checked['o_city'] == 'checked') $str .= self::build_csv_field($contact->getOCity());
		if (isset($checked['o_state']) && $checked['o_state'] == 'checked') $str .= self::build_csv_field($contact->getOState());
		if (isset($checked['o_zipcode']) && $checked['o_zipcode'] == 'checked') $str .= self::build_csv_field($contact->getOZipcode());
		if (isset($checked['o_country']) && $checked['o_country'] == 'checked') $str .= self::build_csv_field($contact->getOCountryName());
		if (isset($checked['o_phone_number']) && $checked['o_phone_number'] == 'checked') $str .= self::build_csv_field($contact->getOPhoneNumber());
		if (isset($checked['o_phone_number2']) && $checked['o_phone_number2'] == 'checked') $str .= self::build_csv_field($contact->getOPhoneNumber2());
		if (isset($checked['o_fax_number']) && $checked['o_fax_number'] == 'checked') $str .= self::build_csv_field($contact->getOFaxNumber());
		if (isset($checked['o_birthday']) && $checked['o_birthday'] == 'checked') $str .= self::build_csv_field($contact->getOBirthday());
		if (isset($checked['email2']) && $checked['email2'] == 'checked') $str .= self::build_csv_field($contact->getEmail2());
		if (isset($checked['email3']) && $checked['email3'] == 'checked') $str .= self::build_csv_field($contact->getEmail3());
		if (isset($checked['job_title']) && $checked['job_title'] == 'checked') $str .= self::build_csv_field($contact->getJobTitle());
		if (isset($checked['department']) && $checked['department'] == 'checked') $str .= self::build_csv_field($contact->getDepartment());
		if (isset($checked['middlename']) && $checked['middlename'] == 'checked') $str .= self::build_csv_field($contact->getMiddlename());
		if (isset($checked['notes']) && $checked['notes'] == 'checked') $str .= self::build_csv_field($contact->getNotes(), true);
		
		$str = str_replace(array(chr(13).chr(10), chr(13), chr(10)), ' ', $str); //remove line breaks
		
		return $str;
	}
	
	function build_csv_from_company(Company $company, $checked) {
		$str = '';
		
		if (isset($checked['name']) && $checked['name'] == 'checked') $str .= self::build_csv_field($company->getName());
		if (isset($checked['address']) && $checked['address'] == 'checked') $str .= self::build_csv_field($company->getAddress());
		if (isset($checked['address2']) && $checked['address2'] == 'checked') $str .= self::build_csv_field($company->getAddress2());
		if (isset($checked['city']) && $checked['city'] == 'checked') $str .= self::build_csv_field($company->getCity());
		if (isset($checked['state']) && $checked['state'] == 'checked') $str .= self::build_csv_field($company->getState());
		if (isset($checked['zipcode']) && $checked['zipcode'] == 'checked') $str .= self::build_csv_field($company->getZipcode());
		if (isset($checked['country']) && $checked['country'] == 'checked') $str .= self::build_csv_field($company->getCountryName());
		if (isset($checked['phone_number']) && $checked['phone_number'] == 'checked') $str .= self::build_csv_field($company->getPhoneNumber());
		if (isset($checked['fax_number']) && $checked['fax_number'] == 'checked') $str .= self::build_csv_field($company->getFaxNumber());
		if (isset($checked['email']) && $checked['email'] == 'checked') $str .= self::build_csv_field($company->getEmail());
		if (isset($checked['homepage']) && $checked['homepage'] == 'checked') $str .= self::build_csv_field($company->getHomepage());
		if (isset($checked['notes']) && $checked['notes'] == 'checked') $str .= self::build_csv_field($company->getNotes());
		
		$str = str_replace(array(chr(13).chr(10), chr(13), chr(10)), ' ', $str); //remove line breaks
		
		return $str;
	}
	
	function search(){
		ajx_current('empty');
		if (!can_manage_contacts(logged_user())) {
			flash_error(lang("no access permissions"));
			return;
		}
		
		$search_for = array_var($_POST,'search_for',false);
		if ($search_for){
			/*if (active_project() instanceof Project) {
				$projects = active_project()->getAllSubWorkspacesQuery(false);
			} else {*/
				$projects = null;
			//}
			
			$search_results = SearchableObjects::searchByType($search_for, $projects, 'Contacts', true, 50);
			$contacts = $search_results[0];
			if ($contacts && count($contacts) > 0){
				$result = array();
				foreach ($contacts as $contactResult){
					$contact = $contactResult['object'];
					$result[] = array(
						'name' => $contact->getFirstname() . ' ' . $contact->getLastname(),
						'phone' => $contact->getWPhoneNumber(),
						'email' => $contact->getEmail(),
						'jobtitle' => $contact->getJobTitle(),
						'company' => $contact->getCompany() instanceof Company?
								array(
									'id' => $contact->getCompany()->getId(),
									'name' => $contact->getCompany()->getName(),
									'phone' => $contact->getCompany()->getPhoneNumber(),
									'email' => $contact->getCompany()->getEmail(),
								) : array(
								),
						'department' => $contact->getDepartment(),
						'id' => $contact->getId()
					);
				}
				ajx_extra_data(array("results" => $result));
			}
		}
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
		}
		
		tpl_assign('import_type', 'contact');
		if(!Contact::canAdd(logged_user(), active_or_personal_project())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$this->setTemplate('vcard_import');
		
		$filedata = array_var($_FILES, 'vcard_file');
		if (is_array($filedata) && !array_var($_GET, 'step2')) {
			
			$filename = ROOT.'/tmp/'.logged_user()->getId().'temp.vcf';
			copy($filedata['tmp_name'], $filename);

			//ajx_current("empty");
			
		} else if (array_var($_GET, 'step2')) {
			$filename = ROOT.'/tmp/'.logged_user()->getId().'temp.vcf';
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
						$company = Companies::findOne(array("conditions" => "`name` = '".mysql_real_escape_string($contact_data['company_name'])."'"));
						if ($company == null) {
							$company = new Company();
							$company->setName($contact_data['company_name']);
							$company->setClientOfId(logged_user()->getCompanyId());
							$company->save();
							ApplicationLogs::createLog($company, null, ApplicationLogs::ACTION_ADD);
						}
						$contact_data['company_id'] = $company->getId();
						unset($contact_data['company_name']);
					}
					
					$contact_data['import_status'] = '('.lang('updated').')';
					$fname = mysql_real_escape_string(array_var($contact_data, "firstname"));
					$lname = mysql_real_escape_string(array_var($contact_data, "lastname"));
					$contact = Contacts::findOne(array("conditions" => "firstname = '".$fname."' AND lastname = '".$lname."' OR email <> '' AND email = '".array_var($contact_data, "email")."'"));
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
						$contact->setFromAttributes($contact_data);
						$contact->save();
						ApplicationLogs::createLog($contact, null, $log_action);
						$contact->setTagsFromCSV(array_var($_GET, 'tags'));
						if(active_project() instanceof Project) {
							$pc = ProjectContacts::findOne(array("conditions" => "contact_id = ".$contact->getId()." AND project_id = ".active_project()->getId()));
							if (!$pc) {
								$pc = new ProjectContact();
								$pc->setContactId($contact->getId());
								$pc->setProjectId(active_project()->getId());
								$pc->setRole(array_var($contact_data,'role'));
								$pc->save();
							}
							$contact->addToWorkspace(active_project());
						}
						$import_result['import_ok'][] = array('firstname' => $fname, 'lastname' => $lname, 'email' => $contact_data['email'], 'import_status' => $contact_data['import_status']);
					} else {
						throw new Exception(lang('no access permissions'));
					}
					DB::commit();					
				} catch (Exception $e) {
					DB::rollback();
					$fail_msg = substr_utf($e->getMessage(), strpos_utf($e->getMessage(), "\r\n"));
					$import_result['import_fail'][] = array('firstname' => $fname, 'lastname' => $lname, 'email' => $contact_data['email'], 'import_status' => $contact_data['import_status'], 'fail_message' => $fail_msg);
				}
			}
			$_SESSION['go_back'] = true;
			tpl_assign('import_result', $import_result);
		}
	}

	private function read_vcard_file($filename, $only_first_record = false) {
		$handle = fopen($filename, 'rb');
        if (! $handle) {
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
                $block_data["firstname"] = trim($matches[count($matches)-1]);
                $block_data["lastname"] = trim($matches[count($matches)-2]);
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
                // unknown / ignored VCard field
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
    		
    		$vcard .= "N:" . $contact->getLastname() . ";" . $contact->getFirstname() . "\n";
    		$vcard .= "FN:" . $contact->getFirstname() . " " . $contact->getLastname() . "\n";
    		if ($contact->getCompany() instanceof Company)
    			$vcard .= "ORG:" . $contact->getCompany()->getName() . "\n";
    		if ($contact->getJobTitle())
    			$vcard .= "TITLE:" . $contact->getJobTitle() . "\n";
    		if ($contact->getOBirthday() instanceof DateTimeValue)
    			$vcard .= "BDAY:" . $contact->getOBirthday()->format("Y-m-d") . "\n";
    		if ($contact->getHAddress())
    			$vcard .= "ADR;TYPE=HOME:;;" . $contact->getHAddress() .";". $contact->getHCity() .";". $contact->getHState() .";". $contact->getHZipcode() .";". $contact->getHCountryName() . "\n";
    		if ($contact->getWAddress())
    			$vcard .= "ADR;TYPE=WORK:;;" . $contact->getWAddress() .";". $contact->getWCity() .";". $contact->getWState() .";". $contact->getWZipcode() .";". $contact->getWCountryName() . "\n";
    		if ($contact->getOAddress())
    			$vcard .= "ADR;TYPE=INTL:;;" . $contact->getOAddress() .";". $contact->getOCity() .";". $contact->getOState() .";". $contact->getOZipcode() .";". $contact->getOCountryName() . "\n";
    		if ($contact->getHPhoneNumber())
    			$vcard .= "TEL;TYPE=HOME,VOICE:" . $contact->getHPhoneNumber() . "\n";
    		if ($contact->getWPhoneNumber())
    			$vcard .= "TEL;TYPE=WORK,VOICE:" . $contact->getWPhoneNumber() . "\n";
    		if ($contact->getOPhoneNumber())
    			$vcard .= "TEL;TYPE=VOICE:" . $contact->getOPhoneNumber() . "\n";
    		if ($contact->getHFaxNumber())
    			$vcard .= "TEL;TYPE=HOME,FAX:" . $contact->getHFaxNumber() . "\n";
    		if ($contact->getWFaxNumber())
    			$vcard .= "TEL;TYPE=WORK,FAX:" . $contact->getWFaxNumber() . "\n";
    		if ($contact->getOFaxNumber())
    			$vcard .= "TEL;TYPE=FAX:" . $contact->getOFaxNumber() . "\n";
    		if ($contact->getHMobileNumber())
    			$vcard .= "TEL;TYPE=CELL,VOICE:" . $contact->getHMobileNumber() . "\n";
    		if ($contact->getHWebPage())
    			$vcard .= "URL;TYPE=HOME:" . $contact->getHWebPage() . "\n";
    		if ($contact->getWWebPage())
    			$vcard .= "URL;TYPE=WORK:" . $contact->getWWebPage() . "\n";
    		if ($contact->getOWebPage())
    			$vcard .= "URL:" . $contact->getOWebPage() . "\n";
    		if ($contact->getEmail())
    			$vcard .= "EMAIL;TYPE=PREF,INTERNET:" . $contact->getEmail() . "\n";
    		if ($contact->getEmail2())
    			$vcard .= "EMAIL;TYPE=INTERNET:" . $contact->getEmail2() . "\n";
    		if ($contact->getEmail3())
    			$vcard .= "EMAIL;TYPE=INTERNET:" . $contact->getEmail3() . "\n";
			if ($contact->getNotes())
    			$vcard .= "NOTE:" . $contact->getNotes() . "\n";
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
   		$ids = explode(",", $ids);
   		$allowed = Contacts::instance()->getAllowedContacts();
		foreach ($allowed as $c) {
			if (in_array($c->getId(), $ids)) $contacts[] = $c;
		}
    	if (count($contacts) == 0) {
			flash_error(lang("you must select the contacts from the grid"));
			ajx_current("empty");
			return;
		}
		
    	$data = self::build_vcard($contacts);
    	$name = (count($contacts) == 1 ? $contacts[0]->getDisplayName() : "contacts") . ".vcf";

    	download_contents($data, 'text/x-vcard', $name, strlen($data), true);
    	die();
    }
    

    function export_to_vcard_all() {
      $contacts_all = Contacts::instance()->getAllowedContacts();
      $user=logged_user();
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
} // ContactController

?>