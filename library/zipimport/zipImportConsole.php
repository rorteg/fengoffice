<?php

if ( !defined( 'DIRECTORY_SEPARATOR' ) ) {
	define( 'DIRECTORY_SEPARATOR',
	strtoupper(substr(PHP_OS, 0, 3) == 'WIN') ? '\\' : '/'
	) ;
}
define('CONSOLE_MODE', true);
define('APP_ROOT', realpath(dirname(__FILE__) . '/../../'));
define('TEMP_PATH', realpath(APP_ROOT . '/tmp/'));
  
// Include library
require_once APP_ROOT . '/index.php';
require_once APP_ROOT . '/library/zipimport/ZipImport.class.php';
require_once APP_ROOT . '/library/zipimport/ImportLogger.class.php';

ini_set('memory_limit', '256M');
@set_time_limit(0);

if(!isset($argv) || !is_array($argv)) {
//	die('There is no input arguments');
} // if

/* IMPORT PARAMETERS */

if (isset($argv[1])) {
	$zip_path = $argv[1];
} else {
	ImportLogger::instance()->logError('Missing Parameter: zip file name');
	die('Missing Parameter: file name or directory');
}
if (isset($argv[2])) {
	$parentWorkSpace = $argv[2];
} else {
	ImportLogger::instance()->logError('Missing Parameter: parent workspace id');
	die('Missing Parameter: parent workspace id');
}
if (isset($argv[3])) {
	$user_id = $argv[3];
} else {
	ImportLogger::instance()->logError('Missing Parameter: user id');
	die('Missing Parameter: user id');
}
/* ***************** */

	ImportLogger::instance()->log("Init Import ------------------------------------------------------------ \r\n");

	$imp = new ZipImport($parentWorkSpace);
	$is_zip = str_ends_with($zip_path, ".zip");
	if ($is_zip)
		$imp->extractToTmpDir($zip_path);
	else $imp->setDirectory($zip_path);
	
	$imp->initUser($user_id);
	$imp->makeWorkSpaces($is_zip ? null : $zip_path);
	if ($is_zip) $imp->deleteTmpDir();

	print "Complete\r\n";
	ImportLogger::instance()->log("\r\nEnd Import -------------------------------------------------------------");
?>
