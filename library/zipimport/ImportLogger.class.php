<?php

class ImportLogger {

	private $logErrorFile;
	private $logStatusFile;

	function setLogErrorFile($filename) {
		$this->logErrorFile = $filename;
	}

	function setLogStatusFile($filename) {
		$this->logErrorFile = $filename;
	}

	private function __construct() {
		if (!isset($this->logErrorFile))
		$this->logErrorFile = CACHE_DIR . DIRECTORY_SEPARATOR . 'importErrorsLog.txt';
		if (!isset($this->logStatusFile))
		$this->logStatusFile = CACHE_DIR . DIRECTORY_SEPARATOR . 'importLog.txt';
	}

	function instance() {
		static $instance;
		if(!instance_of($instance, 'ImportLogger')) {
			$instance = new ImportLogger();
		} // if
		return $instance;
	} // instance

	private function writeLog($file, $message) {
		$message .= "\r\n";
		$handle = fopen($file, 'a');
		fputs($handle, $message, strlen(($message)));
		fclose($handle);
	}
	
	function log($message) {
		$this->writeLog($this->logStatusFile, $message);
	}

	function logError($message) {
		$this->writeLog($this->logErrorFile, $message);
	}
}

?>