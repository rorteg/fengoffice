<?php

  set_page_title(lang('update permissions'));
  administration_tabbed_navigation(ADMINISTRATION_TAB_CLIENTS);
  administration_crumbs(array(
    array(lang('clients'), get_url('administration', 'clients')),
    array($company->getName(), $company->getViewUrl()),
    array(lang('update permissions'))
  ));
  $genid = gen_id();
  
?>
<?php if(isset($projects) && is_array($projects) && count($projects)) { ?>
<div id="companyPermissions" style="padding: 10px">
	<table><tr><td style="padding: 10px">
		<form class="internalForm" action="<?php echo $company->getUpdatePermissionsUrl() ?>" method="post">
			<?php echo select_workspaces("ws_ids", $projects, $company->getProjects(), $genid) ?>
			<input type="hidden" name="submitted" value="submitted" />
			<?php echo submit_button(lang('update permissions')) ?>
		</form>
	</td><td style="padding: 10px">
		<div class="hint">
			<div class="header"><?php echo lang('hint') ?></div>
			<div class="content"><?php echo lang('update company permissions hint') ?></div>
		</div>
	</td></tr></table>
</div>
<?php } // if ?>