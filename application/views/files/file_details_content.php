<?php $file = $object;
	$revisions = $file->getRevisions();
	$last_revision = $file->getLastRevision();
	$genid = gen_id();
?>

<?php if ($file->getType() == ProjectFiles::TYPE_WEBLINK) { ?>
	<b><?php echo lang('url') ?></b>: <a href="<?php echo clean($file->getUrl()) ?>" target="_blank"><?php echo clean($file->getUrl()) ?></a>
<?php } ?>

<?php if ($file->isDisplayable()) {?>
<div>
	<div style="position: relative; left:0; top: 0; width: 100%; height: 200px; background-color: white">
	<iframe style="width:100%;height:100%;border:1px solid #ddd;" src="<?php echo get_sandbox_url("feed", "display_content", array("id" => $file->getId(), "user_id" => logged_user()->getId(), "token" => logged_user()->getTwistedToken())) ?>"></iframe>
	<a class="ico-expand" style="display: block; width: 16px; height: 16px; cursor: pointer; position: absolute; right: 20px; top: 2px" title="<?php echo lang('expand') ?>" onclick="og.expandDocumentView.call(this)"></a>
	</div>
</div>
<?php } // if ?> 

<?php if ($file->getUpdatedOn() instanceof DateTimeValue) {
	$modtime = $file->getUpdatedOn()->getTimestamp();
} else {
	$modtime = $file->getCreatedOn()->getTimestamp();
}?>

<?php if(($ftype = $file->getFileType()) instanceof FileType && $ftype->getIsImage()){?>
	<div>
		<a href="<?php echo get_url('files', 'download_image', array('id' => $file->getId(), 'inline' => true, 'modtime' => $modtime)); ?>" target="_blank" title="<?php echo lang('show image in new page') ?>">
			<img id="<?php echo $genid ?>Image" src="<?php echo get_url('files', 'download_image', array('id' => $file->getId(), 'inline' => true, 'modtime' => $modtime)); ?>" style="max-width:450px;max-height:500px"/>
		</a>
	</div>
<?php }?>



<?php if (substr($file->getFilename(), -3) == '.mm') {
	require_javascript('flashobject.js');
	$flashurl = get_flash_url('visorFreemind.swf') ?>
	<div id="<?php echo $genid ?>mm">
	<script>
		var fo = new FlashObject("<?php echo $flashurl ?>", "visorFreeMind", "100%", "350px", 6, "#9999ff");
		fo.addParam("quality", "high");
		fo.addParam("bgcolor", "#ffffff");
		fo.addVariable("initLoadFile", "<?php echo $file->getDownloadUrl() ?>");
		fo.addVariable("openUrl", "_blank");
		fo.write("<?php echo $genid ?>mm");
	</script>
<?php } ?>


<?php if (count($revisions)){?>
<fieldset>
  <legend class="toggle_collapsed" onclick="og.toggle('<?php echo $genid ?>revisions',this)"><?php echo lang('revisions'); ?> (<?php echo count($revisions);?>)</legend>
<div id="<?php echo $genid ?>revisions" style="display:none">
<table class="revisions">
<?php  $counter = 0;
	foreach($revisions as $revision) { 
		$hasComments = trim($revision->getComment());
		$counter++; 
		$bgColor = $counter % 2 ? ($counter == 1? '#FFD39F' : '#DDD') : '#EEE';
?>
	<tr>
		<td rowspan=2 class='number' style="background-color:<?php echo $bgColor ?>">
			<?php if ($file->canDownload(logged_user())){?>
				<?php if ($file->getType() == ProjectFiles::TYPE_WEBLINK) {?>
				<a target="_blank" class="downloadLink" href="<?php echo $revision->getTypeString() ?>" title="<?php echo $revision->getTypeString()?>">
					<span style="font-size:12px">#</span><?php echo $revision->getRevisionNumber() ?>
				</a>
				<?php } else { ?>
				<a target="_self" class="downloadLink" href="<?php echo $revision->getDownloadUrl() ?>" title="<?php echo lang('download') . ' (' . format_filesize($revision->getFileSize()) .')'?>">
					<span style="font-size:12px">#</span><?php echo $revision->getRevisionNumber() ?>
				</a>
				<?php } ?>
			<?php } else {?>
				<span style="font-size:12px">#</span><?php echo $revision->getRevisionNumber() ?>
			<?php } // if ?>
		</td>
		<td class='line_header' style="background-color:<?php echo $bgColor ?>;">
			<?php if($revision->getCreatedBy() instanceof User) { ?>
			    <?php echo lang('file revision title long', $revision->getCreatedBy()->getCardUrl(), clean($revision->getCreatedBy()->getDisplayName()), format_datetime($revision->getCreatedOn())) ?>
			<?php } else { ?>
			    <?php echo lang('file revision title short', format_datetime($revision->getCreatedOn())) ?>
			<?php } // if ?>
		</td>
		<td class='line_header_icons' style="background-color:<?php echo $bgColor ?>;width:50px;">
			<?php if ($file->canDownload(logged_user())){?>
				<?php if ($file->getType() == ProjectFiles::TYPE_WEBLINK) { ?>
				<a target="_blank" class="downloadLink coViewAction ico-open-link" href="<?php echo $revision->getTypeString() ?>" title="<?php echo $revision->getTypeString()?>">&nbsp;</a>
				<?php } else {?>
				<a target="_self" class="downloadLink coViewAction ico-download" href="<?php echo $revision->getDownloadUrl() ?>" title="<?php echo lang('download') . ' (' . format_filesize($revision->getFileSize()) .')'?>">&nbsp;</a>
				<?php } ?>
			<?php } ?>
			<?php if ($file->canDelete(logged_user()) && !$file->isTrashed()) {?>
				<a onclick="return confirm('<?php echo escape_single_quotes(lang('confirm move to trash'))?>')" href="<?php echo $revision->getDeleteUrl() ?>" class="internalLink coViewAction ico-trash" title="<?php echo lang('move to trash')?>">&nbsp;</a>
			<?php } ?>
		</td>
	</tr>
	<tr>
		<td class='line_comments'>
			<div style="padding:2px;padding-left:6px;padding-right:6px;min-height:24px;">
		<?php if($hasComments) {?>
			 <?php echo nl2br(clean($revision->getComment()))?>
		<?php } ?>
			&nbsp;</div>
		</td>
		<td class="line_comments_icons">
			<?php if ($file->canEdit(logged_user()) && !$file->isTrashed()){?>
				<a href="<?php echo $revision->getEditUrl() ?>" class="internalLink coViewAction ico-edit" title="<?php echo lang('edit revision comment')?>">&nbsp;</a>
			<?php }?>
		</td>
	</tr>
<?php } // foreach ?>
</table>
</div>
</fieldset>
<?php } // if ?>

<?php if(($file->getDescription())) { ?>
      <fieldset><legend><?php echo lang('description')?></legend>
      <?php echo escape_html_whitespace(convert_to_links(clean($file->getDescription()))) ?>
      </fieldset>
<?php } // if ?>

<?php if(($ftype = $file->getFileType()) instanceof FileType && $ftype->getIsImage()){?>
	<script>
	function resizeImage(genid){
		var image = document.getElementById(genid + 'Image');
		if (image){
			var width = (Ext.isIE)? image.parentNode.parentNode.offsetWidth : image.parentNode.parentNode.clientWidth;
			
			image.style.maxWidth = (width - 20) + "px";
			image.style.maxHeight = (width - 20) + "px";
		}
	}
	resizeImage('<?php echo $genid ?>');
	function resizeSmallImage(genid){
		var image = document.getElementById(genid + 'Image');
		if (image){
			image.style.maxWidth = "1px";
			image.style.maxHeight = "1px";
		}
	}
	function resizeImage<?php echo $genid ?>(){
		resizeSmallImage('<?php echo $genid ?>');
		setTimeout('resizeImage("<?php echo $genid ?>")',50);
	}
	og.addDomEventHandler(window, 'resize', resizeImage<?php echo $genid ?>);
	</script>
<?php } ?>
