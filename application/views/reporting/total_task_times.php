<?php //Functions
	$isAlt = false;
	
	$showBillingCol = false;
	
	function format_value_to_print_task($value, $type, $textWrapper='', $dateformat='Y-m-d') {			
		switch ($type) {
			case DATA_TYPE_STRING:
				if(preg_match(EMAIL_FORMAT, strip_tags($value))){
					$formatted = $value;
				}else{ 
					$formatted = $textWrapper . clean($value) . $textWrapper;
				}
				break;
			case DATA_TYPE_INTEGER: $formatted = clean($value);
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
					$formatted = format_date($dtVal, null, 0);
				} else $formatted = '';
				break;
			default: $formatted = $value;
		}
		if($formatted == ''){
			$formatted = '--';
		}
		
		return $formatted;
	}
		
	function has_value($array, $value){
		foreach ($array as $val)
			if ($val == $value)
				return true;
		return false;
	}

	function has_difference($previousTSRow, $tsRow, $field){
		
		if (is_array($previousTSRow))
			$previousTS = $previousTSRow["ts"];
		$ts = $tsRow["ts"];
		
		return !isset($previousTS) || $previousTS == null ||
				($field == 'id' && $previousTS->getObject()->getId() != $ts->getObject()->getId()) ||
				($field == 'user_id' && $previousTS->getUserId() != $ts->getUserId()) ||
				($field == 'state' && $previousTS->getObject()->getState() != $ts->getObject()->getState()) ||
				($field == 'project_id_0' && $previousTSRow["wsId0"] != $tsRow["wsId0"]) ||
				($field == 'project_id_1' && $previousTSRow["wsId1"] != $tsRow["wsId1"]) ||
				($field == 'project_id_2' && $previousTSRow["wsId2"] != $tsRow["wsId2"]) ||
				($field == 'priority' && $previousTS->getObject()->getPriority() != $ts->getObject()->getPriority()) ||
				($field == 'milestone_id' && $previousTS->getObject()->getMilestoneId() != $ts->getObject()->getMilestoneId());
	}

	function getGroupTitle($field, $tsRow){
		$ts = $tsRow["ts"];
		switch($field){
			case 'id': 
				if ($ts->getObjectManager() == 'Projects')
					return $ts->getObject()->getName();
				else
					return $ts->getObject()->getTitle();
			case 'user_id': return Users::getUserDisplayName($ts->getUserId());
			case 'project_id_0': return $tsRow["wsId0"] != 0 ? Projects::findById($tsRow["wsId0"])->getName() : '';
			case 'project_id_1': return $tsRow["wsId1"] != 0 ? Projects::findById($tsRow["wsId1"])->getName() : '';
			case 'project_id_2': return $tsRow["wsId2"] != 0 ? Projects::findById($tsRow["wsId2"])->getName() : '';
			case 'priority' : 
				if ($ts->getObjectManager() == 'ProjectTasks')
				switch ($ts->getObject()->getPriority()){
					case 100: return lang('low priority');
					case 200: return lang('normal priority');
					case 300: return lang('high priority');
					case 400: return lang('urgent priority');
					default: return $ts->getObject()->getPriority();
				}
				else
					return lang('not applicable');
			case 'milestone_id': 
				if ($ts->getObjectManager() == 'ProjectTasks')
					return $ts->getObject()->getMilestoneId() != 0? $ts->getObject()->getMilestone()->getTitle() : '';
				else
					return '';
		}
		return '';
	}
	
	function get_cols($columns){ //get the columns selected by the user to be shown
		if (!is_array($columns)) $columns = array();
		$cols = array();		
		foreach($columns as $k=>$i){					
			if ($i != 0){
				$cols[] = $k;
			}		 					
		}		
		return $cols;		
	}
	
	function count_extra_cols($columns){ //counts the columns selected by the user to be shown
		$cols = get_cols($columns);
		if ($cols == null)
			return 0;
		else
			return count($cols);
	}
	
	$sectionDepth = 0;
	$totCols = 6 + count_extra_cols($columns);
	$date_format = user_config_option('date_format');
	
?>
<?php if ($start_time) { ?><span style="font-weight:bold"><?php echo lang('from date')?></span>:&nbsp;<?php echo format_datetime($start_time, $date_format) ?><?php } // if ?>
<?php if ($end_time) { ?><span style="font-weight:bold; padding-left:10px"><?php echo lang('to date')?></span>:&nbsp;<?php echo format_datetime(($date_type == '6' ? $end_time->add('d',-1) : $end_time), $date_format) ?><?php } // if ?>

<?php if(!isset($task_title)) 
	$task_title = null;
if ($task_title) { ?><div style="font-size:120%"><span style="font-weight:bold"><?php echo lang('title')?></span>:&nbsp;<?php echo clean($task_title) ?></div> <?php } ?>

<br/><br/>
<?php if ($user instanceof User) { ?>
	<span style="font-weight:bold"><?php echo lang('reporting user')?></span>:&nbsp;<?php echo clean($user->getDisplayName()); ?>
	<br/><br/>
<?php }	?>
<?php if ($workspace instanceof Project) { ?>
	<span style="font-weight:bold"><?php echo lang('reporting workspace')?></span>:&nbsp;<?php echo clean($workspace->getName()); ?>
	<br/><br/>
<?php }	?>

<table style="min-width:564px">
<?php 
	$sumTime = 0;
	$sumBilling = 0;
	if (!is_array($timeslotsArray) || count($timeslotsArray) == 0){?>
<tr><td colspan = 4><div style="font-size:120%; padding:10px;"><?php echo lang('no data to display') ?></div></td></tr>
<?php } else { 
	
	//Initialize
	$headerPrinted = false;
	$gbvals = array('','','');
	$sumTimes = array(0,0,0);
	$sumBillings = array(0,0,0);
	$hasGroupBy = is_array($group_by) && count($group_by) > 0;
	$sectionDepth = $hasGroupBy ? count($group_by) : 0;
	$c = 0;
	for ($i = 0; $i < $sectionDepth; $i++)
		if ($group_by[$i] == 'project_id'){
			$group_by[$i] = 'project_id_' . $c;
			$c++;
		}
	$showSelCol = false; //show selected columns
	$showUserCol = !has_value($group_by, 'user_id');
	$showTitleCol = !has_value($group_by, 'id');
	$showBillingCol = array_var($post, 'show_billing', false);
	if (!$showUserCol) $totCols--;
	if (!$showTitleCol) $totCols--;
	if (!$showBillingCol) $totCols--;
	if (count_extra_cols($columns)>0) $showSelCol = true;
	
	$previousTSRow = null;
	foreach ($timeslotsArray as $tsRow)	{
		$ts = $tsRow["ts"];
		$showHeaderRow = false;
		//to skip showing workspaces in case there are conditions
		if (isset($has_conditions) && $has_conditions && $ts->getObjectManager() == 'Projects') continue;
		//Footers
		for ($i = $sectionDepth - 1; $i >= 0; $i--){
			$has_difference = false;
			for ($j = 0; $j <= $i; $j++)
				$has_difference = $has_difference || has_difference($previousTSRow,$tsRow, $group_by[$j]);
				
			if ($has_difference){
				if ($previousTSRow != null) {
			?>		
<tr style="padding-top:2px;font-weight:bold;">
	<td style="padding:4px;border-top:2px solid #888;font-size:90%;color:#AAA;text-align:left;font-weight:normal"><?php echo truncate(clean(getGroupTitle($group_by[$i], $previousTSRow)),40,'&hellip;') ?></td>
	<td colspan=<?php echo ($showBillingCol)? $totCols -2 : $totCols -1 ?> style="padding:4px;border-top:2px solid #888;text-align:right;"><?php echo lang('total') ?>:&nbsp;<?php echo DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($sumTimes[$i] * 60), "hm", 60) ?></td>
	<?php if ($showBillingCol) { ?><td style="width:30px;padding:4px;border-top:2px solid #888;text-align:right;"><?php echo config_option('currency_code', '$') ?>&nbsp;<?php echo $sumBillings[$i] ?></td><?php } ?>
</tr></table></div></td></tr><?php 	}
				$sumTimes[$i] = 0;
				$sumBillings[$i] = 0;
				$isAlt = true;
			}
		}
		
		//Headers
		$has_difference = false;
		for ($i = 0; $i < $sectionDepth; $i++){
			$colspan = 3 - $i;
			$has_difference = $has_difference || has_difference($previousTSRow,$tsRow, $group_by[$i]);
			$showHeaderRow = $has_difference || $showHeaderRow;
			
			if ($has_difference){?>
			<tr><td colspan=<?php echo $totCols ?>><div style="width=100%;<?php echo $i > 0 ? 'padding-left:20px;padding-right:10px;' : '' ?>padding-top:10px;padding-bottom:5px;"><table style="width:100%">
<tr><td colspan=<?php echo $totCols ?> style="border-bottom:2px solid #888;font-size:<?php echo (150 - (15 * $i)) ?>%;font-weight:bold;">
	<?php echo clean(getGroupTitle($group_by[$i], $tsRow)) ?></td></tr>

<?php 		}
			$sumTimes[$i] += $ts->getMinutes();
			$sumBillings[$i] += $ts->getFixedBilling();
		}
		
		$isAlt = !$isAlt;
		$previousTSRow = $tsRow;
		
		if ($showHeaderRow || (!$hasGroupBy && !$headerPrinted)) {
			$headerPrinted = true;
		?><tr><th style="padding:4px;border-bottom:1px solid #666666;width:70px"><?php echo lang('date') ?></th>
	<?php if ($showTitleCol) { ?><th style="padding:4px;border-bottom:1px solid #666666"><?php echo lang('title') ?></th><?php } ?>
	<th style="padding:4px;border-bottom:1px solid #666666"><?php echo lang('description') ?></th>
	<?php if ($showUserCol) { ?><th style="padding:4px;border-bottom:1px solid #666666"><?php echo lang('user') ?></th><?php } ?>
	<th style="padding:4px;text-align:right;border-bottom:1px solid #666666"><?php echo lang('time') ?></th>
	<?php if ($showBillingCol) { ?><th style="padding:4px;text-align:right;border-bottom:1px solid #666666"><?php echo lang('billing') ?></th><?php } ?>
	<?php if ($showSelCol) {
			$cols = get_cols($columns);
			foreach ($cols as $k => $i){
				if (!is_numeric($i)){
					?><th style="padding:4px;border-bottom:1px solid #666666"><?php echo lang("field ProjectTasks ".$i) ?></th><?php 
				} 
				else {
					$cp = CustomProperties::getCustomProperty($i);
					?><th style="padding:4px;border-bottom:1px solid #666666"><?php echo ($cp->getName()) ?></th><?php
				}
			}
		  }//if 
	} ?></tr><?php
		
		//Print row info
?>
<tr>
	<td style="padding:4px;<?php echo $isAlt? 'background-color:#F2F2F2':'' ?>"><?php echo format_datetime($ts->getStartTime(), $date_format)?></td>
	<?php if ($showTitleCol) { ?><td style="padding:4px;max-width:250px;<?php echo $isAlt? 'background-color:#F2F2F2':'' ?>"><?php echo ($ts->getObjectManager() == 'Projects' ? lang('workspace') . ':&nbsp;' . clean($ts->getObject()->getName()) : clean($ts->getObject()->getTitle())) ?></td><?php } ?>
	<td style="padding:4px; width:250px;<?php echo $isAlt? 'background-color:#F2F2F2':'' ?>"><?php echo clean($ts->getDescription()) ?></td>
	<?php if ($showUserCol) { ?><td style="padding:4px;<?php echo $isAlt? 'background-color:#F2F2F2':'' ?>"><?php echo clean(Users::getUserDisplayName($ts->getUserId())) ?></td><?php } ?>
	<?php $lastStop = $ts->getEndTime() != null ? $ts->getEndTime() : ($ts->isPaused() ? $ts->getPausedOn() : DateTimeValueLib::now()); ?>
	<td style="padding:4px;text-align:right;<?php echo $isAlt? 'background-color:#F2F2F2':'' ?>"><?php echo DateTimeValue::FormatTimeDiff($ts->getStartTime(), $lastStop, "hm", 60, $ts->getSubtract()) ?>
	<?php if ($showBillingCol) { ?><td style="padding:4px;text-align:right;<?php echo $isAlt? 'background-color:#F2F2F2':'' ?>"><?php echo config_option('currency_code', '$') ?>&nbsp;<?php echo $ts->getFixedBilling() ?></td><?php } ?>
	<?php if ($showSelCol) {
			$cols = get_cols($columns);	
			foreach ($cols as $k => $i){
				if ($ts->getObjectManager() == 'ProjectTasks'){
					$task = $ts->getObject();						
						if (!is_numeric($i)){	//for normal properties		
												//currently disabled as at the moment the only columns that can be added are custom properties							
								$value = format_value_to_print_task($task->getColumnValue($i),$task->getColumnType($i));
								?><td style="padding:4px;max-width:250px;<?php echo $isAlt? 'background-color:#F2F2F2':'' ?>"><?php echo ($value) ?></td><?php			
							
						} 
						else {//for custom properties									
							$values = CustomPropertyValues::getCustomPropertyValue($task->getId(), $i);	
							if ($values != null){
								$cp = CustomProperties::getCustomProperty($i);											
								$value = format_value_to_print_task($values->getValue(),$cp->getOgType());
								?><td style="padding:4px;max-width:250px;<?php echo $isAlt? 'background-color:#F2F2F2':'' ?>"><?php echo ($value) ?></td><?php
							}else{						
								?><td style="padding:4px;max-width:250px;<?php echo $isAlt? 'background-color:#F2F2F2':'' ?>"><?php echo ('') ?></td><?php
							}							
						}
				} else{				
					?><td style="padding:4px;max-width:250px;<?php echo $isAlt? 'background-color:#F2F2F2':'' ?>"><?php echo ('') ?></td><?php
				}	
								
		   } //foreach ?>
</tr>
<?php } // if
	} //foreach
}

		for ($i = $sectionDepth - 1; $i >= 0; $i--){?>
<tr style="padding-top:2px;text-align:right;font-weight:bold;">
	<td style="padding:4px;border-top:2px solid #888;font-size:90%;color:#AAA;text-align:left;font-weight:normal"><?php echo truncate(clean(getGroupTitle($group_by[$i], $previousTSRow)),40,'&hellip;') ?></td>
	<td colspan=<?php echo ($showBillingCol)? $totCols -2 : $totCols -1 ?> style="padding:4px;border-top:2px solid #888;text-align:right;"><?php echo lang('total') ?>:&nbsp;<?php echo DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($sumTimes[$i] * 60), "hm", 60) ?></td>
	<?php if ($showBillingCol) { ?><td style="width:30px;padding:4px;border-top:2px solid #888;text-align:right;"><?php echo config_option('currency_code', '$') ?>&nbsp;<?php echo $sumBillings[$i] ?></td><?php } ?>
</tr></table></div></td></tr>
		<?php }?>



<?php
// UNWORKED TASKS
if (isset($unworkedTasks) && count($unworkedTasks) > 0) { 
	?>
	<tr><td colspan=<?php echo $totCols ?>><div style="width=100%;padding-top:10px;padding-bottom:5px;"><table style="width:100%">
<tr><td style="border-bottom:2px solid #888;font-size:135%;font-weight:bold;"><?php echo lang("unworked pending tasks") ?></td></tr>	
<?php
	$isAlt = true;
	foreach ($unworkedTasks as $t) {?>
	<tr><td style="padding:4px;<?php echo $isAlt? 'background-color:#F2F2F2':'' ?>"><?php echo clean($t->getTitle()) ?></td></tr>
<?php $isAlt = !$isAlt;
	} // foreach ?>

<tr style="padding-top:2px;font-weight:bold;">
	<td style="padding:4px;border-top:2px solid #888;font-size:90%;color:#AAA;text-align:left;font-weight:normal"><?php echo lang("unworked pending tasks") ?></td>
</tr></table></div></td></tr>
<?php 
} // if
 
// TOTAL TIME
if (is_array($timeslotsArray)) {
	foreach ($timeslotsArray as $ts) {
		$t = $ts['ts'];
		if (isset($has_conditions) && $has_conditions && $t->getObjectManager() == 'Projects') continue;
		$sumTime += $t->getMinutes();
		$sumBilling += $t->getFixedBilling();
	}
}?>
<tr><td style="text-align: right; border-top: 1px solid #AAA; padding: 10px 0; font-weight: bold;" colspan=<?php echo ($showBillingCol)? $totCols -1 : $totCols ?>>
<div ><?php echo strtoupper(lang("total")) . ": " . DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($sumTime * 60), "hm", 60) ?></div>
</td><?php if ($showBillingCol) { ?><td style="width:30px;padding-left:8px;border-top: 1px solid #AAA;"><div style="text-align: right;padding: 10px 0; font-weight: bold;"><?php echo config_option('currency_code', '$') ?>&nbsp;<?php echo $sumBilling ?></div></td><?php } ?>
</tr>
</table>