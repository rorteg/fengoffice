<?php

  /**
  * TabPanels
  *
  * @author Alvaro Torterola <alvarotm01@gmail.com>
  */
  class TabPanels extends BaseTabPanels {
	
  	function getEnabled() {
  		return self::findAll(array("condtitions" => "`enabled` = 1"));
  	}
  } // TabPanels 

?>