<?php

  /**
  * ObjectUserPermission class
  * Written on Tue, 27 Oct 2007 16:53:08 -0300
  *
  * @author Marcos Saiz <marcos.saiz@fengoffice.com>
  */
  class ObjectUserPermission extends BaseObjectUserPermission {
    
    /**
    * Object 
    *
    * @var ObjectType
    */
    private $object;    
    
    /**
    * User
    *
    * @var ObjectType
    */
    private $user;    
    
    /**
    * Return parent object object
    *
    * @param void
    * @return ProjectObject
    */
    function getObject() {
      if(is_null($this->object)) {
        $this->object = ObjectUserPermissions::findById($this->getObjectId());
      } // if
      return $this->object;
    } // getObject
    
    /**
    * Return parent user
    *
    * @param void
    * @return User
    */
    function getUser() {
      if(is_null($this->user)) {
        $this->user = Users::findById($this->getUserId());
      } // if
      return $this->user;
    } // getUser
    
    /**
    * User can read object
    *
    * @param void
    * @return boolean
    */
    function hasReadPermission() {
      return $this->getReadPermission() ;
    } // hasReadPermission    
    
    /**
    * User can write object
    *
    * @param void
    * @return boolean
    */
    function hasWritePermission() {
      return $this->getWritePermission() ;
    } // hasWritePermission
    
    /**
    * User cannot access the object
    *
    * @param void
    * @return boolean
    */
    function hasNoAccess() {      
      return  !($this->getReadPermission()) && !($this->getWritePermission());
    } // getUser
    
    /**
     * Returns true if the user_id corresponds to a group
     *
     * @return unknown
     */
    function isGroup(){
    	return $this->getId() >= Group::CONST_MINIMUM_GROUP_ID;
    }
    
    /**
    * Construct the object
    *
    * @param void
    * @return null
    */
    function __construct() {
      parent::__construct();
    } // __construct
    
    
  } // ObjectUserPermissions

?>