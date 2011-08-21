<?php

/**
 * COTemplate class
 * Generated on Sat, 04 Mar 2006 12:50:11 +0100 by DataObject generation tool
 *
 * @author Ignacio de Soto <ignacio.desoto@gmail.com>
 */
class COTemplate extends BaseCOTemplate {

	protected $is_commentable = true;
	
	function getWorkspaces() {
		if ($this->isNew()) return array();
		return WorkspaceTemplates::getWorkspacesByTemplate($this->getId());
	}
	
	function getObjects() {
		if ($this->isNew()) return array();
		return TemplateObjects::getObjectsByTemplate($this->getId());
	}
	
	function removeObjects() {
		if (!$this->isNew()) {
			return TemplateObjects::deleteObjectsByTemplate($this->getId());
		}
	}
	
	function hasObject($object) {
		return TemplateObjects::templateHasObject($this, $object);
	}
	
	function addObject($object) {
		if ($this->hasObject($object)) return;
		if (!$object->isTemplate() && $object->canBeTemplate()) {
			// the object isn't a template but can be, create a template copy
			$copy = $object->copy();
			$copy->setColumnValue('is_template', true);
			if ($copy instanceof ProjectTask) {
				// don't copy milestone and parent task
				$copy->setMilestoneId(0);
				$copy->setParentId(0);
			}
			$copy->save();
			// copy subtasks
			if ($copy instanceof ProjectTask) {
				ProjectTasks::copySubTasks($object, $copy, true);
			} else if ($copy instanceof ProjectMilestone) {
				ProjectMilestones::copyTasks($object, $copy, true);
			}
			// copy tags
			$tags = implode(',',$object->getTagNames());
			$copy->setTagsFromCSV($tags);
			// copy custom properties			
			$copy->copyCustomPropertiesFrom($object);
			// copy linked objects
			$linked_objects = $object->getAllLinkedObjects();
			if (is_array($linked_objects)) {
				foreach ($linked_objects as $lo) {
					$copy->linkObject($lo);
				}
			}
			// copy reminders
			$reminders = ObjectReminders::getByObject($object);
			foreach ($reminders as $reminder) {
				$copy_reminder = new ObjectReminder();
				$copy_reminder->setContext($reminder->getContext());
				$copy_reminder->setDate(EMPTY_DATETIME);
				$copy_reminder->setMinutesBefore($reminder->getMinutesBefore());
				$copy_reminder->setObject($copy);
				$copy_reminder->setType($reminder->getType());
				$copy_reminder->setUserId($reminder->getUserId());
				$copy_reminder->save();
			}
			$template = $copy;
		} else {
			// the object is already a template or can't be one, use it as it is
			$template = $object;
		}
		$to = new TemplateObject();
		$to->setObject($template);
		$to->setTemplate($this);
		$to->save();
		return $template->getId();
	}
	
	// ---------------------------------------------------
	//  Permissions
	// ---------------------------------------------------

	/**
	 * Returns true if specific user has CAN_MANAGE_TEMPLATES permission set to true
	 *
	 * @access public
	 * @param User $user
	 * @return boolean
	 */
	function canManage(User $user) {		
		return can_manage_templates($user);
	} // canManage

	/**
	 * Returns true if $user can view this template
	 *
	 * @param User $user
	 * @return boolean
	 */
	function canView(User $user) {
		return can_manage_templates($user);
	} // canView

	/**
	 * Check if specific user can add new templates to specific project
	 *
	 * @access public
	 * @param User $user
	 * @param Project $project
	 * @return boolean
	 */
	function canAdd(User $user, Project $project) {
		return can_manage_templates($user);
	} // canAdd

	/**
	 * Check if specific user can edit this template
	 *
	 * @access public
	 * @param User $user
	 * @return boolean
	 */
	function canEdit(User $user) {
		return can_manage_templates($user);
	} // canEdit


	/**
	 * Check if specific user can delete this template
	 *
	 * @access public
	 * @param User $user
	 * @return boolean
	 */
	function canDelete(User $user) {
		return can_manage_templates($user);
	} // canDelete

	// ---------------------------------------------------
	//  URL
	// ---------------------------------------------------

	function getViewUrl() {
		return get_url('template', 'view', array('id' => $this->getId()));
	} // getViewUrl

	/**
	 * Return edit template URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getEditUrl() {
		return get_url('template', 'edit', array('id' => $this->getId()));
	} // getEditUrl

	/**
	 * Return delete template URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getDeleteUrl() {
		return get_url('template', 'delete', array('id' => $this->getId()));
	} // getDeleteUrl

	function getAssignTemplateToWSUrl() {
		return get_url('template', 'assign_to_ws', array('id' => $this->getId()));
	}
	
	// ---------------------------------------------------
	//  System functions
	// ---------------------------------------------------

	/**
	 * Validate before save
	 *
	 * @access public
	 * @param array $errors
	 * @return boolean
	 */
	function validate(&$errors) {
		if(!$this->validatePresenceOf('name')) $errors[] = lang('template name required');
	} // validate

	/**
	 * Delete this object and reset all relationship. This function will not delete any of related objec
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function delete() {
		// permanently delete objects set as template (were created specifically for this template)
		$objs = $this->getObjects();
		foreach ($objs as $o) {
			if ($o->isTemplate()) {
				$o->delete();
			}
		}
		$this->removeObjects();
		TemplateParameters::deleteParametersByTemplate($this->getId());
		TemplateObjectProperties::deletePropertiesByTemplate($this->getId());
		parent::delete();
	} // delete

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
		return 'template';
	} // getObjectTypeName

	/**
	 * Return object URl
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getObjectUrl() {
		return $this->getViewUrl();
	} // getObjectUrl

	function getTitle() {
		return $this->getName();
	}
	
	function getArrayInfo() {
		return array(
			'id' => $this->getId(),
			't' => $this->getName(),
			//'wsid' => $this->getWorkspacesIdsCSV(),
			'c' => $this->getCreatedOn() instanceof DateTimeValue ? $this->getCreatedOn()->getTimestamp() : 0,
			'cid' => $this->getCreatedById()
		);
	}
	
} // COTemplate

?>