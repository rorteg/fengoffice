<?php

/**
 * MailContent class
 * Generated on Wed, 15 Mar 2006 22:57:46 +0100 by DataObject generation tool
 *
 * @author Carlos Palma <chonwil@gmail.com>
 */
class MailContent extends BaseMailContent {

	private $mail_data = null;
	/**
	 * Cache of account
	 *
	 * @var MailAccount
	 */
	private $account;

	//protected $project;
	protected $workspaces = null;

	/**
	 * This project object is taggable
	 *
	 * @var boolean
	 */
	protected $is_taggable = true;
	
	/**
	 * Mail contents are searchable
	 *
	 * @var boolean
	 */
	protected $is_searchable = true;

	/**
	 * Array of searchable columns
	 *
	 * @var array
	 */
	protected $searchable_columns = array('from', 'from_name', 'to', 'cc', 'bcc', 'subject', 'body');
	 
	/**
	 * Project file is commentable object
	 *
	 * @var boolean
	 */
	protected $is_commentable = true;
	
	protected $mail_conversation_mail_ids;
	protected $mail_conversation_mail_ids_w_permissions;

	
	function getConversationMailIds($check_permissions = false){
		if ($check_permissions) {
			if (is_null($this->mail_conversation_mail_ids_w_permissions)) {
				$this->mail_conversation_mail_ids_w_permissions = MailContents::getMailIdsFromConversation($this, true);
			}
			return $this->mail_conversation_mail_ids_w_permissions;
		} else {
			if (is_null($this->mail_conversation_mail_ids)) {
				$this->mail_conversation_mail_ids = MailContents::getMailIdsFromConversation($this);
			}
			return $this->mail_conversation_mail_ids;
		} 
	}
	 
	/**
	 * Gets the owner mail account
	 *
	 * @return MailAccount
	 */
	function getAccount() {
		if (is_null($this->account)){
			$this->account = MailAccounts::findById($this->getAccountId());
		} //if
		return $this->account;
	}
	
	/* <MailData info> */
	function setSubject($subject) {
		if (strlen($subject) > 255) {
			parent::setSubject(substr($subject, 0, 252) . '...');
		} else {
			parent::setSubject($subject);
		}
		$this->getMailData()->setSubject($subject);
	}
	
	function getFullSubject() {
		return $this->getMailData()->getSubject();
	}
	
	function getTo() {
		return $this->getMailData()->getTo();
	}
	
	function setTo($to) {
		return $this->getMailData()->setTo($to);
	}
	
	function getCc() {
		return $this->getMailData()->getCc();
	}
	
	function setCc($cc) {
		return $this->getMailData()->setCc($cc);
	}
	
	function getBcc() {
		return $this->getMailData()->getBcc();
	}
	
	function setBcc($bcc) {
		return $this->getMailData()->setBcc($bcc);
	}
	
	function getBodyHtml() {
		return $this->getMailData()->getBodyHtml();
	}
	
	function setBodyHtml($html) {
		return $this->getMailData()->setBodyHtml($html);
	}
	
	function getBodyPlain() {
		return $this->getMailData()->getBodyPlain();
	}
	
	function setBodyPlain($plain) {
		return $this->getMailData()->setBodyPlain($plain);
	}
	/* </MailData info> */
	 
	/**
	 * Validate before save
	 *
	 * @access public
	 * @param array $errors
	 * @return null
	 */
	function validate(&$errors) {
		if(!$this->validatePresenceOf('uid')) {
			$errors[] = lang('uid required');
		} // if
		if(!$this->validatePresenceOf('account_id')) {
			$errors[] = lang('account id required');
		} // if
	} // validate
	
	function save() {
		parent::save();
		$this->getMailData()->setId($this->getId());
		$this->getMailData()->save();
	}

	/**
	 * 
	 * @return MailData
	 */
	function getMailData() {
		if (!$this->mail_data instanceof MailData) $this->mail_data = MailDatas::findById($this->getId());
		if (!$this->mail_data instanceof MailData) $this->mail_data = new MailData();
		return $this->mail_data;
	}
	
	function delete($delete_db_record = true) {
		$rows = DB::executeAll("SELECT count(`id`) as `c` FROM `".TABLE_PREFIX."mail_contents` WHERE `conversation_id` = " . DB::escape($this->getConversationId()));
		if (is_array($rows) && count($rows) > 0) {
			if ($rows[0]['c'] < 2) {
				// if no other emails in conversation, delete conversation
				DB::execute("DELETE FROM `".TABLE_PREFIX."mail_conversations` WHERE `id` = " . DB::escape($this->getCOnversationId()));
			}
		}
		if ($delete_db_record) {
			return parent::delete();
		} else {
			return $this->mark_as_deleted();
		}
	}
	
	function clearEverything() {
		$this->clearContentFile();
		$this->clearMailData();
		parent::clearEverything();
	}
	
	function clearMailData() {
		if ($this->getMailData() instanceof MailData) {
			$this->getMailData()->delete();
		}
	}
	
	function clearContentFile() {
		if ($this->getContentFileId() != '') {
			try {
				FileRepository::deleteFile($this->getContentFileId());
			} catch (Exception $e) {
				//Logger::log($e->getMessage());
			}
		}
	}
	
	function mark_as_deleted(){
		$this->setIsDeleted(true);
		$this->clearEverything();
		return $this->save();
	}

	function getTitle(){
		return $this->getSubject();
	}
	
	/**
	 * Returns the mail content. If it is in repository returns the file content,
	 * else tries to get the content from database (if column content exists).
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getContent() {
		if (FileRepository::isInRepository($this->getContentFileId())) {
			return FileRepository::getFileContent($this->getContentFileId());
		} else if ($this->getMailData()->columnExists('content')) {
			return $this->getMailData()->getContent();
		}
	} // getContent()
	


	/**
	 * Returns if the field is classified
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function getIsClassified() {
		$wspaces = $this->getWorkspaces();
		return (is_array($wspaces) && count($wspaces) > 0);
	} // getIsClassified()
	
	
	
	/**
	 * Returns if the mail is a draft
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function getIsDraft() {
		return ($this->getState() == 2);
	} // getIsDraft()
	
	
	/**
	 * Returns if the mail was sent
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function getIsSent() {
		return ($this->getState() == 1 || $this->getState() == 3);
	} // getIsSent()

	// ---------------------------------------------------
	//  URLs
	// ---------------------------------------------------

	/**
	 * Return view mail URL of this mail
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getViewUrl() {
		if ($this->getState() == 2)
			return $this->getEditUrl(); // For drafts only
		else
			return get_url('mail', 'view', $this->getId());
	} // getAccountUrl
	
	/**
	 * Return edit mail URL of this mail
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getEditUrl() {
		if ($this->getState() == 2)
			return get_url('mail', 'edit_mail', $this->getId()); // For drafts only
		else
			return get_url('mail', 'view', $this->getId());
	} // getAccountUrl
	
	function getShowContentsUrl() {
		return get_url('mail', 'view', $this->getId());
	} // getAccountUrl

	/**
	 * Return delete mail URL of this mail
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getDeleteUrl() {
		return get_url('mail', 'delete', $this->getId());
	} // getDeleteUrl

	/**
	 * Return classify mail URL of this mail
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getClassifyUrl() {
		return get_url('mail', 'classify', array( 'id' => $this->getId(), 'type' => 'email'));
	} // getClassifyUrl

	/**
	 * Return unclassify mail URL of this mail
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getUnclassifyUrl() {
		return get_url('mail', 'unclassify', array( 'id' => $this->getId(), 'type' => 'email'));
	} // getClassifyUrl
	
	/**
	 * Return send mail URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getSendMailUrl() {
		return get_url('mail', 'add_mail');
	} // getClassifyUrl

	/**
	 * Return reply mail URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getReplyMailUrl() {
		return get_url('mail', 'reply_mail', array( 'id' => $this->getId(), 'type' => 'email'));
	} // getReplyMailUrl
	
	
	/**
	 * Return forward mail URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getForwardMailUrl() {
		return get_url('mail', 'forward_mail', array( 'id' => $this->getId(), 'type' => 'email'));
	} // getForwardMailUrl
	
	/**
	 * Return print mail URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getPrintUrl() {
		return get_url('mail', 'print_mail', array( 'id' => $this->getId()));
	} // getPrintUrl
	
	function getSenderName() {
		$user = Users::getByEmail($this->getFrom());
		if ($user instanceof User && $user->canSeeUser(logged_user())) {
			return $user->getDisplayName();
		} else {
			$contact = Contacts::getByEmail($this->getFrom());
			if ($contact instanceof Contact && $contact->canView(logged_user())) {
				return $contact->getDisplayName();
			}
		}
		return $this->getFromName();
	}
	
	function getSenderUrl() {
		$user = Users::getByEmail($this->getFrom());
		if ($user instanceof User && $user->canSeeUser(logged_user())) {
			return $user->getCardUrl();
		} else {
			$contact = Contacts::getByEmail($this->getFrom());
			if ($contact instanceof Contact && $contact->canView(logged_user())) {
				return $contact->getCardUrl();
			}
		}
		return $this->getViewUrl();
	}
	
	// ---------------------------------------------------
	//  Permissions
	// ---------------------------------------------------

	/**
	 * Returns true if $user can view this email
	 *
	 * @param User $user
	 * @return boolean
	 */
	function canView(User $user) {	
		return can_read($user,$this);
		//return $this->getAccount()->getUserId() == $user->getId() || $user->isAdministrator();
	} // canView


	/**
	 * Returns true if $user can edit this email
	 *
	 * @param User $user
	 * @return boolean
	 */
	function canEdit(User $user) {	
		return can_write($user,$this);
//		return $this->getAccount()->getUserId() == $user->getId() || $user->isAdministrator();
	} // canEdit

	/**
	 * Check if specific user can add contacts to specific project
	 *
	 * @access public
	 * @param User $user
	 * @param Project $project
	 * @return booelean
	 */
	function canAdd(User $user, Project $project) {
		return can_add($user,$project,get_class(MailContents::instance()));
	} // canAdd

	/**
	 * Returns true if $user can delete this email
	 *
	 * @param User $user
	 * @return boolean
	 */
	function canDelete(User $user) {
		return can_delete($user,$this);
//		return $this->getAccount()->getUserId() == $user->getId() || $user->isAdministrator();
	} // canView

	// ---------------------------------------------------
	//  ApplicationDataObject implementation
	// ---------------------------------------------------

	function getSearchableColumnContent($column_name) {
		if ($column_name == 'body') {
			return $this->getTextBody();
		} else if ($this->getMailData()->columnExists($column_name)) {
			return $this->getMailData()->getColumnValue($column_name);
		} else {
			return parent::getSearchableColumnContent($column_name);
		}
	} // getSearchableColumnContent
	
    function addToSearchableObjects($wasNew){
    	$columns_to_drop = array();
    	if ($wasNew)
    		$columns_to_drop = $this->getSearchableColumns();
    	else {
			foreach ($this->getSearchableColumns() as $column_name){
				if (isset($this->searchable_composite_columns[$column_name])){
					foreach ($this->searchable_composite_columns[$column_name] as $colName){
						if ($this->isColumnModified($colName)){
							$columns_to_drop[] = $column_name;
							break;
						}
					}
				} else if ($column_name == 'body') {
					$columns_to_drop[] = $column_name;
				} else if ($this->getMailData()->columnExists($column_name) && $this->getMailData()->isColumnModified($column_name)) {
					$columns_to_drop[] = $column_name;
				} else if ($this->isColumnModified($column_name)) {
					$columns_to_drop[] = $column_name;
				}
			}
    	}
    	
    	if (count($columns_to_drop) > 0){
    		SearchableObjects::dropContentByObjectColumns($this,$columns_to_drop);
    		
	        foreach($columns_to_drop as $column_name) {
	          $content = $this->getSearchableColumnContent($column_name);
	          if(trim($content) <> '') {

	            $searchable_object = new SearchableObject();
	            
	            $searchable_object->setRelObjectManager(get_class($this->manager()));
	            $searchable_object->setRelObjectId($this->getObjectId());
	            $searchable_object->setColumnName($column_name);
	            $searchable_object->setContent($content);
	            $searchable_object->setProjectId(0);
	            $searchable_object->setIsPrivate(false);
	            $searchable_object->setUserId($this->getAccount() instanceof MailAccount ? $this->getAccount()->getUserId() : 0);
	            
	            $searchable_object->save();
	          } // if
	        } // foreach
    	} // if
    	
    	if ($wasNew){
        	SearchableObjects::dropContentByObjectColumns($this,array('uid'));
        	$searchable_object = new SearchableObject();
            
            $searchable_object->setRelObjectManager(get_class($this->manager()));
            $searchable_object->setRelObjectId($this->getObjectId());
            $searchable_object->setColumnName('uid');
            $searchable_object->setContent($this->getUniqueObjectId());
	        $searchable_object->setProjectId(0);
            $searchable_object->setIsPrivate(false);
	        $searchable_object->setUserId($this->getAccount()->getUserId());
            
            $searchable_object->save();
        }
    }
	
	/**
	 * Return object name
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getObjectName() {
		return $this->getName();
	} // getObjectName

	/**
	 * Return object type name
	 *
	 * @param void
	 * @return string
	 */
	function getObjectTypeName() {
		if (!$this->workspaces)
			$this->workspaces = $this->getUserWorkspaces();
		if (is_array($this->workspaces) && count($this->workspaces))
			return 'email';
		else
			return 'emailunclassified';
	} // getObjectTypeName

	/**
	 * Return object URl
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getObjectUrl() {
		return $this->getViewUrl();
	} // getObjectUrl


	/**
	 * Return value of 'subject' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getName() {
		return $this->getSubject();
	} // getSubject()


  function getDashboardObject(){
    	$projectId = "0";
    	$project = "";
    	if (count($this->getWorkspaces()) > 0) {
    		$type = "email";
    	} else {
    		$type = "emailunclassified";
    	}
    	$tags = project_object_tags($this);
    	
    	$deletedOn = $this->getTrashedOn() instanceof DateTimeValue ? ($this->getTrashedOn()->isToday() ? format_time($this->getTrashedOn()) : format_datetime($this->getTrashedOn(), 'M j')) : lang('n/a');
		if ($this->getTrashedById() > 0)
			$deletedBy = Users::findById($this->getTrashedById());
    	if (isset($deletedBy) && $deletedBy instanceof User) {
    		$deletedBy = $deletedBy->getDisplayName();
    	} else {
    		$deletedBy = lang("n/a");
    	}
		
    	if ($this->getState() == 1 || $this->getState() == 3 || $this->getState() == 5) {
    		$createdBy = $this->getCreatedBy();
    	}
    	if (isset($createdBy) && $createdBy instanceof User) {
    		$createdById = $createdBy->getId();
    		$createdBy = $createdBy->getDisplayName();
    	} else {
    		$createdById = 0;
    		$createdBy = $this->getFromName();
    	}
    	
  		$archivedOn = $this->getArchivedOn() instanceof DateTimeValue ? ($this->getArchivedOn()->isToday() ? format_time($this->getArchivedOn()) : format_datetime($this->getArchivedOn(), 'M j')) : lang('n/a');
  		if ($this->getArchivedById() > 0)
			$archivedBy = Users::findById($this->getArchivedById());
    	if (isset($archivedBy) &&  $archivedBy instanceof User) {
    		$archivedBy = $archivedBy->getDisplayName();
    	} else {
    		$archivedBy = lang("n/a");
    	}
    	
    	$sentTimestamp = $this->getReceivedDate() instanceof DateTimeValue ? ($this->getReceivedDate()->isToday() ? format_time($this->getReceivedDate()) : format_datetime($this->getReceivedDate())) : lang('n/a');
    	
		return array(
				"id" => $this->getObjectTypeName() . $this->getId(),
				"object_id" => $this->getId(),
				"name" => $this->getObjectName() != "" ? $this->getObjectName():lang('no subject'),
				"type" => $type,
				"tags" => $tags,
				"createdBy" => $createdBy,
				"createdById" => $createdById,
				"dateCreated" => $sentTimestamp,
				"updatedBy" => $createdBy,
				"updatedById" => $createdById,
				"dateUpdated" => $sentTimestamp,
				"wsIds" => $this->getWorkspacesIdsCSV(logged_user()->getWorkspacesQuery()),
    			"url" => $this->getObjectUrl(),
				"manager" => get_class($this->manager()),
    			"deletedById" => $this->getTrashedById(),
    			"deletedBy" => $deletedBy,
    			"dateDeleted" => $deletedOn,
    			"archivedById" => $this->getArchivedById(),
    			"archivedBy" => $archivedBy,
    			"dateArchived" => $archivedOn,
				"subject" => $this->getSubject(),
				"isRead" => $this->getIsRead(logged_user()->getId())
		);
	}
	
	/**
	 * Returns a plain text version of the email
	 * @return string
	 */
	function getTextBody() {
		if ($this->getBodyHtml()) {
			return html_to_text(html_entity_decode($this->getBodyHtml(),null, "UTF-8"));
		} else {
			return $this->getBodyPlain();
		}
	}
	
	
	function getFromContact(){
		$contacts = Contacts::findAll(array('conditions' => " email = '" . clean($this->getFrom()) . "' OR email2 = '" . clean($this->getFrom()) . "' OR email3 = '" . clean($this->getFrom()) . "' "));
		if (is_array($contacts) && count($contacts) > 0){
			$best_level = 4;
			$best_contact = null;
			if (count($contacts) > 1){
				foreach ($contacts as $contact){
					if ($best_level > 3 && $contact->getEmail3() == $this->getFrom()){
						$best_level = 3;
						$best_contact = $contact;
					} else if ($best_level > 2 && $contact->getEmail2() == $this->getFrom()){
						$best_level = 2;
						$best_contact = $contact;
					} else if ($best_level > 1 && $contact->getEmail() == $this->getFrom()){
						$best_level = 1;
						$best_contact = $contact;
					}
				}
				return $best_contact;
			}
			return $contacts[0];
		}
		return null;
	}
	
	function getLinkedObjects() {
		$conv_emails = MailContents::getMailsFromConversation($this);
		$objects = array();
		foreach ($conv_emails as $mail){
			if(logged_user()->isMemberOfOwnerCompany()) {
				$mail_objects = $mail->getAllLinkedObjects();
			} else {
				if (is_null($mail->linked_objects)) {
					$mail->linked_objects = LinkedObjects::getLinkedObjectsByObject($this, true);
				}
				$mail_objects = $mail->linked_objects;
			}
			if (is_array($mail_objects)){
				foreach ($mail_objects as $mo){
					$objects[] = $mo;
				}
			}
		}
		
		if ($this->isTrashed()) {
			$include_trashed = true;
		} else {
			$include_trashed = false;
		}
		
		if ($include_trashed) {
			return $objects;
		} else {
			$ret = array();
			if (is_array($objects) && count($objects)) {
				foreach ($objects as $o) {
					if (!$o instanceof ProjectDataObject || !$o->isTrashed()) {
						$ret[] = $o;
					}
				}
			}
			return $ret;
		}
	}
	

	/**
	 * Return object comments, filter private comments if user is not member of owner company
	 *
	 * @param void
	 * @return array
	 */
	function getComments() {
		return Comments::getCommentsByObjectIds(implode(',',$this->getConversationMailIds(true)), 'MailContents');
	} // getComments
	
	

	/**
	 * Return tag names for this object
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getTagNames() {
		//Seba ver si es lo mismo ya que siempre que un mail esta en un tag los otros tambien
		//if (user_config_option('show_emails_as_conversations',true,logged_user()->getId())){
		if (user_config_option('show_emails_as_conversations')){
			return Tags::getTagNamesByObjectIds(implode(',',$this->getConversationMailIds(true)), 'MailContents');
		}
		else{
			return parent::getTagNames();
		}
	} // getTagNames
	
	
	
	function addToStatus($amount) {
		try {
			DB::beginWork();
			$state = -1;
			$saved = "false";
			$state = $this->getState();
			$this->setState($state + $amount);
			$this->save();
			$saved = "true";
			DB::commit();
			return true;
		} catch (Exception $e) {
			Logger::log("Could not advance email state, email skipped: ".$e->getMessage()."\nmail_id=".$this->getId()."\nstate=$state\nsaved=$saved");
			DB::rollback();
		}
		return false;
	}
	
}
?>