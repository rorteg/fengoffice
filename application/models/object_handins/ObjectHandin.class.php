<?php

  /**
  * ObjectHandin class
  * Written on Tue, 27 Oct 2007 16:53:08 -0300
  *
  * @author Marcos Saiz <marcos.saiz@fengoffice.com>
  */
  class ObjectHandin extends BaseObjectHandin {
        
    
    /**
    * object 
    *
    * @var ProjectDataObject
    */
    private $object;    
    
    /**
    * User
    *
    * @var User
    */
    private $user; 

    /**
    * Return associated object
    *
    * @param void
    * @return ProjectDataObject
    */
    function getObject() {
      if(is_null($this->object)) {
        $this->object = get_object_by_manager_and_id($this->getId(),get_class($this->manager()));
      } // if
      return $this->object;
    } // getObject
    
    /**
    * Return parent project
    *
    * @param void
    * @return Project
    */
    function getProject() {
 	  $object = $this->getObject();
  	  if($object instanceof ProjectDataObject ) 
  	  	return $object->getProject();
      return null;
    } // getProject
    
    /**
    * Return parent user
    *
    * @param void
    * @return User
    */
    function getResponsibleUser() {
      if(is_null($this->user)) {
        $this->user = Users::findById($this->getResponsibleUserId());
      } // if
      return $this->user;
    } // getUser
    
   
  } // ObjectHandins

?>