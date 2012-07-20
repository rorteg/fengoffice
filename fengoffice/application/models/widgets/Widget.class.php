<?php class Widget extends BaseWidget {
		
	var $path = null;
	
	/**
	 * @var Plugin
	 */
	var $plugin = null ;
	
	var $options = null ;

	function getOptions() {
		if (is_null($this->options)){
			return json_decode($this->getColumnValue('default_options'));
		}else{
			return json_decode($this->options);
		}
	}
	
	function setOptions($options) {
		$this->options=$options;
	}
	
	/**
	 * @return Plugin 
	 */
	function getPlugin() {
		if (is_null($this->plugin) ) {
			if ($pid = $this->getPluginId()){
				$this->plugin = Plugins::instance()->findById($pid);
			}
		}
		return $this->plugin ;
	}
	
	function getPath() {
		$name = $this->getName() ;
		if ($this->path) {
			return $this->path ;
		}elseif (parent::getPath()) {
			$this->path = parent::getPath() ;
			return $this->path ;
		}else{
			// If path not set explicity: calc it
			$prefix = ROOT ;
			if ($plg = $this->getPlugin()){
				$plgName = $this->getPlugin()->getSystemName();
				$prefix = PLUGIN_PATH."/".$plgName  ;
			}
			$this->path = $prefix ."/application/widgets/$name/index.php" ;
		}
		return $this->path ;
	}
	
	function execute() {
		$path =  $this->getPath() ;
		if (file_exists( $path ) ) {
			include $path;
		}else{
			//throw new Error("Widget has invalid path: '".$path."'") ;
		}
	}
	
}