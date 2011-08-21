<?php

  set_page_title($comment->isNew() ? lang('add comment') : lang('edit comment'));
  project_tabbed_navigation(PROJECT_TAB_OVERVIEW);
  project_crumbs(array(
    $comment->isNew() ? lang('add comment') : lang('edit comment')
  )); // project_crumbs

?>
<div class="adminConfiguration" style="height:100%;background-color:white">
  <div class="adminHeader">
  	<div class="adminTitle"><?php echo $comment->isNew() ? lang('add comment') : lang('edit comment') ?></div>
  </div>
  <div class="adminSeparator"></div>
  <div class="adminMainBlock">
  
<?php if($comment->isNew()) {
		$form_action = Comment::getAddUrl($comment_form_object);
	  } else {
	  	$form_action = $comment->getEditUrl();
	  } ?>
<form class="internalForm" action="<?php echo $form_action ?>" method="post">

<?php tpl_display(get_template_path('form_errors')) ?>

<?php if($comment_form_object->columnExists('comments_enabled') && !$comment_form_object->getCommentsEnabled() && logged_user()->isAdministrator()) { ?>
<p class="error"><?php echo lang('admins can post comments on locked objects desc') ?></p>
<?php } // if ?>

  <div class="formAddCommentText">
    <?php echo label_tag(lang('text'), 'addCommentText', true) ?>
    <?php echo textarea_field("comment[text]", array_var($comment_data, 'text'), array('class' => 'huge', 'id' => 'addCommentText')) ?>
  </div>

<?php if($comment->columnExists('comments_enabled') && !$comment->getCommentsEnabled() && logged_user()->isAdministrator()) { ?>
<p class="error"><?php echo lang('admins can post comments on locked objects desc') ?></p>
<?php } // if ?>
    
    <?php echo submit_button($comment->isNew() ? lang('add comment') : lang('save changes')) ?>
</form>

	</div>
</div>