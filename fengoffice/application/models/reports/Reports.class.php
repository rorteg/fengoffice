<?php

/**
 *   Reports class
 *
 * @author Pablo Kamil <pablokam@gmail.com>
 */

class Reports extends BaseReports {

	public function __construct() {
		parent::__construct ();
		$this->object_type_name = 'report';
	}
	/**
	 * Return specific report
	 *
	 * @param $id
	 * @return Report
	 */
	static function getReport($id) {
		return self::findById($id);
	} //  getReport

	/**
	 * Return all reports for an object type
	 *
	 * @param $object_type
	 * @return array
	 */
	static function getAllReportsForObjectType($object_type) {
		return self::findAll(array(
			'conditions' => array("`object_type_id` = ?", $object_type)
		));
	} //  getAllReportsForObjectType

	/**
	 * Return all reports
	 *
	 * @return array
	 */
	static function getAllReports() {
		return self::findAll();
	} //  getAllReports

	/**
	 * Return all reports
	 *
	 * @return array
	 */
	static function getAllReportsByObjectType($context = null) {
		if (is_null($context)) {
			$tmp_context = active_context();
			$context = array();
			foreach($tmp_context as $selection) {
				if ($selection instanceof Member) $context[] = $selection->getDimension();
				else if ($selection instanceof Dimension) $context[] = $selection;
			}
		}
		
		$ot = ObjectTypes::findById(self::instance()->getObjectTypeId());
		$reports_result = ContentDataObjects::getContentObjects($context, $ot);
		$reports = $reports_result->objects;
		$result = array();
		foreach ($reports as $report){
			if (array_key_exists($report->getReportObjectTypeId(), $result)) {
				$result[$report->getReportObjectTypeId()][] = $report;
			} else {
				$result[$report->getReportObjectTypeId()] = array($report);
			}
		}
		return $result;
	} //  getAllReports

	/**
	 * Execute a report and return results
	 *
	 * @param $id
	 * @param $params
	 *
	 * @return array
	 */
	static function executeReport($id, $params, $order_by_col = '', $order_by_asc = true, $offset=0, $limit=50, $to_print = false) {
		
		$results = array();
		$report = self::getReport($id);
		if($report instanceof Report){
			$conditionsFields = ReportConditions::getAllReportConditionsForFields($id);
			$conditionsCp = ReportConditions::getAllReportConditionsForCustomProperties($id);
			
			$ot = ObjectTypes::findById($report->getReportObjectTypeId());
			$table = $ot->getTableName();
			
			eval('$managerInstance = ' . $ot->getHandlerClass() . "::instance();");
			eval('$item_class = ' . $ot->getHandlerClass() . '::instance()->getItemClass(); $object = new $item_class();');
			
			$order_by = '';
			if (is_object($params)) {
				$params = get_object_vars($params);				
			}
			
			$report_columns = ReportColumns::getAllReportColumns($id);

			$allConditions = "";
			
			if(count($conditionsFields) > 0){
				foreach($conditionsFields as $condField){
					
					$skip_condition = false;
					$model = $ot->getHandlerClass();
					$model_instance = new $model();
					$col_type = $model_instance->getColumnType($condField->getFieldName());

					$allConditions .= ' AND ';
					$dateFormat = 'm/d/Y';
					if(isset($params[$condField->getId()])){
						$value = $params[$condField->getId()];
						if ($col_type == DATA_TYPE_DATE || $col_type == DATA_TYPE_DATETIME)
						$dateFormat = user_config_option('date_format');
					} else {
						$value = $condField->getValue();
					}
					if ($value == '' && $condField->getIsParametrizable()) $skip_condition = true;
					if (!$skip_condition) {
						if($condField->getCondition() == 'like' || $condField->getCondition() == 'not like'){
							$value = '%'.$value.'%';
						}
						if ($col_type == DATA_TYPE_DATE || $col_type == DATA_TYPE_DATETIME) {
							$dtValue = DateTimeValueLib::dateFromFormatAndString($dateFormat, $value);
							$value = $dtValue->format('Y-m-d');
						}
						if($condField->getCondition() != '%'){
							if ($col_type == DATA_TYPE_INTEGER || $col_type == DATA_TYPE_FLOAT) {
								$allConditions .= '`'.$condField->getFieldName().'` '.$condField->getCondition().' '.DB::escape($value);
							} else {
								if ($condField->getCondition()=='=' || $condField->getCondition()=='<=' || $condField->getCondition()=='>='){
									$equal = 'datediff('.DB::escape($value).', `'.$condField->getFieldName().'`)=0';										
									switch($condField->getCondition()){
										case '=':
											$allConditions .= $equal;
											break;
										case '<=':
										case '>=':
											$allConditions .= '(`'.$condField->getFieldName().'` '.$condField->getCondition().' '.DB::escape($value).' OR '.$equal.') ';
											break;																
									}										
								} else {
									$allConditions .= '`'.$condField->getFieldName().'` '.$condField->getCondition().' '.DB::escape($value);
								}									
							}
						} else {
							$allConditions .= '`'.$condField->getFieldName().'` like '.DB::escape("%$value");
						}
					} else $allConditions .= ' true';
					
				}
			}
			if(count($conditionsCp) > 0){

				foreach($conditionsCp as $condCp){
					$cp = CustomProperties::getCustomProperty($condCp->getCustomPropertyId());

					$skip_condition = false;
					$dateFormat = 'm/d/Y';
					if(isset($params[$condCp->getId()."_".$cp->getName()])){
						$value = $params[$condCp->getId()."_".$cp->getName()];
						if ($cp->getType() == 'date')
						$dateFormat = user_config_option('date_format');
					}else{
						$value = $condCp->getValue();
					}
					if ($value == '' && $condCp->getIsParametrizable()) $skip_condition = true;
					if (!$skip_condition) {
						$allConditions .= ' AND ';
						$allConditions .= 'o.id IN ( SELECT object_id as id FROM '.TABLE_PREFIX.'custom_property_values cpv WHERE ';
						$allConditions .= ' cpv.custom_property_id = '.$condCp->getCustomPropertyId();
						$fieldType = $object->getColumnType($condCp->getFieldName());

						if($condCp->getCondition() == 'like' || $condCp->getCondition() == 'not like'){
							$value = '%'.$value.'%';
						}
						if ($cp->getType() == 'date') {
							$dtValue = DateTimeValueLib::dateFromFormatAndString($dateFormat, $value);
							$value = $dtValue->format('Y-m-d H:i:s');
						}
						if($condCp->getCondition() != '%'){
							if ($cp->getType() == 'numeric') {
								$allConditions .= ' AND cpv.value '.$condCp->getCondition().' '.DB::escape($value);
							}else{
								$allConditions .= ' AND cpv.value '.$condCp->getCondition().' "'.DB::escape($value).'"';
							}
						}else{
							$allConditions .= ' AND cpv.value like '.DB::escape("%$value");
						}
						$allConditions .= ')';
					}
				}
			}
			
			if ($order_by_col == '') $order_by_col = $report->getOrderBy();
			if ($order_by_asc == null) $order_by_asc = $report->getIsOrderByAsc();

			if ($managerInstance) {
				$result = $managerInstance->listing(array(
					"order" => $order_by_col,
					"order_dir" => ($order_by_asc ? "ASC" : "DESC"),
					"extra_conditions" => $allConditions			
				));
			}else{
				// TODO Performance Killer
				$result = ContentDataObjects::getContentObjects(active_context(), $ot, $order_by_col, ($order_by_asc ? "ASC" : "DESC"), $allConditions);
			}
			$objects = $result->objects;
			$totalResults = $result->total;

			$results['pagination'] = Reports::getReportPagination($id, $params, $order_by_col, $order_by_asc, $offset, $limit, $totalResults);
		
			$dimensions_cache = array();
			
			foreach($report_columns as $column){
				if ($column->getCustomPropertyId() == 0) {
					$field = $column->getFieldName();
					if (str_starts_with($field, 'dim_')) {
						$dim_id = str_replace("dim_", "", $field);
						$dimension = Dimensions::getDimensionById($dim_id);
						$dimensions_cache[$dim_id] = $dimension;
						$doptions = $dimension->getOptions(true);
						$column_name = $doptions && isset($doptions->useLangs) && $doptions->useLangs ? lang($dimension->getCode()) : $dimension->getName();
						
						$results['columns'][$field] = $column_name;
						$results['db_columns'][$column_name] = $field;
					} else {
						if ($managerInstance->columnExists($field) || Objects::instance()->columnExists($field)) {
							$column_name = Localization::instance()->lang('field '.$ot->getHandlerClass().' '.$field);
							if (is_null($column_name)) $column_name = lang('field Objects '.$field);
							$results['columns'][$field] = $column_name;
							$results['db_columns'][$column_name] = $field;
						}
					}
				}
			}
			
			$report_rows = array();
			foreach($objects as &$object){/* @var $object Object */
				$obj_name = $object->getObjectName();
				$icon_class = $object->getIconClass();
				
				$row_values = array('object_type_id' => $object->getObjectTypeId());
				
				if (!$to_print) {
					$row_values['link'] = '<a class="link-ico '.$icon_class.'" title="' . $obj_name . '" target="new" href="' . $object->getViewUrl() . '">&nbsp;</a>';
				}
				
				foreach($report_columns as $column){
					if ($column->getCustomPropertyId() == 0) {
						
						$field = $column->getFieldName();
						
						if (str_starts_with($field, 'dim_')) {
							$dim_id = str_replace("dim_", "", $field);
							if (!array_var($dimensions_cache, $dim_id) instanceof Dimension) {
								$dimension = Dimensions::getDimensionById($dim_id);
								$dimensions_cache[$dim_id] = $dimension;
							} else {
								$dimension = array_var($dimensions_cache, $dim_id);
							}
							$members = ObjectMembers::getMembersByObjectAndDimension($object->getId(), $dim_id, " AND om.is_optimization=0");
							
							$value = "";
							foreach ($members as $member) {/* @var $member Member */
								$val = $member->getPath();
								$val .= ($val == "" ? "" : "/") . $member->getName();
								
								if ($value != "") $val = " - $val";
								$value .= $val;
							}
							
							$row_values[$field] = $value;
						} else {
						
							$value = $object->getColumnValue($field);
								
							if ($value instanceof DateTimeValue) {
								$field_type = $managerInstance->columnExists($field) ? $managerInstance->getColumnType($field) : Objects::instance()->getColumnType($field);
								$value = format_value_to_print($field, $value->toMySQL(), $field_type, $report->getReportObjectTypeId());
							}
								
							if(in_array($field, $managerInstance->getExternalColumns())){
								$value = self::instance()->getExternalColumnValue($field, $value, $managerInstance);
							} else if ($field != 'link'){
								$value = html_to_text($value);
							}
							if(self::isReportColumnEmail($value)) {
								if(logged_user()->hasMailAccounts()){
									$value = '<a class="internalLink" href="'.get_url('mail', 'add_mail', array('to' => clean($value))).'">'.clean($value).'</a></div>';
								}else{
									$value = '<a class="internalLink" target="_self" href="mailto:'.clean($value).'">'.clean($value).'</a></div>';
								}
							}	
							$row_values[$field] = $value;
						}
					} else {
						
						$colCp = $column->getCustomPropertyId();
						$cp = CustomProperties::getCustomProperty($colCp);
						if ($cp instanceof CustomProperty) { /* @var $cp CustomProperty */
							
							$cp_val = CustomPropertyValues::getCustomPropertyValue($object->getId(), $colCp);
							$row_values[$cp->getName()] = $cp_val instanceof CustomPropertyValue ? $cp_val->getValue() : "";
							
							$results['columns'][$colCp] = $cp->getName();
							$results['db_columns'][$cp->getName()] = $colCp;
							
						}
					}
				}
				

				Hook::fire("report_row", $object, $row_values);
				$report_rows[] = $row_values;
			}
			
			if (!$to_print) {
				if (is_array($results['columns'])) {
					array_unshift($results['columns'], '');
				} else {
					$results['columns'] = array('');
				}
				Hook::fire("report_header", $ot, $results['columns']);
			}
			$results['rows'] = $report_rows;
		}

		return $results;
	} //  executeReport
	
	function isReportColumnEmail($col){
		return preg_match(EMAIL_FORMAT, $col);
	}
	
	static function removeDuplicateRows($rows){
		$duplicateIds = array();
		foreach($rows as $row){
			if (!isset($duplicateIds[$row['id']])) $duplicateIds[$row['id']] = 0;
			$duplicateIds[$row['id']]++;
		}
		foreach($duplicateIds as $id => $count){
			if($count < 2){
				unset($duplicateIds[$id]);
			}
		}
		$duplicateIds = array_keys($duplicateIds);
		foreach($rows as $row){
			if(in_array($row['id'], $duplicateIds)){
				foreach($row as $col => $value){
					$cp = CustomProperties::getCustomProperty($col);
					if($cp instanceof CustomProperty && $cp->getIsMultipleValues()){

					}
				}
			}
		}
		return $rows;
	}

	static function getReportPagination($report_id, $params, $order_by='', $order_by_asc=true, $offset, $limit, $total){
		if($total == 0) return '';
		$a_nav = array(
			'<span class="x-tbar-page-first" style="padding-left:16px"/>', 
			'<span class="x-tbar-page-prev" style="padding-left:16px"/>', 
			'<span class="x-tbar-page-next" style="padding-left:16px"/>', 
			'<span class="x-tbar-page-last" style="padding-left:16px"/>'
		);
		$page = intval($offset / $limit);
		$totalPages = ceil($total / $limit);
		if($totalPages == 1) return '';

		$parameters = '';
		if(is_array($params) && count($params) > 0){
			foreach($params as $id => $value){
				$parameters .= '&params['.$id.']='.$value;
			}
		}
		if($order_by != ''){
			$parameters .= '&order_by='.$order_by.'&order_by_asc='.($order_by_asc ? 1 : 0);
		}
		
		$nav = '';
		if($page != 0){
			$nav .= '<a class="internalLink" href="'.get_url('reporting', 'view_custom_report', array('id' => $report_id, 'offset' => '0', 'limit' => $limit)).$parameters.'">'.sprintf($a_nav[0], $offset).'</a>';
			$off = $offset - $limit;
			$nav .= '<a class="internalLink" href="'.get_url('reporting', 'view_custom_report', array('id' => $report_id, 'offset' => $off, 'limit' => $limit)).$parameters.'">'.$a_nav[1].'</a>&nbsp;';
		}
		for($i = 1; $i < $totalPages + 1; $i++){
			$off = $limit * ($i - 1);
			if(($i != $page + 1) && abs($i - 1 - $page) <= 2 ) $nav .= '<a class="internalLink" href="'.get_url('reporting', 'view_custom_report', array('id' => $report_id, 'offset' => $off, 'limit' => $limit)).$parameters.'">'.$i.'</a>&nbsp;&nbsp;';
			else if($i == $page + 1) $nav .= '<span class="bold">'.$i.'</span>&nbsp;&nbsp;';
		}
		if($page < $totalPages - 1){
			$off = $offset + $limit;
			$nav .= '<a class="internalLink" href="'.get_url('reporting', 'view_custom_report', array('id' => $report_id, 'offset' => $off, 'limit' => $limit)).$parameters.'">'.$a_nav[2].'</a>';
			$off = $limit * ($totalPages - 1);
			$nav .= '<a class="internalLink" href="'.get_url('reporting', 'view_custom_report', array('id' => $report_id, 'offset' => $off, 'limit' => $limit)).$parameters.'">'.$a_nav[3].'</a>';
		}
		return $nav . "<br/><span class='desc'>&nbsp;".lang('total').": $totalPages ".lang('pages').'</span>';
	}

	function getExternalColumnValue($field, $id, $manager = null){
		$value = '';
		if($field == 'user_id' || $field == 'contact_id' || $field == 'created_by_id' || $field == 'updated_by_id' || $field == 'assigned_to_contact_id' || $field == 'completed_by_id'|| $field == 'approved_by_id'){
			$contact = Contacts::findById($id);
			if($contact instanceof Contact) $value = $contact->getObjectName();
		} else if($field == 'milestone_id'){
			$milestone = ProjectMilestones::findById($id);
			if($milestone instanceof ProjectMilestone) $value = $milestone->getObjectName();
		} else if ($manager instanceof ContentDataObjects) {
			$value = $manager->getExternalColumnValue($field, $id);
		}
		return $value;
	}

} // Reports

?>