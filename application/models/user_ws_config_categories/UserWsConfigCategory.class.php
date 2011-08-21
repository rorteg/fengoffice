<?php

/**
 * ConfigCategory class
 *
 * @author Marcos Saiz <marcos.saiz@fengoffice.com>
 */
class UserWsConfigCategory extends BaseUserWsConfigCategory {

	/**
	 * Cached array of config options listed per user permissions
	 *
	 * @var array
	 */
	private $user_ws_config_options;

	/**
	 * Cached number of config options that current user can see
	 *
	 * @var integer
	 */
	private $count_user_ws_config_options;

	/**
	 * In DB we store uniqe name. This function will convert that name to the catetory display name in propert language
	 *
	 * @param void
	 * @return string
	 */
	function getDisplayName() {
		return lang('user ws config category name ' . $this->getName());
	} // getDisplayName

	/**
	 * Get DB description from lang based on category name
	 *
	 * @param void
	 * @return string
	 */
	function getDisplayDescription() {
		return Localization::instance()->lang('user ws config category desc ' . $this->getName(), '');
	} // getDisplayDescription

	// ---------------------------------------------------
	//  User Workspace options
	// ---------------------------------------------------

	/**
	 * Return user ws options array
	 *
	 * @param boolean $include_system_options Include system options in the result
	 * @return array
	 */
	function getUserWsOptions($include_system_options = false) {
		if(is_null($this->user_ws_config_options)) {
			$this->user_ws_config_options = UserWsConfigOptions::getOptionsByCategory($this, $include_system_options);
		} // if
		return $this->user_ws_config_options;
	} // getUserWsOptions

	/**
	 * Return the number of option in category that logged user can see
	 *
	 * @param boolean $include_system_options Include system options
	 * @return integer
	 */
	function countUserWsOptions($include_system_options = false) {
		if(is_null($this->count_user_ws_config_options)) {
			$this->count_user_ws_config_options = UserWsConfigOptions::countOptionsByCategory($this, $include_system_options);
		} // if
		return $this->count_user_ws_config_options;
	} //  countUserWsOptions

	/**
	 * Returns true if this category does not have any options to show to the user
	 *
	 * @param void
	 * @return boolean
	 */
	function isEmpty() {
		return $this->countUserWsOptions() < 1;
	} // isEmpty

	// ---------------------------------------------------
	//  Urls
	// ---------------------------------------------------

	/**
	 * View config category
	 *
	 * @param void
	 * @return null
	 */
	function getUpdateUrl() {
		return get_url('user', 'update_user_preferences', $this->getId());
	} // getUpdateUrl
	
	function getDefaultUpdateUrl() {
		return get_url('config', 'update_default_user_preferences', $this->getId());
	}

} // ConfigCategory

?>