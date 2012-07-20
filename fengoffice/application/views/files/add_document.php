<?php
	require_javascript("og/modules/addFileForm.js");
	$genid = gen_id();
	$comments_required = config_option('file_revision_comments_required');
	
	$loc = user_config_option('localization');
	if (strlen($loc) > 2) $loc = substr($loc, 0, 2);
?>

<form class="internalForm" style="height:100%; overflow:hidden;" id="<?php echo $genid ?>form" action="<?php echo get_url('files', 'save_document') ?>" method="post" enctype="multipart/form-data" onsubmit="return og.addDocumentSubmit('<?php echo $genid ?>');">
<input type="hidden" name="instanceName" value="<?php echo $genid ?>" />
<input type="hidden" id="<?php echo $genid ?>commentsRequired" value="<?php echo config_option('file_revision_comments_required')? '1':'0'?>"/>

<?php
	tpl_display(get_template_path('form_errors'));
	if($file->isNew()) {
		$ckEditorContent = '';
	} else {
		$content = $file->getFileContentWithRealUrls() ;		
		require_once LIBRARY_PATH . "/htmlpurifier/HTMLPurifier.standalone.php";
		$ckEditorContent = HTMLPurifier::instance()->purify($content);
	}
	if (config_option('checkout_for_editing_online')) {
		ajx_on_leave("og.openLink('" . get_url('files', 'release_file', array('id' => $file->getId())) . "')");
		add_page_action(lang("checkin file"), "javascript:(function(){ var form = document.getElementById('{$genid}form'); form.checkin.value = '1'; form.new_revision_document.value = 'checked'; form.rename = false; form.onsubmit(); })()", "ico-checkin");
	}

	add_page_action(lang("save"), "javascript:(function(){ var form = document.getElementById('{$genid}form'); form.new_revision_document.value = 'checked'; form.rename = false; form.onsubmit(); })()", "save");
	add_page_action(lang("save as"), "javascript:(function(){ var form = document.getElementById('{$genid}form'); form.new_revision_document.value = 'checked'; form.rename = true; form.onsubmit(); })()", "save_as");
?>

 	<div>
		<input type="hidden" id="fileContent" name="fileContent" value="" />
		<input type="hidden" id="fileid" name="file[id]" value="<?php if (!$file->isNew()) echo $file->getId(); ?>" />
		<input type="hidden" id="filename" name="file[name]" value="<?php if (!$file->isNew()) echo clean($file->getFilename()); ?>" />
		<input type="hidden" id ="<?php echo $genid ?>comment" name="file[comment]" value="" />
		<input type="hidden" name="new_revision_document" value="checked" />
		<input type="hidden" name="checkin" value="" />
	</div>
	

	<div id="<?php echo $genid ?>ckcontainer" style="height: 100%">
		<textarea style="display:none;" cols="80" id="<?php echo $genid ?>ckeditor" name="editor" rows="10" autocomplete="off"><?php echo clean($ckEditorContent) ?></textarea>
	</div>
</form>

<script>

var h = document.getElementById("<?php echo $genid ?>ckcontainer").offsetHeight;
var editor = CKEDITOR.replace('<?php echo $genid ?>ckeditor', {
	uiColor: '#BBCCEA',
	height: (h-60) + 'px',
	enterMode: CKEDITOR.ENTER_P,
	shiftEnterMode: CKEDITOR.ENTER_BR,
	disableNativeSpellChecker: false,
	language: '<?php echo $loc ?>',
	customConfig: '',
	toolbar: [
				['Source','-','PasteText','PasteFromWord','-','Print', 'SpellChecker', 'Scayt','-',
				'Undo','Redo','-','Find','Replace','-','SelectAll', '-',
				'Format','Font','FontSize'],
				'/',
				['Bold','Italic','Underline','Strike','-','Subscript','Superscript','-',
				'NumberedList','BulletedList','-','Outdent','Indent','Blockquote','-',
				'JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-',
				'Link','Unlink','-','Maximize','-',
				'Image','Table','HorizontalRule','Smiley','SpecialChar','PageBreak','-', 
				'TextColor','BGColor','RemoveFormat']
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
			editor.resetDirty();
		},
		selectionChange: function(ev) {
			var p = og.getParentContentPanel(Ext.get('<?php echo $genid ?>ckeditor'));
			Ext.getCmp(p.id).setPreventClose(editor.checkDirty());
		}
	},
	entities_additional : '#39,#336,#337,#368,#369'
});


og.eventManager.addListener("document saved", function(obj) {
	var form = Ext.getDom(obj.instance + 'form');
	if (!form) return;
	form['file[id]'].value = obj.id;
	form['file[comment]'].value = '';

	var instName = obj.instance + 'ckeditor';
	var editor = og.getCkEditorInstance(instName);
	if (editor) {
		if (editor.checkDirty()) editor.resetDirty();
		var p = og.getParentContentPanel(Ext.get(obj.instance + 'form'));
		Ext.getCmp(p.id).setPreventClose(false);
	}
}, null, {replace:true});


og.resizeresizeCkSpaceAux = function() {
	var container = document.getElementById('<?php echo $genid ?>form');
	var parentTd = document.getElementById('cke_contents_<?php echo $genid ?>ckeditor');
	if (container && parentTd) {
		var iframe = parentTd.firstChild;
		iframe.style.height = (container.offsetHeight - 60 ) + 'px';
		parentTd.style.height = (container.offsetHeight - 60 ) + 'px';
	}
}
og.resizeCkSpace = function() {
	if (Ext.isIE) setTimeout('og.resizeresizeCkSpaceAux()', 100);
	else og.resizeresizeCkSpaceAux();
}
og.resizeCkSpace();
window.onresize = og.resizeCkSpace;

</script>
