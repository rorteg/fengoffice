<?php
	require_javascript("og/modules/addContactForm.js");
	$genid = gen_id();
	$object = $contact;
	$renderContext = has_context_to_render($contact->manager()->getObjectTypeId());
	
	$visible_cps = CustomProperties::countVisibleCustomPropertiesByObjectType($object->getObjectTypeId());
?>

<form onsubmit="return og.handleMemberChooserSubmit('<?php echo $genid; ?>', <?php echo $contact->manager()->getObjectTypeId() ?>);" id="<?php echo $genid ?>submit-edit-form" style='height:100%;background-color:white' class="internalForm" action="<?php echo $contact->isNew() ? $contact->getAddUrl() : $contact->getEditUrl() ?>" method="post">
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
			<?php echo text_field('contact[first_name]', array_var($contact_data, 'first_name'), 
				array('id' => $genid . 'profileFormFirstName', 'maxlength' => 50)) ?>
		</div>
	</td><td style="padding-left:20px">
		<div>
			<?php echo label_tag(lang('last name'), $genid . 'profileFormSurName') ?>
			<?php echo text_field('contact[surname]', array_var($contact_data, 'surname'), 
			array('id' => $genid . 'profileFormSurname',  'maxlength' => 50)) ?>
		</div>
	</td>
	<td style="padding-left:20px">
		<div>
			<?php echo label_tag(lang('email address'), $genid.'profileFormEmail') ?>
			<?php echo text_field('contact[email]', array_var($contact_data, 'email'), 
				array('id' => $genid.'profileFormEmail', 'maxlength' => 100, 'style' => 'width:260px;')) ?>
		</div>
	</td>
        <?php if($object->isNew()){?>
        <td style="padding-left:20px">
		<div>
                        <label><?php echo lang("specify username?")?></label>
                        <input class="checkbox" type="checkbox" name="contact[specify_username]" id="<?php echo $genid ?>specify-username"/>
                        <input id="<?php echo $genid ?>profileFormUsername" type="text" value="<?php echo array_var($contact_data, 'username')?>" name="contact[user][username]" maxlength="50" style="display: none;"/>
		</div>
	</td>
        <?php }?>
            </tr></table>
	
	<?php $categories = array(); Hook::fire('object_edit_categories', $object, $categories); ?>
	
	<div style="padding-top:5px">	
		<?php foreach ($categories as $category) : ?>
		<a href="#" class="option" <?php if ($category['visible']) echo 'style="font-weight: bold"'; ?> onclick="og.toggleAndBolden('<?php echo $genid . $category['name'] ?>', this)"><?php echo lang($category['name'])?></a>-
		<?php endforeach; ?>	
		<?php if ( $renderContext ) :?>
		<a href="#" class="option"  onclick="og.toggleAndBolden('<?php echo $genid ?>add_contact_select_context_div',this)"><?php echo lang('context') ?></a> -
		<?php endif;?>
		<a href="#" class="option" style="font-weight:bold" onclick="og.toggleAndBolden('<?php echo $genid ?>add_contact_work', this)"><?php echo lang('work') ?></a> - 
		<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_contact_email_and_im', this)"><?php echo lang('email and instant messaging') ?></a> - 
		<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_contact_home', this)"><?php echo lang('home') ?></a> - 
		<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_contact_other', this)"><?php echo lang('other') ?></a> - 
		<a href="#" class="option <?php echo $visible_cps>0 ? 'bold' : ''?>" onclick="og.toggleAndBolden('<?php echo $genid ?>add_custom_properties_div',this)"><?php echo lang('custom properties') ?></a> - 
		<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_subscribers_div',this)"><?php echo lang('object subscribers') ?></a>
		<?php if($object->isNew() || $object->canLinkObject(logged_user())) { ?> - 
			<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_linked_objects_div',this)"><?php echo lang('linked objects') ?></a>
		<?php } ?>

	</div>
</div>
<div class="coInputSeparator"></div>

	
<div class="coInputMainBlock">
	<input id="<?php echo $genid?>updated-on-hidden" type="hidden" name="updatedon" value="<?php echo !$contact->isNew() ?  $contact->getUpdatedOn()->getTimestamp() : '' ?>">
	<input id="<?php echo $genid?>merge-changes-hidden" type="hidden" name="merge-changes" value="" >
	<input id="<?php echo $genid?>genid" type="hidden" name="genid" value="<?php echo $genid ?>" >
		<?php if ($contact->isNew() || $isEdit){
			$this->includeTemplate(get_template_path("add_contact/access_data_edit","contact")); 
		}?>
	
	<?php foreach ($categories as $category) : ?>
	<div <?php if (!$category['visible']) echo 'style="display:none"' ?> id="<?php echo $genid . $category['name'] ?>">
	<fieldset>
		<legend><?php echo lang($category['name'])?><?php if ($category['required']) echo ' <span class="label_required">*</span>'; ?></legend>
		<?php echo $category['content'] ?>
	</fieldset>
	</div>
	<?php endforeach; ?>
	
	
	<?php if ($renderContext): ?>
	<div id="<?php echo $genid ?>add_contact_select_context_div" style="display:none">
	<fieldset>
		<legend><?php echo lang('context')?></legend>
			<?php 
				if ($contact->isNew()) {
					render_dimension_trees($contact->manager()->getObjectTypeId(), $genid, null, array('select_current_context' => true)); 
				} else {
					render_dimension_trees($contact->manager()->getObjectTypeId(), $genid, $contact->getMemberIds()); 
				} 
			?>
	</fieldset>
	</div>
	<?php endif ;?>
		
	<div style="display:block" id="<?php echo $genid ?>add_contact_work">
	<fieldset><legend><?php echo lang('work') ?></legend>
		<div style="margin-left:12px;margin-right:12px;">
			<div>
				<?php echo label_tag(lang('company'), $genid.'profileFormCompany') ?> 
				<div id="<?php echo $genid ?>existing_company"><?php echo select_company('contact[company_id]', array_var($contact_data, 'company_id'), array('id' => $genid.'profileFormCompany', "class" => "og-edit-contact-select-company", 'tabindex' => '5', 'onchange' => 'og.companySelectedIndexChanged(\''.$genid . '\')')); 
				?><a href="#" class="coViewAction ico-add" title="<?php echo lang('add a new company')?>" onclick="og.addNewCompany('<?php echo $genid ?>')"><?php echo lang('add company') . '...' ?></a></div>
				<div id="<?php echo $genid?>new_company" style="display:none; padding:6px; margin-top:6px;margin-bottom:6px; background-color:#EEE">
					<?php echo label_tag(lang('new company name'), $genid.'profileFormNewCompanyName') ?>
					<table width=100%><tr><td><?php echo text_field('company[first_name]', '', array('id' => $genid.'profileFormNewCompanyName', 'tabindex' => '10', 'onchange' => 'og.checkNewCompanyName("'.$genid .'")')) ?></td>
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
							</tr><tr height=10><td></td><td></td></tr><tr>
								<td class="td-pr"><?php echo label_tag(lang('email address'), $genid.'clientFormEmail') ?> </td>
								<td><?php echo text_field('company[email]', '', array('id' => $genid.'clientFormAssistantNumber', 'tabindex' => '60')) ?></td>
							</tr><tr height=10><td></td><td></td></tr><tr>
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
			</tr>
			</table>
			</td>
		</tr>
		<tr>
			<td><br />
			<div><?php echo label_tag(lang('birthday'), $genid.'profileFormBirthday')?> 
			<?php echo pick_date_widget2('contact[birthday]', array_var($contact_data, 'birthday'), $genid, 265) ?>
			</div>
			</td>
		</tr>
	</table>
	</fieldset>
	</div>
	
	<div id='<?php echo $genid ?>add_custom_properties_div' style="<?php echo ($visible_cps > 0 ? "" : "display:none") ?>">
		<fieldset>
			<legend><?php echo lang('custom properties') ?></legend>
			<?php echo render_object_custom_properties($object, false) ?>
			<?php //echo render_add_custom_properties($object); ?>
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
	
	
	<?php if($object->isNew() || $object->canLinkObject(logged_user())) : ?>
		<div style="display:none" id="<?php echo $genid ?>add_linked_objects_div">
		<fieldset>
			<legend><?php echo lang('linked objects') ?></legend>
			<?php echo render_object_link_form($object) ?>
		</fieldset>	
		</div>
	<?php endif; ?>
	
	
  	<?php echo submit_button($contact->isNew() ? lang('add contact') : lang('save changes'),'s',array('tabindex' => '20000', 'id' => $genid . 'submit2')) ?>

	<script>
	<?php if ($renderContext) :?>
		var memberChoosers = Ext.getCmp('<?php echo "$genid-member-chooser-panel-".$contact->manager()->getObjectTypeId()?>').items;
		
		if (memberChoosers) {
			memberChoosers.each(function(item, index, length) {
				item.on('all trees updated', function() {
					
					var dimensionMembers = {};
					memberChoosers.each(function(it, ix, l) {
						dim_id = this.dimensionId;
						dimensionMembers[dim_id] = [];
						var checked = it.getChecked("id");
						for (var j = 0 ; j < checked.length ; j++ ) {
							dimensionMembers[dim_id].push(checked[j]);
						}
					});
					
					// Subscribers
					var uids = App.modules.addMessageForm.getCheckedUsers('<?php echo $genid ?>');
					Ext.get('<?php echo $genid ?>add_subscribers_content').load({
						url: og.getUrl('object', 'render_add_subscribers', {
							context: Ext.util.JSON.encode(dimensionMembers),
							users: uids,
							genid: '<?php echo $genid ?>',
							otype: '<?php echo $contact->manager()->getObjectTypeId()?>'
						}),
						scripts: true
					});
					// Companies
					// og.reloadCompanies(dimensionMembers, '<?php echo $genid ?>');
					
				});
			});
		}
	<?php endif;?>
	Ext.get('<?php echo $genid ?>profileFormFirstName').focus();

	<?php //if ($contact->isNew()):?>
		$(function(){
			og.checkEmailAddress("#<?php echo $genid ?>profileFormEmail",'<?php echo $contact->getId();?>','<?php echo $genid ?>');
		});
	<?php //endif;?>
	
        $(function(){
                $("#<?php echo $genid ?>specify-username").click(function(){
                    
			if ($(this).is(":checked")) {
				$("#<?php echo $genid ?>profileFormUsername").show();
			} else {
                                $("#<?php echo $genid ?>profileFormUsername").hide();
			}
		});
	});
	</script>
</div>
</div>
</form>