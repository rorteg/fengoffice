<?php

  /**
  * Members
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  class Members extends BaseMembers {
    
	static function getSubmembers(Member $member, $recursive = true) {
		$members = Members::findAll(array('conditions' => '`parent_member_id` = ' . $member->getId()));
		if ($recursive) {
	  		foreach ($members as $m) {
	  			$members = array_merge($members, self::getSubmembers($m, $recursive));
	  		}
		}
		return $members;
	}
	
	static function getByDimensionObjType($dimension_id, $object_type_id) {
		return Members::findAll(array("conditions" => array("`dimension_id` = ? AND `object_type_id` = ?", $dimension_id, $object_type_id)));
	}
	
	/**
	 * @author Ignacio Vazquez - elpepe.uy@gmail.com
	 * Find all members that have $id at 'object_id_column'
	 * Also accepts as optional parameter dimension_id
	 * @return Member
	 */
	static function findByObjectId($id, $dimension_id = null ) {
		$conditions = 	"`object_id` = $id ";
		if (!is_null($dimension_id)) {
			$conditions .= " AND dimension_id = $dimension_id "; 
		}		
		return self::findAll(array("conditions" => array($conditions) ));
	}	
	
	/**
	 * @author Ignacio Vazquez - elpepe.uy@gmail.com
	 * Find one members that have $id at 'object_id_column'
	 * Also accepts as optional parameter dimension_id
	 * @return Member
	 */
	static function findOneByObjectId($id, $dimension_id = null ) {
		$allMembers= self::findByObjectId($id, $dimension_id);
		if(count($allMembers)) {
			return $allMembers[0];	
		}
		return null;
	}

  } 
