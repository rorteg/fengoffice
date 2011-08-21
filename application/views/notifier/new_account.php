<div style="font-family: Verdana, Arial, sans-serif; font-size: 12px;">

	<?php echo lang('hi john doe', $new_account->getDisplayName()) ?>,<br><br>
	
	<?php echo lang('user created your account', $new_account->getCreatedByDisplayName()) ?>.<br><br>
	
	<?php echo lang('visit and login', "<a href='". ROOT_URL ."' target='_blank' style='font-family: Verdana, Arial, sans-serif; font-size: 12px;'>". ROOT_URL ."</a>") ?>:<br><br>
	
	&nbsp;&nbsp;&nbsp;&nbsp;<?php echo lang('username') ?>: <?php echo $new_account->getUsername() ?><br><br>
	
	&nbsp;&nbsp;&nbsp;&nbsp;<?php echo lang('password') ?>: <?php echo $raw_password ?><br><br>
	
	<br><br>

	<div style="color: #818283; font-style: italic; border-top: 2px solid #818283; padding-top: 2px; font-family: Verdana, Arial, sans-serif; font-size: 12px;">
	<?php echo lang('system notification email'); ?><br>
	<a href="<?php echo ROOT_URL; ?>" target="_blank" style="font-family: Verdana, Arial, sans-serif; font-size: 12px;"><?php echo ROOT_URL; ?></a>
	</div>

</div>