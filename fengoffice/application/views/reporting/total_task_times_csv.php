<?php
	$filename = str_replace(' ', '_', $title).date('_YmdHis');
	header('Expires: 0');
	header('Cache-control: private');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Content-Description: File Transfer');
	header('Content-Type: application/csv; charset=iso-8859-1;');
	header('Content-disposition: attachment; filename='.$filename.'.csv');
	
	function cvs_total_task_times_group($group_obj, $grouped_objects, $options, $skip_groups = array(), $level = 0, $prev = "", &$total = 0) {
		
		$pad_str = "";
		for ($k = 0; $k < $level; $k++) $pad_str .= "   ";
		
		$cls_suffix = $level > 2 ? "all" : $level;
		$next_level = $level + 1;
			
		$group_name = $group_obj['group']['name'];
		echo '"'. $pad_str .iconv('utf-8', 'iso-8859-1', $group_name) . '"'. "\n";
		
		$mem_index = $prev . $group_obj['group']['id'];
		
		$group_total = 0;
		
		$table_total = 0;
		// draw the table for the values
		if (isset($grouped_objects[$mem_index]) && count($grouped_objects[$mem_index]) > 0) {
			cvs_total_task_times_table($grouped_objects[$mem_index], $pad_str, $options, $group_name, $table_total);
			$group_total += $table_total;
		}
		
		if (!is_array($group_obj['subgroups'])) return;
		
		$subgroups = order_groups_by_name($group_obj['subgroups']);
		
		foreach ($subgroups as $subgroup) {
			$sub_total = 0;
			cvs_total_task_times_group($subgroup, $grouped_objects, $options, $skip_groups, $next_level, $prev . $group_obj['group']['id'] . "_", $sub_total);
			$group_total += $sub_total;
		}
		
		$total += $group_total;
		
		echo "$group_name;;;;".lang('total'). ': ' . DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($group_total * 60), "hm", 60).";\n\n";
	}
	
	function cvs_total_task_times_table($objects, $pad_str, $options, $group_name, &$sub_total = 0) {
		
		echo lang('date') . ';';
		echo lang('title') . ';';
		echo lang('description') . ';';
		echo lang('person') . ';';
		echo lang('time') . ';';
		echo "\n";
		
		$sub_total = 0;
		
		foreach ($objects as $ts) {
			echo $pad_str . format_date($ts->getStartTime()) . ';';
			echo ($ts->getRelObjectId() == 0 ? clean($ts->getObjectName()) : clean($ts->getRelObject()->getObjectName())) . ';';
			echo clean($ts->getDescription()) .';';
			echo clean($ts->getUser()->getObjectName()) .';';
			$lastStop = $ts->getEndTime() != null ? $ts->getEndTime() : ($ts->isPaused() ? $ts->getPausedOn() : DateTimeValueLib::now());
			echo DateTimeValue::FormatTimeDiff($ts->getStartTime(), $lastStop, "hm", 60, $ts->getSubtract()) .';';
			
			$sub_total += $ts->getMinutes();
			echo "\n";
		}
	}
	
	
	$skip_groups = array();
	$context = active_context();
	foreach ($context as $selection) {
		if ($selection instanceof Member) {
			$sel_parents = $selection->getAllParentMembersInHierarchy();
			foreach ($sel_parents as $sp) $skip_groups[] = $sp->getId();
		}
	}

	$groups = order_groups_by_name($grouped_timeslots['groups']);
	$total = 0;
	foreach ($groups as $gid => $group_obj) {
		cvs_total_task_times_group($group_obj, $grouped_timeslots['grouped_objects'], array_var($_SESSION, 'total_task_times_parameters'), $skip_groups, 0, "", $total);
	}

	echo ";;;;".lang('total'). ': ' . DateTimeValue::FormatTimeDiff(new DateTimeValue(0), new DateTimeValue($total * 60), "hm", 60).";\n";

	die();