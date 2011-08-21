<?php

	foreach ($workspaces as $ws){
		echo select_milestone('task[milestone_id]', $ws, array_var($task_data, 'milestone_id'), array('id' => $genid . 'taskListFormMilestone', 'tabindex' => '40'));	
	}	
	
?>
