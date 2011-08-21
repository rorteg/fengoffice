
<?php
 
$cantUsers = count($users);
$cantPages = round($cantUsers/5);
$page = 1;
$newPage = true;
isset($isMemberList) && $isMemberList == true ? $isUsersList = true : $isUsersList = false;
if(isset($users) && is_array($users) && $cantUsers) { ?>
<div id="usersList">
<?php $counter = 0; 
  foreach($users as $user) {
	$counter++; ?>
	<?php if ($newPage && $isUsersList ) {
		$newPage = false;
		?>
		<div id="<?php echo $page . '-' . $user->getCompanyId()?>userspage" style="display: <?php echo $counter != 1? 'none':'block' ?>" >		
	<?php }//newpage??>
  <div class="listedUser <?php echo $counter % 2 ? 'even' : 'odd' ?>">
    <div class="userAvatar"><img src="<?php echo $user->getAvatarUrl() ?>" alt="<?php echo clean($user->getDisplayName()) ?> <?php echo lang('avatar') ?>" /></div>
    <div class="userDetails">
      <div class="userName"><a class="internalLink" href="<?php echo $user->getCardUrl() ?>"><?php echo clean($user->getDisplayName()) ?></a></div> 
      
<?php if(isset($company) && $company && $company->isOwner()) { ?>
	<?php if ($user->isAdministrator()) { ?>
      	<div class="userIsAdmin"><span><?php echo lang('administrator') ?></span></div>
    <?php } ?>
	<?php if ($user->isGuest()) { ?>
      	<div class="userIsGuest"><span><?php echo lang('guest user') ?></span></div>
    <?php } ?>
      <!-- div class="userAutoAssign"><span><?php echo lang('auto assign') ?>:</span> <?php echo $user->getAutoAssign() ? lang('yes') : lang('no') ?></div -->
<?php } // if  ?>
<?php
  $options = array();
  //if($user->canEdit(logged_user())) $options[] = '<a class="internalLink" href="' . $user->getEditUrl() . '">' . lang('edit') . '</a>';
  if($user->canUpdateProfile(logged_user())) {
    $options[] = '<a class="internalLink" href="' . $user->getEditProfileUrl(/*$company->getViewUrl()*/) . '">' . lang('update profile') . '</a>';
    $options[] = '<a class="internalLink" href="' . $user->getEditPasswordUrl(/*$company->getViewUrl()*/) . '">' . lang('change password') . '</a>';
    $options[] = '<a class="internalLink" href="' . $user->getUpdateAvatarUrl(/*$company->getViewUrl()*/) . '">' . lang('update avatar') . '</a>';
  } // if
  if($user->canUpdatePermissions(logged_user())) {
    $options[] = '<a class="internalLink" href="' . $user->getUpdatePermissionsUrl(/*$company->getViewUrl()*/) . '">' . lang('permissions') . '</a>';
  } // if
  if($user->canDelete(logged_user())) {
  	//$options[] = '<a class="internalLink" href="' . $user->getDeleteUrl() . '" onclick="return confirm(\'' . escape_single_quotes(lang('confirm delete user')) . '\')">' . lang('delete') . '</a>';
    $options[] = '<a class="internalLink" href="' . get_url('user','confirm_delete_user',array('user_id'=>$user->getId())) . '">' . lang('delete') . '</a>';
  } // if
?>
      <div class="userOptions"><?php echo implode(' | ', $options) ?></div>
      <div class="clear"></div>
    </div>
  </div>
 <?php if (($counter % 5 == 0 || ($cantPages > 0 && $counter == $cantUsers)) && $isUsersList ){?> 
  	</div>
  <?php 
  	$newPage = true;
  	$page ++;
	}//if counter
	?>
<?php } // foreach ?>
</div>

<?php } else { ?>
<p><?php echo lang('no users in company') ; ?></p>
<?php } // if 
 	if(isset($company) && $company){
		
		echo  "<div style='padding:10px'><a href='" . $company->getAddUserUrl() . "' class='internalLink coViewAction ico-add'>" . lang('add user') . "</a></div>";
		if ($cantPages > 0){
			?>
			<script type="text/javascript">
					og.paginate = function (cantPages,compId){
						var html ="";
						var op = "";
						html += '<div style="height:15px;">';
						for (i=1;i<=cantPages + 1 ;i++){
								if (i==1){
										op = "-active";
									}else{
										op = "";
									}
								html += '<div class="pagination-user'+ op + '">';
								html += "<a id='userpaginationnumberlink" + compId + i + "' style='font-size:10px;' class='internalLink' href='#' onclick='og.userListPagination(" + i + "," + compId + "," + (cantPages + 1) + ",this.parentNode)' >" +
								 i + "</a>";
								html += '</div>';								
							}
						html += '</div>';
						paginateDiv = document.getElementById("companypagination"+compId);
						if (paginateDiv){
							paginateDiv.innerHTML += html;
						}
					};
					og.paginate(<?php echo $cantPages; ?>,<?php echo $company->getId();?>);
			 </script>			 
 		<?php
 		}
		
 	} ?>
