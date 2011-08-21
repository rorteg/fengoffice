<?php set_page_title(lang('change password')) ?>
<form action="<?php echo get_url('access', 'change_password', array('id' => $user_id)) ?>" method="post">

<div style="color:red;">
<?php echo $reason ?>
</div><br/>

<?php tpl_display(get_template_path('form_errors')) ?>

  <div id="changePasswordDiv">
    <label for="username"><?php echo lang('username') ?>:</label>
    <?php echo text_field('changePassword[username]', null, array('id' => 'username', 'class' => 'medium')) ?>
  </div>
  <div id="repeatPasswordDiv">
    <label for="oldPassword"><?php echo lang('old password') ?>:</label>
    <?php echo password_field('changePassword[oldPassword]', null, array('id' => 'oldPassword', 'class' => 'medium')) ?>
  </div>
  <div class="clean"></div>
  <div id="changePasswordDiv">
    <label for="newPassword"><?php echo lang('new password') ?>(*):</label>
    <?php echo password_field('changePassword[newPassword]', null, array('id' => 'newPassword', 'class' => 'medium')) ?>
  </div>
  <div id="repeatPasswordDiv">
    <label for="repeatPassword"><?php echo lang('password again') ?>:</label>
    <?php echo password_field('changePassword[repeatPassword]', null, array('id' => 'repeatPassword', 'class' => 'medium')) ?>
  </div>
  <div class="clean"></div>
 
  <div id="loginSubmit"><?php echo submit_button(lang('change')) ?></div>
  <br/>
  
  <?php 
  	$min_pass_length = config_option('min_password_length', 0);	
  	if($min_pass_length > 0) echo '*'.lang('password invalid min length', $min_pass_length).'<br/>';
  	$pass_numbers = config_option('password_numbers', 0);			
	if($pass_numbers > 0) echo '*'.lang('password invalid numbers', $pass_numbers).'<br/>';
	$pass_uppercase = config_option('password_uppercase_characters', 0);		
	if($pass_uppercase) echo '*'.lang('password invalid uppercase', $pass_uppercase).'<br/>';
	$pass_metacharacters = config_option('password_metacharacters', 0);		
	if($pass_metacharacters) echo '*'.lang('password invalid metacharacters', $pass_metacharacters).'<br/>';
  ?>
 
</form>