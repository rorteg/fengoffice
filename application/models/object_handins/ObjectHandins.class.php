<?php

  /**
  * ObjectHandins class
  * Written on Tue, 27 Oct 2007 16:53:08 -0300
  *
  * @author Marcos Saiz <marcos.saiz@fengoffice.com>
  */
  class  ObjectHandins extends  BaseObjectHandins {

            /**
    * Construct
    *
    * @return BaseObjectHandin 
    */
    function __construct() {
      parent::__construct();
    } // __construct
    /**
    * Reaturn all handins that an object has
    *
    * @param int $id
    * @param string $manager
    * @param boolean $only_pending when true lists only pending handins
    * @return array
    */
    static function getAllHandinsByIdAndManager($id, $manager, $only_pending = false) {
    	if(!$only_pending)
		      return self::findAll(array(
		        'conditions' => array('`rel_object_id` = ? and `rel_object_manager` = ?', $id, $manager)
		      )); // findAll
		else //return pendieng handins only
		      return self::findAll(array(
		        'conditions' => array('`rel_object_id` = ? and `rel_object_manager` = ? and (completed_on IS NULL or completed_on = 0) ', $id, $manager)
		      )); // findAll
    } //  getAllHandinsByIdAndManager
    
        
    /**
    * Reaturn all handins that an object has
    *
    * @param ProjectDataObject $object
    * @return array
    */
    static function getAllHandinsByObject(ProjectDataObject $object) {
		return self::getAllHandinsByIdAndManager($object->getObjectId(),get_class($object->manager()));
    } //  getAllHandinsByObject
        
    /**
    * Reaturn pending handins that an object has
    *
    * @param ProjectDataObject $object
    * @return array
    */
    static function getPendingHandinsByObject(ProjectDataObject $object) {
		return self::getAllHandinsByIdAndManager($object->getObjectId(),get_class($object->manager()),true);
    } //  getPendingHandinsByObject
        
    /**
    * Reaturn all handins that an user is responsible from
    *
    * @param User $user default value is current user
    * @return array
    */
    static function getAllHandinsByUser($user = null) {
      if (!($user))
      	$user = get_current_user();
      return self::findAll(array(
        'conditions' => array('`responsible_user_id` = ?', $user->getId())
      )); // findAll
    } //  getAllHandinsByUser
        
    /**
    * Reaturn all handins that an user is responsible from
    *
    * @param User $user default value is current user
    * @return array
    */
    static function getPendingHandinsByUser($user = null) {
      if (!($user))
      	$user = get_current_user();
      return self::findAll(array(
        'conditions' => array('`responsible_user_id` = ? and (completed_on IS NULL or completed_on = 0) ', $user->getId())
      )); // findAll
    } //  getPendingHandinsByUser
        
    /**
    * Reaturn handin by id
    *
    * @param int $id
    * @return array
    */
    static function getHandin($id) {
      return self::findAll(array(
        'conditions' => array('`id` = ?', $id)
      )); // findAll
    } //  getPendingHandinsByUser
    
  
  } // ObjectHandins

?>