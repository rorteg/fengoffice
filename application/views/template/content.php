<?php
$description = array_var($variables, "description", "");
$objects = array_var($variables, "objects", "");
?>
<b><?php echo lang("description") ?>:</b><br><?php echo clean($description) ?><br><br>
<b><?php echo lang("objects in template") ?>:</b><br>
<?php
if (is_array($objects) && count($objects)) {
	$isAlt = false;
	foreach ($objects as $o) {
		if (!$o instanceof ProjectDataObject) continue; 
?>
	<div class="og-add-template-object ico-<?php echo $o->getObjectTypeName() ?><?php if ($isAlt) echo " odd" ?>">
		<a class=" internalLink name" href="<?php echo $o->getViewUrl() ?>">
			<?php echo clean($o->getObjectName()) ?>
		</a>
	</div>
<?php
		$isAlt = !$isAlt;	
	}
} else {
	echo "<i>".lang("no objects in template")."</i>";
}
?><br>