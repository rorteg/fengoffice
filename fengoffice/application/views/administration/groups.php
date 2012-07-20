<?php 
  set_page_title(lang('groups'));
  if(can_manage_security(logged_user())) {
    add_page_action(lang('add group'), get_url('group', 'add'), 'ico-add');
  } // if
  
  $genid = gen_id();
?>

<div id="<?php echo $genid ?>adminContainer" class="adminGroups" style="height:100%;background-color:white">
  <div class="adminHeader">
  	<div class="adminTitle"><?php echo lang('groups') ?></div>
  </div>
  <div class="adminSeparator"></div>
  <div class="adminMainBlock">
<?php if(isset($permission_groups) && is_array($permission_groups) && count($permission_groups)) { ?>
<table style="min-width:400px;margin-top:10px;">
  <tr>
    <th><?php echo lang('name') ?></th>
    <th style="text-align: center"><?php echo lang('users') ?></th>
    <th style="text-align: center"><?php echo lang('options') ?></th>
  </tr>
<?php
	$isAlt = true;
	foreach($permission_groups as $group) { 
		$isAlt = !$isAlt;
?>
	  <tr class="<?php echo $isAlt? 'altRow' : ''?>">
	    <td><a class="internalLink" href="<?php echo $group->getViewUrl()?>"><?php echo clean($group->getName()) ?></a></td>
	    <td style="text-align: center"><?php echo array_var($gr_lengths, $group->getId()) ?></td>
	<?php 
		$options = array(); 
		if(can_manage_security(logged_user())) {
			$options[] = '<a class="internalLink" href="' . $group->getEditUrl() . '">' . lang('edit') . '</a>';
			$options[] = '<a class="internalLink" href="' . $group->getDeleteUrl() . '" onclick="return confirm(\'' . escape_single_quotes(lang('confirm delete group')) . '\')">' . lang('delete') . '</a>';
		}
	?>
	    <td style="font-size:80%;text-align: center;"><?php echo implode(' | ', $options) ?></td>
	  </tr>
<?php } ?>
</table>
<?php } else { ?>
<?php echo lang('no groups in company') ?>
<?php } // if ?>
</div>
</div>

<script>
	var div = document.getElementById('<?php echo $genid ?>adminContainer');
	div.parentNode.style.backgroundColor = '#FFFFFF'; 
</script>