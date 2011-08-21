<?php 
	$isUserAccount = isset($object) && $object instanceof User;
	if(isset($user) && ($user instanceof User)) { 
?>
<div class="card" style="padding:0px;">
	<?php
	$show_help_option = user_config_option('show_context_help'); 
	if ($isUserAccount && ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_account_context_help', true, logged_user()->getId())))) {?>
		<div style="padding-bottom:10px;">
		<?php 
			if($user->getId() == logged_user()->getId()){
				$hd_key = 'chelp personal account';
			}else{
				$hd_key = 'chelp user account';
				if (logged_user()->isAdministrator())
					$hd_key .= ' admin';
			}
			render_context_help($this, $hd_key, 'account');
		?>
		</div>
	<?php } ?>
		
  <div class="cardIcon"><img src="<?php echo $user->getAvatarUrl() ?>" alt="<?php echo clean($user->getDisplayName()) ?> avatar" /></div>
  <div class="cardData">
    
    <div class="cardBlock">
    	<div><span><?php echo lang('username') ?>: <?php echo clean($user->getUsername()) ?></span></div>
    	<div><span><?php echo lang('user title') ?>:</span> <?php echo $user->getTitle() ? clean($user->getTitle()) : lang('n/a') ?></div>
		<div><span><?php echo lang('company') ?>:</span> <a class="internalLink" href="<?php echo $user->getCompany()->getCardUrl() ?>"><?php echo clean($user->getCompany()->getName()) ?></a></div>
      <div><span><?php echo lang('email address') ?>:</span> <a <?php echo logged_user()->hasMailAccounts() ? 'href="' . get_url('mail', 'add_mail', array('to' => clean($user->getEmail()))) . '"' : 'target="_self" href="mailto:' . clean($user->getEmail()) . '"' ?>><?php echo clean($user->getEmail()) ?></a></div>
    </div>
  </div>
</div>

<?php if (false && isset($logs)){ 
	$genid = gen_id();
	?>
	<fieldset><legend class="toggle_expanded" onclick="og.toggle('<?php echo $genid ?>user_activity',this)"><?php echo lang('latest user activity') ?></legend>
<div id="<?php echo $genid ?>user_activity"><table><col/><col style="padding-left:10px;"/><col style="padding-left:10px"/>
		<?php foreach ($logs as $log) {
			$log_object = $log->getObject();
			if ($log_object instanceof ApplicationDataObject){?>
			<tr><td><?php 
				if ($log->getCreatedOn()->isToday()){
					$datetime = format_time($log->getCreatedOn());
					echo lang('today at', $datetime);
				} else {
					echo format_date($log->getCreatedOn());
				}?></td>
			<td><div class="db-ico ico-<?php echo $log_object->getObjectTypeName() ?>"></div></td>
			<td><a class='internalLink' href='<?php echo $log_object->getObjectUrl() ?>'><?php echo clean($log_object->getObjectName()) ?></a></td>
			<td><?php echo $log->getText() ?></td>
			</tr>
			
		<?php } // if
			} //foreach ?>
		</table></div>
	</fieldset><br/>
<?php } //if ?>
<?php } // if ?>