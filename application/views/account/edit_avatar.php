<?php
    set_page_title(lang('update avatar'));
?>


<form target="_blank" style='height:100%;background-color:white' action="<?php echo $user->getUpdateAvatarUrl() ?>" method="post" enctype="multipart/form-data" onsubmit="return og.submit(this)">

<div class="avatar">
<div class="coInputSeparator"></div>
<div class="coInputMainBlock">
  
  <fieldset>
    <legend><?php echo lang('current avatar') ?></legend>
<?php if($user->hasAvatar()) { ?>
    <img src="<?php echo $user->getAvatarUrl() ?>" alt="<?php echo clean($user->getDisplayName()) ?> avatar" />
    <p><a class="internalLink" href="<?php echo $user->getDeleteAvatarUrl() ?>" onclick="return confirm('<?php echo escape_single_quotes(lang('confirm delete current avatar')) ?>')"><?php echo lang('delete current avatar') ?></a></p>
<?php } else { ?>
    <?php echo lang('no current avatar') ?>
<?php } // if ?>
  </fieldset>
  
  <div>
    <?php echo label_tag(lang('new avatar'), 'avatarFormAvatar', true) ?>
    <?php echo file_field('new avatar', null, array('id' => 'avatarFormAvatar')) ?>
<?php if($user->hasAvatar()) { ?>
    <p class="desc"><?php echo lang('new avatar notice') ?></p>
<?php } // if ?>
  </div>
  
  <?php echo submit_button(lang('update avatar')) ?>
  
</div>
</div>
</form>