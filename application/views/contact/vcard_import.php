<?php
	$submit_url = get_url('contact', 'import_from_vcard');
	$genid = gen_id();
?>

<script>
og.submitVcard = function(genid, processResult) {
	fname = document.getElementById(genid + 'filenamefield');
	ok = true;
	if (!processResult) {
		if (fname.value.lastIndexOf('.vcf') == -1 || fname.value.lastIndexOf('.vcf') != fname.value.length - 4 ) {
			ok = confirm(lang('not vcf file continue'));
		}
	}
	
	if (ok) {
		form = document.getElementById(genid + 'vcard_import');
		og.submit(form, {
			callback: og.getUrl('contact', 'import_from_vcard', {step2:processResult, tags:Ext.get(genid+'tags').getValue()})
		});
	}
}
</script>

<form style="height:100%;background-color:white" id="<?php echo $genid ?>vcard_import" name="<?php echo $genid ?>vcard_import" class="internalForm" action="<?php echo $submit_url ?>" method="post" enctype="multipart/form-data">

<div class="file">
<div class="coInputHeader">
<div class="coInputHeaderUpperRow">
<div class="coInputTitle">
	<table style="width:535px"><tr>
		<td><?php echo isset($import_result) ? lang('import result') : ($import_type == 'contact' ? lang('import contacts from vcard') : lang('import companies from vcard'));?></td>
		<?php if (!isset($import_result)) { ?>
		<td style="text-align: right">
			<?php echo submit_button(lang('import'),'s',array("onclick" => 'og.submitVcard(\'' . $genid .'\', 1); return false;', 'style'=>'margin-top:0px;margin-left:10px','id' => $genid.'add_file_submit1', 'tabindex' => '210')) ?>
		</td>
		<?php } ?>
	</tr></table>
</div>
</div>

<?php if (!isset($import_result) && array_var($_GET, 'from_menu')) { ?>
	<div id="<?php echo $genid ?>selectFileControlDiv">
        <?php echo label_tag(lang('file'), $genid . 'filenamefield', true) ?>
        <?php echo file_field('vcard_file', null, array('id' => $genid . 'filenamefield', 'class' => 'title', 'tabindex' => 10, 'size' => '88', "onchange" => 'og.submitVcard(\'' . $genid .'\', 0);')) ?>
    </div>
<?php } //if ?>
<?php if (!isset($import_result) && !array_var($_GET, 'from_menu')) { ?>
	<div>
		<a href="#" class="option" tabindex=0 onclick="og.toggleAndBolden('<?php echo $genid ?>import_contact_add_tags_div', this)"><?php echo lang('tags') ?></a>
	</div>
<?php } ?>
</div>
<div class="coInputMainBlock adminMainBlock">
<?php
	if (!isset($import_result)) { ?>
		<p><b><?php 
			if (array_var($_GET, 'from_menu')) echo lang('select a vcard file to load its data');
			else echo lang('you can tag the contacts before running the import');
		?></b></p>
		
		<div id="<?php echo $genid ?>import_contact_add_tags_div" style="display:none">
			<fieldset><legend><?php echo lang('tags')?></legend>
				<?php echo autocomplete_tags_field("tags", '', $genid."tags"); ?>
			</fieldset>
		</div>
<?php	}
	if (isset($import_result)) {
		if (count($import_result['import_ok'])) {
			$isAlt = false;
?>
	<br><table><tr><th colspan="2" style="text-align:center"><?php echo ($import_type == 'contact' ? lang('contacts succesfully imported') : lang('companies succesfully imported')) ?></th>
				   <th style="text-align:center"><?php echo lang('status') ?></th></tr>
<?php 		foreach ($import_result['import_ok'] as $reg) { ?>
				<tr<?php echo ($isAlt ? ' class="altRow"': '') ?>>
				<td style="padding-left:10px;"><?php echo $import_type == 'contact' ? array_var($reg, 'firstname') . ' ' . array_var($reg, 'lastname') : array_var($reg, 'name')?></td>
				<td style="padding-left:10px;"><?php echo array_var($reg, 'email') ?></td>
				<td style="padding-left:10px;"><span class="desc"><?php echo array_var($reg, 'import_status') ?></span></td></tr>
<?php 			$isAlt = !$isAlt;
			} ?>
	</table>
<?php 	} //if
		if (count($import_result['import_fail'])) {
			$isAlt = false;
?>
	<br><table><tr><th colspan="2" style="text-align:center"><?php echo ($import_type == 'contact' ? lang('contacts import fail') : lang('companies import fail')) ?></th>
				   <th style="text-align:center"><?php echo lang('import fail reason') ?></th></tr>
<?php 		foreach ($import_result['import_fail'] as $reg) { ?>
				<tr<?php echo ($isAlt ? ' class="altRow"': '') ?>>
				<td style="padding-left:10px;"><?php echo $import_type == 'contact' ? array_var($reg, 'firstname') . ' ' . array_var($reg, 'lastname') : array_var($reg, 'name')?></td>
				<td style="padding-left:10px;"><?php echo array_var($reg, 'email') ?></td>
				<td style="padding-left:10px;"><?php echo array_var($reg, 'fail_message') ?></td></tr>
<?php 			$isAlt = !$isAlt;
			} ?>
	</table>
<?php 	}
	} //if?>
	</div>
</div>
</form>

<script>
	btn = Ext.get('<?php echo $genid ?>filenamefield');
	if (btn != null) btn.focus();
</script>