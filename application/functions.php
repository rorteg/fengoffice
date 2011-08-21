<?php

// ---------------------------------------------------
//  System callback functions, registered automaticly
//  or in application/application.php
// ---------------------------------------------------

/**
 * Gets called, when an undefined class is being instanciated
 *d
 * @param_string $load_class_name
 */
function feng__autoload($load_class_name) {
	static $loader = null;
	$class_name = strtoupper($load_class_name);

	// Try to get this data from index...
	if(isset($GLOBALS[AutoLoader::GLOBAL_VAR])) {
		if(isset($GLOBALS[AutoLoader::GLOBAL_VAR][$class_name])) {
			return include $GLOBALS[AutoLoader::GLOBAL_VAR][$class_name];
		} // if
	} // if

	if(!$loader) {
		$loader = new AutoLoader();
		$loader->addDir(ROOT . '/application');
		$loader->addDir(ROOT . '/environment');
		$loader->addDir(ROOT . '/library');
		$loader->setIndexFilename(ROOT . '/cache/autoloader.php');
	} // if

	try {
		$loader->loadClass($class_name);
	} catch(Exception $e) {
		try {
			if (function_exists("__autoload")) __autoload($class_name);
		} catch(Exception $ex) {
			die('Caught Exception in AutoLoader: ' . $ex->__toString());
		}
	} // try
} // __autoload

/**
 * Feng Office shutdown function
 *
 * @param void
 * @return null
 */
function __shutdown() {
	DB::close();
	$logger_session = Logger::getSession();
	if(($logger_session instanceof Logger_Session) && !$logger_session->isEmpty()) {
		Logger::saveSession();
	} // if
} // __shutdown

/**
 * This function will be used as error handler for production
 *
 * @param integer $code
 * @param string $message
 * @param string $file
 * @param integer $line
 * @return null
 */
function __production_error_handler($code, $message, $file, $line) {
	// Skip non-static method called staticly type of error...
	if($code == 2048) {
		return;
	} // if

	Logger::log("Error: $message in '$file' on line $line (error code: $code)", Logger::ERROR);
/*	$trace = debug_backtrace();
	Logger::log("trace count: ".count($trace));	
	foreach($trace as $tn=>$tr) {
		if (is_array($tr)) {
			Logger::log($tn . ": " . (isset($tr['file']) ? $tr['file']:'No File') . " " . (isset($tr['line']) ? $tr['line']:'No Line'));
		} 
	}*/
} // __production_error_handler

/**
 * This function will be used as exception handler in production environment
 *
 * @param Exception $exception
 * @return null
 */
function __production_exception_handler($exception) {
	Logger::log($exception, Logger::FATAL);
} // __production_exception_handler

// ---------------------------------------------------
//  Get URL
// ---------------------------------------------------

/**
 * Return an application URL
 *
 * If $include_project_id variable is presend active_project variable will be added to the list of params if we have a
 * project selected (active_project() function returns valid project instance)
 *
 * @param string $controller_name
 * @param string $action_name
 * @param array $params
 * @param string $anchor
 * @param boolean $include_project_id
 * @return string
 */
function get_url($controller_name = null, $action_name = null, $params = null, $anchor = null, $include_project_id = false) {
	$controller = trim($controller_name) ? $controller_name : DEFAULT_CONTROLLER;
	$action = trim($action_name) ? $action_name : DEFAULT_ACTION;
	if(!is_array($params) && !is_null($params)) {
		$params = array('id' => $params);
	} // if

	$url_params = array('c=' . $controller, 'a=' . $action);

	if($include_project_id) {
		if(function_exists('active_project') && (active_project() instanceof Project)) {
			if(!(is_array($params) && isset($params['active_project']))) {
				$url_params[] = 'active_project=' . active_project()->getId();
			} // if
		} // if
	} // if

	if(is_array($params)) {
		foreach($params as $param_name => $param_value) {
			if(is_bool($param_value)) {
				$url_params[] = $param_name . '=1';
			} else {
				$url_params[] = $param_name . '=' . urlencode($param_value);
			} // if
		} // foreach
	} // if

	if(trim($anchor) <> '') {
		$anchor = '#' . $anchor;
	} // if

	return with_slash(ROOT_URL) . 'index.php?' . implode('&', $url_params) . $anchor;
} // get_url

function get_sandbox_url($controller_name = null, $action_name = null, $params = null, $anchor = null, $include_project_id = false) {
	$controller = trim($controller_name) ? $controller_name : DEFAULT_CONTROLLER;
	$action = trim($action_name) ? $action_name : DEFAULT_ACTION;
	if(!is_array($params) && !is_null($params)) {
		$params = array('id' => $params);
	} // if

	$url_params = array('c=' . $controller, 'a=' . $action);

	if($include_project_id) {
		if(function_exists('active_project') && (active_project() instanceof Project)) {
			if(!(is_array($params) && isset($params['active_project']))) {
				$url_params[] = 'active_project=' . active_project()->getId();
			} // if
		} // if
	} // if

	if(is_array($params)) {
		foreach($params as $param_name => $param_value) {
			if(is_bool($param_value)) {
				$url_params[] = $param_name . '=1';
			} else {
				$url_params[] = $param_name . '=' . urlencode($param_value);
			} // if
		} // foreach
	} // if

	if(trim($anchor) <> '') {
		$anchor = '#' . $anchor;
	} // if

	if (defined('SANDBOX_URL')) {
		return with_slash(SANDBOX_URL) . 'index.php?' . implode('&', $url_params) . $anchor;
	} else {
		return with_slash(ROOT_URL) . 'index.php?' . implode('&', $url_params) . $anchor;
	}
} // get_sandbox_url

// ---------------------------------------------------
//  Product
// ---------------------------------------------------

/**
 * Return product name. This is a wrapper function that abstracts the product name
 *
 * @param void
 * @return string
 */
function product_name() {
	return PRODUCT_NAME;
} // product_name

/**
 * Return product version, wrapper function.
 *
 * @param void
 * @return string
 */
function product_version() {
	if (defined('DISPLAY_VERSION')) return DISPLAY_VERSION;
	return include ROOT . '/version.php';
} // product_version

/**
 * Return installed version, wrapper function.
 *
 * @param void
 * @return string
 */
function installed_version() {
	$installed_version = config_option('installed_version');
	if ($installed_version) {
		return $installed_version;
	} else {
		$version = @include ROOT . '/config/installed_version.php';
		if ($version) {
			return $version;
		} else {
			return "unknown";
		}
	}
} // installed_version


/**
 * Returns product signature (name and version). If user is not logged in and
 * is not member of owner company he will see only product name
 *
 * @param void
 * @return string
 */
function product_signature() {
	if(function_exists('logged_user') && (logged_user() instanceof User) && logged_user()->isMemberOfOwnerCompany()) {
		$result = lang('footer powered', 'http://www.fengoffice.com/', clean(product_name()) . ' ' . product_version());
		if(Env::isDebugging()) {
			ob_start();
			benchmark_timer_display(false);
			$result .= '. ' . ob_get_clean();
			if(function_exists('memory_get_usage')) {
				$result .= '. ' . format_filesize(memory_get_usage());
			} // if
		} // if
		return $result;
	} else {
		return  lang('footer powered', 'http://www.fengoffice.com/', clean(product_name()));
	} // if
} // product_signature

// ---------------------------------------------------
//  Request, routes replacement methods
// ---------------------------------------------------

/**
 * Return matched requst controller
 *
 * @access public
 * @param void
 * @return string
 */
function request_controller() {
	$controller = trim(array_var($_GET, 'c', DEFAULT_CONTROLLER));
	return $controller && is_valid_function_name($controller) ? $controller : DEFAULT_CONTROLLER;
} // request_controller

/**
 * Return matched request action
 *
 * @access public
 * @param void
 * @return string
 */
function request_action() {
	$action = trim(array_var($_GET, 'a', DEFAULT_ACTION));
	return $action && is_valid_function_name($action) ? $action : DEFAULT_ACTION;
} // request_action

// ---------------------------------------------------
//  Controllers and stuff
// ---------------------------------------------------

/**
 * Set internals of specific company website controller
 *
 * @access public
 * @param PageController $controller
 * @param string $layout Project or company website layout. Or any other...
 * @return null
 */
function prepare_company_website_controller(PageController $controller, $layout = 'website') {

	// If we don't have logged user prepare referer params and redirect user to login page
	if(!(logged_user() instanceof User)) {
		$ref_params = array();
		foreach($_GET as $k => $v) $ref_params['ref_' . $k] = $v;
		$controller->redirectTo('access', 'login', $ref_params);
	} // if

	$controller->setLayout($layout);
	$controller->addHelper('form', 'breadcrumbs', 'pageactions', 'tabbednavigation', 'company_website', 'project_website', 'textile');
} // prepare_company_website_controller

// ---------------------------------------------------
//  Company website interface
// ---------------------------------------------------

/**
 * Return owner company object if we are on company website and it is loaded
 *
 * @access public
 * @param void
 * @return Company
 */
function owner_company() {
	return CompanyWebsite::instance()->getCompany();
} // owner_company

/**
 * Return logged user if we are on company website
 *
 * @access public
 * @param void
 * @return User
 */
function logged_user() {
	return CompanyWebsite::instance()->getLoggedUser();
} // logged_user

/**
 * Return active project if we are on company website
 *
 * @access public
 * @param void
 * @return Project
 */
function active_project() {
	return CompanyWebsite::instance()->getProject();
} // active_project


/**
 * Return active tag
 *
 * @access public
 * @param void
 * @return Project
 */
function active_tag() {
	return array_var($_GET,'active_tag');
} // active_tag

/**
 * Return active project if we are on company website
 *
 * @access public
 * @param void
 * @return Project
 */
function active_or_personal_project() {
	$act=active_project();
	return  $act ? $act : personal_project();
} // active_project

/**
 * Return active project if we are on company website
 *
 * @access public
 * @param void
 * @return array
 */
function active_projects() {
	return logged_user()->getActiveProjects();
} // active_project

/**
 * Return personal project
 *
 * @access public
 * @param void
 * @return Project
 */
function personal_project() {
	return logged_user() instanceof User ? logged_user()->getPersonalProject():null;
} // active_project

/**
 * Return which is the upload hook
 * @return string
 */
function upload_hook() {
	if (!defined('UPLOAD_HOOK')) define('UPLOAD_HOOK', 'fengoffice');
	return UPLOAD_HOOK;
}


// ---------------------------------------------------
//  Config interface
// ---------------------------------------------------

/**
 * Return config option value
 *
 * @access public
 * @param string $name Option name
 * @param mixed $default Default value that is returned in case of any error
 * @return mixed
 */
function config_option($option, $default = null) {
	// check the cache for the option value
	if (GlobalCache::isAvailable()) {
		$option_value = GlobalCache::get('config_option_'.$option, $success);
		if ($success) return $option_value;
	}
	// value not found in cache
	$option_value = ConfigOptions::getOptionValue($option, $default);
	if (GlobalCache::isAvailable()) {
		GlobalCache::update('config_option_'.$option, $option_value);
	}
	
	return $option_value;
} // config_option

/**
 * Set value of specific configuration option
 *
 * @param string $option_name
 * @param mixed $value
 * @return boolean
 */
function set_config_option($option_name, $value) {
	$config_option = ConfigOptions::getByName($option_name);
	if(!($config_option instanceof ConfigOption)) {
		return false;
	} // if

	$config_option->setValue($value);
	
	// update cache if available
	if (GlobalCache::isAvailable() && GlobalCache::key_exists('config_option_'.$option_name)) {
		GlobalCache::update('config_option_'.$option_name, $value);
	}
	
	return $config_option->save();
} // set_config_option

/**
 * Return user config option value
 *
 * @access public
 * @param string $name Option name
 * @param mixed $default Default value that is returned in case of any error
 * @param int $user_id User Id, if null logged user is taken
 * @return mixed
 */
function user_config_option($option, $default = null, $user_id = null) {
	if (is_null($user_id)) {
		if (logged_user() instanceof User) {
			$user_id = logged_user()->getId();
		} else if (is_null($default)) {
			$def_value = null;
			// check the cache for the option default value
			if (GlobalCache::isAvailable()) {
				$def_value = GlobalCache::get('user_config_option_def_'.$option, $success);
				if ($success) return $def_value;
			}
			// default value not found in cache
			$def_value = UserWsConfigOptions::getDefaultOptionValue($option, $default);
			if (GlobalCache::isAvailable()) {
				GlobalCache::update('user_config_option_def_'.$option, $def_value);
			}
			return $def_value;
		} else {
			return $default;
		}
	}
	
	// check the cache for the option value
	if (GlobalCache::isAvailable()) {
		$option_value = GlobalCache::get('user_config_option_'.$user_id.'_'.$option, $success);
		if ($success) return $option_value;
	}
	// default value not found in cache
	$option_value = UserWsConfigOptions::getOptionValue($option, $user_id, $default);
	if (GlobalCache::isAvailable()) {
		GlobalCache::update('user_config_option_'.$user_id.'_'.$option, $option_value);
	}
	
	return $option_value;
} // user_config_option

function user_has_config_option($option_name, $user_id = 0, $workspace_id = 0) {
	if (!$user_id && logged_user() instanceof User) {
		$user_id = logged_user()->getId();
	} else {
		return false;
	}
	$option = UserWsConfigOptions::getByName($option_name);
	if (!$option instanceof UserWsConfigOption) return false;
	$value = UserWsConfigOptionValues::findById(array(
		'option_id' => $option->getId(),
		'user_id' => $user_id,
		'workspace_id' => $workspace_id));
	return $value instanceof UserWsConfigOptionValue;
}

function default_user_config_option($option, $default = null) {
	return UserWsConfigOptions::getDefaultOptionValue($option, $default);
}


/**
 * Return user config option value
 *
 * @access public
 * @param string $name Option name
 * @param mixed $default Default value that is returned in case of any error
 * @param int $user_id User Id, if null logged user is taken
 * @return mixed
 */
function load_user_config_options_by_category_name($category_name) {
	UserWsConfigOptions::getOptionsByCategoryName($category_name, true);
} // config_option

/**
 * Set value of specific user configuration option
 *
 * @param string $option_name
 * @param mixed $value
 * @param int $user_id User Id, if null logged user is taken
 * @return boolean
 */
function set_user_config_option($option_name, $value, $user_id = null ) {
	$config_option = UserWsConfigOptions::getByName($option_name);
	if(!($config_option instanceof UserWsConfigOption)) {
		return false;
	} // if
	$config_option->setUserValue($value, $user_id);
	
	// update cache if available
	if (GlobalCache::isAvailable() && GlobalCache::key_exists('user_config_option_'.$user_id.'_'.$option_name)) {
		GlobalCache::update('user_config_option_'.$user_id.'_'.$option_name, $value);
	}
	
	return $config_option->save();
} // set_config_option

/**
 * This function will return object by the manager class and object ID
 *
 * @param integer $object_id
 * @param string $manager_class
 * @return ApplicationDataObject
 */
function get_object_by_manager_and_id($object_id, $manager_class) {
	$object_id = (integer) $object_id;
	$manager_class = trim($manager_class);

	if(!is_valid_function_name($manager_class) || !class_exists($manager_class, true)) {
		throw new Error("Class '$manager_class' does not exist");
	} // if

	$code = "return $manager_class::findById($object_id);";
	$object = eval($code);

	return $object instanceof DataObject ? $object : null;
} // get_object_by_manager_and_id

function alert($text) {
	evt_add("popup", array('title' => "Debug", 'message' => $text));
}

// ---------------------------------------------------
//  Encryption/Decryption
// ---------------------------------------------------

function cp_encrypt($password, $time){
	//appending padding characters
	$newPass = rand(0,9) . rand(0,9);
	$c = 1;
	while ($c < 15 && (int)substr($newPass,$c-1,1) + 1 != (int)substr($newPass,$c,1)){
		$newPass .= rand(0,9);
		$c++;
	}
	$newPass .= $password;
	
	//applying XOR
	$newSeed = md5(SEED . $time);
	$passLength = strlen($newPass);
	while (strlen($newSeed) < $passLength) $newSeed.= $newSeed;
	$result = (substr($newPass,0,$passLength) ^ substr($newSeed,0,$passLength));
	
	return base64_encode($result);
}

function cp_decrypt($password, $time){
	$b64decoded = base64_decode($password);
	
	//applying XOR
	$newSeed = md5(SEED . $time);
	$passLength = strlen($b64decoded);
	while (strlen($newSeed) < $passLength) $newSeed.= $newSeed;
	$original_password = (substr($b64decoded,0,$passLength) ^ substr($newSeed,0,$passLength));
	
	//removing padding
	$c = 1;
	while($c < 15 && (int)substr($original_password,$c-1,1) + 1 != (int)substr($original_password,$c,1)){
		$c++;
	}
	return substr($original_password,$c+1);
}

// ---------------------------------------------------
//  Filesystem
// ---------------------------------------------------

function remove_dir($dir) {
	$dh = @opendir($dir);
	if (!is_resource($dh)) return;
    while (false !== ($obj = readdir($dh))) {
		if($obj == '.' || $obj == '..') continue;
		$path = "$dir/$obj";
		if (is_dir($path)) {
			remove_dir($path);
		} else {
			@unlink($path);
		}
	}
	@closedir($dh);
	@rmdir($dir);
}

function new_personal_project_name($username = null) {
	Localization::instance()->loadSettings(DEFAULT_LOCALIZATION, ROOT . '/language');
	$wname = Localization::instance()->lang('personal workspace name');
	if (is_null($wname)) {
		$wname = "{0}\'s Workspace";
	}
	if ($username != null) $wname = str_replace("{0}", $username, $wname);
	if (logged_user() != null)Localization::instance()->loadSettings(logged_user()->getLocale(), ROOT . '/language');
	return $wname;	
}

function help_link() {
	$link = Localization::instance()->lang('wiki help link');
	if (is_null($link)) {
		$link = DEFAULT_HELP_LINK;
	}
	return $link;
}

// ---------------------------------------------------
//  Localization
// ---------------------------------------------------

/**
 * This returns the localization of the logged user, if not defined returns the one defined in config.php
 *
 * @return string
 */
function get_locale() {
	$locale = user_config_option("localization");
	if (!$locale) $locale = DEFAULT_LOCALIZATION;
	
	return $locale;
}

function get_ext_language_file($loc) {
	if (is_file(ROOT . "/language/$loc/_config.php")) {
		$config = include ROOT . "/language/$loc/_config.php";
		if (is_array($config)) {
			return array_var($config, '_ext_language_file', 'ext-lang-en-min.js');
		}
	}
	return 'ext-lang-en-min.js';
}

function get_language_name($loc) {
	if (is_file(ROOT . "/language/$loc/_config.php")) {
		$config = include ROOT . "/language/$loc/_config.php";
		if (is_array($config)) {
			return array_var($config, '_language_name', $loc);
		}
	}
	return $loc;
}

function get_workspace_css_properties($num) {
	static $workspaces_css = array (
    "main"  => array( "padding" => "1px 5px", "font-size" => "90%"),
    "0"  => array("border-color" => "#777777", "background-color" => "#EEEEEE", "color" => "#777777"),
    "1"  => array("color" => "#DEE5F2", "background-color" => "#5A6986", "border-color" => "#5A6986"),
    "2"  => array("color" => "#E0ECFF", "background-color" => "#206CE1", "border-color" => "#206CE1"),
    "3"  => array("color" => "#DFE2FF", "background-color" => "#0000CC", "border-color" => "#0000CC"),
    "4"  => array("color" => "#E0D5F9", "background-color" => "#5229A3", "border-color" => "#5229A3"),
    "5"  => array("color" => "#FDE9F4", "background-color" => "#854F61", "border-color" => "#854F61"),
    "6"  => array("color" => "#FFE3E3", "background-color" => "#CC0000", "border-color" => "#CC0000"),
    "7"  => array("color" => "#FFF0E1", "background-color" => "#EC7000", "border-color" => "#EC7000"),
    "8"  => array("color" => "#FADCB3", "background-color" => "#B36D00", "border-color" => "#B36D00"),
    "9"  => array("color" => "#F3E7B3", "background-color" => "#AB8B00", "border-color" => "#AB8B00"),
    "10"  => array("color" => "#FFFFD4", "background-color" => "#636330", "border-color" => "#636330"),
    "11"  => array("color" => "#F9FFEF", "background-color" => "#64992C", "border-color" => "#64992C"),
    "12"  => array("color" => "#F1F5EC", "background-color" => "#006633", "border-color" => "#006633"),
    "13"  => array("color" => "#5A6986", "background-color" => "#DEE5F2", "border-color" => "#5A6986"),
    "14"  => array("color" => "#206CE1", "background-color" => "#E0ECFF", "border-color" => "#206CE1"),
    "15"  => array("color" => "#0000CC", "background-color" => "#DFE2FF", "border-color" => "#0000CC"),
    "16"  => array("color" => "#5229A3", "background-color" => "#E0D5F9", "border-color" => "#5229A3"),
    "17"  => array("color" => "#854F61", "background-color" => "#FDE9F4", "border-color" => "#854F61"),
    "18"  => array("color" => "#CC0000", "background-color" => "#FFE3E3", "border-color" => "#CC0000"),
    "19"  => array("color" => "#EC7000", "background-color" => "#FFF0E1", "border-color" => "#EC7000"),
    "20"  => array("color" => "#B36D00", "background-color" => "#FADCB3", "border-color" => "#B36D00"),
    "21"  => array("color" => "#AB8B00", "background-color" => "#F3E7B3", "border-color" => "#AB8B00"),
    "22"  => array("color" => "#636330", "background-color" => "#FFFFD4", "border-color" => "#636330"),
    "23"  => array("color" => "#64992C", "background-color" => "#F9FFEF", "border-color" => "#64992C"),
    "24"  => array("color" => "#006633", "background-color" => "#F1F5EC", "border-color" => "#006633"),   
);
	

	return "border-color: ".$workspaces_css[$num]['border-color']."; background-color: ".$workspaces_css[$num]['background-color']."; color: ".$workspaces_css[$num]['color']."; 
	padding: ".$workspaces_css['main']['padding']."; font-size: ".$workspaces_css['main']['font-size'].";";
    
}

function module_enabled($module, $default = null) {
	return config_option("enable_".$module."_module", $default);
}

function create_user_from_email($email, $name, $type = 'guest', $send_notification = true) {
	return create_user(array(
		'username' => substr($email, 0, strpos($email, '@')),
		'display_name' => trim($name),
		'email' => $email,
		'type' => $type,
		'company_id' => owner_company()->getId(),
		'send_email_notification' => $send_notification,
	), '');
}

function create_user($user_data, $permissionsString) {
	$user = new User();
	$user->setUsername(array_var($user_data, 'username'));
	$user->setDisplayName(array_var($user_data, 'display_name'));
	$user->setEmail(array_var($user_data, 'email'));
	$user->setCompanyId(array_var($user_data, 'company_id'));
	$user->setType(array_var($user_data, 'type'));
	$user->setTimezone(array_var($user_data, 'timezone'));
	if (!logged_user() instanceof User || can_manage_security(logged_user())) {
		$user->setCanEditCompanyData(array_var($user_data, 'can_edit_company_data'));
		$user->setCanManageSecurity(array_var($user_data, 'can_manage_security'));
		$user->setCanManageWorkspaces(array_var($user_data, 'can_manage_workspaces'));
		$user->setCanManageConfiguration(array_var($user_data, 'can_manage_configuration'));
		$user->setCanManageContacts(array_var($user_data, 'can_manage_contacts'));
		$user->setCanManageTemplates(array_var($user_data, 'can_manage_templates'));
		$user->setCanManageReports(array_var($user_data, 'can_manage_reports'));
		$user->setCanManageTime(array_var($user_data, 'can_manage_time'));
		$user->setCanAddMailAccounts(array_var($user_data, 'can_add_mail_accounts'));
		$other_permissions = array();
		Hook::fire('add_user_permissions', $user, $other_permissions);
		foreach ($other_permissions as $k => $v) {
			$user->setColumnValue($k, array_var($user_data, $k));
		}
	}

	if (array_var($user_data, 'password_generator', 'random') == 'random') {
		// Generate random password
		$password = UserPasswords::generateRandomPassword();
	} else {
		// Validate input
		$password = array_var($user_data, 'password');
		if (trim($password) == '') {
			throw new Error(lang('password value required'));
		} // if
		if ($password <> array_var($user_data, 'password_a')) {
			throw new Error(lang('passwords dont match'));
		} // if
	} // if
	
	$user->setPassword($password);
	$user->save();

	$user_password = new UserPassword();
	$user_password->setUserId($user->getId());
	$user_password->setPasswordDate(DateTimeValueLib::now());
	$user_password->setPassword(cp_encrypt($password, $user_password->getPasswordDate()->getTimestamp()));
	$user_password->password_temp = $password;
	$user_password->save();
	
	if (array_var($user_data, 'autodetect_time_zone', 1) == 1) {
		set_user_config_option('autodetect_time_zone', 1, $user->getId());
	}
	
	if ($user->getType() == 'admin') {
		if ($user->getCompanyId() != owner_company()->getId() || logged_user() instanceof User && !can_manage_security(logged_user())) {
			// external users can't be admins or logged user has no rights to create admins => set as Normal 
			$user->setType('normal');
		} else {
			$user->setAsAdministrator(true);
		}
	}

	/* create contact for this user*/
	if (array_var($user_data, 'create_contact') == 1) {
		// if contact with same email exists take it, else create new
		$contact = Contacts::getByEmail($user->getEmail(), true);
		if (!$contact instanceof Contact) {
			$contact = new Contact();
			$contact->setEmail($user->getEmail());
		} else if ($contact->isTrashed()) {
			$contact->untrash();
		}
		$contact->setFirstname($user->getDisplayName());
		$contact->setUserId($user->getId());
		$contact->setTimezone($user->getTimezone());
		$contact->setCompanyId($user->getCompanyId());
		$contact->save();
	} else {
		$contact_id = array_var($user_data, 'contact_id');
		$contact = Contacts::findById($contact_id);
		if ($contact instanceof Contact) {
			// user created from a contact 
			$contact->setUserId($user->getId());
			$contact->save();
		} else {
			// if contact with same email exists use it as user's contact, without changing it
			$contact = Contacts::getByEmail($user->getEmail(), true);
			if ($contact instanceof Contact) {
				$contact->setUserId($user->getId());
				if ($contact->isTrashed()) $contact->untrash();
				$contact->save();
			}
		}
	}
	$contact = $user->getContact();
	if ($contact instanceof Contact) {
		// update contact data with data entered for this user
		$contact->setCompanyId($user->getCompanyId());
		if ($contact->getEmail() != $user->getEmail()) {
			// make user's email the contact's main email address
			if ($contact->getEmail2() == $user->getEmail()) {
				$contact->setEmail2($contact->getEmail());
			} else if ($contact->getEmail3() == $user->getEmail()) {
				$contact->setEmail3($contact->getEmail());
			} else if ($contact->getEmail2() == "") {
				$contact->setEmail2($contact->getEmail());
			} else {
				$contact->setEmail3($contact->getEmail());
			}
		}
		$contact->setEmail($user->getEmail());
		$contact->save();
	}

	if (!$user->isGuest()) {
		/* create personal project or assing the selected*/
		//if recived a personal project assing this 
		//project as personal project for this user
		$new_project = null;
		$personalProjectId = array_var($user_data, 'personal_project', 0);
		$project = Projects::findById($personalProjectId);
		if (!$project instanceof Project) {
			$project = new Project();
			$wname = new_personal_project_name($user->getUsername());
			$project->setName($wname);
			$project->setColor(11);
			
			$wdesc = Localization::instance()->lang(lang('personal workspace description'));
			if (!is_null($wdesc)) {
				$project->setDescription($wdesc);
			}
			$project->setCreatedById($user->getId());
	
			$project->save(); //Save to set an ID number
			$project->setP1($project->getId()); //Set ID number to the first project
			$project->save();
			$new_project = $project;		
		}
		$user->setPersonalProjectId($project->getId());
		$project_user = new ProjectUser();
		$project_user->setProjectId($project->getId());
		$project_user->setUserId($user->getId());
		$project_user->setCreatedById($user->getId());
		$project_user->setAllPermissions(true);
		
		$project_user->save();
		/* end personal project */
	}
	$user->save();

	ApplicationLogs::createLog($user, null, ApplicationLogs::ACTION_ADD);

  	//TODO - Make batch update of these permissions
	if ($permissionsString && $permissionsString != '') {
		$permissions = json_decode($permissionsString);
	} else {
		$permissions = null;
	}
  	if (is_array($permissions) && (!logged_user() instanceof User || can_manage_security(logged_user()))) {
  		foreach ($permissions as $perm) {			  			
  			if (ProjectUser::hasAnyPermissions($perm->pr, $perm->pc)) {
  				if (!$personalProjectId || $personalProjectId != $perm->wsid) {
					$relation = new ProjectUser();
			  		$relation->setProjectId($perm->wsid);
			  		$relation->setUserId($user->getId());
					
			  		$relation->setCheckboxPermissions($perm->pc, $user->isGuest() ? false : true);
			  		$relation->setRadioPermissions($perm->pr, $user->isGuest() ? false : true);
			  		$relation->save();
  				}
  			}
  		}
	} // if
	
	if ($new_project instanceof Project && logged_user() instanceof User && logged_user()->isProjectUser($new_project)) {
		evt_add("workspace added", array(
			"id" => $new_project->getId(),
			"name" => $new_project->getName(),
			"color" => $new_project->getColor()
		));
	}

	// Send notification...
	try {
		if (array_var($user_data, 'send_email_notification')) {
			Notifier::newUserAccount($user, $password);
		} // if
	} catch(Exception $e) {
	
	} // try
	return $user;
}

function utf8_safe($text) {
	$safe = html_entity_decode(htmlentities($text, ENT_COMPAT, "UTF-8"), ENT_COMPAT, "UTF-8");
	return preg_replace('/[\xF0-\xF4][\x80-\xBF][\x80-\xBF][\x80-\xBF]/', "", $safe);
}

function clean_csv_addresses($csv) {
	$addrs = explode(",", $csv);
	$parsed = array();
	$pending = false;
	foreach ($addrs as $addr) {
		$addr = trim($addr);
		if ($pending) {
			$addr = $pending . ", " . $addr;
			$pending = false;
		}
		if ($addr == "") continue;
		if ($addr[0] == '"') {
			$pos = strpos($addr, '"', 1);
			if ($pos !== false) {
				// valid address
			} else {
				// name contained a comma so it was split
				$pending = $addr;
				continue;
			}
			if (strpos($addr, '<') === false) {
				// invalid address. has quoted name part but no email address. leave it as is just in case
				$parsed[] = $addr;
				continue;
			}
		}
		if (strpos($addr, '<') === false) {
			$addr = "<$addr>";
		}
		$parsed[] = $addr;
	}
	return implode(",", $parsed);
}

/**
 * Converts HTML to plain text
 * @param $html
 * @return string
 */
function html_to_text($html) {
	include_once "library/html2text/class.html2text.inc";
	$h2t = new html2text($html);
	return $h2t->get_text(); 
}


?>