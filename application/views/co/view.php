<?php 
	$extra_header = isset($mail_conversation_block) && $mail_conversation_block != '';
	Hook::fire("render_page_actions", $object, $ret = 0);
	$coId = $object->getId() . get_class($object->manager()); 
	if (!isset($iconclass))
		$iconclass = "ico-large-" . $object->getObjectTypeName();
		
	$genid = gen_id();
	$date_format = user_config_option('date_format');
	if ($object instanceof ProjectDataObject && $object->canView(logged_user()) || $object instanceof User) {
		add_page_action(lang('view history'),$object->getViewHistoryUrl(),'ico-history',null,null,false);
		/*if (!$object->isTrashed())
			add_page_action(lang('share'), $object->getShareUrl(), 'ico-share');
		*/
	}
?>
<table style="width:100%" id="<?php echo $genid ?>-co"><tr>
<td>
	<table style="width:100%;border-collapse:collapse;table-layout:fixed;">
		
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
					<div title="<?php echo lang('close') ?>" onclick="og.closeView()" class="coViewClose"><?php echo lang('close') ?>&nbsp;&nbsp;X</div>
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
						
				if($object->isLinkableObject() && !$object->isTrashed())
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
					$this->includeTemplate(get_template_path($content_template[0], $content_template[1]));
				}
				else if (isset($content)) echo $content;
				?>
			</div>
			<?php if (isset($internalDivs)){
				foreach ($internalDivs as $idiv)
					echo $idiv;
			}
			
			if (!$object instanceof User) {
			?><div style="padding-bottom:15px"><b><?php echo lang('direct url') ?>:</b>
				<a id="<?php echo $genid ?>task_url" href="<?php echo($object->getViewUrl()) ?>" target="_blank"><?php echo($object->getViewUrl()) ?></a>
			</div><?php
			}
			
			$more_content_templates = array();
			Hook::fire("more_content_templates", $object, $more_content_templates);
			foreach ($more_content_templates as $ct) {
				tpl_assign('object', $object);
				$this->includeTemplate(get_template_path($ct[0], $ct[1]));
			}
			
			if ($object instanceof ApplicationDataObject)
				echo render_custom_properties($object);
			
			if ($object instanceof ProjectDataObject && $object->allowsTimeslots() && can_manage_time(logged_user()))
				echo render_object_timeslots($object, $object->getViewUrl());
				
			if ($object instanceof ProjectDataObject && $object->canView(logged_user()) || $object instanceof User) 				
				echo render_object_latest_activity($object);
			
		
			if ($object instanceof ProjectDataObject && $object->isCommentable())
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


<!-- Actions Panel -->
<td style="width:250px; padding-left:10px">
<table style="width:240px;border-collapse:collapse">
	<col width=12/><col width=216/><col width=12/>
	<tr>
		<td class="coViewHeader coViewSmallHeader" colspan=2 rowspan=2><div class="coViewPropertiesHeader"><?php echo lang("actions") ?></div></td>
		<td class="coViewTopRight"></td>
	</tr>
		
	<tr><td class="coViewRight" rowspan=2></td></tr>
	
	<tr>
		<td class="coViewBody" colspan=2> <?php
		if (count(PageActions::instance()->getActions()) > 0 ) { ?>
			<div id="actionsDialog1"> <?php
				$pactions = PageActions::instance()->getActions();
				$shown = 0;
				foreach ($pactions as $action) {
					if ($action->isCommon) {
				 		//if it is a common action sets the style display:block
				 		if ($action->getTarget() != '') { ?>
	   				    	<a id="<?php $atrib = $action->getAttributes(); echo array_var($atrib,'id'); ?>" style="display:block" class="coViewAction <?php echo $action->getName()?>" href="<?php echo $action->getURL()?>" target="<?php echo $action->getTarget()?>"> <?php echo $action->getTitle(); ?></a>
				 		<?php } else { ?>
							<a id="<?php $atrib = $action->getAttributes(); echo array_var($atrib,'id'); ?>" style="display:block" class="<?php $attribs = $action->getAttributes(); echo isset($attribs["download"]) ? '':'internalLink' ?> coViewAction <?php echo $action->getName()?>" href="<?php echo $action->getURL()?>"> <?php echo $action->getTitle(); ?></a>
						<?php }
				 		$shown++;
					} //if
				}//foreach ?>
			</div> <?php
			
			$count = count($pactions);
			$hidden = false;
			foreach ($pactions as $action) {
				if (!$action->isCommon) {
					if (!$hidden && $shown >= 4 && $shown + 1 < $count) {
						// if 4 actions have already been shown and there's more than one action left to show, hide the rest ?>
			 			<div id="otherActions<?php echo $genid ?>" style="display:none"><?php
			 			$hidden = true;
			 		}
			 		
			 		if ($action->getTarget() != '') { ?>
						<a style="display:block" class="coViewAction <?php echo $action->getName()?>" href="<?php echo $action->getURL()?>" target="<?php echo $action->getTarget()?>"> <?php echo $action->getTitle() ?></a>
					<?php } else { ?>
						<a style="display:block" class="<?php $attribs = $action->getAttributes(); echo isset($attribs["download"]) ? '':'internalLink' ?> coViewAction <?php echo $action->getName()?>" href="<?php echo $action->getURL()?>"> <?php echo $action->getTitle() ?></a>
					<?php }
			    	$shown++;
				}
			} // foreach
			if ($hidden) {
				// close the hidden div and show the "More" link ?>
				</div>											
				<a id="moreOption<?php echo $genid; ?>" style="display:block" class="coViewAction" href="javascript: og.showMoreActions('<?php echo $genid ?>')">
			    	<?php echo lang('more').'...' ?>
			    </a> <?php 
			}
		 }
		 PageActions::clearActions(); ?>
		</td>
	</tr>
	<tr>
		<td class="coViewBottomLeft" style="width:12px;">&nbsp;</td>
		<td class="coViewBottom" style="width:216px;"></td>
		<td class="coViewBottomRight" style="width:12px;">&nbsp;&nbsp;</td>
	</tr>
</table>



<!-- Properties Panel -->
<table style="width:240px">
	<col width=12/><col width=216/><col width=12/>
	<tr>
		<td class="coViewHeader coViewSmallHeader" colspan=2 rowspan=2><div class="coViewPropertiesHeader"><?php echo lang("properties") ?></div></td>
		<td class="coViewTopRight"></td>
	</tr>
		
	<tr><td class="coViewRight" rowspan=2></td></tr>
	
	<tr>
		<td class="coViewBody" colspan=2>
			<div class="prop-col-div" style="width:200;">
				<span style="color:#333333;font-weight:bolder;"><?php echo lang('unique id') ?>:&nbsp;</span><?php echo $object->getUniqueObjectId() ?>
			</div>
		<?php 
		if ($object instanceof ProjectDataObject) {
			$user_object_workspaces = $object->getWorkspaces(logged_user()->getWorkspacesQuery());
		}
		
		$has_wss = $object instanceof ProjectDataObject && (is_array($user_object_workspaces) && count($user_object_workspaces) > 0);
		if ($has_wss || $object->isTaggable()) { ?>
			<div class="prop-col-div" style="width:200;">
			<?php if ($has_wss) {?>
				<span style="color:#333333;font-weight:bolder;"><?php echo lang('workspace') ?>:</span>
			<?php
				$projectLinks = array();
				foreach ($user_object_workspaces as $ws) {
					$projectLinks[] = '<span class="project-replace">' . $ws->getId() . '</span>';
				}
				echo '<br/>' . implode('<br/>', $projectLinks);
			}
		
			if ($object->isTaggable() && ($tags = project_object_tags2($object)) && $tags != '--') {?>
				<br/>
				<div style="color:#333333;font-weight:bolder;"><?php echo lang('tags') ?>:</div><?php echo $tags ?>
			<?php } ?>
		</div>
	<?php } // if ?>
	
	<?php if(false && $object->isLinkableObject() && !$object->isTrashed()) {?>
		<div id="linked_objects_in_prop_panel" class="prop-col-div" style="width:200;"><?php echo render_object_links($object, $object->canEdit(logged_user()))?></div>
	<?php } ?>
	
    <?php if ($object instanceof ProjectDataObject) { ?>
    	<?php if ($object->canEdit(logged_user())) { ?>
    	<script>
			og.show_hide_subscribers_list2 = function(manager, id, genid) {
        		og.openLink(og.getUrl('object', 'add_subscribers_list', {obj_id: id, manager: manager, genid: genid}), {
        			preventPanelLoad:true,
					onSuccess: function(data) {
					
	        			og.ExtendedDialog.show({
	
	                		html: data.current.data,
	                		height: 450,
	                		width: 685,
	                		ok_fn: function() {
	                			formy = document.getElementById(genid + "add-User-Form");
	                			var params = Ext.Ajax.serializeForm(formy);
	            				var options = {callback: function(){og.redrawSubscribers(id, manager, genid)}};
	            				options[formy.method.toLowerCase()] = params;
	            				og.openLink(formy.getAttribute('action'), options);
	            				og.ExtendedDialog.hide();        			
	            			}        			
	                	});
	                	return;
        			}
        		});
    		};
    	<?php } ?>
    	</script>
	<div class="prop-col-div" style="width:200;">
		<div id="<?php echo $genid ?>subscribers_in_prop_panel">
			<?php echo render_object_subscribers($object)?>
		</div>
		<?php if ($object->canEdit(logged_user())) {
			if (count($object->getUserWorkspaces()) > 0)
				$onclick_fn = "og.show_hide_subscribers_list2('". get_class($object->manager()) ."', '". $object->getId() ."', '". $genid ."');";
			else
				$onclick_fn = "Ext.Msg.show({
							   	title: '".lang('cant modify subscribers') . "',
							   	msg: '".lang('this object must belong to a ws to modify its subscribers') . "',
					   			icon: Ext.MessageBox.INFO });";
		?>
			<a id="<?php echo $genid.'add_subscribers_link' ?>" onclick="<?php echo $onclick_fn ?> return false;" href="#" class="ico-add internalLink" style="background-repeat: no-repeat; padding-left: 18px; padding-bottom: 3px;"><?php echo lang('modify object subscribers')?></a>
		<?php } ?>
	</div>
		
	<?php } ?>
	<div class="prop-col-div" style="border:0px;width:200;">
    	<?php if($object->getCreatedBy() instanceof User) { ?>
    		<span style="color:#333333;font-weight:bolder;">
    			<?php echo lang('created by') ?>:
			</span><br/><div style="padding-left:10px">
			<?php 
			if ($object->getCreatedBy() instanceof User){
				if (logged_user()->getId() == $object->getCreatedBy()->getId())
					$username = lang('you');
				else
					$username = clean($object->getCreatedBy()->getDisplayName());
					
				if ($object->getObjectCreationTime() && $object->getCreatedOn()->isToday()){
					$datetime = format_time($object->getCreatedOn());
					echo lang('user date today at', $object->getCreatedBy()->getCardUrl(), $username, $datetime, clean($object->getCreatedBy()->getDisplayName()));
				} else {
					$datetime = format_datetime($object->getCreatedOn(), $date_format, logged_user()->getTimezone());
					echo lang('user date', $object->getCreatedBy()->getCardUrl(), $username, $datetime, clean($object->getCreatedBy()->getDisplayName()));
				}
			} ?></div>
    	<?php } // if ?>
    	
    	<?php if($object->getObjectUpdateTime() && $object->getUpdatedBy() instanceof User && $object->getCreatedOn() != $object->getUpdatedOn()) { ?>
    		<span style="color:#333333;font-weight:bolder;">
    			<?php echo lang('modified by') ?>:
			</span><br/><div style="padding-left:10px">
			<?php 
			if ($object->getUpdatedBy() instanceof User){
					
				if (logged_user()->getId() == $object->getUpdatedBy()->getId())
					$username = lang('you');
				else
					$username = clean($object->getUpdatedByDisplayName());

				if ($object->getUpdatedOn()->isToday()){
					$datetime = format_time($object->getUpdatedOn());
					echo lang('user date today at', $object->getUpdatedBy()->getCardUrl(), $username, $datetime, clean($object->getUpdatedByDisplayName()));
				} else {
					$datetime = format_datetime($object->getUpdatedOn(), $date_format, logged_user()->getTimezone());
					echo lang('user date', $object->getUpdatedBy()->getCardUrl(), $username, $datetime, clean($object->getUpdatedByDisplayName()));
				}
			}?></div>
		<?php } // if ?>
		
		<?php
		if ($object instanceof ProjectDataObject && $object->isTrashable() && $object->getTrashedById() != 0) { ?>
    		<span style="color:#333333;font-weight:bolder;">
    			<?php echo lang('deleted by') ?>:
			</span><br/><div style="padding-left:10px">
			<?php
			$trash_user = Users::findById($object->getTrashedById());
			if ($trash_user instanceof User){
				if (logged_user()->getId() == $trash_user->getId())
					$username = lang('you');
				else
					$username = clean($trash_user->getDisplayName());

				if ($object->getTrashedOn()->isToday()){
					$datetime = format_time($object->getTrashedOn());
					echo lang('user date today at', $trash_user->getCardUrl(), $username, $datetime, clean($trash_user->getDisplayName()));
				} else {
					$datetime = format_datetime($object->getTrashedOn(), $date_format, logged_user()->getTimezone());
					echo lang('user date', $trash_user->getCardUrl(), $username, $datetime, clean($trash_user->getDisplayName()));
				}
			}
			 ?></div>
		<?php } // if ?>
		
		<?php
		if ($object instanceof ProjectDataObject && $object->isArchivable() && $object->isArchived()) { ?>
    		<span style="color:#333333;font-weight:bolder;">
    			<?php echo lang('archived by') ?>:
			</span><br/><div style="padding-left:10px">
			<?php
			$archive_user = Users::findById($object->getArchivedById());
			if ($archive_user instanceof User) {
				if (logged_user()->getId() == $archive_user->getId()) {
					$username = lang('you');
				} else {
					$username = clean($archive_user->getDisplayName());
				}

				if ($object->getArchivedOn()->isToday()) {
					$datetime = format_time($object->getArchivedOn());
					echo lang('user date today at', $archive_user->getCardUrl(), $username, $datetime, clean($archive_user->getDisplayName()));
				} else {
					$datetime = format_datetime($object->getArchivedOn(), $date_format, logged_user()->getTimezone());
					echo lang('user date', $archive_user->getCardUrl(), $username, $datetime, clean($archive_user->getDisplayName()));
				}
			}
			 ?></div>
		<?php } // if ?>
		
		<?php
		if ($object instanceof ProjectFile && $object->getLastRevision() instanceof ProjectFileRevision) { ?>
			<span style="color:#333333;font-weight:bolder;">
    			<?php echo lang('mime type') ?>:
    			<?php $mime = $object->getLastRevision()->getTypeString(); ?>
			</span><br/><div style="padding-left:10px" title="<?php echo  $mime ?>">
				<?php if (strlen($mime) > 30) {
					echo substr_utf($mime, 0, 15) . '&hellip;' . substr_utf($mime, -15);
				} else {
					echo $object->getLastRevision()->getTypeString();
				}?>
			</div>
		<?php if ($object->isCheckedOut()) { ?>
	    		<span style="color:#333333;font-weight:bolder;">
	    			<?php echo lang('checked out by') ?>:
				</span><br/><div style="padding-left:10px">
				<?php
				$checkout_user = Users::findById($object->getCheckedOutById());
				if ($checkout_user instanceof User){
					if (logged_user()->getId() == $checkout_user->getId())
						$username = lang('you');
					else
						$username = clean($checkout_user->getDisplayName());
	
					if ($object->getCheckedOutOn()->isToday()){
						$datetime = format_time($object->getCheckedOutOn());
						echo lang('user date today at', $checkout_user->getCardUrl(), $username, $datetime, clean($checkout_user->getDisplayName()));
					} else {
						$datetime = format_datetime($object->getCheckedOutOn(), $date_format, logged_user()->getTimezone());
						echo lang('user date', $checkout_user->getCardUrl(), $username, $datetime, clean($checkout_user->getDisplayName()));
					}
				}
			 ?></div>
		<?php }
			} // if ?>
	</div>
	
	<?php Hook::fire("render_object_properties", $object, $ret = 0);?>
		</td>
	</tr>
	
	<tr>
		<td class="coViewBottomLeft" style="width:12px;">&nbsp;&nbsp;</td>
		<td class="coViewBottom" style="width:216px;"></td>
		<td class="coViewBottomRight" style="width:12px;">&nbsp;&nbsp;</td>
	</tr>
	</table>
</td>
</tr></table>
<script>
og.showWsPaths('<?php echo $genid ?>-co',null,true);
</script>

