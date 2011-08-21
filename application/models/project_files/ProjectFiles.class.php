<?php

/**
* ProjectFiles, generated on Tue, 04 Jul 2006 06:46:08 +0200 by 
* DataObject generation tool
*
* @author Ilija Studen <ilija.studen@gmail.com>
*/
class ProjectFiles extends BaseProjectFiles {
    
	const ORDER_BY_NAME = 'name';
	const ORDER_BY_POSTTIME = 'dateCreated';
	const ORDER_BY_MODIFYTIME = 'dateUpdated';
	const TYPE_DOCUMENT = 0;
	const TYPE_WEBLINK = 1;
	
	public function __construct() {
		parent::__construct();
	}
	
	public static function getWorkspaceString($ids = '?') {
		return " `id` IN (SELECT `object_id` FROM `" . TABLE_PREFIX . "workspace_objects` WHERE `object_manager` = 'ProjectFiles' AND `workspace_id` IN ($ids)) ";
	}
	
	/**
	* Array of types that will script treat as images (provide thumbnail, add 
	* it to insert image editor function etc)
	*
	* @var array
	*/
	static public $image_types = array(
		'image/jpg', 'image/jpeg', 'image/pjpeg',
		'image/gif',
		'image/png'
	); // array
	
	/**
	* Return paged project files
	*
	* @param Project $project
	* @param ProjectFolder $folder
	* @param boolean $hide_private Don't show private files
	* @param string $order Order files by name or by posttime (desc)
	* @param integer $page Current page
	* @param integer $files_per_page Number of files that will be showed per single page
	* @param boolean $group_by_order Group files by order field
	* added by msaiz 03/10/07:
	* @param string for tag filter
	* @return array
	* 
	*/
	static function getProjectFiles($project = null, $folderId = null, $hide_private = false, $order = null, $orderdir = 'ASC', $page = null, $files_per_page = null, $group_by_order = false, $tag = null, $type_string = null, $userId = null, $archived = false) {
		if ($order == self::ORDER_BY_POSTTIME) {
			$order_by = '`created_on` ' . $orderdir;
		} else if ($order == self::ORDER_BY_MODIFYTIME) {
			$order_by = '`updated_on` ' . $orderdir;
		} else {
			$order_by = '`filename`' . $orderdir;
		} // if
		
		if ((integer) $page < 1) {
			$page = 1;
		} // if
		if ((integer) $files_per_page < 1) {
			$files_per_page = 10;
		} // if		
		
		if ($project instanceof Project){
			$pids = $project->getAllSubWorkspacesQuery(!$archived);
			$projectstr = " AND " . self::getWorkspaceString($pids);
		} else {
			$projectstr = "";
		}
		
		
		if ($tag == '' || $tag == null) {
			$tagstr = "";
		} else {
			$tagstr = " AND (select count(*) from " . TABLE_PREFIX . "tags where " .
				TABLE_PREFIX . "project_files.id = " . TABLE_PREFIX . "tags.rel_object_id and " .
				TABLE_PREFIX . "tags.tag = ".DB::escape($tag)." and " . TABLE_PREFIX . "tags.rel_object_manager ='ProjectFiles' ) > 0 ";
		}
		if ($type_string == '' || $type_string == null) {
			$typestr = "";
		} else {
			$types = explode(',',$type_string);
			$typessql = '( ';
			$cant = count($types);
			$n=0;
			foreach ($types as $type){
				$type .= '%';				
				$typessql .= ' ' . TABLE_PREFIX . "project_file_revisions.type_string LIKE ".DB::escape($type);
				$n++;
				$n != $cant? $typessql .= ' OR ': $typessql .= ' )';
			}			
			$typestr = " AND  (select count(*) from " . TABLE_PREFIX . "project_file_revisions where " .
				$typessql ." AND " . TABLE_PREFIX .	"project_files.id = " .
				TABLE_PREFIX . "project_file_revisions.file_id)";
			
		}
		if ($userId == null || $userId == 0) {
			$userstr = "";
		} else {
			$userstr = " AND `created_by_id` = ".DB::escape($userId)." ";
		}
		$permissionstr = ' AND ( ' . permissions_sql_for_listings(ProjectFiles::instance(),ACCESS_LEVEL_READ, logged_user()) . ') ';
		
		if ($archived) $archived_cond = " AND `archived_by_id` <> 0";
		else $archived_cond = " AND `archived_by_id` = 0";
		
		$otherConditions = $projectstr . $tagstr . $typestr . $userstr . $permissionstr . $archived_cond;
		
		if ($hide_private) {
			$conditions = array('`is_visible` = ?' . $otherConditions,  true);
		} else {
			$conditions = array(' true ' . $otherConditions);
		}
		
		list($files, $pagination) = ProjectFiles::paginate(array(
				'conditions' => $conditions,
				'order' => $order_by
			), $files_per_page, $page);
		
		return array($files, $pagination);
	} // getProjectFiles
	
	/**
	 * Gets project files that satisfy condition and that the user can read
	 *
	 * @param unknown_type $condition
	 */
	function getUserFiles($user = null, $workspace = null, $tag = null, $type_string = null, $order = null, $orderdir = 'ASC', $offset = 0, $limit = 0, $include_sub_workspaces = true, $archived = false) {
		if (!$user instanceof User) $user = logged_user();

		if ($workspace instanceof Project){
			if ($include_sub_workspaces) {
				$wsids = $workspace->getAllSubWorkspacesQuery(!$archived);
			} else {
				$wsids = "".$workspace->getId();
			}
			$wscond = " AND " . self::getWorkspaceString($wsids);
		} else {
			$wscond = "";
		}
		
		if ($tag == '' || $tag == null) {
			$tagcond = "";
		} else {
			$tagcond = " AND (SELECT count(*) FROM `" . TABLE_PREFIX . "tags` WHERE `" .
				TABLE_PREFIX . "project_files`.`id` = `" . TABLE_PREFIX . "tags`.`rel_object_id` AND `" .
				TABLE_PREFIX . "tags`.`tag` = ".DB::escape($tag)." AND `" . TABLE_PREFIX . "tags`.`rel_object_manager` ='ProjectFiles' ) > 0 ";
		}
		
		if ($type_string == '' || $type_string == null) {
			$typecond = "";
		} else {
			$types = explode(',',$type_string);
			$typessql = '(';
			$cant = count($types);
			$n=0;
			foreach ($types as $type){
				$type .= '%';
				$typessql .= ' ' . TABLE_PREFIX . "project_file_revisions.type_string LIKE ".DB::escape($type);
				$n++;
				$n != $cant? $typessql .= ' OR ': $typessql .= ' )';
			}
			
				$typecond = " AND  (SELECT count(*) FROM " . TABLE_PREFIX . "project_file_revisions WHERE " .
				$typessql ." AND " . TABLE_PREFIX ."project_files.id = " . TABLE_PREFIX . "project_file_revisions.file_id)";				
				
		}
		
		$permissions = ' AND ( ' . permissions_sql_for_listings(ProjectFiles::instance(), ACCESS_LEVEL_READ, $user) . ') ';
		
		if ($archived) {
			$archived_cond = " `archived_by_id` <> 0";
		} else {
			$archived_cond = " `archived_by_id` = 0";
		}
		
		$conditions = $archived_cond . $wscond . $tagcond . $typecond . $permissions;
		
		if ($order == self::ORDER_BY_POSTTIME) {
			$order_by = '`created_on` ' . $orderdir;
		} else if ($order == self::ORDER_BY_MODIFYTIME) {
			$order_by = '`updated_on` ' . $orderdir;
		} else {
			$order_by = '`filename`' . $orderdir;
		}
		return self::findAll(array(
			'conditions' => $conditions,
			'order' => $order_by,
			'offset' => $offset,
			'limit' => $limit
		));
	}
	
	/**
	* Orphened files are files that are not part of any folder, but project itself
	*
	* @param Project $project
	* @param boolean $show_private
	* @return null
	*/
	static function getOrphanedFilesByProject(Project $project, $show_private = false, $archived = false) {
		$condstr = self::getWorkspaceString();
		
		if ($archived) $archived_cond = " AND `archived_by_id` <> 0";
		else $archived_cond = " AND `archived_by_id` = 0";
		
		if ($show_private) {
			$conditions = array($condstr, $project->getId());
		} else {
			$conditions = array($condstr . ' AND `is_private` = ?', $project->getId(), false);
		} // if
		
		$conditions .= $archived_cond;
		
		return self::findAll(array(
			'conditions' => $conditions,
			'order' => '`filename`',
		));
	} // getOrphanedFilesByProject
	
	/**
	* Return all project files
	*
	* @param Project $project
	* @return array
	*/
	static function getAllFilesByProject(Project $project, $archived = false) {
		$condstr = self::getWorkspaceString();
		if ($archived) $condstr .= " AND `archived_by_id` <> 0";
		else $condstr .= " AND `archived_by_id` = 0";
		
		return self::findAll(array(
			'conditions' => array($condstr, $project->getId())
		)); // findAll
	} // getAllFilesByProject
	
	/**
	* Return all project files that were automatically checked out (on edit) by the user
	*
	* @param User $user 
	* @return array
	*/
	static function closeAutoCheckedoutFilesByUser($user = null) {
		if(!$user)
			$user = logged_user();
		try{
			$condstr = 'checked_out_by_id = ' . $user->getId() . ' AND was_auto_checked_out = 1 AND checked_out_on <> \'' . EMPTY_DATETIME .'\'';
			$files = self::findAll(array(
				'conditions' => $condstr
			)); // findAll
			if($files){				
				foreach ($files as $file) {				
					$file->setWasAutoCheckedAuto($autoCheckOut);	
					$file->setCheckedOutById(0);
					$file->setCheckedOutOn(EMPTY_DATETIME);
					$file->setMarkTimestamps(false);
					$file->save();
				}
				return true;
			}
			return false;
		}
		catch (Exception $exc){
			flash_error(lang('error checkin file'));
			return false;
		}
			
	} // getAllFilesByProject
	
	
	/**
	* Return file by name.
	*
	* @param $filename
	* @return array
	*/
	static function getByFilename($filename, $order = '`id` DESC') {
		$conditions = array('`filename` = ?', $filename);
		
		return self::findOne(array(
			'conditions' => $conditions,
			'order' => $order,
		));
	} // getByFilename
	
	static function getAllByFilename($filename, $project_ids = null) {
		$projectstr = '';
		if ($project_ids){
			$projectstr = " AND " . self::getWorkspaceString($project_ids);
		}
		
		$conditions = array('`filename` = ?' . $projectstr, $filename);
		
		return self::findAll(array(
			'conditions' => $conditions
		));
	} // getAllByFilename
	
	/**
	* Return files index page
	*
	* @param string $order_by
	* @param integer $page
	* @return string
	*/
	static function getIndexUrl($order_by = null, $page = null) {
		if (($order_by <> ProjectFiles::ORDER_BY_NAME) && ($order_by <> ProjectFiles::ORDER_BY_POSTTIME)) {
			$order_by = ProjectFiles::ORDER_BY_POSTTIME;
		} // if
		
		// #PAGE# is reserved as a placeholder
		if ($page <> '#PAGE#') {
			$page = (integer) $page > 0 ? (integer) $page : 1;
		} // if
		
		return get_url('files', 'index', array(
			'active_project' => active_project()->getId(),
			'order' => $order_by,
			'page' => $page
		)); // array
	} // getIndexUrl
	
	/**
	* Return important project files
	*
	* @param Project $project
	* @param boolean $include_private
	* @return array
	*/
	static function getImportantProjectFiles(Project $project, $include_private = false, $archived = false) {
		$condstr = self::getWorkspaceString();
		if ($include_private) {
			$conditions = array($condstr . ' AND `is_important` = ?', $project->getId(), true);
		} else {
			$conditions = array($condstr . ' AND `is_important` = ? AND `is_private` = ?', $project->getId(), true, false);
		} // if
		if ($archived) $archived_cond = " AND `archived_by_id` <> 0";
		else $archived_cond = " AND `archived_by_id` = 0";
		$conditions .= $archived_cond;
		
		return self::findAll(array(
			'conditions' => $conditions,
			'order' => '`created_on`',
		));
	} // getImportantProjectFiles
	
	/**
	* Handle files uploaded using helper forms. This function will return array of uploaded 
	* files when finished
	*
	* @param Project $project
	* @param string $files_var_prefix If value of this variable is set only elements in $_FILES
	*   with key starting with $files_var_prefix will be handled
	* @return array
	*/
	static function handleHelperUploads(Project $project, $files_var_prefix = null) {
		if (!isset($_FILES) || !is_array($_FILES) || !count($_FILES)) {
			return null; // no files to handle
		} // if
		
		$uploaded_files = array();
		foreach( $_FILES as $uploaded_file_name => $uploaded_file) {
			if ((trim($files_var_prefix) <> '') && !str_starts_with($uploaded_file_name, $files_var_prefix)) {
				continue;
			} // if
			
			if (!isset($uploaded_file['name']) || !isset($uploaded_file['tmp_name']) || !is_file($uploaded_file['tmp_name'])) {
				continue;
			} // if
			
			$uploaded_files[$uploaded_file_name] = $uploaded_file;
		} // foreach
		
		if (!count($uploaded_file)) {
			return null; // no files to handle
		} // if
		
		$result = array(); // we'll put all files here
		$expiration_time = DateTimeValueLib::now()->advance(1800, false);
		
		foreach ($uploaded_files as $uploaded_file) {
			$file = new ProjectFile();
			
			$file->setProjectId($project->getId());
			$file->setFilename($uploaded_file['name']);
			$file->setIsVisible(false);
			$file->setExpirationTime($expiration_time);
			$file->save();
			
			$file->handleUploadedFile($uploaded_file); // initial version
			
			$result[] = $file;
		} // foreach
		
		return count($result) ? $result : null;
	} // handleHelperUploads
	
	function findByCSVIds($ids, $additional_conditions = NULL) {
		if (isset($additional_conditions)) {
			$additional_conditions = " AND $additional_conditions";
		} else {
			$additional_conditions = "";
		}
		return self::findAll(array('conditions' => "`id` IN ($ids) $additional_conditions"));
	}
  
} // ProjectFiles 

?>