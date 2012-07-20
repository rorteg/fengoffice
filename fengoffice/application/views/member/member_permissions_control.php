<?php
	require_javascript("og/Permissions.js");
	if (!isset($genid)) $genid = gen_id();
	set_page_title(lang('update permissions'));
	
	$member = array_var($permission_parameters, 'member');
	$allowed_object_types = array_var($permission_parameters, 'allowed_object_types');
	$allowed_object_types_json = array_var($permission_parameters, 'allowed_object_types_json');
	$permission_groups = array_var($permission_parameters, 'permission_groups');
	$member_permissions = array_var($permission_parameters, 'member_permissions');
?>

	<input id="<?php echo $genid ?>hfPerms" type="hidden" value="<?php echo str_replace('"',"'", json_encode($member_permissions));?>"/>
	<input id="<?php echo $genid ?>hfAllowedOT" type="hidden" value="<?php echo str_replace('"',"'", json_encode($allowed_object_types_json));?>"/>
	
	<input id="<?php echo $genid ?>hfPermsSend" name="permissions" type="hidden" value=""/>
	
	<table><tr><td>
	  <?php	
	  		echo select_users_or_groups("", null, $genid . "user_selector");
	  ?>
	</td><td style="padding-left:20px">
	  <div id="<?php echo $genid ?>member_permissions" style="display:none;">
	  	<div id="<?php echo $genid ?>pg_name" style="font-weight:bold;font-size:120%;padding-bottom:5px"></div>
	  		<table>
		  	<col align=left/><col align=center/>
		  	<tr style="border-bottom:1px solid #888;margin-bottom:5px">
		  	<td style="vertical-align:middle">
		  		<span class="perm_all_checkbox_container">
					<?php echo checkbox_field($genid . 'pAll', false, array('id' => $genid . 'pAll', 'onclick' => 'og.userPermissions.ogPermAllChecked("' . $genid . '", this.checked)')) ?>
					<label style="font-weight:bold" for="<?php echo $genid ?>pAll" class="checkbox"><?php echo lang('all') ?></label>   
		  		</span>
		  	</td>
		  	<td align=center style="padding:0 10px;width:100px;"><a href="#" class="internalLink radio-title-3" onclick="og.userPermissions.ogPermSetLevel('<?php echo $genid ?>', 3);return false;"><?php echo lang('read write and delete') ?></a></td>
		  	<td align=center style="padding:0 10px;width:100px;"><a href="#" class="internalLink radio-title-2" onclick="og.userPermissions.ogPermSetLevel('<?php echo $genid ?>', 2);return false;"><?php echo lang('read and write') ?></a></td>
		  	<td align=center style="padding:0 10px;width:100px;"><a href="#" class="internalLink radio-title-1" onclick="og.userPermissions.ogPermSetLevel('<?php echo $genid ?>', 1);return false;"><?php echo lang('read only') ?></a></td>
		  	<td align=center style="padding:0 10px;width:100px;"><a href="#" class="internalLink radio-title-0" onclick="og.userPermissions.ogPermSetLevel('<?php echo $genid ?>', 0);return false;"><?php echo lang('none no bars') ?></a></td></tr>
		  	
		<?php 
			$row_cls = "";
			foreach ($allowed_object_types as $ot) {
				$row_cls = $row_cls == "" ? "altRow" : "";
				$id_suffix = $ot->getId();
				$change_parameters = '\'' . $genid . '\', ' . $ot->getId();
		?>
		  	<tr class="<?php echo $row_cls?>">
		  		<td style="padding-right:20px"><span id="<?php echo $genid.'obj_type_label'.$id_suffix?>"><?php echo lang($ot->getName()) ?></span></td>
		  		<td align=center><?php echo radio_field($genid .'rg_'.$id_suffix, false, array('onchange' => 'og.userPermissions.ogPermValueChanged('. $change_parameters .')', 'value' => '3', 'style' => 'width:16px', 'id' => $genid . 'rg_3_'.$id_suffix, 'class' => "radio_3")) ?></td>
		  		<td align=center><?php echo radio_field($genid .'rg_'.$id_suffix, false, array('onchange' => 'og.userPermissions.ogPermValueChanged('. $change_parameters .')', 'value' => '2', 'style' => 'width:16px', 'id' => $genid . 'rg_2_'.$id_suffix, 'class' => "radio_2")) ?></td>
		  		<td align=center><?php echo radio_field($genid .'rg_'.$id_suffix, false, array('onchange' => 'og.userPermissions.ogPermValueChanged('. $change_parameters .')', 'value' => '1', 'style' => 'width:16px', 'id' => $genid . 'rg_1_'.$id_suffix, 'class' => "radio_1")) ?></td>
		  		<td align=center><?php echo radio_field($genid .'rg_'.$id_suffix, false, array('onchange' => 'og.userPermissions.ogPermValueChanged('. $change_parameters .')', 'value' => '0', 'style' => 'width:16px', 'id' => $genid . 'rg_0_'.$id_suffix, 'class' => "radio_0")) ?></td>
		    </tr>
		<?php }?>
		    
		    </table>
		<!-- 
		    <br/><?php echo checkbox_field($genid . 'chk_0', false, array('id' => $genid . 'chk_0', 'onclick' => 'og.userPermissions.ogPermValueChanged("' . $genid . '")')) ?> <label style="font-weight:normal" for="<?php echo $genid ?>chk_0" class="checkbox"><?php echo lang('can assign to owners') ?></label>
		    <br/><?php echo checkbox_field($genid . 'chk_1', false, array('id' => $genid . 'chk_1', 'onclick' => 'og.userPermissions.ogPermValueChanged("' . $genid . '")')) ?> <label style="font-weight:normal" for="<?php echo $genid ?>chk_1" class="checkbox"><?php echo lang('can assign to other') ?></label>
		 -->
	  	
	  </div>
	</td></tr></table>


<script>

og.userPermissions.onUserSelect = function(genid, arguments) {
	var panel = Ext.get(genid + 'member_permissions');
	if (panel) {
		var mili = 0;
		if(panel.isVisible()) {
			panel.slideOut('r', {useDisplay:true, duration:0.4});
			mili = 100;
		}
	}
	var pg_id = arguments['id'];
	var name = arguments['n'];
 
	og.showHideNonGuestPermissionOptions(arguments['isg']);
	og.userPermissions.permissionInfo[genid].selectedPG = pg_id;
	og.userPermissions.loadPGPermissions(genid, pg_id);

	// wait for the panel slideIn to render the title
	setTimeout(function() {
		panel.slideIn('l', {useDisplay:true});
		Ext.get(genid + 'pg_name').dom.innerHTML = name;
	}, mili);
}

var genid = '<?php echo $genid ?>';
og.userPermissions.loadPermissions(genid, "user_selector");

var selector = Ext.getCmp(genid + "user_selector");
selector.on("usercheck", function(arguments, checked) {
	if (og.userPermissions.permissionInfo[genid].selectedPG != arguments['id']) {
		og.userPermissions.onUserSelect(genid, arguments);
	}
	og.userPermissions.ogPermSetLevel(genid, checked ? 3 : 0);
}, document);

selector.on("userselect", function(arguments) {
	og.userPermissions.onUserSelect(genid, arguments);
}, document);

selector.on("noneselected", function() {
	var panel = Ext.get(genid + 'member_permissions');
	if (panel) panel.slideOut('l', {useDisplay:true});
}, document);



</script>
