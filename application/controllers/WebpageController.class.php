<?php

/**
 * Webpage controller
 *
 * @version 1.0
 * @author Carlos Palma <chonwil@gmail.com>
 */
class WebpageController extends ApplicationController {

	/**
	 * Construct the WebpageController
	 *
	 * @access public
	 * @param void
	 * @return WebpageController
	 */
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website');
	} // __construct

	function init() {
		require_javascript("og/WebpageManager.js");
		ajx_current("panel", "webpages", null, null, true);
		ajx_replace(true);
	}
	
	/**
	 * Add webpage
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function add() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$this->setTemplate('add');

		if(!ProjectWebpage::canAdd(logged_user(), active_or_personal_project())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$webpage = new ProjectWebpage();

		$webpage_data = array_var($_POST, 'webpage');
		if(!is_array($webpage_data)) {
			$webpage_data = array(
          'milestone_id' => array_var($_GET, 'milestone_id')
			); // array
		} // if

		if(is_array(array_var($_POST, 'webpage'))) {
			try {
				if(substr_utf($webpage_data['url'],0,7) != 'http://' && substr_utf($webpage_data['url'],0,7) != 'file://' && substr_utf($webpage_data['url'],0,8) != 'https://' && substr_utf($webpage_data['url'],0,6) != 'about:' && substr_utf($webpage_data['url'],0,6) != 'ftp://')
					$webpage_data['url'] = 'http://' . $webpage_data['url'];
				$webpage->setFromAttributes($webpage_data);

				$webpage->setIsPrivate(false);
				// Options are reserved only for members of owner company
				if(!logged_user()->isMemberOfOwnerCompany()) {
					$webpage->setIsPrivate(false);
				} // if

				DB::beginWork();
				$webpage->save();
				$webpage->setTagsFromCSV(array_var($webpage_data, 'tags'));

				$object_controller = new ObjectController();
				$object_controller->add_to_workspaces($webpage);
				$object_controller->link_to_new_object($webpage);
				$object_controller->add_subscribers($webpage);
				$object_controller->add_custom_properties($webpage);
					
				ApplicationLogs::createLog($webpage, $webpage->getWorkspaces(), ApplicationLogs::ACTION_ADD);
				DB::commit();


				flash_success(lang('success add webpage', $webpage->getTitle()));
				ajx_current("back");
				// Error...
			} catch(Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			} // try

		} // if

		tpl_assign('webpage', $webpage);
		tpl_assign('webpage_data', $webpage_data);
	} // add

	/**
	 * Edit specific webpage
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function edit() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$this->setTemplate('add');

		$webpage = ProjectWebpages::findById(get_id());
		if(!($webpage instanceof ProjectWebpage)) {
			flash_error(lang('webpage dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$webpage->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$webpage_data = array_var($_POST, 'webpage');
		if(!is_array($webpage_data)) {
			$tag_names = $webpage->getTagNames();
			$webpage_data = array(
          'url' => $webpage->getUrl(),
          'title' => $webpage->getTitle(),
          'description' => $webpage->getDescription(),
          'tags' => is_array($tag_names) ? implode(', ', $tag_names) : '',
          'is_private' => $webpage->isPrivate()
			); // array
		} // if

		if(is_array(array_var($_POST, 'webpage'))) {
			
			//MANAGE CONCURRENCE WHILE EDITING
			$upd = array_var($_POST, 'updatedon');
			if ($upd && $webpage->getUpdatedOn()->getTimestamp() > $upd && !array_var($_POST,'merge-changes') == 'true')
			{
				ajx_current('empty');
				evt_add("handle edit concurrence", array(
					"updatedon" => $webpage->getUpdatedOn()->getTimestamp(),
					"genid" => array_var($_POST,'genid')
				));
				return;
			}
			if (array_var($_POST,'merge-changes') == 'true'){					
				$this->setTemplate('view');
				$edited_wp = ProjectWebpages::findById($webpage->getId());
				ajx_set_no_toolbar(true);
				ajx_set_panel(lang ('tab name',array('name'=>$edited_wp->getTitle())));
				tpl_assign('object', $edited_wp);
				ajx_extra_data(array("title" => $edited_wp->getTitle(), 'icon'=>'ico-webpage'));				
				return;
			}
			
			try {
				$old_is_private = $webpage->isPrivate();
				$webpage->setFromAttributes($webpage_data);

				// Options are reserved only for members of owner company
				if(!logged_user()->isMemberOfOwnerCompany()) {
					$webpage->setIsPrivate($old_is_private);
				} // if

				DB::beginWork();
				
				$webpage->save();
				$webpage->setTagsFromCSV(array_var($webpage_data, 'tags'));

				$object_controller = new ObjectController();
				$object_controller->add_to_workspaces($webpage);
				$object_controller->link_to_new_object($webpage);
				$object_controller->add_subscribers($webpage);
				$object_controller->add_custom_properties($webpage);
				  
				ApplicationLogs::createLog($webpage, $webpage->getWorkspaces(), ApplicationLogs::ACTION_EDIT);

				$webpage->resetIsRead();
				
				DB::commit();
				
				flash_success(lang('success edit webpage', $webpage->getTitle()));
				ajx_current("back");

			} catch(Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			} // try
		} // if

		tpl_assign('webpage', $webpage);
		tpl_assign('webpage_data', $webpage_data);
	} // edit

	/**
	 * Delete specific webpage
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function delete() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$webpage = ProjectWebpages::findById(get_id());
		if(!($webpage instanceof ProjectWebpage)) {
			flash_error(lang('webpage dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$webpage->canDelete(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		try {

			DB::beginWork();
			$webpage->trash();
			ApplicationLogs::createLog($webpage, $webpage->getWorkspaces(), ApplicationLogs::ACTION_TRASH);
			DB::commit();

			flash_success(lang('success deleted webpage', $webpage->getTitle()));
			ajx_current("back");
		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error delete webpage'));
			ajx_current("empty");
		} // try
	} // delete

	function list_all() {
		ajx_current("empty");

		$project = active_project();
		$isProjectView = ($project instanceof Project);
			
		$start = (integer)array_var($_GET,'start');
		$limit = array_var($_GET,'limit');
		if (! $start) {
			$start = 0;
		}
		if (! $limit) {
			$limit = config_option('files_per_page');
		}
		$order = array_var($_GET, 'sort');
		if ($order == "updatedOn" || $order == "updated" || $order == "date" || $order == "dateUpdated") $order = "updated_on";
		else if ($order == "name") $order = "title";
		$orderdir = array_var($_GET, 'dir');
		$tag = array_var($_GET,'tag');
		$page = (integer) ($start / $limit) + 1;
		$hide_private = !logged_user()->isMemberOfOwnerCompany();

		if (array_var($_GET,'action') == 'delete') {
			$ids = explode(',', array_var($_GET, 'webpages'));
			$succ = 0; $err = 0;
			foreach ($ids as $id) {
				$web_page = ProjectWebpages::findById($id);
				if (isset($web_page) && $web_page->canDelete(logged_user())) {
					try{
						DB::beginWork();
						$web_page->trash();
						ApplicationLogs::createLog($web_page, $web_page->getWorkspaces(), ApplicationLogs::ACTION_TRASH);
						DB::commit();
						$succ++;
					} catch(Exception $e){
						DB::rollback();
						$err++;
					}
				} else {
					$err++;
				}
			}
			if ($succ > 0) {
				flash_success(lang("success delete objects", $succ));
			}
			if ($err > 0) {
				flash_error(lang("error delete objects", $err));
			}
		} else if (array_var($_GET, 'action') == 'tag') {
			$ids = explode(',', array_var($_GET, 'webpages'));
			$tagTag = array_var($_GET, 'tagTag');
			$tagged = 0;
			$not_tagged = 0;
			foreach ($ids as $id) {
				$web_page = ProjectWebpages::findById($id);
				if (isset($web_page) && $web_page->canEdit(logged_user())) {
					$arr_tags = $web_page->getTags();
					$arr = array();
					foreach ($arr_tags as $t) {
						$arr[] = $t->getTag();
					}
					if (!array_search($tagTag, $arr)) {
						$arr[] = $tagTag;
						$web_page->setTagsFromCSV(implode(',', $arr));
					}
					$tagged++;
				} else {
					$not_tagged++;
				}
			}
			if ($tagged > 0) {
				flash_success(lang("success tag objects", $tagged));
			} else {
				flash_success(lang("error tag objects", $not_tagged));
			}
		} else if (array_var($_GET, 'action') == 'untag') {
			$ids = explode(',', array_var($_GET, 'webpages'));
			$tagTag = array_var($_GET, 'tagTag');
			$untagged = 0;
			$not_untagged = 0;
			foreach ($ids as $id) {
				$web_page = ProjectWebpages::findById($id);
				if (isset($web_page) && $web_page->canEdit(logged_user())) {
					if ($tagTag != ''){
						$web_page->deleteTag($tagTag);								
					}else{
						$web_page->clearTags();
					}
					$untagged++;
				} else {
					$not_untagged++;
				}
			}
			if ($untagged > 0) {
				flash_success(lang("success untag objects", $untagged));
			} else {
				flash_success(lang("error untag objects", $not_untagged));
			}
		} else if (array_var($_GET, 'action') == 'markasread') {
			$ids = explode(',', array_var($_GET, 'ids'));
			$succ = 0; $err = 0;
				foreach ($ids as $id) {
				$webpage = ProjectWebpages::findById($id);
					try {
						$webpage->setIsRead(logged_user()->getId(),true);
						$succ++;
						
					} catch(Exception $e) {						
						$err ++;
					} // try
				}//for
			if ($succ <= 0) {
				flash_error(lang("error markasread files", $err));
			}
		} else if (array_var($_GET, 'action') == 'markasunread') {
			$ids = explode(',', array_var($_GET, 'ids'));
			$succ = 0; $err = 0;
				foreach ($ids as $id) {
				$webpage = ProjectWebpages::findById($id);
					try {
						$webpage->setIsRead(logged_user()->getId(),false);
						$succ++;
						
					} catch(Exception $e) {						
						$err ++;
					} // try
				}//for
			if ($succ <= 0) {
				flash_error(lang("error markasunread files", $err));
			}
		} else if (array_var($_GET, 'action') == 'move') {
			$wsid = array_var($_GET, "moveTo");
			$destination = Projects::findById($wsid);
			if (!$destination instanceof Project) {
				$resultMessage = lang('project dnx');
				$resultCode = 1;
			} else if (!can_add(logged_user(), $destination, 'ProjectWebpages')) {
				$resultMessage = lang('no access permissions');
				$resultCode = 1;
			} else {
				$count = 0;
				$ids = explode(',', array_var($_GET, 'ids', ''));
				for($i = 0; $i < count($ids); $i++){
					$id = $ids[$i];
					$webpage = ProjectWebpages::findById($id);
					if ($webpage instanceof ProjectWebpage && $webpage->canEdit(logged_user())){
						if (!array_var($_GET, "mantainWs")) {
							$removed = "";
							$ws = $webpage->getWorkspaces();
							foreach ($ws as $w) {
								if (can_add(logged_user(), $w, 'ProjectWebpages')) {
									$webpage->removeFromWorkspace($w);
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
						$webpage->addToWorkspace($destination);
						ApplicationLogs::createLog($webpage, $webpage->getWorkspaces(), $log_action, false, null, true, $log_data);
						$count++;
					};
				}; // for
				$resultMessage = lang("success move objects", $count);
				$resultCode = 0;
			}
		} else if (array_var($_GET,'action') == 'archive') {
			$ids = explode(',', array_var($_GET, 'webpages'));
			$succ = 0; $err = 0;
			foreach ($ids as $id) {
				$web_page = ProjectWebpages::findById($id);
				if (isset($web_page) && $web_page->canEdit(logged_user())) {
					try{
						DB::beginWork();
						$web_page->archive();
						ApplicationLogs::createLog($web_page, $web_page->getWorkspaces(), ApplicationLogs::ACTION_ARCHIVE);
						DB::commit();
						$succ++;
					} catch(Exception $e){
						DB::rollback();
						$err++;
					}
				} else {
					$err++;
				}
			}
			if ($succ > 0) {
				flash_success(lang("success archive objects", $succ));
			}
			if ($err > 0) {
				flash_error(lang("error archive objects", $err));
			}
		}

		$result = ProjectWebpages::getWebpages($project, $tag, $page, $limit, $order, $orderdir);
		if (is_array($result)) {
			list($webpages, $pagination) = $result;
			if ($pagination->getTotalItems() < (($page - 1) * $limit)){
				$start = 0;
				$page = 1;
				$result = ProjectWebpages::getWebpages($project,$tag,$page,$limit);
				if (is_array($result)) {
					list($webpages, $pagination) = $result;
				}else {
					$webpages = null;
					$pagination = 0 ;
				} // if
			}
		} else {
			$webpages = null;
			$pagination = 0 ;
		} // if
		/*tpl_assign('totalCount', $pagination->getTotalItems());
		tpl_assign('webpages', $webpages);
		tpl_assign('pagination', $pagination);
		tpl_assign('tags', Tags::getTagNames());*/

		$object = array(
			"totalCount" => $pagination->getTotalItems(),
			"start" => $start,
			"webpages" => array()
		);
		if (isset($webpages))
		{
			$index = 0;
			foreach ($webpages as $w) {
				$object["webpages"][] = array(
					"ix" => $index++,
					"id" => $w->getId(),
					"title" => $w->getTitle(),
					"description" => $w->getDescription(),
					"url" => $w->getUrl(),
					"tags" => project_object_tags($w),
					"wsIds" => $w->getWorkspacesIdsCSV(logged_user()->getWorkspacesQuery()),
					"updatedOn" => $w->getUpdatedOn() instanceof DateTimeValue ? ($w->getUpdatedOn()->isToday() ? format_time($w->getUpdatedOn()) : format_datetime($w->getUpdatedOn())) : '',
					"updatedOn_today" => $w->getUpdatedOn() instanceof DateTimeValue ? $w->getUpdatedOn()->isToday() : 0,
					"updatedBy" => $w->getUpdatedByDisplayName(),
					"updatedById" => $w->getUpdatedById(),
					"isRead" => $w->getIsRead(logged_user()->getId()),
				);
			}
		}
		ajx_extra_data($object);
		/*tpl_assign("listing", $object);*/
	}
	
	function view() {
		$this->addHelper("textile");
		$weblink = ProjectWebpages::findById(get_id());
		if(!($weblink instanceof ProjectWebpage)) {
			flash_error(lang('weblink dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$weblink->canView(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
		
		//read object for this user
		$weblink->setIsRead(logged_user()->getId(),true);

		tpl_assign('object', $weblink);
		tpl_assign('subscribers', $weblink->getSubscribers());
		ajx_extra_data(array("title" => $weblink->getTitle(), 'icon'=>'ico-weblink'));
		ajx_set_no_toolbar(true);
		
		ApplicationReadLogs::createLog($weblink, $weblink->getWorkspaces(), ApplicationReadLogs::ACTION_READ);
	}
} // WebpageController

?>