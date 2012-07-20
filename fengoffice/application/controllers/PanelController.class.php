<?php

class PanelController extends ApplicationController {
	
	var $panels = null;
	
	function __construct() {
		parent::__construct ();
		prepare_company_website_controller ( $this, 'website' );
	} // __construct	
	

	private function loadPanels($options) {
		if (! $this->panels) {
			$contact_pg_ids = ContactPermissionGroups::getPermissionGroupIdsByContactCSV(logged_user()->getId(),false);
			$this->panels = array();
			$sql = "
				SELECT * FROM " . TABLE_PREFIX . "tab_panels 
				WHERE 
					enabled = 1 AND					
					( 	
						type = 'system' OR 
						plugin_id IN (SELECT id FROM ".TABLE_PREFIX."plugins WHERE is_installed = 1 AND is_activated = 1) 
					)
					AND id IN (SELECT tab_panel_id FROM ".TABLE_PREFIX."tab_panel_permissions WHERE permission_group_id IN ($contact_pg_ids))
				ORDER BY ordering ASC ";
			
			$res = DB::execute ( $sql );
			while ( $row = $res->fetchRow () ) {
				 $object = array (
					"title" => lang($row ['title']), 
					"id" => $row ['id'], 
				 	"quickAddTitle" => lang ($row['default_controller']), 
					"iconCls" => $row ['icon_cls'], 
				 	"refreshOnWorkspaceChange" => (bool) $row ['refresh_on_context_change'] , 
				 	"defaultController" => $row['default_controller'] ,
					"defaultContent" => array (
						"type" => "url", 
						"data" => get_url ( $row ['default_controller'], $row ['default_action'] ) 
					),
					"enabled" => $row ['enabled'], 
					"type" => $row ['type'],
				);

				
				if ( $row ['initial_controller'] && $row['initial_action'] ) {
					$object["initialContent"] = array (
						"type" => "url", 
						"data" => get_url ( $row ['initial_controller'], $row ['initial_action'] ) 
					);
				}
				/*
				if ( $row['title'] == "overview" ) {
					$object["initialContent"] = array (
						"type" => "url", 
						"data" => ROOT_URL 
					);
				}*/
				
				$this->panels [] = $object ;
			}
		}
		
		return $this->panels;
	}
	
	function list_all() {
		ajx_current ( "empty" );
		ajx_extra_data ( array ("panels" => $this->loadPanels ( 'all' ) ) );
	}
}