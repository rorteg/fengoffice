<?php

  /**
  * SearchableObjects, generated on Tue, 13 Jun 2006 12:15:44 +0200 by 
  * DataObject generation tool
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class SearchableObjects extends BaseSearchableObjects {
    
    /**
    * Search for specific search string ($search_for) in specific project
    *
    * @param string $search_for Search string
    * @param Project $project Search in this project
    * @param boolean $include_private
    * @return array
    */
    static function search($search_for, Project $project, $include_private = false) {
      return SearchableObjects::doSearch(SearchableObjects::getSearchConditions($search_for, $project->getId(), $include_private));
    } // search
    
    /**
    * Search paginated
    *
    * @param string $search_for Search string
    * @param Project $project Search in this project
    * @param boolean $include_private
    * @param integer $items_per_page
    * @param integer $current_page
    * @return array
    */
    static function searchPaginated($search_for, $project_csvs, $include_private = false, $items_per_page = 10, $current_page = 1) {
        $conditions = SearchableObjects::getSearchConditions($search_for, $project_csvs, $include_private);
	    $tagconditions = SearchableObjects::getTagSearchConditions($search_for, $project_csvs);
	    $pagination = new DataPagination(SearchableObjects::countUniqueObjects($conditions, $tagconditions), $items_per_page, $current_page);
	    $items = SearchableObjects::doSearch($conditions, $tagconditions, $pagination->getItemsPerPage(), $pagination->getLimitStart(), $search_for);
		 return array($items, $pagination);
    } // searchPaginated
    
    static function searchByType($search_for, $project_csvs, $object_type = '', $include_private = false, $items_per_page = 10, $current_page = 1, $columns_csv = null, $user_id = 0) {
        $remaining = 0;
        $safe_search_for = str_replace("'", '"', $search_for);
	    $conditions = SearchableObjects::getSearchConditions($safe_search_for, $project_csvs, true, $object_type, $columns_csv, $user_id);
	    $count = SearchableObjects::countUniqueObjects($conditions);
	    $pagination = new DataPagination($count, $items_per_page, $current_page);
	    if ($count > 0)
	    	$items = SearchableObjects::doSearch($conditions, $pagination->getItemsPerPage(), $pagination->getLimitStart(), $search_for);
	    else
	    	$items = array();
        return array($items, $pagination);
    } // searchPaginated
    
    /**
    * Prepare search conditions string based on input params
    *
    * @param string $search_for Search string
    * @param string $project_csvs Search in this project
    * @return array
    */
    function getSearchConditions($search_for, $project_csvs = null, $include_private = false, $object_type = '', $columns_csv = null, $user_id = 0) {
       	$otSearch = '';
    	$columnsSearch = '';
    	$wsSearch = '';
    	$search_deep = false;
    	$few_chars = false;
    	if (!is_null($columns_csv))
    		$columnsSearch = " AND `column_name` in (" . $columns_csv . ")";
    		
    	if ($object_type != '')
    		$otSearch = " AND `rel_object_manager` = '$object_type'";
    
    	if ($project_csvs) {
    		$wsSearch .= " AND ";
	    	/*if ($user_id > 0)
	    		$wsSearch .= " (`user_id` = " . $user_id . " OR ";
	    	else
	    		$wsSearch .= " (";*/
	    		
	    	if ($object_type=="ProjectFileRevisions")
	    		$wsSearch .=  "`rel_object_id` IN (SELECT o.id FROM " . TABLE_PREFIX ."project_file_revisions o where o.file_id IN (SELECT p.`object_id` FROM `".TABLE_PREFIX."workspace_objects` p WHERE p.`object_manager` = 'ProjectFiles' && p.`workspace_id` IN ($project_csvs)))";
	    	else
	    		$wsSearch .= "`rel_object_id` IN (SELECT `object_id` FROM `".TABLE_PREFIX."workspace_objects` WHERE `object_manager` = '$object_type' && `workspace_id` IN ($project_csvs))";
	    	//$wsSearch .=  ')';
    	} else {
    		$wsSearch = "";
    	}
    	
    	//Check for trashed and other permissions
    	$tableName = eval("return $object_type::instance()->getTableName();");
    	$trashed = '';
    	if ($object_type != 'Projects' && $object_type != 'Users'){
    		$trashed = " and EXISTS(SELECT * FROM $tableName co where `rel_object_id` = id and trashed_by_id = 0 ";
    		$trashed .= ' AND ( ' . permissions_sql_for_listings(eval("return $object_type::instance();"), ACCESS_LEVEL_READ, logged_user(), '`project_id`', '`co`') .')';
    		$trashed .= ')';
    	}
	    //Check workspace permissions
    	if ($object_type == 'Projects') {
    		$trashed .= " AND `rel_object_id` IN (SELECT `proj`.`id` FROM $tableName `proj` WHERE ";
    		$trashed .= ' ( ' . permissions_sql_for_listings(eval("return $object_type::instance();"), ACCESS_LEVEL_READ, logged_user(), '`project_id`', '`proj`') .'))';
    	}
    	
    	// if search criteria is a mail address, remove its domain to avoid matching emails with same domain that are not from this address
    	
    	/*
    	$pos = strpos_utf($search_for, '@');
    	while ($pos !== FALSE) {
    		$esp = strpos_utf($search_for, ' ', $pos);
    		if ($esp !== FALSE) $search_for = substr_utf($search_for, 0, $pos) . ' ' . substr_utf($search_for, $esp+1);
    		else $search_for = substr_utf($search_for, 0, $pos);
    		$pos = strpos_utf($search_for, '@');
    	}
    	*/
    	// Commented by pepe
    	
    	if($include_private) {
    		$privSearch = 'AND `is_private` = 0';
    	} else {
    		$privSearch = '';
    	}
    	
    	//in case the string to be looked for contains one to three chars and therefore find no objects with a 'quick search'
    	if (strlen($search_for)<=config_option("min_chars_for_match"))
    		$few_chars = true;
    			
    	//in case the user does a deeper search with " or '
    	if(str_starts_with($search_for,'"') && str_ends_with($search_for,'"')){    		
    		$search_deep = true;    		
    		$search_for = str_replace('"', '', $search_for);    		
    	}	  	    	

    	if (user_config_option('search_engine', substr(Localization::instance()->getLocale(),0,2) == 'zh' ? 'like' : null) == 'like' || $few_chars == true) {
    	    $search_for = str_replace("*", "%", $search_for); 
    		if (!$search_deep){     				
	    		$search_words = explode(" ", $search_for);    			
	    		$search_string = "";	    		
	    		foreach ($search_words as $word) {
	    			if ($search_string) $search_string .= " AND "; 
	    			$search_string .= "`content` LIKE '%$word%'";
	    		}
    	    }
    	    else{
    	    	$search_string .= "`content` LIKE '%$search_for%'";    	    	
    	    }    	    
    		return DB::prepareString("$search_string $privSearch $wsSearch $trashed $otSearch $columnsSearch");
    	} else {
    		$search_words = preg_split('/[\s\.\+\-\~]/', $search_for);
    		if(!$search_deep){	    		
	    		$search_for = "";
	    		foreach ($search_words as $word) {
	    			if ($word != "" && $word[0] != "+" && $word[0] != "-") {
	    				$search_for .= " +$word";
	    			}
	    		}
    		}
    		else{
    			$search_for = "\"".$search_for."\"";	
    		}
    		//return DB::prepareString("MATCH (`content`) AGAINST ('$search_for' IN BOOLEAN MODE) $privSearch $wsSearch $trashed $otSearch $columnsSearch"); 
    		//@pepe - AGAINST() fails with special characters like '@' or '/' if string not scaped
    		
    		return  DB::prepareString("MATCH (`content`) AGAINST ('\"".$search_for."\"' IN BOOLEAN MODE) $privSearch $wsSearch $trashed $otSearch $columnsSearch");
    	}
    } // getSearchConditions
    
    /** Prepare search conditions string based on input params
    *
    * @param string $search_for Search string
    * @param string $project_csvs Search in this project
    * @return array
   	*/
    function getTagSearchConditions($search_for, $project_csvs) {
      return DB::prepareString(" tag = '$search_for' ");
    } // getTagSearchConditions
    
    /**
    * Do the search
    *
    * @param string $conditions
    * @param integer $limit
    * @param integer $offset
    * @return array
    */
    function doSearch($conditions, $limit = null, $offset = null, $search_for = '') {
      $table_name = SearchableObjects::instance()->getTableName(true);
      //$tags_table_name = Tags::instance()->getTableName();
      
      $limit_string = '';
      if((integer) $limit > 0) {
        $offset = (integer) $offset > 0 ? (integer) $offset : 0;
        $limit_string = " LIMIT $offset, $limit";
      } // if
      
      $where = '';
      if(trim($conditions) <> '') $where = "WHERE $conditions";
      
      $sql = "SELECT distinct `rel_object_manager`, `rel_object_id` FROM $table_name $where ORDER BY `rel_object_id` DESC $limit_string";
      $result = DB::executeAll($sql);
      if(!is_array($result)) return null;
      
      
      $new_where = "'1' = '2' ";
      foreach($result as $row) {
        $manager_class = array_var($row, 'rel_object_manager');
        $object_id = array_var($row, 'rel_object_id');
        $new_where .= " OR (rel_object_manager = '" . $manager_class ."' AND rel_object_id = '" . $object_id . "')";
      }
      $new_where = " AND (" . $new_where . ')';
      
      $sql = "SELECT `rel_object_manager`, `rel_object_id`, `column_name`, `content` FROM $table_name $where $new_where ORDER BY `rel_object_id`";
      $result = DB::executeAll($sql);
      if(!is_array($result)) return null;
      
      $loaded = array();
      $objects = array();
      
      foreach($result as $row) {
        $manager_class = array_var($row, 'rel_object_manager');
        $object_id = array_var($row, 'rel_object_id');
        
        if(!isset($loaded[$manager_class.'-'.$object_id])) {
          if(class_exists($manager_class)) {
            $object = get_object_by_manager_and_id($object_id, $manager_class);
            if($object instanceof ApplicationDataObject) {
              $objects[] = array(
              	'object' => $object, 
              	'context' => array(array('context' => SearchableObjects::getContext(array_var($row, 'content'), $search_for),
              		'column_name' => array_var($row, 'column_name'))));
              $loaded[$manager_class . '-' . $object_id] = count($objects) - 1;
            } // if
          } // if
        } else {
        	$objects[$loaded[$manager_class.'-'.$object_id]]['context'][] = array(
	        	'context' => SearchableObjects::getContext(array_var($row, 'content'), $search_for),
	        	'column_name' => array_var($row, 'column_name'));
        } // if
      } // foreach
      
      return count($objects) ? $objects : null;
    } // doSearch
    
    /**
     * Returns the searched words placed in a context, already cleaned and formatted in HTML
     * 
     * @param $content The content where the words were found
     * @param $search_for The searched words
     * @return String
     */
    function getContext($content, $search_for){
    	$context = '';
    	$context_length = 80;
    	
    	$content_lc = strtolower($content);
    	$search_for_lc = strtolower($search_for);
    	$pos = strpos($content_lc,$search_for_lc);
    	
    	if ($pos !== false){
	    	$beginning = substr($content, 0, $pos);
	    	
	    	//Get the beginning of the context
	    	if (strlen($beginning) > $context_length){
				$short_beginning = substr($beginning, strlen($beginning)-$context_length); // Shorten the part
	    		$beginning = '&hellip;' . clean(substr($short_beginning, strpos($short_beginning,' ') + 1)); // Do not cut words in half
	    	} else
	    		$beginning = clean($beginning);
	    	
	    	// Get the word searched for
	    	$middle = clean(substr($content, $pos, strlen($search_for)));
	    	
	    	//Get the end part of the context
	    	$ending = substr($content, $pos + strlen($search_for));
	    	if (strlen($ending) > $context_length){
	    		$short_ending = substr($ending, 0, $context_length);
	    		$ending = clean(substr($short_ending, 0, strrpos($short_ending,' '))) . '&hellip;';
	    	} else
	    		$ending = clean($ending);
	    	
	    	//Form the sentence
	    	$context = $beginning . '<b>' . $middle . '</b>' . $ending;
    	}
    	return $context;
    }
    
    /**
    * Return number of unique objects
    *
    * @param string $conditions
    * @return integer
    */
    function countUniqueObjects($conditions) {
      $table_name = SearchableObjects::instance()->getTableName(true);
      //$tags_table_name = Tags::instance()->getTableName();
      $where = '';
      if(trim($conditions <> '')) $where = "WHERE $conditions";
      
      $sql = "SELECT count(distinct `rel_object_manager`, `rel_object_id`) AS `count` FROM $table_name $where";
      $result = DB::executeAll($sql);
      if (!is_array($result) || !count($result)) return 0;
      
      return $result[0]['count'];
    } // countUniqueObjects
    
    /**
    * Drop all content from table related to $object
    *
    * @param ProjectDataObject $object
    * @return boolean
    */
    static function dropContentByObject(ApplicationDataObject $object) {
    	return SearchableObjects::delete(array('`rel_object_manager` = ? AND `rel_object_id` = ?', get_class($object->manager()), $object->getObjectId()));
    } // dropContentByObject
    
    /**
    * Drop all content from table related to $object
    *
    * @param ProjectDataObject $object
    * @return boolean
    */
    static function dropObjectPropertiesByObject(ApplicationDataObject $object) {
    	return SearchableObjects::delete(array('`rel_object_manager` = ? AND `rel_object_id` = ? AND `column_name` LIKE "property%" ', get_class($object->manager()), $object->getObjectId()));
    } // dropContentByObject
    
    /**
    * Drop column content from table related to $object
    *
    * @param ProjectDataObject $object
    * @return boolean
    */
    static function dropContentByObjectColumn(ApplicationDataObject $object, $column = '') {
    	return SearchableObjects::delete(array('`rel_object_manager` = ? AND `rel_object_id` = ? AND `column_name` = '. "'". $column . "'" , get_class($object->manager()), $object->getObjectId(), $column));
    } // dropContentByObject
    
    /**
    * Drop columns content from table related to $object
    *
    * @param ApplicationDataObject $object
    * @return boolean
    */
    static function dropContentByObjectColumns(ApplicationDataObject $object, $columns = array()) {
    	$columns_csv = "'" . implode("','",$columns) . "'";
    	
    	return SearchableObjects::delete(array('`rel_object_manager` = ? AND `rel_object_id` = ? AND `column_name` in ('. $columns_csv . ')' , get_class($object->manager()), $object->getObjectId()));
    } // dropContentByObject
    
  } // SearchableObjects 

?>