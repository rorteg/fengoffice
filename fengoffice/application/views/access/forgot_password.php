<?php set_page_title(lang('forgot password')) ?>
<form class="internalForm" action="<?php echo get_url('access', 'forgot_password') ?>" method="post">
<?php tpl_display(get_template_path('form_errors')) ?>

<?php if (!isset($_GET['instructions_sent']) || !$_GET['instructions_sent']) { ?>
  <div>
    <?php echo label_tag(lang('email address'), 'forgotPasswordEmail')  ?>
    <?php echo text_field('your_email', $your_email, array('class' => 'long', 'id' => 'forgotPasswordEmail')) ?>
  </div>
  <input type="hidden" name="submited" value="submited" />
<?php } ?>
  <div id="forgotPasswordSubmit">
  <?php if (!isset($_GET['instructions_sent']) || !$_GET['instructions_sent']) { 
  			echo submit_button(lang('change password'));
  		} ?>
  	<span>(<a class="internalLink" href="<?php echo get_url('access', 'login') ?>"><?php echo lang('login') ?></a>)</span>
  </div>
</form>