<?php
	if ( !defined( 'DIRECTORY_SEPARATOR' ) ) {
		define( 'DIRECTORY_SEPARATOR',
		strtoupper(substr(PHP_OS, 0, 3) == 'WIN') ? '\\' : '/'
		) ;
	}
	define('CONSOLE_MODE', true);
	define('APP_ROOT', realpath(dirname(__FILE__) . "/../../../../"));
	define('TEMP_PATH', realpath(APP_ROOT . '/tmp/'));
	
	// Include library
	require_once APP_ROOT . '/index.php';
	
	function my_log($msg) {
		file_put_contents(dirname(__FILE__).'/log.txt', "$msg\n", FILE_APPEND);
	}
	
	if (count($_FILES) > 0) {
		$file_info = array_shift($_FILES);
		
		$file_name = rand() . $file_info['name'];
		$file_name = preg_replace("/[^a-zA-Z0-9.]/", "_", $file_name);
		$file_url = ROOT_URL . "/tmp/" . $file_name;
		
		//Exclude non image files.
       /* $pattern = '/^.*\.(jpeg|JPEG|jpg|JPG|gif|GIF|png|PNG|bmp|BMP)$/';
        preg_match($pattern, $file_name, $matches, PREG_OFFSET_CAPTURE);
        if(count($matches) <= 0)
        	die('Invalid File');*/
        	
		copy($file_info['tmp_name'], TEMP_PATH . DIRECTORY_SEPARATOR . $file_name);
		unlink($file_info['tmp_name']);
		
		$err_msg = "";
		$func = preg_replace("/[^0-9]/", "", $_GET['CKEditorFuncNum']);
		
        echo "<script type=\"text/javascript\">";
        echo "window.parent.CKEDITOR.tools.callFunction($func, '" . str_replace("'", "\\'", $file_url) . "', '" .str_replace("'", "\\'", $err_msg). "');";
        echo "</script>";
	}
?>