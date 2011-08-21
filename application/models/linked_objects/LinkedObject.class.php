<?php

  /**
  *  LinkedObject class
  * Generated on Wed, 26 Jul 2006 11:18:14 +0200 by DataObject generation tool
  *
  * @author Marcos Saiz <marcos.saiz@gmail.com>
  */
  class  LinkedObject extends BaseLinkedObject {
  
   
    /**
    * Return object connected with this action
    *
    * @access public
    * @param void
    * @return ProjectDataObject
    */
    function getObject1() {
      return get_object_by_manager_and_id($this->getRelObjectId(), $this->getRelObjectManager());
    } // getObject
    
    function getObject2() {
      return get_object_by_manager_and_id($this->getObjectId(), $this->getObjectManager());
    } // getObject
    
    /**
    * Return object connected with this action, that is not equal to the one received
    *
    * @access public
    * @param  ProjectDataObject $object
    * @return ProjectDataObject
    */
    function getOtherObject($object) {
      if ((get_class($object->manager())!= $this->getObjectManager()) || ($object->getObjectId()!= $this->getObjectId()) )
      {    				
      		return get_object_by_manager_and_id($this->getObjectId(), $this->getObjectManager());
      } else {
      		return get_object_by_manager_and_id($this->getRelObjectId(), $this->getRelObjectManager());
      }
    } // getObject
    
  } //  LinkedObjects 

?>