<?php
$not_overdue_limit = 5 ;
$overdue_limit = 20 ;

$show_more = false ;

// Not due tasks
$not_due_tasks = ProjectTasks::getUpcomingWithoutDate($not_overdue_limit+1);
if ( count($not_due_tasks) > $not_overdue_limit ) {
	$show_more = true ;
	array_pop($not_due_tasks);
}

// Due Tasks
$overdue_upcoming_objects = ProjectTasks::getOverdueAndUpcomingObjects ($overdue_limit+1); // FIXME: performance Killer
if ( count($overdue_upcoming_objects) > $overdue_limit ) {
	$show_more = true ;
	array_pop($overdue_upcoming_objects);
}

$overdue_upcoming_objects = array_merge($not_due_tasks, $overdue_upcoming_objects);
$users = array() ;

// Render only when the context isnt 'all' and you have perms 
$render_add = ( active_context_members(false) && ProjectTask::canAdd(logged_user(), active_context()) ) ;

if ($render_add) {
	$users[] = array(0, lang('dont assign'));	
	foreach ( allowed_users_to_assign() as $company ){
		foreach ($company['users'] as $user ) {
			$name  = logged_user()->getId() == $user['id'] ? lang('me') : $user['name'] ;
			$users[] = array($user['id'], $name);	
		}
	}
}

include_once 'template.php';