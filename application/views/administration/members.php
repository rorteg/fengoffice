<?php
  set_page_title(lang('members'));
  
  if(User::canAdd(logged_user(), owner_company())) {
    add_page_action(lang('add user'), owner_company()->getAddUserUrl(), 'ico-add',null,null,true);
  } // if
?>

<div class="adminUsersList" style="height:100%;background-color:white">
  <div class="adminHeader">
  	<div class="adminTitle"><?php echo lang('users') . (config_option('max_users')?(' (' . Users::count() .' / ' .  config_option('max_users') . ')'):'') ?></div>
  </div>
  <div class="adminSeparator"></div>
  <div class="adminMainBlock">
  		<?php 
			$show_help_option = user_config_option('show_context_help'); 
			if ($show_help_option == 'always' || ($show_help_option == 'until_close')&& user_config_option('show_member_context_help', true, logged_user()->getId())) {?>
			<div id="membersPanelContextHelp" style="padding-left:7px;padding:15px;background-color:white;">
				<?php render_context_help($this, 'chelp members page','member'); ?>
			</div>
		<?php }?>
  <?php
		foreach ($users_by_company as $company_row){
			$company = $company_row['details'];
			$users = $company_row['users'];
			tpl_assign('users', $users);
			tpl_assign('company', $company);
		?>
<div style='padding-bottom:20px;max-width:700px'>
<div style="padding:10px;padding-bottom:13px;background-color:#D7E5F5">
	<h1 style="font-size:140%;font-weight:bold"><a class="internalLink" href="<?php echo $company->getCardUrl() ?>"><?php echo clean($company->getName()) ?></a></h1>
	<div style="float:right;" id="companypagination<?php echo $company->getId(); ?>"></div>
</div>
<div id="usersList" style="border:1px solid #DDD">

  <?php $this->includeTemplate(get_template_path('list_users', 'administration')); ?>
  </div></div>
  <?php } // foreach ?>
  
  </div>
 </div>
 <script type="text/javascript">
 og.userListPagination=function(page,compid,cantpages,element)
 	{ 
	 divs = element.parentNode.childNodes;	 
	 		for (i=1;i<=cantpages;i++){
		 		d = divs[i-1];
			 	d.className="pagination-user";
				elem = document.getElementById(i + '-' + compid + 'userspage');
				if(elem){
					elem.style.display = "none";					
				}
			 }
	 	element.className = "pagination-user-active";
		page = document.getElementById(page + '-' + compid + 'userspage');
		if (page){
		 	page.style.display = "";
		}

	};
 </script>
 