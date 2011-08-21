<?php

  /**
  *  ReadObject class
  * Generated on Wed, 26 Jul 2006 11:18:14 +0200 by DataObject generation tool
  *
  * @author Nicolas Medeiros <nicolas@iugo.com.uy>
  */
  class  ReadObject extends BaseReadObject {  	
    
    /**
    * User
    *
    * @var ObjectType
    */
    private $user;    
   
    /**
    * Return object connected with this action
    *
    * @access public
    * @param void
    * @return ProjectDataObject
    */
    function getObject() {
      return get_object_by_manager_and_id($this->getRelObjectId(), $this->getRelObjectManager());
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
    
    
  } //  ReadObject 

?>