<?php

/**
 * ProjectWebpages, generated on Wed, 15 Mar 2006 22:57:46 +0100 by
 * DataObject generation tool
 *
 * @author Feng Office Dev Team <contact@opengoo.org>
 */
class ProjectWebpages extends BaseProjectWebpages {

	public static function getWorkspaceString($ids = '?') {
		return " `id` IN (SELECT `object_id` FROM `" . TABLE_PREFIX . "workspace_objects` WHERE `object_manager` = 'ProjectWebpages' AND `workspace_id` IN ($ids)) ";
	}
	
	/**
	 * Return all webpages that are involved in specific project
	 *
	 * @access public
	 * @param Project $project
	 * @param string $additional_conditions
	 * @return array
	 */
	function getWebpagesByProject(Project $project, $additional_conditions = null) {
		ProjectWebpages::findAll(array(
			'conditions' => array(
				self::getWorkspaceString(),
				$project->getId()
			)
		));
	}

	function getWebpages($project, $tag = '', $page = 1, $webpages_per_page = 10, $orderBy = 'title', $orderDir = 'ASC', $archived = false) {
		$orderDir = strtoupper($orderDir);
		if ($orderDir != "ASC" && $orderDir != "DESC") $orderDir = "ASC";
		if($page < 0) $page = 1;

		//$conditions = logged_user()->isMemberOfOwnerCompany() ? '' : ' `is_private` = 0';
		if ($tag == '' || $tag == null) {
			$tagstr = "1=1";
		} else {
			$tagstr = "(SELECT count(*) FROM `" . TABLE_PREFIX . "tags` WHERE `" .
					TABLE_PREFIX . "project_webpages`.`id` = `" . TABLE_PREFIX . "tags`.`rel_object_id` AND `" .
					TABLE_PREFIX . "tags`.`tag` = " . DB::escape($tag) ." AND `" . TABLE_PREFIX . "tags`.`rel_object_manager` = 'ProjectWebpages' ) > 0 ";
		}

		$permission_str = ' AND (' . permissions_sql_for_listings(ProjectWebpages::instance(), ACCESS_LEVEL_READ, logged_user()) . ')';
		
		if ($project instanceof Project) {
			$pids = $project->getAllSubWorkspacesCSV(true);
			$project_str = " AND " . self::getWorkspaceString($pids);
		} else {
			$project_str = "";
		}
		
		if ($archived) $archived_cond = " AND `archived_by_id` <> 0";
		else $archived_cond = " AND `archived_by_id` = 0";

		$conditions = $tagstr . $permission_str . $project_str . $archived_cond;
		
		return ProjectWebpages::paginate(
			array("conditions" => $conditions,
	        		'order' => DB::escapeField($orderBy)." $orderDir"),
				config_option('files_per_page', 10),
				$page
		); // paginate
	}


} // ProjectWebpages

?>