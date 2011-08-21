<?php

  /**
  * BaseEventInvitation class
  *
  * @author Alvaro Torterola <alvarotm01@gmail.com>
  */
  abstract class BaseEventInvitation extends DataObject {
  
    // -------------------------------------------------------
    //  Access methods
    // -------------------------------------------------------
  
    /**
    * Return value of 'event_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getEventId() {
      return $this->getColumnValue('event_id');
    } // getId()
    
    /**
    * Set value of 'event_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setEventId($value) {
      return $this->setColumnValue('event_id', $value);
    } // setId() 
    
    /**
    * Return value of 'user_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getUserId() {
      return $this->getColumnValue('user_id');
    } // getUserId()
    
    /**
    * Set value of 'user_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setUserId($value) {
      return $this->setColumnValue('user_id', $value);
    } // setUserId() 
    
    /**
    * Return value of 'invitation_state' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getInvitationState() {
      return $this->getColumnValue('invitation_state');
    } // getInvitationState()
    
    /**
    * Set value of 'invitation_state' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setInvitationState($value) {
      return $this->setColumnValue('invitation_state', $value);
    } // setInvitationState() 
    
    
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return EventInvitations 
    */
    function manager() {
      if(!($this->manager instanceof EventInvitations)) $this->manager = EventInvitations::instance();
      return $this->manager;
    } // manager
  
  } // BaseEventInvitation

?>