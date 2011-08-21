<?php
	
	function format_value_to_print($col, $value, $type, $obj_type, $textWrapper='', $dateformat='Y-m-d') {		
		switch ($type) {
			case DATA_TYPE_STRING:				
				if(preg_match(EMAIL_FORMAT, strip_tags($value))){
					$formatted = $value;
				}else{ 
					$formatted = $textWrapper . clean($value) . $textWrapper;
				}
				break;
			case DATA_TYPE_INTEGER:				
				if ($col == 'priority'){
					switch($value){
					case 100:
						$formatted = lang('low priority'); 
						break;
					case 200:
						$formatted = lang('normal priority');
						break;
					case 300:
						$formatted = lang('high priority');
						break;
					case 400:
						$formatted = lang('urgent priority');
						break;
					default: $formatted = clean($value);
					}					
				}else{		
					$formatted = clean($value);
				}
				break;
			case DATA_TYPE_BOOLEAN: $formatted = ($value == 1 ? lang('yes') : lang('no'));
				break;
			case DATA_TYPE_DATE:
				if ($value != 0) { 
					if (str_ends_with($value, "00:00:00")) $dateformat .= " H:i:s";
					$dtVal = DateTimeValueLib::dateFromFormatAndString($dateformat, $value);
					$formatted = format_date($dtVal, null, 0);
				} else $formatted = '';
				break;
			case DATA_TYPE_DATETIME:
				if ($value != 0) {
					$dtVal = DateTimeValueLib::dateFromFormatAndString("$dateformat H:i:s", $value);
					if ($obj_type == 'ProjectEvents' && ($col == 'start' || $col == 'duration')) $formatted = format_datetime($dtVal);
					else $formatted = format_date($dtVal, null, 0);
				} else $formatted = '';
				break;
			default:
				if ($col == 'state'){					
					$formatted = ($value > '0') ? lang('completed') : lang('open task status');					
				}else{ 				
					$formatted = $value;
				}
		}
		if($formatted == ''){
			$formatted = '--';
		}
		
		return $formatted;
	}
	
	if ($description != '') echo clean($description) . '<br/>';
	$conditionHtml = '';
	
	if (count($conditions) > 0) {		
		foreach ($conditions as $condition) {
			if($condition->getCustomPropertyId() > 0){
				$cp = CustomProperties::getCustomProperty($condition->getCustomPropertyId());
				$name = clean($cp->getName());
				$paramName = $condition->getId()."_".$cp->getName();
				$coltype = $cp->getOgType();
			}else{
				if ($condition->getFieldName()!= 'workspace' && $condition->getFieldName()!= 'tag'){
					    $name = lang('field ' . $model . ' ' . $condition->getFieldName());
				}else{
				 		$name = lang($condition->getFieldName());
				}	
				//$name = lang('field ' . $model . ' ' . $condition->getFieldName());
				$coltype = array_key_exists($condition->getFieldName(), $types)? $types[$condition->getFieldName()]:'';
				$paramName = $condition->getFieldName();
			}
			$paramValue = isset($parameters[$paramName]) ? $parameters[$paramName] : '';
			$value = $condition->getIsParametrizable()? clean($paramValue) : clean($condition->getValue());
			if ($condition->getFieldName() == 'workspace'){
				$workspace_id = $condition->getIsParametrizable()? clean($parameters['workspace']) : clean($condition->getValue());
				$project = Projects::findById($workspace_id);
				if($project instanceof Project){
					$value = $project->getName();
					$coltype = 'external';
				}else{
					continue;
				}
			} 
			eval('$managerInstance = ' . $model . "::instance();");
			$externalCols = $managerInstance->getExternalColumns();
			if(in_array($condition->getFieldName(), $externalCols)){
				$value = clean(Reports::getExternalColumnValue($condition->getFieldName(), $value));
			}
			
			if ($paramName == 'state' && $value != null){					
					$state_value = ($value > '0') ? lang('completed') : lang('open task status');
					$conditionHtml .= '- ' . lang('field ProjectTasks status') . ' = ' . $state_value . '<br/>';
			}else if ($value != ''){
					$conditionHtml .= '- ' . $name . ' ' . ($condition->getCondition() != '%' ? $condition->getCondition() : lang('ends with') ) . ' ' . format_value_to_print($condition->getFieldName(), $value, $coltype, '', '"', user_config_option('date_format')) . '<br/>';
			}			
		}
	}
	
	?>
	
<div id="pdfOptions" style="display:none;">
	<b><?php echo lang('report pdf options') ?></b><hr/>
	<?php echo lang('report pdf page layout') ?>:
	<select name="pdfPageLayout">
		<option value="P" selected><?php echo lang('report pdf vertical') ?></option>
		<option value="L"><?php echo lang('report pdf landscape') ?></option>
	</select>&nbsp;&nbsp;
	<?php echo lang('report font size') ?>:
	<select name="pdfFontSize">
		<option value="8">8</option>
		<option value="9">9</option>
		<option value="10">10</option>
		<option value="11">11</option>
		<option value="12" selected>12</option>
		<option value="13">13</option>
		<option value="14">14</option>
		<option value="15">15</option>
		<option value="16">16</option>
	</select><br/>
	<input type="submit" name="exportPDF" value="<?php echo lang('export') ?>" onclick="document.getElementById('form<?php echo $genid ?>').target = '_download';" style="width:120px; margin-top:10px;"/>
</div>
<br/>

<?php
	if ($conditionHtml != '') {?>
<br/>
<b><?php echo lang('conditions')?>:</b><br/>
<p style="padding-left:10px">
	<?php echo $conditionHtml; ?>
</p>
<?php } // if ?>
<br/>
<?php if (!isset($id)) $id= ''; ?>
<input type="hidden" name="id" value="<?php echo $id ?>" />
<input type="hidden" name="order_by" value="<?php echo $order_by ?>" />
<input type="hidden" name="order_by_asc" value="<?php echo $order_by_asc ?>" />
<table>
<tbody>
<tr>
<?php foreach($columns as $col) { 
	$sorted = false;
	$asc = false;
	if($col != '' && array_var($db_columns, $col) == $order_by) {
		$sorted = true;
		$asc = $order_by_asc;
	}	?>
	<td style="padding-right:10px;border-bottom:1px solid #666"><b>
	<?php if($to_print || $col === lang('tags') || $col === lang('workspaces')){ 	
			echo clean($col);
		  }else if($col != ''){ ?>
		<a href="<?php echo get_url('reporting', 'view_custom_report', array('id' => $id, 'order_by' => $db_columns[$col], 'order_by_asc' => $asc ? 0 : 1)).$parameterURL; ?>"><?php echo clean($col) ?></a>
	<?php } ?>
	</b>
	<?php if(!($to_print || $col === lang('tags') || $col === lang('workspaces')) && $sorted){ ?>
		<img src="<?php echo icon_url($asc ? 'asc.png' : 'desc.png') ?>" />
	<?php } //if ?>
	</td>
<?php } //foreach?>
</tr>
<?php
	$isAlt = true; 
	foreach($rows as $row) {
		$isAlt = !$isAlt;
		$i = 0; 
?>
	<tr<?php echo ($isAlt ? ' style="background-color:#F4F8F9"' : "") ?>>
		<?php foreach($row as $k => $value) {
				$db_col = isset($db_columns[$columns[$i]]) ? $db_columns[$columns[$i]] : '';
			?>
			<td style="padding-right:10px;"><?php echo format_value_to_print($db_col, $value, ($k == 'link'?'':array_var($types, $k)), $model) ?></td>
		<?php
			$i++; 
			}//foreach ?>
	</tr>
<?php } //foreach ?>
</tbody>
</table>

<br/><?php if (isset($pagination)) echo $pagination ?>
