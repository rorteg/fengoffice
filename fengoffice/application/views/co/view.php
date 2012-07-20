<?php 
	$extra_header = isset($mail_conversation_block) && $mail_conversation_block != '';
	Hook::fire("render_page_actions", $object, $ret = 0);
	$coId = $object->getObjectId(); 
	if (!isset($iconclass))
		$iconclass = "ico-large-" . $object->getObjectTypeName();
		
	$genid = gen_id();
	$isUser = $object instanceof Contact && $object->isUser()? true : false;
	if ($object instanceof ContentDataObject && (!$isUser && $object->canView(logged_user())) || $isUser) {
		add_page_action(lang('view history'),$object->getViewHistoryUrl(),'ico-history',null,null,false);
	}
?>
<table style="width:100%" id="<?php echo $genid ?>-co"><tr>
<td>
	<table style="width:100%;border-collapse:collapse;table-layout:fixed;min-width:600px;">
		
		<tr>
			<td class="coViewIcon" colspan=2 rowspan=2>
				<?php if (isset($image)) { echo $image; } else {?>
				<div id="<?php echo $coId; ?>_iconDiv" class="coViewIconImage <?php echo $iconclass ?>"></div>
				<?php } ?>
			</td>
			
			<td class="coViewHeader" rowspan=2>
				<div class="coViewTitleContainer">
					<div class="coViewTitle">
						<table><tr><td>
						<?php echo isset($title)? $title : lang($object->getObjectTypeName()) . ": " . clean($object->getObjectName());?>
						</td>
						
						</tr></table>
					</div>
					<div title="<?php echo lang('close') ?>" onclick="<?php echo $object instanceof Contact ? "og.onPersonClose()" : "og.closeView()"?>" class="coViewClose"><?php echo lang('close') ?>&nbsp;&nbsp;X</div>
				</div>
				<div class="coViewDesc">
					<?php if (!isset($description)) $description = "";
					Hook::fire("render_object_description", $object, $description);
					echo $description;
					?>
				</div>
			</td>
			
			<td class="coViewTopRight" style="width:12px"></td>
		</tr>
		<tr><td class="coViewRight" rowspan=3 style="width:12px"></td></tr>
		<tr><td class="coViewHeader coViewSubHeader" style="padding:10px" colspan=3>
			<?php if (isset($mail_conversation_block) && $mail_conversation_block != '') echo $mail_conversation_block;
						
				if (!isset($show_linked_objects)) $show_linked_objects = true;
				if($object->isLinkableObject() && !$object->isTrashed()&& $show_linked_objects)
					echo render_object_links_main($object, $object->canEdit(logged_user()));
				  ?>
		</td></tr>
		
		<tr>
			<td class="coViewBody" colspan=3>
			<div style="padding-bottom:15px">
				<?php 
				if (isset($content_template) && is_array($content_template)) {
					tpl_assign('object', $object);
					if (isset($variables)) {
						tpl_assign('variables', $variables);
					}
					$this->includeTemplate(get_template_path($content_template[0], $content_template[1], array_var($content_template, 2)));
				}
				else if (isset($content)) echo $content;
				?>
			</div>
			<?php if (isset($internalDivs)){
				foreach ($internalDivs as $idiv)
					echo $idiv;
			}
			
			if (!isset($is_user) && user_config_option("show_object_direct_url") ) { ?>
			<div style="padding-bottom:15px" id="<?php echo $genid?>direct_url"><b><?php echo lang('direct url') ?>:</b>
				<a id="<?php echo $genid ?>task_url" href="<?php echo($object->getViewUrl()) ?>" target="_blank"><?php echo($object->getViewUrl()) ?></a>
			</div>
			<?php } 
			
			$more_content_templates = array();
			Hook::fire("more_content_templates", $object, $more_content_templates);
			foreach ($more_content_templates as $ct) {
				tpl_assign('genid', $genid);
				tpl_assign('object', $object);
				$this->includeTemplate(get_template_path($ct[0], $ct[1], array_var($ct, 2)));
			}
			
			if ($object instanceof ContentDataObject)
				echo render_co_view_member_path($object);
			
			if ($object instanceof ApplicationDataObject)
				echo render_custom_properties($object);
			
			$logged_user_pgs = logged_user()->getPermissionGroupIds();
			if ($object instanceof ContentDataObject && $object->allowsTimeslots() && can_access_pgids($logged_user_pgs, $object->getMembers(), Timeslots::instance()->getObjectTypeId(), ACCESS_LEVEL_READ)) {
				echo render_object_timeslots($object, $object->getViewUrl());
			}
				
			$isUser = ( $object instanceof Contact && $object->isUser() );
			if ($object instanceof ContentDataObject &&	$object->canView(logged_user()) || ( $isUser && (logged_user()->getId() == get_id() || logged_user()->isAdministrator()) ) ){ 
				//echo render_object_latest_activity($object); //TODO SE rompe
			}			
			if (!$isUser && $object instanceof ContentDataObject && $object->isCommentable())
				echo render_object_comments($object, $object->getViewUrl());
			?>
			</td>
		</tr>
		<tr>
			<td class="coViewBottomLeft"></td>
			<td class="coViewBottom" colspan=2></td>
			<td class="coViewBottomRight" style="width:12px">&nbsp;</td>
		</tr>
	</table>
</td>
<td style="width:250px; padding-left:10px">
<?php
	tpl_assign('genid', $genid);
    $this->includeTemplate(get_template_path('actions', 'co')); 
    $this->includeTemplate(get_template_path('properties', 'co')); 
?>
</td>
</tr></table>