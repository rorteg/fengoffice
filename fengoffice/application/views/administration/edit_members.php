<?php
	require_javascript('og/modules/memberListView.js'); 
	$genid = gen_id();
?>


<div class="adminProjects" style="height:100%;background-color:white">
  <div class="adminHeader">
  	<div class="adminTitle"><?php echo lang('dimensions') ?></div>
  </div>
  <div class="adminSeparator"></div>
  <div class="adminMainBlock">
  
<?php 
	if(is_array($dimensions) && count($dimensions) > 0) {
		foreach($dimensions as $dimension) { 
?>
			<fieldset><legend><span class="og-task-expander toggle_collapsed" style="padding-left:20px;" title="<?php echo lang('expand-collapse') ?>" id="<?php echo $genid?>expander<?php echo $dimension->getId()?>"
				onclick="og.editMembers.expandCollapseDim('<?php echo $genid?>dimension<?php echo $dimension->getId()?>', true);">
				<?php echo $dimension->getName() ?></span></legend>
				<div id="<?php echo $genid?>dimension<?php echo $dimension->getId()?>" style="display:none;">
<?php
			$dim_members = array_var($members, $dimension->getId());
			$alt = true;
			if (is_array($dim_members)) {
				foreach ($dim_members as $mem) {/* @var $mem Member */
					$alt = !$alt;
					$indent = 16 * $mem->getDepth();
?>
						<div style="margin-left:<?php echo $indent?>px;width:<?php echo 800 - $indent?>px;" id="abm-members-item-container-<?php echo $mem->getId() ?>"
							class="<?php echo ($mem->getArchivedById() > 0 ? "member-item-archived" : "")?><?php echo ($alt ? " edit-mem-alt" : "")?>"
							onmouseover="og.editMembers.showHideOptions('<?php echo $genid?>actions<?php echo $mem->getId()?>', <?php echo $mem->getId()?>, true);" 
							onmouseout="og.editMembers.showHideOptions('<?php echo $genid?>actions<?php echo $mem->getId()?>', <?php echo $mem->getId()?>, false);">

							<table style="width:100%;"><tr><td style="width:500px;">
								<span class="coViewAction <?php echo $mem->getIconClass()?>">&nbsp;</span>
								<span class="abm-members-name"><?php echo $mem->getName() . ($mem->getArchivedById() > 0 ? " (".lang('archived').")" : "");?></span>
							</td><td>
								<span style="float:right;opacity:0.25;filter:alpha(opacity=25);font-weight:normal;" id="<?php echo $genid?>actions<?php echo $mem->getId()?>">
									<a href="<?php echo get_url('member', 'edit', array('id' => $mem->getId()))?>" class="db-ico ico-edit" style="padding:4px 10px 0 16px;"><?php echo lang('edit')?></a>
								<?php if ($dimension->getDefinesPermissions()) : ?>	
									<a href="<?php echo get_url('member', 'edit_permissions', array('id' => $mem->getId()))?>" class="db-ico ico-permissions" style="padding:4px 10px 0 16px;"><?php echo lang('permissions')?></a>
								<?php endif; ?>
									<a href="<?php echo "javascript:if(confirm(lang('confirm delete permanently'))) og.openLink('" . get_url('member', 'delete', array('id' => $mem->getId(), 'dont_reload' => true)) ."', {callback: function(success, data){if (success) Ext.get('abm-members-item-container-".$mem->getId()."').remove()}});"?>" 
										class="db-ico ico-delete" style="padding:4px 0 0 16px;"><?php echo lang('delete')?></a>
								</span>
							</td></tr></table>
						</div>
<?php			}
			} ?>
				<div style="margin-top:10px;"><a class="db-ico ico-add" style="padding:3px 0 0 20px;" href="<?php echo get_url('member', 'add', array("dim_id" => $dimension->getId()))?>">
					<?php echo lang('add member to this dimension')?>
				</a></div>
				</div>
			</fieldset>
<?php
		}
	}
?>
  </div>
</div>
