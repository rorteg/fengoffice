<?php
	require_javascript("og/Permissions.js");
	$genid = gen_id();
	
	set_page_title(lang('update permissions'));
?>
<form style="height:100%;background-color:white" action="<?php echo get_url("account", "update_permissions", array("id" => $user->getId())) ?>" class="internalForm" onsubmit="javascript:og.ogPermPrepareSendData('<?php echo $genid ?>');return true;" method="POST">
<div class="adminClients">
  <div class="adminHeader">
  	<div class="adminTitle"><?php echo lang("permissions for user", clean($user->getObjectName())) ?></div>
  	<span style="margin-left:30px;"><?php echo submit_button(lang('update permissions'), 's', array('id' => $genid.'_submit_btn')); ?></span>
  </div>
  <div class="adminSeparator"></div>
  <div class="adminMainBlock">
<input name="submitted" type="hidden" value="submitted" />


<script>
	og.guest_permission_group_ids = [];
  <?php 
  	echo "og.roles =".json_encode($roles).";";
  	echo "og.tabs_allowed=".json_encode($tabs_allowed).";";
  	foreach ($guest_groups as $gg) {
  		echo "og.guest_permission_group_ids.push(".$gg->getId().");";
  	}
  ?>
  og.addUpdatePermissionsUserTypeChange = function(genid, type) {
	  $('#'+genid+'userSystemPermissions :input').attr('checked', false);
	  $('#'+genid+'userModulePermissions :input').attr('checked', false);
	  for(i=0; i< og.roles[type].length;i++){
		  $('#'+genid+'userSystemPermissions :input[name$="sys_perm['+og.roles[type][i]+']"]').attr('checked', true);
	  }
	  for(f=0; f< og.tabs_allowed[type].length;f++){
		  $('#'+genid+og.tabs_allowed[type][f]+' :input').attr('checked', true);
	  }

	  var guest_selected = false;
	  for (j=0; j<og.guest_permission_group_ids.length; j++) {
		  if (type == og.guest_permission_group_ids[j]) {
			  guest_selected = true;
			  break;
		  }
	  }

	  og.showHideNonGuestPermissionOptions(guest_selected);  
  };
</script>
  
<div>
<?php
	$actual_user_type = PermissionGroups::instance()->findOne(array("conditions" => "id = ".$user->getUserType()));
	$can_change_type = false;
	$permission_groups = array();
	foreach($groups as $group){
		$permission_groups[] = array($group->getId(),$group->getName());
		if ($group->getId() == $actual_user_type->getId()) $can_change_type = true;
	}

	if ($can_change_type) {
		echo label_tag(lang('user type'), null, true);
		echo simple_select_box('user[type]', $permission_groups, $actual_user_type->getId(), array(
			'onchange' => "og.addUpdatePermissionsUserTypeChange('$genid', this.value)",
			'tabindex' => "300"
		));
	}
	foreach ($guest_groups as $gg) {
  		if ($actual_user_type->getId() == $gg->getId()) echo '<script>og.showHideNonGuestPermissionOptions(true);</script>';
  	}
?>
</div>

<?php
tpl_assign('genid', $genid);
tpl_assign('disable_sysperm_inputs', true);
$this->includeTemplate(get_template_path('system_permissions', 'account'));

echo submit_button(lang('update permissions'));
?>
</div>
</div>
</form>
<script>
setTimeout(function() {
	document.getElementById('<?php echo $genid.'_submit_btn'?>').focus();
}, 500);
</script>
