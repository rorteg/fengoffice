<?php 
	// Render only when no context is selected
	if(!count(active_context_members(false))) {
		// Make calcs, call models, controllers
		$limit = 5 ;
		$result =  Contacts::instance()->listing(array(
			"order" => "name",
			"order_dir" => "asc",
			"extra_conditions" => " AND `is_company` = 0 AND disabled = 0 ",
			"start" =>0,
			"limit" => $limit
		)) ;
		$total = $result->total ;
		$contacts = $result->objects;
		$render_add=can_manage_security(logged_user());
		$genid = gen_id();
		
		include_once 'template.php';
	}