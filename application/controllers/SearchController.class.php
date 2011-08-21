<?php
class SearchController extends ApplicationController {
	
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website');
		ajx_set_panel("search");
	} // __construct
	
	
	/**
	 * Execute search
	 *
	 * @param void
	 * @return null
	 */
	function search() {
		if(active_project() && !logged_user()->isProjectUser(active_project())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
		
		$pageType = array_var($_GET, 'page_type');
		$search_for = array_var($_GET, 'search_for');
		
		$objectManagers = array("ProjectWebpages", "ProjectMessages", "MailContents", "ProjectFiles",
				 "ProjectMilestones", "ProjectTasks", "ProjectEvents");
		$objectTypes = array(lang('webpages'), lang('messages'), lang('emails'), 
				lang('files'), lang('milestones'), lang('tasks'), lang('events'));
		$iconTypes = array('webpage', 'message', 'email', 
				'file', 'milestone', 'task', 'event');
		if (user_config_option('show_file_revisions_search')){			
			array_splice($objectManagers, 4, 0, 'ProjectFileRevisions');
			array_splice($objectTypes, 4, 0, lang('file contents'));
			array_splice($iconTypes, 4, 0, 'file');			
		}                        
		
		$search_results = array();
		
		$timeBegin = microtime(true);
		if(trim($search_for) == '') {
			$search_results = null;
			$pagination = null;
		} else {
			$search_results = $this->searchWorkspaces($search_for,$search_results,5);
			$search_results = $this->searchUsers($search_for,$search_results,5);
			$search_results = $this->searchContacts($search_for,$search_results,5);
			
			if (array_var($_GET, 'search_all_projects') != "true" && active_project() instanceof Project) {
				$projects = active_project()->getAllSubWorkspacesCSV(true);
			} else {
				$projects = null;
			}
			
			$c = 0;
			foreach ($objectManagers as $om){
				$user_id = $om == "MailContents" ? logged_user()->getId() : 0;
				$results = SearchableObjects::searchByType($search_for, $projects, $om, true, 5, 1, null, $user_id);
				if (count($results[0]) > 0){
					$sr = array();
					$sr['result'] = $results[0];
					$sr['pagination'] = $results[1];
					$sr['type'] = $objectTypes[$c];
					$sr['icontype'] = $iconTypes[$c];
					$sr['manager'] = $om;
					$search_results[] = $sr;
				}
				$c++;
			}
		} // if
		$timeEnd = microtime(true);
	    if(str_starts_with($search_for,'"') && str_ends_with($search_for,'"')){    		
    		$search_for = str_replace('"', '', $search_for);    		
		}
		tpl_assign('search_string', $search_for);
		tpl_assign('search_results', $search_results);
		tpl_assign('time', $timeEnd - $timeBegin);
		ajx_set_no_toolbar(true);
		ajx_replace(true);
	} // search
	
	function searchContacts($search_term, $search_results = null, $row_count = 5, $page = 1){
		if (!is_array($search_results))
			$search_results = array();
		
		if (array_var($_GET, 'search_all_projects') != "true" && active_project() instanceof Project) {
			$projects = active_project()->getAllSubWorkspacesCSV(true);
		} else {
			$projects = null;
		}
			
		$results = SearchableObjects::searchByType($search_term, $projects, 'Contacts', true, $row_count, $page = 1);
		if (count($results[0]) > 0){
			$sr = array();
			$sr['result'] = $results[0];
			$sr['pagination'] = $results[1];
			$sr['type'] = lang('contacts');
			$sr['icontype'] = 'contact';
			$sr['manager'] = 'Contacts';
			$search_results[] = $sr;
		}
		
		$results = SearchableObjects::searchByType($search_term, $projects, 'Companies', true, $row_count, $page = 1);
		if (count($results[0]) > 0){
			$sr = array();
			$sr['result'] = $results[0];
			$sr['pagination'] = $results[1];
			$sr['type'] = lang('companies');
			$sr['icontype'] = 'company';
			$sr['manager'] = 'Companies';
			$search_results[] = $sr;
		}
		
		return $search_results;
	}
	
	function searchWorkspaces($search_term, $search_results = null, $row_count = 5, $page = 1){
		if (!is_array($search_results))
			$search_results = array();
		
		$results = SearchableObjects::searchByType($search_term, '0', 'Projects', true, $row_count, $page);
		if (count($results[0]) > 0){
			$sr = array();
			$sr['result'] = $results[0];
			$sr['pagination'] = $results[1];
			$sr['type'] = lang('projects');
			$sr['icontype'] = 'project';
			$sr['manager'] = 'Projects';
			$search_results[] = $sr;
		}
		
		return $search_results;
	}
	
	function searchUsers($search_term, $search_results = null, $row_count = 5, $page = 1){
		if (!is_array($search_results))
			$search_results = array();
		
		$results = SearchableObjects::searchByType($search_term, '0', 'Users', true, $row_count, $page);
		if (count($results[0]) > 0){
			$sr = array();
			$sr['result'] = $results[0];
			$sr['pagination'] = $results[1];
			$sr['type'] = lang('users');
			$sr['icontype'] = 'user';
			$sr['manager'] = 'Users';
			$search_results[] = $sr;
		}
		
		return $search_results;
	}
	
	/**
	 * Execute search
	 *
	 * @param void
	 * @return null
	 */
	function searchbytype() {
		$this->setTemplate('search');
		
		if(active_project() && !logged_user()->isProjectUser(active_project())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
		
		$page = array_var($_GET, 'page');
		$pageType = array_var($_GET, 'page_type');
		$search_for = array_var($_GET, 'search_for');
		$manager = array_var($_GET, 'manager');
		
		$objectManagers = array("ProjectWebpages", "ProjectMessages", "MailContents", "ProjectFiles",
			 "ProjectFileRevisions", "ProjectMilestones", "ProjectTasks", "ProjectEvents");
		$objectTypes = array(lang('webpages'), lang('messages'), lang('emails'), 
			lang('files'), lang('files'), lang('milestones'), lang('tasks'), lang('events'));
		$iconTypes = array('webpage', 'message', 'email', 
			'file', 'file', 'milestone', 'task', 'event');  
		
		$search_results = array();
		
		$timeBegin = microtime(true);
		if(trim($search_for) == '') {
			$search_results = null;
			$pagination = null;
		} else {
			if (array_var($_GET, 'search_all_projects') != "true" && active_project() instanceof Project) {
				$projects = active_project()->getAllSubWorkspacesCSV(true);
			} else {
				$projects = null;
			}
				
			switch($manager) {
				case 'Contacts':
				case 'Companies':
					$search_results = $this->searchContacts($search_for, array(), 20, $page);
					break;
				case 'Projects':
					$search_results = $this->searchWorkspaces($search_for, array(), 20, $page);
					break;
				case 'Users':
					$search_results = $this->searchUsers($search_for, array(), 20, $page);
					break;
				default:
					$user_id = $manager == "MailContents" ? logged_user()->getId() : 0;
					$results = SearchableObjects::searchByType($search_for, $projects, $manager, true, 20,$page, null, $user_id);
					if (count($results[0]) > 0){
						$c = array_search($manager, $objectManagers);
						$sr = array();
						$pagination = $results[1];
						$sr['result'] = $results[0];
						$sr['pagination'] = $pagination;
						$sr['type'] =  $objectTypes[$c];
						$sr['icontype'] = $iconTypes[$c];
						$sr['manager'] = $manager;
						$search_results[] = $sr;
					}
					break;
			}
		} // if
		$timeEnd = microtime(true);
		
		tpl_assign('search_string', $search_for);
		tpl_assign('search_results', $search_results);
		tpl_assign('time', $timeEnd - $timeBegin);
		tpl_assign('enable_pagination', true);
	} // search
}
?>