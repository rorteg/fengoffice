<?php

  set_page_title(lang('tags'));
  project_tabbed_navigation(PROJECT_TAB_TAGS);
  project_crumbs(array(
    array(lang('tags'), get_url('project', 'tags')),
    array($tag)
  ));

?>
<?php if(isset($tagged_objects) && is_array($tagged_objects) && count($tagged_objects)) { ?>
<p><?php echo lang('total objects tagged with', $total_tagged_objects, clean($tag)) ?>:</p>

<?php if(isset($tagged_objects['messages']) && is_array($tagged_objects['messages']) && count($tagged_objects['messages'])) { ?>
<h2><?php echo lang('messages') ?></h2>
<ul>
<?php foreach($tagged_objects['messages'] as $message) { ?>
  <li><a class="internalLink" href="<?php echo $message->getViewUrl() ?>"><?php echo clean($message->getTitle()) ?></a>
<?php if($message->getCreatedBy() instanceof User) { ?>
  <span class="desc">- <?php echo lang('posted on by', format_date($message->getUpdatedOn()), $message->getCreatedByCardUrl(), clean($message->getCreatedByDisplayName())) ?></span>
<?php } // if ?>
  </li>
<?php } // foreach?>
</ul>
<?php } // if ?>

<?php if(isset($tagged_objects['milestones']) && is_array($tagged_objects['milestones']) && count($tagged_objects['milestones'])) { ?>
<h2><?php echo lang('milestones') ?></h2>
<ul>
<?php foreach($tagged_objects['milestones'] as $milestone) { ?>
  <li>
    <a class="internalLink" href="<?php echo $milestone->getViewUrl() ?>"><?php echo clean($milestone->getName()) ?></a>
<?php if($milestone->getAssignedTo() instanceof ApplicationDataObject) { ?>
    <span class="desc">- <?php echo lang('milestone assigned to', clean($milestone->getAssignedTo()->getObjectName())) ?></span>
<?php } // if ?>
<?php if($milestone->isCompleted()) { ?>
    <img src="<?php echo icon_url('ok.gif') ?>" alt="<?php echo lang('completed milestone') ?>" title="<?php echo lang('completed milestone') ?>" />
<?php } ?>
  </li>
<?php } // foreach?>
</ul>
<?php } // if ?>

<?php if(isset($tagged_objects['task_lists']) && is_array($tagged_objects['task_lists']) && count($tagged_objects['task_lists'])) { ?>
<h2><?php echo lang('task lists') ?></h2>
<ul>
<?php foreach($tagged_objects['task_lists'] as $task_list) { ?>
  <li>
    <a class="internalLink" href="<?php echo $task_list->getViewUrl() ?>"><?php echo clean($task_list->getObjectName()) ?></a>
<?php if($task_list->isCompleted()) { ?>
    <img src="<?php echo icon_url('ok.gif') ?>" alt="<?php echo lang('completed task list') ?>" title="<?php echo lang('completed task list') ?>" />
<?php } ?>
  </li>
<?php } // foreach?>
</ul>
<?php } // if ?>

<?php if(isset($tagged_objects['files']) && is_array($tagged_objects['files']) && count($tagged_objects['files'])) { ?>
<h2><?php echo lang('files') ?></h2>
<ul>
<?php foreach($tagged_objects['files'] as $file) { ?>
  <li><a class="internalLink" href="<?php echo $file->getDetailsUrl() ?>"><?php echo clean($file->getFilename()) ?></a> <span class="desc">(<?php echo format_filesize($file->getFilesize()) ?>)</span></li>
<?php } // foreach?>
</ul>
<?php } // if ?>

<?php if(isset($tagged_objects['contacts']) && is_array($tagged_objects['contacts']) && count($tagged_objects['contacts'])) { ?>
<h2><?php echo lang('contacts') ?></h2>
<ul>
<?php foreach($tagged_objects['contacts'] as $role) { ?>
  <li><a class="internalLink" href="<?php echo $role->getContact()->getCardUrl() ?>"><?php echo clean($role->getContact()->getDisplayName()) ?></a>
	<?php if($role->getContact()->getCreatedBy() instanceof User) { ?>
  <span class="desc">- <?php echo lang('created by'); ?> <a class="internalLink" href="<?php echo $role->getContact()->getCreatedByCardUrl() ?>"><?php echo clean($role->getContact()->getCreatedByDisplayName()) ?></a></span>
<?php } // if ?>
</li>
<?php } // foreach?>
</ul>
<?php } // if ?>

<?php if(isset($tagged_objects['webpages']) && is_array($tagged_objects['webpages']) && count($tagged_objects['webpages'])) { ?>
<h2><?php echo lang('webpages') ?></h2>
<ul>
<?php foreach($tagged_objects['webpages'] as $webpage) { ?>
  <li><a href="" onclick="window.open('<?php echo $webpage->getUrl() ?>');return false;"><?php echo clean($webpage->getTitle()) ?></a>
	<?php if($webpage->getCreatedBy() instanceof User) { ?>
  <span class="desc">- <?php echo lang('created by'); ?> <a class="internalLink" href="<?php echo $webpage->getCreatedByCardUrl() ?>"><?php echo clean($webpage->getCreatedByDisplayName()) ?></a></span>
<?php } // if ?>
</li>
<?php } // foreach?>
</ul>
<?php } // if ?>

<?php } else { ?>
<p><?php echo lang('no objects tagged with', clean($tag)) ?></p>
<?php } // if ?>