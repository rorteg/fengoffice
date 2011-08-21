<?php

// Set page title and set crumbs to index
if($user->canUpdateProfile(logged_user())) {
	add_page_action(lang('update profile'),$user->getEditProfileUrl(), 'ico-edit', null, null, true);
	add_page_action(lang('update avatar'), $user->getUpdateAvatarUrl(), 'ico-picture', null, null, true);
	add_page_action(lang('change password'), $user->getEditPasswordUrl(), 'ico-password', null, null, true);
	$contact = $user->getContact();
	if (can_manage_contacts(logged_user()) && !$contact instanceof Contact) {
		add_page_action(lang('create contact from user'), "javascript:if(confirm('" . lang('confirm create contact from user') . "')) og.openLink('" . $user->getCreateContactFromUserUrl() ."');", 'ico-add');
	}
} // if
if($user->getId() == logged_user()->getId()){
	add_page_action(lang('edit preferences'), $user->getEditPreferencesUrl(), 'ico-administration', null, null, true);
}
if($user->canUpdatePermissions(logged_user())) {
	add_page_action(lang('permissions'), $user->getUpdatePermissionsUrl(), 'ico-permissions', null, null, true);
} // if

?>



<div style="padding: 7px">
<div class="user"><?php

$description = "";
if (isset($contact)){
	if ($contact){
		if($description != '') $description .= '<br/>';
		$description .= '<div style="margin-top:3px;"><a class="internalLink coViewAction ico-contact" href="' . $contact->getCardUrl() . '" title="' . lang('contact linked to user', clean($contact->getDisplayName())) . '">' . clean($contact->getDisplayName()) . '</a></div>';
	}
}
tpl_assign('description', $description);
tpl_assign('title', clean($user->getDisplayName()));
tpl_assign('show_linked_objects', false);
tpl_assign('object', $user);
tpl_assign('iconclass', 'ico-large-user');
tpl_assign("content_template", array('user_card', 'user'));

$this->includeTemplate(get_template_path('view', 'co'));
?></div>
</div>
