<?php

  /**
  * Projects, generated on Sun, 26 Feb 2006 23:10:34 +0100 by 
  * DataObject generation tool
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class Projects extends BaseProjects {
    
    /**
    * This constants are used for retriving project data, to see how to order results
    */
    const ORDER_BY_DATE_CREATED = 'created_on';
    const ORDER_BY_NAME = 'name';
    
    /**
    * Return all projects
    *
    * @param void
    * @return array
    */
    static function getAll($order_by = self::ORDER_BY_NAME, $where = null) {
    	if ($where != null)
    	    return Projects::findAll(array(
        'order' => $order_by,
    	'conditions' => $where   	    
      )); // findAll
      else
	      return Projects::findAll(array(
        'order' => $order_by    	    
      )); // findAll
    } // getAll
    
    /**
    * Return all active project from the database
    *
    * @param string $order_by
    * @return null
    */
    static function getActiveProjects($order_by = self::ORDER_BY_NAME) {
      return self::findAll(array(
        'conditions' => array('`completed_on` = ?', EMPTY_DATETIME),
        'order' => $order_by,
      )); // findAll
    } // getActiveProjects
    
    /**
     * Returns the workspaces that have no parent.
     * @return array
     *
     */
    static function getTopWorkspaces() {
    	return Projects::findAll(array('conditions' => array('p2 = ?', 0))); 
    }
    
    /**
    * Return finished projects
    *
    * @param string $order_by
    * @return array
    */
    static function getFinishedProjects($order_by = self::ORDER_BY_NAME) {
      return self::findAll(array(
        'conditions' => array('`completed_on` > ?', EMPTY_DATETIME),
        'order' => $order_by,
      )); // findAll
    } // getFinishedProjects
    
    /** Return project by name.
	*
	* @param name
	* @return array
	*/
	static function getByName($name) {
		$conditions = array('`name` = ?', $name);
		
		return self::findOne(array(
			'conditions' => $conditions
		));
	} // getByName
	
	/**
	 * Receives comma seperated ids and returns the workspaces with those ids
	 *
	 * @param string $csv
	 * @return array
	 */
	static function findByCSVIds($csv) {
		if (!self::is_valid_csv_ids($csv)) return array();
		return self::findAll(array('conditions' => "`id` IN ($csv)"));
	}
    
    /**
    * Return all projects as tree view
    *
    * @access public
    * @param User $user
    * @param 
    * @return array
    */
    function getProjectsByParent(User $user, $additional_conditions = null) {
      	$projects_table = Projects::instance()->getTableName(true);
    	$all = self::getActiveProjects(/*"$projects_table.`parent_id`, $projects_table.`name`"*/);
	    if(is_array($all)) {
	        foreach($all as $proj) {
	          	$projects[$proj->getParentId()] []= $proj;
	        } // foreach
	    } // if
      
      return count($projects) ? $projects : null;
    } // getProjectsByUser
    
    
    /*
     * Gets a Project from a String that states its path. 
     * When user == null, permissions are not checked.
     * When user != null project is only returned if user has permissions over the workspace , else null is returned;
     * If path is root or empty, null is returned
     */
    function getProjectFromPath($path, User $user = null){
    	$parents = explode ("/",$path);
    	$length = count($parents);
		// Clean up the array for empty places
    	while ($parents[0] == "" && $length>0){
    		array_shift($parents);
    		$length = count($parents);    		
    	}
    	while ($length>0 && $parents[$length-1] == ""){
    		array_pop($parents);
    		$length = count($parents);
    	}
    	if($length == 0){
    		// ERROR
    		return null;
    	}
    	else if($length == 1){
    		// Level one workspace
    		$name = $parents[0];
   			// Loof for top level project
    		$proj = Projects::findOne(array('conditions' => array('p2 = ? and `name` = ?', 0, $name)));
			if(!$proj){
				if(!($user instanceof User)){
					// Not checking permissions and project is not top level, so the path is invalid
					return null;
				}else{
					// User might not have permissions over workspace parent and other ancestors.
					// This means current user might see workspace $proj in the root while it is really not top level. 
					$projs = Projects::findAll(array('conditions' => array('`name` = ?', $name)));
					if(!$projs){
						 //No project by that name
						 return null;
					}
					else{
						// Iterate through all projects with that name
						foreach ($projs as $ws){
							// If $user has no permissions over $ws, it is not what we are looking for
							if($user->isProjectUser($ws)){
								$is_candidate_project = true;
								// Iterate through all ancestors to check permissions
								foreach($ws->getParentIds(false) as $ancestor_id){
									$ancestor = Projects::findById($ancestor_id);
									if($user->isProjectUser($ancestor)){
										// If user has permission over an ancestor, path is wrong
										$is_candidate_project = false;
										break;
									} //if
								} //foreach $ancestor
								if($is_candidate_project){
									// No permissions over ancestors
									return $ws;
								}// if
							} // if 
						} // foreach $project
						// No project by that name should appear in the root
						return null;
					}
				}
			}
    		if ($user && (!$user instanceof User || !$user->isProjectUser($proj))){
    			return null;
    		}    		   		
	    	else{ 
	    		return $proj;
	    	}
    	}
    	else
    	{
    		$currentName = array_pop($parents);
    		$length = count($parents);
    		$new_path = implode("/",$parents);
    		$parent = Projects::getProjectFromPath($new_path, $user);
    		if($parent instanceof Project){
    			$conditions = 'p'. ($length ) .' = ? and p'. ($length+2).' = ? and `name` = ?';
    			$proj = Projects::findOne(array('conditions' => array($conditions,$parent->getId() ,0, $currentName)));

				if($user){
					//we are checking permissions
					if( $proj && $user->isProjectUser($proj)){
						// User has permissions over workspace, found!
						return $proj;
					}else{
						// child does not exist, or it exists and user has no permissions
						// still have to check if there exists a descendant (child of child^n) with that name 
						// If we have permissions over that descendant and have no permissions for WS between, we found a valid WS
    					$conditions = 'p'. ($length ) .' = ? and `name` = ?';
						$projs = Projects::findAll(array('conditions' => array($conditions,$parent->getId() , $currentName)));
						if($projs)
						{
							//Iterate through all projects with that name
							foreach ($projs as $ws){								
								if($user->isProjectUser($ws)){
									$is_candidate_project = true;
									// Iterate through all ancestors to check permissions
									$parent_depth = $parent->getDepth();
									$ws_depth = $ws->getDepth(false);
									$parent_ids = $ws->getParentIds(false);
									// Iterate through all descendants of $parent and ancestors of $ws 
									for( $i = $parent_depth; $i<$ws_depth; $i++ ){
										$ancestor_id = $parent_ids [$i];
										$ancestor = Projects::findById($ancestor_id);
										if($user->isProjectUser($ancestor)){
											// If user has permission over an ancestor, path is wrong
											$is_candidate_project = false;
											break;
										} //if
									} //foreach $ancestor
									if($is_candidate_project){
										// No permissions over ancestors
										return $ws;
									}// if									
								} // else: not interested in this project
							}
						}
						else
							// There is no project by that name
							return null;
					}
				}
				else{ //user is null
					//not checking permissions
					if (!$proj instanceof Project)
						// project not found, wrong path
						return null;
					else
						return $proj;
				}
				if(!$proj && $user == null){
					return null;
				}
    		}
    		else {
    			return null;
    		}
    	}
    	
    }
    
  } // Projects 

?>