<?php

  /**
  * ObjectProperty class
  * Written on Tue, 27 Oct 2007 16:53:08 -0300
  *
  * @author Marcos Saiz <marcos.saiz@fengoffice.com>
  */
  class ObjectProperty extends BaseObjectProperty {
    
    /**
    * object
    *
    * @var  ProjectDataObject
    */
    private $object;    

    /**
    * Return object
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
    
    function setObject($o) {
    	$this->setRelObjectId($o->getId());
    	$this->setRelObjectManager(get_class($o->manager()));
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
    
    
  } // ObjectProperty

?>