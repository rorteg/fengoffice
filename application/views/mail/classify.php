<?php $genid = gen_id(); ?>

<script>
function showProjectTagsDiv()
{
	var sel = document.getElementById('classifyFormProject');
	for (var i = 1; i < sel.options.length; i++)
	{
		var div = document.getElementById('ProjectTags'+sel.options[i].value);
		div.style.display =sel.options[i].selected? 'inline':'none';
	} 
}
</script>
    <form id='formClassify' name='formClassify' style='height:100%;background-color:white'  class="internalForm" action="<?php echo get_url('mail','classify', array('id'=>$email->getId())) ?>" method="post">
    
<div class="emailClassify">
  <div class="coInputHeader">
  <div class="coInputHeaderUpperRow">
  	<div class="coInputTitle"><table style="width:535px"><tr><td><?php echo lang('classify email subject', clean($email->getSubject())) ?>
  	</td><td style="text-align:right"><?php echo submit_button(lang('classify'), 's', array('style'=>'margin-top:0px;margin-left:10px;width:auto', 'tabindex' => '40')) ?></td></tr></table></div>
  </div>
  </div>
  
  
<div class="coInputSeparator"></div>
<div class="coInputMainBlock">

  <fieldset>
<legend><?php echo lang('project') ?></legend>
		<?php echo select_workspaces('classification[project_ids]', null, array_var($classification_data, 'project_ids'), $genid.'wsSel'); ?>
		<?php echo label_tag(lang('tags')) ?>
	<?php echo autocomplete_tags_field("classification[tag]", array_var($classification_data, 'tag'), null, 20); ?>
	<br />
	<?php echo checkbox_field('classification[create_task]', false, array('id' => $genid.'create_task', 'tabindex' => '30')); ?>
	<label class="yes_no" for="<?php echo $genid.'create_task';?>"><?php echo lang('create task from email')?></label>
</fieldset>
   
   <?php if ($email->getHasAttachments()) {?>
   <fieldset>
   <legend><?php echo lang('add attachments to project') ?></legend>
   <?php 
   $c = 0;
   foreach($parsedEmail["Attachments"] as $att) { 
   	    $fName = str_starts_with($att["FileName"], "=?") ? iconv_mime_decode($att["FileName"], 0, "UTF-8") : utf8_safe($att["FileName"]);
   	    if (trim($fName) == "" && strlen($att["FileName"]) > 0) $fName = utf8_encode($att["FileName"]);
   		echo checkbox_field('classification[att_'.$c.']', true, array('id' => $genid.'classifyFormAddAttachment'.$c, 'tabindex' => '40'));?>
    <label for="<?php echo $genid.'classifyFormAddAttachment'.$c ?>" class="yes_no"><?php echo $fName ?></label>
    <?php $c++;
   }?>
   </fieldset>
<?php } ?>

<?php echo submit_button(lang('classify'), 's', array('tabindex' => '50')) ?>
  </div>
</div>
</form>