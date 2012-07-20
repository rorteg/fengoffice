<?php $genid = gen_id();?>
<div class="postComment"><?php echo lang('add comment') ?></div>


<form class="internalForm" action="<?php echo Comment::getAddUrl($comment_form_object) ?>" method="post" enctype="multipart/form-data">
<?php tpl_display(get_template_path('form_errors')) ?>

<table style="width:97%"><tr><td>
  <div class="formAddCommentText">
    <?php echo textarea_field("comment[text]", '', array('class' => 'long', 'id' => 'addCommentText', 'onclick' => 'this.className = "huge";document.getElementById("pcs' . $genid . '").focus();this.focus()')) ?>
  </div>
  </td>
    <td style="padding-left:10px">

</td></tr></table>
    
<?php echo submit_button(lang('add comment'), 's', array('id' => 'pcs' . $genid)) ?>
</form>