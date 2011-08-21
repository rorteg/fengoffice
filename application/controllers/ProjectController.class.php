<?php

/**
 * Projec controller
 *
 * @version 1.0
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class ProjectController extends ApplicationController {

	/**
	 * Prepare this controller
	 *
	 * @param void
	 * @return ProjectController
	 */
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website');
	} // __construct

	/**
	 * Call overview action
	 *
	 * @param void
	 * @return null
	 */
	function index() {
		$this->forward('overview');
	} // index

	function get_subws(){
		
		if (active_project() instanceof Project){
			if(!logged_user()->isProjectUser(active_project())) {
				flash_error(lang('no access permissions'));
				ajx_current("empty");
				return;
			} // if
			
			tpl_assign('projects', active_project()->getSubWorkspaces(false, null, false));
		} else {
			
			tpl_assign('projects', logged_user()->getWorkspaces(false,0));
		}
	}
	
	/**
	 * Show project overview
	 *
	 * @param void
	 * @return null
	 */
	function overview() {
		if(!logged_user()->isProjectUser(active_project())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$this->addHelper('textile');

		$project = active_project();

		tpl_assign('project_log_entries', $project->getProjectLog(
		config_option('project_logs_per_page', 20)
		));
		tpl_assign('late_milestones', $project->getLateMilestones());
		tpl_assign('today_milestones', $project->getTodayMilestones());
		tpl_assign('upcoming_milestones', $project->getUpcomingMilestones());

	} // overview

	/**
	 * Execute search
	 *
	 * @param void
	 * @return null
	 */
	function search() {
		ajx_set_panel("search");
		$timeBegin = microtime(true);
		if(active_project() && !logged_user()->isProjectUser(active_project())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$search_for = array_var($_GET, 'search_for');
		$page = (integer) array_var($_GET, 'page', 1);
		if($page < 1) $page = 1;

		if(trim($search_for) == '') {
			$search_results = null;
			$pagination = null;
		} else {
			if (active_project()) {
				$projects = active_project()->getId();
			} else { 
				$projects = null;
			}
			list($search_results, $pagination) = SearchableObjects::searchPaginated($search_for, $projects, logged_user()->isMemberOfOwnerCompany());
		} // if
		$timeEnd = microtime(true);

		tpl_assign('search_string', $search_for);
		tpl_assign('current_page', $page);
		tpl_assign('search_results', $search_results);
		tpl_assign('pagination', $pagination);
		tpl_assign('time', $timeEnd - $timeBegin);
	} // search


	/**
	 * List all companies and users involved in this project
	 *
	 * @param void
	 * @return null
	 */
	function people() {
		$project=active_or_personal_project();
		if(!logged_user()->isProjectUser($project)) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		tpl_assign('project_companies', $project->getCompanies());
	} // people

	/**
	 * Show permission update form
	 *
	 * @param void
	 * @return null
	 */
	function permissions() {
		$project = active_or_personal_project();
		if(!$project->canChangePermissions(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		tpl_assign('project_users', $project->getUsers(false));
		tpl_assign('project_companies', $project->getCompanies());
		tpl_assign('user_projects', logged_user()->getProjects());

		$permissions = ProjectUsers::getNameTextArray();
		tpl_assign('permissions', $permissions);

		$companies = array(owner_company());
		$clients = owner_company()->getClientCompanies();
		if(is_array($clients)) {
			$companies = array_merge($companies, $clients);
		} // if
		tpl_assign('companies', $companies);

		if(array_var($_POST, 'process') == 'process') {
			try {
				DB::beginWork();

				$project->clearCompanies();
				$project->clearUsers();

				$companies = array(owner_company());
				$client_companies = owner_company()->getClientCompanies();
				if(is_array($client_companies)) {
					$companies = array_merge($companies, $client_companies);
				} // if

				foreach($companies as $company) {

					// Company is selected!
					if(array_var($_POST, 'project_company_' . $company->getId()) == 'checked') {

						// Owner company is automaticly included so it does not need to be in project_companies table
						if(!$company->isOwner()) {
							$project_company = new ProjectCompany();
							$project_company->setProjectId($project->getId());
							$project_company->setCompanyId($company->getId());
							$project_company->save();
						} // if

						$users = $company->getUsers();
						if(is_array($users)) {
							$counter = 0;
							foreach($users as $user) {
								$user_id = $user->getId();
								$counter++;
								if(array_var($_POST, "project_user_$user_id") == 'checked') {

									$project_user = new ProjectUser();
									$project_user->setProjectId($project->getId());
									$project_user->setUserId($user_id);

									foreach($permissions as $permission => $permission_text) {

										// Owner company members have all permissions
										$permission_value = $company->isOwner() ? true : array_var($_POST, 'project_user_' . $user_id . '_' . $permission) == 'checked';

										$setter = 'set' . Inflector::camelize($permission);
										$project_user->$setter($permission_value);

									} // if

									$project_user->save();

								} // if

							} // foreach
						} // if
					} // if
				} // foreach

				DB::commit();

				flash_success(lang('success update project permissions'));
				ajx_current("back");
			} catch(Exception $e) {
				DB::rollback();
				flash_error(lang('error update project permissions'));
				ajx_current("empty");
			} // try
		} // if
	} // permissions
	
	function get_ws_permissions() {
		ajx_current("empty");
		$id = array_var($_GET, "ws_id");
		if (!$id) {
			return;
		}
		$project = Projects::findById($id);
		if (!$project instanceof Project) {
			return;
		}
		ajx_extra_data(array('permissions' => $project->getAllPermissions()));
	}

	/**
	 * Add project
	 *
	 * @param void
	 * @return null
	 */
	function add() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$this->setTemplate('add_project');

		if(!Project::canAdd(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$project = new Project();
		$project_data = array_var($_POST, 'project');
		$projects = logged_user()->getActiveProjects();
		
		if (active_project() instanceof Project){ 
			$billing_amounts = active_project()->getBillingAmounts();
		} else {
			$billing_amounts = BillingCategories::getDefaultBillingAmounts();
		}
		
		tpl_assign('project', $project);
		tpl_assign('projects', $projects);
		tpl_assign('project_data', $project_data);
		tpl_assign('billing_amounts', $billing_amounts);
		
		// Submited...
		if(is_array($project_data)) {
			$project->setFromAttributes($project_data);
			
			try {
				DB::beginWork();
				$project->save(); //Save to get the id, then update the project path info
				
				if (array_var($project_data, 'parent_id') != $project->getParentId()){
					$parent = Projects::findById(array_var($project_data, 'parent_id'));
					if ($parent){
						if(!$project->canSetAsParentWorkspace($parent)) {
							flash_error(lang('error cannot set workspace as parent', $parent->getName()));
							ajx_current("empty");
							return;
						}
					}
					$project->setParentWorkspace($parent);
				}
				$project->save();
                
                /* Billing */
				$billings = array_var($project_data,'billing', null);
				if ($billings){
					foreach ($billings as $billing_id => $billing){
						if ($billing['update'] && $billing['value'] && $billing['value'] != 0){
							$wb = new WorkspaceBilling();
							$wb->setProjectId($project->getId());
							$wb->setBillingId($billing_id);
							$value = $billing['value'];
							if (strpos($value,',') && !strpos($value,'.'))
								$value = str_replace(',','.',$value);
							$wb->setValue($value);
							$wb->save();
						}
					}
				}
				
				/* Project contacts */
				if (can_manage_contacts(logged_user())){
					$contacts = array_var($project_data,'contacts', null);
					if ($contacts){
						foreach ($contacts as $contact_data){
							$contact = Contacts::findById($contact_data['contact_id']);
							if ($contact instanceof Contact){
								$pc = new ProjectContact();
								$pc->setProjectId($project->getId());
								$pc->setContactId($contact_data['contact_id']);
								$pc->setRole($contact_data['role']);
								$pc->save();
							}
						}
					}
				}

				/* <permissions> */
				$permissions = null;
				$permissionsString = array_var($_POST, 'permissions');
				if ($permissionsString && $permissionsString != '') {
					$permissions = json_decode($permissionsString);
				}
			  	if(is_array($permissions) && count($permissions) > 0) {
			  		//Add new permissions
			  		//TODO - Make batch update of these permissions
			  		foreach ($permissions as $perm) {
			  			if (ProjectUser::hasAnyPermissions($perm->pr,$perm->pc)) {			  				
				  			$relation = new ProjectUser();
					  		$relation->setProjectId($project->getId());
					  		$relation->setUserId($perm->wsid);
				  			
					  		$relation->setCheckboxPermissions($perm->pc, $relation->getUserOrGroup()->isGuest() ? false : true);
					  		$relation->setRadioPermissions($perm->pr, $relation->getUserOrGroup()->isGuest() ? false : true);
					  		$relation->save();
			  			} //endif
			  			//else if the user has no permissions at all, he is not a project_user. ProjectUser is not created
			  		} //end foreach
				} // if
				/* </permissions> */
				
				$object_controller = new ObjectController();
				$object_controller->add_custom_properties($project);
				
				ApplicationLogs::createLog($project, null, ApplicationLogs::ACTION_ADD, false, true);
				DB::commit();
				
				if (logged_user()->isProjectUser($project)) {
					evt_add("workspace added", array(
						"id" => $project->getId(),
						"name" => $project->getName(),
						"color" => $project->getColor(),
						"parent" => $project->getParentId()
					));
				}

				flash_success(lang('success add project', $project->getName()));
				ajx_current("back");
                
                //Overview Wizard
				if(isset($_REQUEST['wizard']))
				{
					$_SESSION['wizard'] = $_REQUEST['wizard'];
					evt_add('select workspace', $project->getId());
				}

				return;

			} catch(Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			} // try
		} // if
	} // add

	/**
	 * Edit project
	 *
	 * @param void
	 * @return null
	 */
	function edit() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$this->setTemplate('add_project');

		$project = Projects::findById(get_id());
		if(!($project instanceof Project)) {
			flash_error(lang('project dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$project->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$project_data = array_var($_POST, 'project');
		if(!is_array($project_data)) {
			$project_data = array(
				'name' => $project->getName(),
				'description' => $project->getDescription(),
				'show_description_in_overview' => $project->getShowDescriptionInOverview(),
				'color' => 0
			); // array
		} // if
		$projects = logged_user()->getActiveProjects();
		
		tpl_assign('project', $project);
		tpl_assign('projects', $projects);
		tpl_assign('project_data', $project_data);
		tpl_assign('billing_amounts', $project->getBillingAmounts());
		tpl_assign('subject_matter_experts', ProjectContacts::getContactsByProject($project));
		
		if(is_array(array_var($_POST, 'project'))) {
			if (array_var($project_data, 'parent_id') == $project->getId()) {
				flash_error(lang("workspace own parent error"));
				ajx_current("empty");
				return;
			}
			
			if (!isset($project_data['parent_id']))
				$project_data['parent_id'] = $project->getParentId();
			
			$project->setFromAttributes($project_data);

			try {
				DB::beginWork();
				if (array_var($project_data, 'parent_id') != $project->getParentId()){
					if ($project->getParentWorkspace() instanceof Project && !logged_user()->isProjectUser($project->getParentWorkspace())){
						flash_error(lang('no access permissions'));
						ajx_current("empty");
						return;
					} // if
					
					$parent = Projects::findById(array_var($project_data, 'parent_id'));
					if ($parent){
						if(!$project->canSetAsParentWorkspace($parent)) {
							flash_error(lang('error cannot set workspace as parent', $parent->getName()));
							ajx_current("empty");
							return;
						}
					}
					$project->setParentWorkspace($parent);
				}
				$project->save();
				if (GlobalCache::isAvailable()) {
					GlobalCache::delete('user_copt_obj_lastAccessedWorkspace');
					GlobalCache::delete('user_copt_obj_initialWorkspace');		
					$project->deleteProjectFromGlobalCache();
				}
								
				/* Billing */
				WorkspaceBillings::clearByProject($project);
				$billings = array_var($project_data,'billing', null);
				if ($billings){
					foreach ($billings as $billing_id => $billing){
						if ($billing['update'] && $billing['value'] && $billing['value'] != 0){
							$wb = new WorkspaceBilling();
							$wb->setProjectId($project->getId());
							$wb->setBillingId($billing_id);
							$value = $billing['value'];
							if (strpos($value,',') && !strpos($value,'.'))
								$value = str_replace(',','.',$value);
							$wb->setValue($value);
							$wb->save();
						}
					}
				}
				
				/* Project contacts */
				if (can_manage_contacts(logged_user())){
					ProjectContacts::clearByProject($project);
					$contacts = array_var($project_data,'contacts', null);
					if ($contacts){
						foreach ($contacts as $contact_data){
							$contact = Contacts::findById($contact_data['contact_id']);
							if ($contact instanceof Contact){
								$pc = new ProjectContact();
								$pc->setProjectId($project->getId());
								$pc->setContactId($contact_data['contact_id']);
								$pc->setRole($contact_data['role']);
								$pc->save();
							}
						}
					}
				}
				
				
				/* <permissions> */
				$permissions = null;
				$permissionsString = array_var($_POST, 'permissions');
				if ($permissionsString && $permissionsString != '') {
					$permissions = json_decode($permissionsString);
				}
			  	if(is_array($permissions) && count($permissions) > 0) {
			  		//Clear old modified permissions
			  		$ids = array();
			  		foreach($permissions as $perm) {
			  			$ids[] = $perm->wsid;
			  		}
			  		ProjectUsers::clearByProject($project, implode(',', $ids));
			  		
			  		//Add new permissions
			  		//TODO - Make batch update of these permissions
			  		foreach ($permissions as $perm) {
			  			if (ProjectUser::hasAnyPermissions($perm->pr,$perm->pc)) {			  				
				  			$relation = new ProjectUser();
					  		$relation->setProjectId($project->getId());
					  		$relation->setUserId($perm->wsid);
				  			
					  		$relation->setCheckboxPermissions($perm->pc, $relation->getUserOrGroup()->isGuest() ? false : true);
					  		$relation->setRadioPermissions($perm->pr, $relation->getUserOrGroup()->isGuest() ? false : true);
					  		$relation->save();
			  			} //endif
			  			//else if the user has no permissions at all, he is not a project_user. ProjectUser is not created
			  		} //end foreach
				} // if
				/* </permissions> */				
				
				$object_controller = new ObjectController();
				$object_controller->add_custom_properties($project);
				
				ApplicationLogs::createLog($project, null, ApplicationLogs::ACTION_EDIT, false, true);
				DB::commit();
				
				if (logged_user()->isProjectUser($project)) {
					$workspace_info = $this->get_workspace_info($project);
					evt_add("workspace edited", $workspace_info);
				}
				
				flash_success(lang('success edit project', $project->getName()));
				ajx_current("back");
				return;
			} catch(Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			} // try
		} // if
	} // edit

	/**
	 * Delete project
	 *
	 * @param void
	 * @return null
	 */
	function delete() {		
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$pid = get_id();
		$u = Users::findOne(array("conditions" => "personal_project_id = $pid"));
		if ($u) {
			//flash_error("id: $pid, u: ".$u->getId());
			ajx_current("empty");
			flash_error(lang('cannot delete personal project'));
			return;
			//$this->redirectTo('administration', 'projects');
		}
		$project = Projects::findById(get_id());
		if(!($project instanceof Project)) {
			flash_error(lang('project dnx'));
				ajx_current("empty");
				return;
			//$this->redirectTo('administration', 'projects');
		} // if

		if(!$project->canDelete(logged_user())) {
			flash_error(lang('no access permissions'));
				ajx_current("empty");
				return;
			//$this->redirectToReferer(get_url('administration', 'projects'));
		} // if
		if(!array_var($_GET,'confirm')){	
			tpl_assign('project' , $project);		
			$this->setTemplate('pre_delete');
			return ;
		}

		ajx_current("empty");
		try {
			$id = $project->getId();
			$name = $project->getName();
			DB::beginWork();
			$project->delete();
			CompanyWebsite::instance()->setProject(null);
			ApplicationLogs::createLog($project, null, ApplicationLogs::ACTION_DELETE);
			DB::commit();	

			flash_success(lang('success delete project', $project->getName()));
			evt_add("workspace deleted", array(
				"id" => $id,
				"name" => $name
			));
			ajx_current("start");

		} catch(Exception $e) {
			DB::rollback();
			flash_error($e->getMessage());
			ajx_current("empty");
		} // try

		//$this->redirectTo('administration', 'projects');
	} // delete

	/**
	 * Complete this project
	 *
	 * @param void
	 * @return null
	 */
	function complete() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$project = Projects::findById(get_id());
		if(!($project instanceof Project)) {
			flash_error(lang('project dnx'));
			ajx_current("empty");
			return;
		} // if
		
		$projects = $project->getSubWorkspaces(true, logged_user());
		$project->complete();
		foreach ($projects as $p) {
			$p->complete();
		}
		ajx_current("back");
	} // complete

	/**
	 * Reopen project
	 *
	 * @param void
	 * @return null
	 */
	function open() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$project = Projects::findById(get_id());
		if(!($project instanceof Project)) {
			flash_error(lang('project dnx'));
			ajx_current("empty");
			return;
		} // if

		$project->open();
		ajx_current("back");
	} // open

	/**
	 * Remove user from project
	 *
	 * @param void
	 * @return null
	 */
	function remove_user() {
		if(!active_project()->canChangePermissions(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$user = Users::findById(get_id('user_id'));
		if(!($user instanceof User)) {
			flash_error(lang('user dnx'));
			ajx_current("empty");
			return;
		} // if

		if($user->isAccountOwner()) {
			flash_error(lang('user cant be removed from project'));
			ajx_current("empty");
			return;
		} // if

		$project = Projects::findById(get_id('project_id'));
		if(!($project instanceof Project)) {
			flash_error(lang('project dnx'));
			ajx_current("empty");
			return;
		} // if

		$project_user = ProjectUsers::findById(array('project_id' => $project->getId(), 'user_id' => $user->getId()));
		if(!($project_user instanceof ProjectUser)) {
			flash_error(lang('user not on project'));
			ajx_current("empty");
			return;
		} // if

		try {
			$project_user->delete();
			flash_success(lang('success remove user from project'));
			ajx_current("reload");
		} catch(Exception $e) {
			flash_error(lang('error remove user from project'));
			ajx_current("empty");
		} // try

	} // remove_user

	/**
	 * Remove company from project
	 *
	 * @param void
	 * @return null
	 */
	function remove_company() {
		if(!active_project()->canChangePermissions(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$project = Projects::findById(get_id('project_id'));
		if(!($project instanceof Project)) {
			flash_error(lang('project dnx'));
			ajx_current("empty");
			return;
		} // if

		$company = Companies::findById(get_id('company_id'));
		if(!($company instanceof Company)) {
			flash_error(lang('company dnx'));
			ajx_current("empty");
			return;
		} // if

		$project_company = ProjectCompanies::findById(array('project_id' => $project->getId(), 'company_id' => $company->getId()));
		if(!($project_company instanceof ProjectCompany)) {
			flash_error(lang('company not on project'));
			ajx_current("empty");
			return;
		} // if

		try {

			DB::beginWork();
			$project_company->delete();
			$users = ProjectUsers::getCompanyUsersByProject($company, $project);
			if(is_array($users)) {
				foreach($users as $user) {
					$project_user = ProjectUsers::findById(array('project_id' => $project->getId(), 'user_id' => $user->getId()));
					if($project_user instanceof ProjectUser) $project_user->delete();
				} // foreach
			} // if
			DB::commit();

			flash_success(lang('success remove company from project'));
			ajx_current("reload");

		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error remove company from project'));
			ajx_current("empty");
		} // try
	} // remove_company

	/**
	 * Return the list of projects
	 *
	 */
	function list_projects() {
		ajx_current("empty");
		
		$parent = array_var($_GET, 'parent', 0);
		$all_ws = logged_user()->getWorkspaces(true);
		if (!is_array($all_ws)) $all_ws = array();
		
		$wsset = array();
		foreach ($all_ws as $w) {
			$wsset[$w->getId()] = true;
		}
		$ws = array();
		foreach ($all_ws as $w) {
			$toBeAdded = false;
			if ($w->getParentId() == $parent) {
				$toBeAdded = true;
			} else {//if ($wsset[$w->getParentId()] !== true) {
				$x = $w;
				while ($x instanceof Project && $x->getParentId() != $parent && !isset($wsset[$x->getParentId()])) {
					$x = $x->getParentWorkspace();
				}
				if ($x instanceof Project && $x->getParentId() == $parent) {
					$toBeAdded = true;
				}
			}
			if ($toBeAdded) {
				$workspace = array(
					"id" => $w->getId(),
					"name" => $w->getName(),
					"color" => $w->getColor(),
					"parent" => $parent,
					"realParent" => $w->getParentId()
				);
				if (logged_user()->getPersonalProjectId() == $w->getId())
					$workspace["isPersonal"] = true;
				$ws[] = $workspace;
			}
		}
		
		ajx_extra_data(array('workspaces' => $ws));
	}
	
	function get_workspace_info(Project $workspace, $defaultParent = 0, $all_ws = null){
		$parent = $defaultParent;
		if (!$all_ws)
			$all_ws = logged_user()->getWorkspaces(true);
			
		if (!is_array($all_ws)) 
			$all_ws = array();
		
		$wsset = array();
		foreach ($all_ws as $w) {
			$wsset[$w->getId()] = true;
		}
		$tempParent = $workspace->getParentId();
		$x = $workspace;
		while ($x instanceof Project && !isset($wsset[$tempParent])) {
			$tempParent = $x->getParentId();
			$x = $x->getParentWorkspace();
		}
		if (!$x instanceof Project) {
			$tempParent = 0;
		}
		$workspace_info = array(
			"id" => $workspace->getId(),
			"name" => $workspace->getName(),
			"color" => $workspace->getColor(),
			"parent" => $tempParent,
			"realParent" => $workspace->getParentId(),
			"depth" => $workspace->getDepth()
		);
		if (logged_user()->getPersonalProjectId() == $workspace->getId())
			$workspace_info["isPersonal"] = true;
			
		return $workspace_info;
	}
	
	function initial_list_projects() {
		ajx_current("empty");
		
		$parent = 0;
		$all_ws = logged_user()->getWorkspaces(true);
		if (!is_array($all_ws)) $all_ws = array();
		
		$wsset = array();
		foreach ($all_ws as $w) {
			$wsset[$w->getId()] = true;
		}
		$ws = array();
		foreach ($all_ws as $w) {
			$tempParent = $w->getParentId();
			$x = $w;
			while ($x instanceof Project && !isset($wsset[$tempParent])) {
				$tempParent = $x->getParentId();
				$x = $x->getParentWorkspace();
			}
			if (!$x instanceof Project) {
				$tempParent = 0;
			}
			$workspace = array(
				"id" => $w->getId(),
				"name" => $w->getName(),
				"color" => $w->getColor(),
				"parent" => $tempParent,
				"realParent" => $w->getParentId(),
				"depth" => $w->getDepth()
			);
			if (logged_user()->getPersonalProjectId() == $w->getId())
				$workspace["isPersonal"] = true;
			$ws[] = $workspace;
				
		}
		
		ajx_extra_data(array('workspaces' => $ws));
	}
	
	function move() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		ajx_current("empty");
		$id = get_id();
		$to = array_var($_GET, 'to', 0);
		// TODO: check permissions
		$ws = Projects::findById($id);
		$parent = Projects::findById($to);
		if (isset($ws)) {
			if ($to == 0 || isset($parent)) {
				$ws->setParentId($to);
				$ws->save();
				evt_add('workspace_edited', array(
					"is" => $ws->getId(),
					"name" => $ws->getId(),
					"color" => $ws->getId(),
					"parent" => $ws->getParentId()
				));
			}
		}
	}
} // ProjectController

?>