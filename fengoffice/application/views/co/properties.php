<?php $date_format = user_config_option('date_format'); ?>
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
			
	<?php if(false && $object->isLinkableObject() && !$object->isTrashed()) {?>
		<div id="linked_objects_in_prop_panel" class="prop-col-div" style="width:200;"><?php echo render_object_links($object, $object->canEdit(logged_user()))?></div>
	<?php } ?>
	
    <?php if ($object instanceof ContentDataObject && !isset($is_user)) { ?>

	<div class="prop-col-div" style="width:200;">
		<div id="<?php echo $genid ?>subscribers_in_prop_panel">
			<?php  echo render_object_subscribers($object)?>
		</div>
		<?php if ($object->canEdit(logged_user())) {
				$onclick_fn = "og.show_hide_subscribers_list('". $object->getId() ."', '". $genid ."');";
		?>
			<a id="<?php echo $genid.'add_subscribers_link' ?>" onclick="<?php echo $onclick_fn ?> return false;" href="#" class="ico-add internalLink" style="background-repeat: no-repeat; padding-left: 18px; padding-bottom: 3px;"><?php echo lang('modify object subscribers')?></a>
		<?php } ?>
	</div>
		
	<?php } ?>
	<div class="prop-col-div" style="border:0px;width:200;">
    	<?php if($object->getCreatedBy() instanceof Contact && $object->getCreatedBy()->isUser()) { ?>
    		<span style="color:#333333;font-weight:bolder;">
    			<?php echo lang('created by') ?>:
			</span><br/><div style="padding-left:10px">
			<?php 
			if ($object->getCreatedBy() instanceof Contact && $object->getCreatedBy()->isUser()){
				if (logged_user()->getId() == $object->getCreatedBy()->getId())
					$username = lang('you');
				else
					$username = clean($object->getCreatedBy()->getObjectName());
					
				if ($object->getObjectCreationTime() && $object->getCreatedOn()->isToday()){
					$datetime = format_time($object->getCreatedOn());
					echo lang('user date today at', $object->getCreatedBy()->getCardUserUrl(), $username, $datetime, clean($object->getCreatedBy()->getObjectName()));
				} else {
					$datetime = format_datetime($object->getCreatedOn(), $date_format, logged_user()->getTimezone());
					echo lang('user date', $object->getCreatedBy()->getCardUserUrl(), $username, $datetime, clean($object->getCreatedBy()->getObjectName()));
				}
			} ?></div>
    	<?php } // if ?>
    	
    	<?php if($object->getObjectUpdateTime() && $object->getUpdatedBy() instanceof Contact && $object->getCreatedBy()->isUser() && $object->getCreatedOn() != $object->getUpdatedOn()) { ?>
    		<span style="color:#333333;font-weight:bolder;">
    			<?php echo lang('modified by') ?>:
			</span><br/><div style="padding-left:10px">
			<?php 
			if ($object->getUpdatedBy() instanceof Contact && $object->getUpdatedBy()->isUser()){
					
				if (logged_user()->getId() == $object->getUpdatedBy()->getId())
					$username = lang('you');
				else
					$username = clean($object->getUpdatedByDisplayName());

				if ($object->getUpdatedOn()->isToday()){
					$datetime = format_time($object->getUpdatedOn());
					echo lang('user date today at', $object->getUpdatedBy()->getCardUserUrl(), $username, $datetime, clean($object->getUpdatedByDisplayName()));
				} else {
					$datetime = format_datetime($object->getUpdatedOn(), $date_format, logged_user()->getTimezone());
					echo lang('user date', $object->getUpdatedBy()->getCardUserUrl(), $username, $datetime, clean($object->getUpdatedByDisplayName()));
				}
			}?></div>
		<?php } // if ?>
		
		<?php
		if ($object instanceof ContentDataObject  && $object->isTrashable() && $object->getTrashedById() != 0) { ?>
    		<span style="color:#333333;font-weight:bolder;">
    			<?php echo lang('deleted by') ?>:
			</span><br/><div style="padding-left:10px">
			<?php
			$trash_user = Contacts::findById($object->getTrashedById());
			if ($trash_user instanceof Contact && $trash_user->isUser()){
				if (logged_user()->getId() == $trash_user->getId())
					$username = lang('you');
				else
					$username = clean($trash_user->getObjectName());

				if ($object->getTrashedOn()->isToday()){
					$datetime = format_time($object->getTrashedOn());
					echo lang('user date today at', $trash_user->getCardUserUrl(), $username, $datetime, clean($trash_user->getObjectName()));
				} else {
					$datetime = format_datetime($object->getTrashedOn(), $date_format, logged_user()->getTimezone());
					echo lang('user date', $trash_user->getCardUserUrl(), $username, $datetime, clean($trash_user->getObjectName()));
				}
			}
			 ?></div>
		<?php } // if ?>
		
		<?php
		if ($object instanceof ContentDataObject && $object->isArchivable() && $object->isArchived()) { ?>
    		<span style="color:#333333;font-weight:bolder;">
    			<?php echo lang('archived by') ?>:
			</span><br/><div style="padding-left:10px">
			<?php
			$archive_user = Contacts::findById($object->getArchivedById());
			if ($archive_user instanceof Contact && $archive_user->isUser()) {
				if (logged_user()->getId() == $archive_user->getId()) {
					$username = lang('you');
				} else {
					$username = clean($archive_user->getObjectName());
				}

				if ($object->getArchivedOn()->isToday()) {
					$datetime = format_time($object->getArchivedOn());
					echo lang('user date today at', $archive_user->getCardUserUrl(), $username, $datetime, clean($archive_user->getObjectName()));
				} else {
					$datetime = format_datetime($object->getArchivedOn(), $date_format, logged_user()->getTimezone());
					echo lang('user date', $archive_user->getCardUserUrl(), $username, $datetime, clean($archive_user->getObjectName()));
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
				$checkout_user = Contacts::findById($object->getCheckedOutById());
				if ($checkout_user instanceof Contact && $checkout_user->isUser()){
					if (logged_user()->getId() == $checkout_user->getId())
						$username = lang('you');
					else
						$username = clean($checkout_user->getObjectName());
	
					if ($object->getCheckedOutOn()->isToday()){
						$datetime = format_time($object->getCheckedOutOn());
						echo lang('user date today at', $checkout_user->getCardUserUrl(), $username, $datetime, clean($checkout_user->getObjectName()));
					} else {
						$datetime = format_datetime($object->getCheckedOutOn(), $date_format, logged_user()->getTimezone());
						echo lang('user date', $checkout_user->getCardUserUrl(), $username, $datetime, clean($checkout_user->getObjectName()));
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
