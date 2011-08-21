<?php
$genid = gen_id();
set_page_title($mailAccount->isNew() ? lang('add mail account') : lang('edit mail account'));
if (!$mailAccount->isNew() && $mailAccount->canDelete(logged_user())) {
	add_page_action(lang('delete mail account'),  "javascript:og.promptDeleteAccount(".$mailAccount->getId().");", 'ico-delete');
}
$logged_user_settings = MailAccountUsers::getByAccountAndUser($mailAccount, logged_user());
if (!$logged_user_settings instanceof MailAccountUser) {
	$logged_user_can_edit = $mailAccount->isNew();
	$user_settings = array();
} else {
	$logged_user_can_edit = $logged_user_settings->getCanEdit();
	$user_settings = array(
		'is_default' => $logged_user_settings->getIsDefault(),
		'sender_name' => $logged_user_settings->getSenderName(),
		'signature' => $logged_user_settings->getSignature(),
	);
}
if ($mailAccount->getUserId() == logged_user()->getId()) {
	// the creator of the account can always edit it
	$logged_user_can_edit = true;
}

if (!$mailAccount->isNew()){	
	$mail_acc_id = $mailAccount->getId();	
}
?>

<form style="height: 100%; background-color: white" class="internalForm"
	action="<?php echo $mailAccount->isNew() ? get_url('mail', 'add_account') : $mailAccount->getEditUrl()?>"
	method="post">
<input type="hidden" name="submitted" value="true" />

<div class="adminAddMailAccount">
<div class="adminHeader">
	<div class="adminHeaderUpperRow">
		<div class="adminTitle">
		<table style="width: 535px">
			<tr>
				<td><?php echo $mailAccount->isNew() ? lang('new mail account') : lang('edit mail account') ?></td>
				<td style="text-align: right">
					<?php echo submit_button($mailAccount->isNew() ? lang('add mail account') : lang('save changes'), '',  array('style'=>'margin-top:0px;margin-left:10px', 'tabindex'=>'1301')) ?>
				</td>
			</tr>
		</table>
		</div>
	</div>

	<div class="mail-account-item">
		<label for='<?php echo $genid ?>mailAccountFormName'><?php echo lang('mail account name')?>
			<span class="label_required">*</span>
			<span class="desc"><?php echo lang('mail account name description') ?></span>
		</label>
		<?php if ($logged_user_can_edit) {
			echo text_field('mailAccount[name]', array_var($mailAccount_data, 'name'), array('class' => 'title', 'tabindex'=>'10', 'id' => $genid.'mailAccountFormName'));
		} else {
			echo text_field('', array_var($mailAccount_data, 'name'), array('class' => 'title', 'tabindex'=>'10', 'id' => $genid.'mailAccountFormName', 'disabled' => 'disabled'));
		} ?>
	</div>

	<div class="mail-account-item">
		<label for="mailAccountFormEmail"><?php echo lang('mail address')?>
			<span class="label_required">*</span> <span class="desc"><?php echo lang('mail address description') ?></span>
		</label>
		<?php if ($logged_user_can_edit) {
			$sync = (config_option("sent_mails_sync")) ? '1' : '0';
			echo text_field('mailAccount[email_addr]', array_var($mailAccount_data, 'email_addr'), array('id' => 'mailAccountFormEmail', 'tabindex'=>'20','onchange'=> 'og.autofillmailaccountinfo(this.value,\''.$genid.'\','.$sync.')'));
		} else {
			echo text_field('', array_var($mailAccount_data, 'email_addr'), array('id' => 'mailAccountFormEmail', 'tabindex'=>'20', 'disabled' => 'disabled'));
		} ?>
		<div style="color:#e22;" id="<?php echo $genid ?>autoconfigmessage"> </div>
		
	</div>

	<div style="padding-top:5px">
		<?php if ($logged_user_can_edit) { ?>
			<a href="#" class="option" style="font-weight: bold" onclick="og.toggleAndBolden('<?php echo $genid ?>incoming_settings_div', this)"><?php echo lang('incoming settings') ?></a> -
			<a href="#" class="option" style="font-weight: bold" onclick="og.toggleAndBolden('<?php echo $genid ?>smtp_settings_div', this)"><?php echo lang('smtp settings') ?></a> - 
		<?php } ?>
		<a href="#" class="option" style="font-weight: bold" onclick="og.toggleAndBolden('<?php echo $genid ?>other_settings_div',this)"><?php echo lang('personal settings') ?></a>
		<?php if ($logged_user_can_edit) { ?>
			- <a href="#" class="option" style="font-weight: normal" onclick="og.toggleAndBolden('<?php echo $genid ?>account_permissions_div',this)"><?php echo lang('mail account permissions') ?></a>
		<?php } ?>
	</div>
</div>
<div class="adminSeparator"></div>
<div class="adminMainBlock">

<?php if ($logged_user_can_edit) { ?>

	<fieldset id="<?php echo $genid ?>incoming_settings_div">
		<legend><?php echo lang('incoming settings'); ?></legend>

		<div class="mail-account-item">
			<label for="<?php echo $genid ?>email">
				<?php echo lang('mail account id')?><span class="label_required">*</span>
				<span class="desc"><?php echo lang('mail account id description') ?></span>
			</label>
			<?php echo text_field('mailAccount[email]', array_var($mailAccount_data, 'email'), array('id' => $genid.'email', 'tabindex'=>'30')) ?>
		</div>

		<div class="mail-account-item">
			<label for="<?php echo $genid?>password">
				<?php echo lang('password')?><span class="label_required">*</span>
				<span class="desc"><?php echo lang('mail account password description') ?></span>
			</label>
			<?php echo password_field('mailAccount[password]', array_var($mailAccount_data, 'password'), array('id' => $genid.'password', 'tabindex'=>'40')) ?>
		</div>

		<div class="mail-account-item">
			<label for="<?php echo $genid ?>server">
				<?php echo lang('server address')?><span class="label_required">*</span>
				<span class="desc"><?php echo lang('mail account server description') ?></span>
			</label>
			<?php echo text_field('mailAccount[server]', array_var($mailAccount_data, 'server'), array('id' => $genid.'server', 'tabindex'=>'50')) ?>
		</div>

		<div class="mail-account-item">
			<label for="<?php echo $genid ?>method"><?php echo lang('email connection method')?></label><?php
				$options = array();
				$attributes = array();
				if (array_var($mailAccount_data, 'is_imap', false)) {
					$attributes['selected'] = "selected";
				}
				$options[] = option_tag(lang('imap'), '1', $attributes);
				$attributes = array();
				if (!array_var($mailAccount_data, 'is_imap', false)) {
					$attributes['selected'] = "selected";
				}
				$options[] = option_tag(lang('pop3'), '0', $attributes);
				$onchange = "var ssl = document.getElementById('$genid' + 'sslport');var folders = document.getElementById('$genid' + 'folders');if (this.value == 1) { folders.style.display = 'block'; ssl.value = '993'; } else { folders.style.display = 'none'; ssl.value = '995'; }";
				echo select_box('mailAccount[is_imap]', $options, array("onchange" => $onchange, 'tabindex' => '60', 'id' => $genid . 'method'));

				$onchange = "var div = document.getElementById('$genid' + 'sslportdiv');if(this.checked) div.style.display='block';else div.style.display='none';";
				echo checkbox_field('mailAccount[incoming_ssl]', array_var($mailAccount_data, 'incoming_ssl'), array('id' => $genid.'ssl', 'tabindex'=>'70', 'onclick' => $onchange)) ?>
			<label for="<?php echo $genid ?>ssl" class="yes_no"><?php echo lang('incoming ssl') ?></label>
		</div>

		<div class="mail-account-item" id="<?php echo $genid ?>sslportdiv" <?php if (!array_var($mailAccount_data, 'incoming_ssl')) echo 'style="display:none"'; ?>>
			<?php echo label_tag(lang('incoming ssl port'), 'mailAccountFormIncomingSslPort') ?>
			<?php echo text_field('mailAccount[incoming_ssl_port]', array_var($mailAccount_data, 'incoming_ssl_port', 995), array('id' => $genid.'sslport', 'tabindex'=>'120')) ?>
		</div>

		<div class="mail-account-item" id="<?php echo $genid ?>folders" style="padding:5px;<?php if (!array_var($mailAccount_data, 'is_imap', false)) echo 'display:none'; ?>">

			<div id="<?php echo $genid ?>imap_folders"><?php
				tpl_assign('imap_folders', isset($imap_folders) ? $imap_folders : array());
				tpl_assign('genid', $genid);
				tpl_display(get_template_path("fetch_imap_folders", "mail")) ?>
			</div>
		</div>
		
		<div>
			<label for="mailAccountDelMailFromServer">
				<?php echo lang('delete mails from server')?>
				<span class="desc"><?php echo lang('mail account delete mails from server description') ?></span>
			</label>
			<?php $del_from_server = array_var($mailAccount_data, 'del_from_server', 0) ?>
			<?php echo yes_no_widget('mailAccount[del_mails_from_server]', 'mailAccountDelMailFromServer', $del_from_server > 0, lang('yes'), lang('no'), 130) ?>
			<?php echo '<span style="margin-left: 10px">' . lang('after') . '</span>'?>
			<?php echo text_field('mailAccount[del_from_server]', $del_from_server <= 0 ? 1 : $del_from_server, array('id' => 'mailAccountDelFromServer', 'tabindex'=>'140', 'style'=>'width:25px')) ?>
			<?php echo lang('days'); ?>
		</div>
		
		<div>
			<label>
				<?php echo lang ('classify mails on workspace') ?>
				<span class="desc"><?php echo lang ('classify mails on workspace desc') ?> </span>
			</label>
				<?php
					echo select_project2('mailAccount[workspace]', $mailAccount_data['workspace']?$mailAccount_data['workspace']:0,$genid, true,null);
				?>
		</div>
		
	</fieldset>

	<fieldset id="<?php echo $genid ?>smtp_settings_div">
		<legend><?php echo lang('smtp settings')?></legend>
		<div class="mail-account-item">
			<label for="mailSmtpServer">
				<?php echo lang('smtp server')?> <span class="label_required">*</span>
				<span class="desc"><?php echo lang('mail account smtp server description') ?></span>
			</label>
			<?php echo text_field('mailAccount[smtp_server]', array_var($mailAccount_data, 'smtp_server'), array('id' => 'mailSmtpServer', 'tabindex'=>'150')) ?>
		</div>

		<div class="mail-account-item">
			<label for="mailSmtpPort">
				<?php echo lang('smtp port')?> <span class="label_required">*</span>
				<span class="desc"><?php echo lang('mail account smtp port description') ?></span>
			</label>
			<?php echo text_field('mailAccount[smtp_port]', array_var($mailAccount_data, 'smtp_port',25), array('id' => 'mailSmtpPort', 'tabindex'=>'160')) ?>
		</div>

		<div class="mail-account-item">
			<label for="mailSmtpUseAuth">
				<?php echo lang('smtp use auth')?> <span class="label_required">*</span>
				<span class="desc"><?php echo lang('mail account smtp use auth description') ?></span>
			</label> <?php
			$use_auth = array_var($mailAccount_data, 'smtp_use_auth',1);
			$options = array(
				option_tag(lang('no smtp auth'), 0, ($use_auth==0)?array('selected' => 'selected'):null),
				option_tag(lang('same as incoming'), 1, ($use_auth==1)?array('selected' => 'selected'):null),
				option_tag(lang('smtp specific'), 2, ($use_auth==2)?array('selected' => 'selected'):null)
			);
			echo select_box('mailAccount[smtp_use_auth]', $options, array(
				'id' => 'mailSmtpUseAuth', 'tabindex'=>'170',
				'onchange' => "if(document.getElementById('mailSmtpUseAuth').selectedIndex ==2) document.getElementById('smtp_specific_auth').style.display = 'block'; else document.getElementById('smtp_specific_auth').style.display = 'none';"
			)); ?>
		</div>

		<div class="mail-account-item" id = 'smtp_specific_auth' style='<?php if(array_var($mailAccount_data, 'smtp_use_auth',1)!=2) echo 'display:none';?>'>
			<div class="mail-account-item">
				<label for="mailSmtpUsername"><?php echo lang('smtp username')?> <span class="label_required"></span>
					<span class="desc"><?php echo lang('mail account smtp username description') ?></span>
				</label>
				<?php echo text_field('mailAccount[smtp_username]', array_var($mailAccount_data, 'smtp_username'), array('id' => 'mailSmtpUsername', 'tabindex'=>'180')) ?>
			</div>

			<div class="mail-account-item">
				<label for="mailSmtpPassword">
					<?php echo lang('smtp password')?> <span class="label_required"></span>
					<span class="desc"><?php echo lang('mail account smtp password description') ?></span>
				</label>
				<?php echo password_field('mailAccount[smtp_password]', array_var($mailAccount_data, 'smtp_password'), array('id' => 'mailSmtpPassword', 'tabindex'=>'190')) ?>
			</div>
		</div>

		<div class="mail-account-item">
			<label for="mailOutgoingTransportType">
				<?php echo lang('outgoing transport type')?><span class="label_required">*</span>
				<span class="desc"><?php echo lang('mail account outgoing transport type description') ?></span>
			</label> <?php
			$ottype = array_var($mailAccount_data, 'outgoing_transport_type', '');
			$t_options = array(
				option_tag(lang('no'), '', ($ottype=='')?array('selected' => 'selected'):null),
				option_tag('SSL', 'ssl', ($ottype=='ssl')?array('selected' => 'selected'):null),
				option_tag('TLS', 'tls', ($ottype=='tls')?array('selected' => 'selected'):null)
			);
			echo select_box('mailAccount[outgoing_transport_type]', $t_options,
			array('id' => 'mailOutgoingTransportType', 'tabindex'=>'200', 'onchange' => ""));
			?>
		</div>
	</fieldset>
	
	<?php		
		if (config_option("sent_mails_sync")) { ?>
			<fieldset id="<?php echo $genid ?>sent_mails_sync">
				<legend><?php echo lang('sent mails sync'); ?></legend>
							<div class="mail-account-item">
								<label for="<?php echo $genid ?>sync_addr">
									<?php echo lang('mail account id')?><span class="label_required">*</span>
									<span class="desc"><?php echo lang('mail account id description') ?></span>
								</label>
								<?php echo text_field('mailAccount[sync_addr]', array_var($mailAccount_data, 'sync_addr'), array('id' => $genid.'sync_addr', 'tabindex'=>'30')) ?>
							</div>
					
							<div class="mail-account-item">
								<label for="<?php echo $genid?>sync_pass">
									<?php echo lang('password')?><span class="label_required">*</span>
									<span class="desc"><?php echo lang('mail account password description') ?></span>
								</label>
								<?php echo password_field('mailAccount[sync_pass]', array_var($mailAccount_data, 'sync_pass'), array('id' => $genid.'sync_pass', 'tabindex'=>'40')) ?>
							</div>
					
							<div class="mail-account-item">
								<label for="<?php echo $genid ?>sync_server">
									<?php echo lang('server address')?><span class="label_required">*</span>
									<span class="desc"><?php echo lang('mail account server description') ?></span>
								</label>
								<?php echo text_field('mailAccount[sync_server]', array_var($mailAccount_data, 'sync_server'), array('id' => $genid.'sync_server', 'tabindex'=>'50')) ?>
							</div>
					
							<div class="mail-account-item">
								<label for="<?php echo $genid ?>method"><?php echo lang('connnection security')?></label>
								<input id="<?php echo $genid.'is_imap'?>sync_is_imap" type="hidden" name="sync_imap" value="1" ><?php 
																
									$onchange = "var div = document.getElementById('$genid' + 'sync_sslportdiv');if(this.checked) div.style.display='block';else div.style.display='none';";
									echo checkbox_field('mailAccount[sync_ssl]', array_var($mailAccount_data, 'sync_ssl'), array('id' => $genid.'sync_ssl', 'tabindex'=>'70', 'onclick' => $onchange)) ?>											
							
								<label for="<?php echo $genid ?>sync_ssl" class="yes_no"><?php echo lang('incoming ssl') ?></label>
							</div>
					
							<div class="mail-account-item" id="<?php echo $genid ?>sync_sslportdiv" <?php if (!array_var($mailAccount_data, 'sync_ssl')) echo 'style="display:none"'; ?>>
								<?php echo label_tag(lang('incoming ssl port'), 'mailAccountFormIncomingSslPort') ?>
								<?php echo text_field('mailAccount[sync_ssl_port]', array_var($mailAccount_data, 'sync_ssl_port', 993), array('id' => $genid.'sync_sslport', 'tabindex'=>'120')) ?>
							</div>
					
							<div class="mail-account-item" id="<?php echo $genid ?>sync_folders" style="padding:5px;<?php  ?>">
					
								<div id="<?php echo $genid ?>imap_folders_sync"><?php
									tpl_assign('imap_folders_sync', isset($imap_folders_sync) ? $imap_folders_sync : array());									
									tpl_assign('genid', $genid);
									tpl_assign('mail_acc_id',$mail_acc_id);
									tpl_display(get_template_path("fetch_imap_folders_sync", "mail")) ?>
								</div>
							</div>
												
			
			</fieldset>
			
		<?php }

	?>
	
	
<?php } ?>
	
	<fieldset id="<?php echo $genid ?>other_settings_div">
		<legend><?php echo lang('personal settings')?></legend>
		<div class="desc"><?php echo lang('personal settings desc') ?></div>
		<div class="mail-account-item">
			<label for="<?php echo $genid?>sender_name">
				<?php echo lang('mail account sender name') ?>
				<span class="desc"><?php echo lang('mail account sender name description') ?></span>
			</label>
			<?php echo input_field('sender_name', array_var($user_settings, 'sender_name', ''), array('id' => $genid."sender_name", 'tabindex' => 1210)) ?>
		</div>
		
		<?php if ($is_admin){?>
		<div class="mail-account-item">
			<label for="<?php echo $genid ?>assign_to">
				<?php echo lang('assign to')?>
				<span class="desc"><?php echo lang('assigned to description') ?></span>
			</label>
			<?php			
			$select_box_attrib = array('id'=>$genid.'users_select_box');
			echo user_select_box('users_select_box', logged_user()->getId(),$select_box_attrib);			
		?>
							
		</div>
		<?php }?>
		
		<div class="mail-account-item">
			<label for="<?php echo $genid ?>is_default">
				<?php echo lang('default account')?>
				<span class="desc"><?php echo lang('default account description') ?></span>
			</label>
			<?php echo yes_no_widget('is_default', $genid.'is_default', array_var($user_settings, 'is_default', 0) > 0, lang('yes'), lang('no'), 1220) ?>
		</div>
		
		<div>
		    <label for="mailSignature">
		    	<?php echo lang('signature')?>
		    	<span class="desc"><?php echo lang('signature description') ?></span>
		    </label>
		    <?php echo textarea_field('signature', array_var($user_settings, 'signature', ''), array('id' => $genid.'signature', 'tabindex'=>'1230', 'style' => 'width:100%;max-width:500px;height:100px;')) ?>
		</div>
	</fieldset>
	
<?php if ($logged_user_can_edit) { ?>
	<fieldset id="<?php echo $genid ?>account_permissions_div" style="display:none;">
		<legend><?php echo lang('mail account permissions')?></legend>
		<div class="desc"><?php echo lang('mail account permissions desc')?></div>
		<?php
		$account_users = logged_user()->getCompany()->getUsers();
		$account_user_ids = is_array($mailAccountUsers) ? array_keys($mailAccountUsers) : array();
		$num = 0;
		$alt = true;
		foreach ($account_users as $user) {
			$num++;
			$alt = !$alt; ?>
			<div class="account_permissions_user<?php if ($alt) echo " odd"; ?>">
				<div class="user_picture cardIcon"><img src="<?php echo $user->getAvatarUrl();?>"></img></div>
				<div class="user_name">
					<?php echo clean($user->getDisplayName()) ?>
				</div> <?php
				if (in_array($user->getId(), $account_user_ids)) {
					if (array_var($mailAccountUsers[$user->getId()], 'can_edit')) {
						$access = 'write';
					} else {
						$access = 'read';
					} 
				} else {
					$access = 'none';
				} ?>
				<div class="user_access">
					<select name="user_access[<?php echo $user->getId() ?>]" tabindex="<?php echo 200 + $num ?>">
						<option value="none" <?php if ($access == 'none') echo 'selected="selected"'; ?>><?php echo lang('cannot access account')?></option>
						<option value="read" <?php if ($access == 'read') echo 'selected="selected"'; ?>><?php echo lang('can view account emails')?></option>
						<option value="write" <?php if ($access == 'write') echo 'selected="selected"'; ?>><?php echo lang('can view account emails and edit')?></option>
					</select>
				</div>
				<div class="separator"></div>
			</div> <?php
		} ?>
	</fieldset>
<?php } ?>
	
<?php echo submit_button($mailAccount->isNew() ? lang('add mail account') : lang('save changes'), 's', array('tabindex'=>'1240')) ?>

</div>
</div>
</form>

<script>
	og.autofillsyncinfo = function (genid, sync){		
		if (sync){					
			var method = document.getElementById(genid+'method');			
			if (method[0].selected){			
				var serverAddress = document.getElementById(genid+'server');
				var serverAddressSync = document.getElementById(genid+'sync_server');				
				serverAddressSync.value = serverAddress.value;
				var emailAddress = document.getElementById(genid+'email');
				var emailAddressSync = document.getElementById(genid+'sync_addr');
				emailAddressSync.value = emailAddress.value;
				var ssl = document.getElementById(genid+'ssl');
				var sslSync = document.getElementById(genid+'sync_ssl');
				sslSync.checked = ssl.checked;
				var sslport = document.getElementById(genid+'sslport');
				var sslportSync = document.getElementById(genid+'sync_sslport');
				sslportSync.value = sslport.value;
				passInput = document.getElementById(genid+'sync_pass');
				var sync = 'og.fetchImapFoldersSync(\''+genid+'\')';
				passInput.setAttribute('onchange',sync);
			}
		}
	}	


	og.autofillmailaccountinfo = function (addres , genid, sync) {
		atIndex = addres.indexOf("@");
		var autoconf = false;
		if (atIndex != -1) {
			domain = addres.substring(atIndex+1);
			domainName = domain.substring(0,domain.indexOf('.')).toLowerCase();
			messageDiv = document.getElementById(genid+'autoconfigmessage');
			messageDiv.innerHTML = "";
			switch (domainName) {
				case 'hotmail':
					autoconf = true;					
					serverAddres = document.getElementById(genid+'server');
					serverAddres.value = 'pop3.live.com';
					smtpServer = document.getElementById('mailSmtpServer');
					smtpServer.value = 'smtp.live.com';
					email = document.getElementById(genid+'email');
					email.value = addres;
					smtpPort = document.getElementById('mailSmtpPort');
					smtpPort.value = '25';
					method = document.getElementById(genid+'method');
					method[1].selected = 'selected';
					method[0].selected = '';
					useSsl = document.getElementById(genid+'ssl');
					useSsl.checked = 'checked';
					ssl = document.getElementById(genid + 'sslport');
					ssl.value = '995';
					divSsl = document.getElementById(genid + 'sslportdiv');
					divSsl.style.display = 'block';
					connectionType = document.getElementById('mailOutgoingTransportType');
					connectionType[1].selected = 'selected';
					connectionType[0].selected = '';
					connectionType[2].selected = '';
					break;
				case 'gmail':
				case 'googlemail':
					autoconf = true;
					serverAddres = document.getElementById(genid+'server');
					serverAddres.value = 'imap.' + domainName + '.com';
					smtpServer = document.getElementById('mailSmtpServer');
					smtpServer.value = 'smtp.' + domainName + '.com';
					email = document.getElementById(genid+'email');
					email.value = addres;
					smtpPort = document.getElementById('mailSmtpPort');
					smtpPort.value = '465';
					method = document.getElementById(genid+'method');
					method[0].selected = 'selected';
					method[1].selected = '';
					ssl = document.getElementById(genid + 'sslport');
					folders = document.getElementById(genid + 'folders');
					folders.style.display = 'block';
					ssl.value = '993';
					useSsl = document.getElementById(genid+'ssl');
					useSsl.checked = 'checked';
					divSsl = document.getElementById(genid + 'sslportdiv');
					divSsl.style.display = 'block';
					//Ext.get(genid+'password').on('onchange',og.fetchImapFolders(genid));
					passInput = document.getElementById(genid+'password');
					var fun = 'og.fetchImapFolders(\''+genid+'\')';
					passInput.setAttribute('onchange',fun);
					connectionType = document.getElementById('mailOutgoingTransportType');
					connectionType[1].selected = 'selected';
					connectionType[0].selected = '';
					connectionType[2].selected = '';
					domainName = 'gmail';
					break;				
				case 'yahoo':
				case 'ymail':
				case 'rocketmail':
					autoconf = true;
					serverAddres = document.getElementById(genid+'server');
					serverAddres.value = 'plus.pop.mail.yahoo.com';
					smtpServer = document.getElementById('mailSmtpServer');
					smtpServer.value = 'plus.smtp.mail.yahoo.com';
					email = document.getElementById(genid+'email');
					email.value = addres.substring(0,addres.indexOf('@'));
					smtpPort = document.getElementById('mailSmtpPort');
					smtpPort.value = '465';
					method = document.getElementById(genid+'method');
					method[1].selected = 'selected';
					method[0].selected = '';
					useSsl = document.getElementById(genid+'ssl');
					useSsl.checked = 'checked';
					divSsl = document.getElementById(genid + 'sslportdiv');
					divSsl.style.display = 'block';
					connectionType = document.getElementById('mailOutgoingTransportType');
					connectionType[1].selected = 'selected';
					connectionType[0].selected = '';
					connectionType[2].selected = '';
					domainName = 'yahoo';
					break;
				default:
					return;
			}					
			og.autofillsyncinfo(genid, sync);			
			if (autoconf) {
				messageDiv.innerHTML = lang('autoconfig ' + domainName + ' message');
			}
		}
	};

	<?php if ($logged_user_can_edit) { ?>
		Ext.get('<?php echo $genid ?>mailAccountFormName').focus();
	<?php } else { ?>
		Ext.get('<?php echo $genid ?>sender_name').focus();
	<?php } ?>
</script>

