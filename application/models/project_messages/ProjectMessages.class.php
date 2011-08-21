<?php

/**
 * ProjectMessages, generated on Sat, 04 Mar 2006 12:21:44 +0100 by
 * DataObject generation tool
 *
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class ProjectMessages extends BaseProjectMessages {

	function __construct() {
		parent::__construct();
	}

	public static function getWorkspaceString($ids = '?') {
		return " `id` IN (SELECT `object_id` FROM `" . TABLE_PREFIX . "workspace_objects` WHERE `object_manager` = 'ProjectMessages' AND `workspace_id` IN ($ids)) ";
	}
	 
	/**
	 * Return messages that belong to specific project
	 *
	 * @param Project $project
	 * @param boolean $include_private Include private messages in the result
	 * @return array
	 */
	static function getProjectMessages(Project $project, $include_private = false) {
		$condstr = self::getWorkspaceString();
		
		if ($include_private) {
			$conditions = array($condstr, $project->getId());
		} else {
			$conditions = array($condstr . ' AND `is_private` = ?', $project->getId(), false);
		} // if

		return self::findAll(array(
			'conditions' => $conditions,
			'order' => '`created_on` DESC',
		)); // findAll
	} // getProjectMessages

	/**
	 * Return project messages that are marked as important for specific project
	 *
	 * @param Project $project
	 * @param boolean $include_private Include private messages
	 * @return array
	 */
	static function getImportantProjectMessages(Project $project, $include_private = false) {
		$condstr = self::getWorkspaceString();
		if($include_private) {
			$conditions = array($condstr . ' AND `is_important` = ?', $project->getId(), true);
		} else {
			$conditions = array($condstr . ' AND `is_important` = ? AND `is_private` = ?', $project->getId(), true, false);
		} // if

		return self::findAll(array(
	        'conditions' => $conditions,
	        'order' => '`created_on` DESC',
		)); // findAll
	} // getImportantProjectMessages
	
	function getMessages($tag, $project = null, $start = null, $limit = null, $order = null, $order_dir = null, $archived = false) {
		switch ($order){
			case 'updatedOn':
				$order_crit = 'updated_on';
				break;
			case 'createdOn':
				$order_crit = 'created_on';
				break;
			case 'title':
				$order_crit = 'title';
				break;
			default:
				$order_crit = 'updated_on';
				break;
		}
		if (!$order_dir){
			switch ($order){
				case 'name': $order_dir = 'ASC'; break;
				default: $order_dir = 'DESC';
			}
		}

		if ($project instanceof Project) {
			$pids = $project->getAllSubWorkspacesQuery(!$archived);
			$wsConditions = " AND " . self::getWorkspaceString($pids);
		} else {
			$wsConditions = "";
		}
		

		if (!isset($tag) || $tag == '' || $tag == null) {
			$tagstr = "";
		} else {
			$tagstr = "AND (SELECT count(*) FROM `" . TABLE_PREFIX . "tags` WHERE `" .
				TABLE_PREFIX . "project_messages`.`id` = `" . TABLE_PREFIX . "tags`.`rel_object_id` AND `" .
				TABLE_PREFIX . "tags`.`tag` = " . DB::escape($tag) . " AND `" . TABLE_PREFIX . "tags`.`rel_object_manager` ='ProjectMessages' ) > 0 ";
		}
		
		$permissions = ' AND ( ' . permissions_sql_for_listings(ProjectMessages::instance(),ACCESS_LEVEL_READ, logged_user(), 'project_id') .')';

		if ($archived) $archived_cond = "`archived_by_id` <> 0";
		else $archived_cond = "`archived_by_id` = 0";
		
		$conditions = "`trashed_by_id` = 0 AND $archived_cond $wsConditions $tagstr  $permissions";
		$page = (integer) ($start / $limit) + 1;
		$order = "$order_crit $order_dir";

		return self::paginate(array(
			'conditions' => $conditions,
			'order' => $order
		), $limit, $page);
	}

} // ProjectMessages

?>