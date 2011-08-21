<form style="height:100%;background-color:white" class="internalForm" action="<?php echo $user->getDeleteUrl() ?>&confirm=true" method="post">

<div class="adminDeleteProject">
  <div class="adminHeader">
  	<div class="adminHeaderUpperRow">
  		<div class="adminTitle"><table style="width:535px"><tr><td>
  			<?php echo "<img src='" . image_url('16x16/del.png') . "'> &nbsp;" . lang('delete user')  ?>
  		</td><!--td style="text-align:right">
  			<?php //echo submit_button($project->isNew() ? lang('add workspace') : lang('save changes'), 's', array('style'=>'margin-top:0px;margin-left:10px')) ?>
  		</td--></tr></table></div>
  	</div>
  </div>
  
  <div class="adminSeparator"></div>
  <div class="adminMainBlock">
  
  <?php echo lang('confirm permanent delete user') ?>
  <ul>
	  <li> &bull; <?php  echo lang('user') . ': <b>' . $user->getUsername() . '</b>' ?></li>
	  <?php
	  $project = $user->getPersonalProject();
	  if ($project->canDelete(logged_user())) { 
		  $users_with_perosnal_project = Users::GetByPersonalProject($project->getId());
		  if (is_array($users_with_perosnal_project)&& count($users_with_perosnal_project) == 1) {  ?> 
			<li> &bull; <input style="width: 10px;padding-top: 2px;" type="checkbox" name="delete_user_ws" value="1"> <?php echo lang('user personal workspace', $project->getName())?></li>
		  <?php }else{?>
			<li> &bull; <input type="hidden" name="delete_user_ws" value="0"><?php echo lang('other user personal workspace', $project->getName()) ?></li>
		  <?php }//if
	  }
	  ?>
  </ul>
<br>
	<?php echo submit_button(lang('delete user'), 's', array('tabindex' => '2')) ?>
<br>
<?php echo lang('cancel permanent delete') ?>
</div>
</div>
</form>