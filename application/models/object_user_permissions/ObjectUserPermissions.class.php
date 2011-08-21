<?php

  /**
  * ProjectObject class
  * Written on Tue, 27 Oct 2007 16:53:08 -0300
  *
  * @author Marcos Saiz <marcos.saiz@fengoffice.com>
  */
  class  ObjectUserPermissions extends  BaseObjectUserPermissions {

        
    /**
    * Reaturn all permissions that a user has
    *
    * @param Object $object
    * @return array
    */
    static function getAllPermissionsByUser(User $user) {
      return self::findAll(array(
        'conditions' => array('`user_id` = ?', $user->getId())
      )); // findAll
    } //  getAllPermissionsByUser
        
    /**
    * Reaturn all permissions of a object
    *
    * @param Object $object
    * @return array
    */
    static function getAllPermissionsByObject(ApplicationDataObject $object, $user_id_csvs = null ) {
      return self::getAllPermissionsByObjectIdAndManager($object->getId(), get_class($object->manager()),$user_id_csvs);      
    } //  getAllPermissionsByObject
        
    /**
    * Reaturn all permissions of a object
    *
    * @param int $object
    * @return array
    */
    static function getAllPermissionsByObjectIdAndManager($object_id, $manager,$user_id_csvs='') {
    	if ($user_id_csvs != '')
    		$user_id_csvs = ' AND user_id in (' . $user_id_csvs . ') ';
      return self::findAll(array(
        'conditions' => array('`rel_object_id` = ? AND `rel_object_manager` = ? ' . $user_id_csvs, $object_id, $manager),
        'order' => '`user_id`',
      )); // findAll
    } //  getAllPermissionsByObject
    
        
    /**
    * User can read
    *
    * @param int $object_id
    * @param int $user_id
    * @return bool
    */
    static function userCanRead( $user_id, $object ) {
	  $perm = self::findOne(array(
        'conditions' => array('`user_id` = ? and `rel_object_id` = ? AND `rel_object_manager` = ?', $user_id, $object->getId(), get_class($object->manager()))
      )); // findAll
      return $perm!=null && $perm->hasReadPermission();
    } //  userCanRead    
        
    /**
    * User can write
    *
    * @param int $object_id
    * @param int $user_id
    * @return bool
    */
    static function userCanWrite( $user_id, $object ) {
	  $perm = self::findOne(array(
        'conditions' => array('`user_id` = ? and `rel_object_id` = ? AND `rel_object_manager` = ?', $user_id, $object->getId(), get_class($object->manager()))
      )); // findAll      
      return $perm!=null && $perm->hasWritePermission();
    } //  userCanWrite   
        
    /**
    * User can write
    *
    * @param int $object_id
    * @param int $user_id
    * @return bool
    */
    static function userCannotAccess( $user_id, $object ) {
	  $perm = self::findOne(array(
        'conditions' => array('`user_id` = ? and `rel_object_id` = ? AND `rel_object_manager` = ?', $user_id, $object->getId(), get_class($object->manager()))
      )); // findAll      
      return $perm!=null && $perm->hasNoAccess();
    } //  userCanWrite
    
  
  } // ObjectUserPermissions

?>