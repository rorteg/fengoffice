<?php
	$genid = gen_id();
?>
<form style='height:100%;background-color:white' class="internalForm" action="<?php echo $billing->isNew() ? get_url('billing', 'add') : $billing->getEditUrl() ?>" method="post" enctype="multipart/form-data">

<div class="billing">
<div class="coInputHeader">
<div class="coInputHeaderUpperRow">
	<div class="coInputTitle"><table style="width:535px"><tr><td><?php echo $billing->isNew() ? lang('new billing category') : lang('edit billing category') ?>
	</td><td style="text-align:right"><?php echo submit_button($billing->isNew() ? lang('add billing category') : lang('save changes'),'s',array('style'=>'margin-top:0px;margin-left:10px')) ?></td></tr></table>
	</div>
	
	</div>
	<div>
	<?php echo label_tag(lang('name'), $genid . 'billingFormName', true) ?>
	<?php echo text_field('billing[name]', array_var($billing_data, 'name'), 
		array('id' => $genid . 'billingFormName', 'class' => 'title', 'tabindex' => '1')) ?>
	</div>
	
	<div style="padding-top:5px">
	</div>
</div>
<div class="coInputSeparator"></div>
<div class="coInputMainBlock">

	<?php if (false) { ?>
	<?php echo label_tag(lang('report name'), $genid . 'billingFormReportName', false) ?>
	<?php echo text_field('billing[report_name]', array_var($billing_data, 'report_name'), 
		array('id' => $genid . 'billingFormReportName', 'tabindex' => '2')) ?>
	<?php } ?>
		
	<?php echo label_tag(lang('default hourly rates'), $genid . 'billingFormValue', true) ?>
	<?php echo text_field('billing[default_value]', array_var($billing_data, 'default_value'), 
		array('id' => $genid . 'billingFormValue', 'tabindex' => '3')) ?>
		
	<?php echo label_tag(lang('description'), $genid . 'billingFormDescription', false) ?>
	<?php echo textarea_field('billing[description]', array_var($billing_data, 'description'), 
		array('id' => $genid . 'billingFormDescription', 'class' => 'comment', 'tabindex' => '4')) ?>
	<br/>
	<?php echo submit_button($billing->isNew() ? lang('add billing category') : lang('save changes'),'s',
		array('style'=>'margin-top:0px', 'tabindex' => '5')) ?>
</div>
</div>
</form>

<script>
	Ext.get('<?php echo $genid ?>billingFormName').focus();
</script>
