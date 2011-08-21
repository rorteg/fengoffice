<?php
	require_javascript('og/modules/linkToObjectForm.js'); 
	require_javascript('og/ObjectPicker.js');
	require_javascript('og/mail/addMail.js'); 
 
	set_page_title( lang('write mail'));
  
	$genid = gen_id();
 
	$type = array_var($mail_data, 'type', user_config_option('last_mail_format'));
	if (!$type) $type = 'plain';
	$object = $mail;
	$draft_edit = array_var($mail_data, 'draft_edit', false);
	$mail_to = isset($mail_to) ? $mail_to : array_var($mail_data, 'to');
	if (!isset($link_to_objects)) $link_to_objects = null;
?>
<input type="hidden" id="<?php echo $genid ?>signatures" />
<script>
og.attachContents = <?php echo user_config_option('attach_docs_content') ? '1' : '0'; ?>;

var sig = Ext.getDom('<?php echo $genid ?>signatures');
sig.accountSignatures = [];
sig.actualTextSignature = '';
sig.actualHtmlSignature = '';

</script>
<div id="<?php echo $genid ?>main_div" style="height:100%; overflow-y: hidden;">
<form style="height:100%;background-color:white;" id="<?php echo $genid ?>form" name="frmMail"  class="internalForm" action="<?php echo $mail->getSendMailUrl()?>" method="post"  onsubmit="return og.mailSetBody('<?php echo $genid ?>')">
<input type="hidden" name="instanceName" value="<?php echo $genid?>" />
<input type="hidden" name="mail[body]" value="" />
<input type="hidden" name="mail[isDraft]" id="<?php echo $genid ?>isDraft" value="true" />
<input type="hidden" name="mail[id]" id="<?php echo $genid ?>id" value="<?php echo  array_var($mail_data, 'id') ?>" />
<input type="hidden" name="mail[hf_id]" id="<?php echo $genid ?>hf_id" value="<?php echo $genid ?>id" />
<input type="hidden" name="mail[isUpload]" id="<?php echo $genid ?>isUpload" value="false" />
<input type="hidden" name="mail[autosave]" id="<?php echo $genid ?>autosave" value="false" />
<input type="hidden" name="mail[link_to_objects]" id="<?php echo $genid ?>link_to_objects" value="<?php echo $link_to_objects?>" />

<input type="hidden" name="mail[conversation_id]" value="<?php echo array_var($mail_data, 'conversation_id') ?>" />
<input type="hidden" name="mail[in_reply_to_id]" value="<?php echo array_var($mail_data, 'in_reply_to_id') ?>" />
<input type="hidden" name="mail[original_id]" value="<?php echo array_var($mail_data, 'original_id') ?>" />
<input type="hidden" name="mail[last_mail_in_conversation]" value="<?php echo array_var($mail_data, 'last_mail_in_conversation') ?>" />
<input type="hidden" name="mail[pre_body_fname]" value="<?php echo array_var($mail_data, 'pre_body_fname') ?>" />
<?php 

	tpl_display(get_template_path('form_errors'));
	$addresses = Contacts::getContactEmailAddresses();
	$usedEmail = array();
    $allEmails = array();
    foreach ($addresses as $addr) {
		if (array_var($addr, 'email') && !array_var($usedEmail, array_var($addr, 'email'))) {
			$allEmails[] = trim(str_replace(",", " ", array_var($addr, 'firstname') . ' ' . array_var($addr, 'lastname') . ' <' . array_var($addr, 'email') . '>'));
			$usedEmail[array_var($addr, 'email')] = true;
		}
		if (array_var($addr, 'email2') && !array_var($usedEmail, array_var($addr, 'email2'))) {
			$allEmails[] = trim(str_replace(",", " ", array_var($addr, 'firstname') . ' ' . array_var($addr, 'lastname') . ' <' . array_var($addr, 'email2') . '>'));
			$usedEmail[array_var($addr, 'email2')] = true;
		}
    	if (array_var($addr, 'email3') && !array_var($usedEmail, array_var($addr, 'email3'))) {
			$allEmails[] = trim(str_replace(",", " ", array_var($addr, 'firstname') . ' ' . array_var($addr, 'lastname') . ' <' . array_var($addr, 'email3') . '>'));
			$usedEmail[array_var($addr, 'email3')] = true;
    	}
	}
    $addresses = Companies::getCompanyEmailAddresses();
    foreach ($addresses as $addr) {
    	if (array_var($addr, 'email')) {
    		$allEmails[] = trim(str_replace(",", " ", array_var($addr, 'name') . ' <' . array_var($addr, 'email') . '>'));
    	}
    }
	    
    $acc_id = array_var($mail_data, 'account_id', (isset($default_account) ? $default_account : $mail_accounts[0]->getId()));
    $orig_textsignature = $orig_htmlsignature = "";
    ?><script type="text/javascript">
    		sig.actualTextSignature = sig.actualHtmlSignature = "";
    </script> <?php
    foreach ($mail_accounts as $m_acc) {
    	$user_settings = MailAccountUsers::getByAccountAndUser($m_acc, logged_user());
    	if ($user_settings instanceof MailAccountUser) {
    		$sig = $user_settings->getSignature();
    	} else {
    		$sig = "";
    	}
    	$sig = nl2br($sig);
    	$htmlsig = str_replace(array("\r", "\n"), "", "<div class=\"fengoffice_signature\">$sig</div>");
    	$textsig = html_to_text($sig);
    	if ($acc_id) {
	    	if ($m_acc->getId() == $acc_id) {
	    		$orig_textsignature = $textsig;
	    		$orig_htmlsignature = $htmlsig;
	    ?><script type="text/javascript">
	    		sig.actualTextSignature = <?php echo json_encode($textsig) ?>;
	    		sig.actualHtmlSignature = <?php echo json_encode($htmlsig) ?>;
	    </script> <?php
	    	}
    	}
?>
<script type="text/javascript">
		sig.accountSignatures[sig.accountSignatures.length] = {acc:'<?php echo $m_acc->getId() ?>', htmlsig:<?php echo json_encode($htmlsig) ?>, textsig:<?php echo json_encode($textsig) ?>};
</script>
<?php } ?>

<input type="hidden" id="<?php echo $genid ?>hf_mail_contacts" value="<?php echo implode(',',$allEmails) ?>" />


<div id="textarea_new"></div>


<div class="mail" id="<?php echo $genid ?>mail_div" style="height:100%;">
<div class="coInputHeader" id="<?php echo $genid ?>header_div">
	<div class="coInputHeaderUpperRow">
  		<div class="coInputTitle"><table style="width:auto"><tr><td>
  			<?php echo lang('send mail') ?>
  		</td><td>
  			<input type="image" src="s.gif" style="width:1px;height:1px;border:0;background:transparent;cursor:default" /><!-- Opera and IE seem to use first submit button when pressing enter on a form field. If this button is not present the first submit button would be the "Send" button and so the email would be sent -->
  		</td><td style="text-align:right">
  			<?php echo submit_button(lang('send mail'), '', 
  			array('style'=>'margin-top:0px;margin-left:10px','onclick'=>"og.setHfValue('$genid', 'isDraft', 'false');og.stopAutosave('$genid'); return og.attachmentConfirmationAdded('$genid');"))?>
  		</td>
  		<td style="text-align:right">
  			<?php echo submit_button(lang('save')." ".lang('draft'), '', 
  			array('style'=>'margin-top:0px;margin-left:10px','onclick'=>"og.setHfValue('$genid', 'isDraft', 'true');og.stopAutosave('$genid');")) ?>
  		</td>
  		<td style="text-align:right">
  			<?php
  			$strDisabled = "";//array_var($mail_data, 'id') == ''?'disabled':'';
  			echo submit_button(lang('discard'), '', 
  			array('style'=>'margin-top:0px;margin-left:10px','onclick'=>"if (!confirm('" . escape_single_quotes(lang('confirm discard email')) . "')) return false; else {var p = og.getParentContentPanel(Ext.get('{$genid}form'));Ext.getCmp(p.id).setPreventClose(false);} og.setDiscard('$genid', true);og.stopAutosave('$genid');",$strDisabled=>'')) ?>
  		</td>
  		</tr></table>
  		</div>
  	</div>
  
	<div style="padding-top:10px;">
		<table style="width:95%"><tr><td style="width: 60px;">
    	<label for='mailTo'><?php echo lang('mail to')?> <span class="label_required">*</span></label>
    	</td><td>
    	<?php echo autocomplete_textarea_field('mail[to]', $mail_to, $allEmails, 30, 
    		array('class' => 'title', 'tabindex'=>'10', 'id' => $genid . 'mailTo' )); ?>
    	<?php //echo autocomplete_emailfield('mail[to]', $mail_to, $allEmails, '', 
    		//array('class' => 'title', 'tabindex'=>'10', 'id' => $genid . 'mailTo', 'style' => 'width:100%;', 'onchange' => "og.addContactsToAdd('$genid')"), false); ?>
    	</td></tr></table>
	</div>
  
 	<div id="add_mail_CC" style="padding-top:2px;">
 		<table style="width:95%"><tr><td style="width: 60px;">
    	<label for="mailCC"><?php echo lang('mail CC')?> </label>
    	</td><td>
    	<?php echo autocomplete_textarea_field('mail[cc]', array_var($mail_data, 'cc'), $allEmails, 30, 
    		array('class' => 'title', 'tabindex'=>'20', 'id' => $genid . 'mailCC' )); ?>
    	<?php //echo autocomplete_emailfield('mail[cc]', array_var($mail_data, 'cc'), $allEmails, '', 
    		//array('class' => 'title', 'tabindex'=>'20', 'id' => $genid . 'mailCC', 'style' => 'width:100%;', 'onchange' => "og.addContactsToAdd('$genid')"), false); ?>
    	</td></tr></table>
 	</div>
 	
 	<div id="add_mail_BCC" style="padding-top:2px;display:none;">
 		<table style="width:95%"><tr><td style="width: 60px;">
	    <label for="mailBCC"><?php echo lang('mail BCC')?></label>
	    </td><td>
	    <?php echo autocomplete_textarea_field('mail[bcc]', array_var($mail_data, 'bcc'), $allEmails, 30, 
    		array('class' => 'title', 'tabindex'=>'30', 'id' => $genid . 'mailBCC' )); ?>
    	<?php //echo autocomplete_emailfield('mail[bcc]', array_var($mail_data, 'bcc'), $allEmails, '', 
    		//array('class' => 'title', 'tabindex'=>'30', 'id' => $genid . 'mailBCC', 'style' => 'width:100%;', 'onchange' => "og.addContactsToAdd('$genid')"), false); ?>
    	</td></tr></table>
	</div>
 	
	<div style="padding-top:2px;">
		<table style="width:95%"><tr><td style="width: 60px;">
    	<label for='mailSubject'><?php echo lang('mail subject')?></label>
    	</td><td>
    	<?php echo text_field('mail[subject]', array_var($mail_data, 'subject'), 
    		array('class' => 'title', 'tabindex'=>'0', 'id' => $genid . 'mailSubject', 'style' => 'width:100%;', 'autocomplete' => 'off')) ?>
    	</td></tr></table>
	</div>
		
	<div>
		<?php echo render_object_custom_properties($object, 'MailContents', true) ?>
	</div>
	
	<?php $categories = array(); Hook::fire('object_edit_categories', $object, $categories); ?>
	<?php $cps = CustomProperties::getHiddenCustomPropertiesByObjectType('MailContents'); ?>
	
	<div style="padding-top:5px">
		<?php if (count($mail_accounts) > 1) { ?>
		<a href="#" class="option" onclick="og.toggleAndBolden('add_mail_account', this);og.resizeMailDiv();"><?php echo lang('mail from') ?></a> - 
		<?php } ?>
		<a href="#" class="option" onclick="og.toggleAndBolden('add_mail_BCC', this);og.resizeMailDiv();"><?php echo lang('mail BCC') ?></a> - 
		<a href="#" class="option" onclick="og.toggleAndBolden('add_mail_options', this);og.resizeMailDiv();"><?php echo lang('mail format options') ?></a> -
 		<a href="#" class="option" onclick="og.toggleAndBolden('add_mail_attachments', this);og.resizeMailDiv();"><?php echo lang('mail attachments') ?></a> -
 		<?php if (count($cps) > 0) { ?>
			<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_custom_properties_div',this);og.resizeMailDiv();"><?php echo lang('custom properties') ?></a> -
		<?php } ?>
		<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_mail_add_contacts',this);og.resizeMailDiv();"><?php echo lang('mail add contacts') ?></a>
		<?php foreach ($categories as $category) { ?>
			- <a href="#" class="option" <?php if ($category['visible']) echo 'style="font-weight: bold"'; ?> onclick="og.toggleAndBolden('<?php echo $genid . $category['name'] ?>', this);og.resizeMailDiv();"><?php echo lang($category['name'])?></a>
		<?php } ?>
	</div>

	<div id="add_mail_account" style="display:none;">
	    <label for="mailAccount"><?php echo lang('mail from')?>: 
	    <span class="desc"><?php echo lang('mail account desc') ?></span></label>
	    <?php echo render_select_mail_account('mail[account_id]',  $mail_accounts, isset($mail_data['account_id']) ? $mail_data['account_id'] : (isset($default_account) ? $default_account : (count($mail_accounts) > 0 ? $mail_accounts[0]->getId() : 0)),
	    array('id' => $genid . 'mailAccount', 'tabindex'=>'44', 'onchange' => "og.changeSignature('$genid', this.value);")) ?>
	</div>
  
	<div id="add_mail_options" style="display:none;">
		<fieldset>
	    <legend><?php echo lang('mail format options')?></legend>
	    <label><?php echo radio_field('mail[format]',$type=='html', array('id' => $genid . 'format_html','value' => 'html', 'tabindex'=>'45','onchange'=>"og.mailAlertFormat('$genid','html')")) ." ".lang('format html') ?></label>
	    <label><?php echo radio_field('mail[format]',$type=='plain', array('id' => $genid . 'format_plain','value' => 'plain', 'tabindex'=>'46', 'onchange'=>"og.mailAlertFormat('$genid','plain')"))." ".lang('format plain')  ?></label>
		</fieldset>
	</div>
	
	<div id="add_mail_attachments" style="display:none;">
 	<fieldset>
 	    <legend><?php echo lang('mail attachments')?></legend>
 	    <div id="<?php echo $genid ?>attachments"></div>
 	<a href="#" onclick="og.attachFromWorkspace('<?php echo $genid ?>')">
 		<?php echo lang('attach from workspace') ?>
 	</a>
 	<br/>
 	<a href="#" onclick="og.attachFromFileSystem(<?php echo active_or_personal_project()->getId().',\''. active_tag() . '\''; ?>, '<?php echo $genid ?>')">
 		<?php echo lang ('attach from file system') ?>
 	</a>
 	<script type="text/javascript">
 	// add attachments
 	var container = document.getElementById('<?php echo $genid ?>attachments');
	<?php
		$attachs = array_var($mail_data, 'attachs');
		if (is_array($attachs)) {
			foreach ($attachs as $att) {
				$split = explode(':', $att);
				$icon_class = 'ico-file ico-' . str_replace(".", "_", str_replace("/", "-", $split[2]));
	?>
	og.addMailAttachment(container, {
		object_id: '<?php echo $split[1] . ":" . $split[2] . ":" . $split[3] ?>',
		manager: '<?php echo $split[0] ?>',
		name: '<?php echo $split[1] ?>',
		icocls: '<?php echo $icon_class ?>'
	});
	<?php 
			}
		}
	?>
 	</script>
 	</fieldset>
 	</div>
 	
 	<div id="<?php echo $genid ?>add_mail_add_contacts" style="display:none;">
 	<fieldset id="<?php echo $genid ?>fieldset_add_contacts">
 	    <legend><?php echo lang('mail add contacts')?></legend>
 	    <label id="<?php echo $genid ?>label_no_contacts"><?php echo lang('no contacts to add')?></label>
 	    <div id="<?php echo $genid ?>add_contacts_container"></div>
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
	
	<?php if (count($cps) > 0) { ?>
		<div id='<?php echo $genid ?>add_custom_properties_div' style="display:none">
			<fieldset>
				<legend><?php echo lang('custom properties') ?></legend>
				<?php echo render_object_custom_properties($object, 'MailContents', false) ?>
			</fieldset>
		</div>	
	<?php } ?>
  
</div>
<div class="coInputSeparator"></div>
<div id="<?php echo $genid ?>mail_body_container" style="height: 100%;">
    <?php 
    $display = ($type == 'html') ? 'none' : 'block';
    $display_fck = ($type == 'html') ? 'block' : 'none';
    
    $plain_body = $draft_edit ? array_var($mail_data, 'body') : "\n\n--\n$orig_textsignature" . array_var($mail_data, 'body');

    if (!$draft_edit) {
    	$body = array_var($mail_data, 'body');
    	$idx = stripos($body, '<body');
    	if ($idx !== FALSE) {
    		$end_tag = strpos($body, '>', $idx) + 1;
    		$html_body = utf8_substr($body, 0, $end_tag) . "<br />--<br />$orig_htmlsignature" . utf8_substr($body, $end_tag); 
    	} else {
    		$html_body = "<br />--<br />$orig_htmlsignature" . $body;
    	}
    } else $html_body = array_var($mail_data, 'body');
    
    echo textarea_field('plain_body', $plain_body, array('id' => $genid . 'mailBody', 'tabindex' => '50', 
    	'style' => "display:$display;width:97%;height:94%;margin-left:1%;margin-right:1%;margin-top:1%; min-height:250px;", 
    	'onkeypress' => "if (!og.thisDraftHasChanges) og.checkMailBodyChanges();", 'autocomplete' => 'off')) ?>

    <div id="<?php echo $genid ?>ck_editor" style="display:<?php echo $display_fck ?>; width:100%; height:100%; padding:0px; margin:0px; min-height:265px;">
		<textarea style="display:none;" id="<?php echo $genid ?>ckeditor" tabindex="51"><?php echo clean($html_body) ?></textarea>
	</div>
</div>
</div>
</form>
</div>

<?php
	$loc = user_config_option('localization');
	if (strlen($loc) > 2) $loc = substr($loc, 0, 2);
?>
<script>
var focus_editor = false;
<?php if ($mail_to != "") { ?>
	focus_editor = true;
<?php } ?>
var h = document.getElementById("<?php echo $genid ?>ck_editor").offsetHeight;
try {
var editor = CKEDITOR.replace('<?php echo $genid ?>ckeditor', {
	uiColor: '#BBCCEA',
	height: (h-205) + 'px',
	enterMode: CKEDITOR.ENTER_BR,
	shiftEnterMode: CKEDITOR.ENTER_P,
	disableNativeSpellChecker: false,
	resize_enabled: false,
	customConfig: '',
	contentsCss: og.getUrl('mail', 'get_mail_css'),
	language: '<?php echo $loc ?>',
	toolbar: [
		['Bold','Italic','Underline','Strike','-',
		'Outdent','Indent','Blockquote','-',
		'JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-',
		'Link','Unlink','-',
		'Image','Table','HorizontalRule','Smiley','-',
		'Font','FontSize','-',
		'TextColor','BGColor']
	],
	skin: 'office2003',
	keystrokes: [
		[ CKEDITOR.ALT + 121 /*F10*/, 'toolbarFocus' ],
		[ CKEDITOR.ALT + 122 /*F11*/, 'elementsPathFocus' ],

		[ CKEDITOR.SHIFT + 121 /*F10*/, 'contextMenu' ],

		[ CKEDITOR.CTRL + 90 /*Z*/, 'undo' ],
		[ CKEDITOR.CTRL + 89 /*Y*/, 'redo' ],
		[ CKEDITOR.CTRL + CKEDITOR.SHIFT + 90 /*Z*/, 'redo' ],

		[ CKEDITOR.CTRL + 76 /*L*/, 'link' ],

		[ CKEDITOR.CTRL + 66 /*B*/, 'bold' ],
		[ CKEDITOR.CTRL + 73 /*I*/, 'italic' ],
		[ CKEDITOR.CTRL + 85 /*U*/, 'underline' ],

		[ CKEDITOR.CTRL + 83 /*S*/, 'save' ],

		[ CKEDITOR.ALT + 109 /*-*/, 'toolbarCollapse' ]
	],
	filebrowserImageUploadUrl : '<?php echo ROOT_URL ?>/public/assets/javascript/ckeditor/ck_upload_handler.php',
	on: {
		instanceReady: function(ev) {
			og.adjustCkEditorArea('<?php echo $genid ?>');
			var mb = Ext.getDom('<?php echo $genid ?>mailBody');
			mb.oldMailBody = og.getMailBodyFromUI('<?php echo $genid ?>');
			ev.editor.resetDirty();
			if (focus_editor) ev.editor.focus();
		},
		selectionChange: function(ev) {
			var p = og.getParentContentPanel(Ext.get('<?php echo $genid ?>ckeditor'));
			Ext.getCmp(p.id).setPreventClose(ev.editor.checkDirty());
		},
		key: function(ev) {
			var p = og.getParentContentPanel(Ext.get('<?php echo $genid ?>ckeditor'));
			Ext.getCmp(p.id).setPreventClose(ev.editor.checkDirty());
		}
	},
	removePlugins: 'scayt,contextmenu',
	entities_additional : '#39,#336,#337,#368,#369'
});
} catch (e) {
	og.err(e.message);
}
og.eventManager.addListener("email saved", function(obj) {
	var form = Ext.getDom(obj.instance + "form");
	if (!form) return;
	form['mail[id]'].value = obj.id;
	var editor = og.getCkEditorInstance(obj.instance + 'ckeditor');
	if (editor) {
		if (editor.checkDirty()) editor.resetDirty();
		var p = og.getParentContentPanel(Ext.get(obj.instance + 'form'));
		Ext.getCmp(p.id).setPreventClose(false);
	}
}, null, {replace:true});

og.attachmentConfirmationAdded = function (genid){
	 var need_confirmation = true;	 
	 var container = document.getElementById(genid + "attachments");
	 var max = container.getElementsByTagName('span').length;	 
	 if (max > 0){
		 var i = 0;
		 while (i < max && need_confirmation == true){			
			 var hidden = document.getElementById('linked_objects['+i+']');
			 var check_id = hidden.value;		 
			 check = document.getElementById(check_id);			
			 if (!check.checked){ 
				 need_confirmation = false;
			 }
			 i++;		 	
		}
		if (need_confirmation == false){		
			var answer = confirm(lang('warning create users automatically email'));
			if (answer){
				return true;
			}else{
				return false;
			}
		}		
	 }
	 return true;
}

og.resizeMailDiv = function() {
	maindiv = document.getElementById('<?php echo $genid ?>main_div');
	headerdiv = document.getElementById('<?php echo $genid ?>header_div');
	if (maindiv != null && headerdiv != null) {
		var divHeight = maindiv.offsetHeight - headerdiv.offsetHeight - 15;
		document.getElementById('<?php echo $genid ?>mail_div').style.height = divHeight + 'px';
		
		var parentTd = document.getElementById('cke_contents_<?php echo $genid ?>ckeditor');
		if (parentTd) {
			var iframe = parentTd.firstChild;
			iframe.style.height = (divHeight - 20 ) + 'px';
			parentTd.style.height = (divHeight - 20 ) + 'px';
		}
	}
}
og.resizeMailDiv();
window.onresize = og.resizeMailDiv;

if (Ext.getDom('<?php echo $genid ?>format_html') && !Ext.getDom('<?php echo $genid ?>format_html').checked) {
	var mb = Ext.getDom('<?php echo $genid ?>mailBody');
	mb.oldMailBody = og.getMailBodyFromUI('<?php echo $genid ?>');
}
if (og.preferences['draft_autosave_timeout'] > 0) {
	var mb = Ext.getDom('<?php echo $genid ?>mailBody');
	mb.genid = '<?php echo $genid ?>';
	mb.autoSaveTOut = setTimeout(function() {
		var mb = Ext.getDom('<?php echo $genid ?>mailBody');
		og.autoSaveDraft('<?php echo $genid ?>');
	}, og.preferences['draft_autosave_timeout'] * 1000);
}
if (!editor || !focus_editor) Ext.get('auto_complete_input_<?php echo $genid ?>mailTo').focus();
</script>