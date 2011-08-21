<?php 
	$genid = gen_id();
	$selectedPage = user_config_option('custom_report_tab', 'tasks');
	$customReports = Reports::getAllReportsByObjectType();
	$manageReports = can_manage_reports(logged_user());
	$report = new Report(); 
	$can_add_reports = $report->canAdd(logged_user());
	$reportPages = array(
		"contacts" => array("type" => "Contacts"),
		"companies" => array("type" => "Companies"),
		"workspaces" => array("type" => "Projects"),
		"messages" => array("type" => "ProjectMessages"),
		"documents" => array("type" => "ProjectFiles"),
		"emails" => array("type" => "MailContents"),
		"tasks" => array("type" => "ProjectTasks"),
		"milestones" => array("type" => "ProjectMilestones"),
		"events" => array("type" => "ProjectEvents"),
		"weblinks" => array("type" => "ProjectWebpages"));
	
	if (logged_user()->isAdministrator()){
		$reportPages["users"] = array("type" => "Users");
	}
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
	// MAIN PAGES
	$show_help_option = user_config_option('show_context_help'); 
	if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_reporting_panel_context_help', true, logged_user()->getId()))) {
		$hd_key = 'chelp reporting panel';
	  	if (can_manage_reports(logged_user())){
	  		$hd_key .= ' manage';
	  		if (logged_user()->isAdministrator() && can_manage_security(logged_user())){
	  			$hd_key .= ' admin'; 
	  		}
	  	}

		render_context_help($this, $hd_key, 'reporting_panel');
		echo '<br/>';
	} 

	foreach ($reportPages as $pageTitle => $pageInfo) {?>
<div class="inner_report_menu_div" id="<?php echo $genid . $pageTitle?>" style="display:<?php echo $pageTitle == $selectedPage? 'block' : 'none';?>">

<?php 
	// Show default (non-custom) reports
	$hasNonCustomReports = true;
	switch($pageTitle){
	case 'tasks':?>
	<ul style="padding-top:4px;padding-bottom:15px">
		<li><div><a style="font-weight:bold" class="internalLink" href="<?php echo get_url('reporting','total_task_times_p')?>"><?php echo lang('task time report') ?></a>
		<div style="padding-left:15px"><?php echo lang('task time report description') ?></div>
		</div>
		</li>
		<?php if (false) { ?><li><a class="internalLink" href="<?php echo get_url('reporting','total_task_times_vs_estimate_comparison_p')?>"><?php echo lang('estimate vs total task times report') ?></a></li><?php } ?>
	</ul>
	<?php break; // tasks
	default: $hasNonCustomReports = false; break;
	} // switch pagetitle
	
	
	// CUSTOM REPORTS
	$reports = (array_key_exists($pageInfo["type"],$customReports) && is_array($customReports[$pageInfo["type"]])) ? $customReports[$pageInfo["type"]] : array(); 
	if ($manageReports || count($reports) > 0){?>
<div class="report_header"><?php echo lang('custom reports') ?></div>
<?php 
	if(count($reports) > 0){  ?>
	<ul>
	<?php foreach($reports as $report){ ?>
		<li style="padding-top:4px"><div><a style="font-weight:bold;margin-right:15px" class="internalLink" href="<?php echo get_url('reporting','view_custom_report', array('id' => $report->getId()))?>"><?php echo $report->getName() ?></a>
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
		echo lang('no custom reports') . '<br/>';
	} // if count
	
	// Add new custom report 
	if ($can_add_reports) { ?>
	<br/><a class="internalLink coViewAction ico-add" href="<?php echo get_url('reporting', 'add_custom_report', array('type' => $pageInfo['type'])) ?>"><?php echo lang('add custom report')?></a>
	<?php } // add new report link?>
<?php } else {
	if(!$hasNonCustomReports){
		echo lang('no reports found', lang($pageTitle));
	}
} // CUSTOM REPORTS ?>
</div>
<?php } // MAIN PAGES?>
</div>
</td><td class="coViewTopRight"></td></tr>


<?php // MENU ROWS
	foreach ($reportPages as $pageTitle => $pageInfo) {?>
<tr><td class="report_<?php echo $pageTitle == $selectedPage ? '' : 'un'?>selected_menu">
<a href="#" onclick="javascript:og.selectReportingMenuItem(this, '<?php echo $genid . $pageTitle?>', '<?php echo $pageTitle ?>')">
	<div class="coViewAction ico-<?php echo $pageTitle; ?>" style="width:130px;padding-bottom:2px"><?php echo lang($pageTitle) ?></div>
</a>
</td><td class="coViewRight"></td>
</tr>
<?php } // MENU ROWS?>

<tr><td rowspan=2 style="min-height:20px"></td><td class="coViewRight"></td></tr>
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