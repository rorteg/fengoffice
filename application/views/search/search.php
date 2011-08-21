<?php
  set_page_title(lang('search results'));
  $genid = gen_id();
  $search_all_projects = array_var($_GET, 'search_all_projects', 'false');
  $has_search_results = isset($search_results) && is_array($search_results) && count($search_results);
  
  //TODO implement jumping to a specific item if it is the only one returned in the search? possibly just check for UID.
  //if ($has_search_results && count($search_results) == 1 && count($search_results[0]) == 1){
  //}
?>
<div id="<?php echo $genid; ?>Search" style='height:100%;background-color:white'>
<div style='background-color:white'>
<div id="searchForm">
  
  <div id="headerDiv" class="searchDescription">
<?php if (array_var($_GET, 'search_all_projects') != 'true' && active_project() instanceof Project) 
		echo lang("search for in project", clean($search_string), clean(active_project()->getName()));
	else
		echo lang("search for", clean($search_string)); 
	if (array_var($_GET, 'search_all_projects') != 'true' && active_project() instanceof Project) { ?>
	<br/><a class="internalLink" href="<?php echo get_url('search','search',array("search_for" => array_var($_GET, 'search_for'), "search_all_projects" => "true" )) ?>"><?php echo lang('search in all workspaces') ?></a>
<?php } //if ?>
</div>
</div>



<div style="padding-left:10px;padding-right:10px"><?php 

if($has_search_results) {
	foreach($search_results as $search_result) { 
		$alt = false;
		$pagination = $search_result["pagination"];?>
	<div class="searchGroup">
	<table width="100%"><tr><td align=center>
	<div class="searchHeader">
		<table width="100%"><tr><td><a class="coViewAction ico-<?php echo $search_result["icontype"]?> internalLink searchGroupTitle" href='<?php echo get_url('search', 'searchbytype', 
		array('manager' => $search_result["manager"], 'search_for' => $search_string, 'search_all_projects' => $search_all_projects)); ?>'><?php echo $search_result["type"]?></a></td>
		<td align=right><?php if (isset($enable_pagination) && $pagination->getTotalItems() > $pagination->getItemsPerPage()) {?>
			<?php echo advanced_pagination($pagination, get_url('search', 
				'searchbytype',
					array('active_project' => (active_project())?active_project()->getId():'',
					'search_for' => $search_string, 'manager' => $search_result["manager"],
					'page' => '#PAGE#', 'search_all_projects' => $search_all_projects)), 'search_pagination'); ?>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<?php echo lang('search result description short', $pagination->getStartItemNumber(),$pagination->getEndItemNumber() , $pagination->getTotalItems(), clean($search_string)) ?>
		<?php } // if ?>
		<?php if (!isset($enable_pagination) && $pagination->countItemsOnPage(1) < $pagination->getTotalItems()) { ?>
			<a class="internalLink" href='<?php echo get_url('search', 'searchbytype', 
			array('manager' => $search_result["manager"], 'search_for' => $search_string, 'search_all_projects' => $search_all_projects)); ?>'>
			<?php echo lang('more results', $pagination->getTotalItems() - $pagination->countItemsOnPage(1)) ?></a>
		<?php } else echo "" ?>
		</td></tr>
		</table>
	</div>
	<div class="searchResults">
	<table style="width:100%">
	<?php foreach(array_reverse($search_result['result']) as $srrow) {
		$alt = !$alt;
		$object = $srrow['object'];?>
		<tr style="vertical-align:middle" class="<?php echo $alt? "searchAltRow" : 'searchRow' ?>">
			<td style="padding:6px" width=36>
		<?php if ($object instanceof ProjectFile || $object instanceof ProjectFileRevision) {?>
			<img style="width:36px" src="<?php echo $object->getTypeIconUrl() ?>"/>
		<?php } ?>
		<?php if ($object instanceof Contacts) {?>
			<img style="width:36px" src="<?php echo $object->getPictureUrl() ?>"/>
		<?php } ?>
		<?php if ($object instanceof User) {?>
			<img style="width:36px" src="<?php echo $object->getAvatarUrl() ?>"/>
		<?php } ?></td>
		<td style="padding:6px;vertical-align:middle"><?php if ($object instanceof ProjectDataObject){
			$dws = $object->getWorkspaces();
			$projectLinks = array();
			foreach ($dws as $ws) {
				$projectLinks[] = $ws->getId();
			echo '<span style="padding-right:5px"><span class="project-replace">' . implode(',',$projectLinks)  . '</span></span>';
		}}?><?php if ($search_result["manager"] == 'Projects') {?>
			<span class="project-replace" onclick="Ext.getCmp('tabs-panel').setActiveTab('overview-panel')"><?php echo $object->getId() ?></span>
		<?php } else { 
			$object_name = $object->getObjectName();
			$context_on_name = SearchableObjects::getContext($object_name,$search_string);
			if ($context_on_name != '') {
				$object_name = $context_on_name;
			} else {
				$object_name = clean($object_name);
			}
			if ($object instanceof MailContent && $object->getHasAttachments()) {
				$linkIcon = 'link-ico ico-attachment';
			} else {
				$linkIcon = '';
			} ?>
			<a class="<?php echo $linkIcon ?>" href="<?php echo $object->getObjectUrl() ?>" style="font-size:120%;"><?php echo $object_name ?></a>
		<?php } // if ?>
		</td>
		<td style="padding:6px;vertical-align:middle" align=right><?php
			if ($object instanceof MailContent) {
				echo lang("created by on short", $object->getSenderUrl(), clean($object->getSenderName()), format_descriptive_date($object->getReceivedDate()));
			} else {
				echo lang("modified by on short", $object->getUpdatedByCardUrl(), ($object->getUpdatedBy() instanceof User ? clean($object->getUpdatedByDisplayName()) : clean($object->getCreatedByDisplayName())), format_descriptive_date($object->getObjectUpdateTime()));
			}
		?></td>
		</tr>
		<?php foreach ($srrow['context'] as $context) {  // Draw context
			if ($context['context'] != '' 
				&& $context['column_name'] != 'title' 
				&& $context['column_name'] != 'name' 
				&& $context['column_name'] != 'firstname' 
				&& $context['column_name'] != 'lastname' 
				&& $context['column_name'] != 'subject' 
				&& $context['column_name'] != 'filename') {?>
		<tr style="vertical-align:middle" class="<?php echo $alt? "searchAltRow" : 'searchRow' ?>">
		<td></td><td colspan=2 style="padding:6px;padding-top:0px">
			<b><?php 
				$colname = $context['column_name'];
				
				//Check for custom properties
				if (substr($colname,0,8) == 'property'){
					$property_id = trim(substr($colname,8));
					if (is_numeric($property_id)){
						$prop = ObjectProperties::findById($property_id);
						if ($prop instanceof ObjectProperty)
							echo $prop->getPropertyName();
						else
							break;
					} else
						break;
				} else {					
					if (Localization::instance()->lang_exists('field ' . $object->getObjectManagerName() . ' ' . $context['column_name']))
						echo lang('field ' . $object->getObjectManagerName() . ' ' . $context['column_name']);
					else
						echo clean($context['column_name']);
				}
				?>: </b>
			<span class='desc'><?php 
			if ($object instanceof ProjectFileRevision) 
				echo undo_htmlspecialchars($context['context']);
			else
				echo $context['context'] ?></span></td>
		</tr>
		<?php } // if
		} // foreach context ?>
	<?php } // foreach row ?>
	</table>
	</div>
	</td></tr></table>
	</div>
 <?php } // foreach group?>

<?php } else { ?>
<div id="noResultsFoundDiv" class="searchDescription" style="font-weight:normal;font-size:140%;padding-top:30px; padding-bottom:30px">
<?php echo lang('no search result for', clean($search_string)) ?>
</div>
<?php } // if ?>

<div style="width:100%;text-align:center;color:#888;padding-bottom:20px">
	<br/>
	<p><?php echo lang('time used in search', sprintf("%01.2f",$time)) ?></p>
</div>
</div>
</div>
</div>
<script>
og.showWsPaths('<?php echo $genid; ?>Search',true);
</script>