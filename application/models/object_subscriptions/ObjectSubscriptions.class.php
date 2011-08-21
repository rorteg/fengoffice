<?php

  /**
  * ObjectSubscriptions, generated on Mon, 29 May 2006 03:51:15 +0200 by 
  * DataObject generation tool
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class ObjectSubscriptions extends BaseObjectSubscriptions {
  
    /**
    * Return array of users that are subscribed to this specific message
    *
    * @param ProjectDataObject $message
    * @return array
    */
    static function getUsersByObject(ProjectDataObject $object) {
      $users = array();
      $subscriptions = ObjectSubscriptions::findAll(array(
        'conditions' => '`object_id` = ' . DB::escape($object->getId()) .
        		' AND `object_manager` = ' . DB::escape(get_class($object->manager()))
      )); // findAll
      if(is_array($subscriptions)) {
        foreach($subscriptions as $subscription) {
          $user = $subscription->getUser();
          if(!$user instanceof User) continue;
          
		  $user_object_workspaces = $object->getWorkspaces($user->getWorkspacesQuery());
		  $can_read = true;
		  foreach ($user_object_workspaces as $ws) {
			$can_read = can_read_type($user, $ws, get_class($object->manager()));
			if ($can_read) break;  
		  }
		  if (!$can_read) continue;  				

          $users[] = $user;
        } // foreach
      } // if
      return $users;
    } // getUsersByMessage
    
    /**
    * Return array of objects that $user is subscribed to
    *
    * @param User $user
    * @return array
    */
    static function getObjectsByUser(User $user) {
      $objects = array();
      $subscriptions = ObjectSubscriptions::findAll(array(
        'conditions' => '`user_id` = ' . DB::escape($user->getId())
      )); // findAll
      if(is_array($subscriptions)) {
        foreach($subscriptions as $subscription) {
          $object = $subscription->getObject();
          if($object instanceof ProjectDataObject) $objects[] = $object;
        } // foreach
      } // if
      return $objects;
    } // getObjectsByUser
    
    /**
    * Clear subscriptions by object
    *
    * @param ProjectDataObject $message
    * @return boolean
    */
    static function clearByObject(ProjectDataObject $object) {
      return ObjectSubscriptions::delete(
      		'`object_id` = ' . DB::escape($object->getId()) .
      		' AND `object_manager` = ' . DB::escape(get_class($object->manager()))
      );
    } // clearByObject
    
    /**
    * Clear subscriptions by user
    *
    * @param User $user
    * @return boolean
    */
    static function clearByUser(User $user) {
      return ObjectSubscriptions::delete('`user_id` = ' . DB::escape($user->getId()));
    } // clearByUser
    
  } // ObjectSubscriptions 

?>