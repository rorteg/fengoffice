<?php

  /**
  * ObjectTypes
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  class ObjectTypes extends BaseObjectTypes {
  	
  	private static $object_types_by_name = array();
  	
  	/**
  	 * Request-Level cache  
  	 * @var array
  	 */
  	static $listableObjectTypesIds = null ;
  	
  	/**
  	 * Used only for reports
  	 * @param unknown_type $external_conditions
  	 */
	static function getAvailableObjectTypes($external_conditions = "") {
		$object_types = self::findAll(array(
			"conditions" => "`type` = 'content_object' AND 
			`name` <> 'file revision' AND 
			`id` NOT IN (SELECT `object_type_id` FROM ".TabPanels::instance()->getTableName(true)." WHERE `enabled` = 0) $external_conditions"
		));
		return $object_types;
	}
	
	static function isListableObjectType($otid) {
		$listableTypes = self::getListableObjectTypeIds();
		return (!empty($listableTypes[$otid]));
	}
	
	static function getListableObjectTypeIds() {
 		if (is_null(self::$listableObjectTypesIds)) {
			$ids = array(); 
			$sql = "
				SELECT DISTINCT(id) as id  
				FROM ".TABLE_PREFIX."object_types 
				WHERE type IN ('content_object', 'dimension_object') AND (
					plugin_id IS NULL OR 
					plugin_id = 0 OR 
					plugin_id IN ( 
						SELECT id FROM ".TABLE_PREFIX."plugins WHERE is_activated > 0 AND is_installed > 0 
					)
				)";
				
			$rows = DB::executeAll($sql);
			foreach ($rows as $row) {
				$ids[array_var($row, 'id')] = array_var($row, 'id');
			}
			self::$listableObjectTypesIds = $ids ;
 		}
		return self::$listableObjectTypesIds;
	}
	
	static function findByName($name) {
		$ot = array_var(self::$object_types_by_name, $name);
		if (!$ot instanceof ObjectType) {
			$ot = self::findOne(array('conditions' => array("`name` = ?", $name)));
			if ($ot instanceof ObjectType) self::$object_types_by_name[$name] = $ot;
		}
		return $ot;
	}
    
  } // ObjectTypes 

?>