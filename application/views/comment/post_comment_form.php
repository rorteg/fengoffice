<?php $genid = gen_id();?>
<div class="postComment"><?php echo lang('add comment') ?></div>


<form class="internalForm" action="<?php echo Comment::getAddUrl($comment_form_object) ?>" method="post" enctype="multipart/form-data">
<?php tpl_display(get_template_path('form_errors')) ?>

<?php if($comment_form_object->columnExists('comments_enabled') && !$comment_form_object->getCommentsEnabled() && logged_user()->isAdministrator()) { ?>
  <p class="error"><?php echo lang('admin notice comments disabled') ?></p>
<?php } // if ?>

	<table style="width:97%"><tr><td>
  <div class="formAddCommentText">
    <?php echo textarea_field("comment[text]", '', array('class' => 'long', 'id' => 'addCommentText', 'onclick' => 'this.className = "huge";document.getElementById("pcs' . $genid . '").focus();this.focus()')) ?>
  </div>
  </td>
    <td style="padding-left:10px">
<?php if(false && logged_user()->isMemberOfOwnerCompany()) { ?>
    
    <div class="objectOption">
      <div class="optionLabel"><label><?php echo lang('private comment') ?>:</label></div>
      <div class="optionControl"><?php echo yes_no_widget('comment[is_private]', 'addCommentIsPrivate', false, lang('yes'), lang('no')) ?></div>
      <div class="optionDesc"><?php echo lang('private comment desc') ?></div>
      
<?php //if($comment_form_comment->canLinkObject(logged_user(), $comment_form_object->getProject())) { ?>
  <?php //echo render_link_to_object($comment_form_comment ); ?>
<?php //} // if ?>
    </div>
<?php } // if ?>

</td></tr></table>
    
<?php echo submit_button(lang('add comment'), 's', array('id' => 'pcs' . $genid)) ?>
</form>