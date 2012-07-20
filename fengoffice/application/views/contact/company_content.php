<?php if(isset($company) && ($company instanceof Contact)) { ?>
<div class="card">

  <div class="cardIcon"><img src="<?php  echo $company->getPictureUrl() ?>" alt="<?php echo clean($company->getObjectName()) ?> logo" /></div>

  <div class="cardData">
    
    <div class="cardBlock">
      <div class="link-ico ico-email" style="padding-bottom:3px;"><span><?php echo lang('email address') ?>:</span> <a <?php echo logged_user()->hasMailAccounts() ? 'href="' . get_url('mail', 'add_mail', array('to' => clean($company->getEmailAddress()))) . '"' :  'target="_self" href="mailto:' . clean($company->getEmailAddress()) . '"' ?>><?php echo clean($company->getEmailAddress()) ?></a></div>
      <div class="link-ico ico-phone" style="padding-bottom:3px;"><span><?php echo lang('phone number') ?>:</span> <?php echo $company->getPhone('work',true) ? clean($company->getPhone('work',true)->getNumber()) : lang('n/a') ?></div>
      <div class="link-ico ico-fax" style="padding-bottom:3px;"><span><?php echo lang('fax number') ?>:</span> <?php echo $company->getPhone('fax',true) ? clean($company->getPhone('fax',true)->getNumber()) : lang('n/a') ?></div>
<?php if($company->getWebpageURL('work') != "") { ?>
      <div style="padding-bottom:3px;"><span><?php echo lang('homepage') ?>:</span> <a target="_blank" href="<?php echo $company->getWebpageUrl('work') ?>"><?php echo clean($company->getWebpageUrl('work')) ?></a></div>
<?php } else { ?>
      <div style="padding-bottom:3px;"><span><?php echo lang('homepage') ?>:</span> <?php echo lang('n/a') ?></div>
<?php } // if ?>
    </div>
    

    <div  class="link-ico ico-company"><h2><?php echo lang('address') ?></h2></div>
    
    <div class="cardBlock" style="margin-bottom: 0">
<?php    
    $address = $company->getAddress('work',true);
    if($address) { 
       echo clean($address->getStreet()) ;?>
      <br /><?php $city = clean($address->getCity());
      echo $city;
      if( trim($city)!='')
      	echo ',';?> <?php echo clean($address->getState()) ?> <?php echo clean($address->getZipCode()) ?>
<?php if(trim($address->getCountry())) { ?>
      <br /><?php echo clean($address->getCountryName());  
	  } // if  
	  else {  
	  	echo lang('n/a'); 
	  }
	   // if ?>
    </div>
<?php } // if ?> 
  	</div>
</div>
<?php } ?> 
    

<fieldset><legend class="toggle_collapsed" onclick="og.toggle('companyUsers',this)"><?php echo lang('users') ?></legend>
<div id='companyUsers' style="display:none">
<?php
  $this->assign('users', $company->getUsersByCompany());
  $this->includeTemplate(get_template_path('list_users', 'administration'));
?>
</div>
</fieldset>

<?php if (!$company->isOwnerCompany())/*FIXME FENG2*/{ ?>
<fieldset><legend class="toggle_collapsed" onclick="og.toggle('companyContacts',this)"><?php echo lang('persons') ?></legend>
<div id='companyContacts' style="display:none">
<?php

  $this->assign('contacts', $company->getContactsByCompany());
  $this->includeTemplate(get_template_path('list_contacts', 'contact')); ?>
</div>
</fieldset>
<?php } ?>