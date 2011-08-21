<?php if(isset($company) && ($company instanceof Company)) { ?>
<div class="card">

<?php
	$show_help_option = user_config_option('show_context_help'); 
	if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_company_context_help', true, logged_user()->getId()))) {?>
		<div style="padding-bottom:10px;">
		<?php 
			render_context_help($this, 'chelp company card', 'company');
		?>
		</div>
	<?php } ?>

  <div class="cardIcon"><img src="<?php echo $company->getLogoUrl() ?>" alt="<?php echo clean($company->getName()) ?> logo" /></div>
  <div class="cardData">
    
    <div class="cardBlock">
      <div class="link-ico ico-email" style="padding-bottom:3px;"><span><?php echo lang('email address') ?>:</span> <a <?php echo logged_user()->hasMailAccounts() ? 'href="' . get_url('mail', 'add_mail', array('to' => clean($company->getEmail()))) . '"' : 'target="_self" href="mailto:' . clean($company->getEmail()) . '"' ?>><?php echo clean($company->getEmail()) ?></a></div>
      <div class="link-ico ico-phone" style="padding-bottom:3px;"><span><?php echo lang('phone number') ?>:</span> <?php echo $company->getPhoneNumber() ? clean($company->getPhoneNumber()) : lang('n/a') ?></div>
      <div class="link-ico ico-fax" style="padding-bottom:3px;"><span><?php echo lang('fax number') ?>:</span> <?php echo $company->getFaxNumber() ? clean($company->getFaxNumber()) : lang('n/a') ?></div>
<?php if($company->hasHomepage()) { ?>
      <div style="padding-bottom:3px;"><span><?php echo lang('homepage') ?>:</span> <a target="_blank" href="<?php echo $company->getHomepage() ?>"><?php echo clean($company->getHomepage()) ?></a></div>
<?php } else { ?>
      <div style="padding-bottom:3px;"><span><?php echo lang('homepage') ?>:</span> <?php echo lang('n/a') ?></div>
<?php } // if ?>
    </div>
    

    <div  class="link-ico ico-company"><h2><?php echo lang('address') ?></h2></div>
    
    <div class="cardBlock" style="margin-bottom: 0">
<?php if($company->hasAddress()) { ?>
      <?php echo clean($company->getAddress()) ?>
<?php if(trim($company->getAddress2())) { ?>
      <br /><?php echo clean($company->getAddress2()) ?>
<?php } // if ?>
      <br /><?php $city = clean($company->getCity());
      echo $city;
      if( trim($city)!='')
      	echo ',';?> <?php echo clean($company->getState()) ?> <?php echo clean($company->getZipcode()) ?>
<?php if(trim($company->getCountry())) { ?>
      <br /><?php echo clean($company->getCountryName()) ?>
<?php } // if ?>
<?php } else { ?>
      <?php echo lang('n/a') ?>
<?php } // if ?>
    </div>
  
  <?php if ($company->getNotes()) {?>
  <fieldset>
  <legend><?php echo lang('notes'); ?></legend>
  <div><?php echo escape_html_whitespace(convert_to_links(clean($company->getNotes()))) ?></div>
  </fieldset>
  <?php } ?>
  
  </div>
</div>
<?php } // if ?> 
    

<fieldset><legend class="toggle_collapsed" onclick="og.toggle('companyUsers',this)"><?php echo lang('users') ?></legend>
<div id='companyUsers' style="display:none">
<?php
  $this->assign('users', $company->getUsers());
  $this->includeTemplate(get_template_path('list_users', 'administration'));
?>
</div>
</fieldset>

<fieldset><legend class="toggle_collapsed" onclick="og.toggle('companyContacts',this)"><?php echo lang('contacts') ?></legend>
<div id='companyContacts' style="display:none">
<?php
  $this->assign('contacts', $company->getContacts());
  $this->includeTemplate(get_template_path('list_contacts', 'contact'));
?>
</div>
</fieldset>