<?php add_page_action(lang('edit group'), get_url('group', 'edit_group', array('id' => $group->getId())), 'ico-edit')?>

<div class="adminGroups" style="height:100%;background-color:white">
  <div class="adminHeader">
  	<div class="adminTitle"><?php echo clean($group->getName()) ?></div>
  </div>
  <div class="adminSeparator"></div>
  <div class="adminMainBlock">
<?php
  $this->assign('users', $group->getUsers($group->getId()));
  $this->includeTemplate(get_template_path('list_users', 'administration'));
?>
</div>
</div>

