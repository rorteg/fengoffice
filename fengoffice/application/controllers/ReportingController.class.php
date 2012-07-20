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
		Env::useHelper('grouping');
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
			ApplicationLogs::createLog($chart, ApplicationLogs::ACTION_TRASH);
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

		/* FIXME
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
		/*$permission_str = " AND " . ProjectCharts::getWorkspaceString(logged_user()->getWorkspacesQuery(true));

		if ($isProjectView) {
			$pids = $project->getAllSubWorkspacesQuery(true);
			$project_str = " AND " . ProjectCharts::getWorkspaceString($pids);
		} else {
			$project_str = "";
		}
		

		list($charts, $pagination) = ProjectCharts::paginate(
		array("conditions" => '`trashed_on` = 0 AND `archived_on` = 0 AND ' . $tagstr . $permission_str . $project_str ,
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
		tpl_assign("listing", $object);*/
	}



	// ---------------------------------------------------
	//  Tasks Reports
	// ---------------------------------------------------

	function total_task_times_p(){
		if (array_var($_GET, 'ws') !== null) {
			$report_data = array_var($_SESSION, 'total_task_times_report_data', array());
			if (array_var($_GET, 'type')) {
				$report_data['timeslot_type'] = array_var($_GET, 'type');
			}
			$_SESSION['total_task_times_report_data'] = $report_data;
			$this->redirectTo('reporting', 'total_task_times_p');
		}

		$users = Contacts::getAllUsers();

		tpl_assign('users', $users);
		tpl_assign('has_billing', BillingCategories::count() > 0);
	}

	function total_task_times($report_data = null, $task = null){
		if (!$report_data) {
			$report_data = array_var($_POST, 'report');
			// save selections into session
			$_SESSION['total_task_times_report_data'] = $report_data;
		}
		
		if (array_var($_GET, 'export') == 'csv') {
			$context = build_context_array(array_var($_REQUEST, 'context'));
			$report_data = json_decode(str_replace("'",'"', $_REQUEST['parameters']), true);
			tpl_assign('context', $context);
		} else {
			$context = active_context();
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
	
		$user = Contacts::findById(array_var($report_data, 'user'));
		
		$now = DateTimeValueLib::now();
		$now->advance(logged_user()->getTimezone()*3600, true);
		switch (array_var($report_data, 'date_type')){
			case 1: //Today
				$st = DateTimeValueLib::make(0,0,0,$now->getMonth(),$now->getDay(),$now->getYear());
				$et = DateTimeValueLib::make(23,59,59,$now->getMonth(),$now->getDay(),$now->getYear());break;
			case 2: //This week
				$monday = $now->getMondayOfWeek();
				$nextMonday = $now->getMondayOfWeek()->add('w',1)->add('d',-1);
				$st = DateTimeValueLib::make(0,0,0,$monday->getMonth(),$monday->getDay(),$monday->getYear());
				$et = DateTimeValueLib::make(23,59,59,$nextMonday->getMonth(),$nextMonday->getDay(),$nextMonday->getYear());break;
			case 3: //Last week
				$monday = $now->getMondayOfWeek()->add('w',-1);
				$nextMonday = $now->getMondayOfWeek()->add('d',-1);
				$st = DateTimeValueLib::make(0,0,0,$monday->getMonth(),$monday->getDay(),$monday->getYear());
				$et = DateTimeValueLib::make(23,59,59,$nextMonday->getMonth(),$nextMonday->getDay(),$nextMonday->getYear());break;
			case 4: //This month
				$st = DateTimeValueLib::make(0,0,0,$now->getMonth(),1,$now->getYear());
				$et = DateTimeValueLib::make(23,59,59,$now->getMonth(),1,$now->getYear())->add('M',1)->add('d',-1);break;
			case 5: //Last month
				$now->add('M',-1);
				$st = DateTimeValueLib::make(0,0,0,$now->getMonth(),1,$now->getYear());
				$et = DateTimeValueLib::make(23,59,59,$now->getMonth(),1,$now->getYear())->add('M',1)->add('d',-1);break;
			case 6: //Date interval
				$st = getDateValue(array_var($report_data, 'start_value'));
				$st = $st->beginningOfDay();
				
				$et = getDateValue(array_var($report_data, 'end_value'));
				$et = $et->endOfDay();
				break;
		}
		
		$timeslotType = array_var($report_data, 'timeslot_type', 0);
		$group_by = array();
		for ($i = 1; $i <= 3; $i++){
			if ($timeslotType == 0)
				$gb = array_var($report_data, 'group_by_' . $i);
			else
				$gb = array_var($report_data, 'alt_group_by_' . $i);

			if ($gb != '0') $group_by[] = $gb;
		}
		
		$timeslots = Timeslots::getTaskTimeslots($context, null, $user, $st, $et, array_var($report_data, 'task_id', 0), $group_by, null, null, null, $timeslotType);
		
		$unworkedTasks = null;
		if (array_var($report_data, 'include_unworked') == 'checked') {
			$unworkedTasks = ProjectTasks::getPendingTasks(logged_user(), $workspace);
			tpl_assign('unworkedTasks', $unworkedTasks);
		}
		
		
		$gb_criterias = array();
		foreach ($group_by as $text) {
			if (in_array($text, array('contact_id', 'rel_object_id'))) $gb_criterias[] = array('type' => 'column', 'value' => $text);
			else if (in_array($text, array('milestone_id', 'priority'))) $gb_criterias[] = array('type' => 'assoc_obj', 'fk' => 'rel_object_id', 'value' => $text);
			else if (str_starts_with($text, 'dim_')) $gb_criterias[] = array('type' => 'dimension', 'value' => str_replace_first('dim_', '', $text));
		}
		$grouped_timeslots = groupObjects($gb_criterias, $timeslots);
		
		tpl_assign('columns', $columns);
		tpl_assign('timeslotsArray', array());                        
		tpl_assign('grouped_timeslots', $grouped_timeslots);
		if (array_var($report_data, 'date_type') == 6) {
			$st->advance(logged_user()->getTimezone()*3600, true);
			$et->advance(logged_user()->getTimezone()*3600, true);
		}
		tpl_assign('start_time', $st);
		tpl_assign('end_time', $et);
		tpl_assign('user', $user);
		tpl_assign('post', $report_data);
		tpl_assign('template_name', 'total_task_times');
		tpl_assign('title', lang('task time report'));
		tpl_assign('allow_export', false);
		if (array_var($_GET, 'export') == 'csv') {
			$this->setTemplate('total_task_times_csv');
			ajx_current("empty");
		}
		else $this->setTemplate('report_wrapper');
	}

	function total_task_times_by_task_print(){
		$this->setLayout("html");

		$task = ProjectTasks::findById(get_id());

		$st = DateTimeValueLib::make(0,0,0,1,1,1900);
		$et = DateTimeValueLib::make(23,59,59,12,31,2036);

		$timeslotsArray = Timeslots::getTaskTimeslots(active_context(), null,null,$st,$et, get_id());
                
                tpl_assign('columns', array());
                tpl_assign('user', array());
                tpl_assign('group_by', array());
                tpl_assign('grouped_timeslots', array());
		tpl_assign('template_name', 'total_task_times');
                tpl_assign('estimate', $task->getTimeEstimate());
		tpl_assign('timeslotsArray', $timeslotsArray);
		tpl_assign('title',lang('task time report'));
		tpl_assign('task_title', $task->getTitle());
                tpl_assign('start_time', $st);
		tpl_assign('end_time', $et);
		$this->setTemplate('report_printer');
	}


	function total_task_times_vs_estimate_comparison_p(){
		$users = owner_company()->getContacts();
		$workspaces = logged_user()->getActiveProjects();

		tpl_assign('workspaces', $workspaces);
		tpl_assign('users', $users);
	}

	function total_task_times_vs_estimate_comparison($report_data = null, $task = null){
		$this->setTemplate('report_wrapper');

		if (!$report_data)
		$report_data = array_var($_POST, 'report');

/*		$workspace = Projects::findById(array_var($report_data, 'project_id'));
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
*/
		$start = getDateValue(array_var($report_data, 'start_value'));
		$end = getDateValue(array_var($report_data, 'end_value'));

		$st = $start->beginningOfDay();
		$et = $end->endOfDay();
		$st = new DateTimeValue($st->getTimestamp() - logged_user()->getTimezone() * 3600);
		$et = new DateTimeValue($et->getTimestamp() - logged_user()->getTimezone() * 3600);

//		$timeslots = Timeslots::getTimeslotsByUserWorkspacesAndDate($st, $et, 'ProjectTasks', null, $workspacesCSV, array_var($report_data, 'task_id',0));
		$timeslots = array();

		tpl_assign('timeslots', $timeslots);
//		tpl_assign('workspace', $workspace);
		tpl_assign('start_time', $st);
		tpl_assign('end_time', $et);
		tpl_assign('user', $user);
		tpl_assign('post', $report_data);
		tpl_assign('template_name', 'total_task_times');
		tpl_assign('title',lang('task time report'));
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
			if(!is_array($conditions)) {
				$conditions = array();
			}
			tpl_assign('conditions', $conditions);
			$columns = array_var($_POST, 'columns');
			if(is_array($columns) && count($columns) > 0){
				tpl_assign('columns', $columns);
				$newReport = new Report();
				
				$member_ids = json_decode(array_var($_POST, 'members'));
				if (!is_array($member_ids) || count($member_ids) == 0) {
					flash_error(lang('must choose at least one member'));
					ajx_current("empty");
					return;
				}
				$members = Members::findAll(array("conditions" => array("`id` IN(?)", $member_ids)));

				if(!$newReport->canAdd(logged_user(), $members)) {
					flash_error(lang('no access permissions'));
					ajx_current("empty");
					return;
				} // if

				$newReport->setObjectName($report_data['name']);
				$newReport->setDescription($report_data['description']);
				$newReport->setReportObjectTypeId($report_data['report_object_type_id']);
				$newReport->setOrderBy($report_data['order_by']);
				$newReport->setIsOrderByAsc($report_data['order_by_asc'] == 'asc');
				
				try{
					DB::beginWork();
					$newReport->save();
					$allowed_columns = $this->get_allowed_columns($report_data['report_object_type_id'], true);
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
					
					$object_controller = new ObjectController();
					
					$object_controller->add_to_members($newReport, $member_ids);
					
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
		
		$types = array(array("", lang("select one")));
		$object_types = ObjectTypes::getAvailableObjectTypes();
		
		foreach ($object_types as $ot) {
			$types[] = array($ot->getId(), lang($ot->getName()));
		}
		if ($selected_type != '')
			tpl_assign('allowed_columns', $this->get_allowed_columns($selected_type));
		
		tpl_assign('object_types', $types);
		tpl_assign('selected_type', $selected_type);
		$new_report = new Report();
		tpl_assign('object', $new_report);
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
				
				$member_ids = json_decode(array_var($_POST, 'members'));
				if (!is_array($member_ids) || count($member_ids) == 0) {
					flash_error(lang('must choose at least one member'));
					ajx_current("empty");
					return;
				}
				$members = Members::findAll(array("conditions" => array("`id` IN(?)", $member_ids)));
		
				DB::beginWork();
				$report->setObjectName($report_data['name']);
				$report->setDescription($report_data['description']);
				$report->setReportObjectTypeId($report_data['report_object_type_id']);
				$report->setOrderBy($report_data['order_by']);
				$report->setIsOrderByAsc($report_data['order_by_asc'] == 'asc');
				
				$report->save();				
					
				$conditions = array_var($_POST, 'conditions');
				if (!is_array($conditions)) {
					$conditions = array();
				}
				
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
				
				$object_controller = new ObjectController();
				$object_controller->add_to_members($report, $member_ids);
					
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
					'name' => $report->getObjectName(),
					'description' => $report->getDescription(),
					'report_object_type_id' => $report->getReportObjectTypeId(),
					'order_by' => $report->getOrderBy(),
					'order_by_asc' => $report->getIsOrderByAsc(),
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

			$selected_type = $report->getReportObjectTypeId();
			
			$types = array(array("", lang("select one")));
			$object_types = ObjectTypes::getAvailableObjectTypes();
			
			foreach ($object_types as $ot) {
				$types[] = array($ot->getId(), lang($ot->getName()));
			}
			
			tpl_assign('object_types', $types);
			tpl_assign('selected_type', $selected_type);
			tpl_assign('object', $report);
			
			tpl_assign('allowed_columns', $this->get_allowed_columns($selected_type), true);
		}
	}

	function view_custom_report(){
		$report_id = array_var($_GET, 'id');
		if (array_var($_GET, 'replace')) {
			ajx_replace();
		}
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
			
			$ot = ObjectTypes::findById($report->getReportObjectTypeId());
			eval('$managerInstance = ' . $ot->getHandlerClass() . "::instance();");
			$externalCols = $managerInstance->getExternalColumns();
			$externalFields = array();
			foreach($externalCols as $extCol){
				$externalFields[$extCol] = $this->get_ext_values($extCol, $report->getReportObjectTypeId());
			}
			$params = array_var($_GET, 'params');
			if(count($paramConditions) > 0 && !isset($params)){
				$this->setTemplate('custom_report_parameters');
				tpl_assign('model', $report->getReportObjectTypeId());
				tpl_assign('title', $report->getObjectName());
				tpl_assign('description', $report->getDescription());
				tpl_assign('conditions', $paramConditions);
				tpl_assign('external_fields', $externalFields);
			}else{
				$this->setTemplate('report_wrapper');
				tpl_assign('template_name', 'view_custom_report');
				tpl_assign('title', $report->getObjectName());
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
				if(!isset($order_by_asc)) $order_by_asc = null;
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
				$ot = ObjectTypes::findById($report->getReportObjectTypeId());
				tpl_assign('model', $ot->getHandlerClass());
				tpl_assign('description', $report->getDescription());
				tpl_assign('conditions', $conditions);
				tpl_assign('parameters', $params);
				tpl_assign('id', $report_id);
				tpl_assign('to_print', false);
			}
			
			ApplicationReadLogs::createLog($report, ApplicationReadLogs::ACTION_READ);
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
			tpl_assign('title', $report->getObjectName());
			$ot = ObjectTypes::findById($report->getReportObjectTypeId());
			tpl_assign('model', $ot->getHandlerClass());
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
                $results['columns'][] = lang("status");
		$types = self::get_report_column_types($report->getId());
		$filename = str_replace(' ', '_',$report->getObjectName()).date('_YmdHis');
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
                    $i = 0;
			foreach($row as $k => $value){
				if ($k == 'object_type_id') continue;
				$db_col = isset($results['db_columns'][$results['columns'][$i]]) ? $results['db_columns'][$results['columns'][$i]] : '';
                                
                                $cell = format_value_to_print($db_col, html_to_text($value), ($k == 'link'?'':array_var($types, $k)), array_var($row, 'object_type_id'), '', is_numeric(array_var($results['db_columns'], $k)) ? "Y-m-d" : user_config_option('date_format'));
				$cell = iconv(mb_internal_encoding(),"ISO-8859-1",html_entity_decode($cell ,ENT_COMPAT));
				echo $cell.';';
                                $i++;
			}
			echo "\n";
		}
		die();
	}
	
	function generatePDFReport(Report $report, $results){
                $results['columns'][] = lang("status");
		$types = self::get_report_column_types($report->getId());
		$ot = ObjectTypes::findById($report->getReportObjectTypeId());
		eval('$managerInstance = ' . $ot->getHandlerClass() . "::instance();");
		$externalCols = $managerInstance->getExternalColumns();
		$filename = str_replace(' ', '_',$report->getObjectName()).date('_YmdHis');
		$pageLayout = $_POST['pdfPageLayout'];
		$fontSize = $_POST['pdfFontSize'];
		include_once(LIBRARY_PATH . '/pdf/fpdf.php');
		$pdf = new FPDF($pageLayout);
		$pdf->setTitle($report->getObjectName());
		$pdf->AddPage();
		$pdf->SetFont('Arial','',$fontSize);
		$pdf->Cell(80);
		$report_title = iconv(mb_internal_encoding(), "ISO-8859-1", html_entity_decode($report->getObjectName(), ENT_COMPAT));
                $pdf->Cell(30, 10, $report_title);
                $pdf->Ln(20);
                $colSizes = array();
                $maxValue = array();
                $fixed_col_sizes = array();
		foreach($results['rows'] as $row) {
			$i = 0;			
                        array_shift ($row);
			foreach($row as $k => $value){	
				if(!isset($maxValue[$i])) $maxValue[$i] = '';
				if(strlen(strip_tags($value)) > strlen($maxValue[$i])){
					$maxValue[$i] = strip_tags($value);
				}
				$i++;  
			}
    	}
    	$k=0;
    	foreach ($maxValue as $str) {
    		$col_title_len = $pdf->GetStringWidth($results['columns'][$k]);
    		$colMaxTextSize = max($pdf->GetStringWidth($str), $col_title_len);
    		$db_col = $results['columns'][$k];
    		$colType = array_var($types, array_var($results['db_columns'], $db_col, ''), '');
    		if($colType == DATA_TYPE_DATETIME && !($report->getObjectTypeName() == 'event' && $results['db_columns'][$db_col] == 'start')){
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
                                if ($k == 'object_type_id') continue;
				$db_col = isset($results['db_columns'][$results['columns'][$i]]) ? $results['db_columns'][$results['columns'][$i]] : '';
                                
                                $cell = format_value_to_print($db_col, html_to_text($value), ($k == 'link'?'':array_var($types, $k)), array_var($row, 'object_type_id'), '', is_numeric(array_var($results['db_columns'], $k)) ? "Y-m-d" : user_config_option('date_format'));
							
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
		download_file($filename, "application/pdf", $report->getObjectName(), true);
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
		$fields = $this->get_allowed_columns(array_var($_GET, 'object_type'));

		ajx_current("empty");
		ajx_extra_data(array('fields' => $fields));
	}

	function get_object_fields_custom_properties(){ //returns only the custom properties
		$fields = $this->get_allowed_columns_custom_properties(array_var($_GET, 'object_type'));

		ajx_current("empty");
		ajx_extra_data(array('fields' => $fields));
	}
	
	
	private function get_allowed_columns_custom_properties($object_type) {
		return array(); //FIXME: no usar todo lo de custom properties por el momento
		$fields = array();
		if(isset($object_type)){
			$customProperties = CustomProperties::getAllCustomPropertiesByObjectType($object_type);
			$objectFields = array();
			foreach($customProperties as $cp){				
				if ($cp->getType() != 'table')
					$fields[] = array('id' => $cp->getId(), 'name' => $cp->getName(), 'type' => $cp->getType(), 'values' => $cp->getValues(), 'multiple' => $cp->getIsMultipleValues());
			}
			$ot = ObjectTypes::findById($report->getObjectTypeId());
			eval('$managerInstance = ' . $ot->getHandlerClass() . "::instance();");	
	
			$common_columns = Objects::instance()->getColumns(false);
			$common_columns = array_diff_key($common_columns, array_flip($managerInstance->getSystemColumns()));
			$objectFields = array_merge($objectFields, $common_columns);
			
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
				
				$field_name = Localization::instance()->lang('field '.$ot->getHandlerClass().' '.$name);
				if (is_null($field_name)) $field_name = lang('field Objects '.$name);
				
				$fields[] = array('id' => $name, 'name' => $field_name, 'type' => $type);
			}
	
		}
		usort($fields, array(&$this, 'compare_FieldName'));
		return $fields;
	}
	
	function get_object_column_list(){
		$allowed_columns = $this->get_allowed_columns(array_var($_GET, 'object_type'));

		tpl_assign('allowed_columns', $allowed_columns);
		tpl_assign('columns', explode(',', array_var($_GET, 'columns', array())));
		tpl_assign('order_by', array_var($_GET, 'orderby'));
		tpl_assign('order_by_asc', array_var($_GET, 'orderbyasc'));
		tpl_assign('genid', array_var($_GET, 'genid'));
		
		$this->setLayout("html");
		$this->setTemplate("column_list");
	}

	function get_object_column_list_task(){
		$allowed_columns = $this->get_allowed_columns_custom_properties(array_var($_GET, 'object_type'));
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
		if($field == 'contact_id' || $field == 'created_by_id' || $field == 'updated_by_id' || $field == 'assigned_to_contact_id' || $field == 'completed_by_id'
			|| $field == 'approved_by_id'){
			$users = Contacts::getAllUsers();
			foreach($users as $user){
				$values[] = array('id' => $user->getId(), 'name' => $user->getObjectName());
			}
		}else if($field == 'milestone_id'){
			$milestones = ProjectMilestones::getActiveMilestonesByUser(logged_user());
			foreach($milestones as $milestone){
				$values[] = array('id' => $milestone->getId(), 'name' => $milestone->getObjectName());
			}
		/*} else if($field == 'object_subtype'){
			$object_types = ProjectCoTypes::findAll(array('conditions' => (!is_null($manager) ? "`object_manager`='$manager'" : "")));
			foreach($object_types as $object_type){
				$values[] = array('id' => $object_type->getId(), 'name' => $object_type->getName());
			}*/
		}
		return $values;
	}

	private function get_allowed_columns($object_type) {
		$fields = array();
		if(isset($object_type)){
			$customProperties = CustomProperties::getAllCustomPropertiesByObjectType($object_type);
			$objectFields = array();
			
			foreach($customProperties as $cp){
				if ($cp->getType() == 'table') continue;
				
				$fields[] = array('id' => $cp->getId(), 'name' => $cp->getName(), 'type' => $cp->getType(), 'values' => $cp->getValues(), 'multiple' => $cp->getIsMultipleValues());
			}
			
			$ot = ObjectTypes::findById($object_type);
			eval('$managerInstance = ' . $ot->getHandlerClass() . "::instance();");
			$objectColumns = $managerInstance->getColumns();
			
			$objectFields = array();
			
			$objectColumns = array_diff($objectColumns, $managerInstance->getSystemColumns());
			foreach($objectColumns as $column){
				$objectFields[$column] = $managerInstance->getColumnType($column);
			}
			
			$common_columns = Objects::instance()->getColumns(false);
			$common_columns = array_diff_key($common_columns, array_flip($managerInstance->getSystemColumns()));
			$objectFields = array_merge($objectFields, $common_columns);

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
				
				$field_name = Localization::instance()->lang('field '.$ot->getHandlerClass().' '.$name);
				if (is_null($field_name)) $field_name = lang('field Objects '.$name);

				$fields[] = array('id' => $name, 'name' => $field_name, 'type' => $type);
			}
	
			$externalFields = $managerInstance->getExternalColumns();
			foreach($externalFields as $extField){
				$field_name = Localization::instance()->lang('field '.$ot->getHandlerClass().' '.$extField);
				if (is_null($field_name)) $field_name = lang('field Objects '.$extField);
				
				$fields[] = array('id' => $extField, 'name' => $field_name, 'type' => 'external', 'multiple' => 0);
			}
			
			if (!array_var($_REQUEST, 'noaddcol')) {
				Hook::fire('custom_reports_additional_columns', null, $fields);
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
		$ot = ObjectTypes::findById($report->getReportObjectTypeId());
		$model = $ot->getHandlerClass();
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
