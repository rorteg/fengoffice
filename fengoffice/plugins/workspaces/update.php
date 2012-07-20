<?php 
	/**
	 * Feng2 Plugin update engine 
	 * @author Ignacio Vazquez <elpepe.uy at gmail.com>
	 */
	function workspaces_update_1_2() {
		$workspaces = Workspaces::findAll();
		if (!is_array($workspaces)) return;
		foreach  ( $workspaces as $ws ){
			if ($obj instanceof ContentDataObject) {
				$obj->addToSearchableObjects(1);
			}
			$ws->addToSharingTable();
		}
	}