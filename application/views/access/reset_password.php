<?php set_page_title(lang('reset password')) ?>
<form action="<?php echo get_url('access', 'reset_password', array('t' => $token, 'uid' => $user->getId())) ?>" method="post">

<div id="reset_password_desc">
	<?php echo lang('reset password form desc', $user->getUsername()) ?>
</div>
<div id="reset_password_new">
	<?php echo label_tag(lang('new password'), 'new_password', true)?>
	<?php echo password_field('new_password', '', array('id' => 'new_password')) ?>
</div>
<div id="reset_password_repeat">
	<?php echo label_tag(lang('password again'), 'repeat_password', true)?>
	<?php echo password_field('repeat_password', '', array('id' => 'repeat_password')) ?>
</div>
<div id="reset_password_submit">
	<button type="submit"><?php echo lang('reset password')?></button>
</div>
</form>

