<?php

/**
 * BaseMailContent class
 *
 * @author Carlos Palma <chonwil@gmail.com>
 */
abstract class BaseMailContent extends ProjectDataObject {
  
  	protected $objectTypeIdentifier = 'mc';

	// -------------------------------------------------------
	//  Access methods
	// -------------------------------------------------------

	/**
	 * Return value of 'id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getId() {
		return $this->getColumnValue('id');
	} // getId()

	/**
	 * Set value of 'id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setId($value) {
		return $this->setColumnValue('id', $value);
	} // setId()

	/**
	 * Return value of 'account_id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getAccountId() {
		return $this->getColumnValue('account_id');
	} // getAccountId()

	/**
	 * Set value of 'account_id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setAccountId($value) {
		return $this->setColumnValue('account_id', $value);
	} // setAccountId()

	/**
	 * Return value of 'uid' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getUid() {
		return $this->getColumnValue('uid');
	} // getUid()

	/**
	 * Set value of 'uid' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setUid($value) {
		return $this->setColumnValue('uid', $value);
	} // setUid()

	/**
	 * Return value of 'from' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getFrom() {
		return $this->getColumnValue('from');
	} // getFrom()

	/**
	 * Set value of 'from' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setFrom($value) {
		return $this->setColumnValue('from', $value);
	} // setFrom()

	/**
	 * Return value of 'from_name' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getFromName() {
		return $this->getColumnValue('from_name');
	} // getFromName()

	/**
	 * Set value of 'from_name' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setFromName($value) {
		return $this->setColumnValue('from_name', $value);
	} // setFromName()

	/**
	 * Return value of 'subject' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getSubject() {
		return $this->getColumnValue('subject');
	} // getSubject()

	/**
	 * Set value of 'subject' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setSubject($value) {
		return $this->setColumnValue('subject', $value);
	} // setSubject()
	
	
	/**
	 * Return value of 'sent_date' field
	 *
	 * @access public
	 * @param void
	 * @return DateTimeValue
	 */
	function getSentDate() {
		return $this->getColumnValue('sent_date');
	} // getSentDate()

	/**
	 * Set value of 'sent_date' field
	 *
	 * @access public
	 * @param DateTimeValue $value
	 * @return boolean
	 */
	function setSentDate($value) {
		return $this->setColumnValue('sent_date', $value);
	} // setSentDate()
	
	/**
	 * Return value of 'received_date' field
	 *
	 * @access public
	 * @param void
	 * @return DateTimeValue
	 */
	function getReceivedDate() {
		return $this->getColumnValue('received_date');
	} // getReceivedDate()

	/**
	 * Set value of 'received_date' field
	 *
	 * @access public
	 * @param DateTimeValue $value
	 * @return boolean
	 */
	function setReceivedDate($value) {
		return $this->setColumnValue('received_date', $value);
	} // setReceivedDate()
	
	/**
	 * Return value of 'has_attachments' field
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function getHasAttachments() {
		return $this->getColumnValue('has_attachments');
	} // getHasAttachments()

	/**
	 * Set value of 'has_attachments' field
	 *
	 * @access public
	 * @param boolean $value
	 * @return boolean
	 */
	function setHasAttachments($value) {
		return $this->setColumnValue('has_attachments', $value);
	} // setHasAttachments()

	/**
	 * Return value of 'is_deleted' field
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function getIsDeleted() {
		return $this->getColumnValue('is_deleted');
	} // getIsDeleted()

	/**
	 * Set value of 'is_deleted' field
	 *
	 * @access public
	 * @param boolean $value
	 * @return boolean
	 */
	function setIsDeleted($value) {
		return $this->setColumnValue('is_deleted', $value);
	} // setIsDeleted()

	/**
	 * Return value of 'is_shared' field
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function getIsShared() {
		return $this->getColumnValue('is_shared');
	} // getIsShared()

	/**
	 * Set value of 'is_shared' field
	 *
	 * @access public
	 * @param boolean $value
	 * @return boolean
	 */
	function setIsShared($value) {
		return $this->setColumnValue('is_shared', $value);
	} // setIsShared()
	
	/**
	 * Return value of 'size' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getSize() {
		return $this->getColumnValue('size');
	} // getSize()

	/**
	 * Set value of 'size' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setSize($value) {
		return $this->setColumnValue('size', $value);
	} // setSize()
	
	
	/**
	 * Return value of 'state' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getState() {
		return $this->getColumnValue('state');
	} // getState()

	/**
	 * Set value of 'state' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setState($value) {
		return $this->setColumnValue('state', $value);
	} // setState()

	
    /**
    * Return value of 'is_private' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getIsPrivate() {
      return $this->getColumnValue('is_private');
    } // getIsPrivate()
    
    /**
    * Set value of 'is_private' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setIsPrivate($value) {
      return $this->setColumnValue('is_private', $value);
    } // setIsPrivate() 
    
    
  
    /**
    * Return value of 'created_on' field
    *
    * @access public
    * @param void
    * @return DateTimeValue 
    */
    function getCreatedOn() {
      return $this->getColumnValue('created_on');
    } // getCreatedOn()
    
    /**
    * Set value of 'created_on' field
    *
    * @access public   
    * @param DateTimeValue $value
    * @return boolean
    */
    function setCreatedOn($value) {
      return $this->setColumnValue('created_on', $value);
    } // setCreatedOn() 
    
    /**
    * Return value of 'created_by_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getCreatedById() {
      return $this->getColumnValue('created_by_id');
    } // getCreatedById()
    
    /**
    * Set value of 'created_by_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setCreatedById($value) {
      return $this->setColumnValue('created_by_id', $value);
    } // setCreatedById() 

    /**
    * Return value of 'created_on' field
    *
    * @access public
    * @param void
    * @return DateTimeValue 
    */
    function getUpdatedOn() {
      return $this->getColumnValue('created_on');
    } // getUpdatedOn()
    
    /**
    * Set value of 'created_on' field
    *
    * @access public   
    * @param DateTimeValue $value
    * @return boolean
    */
    function setUpdatedOn($value) {
      return true;
    } // setUpdatedOn() 
    
    /**
    * Return value of 'created_by_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getUpdatedById() {
      return $this->getColumnValue('created_by_id');
    } // getUpdatedById()
    
    /**
    * Set value of 'created_by_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setUpdatedById($value) {
      return true;
    } // setUpdatedById() 
    
    
    /** Return value of 'trashed_on' field
    *
    * @access public
    * @param void
    * @return DateTimeValue 
    */
    function getTrashedOn() {
      return $this->getColumnValue('trashed_on');
    } // getTrashedOn()
    
    /**
    * Set value of 'trashed_on' field
    *
    * @access public   
    * @param DateTimeValue $value
    * @return boolean
    */
    function setTrashedOn($value) {
      return $this->setColumnValue('trashed_on', $value);
    } // setTrashedOn() 
    
    /**
    * Return value of 'trashed_by_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getTrashedById() {
      return $this->getColumnValue('trashed_by_id');
    } // getTrashedById()
    
    /**
    * Set value of 'trashed_by_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setTrashedById($value) {
      return $this->setColumnValue('trashed_by_id', $value);
    } // setTrashedById()
    
       
    /**
    * Return value of 'imap_folder_name' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getImapFolderName() {
      return $this->getColumnValue('imap_folder_name');
    } // getImapFolderName()
    
    /**
    * Set value of 'imap_folder_name' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setImapFolderName($value) {
      return $this->setColumnValue('imap_folder_name', $value);
    } // setImapFolderName()
    
        /**
    * Return value of 'account_email' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getAccountEmail() {
      return $this->getColumnValue('account_email');
    } // getAccountEmail()
    
    /**
    * Set value of 'account_email' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setAccountEmail($value) {
      return $this->setColumnValue('account_email', $value);
    } // setAccountEmail()
    
    /**
    * Return value of 'content_file_id' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getContentFileId() {
      return $this->getColumnValue('content_file_id');
    } // getContentFileId()
    
    /**
    * Set value of 'content_file_id' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setContentFileId($value) {
      return $this->setColumnValue('content_file_id', $value);
    } // setContentFileId()

    /**
    * Return value of 'archived_by_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getArchivedById() {
      return $this->getColumnValue('archived_by_id');
    } // getArchivedById()
    
    /**
    * Set value of 'archived_by_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setArchivedById($value) {
      return $this->setColumnValue('archived_by_id', $value);
    } // setArchivedById()
    
    /** Return value of 'archived_on' field
    *
    * @access public
    * @param void
    * @return DateTimeValue 
    */
    function getArchivedOn() {
      return $this->getColumnValue('archived_on');
    } // getArchivedOn()
    
    /**
    * Set value of 'archived_on' field
    *
    * @access public   
    * @param DateTimeValue $value
    * @return boolean
    */
    function setArchivedOn($value) {
      return $this->setColumnValue('archived_on', $value);
    } // setArchivedOn() 
    
    /**
    * Return value of 'message_id' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getMessageId() {
      return $this->getColumnValue('message_id');
    } // getMessageId()
    
    /**
    * Set value of 'message_id' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setMessageId($value) {
      return $this->setColumnValue('message_id', $value);
    } // setMessageId()
    
    /**
    * Return value of 'conversation_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getConversationId() {
      return $this->getColumnValue('conversation_id');
    } // getConversationId()
    
    /**
    * Set value of 'conversation_id' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setConversationId($value) {
      return $this->setColumnValue('conversation_id', $value);
    } // setConversationId()
    
    /**
    * Return value of 'in_reply_to_id' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getInReplyToId() {
      return $this->getColumnValue('in_reply_to_id');
    } // getInReplyToId()
    
    /**
    * Set value of 'in_reply_to_id' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setInReplyToId($value) {
      return $this->setColumnValue('in_reply_to_id', $value);
    } // setInReplyToId()
    
    
    
	/**
	 * Return manager instance
	 *
	 * @access protected
	 * @param void
	 * @return MailContents
	 */
	function manager() {
		if(!($this->manager instanceof MailContents)) $this->manager = MailContents::instance();
		return $this->manager;
	} // manager
	
	 /**
    * Return value of 'sync' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getSync() {
      return $this->getColumnValue('sync');
    } // getSync()
    
    /**
    * Set value of 'sync' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setSync($value) {
      return $this->setColumnValue('sync', $value);
    } // setSync()
    

} // BaseMailContent

?>