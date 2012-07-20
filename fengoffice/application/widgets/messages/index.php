<?php 

	$limit = 5 ;
	$result =  ProjectMessages::instance()->listing(array(
		"order" => "name",
		"order_dir" => "asc",
		"start" => 0,
		"limit" => $limit
	)) ;
	$total = $result->total ;
	$messages = $result->objects;
	$genid = gen_id();
	if ($total) {
		include_once 'template.php';
	}
