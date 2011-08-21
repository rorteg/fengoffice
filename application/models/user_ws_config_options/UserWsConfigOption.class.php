<?php

  /**
  * UserWsConfigOption class
  *
  * @author Marcos Saiz <marcos.saiz@fengoffice.com>
  */
  class UserWsConfigOption extends BaseUserWsConfigOption {
    
    /**
    * Config handler instance
    *
    * @var ConfigHandler
    */
    private $config_handler;
    
    /**
     * @var array Cached values for the option. Order: USER / WORKSPACE / Config option value
     */
    protected $option_values_cache = array();
    
    /**
    * Return display name
    *
    * @param void
    * @return string
    */
    function getDisplayName() {
      return lang('user ws config option name ' . $this->getName());
    } // getDisplayName
    
    /**
    * Return display description
    *
    * @param void
    * @return string
    */
    function getDisplayDescription() {
      return Localization::instance()->lang('user ws config option desc ' . $this->getName(), '');
    } // getDisplayDescription
    
    function useDefaultValue() {
    	$this->getConfigHandler()->setRawValue($this->getDefaultValue());
    }
    
    /**
    * Return config handler instance
    *
    * @param void
    * @return ConfigHandler
    */
    function getConfigHandler() {
      if($this->config_handler instanceof ConfigHandler) return $this->config_handler;
      
      $handler_class = trim($this->getConfigHandlerClass());
      if(!$handler_class) throw new Error('Handler class is not set for "' . $this->getName() . '" config option');
      
      $handler = new $handler_class();
      if(!($handler instanceof ConfigHandler)) throw new Error('Handler class for "' . $this->getName() . '" config option is not valid');
      
      $handler->setConfigOption($this);
      $handler->setRawValue($this->getUserValue(logged_user()->getId()));
      $this->config_handler = $handler;
      return $this->config_handler;
    } // getConfigHandler
  
    /**
     * Returns user value for the config option, or else default is returned
     *
     */
    function getUserValue($user_id = 0, $workspace_id = 0, $default = null){
    	//Return value if found
    	if (!is_null($this->getUserValueCached($user_id, $workspace_id))) 
    		return $this->getUserValueCached($user_id, $workspace_id);
    	else {
    		if (!$this->getUserValueNotFoundCache($user_id, $workspace_id)){
	    		$val = UserWsConfigOptionValues::findById(array('option_id' => $this->getId(), 'user_id'=>$user_id,'workspace_id' => $workspace_id));
	    		if ($val instanceof UserWsConfigOptionValue){
					$this->updateUserValueCache($user_id,$workspace_id,$val->getValue());
	    			return $val->getValue();
	    		} else $this->updateUserValueCache($user_id, $workspace_id, null);
    		}
    	}
    	
    	//Value not found, return default if searching for default user or workspace
    	if ($user_id == 0 || $workspace_id == 0){
    		//Return default settings
    		if (!is_null($default))
    			return $default;
    		else
    			return $this->getDefaultValue();
    	} 
    	
    	//Search user global preferences
    	if (!is_null($this->getUserValueCached($user_id, 0))) 
    		return $this->getUserValueCached($user_id, 0);
    	else {
    		if (!$this->getUserValueNotFoundCache($user_id, 0)){
	    		$val = UserWsConfigOptionValues::findById(array('option_id' => $this->getId(), 'user_id'=>$user_id,'workspace_id' => 0));
	    		if ($val instanceof UserWsConfigOptionValue){
					$this->updateUserValueCache($user_id,0,$val->getValue());
	    			return $val->getValue();
	    		} else $this->updateUserValueCache($user_id, 0, null);
    		}
    	}
    	
    	//Search workspace global preferences
    	if (!is_null($this->getUserValueCached(0, $workspace_id))) 
    		return $this->getUserValueCached(0, $workspace_id);
    	else {
    		if (!$this->getUserValueNotFoundCache(0, $workspace_id)){
	    		$val = UserWsConfigOptionValues::findById(array('option_id' => $this->getId(), 'user_id'=> 0,'workspace_id' => $workspace_id));
	    		if ($val instanceof UserWsConfigOptionValue){
					$this->updateUserValueCache(0,$workspace_id,$val->getValue());
	    			return $val->getValue();
	    		} else $this->updateUserValueCache(0, $workspace_id, null);
    		}
    	}
    	
    	//Nothing found, return default settings
    	if (!is_null($default))
    		return $default;
    	else
    		return $this->getDefaultValue();
    }
    
    private function updateUserValueCache($user_id, $workspace_id, $value){
    	if (!array_key_exists($user_id, $this->option_values_cache))
    		$this->option_values_cache[$user_id] = array();
    	$this->option_values_cache[$user_id][$workspace_id] = $value;
    }
    
    function getUserValueCached($user_id = 0, $workspace_id = 0){
    	if (array_key_exists($user_id, $this->option_values_cache))
    		if (array_key_exists($workspace_id, $this->option_values_cache[$user_id]))
    			return $this->option_values_cache[$user_id][$workspace_id];
    	return null;
    }
    
    /**
     * Returns true if the value was already searched in the database but not found.
     * 
     * @param $user_id
     * @param $workspace_id
     * @return unknown_type
     */
    function getUserValueNotFoundCache($user_id = 0, $workspace_id = 0){
    	if (array_key_exists($user_id, $this->option_values_cache))
    		if (array_key_exists($workspace_id, $this->option_values_cache[$user_id]))
    			return true;
    	return false;
    }
    
    /**
     * Set value  
     *
     */
    function setUserValue($new_value, $user_id = 0, $workspace_id = 0){
    	$val = null;
    	if (GlobalCache::isAvailable()) {
			$val = GlobalCache::get('user_copt_val_'.$user_id."_".$this->getId(), $success);
		}
    	if (!$val) $val = UserWsConfigOptionValues::findById(array('option_id' => $this->getId(), 'user_id' => $user_id, 'workspace_id' => $workspace_id));
		if(!$val){
			// if value was not found, create it
			$val = new UserWsConfigOptionValue();
			$val->setOptionId($this->getId());
			$val->setUserId($user_id);
			$val->setWorkspaceId($workspace_id);
		}
		$val->setValue($new_value);
		$val->save();
		$this->updateUserValueCache($user_id,$workspace_id, $val->getValue());
		
    	if (GlobalCache::isAvailable()) {
    		GlobalCache::update('user_copt_val_'.$user_id."_".$this->getId(), $val);
		}
    }
    
    /**
    * Return config default value
    *
    * @access public
    * @param void
    * @return mixed
    */
    function getValue() {
      $handler = $this->getConfigHandler();
      $handler->setRawValue(parent::getDefaultValue());
      return $handler->getValue();
    } // getDefaultValue
    
    /**
    * Set option value
    *
    * @access public
    * @param mixed $value
    * @return boolean
    */
    function setValue($value) {
      $handler = $this->getConfigHandler();
      $handler->setValue($value);
      return parent::setDefaultValue($handler->getRawValue());
    } //  setDefaultValue
    
    /**
    * Render this control
    *
    * @param string $control_name
    * @return string
    */
    function render($control_name) {
      $handler = $this->getConfigHandler();
      return $handler->render($control_name);
    } // render
    
    function save(){
    	parent::save();
    	UserWsConfigOptions::instance()->updateConfigOptionCache($this);
    }
    
  } // UserWsConfigOption 

?>