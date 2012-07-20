<?php 
	$genid = gen_id();
	$selectedPage = user_config_option('custom_report_tab');
	$customReports = Reports::getAllReportsByObjectType();
	$report = new Report(); 
	$can_add_reports = $report->canAdd(logged_user(), active_context());
	
	$reportPages = array();
	$object_types = ObjectTypes::getAvailableObjectTypes();
	foreach ($object_types as $ot) {
		$reportPages[$ot->getId()] = array("name" => $ot->getName(), "display_name" => lang($ot->getName()));
	}
	
	$ignored = null;
	Hook::fire('modify_report_pages', $ignored, $reportPages); // To add, edit or remove report pages
	
	
	$default_reports = array(
		'task' => array('task time report' => array('url' => get_url('reporting','total_task_times_p'), 'name' => lang('task time report'), 'description' => lang('task time report description'))),
	);
	
	Hook::fire('modify_default_reports', $ignored, $default_reports); // To add, edit or remove default reports

	
	require_javascript("og/ReportingFunctions.js");
?>

<div style="padding:7px">
<table width=100% id="reportingMenu">
<tr>
	<td style="height:2px;width:140px"></td><td width=12></td><td style="line-height:2px;">&nbsp;</td><td width=12></td>
</tr>
<tr>
<td height=12></td>
<td rowspan=<?php echo count($reportPages) + 2 ?> colspan=2 style="background-color:white">

<div style="padding:10px">
<?php 
	
	foreach ($reportPages as $type_id => $pageInfo) {?>
<div class="inner_report_menu_div" id="<?php echo $genid . $type_id?>" style="display:<?php echo $type_id == $selectedPage ? 'block' : 'none';?>">

<?php 
	$page_default_reports = array_var($default_reports, array_var($pageInfo, 'name'), array());
	$hasNonCustomReports = count($page_default_reports) > 0;
	if ($hasNonCustomReports) {
		?><ul style="padding-top:4px;padding-bottom:15px"><?php 
	}
	foreach ($page_default_reports as $def_report) { ?>
		<li><div><a style="font-weight:bold" class="internalLink" href="<?php echo array_var($def_report, 'url') ?>"><?php echo array_var($def_report, 'name') ?></a>
			<div style="padding-left:15px"><?php echo array_var($def_report, 'description') ?></div>
		</div></li>
<?php
	}
	if ($hasNonCustomReports) {
		?></ul><?php 
	}
	
	// CUSTOM REPORTS
	$reports = array_var($customReports, $type_id, array());
	
?>
<div class="report_header"><?php echo lang('custom reports') ?></div>
<?php 
	if(count($reports) > 0){  ?>
	<ul>
	<?php foreach($reports as $report){ ?>
		<li style="padding-top:4px"><div><a style="font-weight:bold;margin-right:15px" class="internalLink" href="<?php echo get_url('reporting','view_custom_report', array('id' => $report->getId()))?>"><?php echo $report->getObjectName() ?></a>
			<?php if ($report->canEdit(logged_user())) { ?>
				<a style="margin-right:5px" class="internalLink coViewAction ico-edit" href="<?php echo get_url('reporting','edit_custom_report', array('id' => $report->getId()))?>"><?php echo lang('edit') ?></a>
			<?php } ?>
			<?php if ($report->canDelete(logged_user())) { ?>
				<a style="margin-right:5px" class="internalLink coViewAction ico-delete" href="javascript:og.deleteReport(<?php echo $report->getId() ?>)"><?php echo lang('delete') ?></a>
			<?php } ?>
			<div style="padding-left:15px"><?php echo $report->getDescription() ?></div>
			</div>
		</li>
	<?php } //foreach?>
	</ul>
<?php } else {
		echo lang('no custom reports', lang($reportPages[$type_id]['name'])) . '<br/>';
	} // if count
	
	// Add new custom report 
	if ($can_add_reports) { ?>
	<br/><a class="internalLink coViewAction ico-add" href="<?php echo get_url('reporting', 'add_custom_report', array('type' => $type_id)) ?>"><?php echo lang('add custom report')?></a>
<?php } ?>

</div>
<?php } // MAIN PAGES?>
</div>
</td><td class="coViewTopRight"></td></tr>


<?php // MENU ROWS
	foreach ($reportPages as $type_id => $pageInfo) {?>
<tr><td class="report_<?php echo $type_id == $selectedPage ? '' : 'un'?>selected_menu">
<a href="#" onclick="javascript:og.selectReportingMenuItem(this, '<?php echo $genid . $type_id?>', '<?php echo $type_id ?>')">
	<div class="report_menu_item ico-<?php echo array_var($pageInfo, 'name'); ?>"><?php echo array_var($pageInfo, 'display_name') ?></div>
</a>
</td><td class="coViewRight"></td>
</tr>
<?php } // MENU ROWS?>

<tr><td rowspan=2 style="min-height:20px;"></td><td class="coViewRight"></td></tr>
<tr><td class="coViewBottomLeft"></td>
	<td class="coViewBottom"></td>
	<td class="coViewBottomRight"></td>
</tr>
</table>

</div>

<script>
	og.deleteReport = function(id){
		if(confirm(lang('delete report confirmation'))){
			og.openLink(og.getUrl('reporting', 'delete_custom_report', {id: id}));
		}
	};
</script>