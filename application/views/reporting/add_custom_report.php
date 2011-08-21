<?php
require_javascript("og/DateField.js");
require_javascript("og/ReportingFunctions.js");
require_javascript('og/modules/doubleListSelCtrl.js');
require_javascript('og/CSVCombo.js');
$genid = gen_id();
if (!isset($conditions)) $conditions = array();
?>
<form style='height: 100%; background-color: white' class="internalForm"
	action="<?php echo $url  ?>" method="post"
	onsubmit="return og.validateReport('<?php echo $genid ?>');"><input
	type="hidden" name="report[object_type]" id="report[object_type]"
	value="<?php echo isset($report_data['object_type']) ? $report_data['object_type'] : "" ?>" />
<div class="coInputHeader">
<div class="coInputHeaderUpperRow">
<div class="coInputTitle">
<table style="width: 535px">
	<tr>
		<td><?php echo (isset($id) ? lang('edit custom report') : lang('new custom report')) ?>
		</td>
		<td style="text-align: right"><?php echo submit_button((isset($id) ? lang('save changes') : lang('add report')),'s',array('style'=>'margin-top:0px;margin-left:10px', 'tabindex' => '20')) ?></td>
	</tr>
</table>
</div>
</div>

<div>
<?php echo label_tag(lang('name'), $genid . 'reportFormName', true) ?>
<?php echo text_field('report[name]', array_var($report_data, 'name'),
array('id' => $genid . 'reportFormName', 'tabindex' => '1', 'class' => 'title')) ?>
<?php echo label_tag(lang('description'), $genid . 'reportFormDescription', false) ?>
<?php echo text_field('report[description]', array_var($report_data, 'description'),
array('id' => $genid . 'reportFormDescription', 'tabindex' => '2', 'class' => 'title')) ?>
<?php echo label_tag(lang('object type'), $genid . 'reportFormObjectType', true) ?>
<?php
$selected_option=null;
$options = array();
foreach ($object_types as $type) {
	if ($selected_type == $type[0]) {
		$selected = 'selected="selected"';
		$selected_option = $type[1];
	} else {
		$selected = '';
	}
	$options[] = '<option value="'.$type[0].'" '.$selected.'>'.$type[1].'</option>';
} 
?>
<?php $strDisabled = count($options) > 1 ? '' : 'disabled'; 
echo select_box('objectTypeSel', $options,
array('id' => 'objectTypeSel' ,'onchange' => 'og.reportObjectTypeChanged("'.$genid.'", "", 1, "")', 'style' => 'width:200px;', $strDisabled => '', 'tabindex' => '10')) ?>

</div>
</div>
<div id="<?php echo $genid ?>MainDiv" class="coInputMainBlock" style="display:none;">

<fieldset><legend><?php echo lang('conditions') ?></legend>
<div id="<?php echo $genid ?>"></div>
<br />
<a href="#" class="link-ico ico-add"
	onclick="og.addCondition('<?php echo $genid ?>', 0, 0, '', '', '', false)"><?php echo lang('add condition')?></a>
</fieldset>

<fieldset><legend><?php echo lang('columns and order') ?></legend>
	<div id="columnListContainer"></div>
</fieldset>
<?php echo submit_button((isset($id) ? lang('save changes') : lang('add report')), 's', array('tabindex' => '20000'))?>

</div>

</form>

<script>
	og.loadReportingFlags();
	og.reportObjectTypeChanged('<?php echo $genid?>', '<?php echo array_var($report_data, 'order_by') ?>', '<?php echo array_var($report_data, 'order_by_asc') ?>', '<?php echo (isset($columns) ? implode(',', $columns) : '') ?>');
	<?php if(isset($conditions)){ ?>
		<?php foreach($conditions as $condition){?>
		    og.addCondition('<?php echo $genid?>',<?php echo $condition->getId() ?>, <?php echo $condition->getCustomPropertyId() ?> , '<?php echo $condition->getFieldName() ?>', '<?php echo $condition->getCondition() ?>', '<?php echo clean(str_replace("'", "\'", $condition->getValue()))?>', '<?php echo $condition->getIsParametrizable() ?>');		
		<?php 
		}//foreach ?>
	<?php }//if ?>
	
	var first = document.getElementById('<?php echo $genid ?>reportFormName');
	if (first) first.focus();
</script>
