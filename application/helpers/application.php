<?php

/**
 * Application helpers. This helpers are injected into the controllers
 * through ApplicationController constructions so they are available in
 * whole application
 *
 * @author Ilija Studen <ilija.studen@gmail.com>
 */

/**
 * Render user box
 *
 * @param User $user
 * @return null
 */
function render_user_box(User $user) {
	tpl_assign('_userbox_user', $user);
	tpl_assign('_userbox_projects', $user->getActiveProjects());
	$crumbs = array();
	$crumbs[] = array(
		'url' => get_url('help','help_options', array('current' => 'help')),
		'text' => lang('help'),
	);
	$crumbs[] = array(
		'url' => logged_user()->getAccountUrl(),
		'target' => 'account',
		'text' => lang('account'),
	);
	if (logged_user()->isMemberOfOwnerCompany() && logged_user()->isAdministrator()) {
		$crumbs[] = array(
			'url' => get_url('administration', 'index'),
			'target' => 'administration',
			'text' => lang('administration'),
		);
	}
	Hook::fire('render_userbox_crumbs', null, $crumbs);
	$crumbs = array_reverse($crumbs);
	tpl_assign('_userbox_crumbs', $crumbs);
	return tpl_fetch(get_template_path('user_box', 'application'));
} // render_user_box
 
/**
 * Render project users combo.
 *
 * @param String $name
 * @param array $attributes
 * @return String All users I am sharing something with.
 */
function render_sharing_users($name, $attributes = null) {
	//TODO:  This functions must be rebuilt
	$perms= ObjectUserPermissions::getAllPermissionsByUser(logged_user());
	$options = array(option_tag(lang('none'), 0));
	$my_id = logged_user()->getId();
	if (isset($perms)) {
		foreach ($perms as $perm)
		{
			$file_id=$perm->getFileId();
			if(trim($file_id) !='')
			{
				$users = ObjectUserPermissions::getAllPermissionsByObjectIdAndManager($file_id, 'ProjectFiles');
				foreach ($users as $user_perm)
				{
					$user_id=$user_perm->getUserId();
					if($user_id!=null && trim($user_id)!='' && $user_id!=$my_id)
					{
						$user = Users::findById($user_id);
						if($user != null )
						{//foreach user
							$options[] = option_tag($user->getUserName(),$user->getUserName());
						}
					}
				}
			}
		}
	}
	$options=array_unique($options);
	return select_box($name,$options, $attributes);
} // render_user_box

/**
 * This function will render system notices for this user
 *
 * @param User $user
 * @return string
 */
function render_system_notices(User $user) {
	if(!$user->isAdministrator()) return;

	$system_notices = array();
	if (config_option('upgrade_last_check_new_version', false)) $system_notices[] = lang('new Feng Office version available', get_url('administration', 'upgrade'));

	if(count($system_notices)) {
		tpl_assign('_system_notices', $system_notices);
		return tpl_fetch(get_template_path('system_notices', 'application'));
	} // if
} // render_system_notices

/**
 * Render select company box
 *
 * @param integer $selected ID of selected company
 * @param array $attributes Additional attributes
 * @return string
 */
function select_company($name, $selected = null, $attributes = null, $allow_none = true, $check_permissions = false) {
	if (!$check_permissions) {
		$companies = Companies::findAll(array('order' => 'client_of_id ASC, name ASC'));
	} else {
		$companies = Companies::getVisibleCompanies(logged_user(), "`id` <> " . owner_company()->getId());
		if (logged_user()->isMemberOfOwnerCompany() || owner_company()->canAddUser(logged_user())) {
			// add the owner company
			$companies = array_merge(array(owner_company()), $companies);
		}
	}
	if ($allow_none) {
		$options = array(option_tag(lang('none'), 0));
	} else {
		$options = array();
	}
	if(is_array($companies)) {
		foreach($companies as $company) {
			$option_attributes = $company->getId() == $selected ? array('selected' => 'selected') : null;
			$company_name = $company->getName();
			//if($company->isOwner()) $company_name .= ' (' . lang('owner company') . ')';
			$options[] = option_tag($company_name, $company->getId(), $option_attributes);
		} // foreach
	} // if
	return select_box($name, $options, $attributes);
} // select_company

/**
 * Render select project box
 *
 * @param integer $selected ID of selected project
 * @param array $attributes Additional attributes
 * @return string
 */
function select_project($name, $projects, $selected = null, $attributes = null, $allow_none = null) {
	$options = array();
	if($allow_none) {
		$options[] = option_tag(lang('none'), 0);
	}
	if(is_array($projects)) {
		foreach($projects as $project) {
			$option_attributes = $project->getId() == $selected ? array('selected' => 'selected') : null;
			$project_name = $project->getName();
			$options[] = option_tag($project_name, $project->getId(), $option_attributes);
		} // foreach
	} // if
	return select_box($name, $options, $attributes);
} // select_project

function select_project2($name, $projectId, $genid, $allowNone = false, $extraWS = null, $workspaces = null) {
	$extra = "";
	if (is_array($extraWS)) {
		foreach ($extraWS as $ws) {
			if ($extra != "") $extra .= ",";
			$extra .= json_encode($ws);
		}
	}
	if (is_array($workspaces)) {
		$workspacesToJson = array();
		$wsset = array();
		foreach ($workspaces as $w) {
			$wsset[$w->getId()] = true;
		}
		foreach ($workspaces as $w){
			$tempParent = $w->getParentId();
			$x = $w;
			while ($x instanceof Project && !isset($wsset[$tempParent])) {
				$tempParent = $x->getParentId();
				$x = $x->getParentWorkspace();
			}
			if (!$x instanceof Project) {
				$tempParent = 0;
			}
			
			$workspacesToJson[] = array(
				"id" => $w->getId(),
				"name" => $w->getName(),
				"parent" => $tempParent,
				"realParent" => $w->getParentId(),
				"depth" => $w->getDepth(),
				"color" => $w->getColor(),
				);
		}
		$wsList = json_encode($workspacesToJson);
	} else {
		$wsList = "null";
	}
	$extra = "[$extra]";
	$html = "<div id='" . $genid  . "wsSel'></div>
		<script>
		og.drawWorkspaceSelector('" .  $genid  . "wsSel', $projectId, '$name', " . ($allowNone? 'true':'false') . ", $extra, $wsList);
		</script>
	";
	
	return $html;
} // select_project

/**
 * Returns a control to select multiple workspaces
 *
 * @param string $name
 * 		Name for the control
 * @param array $workspaces
 * 		Array of workspaces to choose from. If null the workspaces from the WorkspacePanel will be loaded.
 * @param array $selected
 * 		Array of workspaces selected by default
 * @return string
 * 		HTML for the control
 */
function select_workspaces($name = "", $workspaces = null, $selected = null, $id = null) {
	require_javascript('og/WorkspaceChooser.js');
	
	if (!isset($id)) $id = gen_id();
		
	$selectedCSV = "";
	if (is_array($selected)) {
		foreach ($selected as $s) {
			if ($s instanceof Project) {
				if ($selectedCSV != "") $selectedCSV .= ",";
				$selectedCSV .= $s->getId();
			}
		}
	}
		
	$workspacesToJson = array();

	$wsset = array();
	if(is_array($workspaces)){
		foreach ($workspaces as $w) {
			$wsset[$w->getId()] = true;
		}
		foreach ($workspaces as $w){
			$tempParent = $w->getParentId();
			$x = $w;
			while ($x instanceof Project && !isset($wsset[$tempParent])) {
				$tempParent = $x->getParentId();
				$x = $x->getParentWorkspace();
			}
			if (!$x instanceof Project) {
				$tempParent = 0;
			}
			
			$workspacesToJson[] = array(
				"id" => $w->getId(),
				"n" => $w->getName(),
				"p" => $tempParent,
				"rp" => $w->getParentId(),
				"d" => $w->getDepth(),
				"c" => $w->getColor(),
				);
		}
		$loadFrom = 'false';
	} else {
		$loadFrom = "'workspace-panel'";
	}
	$output = "<div id=\"$id-wsTree\"></div>
			<input id=\"$id-field\" type=\"hidden\" value=\"$selectedCSV\" name=\"$name\"></input>
		<script>
		var wsTree = new og.WorkspaceChooserTree({
			renderTo: '$id-wsTree',
			field: '$id-field',
			loadWorkspacesFrom: $loadFrom,
			id: '$id',
			workspaces: " . json_encode($workspacesToJson) . ",
			height: 320,
			width: 210
		});
		</script>
	";
	return $output;
} // select_workspaces

/**
 * Returns a control to select multiple users or groups
 *
 * @param string $name
 * 		Name for the control
 * @param array $workspaces
 * 		Array of workspaces to choose from. If null the workspaces from the WorkspacePanel will be loaded.
 * @param array $selected
 * 		Array of workspaces selected by default
 * @return string
 * 		HTML for the control
 */
function select_users_or_groups($name = "", $users = null, $selected = null, $id = null) {
	require_javascript('og/UserGroupPicker.js');
	
	if (!isset($id)) $id = gen_id();
		
	$selectedCSV = "";
	if (is_array($selected)) {
		foreach ($selected as $s) {
			if ($s instanceof Project) {
				if ($selectedCSV != "") $selectedCSV .= ",";
				$selectedCSV .= $s->getId();
			}
		}
	}
	
	$json = array();
	if (logged_user()->isMemberOfOwnerCompany()) {
		$companies = Companies::findAll(array('order' => 'name ASC'));
	} else {
		$companies = array(owner_company(), logged_user()->getCompany());
	}
	foreach ($companies as $company) {
		$company_users = $company->getUsers();
		if (count($company_users) > 0) {
			$json[] = array(
				'p' => 'users',
				't' => 'company',
				'id' => 'c' . $company->getId(),
				'n' => $company->getName(),
			);
			foreach ($company_users as $u) {
				$json[] = array(
					'p' => 'c' . $company->getId(),
					't' => 'user',
					'g' => $u->isGuest() ? 1 : 0,
					'id' => $u->getId(),
					'n' => $u->getDisplayName(),
				);	
			}
		}
	}
	$groups = Groups::findAll(array('order' => 'name ASC'));
	foreach ($groups as $group) {
		$json[] = array(
			'p' => 'groups',
			't' => 'group',
			'id' => $group->getId(),
			'n' => $group->getName(),
		);
	}
	$jsonUsers = json_encode($json);
	
	$output = "<div id=\"$id-user-picker\"></div>
			<input id=\"$id-field\" type=\"hidden\" value=\"$selectedCSV\" name=\"$name\"></input>
		<script>
		var userPicker = new og.UserPicker({
			renderTo: '$id-user-picker',
			field: '$id-field',
			id: '$id',
			users: $jsonUsers,
			height: 320,
			width: 210
		});
		</script>
	";
	return $output;
} // select_users_or_groups

function intersectCSVs($csv1, $csv2){
	$arr1 = explode(',', $csv1);
	$arr2 = explode(',', $csv2);
	$final = array();
	
	foreach ($arr1 as $a1)
		foreach ($arr2 as $a2)
			if ($a1 == $a2){
				$final[] = $a1;
				break;
			}
			
	return implode(',', $final);
}

function allowed_users_to_assign($wsid) {
	$ws = Projects::findById($wsid);
	$comp_array = array();
	$companies = Companies::findAll();
	if ($companies != null) {
		foreach ($companies as $comp) {
			if ($ws != null) $users = $comp->getUsersOnProject($ws);
			else continue;
			if (is_array($users)) {
				foreach ($users as $k => $user) {
					// if logged_user can assign tasks to user and user can read tasks the user is allowed
					if (!can_assign_task(logged_user(), $ws, $user) || !can_read_type($user, $ws, 'ProjectTasks')) {
						unset($users[$k]);
					}
				}
				if (count($users) > 0) {
					$comp_data = array(
									'id' => $comp->getId(),
									'name' => $comp->getName(),
									'users' => array() 
					);
					foreach ($users as $user) {
						$comp_data['users'][] = $user->getArrayInfo();
					}
					//if ($ws == null || can_assign_task(logged_user(), $ws, $comp)) {
					if (count($users) > 0) {
						$comp_array[] = $comp_data;
					}
				}
			}
		}
	}
	return $comp_array;
}


/**
 * Render assign to SELECT
 *
 * @param string $list_name Name of the select control
 * @param Project $project Selected project, if NULL active project will be used
 * @param integer $selected ID of selected user
 * @param array $attributes Array of select box attributes, if needed
 * @return null
 */
function assign_to_select_box($list_name, $project = null, $selected = null, $attributes = null, $genid = null) {
	if (!$genid) $genid = gen_id();
	$ws_id = $project instanceof Project ? $project->getId() : 0;
	require_javascript('og/tasks/main.js');
	require_javascript('og/tasks/addTask.js');
	ob_start(); ?>
    <input type="hidden" id="<?php echo $genid ?>taskFormAssignedTo" name="<?php echo $list_name?>"></input>
	<div id="<?php echo $genid ?>assignto_div">
		<div id="<?php echo $genid ?>assignto_container_div"></div>
	</div>
	<script>
	og.drawAssignedToSelectBoxSimple = function(companies, user, genid) {
		usersStore = ogTasks.buildAssignedToComboStore(companies);
		var assignCombo = new Ext.form.ComboBox({
			renderTo:genid + 'assignto_container_div',
			name: 'taskFormAssignedToCombo',
			id: genid + 'taskFormAssignedToCombo',
			value: user,
			store: usersStore,
			displayField:'text',
	        typeAhead: true,
	        mode: 'local',
	        triggerAction: 'all',
	        selectOnFocus:true,
	        width:160,
	        tabIndex: '150',
	        valueField: 'value',
	        emptyText: (lang('select user or group') + '...'),
	        valueNotFoundText: ''
		});
		assignCombo.on('select', function() {
			combo = Ext.getCmp(genid + 'taskFormAssignedToCombo');
			assignedto = document.getElementById(genid + 'taskFormAssignedTo');
			if (assignedto) assignedto.value = combo.getValue();
		});
	}
	og.drawAssignedToSelectBoxSimple(<?php echo json_encode(allowed_users_to_assign($ws_id)) ?>, '<?php echo ($selected ? $selected : '0:0') ?>', '<?php echo $genid ?>');
	</script> <?php
	return ob_get_clean();
} // assign_to_select_box



function user_select_box($list_name, $selected = null, $attributes = null) {
	$logged_user = logged_user();
	
	$users = Users::getAll();
	
	if(is_array($users)) {
		foreach($users as $user) {
			$option_attributes = $user->getId() == $selected ? array('selected' => 'selected') : null;
			$options[] = option_tag($user->getDisplayName(), $user->getId(), $option_attributes);
		} // foreach
	} // if

	return select_box($list_name, $options, $attributes);
} // user_select_box



/**
 * Renders select milestone box
 *
 * @param string $name
 * @param Project $project
 * @param integer $selected ID of selected milestone
 * @param array $attributes Array of additional attributes
 * @return string
 * @throws InvalidInstanceError
 */
function select_milestone($name, $project = null, $selected = null, $attributes = null) {
	if(is_array($attributes)) {
		if(!isset($attributes['class'])) $attributes['class'] = 'select_milestone';
	} else {
		$attributes = array('class' => 'select_milestone');
	} // if

	$options = array(option_tag(lang('none'), 0));
	if($project)
	 $milestones = $project->getOpenMilestones();
	else
	 $milestones = ProjectMilestones::getActiveMilestonesByUser(logged_user()); 
	 
	if(is_array($milestones)) {
		if ($selected){		//Fixes bug: If task is in a subworkspace of it's milestone's workspace, and user is standing on it, the assigned milestone is set to none when task is edited.
			$is_in_array = false;	
				foreach($milestones as $milestone)
				if ($milestone->getId() == $selected) $is_in_array = true;
				
			if (!$is_in_array){
				$milestone = ProjectMilestones::findById($selected);
				if ($milestone)
					$milestones[] = $milestone;
			}
		}
		foreach($milestones as $milestone) {
			$option_attributes = $milestone->getId() == $selected ? array('selected' => 'selected') : null;
			$options[] = option_tag($milestone->getName(), $milestone->getId(), $option_attributes);
		} // foreach
	} // if

	return select_box($name, $options, $attributes);
} // select_milestone

/**
 * Render select task list box
 *
 * @param string $name Form control name
 * @param Project $project
 * @param integer $selected ID of selected object
 * @param boolean $open_only List only active task lists (skip completed)
 * @param array $attach_data Additional attributes
 * @return string
 */
function select_task_list($name, $project = null, $selected = null, $open_only = false, $attributes = null) {
	if (is_null($project)) $project = active_or_personal_project();
	//if (!($project instanceof Project)) throw new InvalidInstanceError('$project', $project, 'Project');

	if (is_array($attributes)) {
		if (!isset($attributes['class'])) $attributes['class'] = 'select_task_list';
	} else {
		$attributes = array('class' => 'select_task_list');
	} // if

	$options = array(option_tag(lang('none'), 0));
	if ($project instanceof Project) { 
		$task_lists = $open_only ? $project->getOpenTasks() : $project->getTasks();
	} else {
		$task_lists = $open_only ? ProjectTasks::getProjectTasks(null, null, 'ASC', null, null, null, null, null, null, true) : ProjectTasks::getProjectTasks(null, null, 'ASC', 0, null, null, null, null, null, false);
	}
	$selected_exists = is_null($selected);
	if(is_array($task_lists)) {
		foreach($task_lists as $task_list) {
			if ($task_list->getId() == $selected) {
				$selected_exists = true;
				$option_attributes =  array('selected' => 'selected');
			} else {
				$option_attributes =  null;
			}
			$options[] = option_tag($task_list->getTitle(), $task_list->getId(), $option_attributes);
		} // foreach
	} // if
	if (!$selected_exists) {
		$task = ProjectTasks::findById($selected);
		if ($task instanceof ProjectTask) {
			$options[] = option_tag($task->getTitle(), $task->getId(), array("selected" => "selected"));
		}
	}

	return select_box($name, $options, $attributes);
} // select_task_list

/**
 * Return select message control
 *
 * @param string $name Control name
 * @param Project $project
 * @param integer $selected ID of selected message
 * @param array $attributes Additional attributes
 * @return string
 */
function select_message($name, $project = null, $selected = null, $attributes = null) {
	if(is_null($project)) $project = active_project();
	if(!($project instanceof Project)) throw new InvalidInstanceError('$project', $project, 'Project');

	if(is_array($attributes)) {
		if(!isset($attributes['class'])) $attributes['class'] = 'select_message';
	} else {
		$attributes = array('class' => 'select_message');
	} // if

	$options = array(option_tag(lang('none'), 0));
	$messages = $project->getMessages();
	if(is_array($messages)) {
		foreach($messages as $messages) {
			$option_attributes = $messages->getId() == $selected ? array('selected' => 'selected') : null;
			$options[] = option_tag($messages->getTitle(), $messages->getId(), $option_attributes);
		} // foreach
	} // if

	return select_box($name, $options, $attributes);
} // select_message

/**
 * Render select folder box
 *
 * @param string $name Control name
 * @param Project $project
 * @param integer $selected ID of selected folder
 * @param array $attributes Select box attributes
 * @return string
 */
function select_project_folder($name, $project = null, $selected = null, $attributes = null) {
	if(is_null($project)) {
		$project = active_project();
	} // if
	if(!($project instanceof Project)) {
		throw new InvalidInstanceError('$project', $project, 'Project');
	} // if

	if(is_array($attributes)) {
		if(!isset($attributes['class'])) $attributes['class'] = 'select_folder';
	} else {
		$attributes = array('class' => 'select_folder');
	} // if

	$options = array(option_tag(lang('none'), 0));

	$folders = $project->getFolders();
	if(is_array($folders)) {
		foreach($folders as $folder) {
			$option_attributes = $folder->getId() == $selected ? array('selected' => true) : null;
			$options[] = option_tag($folder->getName(), $folder->getId(), $option_attributes);
		} // foreach
	} // if

	return select_box($name, $options, $attributes);
} // select_project_folder

/**
 * Select a project data object
 *
 * @param string $name Control name
 * @param Project $project
 * @param integer $selected ID of selected object
 * @param array $exclude_files Array of IDs of objects that need to be excluded (already linked to object etc)
 * @param array $attributes
 * @return string
 */
function select_project_object($name, $project = null, $selected = null, $exclude_files = null, $attributes = null) {
	// look for project
	if(is_null($project)) {
		$project = active_project();
	} // if
	if(!($project instanceof Project)) {
		throw new InvalidInstanceError('$project', $project, 'Project');
	} // if
	// look for selection
	$sel_id = 0;
	$sel_type = '';
	if(is_array($selected))
	{
		$sel_id = $selected['id'];
		$sel_type = $selected['type'];
	}
	//default non-value
	$all_options = array(option_tag(lang('none'), 0)); // array of options
	//milestones
	$milestones = $project->getOpenMilestones();
	if(is_array($milestones)) {
		$all_options[] = option_tag('', 0); // separator
		foreach($milestones as $milestone) {
			$option_attributes = $sel_type=='ProjectMilestone' && $milestone->getId() == $selected ? array('selected' => 'selected') : null;
			$all_options[] = option_tag('Milestone:: ' . $milestone->getName(), $milestone->getId() . '::' .
			get_class($milestone->manager()), $option_attributes);
		} // foreach
	} // if
	//tasklists
	$tasks = $project->getOpenTasks();
	if(is_array($tasks)) {
		$all_options[] = option_tag('', 0); // separator
		foreach($tasks as $task) {
			$option_attributes = $sel_type=='ProjectTask' && $task->getId() == $selected ? array('selected' => 'selected') : null;
			$all_options[] = option_tag('Task:: ' . $task->getTitle(), $task->getId() . '::' .
			get_class($task->manager()), $option_attributes);
		} // foreach
	} // if
	//messages
	$messages = $project->getMessages();
	if(is_array($messages)) {
		$all_options[] = option_tag('', 0); // separator
		foreach($messages as $message) {
			$option_attributes = $sel_type=='ProjectMessage' && $message->getId() == $sel_id ? array('selected' => 'selected') : null;
			$all_options[] = option_tag('Message:: ' . $message->getTitle(), $message->getId() . '::' .
			get_class($message->manager()), $option_attributes);
		} // foreach
	} // if
	 
	//all files are orphans
	$orphaned_files = $project->getOrphanedFiles();
	if(is_array($orphaned_files)) {
		$all_options[] = option_tag('', 0); // separator
		foreach($orphaned_files as $file) {
			if(is_array($exclude_files) && in_array($file->getId(), $exclude_files)) continue;

			$option_attrbutes = $sel_type=='ProjectFile' && $file->getId() == $selected ? array('selected' => true) : null;
			$all_options[] = option_tag('File:: ' . $file->getFilename(), $file->getId() . '::' .
			get_class($file->manager()), $option_attrbutes);
		} // foreach
	} // if

	return select_box($name, $all_options, $attributes);
}

/**
 * Select a single project file
 *
 * @param string $name Control name
 * @param Project $project
 * @param integer $selected ID of selected file
 * @param array $exclude_files Array of IDs of files that need to be excluded (already attached to object etc)
 * @param array $attributes
 * @return string
 */
function select_project_file($name, $project = null, $selected = null, $exclude_files = null, $attributes = null) {
	if(is_null($project)) {
		$project = active_project();
	} // if
	if(!($project instanceof Project)) {
		throw new InvalidInstanceError('$project', $project, 'Project');
	} // if

	$all_options = array(option_tag(lang('none'), 0)); // array of options

	$folders = $project->getFolders();
	if(is_array($folders)) {
		foreach($folders as $folder) {
			$files = $folder->getFiles();
			if(is_array($files)) {
				$options = array();
				foreach($files as $file) {
					if(is_array($exclude_files) && in_array($file->getId(), $exclude_files)) continue;

					$option_attrbutes = $file->getId() == $selected ? array('selected' => true) : null;
					$options[] = option_tag($file->getFilename(), $file->getId(), $option_attrbutes);
				} // if

				if(count($options)) {
					$all_options[] = option_tag('', 0); // separator
					$all_options[] = option_group_tag($folder->getName(), $options);
				} // if
			} // if
		} // foreach
	} // if

	$orphaned_files = $project->getOrphanedFiles();
	if(is_array($orphaned_files)) {
		$all_options[] = option_tag('', 0); // separator
		foreach($orphaned_files as $file) {
			if(is_array($exclude_files) && in_array($file->getId(), $exclude_files)) continue;

			$option_attrbutes = $file->getId() == $selected ? array('selected' => true) : null;
			$all_options[] = option_tag($file->getFilename(), $file->getId(), $option_attrbutes);
		} // foreach
	} // if

	return select_box($name, $all_options, $attributes);
} // select_project_file

/**
 * Render select chart type box
 *
 * @param array $chart_types list of chart types as returned by the factory
 * @param integer $selected ID of selected chart type
 * @param array $attributes Additional attributes
 * @return string
 */
function select_chart_type($name, $chart_types, $selected = null, $attributes = null) {
	$options = array();
	if(is_array($chart_types)) {
		foreach($chart_types as $ct) {
			$option_attributes = array_search($ct,$chart_types) == $selected ? array('selected' => 'selected') : null;
			$options[] = option_tag(lang($ct), array_search($ct,$chart_types), $option_attributes);
		} // foreach
	} // if
	return select_box($name, $options, $attributes);
} // select_company

/**
 * Show button with javascript to add tag from combo to text box
 * $src source control name
 * $dest destination control name
 */
function show_addtag_button($src,$dest, $attributes= null)
{
	$src='document.getElementById(\'' . $src .'\').value';
	$dest='document.getElementById(\'' . $dest . '\').value';
	$js='javascript:'.
  		'if(' . $dest . '==\'\') '.
  			' ' . $dest . ' = ' . $src . '; '. 
  		' else '.
	// check whether the tag es included, if it is, do not add it
	// if (dest.substring(1+ dest.lastIndexOf(",",dest.indexOf(src)), dest.indexOf(",",1+dest.lastIndexOf(",",dest.indexOf(src)))).trim().replace(/^\s+|\s+$/g, '') == src)
	//'if (!((' . $dest . ' + \',\').substring(1+ ' . $dest . '.lastIndexOf(",",' . $dest . '.indexOf(' . $src . ')), ' . $dest . '.indexOf(",",1+' . $dest . '.lastIndexOf(",",' . $dest . '.indexOf(' . $src . ')))).replace(/^\s+|\s+$/g, \'\') == ' . $src . '))' .
  			' ' . $dest . ' = '.
  				' ' . $dest . '  + ", " +(' . $src . ')';
	$attributes['type']= 'button';
	$attributes['onclick'] = $js;
	return input_field('addTagButton','>',$attributes);
	 
}

/**
 * Return project object tags widget
 *
 * @param string $name
 * @param Project $project
 * @param string $value
 * @Param array $attributes Array of control attributes
 * @return string
 */
function project_object_tags_widget($name, Project $project, $value, $attributes) {
	return text_field($name, $value, $attributes) . '<br /><span class="desc">' . lang('tags widget description') . '</span>';
} // project_object_tag_widget


/**
 * Render comma separated tags of specific object that link on project tag page
 *
 * @param ProjectDataObject $object
 * @param Project $project
 * @return string
 */
function project_object_tags2(ApplicationDataObject $object) {
	$tag_names = $object->getTagNames();
	if(!is_array($tag_names) || !count($tag_names)) return '--';

	$links = array();
	foreach($tag_names as $tag_name) {
		$links[] = '<a href="#" class="ico-tag coViewAction" onclick="Ext.getCmp(\'tag-panel\').select(\'' . clean($tag_name) . '\')">' . clean($tag_name) . '</a>';
	} // foreach
	return implode('<br/>', $links);
} // project_object_tags


/**
 * Render comma separated tags of specific object that link on project tag page
 *
 * @param ProjectDataObject $object
 * @param Project $project
 * @return string
 */
function project_object_tags(ApplicationDataObject $object) {
	$tag_names = $object->getTagNames();
	if(!is_array($tag_names) || !count($tag_names)) return '--';

	$links = array();
	foreach($tag_names as $tag_name) {
		$links[] = '<a href="#" onclick="Ext.getCmp(\'tag-panel\').select(\'' . clean($tag_name) . '\')">' . clean($tag_name) . '</a>';
	} // foreach
	return implode(', ', $links);
} // project_object_tags

/**
 * Render Latest Activity
 *
 * @param ProjectDataObject $object
 * @return null
 */
function render_object_latest_activity($object) {
	
	$latest_logs = ApplicationLogs::getObjectLogs($object, false, false, 3, 0);
	
	tpl_assign('logs', $latest_logs);
	return tpl_fetch(get_template_path('activity_log', 'latest_activity'));
	
} // render_object_latest_activity

/**
 * Show object comments block
 *
 * @param ProjectDataObject $object Show comments of this object
 * @return null
 */
function render_object_comments(ProjectDataObject $object) {
	if(!$object->isCommentable() || !$object->canReadComments(logged_user())) return '';
	tpl_assign('__comments_object', $object);
	return tpl_fetch(get_template_path('object_comments', 'comment'));
} // render_object_comments

function render_object_comments_for_print(ProjectDataObject $object) {
	if(!$object->isCommentable()) return '';
	tpl_assign('__comments_object', $object);
	return tpl_fetch(get_template_path('object_comments_for_print', 'comment'));
} // render_object_comments

/**
 * Show object custom properties block
 *
 * @param ProjectDataObject $object Show custom properties of this object
 * @return null
 */
function render_object_custom_properties($object, $type, $required, $co_type=null) {
	tpl_assign('_custom_properties_object', $object);
	tpl_assign('required', $required);
	tpl_assign('type', $type);
	tpl_assign('co_type', $co_type);
	return tpl_fetch(get_template_path('object_custom_properties', 'custom_properties'));
} // render_object_custom_properties

/**
 * Show object timeslots block
 *
 * @param ProjectDataObject $object Show timeslots of this object
 * @return null
 */
function render_object_timeslots(ProjectDataObject $object) {
	if(!$object->allowsTimeslots()) return '';
	tpl_assign('__timeslots_object', $object);
	return tpl_fetch(get_template_path('object_timeslots', 'timeslot'));
} // render_object_comments

/**
 * Render post comment form for specific project object
 *
 * @param ProjectDataObject $object
 * @param string $redirect_to
 * @return string
 */
function render_comment_form(ProjectDataObject $object) {
	$comment = new Comment();

	tpl_assign('comment_form_comment', $comment);
	tpl_assign('comment_form_object', $object);
	return tpl_fetch(get_template_path('post_comment_form', 'comment'));
} // render_post_comment_form

/**
 * Render timeslot form for specific project object
 *
 * @param ProjectDataObject $object
 * @return string
 */
function render_timeslot_form(ProjectDataObject $object) {
	$timeslot = new Timeslot();
	tpl_assign('timeslot_form_timeslot', $timeslot);
	tpl_assign('timeslot_form_object', $object);
	return tpl_fetch(get_template_path('post_timeslot_form', 'timeslot'));
} // render_timeslot_form

/**
 * Render open timeslot form for specific project object
 *
 * @param ProjectDataObject $object
 * @return string
 */
function render_open_timeslot_form(ProjectDataObject $object, Timeslot $timeslot) {
	tpl_assign('timeslot_form_timeslot', $timeslot);
	tpl_assign('timeslot_form_object', $object);
	return tpl_fetch(get_template_path('post_open_timeslot_form', 'timeslot'));
} // render_timeslot_form

/**
 * This function will render the code for objects linking section of the form. Note that
 * this need to be part of the existing form. It allows uploading of a new file to directly link to the object.
 *
 * @param string $prefix name prefix
 * @param integer $max_controls Max number of controls
 * @return string
 */
function render_linked_objects($prefix = 'linked_objects', $max_controls = 5) {
	static $ids = array();
	static $js_included = false;

	$linked_objects_id = 0;
	do {
		$linked_objects_id++;
	} while(in_array($linked_objects_id, $ids));

	$old_js_included = $js_included;
	$js_included = true;

	tpl_assign('linked_objects_js_included', $old_js_included);
	tpl_assign('linked_objects_id', $linked_objects_id);
	tpl_assign('linked_objects_prefix', $prefix);
	tpl_assign('linked_objects_max_controls', (integer) $max_controls);
	return tpl_fetch(get_template_path('linked_objects', 'object'));
} // render_linked_objects

/**
 * List all fields attached to specific object
 *
 * @param ProjectDataObject $object
 * @param boolean $can_remove Logged user can remove linked objects
 * @return string
 */
function render_object_links(ApplicationDataObject $object, $can_remove = false, $shortDisplay = false, $enableAdding=true) {
	tpl_assign('linked_objects_object', $object);
	tpl_assign('shortDisplay', $shortDisplay);
	tpl_assign('enableAdding', $enableAdding);
	tpl_assign('linked_objects', $object->getLinkedObjects());
	return tpl_fetch(get_template_path('list_linked_objects', 'object'));
} // render_object_links

/**
 * List all fields attached to specific object, and renders them in the main view
 *
 * @param ProjectDataObject $object
 * @param boolean $can_remove Logged user can remove linked objects
 * @return string
 */
function render_object_links_main(ApplicationDataObject $object, $can_remove = false, $shortDisplay = false, $enableAdding=true) {
	tpl_assign('linked_objects_object', $object);
	tpl_assign('shortDisplay', $shortDisplay);
	tpl_assign('enableAdding', $enableAdding);
	tpl_assign('linked_objects', $object->getLinkedObjects());
	return tpl_fetch(get_template_path('list_linked_objects_main', 'object'));
} // render_object_links

function render_object_link_form(ApplicationDataObject $object, $extra_objects = null) {
	require_javascript("og/ObjectPicker.js");
	$objects = $object->getLinkedObjects();
	if (is_array($extra_objects)) {
		$objects = array_merge($objects, $extra_objects);
	}
	tpl_assign('objects', $objects);
	return tpl_fetch(get_template_path('linked_objects', 'object'));
} // render_object_link_form

function render_object_subscribers(ProjectDataObject $object) {
	tpl_assign('object', $object);
	return tpl_fetch(get_template_path('list_subscribers', 'object'));
}

function render_add_subscribers(ProjectDataObject $object, $genid = null, $subscribers = null, $workspaces = null) {
	if (!isset($genid)) {
		$genid = gen_id();
	}
	$subscriberIds = array();
	if (is_array($subscribers)) {
		foreach ($subscribers as $u) {
			$subscriberIds[] = $u->getId();
		}
	} else {
		if ($object->isNew()) {
			$subscriberIds[] = logged_user()->getId();
		} else {
			foreach ($object->getSubscribers() as $u) {
				$subscriberIds[] = $u->getId();
			}
		}
	}
	if (!isset($workspaces)) {
		if ($object->isNew()) {
			$workspaces = array(active_or_personal_project());
		} else {
			$workspaces = $object->getWorkspaces();
		}
	}
	tpl_assign('type', get_class($object->manager()));
	tpl_assign('workspaces', $workspaces);
	tpl_assign('subscriberIds', $subscriberIds);
	tpl_assign('genid', $genid);
	return tpl_fetch(get_template_path('add_subscribers', 'object'));
}
/**
 * Renders a list of users to add as subscribers, used to add subscribers from the objects view.
 * @param $object
 * @param $genid
 * @param $subscribers
 * @param $workspaces
 * @return html text
 */
function render_add_subscribers_select(ProjectDataObject $object, $genid = null, $subscribers = null, $workspaces = null) {
	if (!isset($genid)) {
		$genid = gen_id();
	}
	$subscriberIds = array();
	if (is_array($subscribers)) {
		foreach ($subscribers as $u) {
			$subscriberIds[] = $u->getId();
		}
	} else {
		if ($object->isNew()) {
			$subscriberIds[] = logged_user()->getId();
		} else {
			foreach ($object->getSubscribers() as $u) {
				$subscriberIds[] = $u->getId();
			}
		}
	}
	if (!isset($workspaces)) {
		if ($object->isNew()) {
			$workspaces = array(active_or_personal_project());
		} else {
			$workspaces = $object->getWorkspaces();
		}
	}
	tpl_assign('type', get_class($object->manager()));
	tpl_assign('workspaces', $workspaces);
	tpl_assign('subscriberIds', $subscriberIds);
	tpl_assign('genid', $genid);
	return tpl_fetch(get_template_path('add_subscribers_list', 'object'));
}

/**
 * Creates a button that shows an object picker to link the object given by $object with the one selected in
 * the it.
 *
 * @param ProjectDataObject $object
 */
function render_link_to_object($object, $text=null, $reload=false){
	require_javascript("og/ObjectPicker.js");
	
	$id = $object->getId();
	$manager = get_class($object->manager());
	if ($text == null) $text = lang('link object');
	$reload_param = $reload ? '&reload=1' : ''; 
	$result = '';
	$result .= '<a href="#" class="link-ico ico-add" onclick="og.ObjectPicker.show(function (data) {' .
			'if (data) {' .
				'var objects = \'\';' .
				'for (var i=0; i < data.length; i++) {' .
					'if (objects != \'\') objects += \',\';' .
					'objects += data[i].data.manager + \':\' + data[i].data.object_id;' .
				'}' .
				' og.openLink(\'' . get_url("object", "link_object") .
						'&object_id=' . $id . '&manager=' . $manager. $reload_param . '&objects=\' + objects' . 
						($reload ? ',{callback: function(){og.redrawLinkedObjects('. $object->getId() .', \''. get_class($object->manager()) .'\')}}' : '') . ');' .
			'}' .
		'})" id="object_linker">';
	$result .= $text;
	$result .= '</a>';
	return $result;
}

function render_link_to_object_2($object, $text=null){
	require_javascript("og/ObjectPicker.js");
	
	$id = $object->getId();
	$manager = get_class($object->manager());
	if($text==null)
	$text=lang('link object');
	$result = '';
	$result .= '<a href="#" onclick="og.ObjectPicker.show(function (data){ if(data) og.openLink(\''
	. get_url('object','link_object') . '&object_id=' . $id . '&manager=' . $manager . '&rel_object_id=\'+data[0].data.object_id + \'&rel_manager=\' + data[0].data.manager);})">';
	$result .=  $text;
	$result .= '</a>';
	return $result;
}

/**
 * Creates a button that shows an object picker to link an object with an object which has not been created yet
 *
 */
function render_link_to_new_object( $text=null){
	require_javascript("og/ObjectPicker.js");
	//$id = $object->getId();
	//$manager = get_class($object->manager());
	if($text==null)
	$text=lang('link object');
	$result = '';
	$result .= '<a href="#" onclick="og.ObjectPicker.show(function (data){	if(data) {	og.addLinkedObjectRow(\'tbl_linked_objects\',data[0].data.type,data[0].data.object_id, data[0].data.name,data[0].data.manager,\''.escape_single_quotes(lang('confirm unlink object')).'\',\''.escape_single_quotes(lang('unlink')).'\'); } })">';
	$result .=  $text;
	$result .= '</a>';
	return $result;
}

/**
 * Render application logs
 *
 * This helper will render array of log entries. Options array of is array of template options and it can have this
 * fields:
 *
 * - show_project_column - When we are on project dashboard we don't actually need to display project column because
 *   all entries are related with current project. That is not the situation on dashboard so we want to have the
 *   control over this. This option is true by default
 *
 * @param array $log_entries
 * @return null
 */
function render_application_logs($log_entries, $options = null) {
	tpl_assign('application_logs_entries', $log_entries);
	tpl_assign('application_logs_show_project_column', array_var($options, 'show_project_column', true));
	return tpl_fetch(get_template_path('render_application_logs', 'application'));
} // render_application_logs

/**
 * Render text that says when action was tacken and by who
 *
 * @param ApplicationLog $application_log_entry
 * @return string
 */
function render_action_taken_on_by(ApplicationLog $application_log_entry) {
	if($application_log_entry->isToday()) {
		$result = '<span class="desc">' . lang('today') . ' ' . clean(format_time($application_log_entry->getCreatedOn()));
	} elseif($application_log_entry->isYesterday()) {
		//return '<span class="desc">' . lang('yesterday') . ' ' . clean(format_time($application_log_entry->getCreatedOn()));
		$result = '<span class="desc">' . lang('yesterday');
	} else {
		$result = '<span class="desc">' . clean(format_date($application_log_entry->getCreatedOn()));
	} // if
	$result .= '</span>';

	$taken_by = $application_log_entry->getTakenBy();
	return $taken_by instanceof User ? $result . ', <a class="internalLink" href="' . $taken_by->getCardUrl() . '">' . clean($taken_by->getDisplayName()) . '</a>' : $result;
} // render_action_taken_on


/**
 * Comma separated values from a set of options.
 *
 * @param string $name Control name
 * @param string $value Initial value
 * @param string $options
 * 		An array of arrays with the values that will be shown when autocompleting.
 * 		The first value of each array will be assumed as the value and the second as the display name.
 * @param array $attributes Other control attributes
 * @return string
 */
function autocomplete_textfield($name, $value, $options, $emptyText, $attributes, $forceSelection = true, $cmp_id = '') {
	require_javascript("og/CSVCombo.js");
	$jsArray = "";
	foreach ($options as $o) {
		if ($jsArray != "") $jsArray .= ",";
		if (count($o) < 2) {
			$jsArray .= '['.json_encode($o).','.json_encode(clean($o)).','.json_encode(clean($o)).']';
		} else {
			$jsArray .= '['.json_encode($o[0]).','.json_encode(clean($o[1])).','.json_encode(clean($o[1])).']';
		}
	}
	$jsArray = "[$jsArray]";

	$id = array_var($attributes, "id", gen_id());
	$attributes["id"] = $id;
	$attributes["autocomplete"] = "off";
	$attributes["onkeypress"] = "if (event.keyCode == 13) return false;";

	$html = '<div class="og-csvcombo-container">' . text_field($name, $value, $attributes) . '</div>
		<script>
		new og.CSVCombo({
			store: new Ext.data.SimpleStore({
        		fields: ["value", "name", "clean"],
        		data: '.$jsArray.'
			}),
			'.($cmp_id == ''?'':'id:"'.$cmp_id.'",').'
			valueField: "value",
        	displayField: "name",
        	mode: "local",
        	forceSelection: '.($forceSelection?'true':'false').',
        	triggerAction: "all",
        	tpl: "<tpl for=\".\"><div class=\"x-combo-list-item\">{clean}</div></tpl>",
        	emptyText: "",
        	applyTo: "'.$id.'"
    	});
    	</script>
	';
	return $html;
}

/**
 * Comma separated values from a set of options.
 *
 * @param string $name Control name
 * @param string $value Initial value
 * @param string $options
 * 		An array of arrays with the values that will be shown when autocompleting.
 * 		The first value of each array will be assumed as the value and the second as the display name.
 * @param array $attributes Other control attributes
 * @return string
 */
function autocomplete_emailfield($name, $value, $options, $emptyText, $attributes, $forceSelection = true) {
	require_javascript("og/CSVCombo.js");
	require_javascript("og/EmailCombo.js");
	$jsArray = "";
	foreach ($options as $o) {
		if ($jsArray != "") $jsArray .= ",";
		if (count($o) < 2) {
			$jsArray .= '['.json_encode($o).','.json_encode($o).','.json_encode(clean($o)).']';
		} else {
			$jsArray .= '['.json_encode($o[0]).','.json_encode($o[1]).','.json_encode(clean($o[1])).']';
		}
	}
	$jsArray = "[$jsArray]";

	$id = array_var($attributes, "id", gen_id());
	$attributes["id"] = $id;
	$attributes["autocomplete"] = "off";
	$attributes["onkeypress"] = "if (event.keyCode == 13) return false;";

	$html = '<div class="og-csvcombo-container">' . text_field($name, $value, $attributes) . '</div>
		<script>
		new og.EmailCombo({
			store: new Ext.data.SimpleStore({
        		fields: ["value", "name", "clean"],
        		data: '.$jsArray.'
			}),
			valueField: "value",
        	displayField: "name",
        	mode: "local",
        	forceSelection: '.($forceSelection?'true':'false').',
        	triggerAction: "all",
        	tpl: "<tpl for=\".\"><div class=\"x-combo-list-item\">{clean}</div></tpl>",
        	emptyText: "",
        	applyTo: "'.$id.'"
    	});
    	</script>
	';
	return $html;
}


/**
 * Comma separated values from a set of options.
 *
 * @param string $name Control name
 * @param string $value Initial value
 * @param string $options
 * 		An array of arrays with the values that will be shown when autocompleting.
 * 		The first value of each array will be assumed as the value and the second as the display name.
 * @param array $attributes Other control attributes
 * @return string
 */
function autocomplete_textarea_field($name, $value, $options, $max_options, $attributes) {
	require_javascript("og/AutocompleteTextarea.js");
	$jsArray = "";
	foreach ($options as $o) {
		if ($jsArray != "") $jsArray .= ",";
		$jsArray .= json_encode($o);
	}
	$jsArray = "[$jsArray]";

	$id = array_var($attributes, "id", gen_id());
	$attributes["id"] = $id;
	$render_to = gen_id().$name;
	
	$html = '<div id="'.$render_to.'"></div>
		<script>
		og.render_autocomplete_field({
			render_to: "'.$render_to.'",
			name: "'.$name.'",
			id: "'.$id.'",
			value: "'.$value.'",
			store: '.$jsArray.',
			limit: '.$max_options.'
		});
    	</script>
	';
	return $html;
}


function autocomplete_tags_field($name, $value, $id = null, $tabindex = null) {
	require_javascript("og/CSVCombo.js");
	if (!isset($id)) $id = gen_id();
	$attributes = array(
		"class" => "long",
		"id" => $id,
		"autocomplete" => "off",
		"onkeypress" => "if (event.keyCode == 13) return false;",
	);
	if ($tabindex != null) $attributes['tabindex'] = $tabindex;

	if (trim($value) != "") $value .= ", ";
	$html = '<div class="og-csvcombo-container">' . text_field($name, $value, $attributes) . '</div>
		<script>
		var tags = Ext.getCmp("tag-panel").getTags();
		var arr = [];
		for (var i=0; i < tags.length; i++) {
			arr.push([tags[i].name, og.clean(tags[i].name)]);
		}
		new og.CSVCombo({
			store: new Ext.data.SimpleStore({
        		fields: ["value", "clean"],
        		data: arr
			}),
			valueField: "value",
        	displayField: "value",
        	mode: "local",
        	forceSelection: true,
        	tpl: "<tpl for=\".\"><div class=\"x-combo-list-item\">{clean}</div></tpl>",
        	emptyText: "",
        	'. ($tabindex != null ? "tabIndex: $tabindex," : "") .'
        	applyTo: "'.$id.'"
    	});
    	</script>
	';
	return $html;
}

function render_add_reminders($object, $context, $defaults = null, $genid = null) {
	require_javascript('og/Reminders.js');
	if(!is_array($defaults)) $defaults = array();
	$default_defaults = array(
		'type' => 'reminder_popup',
		'duration' => '15',
		'duration_type' => '1',
		'for_subscribers' => true,
	); 
	foreach ($default_defaults as $k => $v) {
		if (!isset($defaults[$k])) $defaults[$k] = $v;
	}
	if (is_null($genid)) {
		$genid = gen_id();
	}
	$types = ObjectReminderTypes::findAll();
	$typecsv = "";
	foreach ($types as $type) {
		if ($typecsv != "") {
			$typecsv .= ",";
		}
		$typecsv .= '"'.$type->getName().'"';
	}
	$output = '
		<div id="'.$genid.'" class="og-add-reminders">
			<a id="'.$genid.'-link" href="#" onclick="og.addReminder(this.parentNode, \''.$context.'\', \''.$defaults['type'].'\', \''.$defaults['duration'].'\', \''.$defaults['duration_type'].'\', \''.$defaults['for_subscribers'].'\', this);return false;">' . lang("add object reminder") . '</a>
		</div>
		<script>
		og.reminderTypes = ['.$typecsv.'];
		</script>
	';
	
	if ($object->isNew()) {
		$output .= '<script>og.addReminder(document.getElementById("'.$genid.'"), \''.$context.'\', \''.$defaults['type'].'\', \''.$defaults['duration'].'\', \''.$defaults['duration_type'].'\', \''.$defaults['for_subscribers'].'\', document.getElementById("'.$genid.'-link"));</script>';
	} else {
		$reminders = ObjectReminders::getAllRemindersByObjectAndUser($object, logged_user(), $context, true);
		foreach($reminders as $reminder) {
			$mins = $reminder->getMinutesBefore();
			if ($mins % 10080 == 0) {
				$duration = $mins / 10080;
				$duration_type = "10080";
			} else if ($mins % 1440 == 0) {
				$duration = $mins / 1440;
				$duration_type = "1440";
			} else if ($mins % 60 == 0) {
				$duration = $mins / 60;
				$duration_type = "60";
			} else {
				$duration = $mins;
				$duration_type = "1";
			}
			$type = $reminder->getType();
			$forSubscribers = $reminder->getUserId() == 0 ? "true" : "false";
			$output .= '<script>og.addReminder(document.getElementById("'.$genid.'"), "'.$context.'", "'.$type.'", "'.$duration.'", "'.$duration_type.'", '.$forSubscribers.', document.getElementById(\''.$genid.'-link\'));</script>';
		} // for
	}
	return $output;
}

/**
 * Renders a form to set an object's custom properties.
 *
 * @param ProjectDataObject $object
 * @return string
 */
function render_add_custom_properties(ProjectDataObject $object) {
	$genid = gen_id();
	$output = '
		<div id="'.$genid.'" class="og-add-custom-properties">
			<table><tbody><tr>
			<th>' . lang('name') . '</th>
			<th>' . lang('value') . '</th>
			<th class="actions"></th>
			</tr></tbody></table>
			<a href="#" onclick="og.addObjectCustomProperty(this.parentNode, \'\', \'\', true);return false;">' . lang("add custom property") . '</a>
		</div>
		<script>
		var ti = 30000;
		og.addObjectCustomProperty = function(parent, name, value, focus) {
			var count = parent.getElementsByTagName("tr").length - 1;
			var tbody = parent.getElementsByTagName("tbody")[0];
			var tr = document.createElement("tr");
			var td = document.createElement("td");
			td.innerHTML = \'<input class="name" type="text" name="custom_prop_names[\' + count + \']" value="\' + name + \'" tabindex=\' + ti + \'>\';;
			if (td.children) var input = td.children[0];
			tr.appendChild(td);
			var td = document.createElement("td");
			td.innerHTML = \'<input class="value" type="text" name="custom_prop_values[\' + count + \']" value="\' + value + \'" tabindex=\' + (ti + 1) + \'>\';;
			tr.appendChild(td);
			var td = document.createElement("td");
			td.innerHTML = \'<div class="ico ico-delete" style="width:16px;height:16px;cursor:pointer" onclick="og.removeCustomProperty(this.parentNode.parentNode);return false;">&nbsp;</div>\';
			tr.appendChild(td);
			tbody.appendChild(tr);
			if (input && focus)
				input.focus();
			ti += 2;
		}
		og.removeCustomProperty = function(tr) {
			var parent = tr.parentNode;
			parent.removeChild(tr);
			// reorder property names
			var row = parent.firstChild;
			var num = -1; // first row has no inputs
			while (row != null) {
				if (row.tagName == "TR") {
					var inputs = row.getElementsByTagName("INPUT");
					for (var i=0; i < inputs.length; i++) {
						var input = inputs[i];
						if (input.className == "name") {
							input.name = "custom_prop_names[" + num + "]";
						} else {
							input.name = "custom_prop_values[" + num + "]";
						}
					}
					num++;
				}
				row = row.nextSibling;
			}
		}
		</script>
	';
	$properties = ObjectProperties::getAllPropertiesByObject($object);
	if (is_array($properties)) {
		foreach($properties as $property) {
			$output .= '<script>og.addObjectCustomProperty(document.getElementById("'.$genid.'"), "'.clean($property->getPropertyName()).'", "'.clean($property->getPropertyValue()).'");</script>';
		} // for
	} // if
	$output .= '<script>og.addObjectCustomProperty(document.getElementById("'.$genid.'"), "", "");</script>';
	return $output;
}

/**
 * Renders an object's custom properties
 * @return string
 */
function render_custom_properties(ApplicationDataObject $object) {
	//if(!$object->isCommentable()) return '';
	tpl_assign('__properties_object', $object);
	return tpl_fetch(get_template_path('view', 'custom_properties'));
}

/**
 * Returns a control to select mail account
 *
 * @param string $name
 * 		Name for the control
 * @param array $mail_accounts
 * 		Array of accounts to choose from
 * @param array $selected
 * 		Index of account selected by default
 * @return string
 * 		HTML for the control
 */
function render_select_mail_account($name, $mail_accounts, $selected = null, $attributes = null) {
	$options = null;
	if(is_array($mail_accounts)) {
		foreach($mail_accounts as $mail_account) {
			$option_attributes = $mail_account->getId() == $selected ? array('selected' => 'selected') : null;
			$mail = $mail_account->getName() . " [" . $mail_account->getEmail() . "]";
			$options[] = option_tag($mail, $mail_account->getId(), $option_attributes);
		} // foreach
	} // if
	return select_box($name, $options, $attributes);
} //  render_select_mail_account


/**
 * Render select task priority box
 *
 * @param integer $selected Selected priority
 * @param array $attributes Additional attributes
 * @return string
 */
function select_task_priority($name, $selected = null, $attributes = null) {
	$options = array(
		option_tag(lang('urgent priority'), ProjectTasks::PRIORITY_URGENT, ($selected >= ProjectTasks::PRIORITY_URGENT)?array('selected' => 'selected'):null),
		option_tag(lang('high priority'), ProjectTasks::PRIORITY_HIGH, ($selected >= ProjectTasks::PRIORITY_HIGH && $selected < ProjectTasks::PRIORITY_URGENT)?array('selected' => 'selected'):null),
		option_tag(lang('normal priority'), ProjectTasks::PRIORITY_NORMAL, ($selected > ProjectTasks::PRIORITY_LOW && $selected < ProjectTasks::PRIORITY_HIGH)?array('selected' => 'selected'):null),
		option_tag(lang('low priority'), ProjectTasks::PRIORITY_LOW, ($selected <= ProjectTasks::PRIORITY_LOW)?array('selected' => 'selected'):null),
	);
	return select_box($name, $options, $attributes);
} // select_task_priority

function select_object_type($name, $types, $selected = null, $attributes = null) {
	$options = array();
	foreach ($types as $type) {
		$options[] = option_tag($type->getName(), $type->getId(), ($selected == $type->getId())?array('selected' => 'selected'):null);
	}
	return select_box($name, $options, $attributes);
} // select_task_priority


/**
 * Render assign to SELECT
 * @param string $list_name Name of the select control
 * @param Project $project Selected project, if NULL active project will be used
 * @param integer $selected ID of selected user
 * @param array $attributes Array of select box attributes, if needed
 * @return null
 */ 
function filter_assigned_to_select_box($list_name, $project = null, $selected = null, $attributes = null) {
	$logged_user = logged_user();
	if ($project) {		
		$project_ids = $project->getAllSubWorkspacesQuery(true,logged_user());
	} else {
		$project_ids = logged_user()->getWorkspacesQuery(true);
	}
	$grouped_users = Users::getGroupedByCompanyFromProjectIds($project_ids);

	$options = array(option_tag(lang('anyone'), '0:0'),option_tag(lang('unassigned'), '-1:-1', '-1:-1' == $selected ? array('selected' => 'selected') : null));
	
	if(is_array($grouped_users) && count($grouped_users)) {
		foreach($grouped_users as $company_id => $users) {
			$company = Companies::findById($company_id);
			if(!($company instanceof Company)) {
				continue;
			} // if

			$options[] = option_tag('--', '0:0'); // separator

			$option_attributes = $company->getId() . ':0' == $selected ? array('selected' => 'selected') : null;
			$options[] = option_tag($company->getName(), $company_id . ':0', $option_attributes);

			if(is_array($users)) {
				foreach($users as $user) {
					$option_attributes = $company_id . ':' . $user->getId() == $selected ? array('selected' => 'selected') : null;
					$options[] = option_tag($user->getDisplayName() . ' : ' . $company->getName() , $company_id . ':' . $user->getId(), $option_attributes);
				} // foreach
			} // if

		} // foreach
	} // if

	return select_box($list_name, $options, $attributes);
} // assign_to_select_box

function render_initial_workspace_chooser($name, $value) {
	return select_project2($name, "'$value'", gen_id(), true, array(array('id'=>'remember', 'name'=>lang('remember last'), 'color'=>'remember', 'parent'=>'root')));
}

/**
 * Renders context help in a view, only if description_key is a valid lang.
 * If helpTemplate is null, default template is used
 *
 * @param $view View where the context help will be placed
 * @param string $description_key Key of the description to show, if not exists help will not be shown.
 * @param string $option_name
 * @param string $helpTemplate
 */
function render_context_help($view, $description_key, $option_name = null, $helpTemplate = null) {
	if ($view != null && $description_key != null && Localization::instance()->lang_exists($description_key)) {
		
		if ($option_name != null) { 
			tpl_assign('option_name' , $option_name);
		}
		
		if ($helpTemplate == null) {
			tpl_assign('helpDescription', lang($description_key));
		} else {
			tpl_assign('helpTemplate', $helpTemplate);
		}
		
		$view->includeTemplate(get_template_path('context_help', 'help'));
	}
}

?>