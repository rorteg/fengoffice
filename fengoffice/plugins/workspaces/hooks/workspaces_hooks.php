<?php
Hook::register ( "workspaces" );

function workspaces_total_task_timeslots_group_by_criterias($args, &$ret) {
	$wdimension = Dimensions::findByCode ( 'workspaces' );
	$tdimension = Dimensions::findByCode ( 'tags' );
	$ret[] = array('val' => 'dim_'.$wdimension->getId(), 'name' => $wdimension->getName());
	$ret[] = array('val' => 'dim_'.$tdimension->getId(), 'name' => $tdimension->getName());
}


function workspaces_custom_reports_additional_columns($args, &$ret) {
	$dimensions = Dimensions::findAll ( array("conditions" => "code IN ('workspaces','tags')") );
	foreach ($dimensions as $dimension) {
		$doptions = $dimension->getOptions(true);
		
		if( $doptions && isset($doptions->useLangs) && $doptions->useLangs ) {
			$name = lang($dimension->getCode());
		} else {
			$name = $dimension->getName();
		}
		
		$ret[] =  array('id' => 'dim_'.$dimension->getId(), 'name' => $name, 'type' => DATA_TYPE_STRING);
	}
}


function workspaces_include_tasks_template($ignored, &$more_content_templates) {
	$more_content_templates[] = array(
		'template' => 'groupby',
		'controller' => 'task',
		'plugin' => 'workspaces'
	);
}

function workspaces_override_object_color($object, &$color) {
	
	$ws_ot = ObjectTypes::findByName('workspace');
	if (!$ws_ot instanceof ObjectType) return;
	
	$members = $object->getMembers();
	foreach ($members as $member) {
		/* @var $member Member */
		if ($member->getObjectTypeId() == $ws_ot->getId()) {
			$ws = Workspaces::getWorkspaceById($member->getObjectId());
			if ($ws instanceof Workspace) {
				$color = $ws->getColumnValue('color');
				return;
			}
		}
	}
}


function workspaces_override_member_color($member, &$color) {
	
	$ws_ot = ObjectTypes::findByName('workspace');
	if (!$ws_ot instanceof ObjectType) return;
	
	if ($member->getObjectTypeId() == $ws_ot->getId()) {
		$ws = Workspaces::getWorkspaceById($member->getObjectId());
		if ($ws instanceof Workspace) {
			$color = $ws->getColumnValue('color');
		}
	}
}