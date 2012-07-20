<?php
if (!isset($genid)) $genid = gen_id();
if (!isset($allow_export)) $allow_export = true;
  	/*add_page_action(lang('print view'), '#', "ico-print", "_blank", array('onclick' => 'this.form' . $genid . '.submit'));*/
?>
<form id="form<?php echo $genid ?>" name="form<?php echo $genid ?>" action="<?php echo get_url('reporting', $template_name . '_print') ?>" method="post" enctype="multipart/form-data" target="_download">

    <input name="post" type="hidden" value="<?php echo str_replace('"',"'", json_encode($post))?>"/>
    
<div class="report" style="padding:7px">
<table style="min-width:600px">

<tr>
	<td rowspan=2 colspan="2" class="coViewHeader" style="width:auto;">
		<div id="iconDiv" class="coViewIconImage ico-large-report" style="float:left;"></div>

		<div class="coViewTitleContainer">
			<div class="coViewTitle" style="margin-left:55px;"><?php echo $title ?></div>			
                        <input type="submit" name="print" value="<?php echo lang('print view') ?>" onclick="og.reports.printReport('<?php echo $genid?>','<?php echo $title ?>'); return false;" style="width:120px; margin-top:10px;"/>

                        <input type="submit" name="exportCSV" value="<?php echo lang('export csv') ?>" onclick="document.getElementById('form<?php echo $genid ?>').target = '_download';" style="width:120px; margin-top:10px;"/>
                        <?php if ($allow_export) { ?>
                        <input type="button" name="exportPDFOptions" onclick="og.showPDFOptions();" value="<?php echo lang('export pdf') ?>" style="width:120px; margin-top:10px;"/>
                        <?php } ?>

                        <input name="parameters" type="hidden" value="<?php echo str_replace('"',"'", json_encode($post))?>"/>
                        <input name="context" type="hidden" value="" id="<?php echo $genid?>_plain_context"/>
		</div>
		<div class="clear"></div>
	</td>
	
	<td class="coViewTopRight" width="10px"></td>
</tr>
<tr>
	<td class="coViewRight" rowspan=1></td>
</tr>
<tr>
	<td colspan=2 class="coViewBody" style="padding-left:12px" id="<?php echo $genid?>report_container">
		<?php $this->includeTemplate(get_template_path($template_name, 'reporting'));?>
	</td>
		<td class="coViewRight"/>
</tr>
<tr>
	<td class="coViewBottomLeft"></td>
	<td class="coViewBottom" style="width:100%;"></td>
	
	<td class="coViewBottomRight"></td>
</tr>
</table>

</div>
</form>
<script>
document.getElementById('<?php echo $genid?>_plain_context').value = og.contextManager.plainContext();

og.reports = {};
og.reports.createPrintWindow = function(title) {
	var disp_setting = "toolbar=yes,location=no,directories=yes,menubar=yes,scrollbars=yes,";
	var printWindow = window.open("","",disp_setting);
	printWindow.document.open(); 
	printWindow.document.write('<html><head><title>' + title + '</title>');
	printWindow.document.write('<link href="' + og.hostName + '/public/assets/themes/default/stylesheets/website.css" rel="stylesheet" type="text/css">');
	printWindow.document.write('<link href="' + og.hostName + '/public/assets/themes/default/stylesheets/general/rewrites.css" rel="stylesheet" type="text/css">');
	printWindow.document.write('</head><body onLoad="self.print()" id="body"><h1>' + title + '</h1>');             
	return printWindow;
}

og.reports.closePrintWindow = function(printWindow) {
	printWindow.document.write('</body></html>');    
	printWindow.document.close();
	printWindow.focus();
}

og.reports.printReport = function(genid, title) {
	var printWindow = og.reports.createPrintWindow(title);

	printWindow.document.write(document.getElementById(genid + 'report_container').innerHTML);
	
	og.reports.closePrintWindow(printWindow);
}

</script>