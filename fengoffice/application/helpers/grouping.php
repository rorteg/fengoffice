<?php


	function getGroup($groups, $member_id) {
		if (!$member_id) return null;

		if (array_key_exists($member_id, $groups)) {
			return $groups[$member_id];
		}
		$res = null;
		foreach ($groups as $g) {
			$res = getGroup($g['subgroups'], $member_id);
			if (!is_null($res)) break;
		}
		return $res;
	}
	
	function setGroup(&$groups, $member_id, $group) {
		if (array_key_exists($member_id, $groups)) {
			$groups[$member_id] = $group;
			return true;
		}
		$res = false;
		foreach ($groups as &$g) {
			$res = setGroup($g['subgroups'], $member_id, $group);
			if ($res) break;
		}
		return $res;
	}
	
	
	function makeDimensionGroups($objects, $dimension_id, &$parent_group = null) {
		// key = member_id - values = subset of objects or subgroups
		$groups = array();
		$grouped_objects = array();
		
		$max_level = 0;
		
		foreach ($objects as $object) {
			$object_id = $object instanceof Timeslot && $object->getColumnValue('rel_object_id') > 0 ? $object->getRelObjectId() : $object->getId();
			$members = ObjectMembers::getMembersByObjectAndDimension($object_id, $dimension_id, "AND om.is_optimization = 0");
			if (is_array($members) && count($members) > 0) {
				$member = $members[0];
				$all_parents = array_reverse($member->getAllParentMembersInHierarchy(true));
				$all_p_keys = "";
				
				foreach ($all_parents as $p_member) {
					$all_p_keys .= ($all_p_keys == "" ? "" : "_") . $p_member->getId();
					
					$new_group = array('group' => array('id' => $p_member->getId(), 'name' => $p_member->getName(), 'pid' => $p_member->getParentMemberId(), 'type' => $p_member->getObjectTypeId(), 'obj' => $p_member->getObjectId()), 'subgroups' => array());
					
					$level = $p_member->getDepth();
					$max_level = $level > $max_level ? $level : $max_level;
					
					if (isset($groups[$level]) && isset($groups[$level][$p_member->getId()])) {
						$new_group = $groups[$level][$p_member->getId()];
					}
					
					if (!isset($groups[$level])) {
						$groups[$level] = array($p_member->getId() => $new_group);
					} else if (!isset($groups[$level][$p_member->getId()])) {
						$groups[$level][$p_member->getId()] = $new_group;
					}
					
					if ($p_member->getId() == $member->getId()) {
						
						if (!isset($grouped_objects[$all_p_keys])) $grouped_objects[$all_p_keys] = array($object);
						else $grouped_objects[$all_p_keys][] = $object;
						
					}
				}
			}
		}
		
		$i = $max_level;
		while ($i > 1) {
			foreach ($groups[$i] as $member_id => $gp) {
				$member = $gp['group'];
				$pid = $member['pid'];
				
				if (isset($groups[$i-1][$pid])) $groups[$i-1][$pid]['subgroups'][$member_id] = $gp;
			}
			$i--;
		}
		
		foreach ($groups as $level => $value) {
			if ($level > 1) unset($groups[$level]);
		}
		
		if ($parent_group != null && isset($groups[1])) {
			foreach ($groups[1] as $mid => $group) {
				$parent_group['subgroups'][$mid] = $group;
			}
		}

		return array('groups' => isset($groups[1]) ? $groups[1] : array(), 'grouped_objects' => $grouped_objects);
	}
	
	
	function groupObjects($group_by, $objects) {
		if (count($group_by) == 0) {
			$grouped = array(
				'groups' => array(array('group' => array('id' => 0, 'name' => '', 'pid' => 0), 'subgroups' => array())),
				'grouped_objects' => array(0 => $objects),
			);
		} else {
			// first grouping
			$grouped = makeGroups($objects, $group_by[0]);
			
			// more groupings
			for ($gb_index = 1; $gb_index < count($group_by); $gb_index++) {
			
				$to_remove = array();
				foreach ($grouped['grouped_objects'] as $key => $gobjects) {
					
					$member_id = strrpos($key, "_") === FALSE ? $key : substr($key, strrpos($key, "_")+1);
					
					$parent_group = getGroup($grouped['groups'], $member_id);
					
					$grouped_tmp = makeGroups($gobjects, $group_by[$gb_index], $parent_group);
					
					if ($parent_group)
						setGroup($grouped['groups'], $member_id, $parent_group);
					
					if (count($grouped_tmp['grouped_objects']) > 0) {
						foreach ($grouped_tmp['grouped_objects'] as $m => $objs) {
							foreach ($objs as $obj) {
								$grouped['grouped_objects'][$key . "_" . $m][] = $obj;
							}
						}
						$to_remove[] = $key;
					}
				}
				foreach ($to_remove as $k) unset($grouped['grouped_objects'][$k]);

			}
		}
		
		return $grouped;
	}
	
	
	function makeGroups($objects, $gb_criteria, &$parent_group = null) {
		if (array_var($gb_criteria, 'type') == 'dimension') {
			$grouped = makeDimensionGroups($objects, array_var($gb_criteria, 'value'), $parent_group);
		} else if (array_var($gb_criteria, 'type') == 'column') {
			$grouped = groupObjectsByColumnValue($objects, array_var($gb_criteria, 'value'), $parent_group);
		} else if (array_var($gb_criteria, 'type') == 'assoc_obj') {
			$grouped = groupObjectsByAssocObjColumnValue($objects, array_var($gb_criteria, 'value'), array_var($gb_criteria, 'fk'), $parent_group);
		}
		return $grouped;
	}
	
	
	function order_groups_by_name($groups) {
		$tmp = array();
		foreach ($groups as $group_obj) {
			// id is concatenated to avoid losing information when two groups have the same name
			$tmp[strtoupper($group_obj['group']['name'] . "_" . $group_obj['group']['id'])] = $group_obj;
		}
		ksort($tmp, SORT_STRING);
		$ordered = array();
		foreach ($tmp as $group_obj) {
			$ordered[$group_obj['group']['id']] = $group_obj;
		}
		return $ordered;
	}
	
	
	function groupObjectsByColumnValue($objects, $column, &$parent_group = null) {
		
		$groups = array();
		$grouped_objects = array();
		
		foreach ($objects as $obj) {
			$gb_val = $obj->getColumnValue($column);
			$group = null;
			foreach ($groups as $g) {
				if (array_var($g, 'id') == $gb_val) $group = $g;
			}
			if (is_null($group)) {
				/* @var $obj ContentDataObject */
				if (in_array($column, $obj->manager()->getExternalColumns())) {
					$name = Objects::findObject($obj->getColumnValue($column))->getObjectName();
				} else {
					$name = lang($gb_val);
				}
				$group = array('group' => array('id' => $gb_val, 'name' => $name, 'pid' => 0), 'subgroups' => array());
				$groups[$gb_val] = $group;
			}
			
			if (!isset($grouped_objects[$gb_val])) $grouped_objects[$gb_val] = array();
			$grouped_objects[$gb_val][] = $obj;
		}
		
		if ($parent_group != null) {
			foreach ($groups as $mid => $group) {
				$parent_group['subgroups'][$mid] = $group;
			}
		}
		
		return array('groups' => $groups, 'grouped_objects' => $grouped_objects);
	}
	
	function groupObjectsByAssocObjColumnValue($objects, $column, $fk, &$parent_group = null) {
		
		$groups = array();
		$grouped_objects = array();
		$i=1;
		foreach ($objects as $obj) {
			$group = null;
			$rel_obj = Objects::findObject($obj->getColumnValue($fk));
			if (!$rel_obj instanceof ContentDataObject) {
				$gb_val = 'unclassified';
			} else {
				$gb_val = $rel_obj->getColumnValue($column);
				if ($gb_val == 0) $gb_val = 'unclassified';
			}
			foreach ($groups as $g) {
				if (array_var($g, 'id') == $gb_val) $group = $g;
			}
			if (is_null($group)) {
				if ($gb_val != 'unclassified' && in_array($column, $rel_obj->manager()->getExternalColumns())) {
					$name = Objects::findObject($rel_obj->getColumnValue($column))->getObjectName();
				} else {
					$name = lang("$column $gb_val");
				}
				
				$group = array('group' => array('id' => $gb_val, 'name' => $name, 'pid' => 0), 'subgroups' => array());
				$groups[$gb_val] = $group;
			}
			
			if (!isset($grouped_objects[$gb_val])) $grouped_objects[$gb_val] = array();
			$grouped_objects[$gb_val][] = $obj;
		}
		
		if ($parent_group != null) {
			foreach ($groups as $mid => $group) {
				$parent_group['subgroups'][$mid] = $group;
			}
		}
		
		return array('groups' => $groups, 'grouped_objects' => $grouped_objects);
	}
	