<?php

  /**
  * EventInvitations class
  * Generated on Mon, 13 Oct 2008
  *
  * @author Alvaro Torterola <alvarotm01@gmail.com>
  */
  class EventInvitations extends BaseEventInvitations {    
  	function clearByUser($user) {
  		self::delete(array(
  			'`user_id` = ?',
  			$user->getId()
  		));
  	}
  } // EventInvitations 

?>