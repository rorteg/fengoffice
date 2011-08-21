<?php

/**
 * Project class
 * Generated on Sun, 26 Feb 2006 23:10:34 +0100 by DataObject generation tool
 *
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class Project extends BaseProject {
	/**
	 * Cache of all project roles
	 *
	 * @var array
	 */
	private $all_roles;
	/**
	 * Cache of all project webpages
	 *
	 * @var array
	 */
	private $all_webpages;
	
	private $all_events;
	
	private $all_timeslots;
	
	private $parent_project;

	
	/**
	 * Projects are searchable
	 *
	 * @var boolean
	 */
	protected $is_searchable = true;

	/**
	 * Array of searchable columns
	 *
	 * @var array
	 */
	protected $searchable_columns = array('name', 'description');
	
	private $billing_values;
	
	// ---------------------------------------------------
	//  Parent workspace management
	// ---------------------------------------------------
	
	private $depth = 0;
	
	function getPID($i){
		switch ($i){
			case 1: return $this->getP1();
			case 2: return $this->getP2();
			case 3: return $this->getP3();
			case 4: return $this->getP4();
			case 5: return $this->getP5();
			case 6: return $this->getP6();
			case 7: return $this->getP7();
			case 8: return $this->getP8();
			case 9: return $this->getP9();
			case 10: return $this->getP10();
			default: return 0;
		}
	}
	
	function setPID($i, $workspace_id){
		switch ($i){
			case 1: $this->setP1($workspace_id); break;
			case 2: $this->setP2($workspace_id); break;
			case 3: $this->setP3($workspace_id); break;
			case 4: $this->setP4($workspace_id); break;
			case 5: $this->setP5($workspace_id); break;
			case 6: $this->setP6($workspace_id); break;
			case 7: $this->setP7($workspace_id); break;
			case 8: $this->setP8($workspace_id); break;
			case 9: $this->setP9($workspace_id); break;
			case 10: $this->setP10($workspace_id); break;
		}
	}
	
	function isParentOf($workspace) {
		if (!$workspace instanceof Project) {
			return false;
		}
		$depth = $this->getDepth();
	 	return $workspace->getPID($depth) == $this->getId();
	}
	
	function getParentIds($include_self = false){
		$result = array();
		for ($i = 1; $i <= 10; $i++){
			if ($this->getPID($i) != $this->getId())
				$result[$i] = $this->getPID($i);
			else{
				if ($include_self)
					$result[$i] = $this->getPID($i);
				break;
			}
		}
		return $result;
	}
	
	function getDepth(){
		if ($this->depth == 0){
			$this->depth = 10;
			for ($i = 1; $i <= 10; $i++){
				if ($this->getPID($i) == $this->getId()){
					$this->depth = $i;
					break;
				}
			}
		}
		return $this->depth;
	}
	
	function getMaxBranchDepth(){
		$subs = $this->getSubWorkspaces();
		$result = $this->getDepth();
		foreach ($subs as $sub)
			if ($sub->getDepth() > $result)
				$result = $sub->getDepth();
				
		return $result;
	}
	

	function getPath(){
		$path = $this->getName();
		if ($this->getParentWorkspace() instanceof Project){
			$path = $this->getParentWorkspace()->getPath() . ' / ' . $path;
		}
		
		return $path;
	}

	function unarchive() {
		$this->open();
	}
	
	function open() {
		if(!$this->canChangeStatus(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		try {
			$this->setCompletedOn(EMPTY_DATETIME);
			$this->setCompletedById(0);

			DB::beginWork();
			$this->save();
			ApplicationLogs::createLog($this, null, ApplicationLogs::ACTION_UNARCHIVE);
			DB::commit();

			flash_success(lang('success unarchive objects', 1));
			
			evt_add("workspace added", array(
				"id" => $this->getId(),
				"name" => $this->getName(),
				"color" => $this->getColor(),
				"parent" => $this->getParentId()
			));
			
			ajx_current("reload");
		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error unarchive objects'));
			ajx_current("empty");
		} // try
	}
	
	function archive() {
		$this->complete();
	}
	
	function complete() {
		if(!$this->canChangeStatus(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		try {
			$this->setCompletedOn(DateTimeValueLib::now());
			$this->setCompletedById(logged_user()->getId());

			DB::beginWork();
			$this->save();
			ApplicationLogs::createLog($this, null, ApplicationLogs::ACTION_ARCHIVE);
			DB::commit();

			flash_success(lang('success archive objects', 1));
			evt_add("workspace deleted", array(
				"id" => $this->getId(),
				"name" => $this->getName()
			));
			ajx_current("reload");
		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error archive objects'));
			ajx_current("empty");
		} // try
	}
	
	function getDashboardObject(){
    	$updated_by_name = $this->getUpdatedByDisplayName();
		$updated_on = $this->getObjectUpdateTime() instanceof DateTimeValue ? ($this->getObjectUpdateTime()->isToday() ? format_time($this->getObjectUpdateTime()) : format_datetime($this->getObjectUpdateTime())) : lang('n/a');	
    	
		$created_by_name = $this->getCreatedByDisplayName();
		$created_on = $this->getObjectCreationTime() instanceof DateTimeValue ? ($this->getObjectCreationTime()->isToday() ? format_time($this->getObjectCreationTime()) : format_datetime($this->getObjectCreationTime())) : lang('n/a');
    	
    	$archivedOn = $this->getCompletedOn() instanceof DateTimeValue ? ($this->getCompletedOn()->isToday() ? format_time($this->getCompletedOn()) : format_datetime($this->getCompletedOn(), 'M j')) : lang('n/a');
		$archivedBy = $this->getCompletedByDisplayName();
    		
    	return array(
				"id" => $this->getObjectTypeName() . $this->getId(),
				"object_id" => $this->getId(),
				"name" => $this->getObjectName(),
				"type" => $this->getObjectTypeName(),
				"tags" => '',
				"createdBy" => $created_by_name,
				"createdById" => $this->getCreatedById(),
    			"dateCreated" => $created_on,
				"updatedBy" => $updated_by_name,
				"updatedById" => $this->getUpdatedById(),
				"dateUpdated" => $updated_on,
				"wsIds" => '',
				"url" => $this->getEditUrl(),
				"manager" => get_class($this->manager()),
    			"deletedById" => 0,
    			"deletedBy" => lang("n/a"),
    			"dateDeleted" => lang("n/a"),
    			"archivedById" => $this->getCompletedById(),
    			"archivedBy" => $archivedBy,
    			"dateArchived" => $archivedOn
			);
    }
	
	
	// ---------------------------------------------------
	//  Workspace Hierarchy
	// ---------------------------------------------------
	
	/**
	 * Returns true if the workspace has a Parent workspace
	 *
	 * @return boolean
	 */
	function hasParentWorkspace() {
		return $this->getDepth() > 1;
	}
	
	/**
	 * Returns the parent workspace or null if there isn't one
	 * @return Project
	 */
	function getParentWorkspace() {
		if (!isset($this->parent_project)){
			if ($this->getParentId() != 0) 
				 $this->parent_project = Projects::findById($this->getParentId());
		}
		return $this->parent_project;
	}
	
	function getParentId(){
		if (!$this->hasParentWorkspace()) 
			return 0;
		else
			return $this->getPID($this->getDepth() - 1);
	}
	
	function canSetAsParentWorkspace(Project $workspace){
		if ($workspace->getDepth() < $this->getDepth())
			return true;
		else {
			
			if($this->isParentOf($workspace))
				return false; 

			return (($workspace->getDepth() - $this->getDepth() + $this->getMaxBranchDepth()) <= 10);
		}
	}
	
	function setParentWorkspace($workspace = null) {
		$oldlevel = $this->getDepth();
		$subs = $this->getSubWorkspaces();
		
		$this->initializeParents();
		
		if ($workspace instanceof Project){
			for ($i = 1; $i < $workspace->getDepth(); $i++)
				$this->setPID($i,$workspace->getPID($i));
			
			$this->setPID($workspace->getDepth(),$workspace->getId());
			$this->setPID($workspace->getDepth() + 1, $this->getId());
		}
		else
			$this->setPID(1, $this->getId());
		$this->depth = 0; //Initialize depth
		
		if (is_array($subs) && count($subs) > 0){
			foreach ($subs as $sub){
				$sub->setNewParentIds($oldlevel,$workspace);
				$sub->save();
			}
		}
	}
	
	private function setNewParentIds($oldlevel, $newParent = null){
		$oldparents = $this->getParentIds();
		
		$this->initializeParents();
		if ($newParent){
			$newparents = $newParent->getParentIds();
			$c = $newParent->getDepth();
			$newparents[$c] = $newParent->getId();
		} else {
			$newparents = array();
			$c = 0;
		}
		for ($i = $oldlevel; $i <= count($oldparents); $i++){
			$c++;
			$newparents[$c] = $oldparents[$i];
		}
		$c++;
		$newparents[$c] = $this->getId();
		for ($i = 1; $i <= count($newparents); $i++)
			$this->setPID($i,$newparents[$i]);
		$this->depth = count($newparents);
	}
	
	private function initializeParents() {
		for ($i = 1; $i <= 10; $i++)
			$this->setPID($i,0);
	}
	
	/**
	 * Returns all workspaces that have this Workspace as their parent
	 * @return array
	 */
	function getSubWorkspaces($active = false, $user = null, $allLevels = true) {
		$key = ($active?"active":"closed")."_".($user instanceof User?$user->getId():0)."_".($allLevels?'all':'single');
		if (!(array_var($this->subWScache, $key))) {
			$depth = $this->getDepth();
			$conditions = array("id != " . $this->getId() . " AND `p" . $depth . "` = ?", $this->getId());
			if (!$allLevels && $depth < 9)
				$conditions[0] .= ' AND `p' . ($depth + 2) .'` = 0 ';
			if ($active) {
				$conditions[0] .= ' AND `completed_on` = ? ';
				$conditions[] = EMPTY_DATETIME;
			}
			if ($user instanceof User) {
				$pu_tbl = ProjectUsers::instance()->getTableName(true);
				$conditions[0] .= " AND `id` IN (SELECT `project_id` FROM $pu_tbl WHERE `user_id` = ?)";
				$conditions[] = $user->getId();
			}
			$this->subWScache[$key] = Projects::findAll(array('conditions' => $conditions));
		}
		return $this->subWScache[$key];
	}
	/**
	 * Returns all descendence order by name and depth
	 * @param $user the logged user
	 * @return Array of workspaces.
	 */
	function getSortedChildren($user) {
		$projects = null;
		$padres = $this->getSubWorkspacesSorted(false, $user);
		if (is_array($padres)) {
			foreach($padres as $hijo){
				$projects[] = $hijo;
				$aux = $hijo->getSortedChildren($user);
				if (is_array($aux)){
					foreach($aux as $a){$projects[] = $a;}
				}
			}
		}
		return $projects; 
	}
	/**
	 * returns first level child workspaces sorted by name
	 * @param $active
	 * @param $user
	 * @param $allLevels
	 * @return unknown_type
	 */
	function getSubWorkspacesSorted($active = false, $user = null) {
		 $allLevels = false;
		$key = ($active?"active":"closed")."_".($user instanceof User?$user->getId():0)."_".($allLevels?'all':'single');
		if (!(array_var($this->subWScache, $key))) {
			$depth = $this->getDepth();
			$conditions = array("id != " . $this->getId() . " AND `p" . $depth . "` = ?", $this->getId());
			if (!$allLevels && $depth < 9)
				$conditions[0] .= ' AND `p' . ($depth + 2) .'` = 0 ';
			if ($active) {
				$conditions[0] .= ' AND `completed_on` = ? ';
				$conditions[] = EMPTY_DATETIME;
			}
			if ($user instanceof User && !can_manage_workspaces($user)) {
				$pu_tbl = ProjectUsers::instance()->getTableName(true);
				$conditions[0] .= " AND `id` IN (SELECT `project_id` FROM $pu_tbl WHERE `user_id` = ?)";
				$conditions[] = $user->getId();
			}
			$this->subWScache[$key] = Projects::findAll(array('conditions' => $conditions,'order'=>'name'));
		}
		return $this->subWScache[$key];
	}
	
	function getAllSubWorkspacesCSV($active = false, $user = null) {
		$key = ($active?"active":"closed")."_".($user instanceof User?$user->getId():0);
		
		if (!(array_var($this->sub_ws_ids, $key))){
			$csv = "" . $this->getId();
			$subs = $this->getSubWorkspaces($active, $user);
			if (is_array($subs) && count($subs) > 0)
				foreach ($subs as $sub)
					$csv .= ', ' . $sub->getId();
			$this->sub_ws_ids[$key] = $csv;
		}
		return $this->sub_ws_ids[$key];
	}
	
	function getAllSubWorkspacesQuery($active = true, $user = null, $additional_conditions = null) {
		
		$addcond = $additional_conditions ==null ? "" : "AND ".$additional_conditions;
		$id = $this->getId();
		$table = $this->getTableName(true);
		$condition = "(`p1` = $id OR `p2` = $id OR `p3` = $id OR `p4` = $id OR `p5` = $id OR `p6` = $id OR `p7` = $id OR `p8` = $id OR `p9` = $id OR `p10` = $id)";
		if ($user instanceof User) {
			$pu_tbl = ProjectUsers::instance()->getTableName(true);
			$uquery = $user->getWorkspacesQuery();
			$condition .= " AND `id` IN ($uquery) $addcond";
		}
		if ($active !== null) {
			if ($active) {
				$condition .= " AND `completed_on` = " . DB::escape(EMPTY_DATETIME);
			} else {
				$condition .= " AND `completed_on` <> " . DB::escape(EMPTY_DATETIME);
			}
		}
		$query = "SELECT `id` FROM $table WHERE $condition";
		return $query;
	}
	
	
	// ---------------------------------------------------
	//  Messages
	// ---------------------------------------------------

	/**
	 * Cache of all messages
	 *
	 * @var array
	 */
	private $all_messages;

	/**
	 * Cache of all mails
	 *
	 * @var array
	 */
	private $all_mails;

	/**
	 * Cached array of messages that user can access. If user is member of owner company
	 * $all_messages will be used (members of owner company can browse all messages)
	 *
	 * @var array
	 */
	private $messages;

	/**
	 * Array of all important messages (incliduing private ones)
	 *
	 * @var array
	 */
	private $all_important_messages;

	/**
	 * Array of important messages. If user is not member of owner company private
	 * messages will be skipped
	 *
	 * @var array
	 */
	private $important_messages;

	// ---------------------------------------------------
	//  Milestones
	// ---------------------------------------------------

	/**
	 * Cached array of milestones. This is array of all project milestones. They are not
	 * filtered by is_private stamp
	 *
	 * @var array
	 */
	private $all_milestones;

	/**
	 * Cached array of project milestones
	 *
	 * @var array
	 */
	private $milestones;

	/**
	 * Array of all open milestones in this projects
	 *
	 * @var array
	 */
	private $all_open_milestones;

	/**
	 * Array of open milestones in this projects that user can access. If user is not member of owner
	 * company private milestones will be hidden
	 *
	 * @var array
	 */
	private $open_milestones;

	/**
	 * Cached array of late milestones. This variable is populated by splitOpenMilestones() private
	 * function on request
	 *
	 * @var array
	 */
	private $late_milestones = false;

	/**
	 * Cached array of today milestones. This variable is populated by splitOpenMilestones() private
	 * function on request
	 *
	 * @var array
	 */
	private $today_milestones = false;

	/**
	 * Cached array of upcoming milestones. This variable is populated by splitOpenMilestones() private
	 * function on request
	 *
	 * @var array
	 */
	private $upcoming_milestones = false;

	/**
	 * Cached all completed milestones
	 *
	 * @var array
	 */
	private $all_completed_milestones;

	/**
	 * Cached array of completed milestones - is_private check is made before retriving meaning that if
	 * user is no member of owner company all private data will be hiddenas
	 *
	 * @var array
	 */
	private $completed_milestones;

	// ---------------------------------------------------
	//  Task lists
	// ---------------------------------------------------

	/**
	 * All task lists in this project
	 *
	 * @var array
	 */
	private $all_task_lists;

	/**
	 * Array of all task lists. If user is not member of owner company private task
	 * lists will be excluded from the list
	 *
	 * @var array
	 */
	private $task_lists;

	/**
	 * All open task lists in this project
	 *
	 * @var array
	 */
	private $all_open_task_lists;

	/**
	 * Array of open task lists. If user is not member of owner company private task
	 * lists will be excluded from the list
	 *
	 * @var array
	 */
	private $open_task_lists;

	/**
	 * Array of all completed task lists in this project
	 *
	 * @var array
	 */
	private $all_completed_task_lists;

	/**
	 * Array of completed task lists. If user is not member of owner company private task
	 * lists will be excluded from the list
	 *
	 * @var array
	 */
	private $completed_task_lists;

	// ---------------------------------------------------
	//  Tags
	// ---------------------------------------------------

	/**
	 * Cached object tag names
	 *
	 * @var array
	 */
	private $tag_names;
	
	// ---------------------------------------------------
	//  Billing
	// ---------------------------------------------------
	
	function getBillingAmount($billing_category_id){
		if (!is_numeric($billing_category_id)) $billing_category_id = 0;
		$wsBilling = WorkspaceBillings::findOne(
			array('conditions' => array(
				'project_id = ? AND billing_id = ?',
				$this->getId(),
				$billing_category_id
			))
		);
		if ($wsBilling)
			return $wsBilling->getValue();
		else {
			$parent = $this->getParentWorkspace();
			if ($parent instanceof Project){
				return $parent->getBillingAmount($billing_category_id);
			} else {
				$billing_category = BillingCategories::findById($billing_category_id);
				if ($billing_category instanceof BillingCategory){
					return $billing_category->getDefaultValue();
				} else return 0;
			}
		}
	}

	/**
	 * Returns an array with a list of values and information about where they were obtained from
	 *
	 * @param array $billing_category_ids
	 */
	function getBillingAmounts($billing_categories = null){
		if(!$billing_categories){
			$billing_categories = BillingCategories::findAll();
		}
		
		if ($billing_categories && count($billing_categories) > 0){
			$result = array();
			$billing_category_ids = array();
			foreach ($billing_categories as $category)
				$billing_category_ids[] = $category->getId();
			
			$wsBillingCategories = WorkspaceBillings::findAll(
				array('conditions' => 'project_id = ' . $this->getId() . ' and billing_id in (' . implode(',',$billing_category_ids) . ')'));
			if ($wsBillingCategories){
				foreach ($wsBillingCategories as $wsCategory){
					for ($i = 0; $i < count($billing_categories); $i++){
						if ($billing_categories[$i]->getId() == $wsCategory->getBillingId()){
							$result[] = array('category' => $billing_categories[$i], 'value' => $wsCategory->getValue(), 'origin' => $this->getId());
							array_splice($billing_categories,$i,1);
							array_splice($billing_category_ids,$i,1);
							break;
						}
					}
				}
			}
			if (count($billing_categories) > 0){
				if ($this->getParentWorkspace() instanceof Project){
					$resultToConcat = $this->getParentWorkspace()->getBillingAmounts($billing_categories);
					foreach ($resultToConcat as $resultValue){
						$result[] = array('category' => $resultValue['category'], 
							'value' => $resultValue['value'], 
							'origin' => (($resultValue['origin'] == 'default')? 'default':'inherited'));
					}
				} else {
					foreach ($billing_categories as $category){
						$result[] = array('category' => $category,'value' => $category->getDefaultValue(), 'origin' => 'default');
					}
				}
			}
			return $result;
		} else return null;
	}
	
	function getBillingTotal(User $user){
		//$project_ids = $this->getAllSubWorkspacesQuery($user);
		
		$user_cond = '';
		if (isset($user_id))
			$user_cond = ' AND timeslots.user_id = ' . $user_id;
		
		$row = DB::executeOne('SELECT SUM(timeslots.fixed_billing) as total_billing from ' . Timeslots::instance()->getTableName() . ' as timeslots, ' . ProjectTasks::instance()->getTableName() .
			' as tasks WHERE ((' . ProjectTasks::getWorkspaceString($this->getId()) . ' AND timeslots.object_id = tasks.id AND timeslots.object_manager = \'ProjectTasks\')' .
			' OR (timeslots.object_manager = \'Project\' AND timeslots.object_id = ' . $this->getId() . '))' . $user_cond);
		
		return array_var($row, 'total_billing', 0);
	}
	
	function getBillingTotalByUsers(User $user, $user_id = null){
		//$project_ids = $this->getAllSubWorkspacesQuery($user);
		
		$user_cond = '';
		if (isset($user_id))
			$user_cond = ' AND timeslots.user_id = ' . $user_id;

		$rows = DB::executeAll('SELECT SUM(timeslots.fixed_billing) as total_billing, timeslots.user_id as user from ' . Timeslots::instance()->getTableName() . ' as timeslots, ' . ProjectTasks::instance()->getTableName() .
			' as tasks WHERE ((tasks.' . trim(ProjectTasks::getWorkspaceString($this->getId())) . ' AND timeslots.object_id = tasks.id AND timeslots.object_manager = \'ProjectTasks\')' .
			' OR (timeslots.object_manager = \'Project\' AND timeslots.object_id = ' . $this->getId() . '))' . $user_cond . ' GROUP BY user');
		
		if(!is_array($rows) || !count($rows)) 
			return null;
		else{
			for ($i = 0; $i < count($rows); $i++){
				if ($rows[$i]['total_billing'] == 0)
					unset($rows[$i]);
			}
			return $rows;
		}
	}

	// ---------------------------------------------------
	//  Log
	// ---------------------------------------------------

	/**
	 * Cache of all project logs
	 *
	 * @var array
	 */
	private $all_project_logs;

	/**
	 * Cache of all project logs that current user can access
	 *
	 * @var array
	 */
	private $project_logs;

	// ---------------------------------------------------
	//  Forms
	// ---------------------------------------------------

	/**
	 * Cache of all project forms
	 *
	 * @var array
	 */
	private $all_forms;

	// ---------------------------------------------------
	//  Files
	// ---------------------------------------------------

	/**
	 * Cached array of project folders
	 *
	 * @var array
	 */
	private $folders;

	/**
	 * Cached array of all important files
	 *
	 * @var array
	 */
	private $all_important_files;

	/**
	 * Important files filtered by the users access permissions
	 *
	 * @var array
	 */
	private $important_files;

	/**
	 * All orphened files, user permissions are not checked
	 *
	 * @var array
	 */
	private $all_orphaned_files;

	/**
	 * Orphaned file
	 *
	 * @var array
	 */
	private $orphaned_files;

	private $subWScache = array();
	
	private $sub_ws_ids = array();
	
	
	
	// ---------------------------------------------------
	//  Messages
	// ---------------------------------------------------

	/**
	 * This function will return all messages in project and it will not exclude private
	 * messages if logged user is not member of owner company
	 *
	 * @param void
	 * @return array
	 */
	function getAllMessages() {
		if(is_null($this->all_messages)) {
			$this->all_messages = ProjectMessages::getProjectMessages($this, true);
		} // if
		return $this->all_messages;
	} // getAllMessages

	/**
	 * This function will return all mails in project
	 *
	 * @param void
	 * @return array
	 */
	function getAllMails() {
		if(is_null($this->all_mails)) {
			$this->all_mails = MailContents::getProjectMails($this);
		} // if
		return $this->all_mails;
	} //  getAllMails

	/**
	 * Return only the messages that current user can see (if not member of owner company private
	 * messages will be excluded)
	 *
	 * @param null
	 * @return null
	 */
	function getMessages() {
		if(logged_user()->isMemberOfOwnerCompany()) {
			return $this->getAllMessages(); // members of owner company can view all messages
		} // if

		if(is_null($this->messages)) {
			$this->messages = ProjectMessages::getProjectMessages($this, false);
		} // if
		return $this->messages;
	} // getMessages

	/**
	 * Return all important messages
	 *
	 * @param void
	 * @return array
	 */
	function getAllImportantMessages() {
		if(is_null($this->all_important_messages)) {
			$this->all_important_messages = ProjectMessages::getImportantProjectMessages($this, true);
		} // if
		return $this->all_important_messages;
	} // getAllImportantMessages

	/**
	 * Return array of important messages
	 *
	 * @param void
	 * @return array
	 */
	function getImportantMessages() {
		if(logged_user()->isMemberOfOwnerCompany()) {
			return $this->getAllImportantMessages();
		} // if

		if(is_null($this->important_messages)) {
			$this->important_messages = ProjectMessages::getImportantProjectMessages($this, false);
		} // if
		return $this->important_messages;
	} // getImportantMessages

	// ---------------------------------------------------
	//  Milestones
	// ---------------------------------------------------

	/**
	 * Return all milestones, don't filter them by is_private stamp based on users permissions
	 *
	 * @param void
	 * @return array
	 */
	function getAllMilestones() {
		if(is_null($this->all_milestones)) {
			$this->all_milestones = ProjectMilestones::findAll(array(
          'conditions' => array(ProjectTasks::getWorkspaceString(), $this->getId()),
          'order' => 'due_date'
          )); // findAll
		} // if
		return $this->all_milestones;
	} // getAllMilestones

	/**
	 * Return all project milestones
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getMilestones() {
		if(logged_user()->isMemberOfOwnerCompany()) return $this->getAllMilestones(); // member of owner company
		if(is_null($this->milestones)) {
			$this->milestones = ProjectMilestones::findAll(array(
          'conditions' => array(ProjectTasks::getWorkspaceString().' AND `is_private` = ?', $this->getId(), 0),
          'order' => 'due_date'
          )); // findAll
		} // if
		return $this->milestones;
	} // getMilestones

	/**
	 * Return all opet milestones without is_private check
	 *
	 * @param void
	 * @return array
	 */
	function getAllOpenMilestones() {
		if(is_null($this->all_open_milestones)) {
			$this->all_open_milestones = ProjectMilestones::findAll(array(
          'conditions' => array(ProjectMilestones::getWorkspaceString($this->getParentIds(true)).' AND `completed_on` = ?', EMPTY_DATETIME),
          'order' => 'due_date'
          )); // findAll
		} // if
		return $this->all_open_milestones;
	} // getAllOpenMilestones

	/**
	 * Return open milestones
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getOpenMilestones() {
		if(logged_user()->isMemberOfOwnerCompany()) return $this->getAllOpenMilestones();
		if(is_null($this->open_milestones)) {
			$this->open_milestones = ProjectMilestones::findAll(array(
          'conditions' => array(ProjectMilestones::getWorkspaceString($this->getParentIds(true)).' AND `completed_on` = ? AND `is_private` = ?', EMPTY_DATETIME, 0),
          'order' => 'due_date'
          )); // findAll
		} // if
		return $this->open_milestones;
	} // getOpenMilestones

	/**
	 * This function will return all completed milestones
	 *
	 * @param void
	 * @return array
	 */
	function getAllCompletedMilestones() {
		if(is_null($this->all_completed_milestones)) {
			$this->all_completed_milestones = ProjectMilestones::findAll(array(
          'conditions' => array(ProjectTasks::getWorkspaceString().' AND `completed_on` > ?', $this->getId(), EMPTY_DATETIME),
          'order' => 'due_date'
          )); // findAll
		} // if
		return $this->all_completed_milestones;
	} // getAllCompletedMilestones

	/**
	 * Return completed milestones
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getCompletedMilestones() {
		if(logged_user()->isMemberOfOwnerCompany()) return $this->getAllCompletedMilestones();
		if(is_null($this->completed_milestones)) {
			$this->completed_milestones = ProjectMilestones::findAll(array(
          'conditions' => array(ProjectTasks::getWorkspaceString().' AND `completed_on` > ? AND `is_private` = ?', $this->getId(), EMPTY_DATETIME, 0),
          'order' => 'due_date'
          )); // findAll
		} // if
		return $this->completed_milestones;
	} // getCompletedMilestones

	/**
	 * Return array of late open milestones
	 *
	 * @param void
	 * @return array
	 */
	function getLateMilestones() {
		if($this->late_milestones === false) $this->splitOpenMilestones();
		return $this->late_milestones;
	} // getLateMilestones

	/**
	 * Return array of today open milestones
	 *
	 * @param void
	 * @return array
	 */
	function getTodayMilestones() {
		if($this->today_milestones === false) $this->splitOpenMilestones();
		return $this->today_milestones;
	} // getTodayMilestones

	/**
	 * Return array of upcoming open milestones
	 *
	 * @param void
	 * @return array
	 */
	function getUpcomingMilestones() {
		if($this->upcoming_milestones === false) $this->splitOpenMilestones();
		return $this->upcoming_milestones;
	} // getUpcomingMilestones

	/**
	 * This function will walk through open milestones array and splid them into late, today and upcomming
	 *
	 * @param void
	 * @return array
	 */
	private function splitOpenMilestones() {
		$open_milestones = $this->getOpenMilestones();

		// Reset from false
		$this->late_milestones = null;
		$this->today_milestones = null;
		$this->upcoming_milestones = null;

		if(is_array($open_milestones)) {
			foreach($open_milestones as $open_milestone) {
				if($open_milestone->isLate()) {
					if(!is_array($this->late_milestones)) $this->late_milestones = array();
					$this->late_milestones[] = $open_milestone;
				} elseif($open_milestone->isToday()) {
					if(!is_array($this->today_milestones)) $this->today_milestones = array();
					$this->today_milestones[] = $open_milestone;
				} else {
					if(!is_array($this->upcoming_milestones)) $this->upcoming_milestones = array();
					$this->upcoming_milestones[] = $open_milestone;
				} // if
			} // foreach
		} // if
	} // splitOpenMilestones

	// ---------------------------------------------------
	//  Task lists
	// ---------------------------------------------------

	/**
	 * Return all task lists
	 *
	 * @param void
	 * @return array
	 */
	function getAllTasks() {
		if(is_null($this->all_task_lists)) {
			$this->all_task_lists = ProjectTasks::findAll(array(
          'conditions' => array(ProjectTasks::getWorkspaceString(), $this->getId()),
          'order' => '`order`'
          )); // findAll
		} // if
		return $this->all_task_lists;
	} // getAllTasks

	/**
	 * Return all taks lists
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getTasks() {
		if(logged_user()->isMemberOfOwnerCompany()) return $this->getAllTasks();
		if(is_null($this->task_lists)) {
			$this->task_lists = ProjectTasks::findAll(array(
          'conditions' => array(ProjectTasks::getWorkspaceString().' AND `is_private` = ?', $this->getId(), 0),
          'order' => '`order`'
          )); // findAll
		} // if
		return $this->task_lists;
	} // getTasks

	/**
	 * Return all open task lists from this project
	 *
	 * @param void
	 * @return array
	 */
	function getAllOpenTasks() {
		if(is_null($this->all_open_task_lists)) {
			$this->all_open_task_lists = ProjectTasks::findAll(array(
          'conditions' => array(ProjectTasks::getWorkspaceString().' AND `completed_on` = ? AND (`parent_id` = 0 OR `parent_id` is NULL )', $this->getId(), EMPTY_DATETIME),
          'order' => '`order`'
          )); // findAll
		} // if
		return $this->all_open_task_lists;
	} // getAllOpenTasks

	/**
	 * Return open task lists
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getOpenTasks() {
		if(logged_user()->isMemberOfOwnerCompany()) return $this->getAllOpenTasks();
		if(is_null($this->open_task_lists)) {
			$this->open_task_lists = ProjectTasks::findAll(array(
          'conditions' => array(ProjectTasks::getWorkspaceString().' AND `completed_on` = ? AND `is_private` = ? AND (`parent_id` = 0 OR `parent_id` is NULL )', $this->getId(), EMPTY_DATETIME, 0),
          'order' => '`order`'
          )); // findAll
		} // if
		$this->open_task_lists = null;
		return $this->open_task_lists;
	} // getOpenTasks


	/**
	 * Return all completed task lists
	 *
	 * @param void
	 * @return array
	 */
	function getAllCompletedTasks() {
		if(is_null($this->all_completed_task_lists)) {
			$this->all_completed_task_lists = ProjectTasks::findAll(array(
          'conditions' => array(ProjectTasks::getWorkspaceString().' AND `completed_on` > ?', $this->getId(), EMPTY_DATETIME),
          'order' => '`order`'
          )); // findAll
		} // if
		return $this->all_completed_task_lists;
	} // getAllCompletedTasks

	/**
	 * Return completed task lists
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getCompletedTasks() {
		if(logged_user()->isMemberOfOwnerCompany()) return $this->getAllCompletedTasks();
		if(is_null($this->completed_task_lists)) {
			$this->completed_task_lists = ProjectTasks::findAll(array(
          'conditions' => array(ProjectTasks::getWorkspaceString().' AND `completed_on` > ? AND `is_private` = ?', $this->getId(), EMPTY_DATETIME, 0),
          'order' => '`order`'
          )); // findAll
		} // if
		return $this->completed_task_lists;
	} // getCompletedTasks

	// ---------------------------------------------------
	//  Tags
	// ---------------------------------------------------

	/**
	 * This function return associative array of project objects tagged with specific tag. Array has following elements:
	 *
	 *  - messages
	 *  - milestones
	 *  - tast lists
	 *  - files
	 *
	 * @access public
	 * @param string $tag
	 * @return array
	 */
	function getObjectsByTag($tag) {
		$exclude_private = !logged_user()->isMemberOfOwnerCompany();
		return array(
        'messages'   => Tags::getObjects($this, $tag, 'ProjectMessages', $exclude_private),
        'milestones' => Tags::getObjects($this, $tag, 'ProjectMilestones', $exclude_private),
        'task_lists' => Tags::getObjects($this, $tag, 'ProjectTasks', $exclude_private),
        'files'      => Tags::getObjects($this, $tag, 'ProjectFiles', $exclude_private),
        'contacts'   => Tags::getObjects($this, $tag, 'ProjectContacts', $exclude_private),
        'webpages'   => Tags::getObjects($this, $tag, 'ProjectWebpages', $exclude_private),
		); // array
	} // getObjectsByTag

	/**
	 * Return number of project objects tagged with $tag
	 *
	 * @param string $tag
	 * @return integer
	 */
	function countObjectsByTag($tag) {
		$exclude_private = !logged_user()->isMemberOfOwnerCompany();
		return Tags::countObjectsByTag($tag, $exclude_private);
	} // countObjectsByTag

	// ---------------------------------------------------
	//  Project log
	// ---------------------------------------------------

	/**
	 * Return full project log
	 *
	 * @param integer $limit
	 * @param integer $offset
	 * @return array
	 */
	function getFullProjectLog($limit = null, $offset = null) {
		return ApplicationLogs::getProjectLogs($this, true, true, $limit, $offset);
	} // getFullProjectLog

	/**
	 * Return all project log entries that this user can see
	 *
	 * @param integer $limit Number of logs that will be returned
	 * @param integer $offset Return from this record
	 * @return array
	 */
	function getProjectLog($limit = null, $offset = null) {
		$include_private = logged_user()->isMemberOfOwnerCompany();
		$include_silent = logged_user()->isAdministrator();

		return ApplicationLogs::getProjectLogs($this, $include_private, $include_silent, $limit, $offset);
	} // getProjectLog

	/**
	 * Return number of logs for this project
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function countProjectLogs() {
		return ApplicationLogs::count(array('`project_id` = ?', $this->getId()));
	} // countProjectLogs

	// ---------------------------------------------------
	//  Project forms
	// ---------------------------------------------------

	/**
	 * Return all project forms
	 *
	 * @param void
	 * @return array
	 */
	function getAllForms() {
		if(is_null($this->all_forms)) {
			$this->all_forms = ProjectForms::findAll(array(
          'conditions' => array('`project_id` = ?', $this->getId()),
          'order' => '`order`'
          )); // findAll
		} // if
		return $this->all_forms;
	} // getAllForms

	/**
	 * Return only visible project forms
	 *
	 * @param void
	 * @return null
	 */
	function getVisibleForms($only_enabled = false) {
		$conditions = '`project_id` = ' . DB::escape($this->getId());
		if($only_enabled) {
			$conditions .= ' AND `is_enabled` = ' . DB::escape(true);
		} // if

		return ProjectForms::findAll(array(
        'conditions' => $conditions,
        'order' => '`order`'
        )); // findAll
	} // getVisibleForms

	/**
	 * Return owner company object
	 *
	 * @access public
	 * @param void
	 * @return Company
	 */
	function getCompany() {
		return owner_company();
	} // getCompany

	/**
	 * Get all companies involved in this project
	 *
	 * @access public
	 * @param boolean $include_owner_company Include owner in result
	 * @return array
	 */
	function getCompanies($include_owner_company = true) {
		$result = array();
		if($include_owner_company) $result[] = $this->getCompany();

		$companies = ProjectCompanies::getCompaniesByProject($this);
		if(is_array($companies)) {
			$result = array_merge($result, $companies);
		} // if

		return $result;
	} // getCompanies

	/**
	 * Remove all companies from project
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function clearCompanies() {
		return ProjectCompanies::clearByProject($this);
	} // clearCompanies

	/**
	 * Return all users involved in this project
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getUsers($group_by_company = true) {
		if ($this->isNew()) return array();
		$users = ProjectUsers::getUsersByProject($this);
		if(!is_array($users) || !count($users)) {
			return null;
		} // if

		if($group_by_company) {

			$grouped = array();
			foreach($users as $user) {
				if(!isset($grouped[$user->getCompanyId()]) || !is_array($grouped[$user->getCompanyId()])) {
					$grouped[$user->getCompanyId()] = array();
				} // if
				$grouped[$user->getCompanyId()][] = $user;
			} // foreach
			return $grouped;

		} else {
			return $users;
		} // if
	} // getUsers

	/**
	 * Remove all users from project
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function clearUsers() {
		return ProjectUsers::clearByProject($this);
	} // clearUsers

	/**
	 * Return user who created this milestone
	 *
	 * @access public
	 * @param void
	 * @return User
	 */
	function getCreatedBy() {
		return Users::findById($this->getCreatedById());
	} // getCreatedBy

	/**
	 * Return user who completed this project
	 *
	 * @access public
	 * @param void
	 * @return User
	 */
	function getCompletedBy() {
		return Users::findById($this->getCompletedById());
	} // getCompletedBy

	/**
	 * Return display name of user who completed this project
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getCompletedByDisplayName() {
		$completed_by = $this->getCompletedBy();
		return $completed_by instanceof User ? $completed_by->getDisplayName() : lang('n/a');
	} // getCompletedByDisplayName

	// ---------------------------------------------------
	//  User tasks
	// ---------------------------------------------------

	/**
	 * Return array of milestones that are assigned to specific user or his company
	 *
	 * @param User $user
	 * @return array
	 */
	function getUsersMilestones(User $user) {
		$conditions = DB::prepareString(ProjectTasks::getWorkspaceString().' AND ((`assigned_to_user_id` = ? AND `assigned_to_company_id` = ?) OR (`assigned_to_user_id` = ? AND `assigned_to_company_id` = ?) OR (`assigned_to_user_id` = ? AND `assigned_to_company_id` = ?)) AND `completed_on` = ?', array($this->getId(), $user->getId(), $user->getCompanyId(), 0, $user->getCompanyId(), 0, 0, EMPTY_DATETIME));
		if(!$user->isMemberOfOwnerCompany()) {
			$conditions .= DB::prepareString(' AND `is_private` = ?', array(0));
		} // if
		return ProjectMilestones::findAll(array(
        'conditions' => $conditions,
        'order' => '`due_date`'
        ));
	} // getUsersMilestones

	/**
	 * Return array of task that are assigned to specific user or his company
	 *
	 * @param User $user
	 * @return array
	 */
	function getUsersTasks(User $user) {
		$task_lists = $this->getTasks();
		if(!is_array($task_lists)) {
			return false;
		} // if

		$task_list_ids = array();
		foreach($task_lists as $task_list) {
			if(!$user->isMemberOfOwnerCompany() && $task_list->isPrivate()) {
				continue;
			} // if
			$task_list_ids[] = $task_list->getId();
		} // if

		return ProjectTasks::findAll(array(
        'conditions' => array('`task_list_id` IN (?) AND ((`assigned_to_user_id` = ? AND `assigned_to_company_id` = ?) OR (`assigned_to_user_id` = ? AND `assigned_to_company_id` = ?) OR (`assigned_to_user_id` = ? AND `assigned_to_company_id` = ?)) AND `completed_on` = ?', $task_list_ids, $user->getId(), $user->getCompanyId(), 0, $user->getCompanyId(), 0, 0, EMPTY_DATETIME),
        'order' => '`created_on`'
        )); // findAll
	} // getUsersTasks

	// ---------------------------------------------------
	//  Files
	// ---------------------------------------------------

//	function getFolders() {
//		if(is_null($this->folders)) {
//			$this->folders = ProjectFolders::getProjectFolders($this);
//		} // if
//		return $this->folders;
//	} // getFolders

	/**
	 * Return all important files
	 *
	 * @param void
	 * @return array
	 */
	function getAllImportantFiles() {
		if(is_null($this->all_important_files)) {
			$this->all_important_files = ProjectFiles::getImportantProjectFiles($this, true);
		} // if
		return $this->all_important_files;
	} // getAllImportantFiles

	/**
	 * Return important files
	 *
	 * @param void
	 * @return array
	 */
	function getImportantFiles() {
		if(logged_user()->isMemberOfOwnerCompany()) {
			return $this->getAllImportantFiles();
		} // if

		if(is_null($this->important_files)) {
			$this->important_files = ProjectFiles::getImportantProjectFiles($this, false);
		} // if
		return $this->important_files;
	} // getImportantFiles

	/**
	 * Return all orphaned files
	 *
	 * @param void
	 * @return array
	 */
	function getAllOrphanedFiles() {
		if(is_null($this->all_orphaned_files)) {
			$this->all_orphaned_files = ProjectFiles::getOrphanedFilesByProject($this, true);
		} //
		return $this->all_orphaned_files;
	} // getAllOrphanedFiles

	/**
	 * Return orphaned files
	 *
	 * @param void
	 * @return array
	 */
	function getOrphanedFiles() {
		if(is_null($this->orphaned_files)) {
			$this->orphaned_files = ProjectFiles::getOrphanedFilesByProject($this, logged_user()->isMemberOfOwnerCompany());
		} // if
		return $this->orphaned_files;
	} // getOrphanedFiles

	// ---------------------------------------------------
	//  Status
	// ---------------------------------------------------

	/**
	 * Check if this project is active
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function isActive() {
		return !$this->isCompleted();
	} // isActive

	/**
	 * Check if this project is completed
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function isCompleted() {
		return (boolean) $this->getCompletedOn();
	} // isCompleted

	// ---------------------------------------------------
	//  Permissions
	// ---------------------------------------------------

	/**
	 * Check if user can add project
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function canAdd(User $user) {
		return $user->isAccountOwner() || can_manage_workspaces(logged_user());
	} // canAdd

	/**
	 * Returns true if user can view specific project
	 *
	 * @access public
	 * @param User $user
	 * @return boolean
	 */
	function canView(User $user) {
		return $user->getPersonalProjectId() == $this->getId() || $user->isAccountOwner() || can_manage_workspaces(logged_user());
	} // canView

	/**
	 * Returns true if user can update specific project
	 *
	 * @access public
	 * @param User $user
	 * @return boolean
	 */
	function canEdit(User $user) {
		return $user->getPersonalProjectId() == $this->getId() || $user->isAccountOwner() || can_manage_workspaces(logged_user());
	} // canEdit
	
	/**
	 * Returns true if user can delete specific project
	 *
	 * @access public
	 * @param User $user
	 * @return boolean
	 */
	function canDelete(User $user) {
		return $user->isAccountOwner() || (can_manage_workspaces($user) && $user->isProjectUser($this));
	} // canDelete

	/**
	 * Returns true if user can change status of this project
	 *
	 * @access public
	 * @param User $user
	 * @return boolean
	 */
	function canChangeStatus(User $user) {
		return $this->canEdit($user);
	} // canChangeStatus

	/**
	 * Returns true if user can access permissions page and can update permissions
	 *
	 * @access public
	 * @param User $user
	 * @return boolean
	 */
	function canChangePermissions(User $user) {
		return $user->isAccountOwner() || can_manage_workspaces(logged_user()) || can_manage_security(logged_user());
	} // canChangePermissions

	/**
	 * Check if specific user can remove company from project
	 *
	 * @access public
	 * @param User $user
	 * @param Company $remove_company Remove this company
	 * @return boolean
	 */
	function canRemoveCompanyFromProject(User $user, Company $remove_company) {
		if($remove_company->isOwner()) return false;
		return $user->isAccountOwner() || can_manage_workspaces(logged_user()) || can_manage_security(logged_user());
	} // canRemoveCompanyFromProject

	/**
	 * Check if this user can remove other user from project
	 *
	 * @access public
	 * @param User $user
	 * @param User $remove_user User that need to be removed
	 * @return boolean
	 */
	function canRemoveUserFromProject(User $user, User $remove_user) {
		if($remove_user->isAccountOwner()) return false;
		return $user->isAccountOwner() || can_manage_workspaces(logged_user()) || can_manage_security(logged_user());
	} // canRemoveUserFromProject

	// ---------------------------------------------------
	//  URLS
	// ---------------------------------------------------

	/**
	 * Link to project dashboard page
	 *
	 * @access public
	 * @param void
	 * @return stirng
	 */
	function getDashboardUrl() {
		return get_url('dashboard', 'index', array('active_project' => $this->getId()));
	} // getDashboardUrl
	
	/**
	 * Link to project overview page
	 *
	 * @access public
	 * @param void
	 * @return stirng
	 */
	function getOverviewUrl() {
		return get_url('dashboard', 'index', array('active_project' => $this->getId()));
	} // getOverviewUrl

	/**
	 * Return project messages index page URL
	 *
	 * @param void
	 * @return string
	 */
	function getMessagesUrl() {
		return get_url('message', 'index', array('active_project' => $this->getId()));
	} // getMessagesUrl

	/**
	 * Return project tasks index page URL
	 *
	 * @param void
	 * @return string
	 */
	function getTasksUrl() {
		return get_url('task', 'index', array('active_project' => $this->getId()));
	} // getTasksUrl

	/**
	 * Return project milestones index page URL
	 *
	 * @param void
	 * @return string
	 */
	function getMilestonesUrl() {
		return get_url('milestone', 'index', array('active_project' => $this->getId()));
	} // getMilestonesUrl

	/**
	 * Return project forms index page URL
	 *
	 * @param void
	 * @return string
	 */
	function getFormsUrl() {
		return get_url('form', 'index', array('active_project' => $this->getId()));
	} // getFormsUrl

	/**
	 * Return project people index page URL
	 *
	 * @param void
	 * @return string
	 */
	function getPeopleUrl() {
		return get_url('project', 'people', array('active_project' => $this->getId()));
	} // getPeopleUrl

	/**
	 * Return project permissions page URL
	 *
	 * @param void
	 * @return string
	 */
	function getPermissionsUrl() {
		return get_url('project', 'permissions', array('active_project' => $this->getId()));
	} // getPermissionsUrl

	/**
	 * Return search URL
	 *
	 * @param string $search_for
	 * @param integer $page
	 * @return string
	 */
	function getSearchUrl($search_for = null, $page = null) {
		if(trim($search_for) <> '') {
			$params = array(
          'active_project' => $this->getId(),
          'search_for' => $search_for,
          'page' => (integer) $page > 0 ? (integer) $page : 1
			); // array
			return get_url('project', 'search', $params);
		} else {
			return ROOT_URL . '/index.php';
		} // if
	} // getSearchUrl

	/**
	 * Return edit project URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getEditUrl() {
		return get_url('project', 'edit', array(
        'id' => $this->getId(),
        'active_project' => $this->getId(),
		));
	} // getEditUrl

	/**
	 * Return delete project URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getDeleteUrl() {
		return get_url('project', 'delete', array(
        'id' => $this->getId(),
        'active_project' => $this->getId(),
		));
	} // getDeleteUrl

	/**
	 * Return complete project url
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getCompleteUrl() {
		return get_url('project', 'complete', $this->getId());
	} // getCompleteUrl

	/**
	 * Return open project URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getOpenUrl() {
		return get_url('project', 'open', $this->getId());
	} // getOpenUrl
	
	/**
	 * Return archive project url
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getArchiveUrl() {
		return get_url('project', 'archive', $this->getId());
	} // getArchiveUrl

	/**
	 * Return unarchive project URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getUnarchiveUrl() {
		return get_url('project', 'unarchive', $this->getId());
	} // getUnarchiveUrl

	/**
	 * Return remove user from project URL
	 *
	 * @access public
	 * @param User $user
	 * @return string
	 */
	function getRemoveUserUrl(User $user) {
		return get_url('project', 'remove_user', array('user_id' => $user->getId(), 'project_id' => $this->getId()));
	} // getRemoveUserUrl

	/**
	 * Return remove company from project URL
	 *
	 * @access public
	 * @param Company $company
	 * @return string
	 */
	function getRemoveCompanyUrl(Company $company) {
		return get_url('project', 'remove_company', array('company_id' => $company->getId(), 'project_id' => $this->getId()));
	} // getRemoveCompanyUrl

	/**
	 * Return tag URL
	 *
	 * @access public
	 * @param string $tag_name
	 * @return string
	 */
	function getTagUrl($tag_name) {
		return get_url('tag', 'project_tag', array('tag' => $tag_name, 'active_project' => $this->getId()));
	} // getTagUrl

	/**
	 * Delete tag URL
	 *
	 * @access public
	 * @param string $tag_name
	 * @return string
	 */
	function getDeleteTagUrl($tag_name, $object_id, $manager_class) {
		return get_url('tag', 'delete_tag', array('tag_name' => $tag_name, 'project_id' => $this->getId(), 'object_id' => $object_id, 'manager_class' => $manager_class));
	} // getDeleteTagUrl


	// ---------------------------------------------------
	//  Roles
	// ---------------------------------------------------

	/**
	 * This function will return all roles in project
	 *
	 * @param void
	 * @return array
	 */
	function getAllRoles() {
		if(is_null($this->all_roles)) {
			$this->all_roles = ProjectContacts::getRolesByProject($this);
		} // if
		return $this->all_roles;
	} // getAllRoles

	// ---------------------------------------------------
	//  WEbpages
	// ---------------------------------------------------

	/**
	 * This function will return all webpages in a project
	 *
	 * @param void
	 * @return array
	 */
	function getAllWebpages() {
		if(is_null($this->all_webpages)) {
			$this->all_webpages = ProjectWebpages::getWebpagesByProject($this);
		} // if
		return $this->all_webpages;
	} //  getAllWebpages
	
	/**
	 * This function will return all events in a project
	 *
	 * @param void
	 * @return array
	 */
	function getAllEvents() {
		if(is_null($this->all_events)) {
			$this->all_events = ProjectEvents::getAllEventsByProject($this);
		} // if
		return $this->all_events;
	} //  getAllEvents

	function getAllPermissions($project_permissions = null) {
    	if (is_null($project_permissions) && !$this->isNew()) {
    		$project_permissions = ProjectUsers::findAll(array('conditions' => 'project_id = '. $this->getId()) );
    	}
    	$result = array();
    	if (is_array($project_permissions)) {
	    	foreach ($project_permissions as $perm){
	    		if (!$perm->getUserOrGroup() instanceof User && !$perm->getUserOrGroup() instanceof Group) continue;
	    		$chkArray = array();
	    		$chkArray[0] = ($perm->getCanAssignToOwners() ? 1 : 0);
	    		$chkArray[1] = ($perm->getCanAssignToOther() ? 1 : 0);
	    		
	    		$radioArray = array();
	    		$radioArray[0] = ($perm->getCanWriteMessages() ? 2 : ($perm->getCanReadMessages()? 1 : 0));
	    		$radioArray[1] = ($perm->getCanWriteTasks() ? 2 : ($perm->getCanReadTasks()? 1 : 0));
	    		$radioArray[2] = ($perm->getCanWriteMilestones() ? 2 : ($perm->getCanReadMilestones()? 1 : 0));
	    		$radioArray[3] = ($perm->getCanWriteMails() ? 2 : ($perm->getCanReadMails()? 1 : 0));
	    		$radioArray[4] = ($perm->getCanWriteComments() ? 2 : ($perm->getCanReadComments()? 1 : 0));
	    		$radioArray[5] = ($perm->getCanWriteContacts() ? 2 : ($perm->getCanReadContacts()? 1 : 0));
	    		$radioArray[6] = ($perm->getCanWriteWeblinks() ? 2 : ($perm->getCanReadWeblinks()? 1 : 0));
	    		$radioArray[7] = ($perm->getCanWriteFiles() ? 2 : ($perm->getCanReadFiles()? 1 : 0));
	    		$radioArray[8] = ($perm->getCanWriteEvents() ? 2 : ($perm->getCanReadEvents()? 1 : 0));
	    		
	    		$result[] = array("wsid" => $perm->getUserId(), "pc" => $chkArray, "pr" => $radioArray, 'maxPerm' => $perm->getUserOrGroup()->isGuest() ? 1 : 2);
	    	}
    	}
    	
    	return $result;
    }
	
	// ---------------------------------------------------
	//  System functions
	// ---------------------------------------------------

	function isTrashable() {
		return false;
	}
    
	/**
	 * Validate object before save
	 *
	 * @access public
	 * @param array $errors
	 * @return null
	 */
	function validate(&$errors) {
		if(!$this->validatePresenceOf('name')) {
			$errors[] = lang('project name required');
		} // if
	} // validate

	/**
	 * Delete project
	 *
	 * @param void
	 * @return boolean
	 */
	function delete() {		
		$wsIds = $this->getAllSubWorkspacesQuery();
		if ($wsIds) {
			$ws = $this->getSubWorkspaces();
			if(isset($ws) && !is_null($ws)){
				$wsToDelete = array();
				$wsToMove = array();
				
				$users = Users::findAll(array("conditions" => "personal_project_id in ($wsIds)"));
				foreach ($ws as $w) {
					$canDelete = $w->canDelete(logged_user());
					
					if ($users && $canDelete){
						foreach ($users as $user)
							if ($user->getPersonalProjectId() == $w->getId()){
								$canDelete = false;
								break;	
							}
					}
					if ($canDelete){
						$wsToDelete[] = $w;
					} else {
						$wsToMove[] = $w;
					}
				}
				
				if (count($wsToMove) > 0){
					//Find the new parents
					$moves = array();
					
					foreach ($wsToMove as $w){
						$parentIds = $w->getParentIds();
						for ($i = $w->getDepth() - 1; $i > 0; $i--){
							if ($parentIds[$i] == $this->getId()){
								$moves[] = array($w,$this->getParentWorkspace());
								break;
							} else {
								$found = false;
								for ($j = 0; $j < count($wsToMove); $j++)
									if ($parentIds[$i] == $wsToMove[$j]->getId()){
										$moves[] = array($w,$wsToMove[$j]);
										$found = true;
										break;
									}
								if ($found)
									break;
							}
						}
					}
				}
				
				foreach ($wsToDelete as $w)
					$w->deleteSingle();	
								
				if (isset($moves))
					foreach ($moves as $move){
						$move[0]->setParentWorkspace($move[1]);
						$move[0]->save();
					}
			}
		}
		// Delete ws from old global cache 
		if (GlobalCache::isAvailable()) {			
			GlobalCache::delete('user_copt_obj_lastAccessedWorkspace');
			GlobalCache::delete('user_copt_obj_initialWorkspace');
		}	
		return $this->deleteSingle();
	} // delete
	
	/**
	 * Delete project
	 *
	 * @param void
	 * @return boolean
	 */
	protected function deleteSingle() {
		@set_time_limit(0);
		
		if (GlobalCache::isAvailable()) {			
			$this->deleteProjectFromGlobalCache();			
		}
		
		$this->clearMessages();
		$this->clearTasks();
		$this->clearMilestones();
		$this->clearFiles();
		$this->clearForms();
		$this->clearPermissions();
		$this->clearLogs();
		$this->clearRoles();
		$this->clearMails();
		$this->clearWebpages();
		$this->clearEvents();
		$this->clearCompanies();
		$this->clearTimeslots();
		return parent::delete();
	} // delete
	
	/**
	 * Deletes all of the workspace/project cached values from the Global Cache
	 *
	 * @param void
	 * @return boolean
	 */
	function deleteProjectFromGlobalCache() {
		if (GlobalCache::isAvailable()) {					
				$users = Users::getAll();				
				foreach($users as $user){
					$id = $user->getId();			
					GlobalCache::delete('user_config_option_'.$id.'_lastAccessedWorkspace');
					GlobalCache::delete('user_config_option_'.$id.'_initialWorkspace');			
					GlobalCache::delete('active_ws_'.$id);				
				}
		}	
	}

	/**
	 * Clear all project webpages
	 *
	 * @param void
	 * @return null
	 */
	private function clearWebpages() {
		$webpages = $this->getAllWebpages();
		if(is_array($webpages)) {
			foreach($webpages  as $webpage) {
				if (count($webpage->getWorkspaces()) == 1){
					$webpage->delete();
				} else {
					$webpage->removeFromWorkspace($this);
				} // if
			} // foreach
		} // if
	} //  clearWebpages
	
	/**
	 * Clear all project timeslots
	 *
	 * @param void
	 * @return null
	 */
	private function clearTimeslots() {
		if (is_null($this->all_timeslots)) {
			$this->all_timeslots = Timeslots::getAllProjectTimeslots($this);
		}
		if (is_array($this->all_timeslots)) {
			foreach ($this->all_timeslots as $t) {
				$t->delete();
			}
		}
	}

	/**
	 * Clear all project messages
	 *
	 * @param void
	 * @return null
	 */
	private function clearMessages() {
		$messages = $this->getAllMessages();
		if(is_array($messages)) {
			foreach($messages as $message) {
				if (count($message->getWorkspaces()) == 1){
					$message->delete();
				} else {
					$message->removeFromWorkspace($this);
				} // if
			} // foreach
		} // if
	} // clearMessages
	
	/**
	 * Clear all project events
	 *
	 * @param void
	 * @return null
	 */
	private function clearEvents() {
		$events = $this->getAllEvents();
		if(is_array($events)) {
			foreach($events as $event) {
				if (count($event->getWorkspaces()) == 1){
					$event->delete();
				} else {
					$event->removeFromWorkspace($this);
				} // if
			} // foreach
		} // if
	} // clearEvents

	/**
	 * Clear all project mails
	 *
	 * @param void
	 * @return null
	 */
	private function clearMails() {
		$mails = $this->getAllMails();
		if(is_array($mails)) {
			foreach($mails as $mail) {
				$mail->removeFromWorkspace($this);
			} // foreach
		} // if
	} //  clearMails

	/**
	 * Clear all project roles
	 *
	 * @param void
	 * @return null
	 */
	private function clearRoles() {
		$roles = $this->getAllRoles();
		if(is_array($roles)) {
			foreach($roles as $rol) {
				$rol->delete();
			} // foreach
		} // if
	} // clearRoles

	/**
	 * Clear all task lists
	 *
	 * @param void
	 * @return null
	 */
	private function clearTasks() {
		$task_lists = $this->getAllTasks();
		if(is_array($task_lists)) {
			foreach($task_lists as $task_list) {
				$task_list->delete();
			} // foreach
		} // if
	} // clearTasks

	/**
	 * Clear all milestones
	 *
	 * @param void
	 * @return null
	 */
	private function clearMilestones() {
		$milestones = $this->getAllMilestones();
		if(is_array($milestones)) {
			foreach($milestones as $milestone) {
				$milestone->delete();
			} // foreach
		} // if
	} // clearMilestones

	/**
	 * Clear forms
	 *
	 * @param void
	 * @return null
	 */
	private function clearForms() {
		$forms = $this->getAllForms();
		if(is_array($forms)) {
			foreach($forms as $form) {
				$form->delete();
			} // foreach
		} // if
	} // clearForms

	/**
	 * Clear all files and folders
	 *
	 * @param void
	 * @return null
	 */
	private function clearFiles() {
		$files = ProjectFiles::getAllFilesByProject($this);
		if(is_array($files)) {
			foreach($files as $file) {
				if (count($file->getWorkspaces()) == 1){
					$file->delete();
				} else {
					$file->removeFromWorkspace($this);
				} // if
			} // foreach
		} // if
	} // clearFiles

	/**
	 * Clear project level permissions
	 *
	 * @param void
	 * @return null
	 */
	function clearPermissions() {
		ProjectCompanies::clearByProject($this);
		ProjectUsers::clearByProject($this);
	} // clearPermissions

	/**
	 * Clear application logs for this project
	 *
	 * @param void
	 * @return null
	 */
	function clearLogs() {
		ApplicationLogs::clearByProject($this);
	} // clearLogs

	// ---------------------------------------------------
	//  ApplicationDataObject implementation
	// ---------------------------------------------------

	/**
	 * Return object type name
	 *
	 * @param void
	 * @return string
	 */
	function getObjectTypeName() {
		return 'project';
	} // getObjectTypeName

	/**
	 * Return object URl
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getObjectUrl() {
		return $this->getOverviewUrl();
	} // getObjectUrl

} // Project

?>