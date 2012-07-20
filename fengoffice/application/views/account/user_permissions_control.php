<?php
require_javascript('og/modules/memberListView.js'); 
require_javascript("og/Permissions.js");

if (!isset($genid)) $genid = gen_id();
if (!isset($name)) $name = 'permissions';

?>

<input id="<?php echo $genid ?>hfPerms" type="hidden" value="<?php echo str_replace('"',"'", json_encode($member_permissions));?>"/>
<input id="<?php echo $genid ?>hfAllowedOT" type="hidden" value="<?php echo str_replace('"',"'", json_encode($allowed_object_types));?>"/>
<input id="<?php echo $genid ?>hfAllowedOTbyMemType" type="hidden" value="<?php echo str_replace('"',"'", json_encode($allowed_object_types_by_member_type));?>"/>
<input id="<?php echo $genid ?>hfMemTypes" type="hidden" value="<?php echo str_replace('"',"'", json_encode($member_types));?>"/>

<input id="<?php echo $genid ?>hfPermsSend" name="<?php echo $name ?>" type="hidden" value=""/>

<?php foreach ($dimensions as $dimension) {
		if ( $dimension->getOptions(1) && isset($dimension->getOptions(1)->hidden) && $dimension->getOptions(1)->hidden ) continue;
?>
<fieldset>
	<legend><span class="og-task-expander toggle_expanded" style="padding-left:20px;" title="<?php echo lang('expand-collapse') ?>" id="<?php echo $genid?>expander<?php echo $dimension->getId()?>"
				onclick="og.editMembers.expandCollapseDim('<?php echo $genid?>dimension<?php echo $dimension->getId()?>', false);"><?php echo $dimension->getName()?></span></legend>
	<div id="<?php echo $genid?>dimension<?php echo $dimension->getId()?>">
	<table><tr><td>
  <?php	
  		echo render_single_dimension_tree($dimension, $genid, null, array('all_members' => true, 'select_root' => true));
  ?>
  </td><td style="padding-left:20px">
  <div id="<?php echo $genid ?>member_permissions<?php echo $dimension->getId() ?>" style="display:none;">
  <div id="<?php echo $genid . "_" . $dimension->getId()?>member_name" style="font-weight:bold;font-size:120%;padding-bottom:5px"></div>
  
  <table>
  	<col align=left/><col align=center/>
  	<tr style="border-bottom:1px solid #888;margin-bottom:5px">
  	<td style="vertical-align:middle">
  		<span class="perm_all_checkbox_container">
			<?php echo checkbox_field($genid . $dimension->getId() . 'pAll', false, array('id' => $genid . $dimension->getId() .'pAll', 'onclick' => 'og.ogPermAllChecked("' . $genid . '", '. $dimension->getId() .', this.checked)')) ?> <label style="font-weight:bold" for="<?php echo $genid .$dimension->getId() ?>pAll" class="checkbox"><?php echo lang('all') ?></label>   
  		</span>
  	</td>
  	<td align=center style="padding-left:10px;padding-right:10px;width:100px;"><a href="#" class="internalLink radio-title-3" onclick="og.ogPermSetLevel('<?php echo $genid ?>', '<?php echo $dimension->getId() ?>', 3);return false;"><?php echo lang('read write and delete') ?></a></td>
  	<td align=center style="padding-left:10px;padding-right:10px;width:100px;"><a href="#" class="internalLink radio-title-2" onclick="og.ogPermSetLevel('<?php echo $genid ?>', '<?php echo $dimension->getId() ?>', 2);return false;"><?php echo lang('read and write') ?></a></td>
  	<td align=center style="padding-left:10px;padding-right:10px;width:100px;"><a href="#" class="internalLink radio-title-1" onclick="og.ogPermSetLevel('<?php echo $genid ?>', '<?php echo $dimension->getId() ?>', 1);return false;"><?php echo lang('read only') ?></a></td>
  	<td align=center style="padding-left:10px;padding-right:10px;width:100px;"><a href="#" class="internalLink radio-title-0" onclick="og.ogPermSetLevel('<?php echo $genid ?>', '<?php echo $dimension->getId() ?>', 0);return false;"><?php echo lang('none no bars') ?></a></td></tr>
  	
<?php 
	$row_cls = "";
	foreach ($all_object_types as $ot) {
		if (!in_array($ot->getId(), $allowed_object_types[$dimension->getId()])) continue;
		$row_cls = $row_cls == "" ? "altRow" : "";
		$id_suffix = $dimension->getId() . "_" . $ot->getId();
		$change_parameters = '\'' . $genid . '\', ' . $dimension->getId() . ', ' . $ot->getId();
?>
  	<tr class="<?php echo $row_cls?>">
  		<td style="padding-right:20px"><span id="<?php echo $genid.'obj_type_label'.$id_suffix?>"><?php echo lang($ot->getName()) ?></span></td>
  		<td align=center><?php echo radio_field($genid .'rg_'.$id_suffix, false, array('onchange' => 'og.ogPermValueChanged('. $change_parameters .')', 'value' => '3', 'style' => 'width:16px', 'id' => $genid . 'rg_3_'.$id_suffix, 'class' => "radio_3")) ?></td>
  		<td align=center><?php echo radio_field($genid .'rg_'.$id_suffix, false, array('onchange' => 'og.ogPermValueChanged('. $change_parameters .')', 'value' => '2', 'style' => 'width:16px', 'id' => $genid . 'rg_2_'.$id_suffix, 'class' => "radio_2")) ?></td>
  		<td align=center><?php echo radio_field($genid .'rg_'.$id_suffix, false, array('onchange' => 'og.ogPermValueChanged('. $change_parameters .')', 'value' => '1', 'style' => 'width:16px', 'id' => $genid . 'rg_1_'.$id_suffix, 'class' => "radio_1")) ?></td>
  		<td align=center><?php echo radio_field($genid .'rg_'.$id_suffix, false, array('onchange' => 'og.ogPermValueChanged('. $change_parameters .')', 'value' => '0', 'style' => 'width:16px', 'id' => $genid . 'rg_0_'.$id_suffix, 'class' => "radio_0")) ?></td>
    </tr>
<?php }?>
    
    </table>
    <div style="width:100%;text-align:right;">
	    <div>
	    	<a href="#" class="internalLink" onclick="og.ogPermApplyToSubmembers('<?php echo $genid ?>', '<?php echo $dimension->getId() ?>');return false;" title="<?php echo lang('apply to all submembers desc') ?>"><?php echo lang('apply to all submembers') ?></a>
	    </div>
	    <div>
	    	<a href="#" class="internalLink" onclick="og.ogPermApplyToAllMembers('<?php echo $genid ?>', '<?php echo $dimension->getId() ?>');return false;" title="<?php echo lang('apply to all members desc') ?>"><?php echo lang('apply to all members') ?></a>
	    </div>
    </div>
    </div>
   </td></tr></table>
   </div>
</fieldset>

<script>
	if (!og.permissionDimensions) og.permissionDimensions = [];
	og.permissionDimensions.push(<?php echo $dimension->getId() ?>);
	
	var memberChoosers = Ext.getCmp('<?php echo "$genid-member-chooser-panel-".$dimension->getId()?>').items;
	if (memberChoosers) {
		memberChoosers.each(function(item, index, length) {
			item.on('click', function(member) {
				var panel = Ext.get('<?php echo $genid ?>member_permissions<?php echo $dimension->getId() ?>');
				if (!isNaN(member.id)) {
					var mili = 0;
					if(panel.isVisible()) {
						panel.slideOut('r', {useDisplay:true, duration:0.4});
						mili = 100;
					}
					
					// wait for the panel slideIn to render the title
					setTimeout(function() {
						og.loadMemberPermissions('<?php echo $genid ?>', <?php echo $dimension->getId() ?>, member.id);
						og.permissionInfo['<?php echo $genid ?>'].selectedMember = member.id;
						panel.slideIn('l', {useDisplay:true});
						Ext.get('<?php echo $genid . "_" . $dimension->getId()?>member_name').dom.innerHTML = member.text;
					}, mili);
				} else {
					// All selected
					panel.slideOut('l', {useDisplay:true});
				}
			});
		});
	}

</script>
<?php }?>

<script>
	og.ogLoadPermissions('<?php echo $genid ?>');
</script>