<?php

  /**
  * UserWsConfigOptionValue class
  *  When user id is zero, the option is valid for the workspace in general.
  *  When workspace id is zero, the option is valid for the user in general.
  *  When workspace id is set, the setting should be specific for one Workspace.
  * 
  * @author Marcos Saiz <marcos.saiz@fengoffice.com>
  */
  class UserWsConfigOptionValue extends BaseUserWsConfigOptionValue {
    
//    /**
//    * Config handler instance
//    *
//    * @var ConfigHandler
//    */
//    private $config_handler;
//    
//    /**
//    * Return display name
//    *
//    * @param void
//    * @return string
//    */
//    function getDisplayName() {
//      return lang('config option name ' . $this->getName());
//    } // getDisplayName
//    
//    /**
//    * Return display description
//    *
//    * @param void
//    * @return string
//    */
//    function getDisplayDescription() {
//      return Localization::instance()->lang('config option desc ' . $this->getName(), '');
//    } // getDisplayDescription
//    
//    /**
//    * Return config handler instance
//    *
//    * @param void
//    * @return ConfigHandler
//    */
//    function getConfigHandler() {
//      if($this->config_handler instanceof ConfigHandler) return $this->config_handler;
//      
//      $handler_class = trim($this->getConfigHandlerClass());
//      if(!$handler_class) throw new Error('Handler class is not set for "' . $this->getName() . '" config option');
//      
//      $handler = new $handler_class();
//      if(!($handler instanceof ConfigHandler)) throw new Error('Handler class for "' . $this->getName() . '" config option is not valid');
//      
//      $handler->setConfigOption($this);
//      $handler->setRawValue(parent::getValue());
//      $this->config_handler = $handler;
//      return $this->config_handler;
//    } // getConfigHandler
//  
//    /**
//    * Return config value
//    *
//    * @access public
//    * @param void
//    * @return mixed
//    */
//    function getValue() {
//      $handler = $this->getConfigHandler();
//      $handler->setRawValue(parent::getValue());
//      return $handler->getValue();
//    } // getValue
//    
//    /**
//    * Set option value
//    *
//    * @access public
//    * @param mixed $value
//    * @return boolean
//    */
//    function setValue($value) {
//      $handler = $this->getConfigHandler();
//      $handler->setValue($value);
//      return parent::setValue($handler->getRawValue());
//    } // setValue
//    
//    /**
//    * Render this control
//    *
//    * @param string $control_name
//    * @return string
//    */
//    function render($control_name) {
//      $handler = $this->getConfigHandler();
//      return $handler->render($control_name);
//    } // render
    
  } // ConfigOption 

?>