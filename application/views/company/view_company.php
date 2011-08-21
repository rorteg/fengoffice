<?php
	if (!$company->isTrashed()){
		if (User::canAdd(logged_user(), $company)) {
			add_page_action(lang('add user'), $company->getAddUserUrl(), 'ico-add');
		} // if
		if (Contact::canAdd(logged_user(), active_or_personal_project())) {
			add_page_action(lang('add contact'), $company->getAddContactUrl(), 'ico-add');
		} // if
		if ($company->canEdit(logged_user())) {
			add_page_action(lang('edit company'), $company->getEditUrl(), 'ico-edit',null, null, true);
			add_page_action(lang('edit company logo'), $company->getEditLogoUrl(), 'ico-picture', null, null, true);
			if (!$company->isOwner()) {
				add_page_action(lang('permissions'), $company->getUpdatePermissionsUrl(), 'ico-permissions', null, null, true);
			} // if
			if (!$company->isArchived()) {
				add_page_action(lang('archive'), "javascript:if(confirm(lang('confirm archive object'))) og.openLink('" . $company->getArchiveUrl() ."');", 'ico-archive-obj');
			} else {
				add_page_action(lang('unarchive'), "javascript:if(confirm(lang('confirm unarchive object'))) og.openLink('" . $company->getUnarchiveUrl() ."');", 'ico-unarchive-obj');
			}
		} // if
	}

	if ($company->canDelete(logged_user())){
		if ($company->isTrashed()) {
			add_page_action(lang('restore from trash'), "javascript:if(confirm(lang('confirm restore objects'))) og.openLink('" . $company->getUntrashUrl() ."');", 'ico-restore',null,null,true);
			add_page_action(lang('delete permanently'), "javascript:if(confirm(lang('confirm delete permanently company'))) og.openLink('" . $company->getDeletePermanentlyUrl() ."');", 'ico-delete',null,null,true);
		} else {
			add_page_action(lang('move to trash'), "javascript:if(confirm(lang('confirm move to trash company'))) og.openLink('" . $company->getTrashUrl() ."');", 'ico-trash',null,null,true);
		}
	}
  

?>



<div style="padding:7px">
<div class="company">
<?php
	if(isset($title) && $title != '')
		tpl_assign('title', clean($title));
	tpl_assign('show_linked_objects', false);
	tpl_assign('object', $company);
	tpl_assign('iconclass', $company->isTrashed() ? 'ico-large-company-trashed' : ($company->isArchived() ? 'ico-large-company-archived' : 'ico-large-company'));
	tpl_assign("content_template", array('company_content', 'company'));
	
	$this->includeTemplate(get_template_path('view', 'co'));
?>
</div>
</div>

