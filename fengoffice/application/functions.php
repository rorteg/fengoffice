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
	static  $loader ;
	//$loader = null;
	$class_name = strtoupper($load_class_name);

	// Try to get this data from index...
	if(isset($GLOBALS[AutoLoader::GLOBAL_VAR])) {
		if(isset($GLOBALS[AutoLoader::GLOBAL_VAR][$class_name])) {
			return include $GLOBALS[AutoLoader::GLOBAL_VAR][$class_name];
		} // if
	} // if
	//pre_print_r($loader) ;exit;
	
	if(!$loader) {
		$loader = new AutoLoader();
		$loader->addDir(ROOT . '/application');
		$loader->addDir(ROOT . '/environment');
		$loader->addDir(ROOT . '/library');
		
		//TODO Pepe: No tengo la conexion ni las clases de DB en este momento.. me conecto derecho 
		$temp_link  = mysql_connect(DB_HOST, DB_USER, DB_PASS) ;
		mysql_select_db(DB_NAME) ;
		$res = mysql_query("SELECT name FROM ".TABLE_PREFIX."plugins WHERE is_installed = 1 AND is_activated = 1;");
		while ($row = mysql_fetch_object($res)) {	
			$plugin_name =  strtolower($row->name) ;
			$dir  = ROOT . '/plugins/'.$plugin_name.'/application' ;
			if (is_dir($dir)) {
				$loader->addDir($dir); 
			}
		}
		mysql_close($temp_link);
		
		
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
	}

	$url_params = array('c=' . $controller, 'a=' . $action);

	if(is_array($params)) {
		foreach($params as $param_name => $param_value) {
			if(is_bool($param_value)) {
				$url_params[] = $param_name . '=1';
			} else {
				$url_params[] = $param_name . '=' . urlencode($param_value);
			}
		}
	}

	if(trim($anchor) <> '') {
		$anchor = '#' . $anchor;
	}

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
 * Return revision, to add as parameters when including static files, to control the browser's cache.
 *
 * @return string
 */
function product_version_revision() {
	try{
		$revision = @include ROOT . '/revision.php';
		return $revision;
	}
	catch(Exception $e){}
	
	return "";
}

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
	if(function_exists('logged_user') && (logged_user() instanceof Contact) && logged_user()->isMemberOfOwnerCompany()) {
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

	if (defined('CONSOLE_MODE') && CONSOLE_MODE) return;
	
	// If we don't have logged user prepare referer params and redirect user to login page
	if(!(logged_user() instanceof Contact)) {
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
 * @return Contact
 */
function logged_user() {
	return CompanyWebsite::instance()->getLoggedUser();
} // logged_user

//FIXME remove function
function active_project(){
	return null;
}

//FIXME remove function
function active_tag(){
	return null;
}


//FIXME remove function
function active_projects() {
	return true;
}

//FIXME remove function
function active_or_personal_project() {
	return true;
}

//FIXME remove function
function personal_project() {
	return true;
}
/**
 * 
 * @Feng 2.0 - ivazquez 
 * 
 */
function active_context() {
	return CompanyWebsite::instance()->getContext() ;
}

function current_dimension_id() {
	return array_var($_REQUEST,'currentdimension');
}

function current_member(){
	$did = current_dimension_id();
	if ( $did == 0 ) {
		return null ;
	}else{ 
		foreach (active_context() as $item){
			if ($item instanceof Member) {
				if ( $item->getDimensionId() == $did ) {
					return $item;
				}
			}
		}
	}
	return null ;   
}

function current_member_search(){
        $members = array();
        foreach (active_context() as $item){
                if ($item instanceof Member) {
                        $members[] = $item;
                }
        }
	return $members;   
}

function context_type() {
	foreach ( active_context() as $ctx ) {
		if ( $ctx instanceof Member ) {
			return "mixed";		
		}	
	}
	return "all";
}


/**
 * @author Ignacio Vazquez - elpepe.uy@gmail.com
 */
function active_context_members($full = true ) {
	
	$ctxMembers  = array ();
	if (is_array(active_context())) {
		foreach (active_context() as $ctx) {
			if ( $ctx instanceof Member ) {
				/* @var Dimension $ctx */
				$ctxMembers[$ctx->getId()] = $ctx->getId() ;
				if($full){
					foreach ( Members::getSubmembers($ctx, 1) as $sub ) {
						$ctxMembers[$sub->getId()] = $sub->getId() ;		
					}
				}
				
			}
			
			if  ( $full && $ctx instanceof Dimension ) {
				/// @var Dimension $ctx 
				foreach ($ctx->getAllMembers() as $member) {
					$ctxMembers[$member->getId()] = $member->getId() ;
					foreach ( Members::getSubmembers($member, 1) as $sub ) {
						$ctxMembers[$sub->getId()] = $sub->getId() ;
					}
				} 
			}
		}
	}
	return $ctxMembers ;
}

function get_context_from_array($ids){
	$context = array();
	foreach ($ids as $id) {
		$member = Members::findById($id) ;
		$context[] = $member;
	}
	return $context ;
}

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
	}

	$config_option->setValue($value);
	
	// update cache if available
	if (GlobalCache::isAvailable()) {
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
		if (logged_user() instanceof Contact) {
			$user_id = logged_user()->getId();
		} else if (is_null($default)) {
			$def_value = null;
			// check the cache for the option default value
			if (GlobalCache::isAvailable()) {
				$def_value = GlobalCache::get('user_config_option_def_'.$option, $success);
				if ($success) return $def_value;
			}
			// default value not found in cache
			$def_value = ContactConfigOptions::getDefaultOptionValue($option, $default);
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
	$option_value = ContactConfigOptions::getOptionValue($option, $user_id, $default);
	if (GlobalCache::isAvailable()) {
		GlobalCache::update('user_config_option_'.$user_id.'_'.$option, $option_value);
	}
	
	return $option_value;
} // user_config_option

function user_has_config_option($option_name, $user_id = 0, $workspace_id = 0) {
	//FIXME
	return;
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
	ContactConfigOptions::getOptionsByCategoryName($category_name, true);
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
	$config_option = ContactConfigOptions::getByName($option_name);
	if(!($config_option instanceof ContactConfigOption)) {
		return false;
	}
	$config_option->setContactValue($value, $user_id);
	
	// update cache if available
	if (GlobalCache::isAvailable()) {
		GlobalCache::update('user_config_option_'.$user_id.'_'.$option_name, $value);
	}
	
	return $config_option->save();
} // set_config_option


function alert($text) {
	evt_add("popup", array('title' => "Debug", 'message' => $text));
}
function alert_r($var) {
	alert(print_r($var,1));
}

function get_back_trace($return_array = false) {
	$back_trace = debug_backtrace();
	$array = array();
	foreach ($back_trace as $trace) 
		$array[] = $trace['file']." - line: ".$trace['line']." - ".(isset($trace['class'])?$trace['class']."::":"").$trace['function'];
	
	return ($return_array ? $array : print_r($array, 1));
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
	$module .= '-panel';
	$contact_pg_ids = ContactPermissionGroups::getPermissionGroupIdsByContactCSV(logged_user()->getId(),false);
	return TabPanelPermissions::instance()->isModuleEnabled($module, $contact_pg_ids);
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
    
	// try to find contact by some properties 
	$contact_id = array_var($user_data, "contact_id") ;
	$contact =  Contacts::instance()->findById($contact_id) ; 
	/*if (!$contact instanceof Contact) {
		$contact = Contacts::getByEmail(array_var($user_data, 'email'), true);
	}*/

	if (!$contact instanceof Contact) {
		// Create a new user
		$contact = new Contact();
		$contact->setUsername(array_var($user_data, 'username'));
		$contact->setDisplayName(array_var($user_data, 'display_name'));
		$contact->setCompanyId(array_var($user_data, 'company_id'));
		$contact->setUserType(array_var($user_data, 'type'));
		$contact->setTimezone(array_var($user_data, 'timezone'));
		$contact->setFirstname($contact->getObjectName() != "" ? $contact->getObjectName() : $contact->getUsername());
		$contact->setObjectName();
	} else {
		// Create user from contact
		$contact->setUserType(array_var($user_data, 'type'));
		if (array_var($user_data, 'company_id')) {
			$contact->setCompanyId(array_var($user_data, 'company_id'));
		}	
		$contact->setUsername(array_var($user_data, 'username'));
		$contact->setTimezone(array_var($user_data, 'timezone'));
	}
	$contact->save();
	if (is_valid_email(array_var($user_data, 'email'))) {
		$contact->addEmail(array_var($user_data, 'email'), 'personal', true);
	}
	
	
	//permissions
	$permission_group = new PermissionGroup();
	$permission_group->setName('User '.$contact->getId().' Personal');
	$permission_group->setContactId($contact->getId());
	$permission_group->setIsContext(false);
        $permission_group->setType("permission_groups");
	$permission_group->save();
	$contact->setPermissionGroupId($permission_group->getId());
	
	$contact_pg = new ContactPermissionGroup();
	$contact_pg->setContactId($contact->getId());
	$contact_pg->setPermissionGroupId($permission_group->getId());
	$contact_pg->save();

	if ( can_manage_security(logged_user()) ) {
		
		$sp = new SystemPermission();
		$rol_permissions=SystemPermissions::getRolePermissions(array_var($user_data, 'type'));
		foreach($rol_permissions as $pr){
			$sp->setPermission($pr);
		}
		$sp->setPermissionGroupId($permission_group->getId());

		$sp->setCanManageSecurity(array_var($user_data, 'can_manage_security'));
		$sp->setCanManageConfiguration(array_var($user_data, 'can_manage_configuration'));
		$sp->setCanManageTemplates(array_var($user_data, 'can_manage_templates'));
		$sp->setCanManageTime(array_var($user_data, 'can_manage_time'));
		$sp->setCanAddMailAccounts(array_var($user_data, 'can_add_mail_accounts'));
		$sp->setCanManageDimensions(array_var($user_data, 'can_manage_dimensions'));
		$sp->setCanManageDimensionMembers(array_var($user_data, 'can_manage_dimension_members'));
		$sp->setCanManageTasks(array_var($user_data, 'can_manage_tasks'));
		$sp->setCanTasksAssignee(array_var($user_data, 'can_task_assignee'));
		$sp->setCanManageBilling(array_var($user_data, 'can_manage_billing'));
		$sp->setCanViewBilling(array_var($user_data, 'can_view_billing'));
		
		Hook::fire('add_user_permissions', $sp, $other_permissions);
		if (!is_null($other_permissions) && is_array($other_permissions)) {
			foreach ($other_permissions as $k => $v) {
				$sp->setColumnValue($k, array_var($user_data, $k));
			}
		}
		$sp->save();
		
		if ($contact->isAdminGroup()) {
			// allow all un all dimensions if new user is admin
			$dimensions = Dimensions::findAll();
			$permissions = array();
			foreach ($dimensions as $dimension) {
				if ($dimension->getDefinesPermissions()) {
					$cdp = ContactDimensionPermissions::findOne(array("conditions" => "`permission_group_id` = ".$contact->getPermissionGroupId()." AND `dimension_id` = ".$dimension->getId()));
					if (!$cdp instanceof ContactDimensionPermission) {
						$cdp = new ContactDimensionPermission();
						$cdp->setPermissionGroupId($contact->getPermissionGroupId());
						$cdp->setContactDimensionId($dimension->getId());
					}
					$cdp->setPermissionType('allow all');
					$cdp->save();
					
					// contact member permisssion entries
					$members = $dimension->getAllMembers();
					foreach ($members as $member) {
						
						$ots = DimensionObjectTypeContents::getContentObjectTypeIds($dimension->getId(), $member->getObjectTypeId());
						$ots[]=$member->getObjectId();
						foreach ($ots as $ot) {
							$cmp = ContactMemberPermissions::findOne(array("conditions" => "`permission_group_id` = ".$contact->getPermissionGroupId()." AND `member_id` = ".$member->getId()." AND `object_type_id` = $ot"));
							if (!$cmp instanceof ContactMemberPermission) {
								$cmp = new ContactMemberPermission();
								$cmp->setPermissionGroupId($contact->getPermissionGroupId());
								$cmp->setMemberId($member->getId());
								$cmp->setObjectTypeId($ot);
							}
							$cmp->setCanWrite(1);
							$cmp->setCanDelete(1);
							$cmp->save();
							
							// Add persmissions to sharing table
							$perm = new stdClass();
							$perm->m = $member->getId();
							$perm->r= 1;
							$perm->w= 1;
							$perm->d= 1;
							$perm->o= $ot;
							$permissions[] = $perm ;
						}
					}
				}
			}
			
			if(count($permissions)){
				$sharingTableController = new SharingTableController();
				$sharingTableController->afterPermissionChanged($contact->getPermissionGroupId(), $permissions);
			}
			
		}
		
	}
	if(!isset($_POST['sys_perm'])){
		$rol_permissions=SystemPermissions::getRolePermissions(array_var($user_data, 'type'));
		$_POST['sys_perm']=array();
		foreach($rol_permissions as $pr){
			$_POST['sys_perm'][$pr]=1;
		}
		
	}
	if(!isset($_POST['mod_perm'])){
		$tabs_permissions=TabPanelPermissions::getRoleModules(array_var($user_data, 'type'));
		$_POST['mod_perm']=array();
		foreach($tabs_permissions as $pr){
			$_POST['mod_perm'][$pr]=1;
		}
	}
        
    $password = '';
	if (array_var($user_data, 'password_generator') == 'specify') {
		// Validate input
		$password = array_var($user_data, 'password');
		if (trim($password) == '') {
			throw new Error(lang('password value required'));
		} // if
		if ($password <> array_var($user_data, 'password_a')) {
			throw new Error(lang('passwords dont match'));
		} // if
                             
	} // if        

	$contact->setPassword($password);   
	$contact->save();

	$user_password = new ContactPassword();
	$user_password->setContactId($contact->getId());
	$user_password->setPasswordDate(DateTimeValueLib::now());
	$user_password->setPassword(cp_encrypt($password, $user_password->getPasswordDate()->getTimestamp()));
	$user_password->password_temp = $password;
	$user_password->save();
        
	if (array_var($user_data, 'autodetect_time_zone', 1) == 1) {
		set_user_config_option('autodetect_time_zone', 1, $contact->getId());
	}
	
	/* create contact for this user*/

	ApplicationLogs::createLog($contact, ApplicationLogs::ACTION_ADD);

  	$pg_id = $contact->getPermissionGroupId();
  	save_permissions($pg_id, $contact->isGuest());

	Hook::fire('after_user_add', $contact, $null);
	
	// Send notification
	try {
		if (array_var($user_data, 'send_email_notification') && $contact->getEmailAddress()) {
                    
			if (array_var($user_data, 'password_generator', 'link') == 'link') {
				// Generate link password
				$user = Contacts::getByEmail(array_var($user_data, 'email'));
				$token = sha1(gen_id() . (defined('SEED') ? SEED : ''));
				$timestamp = time() + 60*60*24;
				set_user_config_option('reset_password', $token . ";" . $timestamp, $user->getId());
				Notifier::newUserAccountLinkPassword($contact, $password, $token);

			} else {
				Notifier::newUserAccount($contact, $password);
			}
			
		}
	} catch(Exception $e) {
		Logger::log($e->getTraceAsString());
	} // try
	return $contact;
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

/**
 * Returns an array with the enum values of an enum column
 * @param string $table: name of the table to check
 * @param string $column: name of the enum column to retrieve its values
 * @return An array with the enum values of an enum column.
 */
function get_enum_values($table, $column) {
	$sql = "SHOW COLUMNS FROM `$table` LIKE '$column';";
	$result = DB::execute($sql);
	$row = $result->fetchRow();
	preg_match_all( "/'(.*?)'/" , $row['Type'], $enum_array );
	$enum_fields = $enum_array[1];
	return $enum_fields;
}


function get_user_dimensions_ids(){
		
	//All dimensions
		$all_dimensions = Dimensions::findAll();
		$dimensions_to_show = array();
		
		foreach ($all_dimensions as $dim){
			if (!$dim->getDefinesPermissions()){
				$dimensions_to_show [$dim->getId()] = $dim->getId();
			}
			else{
				$contact_pg_ids = ContactPermissionGroups::getPermissionGroupIdsByContactCSV(logged_user()->getId(),false);
				/*if dimension does not deny everything for each contact's PG, show it*/
				if (!$dim->deniesAllForContact($contact_pg_ids)){
					$dimensions_to_show [$dim->getId()] = $dim->getId();
				}
			}
		}
		return $dimensions_to_show;
}

function build_context_array($context_plain) {
	$context = null ;
	if (!empty($context_plain)) {
		$dimensions = json_decode($context_plain) ;
		if ($dimensions) {
			$context = array () ;
			foreach ($dimensions as $dimensionId => $members) {
				if ($members && is_array($members)) {
					//cambiar
					foreach ($members as $member) {
						if ($member && is_numeric($member)) { 
							$member = Members::findById($member) ;													
							if ($member instanceof Member ){
								$context[] = $member ;
							}
						}elseif($member === 0 && count($members)<=1){
							// IS root. Retrieve the dimension 
							$dimension = Dimensions::getDimensionById($dimensionId) ;								
							if ($dimension instanceof Dimension ){					
								$context[] = $dimension ;
							}
						}
					}
				}
			}
		}
	}
	return $context;
}

/**
 * @author Ignacio Vazquez - elpepe.uy@gmail.com
 * @param string  $tableName
 * @param array $cols
 * @param array $rows
 * @param int $packageSize
 */
function massiveInsert($tableName, $cols,  $rows, $packageSize = 100 ) {

	$total = count($rows);
	$totalPackets = ceil($total/$packageSize);
	$cols = implode(",", $cols);
	for ($i = 0 ; $i < $totalPackets ; $i++ ) {
		$sql = "INSERT INTO $tableName ($cols) VALUES  ";
		for ($j = $i * $packageSize ; $j < min ( ($i+1) * $packageSize , $total ) ; $j++ ) {
			$sql.= " (";
			$sql.="'".implode("','",$rows[$j])."'";
			$sql.=")";
			if ($j + 1 <  min ( ($i+1) * $packageSize , $total ) ){
				$sql.=",";
			}
		}
		//echo alert_r($sql);
		if (!DB::execute($sql)){
			throw new DBQueryError($sql);
		}
		$sql = null;
	}
	$cols = null;
} 


function prepare_email_addresses($addr_str) {
	// exclude \n \t characters
	$addr_str = str_replace(array("\n","\r","\t"), "", $addr_str);
	// replace ; with , to separate email addresses
	$addr_str = str_replace(";", ",", $addr_str);
	
	$result = array();
	$addresses = explode(",", $addr_str);
	foreach ($addresses as $addr) {
		$addr = trim($addr);
		if ($addr == '') continue;
		$pos = strpos($addr, "<");
		if ($pos !== FALSE && strpos($addr, ">", $pos) !== FALSE) {
			$name = trim(substr($addr, 0, $pos));
			$val = trim(substr($addr, $pos + 1, -1));
			if (preg_match(EMAIL_FORMAT, $val)) {
				$result[] = array($val, $name);
			}
		} else {
			if (preg_match(EMAIL_FORMAT, $addr)) {
				$result[] = array($addr);
			}
		}
	}
	return $result;
}

/**
 * Iused by installers (plugin installers) 
 */
function executeMultipleQueries($sql, &$total_queries = null , &$executed_queries = null ) {
	if(!trim($sql)) {
		$total_queries = 0;
		$executed_queries = 0;
		return true;
	} // if

	// Make it work on PHP 5.0.4
	$sql = str_replace(array("\r\n", "\r"), array("\n", "\n"), $sql);

	$queries = explode(";\n", $sql);
	if(!is_array($queries) || !count($queries)) {
		$total_queries = 0;
		$executed_queries = 0;
		return true;
	} 

	$total_queries = count($queries);
	foreach($queries as $query) {
		if(trim($query)) {
			if(@mysql_query(trim($query))) {
				$executed_queries++;
			} else {
				return false; 
			} 
		}
	}
	return true ;
}

function getAllRoleUsers($role){
	$contacts=Contacts::getAllUsers(" AND `user_type` = $role");
	$pgs=array();
	if(!$contacts)return false;
	foreach ($contacts as $contact){
		alert(" ".$contact->getObjectName());
		$pgs[]=$contact->getPermissionGroupId();
	}
	return $pgs;
}

function render_mailto($address) {
	return "<a href='mailto:$address'>$address</a>";
}

/**
 * Generic sort for many type of arrays
 * @param array $array
 * @param property, key or method $field 
 * @autor PHPepe.com
 */
function feng_sort($array, $field = 'getName', $id = 'getId', $removeDuplicateId = false){
	$ids = array();	
	$index = array(); 
	foreach ($array as $k => $row){
		// Elem is associative array and exists the key
		if (is_array($row) && array_key_exists($field, $row)){
			$val = strtolower($row[$field]);
			// Remove Duplicated ids
			if ($id && isset($row[$id])){
				if ($removeDuplicateId && isset($ids[$row[$id]])){
					continue ;
				}else{
					$ids[$row[$id]] = true ;
				}
			}
		}elseif (is_object($row) && ( isset($row->$field) || method_exists($row, $field))) {
			// Elem is an object and has $field as a propery or method
			if ( method_exists($row, $field)) {
				$val =  strtolower($row->$field());
			}elseif (property_exists($row, $field)){
				$val =  strtolower($row->$field);
			}

			// Remove Duplicated ids
			if ($id && method_exists($row, $field)){
				// $field is a method method
				if ($removeDuplicateId && isset($ids[$row->$id()])){
					continue ;
				}else{
					$ids[$row->$id()] = true ;
				}
			}
			
		}
		if (!empty($val) && !isset($index[$val]) ){
			$index[$val] = $row ;
		}else{
			$index[] = $row ;
		}
	}
	ksort($index);
	return $index; 
}

function controller_exists($name, $plugin_id) {
	$class_filename = ucfirst($name)."Controller.class.php";
	if ($plugin_id && ($plugin = Plugins::instance()->findById($plugin_id)) instanceof Plugin ){
		$plgName = $plugin->getName();
		return file_exists(ROOT."/plugins/".$plgName."/application/controllers/".$class_filename);
	}else{
		return file_exists(ROOT."/application/controllers/".$class_filename);
	}
}

function decodeAsciiHex($input) {
    $output = "";

    $isOdd = true;
    $isComment = false;

    for($i = 0, $codeHigh = -1; $i < strlen($input) && $input[$i] != '>'; $i++) {
        $c = $input[$i];

        if($isComment) {
            if ($c == '\r' || $c == '\n')
                $isComment = false;
            continue;
        }

        switch($c) {
            case '\0': case '\t': case '\r': case '\f': case '\n': case ' ': break;
            case '%': 
                $isComment = true;
            break;

            default:
                $code = hexdec($c);
                if($code === 0 && $c != '0')
                    return "";

                if($isOdd)
                    $codeHigh = $code;
                else
                    $output .= chr($codeHigh * 16 + $code);

                $isOdd = !$isOdd;
            break;
        }
    }

    if($input[$i] != '>')
        return "";

    if($isOdd)
        $output .= chr($codeHigh * 16);

    return $output;
}
function decodeAscii85($input) {
    $output = "";

    $isComment = false;
    $ords = array();
    
    for($i = 0, $state = 0; $i < strlen($input) && $input[$i] != '~'; $i++) {
        $c = $input[$i];

        if($isComment) {
            if ($c == '\r' || $c == '\n')
                $isComment = false;
            continue;
        }

        if ($c == '\0' || $c == '\t' || $c == '\r' || $c == '\f' || $c == '\n' || $c == ' ')
            continue;
        if ($c == '%') {
            $isComment = true;
            continue;
        }
        if ($c == 'z' && $state === 0) {
            $output .= str_repeat(chr(0), 4);
            continue;
        }
        if ($c < '!' || $c > 'u')
            return "";

        $code = ord($input[$i]) & 0xff;
        $ords[$state++] = $code - ord('!');

        if ($state == 5) {
            $state = 0;
            for ($sum = 0, $j = 0; $j < 5; $j++)
                $sum = $sum * 85 + $ords[$j];
            for ($j = 3; $j >= 0; $j--)
                $output .= chr($sum >> ($j * 8));
        }
    }
    if ($state === 1)
        return "";
    elseif ($state > 1) {
        for ($i = 0, $sum = 0; $i < $state; $i++)
            $sum += ($ords[$i] + ($i == $state - 1)) * pow(85, 4 - $i);
        for ($i = 0; $i < $state - 1; $i++)
            $ouput .= chr($sum >> ((3 - $i) * 8));
    }

    return $output;
}
function decodeFlate($input) {
    return @gzuncompress($input);
}

function getObjectOptions($object) {
    $options = array();
    if (preg_match("#<<(.*)>>#ismU", $object, $options)) {
        $options = explode("/", $options[1]);
        @array_shift($options);

        $o = array();
        for ($j = 0; $j < @count($options); $j++) {
            $options[$j] = preg_replace("#\s+#", " ", trim($options[$j]));
            if (strpos($options[$j], " ") !== false) {
                $parts = explode(" ", $options[$j]);
                $o[$parts[0]] = $parts[1];
            } else
                $o[$options[$j]] = true;
        }
        $options = $o;
        unset($o);
    }

    return $options;
}
function getDecodedStream($stream, $options) {
    $data = "";
    if (empty($options["Filter"]))
        $data = $stream;
    else {
        $length = !empty($options["Length"]) ? $options["Length"] : strlen($stream);
        $_stream = substr($stream, 0, $length);

        foreach ($options as $key => $value) {
            if ($key == "ASCIIHexDecode")
                $_stream = decodeAsciiHex($_stream);
            if ($key == "ASCII85Decode")
                $_stream = decodeAscii85($_stream);
            if ($key == "FlateDecode")
                $_stream = decodeFlate($_stream);
        }
        $data = $_stream;
    }
    return $data;
}
function getDirtyTexts(&$texts, $textContainers) {
    for ($j = 0; $j < count($textContainers); $j++) {
        if (preg_match_all("#\[(.*)\]\s*TJ#ismU", $textContainers[$j], $parts))
            $texts = array_merge($texts, @$parts[1]);
        elseif(preg_match_all("#Td\s*(\(.*\))\s*Tj#ismU", $textContainers[$j], $parts))
            $texts = array_merge($texts, @$parts[1]);
    }
}
function getCharTransformations(&$transformations, $stream) {
    preg_match_all("#([0-9]+)\s+beginbfchar(.*)endbfchar#ismU", $stream, $chars, PREG_SET_ORDER);
    preg_match_all("#([0-9]+)\s+beginbfrange(.*)endbfrange#ismU", $stream, $ranges, PREG_SET_ORDER);

    for ($j = 0; $j < count($chars); $j++) {
        $count = $chars[$j][1];
        $current = explode("\n", trim($chars[$j][2]));
        for ($k = 0; $k < $count && $k < count($current); $k++) {
            if (preg_match("#<([0-9a-f]{2,4})>\s+<([0-9a-f]{4,512})>#is", trim($current[$k]), $map))
                $transformations[str_pad($map[1], 4, "0")] = $map[2];
        }
    }
    for ($j = 0; $j < count($ranges); $j++) {
        $count = $ranges[$j][1];
        $current = explode("\n", trim($ranges[$j][2]));
        for ($k = 0; $k < $count && $k < count($current); $k++) {
            if (preg_match("#<([0-9a-f]{4})>\s+<([0-9a-f]{4})>\s+<([0-9a-f]{4})>#is", trim($current[$k]), $map)) {
                $from = hexdec($map[1]);
                $to = hexdec($map[2]);
                $_from = hexdec($map[3]);

                for ($m = $from, $n = 0; $m <= $to; $m++, $n++)
                    $transformations[sprintf("%04X", $m)] = sprintf("%04X", $_from + $n);
            } elseif (preg_match("#<([0-9a-f]{4})>\s+<([0-9a-f]{4})>\s+\[(.*)\]#ismU", trim($current[$k]), $map)) {
                $from = hexdec($map[1]);
                $to = hexdec($map[2]);
                $parts = preg_split("#\s+#", trim($map[3]));
                
                for ($m = $from, $n = 0; $m <= $to && $n < count($parts); $m++, $n++)
                    $transformations[sprintf("%04X", $m)] = sprintf("%04X", hexdec($parts[$n]));
            }
        }
    }
}
function getTextUsingTransformations($texts, $transformations) {
    $document = "";
    for ($i = 0; $i < count($texts); $i++) {
        $isHex = false;
        $isPlain = false;

        $hex = "";
        $plain = "";
        for ($j = 0; $j < strlen($texts[$i]); $j++) {
            $c = $texts[$i][$j];
            switch($c) {
                case "<":
                    $hex = "";
                    $isHex = true;
                break;
                case ">":
                    $hexs = str_split($hex, 4);
                    for ($k = 0; $k < count($hexs); $k++) {
                        $chex = str_pad($hexs[$k], 4, "0");
                        if (isset($transformations[$chex]))
                            $chex = $transformations[$chex];
                        $document .= html_entity_decode("&#x".$chex.";");
                    }
                    $isHex = false;
                break;
                case "(":
                    $plain = "";
                    $isPlain = true;
                break;
                case ")":
                    $document .= $plain;
                    $isPlain = false;
                break;
                case "\\":
                    $c2 = $texts[$i][$j + 1];
                    if (in_array($c2, array("\\", "(", ")"))) $plain .= $c2;
                    elseif ($c2 == "n") $plain .= '\n';
                    elseif ($c2 == "r") $plain .= '\r';
                    elseif ($c2 == "t") $plain .= '\t';
                    elseif ($c2 == "b") $plain .= '\b';
                    elseif ($c2 == "f") $plain .= '\f';
                    elseif ($c2 >= '0' && $c2 <= '9') {
                        $oct = preg_replace("#[^0-9]#", "", substr($texts[$i], $j + 1, 3));
                        $j += strlen($oct) - 1;
                        $plain .= html_entity_decode("&#".octdec($oct).";");
                    }
                    $j++;
                break;

                default:
                    if ($isHex)
                        $hex .= $c;
                    if ($isPlain)
                        $plain .= $c;
                break;
            }
        }
        $document .= "\n";
    }

    return $document;
}

function pdf2text($filename) {
    $infile = @file_get_contents($filename, FILE_BINARY);
    if (empty($infile))
        return "";

    $transformations = array();
    $texts = array();

    preg_match_all("#obj(.*)endobj#ismU", $infile, $objects);
    $objects = @$objects[1];

    for ($i = 0; $i < count($objects); $i++) {
        $currentObject = $objects[$i];

        if (preg_match("#stream(.*)endstream#ismU", $currentObject, $stream)) {
            $stream = ltrim($stream[1]);

            $options = getObjectOptions($currentObject);
            if (!(empty($options["Length1"]) && empty($options["Type"]) && empty($options["Subtype"])))
                continue;

            $data = getDecodedStream($stream, $options); 
            if (strlen($data)) {
                if (preg_match_all("#BT(.*)ET#ismU", $data, $textContainers)) {
                    $textContainers = @$textContainers[1];
                    getDirtyTexts($texts, $textContainers);
                } else
                    getCharTransformations($transformations, $data);
            }
        }
    }

    return getTextUsingTransformations($texts, $transformations);
}

function docx2text($filename) {
    return readZippedXML($filename, "word/document.xml");
}

function odt2text($filename) {
    return readZippedXML($filename, "content.xml");
}

function fodt2text($filename,$id) {    
    Env::useLibrary('ezcomponents');
    
    $odt = new ezcDocumentOdt();
    $odt->loadFile( $filename );

    $docbook = $odt->getAsDocbook();

    $converter = new ezcDocumentDocbookToRstConverter();
    $rst = $converter->convert( $docbook );
    
    $file_path_txt = 'tmp/fodt2text_' . $id . '.txt';
    file_put_contents( $file_path_txt, $rst );
    $content = file_get_contents($file_path_txt); //Guardamos archivo.txt en $archivo
    unlink($file_path_txt);
    return $content;
}

function readZippedXML($archiveFile, $dataFile) {
    // Create new ZIP archive
    $zip = new ZipArchive;

    // Open received archive file
    if (true === $zip->open($archiveFile)) {
        // If done, search for the data file in the archive
        if (($index = $zip->locateName($dataFile)) !== false) {
            // If found, read it to the string
            $data = $zip->getFromIndex($index);
            // Close archive file
            $zip->close();
            // Load XML from a string
            // Skip errors and warnings
            $xml = DOMDocument::loadXML($data, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
            // Return data without XML formatting tags
            return strip_tags($xml->saveXML());
        }
        $zip->close();
    }

    // In case of failure return empty string
    return "";
} 

function make_post_async($url, $params)	{
	foreach ($params as $key => &$val) {
		if (is_array($val)) $val = implode(',', $val);
		$post_params[] = $key.'='.urlencode($val);
	}
	$post_string = implode('&', $post_params);

	$parts = parse_url($url);

	$fp = fsockopen($parts['host'], isset($parts['port']) ? $parts['port'] : 80, $errno, $errstr, 30);

	$out = "POST ".$parts['path']." HTTP/1.1\r\n";
	$out.= "Host: ".$parts['host']."\r\n";
	$out.= "Content-Type: application/x-www-form-urlencoded\r\n";
	$out.= "Content-Length: ".strlen($post_string)."\r\n";
	$out.= "Connection: Close\r\n\r\n";
	if (isset($post_string)) $out.= $post_string;

	fwrite($fp, $out);
	sleep(1);
	fclose($fp);
}

/**
 * Checks if a column exists in a table
 *
 *  This function returns true if the column exists
 *
 * @param string $table_name Name of the table
 * @param string $col_name Name of the column
 * @return boolean
 */
function check_column_exists($table_name, $col_name) {
	$res = mysql_query("DESCRIBE `$table_name`", DB::connection()->getLink());
	while($row = mysql_fetch_array($res)) {
		if ($row['Field'] == $col_name) return true;
	}
	return false;
} // checkColumnExists

/**
 * Checks if a table exists
 *
 *  This function returns true if the table exists
 *
 * @param string $table_name Name of the table
 * @return boolean
 */
function checkTableExists($table_name) {
	$res = mysql_query("SHOW TABLES", DB::connection()->getLink());
	while ($row = mysql_fetch_array($res)) {
		if ($row[0] == $table_name) return true;
	}
	return false;
}
