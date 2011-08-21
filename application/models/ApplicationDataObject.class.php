<?php

/**
 * Class that implements method common to all application objects (users, companies, projects etc)
 *
 * @version 1.0
 * @author Ilija Studen <ilija.studen@gmail.com>,  Marcos Saiz <marcos.saiz@fengoffice.com>
 */
abstract class ApplicationDataObject extends DataObject {

	// ---------------------------------------------------
	//  Search
	// ---------------------------------------------------

	/**
	 * If this object is searchable search related methods will be unlocked for it. Else this methods will
	 * throw exceptions pointing that this object is not searchable
	 *
	 * @var boolean
	 */
	protected $is_searchable = false;

	/**
	 * Array of searchable columns
	 *
	 * @var array
	 */
	protected $searchable_columns = array();
	protected $searchable_composite_columns = array();
	 
	/**
	 * Returns true if this object is searchable (maked as searchable and has searchable columns)
	 *
	 * @param void
	 * @return boolean
	 */
	function isSearchable() {
		return $this->is_searchable && is_array($this->searchable_columns) && (count($this->searchable_columns) > 0);
	} // isSearchable

	/**
	 * Returns array of searchable columns or NULL if this object is not searchable or there
	 * is no searchable columns
	 *
	 * @param void
	 * @return array
	 */
	function getSearchableColumns() {
		if(!$this->isSearchable()) return null;
		return $this->searchable_columns;
	} // getSearchableColumns

	/**
	 * This function will return content of specific searchable column. It can be overriden in child
	 * classes to implement extra behaviour (like reading file contents for project files)
	 *
	 * @param string $column_name Column name
	 * @return string
	 */
	function getSearchableColumnContent($column_name) {
		if(!$this->columnExists($column_name)) 
			throw new Error("Object column '$column_name' does not exist");
		return (string) $this->getColumnValue($column_name);
	} // getSearchableColumnContent

	/**
	 * Clear search index that is associated with this object
	 *
	 * @param void
	 * @return boolean
	 */
	function clearSearchIndex() {
		return SearchableObjects::dropContentByObject($this);
	} // clearSearchIndex

	function addToSearchableObjects($wasNew = false){
		$columns_to_drop = array();
		if ($wasNew)
			$columns_to_drop = $this->getSearchableColumns();
		else {
			foreach ($this->getSearchableColumns() as $column_name){
				if (isset($this->searchable_composite_columns[$column_name])){
					foreach ($this->searchable_composite_columns[$column_name] as $colName){
						if ($this->isColumnModified($colName)){
							$columns_to_drop[] = $column_name;
							break;
						}
					}
				} else if ($this->isColumnModified($column_name))
					$columns_to_drop[] = $column_name;
			}
		}
		 
		if (count($columns_to_drop) > 0){
			if (!$wasNew)
				SearchableObjects::dropContentByObjectColumns($this,$columns_to_drop);

			foreach($columns_to_drop as $column_name) {
				$content = $this->getSearchableColumnContent($column_name);
				if (get_class($this->manager()) == 'ProjectFiles') {
					$content = utf8_encode($content);
				}
				if(trim($content) <> '') {
					$searchable_object = new SearchableObject();
					 
					$searchable_object->setRelObjectManager(get_class($this->manager()));
					$searchable_object->setRelObjectId($this->getObjectId());
					$searchable_object->setColumnName($column_name);
					$searchable_object->setContent($content);
					$searchable_object->setProjectId(0);
					$searchable_object->setIsPrivate(false);

					$searchable_object->save();
				} // if
			} // foreach
		} // if
		 
		//Add Unique ID to search
		if ($wasNew){
			SearchableObjects::dropContentByObjectColumns($this, array('uid')); // Fixes Query failed with message 'Duplicate entry 'xxxxx-31-uid' for key 1'
			
			$searchable_object = new SearchableObject();

			$searchable_object->setRelObjectManager(get_class($this->manager()));
			$searchable_object->setRelObjectId($this->getObjectId());
			$searchable_object->setColumnName('uid');
			$searchable_object->setContent($this->getUniqueObjectId());
			$searchable_object->setProjectId(0);
			$searchable_object->setIsPrivate(false);

			$searchable_object->save();
		}
	}

	function save() {
		$wasNew = $this->isNew();
		$result = parent::save();

		if ($result && $this->isSearchable()){
			$this->addToSearchableObjects($wasNew);
		}

		return $result;
	} // save
	 
	function delete(){
		$this->clearEverything();
		return parent::delete();
	}
	
	/**
	 * Deletes the object from Searchable Objects
	 *
	 */
    function deleteFromSearchableObjects(){
    	$this->clearSearchIndex();      	
    }
	
	/**
	 * This function deletes everything related to the object.
	 * Child classes can call this method to clear everything
	 * but not delete the object. 
	 * @return void
	 */
	function clearEverything() {
		if($this->isSearchable()) {
			$this->clearSearchIndex();
		} // if
		if($this->isLinkableObject()) {
			$this->clearLinkedObjects();
		} // if
	}

	function getTitle(){
		return lang('no title');
	}

	// ---------------------------------------------------
	//  Linked Objects (Replacement for attached files)
	// ---------------------------------------------------

	/**
	 * Mark this object as linkable to another object (in this case other project data objects can be linked to
	 * this object)
	 *
	 * @var boolean
	 */
	protected $is_linkable_object= true;

	/**
	 * Array of all linked objects
	 *
	 * @var array
	 */
	protected $all_linked_objects;

	/**
	 * Cached array of linked objects (filtered by users access permissions)
	 *
	 * @var array
	 */
	protected $linked_objects;

	/**
	 * Cached array of linked objects (filtered by users access permissions and excluding trashed objects)
	 *
	 * @var array
	 */
	protected $linked_objects_no_trashed;



	/**
	 * Cached author object reference
	 *
	 * @var User
	 */
	private $created_by = null;

	/**
	 * Cached reference to user who created last update on object
	 *
	 * @var User
	 */
	private $updated_by = null;

	/**
	 * Cached reference to user who created last update on object
	 *
	 * @var User
	 */
	private $trashed_by = null;
	


	/*
	 * Object type identifier
	 *
	 * ch - ProjectChart
	 * cm - Comment
	 * ct - Contact
	 * co - Company
	 * cp - Chart Parameter
	 * d - ProjectFile
	 * d - ProjectFileRevision
	 * ev - ProjectEvent
	 * fo - ProjectForm
	 * gp - Group
	 * me - ProjectMessage
	 * mc - Mail Content
	 * mi - ProjectMilestone
	 * re - Report
	 * ro - ProjectContact (Role)
	 * ta - ProjectTask
	 * tg - Tag
	 * ts - Timeslot
	 * us - User
	 * wp - WebPages
	 * ws - Project (Workspace)
	 */
	protected $objectTypeIdentifier = '';

	/**
	 * Return object ID
	 *
	 * @param void
	 * @return integer
	 */
	function getObjectId() {
		return $this->columnExists('id') ? $this->getId() : null;
	} // getObjectId

	/**
	 * Return object name
	 *
	 * @param void
	 * @return string
	 */
	function getObjectName() {
		return $this->columnExists('name') ? $this->getName() : null;
	} // getObjectName

	function getUniqueObjectId(){
		$oid = $this->getObjectId();
		if ($oid < 10)
			$oid = '00' . $oid;
		else if ($oid < 100)
			$oid = '0' . $oid;
		 
		return $this->objectTypeIdentifier . $oid;
	}

	/**
	 * Return object type name - message, user, project etc
	 *
	 * @param void
	 * @return string
	 */
	function getObjectTypeName() {
		return '';
	} // getObjectTypeName
	
	/**
	 * Returns the object's manager's name.
	 *
	 * @return string
	 */
	function getObjectManagerName() {
		return get_class($this->manager());
	}

	/**
	 * Return object URL
	 *
	 * @param void
	 * @return string
	 */
	function getObjectUrl() {
		return '#';
	} // getObjectUrl

	/**
	 * Return time when this object was created
	 *
	 * @param void
	 * @return DateTime
	 */
	function getObjectCreationTime() {
		return $this->columnExists('created_on') ? $this->getCreatedOn() : null;
	} // getObjectCreationTime

	/**
	 * Return time when this object was updated last time
	 *
	 * @param void
	 * @return DateTime
	 */
	function getObjectUpdateTime() {
		return $this->columnExists('updated_on') ? $this->getUpdatedOn() : $this->getObjectCreationTime();
	} // getOjectUpdateTime

	/**
	 * Return time when this object was updated last time
	 *
	 * @param void
	 * @return DateTime
	 */
	function getViewHistoryUrl() {
		return get_url('object','view_history',array('id'=> $this->getId(), 'manager'=> get_class($this->manager)));
	} // getViewHistoryUrl

	// ---------------------------------------------------
	//  Created by
	// ---------------------------------------------------

	/**
	 * Return user who created this message
	 *
	 * @access public
	 * @param void
	 * @return User
	 */
	function getCreatedBy() {
		if(is_null($this->created_by)) {
			if($this->columnExists('created_by_id')) $this->created_by = Users::findById($this->getCreatedById());
		} //
		return $this->created_by;
	} // getCreatedBy

	/**
	 * Return display name of author
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getCreatedByDisplayName() {
		$created_by = $this->getCreatedBy();
		return $created_by instanceof User ? $created_by->getDisplayName() : lang('n/a');
	} // getCreatedByDisplayName

	/**
	 * Return card URL of created by user
	 *
	 * @param void
	 * @return string
	 */
	function getCreatedByCardUrl() {
		$created_by = $this->getCreatedBy();
		return $created_by instanceof User ? $created_by->getCardUrl() : null;
	} // getCreatedByCardUrl

	// ---------------------------------------------------
	//  Updated by
	// ---------------------------------------------------

	/**
	 * Return user who updated this object
	 *
	 * @access public
	 * @param void
	 * @return User
	 */
	function getUpdatedBy() {
		if(is_null($this->updated_by)) {
			if($this->columnExists('updated_by_id')) $this->updated_by = Users::findById($this->getUpdatedById());
		} //
		return $this->updated_by;
	} // getCreatedBy

	/**
	 * Return display name of author
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getUpdatedByDisplayName() {
		$updated_by = $this->getUpdatedBy();
		return $updated_by instanceof User ? $updated_by->getDisplayName() : lang('n/a');
	} // getUpdatedByDisplayName

	/**
	 * Return card URL of created by user
	 *
	 * @param void
	 * @return string
	 */
	function getUpdatedByCardUrl() {
		$updated_by = $this->getUpdatedBy();
		return $updated_by instanceof User ? $updated_by->getCardUrl() : null;
	} // getUpdatedByCardUrl
	
	// ---------------------------------------------------
	//  Trashed by
	// ---------------------------------------------------

	/**
	 * Return user who trashed this object
	 *
	 * @access public
	 * @param void
	 * @return User
	 */
	function getTrashedBy() {
		if(is_null($this->trashed_by)) {
			if($this->columnExists('trashed_by_id')) $this->trashed_by = Users::findById($this->getTrashedById());
		} //
		return $this->trashed_by;
	} // getTrashedBy

	/**
	 * Return display name of trasher
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getTrashedByDisplayName() {
		$trashed_by = $this->getTrashedBy();
		return $trashed_by instanceof User ? $trashed_by->getDisplayName() : lang('n/a');
	} // getTrashedByDisplayName

	/**
	 * Return card URL of trashed by user
	 *
	 * @param void
	 * @return string
	 */
	function getTrashedByCardUrl() {
		$trashed_by = $this->getTrashedBy();
		return $trashed_by instanceof User ? $trashed_by->getCardUrl() : null;
	} // getTrashedByCardUrl

	// ---------------------------------------------------
	//  Linked Objects
	// ---------------------------------------------------

	/**
	 * This function will return true if this object can have objects linked to it
	 *
	 * @param void
	 * @return boolean
	 */
	function isLinkableObject() {
		return $this->is_linkable_object;
	} // isLinkableObject

	/**
	 * Link object to this object
	 *
	 * @param ProjectDataObject $object
	 * @return LinkedObject
	 */
	function linkObject(ApplicationDataObject $object) {
		$manager_class = get_class($this->manager());
		$object_id = $this->getObjectId();

		$linked_object = LinkedObjects::findById(array(
        'rel_object_manager' => $manager_class,
        'rel_object_id' => $object_id,
        'object_id' => $object->getId(),
        'object_manager' => get_class($object->manager()),
		)); // findById

		if($linked_object instanceof LinkedObject) {
			return $linked_object; // Already linked
		}
		else
		{//check inverse link
			$linked_object = LinkedObjects::findById(array(
	        'rel_object_manager' => get_class($object->manager()),
	        'rel_object_id' => $object->getId(),
	        'object_id' => $object_id,
	        'object_manager' => $manager_class,
			)); // findById
			if($linked_object instanceof LinkedObject) {
				return $linked_object; // Already linked
			}
		} // if

		$linked_object = new LinkedObject();
		$linked_object->setRelObjectManager($manager_class);
		$linked_object->setRelObjectId($object_id);
		$linked_object->setObjectId($object->getId());
		$linked_object->setObjectManager(get_class($object->manager()));

		$linked_object->save();
		/*  if(!$object->getIsVisible()) {
		 $object->setIsVisible(true);
		 $object->setExpirationTime(EMPTY_DATETIME);
		 $object->save();
	  } // if*/
		return $linked_object;
	} // linkObject

	/**
	 * Return all linked objects
	 *
	 * @param void
	 * @return array
	 */
	function getAllLinkedObjects() {
	//	if(is_null($this->all_linked_objects)) {
			$this->all_linked_objects = LinkedObjects::getLinkedObjectsByObject($this);
	//	} // if
		return $this->all_linked_objects;
	} //  getAllLinkedObjects

	/**
	 * Return linked objects but filter the private ones if user is not a member
	 * of the owner company
	 *
	 * @param void
	 * @return array
	 */
	function getLinkedObjects() {
		if(logged_user()->isMemberOfOwnerCompany()) {
			$objects = $this->getAllLinkedObjects();
		} else {
			if (is_null($this->linked_objects)) {
				$this->linked_objects = LinkedObjects::getLinkedObjectsByObject($this, true);
			}
			$objects = $this->linked_objects;
		}
		if ($this instanceof ProjectDataObject && $this->isTrashed()) {
			$include_trashed = true;
		} else {
			$include_trashed = false;
		}
		if ($include_trashed) {
			return $objects;
		} else {
			$ret = array();
			if (is_array($objects) && count($objects)) {
				foreach ($objects as $o) {
					if (!$o instanceof ProjectDataObject || !$o->isTrashed()) {
						$ret[] = $o;
					}
				}
			}
			return $ret;
		}
	} // getLinkedObjects
	
	function copyLinkedObjectsFrom($object) {
		$linked_objects = $object->getAllLinkedObjects();
		if (is_array($linked_objects)) {
			foreach ($linked_objects as $lo) {
				$this->linkObject($lo);
			}
		}
	}
	
	/**
	 * Drop all relations with linked objects for this object
	 *
	 * @param void
	 * @return null
	 */
	function clearLinkedObjects() {
		return LinkedObjects::clearRelationsByObject($this);
	} // clearLinkedObjects

	/**
	 * Return link objects url
	 *
	 * @param void
	 * @return string
	 */
	function getLinkObjectUrl() {
		return get_url('object', 'link_to_object', array(
        'manager' => get_class($this->manager()),
        'object_id' => $this->getObjectId()
		)); // get_url
	} // getLinkedObjectsUrl

	/**
	 * Return object properties url
	 *
	 * @param void
	 * @return string
	 */
	function getObjectPropertiesUrl() {
		return get_url('object', 'view_properties', array(
        'manager' => get_class($this->manager()),
        'object_id' => $this->getObjectId()
		)); // get_url
	} // getLinkedObjectsUrl

	/**
	 * Return unlink object URL
	 *
	 * @param ProjectDataObject $object
	 * @return string
	 */
	function getUnlinkObjectUrl(ApplicationDataObject $object) {
		return get_url('object', 'unlink_from_object', array(
        'manager' => get_class($this->manager()),
        'object_id' => $this->getObjectId(),
        'rel_object_id' => $object->getId(),
        'rel_object_manager' => get_class($object->manager()),
		)); // get_url
	} //  getUnlinkedObjectUrl


	/**
	 * Returns true if user can link an object to this object
	 *
	 * @param User $user
	 * @param Project $project
	 * @return boolean
	 */
	function canLinkObject(User $user) {
		if(!$this->isLinkableObject()) return false;
		return $this->canEdit($user);
	} // canLinkObject

	/**
	 * Check if $user can un-link $object from this object
	 *
	 * @param User $user
	 * @param ProjectDataObject $object
	 * @return booealn
	 */
	function canUnlinkObject(User $user, ApplicationDataObject $object) {
		return $this->canEdit($user);
	} // canUnlinkObject



	function getProject() {
		//Logger::log("WARNING: Calling getProject() on an object with multiple workspaces.");
		return null;
	}
	
	function copy() {
		$class = get_class($this);
		$copy = new $class();
		$cols = $this->getColumns();
		$not_to_be_copied = array(
			'id',
			'created_on',
			'created_by_id',
			'updated_on',
			'updated_by_id',
			'trashed_on',
			'trashed_by_id',
		); // columns with special meanings that are not to be copied
		foreach ($cols as $col) {
			if (!in_array($col, $not_to_be_copied)) {
				$copy->setColumnValue($col, $this->getColumnValue($col));
			}
		}
		return $copy;
	}
	
	function isTrashed() {
		return false;
	}
	
// ---------------------------------------------------
	//  Object Properties
	// ---------------------------------------------------
	/**
	 * Returns whether an object can have properties
	 *
	 * @return bool
	 */
	function isPropertyContainer(){
		return $this->is_property_container;
	}

	/**
	 * Given the object_data object (i.e. file_data) this function
	 * updates all ObjectProperties (deleting or creating them when necessary)
	 *
	 * @param  $object_data
	 */
	function save_properties($object_data){
		$properties = array();
		for($i = 0; $i < 200; $i++) {
			if(isset($object_data["property$i"]) && is_array($object_data["property$i"]) &&
			(trim(array_var($object_data["property$i"], 'id')) <> '' || trim(array_var($object_data["property$i"], 'name')) <> '' ||
			trim(array_var($object_data["property$i"], 'value')) <> '')) {
				$name = array_var($object_data["property$i"], 'name');
				$id = array_var($object_data["property$i"], 'id');
				$value = array_var($object_data["property$i"], 'value');
				if($id && trim($name)=='' && trim($value)=='' ){
					$property = ObjectProperties::findById($id);
					$property->delete( 'id = $id');
				}else{
					if($id){
						{
							SearchableObjects::dropContentByObjectColumn($this, 'property' . $id);
							$property = ObjectProperties::findById($id);
						}
					}else{
						$property = new ObjectProperty();
						$property->setRelObjectId($this->getId());
						$property->setRelObjectManager(get_class($this->manager()));
					}
					$property->setFromAttributes($object_data["property$i"]);
					$property->save();
						
					if ($this->isSearchable())
					$this->addPropertyToSearchableObject($property);
				}
			} // if
			else break;
		} // for
	}

	function addPropertyToSearchableObject(ObjectProperty $property){
		$searchable_object = new SearchableObject();
		 
		$searchable_object->setRelObjectManager(get_class($this->manager()));
		$searchable_object->setRelObjectId($this->getObjectId());
		$searchable_object->setColumnName('property'.$property->getId());
		$searchable_object->setContent($property->getPropertyValue());
		$searchable_object->setIsPrivate(false);
	  
		$searchable_object->save();
	}

	/**
	 * Get one value of a property. Returns an empty string if there's no value.
	 *
	 * @param string $name
	 * @return string
	 */
	function getProperty($name) {
		$op = ObjectProperties::getPropertyByName($this, $name);
		if ($op instanceof ObjectProperty) {
			return $op->getPropertyValue();
		} else {
			return "";
		}
	}

	/**
	 * Return all values of a property
	 *
	 * @param string $name
	 * @return array
	 */
	function getProperties($name) {
		$ops = ObjectProperties::getAllProperties($this, $name);
		$ret = array();
		foreach ($ops as $op) {
			$ret[] = $op->getPropertyValue();
		}
		return $ret;
	}
	
	/**
	 * Returns all ObjectProperties of the object.
	 *
	 * @return array
	 */
	function getCustomProperties() {
		return ObjectProperties::getAllPropertiesByObject($this);
	}
	
	/**
	 * Copies custom properties from an object
	 * @param ProjectDataObject $object
	 */
	function copyCustomPropertiesFrom($object) {
		$properties = $object->getCustomProperties();
		foreach ($properties as $property) {
			$copy = new ObjectProperty();
			$copy->setPropertyName($property->getPropertyName());
			$copy->setPropertyValue($property->getPropertyValue());
			$copy->setObject($this);
			$copy->save();
		}
	}

	/**
	 * Sets the value of a property, removing all its previous values.
	 *
	 * @param string $name
	 * @param string $value
	 */
	function setProperty($name, $value) {
		$this->deleteProperty($name);
		$this->addProperty($name, $value);
	}

	/**
	 * Adds a value to property $name
	 *
	 * @param string $name
	 * @param string $value
	 */
	function addProperty($name, $value) {
		$op = new ObjectProperty();
		$op->setRelObjectId($this->getId());
		$op->setRelObjectManager(get_class($this->manager()));
		$op->setPropertyName($name);
		$op->setPropertyValue($value);
		$op->save();
	}

	/**
	 * Deletes all values of property $name.
	 *
	 * @param string $name
	 */
	function deleteProperty($name) {
		ObjectProperties::deleteByObjectAndName($this, $name);
	}
	

	function clearObjectProperties(){
		ObjectProperties::deleteAllByObject($this);
		if ($this->isSearchable()){
			SearchableObjects::dropObjectPropertiesByObject($this);
		}
	}

	// ---------------------------------------------------
	//  Utilities
	// ---------------------------------------------------

	protected function isInCsv($value, $csv){
		$arr = explode(',',$csv);
		foreach($arr as $s)
			if (intval($s) == $value)
				return true;
		return false;
	}


} // ApplicationDataObject

?>