<?php 
	//set_page_title(lang('contact card of').' '.$contact->getDisplayName());
	if (!$contact->isTrashed()){
		if($contact->canEdit(logged_user())) {
			add_page_action(lang('edit contact'), $contact->getEditUrl(), 'ico-edit', null, null, true);
			add_page_action(lang('edit picture'), $contact->getUpdatePictureUrl(), 'ico-picture', null, null, true);
			add_page_action(lang('assign to project'), $contact->getAssignToProjectUrl(), 'ico-workspace',null, null, true);
		}
	}
	if ($contact->canDelete(logged_user())) {
		if ($contact->isTrashed()) {
			add_page_action(lang('restore from trash'), "javascript:if(confirm(lang('confirm restore objects'))) og.openLink('" . $contact->getUntrashUrl() ."');", 'ico-restore',null, null, true);
			add_page_action(lang('delete permanently'), "javascript:if(confirm(lang('confirm delete permanently'))) og.openLink('" . $contact->getDeletePermanentlyUrl() ."');", 'ico-delete',null, null, true);
		} else {
			add_page_action(lang('move to trash'), "javascript:if(confirm(lang('confirm move to trash'))) og.openLink('" . $contact->getTrashUrl() ."');", 'ico-trash',null, null, true);
		}
	} // if
	if (!$contact->isTrashed()) {
		if (can_manage_security(logged_user())) {
			if (! $contact->getUserId() || $contact->getUserId() == 0){
				add_page_action(lang('create user from contact'), $contact->getCreateUserUrl() , 'ico-user-old');
			}
		}
		if ($contact->canEdit(logged_user())) {
			if (!$contact->isArchived()) {
				add_page_action(lang('archive'), "javascript:if(confirm(lang('confirm archive object'))) og.openLink('" . $contact->getArchiveUrl() ."');", 'ico-archive-obj');
			} else {
				add_page_action(lang('unarchive'), "javascript:if(confirm(lang('confirm unarchive object'))) og.openLink('" . $contact->getUnarchiveUrl() ."');", 'ico-unarchive-obj');
			}
		}
	}
?>
   
<div style="padding:7px">
<div class="contact">

<?php
	if ($contact->hasPicture()){
		$image = '<div class="cardIcon">';
		
		if ($contact->canEdit(logged_user()))
			$image .= '<a class="internalLink" href="' .
			$contact->getUpdatePictureUrl() .'" title="' . lang('edit picture') . '">';
		
		$image .= '<img src="' . $contact->getPictureUrl() .'" alt="'. clean($contact->getDisplayName()) .' picture" />';
		
		if ($contact->canEdit(logged_user()))
			$image .= '</a>';
		
		$image .= '</div>';
		
		tpl_assign("image",$image);
	}
	$description = "";
	$company = $contact->getCompany();
	if ($company instanceof Company)
		$description = '<a class="internalLink coViewAction ico-company" href="' . $company->getCardUrl() . '">' . clean($company->getName()) . '</a>';
	
	if ($contact->getJobTitle() != ''){
		if($description != '')
			$description .= ' - ';
		$description .= clean($contact->getJobTitle());
	}
	
	if ($contact->getDepartment() != ''){
		if($description != ''){
			if ($contact->getJobTitle() != '')
				$description .= ', ';
			else
				$description .= ' - ';
		}
		$description .= clean($contact->getDepartment());
	}
	
	$userLink = '';
	if ($contact->getUserId() > 0 && $contact->getUser()){
		if($description != '')
			$description .= '<br/>';
		$description .= '<a class="internalLink coViewAction ico-user" href="' . $contact->getUser()->getCardUrl() . '" title="' . lang('contact linked to user', clean($contact->getUser()->getUsername())) . '">' . clean($contact->getUser()->getUsername()) . '</a>';
	}
		
    
	tpl_assign("description", $description);
	tpl_assign("content_template", array('card_content', 'contact'));
	tpl_assign("object", $contact);
	tpl_assign("title", lang('contact') . ': ' . clean($contact->getDisplayName()));
	tpl_assign('iconclass', $contact->isTrashed()? 'ico-large-contact-trashed' :  ($contact->isArchived() ? 'ico-large-contact-archived' : 'ico-large-contact'));
		
  	$this->includeTemplate(get_template_path('view', 'co'));
  	
  	clear_page_actions();
?>

</div>
</div>