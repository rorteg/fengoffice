<?php

/**
 * Handle all comment related requests
 *
 * @version 1.0
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class CommentController extends ApplicationController {

	/**
	 * Construct the CommentController
	 *
	 * @access public
	 * @param void
	 * @return FilesController
	 */
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website');
	} // __construct

	/**
	 * Add comment
	 *
	 * Through this controller only logged users can post (no anonymous comments here)
	 *
	 * @param void
	 * @return null
	 */
	function add() {
		$this->setTemplate('add_comment');

		$object_id = get_id('object_id');
		$object_manager = array_var($_GET, 'object_manager');

		if(!is_valid_function_name($object_manager)) {
			flash_error(lang('invalid request'));
			ajx_current("empty");
			return;
		} // if

		$object = get_object_by_manager_and_id($object_id, $object_manager);
		if(!($object instanceof ProjectDataObject) || !($object->canComment(logged_user()))) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$comment = new Comment();
		$comment_data = array_var($_POST, 'comment');

		tpl_assign('comment_form_object', $object);
		tpl_assign('comment', $comment);
		tpl_assign('comment_data', $comment_data);

		if(is_array($comment_data)) {
			try {
				try {
					$attached_files = ProjectFiles::handleHelperUploads(active_or_personal_project());
				} catch(Exception $e) {
					$attached_files = null;
				} // try

				$comment->setFromAttributes($comment_data);
				$comment->setRelObjectId($object_id);
				$comment->setRelObjectManager($object_manager);
//				if(!logged_user()->isMemberOfOwnerCompany()) {
					$comment->setIsPrivate(false);
//				} // if

				DB::beginWork();
				$comment->save();

				if(is_array($attached_files)) {
					foreach($attached_files as $attached_file) {
						$comment->attachFile($attached_file);
					} // foreach
				} // if

								// Subscribe user to object
				if(!$object->isSubscriber(logged_user())) {
					$object->subscribeUser(logged_user());
				} // if
				if (strlen($comment->getText()) < 100) {
					$comment_head = $comment->getText();
				} else {
					$lastpos = strpos($comment->getText(), " ", 100);
					if ($lastpos === false) $comment_head = $comment->getText();
					else $comment_head = substr($comment->getText(), 0, $lastpos) . "...";
				}
				$comment_head = html_to_text($comment_head);
				ApplicationLogs::createLog($object, $object->getWorkspaces(), ApplicationLogs::ACTION_COMMENT, false, null, true, $comment_head);

				DB::commit();

				flash_success(lang('success add comment'));

				ajx_current("reload");
			} catch(Exception $e) {
				DB::rollback();
				ajx_current("empty");
				flash_error($e->getMessage());
			} // try
		} // if
	} // add

	/**
	 * Edit comment
	 *
	 * @param void
	 * @return null
	 */
	function edit() {
		$this->setTemplate('add_comment');

		$comment = Comments::findById(get_id());
		if(!($comment instanceof Comment)) {
			flash_error(lang('comment dnx'));
			ajx_current("empty");
			return;
		} // if

		$object = $comment->getObject();
		if(!($object instanceof ProjectDataObject)) {
			flash_error(lang('object dnx'));
			ajx_current("empty");
			return;
		} // if
		
		if(trim($comment->getViewUrl())) {
			$redirect_to = $comment->getViewUrl();
		} elseif(trim($object->getObjectUrl())) {
			$redirect_to = $object->getObjectUrl();
		} // if
		
		if(!$comment->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$comment_data = array_var($_POST, 'comment');
		if(!is_array($comment_data)) {
			$comment_data = array(
          'text' => $comment->getText(),
          'is_private' => $comment->isPrivate(),
			); // array
		} // if

		tpl_assign('comment_form_object', $object);
		tpl_assign('comment', $comment);
		tpl_assign('comment_data', $comment_data);

		if(is_array(array_var($_POST, 'comment'))) {
			try {
				$old_is_private = $comment->isPrivate();

				$comment->setFromAttributes($comment_data);
				$comment->setRelObjectId($object->getObjectId());
				$comment->setRelObjectManager(get_class($object->manager()));
				if(!logged_user()->isMemberOfOwnerCompany()) $comment->setIsPrivate($old_is_private);

				DB::beginWork();
				$comment->save();
				ApplicationLogs::createLog($comment, $object->getWorkspaces(), ApplicationLogs::ACTION_EDIT);
				$object->onEditComment($comment);
				DB::commit();

				flash_success(lang('success edit comment'));
				ajx_current("back");
			} catch(Exception $e) {
				DB::rollback();
				ajx_current("empty");
				flash_error($e->getMessage());
			} // try
		} // if
	} // edit

	/**
	 * Delete specific comment
	 *
	 * @param void
	 * @return null
	 */
	function delete() {
		$comment = Comments::findById(get_id());
		if(!($comment instanceof Comment)) {
			flash_error(lang('comment dnx'));
			ajx_current("empty");
			return;
		} // if

		$object = $comment->getObject();
		if(!($object instanceof ProjectDataObject)) {
			flash_error(lang('object dnx'));
			ajx_current("empty");
			return;
		} // if

		if(trim($object->getObjectUrl())) $redirect_to = $object->getObjectUrl();

		if(!$comment->canDelete(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		try {
			DB::beginWork();
			$comment->trash();
			ApplicationLogs::createLog($comment, $object->getWorkspaces(), ApplicationLogs::ACTION_TRASH);
			DB::commit();

			flash_success(lang('success delete comment'));
			ajx_current("reload");
		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error delete comment'));
			ajx_current("empty");
		} // try

	} // delete

} // CommentController

?>