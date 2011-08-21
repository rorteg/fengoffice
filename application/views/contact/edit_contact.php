<?php
	require_javascript("og/modules/addContactForm.js");
	$genid = gen_id();
	$object = $contact;
	$all = active_project()!= null ? false : true;
?>

<form id="<?php echo $genid ?>submit-edit-form" style='height:100%;background-color:white' class="internalForm" action="<?php echo $contact->isNew() ? $contact->getAddUrl() : $contact->getEditUrl() ?>" method="post">
<input id="<?php echo $genid ?>hfIsNewCompany" type="hidden" name="contact[isNewCompany]" value=""/>

<div class="contact">
<div class="coInputHeader">
	<div class="coInputHeaderUpperRow">
	<div class="coInputTitle"><table style="width:535px">
	<tr><td><?php echo $contact->isNew() ? lang('new contact') : lang('edit contact') ?>
	</td><td style="text-align:right"><?php echo submit_button($contact->isNew() ? lang('add contact') : lang('save changes'),'s',array('style'=>'margin-top:0px;margin-left:10px', 'tabindex' => 4, 'id' => $genid . 'submit1')) ?></td></tr></table>
	</div>
	
	</div>
	<input type="hidden" name="contact[new_contact_from_mail_div_id]" value="<?php echo array_var($contact_data, 'new_contact_from_mail_div_id', '') ?>"/>
	<input type="hidden" name="contact[hf_contacts]" value="<?php echo array_var($contact_data, 'hf_contacts') ?>"/>
	<table><tr><td>
		<div>
			<?php echo label_tag(lang('first name'), $genid . 'profileFormFirstName') ?>
			<?php echo text_field('contact[firstname]', array_var($contact_data, 'firstname'), 
				array('id' => $genid . 'profileFormFirstName', 'tabindex' => '1', 'maxlength' => 50)) ?>
		</div>
	</td><td style="padding-left:20px">
		<div>
			<?php echo label_tag(lang('last name'), $genid . 'profileFormLastName') ?>
			<?php echo text_field('contact[lastname]', array_var($contact_data, 'lastname'), 
			array('id' => $genid . 'profileFormLastName', 'tabindex' => '2', 'maxlength' => 50)) ?>
		</div>
	</td><td style="padding-left:20px">
		<div>
			<?php echo label_tag(lang('email address'), $genid.'profileFormEmail') ?>
			<?php echo text_field('contact[email]', array_var($contact_data, 'email'), 
				array('id' => $genid.'profileFormEmail', 'tabindex' => '3', 'maxlength' => 100)) ?>
		</div>
	</td></tr></table>
	
	<?php $categories = array(); Hook::fire('object_edit_categories', $object, $categories); ?>
	
	<div style="padding-top:5px">		
		<?php if ($all) { ?>
			<a href="#" class="option" style="font-weight:bold" onclick="og.toggleAndBolden('<?php echo $genid ?>add_contact_select_workspace_div',this)"><?php echo lang('workspace') ?></a> - 
		<?php } else {?>
			<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_contact_select_workspace_div',this)"><?php echo lang('workspace') ?></a> -
		<?php }?>
		<?php if (isset($isAddProject) && $isAddProject) { ?>
			<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_contact_role_div', this)"><?php echo lang('role') ?></a> - 
		<?php } ?>
		<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_contact_add_tags_div', this)"><?php echo lang('tags') ?></a> -
		<a href="#" class="option" style="font-weight:bold" onclick="og.toggleAndBolden('<?php echo $genid ?>add_contact_work', this)"><?php echo lang('work') ?></a> - 
		<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_contact_email_and_im', this)"><?php echo lang('email and instant messaging') ?></a> - 
		<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_contact_home', this)"><?php echo lang('home') ?></a> - 
		<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_contact_other', this)"><?php echo lang('other') ?></a> - 
		<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_contact_notes', this)"><?php echo lang('notes') ?></a> -
		<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_custom_properties_div',this)"><?php echo lang('custom properties') ?></a> -
		<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_subscribers_div',this)"><?php echo lang('object subscribers') ?></a>
		<?php if($object->isNew() || $object->canLinkObject(logged_user())) { ?> - 
			<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_linked_objects_div',this)"><?php echo lang('linked objects') ?></a>
		<?php } ?>
		<?php foreach ($categories as $category) { ?>
			- <a href="#" class="option" <?php if ($category['visible']) echo 'style="font-weight: bold"'; ?> onclick="og.toggleAndBolden('<?php echo $genid . $category['name'] ?>', this)"><?php echo lang($category['name'])?></a>
		<?php } ?>
	</div>
</div>
<div class="coInputSeparator"></div>
<div class="coInputMainBlock">
	<input id="<?php echo $genid?>updated-on-hidden" type="hidden" name="updatedon" value="<?php echo !$contact->isNew() ?  $contact->getUpdatedOn()->getTimestamp() : '' ?>">
	<input id="<?php echo $genid?>merge-changes-hidden" type="hidden" name="merge-changes" value="" >
	<input id="<?php echo $genid?>genid" type="hidden" name="genid" value="<?php echo $genid ?>" >
	
		<?php 
			$show_help_option = user_config_option('show_context_help'); 
			if ($show_help_option == 'always' || ($show_help_option == 'until_close')&& user_config_option('show_add_contact_context_help', true, logged_user()->getId())) {?>
			<div id="contactPanelContextHelp" class="contextHelpStyle">
				<?php render_context_help($this, 'chelp add contact','add_contact'); ?>
			</div>
		<?php }?>
		
	<?php if ($all) { ?>
			<div id="<?php echo $genid ?>add_contact_select_workspace_div" style="display:block"> 
	<?php } else {?>
			<div id="<?php echo $genid ?>add_contact_select_workspace_div" style="display:none">
	<?php }?>
	<fieldset><legend><?php echo lang('workspace')?></legend>
	<?php if ($object->isNew()) {
		echo select_workspaces('ws_ids', null, array(active_or_personal_project()), $genid.'ws_ids');
	} else {
	echo select_workspaces('ws_ids', null, $object->getWorkspaces(null, 'workspace'), $genid.'ws_ids');
	} ?>
	</fieldset>
	</div>
		
	<div style="display:block" id="<?php echo $genid ?>add_contact_work">
	<fieldset><legend><?php echo lang('work') ?></legend>
		<div style="margin-left:12px;margin-right:12px;">
			<div>
				<?php echo label_tag(lang('company'), $genid.'profileFormCompany') ?> 
				<div id="<?php echo $genid ?>existing_company"><?php echo select_company('contact[company_id]', array_var($contact_data, 'company_id'), array('id' => $genid.'profileFormCompany', "class" => "og-edit-contact-select-company", 'tabindex' => '5', 'onchange' => 'og.companySelectedIndexChanged(\''.$genid . '\')')); 
				?><a href="#" class="coViewAction ico-add" title="<?php echo lang('add a new company')?>" onclick="og.addNewCompany('<?php echo $genid ?>')"><?php echo lang('add company') . '...' ?></a></div>
				<div id="<?php echo $genid?>new_company" style="display:none; padding:6px; margin-top:6px;margin-bottom:6px; background-color:#EEE">
					<?php echo label_tag(lang('new company name'), $genid.'profileFormNewCompanyName') ?>
					<table width=100%><tr><td><?php echo text_field('company[name]', '', array('id' => $genid.'profileFormNewCompanyName', 'tabindex' => '10', 'onchange' => 'og.checkNewCompanyName("'.$genid .'")')) ?></td>
					<td style="text-align:right;vertical-align:bottom"><a href="#" title="<?php echo lang('cancel')?>" onclick="og.addNewCompany('<?php echo $genid ?>')"><?php echo lang('cancel') ?></a></td></tr></table>
					<div id="<?php echo $genid ?>duplicateCompanyName" style="display:none"></div>
					<div id="<?php echo $genid ?>companyInfo" style="display:block">
						<table style="margin-top:12px">
						<tr>
						<td style="padding-right:30px">
							<table style="width:100%">
							<tr>
								<td class="td-pr"><?php echo label_tag(lang('address'), $genid.'profileFormWAddress') ?></td>
								<td><?php echo text_field('company[address]', '', array('id' => $genid.'clientFormAddress', 'tabindex' => '15')) ?></td>
							</tr><tr>
								<td class="td-pr"><?php echo label_tag(lang('address2'), $genid.'clientFormAddress') ?></td>
								<td><?php echo text_field('company[address2]', '', array('id' => $genid.'clientFormAddress', 'tabindex' => '20')) ?></td>
							</tr><tr>
								<td class="td-pr"><?php echo label_tag(lang('city'), $genid.'clientFormCity') ?></td>
								<td><?php echo text_field('company[city]', '', array('id' => $genid.'clientFormCity', 'tabindex' => '25')) ?></td>
							</tr><tr>
								<td class="td-pr"><?php echo label_tag(lang('state'), $genid.'clientFormState') ?></td>
								<td><?php echo text_field('company[state]', '', array('id' => $genid.'clientFormState', 'tabindex' => '30')) ?></td>
							</tr><tr>
								<td class="td-pr"><?php echo label_tag(lang('zipcode'), $genid.'clientFormZipcode') ?></td>
								<td><?php echo text_field('company[zipcode]', '', array('id' => $genid.'clientFormZipcode', 'tabindex' => '35')) ?></td>
							</tr><tr>
								<td class="td-pr"><?php echo label_tag(lang('country'), $genid.'clientFormCountry') ?></td>
								<td><?php echo select_country_widget('company[country]', '', array('id' => $genid.'clientFormCountry', 'tabindex' => '40')) ?></td>
							</tr><tr>
								<td class="td-pr"><?php echo label_tag(lang('website'), $genid.'clientFormWebPage') ?></td>
								<td><?php echo text_field('company[w_web_page]', '', array('id' => $genid.'clientFormWebPage', 'tabindex' => '45')) ?></td>
							</tr>
							</table>
						</td><td>
							<table style="width:100%">
							<tr>
								<td class="td-pr"><?php echo label_tag(lang('phone'), $genid.'clientFormPhoneNumber') ?> </td>
								<td><?php echo text_field('company[phone_number]', '', array('id' => $genid.'clientFormPhoneNumber', 'tabindex' => '50')) ?></td>
							</tr><tr>
								<td class="td-pr"><?php echo label_tag(lang('fax'), $genid.'clientFormFaxNumber') ?> </td>
								<td><?php echo text_field('company[fax_number]', '', array('id' => $genid.'clientFormFaxNumber', 'tabindex' => '55')) ?></td>
							</tr><tr height=20><td></td><td></td></tr><tr>
								<td class="td-pr"><?php echo label_tag(lang('email address'), $genid.'clientFormEmail') ?> </td>
								<td><?php echo text_field('company[email]', '', array('id' => $genid.'clientFormAssistantNumber', 'tabindex' => '60')) ?></td>
							</tr><tr height=20><td></td><td></td></tr><tr>
								<td class="td-pr"><?php echo label_tag(lang('homepage'), $genid.'clientFormHomepage') ?></td>
								<td><?php echo text_field('company[homepage]', '', array('id' => $genid.'clientFormCallbackNumber', 'tabindex' => '65')) ?></td>
							</tr>
							</table>
							</td>
						</tr>
						</table>
					</div>
				</div>
			</div>
	
		<table style=" margin-top:12px">
			<tr>
				<td style="padding-right:30px">
				<table style="width:100%">
				<tr>
					<td class="td-pr"><?php echo label_tag(lang('department'), $genid.'profileFormDepartment') ?></td>
					<td><?php echo text_field('contact[department]', array_var($contact_data, 'department'), array('id' => $genid.'profileFormDepartment', 'tabindex' => '70', 'maxlength' => 50)) ?></td>
				</tr><tr height=20><td></td><td></td></tr>
				<tr>
					<td class="td-pr"><?php echo label_tag(lang('address'), $genid.'profileFormWAddress') ?></td>
					<td><?php echo text_field('contact[w_address]', array_var($contact_data, 'w_address'), array('id' => $genid.'profileFormWAddress', 'tabindex' => '75', 'maxlength' => 200)) ?></td>
				</tr><tr>
					<td class="td-pr"><?php echo label_tag(lang('city'), $genid.'profileFormWCity') ?></td>
					<td><?php echo text_field('contact[w_city]', array_var($contact_data, 'w_city'), array('id' => $genid.'profileFormWCity', 'tabindex' => '80', 'maxlength' => 50)) ?></td>
				</tr><tr>
					<td class="td-pr"><?php echo label_tag(lang('state'), $genid.'profileFormWState') ?></td>
					<td><?php echo text_field('contact[w_state]', array_var($contact_data, 'w_state'), array('id' => $genid.'profileFormWState', 'tabindex' => '85', 'maxlength' => 50)) ?></td>
				</tr><tr>
					<td class="td-pr"><?php echo label_tag(lang('zipcode'), $genid.'profileFormWZipcode') ?></td>
					<td><?php echo text_field('contact[w_zipcode]', array_var($contact_data, 'w_zipcode'), array('id' => $genid.'profileFormWZipcode', 'tabindex' => '90', 'maxlength' => 50)) ?></td>
				</tr><tr>
					<td class="td-pr"><?php echo label_tag(lang('country'), $genid.'profileFormWCountry') ?></td>
					<td><?php echo select_country_widget('contact[w_country]', array_var($contact_data, 'w_country'), array('id' => $genid.'profileFormWCountry', 'tabindex' => '95')) ?></td>
				</tr><tr>
					<td class="td-pr"><?php echo label_tag(lang('website'), $genid.'profileFormWWebPage') ?></td>
					<td><?php echo text_field('contact[w_web_page]', array_var($contact_data, 'w_web_page'), array('id' => $genid.'profileFormWWebPage', 'tabindex' => '100')) ?></td>
				</tr>
				</table>
				</td><td>
				<table style="width:100%">
				<tr>
					<td class="td-pr"><?php echo label_tag(lang('job title'), $genid.'profileFormJobTitle') ?></td>
					<td><?php echo text_field('contact[job_title]', array_var($contact_data, 'job_title'), array('id' => $genid.'profileFormJobTitle', 'maxlength' => '40', 'tabindex' => '105', 'maxlength' => 50)) ?></td>
				</tr><tr height=20><td></td><td></td></tr>
				<tr>
					<td class="td-pr"><?php echo label_tag(lang('wphone'), $genid.'profileFormWPhoneNumber') ?> </td>
					<td><?php echo text_field('contact[w_phone_number]', array_var($contact_data, 'w_phone_number'), array('id' => $genid.'profileFormWPhoneNumber', 'tabindex' => '110', 'maxlength' => 50)) ?></td>
				</tr><tr>
					<td class="td-pr"><?php echo label_tag(lang('wphone 2'), $genid.'profileFormWPhoneNumber2') ?> </td>
					<td><?php echo text_field('contact[w_phone_number2]', array_var($contact_data, 'w_phone_number2'), array('id' => $genid.'profileFormWPhoneNumber2', 'tabindex' => '115', 'maxlength' => 50)) ?></td>
				</tr><tr>
					<td class="td-pr"><?php echo label_tag(lang('wfax'), $genid.'profileFormWFaxNumber') ?> </td>
					<td><?php echo text_field('contact[w_fax_number]', array_var($contact_data, 'w_fax_number'), array('id' => $genid.'profileFormWFaxNumber', 'tabindex' => '120', 'maxlength' => 50)) ?></td>
				</tr><tr>
					<td class="td-pr"><?php echo label_tag(lang('wassistant'), $genid.'profileFormWAssistantNumber') ?> </td>
					<td><?php echo text_field('contact[w_assistant_number]', array_var($contact_data, 'w_assistant_number'), array('id' => $genid.'profileFormWAssistantNumber', 'tabindex' => '125', 'maxlength' => 50)) ?></td>
				</tr><tr>
					<td class="td-pr"><?php echo label_tag(lang('wcallback'), $genid.'profileFormWCallbackNumber') ?></td>
					<td><?php echo text_field('contact[w_callback_number]', array_var($contact_data, 'w_callback_number'), array('id' => $genid.'profileFormWCallbackNumber', 'tabindex' => '130', 'maxlength' => 50)) ?></td>
				</tr>
				</table>
				</td>
			</tr>
		</table>
		</div>
		</fieldset>
	</div>
	
	
	<div id="<?php echo $genid ?>add_contact_email_and_im" style="display:none">
	<fieldset>
		<legend><?php echo lang("email and instant messaging") ?></legend>
			<div>
				<?php echo label_tag(lang('email address 2'), $genid.'profileFormEmail2') ?>
				<?php echo text_field('contact[email2]', array_var($contact_data, 'email2'), array('id' => $genid.'profileFormEmail2', 'tabindex' => '135', 'maxlength' => 100)) ?>
			</div>
	
			<div>
				<?php echo label_tag(lang('email address 3'), $genid.'profileFormEmail3') ?>
				<?php echo text_field('contact[email3]', array_var($contact_data, 'email3'), array('id' => $genid.'profileFormEmail3', 'tabindex' => '140', 'maxlength' => 100)) ?>
			</div>
			
			<?php if(is_array($im_types) && count($im_types)) { ?>
			<fieldset><legend><?php echo lang('instant messengers') ?></legend>
			<table class="blank">
				<tr>
					<th colspan="2"><?php echo lang('im service') ?></th>
					<th><?php echo lang('value') ?></th>
					<th><?php echo lang('primary im service') ?></th>
				</tr>
				<?php foreach($im_types as $im_type) { ?>
				<tr>
					<td style="vertical-align: middle"><img
						src="<?php echo $im_type->getIconUrl() ?>"
						alt="<?php echo $im_type->getName() ?> icon" /></td>
					<td style="vertical-align: middle"><label class="checkbox"
						for="<?php echo 'profileFormIm' . $im_type->getId() ?>"><?php echo $im_type->getName() ?></label></td>
					<td style="vertical-align: middle"><?php echo text_field('contact[im_' . $im_type->getId() . ']', array_var($contact_data, 'im_' . $im_type->getId()), array('id' => $genid.'profileFormIm' . $im_type->getId(), 'tabindex' => '145')) ?></td>
					<td style="vertical-align: middle"><?php echo radio_field('contact[default_im]', array_var($contact_data, 'default_im') == $im_type->getId(), array('value' => $im_type->getId(), 'tabindex' => '150')) ?></td>
				</tr>
				<?php } // foreach ?>
			</table>
			<p class="desc"><?php echo lang('primary im description') ?></p>
			</fieldset>
			<?php } // if ?>
	</fieldset>
	</div>
	
	
	<div style="display:none" id="<?php echo $genid ?>add_contact_home">
	<fieldset><legend><?php echo lang('home') ?></legend>
	<table style="margin-left:20px;margin-right:20px">
		<tr>
			<td  style="padding-right:30px">
			<table><tr>
				<td class="td-pr"><?php echo label_tag(lang('address'), $genid.'profileFormHAddress') ?></td>
				<td><?php echo text_field('contact[h_address]', array_var($contact_data, 'h_address'), array('id' => $genid.'profileFormHAddress', 'tabindex' => '160', 'maxlength' => 200)) ?></td>
			</tr><tr>
				<td class="td-pr"><?php echo label_tag(lang('city'), $genid.'profileFormHCity') ?> </td>
				<td><?php echo text_field('contact[h_city]', array_var($contact_data, 'h_city'), array('id' => $genid.'profileFormHCity', 'tabindex' => '165', 'maxlength' => 50)) ?></td>
			</tr><tr>
				<td class="td-pr"><?php echo label_tag(lang('state'), $genid.'profileFormHState') ?></td>
				<td><?php echo text_field('contact[h_state]', array_var($contact_data, 'h_state'), array('id' => $genid.'profileFormHState', 'tabindex' => '170', 'maxlength' => 50)) ?></td>
			</tr><tr>
				<td class="td-pr"><?php echo label_tag(lang('zipcode'), $genid.'profileFormHZipcode') ?></td>
				<td><?php echo text_field('contact[h_zipcode]', array_var($contact_data, 'h_zipcode'), array('id' => $genid.'profileFormHZipcode', 'tabindex' => '175', 'maxlength' => 50)) ?></td>
			</tr><tr>
				<td class="td-pr"><?php echo label_tag(lang('country'), $genid.'profileFormHCountry') ?></td>
				<td><?php echo select_country_widget('contact[h_country]', array_var($contact_data, 'h_country'), array('id' => $genid.'profileFormHCountry', 'tabindex' => '180')) ?></td>
			</tr><tr>
				<td class="td-pr"><?php echo label_tag(lang('website'), $genid.'profileFormHWebPage') ?></td>
				<td><?php echo text_field('contact[h_web_page]', array_var($contact_data, 'h_web_page'), array('id' => $genid.'profileFormHWebPage', 'tabindex' => '185')) ?></td>
			</tr>
			</table>
			</td>
			<td>
			<table><tr>
				<td class="td-pr"><?php echo label_tag(lang('hphone'), $genid.'profileFormHPhoneNumber') ?></td>
				<td><?php echo text_field('contact[h_phone_number]', array_var($contact_data, 'h_phone_number'), array('id' => $genid.'profileFormHPhoneNumber', 'tabindex' => '190', 'maxlength' => 50)) ?></td>
			</tr><tr>
				<td class="td-pr"><?php echo label_tag(lang('hphone 2'), $genid.'profileFormHPhoneNumber2') ?></td>
				<td><?php echo text_field('contact[h_phone_number2]', array_var($contact_data, 'h_phone_number2'), array('id' => $genid.'profileFormHPhoneNumber2', 'tabindex' => '195', 'maxlength' => 50)) ?></td>
			</tr><tr>
				<td class="td-pr"><?php echo label_tag(lang('hfax'), $genid.'profileFormHFaxNumber') ?></td>
				<td><?php echo text_field('contact[h_fax_number]', array_var($contact_data, 'h_fax_number'), array('id' => $genid.'profileFormHFaxNumber', 'tabindex' => '200', 'maxlength' => 50)) ?></td>
			</tr><tr>
				<td class="td-pr"><?php echo label_tag(lang('hmobile'), $genid.'profileFormHMobileNumber') ?></td>
				<td><?php echo text_field('contact[h_mobile_number]', array_var($contact_data, 'h_mobile_number'), array('id' => $genid.'profileFormHMobileNumber', 'tabindex' => '205', 'maxlength' => 50)) ?></td>
			</tr><tr>
				<td class="td-pr"><?php echo label_tag(lang('hpager'), $genid.'profileFormHPagerNumber') ?></td>
				<td><?php echo text_field('contact[h_pager_number]', array_var($contact_data, 'h_pager_number'), array('id' => $genid.'profileFormHPagerNumber', 'tabindex' => '210', 'maxlength' => 50)) ?></td>
			</tr>
			</table>
			</td>
		</tr>
	</table>
	</fieldset>
	</div>
	
	<div style="display:none" id="<?php echo $genid ?>add_contact_other">
	<fieldset><legend><?php echo lang('other') ?></legend>
	<table style="margin-left:20px;margin-right:20px">
		<tr>
			<td style="padding-right:30px">
			<table><tr>
				<td><?php echo label_tag(lang('middle name'), $genid.'profileFormMiddleName') ?></td>
				<td><?php echo text_field('contact[middlename]', array_var($contact_data, 'middlename'), array('id' => $genid.'profileFormMiddleName', 'tabindex' => '215', 'maxlength' => 50)) ?></td>
			</tr><tr>
				<td class="td-pr"><?php echo label_tag(lang('address'), $genid.'profileFormOAddress') ?></td>
				<td><?php echo text_field('contact[o_address]', array_var($contact_data, 'o_address'), array('id' => $genid.'profileFormOAddress', 'tabindex' => '220', 'maxlength' => 200)) ?></td>
			</tr><tr>
				<td class="td-pr"><?php echo label_tag(lang('city'), $genid.'profileFormOCity') ?> </td>
				<td><?php echo text_field('contact[o_city]', array_var($contact_data, 'o_city'), array('id' => $genid.'profileFormOCity', 'tabindex' => '225', 'maxlength' => 50)) ?></td>
			</tr><tr>
				<td class="td-pr"><?php echo label_tag(lang('state'), $genid.'profileFormOState') ?></td>
				<td><?php echo text_field('contact[o_state]', array_var($contact_data, 'o_state'), array('id' => $genid.'profileFormOState', 'tabindex' => '230', 'maxlength' => 50)) ?></td>
			</tr><tr>
				<td class="td-pr"><?php echo label_tag(lang('zipcode'), $genid.'profileFormOZipcode') ?></td>
				<td><?php echo text_field('contact[o_zipcode]', array_var($contact_data, 'o_zipcode'), array('id' => $genid.'profileFormOZipcode', 'tabindex' => '235', 'maxlength' => 50)) ?></td>
			</tr><tr>
				<td class="td-pr"><?php echo label_tag(lang('country'), $genid.'profileFormOCountry') ?></td>
				<td><?php echo select_country_widget('contact[o_country]', array_var($contact_data, 'o_country'), array('id' => $genid.'profileFormOCountry', 'tabindex' => '240', 'maxlength' => 50)) ?></td>
			</tr><tr>
				<td class="td-pr"><?php echo label_tag(lang('website'), $genid.'profileFormOWebPage') ?></td>
				<td><?php echo text_field('contact[o_web_page]', array_var($contact_data, 'o_web_page'), array('id' => $genid.'profileFormOWebPage', 'tabindex' => '245')) ?></td>
			</tr>
			</table>
			</td>
			<td>
			<table><tr>
				<td><?php echo label_tag(lang('ophone'), $genid.'profileFormOPhoneNumber') ?></td>
				<td><?php echo text_field('contact[o_phone_number]', array_var($contact_data, 'o_phone_number'), array('id' => $genid.'profileFormOPhoneNumber', 'tabindex' => '250', 'maxlength' => 50)) ?></td>
			</tr><tr>
				<td class="td-pr"><?php echo label_tag(lang('ophone 2'), $genid.'profileFormOPhoneNumber2') ?></td>
				<td><?php echo text_field('contact[o_phone_number2]', array_var($contact_data, 'o_phone_number2'), array('id' => $genid.'profileFormOPhoneNumber2', 'tabindex' => '255', 'maxlength' => 50)) ?></td>
			</tr><tr>
				<td class="td-pr"><?php echo label_tag(lang('ofax'), $genid.'profileFormOFaxNumber') ?></td>
				<td><?php echo text_field('contact[o_fax_number]', array_var($contact_data, 'o_fax_number'), array('id' => $genid.'profileFormOFaxNumber', 'tabindex' => '260', 'maxlength' => 50)) ?></td>
			</tr>
			</table>
			</td>
		</tr>
		<tr>
			<td><br />
			<div><?php echo label_tag(lang('birthday'), $genid.'profileFormBirthday')?> 
			<?php //echo pick_date_widget('contact[o_birthday]', array_var($contact_data, 'o_birthday'), 1902, date("Y")) ?>
			<?php echo pick_date_widget2('contact[o_birthday_value]', array_var($contact_data, 'o_birthday'), $genid, 265) ?>
			</div>
			<div><?php echo label_tag(lang('timezone'), $genid.'profileFormTimezone')?> <?php echo select_timezone_widget('contact[timezone]', array_var($contact_data, 'timezone'), array('id' => $genid.'profileFormTimezone', 'class' => 'long', 'tabindex' => '270')) ?>
			</div>
			</td>
		</tr>
	</table>
	</fieldset>
	</div>
	
	<div style="display:none" id="<?php echo $genid ?>add_contact_notes">
	<fieldset><legend><?php echo lang('notes') ?></legend>
	    <div>
	      <?php echo label_tag(lang('notes'), $genid.'profileFormNotes') ?>
	      <?php echo textarea_field('contact[notes]', array_var($contact_data, 'notes'), array('id' => $genid.'profileFormNotes', 'tabindex' => '275')) ?>
	    </div>
	</fieldset>
	</div>
	
	<div id='<?php echo $genid ?>add_custom_properties_div' style="display:none">
		<fieldset>
			<legend><?php echo lang('custom properties') ?></legend>
			<?php echo render_object_custom_properties($object, 'Contacts', false) ?><br/><br/>
			<?php echo render_add_custom_properties($object); ?>
		</fieldset>
	</div>
	
	<div id="<?php echo $genid ?>add_subscribers_div" style="display:none">
		<fieldset>
		<legend><?php echo lang('object subscribers') ?></legend>
		<div id="<?php echo $genid ?>add_subscribers_content">
			<?php echo render_add_subscribers($object, $genid); ?>
		</div>
		</fieldset>
	</div>
	<script>
	var wsch = Ext.getCmp('<?php echo $genid ?>ws_ids');
	wsch.on("wschecked", function(arguments) {
		if (!this.getValue().trim()) return;
		var uids = App.modules.addMessageForm.getCheckedUsers('<?php echo $genid ?>');
		Ext.get('<?php echo $genid ?>add_subscribers_content').load({
			url: og.getUrl('object', 'render_add_subscribers', {
				workspaces: this.getValue(),
				users: uids,
				genid: '<?php echo $genid ?>',
				object_type: '<?php echo get_class($object->manager()) ?>'
			}),
			scripts: true
		});
	}, wsch);
	</script>
	
	<div style="display:none" id="<?php echo $genid ?>add_linked_objects_div">
	<fieldset>
		<legend><?php echo lang('linked objects') ?></legend>
		<?php echo render_object_link_form($object) ?>
	</fieldset>	
	</div>
	
	<div id="<?php echo $genid ?>add_contact_add_tags_div" style="display:none">
	<fieldset><legend><?php echo lang('tags')?></legend>
		<?php echo autocomplete_tags_field("contact[tags]", array_var($contact_data, 'tags'), null, 290); ?>
	</fieldset>
	</div>
	
	<?php foreach ($categories as $category) { ?>
	<div <?php if (!$category['visible']) echo 'style="display:none"' ?> id="<?php echo $genid . $category['name'] ?>">
	<fieldset>
		<legend><?php echo lang($category['name'])?><?php if ($category['required']) echo ' <span class="label_required">*</span>'; ?></legend>
		<?php echo $category['content'] ?>
	</fieldset>
	</div>
	<?php } ?>
	
	<?php if (isset($isAddProject) && $isAddProject)
	{
		?>
		<div id="<?php echo $genid ?>add_contact_role_div" style="display:none">
		<fieldset>
			<legend> <?php echo label_tag(lang('role in project', clean(active_project()->getName())), $genid.'profileFormRole')?></legend>
			<?php echo text_field('contact[role]', array_var($contact_data, 'role'), array('class' => 'long', 'id' => $genid.'profileFormRole', 'tabindex' => '295') ) ?>
		</fieldset>
		</div>
	<?php }?>

	<div>
		<?php echo render_object_custom_properties($object, 'Contacts', true) ?>
	</div><br/>
	
  	<?php echo submit_button($contact->isNew() ? lang('add contact') : lang('save changes'),'s',array('tabindex' => '20000', 'id' => $genid . 'submit2')) ?>

<script>
	Ext.get('<?php echo $genid ?>profileFormFirstName').focus();
</script>
</div>
</div>
</form>