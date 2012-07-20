<?php

  /**
  * AddressTypes class
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  class AddressTypes extends BaseAddressTypes {
  
    static private  $home_type_id = null;
  	static private  $work_type_id = null;
  	static private  $other_type_id = null;
  	
  	
  	static function getAddressTypeId($type){
  		switch($type){
  			case 'home': if (is_null(self::$home_type_id)){
  							self::$home_type_id = self::getTypeId($type);
  						 }
  						 return self::$home_type_id;
  						 break;
  			case 'work': if (is_null(self::$work_type_id)){
  							self::$work_type_id = self::getTypeId($type);
  						 }
  						 return self::$work_type_id;
  						 break;
  			case 'other': if (is_null(self::$other_type_id)){
  							self::$other_type_id = self::getTypeId($type);
  						 }
  						 return self::$other_type_id;
  						 break;
  			default: return self::getTypeId($type);
  		}
  	}
  	
  	
  	static function getTypeId($type){
  		$address_type = AddressTypes::findOne(array('conditions' => array("`name` = ?",$type)));
  		if (!is_null($address_type)) return $address_type->getId();
  		else return null;
    }
  } // AddressTypes 

?>