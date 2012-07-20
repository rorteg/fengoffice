<?php 
	/**
	 * Feng2 Plugin update engine 
	 * @author Ignacio Vazquez <elpepe.uy at gmail.com>
	 */
	function mail_update_1_2() {
		DB::execute("UPDATE ".TABLE_PREFIX."tab_panels SET type = 'plugin', plugin_id = (SELECT id FROM ".TABLE_PREFIX."plugins WHERE name='mail') WHERE id='mails-panel'");
	}