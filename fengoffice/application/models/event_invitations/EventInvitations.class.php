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
  			'`contact_id` = ?',
  			$user->getId()
  		));
  	}
        
        function findByEvent($event_id) {
                return EventInvitations::findAll(array('conditions' => array('`event_id` = ?', $event_id)));
        }
  } // EventInvitations 

?>