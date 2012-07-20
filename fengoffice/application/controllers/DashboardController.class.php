<?php

/**
 * Dashboard controller
 *
 * @author Ilija Studen <ilija.studen@gmail.com>, Marcos Saiz <marcos.saiz@fengoffice.com>
 */
class DashboardController extends ApplicationController {

	/**
	 * Construct controller and check if we have logged in user
	 *
	 * @param voidnetaractivi
	 * @return null
	 */
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website');
		$this->addHelper('calendar');
	} // __construct

	function init_overview() {
		require_javascript("og/OverviewManager.js");
		ajx_current("panel", "overview", null, null, true);
		ajx_replace(true);
	}
	
	/**
	 * 
	 * 
	 */
	public function activity_feed()
	{
		ajx_set_no_back(true);
		require_javascript("og/modules/dashboardComments.js");
		require_javascript("jquery/jquery.scrollTo-min.js");
		
		/* get query parameters */
		$filesPerPage = config_option('files_per_page');
		$start = array_var($_GET,'start') ? (integer)array_var($_GET,'start') : 0;
		$limit = array_var($_GET,'limit') ? array_var($_GET,'limit') : $filesPerPage;

		$order = array_var($_GET,'sort');
		$orderdir = array_var($_GET,'dir');
		$page = (integer) ($start / $limit) + 1;
		$hide_private = !logged_user()->isMemberOfOwnerCompany();
		
		$typeCSV = array_var($_GET, 'type');
		$types = null;
		if ($typeCSV) {
			$types = explode(",", $typeCSV);
		}
		$name_filter = array_var($_GET, 'name');
		$linked_obj_filter = array_var($_GET, 'linkedobject');
		$object_ids_filter = '';
		if (!is_null($linked_obj_filter)) {
			$linkedObject = Objects::findObject($linked_obj_filter);
			$objs = $linkedObject->getLinkedObjects();
			foreach ($objs as $obj) $object_ids_filter .= ($object_ids_filter == '' ? '' : ',') . $obj->getId();
		}
		
		$filters = array();
		if (!is_null($types)) $filters['types'] = $types;
		if (!is_null($name_filter)) $filters['name'] = $name_filter;
		if ($object_ids_filter != '') $filters['object_ids'] = $object_ids_filter;

		$user = array_var($_GET,'user');
		$trashed = array_var($_GET, 'trashed', false);
		$archived = array_var($_GET, 'archived', false);

		/* if there's an action to execute, do so */
		if (array_var($_GET, 'action') == 'delete') {
			$ids = explode(',', array_var($_GET, 'objects'));
			$result = Objects::getObjectsFromContext(active_context(), null, null, false, false, array('object_ids' => implode(",",$ids)));
			$objects = $result->objects;
			
			list($succ, $err) = $this->do_delete_objects($objects);
			
			if ($err > 0) {
				flash_error(lang('error delete objects', $err));
			} else {
				flash_success(lang('success delete objects', $succ));
			}
		} else if (array_var($_GET, 'action') == 'delete_permanently') {
			$ids = explode(',', array_var($_GET, 'objects'));
			$result = Objects::getObjectsFromContext(active_context(), null, null, true, false, array('object_ids' => implode(",",$ids)));
			$objects = $result->objects;
			
			list($succ, $err) = $this->do_delete_objects($objects, true);
			
			if ($err > 0) {
				flash_error(lang('error delete objects', $err));
			}
			if ($succ > 0) {
				flash_success(lang('success delete objects', $succ));
			}
		}else if (array_var($_GET, 'action') == 'markasread') {
			$ids = explode(',', array_var($_GET, 'objects'));
			list($succ, $err) = $this->do_mark_as_read_unread_objects($ids, true);
			
		}else if (array_var($_GET, 'action') == 'markasunread') {
			$ids = explode(',', array_var($_GET, 'objects'));
			list($succ, $err) = $this->do_mark_as_read_unread_objects($ids, false);
			
		}else if (array_var($_GET, 'action') == 'empty_trash_can') {

			$result = Objects::getObjectsFromContext(active_context(), 'trashed_on', 'desc', true);
			$objects = $result->objects;

			list($succ, $err) = $this->do_delete_objects($objects, true);		
			if ($err > 0) {
				flash_error(lang('error delete objects', $err));
			}
			if ($succ > 0) {
				flash_success(lang('success delete objects', $succ));
			}
		} else if (array_var($_GET, 'action') == 'archive') {
			$ids = explode(',', array_var($_GET, 'objects'));
			list($succ, $err) = $this->do_archive_unarchive_objects($ids, 'archive');
			if ($err > 0) {
				flash_error(lang('error archive objects', $err));
			} else {
				flash_success(lang('success archive objects', $succ));
			}
		} else if (array_var($_GET, 'action') == 'unarchive') {
			$ids = explode(',', array_var($_GET, 'objects'));
			list($succ, $err) = $this->do_archive_unarchive_objects($ids, 'unarchive');
			if ($err > 0) {
				flash_error(lang('error unarchive objects', $err));
			} else {
				flash_success(lang('success unarchive objects', $succ));
			}
		}
		else if (array_var($_GET, 'action') == 'unclassify') {
			$ids = explode(',', array_var($_GET, 'objects'));
			$err = 0;
			$succ = 0;
			foreach ($ids as $id) {
				$split = explode(":", $id);
				$type = $split[0];
				if ($type == 'MailContents') {
					$email = MailContents::findById($split[1]);
					if (isset($email) && !$email->isDeleted() && $email->canEdit(logged_user())){
						if (MailController::do_unclassify($email)) $succ++;
						else $err++;
					} else $err++;
				}
			}
			if ($err > 0) {
				flash_error(lang('error unclassify emails', $err));
			} else {
				flash_success(lang('success unclassify emails', $succ));
			}
		}
		else if (array_var($_GET, 'action') == 'restore') {
			$errorMessage = null;
			$ids = explode(',', array_var($_GET, 'objects'));
			$success = 0; $error = 0;
			foreach ($ids as $id) {
				$obj = Objects::findObject($id);
				if ($obj->canDelete(logged_user())) {
					try {
						$obj->untrash($errorMessage);
						ApplicationLogs::createLog($obj, ApplicationLogs::ACTION_UNTRASH);
						$success++;
					} catch (Exception $e) {
						$error++;
					}
				} else {
					$error++;
				}
			}
			if ($success > 0) {
				flash_success(lang("success untrash objects", $success));
			}
			if ($error > 0) {
				$errorString = is_null($errorMessage) ? lang("error untrash objects", $error) : $errorMessage;
				flash_error($errorString);
			}
		} 
		/*FIXME else if (array_var($_GET, 'action') == 'move') {
			$wsid = array_var($_GET, "moveTo");
			$destination = Projects::findById($wsid);
			if (!$destination instanceof Project) {
				$resultMessage = lang('project dnx');
				$resultCode = 1;
			} else if (!can_add(logged_user(), $destination, 'ProjectMessages')) {
				$resultMessage = lang('no access permissions');
				$resultCode = 1;
			} else {
				$ids = explode(',', array_var($_GET, 'objects'));
				$count = 0;
				DB::beginWork();
				foreach ($ids as $id) {
					$split = explode(":", $id);
					$type = $split[0];
					$obj = Objects::findObject($split[1]);
					$mantainWs = array_var($_GET, "mantainWs");
					if ($type != 'Projects' && $obj->canEdit(logged_user())) {
						if ($type == 'MailContents') {
							$email = MailContents::findById($split[1]);
							$conversation = MailContents::getMailsFromConversation($email);
							foreach ($conversation as $conv_email) {
								$count += MailController::addEmailToWorkspace($conv_email->getId(), $destination, $mantainWs);
								if (array_var($_GET, 'classify_atts') && $conv_email->getHasAttachments()) {
									MailUtilities::parseMail($conv_email->getContent(), $decoded, $parsedEmail, $warnings);
									$classification_data = array();
									for ($j=0; $j < count(array_var($parsedEmail, "Attachments", array())); $j++) {
										$classification_data["att_".$j] = true;		
									}
									$tags = implode(",", $conv_email->getTagNames());
									MailController::classifyFile($classification_data, $conv_email, $parsedEmail, array($destination), $mantainWs, $tags);
								}								
							}
							$count++;
						} else {
							if (!$mantainWs || $type == 'ProjectTasks' || $type == 'ProjectMilestones') {
								$removed = "";
								$ws = $obj->getWorkspaces();
								foreach ($ws as $w) {
									if (can_add(logged_user(), $w, $type)) {
										$obj->removeFromWorkspace($w);
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
							$obj->addToWorkspace($destination);
							ApplicationLogs::createLog($obj, $log_action, false, null, true, $log_data);
							$count++;
						}
					}
				}
				if ($count > 0) {
					$reload = true;
					DB::commit();
					flash_success(lang("success move objects", $count));
				} else {
					DB::rollback();
				}
			}
		}*/
		
		$filterName = array_var($_GET,'name');
		$result = null;
		
		$context = active_context();

		$obj_type_types = array('content_object');
		if (array_var($_GET, 'include_comments')) $obj_type_types[] = 'comment';
		
		$pagination = Objects::getObjects($context,$start,$limit,$order,$orderdir,$trashed,$archived, $filters,$start, $limit, $obj_type_types);
		$result = $pagination->objects; 
		$total_items = $pagination->total ;
		 
		if(!$result) $result = array();

		/* prepare response object */
		$info = array();

		foreach ($result as $obj /* @var $obj Object */) {
			
			$info_elem =  $obj->getArrayInfo($trashed, $archived);
			
			$instance = Objects::instance()->findObject($info_elem['object_id']);
			$info_elem['url'] = $instance->getViewUrl();
		
			if( method_exists($instance, "getText"))
				$info_elem['content'] = $instance->getText();
			
			$info_elem['picture'] = $instance->getCreatedBy()->getPictureUrl();
			$info_elem['friendly_date'] = friendly_date($instance->getCreatedOn());
			$info_elem['comment'] = $instance->getComments();		
			
			/* @var $instance Contact  */
			if ($instance instanceof  Contact /* @var $instance Contact  */ ) {
				if( $instance->isCompany() ) {
					$info_elem['icon'] = 'ico-company';
					$info_elem['type'] = 'company';
				}
			}
			$info_elem['isRead'] = $instance->getIsRead(logged_user()->getId()) ;
			$info_elem['manager'] = get_class($instance->manager()) ;
			
			$info[] = $info_elem;
			
		}
		
		$listing = array(
			"totalCount" => $total_items,
			"start" => $start,
			"objects" => $info
		);
		
		tpl_assign("feeds", $listing);
	}
	
	/**
	 * Show dashboard index page
	 *
	 * @param void
	 * @return null
	 */
	function index() {
		$this->setHelp('dashboard');
		ajx_set_no_toolbar(true);
		
		$logged_user = logged_user();
		
		$activity_log = null;
		$include_private = $logged_user->isMemberOfOwnerCompany();
		$include_silent = $logged_user->isAdminGroup();

		// FIXME
		$activity_log = array();//ApplicationLogs::getOverallLogs($include_private, $include_silent, $wscsv, config_option('dashboard_logs_count', 15));

		/* FIXME if (user_config_option('show charts widget') && module_enabled('reporting')) {
			$charts = ProjectCharts::getChartsAtProject(active_project(), active_tag());
			tpl_assign('charts', $charts);
			
			if (BillingCategories::count() > 0 && active_project() instanceof Project){
				tpl_assign('billing_chart_data', active_project()->getBillingTotalByUsers(logged_user()));
			}
		}*/
		if (user_config_option('show messages widget') && module_enabled('notes')) {
			//FIXME list($messages, $pagination) = ProjectMessages::getMessages(active_tag(), active_project(), 0, 10, '`updated_on`', 'DESC', false);
			tpl_assign('messages', $messages);
		}
		if (user_config_option('show comments widget')) {
			//FIXME $comments = Comments::getSubscriberComments(active_project(), $tag);
			tpl_assign('comments', $comments);
		}
		if (user_config_option('show documents widget') && module_enabled('documents')) {
			//FIXME list($documents, $pagination) = ProjectFiles::getProjectFiles(active_project(), null, false, ProjectFiles::ORDER_BY_MODIFYTIME, 'DESC', 1, 10, false, active_tag(), null);
			tpl_assign('documents', $documents);
		}
		
		if (user_config_option('show emails widget') && module_enabled('email')) {
			/* FIXME $activeWs = active_project();
			list($unread_emails, $pagination) = MailContents::getEmails($tag, null, 'received', 'unread', '', $activeWs, 0, 10);

			if ($activeWs && user_config_option('always show unread mail in dashboard')) {
				// add unread unclassified emails
				list($all_unread, $pagination) = MailContents::getEmails($tag, null, 'received', 'unread', 'unclassified', null, 0, 10);
				$unread_emails = array_merge($unread_emails, $all_unread);
			}*/
			
			tpl_assign('unread_emails', $unread_emails);
		}
		
		//Tasks widgets
		$show_pending = user_config_option('show pending tasks widget')  && module_enabled('tasks');
		$show_in_progress = user_config_option('show tasks in progress widget') && module_enabled('tasks');
		$show_late = user_config_option('show late tasks and milestones widget') && module_enabled('tasks');
		if ($show_pending || $show_in_progress || $show_late) {
			$assigned_to = explode(':', user_config_option('pending tasks widget assigned to filter'));
			$to_company = array_var($assigned_to, 0,0);
			$to_user = array_var($assigned_to, 1, 0);
			tpl_assign('assigned_to_user_filter',$to_user);
			tpl_assign('assigned_to_company_filter',$to_company);
		}
		if ($show_pending) {
			//FIXME $tasks = ProjectTasks::getProjectTasks(active_project(), ProjectTasks::ORDER_BY_DUEDATE, 'ASC', null, null, $tag, $to_company, $to_user, null, true, 'all', false, false, false, 10);
			tpl_assign('dashtasks', $tasks);
		}
		if ($show_in_progress) {
			//FIXME $tasks_in_progress = ProjectTasks::getOpenTimeslotTasks(logged_user(),logged_user(), active_project(), $tag,$to_company,$to_user);
			tpl_assign('tasks_in_progress', $tasks_in_progress);
		}
		if ($show_late) {
			//FIXME tpl_assign('today_milestones', $logged_user->getTodayMilestones(active_project(), $tag, 10));
			//FIXME tpl_assign('late_milestones', $logged_user->getLateMilestones(active_project(), $tag, 10));
			//FIXME tpl_assign('today_tasks', ProjectTasks::getDayTasksByUser(DateTimeValueLib::now(), $logged_user, active_project(), $tag, $to_company, $to_user, 10));
			//FIXME tpl_assign('late_tasks', ProjectTasks::getLateTasksByUser($logged_user, active_project(), $tag, $to_company, $to_user, 10));
		}
		
		tpl_assign('activity_log', $activity_log);
		
		$usu = logged_user();
		$conditions = array("conditions" => array("`state` >= 200 AND (`state`%2 = 0) AND `trashed_on=0 AND `created_by_id` =".$usu->getId()));
		//FIXME $outbox_mails = MailContents::findAll($conditions);
		if ($outbox_mails!= null){
			if (count($outbox_mails)==1){		
				flash_error(lang('outbox mail not sent', 1));
			} else if (count($outbox_mails)>1){
				flash_error(lang('outbox mails not sent', count($outbox_mails)));
			}
		}
	} // index

	/**
	 * Show my projects page
	 *
	 * @param void
	 * @return null
	 */
	function my_projects() {
		$this->addHelper('textile');
		tpl_assign('active_projects', logged_user()->getActiveProjects());
		tpl_assign('finished_projects', logged_user()->getFinishedProjects());
	} // my_projects

	/**
	 * Show milestones and tasks assigned to specific user
	 *
	 * @param void
	 * @return null
	 */
	function my_tasks() {
		tpl_assign('active_projects', logged_user()->getActiveProjects());
	} // my_tasks
	
	
	
	//*************** Main dashboard ***********************//

	/**
	 * @author Ignacio Vazquez
	 */
	function main_dashboard(){
		ajx_set_no_toolbar(true);
		
		
	}
	
	function load_widget () {
		$this->setLayout('empty');
		ajx_current('empty');
		$this->setTemplate('empty');
		$name = $_GET['name'];
		if ($w = Widgets::instance()->findById($name) ){ /* @var $w Widget */
			echo $w->execute();
		}
		exit;
		//TODO Avoid exit : find the way to do that with the framework
	}
	
} 



/**
 * @author pepe
 */
class DashboardTools {
	
	static $widgets = array(); 

	static function renderSection($name) {

		$widgetsToRender = array();
		
		self::$widgets = Widgets::instance()->findAll(array(
			"conditions" => " plugin_id = 0 OR plugin_id IS NULL OR plugin_id IN ( SELECT id FROM ".TABLE_PREFIX."plugins WHERE is_activated > 0 AND is_installed > 0 )",
			"order" => "default_order",
			"order_dir" => "DESC",
		
		));
		// If exists an instance of cw for this section, render the widgets with the options overriden
		foreach (self::$widgets as $w) {
			/* @var $w Widget */
			
			if 	($cw = ContactWidgets::instance()->findById(array(
				'contact_id'=>logged_user()->getId(), 
				'widget_name'=>$w->getName()))
			){
				if ( $cw->getSection() == $name ) {
					$w->setOptions($cw->getOptions()); 
					$w->setDefaultOrder($cw->getOrder());
					$widgetsToRender[] = $w ;
				}
			}elseif($w->getDefaultSection() == $name){
				$widgetsToRender[] = $w ;
			}
		}
		
		usort($widgetsToRender, "widget_sort") ;
		foreach ($widgetsToRender as $k=> $w) {
			$w->execute();
		}
		
	}
}

		
function widget_sort(Widget $a, Widget $b) {
    if ($a->getDefaultOrder() == $b->getDefaultOrder()) {
        return 0;
    }
    return ($a->getDefaultOrder() < $b->getDefaultOrder()) ? -1 : 1;
}
	
			
