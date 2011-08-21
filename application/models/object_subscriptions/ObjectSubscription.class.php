<?php

  /**
  * ObjectSubscription class
  * Generated on Mon, 29 May 2006 03:51:15 +0200 by DataObject generation tool
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class ObjectSubscription extends BaseObjectSubscription {
  
    /**
    * User who is subscribed to this message
    *
    * @var User
    */
    private $user;
    
    /**
    * Object
    *
    * @var ApplicationDataObject
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
    
  } // ObjectSubscription 

?>