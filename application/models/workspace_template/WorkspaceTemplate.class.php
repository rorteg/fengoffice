<?php

/**
 *  WorkspaceTemplate class
 *
 * @author Ignacio de Soto
 */
class WorkspaceTemplate extends BaseWorkspaceTemplate {

	/**
	 * Returns the Workspace
	 *
	 * @return Project
	 */
	function getWorkspace() {
		return Projects::findById($this->getWorkspaceId());
	}
	
	function getTemplate() {
		return COTemplates::findById($this->getTemplateId());
	}
} // WorkspaceObject

?>