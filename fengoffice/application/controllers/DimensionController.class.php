<?php

/**
 * Dimension controller
 *
 * @version 1.0
 * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
 */
class DimensionController extends ApplicationController {

	/**
	 * Prepare this controller
	 *
	 * @param void
	 * @return ProjectController
	 */
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website');
	} // __construct
	

	/**
	 * Gets all the dimensions that user can see plus those wich must be displayed in the panels 
	 * 
	 */
	function get_context(){
		ajx_current("empty");

		// User config root dimensions
		$dids = explode ("," ,user_config_option('root_dimensions', null, logged_user()->getId() ));
		foreach ($dids as  $id) {
			if (is_numeric($id) && $id > 0 ) {
				$user_root_dimensions[$id] = true ;
			}
		}		
		
		//All dimensions
		$all_dimensions = Dimensions::findAll(array('order'=>'default_order ASC , id ASC'));
		$dimensions_to_show = array();
		
		
		$contact_pg_ids = ContactPermissionGroups::getPermissionGroupIdsByContactCSV(logged_user()->getId(),false);
		
		foreach ($all_dimensions as $dim){
			$did = $dim->getId();
			if (isset($user_root_dimensions) && count($user_root_dimensions)) {
				if ( isset($user_root_dimensions[$did]) && $user_root_dimensions[$did] ){
					$dim->setIsRoot(true);
				}else{
					$dim->setIsRoot(false);
				}
			} 
					
			$added=false;
			
			if (!$dim->getDefinesPermissions()){
				$dimensions_to_show ['dimensions'][] = $dim;
				$added = true;
			}
			else{
				/*if dimension does not deny everything for each contact's PG, show it*/
				if (!$dim->deniesAllForContact($contact_pg_ids)){
					$dimensions_to_show ['dimensions'][] = $dim;
					$added = true;
				}
			}
			if ($dim->getIsRoot()&& $added){
					$dimensions_to_show ['is_root'][] = true;
			}
			
		}
		return $dimensions_to_show;
	}
	
	/** 
	 * Returns all the members to be displayed in the panel that corresponds to the dimension whose id is received by
	 * parameter. It is called when the application is first loaded. 
	*/
	function initial_list_dimension_members($dimension_id, $object_type_id, $allowed_member_type_ids = null, $return_all_members = false){
		$allowed_object_type_ids = array();
		$item_object = null ;
		if(logged_user()->isAdministrator())$return_all_members=true;
		$contact_pg_ids = ContactPermissionGroups::getPermissionGroupIdsByContactCSV(logged_user()->getId(),false);
		$dimension = Dimensions::getDimensionById($dimension_id);
		
		if ($object_type_id != null){
			$dimension_object_type_contents = $dimension->getObjectTypeContent($object_type_id);
			foreach ($dimension_object_type_contents as $dotc){
				$dot_id = $dotc->getDimensionObjectTypeId();
				if (is_null($allowed_member_type_ids) || in_array($dot_id, $allowed_member_type_ids))
					$allowed_object_type_ids[] = $dot_id;
			}
			
			$object_type = ObjectTypes::findById($object_type_id);
			if ($object_type instanceof ObjectType && $object_type->getType() == 'dimension_object' ) {
				eval('$ot_manager = '.$object_type->getHandlerClass().'::instance();');
				if (isset($ot_manager)) {
					eval('$item_object = new '.$ot_manager->getItemClass().'();');
				}
			}
		}
		if ($dimension instanceof Dimension){
			$parent = 0;
			if (!$dimension->getDefinesPermissions() || $dimension->hasAllowAllForContact($contact_pg_ids) || $return_all_members){
				$all_members = $dimension->getAllMembers(false,"parent_member_id, name",true);
			}
			else if ($dimension->hasCheckForContact($contact_pg_ids)){
				$member_list = $dimension->getAllMembers(false,"parent_member_id, name",true);
				$allowed_members = array();
				foreach ($member_list as $dim_member){
					if (ContactMemberPermissions::instance()->contactCanReadMemberAll($contact_pg_ids, $dim_member->getId(), logged_user())) {
						$allowed_members[] = $dim_member;
					}
				}
				$all_members = $allowed_members;
			}
			if (!isset($all_members)) {
				$all_members = array();	
			}
			return $this->buildMemberList($all_members, $dimension, $allowed_member_type_ids,$allowed_object_type_ids, $item_object, $object_type_id );
		}
		return null;
	}

	/**
	 * 
	 * 
	 */
	function list_dimension_members($member_id, $context_dimension_id, $object_type_id, $allowed_member_type_ids){
		
		if ($member_id != 0){

			$contact_pg_ids = ContactPermissionGroups::getPermissionGroupIdsByContactCSV(logged_user()->getId(),false);
			$member = members::findById($member_id);
			$dimension = Dimensions::getDimensionById($context_dimension_id);
			
			if ($object_type_id != null){
				$dimension_object_type_contents = $dimension->getObjectTypeContent($object_type_id);
				foreach ($dimension_object_type_contents as $dotc){
					$dot_id = $dotc->getDimensionObjectTypeId();
					if (is_null($allowed_member_type_ids) || in_array($dot_id, $allowed_member_type_ids))
						$allowed_object_type_ids[] = $dot_id;
				}
			}
			
			if ($dimension instanceof Dimension && $member instanceof Member){
							
				if (!$dimension->getDefinesPermissions() || $dimension->hasAllowAllForContact($contact_pg_ids)){
					$dimension_members = $dimension->getAllMembers(false,"parent_member_id, name",true);
				}
				else if ($dimension->hasCheckForContact($contact_pg_ids)){
					$member_list = $dimension->getAllMembers(false,"parent_member_id, name",true);
					$allowed_members = array();
					foreach ($member_list as $dim_member){
						if (ContactMemberPermissions::instance()->contactCanReadMemberAll($contact_pg_ids, $dim_member->getId(), logged_user())) {
							$allowed_members[] = $dim_member;
						}
					}
					$dimension_members = $allowed_members;
				}
				
				$members_to_retrieve = array();
				$association_ids = DimensionMemberAssociations::getAllAssociationIds($member->getDimensionId(),$context_dimension_id);
				
				if (count($association_ids)>0){
						$associated_members_ids_csv = '';
						foreach ($association_ids as $id){
							$association = DimensionMemberAssociations::findById($id);
							$children = $member->getAllChildrenInHierarchy();
							if ($association->getDimensionId()== $context_dimension_id){
								$new_csv = MemberPropertyMembers::getAllMemberIds($id,$member_id);
								$associated_members_ids_csv.= $new_csv!= '' ? $new_csv."," : '';	
								foreach ($children as $child){
									$new_csv = MemberPropertyMembers::getAllMemberIds($id,$child->getId());
									$associated_members_ids_csv.= $new_csv!= '' ? $new_csv."," : '';
								}
							}
							else{
								$new_csv =  MemberPropertyMembers::getAllPropertyMemberIds($id,$member_id).",";
								$associated_members_ids_csv.= $new_csv!= '' ? $new_csv."," : '';
								foreach ($children as $child){
									$new_csv = MemberPropertyMembers::getAllPropertyMemberIds($id,$child->getId());
									$associated_members_ids_csv.= $new_csv!= '' ? $new_csv."," : '';
								}
							}
						}
					    $associated_members_ids = explode(',',$associated_members_ids_csv);
						$associated_members_ids = array_unique($associated_members_ids);
				}
				
				if (isset($associated_members_ids) && count($associated_members_ids)>0){
					foreach ($associated_members_ids as $id){
						$associated_member = Members::findById($id);
						if (in_array($associated_member, $dimension_members)){
							$context_hierarchy_members = $associated_member->getAllParentMembersInHierarchy(true);
							foreach ($context_hierarchy_members as $context_member){
								if (!in_array($context_member, $members_to_retrieve) && in_array($context_member, $dimension_members))
									$members_to_retrieve [$context_member->getName()] = $context_member;
							}
						}
					}
					// alphabetical order
					$members_to_retrieve = array_ksort($members_to_retrieve);
				}
				else{
					$members_to_retrieve [] = $dimension_members;
				}
				
				$membersset = array();
				foreach ($members_to_retrieve as $m) {
					$membersset[$m->getId()] = true;
				}
				$members = array();
				// Todo adapt this code to call "buildMemberList" - (performance and code improvement)
				foreach ($members_to_retrieve as $m) {
					if ($m->getArchivedById() > 0) continue;
					
					if ($object_type_id!=null){
						$selectable = in_array($m->getObjectTypeId(), $allowed_object_type_ids) ? true : false;
					}
					
					$tempParent = $m->getParentMemberId();
					$x = $m;
					while ($x instanceof Member && !isset($membersset[$tempParent])) {
						$tempParent = $x->getParentMemberId();
						$x = $x->getParentMember();
					}
					if (!$x instanceof Member) {
						$tempParent = 0;
					}
				
					if ($dot = DimensionObjectTypes::instance()->findOne(array("conditions" =>"
						dimension_id = ".$dimension->getId() ." AND
						object_type_id = ".$m->getObjectTypeId() 
					))){
						$memberOptions = $dot->getOptions(true);
					}else{
						$memberOptions = '' ;
					}
					
					/* @var $m Member */
					$member = array(
						"id" => $m->getId(),
						"name" => clean($m->getName()),
						"parent" => $tempParent,
						"realParent" => $m->getParentMemberId(),
						"object_id" => $m->getObjectId(),
						"options"  => $memberOptions, 
						"depth" => $m->getDepth(),
						"iconCls" => $m->getIconClass(),
						"selectable" => isset($selectable) ? $selectable : false,
						"dimension_id" => $m->getDimensionId() ,
						"object_type_id" => $m->getObjectTypeId(),
						"allow_childs" => $m->allowChilds()
										
					);
					
					
					if ($oid = $m->getObjectId() ) {
						if ( $obj = Objects::instance()->findObject($m->getObjectId()) ){
							$editUrl = $obj->getEditUrl();	
						}
					}
					
					// Member Actions
					if (can_manage_dimension_members(logged_user())){
						if ($oid = $m->getObjectId() ) {
							if ( $obj = Objects::instance()->findObject($m->getObjectId()) ){
								$editUrl = $obj->getEditUrl();
							}
						}else{
							$editUrl =  get_url('member', 'edit', array('id'=> $m->getId())) ;
						}
						$member['actions'] = array(array(
							'url' => $editUrl,
				  			'text' =>  '',
				  			'iconCls' =>  'ico-edit'
						));	
					}
					
					$members[] = $member;
				}
				return $members;
			}
			return null ;
		}
		else {
			$members = $this->initial_list_dimension_members($context_dimension_id, $object_type_id, $allowed_member_type_ids);
			return $members;
		}
	}
	
	/**
	 * 
	 * @author Ignacio Vazquez - elpepe.uy@gmail.com
	 */
	function list_dimension_members_tree() {
		$dimension_id  = array_var($_GET, 'dimension_id') ;
		$checkedField = (array_var($_GET, 'checkboxes'))?"checked":"_checked";		
		$objectTypeId = array_var($_GET, 'object_type_id', null );	
		$allowedMemberTypes = json_decode(array_var($_GET, 'allowedMemberTypes', null ));	
		if (!is_numeric($allowedMemberTypes)) {
			$allowedMemberTypes = null ;
		}	
		$member_id  = array_var($_GET, 'member_id') ;
		$memberList = $this->list_dimension_members($member_id, $dimension_id, $objectTypeId, $allowedMemberTypes);
		$tree = buildTree($memberList, "parent", "children", "id", "name", $checkedField) ;
		ajx_current("empty");		
		ajx_extra_data(array('dimension_members' => $tree ));			
	}
	
	/**
	 * 
	 * @author Ignacio Vazquez - elpepe.uy@gmail.com
	 */
	function initial_list_dimension_members_tree() {
		$dimension_id  = array_var($_GET, 'dimension_id') ;
		$checkedField = (array_var($_GET, 'checkboxes'))?"checked":"_checked";
		$objectTypeId = array_var($_GET, 'object_type_id', null );
		$allowedMemberTypes = json_decode(array_var($_GET, 'allowedMemberTypes', null ));	
		if (!is_array($allowedMemberTypes)) {
			$allowedMemberTypes = null ;
		}		
		$memberList = $this->initial_list_dimension_members($dimension_id, $objectTypeId, $allowedMemberTypes);
		
		$tree = buildTree($memberList,  "parent", "children", "id", "name", $checkedField) ;
		ajx_current("empty");		
		ajx_extra_data(array('dimension_members' => $tree ));	
	}
	
	function dimensions_js () {
		session_write_close();
		header("Content-Type: text/javascript" ); 
		$dimensions = Dimensions::findAll();
		echo "og.dimensions = [];\n" ;
		foreach ($dimensions as $dim) {
			$members = $dim->getAllMembers();
			echo "var members = [];\n";
			foreach ($members as $member) { 
				echo "members[".$member->getId()."] = {\n";
				echo "  id: ".$member->getId().",\n";
				echo "  name:'". str_replace(array("'", "\\"), array("","\\\\" ), clean($member->getName()))."',\n";
				echo "  ot:". $member->getObjectTypeId().",\n" ;
				echo "  ico:'".$member->getIconClass()."'\n" ;
				echo "};\n";
			}
			echo "og.dimensions[".$dim->getId()."] = members;\n\n";
		}
		exit ;
	}
	
	
	
	function buildMemberList($all_members, $dimension,  $allowed_member_type_ids, $allowed_object_type_ids, $item_object, $object_type_id) {
		$dot_array = array(); // Dimensio Object Types array (cache)
		$start_time = microtime(1);
		$membersset = array();
		foreach ($all_members as $m) {
			$membersset[$m->getId()] = true;
		}
		$members = array();
		foreach ($all_members as $m) {
			/* @var  $m Member */
			if ($m->getArchivedById() > 0) continue;
			if ($object_type_id != null){
				$selectable = in_array($m->getObjectTypeId(), $allowed_object_type_ids) ? true : false;
				if ($selectable && isset($item_object)) {
					if (! $item_object->canAdd(logged_user(), array($m)) ) continue;
				}
			}else{
				$selectable = true ;
			}
			if ( count($allowed_member_type_ids) && !in_array($m->getObjectTypeId(), $allowed_member_type_ids) ) {
				continue;	
			}
			$tempParent = $m->getParentMemberId();
			$x = $m;
			while ($x instanceof Member && !isset($membersset[$tempParent])) {
				$tempParent = $x->getParentMemberId();
				$x = $x->getParentMember();
			}
			if (!$x instanceof Member) {
				$tempParent = 0;
			}
			$memberOptions = '';
			// SET member options (dimension object types table)
			// CHeck dot cache, if not set goto database and add to cache
			if ( empty($dot_array[$dimension->getId()]) || empty ($dot_array[$dimension->getId()][$m->getObjectTypeId()]) ) {
				if ($dot = DimensionObjectTypes::instance()->findOne(array("conditions" =>"
					dimension_id = ".$dimension->getId() ." AND
					object_type_id = ".$m->getObjectTypeId() 
				))){
					if (empty($dot_array['dimension_id'])) {
						$dot_array[$dimension->getId()] = array();
					}
					$dot_array[$dimension->getId()][$m->getObjectTypeId()]= $dot ;
				}
			}
			if ( !empty($dot_array[$dimension->getId()]) || ($dot_array[$dimension->getId()][$m->getObjectTypeId()]) instanceof DimensionObjectType ) {
				$dot =  $dot_array[$dimension->getId()][$m->getObjectTypeId()];
				$memberOptions = $dot->getOptions(true);
			}
			
			/* @var $m Member */
			$member = array(
				"id" => $m->getId(),
				"name" => clean($m->getName()),
				"parent" => $tempParent,
				"realParent" => $m->getParentMemberId(),
				"object_id" => $m->getObjectId(),
				"options"  => $memberOptions,
				"depth" => $m->getDepth(),
				"iconCls" => $m->getIconClass(),
				"selectable" => isset($selectable) ? $selectable : false,
				"dimension_id" => $m->getDimensionId() ,
				"object_type_id" => $m->getObjectTypeId(),
				"allow_childs" => $m->allowChilds()
			);
			// Member Actions
			if (can_manage_dimension_members(logged_user())){
				$editUrl = '';			
				// If member has an object linked, take object edit url 
				if ($otid = $m->getObjectTypeId()){
					if ($ot = ObjectTypes::findById($otid)) {
						if ($handler = $ot->getHandlerClass() ){
							eval ("\$itemClass = $handler::instance()->getItemClass();");
							if ($itemClass) {
								$instance = new $itemClass();
								$instance->setId($m->getObjectId());
								$instance->setObjectId($m->getObjectId());
								if ($instance) {
									$editUrl = $instance->getEditUrl();
								}
							}
						}
					}
				}
				// Take default membewr edit url if not overwitten
				if (!$editUrl) {
					$editUrl =  get_url('member', 'edit', array('id'=> $m->getId())) ;
				}
				$member['actions'] = array(array(
					'url' => $editUrl,
		  			'text' =>  '',
		  			'iconCls' =>  'ico-edit'
				));	
			}
			$members[] = $member;
		}
		return $members ;
	}
}