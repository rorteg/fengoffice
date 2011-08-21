<?php

/**
 * Message controller
 *
 * @version 1.0
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class MessageController extends ApplicationController {

	/**
	 * Construct the MessageController
	 *
	 * @access public
	 * @param void
	 * @return MessageController
	 */
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website');
	} // __construct
	
	// ---------------------------------------------------
	//  Index
	// ---------------------------------------------------
	
	function init() {
//		require_javascript("og/HtmlPanel.js");
		require_javascript("og/MessageManager.js");
		ajx_current("panel", "messages", null, null, true);
		ajx_replace(true);
	}
	
	function list_all() {
		ajx_current("empty");
		
		// Get all variables from request
		$start = array_var($_GET,'start', 0);
		$limit = array_var($_GET,'limit', config_option('files_per_page'));
		$order = array_var($_GET,'sort');
		$order_dir = array_var($_GET,'dir');
		$tag = array_var($_GET,'tag');
		$action = array_var($_GET,'action');
		$attributes = array(
			"ids" => explode(',', array_var($_GET,'ids')),
			"types" => explode(',', array_var($_GET,'types')),
			"tag" => array_var($_GET,'tagTag'),
			"accountId" => array_var($_GET,'account_id'),
			"moveTo" => array_var($_GET,'moveTo'),
			"mantainWs" => array_var($_GET,'mantainWs'),
		);
		
		//Resolve actions to perform
		$actionMessage = array();
		if (isset($action)) {
			$actionMessage = $this->resolveAction($action, $attributes);
			if ($actionMessage["errorCode"] == 0) {
				flash_success($actionMessage["errorMessage"]);
			} else {
				flash_error($actionMessage["errorMessage"]);
			}
		} 

		// Get all emails and messages to display
		$project = active_project();
		list($messages, $pagination) = ProjectMessages::getMessages($tag, $project, $start, $limit, $order, $order_dir);
		$total = $pagination->getTotalItems();

		// Prepare response object
		$object = $this->prepareObject($messages, $start, $limit, $total);
		ajx_extra_data($object);
		tpl_assign("listing", $object);

	}
	
	/**
	 * Resolve action to perform
	 *
	 * @param string $action
	 * @param array $attributes
	 * @return string $message
	 */
	private function resolveAction($action, $attributes){
		$resultMessage = "";
		$resultCode = 0;
		switch ($action){
			case "delete":
				$succ = 0; $err = 0;
				for($i = 0; $i < count($attributes["ids"]); $i++){
					$id = $attributes["ids"][$i];
					$type = $attributes["types"][$i];
					
					switch ($type){
						case "message":
							$message = ProjectMessages::findById($id);
							if (isset($message) && $message->canDelete(logged_user())){
								try{
									DB::beginWork();
									$message->trash();
									ApplicationLogs::createLog($message, $message->getWorkspaces(), ApplicationLogs::ACTION_TRASH);
									DB::commit();
									$succ++;
								} catch(Exception $e){
									DB::rollback();
									$err++;
								}
							} else {
								$err++;
							}
							break;
							
						default: 
							$err++;
							break;
					}; // switch
				}; // for
				if ($err > 0) {
					$resultCode = 2;
					$resultMessage = lang("error delete objects", $err) . "<br />" . ($succ > 0 ? lang("success delete objects", $succ) : "");
				} else {
					$resultMessage = lang("success delete objects", $succ);
				}
				break;
			case "markasread":
				$succ = 0; $err = 0;
				for($i = 0; $i < count($attributes["ids"]); $i++){
					$id = $attributes["ids"][$i];
					$type = $attributes["types"][$i];
					
					switch ($type){
						case "message":
							$message = ProjectMessages::findById($id);
							try {
								
								$message->setIsRead(logged_user()->getId(),true);						
								$succ++;
								
							} catch(Exception $e) {
								$err ++;
							} // try
							break;							
						default: 
							$err++;
							break;
					}; // switch
				}; // for
				if ($err > 0) {
					$resultCode = 2;
					$resultMessage = lang("error markasread objects", $err) . "<br />" . ($succ > 0 ? lang("success markasread objects", $succ) : "");
				}
				break;
				case "markasunread":
				$succ = 0; $err = 0;
				for($i = 0; $i < count($attributes["ids"]); $i++){
					$id = $attributes["ids"][$i];
					$type = $attributes["types"][$i];
					
					switch ($type){
						case "message":
							$message = ProjectMessages::findById($id);
							try {
								
								$message->setIsRead(logged_user()->getId(),false);						
								$succ++;
								
							} catch(Exception $e) {
								$err ++;
							} // try
							break;							
						default: 
							$err++;
							break;
					}; // switch
				}; // for
				if ($err > 0) {
					$resultCode = 2;
					$resultMessage = lang("error markasunread objects", $err) . "<br />" . ($succ > 0 ? lang("success markasunread objects", $succ) : "");
				}
				break;
			case "tag":
				$tag = $attributes["tag"];
				for($i = 0; $i < count($attributes["ids"]); $i++){
					$id = $attributes["ids"][$i];
					$type = $attributes["types"][$i];
					switch ($type){
						case "message":
							$message = ProjectMessages::findById($id);
							if (isset($message) && $message->canEdit(logged_user())){
								Tags::addObjectTag($tag, $message);
								ApplicationLogs::createLog($message, $message->getWorkspaces(), ApplicationLogs::ACTION_TAG,false,null,true,$tag);
								$resultMessage = lang("success tag objects", '');
							};
							break;

						default:
							$resultMessage = lang("Unimplemented type: '" . $type . "'");// if
							$resultCode = 2;
							break;
					}; // switch
				}; // for
				break;
				
				case "untag":
				$tag = $attributes["tag"];
				for($i = 0; $i < count($attributes["ids"]); $i++){
					$id = $attributes["ids"][$i];
					$type = $attributes["types"][$i];
					switch ($type){
						case "message":
							$message = ProjectMessages::findById($id);
							if (isset($message) && $message->canEdit(logged_user())){
								if ($tag != ''){
									$message->deleteTag($tag);
								}else{
									$message->clearTags();
								}
								$resultMessage = lang("success untag objects", '');
							};
							break;

						default:
							$resultMessage = lang("Unimplemented type: '" . $type . "'");// if
							$resultCode = 2;
							break;
					}; // switch
				}; // for
				break;
				
			case "move":
				$wsid = $attributes["moveTo"];
				$destination = Projects::findById($wsid);
				if (!$destination instanceof Project) {
					$resultMessage = lang('project dnx');
					$resultCode = 1;
				} else if (!can_add(logged_user(), $destination, 'ProjectMessages')) {
					$resultMessage = lang('no access permissions');
					$resultCode = 1;
				} else {
					$count = 0;
					for($i = 0; $i < count($attributes["ids"]); $i++){
						$id = $attributes["ids"][$i];
						$type = $attributes["types"][$i];
						switch ($type){
							case "message":
								$message = ProjectMessages::findById($id);
								if ($message instanceof ProjectMessage && $message->canEdit(logged_user())){
									if (!$attributes["mantainWs"]) {
										$removed = "";
										$ws = $message->getWorkspaces();
										foreach ($ws as $w) {
											if (can_add(logged_user(), $w, 'ProjectMessages')) {
												$message->removeFromWorkspace($w);
												$removed .= $w->getId() . ",";
											}
										}
										$removed = substr($removed, 0, -1);
										$log_action = ApplicationLogs::ACTION_MOVE;
										$log_data = ($removed == "" ? "" : "from:$removed;") . "to:$wsid";
									} else {
										$log_action = ApplicationLogs::ACTION_COPY;
										$log_data = "to:$wsid";
									}
									$message->addToWorkspace($destination);
									ApplicationLogs::createLog($message, $message->getWorkspaces(), $log_action, false, null, true, $log_data);
									$count++;
								};
								break;
	
							default:
								$resultMessage = lang("Unimplemented type: '" . $type . "'");// if
								$resultCode = 2;
								break;
						}; // switch
					}; // for
					$resultMessage = lang("success move objects", $count);
					$resultCode = 0;
				}
				break;
			case "archive":
				$succ = 0; $err = 0;
				for($i = 0; $i < count($attributes["ids"]); $i++){
					$id = $attributes["ids"][$i];
					$type = $attributes["types"][$i];
					
					switch ($type){
						case "message":
							$message = ProjectMessages::findById($id);
							if (isset($message) && $message->canEdit(logged_user())){
								try{
									DB::beginWork();
									$message->archive();
									ApplicationLogs::createLog($message, $ws, ApplicationLogs::ACTION_ARCHIVE);
									DB::commit();
									$succ++;
								} catch(Exception $e){
									DB::rollback();
									$err++;
								}
							} else {
								$err++;
							}
							break;
							
						default: 
							$err++;
							break;
					}; // switch
				}; // for
				if ($err > 0) {
					$resultCode = 2;
					$resultMessage = lang("error archive objects", $err) . "<br />" . ($succ > 0 ? lang("success archive objects", $succ) : "");
				} else {
					$resultMessage = lang("success archive objects", $succ);
				}
				break;
			default:
				$resultMessage = lang("Unimplemented action: '" . $action . "'");// if 
				$resultCode = 2;	
				break;		
		} // switch
		return array("errorMessage" => $resultMessage, "errorCode" => $resultCode);
	}
	
	/**
	 * Prepares return object for a list of emails and messages
	 *
	 * @param array $totMsg
	 * @param integer $start
	 * @param integer $limit
	 * @return array
	 */
	private function prepareObject($totMsg, $start, $limit, $total) {
		$object = array(
			"totalCount" => $total,
			"start" => $start,
			"messages" => array()
		);
		for ($i = 0; $i < $limit; $i++){
			if (isset($totMsg[$i])){
				$msg = $totMsg[$i];
				if ($msg instanceof ProjectMessage){
					$text = $msg->getText();
					if (strlen($text) > 100) $text = substr_utf($text,0,100) . "...";
					$object["messages"][] = array(
					    "id" => $i,
						"ix" => $i,
						"object_id" => $msg->getId(),
						"type" => 'message',
						"title" => $msg->getTitle(),
						"text" => $text,
						"date" => $msg->getUpdatedOn() instanceof DateTimeValue ? ($msg->getUpdatedOn()->isToday() ? format_time($msg->getUpdatedOn()) : format_datetime($msg->getUpdatedOn())) : '',
						"is_today" => $msg->getUpdatedOn() instanceof DateTimeValue ? $msg->getUpdatedOn()->isToday() : 0,
						"wsIds" => $msg->getUserWorkspacesIdsCSV(logged_user()),
						"userId" => $msg->getCreatedById(),
						"userName" => $msg->getCreatedByDisplayName(),
						"updaterId" => $msg->getUpdatedById() ? $msg->getUpdatedById() : $msg->getCreatedById(),
						"updaterName" => $msg->getUpdatedById() ? $msg->getUpdatedByDisplayName() : $msg->getCreatedByDisplayName(),
						"tags" => project_object_tags($msg),
						"isRead" => $msg->getIsRead(logged_user()->getId()),						
					);
    			}
			}
		}
		return $object;
	}
	

	// ---------------------------------------------------
	//  Messages
	// ---------------------------------------------------
	
	/**
	 * View single message
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function view() {
		$this->addHelper('textile');

		$message = ProjectMessages::findById(get_id());
		if(!($message instanceof ProjectMessage)) {
			flash_error(lang('message dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$message->canView(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		//$this->setHelp("view_message");
		
		//read object for this user
		$message->setIsRead(logged_user()->getId(),true);
		tpl_assign('message', $message);
		tpl_assign('subscribers', $message->getSubscribers());
		ajx_extra_data(array("title" => $message->getTitle(), 'icon'=>'ico-message'));
		ajx_set_no_toolbar(true);
		
		ApplicationReadLogs::createLog($message, $message->getWorkspaces(), ApplicationReadLogs::ACTION_READ);
	} // view
	
	/**
	 * View a message in a printer-friendly format.
	 *
	 */
	function print_view() {
		$this->setLayout("html");
		$this->addHelper('textile');
		
		$message = ProjectMessages::findById(get_id());
		if(!($message instanceof ProjectMessage)) {
			flash_error(lang('message dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$message->canView(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
		
		tpl_assign('message', $message);
	} // print_view

	/**
	 * Add message
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function add() {
		$this->setTemplate('add_message');
		
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current('empty');
			return;
		}
		
		$message = new ProjectMessage();
		tpl_assign('message', $message);

		$message_data = array_var($_POST, 'message');
		if(!is_array($message_data)) {
			$message_data = array(); // array
		} // if
		tpl_assign('message_data', $message_data);

		if(is_array(array_var($_POST, 'message'))) {
			try {
				$message->setFromAttributes($message_data);
				$message->setIsPrivate(false);
				// Options are reserved only for members of owner company
				if(!logged_user()->isMemberOfOwnerCompany()) {
					$message->setIsImportant(false);
					$message->setCommentsEnabled(true);
					$message->setAnonymousCommentsEnabled(false);
				} // if

				DB::beginWork();
				$message->save();
				$message->setTagsFromCSV(array_var($message_data, 'tags'));
				
				$object_controller = new ObjectController();
				$object_controller->add_to_workspaces($message);
			    $object_controller->link_to_new_object($message);
				$object_controller->add_subscribers($message);
				$object_controller->add_custom_properties($message);
				
				ApplicationLogs::createLog($message, $message->getWorkspaces(), ApplicationLogs::ACTION_ADD);
			    
				DB::commit();

				flash_success(lang('success add message', $message->getTitle()));
				if (array_var($_POST, 'popup', false)) {
					ajx_current("reload");
	          	} else {
	          		ajx_current("back");
	          	}
	          	ajx_add("overview-panel", "reload");          	
					
				// Error...
			} catch(Exception $e) {
				DB::rollback();

				$message->setNew(true);
				flash_error($e->getMessage());
				ajx_current("empty");
				
			} // try

		} // if
	} // add

	/**
	 * Edit specific message
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function edit() {
		$this->setTemplate('add_message');
		
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current('empty');
			return;
		}

		$message = ProjectMessages::findById(get_id());
		if(!($message instanceof ProjectMessage)) {
			flash_error(lang('message dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$message->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
		
		$message_data = array_var($_POST, 'message');
		if(!is_array($message_data)) {
			$tag_names = $message->getTagNames();
			$message_data = array(
				'milestone_id' => $message->getMilestoneId(),
				'title' => $message->getTitle(),
				'text' => $message->getText(),
				'additional_text' => $message->getAdditionalText(),
				'tags' => is_array($tag_names) ? implode(', ', $tag_names) : '',
				'is_private' => $message->isPrivate(),
				'is_important' => $message->getIsImportant(),
				'comments_enabled' => $message->getCommentsEnabled(),
				'anonymous_comments_enabled' => $message->getAnonymousCommentsEnabled(),
			); // array
		} // if
		
		tpl_assign('message', $message);
		tpl_assign('message_data', $message_data);

		if(is_array(array_var($_POST, 'message'))) {
			try {
				
				//MANAGE CONCURRENCE WHILE EDITING
				$upd = array_var($_POST, 'updatedon');
				if ($upd && $message->getUpdatedOn()->getTimestamp() > $upd && !array_var($_POST,'merge-changes') == 'true')
				{
					ajx_current('empty');
					evt_add("handle edit concurrence", array(
						"updatedon" => $message->getUpdatedOn()->getTimestamp(),
						"genid" => array_var($_POST,'genid')
					));
					return;
				}
				if (array_var($_POST,'merge-changes') == 'true')
				{
					$this->setTemplate('view');
					$edited_note = ProjectMessages::findById($message->getId());
					tpl_assign('message', $edited_note);
					tpl_assign('subscribers', $edited_note->getSubscribers());
					ajx_extra_data(array("title" => $edited_note->getTitle(), 'icon'=>'ico-message'));
					ajx_set_no_toolbar(true);
					ajx_set_panel(lang ('tab name',array('name'=>$edited_note->getTitle())));					
					return;
				}
				
				$old_is_private = $message->isPrivate();
				$old_is_important = $message->getIsImportant();
				$old_comments_enabled = $message->getCommentsEnabled();
				$old_anonymous_comments_enabled = $message->getAnonymousCommentsEnabled();

				$message->setFromAttributes($message_data);

				// Options are reserved only for members of owner company
				if(!logged_user()->isMemberOfOwnerCompany()) {
					$message->setIsPrivate($old_is_private);
					$message->setIsImportant($old_is_important);
					$message->setCommentsEnabled($old_comments_enabled);
					$message->setAnonymousCommentsEnabled($old_anonymous_comments_enabled);
				} // if

				DB::beginWork();
				$message->save();
				$message->setTagsFromCSV(array_var($message_data, 'tags'));
				
				$object_controller = new ObjectController();
				$object_controller->add_to_workspaces($message);
			    $object_controller->link_to_new_object($message);
				$object_controller->add_subscribers($message);
				$object_controller->add_custom_properties($message);
				
				$message->resetIsRead();
				
				ApplicationLogs::createLog($message, $message->getWorkspaces(), ApplicationLogs::ACTION_EDIT);
			    
				DB::commit();
				
				flash_success(lang('success edit message', $message->getTitle()));
				if (array_var($_POST, 'popup', false)) {
					ajx_current("reload");
	          	} else {
	          		ajx_current("back");
	          	}

			} catch(Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			} // try
		} // if
	} // edit

	/**
	 * Delete specific message
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function delete() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current('empty');
			return;
		}
		
		ajx_current("empty");
		$message = ProjectMessages::findById(get_id());
		if(!($message instanceof ProjectMessage)) {
			flash_error(lang('message dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$message->canDelete(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		try {

			DB::beginWork();
			$message->trash();
			$ws = $message->getWorkspaces();
			ApplicationLogs::createLog($message, $ws, ApplicationLogs::ACTION_TRASH);
			DB::commit();

			flash_success(lang('success deleted message', $message->getTitle()));
			if (array_var($_POST, 'popup', false)) {
				ajx_current("reload");
          	} else {
          		ajx_current("back");
          	}
          	ajx_add("overview-panel", "reload");          	
		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error delete message'));
			ajx_current("empty");
		} // try
	} // delete



} // MessageController

?>