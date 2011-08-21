<?php

  /**
  *  GroupUsers class
  *
  * @author Marcos Saiz <marcos.saiz@gmail.com>
  */
  class  GroupUsers extends BaseGroupUsers {
  	/**
  	 * Returns all User objects that belong to a group
  	 *
  	 * @param integer $group_id
  	 * @return User list
  	 */
  	static function getUsersByGroup($group_id){
  		 $all = self::findAll(array('conditions' => array('`group_id` = ?', $group_id) ));
  		 $cond= '0';  		 
  		 if(!$all)
  		 	return array(); //empty result, avoid query
  		 foreach ($all as $usr)  		 	
  		 	$cond .= ',' . $usr->getUserId(); 
  		 $cond = '(' . $cond . ') ';
  		 return  Users::findAll(array('conditions' => array('`id` in ' . $cond) ));
  	}
  	/**
	 * Returns all groups a user belongs to
  	 *
  	 * @param $user_id
  	 * @return unknown
  	 */
  	static function getGroupsByUser($user_id) {
  		return Groups::findAll(array(
  			'conditions' => array(
  				'`id` IN (SELECT `group_id` FROM `' . TABLE_PREFIX . 'group_users` WHERE `user_id` = ?)',
  				$user_id
  			)
  		));
  	}
  	
  	/**
  	 * Returns all group ids (separated by commas) which a user belongs to
  	 *
  	 * @param $user_id
  	 * @return unknown
  	 */
  	static function getGroupsCSVsByUser($user_id){
  		$groups = self::getGroupsByUser($user_id);
  		$csv = "";
  		foreach ($groups as $group) {
  			if ($csv != "") $csv .= ",";
  			$csv .= $group->getId();
  		}
  		return $csv;
  	}
  	
  	/**
	 * Returns true is a user belongs to a group
  	 *
  	 * @param $user_id
  	 * @return unknown
  	 */
  	static function isUserInGroup($user_id, $group_id){
		try{
 			return (count(self::find(array('conditions' => array("`user_id`  =  $user_id  AND `group_id` =  $group_id") ))) > 0);
		}
		catch (Exception $e){
			return false;
		}
  	}// isUserInGroup
  	
  	/**
  	 * Removes all users of a given grouop
  	 *
  	 * @param unknown_type $group_id
  	 */
  	static function clearByGroup($group) {
  		return self::delete(array('`group_id` = ?', $group->getId()));
  	}
  	
  	static function clearByUser(User $user) {
		return self::delete(array('`user_id` = ?', $user->getId()));
	} // clearByUser

//    /**
//    * Return all relation objects ( GroupUsers) for specific object
//    *
//    * @param ProjectDataObject $object
//    * @return array
//    */
//    static function getRelationsByObject(ProjectDataObject $object) {
//      return self::findAll(array(
//        'conditions' => array('(`rel_object_manager` = ? and `rel_object_id` = ?) or (`object_manager` = ? and `object_id` = ?)', 
//        		get_class($object->manager()), $object->getObjectId(), get_class($object->manager()), $object->getObjectId()),
//        'order' => '`created_on`'
//      )); // findAll
//    } // getRelationsByObject
//    
//    
//    /**
//    * Return linked objects by object
//    *
//    * @param ProjectDataObject $object
//    * @param boolean $exclude_private Exclude private objects
//    * @return array
//    */
//    static function getGroupUsersByObject(ProjectDataObject $object, $exclude_private = false) {
//      return self::getObjectsByRelations(self::getRelationsByObject($object), $object, $exclude_private);
//    } // getGroupUsersByObject
//    
//    /**
//    * Return objects by array of object - object relations
//    *
//    * @param array $relations
//    * @param boolean $exclude_private Exclude private objects
//    * @return array
//    */
//    static function getObjectsByRelations($relations, $originalObject, $exclude_private = false) {
//      if(!is_array($relations)) return null;
//      
//      $objects = array();
//      foreach($relations as $relation) {
//        $object = $relation->getOtherObject($originalObject);
//        if($object instanceof ProjectDataObject) {
//          if(!($exclude_private && $object->isPrivate())) $objects[] = $object;
//        } // if
//      } // if
//      return count($objects) ? $objects : null;
//    } //getObjectsByRelations
//    
//    /**
//    * Remove all relations by object
//    *
//    * @param ProjectDataObject $object
//    * @return boolean
//    */
//    static function clearRelationsByObject(ProjectDataObject $object) {
//      return self::delete(array('(`object_id` = ? and `object_manager` = ?) or (`object_id` = ? and `object_manager` = ?)', 
//      $object->getId(), get_class($object->manager()), $object->getId(),  get_class($object->manager())));
//    } // clearRelationsByObject
    
  } // clearRelationsByObject

?>