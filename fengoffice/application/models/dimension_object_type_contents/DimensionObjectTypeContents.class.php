<?php

  /**
  * DimensionObjectTypeContents
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  class DimensionObjectTypeContents extends BaseDimensionObjectTypeContents {
    

  	static function getContentObjectTypeIds($dimension_id, $dimension_object_type = null) {
  		$type_ids = array();
  		$cond = "";
  		
  		if ($dimension_object_type != null)
  			$cond = ' AND `dimension_object_type_id` = '.$dimension_object_type;
  		
  		$types = self::findAll(array('conditions' => '`dimension_id` = '.$dimension_id.$cond));
  		foreach ($types as $type) {
  			$type_ids[] = $type->getContentObjectTypeId();
  		}
  		return array_unique($type_ids);
  	}
    
  	
  	static function getDimensionObjectTypesforObject($object_type_id){
  		return self::findAll(array('conditions' => '`content_object_type_id` = '.$object_type_id));
  	}
  	
  	
  	static function getRequiredDimensions($object_type_id){
  		$sql = "SELECT DISTINCT `dimension_id` FROM `".TABLE_PREFIX."dimension_object_type_contents` WHERE 
  			   `content_object_type_id` = $object_type_id AND `is_required` = 1";
  		
  		$result = DB::execute($sql);
    	$rows = $result->fetchAll();
    	$dimension_ids = array();
    	if ($rows){
	    	foreach ($rows as $row){
	    		$dimension_ids[] = (int)$row['dimension_id'];
	    	}
    	}
    	return $dimension_ids;
  	}
  } // DimensionObjectTypeContents 

