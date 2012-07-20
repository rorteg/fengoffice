<?php

  /**
  * ContactPermissionGroups
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  class ContactPermissionGroups extends BaseContactPermissionGroups {
    
  	private static $cache = array();
  	
  	static function getPermissionGroupIdsByContactCSV($contact_id, $ignore_context = true) {
 	
  		if (isset(self::$cache[$contact_id])) return self::$cache[$contact_id];
  		 
 		$pg_ids = array();
 		
 		$context_cond = $ignore_context ? "" : " AND p.is_context=0";
  		$res = DB::execute("SELECT cp.permission_group_id as pid FROM ".TABLE_PREFIX."contact_permission_groups cp INNER JOIN ".TABLE_PREFIX."permission_groups p ON p.id=cp.permission_group_id WHERE cp.contact_id=$contact_id $context_cond");
 		$rows = $res->fetchAll();
 		if (is_array($rows)) {
 			foreach ($rows as $pg) $pg_ids[] = $pg['pid'];
 		}
 		
 		$csv_pg_ids = '';
 		if ($pg_ids != null){
 			$csv_pg_ids = implode(',',$pg_ids);
 		}
 		
 		self::$cache[$contact_id] = $csv_pg_ids;
 		
 		return $csv_pg_ids;
 		
  	}
  	
  	
    static function getContextPermissionGroupIdsByContactCSV($contact_id) {
 		
    	$pg_ids = array();
 		$res = DB::execute("SELECT cp.permission_group_id as pid FROM ".TABLE_PREFIX."contact_permission_groups cp INNER JOIN ".TABLE_PREFIX."permission_groups p ON p.id=cp.permission_group_id WHERE cp.contact_id=$contact_id AND p.is_context=1");
 		$rows = $res->fetchAll();
 		if (is_array($rows)) {
 			foreach ($rows as $pg) $pg_ids[] = $pg['pid'];
 		}
 		
 		$csv_pg_ids = $pg_ids != null ? implode(',',$pg_ids) : 0;
 		
 		return $csv_pg_ids;
  	}
    
  } // ContactPermissionGroups 

?>