<?php

/**
 *  WorkspaceObjects class
 *
 * @author Ignacio de Soto
 */
class WorkspaceObjects extends BaseWorkspaceObjects {
	/**
	 * Returns all Objects of a Workspace
	 *
	 * @param integer $workspace_id
	 * @return array
	 */
	static function getObjectsByWorkspace($workspace_id) {
		$all = self::findAll(array('conditions' => array('`workspace_id` = ?', $workspace_id) ));
		if (!is_array($all)) return array();
		$objs = array();
		foreach ($all as $obj) {
			$objs[] = get_object_by_manager_and_id($obj->getObjectId(), $obj->getObjectManager());
		}
		return $objs;
	}
	/**
	 * Returns all Workspaces an Object belongs to
	 *
	 * @param $object_manager
	 * @param $object_id
	 * @return array
	 */
	static function getWorkspacesByObject($object_manager, $object_id, $wsCSV = null){
		$all = self::findAll(array('conditions' => "`object_manager` = '$object_manager' AND `object_id` = $object_id" . ($wsCSV ? " AND `workspace_id` IN ($wsCSV)":'')));//array('`object_manager` = ? AND `object_id` = ?', $object_manager, $object_id)));
		if (!is_array($all) || count($all) == 0) return array();
		$wss = array();
		foreach ($all as $w) {
			$ws = Projects::findById($w->getWorkspaceId());
			if ($ws == null) continue;
			$wss[] = $ws;		
		}
		return $wss;				
	}
	/**
	 * Returns true if an Object is in a Workspace
	 *
	 * @param $object_manager
	 * @param $object_id
	 * @param $workspace_id
	 * @return boolean
	 */
	static function isObjectInWorkspace($object_manager, $object_id, $workspace_id){
		try {
			return count(self::find(array('conditions' => array("`object_manager` = ? AND `object_id` = ? AND `workspace_id` ?", $object_manager, $object_id, $workspace_id)))) > 0;
		} catch (Exception $e) {
			return false;
		}
	} // isObjectInWorkspace
	
	
	static function addObjectToWorkspace($object, $workspace) {
		$wo = new WorkspaceObject();
		$wo->setWorkspace($workspace);
		$wo->setObject($object);
		$wo->save();
	}
} // WorkspaceObjects

?>