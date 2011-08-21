<?php

/**
 *   CustomProperty class
 *
 * @author Pablo Kamil <pablokam@gmail.com>
 */
class  CustomProperties extends  BaseCustomProperties {

	/**
	 * Return custom properties that are not visilbe by default.
	 * @param $object_type
	 * @return unknown_type
	 */
	static function getHiddenCustomPropertiesByObjectType($object_type) {
		return self::findAll(array(
			'conditions' => array("`object_type` = ? AND `is_required` = ? AND `visible_by_default` = ?", $object_type, false, false),
			'order' => 'property_order asc'
		)); // findAll
	}
	
	/**
	 * Count custom properties that are not visilbe by default.
	 * @param $object_type
	 * @return unknown_type
	 */
	static function countHiddenCustomPropertiesByObjectType($object_type) {
		return self::count(array("`object_type` = ? AND `is_required` = ? AND `visible_by_default` = ?", $object_type, false, false));
	}

	/**
	 * Return all custom properties that an object type has
	 *
	 * @param $object_type
	 * @return array
	 */
	static function getAllCustomPropertiesByObjectType($object_type, $co_type = null) {
		if ($co_type) {
			$cond = "`object_type` = '$object_type' AND `id` IN (".CustomPropertiesByCoType::instance()->getCustomPropertyIdsByCoTypeCSV($co_type).")";
		} else {
			$cond = array("`object_type` = ?", $object_type);
		}
		return self::findAll(array(
			'conditions' => $cond,
			'order' => 'property_order asc'
		)); // findAll
	} //  getAllCustomPropertiesByObjectType
	
	
	/**
	 * Returns an array of the custom property ids for a given object type
	 *
	 * @param $object_type
	 * @return array
	 */
	static function getCustomPropertyIdsByObjectType($object_type) {
		$rows = DB::executeAll("SELECT `id` FROM " . self::instance()->getTableName(true) . " WHERE `object_type` = '" . $object_type ."'");
		$result = array();
		if (is_array($rows) && (count($rows) > 0)){
			foreach($rows as $row)
				$result[] = $row['id'];
		}
		return $result;
	} //  getAllCustomPropertiesByObjectType
	

	/**
	 * Return one custom property, given the object type and the property name
	 *
	 * @param String $custom_property_name
	 * @return array
	 */
	static function getCustomPropertyByName($object_type, $custom_property_name) {
		return self::findOne(array(
        'conditions' => array("`object_type` = ? and `name` = ? ",
			$object_type, $property_name)
		)); // findAll
	} //  getCustomPropertyByName

	/**
	 * Return one custom property given the id
	 *
	 * @param int $prop_id
	 * @return CustomProperty
	 */
	static function getCustomProperty($prop_id) {
		return self::findOne(array(
        'conditions' => array("`id` = ? ", $prop_id)
		)); // findOne
	} //  getCustomProperty

	
	static function deleteAllByObjectType($object_type){
		return self::delete("`object_type` = ?", $object_type);
	}

	static function deleteByObjectTypeAndName($object_type, $name) {
		return self::delete("`object_type` = ?", $object_type."' AND `name` = " . DB::escape($name));
	}

} // CustomProperties

?>