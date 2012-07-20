<?php 
require_javascript('og/modules/linkToObjectForm.js');
if (!$genid) $genid = gen_id();
?>
<a id="<?php echo $genid ?>before" href="#" onclick="App.modules.linkToObjectForm.pickObject(this)"><?php echo lang('link object') ?></a>

<script>
<?php
if (is_array($objects)) {
	foreach ($objects as $o) {
		if (!$o instanceof ContentDataObject) continue;
?>
App.modules.linkToObjectForm.addObject(document.getElementById('<?php echo $genid ?>before'), {
	'object_id': <?php echo $o->getId() ?>,
	'type': '<?php echo $o->getObjectTypeName() ?>',
	'name': <?php echo json_encode($o->getObjectName()) ?>
});
<?php
	}
}
?>
</script>