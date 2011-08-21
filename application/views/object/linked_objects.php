<?php 
require_javascript('og/modules/linkToObjectForm.js');
?>
<a id="<?php echo $genid ?>before" href="#" onclick="App.modules.linkToObjectForm.pickObject(this)"><?php echo lang('link object') ?></a>

<script>
<?php
if (is_array($objects)) {
	foreach ($objects as $o) {
		if (!$o instanceof ProjectDataObject) continue;
?>
App.modules.linkToObjectForm.addObject(document.getElementById('<?php echo $genid ?>before'), {
	'manager': '<?php echo get_class($o->manager()) ?>',
	'object_id': <?php echo $o->getId() ?>,
	'type': '<?php echo $o->getObjectTypeName() ?>',
	'name': <?php echo json_encode($o->getObjectName()) ?>
});
<?php
	}
}
?>
</script>