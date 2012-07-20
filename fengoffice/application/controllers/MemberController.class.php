<?php

/**
 * Member controller
 *
 * @version 1.0
 * @author Alvaro Torterola <alvarotm01@gmail.com>
 */
class MemberController extends ApplicationController {
        
        var $dimension = 3 ;
        
	/**
	 * Prepare this controller
	 *
	 * @param void
	 * @return MemberController
	 */
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website');
	} 
	
        
        function init() {
		require_javascript("og/MemberManager.js");
		ajx_current("panel", "members", null, null, true);
		ajx_replace(true);
	}
        
        function list_all() {
		
		ajx_current("empty");
		// Get all variables from request
		$start = array_var($_GET,'start', 0);
		$limit = array_var($_GET,'limit', config_option('files_per_page'));
		$order = 'name';
		$order_dir = array_var($_GET,'dir');
		$action = array_var($_GET,'action');
		$attributes = array("ids" => explode(',', array_var($_GET,'ids')));
		
		if (!$order_dir){
			switch ($order){
				case 'name': $order_dir = 'ASC'; break;
				default: $order_dir = 'DESC';
			}
		}		

		$dim_controller = new DimensionController();
		$members = $dim_controller->initial_list_dimension_members(Dimensions::findByCode('workspaces')->getId(), ObjectTypes::findByName('workspace')->getId(), $context, true);
		$ids = array();
		foreach ($members as $m){
			$ids[]=$m['object_id'];
		}		
                $members = active_context_members(false); // Context Members Ids
                $members_sql = "";
                if(count($members) > 0){
                    $members_sql .= " AND parent_member_id IN (" . implode ( ',', $members ) . ")";
                }else{
                    $members_sql .= " AND parent_member_id = 0";
                }
		$res = Members::findAll(array("conditions" => "object_id IN (".implode(',', $ids).") ". $members_sql,'offset' => $start, 'limit' => $limit, 'order' => "$order $order_dir"));
		
		$object = $this->prepareObject($res, $start, $limit, count($res));
                
		ajx_extra_data($object);
		tpl_assign("listing", $object);
	}
	
	private function prepareObject($totMsg, $start, $limit, $total) {
		$object = array(
			"totalCount" => $total,
			"start" => $start,
			"dimension_id" => $this->dimension,
			"members" => array()
		);
		for ($i = 0; $i < $limit; $i++){
			if (isset($totMsg[$i])){
				$member = $totMsg[$i];
				if ($member instanceof Member){
					$object["members"][] = array(
                                                            'object_id' => $member->getObjectId(),
                                                            'name' => $member->getName(),
                                                            'depth' => $member->getDepth(),
                                                            'parent_member_id' => $member->getParentMemberId(),
                                                            'dimension_id' => $member->getDimensionId(),
                                                            'id' => $member->getId(),
                                                            'ico_color' => $member->getMemberColor());
                                }
			}
		}
		
		return $object;
	}

	
	/**
	 * Adds a member to a dimension
	 */
	function add() {

		
		if (!can_manage_dimension_members(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		
		$member_data = array_var($_POST, 'member');
		$member = new Member();
		
		if (!is_array($member_data)) {
			
			$member_data = array();
			if ($name = array_var($_GET,'name') ) {
				$member_data['name'] = $name;
			}
			if ($parent = array_var($_GET,'parent')) {
				tpl_assign('parent_sel', $parent); 
			}
			tpl_assign('member_data', $member_data);
			
			// New ! Permissions
			$permission_parameters = permission_member_form_parameters();
			
			$logged_user_pg = array();
			foreach ($permission_parameters['allowed_object_types'] as $ot){
				$logged_user_pg[] = array(
					'o' => $ot->getId(),
					'w' => 1,
					'd' => can_manage_dimension_members(logged_user()) ? 1 : 0,
					'r' => 1
				);
			}
			$permission_parameters['member_permissions'][logged_user()->getPermissionGroupId()] = $logged_user_pg;
			tpl_assign('permission_parameters', $permission_parameters);
			//--
			
			tpl_assign("member", $member);
			
			$sel_dim = get_id("dim_id");
			$current_dimension = Dimensions::getDimensionById($sel_dim);
			if (!$current_dimension instanceof Dimension) {
				flash_error("dimension dnx");
				ajx_current("empty");
				return;
			}
			tpl_assign("current_dimension", $current_dimension);
			
			$ot_ids = implode(",", DimensionObjectTypes::getObjectTypeIdsByDimension($current_dimension->getId()));
			$dimension_obj_types = ObjectTypes::findAll(array("conditions" => "`id` IN ($ot_ids)"));
			$dimension_obj_types_info = array();
			foreach ($dimension_obj_types as $ot) {
				$info = $ot->getArrayInfo(array('id', 'name', 'type'));
				$info['name'] = lang(array_var($info, 'name'));
				$dimension_obj_types_info[] = $info;
			}
			tpl_assign('dimension_obj_types', $dimension_obj_types_info);
			if (count($dimension_obj_types_info) == 1)
				tpl_assign('obj_type_sel', $dimension_obj_types_info[0]['id']);
			
			tpl_assign('parents', array());
			tpl_assign('can_change_type', true);
			
			
			$restricted_dim_defs = DimensionMemberRestrictionDefinitions::findAll(array("conditions" => array("`dimension_id` = ?", $sel_dim)));
			$ot_with_restrictions = array();
			foreach($restricted_dim_defs as $rdef) {
				if (!isset($ot_with_restrictions[$rdef->getObjectTypeId()])) $ot_with_restrictions[$rdef->getObjectTypeId()] = true;
			}
			tpl_assign('ot_with_restrictions', $ot_with_restrictions);
			
			$associations = DimensionMemberAssociations::findAll(array("conditions" => array("`dimension_id` = ?", $sel_dim)));
			$ot_with_associations = array();
			foreach($associations as $assoc) {
				if (!isset($ot_with_associations[$assoc->getObjectTypeId()])) $ot_with_associations[$assoc->getObjectTypeId()] = true;
			}
			tpl_assign('ot_with_associations', $ot_with_associations);
			
			if (array_var($_GET, 'rest_genid') != "") tpl_assign('rest_genid', array_var($_GET, 'rest_genid'));
			if (array_var($_GET, 'prop_genid') != "") tpl_assign('prop_genid', array_var($_GET, 'prop_genid'));
			
		} else {
							
			$ok = $this->saveMember($member_data, $member);
			save_member_permissions($member);
			
			if ($ok) {
				ajx_extra_data( array(
					"member"=>array(
						"id" => $member->getId(),
						"dimension_id" => $member->getDimensionId()
					)
				));
				$ret = null;
				Hook::fire('after_add_member', $member, $ret);
				evt_add("reload dimension tree", $member->getDimensionId());
			//	evt_add('select dimension member', array('dim_id' => $member->getDimensionId(), 'node' => $member->getId()));
				if (array_var($_POST, 'rest_genid')) evt_add('reload member restrictions', array_var($_POST, 'rest_genid'));
				if (array_var($_POST, 'prop_genid')) evt_add('reload member properties', array_var($_POST, 'prop_genid'));
				if (array_var($_GET, 'current') == 'overview-panel' && array_var($_GET, 'quick') ) {
					ajx_current("reload");
				}
			}
		}

	}
	
	function edit() {
		if (!can_manage_dimension_members(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		
		$member = Members::findById(get_id());
		if (!$member instanceof Member) {
			flash_error(lang('member dnx'));
			ajx_current("empty");
			return;
		}
		
		
		
		$this->setTemplate('add');
		$member_data = array_var($_POST, 'member');
		
		if (!is_array($member_data)) {
			
			// New ! Permissions
			$permission_parameters = permission_member_form_parameters($member);
			tpl_assign('permission_parameters', $permission_parameters);
			//--
			
			tpl_assign("member", $member);
			$member_data['name'] = $member->getName();
			
			$current_dimension = $member->getDimension();
			if (!$current_dimension instanceof Dimension) {
				flash_error("dimension dnx");
				ajx_current("empty");
				return;
			}
			tpl_assign("current_dimension", $current_dimension);
			
			$ot_ids = implode(",", DimensionObjectTypes::getObjectTypeIdsByDimension($current_dimension->getId()));
			$dimension_obj_types = ObjectTypes::findAll(array("conditions" => "`id` IN ($ot_ids)"));
			$dimension_obj_types_info = array();
			foreach ($dimension_obj_types as $ot) {
				$info = $ot->getArrayInfo(array('id', 'name', 'type'));
				$info['name'] = lang(array_var($info, 'name'));
				$dimension_obj_types_info[] = $info;
			}
			tpl_assign('dimension_obj_types', $dimension_obj_types_info);
			tpl_assign('obj_type_sel', $member->getObjectTypeId());
			
			tpl_assign('parents', self::getAssignableParents($member->getDimensionId(), $member->getObjectTypeId()));
			tpl_assign('parent_sel', $member->getParentMemberId());
			
			tpl_assign("member_data", $member_data);
			
			tpl_assign('can_change_type', false);
			
			$restricted_dim_defs = DimensionMemberRestrictionDefinitions::findAll(array("conditions" => array("`dimension_id` = ?", $member->getDimensionId())));
			$ot_with_restrictions = array();
			foreach($restricted_dim_defs as $rdef) {
				if (!isset($ot_with_restrictions[$rdef->getObjectTypeId()])) $ot_with_restrictions[$rdef->getObjectTypeId()] = true;
			}
			tpl_assign('ot_with_restrictions', $ot_with_restrictions);
			
			$associations = DimensionMemberAssociations::findAll(array("conditions" => array("`dimension_id` = ?", $member->getDimensionId())));
			$ot_with_associations = array();
			foreach($associations as $assoc) {
				if (!isset($ot_with_associations[$assoc->getObjectTypeId()])) $ot_with_associations[$assoc->getObjectTypeId()] = true;
			}
			tpl_assign('ot_with_associations', $ot_with_associations);
			
		} else {
			$ok = $this->saveMember($member_data, $member, false);
			save_member_permissions($member);// NEW member permissions
			if ($ok) {
				$ret = null;
				Hook::fire('after_edit_member', $member, $ret);
				evt_add("reload dimension tree", $member->getDimensionId());
//				ApplicationLogs::createLog($member, ApplicationLogs::ACTION_EDIT, false, true);
			}
		}
	}
	
	function saveMember($member_data, Member $member, $is_new = true) {
		try {
			DB::beginWork();
			
			if (!$is_new) {
				$old_parent = $member->getParentMemberId();
			}
						
			$member->setFromAttributes($member_data);
			
			/* @var $member Member */
			$object_type = ObjectTypes::findById($member->getObjectTypeId());
			
			if (!$object_type instanceof ObjectType) {
				throw new Exception(lang("you must select a valid object type"));
			}
			
			if ($member->getParentMemberId() == 0) {
				$dot = DimensionObjectTypes::findById(array('dimension_id' => $member->getDimensionId(), 'object_type_id' => $member->getObjectTypeId()));
				if (!$dot->getIsRoot()) {
					throw new Exception(lang("member cannot be root", lang($object_type->getName())));
				}
				$member->setDepth(1);
			}
			else {
				$allowedParents = $this->getAssignableParents($member->getDimensionId(), $member->getObjectTypeId());
				if (!$is_new) $childrenIds = $member->getAllChildrenIds(true);
				$hasValidParent = false ;
				if ($member->getId() == $member->getParentMemberId() ||  (!$is_new && in_array($member->getParentMemberId(), $childrenIds))) {
					throw new Exception(lang("invalid parent member"));
				}
				foreach ($allowedParents as $parent) {
					if ( $parent['id'] == $member->getParentMemberId() ){
						$hasValidParent = true;	
						break ;
					}
				}
				if (!$hasValidParent){
					throw new Exception(lang("invalid parent member"));
				}
				$parent = Members::findById($member->getParentMemberId());
				if ($parent instanceof Member) $member->setDepth($parent->getDepth() + 1);
				else $member->setDepth(1);
			}
			
			if ($object_type->getType() == 'dimension_object') {
				$handler_class = $object_type->getHandlerClass();
				if ($is_new || $member->getObjectId() == 0) {
					eval('$dimension_object = '.$handler_class.'::instance()->newDimensionObject();');
				} else {
					$dimension_object = Objects::findObject($member->getObjectId());
				}
				if ($dimension_object) {
					$dimension_object->modifyMemberValidations($member);
					$dimension_obj_data = array_var($_POST, 'dim_obj');
					if (!array_var($dimension_obj_data, 'name')) $dimension_obj_data['name'] = $member->getName();
					
					eval('$fields = '.$handler_class.'::getPublicColumns();');
					foreach ($fields as $field) {
						if (array_var($field, 'type') == DATA_TYPE_DATETIME) {
							$dimension_obj_data[$field['col']] = getDateValue($dimension_obj_data[$field['col']]);
						}
					}
					$member->save();
					$dimension_object->setFromAttributes($dimension_obj_data, $member);
					$dimension_object->save();
					$member->setObjectId($dimension_object->getId());
					$member->save();
					Hook::fire("after_add_dimension_object_member", $member, $null);
				}
			} else {
				$member->save();
				
			}
			
			
			
			
			// Other dimensions member restrictions
			$restricted_members = array_var($_POST, 'restricted_members');
			if (is_array($restricted_members)) {
				MemberRestrictions::clearRestrictions($member->getId());
				foreach ($restricted_members as $dim_id => $dim_members) {
					foreach ($dim_members as $mem_id => $member_restrictions) {
						
						$restricted = isset($member_restrictions['restricted']);
						if ($restricted) {
							$order_num = array_var($member_restrictions, 'order_num', 0);
							
							$member_restriction = new MemberRestriction();
							$member_restriction->setMemberId($member->getId());
							$member_restriction->setRestrictedMemberId($mem_id);
							$member_restriction->setOrder($order_num);
							$member_restriction->save();
						}
					}
				}
			}
			
			// Save member property members (also check for required associations)
			if (array_var($_POST, 'save_properties')) {
				$required_association_ids = DimensionMemberAssociations::getRequiredAssociatations($member->getDimensionId(), $member->getObjectTypeId(), true);
				$missing_req_association_ids = array_fill_keys($required_association_ids, true);
				
				// if keeps record change is_active, if not delete record
				$old_properties = MemberPropertyMembers::getAssociatedPropertiesForMember($member->getId());
				foreach ($old_properties as $property){
					$association = DimensionMemberAssociations::findById($property->getAssociationId());
					if (!$association->getKeepsRecord()){
						$property->delete();
					}
				}
				

				$new_properties = array();
				$associated_members = array_var($_POST, 'associated_members', array());
				
				foreach($associated_members as $prop_member_id => $assoc_id) {
					$active_association = null;
					
					if (isset($missing_req_association_ids[$assoc_id])) $missing_req_association_ids[$assoc_id] = false;
					
					$conditions = "`association_id` = $assoc_id AND `member_id` = ".$member->getId()." AND `is_active` = 1";
					
					$active_associations = MemberPropertyMembers::find(array('conditions'=>$conditions));
					if (count($active_associations)>0) $active_association = $active_associations[0];
					
					$association = DimensionMemberAssociations::findById($assoc_id);
					if ($active_association instanceof MemberPropertyMember){
						if ($active_association->getPropertyMemberId() != $prop_member_id){
							if ($association->getKeepsRecord()){
								$active_association->setIsActive(false);
								$active_association->save();
							}
							// save current association
							$mpm = new MemberPropertyMember();
							$mpm->setAssociationId($assoc_id);
							$mpm->setMemberId($member->getId());
							$mpm->setPropertyMemberId($prop_member_id);
							$mpm->setIsActive(true);
							$mpm->save();
							$new_properties[] =  $mpm;
						}
					}
					else{
						// save current association
						$mpm = new MemberPropertyMember();
						$mpm->setAssociationId($assoc_id);
						$mpm->setMemberId($member->getId());
						$mpm->setPropertyMemberId($prop_member_id);
						$mpm->setIsActive(true);
						$mpm->save();
						$new_properties[] =  $mpm;
					}
				}
				
				$missing_names = array();
				$missing_count = 0;
				foreach ($missing_req_association_ids as $assoc => $missing) {
					$assoc_instance = DimensionMemberAssociations::findById($assoc);
					if ($assoc_instance instanceof DimensionMemberAssociation) {
						$assoc_dim = Dimensions::getDimensionById($assoc_instance->getAssociatedDimensionMemberAssociationId());
						if ($assoc_dim instanceof Dimension) {
							if (!in_array($assoc_dim->getName(), $missing_names)) $missing_names[] = $assoc_dim->getName();
						}
					}
					if ($missing) $missing_count++;
				}
				if ($missing_count > 0) {
					throw new Exception(lang("missing required associations", implode(", ", $missing_names)));
				}
				
				$args = array($member, $old_properties, $new_properties);
				Hook::fire('edit_member_properties', $args, $ret);
			}
			
			
			if ($is_new) {
				// set all permissions for the creator
				$dimension = $member->getDimension();
				
				$allowed_object_types = array();
				$dim_obj_types = $dimension->getAllowedObjectTypeContents();
				foreach ($dim_obj_types as $dim_obj_type) {
					// To draw a row for each object type of the dimension
					if (!in_array($dim_obj_type->getContentObjectTypeId(), $allowed_object_types) && $dim_obj_type->getDimensionObjectTypeId() == $member->getObjectTypeId()) {
						$allowed_object_types[] = $dim_obj_type->getContentObjectTypeId();
					}
				}
				$allowed_object_types[]=$object_type->getId();
				foreach ($allowed_object_types as $ot) {
					$cmp = ContactMemberPermissions::findOne(array('conditions' => 'permission_group_id = '.logged_user()->getPermissionGroupId().' AND member_id = '.$member->getId().' AND object_type_id = '.$ot));
					if (!$cmp instanceof ContactMemberPermission) {
						$cmp = new ContactMemberPermission();
						$cmp->setPermissionGroupId(logged_user()->getPermissionGroupId());
						$cmp->setMemberId($member->getId());
						$cmp->setObjectTypeId($ot);
					}
					$cmp->setCanWrite(1);
					$cmp->setCanDelete(1);
					$cmp->save();
				}
				
				// set all permissions for permission groups that has allow all in the dimension
				$permission_groups = ContactDimensionPermissions::findAll(array("conditions" => array("`dimension_id` = ? AND `permission_type` = 'allow all'", $dimension->getId())));
				if (is_array($permission_groups)) {
					foreach ($permission_groups as $pg) {
						foreach ($allowed_object_types as $ot) {
							$cmp = ContactMemberPermissions::findById(array('permission_group_id' => $pg->getPermissionGroupId(), 'member_id' => $member->getId(), 'object_type_id' => $ot));
							if (!$cmp instanceof ContactMemberPermission) {
								$cmp = new ContactMemberPermission();
								$cmp->setPermissionGroupId($pg->getPermissionGroupId());
								$cmp->setMemberId($member->getId());
								$cmp->setObjectTypeId($ot);
							}
							$cmp->setCanWrite(1);
							$cmp->setCanDelete(1);
							$cmp->save();
						}
					}
				}
				
				// Inherit permissions from parent node, if they are not already set
				if ( $member->getDepth() && $member->getParentMember() ) {
					$parentNodeId = $member->getParentMember()->getId();
					$condition = "member_id = $parentNodeId" ;
					foreach ( ContactMemberPermissions::instance()->findAll(array("conditions"=>$condition)) as $parentPermission ){
						/* @var $parentPermission ContactMemberPermission */
						$g = $parentPermission->getPermissionGroupId() ;
						$t = $parentPermission->getObjectTypeId() ;
						$w = $parentPermission->getCanWrite() ;
						$d = $parentPermission->getCanDelete() ;
						$existsCondition = "member_id = ".$member->getId()." AND permission_group_id= $g AND object_type_id = $t";
						if (!ContactMemberPermissions::instance()->count(array("conditions"=>$existsCondition))){
							$newPermission = new ContactMemberPermission();
							$newPermission->setPermissionGroupId($g);
							$newPermission->setObjectTypeId($t);
							$newPermission->setCanWrite($w);
							$newPermission->setCanDelete($d);
							$newPermission->setMemberId($member->getId());
							$newPermission->save();
						}
					}
				}
				
				// Fill sharing table if is a dimension object (after permission creation);
				if (isset($dimension_object) && $dimension_object instanceof ContentDataObject) {
					$dimension_object->addToSharingTable();
				}
				
			} else {
				// if parent changed rebuild object_members for every object in this member
				if ($old_parent != $member->getParentMemberId()) {
					$sql = "SELECT om.object_id FROM ".TABLE_PREFIX."object_members om WHERE om.member_id=".$member->getId();
					$object_ids = DB::executeAll($sql);
					if (!is_array($object_ids)) $object_ids = array();
					foreach ($object_ids as $row) {
						$content_object = Objects::findObject($row['object_id']);
						if (!$content_object instanceof ContentDataObject) continue;
						
						$parent_ids = array();
						if ($old_parent > 0) {
							$all_parents = Members::findById($old_parent)->getAllParentMembersInHierarchy(true);
							foreach ($all_parents as $p) $parent_ids[] = $p->getId();
							if (count($parent_ids) > 0) {
								DB::execute("DELETE FROM ".TABLE_PREFIX."object_members WHERE object_id=".$content_object->getId()." AND member_id IN (".implode(",",$parent_ids).")");
							}
						}
						
						$content_object->addToMembers(array($member));
						$content_object->addToSharingTable();
					}
				}
			}
			
			DB::commit();
			flash_success(lang('success save member', lang(ObjectTypes::findById($member->getObjectTypeId())->getName()), $member->getName()));
			ajx_current("back");
			// Add od to array on new members
			if ($is_new) {
				$member_data['member_id'] = $member->getId();
			}
			evt_add("after member save", $member_data) ;
			return $member;
		} catch (Exception $e) {
			DB::rollback();
			flash_error($e->getMessage());
			ajx_current("empty");
		}
	}
	
	function delete() {
		if(!can_manage_dimension_members(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$member = Members::findById(get_id());
		try {
			
			DB::beginWork();
			
			if (!$member->canBeDeleted($error_message)) {
				throw new Exception($error_message);
			}
			$dim_id = $member->getDimensionId();
			
			// Remove from shring table
			SharingTables::instance()->delete(" 
				object_id IN (
 				 SELECT distinct(object_id) FROM ".TABLE_PREFIX."object_members WHERE member_id = ".get_id()." AND is_optimization = 0
				)
			");
			$affectedObjectsRows = DB::executeAll("SELECT distinct(object_id) AS object_id FROM ".TABLE_PREFIX."object_members where member_id = ".get_id()." AND is_optimization = 0") ;
			if (is_array($affectedObjectsRows) && count($affectedObjectsRows) > 0) {
				foreach ( $affectedObjectsRows as $row ) {
					$oid = $row['object_id'];
					$object = Objects::findObject($row['object_id']) ; // return an instance of Message, contact, etc.
					/* @var $object ContentDataObject */
					if ($object) {
						if ( $object instanceof ContentDataObject ) {
							$object->addToSharingTable();
						}
					}	 
				}
			}
			
			$args = $member;
			Hook::fire('delete_member', $args, $ret);

//			ApplicationLogs::createLog($member, ApplicationLogs::ACTION_DELETE, false, true);
			$ok = $member->delete();
			if ($ok) evt_add("reload dimension tree", $dim_id);
			
			DB::commit();
			flash_success(lang('success delete member', $member->getName()));
			if (get_id('start')) {
				ajx_current("start");
			} else {
				if (get_id('dont_reload')) {
					ajx_current("empty");
				} else {
					ajx_current("reload");
				}
			}
		} catch (Exception $e) {
			DB::rollback();
			flash_error($e->getMessage());
			ajx_current("empty");
		}
	}
        
	function get_dimension_object_fields() {
		ajx_current("empty");
		if (!can_manage_dimension_members(logged_user())) {
			flash_error(lang('no access permissions'));
			return;
		}
		
		$object_type = ObjectTypes::findById(get_id());
		if (!$object_type instanceof ObjectType) {
			flash_error(lang('object type dnx'));
			return;
		}
		
		$handler_class = $object_type->getHandlerClass();
		eval('$fields = '.$handler_class.'::getPublicColumns();');
		
		if (get_id('mem_id') > 0) {
			$date_format = user_config_option('date_format');
			$member = Members::findById(get_id('mem_id'));
			if ($member instanceof Member) {
				$dim_obj = Objects::findObject($member->getObjectId());
			}
			if (isset($dim_obj) && !is_null($dim_obj)) {
				foreach($fields as &$field) {
					$value = $dim_obj->getColumnValue($field['col']);
					if ($field['type'] == DATA_TYPE_DATETIME && $value instanceOf DateTimeValue) {
					  	$value = $value->format($date_format);
					}
					$field['val'] = $value;
				}
			}
		}

		$data = array( 'fields' => $fields, 'title' => lang($object_type->getName()) );
		
		ajx_extra_data($data);
	}
	
	function get_dimensions_for_restrictions() {
		if(!can_manage_dimension_members(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		
		$dim_id = get_id();
		$obj_type = get_id('otype');
		
		$restricted_dim_defs = DimensionMemberRestrictionDefinitions::findAll(array("conditions" => array("`dimension_id` = ? AND `object_type_id` = ?", $dim_id, $obj_type)));
		$restricted_ids_csv = "";
		$orderable_dimensions_otypes = array();
		foreach($restricted_dim_defs as $def) {
			$restricted_ids_csv .= ($restricted_ids_csv == "" ? "" : ",") . $def->getRestrictedDimensionId();
			if ($def->getIsOrderable()) 
				$orderable_dimensions_otypes[] = $def->getRestrictedDimensionId() . "_" . $def->getRestrictedObjectTypeId();
		}
		if ($restricted_ids_csv == "") $restricted_ids_csv = "0";
		$dimensions = Dimensions::findAll(array("conditions" => array("`id` <> ? AND `id` IN ($restricted_ids_csv)", $dim_id)));

		$childs_info = array();
		$members = array();
		foreach($dimensions as $dim) {
			$root_members = Members::findAll(array('conditions' => array('`dimension_id`=? AND `parent_member_id`=0', $dim->getId()), 'order' => '`name` ASC'));
			foreach ($root_members as $mem) {
				$members[$dim->getId()][] = $mem;
				$members[$dim->getId()] = array_merge($members[$dim->getId()], $mem->getAllChildrenSorted());
			}
			//generate child array info
			foreach($members[$dim->getId()] as $pmember) {
				$childs_info[] = array("p" => $pmember->getID(), "ch" => $pmember->getAllChildrenIds(), "d" => $pmember->getDimensionId());
			}
		}
		ajx_extra_data(array('childs' => $childs_info));
		
		$orderable_members = array();
		foreach ($members as $d => $dim_members) {
			foreach ($dim_members as $mem) {
				if (in_array($d."_".$mem->getObjectTypeId(), $orderable_dimensions_otypes)) $orderable_members[] = $mem->getId();
			}
		}
		
		$member_id = get_id('mem_id');
		if ($member_id > 0) {
			// actual restrictions
			$restrictions_info = array();
			$restrictions = MemberRestrictions::findAll(array("conditions" => array("`member_id` = ?", $member_id)));
			foreach ($restrictions as $rest) {
				$restrictions_info[$rest->getRestrictedMemberId()] = $rest->getOrder();
			}
			tpl_assign('restrictions', $restrictions_info);
			
			$actual_order_info = array();
			$actual_order = array_keys($restrictions_info);
			foreach($actual_order as $mem_id) {
				$break = false;
				foreach ($members as $d => $dim_members) {
					foreach ($dim_members as $member) {
						if ($member->getId() == $mem_id) {
							$actual_order_info[] = array('dim'=>$d, 'mem'=>$mem_id, 'parent' => $member->getParentMemberId());
							$break = true;
							break;
						}
					}
					if ($break) break;
				}
			}
			ajx_extra_data(array('actual_order' => $actual_order_info));
		}
		
		tpl_assign('genid', array_var($_GET, 'genid'));
		tpl_assign('members', $members);
		tpl_assign('dimensions', $dimensions);
		tpl_assign('orderable_dimensions_otypes', $orderable_dimensions_otypes);
		
		ajx_extra_data(array('ord_members' => $orderable_members));

		$this->setTemplate('dim_restrictions');
	}
	
	
	
	function get_dimensions_for_properties() {
		if(!can_manage_dimension_members(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		
		$dim_id = get_id();
		$obj_type = get_id('otype');
		$parent_id = get_id('parent');
		
		if ($parent_id == 0) {
			$dim_obj_type = DimensionObjectTypes::findById(array('dimension_id' => $dim_id, 'object_type_id' => $obj_type));
			if (!$dim_obj_type->getIsRoot()) {
				flash_error(lang('parent member must be selected to set properties'));
				ajx_current("empty");
				return;
			}
		}
		
		$dimensions = array();
		$associations_info = array();
		$associations_info_tmp = array();
		$member_parents = array();
		
		$associations = DimensionMemberAssociations::getAssociatations($dim_id, $obj_type);
		foreach ($associations as $assoc) {
			$assoc_info = array('id' => $assoc->getId(), 'required' => $assoc->getIsRequired(), 'multi' => $assoc->getIsMultiple(), 'ot' => $assoc->getAssociatedObjectType());
			$assoc_info['members'] = Members::getByDimensionObjType($assoc->getAssociatedDimensionMemberAssociationId(), $assoc->getAssociatedObjectType());
			
			$ot = ObjectTypes::findById($assoc->getAssociatedObjectType());
			$assoc_info['ot_name'] = $ot->getName();
			
			if (!isset($associations_info_tmp[$assoc->getAssociatedDimensionMemberAssociationId()])) {
				$associations_info_tmp[$assoc->getAssociatedDimensionMemberAssociationId()] = array();
				$dimensions[] = Dimensions::getDimensionById($assoc->getAssociatedDimensionMemberAssociationId());
			}
			$associations_info_tmp[$assoc->getAssociatedDimensionMemberAssociationId()][] = $assoc_info;
		}
		
		// check for restrictions
		if ($parent_id > 0) {
			$parent = Members::findById($parent_id);
			$all_parents = $parent->getAllParentMembersInHierarchy();
			$all_parent_ids = array($parent_id);
			foreach ($all_parents as $p) $all_parent_ids[] = $p->getId();
		} else {
			$all_parent_ids = array(0);
		}
		
		$all_property_members = array();
		
		foreach ($associations_info_tmp as $assoc_dim => $ot_infos) {
			
			foreach ($ot_infos as $info) {
				$restriction_defs = DimensionMemberRestrictionDefinitions::findAll(array("conditions" => "`dimension_id` = $dim_id AND `restricted_dimension_id` = $assoc_dim 
					AND `restricted_object_type_id` = ".$info['ot']));
				
				if (!is_array($restriction_defs) || count($restriction_defs) == 0) {
					// no restriction definitions => include all members
					$associations_info[$assoc_dim][] = $info;
					$restricted_dimensions[$assoc_dim] = false;
				} else {
					// restriction definition found => filter members
					$restricted_dimensions[$assoc_dim] = true;
					$restrictions = array();
					$rest_members = array();
					$conditions = "";
					foreach ($restriction_defs as $rdef) {
						
						$conditions = "`restricted_member_id` IN (SELECT `id` FROM ".Members::instance()->getTableName(true)." WHERE 
							`object_type_id` = ".$rdef->getRestrictedObjectTypeId()." AND `dimension_id` = $assoc_dim) AND `member_id` IN (".implode(",", $all_parent_ids).")";

						$restrictions[] = MemberRestrictions::findAll(array("conditions" => $conditions));
					}
					
					$to_intersect = array();
					foreach ($restrictions as $k => $rests) {
						$to_intersect[$k] = array();
						foreach ($rests as $rest) {
							$to_intersect[$k][] = $rest->getRestrictedMemberId();
						}
						if (count($to_intersect[$k]) == 0) unset($to_intersect[$k]);
					}
					
					$apply_filter = true;
			    	$intersection = array_var($to_intersect, 0, array());
			    	if (count($to_intersect) > 1) {
			    		$k = 1;
			    		while ($k < count($to_intersect)) {
			    			$intersection = array_intersect($intersection, $to_intersect[$k++]);
			    		}
			    	} else if (count($to_intersect) == 0) {
			    		// no restrictions found for members
			    		$apply_filter = false;
			    	}
			    	
					if ($apply_filter) 
						$rest_members = Members::findAll(array("conditions" => "`id` IN (".implode(",", $intersection).")"));
					else 
						$rest_members = $info['members'];
					
					$new_info = $info;
					$new_info['members'] = $rest_members;
					$associations_info[$assoc_dim][] = $new_info;
					
					foreach ($rest_members as $member) {
						if (!isset($member_parents[$assoc_dim])) $member_parents[$assoc_dim] = array();
						if ($member->getParentMemberId() > 0) {
							$member_parents[$assoc_dim][$member->getId()] = $member->getParentMemberId();
						}
					}
				}
			}
		}
		
		foreach ($associations_info as $assoc_dim => $ot_infos) {
			foreach ($ot_infos as $info) {
				foreach ($info['members'] as $mem) $all_property_members[] = $mem->getId();
			}
		}
		
		// para cada $info['ot'] ver si en el resultado hay miembros que los restringen
		foreach ($associations_info as $assoc_dim => &$ot_infos) {
			foreach ($ot_infos as &$info) {
				$restriction_defs = DimensionMemberRestrictionDefinitions::findAll(array("conditions" => "`restricted_dimension_id` = $assoc_dim 
					AND `restricted_object_type_id` = ".$info['ot']));

				$restrictions = array();
				foreach ($restriction_defs as $rdef) {
					$restrictions_tmp = MemberRestrictions::findAll(array("conditions" => "`member_id` IN (
						SELECT `id` FROM ".Members::instance()->getTableName(true)." WHERE `dimension_id` = ".$rdef->getDimensionId()." AND `object_type_id` = ".$rdef->getObjectTypeId()." AND `id` IN (".implode(",", $all_property_members)."))"));
					
					$restrictions = array_merge($restrictions, $restrictions_tmp);
				}
				
				$restricted_ids = array();
				if (count($restrictions) == 0) continue;
				
				foreach ($restrictions as $rest) $restricted_ids[] = $rest->getRestrictedMemberId();
				$tmp = array();
				foreach ($info['members'] as $rmem) {
					if (in_array($rmem->getId(), $restricted_ids)) $tmp[] = $rmem;
				}
				$info['members'] = $tmp;
			}
		}

		
		$req_dimensions = array();
		foreach ($associations_info as $assoc_dim => &$ot_infos) {
			$required_count = 0;
			foreach ($ot_infos as &$info) {
				if ($info['required']) $required_count++;
			}
			$req_dimensions[$assoc_dim] = $required_count > 0;
		}

		$member_id = get_id('mem_id');
		$actual_associations_info = array();
		if ($member_id > 0) {
			// actual associations
			$actual_associations = MemberPropertyMembers::getAssociatedPropertiesForMember($member_id);
			foreach ($actual_associations as $actual_assoc) {
				$actual_associations_info[$actual_assoc->getPropertyMemberId()] = true;
			}
		}
		
		tpl_assign('genid', array_var($_GET, 'genid'));
		tpl_assign('dimensions', $dimensions);
		tpl_assign('associations', $associations_info);
		tpl_assign('actual_associations', $actual_associations_info);
		tpl_assign('req_dimensions', $req_dimensions);
		tpl_assign('restricted_dimensions', isset($restricted_dimensions) ? $restricted_dimensions : array());
		
		ajx_extra_data(array('parents' => $member_parents, 'genid' => array_var($_GET, 'genid')));
		
		$this->setTemplate('dim_properties');
	}
	
	
	
	function get_assignable_parents() {
		if(!can_manage_dimension_members(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		
		$dim_id = get_id('dim');
		$otype_id = get_id('otype');
		
		$parents_info = self::getAssignableParents($dim_id, $otype_id);
		
		ajx_extra_data(array("parents" => $parents_info));
		ajx_current("empty");
	}
	
	private function getAssignableParents($dim_id, $otype_id) {
		$parents = Members::findAll(array("conditions" => array("`object_type_id` IN (
			SELECT `parent_object_type_id` FROM `". DimensionObjectTypeHierarchies::instance()->getTableName() ."` WHERE `dimension_id` = ? AND `child_object_type_id` = ?
		)", $dim_id, $otype_id)));
		
		$parents_info = array();
		foreach ($parents as $parent) {
			$parents_info[] = array('id' => $parent->getId(), 'name' => $parent->getName());
		}
		
		$dim_obj_type = DimensionObjectTypes::findById(array('dimension_id' => $dim_id, 'object_type_id' => $otype_id));
		if ($dim_obj_type && $dim_obj_type->getIsRoot()) {
			array_unshift($parents_info, array('id' => 0, 'name' => lang('none')));
		}
		
		return $parents_info;
	}
	
	
	
	
	function edit_permissions() {
		if (!can_manage_security(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		
		$member = Members::findById(get_id());
		if (!$member instanceof Member) {
			flash_error(lang('member dnx'));
			ajx_current("empty");
			return;
		}
		
		if (!array_var($_POST, 'permissions')) {

			$permission_parameters = permission_member_form_parameters($member);
			tpl_assign('permission_parameters', $permission_parameters);

		} else {
			try {
				DB::beginWork();
				
				save_member_permissions($member);

				DB::commit();
				flash_success(lang('success user permissions updated'));
				ajx_current("back");
			} catch (Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			}
		}
	}
	
	
	function quick_add_form() {
		$this->setLayout('empty');
		if ($dimension_id = array_var($_GET, 'dimension_id')){
			$dimension = Dimensions::instance()->findById($dimension_id);
			$dimensionOptions = $dimension->getOptions(true);

			$object_Types = array();
			$parent_member_id = array_var($_GET, 'parent_member_id');
			
			if ($parent_member_id) {
				$parent_member= Members::instance()->findById($parent_member_id) ;
				$object_types = DimensionObjectTypes::getChildObjectTypes($parent_member_id);
			}else {
				$object_types = DimensionObjectTypes::instance()->findAll(array("conditions"=>"dimension_id = $dimension_id AND is_root = 1 "));
			}
			if (count($object_types)) {
				if (count($object_types) == 1) { 
					// Input Hidden
					tpl_assign('object_type', $object_types[0]);
					tpl_assign('object_type_name',ObjectTypes::instance()->findById($object_types[0]->getObjectTypeId())->getName());
				}else{
					// Input combo
					tpl_assign('object_types', $object_types);
				}
			}else{
				tpl_assign("error_msg", $parent_member->getName() ." does not accept child nodes ");
			}
			
			$editUrls = array() ;
			foreach ($object_types as $object_type ){ /* @var $object_type DimensionObjectType */
				if (ObjectTypes::instance()->findById($object_type->getObjectTypeId())->getType() != 'dimension_object') continue;
				$options = $object_type->getOptions(1);
				if (isset($options->defaultAjax) && $options->defaultAjax->controller != "dashboard" )  {
					$editUrls[$object_type->getObjectTypeId()] = get_url( $options->defaultAjax->controller, 'add' );
				}else{
					$t = ObjectTypes::instance()->findById($object_type->getObjectTypeId());
					/* @var $t ObjectType */
					$class_name = ucfirst($t->getName())."Controller";
					if ( $t && controller_exists($t->getName(), $t->getPluginId())) {
						$editUrls[$object_type->getObjectTypeId()] = get_url($t->getName(), 'add');
					}else{
						$editUrls[$object_type->getObjectTypeId()] = get_url( 'member', 'add' , array("dim_id"=>$dimension_id));
					}
				}
			}
			
			tpl_assign('editUrls', $editUrls);
			tpl_assign('parent_member_id',$parent_member_id );
			tpl_assign('dimension_id', $dimension_id );
			if (is_object($dimensionOptions) && is_object($dimensionOptions->quickAdd) && $dimensionOptions->quickAdd->formAction ) {
				tpl_assign('form_action', ROOT_URL."/".$dimensionOptions->quickAdd->formAction );
			}else{
				tpl_assign('form_action', get_url('member', 'add', array('quick'=>'1')));
			}
		}else{
			die("SORRY. Invalid dimension");
		}
		
	}
	
	
	
	/**
	 * Used for Drag & Drop, adds objects to a member
	 * @author alvaro
	 */
	function add_objects_to_member() {
		$ids = json_decode(array_var($_POST, 'objects'));
		$mem_id = array_var($_POST, 'member');
		
		if (!is_array($ids) || count($ids) == 0) {
			ajx_current("empty");
			return;
		}
		
		$member = Members::findById($mem_id);
		
		try {
			DB::beginWork();
			
			$objects = array();
			$from = array();
			foreach ($ids as $oid) {
				/* @var $obj ContentDataObject */
				$obj = Objects::findObject($oid);
				$dim_obj_type_content = DimensionObjectTypeContents::findOne(array('conditions' => array('`dimension_id`=? AND `dimension_object_type_id`=? AND `content_object_type_id`=?', $member->getDimensionId(), $member->getObjectTypeId(), $obj->getObjectTypeId())));
				if (!($dim_obj_type_content instanceof DimensionObjectTypeContent)) continue;
				if (!$dim_obj_type_content->getIsMultiple() || array_var($_POST, 'remove_prev')) {
					$db_res = DB::execute("SELECT group_concat(om.member_id) as old_members FROM ".TABLE_PREFIX."object_members om INNER JOIN ".TABLE_PREFIX."members m ON om.member_id=m.id WHERE m.dimension_id=".$member->getDimensionId()." AND om.object_id=".$obj->getId());
					$row = $db_res->fetchRow();
					if (array_var($row, 'old_members') != "") $from[$obj->getId()] = $row['old_members'];
					// remove from previous members
					ObjectMembers::delete('`object_id` = ' . $obj->getId() . ' AND `member_id` IN (SELECT `m`.`id` FROM `'.TABLE_PREFIX.'members` `m` WHERE `m`.`dimension_id` = '.$member->getDimensionId().')');
				}
				$obj->addToMembers(array($member));
				$obj->addToSharingTable();
				$objects[] = $obj;
			}
			
			DB::commit();
			
			// add to application logs
			foreach ($objects as $object) {
				$action = array_var($from, $obj->getId()) ? ApplicationLogs::ACTION_MOVE : ApplicationLogs::ACTION_COPY;
				$log_data = (array_var($from, $obj->getId()) ? "from:" . array_var($from, $obj->getId()) . ";" : "") . "to:" . $member->getId();
				ApplicationLogs::instance()->createLog($object, $action, false, true, true, $log_data);
			}
			
			$lang_key = count($ids)>1 ? 'objects moved to member success' : 'object moved to member success';
			flash_success(lang($lang_key, $member->getName()));
			if (array_var($_POST, 'reload')) ajx_current('reload');
			else ajx_current('empty');
			
		} catch (Exception $e) {
			DB::rollback();
			ajx_current("empty");
			flash_error(lang('unable to move objects'));
		}
	}
	

	
	function archive() {
		if (!can_manage_dimension_members(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$member = Members::findById(get_id());
		if (!$member instanceof Member) {
			flash_error(lang('member dnx'));
			ajx_current("empty");
			return;
		}
		if (get_id('user')) $user = Contacts::findById($get_id('user'));
		else $user = logged_user();
		
		if (!$user instanceof Contact) {
			ajx_current("empty");
			return;
		}
		
		try {
			DB::beginWork();
			set_time_limit(0);
			
			$count = $member->archive($user);
			
			evt_add("reload dimension tree", $member->getDimensionId());
			
			ajx_current("back");
			flash_success(lang('success archive member', $member->getName(), $count));
			
			DB::commit();
		} catch (Exception $e) {
			DB::rollback();
			flash_error($e->getMessage());
			ajx_current("empty");
		}
	}
	
	
	function unarchive() {
		if (!can_manage_dimension_members(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$member = Members::findById(get_id());
		if (!$member instanceof Member) {
			flash_error(lang('member dnx'));
			ajx_current("empty");
			return;
		}
		if (get_id('user')) $user = Contacts::findById($get_id('user'));
		else $user = logged_user();
		
		if (!$user instanceof Contact) {
			ajx_current("empty");
			return;
		}
		
		try {
			DB::beginWork();
			set_time_limit(0);
			
			$count = $member->unarchive($user);
			
			evt_add("reload dimension tree", $member->getDimensionId());
			
			ajx_current("back");
			flash_success(lang('success unarchive member', $member->getName(), $count));
			
			DB::commit();
		} catch (Exception $e) {
			DB::rollback();
			flash_error($e->getMessage());
			ajx_current("empty");
		}
	}
}