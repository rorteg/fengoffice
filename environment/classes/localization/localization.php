<?php

/**
 * Shortcut method for retriving single lang value
 *
 * @access public
 * @param string $neme
 * @return string
 */
function lang($name) {

	// Get function arguments and remove first one.
	$args = func_get_args();
	if(is_array($args)) array_shift($args);

	return langA($name, $args);

} // lang

function langA($name, $args) {
	static $base = null;
	$value = Localization::instance()->lang($name);
	if (is_null($value)) {
		if (!Env::isDebugging()) {
			if (!$base instanceof Localization) {
				$base = new Localization();
				$base->loadSettings("en_us", ROOT . "/language");
			}
			$value = $base->lang($name);
		}
		if (is_null($value)){ 
			if (defined('DEBUG_TRANSLATION') && DEBUG_TRANSLATION){ //this has been changed so if a lang is missing in other language, the missing lang the will be shown in English if debug is in FALSE				
				$base = new Localization();
				$base->loadSettings("en_us", ROOT . "/language");
				$value = $base->lang($name);
			}
			file_put_contents(ROOT."/cache/missing_lang.txt", " Missing lang found in ".Localization::instance()->getLocale().": ".$name." \n", FILE_APPEND);
			if (is_null($value))return "Missing lang: $name";
		}
	}

	// We have args? Replace all {x} with arguments
	if(is_array($args) && count($args)) {
		$i = 0;
		foreach($args as $arg) {
			$value = str_replace('{'.$i.'}', $arg, $value);
			$i++;
		} // foreach
	} // if

	// Done here...
	return $value;
}
?>