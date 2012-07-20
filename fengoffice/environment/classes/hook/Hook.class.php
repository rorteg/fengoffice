<?php
class Hook {
	static private $hooks = array();
	
	static function register($hook) {
		self::$hooks[] = $hook;
	}
	
	static function fire($function, $argument, &$ret) {
		foreach (self::$hooks as $hook) {
			$callback = $hook."_".$function;
			if (function_exists($callback)) {
				$callback($argument, $ret);
			}
		}
	}
	
	static function init() {
		$base_hooks_dir = ROOT . "/hooks" ;
		if (is_dir($base_hooks_dir)) {
			$handle = opendir($base_hooks_dir);
			while ($file = readdir($handle)) {
				if (is_file("$base_hooks_dir/$file") && substr($file, -4) == '.php') {
					include_once "$base_hooks_dir/$file";
				}
			}
			closedir($handle);
		}
		
		foreach ( Plugins::instance()->getActive() as $plugin ){
			/* @var $plugin Plugin  */
			$plugin_hooks_dir = $plugin->getHooksPath();
			if (is_dir($plugin_hooks_dir)) {
				$handle = opendir($plugin_hooks_dir);
				while ($file = readdir($handle)) {
					if (is_file("$plugin_hooks_dir/$file") && substr($file, -4) == '.php') {
						include_once "$plugin_hooks_dir/$file";
					}
				}
				$plugin->getSystemName() ;
				closedir($handle);
			}
		}
	}
}