<?php

/**
 * Plugin class
 *
 * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
 */
class Plugin extends BasePlugin {
	var $systemName = null;
	
	var $metadata = null;
	
	function isActive() {
		return $this->getIsActivated ();
	}
	
	function isInstalled() {
		return $this->getIsInstalled ();
	}
	
	function activate() {
		$this->setIsActivated ( 1 );
		$this->save ();
	}
	
	function deactivate() {
		$this->setIsActivated ( 0 );
		$this->save ();
	}
	
	function update() {
		foreach ( $this->getUpdateFunctions () as $updateFunction ) {
			if (function_exists($updateFunction)) {
				call_user_func($updateFunction);
			}
		}
		$meta = $this->getMetadata ();
		$this->setVersion(array_var($meta,'version'));
		$this->save();
	}
	
	function getSystemName() {
		if (! $this->systemName) {
			$this->systemName = str_replace ( array (' ', '-', '�', '�', '�', '�', '�', '�', '.' ), array ('_' . '_', 'n', 'a', 'e', 'i', 'o', 'u', '' ), strtolower ( $this->getName () ) );
		}
		return $this->systemName;
	}
	
	/**
	 * Returns the path of the controller folder 
	 * @author Ignacio Vazquez - elpepe.uy@gmail.com
	 */
	function getControllerPath() {
		return ROOT . "/plugins/" . $this->getSystemName () . "/application/controllers/";
	}
	
	function getMetadata() {
		if ($this->metadata === null) {
			$this->scanMetadata ();
		}
		
		return $this->metadata;
	}
	
	/**
	 * Returns the path of the plugin folder  
	 * @author Ignacio Vazquez - elpepe.uy@gmail.com
	 */
	function getHooksPath() {
		return ROOT . "/plugins/" . $this->getSystemName () . "/hooks/";
	}
	
	/**
	 * Returns the path to the view folder 
	 * @author Ignacio Vazquez - elpepe.uy@gmail.com
	 */
	function getViewPath() {
		return ROOT . "/plugins/" . $this->getSystemName () . "/application/views/";
	}
	
	/**
	 * 
	 * @author Ignacio Vazquez - elpepe.uy@gmail.com
	 */
	function getLanguagePath() {
		return PLUGIN_PATH . "/" . $this->getSystemName () . "/language";
	}
	
	function scanMetadata() {
		$metadata = include PLUGIN_PATH . "/" . $this->getSystemName () . "/info.php";
		$this->metadata = $metadata;
	}
	
	/**
	 * @return mixed - false if not update avalable - update function otherwise 	
	 * @author Ignacio Vazquez <elpepe.uy at gmail.com>
	 */
	function updateAvailable() {
		$meta = $this->getMetadata ();
		$name = $this->getSystemName ();
		$installedVersion = $this->getVersion ();
		$nextVersion = array_var ( $meta, 'version' );
		return ($installedVersion && ($installedVersion < $nextVersion));
	}
	
	/**
	 * @author Ignacio Vazquez <elpepe.uy at gmail.com>
	 */
	function getUpdateFunctions() {
		$functions = array ();
		$meta = $this->getMetadata ();
		$name = $this->getSystemName ();
		$path = ROOT . "/plugins/$name/update.php";
		$installedVersion = $this->getVersion ();
		$nextVersion = array_var ( $meta, 'version' );
		if ($installedVersion && ($installedVersion < $nextVersion)) {
			if (file_exists ( $path )) {
				include_once $path;
				for($v = $installedVersion; $v < $nextVersion; $v++) {
					$function_name = $this->getSystemName () . "_update_" . $v . "_" . ($v + 1);
					if (function_exists ( $function_name )) {
						$functions[] = $function_name;						
					}
				}
			}
		}
		return $functions;
	}
}