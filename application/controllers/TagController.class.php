<?php

/**
 * Tag controller
 *
 * @version 1.0
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class TagController extends ApplicationController {

	/**
	 * Construct the TagController
	 *
	 * @access public
	 * @param void
	 * @return TagController
	 */
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website');
	} // __construct

	/**
	 * Delete tag URL
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function delete_tag() {
		if (!logged_user()->isAdministrator()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$tag_name = array_var($_GET,'tag_name');
		$object_id = array_var($_GET,'object_id');
		$manager_class = array_var($_GET,'manager_class');
		$obj = get_object_by_manager_and_id($object_id, $manager_class);
		$obj->deleteTag($tag_name);
		$this->redirectToReferer('');
	}
	/**
	 * Show project objects tagged with specific tag
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function project_tag() {

		$tag = array_var($_GET, 'tag');
		if(trim($tag) == '') {
			flash_error(lang('tag dnx'));
			$this->redirectTo('project', 'tags');
		} // if

		$tagged_objects = active_or_personal_project()->getObjectsByTag($tag);
		$total_tagged_objects = Tags::countObjectsByTag($tag);
		if(is_array($tagged_objects)) {
			foreach($tagged_objects as $type => $objects) {
				if(is_array($objects)) $total_tagged_objects += count($objects);
			} // foreach
		} // if

		tpl_assign('tag', $tag);
		tpl_assign('tagged_objects', $tagged_objects);
		tpl_assign('total_tagged_objects', $total_tagged_objects);

	} // project_tag

	/**
	 * List all tags
	 *
	 */
	function list_tags() {
		ajx_current("empty");
		$order = array_var($_GET, 'order', 'count');
		$ts = array();
		$tags = Tags::getTagNames($order);
		$extra = array();
		$extra['tags'] = $tags;
		ajx_extra_data($extra);
	}
	
	/**
	 * Change the name of a tag. If another tag with the same name
	 * exists it will be merged with it.
	 *
	 */
	function rename_tag() {
		if (!logged_user()->isAdministrator()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		ajx_current("empty");
		$tag = array_var($_GET, 'tag');
		$new_tag = array_var($_GET, 'new_tag');
		if (is_null($tag)) {
			flash_error(lang("error must enter tag"));
			return;
		}
		if (is_null($new_tag)) {
			flash_error(lang("error must enter new tag"));
			return;
		}
		Tags::renameTag($tag, $new_tag);
		$this->redirectTo("tag", "list_tags");
	}
	
	function delete_tag_by_name() {
		if (!logged_user()->isAdministrator()) {
			flash_error(lang("no access permissions"));
			return;
		}
		ajx_current("empty");
		$tag = array_var($_GET, 'tag');
		if (is_null($tag)) {
			flash_error(lang("error must enter tag"));
			return;
		}
		Tags::deleteTagByName($tag);
		evt_add('tag delete', $tag);
		$this->redirectTo("tag", "list_tags");
	}
} // TagController

?>