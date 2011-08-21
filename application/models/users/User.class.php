<?php

/**
 * User class
 * Generated on Sat, 25 Feb 2006 17:37:12 +0100 by DataObject generation tool
 *
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class User extends BaseUser {

	/**
	 * Users are searchable
	 *
	 * @var boolean
	 */
	protected $is_searchable = true;
	
	/**
	 * Users are not linkable
	 *
	 * @var boolean
	 */
	protected $is_linkable_object = false;

	/**
    * This project object is taggable
    *
    * @var boolean
    */
    protected $is_taggable = false;
    
	/**
	 * Array of searchable columns
	 *
	 * @var array
	 */
	protected $searchable_columns = array('username', 'display_name', 'email');
	
	/**
	 * Cached project permission values. Two level array. First level are projects (project ID) and
	 * second are permissions associated with permission name
	 *
	 * @var array
	 */
	private $project_permissions_cache = array();

	/**
	 * Associative array. Key is project ID and value is true if user has access to that project
	 *
	 * @var array
	 */
	private $is_project_user_cache = array();

	/**
	 * True if user is member of owner company. This value is read on first request and cached
	 *
	 * @var boolean
	 */
	private $is_member_of_owner_company = null;

	/**
	 * Cached is_administrator value. First time value is requested it will be checked and cached.
	 * After that every request will return cached value
	 *
	 * @var boolean
	 */
	private $is_administrator = null;

	/**
	 * Cached is_account_owner value. Value is retrived on first requests
	 *
	 * @var boolean
	 */
	private $is_account_owner = null;

	/**
	 * Cached value of all projects
	 *
	 * @var array
	 */
	private $projects;

	/**
	 * Cached value of active projects
	 *
	 * @var array
	 */
	private $active_projects;

	/**
	 * Cached value of owned projects
	 *
	 * @var array
	 */
	private $own_projects;

	/**
	 * Cached value of active project ids
	 *
	 * @var string
	 */
	private $active_projects_ids;

	/**
	 * Default project for the user
	 *
	 * @var Project
	 */
	private $personal_project;

	/**
	 * Cached value of finished projects
	 *
	 * @var array
	 */
	private $finished_projects;

	/**
	 * Array of all active milestons
	 *
	 * @var array
	 */
	private $all_active_milestons;

	/**
	 * Cached late milestones
	 *
	 * @var array
	 */
	private $late_milestones;

	/**
	 * Cached today milestones
	 *
	 * @var array
	 */
	private $today_milestones;

	/**
	 * Cached late tasks
	 *
	 * @var array
	 */
	private $late_tasks;

	/**
	 * Cached today tasks
	 *
	 * @var array
	 */
	private $today_tasks;
	
	/**
	 * Cached array of new objects
	 *
	 * @var array
	 */
	private $whats_new;

	/**
	 * canSeeCompany() method will cache its result here (company_id => visible as bool)
	 *
	 * @var array
	 */
	private $visible_companies = array();
	
	private $groups;
	
	private $groups_csv;
	
	private $default_billing;
	
    /**
    * Returns true if this user is taggable
    *
    * @param void
    * @return boolean
    */
    function isTaggable() {
      return $this->is_taggable;
    } // isTaggable
    
	/**
	 * Save
	 *
	 */
   	function save() {
   		if($this->isNew()){
	   		$max_users = config_option('max_users');
	        if ($max_users && Users::count() >= $max_users) {
	            throw new Exception(lang("maximum number of users reached error"));
	        }
   		}
        parent::save();

        // update logged_user info in global cache
        if (logged_user() instanceof User && $this->getId() == logged_user()->getId() && GlobalCache::isAvailable()) {
			GlobalCache::update('logged_user_'.$this->getId(), $this);
		}
    }
    
    /**
    * Returns true if user has at least one email account to send emails from.
    *
    * @access public
    * @return boolean
    */
    function hasEmailAccounts() {
    	$accounts = MailAccountUsers::find(array('conditions' => '`user_id` = '.$this->getId()));
    	return is_array($accounts) && count($accounts) > 0;
    } // hasEmailAccounts

	/**
	 * Construct user object
	 *
	 * @param void
	 * @return User
	 */
	function __construct() {
		parent::__construct();
		$this->addProtectedAttribute('password', 'salt', 'session_lifetime', 'token', 'twister', 'last_login', 'last_visit', 'last_activity');
	} // __construct

	
    /**
    * Returns true if user info is updated by the user since user is created.
    *
    * @access public
    * @param void
    * @return boolean
    */
    function isInfoUpdated() {
      return $this->getCreatedOn()->getTimestamp() < $this->getUpdatedOn()->getTimestamp();
    } // isInfoUpdated

	
    /**
    * Returns true if user info is updated by the user since user is created.
    *
    * @access public
    * @param void
    * @return boolean
    */
    function hasPreferencesUpdated() {
      return UserWsConfigOptionValues::hasOptionValues($this);
    } // isInfoUpdated
	
	/**
	 * Check if this user is member of specific company
	 *
	 * @access public
	 * @param Company $company
	 * @return boolean
	 */
	function isMemberOf(Company $company) {
		return $company instanceof Company ? $this->getCompanyId() == $company->getId() : false;
	} // isMemberOf

	/**
	 * Usualy we check if user is member of owner company so this is the shortcut method
	 *
	 * @param void
	 * @return boolean
	 */
	function isMemberOfOwnerCompany() {
		if(is_null($this->is_member_of_owner_company)) $this->is_member_of_owner_company = $this->isMemberOf(owner_company());
		return $this->is_member_of_owner_company;
	} // isMemberOfOwnerCompany

	/**
	 * Check if this user is part of specific project
	 *
	 * @param Project $project
	 * @return boolean
	 */
	function isProjectUser(Project $project) {
		if(!isset($this->is_project_user_cache[$project->getId()])) {
			$user_ids = $this->getId();
			$group_ids = GroupUsers::getGroupsCSVsByUser($user_ids);
			if($group_ids && $group_ids != '')
				$user_ids = $user_ids . ',' . $group_ids ;
			$project_user = ProjectUsers::findOne(array('conditions' => 
				'`user_id` in (' . $user_ids  . ') AND '.
				'project_id =' . $project->getId() 
			)); // findById
			$this->is_project_user_cache[$project->getId()] = $project_user instanceof ProjectUser;
		} // if
		return $this->is_project_user_cache[$project->getId()];
	} // isProjectUser

	/**
	 * Check if this user is member of administrators group (id = CONST_ADMIN_GROUP_ID )
	 *
	 * @param void
	 * @return boolean
	 */
	function isAdministrator() {
		return $this->getType() == 'admin';
	} // isAdministrator

	function setAsAdministrator($setAsAdmin = true) {
		if (!logged_user() instanceof User || can_manage_security(logged_user())) {
			if ($setAsAdmin && !$this->isAdministrator()) {
				$group_user = new GroupUser();
				$group_user->setUserId($this->getId());
				$group_user->setGroupId(Group::CONST_ADMIN_GROUP_ID);
				$group_user->save();
			}
			if (!$setAsAdmin && $this->getId() != 1 && $this->isAdministrator()) {
				GroupUsers::delete('user_id = ' . $this->getId() . ' and group_id = ' . Group::CONST_ADMIN_GROUP_ID);
			}
		}
	}
	
	/**
	 * Account owner is user account that was created when company website is created
	 *
	 * @param void
	 * @return boolean
	 */
	function isAccountOwner() {
		if(is_null($this->is_account_owner)) {
			$this->is_account_owner = $this->isMemberOfOwnerCompany() && (owner_company()->getCreatedById() == $this->getId());
		} // if
		return $this->is_account_owner;
	} // isAccountOwner

	/**
	 * Check if this user have specific project permission. $permission is the name of table field that holds the value
	 *
	 * @param Project $project
	 * @param string $permission Name of the field where the permission value is stored. There are set of constants
	 *   in ProjectUser that hold field names (ProjectUser::CAN_MANAGE_MESSAGES ...)
	 * @return boolean
	 */
	function hasProjectPermission(Project $project, $permission, $use_cache = true) {
		if($use_cache) {
			if(isset($this->project_permissions_cache[$project->getId()]) && isset($this->project_permissions_cache[$project->getId()][$permission])) {
				return $this->project_permissions_cache[$project->getId()][$permission];
			} // if
		} // if

		$project_user = ProjectUsers::findById(array('project_id' => $project->getId(), 'user_id' => $this->getId()));
		if(!($project_user instanceof ProjectUser)) {
			if($use_cache) {
				$this->project_permissions_cache[$project->getId()][$permission] = false;
			} // if
			return false;
		} // if

		$getter_method = 'get' . Inflector::camelize($permission);
		$project_user_methods = get_class_methods('ProjectUser');

		$value = in_array($getter_method, $project_user_methods) ? $project_user->$getter_method() : false;

		if($use_cache) $this->project_permissions_cache[$project->getId()][$permission] = $value;
		return $value;
	} // hasProjectPermission

	/**
	 * This function will check if this user have all project permissions
	 *
	 * @param Project $project
	 * @param boolean $use_cache
	 * @return boolean
	 */
	function hasAllProjectPermissions(Project $project, $use_cache = true) {
		$permissions = ProjectUsers::getPermissionColumns();
		if(is_array($permissions)) {
			foreach($permissions as $permission) {
				if(!$this->hasProjectPermission($project, $permission, $use_cache)) return false;
			} // foreach
		} // if
		return true;
	} // hasAllProjectPermissions

	// ---------------------------------------------------
	//  Retrive
	// ---------------------------------------------------

	/**
	 * Returns the contact associated with the user, or null otherwise
	 *
	 */
	function getContact(){		
		$cont = Contacts::findOne(array('include_trashed' => true, 'conditions'=>array('user_id = ' . $this->getId())));
		if($cont instanceof Contact )
			return $cont;
		else 
			return null;
	}
	/**
	 * Return owner company
	 *
	 * @access public
	 * @param void
	 * @return Company
	 */
	function getCompany() {
		return Companies::findById($this->getCompanyId());
	} // getCompany

	/**
	 * Return all projects that this user is member of.
	 * If active is true only active projects are returned.
	 * If top is true only top projects are returned.
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getProjects($order_by = '', $where = null) {
		return ProjectUsers::getProjectsByUser($this, $where, $order_by);
	} // getProjects
	
	/*
	 * Get all top level workspaces for this user 
	 */
	
	function getTopLevelWorkspaces($order_by = ''){
		 return  $this->getProjects($order_by , ' p2 = 0 ');		
	}
	function getWorkspaces($active = false, $parent = null) {
		$conditions = '';
		if ($active) {
			$conditions .= '`completed_on` = ' . DB::escape(EMPTY_DATETIME);
		}
		if ($parent != null) {
			if ($conditions != '') $conditions .= " AND ";
			$conditions .= '`parent_id` = ' . DB::escape($parent);
		}
		return ProjectUsers::getProjectsByUser($this, $conditions);
	}
	
	function removeFromWorkspace($workspace) {
		$pu = ProjectUsers::getByUserAndProject($workspace, $this);
		if ($pu instanceof ProjectUser) {
			$pu->delete();
		}
	}
	
	/**
	 * Return array of active projects that this user have access
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getActiveProjects() {
		if(is_null($this->active_projects)) {
			$this->active_projects = ProjectUsers::getProjectsByUser($this, '`completed_on` = ' . DB::escape(EMPTY_DATETIME));
		} // if
		return $this->active_projects;
	} // getActiveProjects
	
	/**
	 * Return array of active projects that this user created
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getOwnProjects() {
		if(is_null($this->own_projects)) {
			$this->own_projects = Projects::find('`created_by_id` = ' . $this->getId());
		} // if
		return $this->own_projects;
	} // getOwnProjects

	/**
	 * Returns csv list of email account Ids
	 *
	 * @return string
	 */
	function getMailAccountIdsCSV(){
		$accounts = MailAccounts::findAll(array('conditions' => '`user_id` = ' . logged_user()->getId()));
		$result = "";
		if($accounts){
			foreach ($accounts as $acc)
				$result .= "," . $acc->getId();
		}
		if ($result == "")
		return $result;
		else
		return substr($result,1);
	}


	/**
	 * Return array of active projects that this user has access to
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getActiveProjectIdsCSV() {
		if (is_null($this->active_projects_ids)) {
			$ids = ProjectUsers::getProjectIdsByUser($this);
			if (count($ids) > 0) {
				$this->active_projects_ids = implode(',', $ids);
			} else { 
				$this->active_projects_ids = "0";
			}
		}
		return $this->active_projects_ids;
	} // getActiveProjects

	/**
	 * Return the user's workspaces query that returns user's workspaces ids.
	 * @param bool $active If null, all projects; if true, only active, if false, only archived
	 * @return string
	 */
	function getWorkspacesQuery($active = null, $additional_conditions = null) {
		//return $this->getActiveProjectIdsCSV();
		$project_users_table =  ProjectUsers::instance()->getTableName(true);
		$group_users_table = GroupUsers::instance()->getTableName(true);
		
		$usercond = "($project_users_table.`user_id` = " . DB::escape($this->getId()) . ")";
		$groupcond = "($project_users_table.`user_id` IN (SELECT `group_id` FROM $group_users_table WHERE $group_users_table.`user_id` = " . DB::escape($this->getId()) . "))";
		$addcond = $additional_conditions ==null ? "" : "AND ".$additional_conditions;
		
		if ($active === null) {
			return "SELECT $project_users_table.`project_id` FROM $project_users_table WHERE ($usercond OR $groupcond) $addcond";
		} else {
			$projects_table =  Projects::instance()->getTableName(true);
			$empty_date = DB::escape(EMPTY_DATETIME);
			$active_cond = $active ? "$projects_table.`completed_on` = $empty_date" : "$projects_table.`completed_on` <> $empty_date";
			$projectcond = "($project_users_table.`project_id` = $projects_table.`id` AND  $active_cond)";
			return "SELECT $project_users_table.`project_id` FROM $project_users_table, $projects_table WHERE ($usercond OR $groupcond) AND $projectcond $addcond";
		}
	}
	
	/**
	 * Return the personal project of the user
	 *
	 * @access public
	 * @param void
	 * @return Project
	 */
	function getPersonalProject() {
		if(is_null($this->personal_project)) {
			$this->personal_project = Projects::findById($this->getPersonalProjectId());
			if (!$this->personal_project instanceof Project) {
				$this->personal_project = new Project();
				$this->personal_project->setId(0);
				$this->personal_project->setColor(0);
				$this->personal_project->setParentWorkspace(null);
			}
		} // if
		return $this->personal_project;
	} // getPersonalProject

	/**
	 * Return array of finished projects
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getFinishedProjects() {
		if(is_null($this->finished_projects)) {
			$this->finished_projects = ProjectUsers::getProjectsByUser($this, '`completed_on` > ' . DB::escape(EMPTY_DATETIME));
		} // if
		return $this->finished_projects;
	} // getFinishedProjects

	/**
	 * Return all active milestones assigned to this user
	 *
	 * @param void
	 * @return array
	 */
	function getActiveMilestones() {
		if(is_null($this->all_active_milestons)) {
			$this->all_active_milestons = ProjectMilestones::getActiveMilestonesByUser($this);
		} // if
		return $this->all_active_milestons;
	} // getActiveMilestones

	/**
	 * Return late milestones that this user have access to
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getLateMilestones($project = null, $tag = null,$limit = null) {
		if (is_null($this->late_milestones)) {
			$this->late_milestones = ProjectMilestones::getLateMilestonesByUser($this, $project, $tag, $limit);
		} // if
		return $this->late_milestones;
	} // getLateMilestones

	/**
	 * Return today milestones that this user have access to
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getTodayMilestones($project = null, $tag = null, $limit) {
		if (is_null($this->today_milestones)) {
			$this->today_milestones = ProjectMilestones::getTodayMilestonesByUser($this, $project, $tag, $limit);
		} // if
		return $this->today_milestones;
	} // getTodayMilestones

	
	/**
	 * Return late tasks that this user has access to
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getLateTasks($project = null, $tag = null) {
		if(is_null($this->late_tasks)) {
			$this->late_tasks = ProjectTasks::getLateTasksByUser($this, $project, $tag);
		} // if
		return $this->late_tasks;
	} // getLateMilestones

	/**
	 * Return today tasks that this user has access to
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getTodayTasks($project = null, $tag = null) {
		if(is_null($this->today_tasks)) {
			$this->today_tasks = ProjectTasks::getDayTasksByUser(new DateTimeValue(mktime()),$this, $project, $tag);
		} // if
		return $this->today_tasks;
	} // getTodayMilestones
	
	
	/**
	 * Return display name for this account. If there is no display name set username will be used
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getDisplayName() {
		$display = parent::getDisplayName();
		return trim($display) == '' ? $this->getUsername() : $display;
	} // getDisplayName

	/**
	 * Returns true if we have title value set
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function hasTitle() {
		return trim($this->getTitle()) <> '';
	} // hasTitle

	function getMailAccounts()
	{
		return MailAccounts::getMailAccountsByUser($this);
	}

	function getGroups(){
		if(is_null($this->groups)) {
			$this->groups = GroupUsers::getGroupsByUser($this->getId());
		} // if
		return $this->groups;
	}
	
	function getGroupsCSV(){
		if (is_null($this->groups_csv)){
			$this->groups_csv = GroupUsers::getGroupsCSVsByUser($this->getId());
		} // if
		return $this->groups_csv;
	}
	
	function getDefaultBilling(){
		if (is_null($this->default_billing) && $this->getDefaultBillingId() != false){
			$this->default_billing = BillingCategories::findById($this->getDefaultBillingId());
		} // if
		return $this->default_billing;
	}
	
	// ---------------------------------------------------
	//  Avatars
	// ---------------------------------------------------

	/**
	 * Set user avatar from $source file
	 *
	 * @param string $source Source file
	 * @param integer $max_width Max avatar widht
	 * @param integer $max_height Max avatar height
	 * @param boolean $save Save user object when done
	 * @return string
	 */
	function setAvatar($source,$fileType, $max_width = 50, $max_height = 50, $save = true) {
		if(!is_readable($source)) return false;

		do {
			$temp_file = ROOT . '/cache/' . sha1(uniqid(rand(), true));
		} while(is_file($temp_file));

		try {
			Env::useLibrary('simplegd');

			$image = new SimpleGdImage($source);
	        if ($image->getImageType() == IMAGETYPE_PNG) {
	        	if ($image->getHeight() > 128 || $image->getWidth() > 128) {
		        	//	resize images if are png bigger than 128 px
	        		$thumb = $image->scale($max_width, $max_height, SimpleGdImage::BOUNDARY_DECREASE_ONLY, false);
	        		$thumb->saveAs($temp_file, IMAGETYPE_PNG);
	        		$public_fileId = FileRepository::addFile($temp_file, array('type' => 'image/png', 'public' => true));
	        	}else{
	        		//keep the png as it is.
	        		$public_fileId = FileRepository::addFile($source, array('type' => 'image/png', 'public' => true));
	        	}
	        } else {
	        	$thumb = $image->scale($max_width, $max_height, SimpleGdImage::BOUNDARY_DECREASE_ONLY, false);
	        	$thumb->saveAs($temp_file, IMAGETYPE_PNG);
	        	$public_fileId = FileRepository::addFile($temp_file, array('type' => 'image/png', 'public' => true));
	        }
			
			if($public_fileId) {
				$this->setAvatarFile($public_fileId);
				if($save) {
					$this->save();
				} // if
			} // if

			$result = true;
		} catch(Exception $e) {
			$result = false;
		} // try

		// Cleanup
		if(!$result && $public_fileId) {
			FileRepository::deleteFile($public_fileId);
		} // if
		@unlink($temp_file);

		return $result;
	} // setAvatar

	/**
	 * Delete avatar
	 *
	 * @param void
	 * @return null
	 */
	function deleteAvatar() {
		if($this->hasAvatar()) {
			FileRepository::deleteFile($this->getAvatarFile());
			$this->setAvatarFile('');
		} // if
	} // deleteAvatar

	/**
	 * Delete personal project
	 * It doesnt delete the project If more than one user have this project as personal project. 
	 * @param void
	 * @return null
	 */
	function deletePersonalProject() {
		$usersWithThatProject = count(Users::GetByPersonalProject($this->getPersonalProjectId()));
		if($this->personal_project && $usersWithThatProject == 1){
			$this->personal_project->delete();
		}// if
	} // deletePersonalProject

	/**
	 * Return path to the avatar file. This function just generates the path, does not check if file really exists
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getAvatarPath() {
		return PublicFiles::getFilePath($this->getAvatarFile());
	} // getAvatarPath

	/**
	 * Return URL of avatar
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getAvatarUrl() {
		return $this->hasAvatar() ? get_url('files', 'get_public_file', array('id' => $this->getAvatarFile())): get_image_url('avatar.gif');
		//return $this->hasAvatar() ? PublicFiles::getFileUrl($this->getAvatarFile()) : get_image_url('avatar.gif');
	} // getAvatarUrl

	/**
	 * Check if this user has uploaded avatar
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function hasAvatar() {
		return (trim($this->getAvatarFile()) <> '') && FileRepository::isInRepository($this->getAvatarFile());
	} // hasAvatar

	// ---------------------------------------------------
	//  Utils
	// ---------------------------------------------------

	/**
	 * This function will generate new user password, set it and return it
	 *
	 * @param boolean $save Save object after the update
	 * @return string
	 */
	function resetPassword($save = true) {
		$new_password = substr(sha1(uniqid(rand(), true)), rand(0, 25), 13);
		$this->setPassword($new_password);
		if($save) {
			$this->save();
		} // if
		return $new_password;
	} // resetPassword

	/**
	 * Set password value
	 *
	 * @param string $value
	 * @return boolean
	 */
	function setPassword($value) {
		do {
			$salt = substr(sha1(uniqid(rand(), true)), rand(0, 25), 13);
			$token = sha1($salt . $value);
		} while(Users::tokenExists($token));

		$this->setToken($token);
		$this->setSalt($salt);
		$this->setTwister(StringTwister::getTwister());
	} // setPassword

	/**
	 * Return twisted token
	 *
	 * @param void
	 * @return string
	 */
	function getTwistedToken() {
		return StringTwister::twistHash($this->getToken(), $this->getTwister());
	} // getTwistedToken

	/**
	 * Check if $check_password is valid user password
	 *
	 * @param string $check_password
	 * @return boolean
	 */
	function isValidPassword($check_password) {
		return  sha1($this->getSalt() . $check_password) == $this->getToken();
	} // isValidPassword

	/**
	 * Check if $check_password is valid user password
	 *
	 * @param string $check_password
	 * @return boolean
	 */
	function isValidPasswordLdap($user, $password, $config) {
	
		// Connecting using the configuration:
		require_once "Net/LDAP2.php";
		
		$ldap = Net_LDAP2::connect($config);

		// Testing for connection error
		if (PEAR::isError($ldap)) {
			return false;
		}
		$filter = Net_LDAP2_Filter::create($config['uid'], 'equals', $user);
		$search = $ldap->search(null, $filter, null);

		if (Net_LDAP2::isError($search)) {
			return false;
		}
		
		if ($search->count() != 1) {
			return false;
		}

		// User exists so we may rebind to authenticate the password
		$entries = $search->entries();
		$bind_result = $ldap->bind($entries[0]->dn(), $password);

		if (PEAR::isError($bind_result)) {
			return false;
		}
		return true;
	} // isValidPassword

	/**
	 * Check if $twisted_token is valid for this user account
	 *
	 * @param string $twisted_token
	 * @return boolean
	 */
	function isValidToken($twisted_token) {
		return StringTwister::untwistHash($twisted_token, $this->getTwister()) == $this->getToken();
	} // isValidToken

	// ---------------------------------------------------
	//  Permissions
	// ---------------------------------------------------

	/**
	 * Can specific user add user to specific company
	 *
	 * @access public
	 * @param User $user
	 * @param Company $to Can user add user to this company
	 * @return boolean
	 */
	function canAdd(User $user, Company $to) {
		if($user->isAccountOwner()) {
			return true;
		} // if
		return can_manage_security(logged_user());
	} // canAdd

	/**
	 * Check if specific user can update this user account
	 *
	 * @access public
	 * @param User $user
	 * @return boolean
	 */
	function canEdit(User $user) {
		if($user->getId() == $this->getId()) {
			return true; // account owner
		} // if
		if($user->isAccountOwner()) {
			return true;
		} // if
		return can_manage_security(logged_user());
	} // canEdit

	/**
	 * Check if specific user can delete specific account
	 *
	 * @param User $user
	 * @return boolean
	 */
	function canDelete(User $user) {
		if($this->isAccountOwner()) {
			return false; // can't delete accountowner
		} // if

		if($this->getId() == $user->getId()) {
			return false; // can't delete self
		} // if

		return  can_manage_security(logged_user());
	} // canDelete

	/**
	 * Returns true if this user can see $user
	 *
	 * @param User $user
	 * @return boolean
	 */
	function canSeeUser(User $user) {
		if($this->isMemberOfOwnerCompany()) {
			return true; // see all
		} // if
		if($user->getCompanyId() == $this->getCompanyId()) {
			return true; // see members of your own company
		} // if
		if($user->isMemberOfOwnerCompany()) {
			return true; // see members of owner company
		} // if
		return false;
	} // canSeeUser

	/**
	 * Returns true if this user can see $company. Members of owener company and
	 * coworkers are visible without project check! Also, members of owner company
	 * can see all clients without any prior check!
	 *
	 * @param Company $company
	 * @return boolean
	 */
	function canSeeCompany(Company $company) {
		if($this->isMemberOfOwnerCompany()) {
			return true;
		} // if

		if(isset($this->visible_companies[$company->getId()])) {
			return $this->visible_companies[$company->getId()];
		} // if

		if($company->isOwner()) {
			$this->visible_companies[$company->getId()] = true;
			return true;
		} // if

		if($this->getCompanyId() == $company->getId()) {
			$this->visible_companies[$company->getId()] = true;
			return true;
		} // if

		if ($company->canView($this)) {
			$this->visible_companies[$company->getId()] = true;
			return true;
		}

		$this->visible_companies[$company->getId()] = false;
		return false;
	} // canSeeCompany

	/**
	 * Check if specific user can update this profile
	 *
	 * @param User $user
	 * @return boolean
	 */
	function canUpdateProfile(User $user) {
		if($this->getId() == $user->getId()) {
			return true;
		} // if
		if($user->isAdministrator()) {
			return true;
		} // if
		return false;
	} // canUpdateProfile

	/**
	 * Check if this user can update this users permissions
	 *
	 * @param User $user
	 * @return boolean
	 */
	function canUpdatePermissions(User $user) {
//		if($this->isAccountOwner()) {
//			return false; // noone will touch this
//		} // if
		return can_manage_security(logged_user());
	} // canUpdatePermissions

	/**
	 * Check if this user is company administration (used to check many other permissions). User must
	 * be part of the company and have is_admin stamp set to true
	 *
	 * @access public
	 * @param Company $company
	 * @return boolean
	 */
	function isCompanyAdmin(Company $company) {
		return ($this->getCompanyId() == $company->getId()) && $this->isAdministrator();
	} // isCompanyAdmin

	/**
	 * Return project permission for specific user if he is on project. In case of any error $default is returned
	 *
	 * @access public
	 * @param Project $project
	 * @param string $permission Permission name
	 * @param boolean $default Default value
	 * @return boolean
	 */
	function getProjectPermission(Project $project, $permission, $default = false) {
		static $valid_permissions = null;
		if(is_null($valid_permissions)) {
			$valid_permissions = ProjectUsers::getPermissionColumns();
		} // if

		if(!in_array($permission, $valid_permissions)) {
			return $default;
		} // if
		$project_user = ProjectUsers::findById(array(
        'project_id' => $project->getId(),
        'user_id' => $this->getId()
		)); // findById


		if(!($project_user instanceof ProjectUser)) {
			return $default;
		} // if
		$getter = 'get' . Inflector::camelize($permission);
		//if($project_user->getColumnValue('can_manage_events')) echo 'asd '  ;
		//else echo 'zxc ' . $project_user->getColumnValue('can_manage_events');
		//echo 'p';
		return $project_user->$getter();
	} // getProjectPermission


    
    function getAllPermissions($user_permissions = null) {
    	if (is_null($user_permissions) && !$this->isNew())
    		$user_permissions = ProjectUsers::findAll(array('conditions' => 'user_id = '. $this->getId()) );
    	$result = array();
    	if (is_array($user_permissions)){
	    	foreach ($user_permissions as $perm){
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
	    		
	    		$result[] = array("wsid" => $perm->getProjectId(), "pc" => $chkArray, "pr" => $radioArray);
	    	}
    	}
    	
    	return $result;
    }
	
	// ---------------------------------------------------
	//  URLs
	// ---------------------------------------------------

	/**
	 * Return view account URL of this user
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getAccountUrl() {
		return get_url('account', 'index');
	} // getAccountUrl
	
	/**
	 * Return edit preferences URL of this user
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getEditPreferencesUrl() {
		return get_url('user', 'list_user_categories');
	} // getAccountUrl

	/**
	 * Show company card page
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function getCardUrl() {
		return get_url('user', 'card', $this->getId());
	} // getCardUrl

	/**
	 * Return edit user URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getEditUrl() {
		return get_url('user', 'edit', $this->getId());
	} // getEditUrl

	/**
	 * Return URL to create contact from User
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getCreateContactFromUserUrl() {
		return get_url('user', 'create_contact_from_user', $this->getId());
	} // getCreateContactFromUserUrl

	/**
	 * Return delete user URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getDeleteUrl() {
		return get_url('user', 'delete', $this->getId());
	} // getDeleteUrl

	/**
	 * Return edit profile URL
	 *
	 * @param string $redirect_to URL where we need to redirect user when he updates profile
	 * @return string
	 */
	function getEditProfileUrl($redirect_to = null) {
		$attributes = array('id' => $this->getId());
		if(trim($redirect_to) <> '') {
			$attributes['redirect_to'] = str_replace('&amp;', '&', trim($redirect_to));
		} // if

		return get_url('account', 'edit_profile', $attributes);
	} // getEditProfileUrl

	/**
	 * Edit users password
	 *
	 * @param string $redirect_to URL where we need to redirect user when he updates password
	 * @return null
	 */
	function getEditPasswordUrl($redirect_to = null) {
		$attributes = array('id' => $this->getId());
		if(trim($redirect_to) <> '') {
			$attributes['redirect_to'] = str_replace('&amp;', '&', trim($redirect_to));
		} // if

		return get_url('account', 'edit_password', $attributes);
	} // getEditPasswordUrl

	/**
	 * Return update user permissions page URL
	 *
	 * @param string $redirect_to
	 * @return string
	 */
	function getUpdatePermissionsUrl($redirect_to = null) {
		$attributes = array('id' => $this->getId());
		if(trim($redirect_to) <> '') {
			$attributes['redirect_to'] = str_replace('&amp;', '&', trim($redirect_to));
		} // if

		return get_url('account', 'update_permissions', $attributes);
	} // getUpdatePermissionsUrl

	function giveAccessToObject(ProjectDataObject $object) {
		$ou = new ObjectUserPermission();
		$ou->setObjectId($object->getId());
		$ou->setObjectManager($object->getObjectManagerName());
		$ou->setUserId($this->getId());
		$ou->setReadPermission(true);
		$ou->setWritePermission(false);
		$ou->save();
	}
	
	/**
	 * Return update avatar URL
	 *
	 * @param string
	 * @return string
	 */
	function getUpdateAvatarUrl($redirect_to = null) {
		$attributes = array('id' => $this->getId());
		if(trim($redirect_to) <> '') {
			$attributes['redirect_to'] = str_replace('&amp;', '&', trim($redirect_to));
		} // if

		return get_url('account', 'edit_avatar', $attributes);
	} // getUpdateAvatarUrl

	/**
	 * Return delete avatar URL
	 *
	 * @param void
	 * @return string
	 */
	function getDeleteAvatarUrl($redirect_to = null) {
		$attributes = array('id' => $this->getId());
		if(trim($redirect_to) <> '') {
			$attributes['redirect_to'] = str_replace('&amp;', '&', trim($redirect_to));
		} // if

		return get_url('account', 'delete_avatar', $attributes);
	} // getDeleteAvatarUrl

	/**
	 * Return recent activities feed URL
	 *
	 * If $project is valid project instance URL will be limited for that project only, else it will be returned for
	 * overal feed
	 *
	 * @param Project $project
	 * @return string
	 */
	function getRecentActivitiesFeedUrl($project = null) {
		$params = array(
        'id' => $this->getId(),
        'token' => $this->getTwistedToken(),
		); // array

		if($project instanceof Project) {
			$params['project'] = $project->getId();
			return get_url('feed', 'project_activities', $params, null, false);
		} else {
			return get_url('feed', 'recent_activities', $params, null, false);
		} // if
	} // getRecentActivitiesFeedUrl

	/**
	 * Return iCalendar URL
	 *
	 * If $project is valid project instance calendar will be rendered just for that project, else it will be rendered
	 * for all active projects this user is involved with
	 *
	 * @param Project $project
	 * @return string
	 */
	function getICalendarUrl($project = null) {
		$params = array(
        'id' => $this->getId(),
        'token' => $this->getTwistedToken(),
		); // array

		if($project instanceof Project) {
			$params['project'] = $project->getId();
			return get_url('feed', 'project_ical', $params, null, false);
		} else {
			return get_url('feed', 'user_ical', $params, null, false);
		} // if
	} // getICalendarUrl

	// ---------------------------------------------------
	//  System functions
	// ---------------------------------------------------

	/**
	 * Validate data before save
	 *
	 * @access public
	 * @param array $errors
	 * @return void
	 */
	function validate(&$errors) {
		// Validate username if present
		if ($this->validatePresenceOf('username')) {
			if (!$this->validateUniquenessOf('username')) $errors[] = lang('username must be unique');
		} else {
			$errors[] = lang('username value required');
		} // if

		if (!$this->validatePresenceOf('token')) $errors[] = lang('password value required');

		// Validate email if present
		if ($this->validatePresenceOf('email')) {
			if (!$this->validateFormatOf('email', EMAIL_FORMAT)) $errors[] = lang('invalid email address');
			if (!$this->validateUniquenessOf('email')) $errors[] = lang('email address must be unique');
		} else {
			$errors[] = lang('email value is required');
		} // if
		// Company ID
		if (!$this->validatePresenceOf('company_id')) $errors[] = lang('company value required');
	} // validate

	/**
	 * Delete this object
	 *
	 * @param void
	 * @return boolean
	 */
	function delete() {	
		if($this->isAccountOwner()) {
			return false;
		} // if
				
		// Delete old global cache 
		if (GlobalCache::isAvailable()) {			
			$this->deleteUserFromGlobalCache($this->getId());			
		}
		
		$this->deleteAvatar();
		//$this->deletePersonalProject();
		MailAccountUsers::deleteByUser($this);
		GroupUsers::clearByUser($this);
		Contacts::updateUserIdOnUserDelete($this->getId());
		ProjectUsers::clearByUser($this);
		ObjectSubscriptions::clearByUser($this);
		ObjectReminders::clearByUser($this);
		EventInvitations::clearByUser($this);
		UserPasswords::clearByUser($this);
		return parent::delete();
	} // delete
	
	/**
	 * Deletes all of the users cached values from the Global Cache
	 *
	 * @param id = user_id
	 * @return boolean
	 */
	function deleteUserFromGlobalCache($id) {
		if (GlobalCache::isAvailable()) {						
				GlobalCache::delete('logged_user_'.$this->getId());
				GlobalCache::delete('active_ws_'.$this->getId());
				GlobalCache::delete('user_copt_obj_lastAccessedWorkspace');
				GlobalCache::delete('user_copt_obj_initialWorkspace');
				$opts = UserWsConfigOptions::findAll();				
				foreach($opts as $opt){
					$name = $opt->getName();
					GlobalCache::delete('user_config_option_'.$id.'_'.$name);					
				}
		}	
	}

	// ---------------------------------------------------
	//  ApplicationDataObject implementation
	// ---------------------------------------------------

	/**
	 * Return object name
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getObjectName() {
		return $this->getDisplayName();
	} // getObjectName

	/**
	 * Return object type name
	 *
	 * @param void
	 * @return string
	 */
	function getObjectTypeName() {
		return 'user';
	} // getObjectTypeName

	/**
	 * Return object URl
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getObjectUrl() {
		return $this->getCardUrl();
	} // getObjectUrl

	function getArrayInfo(){
		$result = array(
			'id' => $this->getId(),
			'name' => $this->getDisplayName(),
			'cid' => $this->getCompanyId());
		
		if ($this->getId() == logged_user()->getId())
			$result['isCurrent'] = true;
		
		return $result;
	}
	
	function getLocale() {
		$locale = user_config_option("localization", null, $this->getId());
		return $locale ? $locale : DEFAULT_LOCALIZATION;
	}
	/**
	 * Returns true if $user is logged user or if user is an administrator
	 *
	 * @param User $user
	 * @return boolean
	 */
	function canView(User $user) {
		if (logged_user()->getId()==$user->getId() || logged_user()->isAdministrator()) {
			return true;
		}//if
		return false;
	} // canView

	/**
	 * Array of email accounts
	 *
	 * @var array
	 */
	protected $mail_accounts;
	
	function hasMailAccounts(){
		if(is_null($this->mail_accounts))
			$this->mail_accounts = MailAccounts::getMailAccountsByUser(logged_user());
		return is_array($this->mail_accounts) && count($this->mail_accounts) > 0;
	}
	
	function isGuest() {
		return $this->getType() == 'guest';
	}
	
	function getAssignableUsers($project = null) {
		if ($this->isMemberOfOwnerCompany()) {
			return Users::getAll();
		}
		TimeIt::start('get assignable users');
		if ($project instanceof Project) {
			$ws = $project->getAllSubWorkspacesQuery(true);
		}
		$users = $this->getCompany()->getUsers();
		$uid = $this->getId();
		$cid = $this->getCompany()->getId();
		$tp = TABLE_PREFIX;
		$gids = "SELECT `group_id` FROM `{$tp}group_users` WHERE `user_id` = $uid";
		$q1 = "SELECT `project_id` FROM `{$tp}project_users` WHERE (`user_id` = $uid OR `user_id` IN ($gids)) AND `can_assign_to_other` = '1'";
		$q2 = "SELECT `project_id` FROM `{$tp}project_users` WHERE (`user_id` = $uid OR `user_id` IN ($gids)) AND `can_assign_to_owners` = '1'";
		if (isset($ws)) {
			 $q1 .= " AND `project_id` IN ($ws)";
			 $q2 .= " AND `project_id` IN ($ws)";
		}
		$query1 = "SELECT `user_id` FROM `{$tp}project_users` WHERE `project_id` IN ($q1)";
		$query2 = "SELECT `user_id` FROM `{$tp}project_users` WHERE `project_id` IN ($q2)";
		// get users from other client companies that share workspaces in which the user can assign to other clients' members
		$us1 = Users::findAll(array('conditions' => "`id` IN ($query1) AND `company_id` <> 1 AND `company_id` <> $cid"));
		// get users from the owner company that share workspaces in which the user can assign to owner company members
		$us2 = Users::findAll(array('conditions' => "`id` IN ($query2) AND `company_id` = 1"));
		$users = array_merge($users, $us1);
		$users = array_merge($users, $us2);
		TimeIt::stop();
		return $users;
	}
	
	function getAssignableCompanies($project = null) {
		if ($this->isMemberOfOwnerCompany()) {
			return Companies::getCompaniesWithUsers();
		}
		TimeIt::start('get assignable companies');
		if ($project instanceof Project) {
			$ws = $project->getAllSubWorkspacesQuery(true);
		}
		$uid = $this->getId();
		$cid = $this->getCompany()->getId();
		$tp = TABLE_PREFIX;
		$gids = "SELECT `group_id` FROM `{$tp}group_users` WHERE `user_id` = $uid";
		$q1 = "SELECT `project_id` FROM `{$tp}project_users` WHERE (`user_id` = $uid OR `user_id` IN ($gids)) AND `can_assign_to_other` = '1'";
		$q2 = "SELECT `project_id` FROM `{$tp}project_users` WHERE (`user_id` = $uid OR `user_id` IN ($gids)) AND `can_assign_to_owners` = '1'";
		if (isset($ws)) {
			 $q1 .= " AND `project_id` IN ($ws)";
			 $q2 .= " AND `project_id` IN ($ws)";
		}
		$query1 = "SELECT `user_id` FROM `{$tp}project_users` WHERE `project_id` IN ($q1)";
		$query2 = "SELECT `user_id` FROM `{$tp}project_users` WHERE `project_id` IN ($q2)";
		$query = "SELECT `company_id` FROM `{$tp}users` WHERE `id` IN ($query1) AND `company_id` <> 1 AND `company_id` <> $cid OR `id` IN ($query2) AND `company_id` = 1";
		// get companies for assignable users (see getAssignableUsers)
		$companies = Companies::findAll(array('conditions' => "`id` = $cid OR `id` IN ($query)"));
		TimeIt::stop();
		return $companies;
	}
	
} // User

?>
