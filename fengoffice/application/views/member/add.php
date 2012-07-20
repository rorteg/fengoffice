<?php
	require_javascript("og/DateField.js");
	require_javascript("og/modules/addMemberForm.js");
	set_page_title(lang('members'));
	$genid = gen_id();
	if (!isset($parent_sel)) $parent_sel = 0;
	if (!isset($obj_type_sel)) $obj_type_sel = 0;
	if (!isset($member)) $member = null;
	if ($member instanceof Member && !$member->isNew()) {
		$memberId = $member->getId();
	}
	
	$object_type_selected = $obj_type_sel > 0 ? ObjectTypes::findById($obj_type_sel) : null;
	if ($member instanceof Member && !$member->isNew()) {
		$object_type_name = lang(ObjectTypes::findById($member->getObjectTypeId())->getName());
	} else {
		$object_type_name = $object_type_selected instanceof ObjectType ? lang($object_type_selected->getName()) : null;
	}
	if($member instanceof Member && !$member->isNew()) {
		$ot = ObjectTypes::findById($member->getObjectTypeId());
		$ot_name = lang($ot->getName());
		if ($member->getArchivedById() == 0) {
			add_page_action(lang('archive'), "javascript:if(confirm('".lang('confirm archive member',$ot_name)."')) og.openLink('".get_url('member', 'archive', array('id' => $member->getId()))."');", 'ico-archive-obj');
		} else {
			add_page_action(lang('unarchive'), "javascript:if(confirm('".lang('confirm unarchive member',$ot_name)."')) og.openLink('".get_url('member', 'unarchive', array('id' => $member->getId()))."');", 'ico-unarchive-obj');
		}
		add_page_action(lang('delete'), "javascript:if(confirm('".lang('confirm delete permanently', $member->getName())."')) og.openLink('".get_url('member', 'delete', array('id' => $member->getId(),'start' => true))."');", 'ico-delete');
	}
	$form_title = $object_type_name ? ($member->isNew() ? lang('new') : lang('edit')) . " $object_type_name" : lang('new member');
?>

<form 
	id="<?php echo $genid ?>submit-edit-form" 
	class="edit-member" 
	method="post" enctype="multipart/form-data"  
	action="<?php echo $member == null || $member->isNew() ? get_url('member', 'add') : get_url('member', 'edit', array("id" => $member->getId())) ?>"
<?php if ( $current_dimension->getDefinesPermissions()):?>
	onsubmit="og.userPermissions.ogPermPrepareSendData('<?php echo $genid ?>'); return true">
<?php endif;?>

	<div class="coInputHeader">
	<div class="coInputHeaderUpperRow">
		<div class="coInputTitle">
			<table style="width:535px"><tr><td>
				<?php echo $form_title ?>
			</td><td style="text-align:right">
				<?php echo submit_button($member == null || $member->isNew() ? lang('add member') : lang('save changes'),'s',array('style'=>'margin-top:0px;margin-left:10px', 'tabindex' => '5')) ?>
			</td></tr></table>
		</div>
		
		</div>
		
		<?php $categories = array(); Hook::fire('object_edit_categories', $member, $categories); ?>
		
		<div style="padding-top:10px">
			<!--  <div><span class="bold"><?php echo lang('dimension')?>:&nbsp;</span><span class="desc"><?php echo $current_dimension->getName()?></span></div> -->
			<input type="hidden" id="<?php echo $genid?>dimension_id" name="member[dimension_id]" value="<?php echo $current_dimension->getId();?>" />
			<input type="hidden" id="<?php echo $genid?>member_id" name="member[member_id]" value="<?php echo ($member == null || $member->isNew() ? '0' : $member->getId());?>" />
		</div>
	</div>
	<div class="coInputSeparator"></div>
	
	<div class="coInputMainBlock">
	
		<div>
			<?php echo label_tag(lang('name'), $genid . 'memberFormTitle', true) ?>
			<?php echo text_field('member[name]', array_var($member_data, 'name'), array('id' => $genid . 'memberFormTitle', 'class' => 'title', 'tabindex' => '1')) ?>
		</div>
	
		<div <?php echo ($member == null || $member->isNew() ? "" : 'style="display:none;"')?>>
			<?php echo label_tag(lang('type'), "", true) ?>
			<input type="hidden" id="<?php echo $genid ?>memberObjectType" name="member[object_type_id]"></input>
			<div id="<?php echo $genid ?>object_type_combo_container"></div>
		</div>
		
		<div id="<?php echo $genid?>memberParentContainer" <?php echo ($parent_sel > 0 ? "" : 'style="display:none;margin-top: 5px"')?>>
			<?php  
				
				$selected_members = array();
				if ($parent_sel) {
					$selected_members[] = $parent_sel ;
				}
				echo label_tag(lang('parent member'), "", false);
				render_single_dimension_tree($current_dimension, $genid, $selected_members, array('checkBoxes'=>false,'all_members' => true))  ;
				
			?>
				<input type="hidden" id="<?php echo $genid ?>memberParent" value="<?php echo $parent_sel; ?>" name="member[parent_member_id]"></input>
				
			<!-- 
				<div id="<?php echo $genid ?>parent_combo_container"></div>
			 -->
		</div>
		
		<div id="<?php echo $genid?>dimension_object_fields" style="display:none;"></div>
		
		<div style="margin-top:10px; display:none;" id="<?php echo $genid?>property_links">
			<span id="<?php echo $genid ?>addPropertiesLink"
				onclick="App.modules.addMemberForm.drawDimensionProperties('<?php echo $genid;?>', <?php echo $current_dimension->getId();?>);"
				class="db-ico ico-add bold" style="padding:3px 0 0 20px; cursor:pointer;"><?php echo lang('vinculations')?></span>
				
			<span id="<?php echo $genid ?>delPropertiesLink"
				onclick="App.modules.addMemberForm.deleteDimensionProperties('<?php echo $genid?>');"
				class="db-ico ico-delete bold" style="padding:3px 0 0 20px; cursor:pointer; display:none;"><?php echo lang('hide vinculations')?></span>
		</div>
		
		<div id="<?php echo $genid?>dimension_properties" style="width:750px;"></div>
		
		<div style="margin-top:10px; display:none;" id="<?php echo $genid?>restriction_links">
			<input type="hidden" id="<?php echo $genid?>ot_with_restrictions" value="" />
			<span id="<?php echo $genid ?>addRestrictionsLink"
				onclick="App.modules.addMemberForm.drawDimensionRestrictions('<?php echo $genid;?>', <?php echo $current_dimension->getId();?>);"
				class="db-ico ico-add bold" style="padding:3px 0 0 20px; cursor:pointer;"><?php echo lang('restrictions')?></span>
				
			<span id="<?php echo $genid ?>delRestrictionsLink"
				onclick="App.modules.addMemberForm.deleteDimensionRestrictions('<?php echo $genid?>');"
				class="db-ico ico-delete bold" style="padding:3px 0 0 20px; cursor:pointer; display:none;"><?php echo lang('hide restrictions')?></span>
		</div>
		<?php if ($current_dimension->getDefinesPermissions() && can_manage_security(logged_user())):?>
			<label><?php  echo lang("permissions")?></label>			
			<?php
				// Permissions (new!)
				tpl_assign('genid', $genid); 
				$this->includeTemplate(get_template_path('member_permissions_control', 'member'));
			?>
		<?php endif ;?>
		
		<div id="<?php echo $genid?>dimension_restrictions" style="width:750px;"></div>
	<?php if (isset($rest_genid)) { ?>
		<input type="hidden" name="rest_genid" value="<?php echo $rest_genid?>" />
	<?php } ?>
	<?php if (isset($prop_genid)) { ?>
		<input type="hidden" name="prop_genid" value="<?php echo $prop_genid?>" />
	<?php } ?>
	<div style="margin-top:10px;"></div>
	<?php echo submit_button($member == null || $member->isNew() ? lang('add member') : lang('save changes'),'s',array('style'=>'margin-top:0px;margin-left:10px', 'tabindex' => '5')) ?>
	</div>
</form>

<script>
	var genid = '<?php echo $genid?>';
	Ext.get('<?php echo $genid ?>memberFormTitle').focus();

	og.dimRestrictions.ot_with_restrictions = Ext.util.JSON.decode('<?php echo json_encode($ot_with_restrictions)?>');
	og.dimProperties.ot_with_properties = Ext.util.JSON.decode('<?php echo json_encode($ot_with_associations)?>');

	App.modules.addMemberForm.drawObjectTypesSelectBox(genid, Ext.util.JSON.decode('<?php echo json_encode($dimension_obj_types)?>'), 'object_type_combo_container', 'memberObjectType', '<?php echo (isset($obj_type_sel) ? $obj_type_sel : 0) ?>', '<?php echo (isset($can_change_type) && $can_change_type ? '0' : '1')?>');
	App.modules.addMemberForm.objectTypeChanged('<?php echo $obj_type_sel ?>', genid);


	var trees = Ext.getCmp(genid + "-member-chooser-panel-<?php echo $current_dimension->getId()?>").items;
	
	trees.each(function(tree, index, length) {
		tree.getSelectionModel().on("selectionchange",function(sm,node) {
			if (node.id) {
				document.getElementById(genid+"memberParent").value = node.id;
			}	
		});			
	});

	<?php if (count($selected_members) > 0) { ?>
	App.modules.addMemberForm.drawDimensionProperties('<?php echo $genid;?>', <?php echo $current_dimension->getId();?>);
	<?php } ?>
	
	og.eventManager.fireEvent("after member add render",{
		genid: genid,
		dimensionCode: '<?php echo $current_dimension->getCode()?>'
	});
	
</script>
