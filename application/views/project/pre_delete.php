<form style="height:100%;background-color:white" class="internalForm" action="<?php echo $project->getDeleteUrl() ?>&confirm=true" method="post">

<div class="adminDeleteProject">
  <div class="adminHeader">
  	<div class="adminHeaderUpperRow">
  		<div class="adminTitle"><table style="width:535px"><tr><td>
  			<?php echo "<img src='" . image_url('16x16/del.png') . "'> &nbsp;" . lang('delete workspace')  ?>
  		</td><!--td style="text-align:right">
  			<?php //echo submit_button($project->isNew() ? lang('add workspace') : lang('save changes'), 's', array('style'=>'margin-top:0px;margin-left:10px')) ?>
  		</td--></tr></table></div>
  	</div>
  	<div>
  	<?php echo lang('confirm permanent delete workspace', $project->getName()) ?>
  	</div>
  </div>
  
  <div class="adminSeparator"></div>
  <div class="adminMainBlock">
  <br> 
  <?php echo lang('workspace permamanent delete') ?>:
<ul>
<li> &bull; <?php echo lang('workspace permamanent delete messages') ?></li>
<li> &bull; <?php echo lang('workspace permamanent delete tasks') ?></li>
<li> &bull; <?php echo lang('workspace permamanent delete milestones') ?></li>
<li> &bull; <?php echo lang('workspace permamanent delete files') ?></li>
<li> &bull; <?php echo lang('workspace permamanent delete logs') ?></li>
<li> &bull; <?php echo lang('workspace permamanent delete mails') ?> </li>
</ul>
<br>
<?php $subws= count($project->getSubWorkspaces(true,logged_user())) ;
if($subws) { ?>

<?php echo lang('sub-workspaces permament delete', $subws, $project->getName()) ?> 

<br><br><?php } 

echo lang('multiples workspace object permanent delete');
?>

<br>
	<?php echo submit_button(lang('delete workspace'), 's', array('tabindex' => '2')) ?>
<br>
<?php echo lang('cancel permanent delete') ?>
</div>
</div>
</form>