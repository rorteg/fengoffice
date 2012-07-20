<?php
    set_page_title($group->isNew() ? lang('add group') : lang('edit group'));
    administration_tabbed_navigation(ADMINISTRATION_TAB_GROUPS);
    $genid = gen_id();
    tpl_assign('genid', $genid);
?>

<form style="height:100%;background-color:white" class="internalForm" action="<?php echo $group->isNew() ? get_url('group', 'add') : $group->getEditUrl() ?>" onsubmit="javascript:og.ogPermPrepareSendData('<?php echo $genid ?>');return true;" method="post">

<div class="adminAddGroup">
  <div class="adminHeader">
  	<div class="adminHeaderUpperRow">
  		<div class="adminTitle"><table style="width:535px"><tr><td>
  			<?php echo $group->isNew() ? lang('new group') : lang('edit group') ?>
  		</td><td style="text-align:right">
  			<?php echo submit_button($group->isNew() ? lang('add group') : lang('save changes'), '', array('style'=>'margin-top:0px;margin-left:10px','id'=>$genid.'submit_btn')) ?>
  		</td></tr></table>
  		</div>
  	</div>
  	
  <div>
    <?php echo label_tag(lang('name'), 'groupFormName', true) ?>
    <?php echo text_field('group[name]', array_var($group_data, 'name'), array('class' => 'title', 'id' => $genid.'groupFormName')) ?>
  </div>
  
  </div>
  <div class="adminSeparator"></div>
  <div class="adminMainBlock">

  <fieldset class="">
	 <legend><?php echo lang('group users') ?></legend>
		<?php
			$this->includeTemplate(get_template_path('group_users_control', 'group'));
		?>
   </fieldset>
  
  <?php
	$this->includeTemplate(get_template_path('system_permissions', 'account')); 
  ?>
  <?php echo submit_button($group->isNew() ? lang('add group') : lang('save changes')) ?>
</div>
</div>
</form>
<script>
setTimeout(function() {
	document.getElementById('<?php echo $genid.'submit_btn'?>').focus();
	document.getElementById('<?php echo $genid.'groupFormName'?>').focus();
}, 1000);
</script>