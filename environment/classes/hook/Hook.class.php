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
		$handle = opendir(ROOT . "/application/hooks");
		while ($file = readdir($handle)) {
			if (is_file(ROOT . "/application/hooks/$file") && substr($file, -4) == '.php') {
				include_once ROOT . "/application/hooks/$file";
			}
		}
		closedir($handle);
	}
}
?>