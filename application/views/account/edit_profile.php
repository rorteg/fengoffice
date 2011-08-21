<?php
	$object = $user;
    set_page_title(lang('update profile'));
 	$now = DateTimeValueLib::now();
	$genid = gen_id();
?>
<form style="height:100%;background-color:white" class="internalForm" action="<?php echo $user->getEditProfileUrl($redirect_to) ?>" method="post">


<div class="adminEditProfile">
  <div class="adminHeader">
  	<div class="adminHeaderUpperRow">
  		<div class="adminTitle"><table style="width:535px"><tr><td>
  			<?php echo lang('update profile') ?>
  		</td><td style="text-align:right">
  			<?php echo submit_button(lang('save changes'), 's', array('style'=>'margin-top:0px;margin-left:10px', 'tabindex' => '1100', 'onclick'=>'og.getTimezoneFromBrowser(new Date('.$now->getYear().','.($now->getMonth() - 1).','.$now->getDay().','.$now->getHour().','.$now->getMinute().','.$now->getSecond().'))')) ?>
  		</td></tr></table></div>
  	</div>
  
    <div>
      <?php echo label_tag(lang('display name'), 'profileFormDisplayName') ?>
      <?php echo text_field('user[display_name]', array_var($user_data, 'display_name'), 
    	  array('id' => 'profileFormDisplayName', 'tabindex' => '1000', 'class' => 'title')) ?>
    </div>
  
  	<?php $categories = array(); Hook::fire('object_edit_categories', $object, $categories); ?>
  	<?php $cps = CustomProperties::countHiddenCustomPropertiesByObjectType('Users'); ?>
  
  	<div style="padding-top:5px">
		<?php if(logged_user()->isAdministrator()) { ?>
			<a href="#" class="option" tabindex=1010 onclick="og.toggleAndBolden('<?php echo $genid ?>update_profile_administrator_options',this)"><?php echo lang('administrator options') ?></a> - 
		<?php } // if ?>
		<?php if (logged_user()->isAdministrator() && isset($billing_categories) && count($billing_categories) > 0) {?>
			<a href="#" class="option" tabindex=1010 onclick="og.toggleAndBolden('<?php echo $genid ?>update_profile_billing',this)"><?php echo lang('billing') ?></a> - 
		<?php } // if ?>
		<a href="#" class="option" tabindex=1020 onclick="og.toggleAndBolden('<?php echo $genid ?>update_profile_timezone',this)"><?php echo lang('timezone') ?></a>
		<?php foreach ($categories as $category) { ?>
			- <a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid . $category['name'] ?>', this)"><?php echo lang($category['name'])?></a>
		<?php } ?>
		<?php if ($cps > 0) { ?>
			- <a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_custom_properties_div',this)"><?php echo lang('custom properties') ?></a>
		<?php } ?> 
	</div>
  
  </div>
  <div class="adminSeparator"></div>
  <div class="adminMainBlock">

<?php if(logged_user()->isAdministrator()) { ?>

  <div id="<?php echo $genid ?>update_profile_administrator_options" style="display:none">
  <fieldset>
    <legend><?php echo lang('administrator update profile notice') ?></legend>
    <div class="content">
      <div class="formBlock">
        <?php echo label_tag(lang('username'), $genid . 'profileFormUsername', true) ?>
        <?php echo text_field('user[username]', array_var($user_data, 'username'), 
        array('id' => $genid . 'profileFormUsername', 'tabindex' => '2000')) ?>
      </div>
      
      <div class="formBlock">
        <?php echo label_tag(lang('company'), $genid . 'userFormCompany', true) ?>
        <?php $attributes = array(
        	'id' => $genid . 'userFormCompany',
        	'tabindex' => '2100',
        	'onchange' => "var d = document.getElementById('" . $genid . "options'); var n = document.getElementById('" . $genid . "userFormIsAdminNo'); if (this.value == '1') { if (d) d.style.display = 'block'; if (n) n.checked = true; } else { if (d) d.style.display = 'none'; if(n) n.checked = false; }"
		);
		if ($user->getId() == 1) {
			$attributes['disabled'] = 'disabled';
		}
		?>
        <?php echo select_company('user[company_id]', array_var($user_data, 'company_id'),  $attributes, false) ?>
      </div>
      
      <div class="formBlock">
        <?php echo label_tag(lang('personal project'), $genid . 'userPersonalProject', true) ?>
        <div class="desc"><?php echo lang('personal project desc') ?></div>
        <?php echo select_project2('user[personal_project_id]', $user->getPersonalProjectId(), $genid, false, null, Projects::getActiveProjects()) ?>
      </div>
      

	  <!-- user type -->
	  <div class="formBlock">
	    <?php echo label_tag(lang('user type'), null, true) ?>
	    <?php echo simple_select_box('user[type]', array(
	    		array('admin', lang('admin user')),
	    		array('normal', lang('normal user')),
	    		array('guest', lang('guest user')),
	    		), array_var($user_data, 'type', 'normal')) ?>
	  </div>

	<input type="hidden" name="user[auto_assign]" value="0" />
    </div>
    
    </fieldset>
  </div>
<?php } else { ?>
  <div>
    <?php echo label_tag(lang('username')) ?>
    <?php echo clean(array_var($user_data, 'username')) ?>
    <input type="hidden" name="user[username]" value="<?php echo clean(array_var($user_data, 'username')) ?>" />
  </div>
<?php } // if ?>

<?php if (logged_user()->isAdministrator() && isset($billing_categories) && count($billing_categories) > 0) {?>
  <div id="<?php echo $genid ?>update_profile_billing" style="display:none">
<fieldset>
	<legend><?php echo lang('billing') ?></legend>
<?php 
	$options = array(option_tag(lang('select billing category'),0,($user->getDefaultBillingId() == 0?array('selected' => 'selected'):null)));
	foreach ($billing_categories as $category){
		$options[] = option_tag($category->getName(),$category->getId(),($category->getId()==$user->getDefaultBillingId())?array('selected' => 'selected'):null);	
	}
    echo label_tag(lang('billing category'), null, false);
	echo select_box('user[default_billing_id]',$options,array('id' => 'userDefaultBilling'))
?>
</fieldset>
</div>
<?php } //if ?>

   
  <div id="<?php echo $genid ?>update_profile_timezone" style="display:none">
  
  <fieldset>
    <legend><?php echo lang('timezone') ?></legend>
    <span class="desc"><?php echo lang('auto detect user timezone') ?></span>
    <div id ="<?php echo $genid?>detectTimeZone">
    <?php echo yes_no_widget('user[autodetect_time_zone]', 'userFormAutoDetectTimezone', user_config_option('autodetect_time_zone', null, $user->getId()), lang('yes'), lang('no'), null, array('onclick' => "og.showSelectTimezone('$genid')")) ?>
    </div>
    <div id="<?php echo $genid?>selecttzdiv" <?php if (user_config_option('autodetect_time_zone', null, $user->getId())) echo 'style="display:none"'; ?>>
    <?php echo select_timezone_widget('user[timezone]', array_var($user_data, 'timezone'), 
    	array('id' => 'userFormTimezone', 'class' => 'long', 'tabindex' => '600' )) ?>
    </div>
      <input id="userFormTimezoneHidden" type="hidden" name="user[timezone]" />
	  <script type="text/javascript">
	  
		og.showSelectTimezone = function(genid)	{
			check = document.getElementById("userFormAutoDetectTimezoneYes");
			div = document.getElementById(genid + "selecttzdiv");
			if (check.checked){
				div.style.display= "none";
			}else{
				div.style.display= "";
			}
		};

		og.getTimezoneFromBrowser = function(server){	
			check = document.getElementById('userFormAutoDetectTimezoneYes');
			div = document.getElementById('userFormTimezone');
			hidden = document.getElementById('userFormTimezoneHidden');						
			if (check.checked){				
				var client = new Date();			
				var diff = client.getTime() - server.getTime();			
				diff = Math.round(diff*2/3600000);					
				hidden.value = diff/2;		
			}else{
				hidden.value = div.value;
			}					
		};
		  
	  </script>
  </fieldset>
  </div>
  
  <?php foreach ($categories as $category) { ?>
	<div style="display:none" id="<?php echo $genid . $category['name'] ?>">
	<fieldset>
		<legend><?php echo lang($category['name'])?></legend>
		<?php echo $category['content'] ?>
	</fieldset>
	</div>
	<?php } ?>

  <div>
    <?php echo label_tag(lang('email address'), 'profileFormEmail', true) ?>
    <?php echo text_field('user[email]', array_var($user_data, 'email'), 
    	array('id' => 'profileFormEmail', 'tabindex' => '2700', 'class' => 'long')) ?>
  </div>
  
  
  <div>
    <?php echo label_tag(lang('user title'), 'profileFormTitle') ?>
    <?php echo text_field('user[title]', array_var($user_data, 'title'), 
    	array('id' => 'profileFormTitle', 'tabindex' => '2800')) ?>
  </div>
  
  	<?php if ($cps > 0) { ?>
  	<div id='<?php echo $genid ?>add_custom_properties_div' style="">
		<fieldset>
			<legend><?php echo lang('custom properties') ?></legend>
			<?php echo render_object_custom_properties($user, 'Users', false) ?>
		</fieldset>
	</div>
	<?php } ?>
	
	<?php foreach ($categories as $category) { ?>
	<div style="display:none" id="<?php echo $genid . $category['name'] ?>">
	<fieldset>
		<legend><?php echo lang($category['name'])?></legend>
		<?php echo $category['content'] ?>
	</fieldset>
	</div>
	<?php } ?>
	
	<div>
		<?php echo render_object_custom_properties($user, 'Users', true) ?>
	</div>
	
  <?php echo submit_button(lang('save changes'),'s',array('tabindex' => '3000', 'onclick'=>'og.getTimezoneFromBrowser(new Date('.$now->getYear().','.($now->getMonth() - 1).','.$now->getDay().','.$now->getHour().','.$now->getMinute().','.$now->getSecond().'))')) ?>

</div>
</div>
</form>

<script>
	Ext.get('profileFormDisplayName').focus();
</script>