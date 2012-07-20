<?php
  set_page_title(lang('edit picture'));
?>

<form target="_blank" style='height:100%;background-color:white' action="<?php echo $contact->getUpdatePictureUrl() ?>" method="post" enctype="multipart/form-data" onsubmit="return og.submit(this)">
  
<div class="avatar">
<div class="coInputSeparator"></div>
<div class="coInputMainBlock">
  
  <fieldset>
    <legend><?php echo lang('current picture') ?></legend>
<?php if($contact->hasPicture()) { ?>
    <img src="<?php echo $contact->getPictureUrl() ?>" alt="<?php echo clean($contact->getObjectName()) ?> picture" />
    <p><a class="internalLink" href="<?php echo $contact->getDeletePictureUrl() ?>" onclick="return confirm('<?php echo escape_single_quotes(lang('confirm delete current picture')) ?>')"><?php echo lang('delete current picture') ?></a></p>
<?php } else { ?>
    <?php echo lang('no current picture') ?>
<?php } // if ?>
  </fieldset>
  
  <div>
    <?php echo label_tag(lang('new picture'), 'pictureFormPicture', true) ?>
    <?php echo file_field('new picture', null, array('id' => 'pictureFormPicture', 'tabindex' => '1')) ?>
<?php if($contact->hasPicture()) { ?>
    <p class="desc"><?php echo lang('new picture notice') ?></p>
<?php } // if ?>
  </div>
  
  <?php echo submit_button(lang('save'), 's', array('tabindex' => '10')) ?>
 
 </div>
 </div>
</form>
<script>
	Ext.get('pictureFormPicture').focus();
</script>