<?php

/**
 * ProjectEvent class
 * Generated on Tue, 04 Jul 2006 06:46:08 +0200 by DataObject generation tool
 *
 * @author Marcos Saiz <marcos.saiz@gmail.com>
 */
class ProjectEvent extends BaseProjectEvent {

	/**
	 * This project object is taggable
	 *
	 * @var boolean
	 */
	protected $is_taggable = true;

	/**
	 * Message comments are searchable
	 *
	 * @var boolean
	 */
	protected $is_searchable = true;

	/**
	 * Array of searchable columns
	 *
	 * @var array
	 */
	protected $searchable_columns = array('subject', 'description');

	/**
	 * Project Event is commentable object
	 *
	 * @var boolean
	 */
	protected $is_commentable = true;

	/**
	 * Array of invitated Users
	 *
	 * @var array
	 */
	private $event_invitations;

	/**
	 * Contruct the object
	 *
	 * @param void
	 * @return null
	 */
	function __construct() {
		//      $this->addProtectedAttribute('system_Eventname', 'Eventname', 'type_string', 'Eventsize');
		parent::__construct();
	} // __construct

	function getUserName(){
		$user = Users::findById($this->getCreatedById());
		if ($user instanceof User ) return $user->getUsername();
		else return null;
	}
	
	function getTitle(){
		return $this->getSubject();
	}
	
	function isRepetitive() {
		return $this->getRepeatD() > 0 || $this->getRepeatM() > 0 || $this->getRepeatY() > 0 || $this->getRepeatH() > 0;
	}

	// ---------------------------------------------------
	//  URLs
	// ---------------------------------------------------

	/**
	 * Return Event modification URL
	 *
	 * @param void
	 * @return string
	 */
	function getModifyUrl() {
		return get_url('event','edit',array('id'=> $this->getId() ));
		
		
		//return get_url('event','submitevent',array('id'=> $this->getId() ));
		//antes: return get_url('event','modify',array('id'=> $this->getId() ));
		//ejemplo:http://localhost/fengoffice/index.php?ajax=true&a=modify&id=8&day=02&month=4&year=2008&c=event&_dc=1208295398801
	} // getModifyUrl

	/**
	 * Return Event viewing URL
	 *
	 * @param void
	 * @return string
	 */
	function getOpenUrl() {
		return $this->getModifyUrl();
	} // getOpenUrl
	 
	 
	/**
	 * Return Event details URL
	 *
	 * @param void
	 * @return string
	 */
	function getDetailsUrl() {
		return get_url('event', 'viewevent', array('id' => $this->getId()));
	} // getDetailsUrl

	/**
	 * Return comments URL
	 *
	 * @param void
	 * @return string
	 */
	function getCommentsUrl() {
		return $this->getDetailsUrl() . '#objectComments';
	} // getCommentsUrl

	/**
	 * Return Event download URL
	 *
	 * @param void
	 * @return string
	 */
	function getDownloadUrl() {
		return $this->getModifyUrl();
	} // getDownloadUrl

	/**
	 * Return edit Event URL
	 *
	 * @param void
	 * @return string
	 */
	function getEditUrl() {
		return $this->getModifyUrl();
	} // getEditUrl

	/**
	 * Return delete Event URL
	 *
	 * @param void
	 * @return string
	 */
	function getDeleteUrl() {
		return get_url('event','delete',array('id'=> $this->getId() ));

	} // getDeleteUrl

	
	function getViewUrl() {
		return get_url('event', 'viewevent', array('id' => $this->getId()));
	}
	
	
	// ---------------------------------------------------
	//  Permissions
	// ---------------------------------------------------

	/**
	 * Check CAN_MANAGE_EventS permission
	 *
	 * @access public
	 * @param User $user
	 * @return boolean
	 */
	function canManage(User $user) {
		return can_write($user,$this);
	} // canManage


	/**
	 * Empty implementation of abstract method. Message determins if user have view access
	 *
	 * @param void
	 * @return boolean
	 */
	function canView(User $user) {
		return can_read($user,$this);
	} // canView

	/**
	 * Returns true if user can download this Event
	 *
	 * @param User $user
	 * @return boolean
	 */
	function canDownload(User $user) {
		return can_read($user,$this);
	} // canDownload

	/**
	 * Empty implementation of abstract methods. Messages determine does user have
	 * permissions to add comment
	 *
	 * @param void
	 * @return null
	 */
	function canAdd(User $user, Project $project) {
		return can_add($user,$project,get_class(ProjectEvents::instance()));	
	} // canAdd

	/**
	 * Check if specific user can edit this Event
	 *
	 * @access public
	 * @param User $user
	 * @return boolean
	 */
	function canEdit(User $user) {
		return can_write($user,$this);
	} // canEdit

	/**
	 * Returns true if $user can update Event options
	 *
	 * @param User $user
	 * @return boolean
	 */
	function canUpdateOptions(User $user) {
		return can_write($user,$this);
	} // canUpdateOptions

	/**
	 * Check if specific user can delete this comment
	 *
	 * @access public
	 * @param User $user
	 * @return boolean
	 */
	function canDelete(User $user) {
		return can_delete($user,$this);
	} // canDelete

	// ---------------------------------------------------
	//  System
	// ---------------------------------------------------

	function save() {
		parent::save();
		$id = $this->getId();
		$sql = "UPDATE `".TABLE_PREFIX."object_reminders` SET
			`date` = date_sub((SELECT `start` FROM `".TABLE_PREFIX."project_events` WHERE `id` = $id),
				interval `minutes_before` minute) WHERE
				`object_manager` = 'ProjectEvents' AND `object_id` = $id;";
		DB::execute($sql);
	}
	
	function delete() {
		// delete invitations
		$this->clearInvitations();
		parent::delete();
	}
	
	// ---------------------------------------------------
	//  ApplicationDataObject implementation
	// ---------------------------------------------------

	/**
	 * Return object name
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getObjectName() {
		return $this->getSubject();
	} // getObjectName

	/**
	 * Return object type name
	 *
	 * @param void
	 * @return string
	 */
	function getObjectTypeName() {
		return 'event';
	} // getObjectTypeName

	/**
	 * Return object URl
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getObjectUrl() {
		return $this->getDetailsUrl();
	} // getObjectUrl
	 
	 /**
	 * Validate before save
	 *
	 * @access public
	 * @param array $errors
	 * @return boolean
	 */
	function validate(&$errors) {
		if(!$this->validatePresenceOf('subject')) $errors[] = lang('event subject required');
		if(!$this->validateMaxValueOf('description',3000)) $errors[] = lang('event description maxlength');
		if(!$this->validateMaxValueOf('subject', 100)) $errors[] = lang('event subject maxlength');
	} // validate
	
	function getInvitations() {
		return $this->event_invitations;
	}
	
	function clearInvitations() {
		$this->event_invitations = array();
		EventInvitations::delete(array ('`event_id` = ?', $this->getId()));
	}
	
	function addInvitation($inv) {
		if (!is_array($this->event_invitations)) {
			$this->event_invitations = array();
		}
		if (isset($inv)) {
			$this->event_invitations[$inv->getUserId()] = $inv;
		}
	}

} // projectEvent

?>