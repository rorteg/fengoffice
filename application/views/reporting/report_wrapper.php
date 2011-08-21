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
	<td  class="coViewIcon" width="36px">
		<div id="iconDiv" class="coViewIconImage ico-large-report"></div>
	</td>
	<td rowspan=2 colspan="1" class="coViewHeader" style="width:auto;">
		<div class="coViewTitleContainer">
			<div class="coViewTitle"><?php echo $title ?></div>
			<input type="submit" name="print" value="<?php echo lang('print view') ?>" onclick="document.getElementById('form<?php echo $genid ?>').target = '_blank' + Ext.id()" style="width:120px; margin-top:10px;"/>
			<input type="submit" name="exportCSV" value="<?php echo lang('export csv') ?>" onclick="document.getElementById('form<?php echo $genid ?>').target = '_download';" style="width:120px; margin-top:10px;"/>
			<?php if ($allow_export) { ?>
			<input type="button" name="exportPDFOptions" onclick="og.showPDFOptions();" value="<?php echo lang('export pdf') ?>" style="width:120px; margin-top:10px;"/>
			<?php } ?>
		</div>
	</td>
	
	<td class="coViewTopRight" width="10px"></td>
</tr>
<tr>
	<td class="coViewRight" rowspan=1></td>
</tr>
<tr>
	<td colspan=2 class="coViewBody" style="padding-left:12px">
		<?php $this->includeTemplate(get_template_path($template_name, 'reporting'));?>
	</td>
		<td class="coViewRight"/>
</tr>
<tr>
	<td class="coViewBottomLeft"></td>
	<td class="coViewBottom" ></td>
	
	<td class="coViewBottomRight"></td>
</tr>
</table>

</div>

</form>