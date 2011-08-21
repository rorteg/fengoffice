<?php

  /**
  * ObjectReminder class
  * Generated on Mon, 29 May 2006 03:51:15 +0200 by DataObject generation tool
  *
  * @author Ignacio de Soto <ignacio.desoto@fengoffice.com>
  */
  class ObjectReminder extends BaseObjectReminder {
  
    /**
    * User who is to be notified
    *
    * @var User
    */
    private $user;
    
    /**
    * Object
    *
    * @var ProjectDataObject
    */
    private $object;
    
    /**
    * Return user object
    *
    * @param void
    * @return User
    */
    function getUser() {
      if(is_null($this->user)) $this->user = Users::findById($this->getUserId());
      return $this->user;
    } // getUser
    
    function setUser($user) {
    	$this->setUserId($user->getId());
    }
    
    /**
    * Return object
    *
    * @param void
    * @return ApplicationDataObject
    */
    function getObject() {
      if(is_null($this->object)) $this->object = get_object_by_manager_and_id($this->getObjectId(), $this->getObjectManager()); 
      return $this->object;
    } // getObject
    
    function setObject($object) {
    	$manager = get_class($object->manager());
    	$this->setObjectId($object->getId());
    	$this->setObjectManager($manager);
    }
    
  } // ObjectReminder 

?>