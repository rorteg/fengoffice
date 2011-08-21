<?php

/**
 * Controller that is responsible for handling project events related requests
 *
 * @version 1.0
 * @author Marcos Saiz <marcos.saiz@gmail.com>
 * @adapted from Reece calendar <http://reececalendar.sourceforge.net/>.
 * Acknowledgements at the bottom.
 */

class ReportingController extends ApplicationController {

	/**
	 * Construct the ReportingController
	 *
	 * @access public
	 * @param void
	 * @return ReportingController
	 */
	function __construct()
	{
		parent::__construct();
		prepare_company_website_controller($this, 'website');
	} // __construct

	function chart_details()
	{
		$pcf = new ProjectChartFactory();
		$chart = $pcf->loadChart(get_id());
		$chart->ExecuteQuery();
		tpl_assign('chart', $chart);
		ajx_set_no_toolbar(true);
	}

	function init() {
		require_javascript("og/ReportingManager.js");
		ajx_current("panel", "reporting");
		ajx_replace(true);
	}
	
	/**
	 * Show reporting index page
	 *
	 * @param void
	 * @return null
	 */
	function add_chart() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$factory = new ProjectChartFactory();
		$types = $factory->getChartTypes();

		$chart_data = array_var($_POST, 'chart');
		if(!is_array($chart_data)) {
			$chart_data = array(
				'type_id' => 1,
				'display_id' => 20,
				'show_in_project' => 1,
				'show_in_parents' => 0
			); // array
		} // if
		tpl_assign('chart_data', $chart_data);


		if (is_array(array_var($_POST, 'chart'))) {
			$project = Projects::findById(array_var($chart_data, 'project_id'));
			if (!$project instanceof Project) {
				flash_error(lang('project dnx'));
				ajx_current("empty");
				return;
			}
			$chart = $factory->getChart(array_var($chart_data, 'type_id'));
			$chart->setDisplayId(array_var($chart_data, 'display_id'));
			$chart->setTitle(array_var($chart_data, 'title'));

			if (array_var($chart_data, 'save') == 1){
				$chart->setFromAttributes($chart_data);

				try {
					DB::beginWork();
					$chart->save();
					$chart->setProject($project);
					DB::commit();
					flash_success(lang('success add chart', $chart->getTitle()));
					ajx_current('back');
				} catch(Exception $e) {
					DB::rollback();
					flash_error($e->getMessage());
					ajx_current("empty");
				}
				return;
			}

			$chart->ExecuteQuery();
			tpl_assign('chart', $chart);
			ajx_replace(true);
		}
		tpl_assign('chart_displays', $factory->getChartDisplays());
		tpl_assign('chart_list', $factory->getChartTypes());
	}

	function delete_chart() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$chart = ProjectCharts::findById(get_id());
		if(!($chart instanceof ProjectChart)) {
			flash_error(lang('chart dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$chart->canDelete(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		try {

			DB::beginWork();
			$chart->trash();
			ApplicationLogs::createLog($chart, $chart->getWorkspaces(), ApplicationLogs::ACTION_TRASH);
			DB::commit();

			flash_success(lang('success deleted chart', $chart->getTitle()));
			ajx_current("back");
		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error delete chart'));
			ajx_current("empty");
		} // try
	}

	/**
	 * Show reporting add chart page
	 *
	 * @param void
	 * @return null
	 */
	function index()
	{
		ajx_set_no_toolbar(true);
	}

	function list_all()
	{
		ajx_current("empty");

		$project = active_project();
		$isProjectView = ($project instanceof Project);
			
		$start = array_var($_GET,'start');
		$limit = array_var($_GET,'limit');
		if (! $start) {
			$start = 0;
		}
		if (! $limit) {
			$limit = config_option('files_per_page');
		}
		$order = array_var($_GET,'sort');
		$orderdir = array_var($_GET,'dir');
		$tag = array_var($_GET,'tag');
		$page = (integer) ($start / $limit) + 1;
		$hide_private = !logged_user()->isMemberOfOwnerCompany();

		if (array_var($_GET,'action') == 'delete') {
			$ids = explode(',', array_var($_GET, 'charts'));
			list($succ, $err) = ObjectController::do_delete_objects($ids, 'ProjectCharts');
			if ($err > 0) {
				flash_error(lang('error delete objects', $err));
			} else {
				flash_success(lang('success delete objects', $succ));
			}
		} else if (array_var($_GET, 'action') == 'tag') {
			$ids = explode(',', array_var($_GET, 'charts'));
			$tagTag = array_var($_GET, 'tagTag');
			list($succ, $err) = ObjectController::do_tag_object($tagTag, $ids, 'ProjectCharts');
			if ($err > 0) {
				flash_error(lang('error tag objects', $err));
			} else {
				flash_success(lang('success tag objects', $succ));
			}
		}

		if($page < 0) $page = 1;

		//$conditions = logged_user()->isMemberOfOwnerCompany() ? '' : ' `is_private` = 0';
		if ($tag == '' || $tag == null) {
			$tagstr = " 1=1" ; // dummy condition
		} else {
			$tagstr = "(select count(*) from " . TABLE_PREFIX . "tags where " .
			TABLE_PREFIX . "project_charts.id = " . TABLE_PREFIX . "tags.rel_object_id and " .
			TABLE_PREFIX . "tags.tag = '".$tag."' and " . TABLE_PREFIX . "tags.rel_object_manager ='ProjectCharts' ) > 0 ";
		}
		/* TODO: handle with permissions_sql_for_listings */
		//$permission_str = ' AND (' . permissions_sql_for_listings(ProjectCharts::instance(), ACCESS_LEVEL_READ, logged_user()) . ')';
		$permission_str = " AND " . ProjectCharts::getWorkspaceString(logged_user()->getWorkspacesQuery(true));

		if ($isProjectView) {
			$pids = $project->getAllSubWorkspacesQuery(true);
			$project_str = " AND " . ProjectCharts::getWorkspaceString($pids);
		} else {
			$project_str = "";
		}
		

		list($charts, $pagination) = ProjectCharts::paginate(
		array("conditions" => '`trashed_by_id` = 0 AND `archived_by_id` = 0 AND ' . $tagstr . $permission_str . $project_str ,
	        		'order' => '`title` ASC'),
		config_option('files_per_page', 10),
		$page
		); // paginate

		tpl_assign('totalCount', $pagination->getTotalItems());
		tpl_assign('charts', $charts);
		tpl_assign('pagination', $pagination);
		tpl_assign('tags', Tags::getTagNames());

		$object = array(
			"totalCount" => $pagination->getTotalItems(),
			"charts" => array()
		);

		$factory = new ProjectChartFactory();
		$types = $factory->getChartDisplays();

		if (isset($charts))
		{
			foreach ($charts as $c) {
				if ($c->getProject() instanceof Project)
				$tags = project_object_tags($c);
				else
				$tags = "";
					
				$object["charts"][] = array(
				"id" => $c->getId(),
				"name" => $c->getTitle(),
				"type" => $types[$c->getDisplayId()],
				"tags" => $tags,
				"project" => $c->getProject()?$c->getProject()->getName():'',
				"projectId" => $c->getProjectId()
				);
			}
		}
		ajx_extra_data($object);
		tpl_assign("listing", $object);
	}



	// ---------------------------------------------------
	//  Tasks Reports
	// ---------------------------------------------------

	function total_task_times_p(){
		if (array_var($_GET, 'ws') !== null) {
			$report_data = array_var($_SESSION, 'total_task_times_report_data', array());
			$report_data['project_id'] = array_var($_GET, 'ws');
			$_SESSION['total_task_times_report_data'] = $report_data;
			$this->redirectTo('reporting', 'total_task_times_p', array('type' => array_var($_GET, 'type', '')));
		}

		$comp = logged_user()->getCompany();
		$users = ( $comp instanceof Company ? $comp->getUsers() : owner_company()->getUsers());
		$workspaces = logged_user()->getActiveProjects();

		tpl_assign('type', array_var($_GET, 'type'));
		tpl_assign('workspaces', $workspaces);
		tpl_assign('users', $users);
		tpl_assign('has_billing',BillingCategories::count() > 0);
	}

	function total_task_times($report_data = null, $task = null){
		if (!$report_data) {
			$report_data = array_var($_POST, 'report');
			// save selections into session
			$_SESSION['total_task_times_report_data'] = $report_data;
		}
		
		$conditions = array();
		$all_conditions = array_var($report_data, 'conditions');
		if (!is_array($all_conditions)) $all_conditions = array_var($_POST, 'conditions');
		if ($all_conditions != null) tpl_assign('has_conditions', true);
		if (is_array($all_conditions)) {
			foreach($all_conditions as $condition){
				 if($condition['deleted'] != "1"){
					$conditions[] = ($condition);
				 }
			}
		}
		
		$columns = array_var($report_data, 'columns');
		if (!is_array($columns)) $columns = array_var($_POST, 'columns', array());
									
		asort($columns); //sort the array by column order
		foreach($columns as $column => $order){
			if ($order > 0) {
				$newColumn = new ReportColumn();
				//$newColumn->setReportId($newReport->getId());
				if(is_numeric($column)){
					$newColumn->setCustomPropertyId($column);
				}else{
					$newColumn->setFieldName($column);
				}
				
			}
		}
	
		tpl_assign('allow_export', false);
		$this->setTemplate('report_wrapper');


		$user = Users::findById(array_var($report_data, 'user'));
		$workspace = Projects::findById(array_var($report_data, 'project_id'));
		if ($workspace instanceof Project){
			if (array_var($report_data, 'include_subworkspaces')) {
				$workspacesCSV = $workspace->getAllSubWorkspacesQuery(null,logged_user());
			} else {
				$workspacesCSV = $workspace->getId();
			}
		} else {
			$workspacesCSV = logged_user()->getWorkspacesQuery();
		}

		$st = DateTimeValueLib::now();
		$et = DateTimeValueLib::now();
		
		$date_type = array_var($report_data, 'date_type');
		
		switch ($date_type){
			case 1: //Today
				$now = DateTimeValueLib::now();
				$st = DateTimeValueLib::make(0,0,0,$now->getMonth(),$now->getDay(),$now->getYear());
				$et = DateTimeValueLib::make(23,59,59,$now->getMonth(),$now->getDay(),$now->getYear());break;
			case 2: //This week
				$now = DateTimeValueLib::now();
				$monday = $now->getMondayOfWeek();
				$nextMonday = $now->getMondayOfWeek()->add('w',1)->add('d',-1);
				$st = DateTimeValueLib::make(0,0,0,$monday->getMonth(),$monday->getDay(),$monday->getYear());
				$et = DateTimeValueLib::make(23,59,59,$nextMonday->getMonth(),$nextMonday->getDay(),$nextMonday->getYear());break;
			case 3: //Last week
				$now = DateTimeValueLib::now();
				$monday = $now->getMondayOfWeek()->add('w',-1);
				$nextMonday = $now->getMondayOfWeek()->add('d',-1);
				$st = DateTimeValueLib::make(0,0,0,$monday->getMonth(),$monday->getDay(),$monday->getYear());
				$et = DateTimeValueLib::make(23,59,59,$nextMonday->getMonth(),$nextMonday->getDay(),$nextMonday->getYear());break;
			case 4: //This month
				$now = DateTimeValueLib::now();
				$st = DateTimeValueLib::make(0,0,0,$now->getMonth(),1,$now->getYear());
				$et = DateTimeValueLib::make(23,59,59,$now->getMonth(),1,$now->getYear())->add('M',1)->add('d',-1);break;
			case 5: //Last month
				$now = DateTimeValueLib::now();
				$now->add('M',-1);
				$st = DateTimeValueLib::make(0,0,0,$now->getMonth(),1,$now->getYear());
				$et = DateTimeValueLib::make(23,59,59,$now->getMonth(),1,$now->getYear())->add('M',1)->add('d',-1);break;
			case 6: //Date interval
				$st = getDateValue(array_var($report_data, 'start_value'));
				$st = $st->beginningOfDay();
				$et = getDateValue(array_var($report_data, 'end_value'));
				$et = $et->beginningOfDay()->add('d',1);
				break;
		}

		$st = new DateTimeValue($st->getTimestamp() - logged_user()->getTimezone() * 3600);
		$et = new DateTimeValue($et->getTimestamp() - logged_user()->getTimezone() * 3600);
		$timeslotType = array_var($report_data, 'timeslot_type', 0);
		$group_by = array();
		for ($i = 1; $i  <= 3; $i++){
			if ($timeslotType == 0)
			$gb = array_var($report_data, 'group_by_' . $i);
			else
			$gb = array_var($report_data, 'alt_group_by_' . $i);

			if ($gb != '0')
			$group_by[] = $gb;
		}

		$object_subtype = array_var($report_data, 'object_subtype');

		$timeslotsArray = Timeslots::getTaskTimeslots($workspace, $user, $workspacesCSV, $st, $et, array_var($report_data, 'task_id', 0), $group_by,null,0,0,$timeslotType, $conditions, $object_subtype);
		$unworkedTasks = null;
		if (array_var($report_data, 'include_unworked') == 'checked') {
			$unworkedTasks = ProjectTasks::getUnworkedPendingTasks(logged_user(), $workspace, null, null, $st, $et);
			tpl_assign('unworkedTasks', $unworkedTasks);
		}

		if(array_var($_POST, 'exportCSV')){
			$skip_ws = ($all_conditions!=null)?true:false;
			self::total_task_times_csv($report_data, $columns, $timeslotsArray, $skip_ws);
		}else if(array_var($_POST, 'exportPDF')){
			//self::total_task_times_pdf($report_data, $columns, $timeslotsArray);
		}else{
			tpl_assign('columns', $columns);
			tpl_assign('conditions', $conditions);
			tpl_assign('timeslot_type', $timeslotType);
			tpl_assign('group_by', $group_by);
			tpl_assign('timeslotsArray', $timeslotsArray);
			tpl_assign('workspace', $workspace);
			tpl_assign('start_time', $st);
			tpl_assign('end_time', $et);
			tpl_assign('user', $user);
			tpl_assign('date_type', $date_type);
			$report_data['conditions'] = $conditions;
			$report_data['columns'] = $columns;
			tpl_assign('post', $report_data);
			tpl_assign('template_name', 'total_task_times');
			tpl_assign('title',lang('task time report'));
		}
	}

	function total_task_times_by_task_print(){
		$this->setLayout("html");

		$task = ProjectTasks::findById(get_id());

		$st = DateTimeValueLib::make(0,0,0,1,1,1900);
		$et = DateTimeValueLib::make(23,59,59,12,31,2036);

		$timeslotsArray = Timeslots::getTaskTimeslots(null,null,null,$st,$et, get_id());

		tpl_assign('estimate', $task->getTimeEstimate());
		//tpl_assign('timeslots', $timeslots);
		tpl_assign('timeslotsArray', $timeslotsArray);
		tpl_assign('workspace', $task->getProject());
		tpl_assign('template_name', 'total_task_times');
		tpl_assign('title',lang('task time report'));
		tpl_assign('task_title', $task->getTitle());
		$this->setTemplate('report_printer');
	}

	function total_task_times_print(){
		$this->setLayout("html");

		$report_data = json_decode(str_replace("'",'"', array_var($_POST, 'post')),true);

		$this->total_task_times($report_data);
		$this->setTemplate('report_printer');
	}

	function total_task_times_vs_estimate_comparison_p(){
		$users = owner_company()->getUsers();
		$workspaces = logged_user()->getActiveProjects();

		tpl_assign('workspaces', $workspaces);
		tpl_assign('users', $users);
	}

	function total_task_times_vs_estimate_comparison($report_data = null, $task = null){
		$this->setTemplate('report_wrapper');

		if (!$report_data)
		$report_data = array_var($_POST, 'report');

		$workspace = Projects::findById(array_var($report_data, 'project_id'));
		if ($workspace instanceof Project){
			if (array_var($report_data, 'include_subworkspaces')) {
				$workspacesCSV = $workspace->getAllSubWorkspacesQuery(false);
			} else {
				$workspacesCSV = $workspace->getId();
			}
		}
		else {
			$workspacesCSV = null;
		}

		$start = getDateValue(array_var($report_data, 'start_value'));
		$end = getDateValue(array_var($report_data, 'end_value'));

		$st = $start->beginningOfDay();
		$et = $end->endOfDay();
		$st = new DateTimeValue($st->getTimestamp() - logged_user()->getTimezone() * 3600);
		$et = new DateTimeValue($et->getTimestamp() - logged_user()->getTimezone() * 3600);

		$timeslots = Timeslots::getTimeslotsByUserWorkspacesAndDate($st, $et, 'ProjectTasks', null, $workspacesCSV, array_var($report_data, 'task_id',0));

		tpl_assign('timeslots', $timeslots);
		tpl_assign('workspace', $workspace);
		tpl_assign('start_time', $st);
		tpl_assign('end_time', $et);
		tpl_assign('user', $user);
		tpl_assign('post', $report_data);
		tpl_assign('template_name', 'total_task_times');
		tpl_assign('title',lang('task time report'));
	}

	
	
	function total_task_times_csv($report_data, $columns, $timeslotsArray, $skip_ws=false){
		//$types = self::get_report_column_types($report->getId());
		$filename = str_replace(' ', '_',lang('task time report')).date('_YmdHis');
		header('Expires: 0');
		header('Cache-control: private');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Description: File Transfer');
		header('Content-Type: application/csv');
		header('Content-disposition: attachment; filename='.$filename.'.csv');
		
		// titles
		$titles = lang('date').";".lang('title').";".lang('description').";".lang('user').";".lang('time').";";
		
		if (array_var($report_data, 'show_billing', false)) $titles .= lang('billing').";";
		foreach($columns as $id => $pos){
			if ($pos == 0) continue;
			if (!is_numeric($id)){
				$col_name = lang("field ProjectTasks ".$id);
			} else {
				$cp = CustomProperties::getCustomProperty($id);					
				$col_name = $cp->getName();
			}
			$titles .= $col_name.";";
		}
		$titles = iconv(mb_internal_encoding(),"ISO-8859-1",html_entity_decode($titles, ENT_COMPAT));
		echo "$titles\n";

		// data
		foreach($timeslotsArray as $tsRow) {
			$ts = $tsRow["ts"];
			if ($skip_ws && $ts->getObjectManager() == 'Projects') continue;
			$to_print = format_date($ts->getStartTime()) . ";";
			if ($ts->getObject() instanceof ProjectTask) {
				$to_print .= $ts->getObject()->getTitle() . ";";
				$to_print .= $ts->getObject()->getText() . ";";
			} else if ($ts->getObject() instanceof Project) {
				$ws_name = lang('workspace') . ' ' . clean($ts->getObject()->getName());
				$to_print .= "$ws_name;;";
			} else $to_print .= ";;";
			
			$to_print .= clean(Users::getUserDisplayName($ts->getUserId())) . ";";
			$lastStop = $ts->getEndTime() != null ? $ts->getEndTime() : ($ts->isPaused() ? $ts->getPausedOn() : DateTimeValueLib::now()) . ";";
			$to_print .= DateTimeValue::FormatTimeDiff($ts->getStartTime(), $lastStop, "hm", 60, $ts->getSubtract()) . ";";
			if (array_var($report_data, 'show_billing', false)) 
				$to_print .= config_option('currency_code', '$') ." ". $ts->getFixedBilling();

			// other columns
			foreach($columns as $id => $pos){
				if ($pos == 0) continue;
				if ($ts->getObject() instanceof ProjectTask) {
					if (!is_numeric($id)){
						$col_value = self::format_value_to_print($id, $ts->getObject()->getColumnValue($id), $ts->getObject()->manager()->getColumnType($id), $ts->getObject()->getObjectManagerName()); 
						
					} else {
						$cp = CustomProperties::getCustomProperty($id);
						$cpv = CustomPropertyValues::getCustomPropertyValue($ts->getObject()->getId(), $cp->getId());
						if ($cpv instanceof CustomPropertyValue) {
							$col_value = self::format_value_to_print($cp->getName(), $cpv->getValue(), $cp->getOgType(), $ts->getObject()->getObjectManagerName());
						} else $col_value = "";
					}
				} else $col_value = "";
				$to_print .= $col_value.";";
			}
			$to_print = iconv(mb_internal_encoding(),"ISO-8859-1",html_entity_decode($to_print, ENT_COMPAT));
			echo "$to_print\n";
		}
		die();
	}
	
	
	
	// ---------------------------------------------------
	//  Custom Reports
	// ---------------------------------------------------

	function add_custom_report(){
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		tpl_assign('url', get_url('reporting', 'add_custom_report'));
		$report_data = array_var($_POST, 'report');
		if(is_array($report_data)){
			tpl_assign('report_data', $report_data);
			$conditions = array_var($_POST, 'conditions');
			if(!is_array($conditions))
			$conditions = array();
			tpl_assign('conditions', $conditions);
			$columns = array_var($_POST, 'columns');
			if(is_array($columns) && count($columns) > 0){
				tpl_assign('columns', $columns);
				$newReport = new Report();

				if(!$newReport->canAdd(logged_user())) {
					flash_error(lang('no access permissions'));
					ajx_current("empty");
					return;
				} // if

				$newReport->setName($report_data['name']);
				$newReport->setDescription($report_data['description']);
				$newReport->setObjectType($report_data['object_type']);
				$newReport->setOrderBy($report_data['order_by']);
				$newReport->setIsOrderByAsc($report_data['order_by_asc'] == 'asc');
				
				try{
					DB::beginWork();
					$newReport->save();
					$allowed_columns = $this->get_allowed_columns($report_data['object_type'], true);
					foreach($conditions as $condition){
						if($condition['deleted'] == "1") continue;
						foreach ($allowed_columns as $ac){
							if ($condition['field_name'] == $ac['id']){
								$newCondition = new ReportCondition();
								$newCondition->setReportId($newReport->getId());
								$newCondition->setCustomPropertyId($condition['custom_property_id']);
								$newCondition->setFieldName($condition['field_name']);
								$newCondition->setCondition($condition['condition']);
								
								$condValue = array_key_exists('value', $condition) ? $condition['value'] : '';
								if($condition['field_type'] == 'boolean'){
									$newCondition->setValue(array_key_exists('value', $condition));
								}else if($condition['field_type'] == 'date'){
									if ($condValue != '') {
										$dtFromWidget = DateTimeValueLib::dateFromFormatAndString(user_config_option('date_format'), $condValue);
										$newCondition->setValue(date("m/d/Y", $dtFromWidget->getTimestamp()));
									}
								}else{
									$newCondition->setValue($condValue);
								}
								$newCondition->setIsParametrizable(isset($condition['is_parametrizable']));
								$newCondition->save();
							}
						}
					}
					
					asort($columns); //sort the array by column order
					foreach($columns as $column => $order){
						if ($order > 0) {
							$newColumn = new ReportColumn();
							$newColumn->setReportId($newReport->getId());
							if(is_numeric($column)){
								$newColumn->setCustomPropertyId($column);
							}else{
								$newColumn->setFieldName($column);
							}
							$newColumn->save();
						}
					}
					DB::commit();
					flash_success(lang('custom report created'));
					ajx_current('back');
				}catch(Exception $e){
					DB::rollback();
					flash_error($e->getMessage());
					ajx_current("empty");
				}
			}
		}
		$selected_type = array_var($_GET, 'type', '');
		$types = array(
			array("", lang("select one")),
			array("Companies", lang("companies")),
			array("Contacts", lang("contacts")),
			array("MailContents", lang("email type")),
			array("ProjectEvents", lang("events")),
			array("ProjectFiles", lang("file")),
			array("ProjectMilestones", lang("milestone")),
			array("ProjectMessages", lang("message")),
			array("ProjectTasks", lang("task")),
			array("Users", lang("user")),
			array("ProjectWebpages", lang("webpage")),
			array("Projects", lang("workspace")),
		);
		if ($selected_type != '')
			tpl_assign('allowed_columns', $this->get_allowed_columns($selected_type));
		
		tpl_assign('object_types', $types);
		tpl_assign('selected_type', $selected_type);
	}

	function edit_custom_report(){
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$report_id = array_var($_GET, 'id');
		$report = Reports::getReport($report_id);

		if(!$report->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		if(is_array(array_var($_POST, 'report'))) {
			try{
				ajx_current("empty");
				$report_data = array_var($_POST, 'report');
				DB::beginWork();
				$report->setName($report_data['name']);
				$report->setDescription($report_data['description']);
				$report->setObjectType($report_data['object_type']);
				$report->setOrderBy($report_data['order_by']);
				$report->setIsOrderByAsc($report_data['order_by_asc'] == 'asc');
				
				$report->save();				
					
				$conditions = array_var($_POST, 'conditions');
				if (!is_array($conditions))
					$conditions = array();
				foreach($conditions as $condition){
					$newCondition = new ReportCondition();
					if($condition['id'] > 0){
						$newCondition = ReportConditions::getCondition($condition['id']);
					}
					if($condition['deleted'] == "1"){
						$newCondition->delete();
						continue;
					}
					$newCondition->setReportId($report_id);
					$custom_prop_id = isset($condition['custom_property_id']) ? $condition['custom_property_id'] : 0;
					$newCondition->setCustomPropertyId($custom_prop_id);
					$newCondition->setFieldName($condition['field_name']);
					$newCondition->setCondition($condition['condition']);
					if($condition['field_type'] == 'boolean'){
						$newCondition->setValue(isset($condition['value']) && $condition['value']);
					}else if($condition['field_type'] == 'date'){
						if ($condition['value'] == '') $newCondition->setValue('');
						else {
							$dtFromWidget = DateTimeValueLib::dateFromFormatAndString(user_config_option('date_format'), $condition['value']);
							$newCondition->setValue(date("m/d/Y", $dtFromWidget->getTimestamp()));
						}
					}else{
						$newCondition->setValue(isset($condition['value']) ? $condition['value'] : '');
					}
					$newCondition->setIsParametrizable(isset($condition['is_parametrizable']));
					$newCondition->save();
				}
				ReportColumns::delete('report_id = ' . $report_id);
				$columns = array_var($_POST, 'columns');
				
				asort($columns); //sort the array by column order
				foreach($columns as $column => $order){
					if ($order > 0) {
						$newColumn = new ReportColumn();
						$newColumn->setReportId($report_id);
						if(is_numeric($column)){
							$newColumn->setCustomPropertyId($column);
						}else{
							$newColumn->setFieldName($column);
						}
						$newColumn->save();
					}
				}
				DB::commit();
				flash_success(lang('custom report updated'));
				ajx_current('back');
			} catch(Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			} // try
		}else{
			$this->setTemplate('add_custom_report');
			tpl_assign('url', get_url('reporting', 'edit_custom_report', array('id' => $report_id)));
			if($report instanceof Report){
				tpl_assign('id', $report_id);
				$report_data = array(
					'name' => $report->getName(),
					'description' => $report->getDescription(),
					'object_type' => $report->getObjectType(),
					'order_by' => $report->getOrderBy(),
					'order_by_asc' => $report->getIsOrderByAsc(),
					'workspace' => $report->getWorkspace(),
					'tags' => $report->getTags()
				);
				tpl_assign('report_data', $report_data);
				$conditions = ReportConditions::getAllReportConditions($report_id);
				tpl_assign('conditions', $conditions);
				$columns = ReportColumns::getAllReportColumns($report_id);
				$colIds = array();
				foreach($columns as $col){
					if($col->getCustomPropertyId() > 0){
						$colIds[] = $col->getCustomPropertyId();
					}else{
						$colIds[] = $col->getFieldName();
					}
				}
				tpl_assign('columns', $colIds);
			}

			$selected_type = $report->getObjectType();
			$types = array(
				array("", lang("select one")),
				array("Companies", lang("companies")),
				array("Contacts", lang("contacts")),
				array("MailContents", lang("email type")),
				array("ProjectEvents", lang("events")),
				array("ProjectFiles", lang("file")),
				array("ProjectMilestones", lang("milestone")),
				array("ProjectMessages", lang("message")),
				array("ProjectTasks", lang("task")),
				array("Users", lang("user")),
				array("ProjectWebpages", lang("webpage")),
				array("Projects", lang("workspace")),
			);
			tpl_assign('object_types', $types);
			tpl_assign('selected_type', $selected_type);
			
			tpl_assign('allowed_columns', $this->get_allowed_columns($selected_type), true);
		}
	}

	function view_custom_report(){
		$report_id = array_var($_GET, 'id');
		tpl_assign('id', $report_id);
		if(isset($report_id)){
			$report = Reports::getReport($report_id);
			$conditions = ReportConditions::getAllReportConditions($report_id);
			$paramConditions = array();
			foreach($conditions as $condition){
				if($condition->getIsParametrizable()){
					$paramConditions[] = $condition;
				}
			}
			eval('$managerInstance = ' . $report->getObjectType() . "::instance();");
			$externalCols = $managerInstance->getExternalColumns();
			$externalFields = array();
			foreach($externalCols as $extCol){
				$externalFields[$extCol] = $this->get_ext_values($extCol, $report->getObjectType());
			}
			$params = array_var($_GET, 'params');
			if(count($paramConditions) > 0 && !isset($params)){
				$this->setTemplate('custom_report_parameters');
				tpl_assign('model', $report->getObjectType());
				tpl_assign('title', $report->getName());
				tpl_assign('description', $report->getDescription());
				tpl_assign('conditions', $paramConditions);
				tpl_assign('external_fields', $externalFields);
			}else{
				$this->setTemplate('report_wrapper');
				tpl_assign('template_name', 'view_custom_report');
				tpl_assign('title', $report->getName());
				tpl_assign('genid', gen_id());
				$parameters = '';
				if(isset($params)){
					foreach($params as $id => $value){
						$parameters .= '&params['.$id.']='.$value;
					}
				}
				tpl_assign('parameterURL', $parameters);
				$offset = array_var($_GET, 'offset');
				if(!isset($offset)) $offset = 0;
				$limit = array_var($_GET, 'limit');
				if(!isset($limit)) $limit = 50;
				$order_by = array_var($_GET, 'order_by');
				if(!isset($order_by)) $order_by = '';
				tpl_assign('order_by', $order_by);
				$order_by_asc = array_var($_GET, 'order_by_asc');
				if(!isset($order_by_asc)) $order_by_asc = true;
				tpl_assign('order_by_asc', $order_by_asc);
				$results = Reports::executeReport($report_id, $params, $order_by, $order_by_asc, $offset, $limit);
				if(!isset($results['columns'])) $results['columns'] = array(); 
				tpl_assign('columns', $results['columns']);
				tpl_assign('db_columns', $results['db_columns']);
				if(!isset($results['rows'])) $results['rows'] = array();
				tpl_assign('rows', $results['rows']);
				if(!isset($results['pagination'])) $results['pagination'] = '';
				tpl_assign('pagination', $results['pagination']);
				tpl_assign('types', self::get_report_column_types($report_id));
				tpl_assign('post', $params);
				tpl_assign('model', $report->getObjectType());
				tpl_assign('description', $report->getDescription());
				tpl_assign('conditions', $conditions);
				tpl_assign('parameters', $params);
				tpl_assign('id', $report_id);
				tpl_assign('to_print', false);
			}
			
			ApplicationReadLogs::createLog($report, "", ApplicationReadLogs::ACTION_READ);
		}
	}

	function view_custom_report_print(){		
		$this->setLayout("html");

		$params = json_decode(str_replace("'",'"', array_var($_POST, 'post')),true);

		$report_id = array_var($_POST, 'id');
		$order_by = array_var($_POST, 'order_by');
		if(!isset($order_by)) $order_by = '';
		tpl_assign('order_by', $order_by);
		$order_by_asc = array_var($_POST, 'order_by_asc');
		if(!isset($order_by_asc)) $order_by_asc = true;
		tpl_assign('order_by_asc', $order_by_asc);
		$report = Reports::getReport($report_id);
		$results = Reports::executeReport($report_id, $params, $order_by, $order_by_asc, 0, 50, true);
		if(isset($results['columns'])) tpl_assign('columns', $results['columns']);
		if(isset($results['rows'])) tpl_assign('rows', $results['rows']);
		tpl_assign('db_columns', $results['db_columns']);

		if(array_var($_POST, 'exportCSV')){
			$this->generateCSVReport($report, $results);
		}else if(array_var($_POST, 'exportPDF')){
			$this->generatePDFReport($report, $results);
		}else{
			tpl_assign('types', self::get_report_column_types($report_id));
			tpl_assign('template_name', 'view_custom_report');
			tpl_assign('title', $report->getName());
			tpl_assign('model', $report->getObjectType());
			tpl_assign('description', $report->getDescription());
			$conditions = ReportConditions::getAllReportConditions($report_id);
			tpl_assign('conditions', $conditions);
			tpl_assign('parameters', $params);
			tpl_assign('id', $report_id);
			tpl_assign('to_print', true);
			$this->setTemplate('report_printer');
		}
	}
	
	function generateCSVReport($report, $results){
		$types = self::get_report_column_types($report->getId());
		$filename = str_replace(' ', '_',$report->getName()).date('_YmdHis');
		header('Expires: 0');
		header('Cache-control: private');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Description: File Transfer');
		header('Content-Type: application/csv');
		header('Content-disposition: attachment; filename='.$filename.'.csv');
		foreach($results['columns'] as $col){
			echo $col.';';
		}
		echo "\n";
		foreach($results['rows'] as $row) {
			foreach($row as $k => $value){
				$type = '';
				if($k == 'link'){
					$value = strip_tags($value);
				}else if ($k == 't.state'){
					$value = ($value != '') ? lang('completed') : lang('open task status');
				}else{
					$type = $types[$k];
				}
				$cell = $this->format_value_to_print($k, $value, $type, $report->getObjectType());				
				$cell = iconv(mb_internal_encoding(),"ISO-8859-1",html_entity_decode($cell ,ENT_COMPAT));
				echo $cell.';';
			}
			echo "\n";
		}
		die();
	}
	
	function generatePDFReport($report, $results){
		$types = self::get_report_column_types($report->getId());
		eval('$managerInstance = ' . $report->getObjectType() . "::instance();");
		$externalCols = $managerInstance->getExternalColumns();
		$filename = str_replace(' ', '_',$report->getName()).date('_YmdHis');
		$pageLayout = $_POST['pdfPageLayout'];
		$fontSize = $_POST['pdfFontSize'];
		include_once(LIBRARY_PATH . '/pdf/fpdf.php');
		$pdf = new FPDF($pageLayout);
		$pdf->setTitle($report->getName());
		$pdf->AddPage();
		$pdf->SetFont('Arial','',$fontSize);
		$pdf->Cell(80);
		$report_title = iconv(mb_internal_encoding(), "ISO-8859-1", html_entity_decode($report->getName(), ENT_COMPAT));
    	$pdf->Cell(30, 10, $report_title);
    	$pdf->Ln(20);
    	$colSizes = array();
    	$maxValue = array();
    	$fixed_col_sizes = array();
		foreach($results['rows'] as $row) {
			$i = 0;			
			foreach($row as $k => $value){	
				if(!isset($maxValue[$i])) $maxValue[$i] = '';
				if ($k == 't.state'){
					$value = ($value != '') ? lang('completed') : lang('open task status');
				}				
				if(strlen(strip_tags($value)) > strlen($maxValue[$i])){
					$maxValue[$i] = strip_tags($value);
				}
				$i++;  
			}
    	}
    	$k=0;
    	foreach ($maxValue as $str) {    		    	
    		$col_title_len = $pdf->GetStringWidth($results['columns'][$k]);
    		//file_put_contents('log.txt', ' tit:'.$results['columns'][$k], FILE_APPEND);    		
    		$colMaxTextSize = max($pdf->GetStringWidth($str), $col_title_len);
    		$db_col = $results['columns'][$k];    		
    		$colType = array_var($types, array_var($results['db_columns'], $db_col, ''), '');
    		if($colType == DATA_TYPE_DATETIME && !($report->getObjectType() == 'ProjectEvents' && $results['db_columns'][$db_col] == 'start')){
    			$colMaxTextSize = $colMaxTextSize / 2;
    			if ($colMaxTextSize < $col_title_len) $colMaxTextSize = $col_title_len;
    		}
    		$fixed_col_sizes[$k] = $colMaxTextSize;
    		$k++;
    	}
    	
    	$fixed_col_sizes = self::fix_column_widths(($pageLayout=='P'?172:260), $fixed_col_sizes);
    	
    	$max_char_len = array();
		$i = 0;
		foreach($results['columns'] as $col){
			$colMaxTextSize = $fixed_col_sizes[$i];
			$colFontSize = $colMaxTextSize + 5;
			$colSizes[$i] = $colFontSize ;
			$col_name = iconv(mb_internal_encoding(), "ISO-8859-1", html_entity_decode($col, ENT_COMPAT));
    		$pdf->Cell($colFontSize, 7, $col_name);
    		$max_char_len[$i] = self::get_max_length_from_pdfsize($pdf, $colFontSize);
    		$i++;
		}
		
		$lastColX = $pdf->GetX();
		$pdf->Ln();
		$pdf->Line($pdf->GetX(), $pdf->GetY(), $lastColX, $pdf->GetY());
		foreach($results['rows'] as $row) {
			$i = 0;
			$more_lines = array();
			$col_offsets = array();
			foreach($row as $k => $value){				
				if($k == 'link'){
					$value = strip_tags($value);	
					$cell = $value;					
				}else{
					$cell = $this->format_value_to_print($k, $value, $types[$k], $report->getObjectType());	
				}
				if($report->getObjectType() == 'ProjectTasks' && $k == 't.state'){
					$cell = ($cell != '--') ? lang('completed') : lang('open task status');
				}							
				$cell = iconv(mb_internal_encoding(), "ISO-8859-1", html_entity_decode($cell, ENT_COMPAT));
				
				$splitted = self::split_column_value($cell, $max_char_len[$i]);
				$cell = $splitted[0];
				if (count($splitted) > 1) {
					array_shift($splitted);
					$ml = 0;
					foreach ($splitted as $sp_val) {
						if (!isset($more_lines[$ml]) || !is_array($more_lines[$ml])) $more_lines[$ml] = array();
						$more_lines[$ml][$i] = $sp_val;
						$ml++;
					}
					$col_offsets[$i] = $pdf->x;
				}
				
				$pdf->Cell($colSizes[$i],7,$cell);
				$i++;
			}
			foreach ($more_lines as $ml_values) {
				$pdf->Ln();
				foreach ($ml_values as $col_idx => $col_val) {
					$pdf->SetX($col_offsets[$col_idx]);
					$pdf->Cell($colSizes[$col_idx],7,$col_val);
				}
			}
			$pdf->Ln();
			$pdf->SetDrawColor(220, 220, 220);
			$pdf->Line($pdf->GetX(), $pdf->GetY(), $lastColX, $pdf->GetY());
			$pdf->SetDrawColor(0, 0, 0);
		}
		$filename = ROOT."/tmp/".gen_id().".pdf";
		$pdf->Output($filename, "F");
		download_file($filename, "application/pdf", $report->getName(), true);
		unlink($filename);
		die();
	}
	
	/**
	 * Returns an array containing the fixed widths of every column.
	 * If the sum of the column widths is longer than the page's width
	 * the bigger columns are resized to fit the page.
	 *
	 * @param integer $total_width
	 * @param array $max_col_valuesues
	 * @return array containing the fixed widths for every column
	 */
	function fix_column_widths($total_width, $max_col_values) {
		$fixed_widths = array();
		$columns_to_adjust = array();
		$to_add = 0;
		
		$average = floor($total_width / count($max_col_values));
		foreach ($max_col_values as $k => $width) {
			if ($width <= $average) {
				$fixed_widths[$k] = $width;
				$to_add += floor($average - $width);
			} else {
				$columns_to_adjust[] = $k;
			}
		}
		if (count($columns_to_adjust) > 0)
			$new_col_width = $average + (floor($to_add / count($columns_to_adjust)));

		foreach ($columns_to_adjust as $col) {
			if ($max_col_values[$col] > $new_col_width) $fixed_widths[$col] = $new_col_width;
			else $fixed_widths[$col] = $max_col_values[$col];
		}
		
		return $fixed_widths;
	}
	
	/**
	 * Gets the aproximated character count that can be written in the space delimited by $width.
	 *
	 * @param $pdf
	 * @param $width
	 * @return integer
	 */
	function get_max_length_from_pdfsize($pdf, $width) {
		$cw = &$pdf->CurrentFont['cw'];
		$w = 0;
		$i = 0;
		while($w < $width) {
			$w += $cw['a'] * $pdf->FontSize / 1000;
			$i++;
		}
		return $i;
	}
	
	/**
	 * Splits a value in pieces of maximum length = $length.
	 * The split point is the last position of a space char that is before the piece length 
	 *
	 * @param $value: value to split
	 * @param $length: max length of each piece
	 * @return array containing the pieces after splitting the value
	 */
	function split_column_value($value, $length) {
		if (strlen($value) <= $length) return array($value);
		$splitted = array();
		$i=0;
		while (strlen($value) > $length) {
			$pos = -1;
			while ($pos !== false && $pos < $length) {
				$pos_ant = $pos;
				$pos = strpos($value, " ", $pos+1);
			}
			if ($pos_ant != -1) $pos = $pos_ant;

			$splitted[$i] = substr($value, 0, $pos+1);
			$value = substr($value, $pos+1);
			$i++;
		}
		$splitted[$i] = $value;
		return $splitted;
	}
	
	function format_value_to_print($col, $value, $type, $obj_type, $textWrapper='', $dateformat='Y-m-d') {
		switch ($type) {
			case DATA_TYPE_STRING: 
				if(preg_match(EMAIL_FORMAT, strip_tags($value))){
					$formatted = strip_tags($value);
				}else{ 
					$formatted = $textWrapper . clean($value) . $textWrapper;
				}
				break;
			case DATA_TYPE_INTEGER:
				if ($col == 'priority'){
					switch($value){
					case 100:
						$formatted = lang('low priority'); 
						break;
					case 200:
						$formatted = lang('normal priority');
						break;
					case 300:
						$formatted = lang('high priority');
						break;
					case 400:
						$formatted = lang('urgent priority');
						break;
					default: $formatted = clean($value);
					}					
				}
				else{				
					$formatted = clean($value);
				}
				break;
			case DATA_TYPE_BOOLEAN: $formatted = ($value == 1 ? lang('yes') : lang('no'));
				break;
			case DATA_TYPE_DATE:
				if ($value != 0) { 
					if (str_ends_with($value, "00:00:00")) $dateformat .= " H:i:s";
					$dtVal = DateTimeValueLib::dateFromFormatAndString($dateformat, $value);
					$formatted = format_date($dtVal, null, 0);
				} else $formatted = '';
				break;
			case DATA_TYPE_DATETIME:
				if ($value != 0) {
					$dtVal = DateTimeValueLib::dateFromFormatAndString("$dateformat H:i:s", $value);
					if ($obj_type == 'ProjectEvents' && $col == 'start') $formatted = format_datetime($dtVal);
					else $formatted = format_date($dtVal, null, 0);
				} else $formatted = '';
				break;
			default: $formatted = $value;
		}
		if($formatted == ''){
			$formatted = '--';
		}
		
		return $formatted;
	}

	function delete_custom_report(){
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$report_id = array_var($_GET, 'id');
		$report = Reports::getReport($report_id);

		if(!$report->canDelete(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		try{
			DB::beginWork();
			$report->delete();
			DB::commit();
			ajx_current("reload");
		}catch(Exception $e) {
			DB::rollback();
			flash_error($e->getMessage());
			ajx_current("empty");
		} // try
	}

	function get_object_fields(){
		$fields = $this->get_allowed_columns(array_var($_GET, 'object_type'), true);

		ajx_current("empty");
		ajx_extra_data(array('fields' => $fields));
	}

	function get_object_fields_custom_properties(){ //returns only the custom properties
		$fields = $this->get_allowed_columns_custom_properties(array_var($_GET, 'object_type'), true);

		ajx_current("empty");
		ajx_extra_data(array('fields' => $fields));
	}
	
	
	private function get_allowed_columns_custom_properties($object_type, $includeWsAndTag=false) {
		$fields = array();
		if(isset($object_type)){
			$customProperties = CustomProperties::getAllCustomPropertiesByObjectType($object_type);
			$objectFields = array();
			foreach($customProperties as $cp){				
				if ($cp->getType() != 'table')
					$fields[] = array('id' => $cp->getId(), 'name' => $cp->getName(), 'type' => $cp->getType(), 'values' => $cp->getValues(), 'multiple' => $cp->getIsMultipleValues());
			}
			eval('$managerInstance = ' . $object_type . "::instance();");	
	
			foreach($objectFields as $name => $type){
				if($type == DATA_TYPE_FLOAT || $type == DATA_TYPE_INTEGER){
					$type = 'numeric';
				}else if($type == DATA_TYPE_STRING){
					$type = 'text';
				}else if($type == DATA_TYPE_BOOLEAN){
					$type = 'boolean';
				}else if($type == DATA_TYPE_DATE || $type == DATA_TYPE_DATETIME){
					$type = 'date';
				}
				$fields[] = array('id' => $name, 'name' => lang('field ' . $object_type . ' ' .$name), 'type' => $type);
			}
	
		}
		usort($fields, array(&$this, 'compare_FieldName'));
		return $fields;
	}
	
	function get_object_column_list(){
		$allowed_columns = $this->get_allowed_columns(array_var($_GET, 'object_type'), true);

		tpl_assign('allowed_columns', $allowed_columns);
		tpl_assign('columns', explode(',', array_var($_GET, 'columns', array())));
		tpl_assign('order_by', array_var($_GET, 'orderby'));
		tpl_assign('order_by_asc', array_var($_GET, 'orderbyasc'));
		tpl_assign('genid', array_var($_GET, 'genid'));
		
		$this->setLayout("html");
		$this->setTemplate("column_list");
	}

	function get_object_column_list_task(){
		$allowed_columns = $this->get_allowed_columns_custom_properties(array_var($_GET, 'object_type'), true);
		$for_task = true;
		
		tpl_assign('allowed_columns', $allowed_columns);
		tpl_assign('columns', explode(',', array_var($_GET, 'columns', array())));	
		tpl_assign('genid', array_var($_GET, 'genid'));
		tpl_assign('for_task', $for_task);
		
		$this->setLayout("html");
		$this->setTemplate("column_list");
	}
	
	function get_external_field_values(){
		$field = array_var($_GET, 'external_field');
		$report_type = array_var($_GET, 'report_type');
		$values = $this->get_ext_values($field, $report_type);
		ajx_current("empty");
		ajx_extra_data(array('values' => $values));
	}

	private function get_ext_values($field, $manager = null){
		$values = array(array('id' => '', 'name' => '-- ' . lang('select') . ' --'));
		if($field == 'company_id' || $field == 'assigned_to_company_id'){
			$companies = Companies::getVisibleCompanies(logged_user());
			foreach($companies as $company){
				$values[] = array('id' => $company->getId(), 'name' => $company->getName());
			}
		}else if($field == 'user_id' || $field == 'created_by_id' || $field == 'updated_by_id' || $field == 'assigned_to_user_id' || $field == 'completed_by_id'){
			$users = Users::getVisibleUsers(logged_user());
			foreach($users as $user){
				$values[] = array('id' => $user->getId(), 'name' => $user->getDisplayName());
			}
		}else if($field == 'milestone_id'){
			$milestones = ProjectMilestones::getActiveMilestonesByUser(logged_user());
			foreach($milestones as $milestone){
				$values[] = array('id' => $milestone->getId(), 'name' => $milestone->getName());
			}
		} else if($field == 'workspace'){
			$workspaces = logged_user()->getWorkspaces(false,0);
			foreach($workspaces as $ws){
				$values[] = array('id' => $ws->getId(), 'name' => $ws->getName());
			}
		} else if($field == 'tag'){
			$tags = Tags::getTagNames();
			foreach($tags as $tag){
				$values[] = array('id' => $tag['name'], 'name' => $tag['name']);
			}
		} else if($field == 'object_subtype'){
			$object_types = ProjectCoTypes::findAll(array('conditions' => (!is_null($manager) ? "`object_manager`='$manager'" : "")));
			foreach($object_types as $object_type){
				$values[] = array('id' => $object_type->getId(), 'name' => $object_type->getName());
			}
		}
		return $values;
	}

	private function get_allowed_columns($object_type, $includeWsAndTag=false) {
		$fields = array();
		if(isset($object_type)){
			$customProperties = CustomProperties::getAllCustomPropertiesByObjectType($object_type);
			$objectFields = array();
			foreach($customProperties as $cp){
				$fields[] = array('id' => $cp->getId(), 'name' => $cp->getName(), 'type' => $cp->getType(), 'values' => $cp->getValues(), 'multiple' => $cp->getIsMultipleValues());
			}
			eval('$managerInstance = ' . $object_type . "::instance();");
			$objectColumns = $managerInstance->getColumns();
			$objectFields = array();
			$objectColumns = array_diff($objectColumns, $managerInstance->getSystemColumns());
			foreach($objectColumns as $column){
				$objectFields[$column] = $managerInstance->getColumnType($column);
			}

			foreach($objectFields as $name => $type){
				if($type == DATA_TYPE_FLOAT || $type == DATA_TYPE_INTEGER){
					$type = 'numeric';
				}else if($type == DATA_TYPE_STRING){
					$type = 'text';
				}else if($type == DATA_TYPE_BOOLEAN){
					$type = 'boolean';
				}else if($type == DATA_TYPE_DATE || $type == DATA_TYPE_DATETIME){
					$type = 'date';
				}
				$fields[] = array('id' => $name, 'name' => lang('field ' . $object_type . ' ' .$name), 'type' => $type);
			}
			
			//to add the 'Status' parameter
			if ($object_type == 'ProjectsTasks'){				
				$fields[] = array('id' => 'status', 'name' => lang('field ' . $object_type . ' status'), 'type' => 'boolean');
			}			
	
			$externalFields = $managerInstance->getExternalColumns();
			foreach($externalFields as $extField){				
				$fields[] = array('id' => $extField, 'name' => lang('field ' . $object_type . ' '.$extField), 'type' => 'external', 'multiple' => 0);
			}
			// Workspace and Tags
			if($includeWsAndTag && $object_type != 'Projects' && $object_type != 'Users'){
				$fields[] = array('id' => 'workspace', 'name' => lang('workspace'), 'type' => 'external');
				$fields[] = array('id' => 'tag', 'name' => lang('tag'), 'type' => 'external');
			}
		}
		usort($fields, array(&$this, 'compare_FieldName'));
		return $fields;
	}

	function compare_FieldName($field1, $field2){
		return strnatcmp($field1['name'], $field2['name']);
	}

	private function get_report_column_types($report_id) {
		$col_types = array();
		$report = Reports::getReport($report_id);
		$model = $report->getObjectType();
		$manager = new $model();

		$columns = ReportColumns::getAllReportColumns($report_id);

		foreach ($columns as $col) {
			$cp_id = $col->getCustomPropertyId();
			if ($cp_id == 0)
			$col_types[$col->getFieldName()] = $manager->getColumnType($col->getFieldName());
			else {
				$cp = CustomProperties::getCustomProperty($cp_id);
				if ($cp)
				$col_types[$cp->getName()] = $cp->getOgType();
			}
		}

		return $col_types;
	}
}
?>
