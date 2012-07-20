<?php
	$contact = $object;
	$hasEmailAddrs = false;
	
	$main_email = $contact->getEmailAddress('personal');
	$personal_emails = $contact->getContactEmails('personal');
?>
    <table width=100%><col width=250px/><col/>
    <?php if ($main_email || count($personal_emails)> 0 || is_array($im_values = $contact->getImValues()) && count($contact) || $contact->getBirthday()) {?>
    	<tr><td>
	 	 <?php if ($main_email || count($personal_emails)> 0){ 
	  			$hasEmailAddrs = true; ?>
	  			<span style="font-weight:bold"><?php echo lang('email addresses') ?>:</span>
      			<?php if ($main_email) { ?>
      					<div style="padding-left:10px"><a <?php echo logged_user()->hasMailAccounts() ? 'href="' . get_url('mail', 'add_mail', array('to' => clean($main_email))) . '"' : 'target="_self" href="mailto:' . clean($main_email) . '"' ?>><?php echo clean($main_email);?></a></div>
      			<?php } ?>
      			<?php if (count($personal_emails)> 0) { 
      				    foreach ($personal_emails as $pe){
      						?>
      						<div style="padding-left:10px"><a <?php echo logged_user()->hasMailAccounts() ? 'href="' . get_url('mail', 'add_mail', array('to' => clean($pe->getEmailAddress()))) . '"' : 'target="_self" href="mailto:' . clean($pe->getEmailAddress()) . '"';?>"><?php echo clean($pe->getEmailAddress());?></a></div>
      			<?php	} 
            		  }
	   } 	   
       if ($contact->getBirthday()) { ?><?php echo $hasEmailAddrs? '<br/>':'' ?>
      <div><span style="font-weight:bold"><?php echo lang('birthday') ?>:</span> 
      <?php if ($contact->getBirthday() instanceof DateTimeValue) {
      		$bday = new DateTimeValue($contact->getBirthday()->getTimestamp() - logged_user()->getTimezone() * 3600);
      		echo clean(format_datetime($bday, user_config_option('date_format')));
      		} ?>
      </div>
      <?php } ?>
      </td><td>
      <?php if(is_array($im_values = $contact->getImValues()) && count($im_values)) { ?>
	  <span style="font-weight:bold"><?php echo lang('instant messaging') ?>:</span>
      <table class="imAddresses">
<?php foreach($im_values as $im_value) { ?>
<?php if($im_type = $im_value->getImType()) { ?>
        <tr>
          <td><img src="<?php echo $im_type->getIconUrl() ?>" alt="<?php echo $im_type->getName() ?>" /></td>
          <td><?php echo clean($im_value->getValue()) ?> <?php if($im_value->getIsMain()) { ?><span class="desc">(<?php echo lang('primary im service') ?>)</span><?php } ?></td>
        </tr>
<?php } // if ?>
<?php } // foreach ?>
      </table>
<?php } // if ?>
    </td></tr>
<?php } // if ?>
    
    <?php 
    $w_address = $contact->getAddress('work');
    $w_web_page= $contact->getWebpageUrl('work');	 
	$w_phone_number= $contact->getPhoneNumber('work', 1); 
	$w_phone_number2= $contact->getPhoneNumber('work'); 
	$w_fax_number= $contact->getPhoneNumber('fax', true); 
	$w_assistant_number= $contact->getPhoneNumber('assistant'); 
	$w_callback_number= $contact->getPhoneNumber('callback');

    
    //if($contact->getWAddress() || $contact->getWCity() || $contact->getWState() || $contact->getWWebPage() || $contact->getWZipcode() || $contact->getWCountry() || $contact->getWPhoneNumber() || $contact->getWPhoneNumber2() || $contact->getWFaxNumber() || $contact->getWAssistantNumber() || $contact->getWCallbackNumber()) {
    if($w_address||$w_web_page||$w_phone_number||$w_phone_number2||$w_fax_number||$w_assistant_number||$w_callback_number){?>
    <tr><td colspan=2><div style="font-weight:bold; font-size:120%; color:#888; border-bottom:1px solid #DDD;width:100%; padding-top:14px">
    <?php echo lang('work'); ?>
    </div></td></tr><tr><td>
      <?php  if ($contact->getFullAddress($w_address)) { ?>
      	<span style="font-weight:bold"><?php echo lang('address') ?>:</span> <div style="padding-left:10px"><p><?php echo nl2br(clean($contact->getFullAddress($w_address)));?></p></div><br/>
      <?php } if ($w_web_page != '') { ?>
      	<div><span style="font-weight:bold"><?php echo lang('website') ?>:</span> <div style="padding-left:10px"><a href="<?php echo cleanUrl($w_web_page) ?>" target="_blank" title="<?php echo lang('open this link in a new window') ?>"><?php echo clean($w_web_page) ?></a></div></div>
      <?php } ?>
      </td><td>
      <?php  if($w_phone_number||$w_phone_number2||$w_fax_number||$w_assistant_number||$w_callback_number) { ?>
      
    	  <span style="font-weight:bold"><?php echo lang('wphone title') ?>:</span>
	      <?php if ($w_phone_number) { ?>
	      <div><span><?php echo lang('wphone') ?>:</span> <?php echo clean($w_phone_number);?></div><?php } ?>
	      <?php if ($w_phone_number2) { ?>
	      <div><span><?php echo lang('wphone 2') ?>:</span> <?php echo clean($w_phone_number2);?></div><?php } ?>
	      <?php if ($w_fax_number) { ?>
	      <div><span><?php echo lang('wfax') ?>:</span> <?php echo clean($w_fax_number);?></div><?php } ?>
	      <?php if ($w_assistant_number) { ?>
	      <div><span><?php echo lang('wassistant') ?>:</span> <?php echo clean($w_assistant_number);?></div><?php } ?>
	      <?php if ($w_callback_number) { ?>
	      <div><span><?php echo lang('wcallback') ?>:</span> <?php echo clean($w_callback_number);?></div><?php } ?>
      <?php } ?>
    </td></tr> 
<?php } // if ?>


    <?php $company = $contact->getCompany();
    	 if($company instanceof Contact){
    	?>
    <tr><td colspan=2><div style="background-position:center left;font-weight:bold; font-size:120%; color:#AAA; border-bottom:1px solid #DDD;width:100%; padding-top:14px">
    	<?php echo lang('company') ?>
    </div></td></tr><tr><td colspan=2>
    	<?php
    	tpl_assign('company',$company);
    	$this->includeTemplate(get_template_path('company_card', 'contact'));?>
    </td></tr> 
    <?php } ?>
    
    <?php 
    $h_address = $contact->getAddress('home');
    $h_web_page= $contact->getWebpageUrl('personal');
	$h_phone_number= $contact->getPhoneNumber('home', true); 
	$h_phone_number2= $contact->getPhoneNumber('home'); 
	$h_fax_number= $contact->getPhoneNumber('fax'); 
	$h_mobile_number= $contact->getPhoneNumber('mobile'); 
	$h_pager_number= $contact->getPhoneNumber('pager'); 

    
    //if($contact->getHAddress() || $contact->getHCity() || $contact->getHState() || $contact->getHWebPage() || $contact->getHZipcode() || $contact->getHCountry() || $contact->getHPhoneNumber() || $contact->getHPhoneNumber2() || $contact->getHFaxNumber() || $contact->getHMobileNumber() || $contact->getHPagerNumber()) {
    if($h_address || $h_web_page || $h_phone_number || $h_phone_number2 || $h_fax_number || $h_mobile_number || $h_pager_number) {?>
	<tr><td colspan=2><div style="font-weight:bold; font-size:120%; color:#888; border-bottom:1px solid #DDD;width:100%; padding-top:14px">
    	<?php echo lang('home'); ?>
    </div></td></tr><tr><td>
      <?php if ($contact->getFullAddress($h_address)) { ?>
      	<span style="font-weight:bold"><?php echo lang('address') ?>:</span> <div style="padding-left:10px"><p><?php echo nl2br(clean($contact->getFullAddress($h_address)));?></p></div><br/>
      <?php } if ($h_web_page != '') { ?>
      	<div><span style="font-weight:bold"><?php echo lang('website') ?>:</span> <div style="padding-left:10px"><a href="<?php echo cleanUrl($h_web_page) ?>" target="_blank" title="<?php echo lang('open this link in a new window') ?>"><?php echo clean($h_web_page) ?></a></div></div>
      <?php } ?>
      </td><td>
      
      <?php if($h_phone_number || $h_phone_number2 || $h_fax_number || $h_mobile_number) {?>
    	  <span style="font-weight:bold"><?php echo lang('hphone title') ?>:</span>
	      <?php if ($h_phone_number) { ?>
	      <div><span><?php echo lang('hphone') ?>:</span> <?php echo clean($h_phone_number);?></div><?php } ?>
	      <?php if ($h_phone_number2) { ?>
	      <div><span><?php echo lang('hphone 2') ?>:</span> <?php echo clean($h_phone_number2);?></div><?php } ?>
	      <?php if ($h_fax_number) { ?>
	      <div><span><?php echo lang('hfax') ?>:</span> <?php echo clean($h_fax_number);?></div><?php } ?>
	      <?php if ($h_mobile_number) { ?>
	      <div><span><?php echo lang('hmobile') ?>:</span> <?php echo clean($h_mobile_number);?></div><?php } ?>
	      <?php if ($h_pager_number) { ?>
	      <div><span><?php echo lang('hpager') ?>:</span> <?php echo clean($h_pager_number);?></div><?php } ?>
	      
      <?php } ?>
    </td></tr> 
<?php } // if ?>
    
    <?php 
    $o_address = $contact->getAddress('other');
    $o_web_page= $contact->getWebpageUrl('other'); 	 
	$o_phone_number= $contact->getPhoneNumber('other', true); 
	$o_phone_number2= $contact->getPhoneNumber('other'); 
    
    //if($contact->getOAddress() || $contact->getOCity() || $contact->getOState() || $contact->getOZipcode() || $contact->getOCountry() || $contact->getOPhoneNumber() || $contact->getOPhoneNumber2() || $contact->getOFaxNumber()) {
    if($o_address || $o_web_page || $o_phone_number || $o_phone_number2) {?>
	<tr><td colspan=2><div style="font-weight:bold; font-size:120%; color:#888; border-bottom:1px solid #DDD;width:100%; padding-top:14px">
    	<?php echo lang('other'); ?>
    </div></td></tr><tr><td>
      <?php if ($contact->getFullAddress($o_address)) { ?>
      	<span style="font-weight:bold"><?php echo lang('address') ?>:</span> <div style="padding-left:10px"><p><?php echo nl2br(clean($contact->getFullAddress($o_address)));?></p></div><br/>
      <?php } if ($o_web_page != '') { ?>
      <div><span style="font-weight:bold"><?php echo lang('website') ?>:</span> <div style="padding-left:10px"><a href="<?php echo cleanUrl($o_web_page) ?>" target="_blank" title="<?php echo lang('open this link in a new window') ?>"><?php echo clean($o_web_page) ?></a></div></div>
      <?php } ?>
      </td><td>
      
      <?php if($o_phone_number || $o_phone_number2 ) {?>
		<span style="font-weight:bold"><?php echo lang('ophone title') ?>:</span>
    	<?php if ($o_phone_number) { ?>
      	<div><span><?php echo lang('ophone') ?>:</span> <?php echo clean($o_phone_number);?></div><?php } ?>
      	<?php if ($o_phone_number2) { ?>
      	<div><span><?php echo lang('ophone 2') ?>:</span> <?php echo clean($o_phone_number2);?></div><?php } ?>
      	
      <?php } ?>
    </td></tr> 
<?php } // if ?>
    

    
    
    <?php if($contact->isUser()) {?>
    <tr><td colspan=2><div style="font-weight:bold; font-size:120%; color:#888; border-bottom:1px solid #DDD;width:100%; padding-top:14px">
    	<?php echo lang('assigned user'); ?>
    </div></td></tr><tr><td colspan=2>
    	<?php tpl_assign('user',$contact);
    	$this->includeTemplate(get_template_path('user_card', 'contact'));?>
    </td></tr> 
    <?php } ?>
    </table>