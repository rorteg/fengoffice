<?php

  set_page_title(lang('search results'));
  project_tabbed_navigation();
  project_crumbs(lang('search results'));
  //add_stylesheet_to_page('project/search_results.css');

?>
<div id="searchForm">
  <form class="internalForm" action="<?php echo get_url('project','search') ?>" method="get">
    <?php echo input_field('search_for', array_var($_GET, 'search_for')) ?>
    <input type="hidden" name="c" value="project" />
    <input type="hidden" name="a" value="search" />
    <input type="hidden" name="active_project" value="<?php echo (active_project())?active_project()->getId():'' ?>" />
    <?php echo submit_button(lang('search')) ?>
  </form>
</div>

<?php if(isset($search_results) && is_array($search_results) && count($search_results)) { ?>
<p><?php echo lang('search result description', $pagination->countItemsOnPage($current_page), $pagination->getTotalItems(), clean($search_string)) ?>:</p>
<ul>
<?php foreach($search_results as $search_result) { ?>
  <li><?php echo clean($search_result->getObjectTypeName()) ?>: <a class="internalLink" href="<?php echo $search_result->getObjectUrl() ?>"><?php echo clean($search_result->getObjectName()) ?></a></li>
<?php } // foreach ?>
</ul>

<?php if(isset($pagination) && ($pagination instanceof DataPagination)) { ?>
<?php echo advanced_pagination($pagination, get_url('project', 
			'search',
				array('active_project' => (active_project())?active_project()->getId():'',
				'search_for' => $search_string,
				'page' => (integer) '#PAGE#' > 0 ? (integer) '#PAGE#' : 1 ))); ?>
<?php } // if ?>

<?php } else { ?>
<p><?php echo lang('no search result for', $search_string) ?></p>
<?php } // if ?>

<p><?php echo $time ?></p>