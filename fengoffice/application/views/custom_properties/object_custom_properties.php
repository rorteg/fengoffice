<?php
require_javascript("og/CustomProperties.js");

$cps = CustomProperties::getAllCustomPropertiesByObjectType($_custom_properties_object->getObjectTypeId(), $co_type);
$ti = 0;

if (!isset($genid)) $genid = gen_id();
if (!isset($startTi)) $startTi = 10000;

if(count($cps) > 0){
	$print_table_functions = false;
	foreach($cps as $customProp){
		if(!isset($required) || ($required && ($customProp->getIsRequired() || $customProp->getVisibleByDefault())) || (!$required && !($customProp->getIsRequired() || $customProp->getVisibleByDefault()))){
			$ti++;
			$cpv = CustomPropertyValues::getCustomPropertyValue($_custom_properties_object->getId(), $customProp->getId());
			$default_value = $customProp->getDefaultValue();
			if($cpv instanceof CustomPropertyValue){
				$default_value = $cpv->getValue();
			}
			$name = 'object_custom_properties['.$customProp->getId().']';
			echo '<div style="margin-top:6px">';

			if ($customProp->getType() == 'boolean')
				echo checkbox_field($name, $default_value, array('tabindex' => $startTi + $ti, 'style' => 'margin-right:4px', 'id' => $genid . 'cp' . $customProp->getId()));

			echo label_tag(clean($customProp->getName()), $genid . 'cp' . $customProp->getId(), $customProp->getIsRequired(), array('style' => 'display:inline'), $customProp->getType() == 'boolean'?'':':');
			if ($customProp->getDescription() != ''){
				echo '<span class="desc"> - ' . clean($customProp->getDescription()) . '</span>';
			}
			echo '</div>';

			switch ($customProp->getType()) {
				case 'text':
				case 'numeric':
				case 'memo':
					if($customProp->getIsMultipleValues()){
						$numeric = ($customProp->getType() == "numeric");
						echo "<table><tr><td>";
						echo '<div id="listValues'.$customProp->getId().'" name="listValues'.$customProp->getId().'">';
						$isMemo = $customProp->getType() == 'memo';
						$count = 0;
						$fieldValues = CustomPropertyValues::getCustomPropertyValues($_custom_properties_object->getId(), $customProp->getId());
						if (!is_array($fieldValues) || count($fieldValues) == 0) {
							$def_cp_value = new CustomPropertyValue();
							$def_cp_value->setValue($default_value);
							$fieldValues = array($def_cp_value);
						}
						foreach($fieldValues as $value){
							$value = str_replace('|', ',', $value->getValue());
							if($value != ''){
								echo '<div id="value'.$count.'">';
								if($isMemo){
									echo textarea_field($name.'[]', $value, array('tabindex' => $startTi + $ti, 'id' => $name.'[]'));
								}else{
									echo text_field($name.'[]', $value, array('tabindex' => $startTi + $ti, 'id' => $name.'[]'));
								}
								echo '&nbsp;<a href="#" class="link-ico ico-delete" onclick="og.removeCPValue('.$customProp->getId().','.($count).','.($isMemo ? 1 : 0).')" ></a>';
								echo '</div>';
								$count++;
							}
						}
						echo '<div id="value'.$count.'">';
						if($customProp->getType() == 'memo'){
							echo textarea_field($name.'[]', '', array('tabindex' => $startTi + $ti, 'id' => $name.'[]'));
						}else{
							echo text_field($name.'[]', '', array('tabindex' => $startTi + $ti, 'id' => $name.'[]'));
						}
						echo '&nbsp;<a href="#" class="link-ico ico-add" onclick="og.addCPValue('.$customProp->getId().',\''.$isMemo.'\')">'.lang('add value').'</a><br/>';
						echo '</div>';
						echo '</div>';
						echo "</td></tr></table>";
						$include_script = true;
					} else {
						if($customProp->getType() == 'memo'){
							echo textarea_field($name, $default_value, array('tabindex' => $startTi + $ti, 'class' => 'short', 'id' => $genid . 'cp' . $customProp->getId()));
						}else{
							echo text_field($name, $default_value, array('tabindex' => $startTi + $ti, 'id' => $genid . 'cp' . $customProp->getId()));
						}
					}
					break;
				case 'boolean':
					break;
				case 'date':
					// dates from table are saved as a string in "Y-m-d H:i:s" format
					if($customProp->getIsMultipleValues()){
						$name .= '[]';
						$count = 0;
						$fieldValues = CustomPropertyValues::getCustomPropertyValues($_custom_properties_object->getId(), $customProp->getId());
						if (!is_array($fieldValues) || count($fieldValues) == 0) {
							$def_cp_value = new CustomPropertyValue();
							$def_cp_value->setValue($default_value);
							$fieldValues = array($def_cp_value);
						}
						echo '<table id="table'.$genid.$customProp->getId().'">';
						foreach($fieldValues as $val){
							$value = DateTimeValueLib::dateFromFormatAndString("Y-m-d H:i:s", $val->getValue());
							echo '<tr><td style="width:150px;">';
							echo pick_date_widget2($name, $value, null, $startTi + $ti, null, $genid . 'cp' . $customProp->getId());
							echo '</td><td>';
							echo '<a href="#" class="link-ico ico-delete" onclick="og.removeCPDateValue(\''.$genid.'\','.$customProp->getId().','.$count.')"></a>';
							echo '</td></tr>';
							$count++;
						}
						echo '</table>';
						echo '&nbsp;<a href="#" class="link-ico ico-add" onclick="og.addCPDateValue(\''.$genid.'\','.$customProp->getId().')">'.lang('add value').'</a><br/>';
					}else{
						$value = DateTimeValueLib::dateFromFormatAndString("Y-m-d H:i:s", $default_value);
						echo pick_date_widget2($name, $value, null, $startTi + $ti, null, $genid . 'cp' . $customProp->getId());
					}
					break;
				case 'list':
					$options = array();
					if(!$customProp->getIsRequired()){
						$options[] = '<option value=""></option>';
					}
					$totalOptions = 0;
					$multValues = CustomPropertyValues::getCustomPropertyValues($_custom_properties_object->getId(), $customProp->getId());
					$toSelect = array();
					foreach ($multValues as $m){
						$toSelect[] = $m->getValue();
					}
					foreach(explode(',', $customProp->getValues()) as $value){
						$selected = ($value == $default_value) || ($customProp->getIsMultipleValues() && (in_array($value, explode(',', $default_value)))||in_array($value,$toSelect));
						if($selected){
							$options[] = '<option value="'. clean($value) .'" selected>'. clean($value) .'</option>';
						}else{
							$options[] = option_tag($value, $value);
						}
						$totalOptions++;
					}
					if($customProp->getIsMultipleValues()){
						$name .= '[]';
						echo select_box($name, $options, array('tabindex' => $startTi + $ti, 'style' => 'min-width:140px',  'size' => $totalOptions, 'multiple' => 'multiple', 'id' => $genid . 'cp' . $customProp->getId()));
					}else{
						echo select_box($name, $options, array('tabindex' => $startTi + $ti, 'style' => 'min-width:140px', 'id' => $genid . 'cp' . $customProp->getId()));
					}
					break;
				case 'table':
					$columnNames = explode(',', $customProp->getValues());
					$cell_width = (600 / count($columnNames)) . "px";
					$html = '<div class="og-add-custom-properties"><table><tr>';
					foreach ($columnNames as $colName) {
						$html .= '<th style="width:'.$cell_width.';min-width:120px;">'.$colName.'</th>';
					}
					$ti += 1000;
					$html .= '</tr><tr>';
					$values = CustomPropertyValues::getCustomPropertyValues($_custom_properties_object->getId(), $customProp->getId());
					if (!is_array($values) || count($values) == 0) {
						$def_cp_value = new CustomPropertyValue();
						$def_cp_value->setValue($default_value);
						$values = array($def_cp_value);
					}
					$rows = 0;
					if (is_array($values) && count($values) > 0) {
						foreach ($values as $val) {
							$col = 0;
							$values = str_replace("\|", "%%_PIPE_%%", $val->getValue());
							$exploded = explode("|", $values);
							foreach ($exploded as $v) {
								$v = str_replace("%%_PIPE_%%", "|", $v);
								$html .= '<td><input class="value" style="width:'.$cell_width.';min-width:120px;" name="'.$name."[$rows][$col]". '" value="'. clean($v) .'" tabindex="'.($startTi + $ti++).'"/></td>';
								$col++;
							}
							$html .= '<td><div class="ico ico-delete" style="width:16px;height:16px;cursor:pointer" onclick="og.removeTableCustomPropertyRow(this.parentNode.parentNode);return false;">&nbsp;</div></td>';
							$html .= '</tr><tr>';
							$rows++;
						}
					}
					$html .= '</tr></table>';
					$html .= '<a href="#" tabindex="'.($startTi + $ti + 50*count($columnNames)).'" onclick="og.addTableCustomPropertyRow(this.parentNode, true, null, '.count($columnNames).', '.($startTi + $ti).', '.$customProp->getId().');return false;">' . lang("add") . '</a></div>';
					$ti += 50*count($columnNames);
					$print_table_functions = true;
					echo $html;
					break;
				default: break;
			}
		}
	}
	if ($print_table_functions) {
		echo '<script>
				og.addTableCustomPropertyRow = function(parent, focus, values, col_count, ti, cpid) {
					var count = parent.getElementsByTagName("tr").length;
					var tbody = parent.getElementsByTagName("tbody")[0];
					var tr = document.createElement("tr");
					ti = ti + col_count * count;
					var cell_w = (600 / col_count) + \'px\';					
					for (row = 0; row < col_count; row++) {
						var td = document.createElement("td");						
						var row_val = values && values[row] ? values[row] : "";
						td.innerHTML = \'<input class="value" style="width:\'+cell_w+\';min-width:120px;" type="text" name="object_custom_properties[\' + cpid + \'][\' + count + \'][\' + row + \']" value="\' + row_val + \'" tabindex=\' + ti + \'>\';
						if (td.children && row == 0) var input = td.children[0];
						tr.appendChild(td);
						ti += 1;
					}
					tbody.appendChild(tr);
					var td = document.createElement("td");
					td.innerHTML = \'<div class="ico ico-delete" style="width:16px;height:16px;cursor:pointer" onclick="og.removeTableCustomPropertyRow(this.parentNode.parentNode);return false;">&nbsp;</div>\';
					tr.appendChild(td);
					tbody.appendChild(tr);
					if (input && focus)
						input.focus();
				}
				og.removeTableCustomPropertyRow = function(tr) {
					var parent = tr.parentNode;
					parent.removeChild(tr);
				}
			</script>';
	}
}

?>