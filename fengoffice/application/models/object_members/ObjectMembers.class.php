<?php

  /**
  * ObjectMembers
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  class ObjectMembers extends BaseObjectMembers {
    
    	
  		static function addObjectToMembers($object_id, $members_array){
  			
  			foreach ($members_array as $member){
  				$exists = self::findOne(array("conditions" => array("`object_id` = ? AND `member_id` = ? ", $object_id, $member->getId()))) != null;
  				if (!$exists) {
	  				$om = new ObjectMember();
	  				$om->setObjectId($object_id);
	  				$om->setMemberId($member->getId());
	  				$om->setIsOptimization(0);
	  				$om->save();
  				}
  			}
  			
  			foreach ($members_array as $member){
  				$parents = $member->getAllParentMembersInHierarchy();
  				$stop = false;
  				foreach ($parents as $parent){
  					if (!$stop){
	  					$exists = self::findOne(array("conditions" => array("`object_id` = ? AND `member_id` = ? ", 
	  							  $object_id, $parent->getId())))!= null;
	  					if (!$exists){
	  						$om = new ObjectMember();
			  				$om->setObjectId($object_id);
			  				$om->setMemberId($parent->getId());
			  				$om->setIsOptimization(1);
			  				$om->save();
	  					} 	
	  					else $stop = true;	
  					} 
  				}
  			}
  		}
  		
  		
		/**
		 * Removes the object from those members where the user can see the object(and its corresponding parents)
		 * 
		 */
  		static function removeObjectFromMembers(ContentDataObject $object, Contact $contact, $context_members){
  			
  			$object_type_id = $object->getObjectTypeId();
  			$member_ids = self::getMemberIdsbyObject($object->getId());
  			
  			foreach($member_ids as $id){
				
				$member = Members::findById($id);
				
				//can write this object type in the member
				$can_write = $object->canAddToMember($contact, $member, $context_members);
				
				
				if ($can_write){
					$om = self::findById(array('object_id' => $object->getId(), 'member_id' => $id));
					$om->delete();
					
					$stop=false;
					while ($member->getParentMember()!=null && !$stop){
						$member = $member->getParentMember();
						$obj_member = ObjectMembers::findOne(array("conditions" => array("`object_id` = ? AND `member_id` = ? AND 
									`is_optimization` = 1", $object->getId(),$member->getId())));
						if (!is_null($obj_member)){
							$obj_member->delete();
						}
						else $stop = true;
					}
				}
			}
  		}
  		
  		
  		static function getMemberIdsByObject($object_id){
  			if ($object_id) {
	  			$db_res = DB::execute("SELECT member_id FROM ".TABLE_PREFIX."object_members WHERE object_id = $object_id AND is_optimization = 0");
	  			$rows = $db_res->fetchAll();
  			} else {
  				return array();
  			}
  			
  			$member_ids = array();
                        if(count($rows) > 0){
                            foreach ($rows as $row){
                                    $member_ids[] = $row['member_id'];
                            }
                        }
  			
  			return $member_ids;
  		}
  		
  		
  		
    	static function getMembersByObject($object_id){
  			$ids = self::getMemberIdsByObject($object_id);
  			$members = Members::findAll(array("conditions" => "`id` IN (".implode(",", $ids).")"));
  			
  			return $members;				  
  		}
  		
  		
  		static function getMembersByObjectAndDimension($object_id, $dimension_id, $extra_conditions = "") {
  			$sql = "
  				SELECT distinct(id) 
  				FROM ".TABLE_PREFIX."object_members om 
  				INNER JOIN ".TABLE_PREFIX."members m ON om.member_id = m.id 
  				WHERE 
  					dimension_id = $dimension_id AND 
  					om.object_id = $object_id 
  					$extra_conditions";
  			
  			$result = array() ;
  			$rows = DB::executeAll($sql);
  			
  			if (!is_array($rows)) return $result;
  			
  			foreach ($rows as $row) {
  				$member = Members::instance()->findById($row['id']);
  				if ($member instanceof  Member) {
  					$result[]= $member ;
  				}
  			}
  			return $result ;
  		}
     
  		
  } // ObjectMembers 

?>