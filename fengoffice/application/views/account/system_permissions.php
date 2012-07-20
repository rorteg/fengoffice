
<?php if (logged_user()->isAdminGroup()) { ?>
<table style="width:100%;"><tr><td style="padding-right:10px;width:50%;">
<fieldset class=""><legend class="toggle_expanded" onclick="og.toggle('<?php echo $genid ?>userSystemPermissions',this)"><?php echo lang("system permissions") ?></legend>
	<div id="<?php echo $genid ?>userSystemPermissions" style="display:block">
	
		<?php
			$columns = SystemPermissions::instance()->getColumns();
			$hidden_cols = array('permission_group_id', 'can_manage_billing', 'can_view_billing', 'can_task_assignee');
			foreach ($columns as $column_name) :
				if (in_array($column_name, $hidden_cols)) continue;
		?>
				<div id="<?php echo $genid ?>div_<?php echo $column_name ?>">
				<?php
					$attributes = array('id' => $genid . 'sys_perm['.$column_name.']');
					if (isset($disable_sysperm_inputs) && $disable_sysperm_inputs) {
						$attributes['onclick'] = 'return false;';
						$attributes['class'] = 'disabled';
					}
					echo checkbox_field('sys_perm['.$column_name.']', $system_permissions->getColumnValue($column_name), $attributes) ?> 
			      <label for="<?php echo $genid . 'sys_perm['.$column_name.']' ?>" class="checkbox"><?php echo lang($column_name) ?></label>
			      <a class="help-sign" href="javascript:og.toggle('<?php echo $genid . $column_name ?>_help')">?</a>
			      <div id="<?php echo $genid . $column_name ?>_help" class="permissions-help" style="display:none"><?php echo lang($column_name . ' description') ?></div>
			    </div>
		<?php 
			endforeach;
		?>
	    
		<?php
			$other_permissions = array();
			if (!is_null($user)) {
				Hook::fire('add_user_permissions', $user, $other_permissions);
			}
			foreach ($other_permissions as $perm => $perm_val) {?>
				<div id="<?php echo $genid ?>div_<?php echo $perm ?>">
			      <?php  
			        $attributes = array('id' => $genid . "sys_perm[$perm]");
					if (isset($disable_sysperm_inputs) && $disable_sysperm_inputs) {
						$attributes['onclick'] = 'return false;';
						$attributes['class'] = 'disabled';
					}
					echo checkbox_field("sys_perm[$perm]", array_var($more_permissions, $perm), $attributes) ?> 
			      <label for="<?php echo $genid . "sys_perm[$perm]" ?>" class="checkbox"><?php echo lang($perm) ?></label>
			      <a class="help-sign" href="javascript:og.toggle('<?php echo $genid ?><?php echo $perm ?>_help')">?</a>
			      <div id="<?php echo $genid ?><?php echo $perm ?>_help" class="permissions-help" style="display:none"><?php echo lang($perm.' description') ?></div>
				</div>
			<?php }
		?>
		<?php if (!isset($disable_sysperm_inputs) || !$disable_sysperm_inputs) : ?>
		<a href="#" class="internalLink ogTasksGroupAction ico-complete" onclick="checks=this.parentNode.getElementsByTagName('input'); for(i=0;i<checks.length;i++) checks[i].checked = true;"><?php echo lang('check all')?></a>
		<a href="#" class="internalLink ogTasksGroupAction ico-delete" onclick="checks=this.parentNode.getElementsByTagName('input'); for(i=0;i<checks.length;i++) checks[i].checked = false;"><?php echo lang('uncheck all')?></a>
		<?php endif; ?>
	</div>
</fieldset>


<?php 	if (is_array($all_modules_info) && count($all_modules_info) > 0) {?>
</td><td style="padding-left:10px;width:50%;">
<fieldset class=""><legend class="toggle_expanded" onclick="og.toggle('<?php echo $genid ?>userModulePermissions',this)"><?php echo lang("module permissions") ?></legend>
	<div id="<?php echo $genid ?>userModulePermissions" style="display:block">	
	<?php foreach ($all_modules_info as $mod_info) { ?>
	
		<div id="<?php echo $genid . array_var($mod_info, 'id')?>">
	      <?php  
	        $attributes = array('id' => $genid . 'mod_perm['.array_var($mod_info, 'ot').']', 'onchange' => 'if(!this.checked) og.removeAllPermissionsForObjType(\''.$genid.'\','.array_var($mod_info, 'ot').')');
			if (isset($disable_sysperm_inputs) && $disable_sysperm_inputs) {
				$attributes['onclick'] = 'return false;';
				$attributes['class'] = 'disabled';
			}
			echo checkbox_field('mod_perm['.array_var($mod_info, 'id').']', array_var($module_permissions_info, array_var($mod_info, 'id')), $attributes) ?> 
	      <label for="<?php echo $genid . 'mod_perm['.array_var($mod_info, 'ot').']' ?>" class="checkbox"><?php echo array_var($mod_info, 'name') ?></label>
		</div>
	
	<?php } ?>
	
	<?php if (!isset($disable_sysperm_inputs) || !$disable_sysperm_inputs) : ?>
		<a href="#" class="internalLink ogTasksGroupAction ico-complete" onclick="checks=this.parentNode.getElementsByTagName('input'); for(i=0;i<checks.length;i++) checks[i].checked = true;"><?php echo lang('check all')?></a>
		<a href="#" class="internalLink ogTasksGroupAction ico-delete" onclick="checks=this.parentNode.getElementsByTagName('input'); for(i=0;i<checks.length;i++) checks[i].checked = false;"><?php echo lang('uncheck all')?></a>
		<p class="desc"><?php echo lang('module permission uncheck warning')?></p>
	<?php endif; ?>
	</div>
</fieldset>
<?php 	} ?>

</td></tr></table>
<?php } ?>



<?php 
	tpl_assign('genid', $genid);
	
	tpl_assign('member_types', $permission_parameters['member_types']);
	tpl_assign('allowed_object_types_by_member_type', $permission_parameters['allowed_object_types_by_member_type']);
	tpl_assign('allowed_object_types', $permission_parameters['allowed_object_types']);
	tpl_assign('all_object_types', $permission_parameters['all_object_types']);
	tpl_assign('member_permissions', $permission_parameters['member_permissions']);
	tpl_assign('dimensions', $permission_parameters['dimensions']);
	
	$this->includeTemplate(get_template_path('user_permissions_control', 'account'));
?>