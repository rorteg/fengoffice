<?php

/**
 * Tags, generated on Wed, 05 Apr 2006 06:44:54 +0200 by
 * DataObject generation tool
 *
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class Tags extends BaseTags {

	/**
	 * Returns a list of ids of objects which have tags. Works as a performance boost when most objects don't have tags.
	 *
	 * @access public
	 * @param ApplicationDataObject $object
	 * @param string $manager_class
	 * @return array
	 */
	function getObjectsWithTags($object_ids, $manager_class) {
		$tags = self::findAll(array(
        'conditions' => array('`rel_object_id` IN (' . implode(',', $object_ids) . ') AND `rel_object_manager` = ?', $manager_class)
        )); // findAll
        $result = array();
        if (is_array($tags) && count($tags) > 0){
        	foreach ($tags as $tag){
        		$result[$tag->getRelObjectId()] = true;
        	}
        }
        return $result;
	} // getTagsByObject

	/**
	 * Return tags for specific object
	 *
	 * @access public
	 * @param ApplicationDataObject $object
	 * @param string $manager_class
	 * @return array
	 */
	function getTagsByObject(ApplicationDataObject $object, $manager_class) {
		return self::findAll(array(
        'conditions' => array('`rel_object_id` = ? AND `rel_object_manager` = ?', $object->getObjectId(), get_class($object->manager())),
        'order' => '`tag`'
        )); // findAll
	} // getTagsByObject

	/**
	 * Return tag names as array for specific object
	 *
	 * @access public
	 * @param ApplicationDataObject $object
	 * @param string $manager_class
	 * @return array
	 */
	function getTagNamesByObject(ApplicationDataObject $object) {
		$rows = DB::executeAll('SELECT distinct `tag` FROM ' .  self::instance()->getTableName(true) . ' WHERE `rel_object_id` = ? AND `rel_object_manager` = ? ORDER BY `tag`', $object->getId(), get_class($object->manager()));

		if(!is_array($rows)) return array();

		$tags = array();
		foreach($rows as $row) $tags[] = $row['tag'];
		return $tags;
	} // getTagNamesByObject
	
	/**
	 * Return tags for specific object
	 *
	 * @access public
	 * @param ApplicationDataObject $object
	 * @param string $manager_class
	 * @return array
	 */
	function getTagsByObjectIds($object_ids, $object_manager) {
		return self::findAll(array(
        'conditions' => array('`rel_object_id` IN (' . $object_ids . ') AND `rel_object_manager` = ?', $object_manager),
        'order' => '`tag` ASC'
        )); // findAll
	} // getTagsByObject

	/**
	 * Return tag names as array for specific object
	 *
	 * @access public
	 * @param ApplicationDataObject $object
	 * @param string $manager_class
	 * @return array
	 */
	function getTagNamesByObjectIds($object_ids, $object_manager) {
		$rows = DB::executeAll('SELECT distinct `tag` FROM ' .  self::instance()->getTableName(true) . ' WHERE `rel_object_id` IN (' . $object_ids . ') AND `rel_object_manager` = ? ORDER BY `tag`', $object_manager);

		if(!is_array($rows)) return array();

		$tags = array();
		foreach($rows as $row) $tags[] = $row['tag'];
		return $tags;
	} // getTagNamesByObject

	/**
	 * Return tag names as array ordered by occurrence
	 * $order_by sort order, possible values are 'name' and 'count'
	 *
	 * @access public
	 * @return array
	 */
	function getTagNames($order_by = 'count') {
		$oc = new ObjectController();
		$dqs = $oc->getDashboardObjectQueries();
		$conditions = "";
		foreach ($dqs as $type => $q) {
			if (substr($type, -8) == 'Comments') continue;
			$q = substr($q, strpos($q, "FROM"));
			$q = "`rel_object_id` IN (SELECT `id` $q) AND `rel_object_manager` = '$type'";
			if ($conditions) $conditions .= " \n\nOR\n\n ";
			$conditions .= $q;
		}
		$query = '';
		switch ($order_by){
			case 'name':
				$query = 'SELECT DISTINCT `tag` as `name`  FROM '
					. self::instance()->getTableName(true)
					. ' WHERE' . $conditions . ' GROUP BY `tag` ORDER BY  `tag` ';
				break ;
			case 'count':
			default:
				$query = 'SELECT DISTINCT `tag` as `name`, count(`tag`) `count` FROM '
					. self::instance()->getTableName(true)
					. ' WHERE' . $conditions
					. ' GROUP BY `tag` ORDER BY `count` DESC , `tag`' ;
		}
		$rows = DB::executeAll($query);
		if(!is_array($rows)) return array();
		return $rows;
	} // getTagNames

	/**
	 * Return tag names as array for project file id
	 *
	 * @access public
	 * @param int $fileId
	 * @return array
	 */
	function getTagNamesByFileId( $fileId) {
		$rows = DB::executeAll('SELECT `tag` FROM ' .  self::instance()->getTableName(true) . ' WHERE `rel_object_id` = ? AND `rel_object_manager` =\'ProjectFiles\' ORDER BY `tag`', $fileId);

		if(!is_array($rows)) return null;

		$tags = array();
		foreach($rows as $row) $tags[] = $row['tag'];
		return $tags;
	} // getTagNamesByFileId

	/**
	 * Clear tags of specific object
	 *
	 * @access public
	 * @param ProjectDataObject $object
	 * @param string $manager_class
	 * @return boolean
	 */
	function clearObjectTags(ProjectDataobject $object, $manager_class) {
		$tags = $object->getTags(); // save the tags list
		if(is_array($tags)) {
			foreach($tags as $tag) $tag->delete();
		} // if
	} // clearObjectTags

	/**
	 * Delete a tag for a project object
	 *
	 * @access public
	 * @param tag to delete
	 * @param int fileID
	 * @param Project $project
	 * @return null
	 */

	function deleteObjectTag($tag_name, $object_id, $manager_class) {
		$obj = get_object_by_manager_and_id($object_id,$manager_class);
		if (!$obj instanceof ApplicationDataObject) return true;
		return self::deleteByTagNameAndObject($tag_name, $obj);
	} //  deleteObjectTag


	/**
	 * Add tags for an object
	 *
	 * @access public
	 * @param string $tag_name tag to be added
	 * @param ProjectDataObject $obj
	 * @return null
	 */
	function addObjectTag($tag_name, ApplicationDataObject $obj) {
		$tag_name = trim($tag_name);
		if (!(isset($obj) && $obj && ($obj instanceof ApplicationDataObject) ) )
		return true;
		$prevTags=Tags::getTagNamesByObject($obj);
		if($prevTags){
			foreach($prevTags as $tag_iter) {
				if(strcmp($tag_name,$tag_iter)==0)
				return true; //tag already added
			}
		}
		if(strcmp($tag_name , '')) {
			$exists = true;
			if (Tags::countObjectsByTag($tag_name) <= 0) {
				$exists = false;
			}
			$tag = new Tag();

			$tag->setTag($tag_name);
			$tag->setRelObjectId($obj->getId());
			$tag->setRelObjectManager(get_class($obj->manager()));
			$tag->setIsPrivate(false);
			$tag->save();
			if (!$exists) {
				$real_tag = self::findById($tag->getId());
				evt_add("tag added", array("name"=>$real_tag->getTag()));
			}
		} // if
		 
		return true;
	} //  addObjectTag

	/**
	 * Set tags for specific object
	 *
	 * @access public
	 * @param array $tags Array of tags... Can be NULL or empty
	 * @param ProjectDataObject $object
	 * @param string $manager_class
	 * @return null
	 */
	function setObjectTags($tags, ProjectDataObject $object, $manager_class) {
		self::clearObjectTags($object, $manager_class);
		if(is_array($tags) && count($tags)) {
			foreach($tags as $tag_name) {

				if(trim($tag_name) <> '') {
					$tag = new Tag();

					$tag->setTag($tag_name);
					$tag->setRelObjectId($object->getId());
					$tag->setRelObjectManager($manager_class);
					$tag->setIsPrivate($object->isPrivate());
	
					$tag->save();
					evt_add("tag added", array("name"=>$tag_name));
				} // if

			} // foreach
		} // if
		return true;
	} // setObjectTags

	/**
	 * Return array of project objects. Optional filters are by tag and / or by object class
	 *
	 * @access public
	 * @param Project $project
	 * @param string $tag Return objects that are tagged with specific tag
	 * @param string $class Return only object that match specific class (manager class name)
	 * @param boolean $exclude_private Exclude private objects from listing
	 * @return array
	 */
	function getObjects(Project $project, $tag = null, $class = null, $exclude_private = false) {
		$conditions = '1=1';
		if(trim($tag) <> '') $conditions .= ' AND `tag` = ' . DB::escape($tag);
		if(trim($class) <> '') $conditions .= ' AND `rel_object_manager` = ' .  DB::escape($class);
		if($exclude_private) $conditions .= ' AND `is_private` = ' . DB::escape(0);

		$tags = self::findAll(array(
        'conditions' => $conditions,
        'order_by' => '`created_on`'
        )); // findById

        if(!is_array($tags)) return null;

        $objects = array();
        foreach($tags as $tag_object) {
        	$object = $tag_object->getObject();
        	if($object instanceof ProjectDataObject) $objects[] = $object;
        } // foreach

        return count($objects) ? $objects : null;
	} // getObjects

	/**
	 * Returns number of objects tagged with specific tag
	 *
	 * @access public
	 * @param string $tag Tag name
	 * @param Project $project Only objects that belong to this project
	 * @param boolean $exclude_private Exclude private objects from listing
	 * @return integer
	 */
	function countObjectsByTag($tag, $exclude_private = false) {
		if($exclude_private) {
			$row = DB::executeOne("SELECT COUNT(`id`) AS 'row_count' FROM " . self::instance()->getTableName(true) . " WHERE `tag` = ? AND `is_private` = ?", $tag, 0);
		} else {
			$row = DB::executeOne("SELECT COUNT(`id`) AS 'row_count' FROM " . self::instance()->getTableName(true) . " WHERE `tag` = ?", $tag);
		} // if
		return array_var($row, 'row_count', 0);
	} // countObjectsByTag

	/**
	 * Changes all tags named $tag to $new_tag.
	 *
	 * @param string $tag
	 * @param string $new_tag
	 */
	function renameTag($tag, $new_tag) {
		$sql = "UPDATE " . self::instance()->getTableName(true) .
			" SET `tag` = " . DB::escape($new_tag) .
			" WHERE `tag` = " . DB::escape($tag);
			// TODO: only move tags for objects the user can write.
			// There's no easy way to check that now, because you can't know
			// the project of an object from the data in the tags table, but
			// it'll be possible when all objects can have multiple workspaces.
		DB::execute($sql);
	}
	
	function deleteTagByName($tag) {
		self::delete(array('`tag` = ?', $tag));
	}
	
	function deleteByTagNameAndObject($tagname, $object) {
		return self::delete("`tag` = " . DB::escape($tagname) . " AND `rel_object_id` = " . DB::escape($object->getId()) . " AND `rel_object_manager` = " . DB::escape(get_class($object->manager())));
	}
} // Tags

?>