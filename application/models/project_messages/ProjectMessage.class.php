<?php

/**
 * ProjectMessage class
 * Generated on Sat, 04 Mar 2006 12:21:44 +0100 by DataObject generation tool
 *
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class ProjectMessage extends BaseProjectMessage {

	/**
	 * This project object is taggable
	 *
	 * @var boolean
	 */
	protected $is_taggable = true;

	/**
	 * Project messages are searchable
	 *
	 * @var boolean
	 */
	protected $is_searchable = true;

	/**
	 * Array of searchable columns
	 *
	 * @var array
	 */
	protected $searchable_columns = array('title', 'text', 'additional_text');

	/**
	 * Messages are commentable
	 *
	 * @var boolean
	 */
	protected $is_commentable = true;

	/**
	 * Message is file container
	 *
	 * @var boolean
	 */
	protected $is_file_container = true;


	/**
	 * Cached array of related forms
	 *
	 * @var array
	 */
	private $related_forms;

	// ---------------------------------------------------
	//  Comments
	// ---------------------------------------------------

	/**
	 * Create new comment. This function is used by ProjectForms to post comments
	 * to the messages
	 *
	 * @param string $content
	 * @param boolean $is_private
	 * @return Comment or NULL if we fail to save comment
	 * @throws DAOValidationError
	 */
	function addComment($content, $is_private = false) {
		$comment = new Comment();
		$comment->setText($content);
		$comment->setIsPrivate($is_private);
		return $this->attachComment($comment);
	} // addComment
	


	// ---------------------------------------------------
	//  Related forms
	// ---------------------------------------------------

	/**
	 * Get project forms that are in relation with this message
	 *
	 * @param void
	 * @return array
	 */
	function getRelatedForms() {
		if(is_null($this->related_forms)) {
			$this->related_forms = ProjectForms::findAll(array(
          'conditions' => '`action` = ' . DB::escape(ProjectForm::ADD_COMMENT_ACTION) . ' AND `in_object_id` = ' . DB::escape($this->getId()),
          'order' => '`order`'
          )); // findAll
		} // if
		return $this->related_forms;
	} // getRelatedForms

	// ---------------------------------------------------
	//  Permissions
	// ---------------------------------------------------

	/**
	 * Check CAN_MANAGE_MESSAGES permission
	 *
	 * @access public
	 * @param User $user
	 * @return boolean
	 */
	function canManage(User $user) {
		can_write($user,$this);
	} // canManage

	/**
	 * Returns true if $user can access this message
	 *
	 * @param User $user
	 * @return boolean
	 */
	function canView(User $user) {
		return can_read($user,$this);
	} // canView

	/**
	 * Check if specific user can add messages to specific project
	 *
	 * @access public
	 * @param User $user
	 * @param Project $project
	 * @return booelean
	 */
	function canAdd(User $user, Project $project) {
		return can_add($user,$project,get_class(ProjectMessages::instance()));
	} // canAdd

	/**
	 * Check if specific user can edit this messages
	 *
	 * @access public
	 * @param User $user
	 * @return boolean
	 */
	function canEdit(User $user) {		
		return can_write($user,$this);
	} // canEdit

	/**
	 * Check if $user can update message options
	 *
	 * @param User $user
	 * @return boolean
	 */
	function canUpdateOptions(User $user) {
		return can_write($user,$this);
	} // canUpdateOptions

	/**
	 * Check if specific user can delete this messages
	 *
	 * @access public
	 * @param User $user
	 * @return boolean
	 */
	function canDelete(User $user) {
		return can_delete($user,$this);
	} // canDelete

	/**
	 * Check if specific user can comment this message
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function canAddComment(User $user) {
		return can_write($user,$this);
	} // canAddComment

	// ---------------------------------------------------
	//  URLS
	// ---------------------------------------------------

	/**
	 * Return view message URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getViewUrl() {
		return get_url('message', 'view', array('id' => $this->getId()));
	} // getViewUrl

	/**
	 * Return edit message URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getEditUrl() {
		return get_url('message', 'edit', array('id' => $this->getId()));
	} // getEditUrl


	/**
	 * Return delete message URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getDeleteUrl() {
		return get_url('message', 'delete', array('id' => $this->getId()));
	} // getDeleteUrl



	/**
	 * Return print view URL
	 *
	 * @return string
	 */
	function getPrintViewUrl() {
		return get_url('message', 'print_view', array('id' => $this->getId()));
	}
	
	// ---------------------------------------------------
	//  System
	// ---------------------------------------------------

	/**
	 * Delete this object
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function delete() {
		return parent::delete();
	} // delete

	/**
	 * Validate before save
	 *
	 * @access public
	 * @param array $errors
	 * @return null
	 */
	function validate(&$errors) {
		if(!$this->validatePresenceOf('title')) {
			$errors[] = lang('message title required');
		} // if
		//if(!$this->validatePresenceOf('text')) $errors[] = lang('message text required');
	} // validate

	// ---------------------------------------------------
	//  Override ApplicationDataObject methods
	// ---------------------------------------------------

	/**
	 * Return object name
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getObjectName() {
		return $this->getTitle();
	} // getObjectName

	/**
	 * Return object type name
	 *
	 * @param void
	 * @return string
	 */
	function getObjectTypeName() {
		return 'message';
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
	
} // ProjectMessage

?>