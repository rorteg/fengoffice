<?php return  array(
	
	"name" => "mail",
	"version" => "2",
	"author" => "Feng Office",
	"website" => "http://fengoffice.com",
	"description" => "Email web client",
	"dependences" => array('core_dimensions'),// Array of plugin names (TODO check dependences)
	"order" => 1,
	"types" => array (
		array(
			"id" => 22, 
			"name" => "mail",
			"handler_class" => "MailContents",
			"table_name" => "mail_contents",
			"type" => "content_object",
			"icon" => "mail", 
			
		)
	),
	"tabs" => array (
		array(
			"id" => "mails-panel",
			"ordering" => 2,
			"title" => "email tab",
			"icon_cls" => "ico-mail",
			"refresh_on_context_change" => true,
			"default_controller" => "mail", 
			"default_action" => "init" ,
			"initial_controller" => "" ,
			"initial_action" => "" ,
			"type" => "plugin" ,
			"object_type_id" => 22 
		)
	)
		
);