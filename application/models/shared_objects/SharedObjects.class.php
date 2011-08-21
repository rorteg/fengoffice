<?php

  class SharedObjects extends BaseSharedObjects {
  	
  	function getSharedObjectsByUser($userid)
  	{
  		return SharedObjects::findAll(array(
        	'conditions' => '`user_id` = ' . $userid
      	)); 
  	}
  	
  	function getUsersSharing($objectid, $manager)
  	{
  		return SharedObjects::findAll(array(
        	'conditions' => "`object_id` = " . $objectid . " AND `object_manager` = '" . $manager . "'" 
      	)); 
  	}
  }  

?>