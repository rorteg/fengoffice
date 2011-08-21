<?php

/**
 *  WorkspaceTemplates class
 *
 * @author Ignacio de Soto
 */
class WorkspaceTemplates extends BaseWorkspaceTemplates {
	
	 
	/**
	 * Returns all Templates of a Workspace
	 *
	 * @param integer $workspace_id
	 * @return array
	 */
	static function getTemplatesByWorkspace($workspace_id) {
		$all = self::findAll(array('conditions' => array('`workspace_id` = ?', $workspace_id) ));
		if (!is_array($all)) return array();
		$objs = array();
		foreach ($all as $obj) {
			$template = $obj->getTemplate();
			if ($template instanceof COTemplate)
				$objs[] = $obj->getTemplate();
		}
		return $objs;
	}
	/**
	 * Returns all Workspaces an Template belongs to
	 *
	 * @param $object_manager
	 * @param $Template_id
	 * @return array
	 */
	static function getWorkspacesByTemplate($template_id, $wsCSV = null){
		$all = self::findAll(array('conditions' => "`template_id` = $template_id" . ($wsCSV ? " AND `workspace_id` IN ($wsCSV)":'')));
		if (!is_array($all)) return array();
		$csv = "";
		foreach ($all as $w) {
			if ($csv != "") $csv .= ",";
			$csv .= $w->getWorkspaceId();
		}
		return Projects::findByCSVIds($csv);
	}
	/**
	 * Returns true if an Template is in a Workspace
	 *
	 * @param $object_manager
	 * @param $Template_id
	 * @param $workspace_id
	 * @return boolean
	 */
	static function isTemplateInWorkspace($template_id, $workspace_id){
		try {
			return count(self::find(array('conditions' => array("`template_id` = ? AND `workspace_id` = ?", $template_id, $workspace_id)))) > 0;
		} catch (Exception $e) {
			return false;
		}
	} // isTemplateInWorkspace
	
	/**
	 * Returns one Workspace Template given WS id and template id and manager
	 *
	 * @param unknown_type $workspace_id
	 * @param unknown_type $template_id
	 * @param unknown_type $object_manager
	 */
	function getByTemplateAndWorkspace($workspace_id,$template_id){
		return self::find(array('conditions' => 
			array("`template_id` = ".$template_id." AND `workspace_id` = ". $workspace_id)));		
	} //getByTemplateAndWorkspace
	
	/**
	 * delete all workspace-template associations for a given template
	 *
	 * @param unknown_type $template_id
	 * @param unknown_type $object_manager
	 * @return unknown
	 */
	function deleteByTemplate($template_id){
		return self::delete(array("`template_id` = ".$template_id));		
	}
} // WorkspaceTemplates

?>