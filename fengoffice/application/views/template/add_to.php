<?php 
	
	set_page_title(lang('templates'));
	add_page_action(lang('new template'), get_url('template', 'add'), 'ico-add');
	$objid = get_class($object->manager()).":".$object->getId();
?>

<div class="adminClients" style="height:100%;background-color:white">
	<div class="adminHeader">
		<div class="adminTitle"><?php echo lang('templates') ?></div>
	</div>
	<div class="adminSeparator"></div>
	<div class="adminMainBlock">
	
	<div class="addToTemplateDesc"><?php echo lang("you are adding object to template", lang($object->getObjectTypeName()), '<span class="bg-ico ico-'.$object->getObjectTypeName().'">\''.clean($object->getObjectName()).'\'</span>') ?>

<?php if(isset($templates) && is_array($templates) && count($templates)) { ?>
<table style="min-width:400px;margin-top:10px;">
	<tr>
		<th><?php echo lang('template') ?></th>
	</tr>
<?php 
	$isAlt = true;
foreach($templates as $template) { 
	$isAlt = !$isAlt; ?>
	<tr class="<?php echo $isAlt? 'altRow' : ''?>">
		<td><a class="internalLink ico-template bg-ico" href="<?php echo get_url('template', 'add_to', array(
			'id' => $object->getId(),
			'manager' => get_class($object->manager()),
			'template' => $template->getId()
		)) ?>"><?php echo clean($template->getObjectName()) ?></a></td>
	</tr>
<?php } // foreach ?>
</table>
<?php } else { ?>
<?php echo lang('no templates') ?><br>
<?php } // if ?>
<br>
<a href="<?php echo get_url("template", "add", array(
	'id' => $object->getId(),
	'manager' => get_class($object->manager())
)) ?>" class="internalLink ico-add bg-ico"><?php echo lang("new template") ?></a>
</div>
</div>