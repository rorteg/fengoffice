<?php $genid = gen_id(); ?>

<script>
	var allCompanies = [];
	var emailComp = []
<?php 
		if (is_array($allCompanies)) { 
			foreach ($allCompanies as $id => $name) { ?>
				allCompanies[allCompanies.length] = {id: '<?php echo $id ?>', name: <?php echo json_encode($name) ?>};
<?php 		}
		} ?>
<?php 
		if (is_array($emailAndComp)) { 
			foreach ($emailAndComp as $email => $comp) { ?>
				emailComp[emailComp.length] = {email: <?php echo json_encode($email) ?>, company: <?php echo json_encode($comp) ?>};
<?php 		}
		} ?>

	og.retrieveCompanyName = function(id) {
		for (i=0; i<allCompanies.length; i++) {
			if (allCompanies[i].id == id) return allCompanies[i].name;
		}
		return null;
	}
	
	og.retrieveEmailCompany = function(email) {
		for (i=0; i<emailComp.length; i++) {
			if (emailComp[i].email == email) return emailComp[i].company;
		}
		return null;
	}
	
	var invited = 0;
	og.onAddPeopleToShare = function() {
		var email = Ext.get('<?php echo $genid ?>shareWithEmails').getValue();
		var company_id = Ext.get('<?php echo $genid ?>shareCompanies').getValue();
		var comp_name = og.retrieveCompanyName(company_id);
		if (email.length > 0) {
			if (email.indexOf(',') >= 0) email = email.substring(1, email.indexOf(','));
			email = email.replace(/"/g, "'");
			comp_name = comp_name.replace(/"/g, "'");
			
			var inv_tab = Ext.get('<?php echo $genid ?>invitedPeople');
			if (inv_tab != null) {
				inv_tab.insertHtml('beforeEnd', '<tr><td style="padding-left:5px;margin-right:15px;">' +
					'<input type="text" name="emails['+invited+']" value="'+email+'" readonly="readonly" style="border-width:0px;width:400px;font-style:italic;"></input>' +
					'</td><td style="padding-left:5px;">' + 
					'<input type="text" name="companies['+invited+']" value="'+comp_name+'" readonly="readonly" style="border-width:0px;width:150px;font-style:italic;"></input>' +
					'<input type="hidden" name="companiesId['+invited+']" value="'+company_id+'" readonly="readonly"></input>' +
					'</td></tr>');	
				invited++;
				inv_tab.repaint();
			}
			document.getElementById('<?php echo $genid ?>shareWithEmails').value = '';
			document.getElementById('<?php echo $genid ?>shareCompanies').selectedIndex = 0;
			Ext.get('<?php echo $genid ?>shareWithEmails').focus();
		}
	}
	
	og.onBlurSetComp = function() {
		var email = Ext.get('<?php echo $genid ?>shareWithEmails').getValue();
		if (email.length > 0)
			if (email.indexOf(',') >= 0) email = email.substring(1, email.indexOf(','));
		var el = document.getElementById('<?php echo $genid ?>shareCompanies');
		comp = og.retrieveEmailCompany(email);
		if (comp != null) {
			for (i=0; i<el.options.length; i++) {
				if (el.options[i].value == comp) {
					el.selectedIndex = i;
					break;
				}
			}
		}
	}
	
	og.validateCompany = function() {
		var el = document.getElementById('<?php echo $genid ?>shareCompanies');
		if (el.value == 0) {
			og.err(lang('must choose company'));
			el.focus();
			return false;
		}
		return true;
	}
</script>


<div style="height:100%;background-color:white">
<form style="height:100%;" class="internalForm" action="<?php echo get_url('object', 'do_share'); ?>" method="post">
<div class="coInputHeader">
<table><tr><td>
<div id="ObjectShare_iconDiv" class="coViewIconImage ico-large-group"></div>
</td><td style="padding-left:10px">
<div class="coInputHeaderUpperRow">
<div class="coInputTitle"><?php echo lang('share this') . ' ' . lang($object->getObjectTypeName()) . ': ' . clean($object->getObjectName()); ?></div>
</div>
<b><span class="desc"><?php echo lang('share object desc') ?></span></b>

<?php echo submit_button(lang('share'), 's', array('style'=>'margin-top:0px;margin-left:10px', 'id' => $genid.'shareSubmit1', 'tabindex'=>'501'))?>
</td></tr></table>
</div>
<div class="coInputSeparator"></div>
<div class="coInputMainBlock adminMainBlock">

<input type="hidden" name="share_data[object_id]" value="<?php echo $object->getId() ?>">
<input type="hidden" name="share_data[object_manager]" value="<?php echo $object->getObjectManagerName() ?>">


<?php if (is_array($actuallySharing) && count($actuallySharing)) { ?>
	<fieldset><legend><?php echo lang('actually sharing with')?></legend>
	<table>
<?php	foreach($actuallySharing as $user) { ?>
			<tr><td><span style="width:250px;font-style:italic;">
			<?php echo $user['name']?> (<?php echo $user['email']?>) - <?php echo $user['company']?>
			</span></td></tr>
<?php	} ?>
	</table>
	</fieldset>
<?php } ?>

<fieldset><legend><?php echo lang('share with')?></legend>
<table id="<?php echo $genid ?>invitedPeople"></table>
<table><tr><td style="padding:5px;">
 	<div class="desc"><?php echo lang('email address') ?></div>
	<?php echo autocomplete_emailfield('share_data[emails]', '', $allEmails, '', 
    	array('class' => 'title', 'id' => $genid.'shareWithEmails', 'style' => 'width:370px;', 'tabindex'=>'360', 'onblur' => 'og.onBlurSetComp()'), false); ?>

</td><td style="padding:5px;">
 	<div class="desc"><?php echo lang('company') ?></div>
	<?php //echo autocomplete_emailfield('share_data[companies]', '', $allCompanies, '', 
    	echo select_company('share_data[companies]', null,
		array('class' => 'title', 'id' => $genid.'shareCompanies', 'style' => 'width:150px;', 'tabindex'=>'380', 'onblur' => 'og.onBlurSetComp();'), true); ?>

</td><td style="padding:5px;vertical-align:bottom;">
	<?php echo button(lang('add'), 'A', array('style'=>'margin-top:0px;margin-left:10px', 'id' => $genid.'addPeople', 'tabindex'=>'400',
				'onclick' => 'if(og.validateCompany()) og.onAddPeopleToShare();'))?>
	
</td></tr></table>

	<br>
    <div class="desc"><?php echo lang('allow people edit object') ?></div>
    <?php echo yes_no_widget('share_data[allow_edit]', $genid.'allow_edit', false, lang('yes'), lang('no'), 400) ?>
	
</fieldset>

<?php echo submit_button(lang('share'), 's', array('style'=>'margin-top:0px;margin-left:10px', 'id' => $genid.'shareSubmit2', 'tabindex'=>'500'))?>

</div>
</form>
</div>
<script>
	Ext.get('<?php echo $genid ?>shareWithEmails').focus();
</script>