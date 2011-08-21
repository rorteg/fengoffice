<?php
	require_javascript("og/modules/addUserForm.js");
	require_javascript("og/Permissions.js");
	$genid = gen_id();
	$object = $user;
    set_page_title($user->isNew() ? lang('add user') : lang('edit user'));
?>

<style>
	div.adminAddUser h2
	{
		font-size: 12px;
		margin-top: 10px;
	}
	
	div.adminAddUser a.showSettings
	{
		font-size: 11px;
		font-weight: bold;
		text-decoration: underline;
	}
}
</style>

<script>
function showMoreSettings()
{
	var element = Ext.query('div.advanceOptionsContainer')[0];
	var extele= Ext.get(element);
	if(element.style.display == 'none')
	{
		extele.slideIn('t',{useDisplay:true});
		Ext.select('a.showSettings').update('<?php echo lang('add user hide settings'); ?>');
	}else{
		extele.slideOut('t',{useDisplay:true});
		
		Ext.select('a.showSettings').update('<?php echo lang('add user show settings'); ?>');
	}
}
</script>
<form style="height:100%;background-color:white" class="internalForm" action="<?php echo $company->getAddUserUrl() ?>" onsubmit="javascript:og.ogPermPrepareSendData('<?php echo $genid ?>');return true;" method="post">
    <div class="adminAddUser">
      <div class="adminHeader">
        
        <div class="adminHeaderUpperRow">
            <div class="adminTitle">
            	<table style="width:535px">
            		<tr>
            			<td>
                			<?php echo $user->isNew() ? lang('add user action') : lang('edit user') ?>
            			</td>
            		</tr>
            	</table>
            </div>
        </div>
        
		<h2><?php echo lang('add user essential') ?></h2>
        <p><?php echo lang('add user essential text'); ?></p>
        
        <!-- username -->
        <div>
        	<?php echo label_tag(lang('username'), $genid.'userFormName', true) ?>
          	<?php echo text_field('user[username]', array_var($user_data, 'username'), 
            array('class' => 'medium', 'id' => $genid.'userFormName', 'tabindex' => '100','onchange'=>'og.determinePersonalwsName(this, \'' . escape_single_quotes(new_personal_project_name()) .'\')')) ?>
        </div>
        
        <!-- email -->
        <div>
          	<?php echo label_tag(lang('email address'), 'userFormEmail', true) ?>
          	<?php echo text_field('user[email]', array_var($user_data, 'email'), 
            array('class' => 'title', 'id' => 'userFormEmail', 'tabindex' => '200')) ?>
        </div>
      
    	<script>
      	og.addUserTypeChange = function(genid, type) {
          Ext.get(genid + 'userSystemPermissions').setDisplayed(type != 'guest');
          og.ogPermReadOnly(genid, type == 'guest');
          var div = document.getElementById(genid + 'userSystemPermissions');
          var cbs = div.getElementsByTagName('input');
          for (var i=0; i < cbs.length; i++) {
              if (cbs[i].type == 'checkbox') {
                  if (cbs[i].name == 'user[can_manage_time]') {
                      cbs[i].checked = true;
                  } else {
                      cbs[i].checked = type == 'admin';
                  }
              }
          }
          
          /*Ext.get(genid + 'div_can_edit_company_data').setDisplayed(type != 'collab');
          Ext.get(genid + 'div_can_manage_security').setDisplayed(type != 'collab');
          Ext.get(genid + 'div_can_manage_workspaces').setDisplayed(type != 'collab');*/
          
      };
      </script>
          <?php $categories = array(); Hook::fire('object_add_categories', $object, $categories); ?>
          <?php $cps = CustomProperties::countHiddenCustomPropertiesByObjectType('Users'); ?>
      </div>

      <div class="adminSeparator"></div>
      <div class="adminMainBlock">
        
        <a href="Javascript:;" class="showSettings" onclick="showMoreSettings()">
        	<span><?php echo lang('add user show settings'); ?></span>
        </a>
        <p><?php echo lang('add user show settings text'); ?></p>
        
        <br/>
        <div class="adminSeparator"></div>
        <br/>
        
        <div class="advanceOptionsContainer" style="display:none">
      		<h2><?php echo lang('add user what does'); ?></h2>
      		<p><?php echo lang('add user what does text'); ?></p>
        	<!-- company -->
        	<fieldset>
        		<legend><?php echo lang('add user role'); ?></legend>
	        	<?php if(logged_user()->isAdministrator()) { ?>
	        		<div>
	          			<script>
				            //	Hide the "is administrator" option if the selected company is no the ownerCompany
	            			//it also set the option isAdministrator to NO when it is hidden.
	            			og.validateOwnerCompany = function(selectedCompany,genid) {
		                		var ownerCompanyId = <?php echo owner_company()->getId()?>;
		                		companyId= selectedCompany.value;
		                		idDivAdmin = genid + "isAdministratorDiv";
		                		adminOption = document.getElementById(idDivAdmin);
		                		if (companyId == ownerCompanyId){
		                    		if (adminOption) {
		                        		adminOption.style.display = "block";
		                    		}
		                		} else {
		                    		if (adminOption) {
		                        		radioNo = document.getElementById("userFormIsAdminNo");		  	  			 
		                        		radioYes = document.getElementById("userFormIsAdminYes");
		                        		radioNo.checked = "checked";
		                        		radioYes.checked = "";
	                        			adminOption.style.display = "none";  
		                     		}
		                		}
	            			};
	          			</script>
	          			<?php echo label_tag(lang('company'), $genid.'userFormCompany', false) ?>
	          			<?php echo select_company('user[company_id]', array_var($user_data, 'company_id'), 
	           			 array('id' => $genid.'userFormCompany', 'tabindex' => '300','onchange' => "og.validateOwnerCompany(this,'$genid')"), false, true) ?>
          				<a href="<?php echo get_url("company", "add_client") ?>" target="company" class="internalLink coViewAction ico-add" title="<?php echo lang('add a new company')?>"><?php echo lang('add company') . '...' ?></a>
	        		</div>
	      		<?php } else { ?>
	       			<input type="hidden" name="user[company_id]" value="<?php echo $company->getId()?>" />
	      		<?php } // if ?>
	      
				<!-- user type -->
	    		<div>
			        <?php echo label_tag(lang('user type'), null, false) ?>
	        		<?php echo simple_select_box('user[type]', array(
                    array('admin', lang('admin user')),
                    array('normal', lang('normal user')),
                    array('guest', lang('guest user')),
                    ), array_var($user_data, 'type', 'normal'),
                    array(
                        'onchange' => "og.addUserTypeChange('{$genid}', this.value)"
                    )) ?>
				</div>  	      
			</fieldset>

			<h2><?php echo lang('add user others'); ?></h2>
			<p><?php echo lang('add user others text'); ?></p>
      
		    <fieldset>
		    	<legend><?php echo lang('display name') ?></legend>
		        <?php echo text_field('user[display_name]', array_var($user_data, 'display_name'), 
		            array('class' => 'medium', 'id' => 'userFormDisplayName', 'tabindex' => '500')) ?>
		    </fieldset>      
		      
			<fieldset>
				<legend><?php echo lang('advanced')?></legend>
                	<div class="formBlock" onclick="og.showSelectTimezone('<?php echo $genid ?>')" >
                    	<?php echo label_tag(lang('timezone'), 'userFormTimezone', false)?>
	                    <span class="desc"><?php echo lang('auto detect user timezone') ?></span>
	                    <div id ="<?php echo $genid?>detectTimeZone">
	                    	<?php echo yes_no_widget('user[autodetect_time_zone]', 'userFormAutoDetectTimezone', user_config_option('autodetect_time_zone', null, $user->getId()), lang('yes'), lang('no')) ?>
		                    <div id="<?php echo $genid?>selecttzdiv" <?php if (user_config_option('autodetect_time_zone', null, $user->getId())) echo 'style="display:none"'; ?>>
		                    	<?php echo select_timezone_widget('user[timezone]', array_var($user_data, 'timezone'), 
		                        	array('id' => 'userFormTimezone', 'class' => 'long', 'tabindex' => '600')) ?>
		                    </div>
	                    </div>
                  
	                    <script type="text/javascript">
	                  
	                    og.showSelectTimezone = function(genid)	{
	                        check = document.getElementById("userFormAutoDetectTimezoneYes");
	                        div = document.getElementById(genid + "selecttzdiv");
	                        if (check.checked == true){
	                            div.style.display= "none";
	                        }else{
	                            div.style.display= "";
	                        }
	                        
	                      };
	                      
	                    </script>
                 	 </div>
             		<?php if($user->isNew()) { ?>
             		<?php 
             		if (array_var($user_data, 'create_contact')){ 
             		// this condition is only false when the user is created from a contact, in which case creating a new contact is not desired.
                	?>
	              	<div class="formBlock">
	                	<?php echo label_tag(lang('create contact from user')) ?>
	                	<?php echo yes_no_widget('user[create_contact]', 'createContact', array_var($user_data, 'create_contact'), lang('yes'), lang('no'), '1400') ?>
	                	<br />
	                	<span class="desc"><?php echo lang('create contact from user desc') ?></span>
	              	</div>
              		<?php } ?>
		            <div class="formBlock">
		            	<div id="<?php echo $genid . 'createPersonalWS'?>" onclick="og.showSelectPersonalWS('<?php echo $genid ?>')">
		                	<?php echo label_tag(lang('use previous personal workspace')) ?>
		                    <?php echo yes_no_widget('user[createPersonalProject]', 'user[createPersonalProject]', false, lang('use an existing workspace'), lang('create personal workspace'), '1500') ?>
		                	<br /><span class="desc"><?php echo lang('use previous personal workspace desc') ?></span>
		                    <div class="ico-color0" style="padding-left:18px" id="newWsName"></div>
		                    <div id="<?php echo $genid ?>selectPersonalProject" style="display:none">
		                        <?php echo label_tag(lang('select personal workspace'), null, true) ?>
		                        <?php echo select_project2('user[personal_project]',($user->getPersonalProject())? $user->getPersonalProject()->getId():0,$genid) ?>
		                    </div>
		                  	<script>
		                    	og.determinePersonalwsName = function (input, template) {
		                        	div = document.getElementById("newWsName");
		                        	div.innerHTML = template.replace('{0}', input.value);
		                    	};
		                    
			                    og.showSelectPersonalWS = function(genid) {
			                        check = document.getElementById("user[createPersonalProject]Yes");
			                        div = document.getElementById(genid + "selectPersonalProject");
			                        div2 = document.getElementById("newWsName");
			                        if (check.checked == true) {
			                            div.style.display = "";
			                            div2.style.display = "none";
			                            } else {
			                                div.style.display = "none";
			                                div2.style.display = "";
			                            }
			                    };
		                   		og.determinePersonalwsName(Ext.getDom('<?php echo $genid ?>userFormName'), <?php echo json_encode(new_personal_project_name()) ?>);
		                  	</script>
		            	</div>
		        	</div>
				</fieldset>

      <?php } // if ?>
      
        <fieldset>
            <legend><?php echo lang('permissions') ?></legend>
            <fieldset id="<?php echo $genid ?>userSystemPermissions" style="display:block;">
                <legend><?php echo lang('system permissions') ?></legend>
                <div id="<?php echo $genid ?>div_can_edit_company_data">
                    <?php echo checkbox_field('user[can_edit_company_data]',array_var($user_data,'can_edit_company_data'), array('id' => $genid . 'user[can_edit_company_data]' )) ?> 
                    <label for="<?php echo $genid . 'user[can_edit_company_data]' ?>" class="checkbox"><?php echo lang('can edit company data') ?></label>
                    <a href="javascript:og.toggle('<?php echo $genid ?>can_edit_company_data_help')">?</a>
                    <div id="<?php echo $genid ?>can_edit_company_data_help" class="permissions-help" style="display:none"><?php echo lang('can_edit_company_data description') ?></div>
                </div>
                <div id="<?php echo $genid ?>div_can_manage_security">
                    <?php echo checkbox_field('user[can_manage_security]', array_var($user_data,'can_manage_security'), array('id' => $genid . 'user[can_manage_security]' )) ?> 
                    <label for="<?php echo $genid . 'user[can_manage_security]' ?>" class="checkbox"><?php echo lang('can manage security') ?></label>
                    <a href="javascript:og.toggle('<?php echo $genid ?>can_manage_security_help')">?</a>
                    <div id="<?php echo $genid ?>can_manage_security_help" class="permissions-help" style="display:none"><?php echo lang('can_manage_security description') ?></div>
                </div>
                <div id="<?php echo $genid ?>div_can_manage_workspaces">
                    <?php echo checkbox_field('user[can_manage_workspaces]', array_var($user_data,'can_manage_workspaces'), array('id' => $genid . 'user[can_manage_workspaces]' )) ?> 
                    <label for="<?php echo $genid . 'user[can_manage_workspaces]' ?>" class="checkbox"><?php echo lang('can manage workspaces') ?></label>
                    <a href="javascript:og.toggle('<?php echo $genid ?>can_manage_workspaces_help')">?</a>
                    <div id="<?php echo $genid ?>can_manage_workspaces_help" class="permissions-help" style="display:none"><?php echo lang('can_manage_workspaces description') ?></div>
                </div>
                <div id="<?php echo $genid ?>div_can_manage_configuration">
                    <?php echo checkbox_field('user[can_manage_configuration]', array_var($user_data,'can_manage_configuration'), array('id' => $genid . 'user[can_manage_configuration]' )) ?> 
                    <label for="<?php echo $genid . 'user[can_manage_configuration]' ?>" class="checkbox"><?php echo lang('can manage configuration') ?></label>
                    <a href="javascript:og.toggle('<?php echo $genid ?>can_manage_configuration_help')">?</a>
                    <div id="<?php echo $genid ?>can_manage_configuration_help" class="permissions-help" style="display:none"><?php echo lang('can_manage_configuration description') ?></div>
                </div>
                <div id="<?php echo $genid ?>div_can_manage_contacts">
                    <?php echo checkbox_field('user[can_manage_contacts]', array_var($user_data,'can_manage_contacts'), array('id' => $genid . 'user[can_manage_contacts]' )) ?> 
                    <label for="<?php echo $genid . 'user[can_manage_contacts]' ?>" class="checkbox"><?php echo lang('can manage contacts') ?></label>
                    <a href="javascript:og.toggle('<?php echo $genid ?>can_manage_contacts_help')">?</a>
                    <div id="<?php echo $genid ?>can_manage_contacts_help" class="permissions-help" style="display:none"><?php echo lang('can_manage_contacts description') ?></div>
                </div>
                <div id="<?php echo $genid ?>div_can_manage_templates">
                    <?php echo checkbox_field('user[can_manage_templates]', array_var($user_data,'can_manage_templates'), array('id' => $genid . 'user[can_manage_templates]' )) ?> 
                    <label for="<?php echo $genid . 'user[can_manage_templates]' ?>" class="checkbox"><?php echo lang('can manage templates') ?></label>
                    <a href="javascript:og.toggle('<?php echo $genid ?>can_manage_templates_help')">?</a>
                    <div id="<?php echo $genid ?>can_manage_templates_help" class="permissions-help" style="display:none"><?php echo lang('can_manage_templates description') ?></div>
                </div>
                <div id="<?php echo $genid ?>div_can_manage_reports">
                    <?php echo checkbox_field('user[can_manage_reports]', array_var($user_data,'can_manage_reports'), array('id' => $genid . 'user[can_manage_reports]' )) ?> 
                    <label for="<?php echo $genid . 'user[can_manage_reports]' ?>" class="checkbox"><?php echo lang('can manage reports') ?></label>
                    <a href="javascript:og.toggle('<?php echo $genid ?>can_manage_reports_help')">?</a>
                    <div id="<?php echo $genid ?>can_manage_reports_help" class="permissions-help" style="display:none"><?php echo lang('can_manage_reports description') ?></div>
                </div>
                <div id="<?php echo $genid ?>div_can_manage_time">
                    <?php echo checkbox_field('user[can_manage_time]', array_var($user_data,'can_manage_time'), array('id' => $genid . 'user[can_manage_time]' )) ?> 
                    <label for="<?php echo $genid . 'user[can_manage_time]' ?>" class="checkbox"><?php echo lang('can manage time') ?></label>
                    <a href="javascript:og.toggle('<?php echo $genid ?>can_manage_time_help')">?</a>
                    <div id="<?php echo $genid ?>can_manage_time_help" class="permissions-help" style="display:none"><?php echo lang('can_manage_time description') ?></div>
                </div>
                <div id="<?php echo $genid ?>div_can_add_mail_accounts">
                    <?php echo checkbox_field('user[can_add_mail_accounts]', array_var($user_data,'can_add_mail_accounts'), array('id' => $genid . 'user[can_add_mail_accounts]' )) ?> 
                    <label for="<?php echo $genid . 'user[can_add_mail_accounts]' ?>" class="checkbox"><?php echo lang('can add mail accounts') ?></label>
                    <a href="javascript:og.toggle('<?php echo $genid ?>can_add_mail_accounts_help')">?</a>
                    <div id="<?php echo $genid ?>can_add_mail_accounts_help" class="permissions-help" style="display:none"><?php echo lang('can_add_mail_accounts description') ?></div>
                </div>
                <?php
                    $other_permissions = array();
                    Hook::fire('add_user_permissions', $user, $other_permissions);
                    foreach ($other_permissions as $perm => $perm_val) {?>
                        <div id="<?php echo $genid ?>div_<?php echo $perm ?>">
                          <?php echo checkbox_field("user[$perm]", array_var($user_data,$perm), array('id' => $genid . "user[$perm]" )) ?> 
                          <label for="<?php echo $genid . "user[$perm]" ?>" class="checkbox"><?php echo lang($perm) ?></label>
                          <a href="javascript:og.toggle('<?php echo $genid ?><?php echo $perm ?>_help')">?</a>
                          <div id="<?php echo $genid ?><?php echo $perm ?>_help" class="permissions-help" style="display:none"><?php echo lang($perm.' description') ?></div>
                        </div>
                    <?php }
                ?>
            </fieldset>
            <fieldset>
                <legend><?php echo lang('project permissions') ?></legend>
                <?php 
                tpl_assign('genid', $genid);
                $this->includeTemplate(get_template_path('user_permissions_control', 'account'));
                ?>
            </fieldset>
        </fieldset>
           
    <?php if($user->isNew() || logged_user()->isAdministrator()) { ?>
      	<fieldset>
      		<legend><?php echo lang('password') ?></legend>
        	<div>
          		<?php echo radio_field('user[password_generator]', array_var($user_data, 'password_generator') == 'random', array('value' => 'random', 'class' => 'checkbox', 'id' => 'userFormRandomPassword', 'onclick' => 'App.modules.addUserForm.generateRandomPasswordClick()', 'tabindex' => '700')) ?> <?php echo label_tag(lang('user password generate'), 'userFormRandomPassword', false, array('class' => 'checkbox'), '') ?>
        	</div>
        	<div>
          		<?php echo radio_field('user[password_generator]', array_var($user_data, 'password_generator') == 'specify', array('value' => 'specify', 'class' => 'checkbox', 'id' => 'userFormSpecifyPassword', 'onclick' => 'App.modules.addUserForm.generateSpecifyPasswordClick()', 'tabindex' => '800')) ?> <?php echo label_tag(lang('user password specify'), 'userFormSpecifyPassword', false, array('class' => 'checkbox'), '') ?>
        	</div>
        	<div id="userFormPasswordInputs">
        		<div>
            		<?php echo label_tag(lang('password'), 'userFormPassword', true) ?>
            		<?php echo password_field('user[password]', null, array('id' => 'userFormPassword', 'tabindex' => '900')) ?>
          		</div>
          		<div>
            		<?php echo label_tag(lang('password again'), 'userFormPasswordA', true) ?>
            		<?php echo password_field('user[password_a]', null, array('id' => 'userFormPasswordA', 'tabindex' => '1000')) ?>
          		</div>
        	</div>
        	<div style="margin-top:10px">
        			<label class="checkbox">
       				 <?php echo checkbox_field('user[send_email_notification]', array_var($user, 'send_email_notification', 1), array('id' => $genid . 'notif', 'tabindex' => '1050')) ?>
        			<?php echo lang('send new account notification')?>
        		</label>
        	</div>
      		</fieldset>
				<script>
	        		App.modules.addUserForm.generateRandomPasswordClick();
	        	</script>
    			<?php } // if ?>

        		<?php if (isset($billing_categories) && count($billing_categories) > 0) {?>
        		<fieldset>
	            	<legend><?php echo lang('billing') ?></legend>
		        	<?php 
		            	$options = array();
		            	$options[] = option_tag(lang('none'),0,(0==$user->getDefaultBillingId())?array('selected' => 'selected'):null);
		                
			            foreach ($billing_categories as $category){
			                $options[] = option_tag($category->getName(),$category->getId(),($category->getId()==$user->getDefaultBillingId())?array('selected' => 'selected'):null);	
			            }
		            	echo label_tag(lang('billing category'), null, false);
		            	echo select_box('user[default_billing_id]',$options,array('id' => 'userDefaultBilling'))
		        	?>
        		</fieldset>
        	<?php } //if ?>

        	<div id='<?php echo $genid ?>add_custom_properties_div' style="display:none">
            	<fieldset>
                	<legend><?php echo lang('custom properties') ?></legend>
                	<?php echo render_object_custom_properties($user, 'Users', false) ?>
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
            	<?php echo render_object_custom_properties($user, 'Users', true) ?>
        	</div>
      	</div>
      	<?php 
      		echo input_field('user[contact_id]',array_var($user_data, 'contact_id',''), array('type' => 'hidden'));
      		echo submit_button($user->isNew() ? lang('add user button') : lang('save changes'), 's', array('tabindex' => '1500')); 
      	?>        
    	</div>
    </div>
</form>

<script>
	Ext.get('<?php echo $genid ?>userFormName').focus();
	
	og.eventManager.addListener("company added", function(company) {
		var id = '<?php echo $genid.'userFormCompany' ?>';
		var select = document.getElementById('<?php echo $genid.'userFormCompany' ?>');
		if (!select) return "remove";
		var newopt = document.createElement('option');
		newopt.value = company.id;
		newopt.innerHTML = company.name;
		select.appendChild(newopt);
		select.value = company.id;
	}); 
</script>
