<?php 
	$limit = 5 ;
	$result =  ProjectFiles::instance()->listing(array(
		"order" => "name",
		"order_dir" => "asc",
		"start" => 0,
		"limit" => $limit
	)) ;
	$total = $result->total ;
	$documents = $result->objects;
	$genid = gen_id();
	if ($total) {
		include_once 'template.php';
	}
