<?php

/**
 *  WorkspaceObject class
 *
 * @author Ignacio de Soto
 */
class WorkspaceObject extends BaseWorkspaceObject {

	/**
	 * Returns the Workspace
	 *
	 * @return Project
	 */
	function getWorkspace() {
		return Projects::findById($this->getWorkspaceId());
	}
	
	function getObject() {
		return get_object_by_manager_and_id($this->getObjectId(), $this->getObjectManager());
	}
	
	function setWorkspace($workspace) {
		if ($workspace instanceof Project) {
			$this->setWorkspaceId($workspace->getId());
		}
	}
	
	function setObject($object) {
		if ($object instanceof DataObject) {
			$this->setObjectId($object->getId());
			$this->setObjectManager(get_class($object->manager()));
		}
	}
} // WorkspaceObject

?>