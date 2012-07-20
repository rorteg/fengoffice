<?php

class SharingTables extends BaseSharingTables {
	
	/* 
	 * @author Ignacio Vazquez - elpepe.uy@gmail.com
	 * @param array $groupIds
	 * @param int $objectId
	 */
	public function populateGroups ( $groupIds, $objectId) {
		if ( !is_array($groupIds) ) {
			throw new Error(lang("empty group array"), null , null );
		}
		
		// Delete old rows
		self::delete("object_id = $objectId");

		// Insert new rows
		$table = self::getTableName();
		$cols = array("group_id", "object_id") ;
		$rows = array() ;
		foreach ($groupIds as $gid) {
			$rows[] = array( $gid, $objectId);
		}
		massiveInsert($table, $cols, $rows);
		$rows = null;
	}
	
	/**
	 * @author Ignacio Vazquez - elpepe.uy@gmail.com
	 * @param array $objectIds
	 * @param int $groupId
	 */
	public function populateObjects($objectIds, $groupId ) {
		
		if ( !is_array($objectIds) ) {
			throw new Error(lang("empty group array"), null , null );
		}

		// Insert new rows
		$table = SharingTables::getTableName();
		$cols = array("group_id", "object_id") ;
		$rows = array() ;
		foreach ($objectIds as $oid) {
			$rows[] = array($groupId, $oid );
		}
		massiveInsert($table, $cols, $rows, 10000);
		$rows = null;
	}
	


}
