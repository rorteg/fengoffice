<?php
class BaseContactWidget extends DataObject {
	
	/**
	 * Return manager instance
	 *
	 * @access protected
	 * @param void
	 * @return ContactWebpages 
	 */
	function manager() {
		if (! ($this->manager instanceof ContactWidgets))
			$this->manager = ContactWidgets::instance ();
		return $this->manager;
	}
	
	function getOptions() {
		return $this->getColumnValue('options');
	} 
	
	function getSection() {
		return $this->getColumnValue('section');
	}
	
	function getOrder() {
		return $this->getColumnValue('order');
	}
	
	
}