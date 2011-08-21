<?php

/**
 *   CustomPropertyValues class
 *
 * @author Pablo Kamil <pablokam@gmail.com>
 */
class CustomPropertyValues extends BaseCustomPropertyValues {


	/**
	 * Return custom property value for the object
	 *
	 * @param $object_id
	 * @param $custom_property_id
	 * @return array
	 */
	static function getCustomPropertyValue($object_id, $custom_property_id) {
		return self::findOne(array(
			'conditions' => array("`object_id` = ? AND `custom_property_id` = ?", $object_id, $custom_property_id)
		)); // findOne
	} //  getCustomPropertyValue
	
	/**
	 * Return custom property values for the object
	 *
	 * @param $object_id
	 * @param $custom_property_id
	 * @return array
	 */
	static function getCustomPropertyValues($object_id, $custom_property_id) {
		return self::findAll(array(
			'conditions' => array("`object_id` = ? AND `custom_property_id` = ?", $object_id, $custom_property_id)
		)); // findAll
	} //  getCustomPropertyValue
	
	/**
	 * Delete custom property values for the object
	 *
	 * @param $object_id
	 * @param $custom_property_id
	 * 
	 */
	static function deleteCustomPropertyValues($object_id, $custom_property_id) {
		return self::delete(array("`object_id` = ? AND `custom_property_id` = ?", $object_id, $custom_property_id)); 
	} //  deleteCustomPropertyValues
	
	/**
	 * Return custom property value count for the object
	 *
	 * @param $object_id
	 * @return array
	 */
	static function getCustomPropertyValueCount($object_id, $object_type) {
		return count(self::findAll(array(
			'conditions' => array("`object_id` = ? AND `custom_property_id` in (SELECT `id` FROM " . CustomProperties::instance()->getTableName(true) . " where `object_type` = ?)"  , $object_id, $object_type)
		))); // findAll
	} //  getCustomPropertyValue
	

} // CustomProperties

?>