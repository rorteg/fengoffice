<?php

  /**
  *  LinkedObjects, generated on Wed, 26 Jul 2006 11:18:14 +0200 by 
  * DataObject generation tool
  *
  * @author Marcos Saiz <marcos.saiz@gmail.com>
  */
  class  LinkedObjects extends BaseLinkedObjects {
  
    /**
    * Return all relation objects ( LinkedObjects) for specific object
    *
    * @param ProjectDataObject $object
    * @return array
    */
    static function getRelationsByObject(ApplicationDataObject $object) {
      return self::findAll(array(
        'conditions' => array('(`rel_object_manager` = ? and `rel_object_id` = ?) or (`object_manager` = ? and `object_id` = ?)', 
        		get_class($object->manager()), $object->getObjectId(), get_class($object->manager()), $object->getObjectId()),
        'order' => '`created_on`'
      )); // findAll
    } // getRelationsByObject
    
    
    /**
    * Return linked objects by object
    *
    * @param ProjectDataObject $object
    * @param boolean $exclude_private Exclude private objects
    * @return array
    */
    static function getLinkedObjectsByObject(ApplicationDataObject $object, $exclude_private = false) {
      return self::getObjectsByRelations(self::getRelationsByObject($object), $object, $exclude_private);
    } // getLinkedObjectsByObject
    
    
    /**
	 *  Returns all linked objects to an object, to be listed
	 * 
     */
	public static function getLinkedObjectsWithPaging($page, $order=null, $orderdir=null, $objid, $mangr, $objects_per_page){		
		$query = "SELECT `object_manager` AS `object_manager_value`, `object_id` AS `oid`, `created_on` AS `order_value`
				  FROM `".TABLE_PREFIX."linked_objects` 
				  WHERE (`rel_object_manager` LIKE '".mysql_real_escape_string($mangr)."' AND `rel_object_id` = ".mysql_real_escape_string($objid).")
				  UNION
				  SELECT `rel_object_manager` AS `object_manager_value`, `rel_object_id` AS `oid`, `created_on` AS `order_value`
				  FROM `".TABLE_PREFIX."linked_objects` 
				  WHERE (`object_manager` LIKE '".mysql_real_escape_string($mangr)."' AND `object_id` = ".mysql_real_escape_string($objid).")";		
					
		if (!$order_dir){
			switch ($order){
				case 'name': $order_dir = 'ASC'; break;
				default: $order_dir = 'DESC';
			}
		}		
		if($order){
			$query .= " ORDER BY `order_value` ";
			if($order_dir) $query .= " " . mysql_real_escape_string($order_dir) . " ";			
		}
		else{
			$query .= " ORDER BY `order_value` DESC ";
		}
		if($page && $objects_per_page){
			$start=($page-1) * $objects_per_page ;
			$query .=  " LIMIT " . $start . "," . $objects_per_page. " ";
		}
		elseif($objects_per_page){
			$query .= " LIMIT " . $objects_per_page;
		}		
		$res = DB::execute($query);
		$objects = array();
		if(!$res)  return $objects;
		$rows=$res->fetchAll();
		if(!$rows)  return $objects;
		$index=0;
		foreach ($rows as $row){
			$manager= $row['object_manager_value'];
			$id = $row['oid'];
			if($id && $manager){
				$obj=get_object_by_manager_and_id($id,$manager);
				if($obj->canView(logged_user())){
					$objects[] = $obj;			
				}
			}//if($id && $manager)
		}//foreach
		ProjectDataObjects::populateData($objects);
		$linked_objects = array();
		
		foreach ($objects as $obj){			
			$linked_object= $obj->getDashboardObject();
			$linked_object['ix'] = $index++;
			$linked_objects[] = $linked_object;	
		}		
		return $linked_objects;		
	}	
    
    /**
    * Return objects by array of object - object relations
    *
    * @param array $relations
    * @param boolean $exclude_private Exclude private objects
    * @return array
    */
    static function getObjectsByRelations($relations, $originalObject, $exclude_private = false) {
      if(!is_array($relations)) return null;
      
      $objects = array();
      foreach($relations as $relation) {
	  $object = $relation->getOtherObject($originalObject);
		if (!$object || !can_access(logged_user(), $object, ACCESS_LEVEL_READ)) continue;
        if($object instanceof ProjectDataObject) {
          if(!($exclude_private && $object->isPrivate())) $objects[] = $object;
        } else {
        	$objects[] = $object;
        }
      } // if
      return count($objects) ? $objects : null;
    } //getObjectsByRelations
    
    /**
    * Remove all relations by object
    *
    * @param ProjectDataObject $object
    * @return boolean
    */
    static function clearRelationsByObject(ApplicationDataObject $object) {
      return self::delete(array('(`object_id` = ? and `object_manager` = ?) or (`rel_object_id` = ? and `rel_object_manager` = ?)', 
      $object->getId(), get_class($object->manager()), $object->getId(),  get_class($object->manager())));
    } // clearRelationsByObject
    
  } // clearRelationsByObject

?>