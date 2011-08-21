<?php 
  set_page_title(lang('groups'));
  
  if(owner_company()->canAddGroup(logged_user())) {
    add_page_action(lang('add group'), get_url('group', 'add_group'), 'ico-add');
  } // if
  
  $genid = gen_id();
?>

<div id="<?php echo $genid ?>adminContainer" class="adminGroups" style="height:100%;background-color:white">
  <div class="adminHeader">
  	<div class="adminTitle"><?php echo lang('groups') ?></div>
  </div>
  <div class="adminSeparator"></div>
  <div class="adminMainBlock">
<?php if(isset($groups) && is_array($groups) && count($groups)) { ?>
<table style="min-width:400px;margin-top:10px;">
  <tr>
    <th><?php echo lang('name') ?></th>
    <th><?php echo lang('users') ?></th>
    <th><?php echo lang('options') ?></th>
  </tr>
<?php
	$isAlt = true;
foreach($groups as $group) { 
	$isAlt = !$isAlt;
	?>
  <tr class="<?php echo $isAlt? 'altRow' : ''?>">
    <td><a class="internalLink" href="<?php echo $group->getViewUrl() ?>"><?php echo clean($group->getName()) ?></a></td>
    <td style="text-align: center"><?php echo $group->countUsers() ?></td>
<?php 
  $options = array(); 
//  if($group->canAddUser(logged_user())) {
//    $options[] = '<a class="internalLink" href="' . $group->getAddUserUrl() . '">' . lang('add user') . '</a>';
//  } // if
//  if($group->canUpdatePermissions(logged_user())) {
//    $options[] = '<a class="internalLink" href="' . $group->getUpdatePermissionsUrl() . '">' . lang('permissions') . '</a>';
//  } // if
  if($group->canEdit(logged_user())) {
    $options[] = '<a class="internalLink" href="' . $group->getEditUrl() . '">' . lang('edit') . '</a>';
  } // if
  if($group->canDelete(logged_user()) && !$group->isAdministratorGroup()) {
    $options[] = '<a class="internalLink" href="' . $group->getDeleteGroupUrl() . '" onclick="return confirm(\'' . escape_single_quotes(lang('confirm delete group')) . '\')">' . lang('delete') . '</a>';
  } // if
?>
    <td style="font-size:80%;"><?php echo implode(' | ', $options) ?></td>
  </tr>
<?php } // foreach ?>
</table>
<?php } else { ?>
<?php echo lang('no groups in company') ?>
<?php } // if ?>
</div>
</div>


<script>
	var div = document.getElementById('<?php echo $genid ?>adminContainer');
	div.parentNode.style.backgroundColor = '#FFFFFF'; 
</script>