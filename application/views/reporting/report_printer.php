<style>
body {
	font-family: sans-serif;
	font-size:11px;
}
.header {
	border-bottom: 1px solid black;
	padding: 10px;
}
h1 {
	font-size: 150%;
	margin: 15px 0;
}
h2 {
	font-size: 120%;
	margin: 15px 0;
}
th {
	border-bottom:2px solid #333;
}
.body {
	margin-left: 20px;
	padding: 10px;
}
table {
	border-collapse:collapse;
	border-spacing:0;
}
</style>

<div class="print" style="padding:7px">

<div class="printHeader">
<h1 align="center"><?php echo $title ?></h1>
<?php $this->includeTemplate(get_template_path($template_name, 'reporting'));?>
</div>
</div>

<script>
window.print();
</script>