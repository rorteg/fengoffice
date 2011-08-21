<?php header('Content-type: text/html; charset=UTF-8', true); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
<head>
<?php echo meta_tag('content-type', 'text/html; charset=utf-8', true) ?>
</head>
<body>
<script>
var data = <?php echo $content_for_layout ?>;
//alert(<?php echo json_encode($content_for_layout) ?>);
parent.og.processResponse(data, <?php echo json_encode(array('caller' => array_var($_GET, 'current'), 'options' => array())) ?>);
//alert(<?php echo json_encode($content_for_layout) ?>);
var request_id = <?php echo json_encode(array_var($_GET, 'request_id')) ?>;
var options = parent.og.submit[request_id];
if (options) {
	if (typeof options.callback == 'function') {
		options.callback(data, options);
	} else if (typeof options.callback == 'string') {
		parent.og.openLink(options.callback, {caller: options.panel});
	} else if (typeof options.callback == 'object') {
		parent.Ext.getCmp(options.panel).load(options.callback);
	}
	delete parent.og.submit[request_id];
}
</script>

</body>
</html>