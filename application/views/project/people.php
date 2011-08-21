<?php

  set_page_title(lang('people'));
  project_tabbed_navigation(PROJECT_TAB_PEOPLE);
  project_crumbs(lang('people'));
  
  if(active_project()->canChangePermissions(logged_user())) {
    add_page_action(lang('permissions'), get_url('project', 'permissions'), 'ico-properties');
  } // if
  
  //add_stylesheet_to_page('project/people.css');

?>
<?php if(isset($project_companies) && is_array($project_companies) && count($project_companies)) { ?>
<div id="people">
<?php foreach($project_companies as $company) { ?>
  <div class="projectCompany">
  <fieldset> <legend><?php echo clean($company->getName()) ?></legend>
    <div class="projectCompanyLogo"><img src="<?php echo $company->getLogoUrl() ?>" alt="<?php echo clean($company->getName()) ?>" /></div>
    <div class="projectCompanyMeta">
      <div class="projectCompanyInfo">
        <div class="projectCompanyName"><a class="internalLink" href="<?php echo $company->getCardUrl() ?>" class="companyName"><?php echo clean($company->getName()) ?></a></div>
<?php if($company->hasAddress()) { ?>
        <div class="projectCompanyAddress">
          <?php echo clean($company->getAddress()) ?>
<?php if(trim($company->getAddress2())) { ?>
          <br /><?php echo clean($company->getAddress2()) ?>
<?php } // if ?>
          <br /><?php echo clean($company->getCity()) ?>, <?php echo clean($company->getState()) ?> <?php echo clean($company->getZipcode()) ?>
        </div>
<?php } // if ?>
<?php if($company->hasHomepage()) { ?>
        <div class="projectCompanyHomepage"><a class="internalLink" href="<?php echo $company->getHomepage() ?>"><?php echo $company->getHomepage() ?></a></div>
<?php } // if ?>
<?php
  $options = array();
  if($company->canEdit(logged_user())) $options[] = lang('edit company data', $company->getEditUrl());
  if(active_project()->canRemoveCompanyFromProject(logged_user(), $company)) $options[] = '<a class="internalLink" href="' . active_project()->getRemoveCompanyUrl($company) . '" onclick="return confirm(\'' . escape_single_quotes(lang('confirm remove company from project')) . '\')">' . lang('remove company from project') . '</a>';
?>
<?php if(count($options)) { ?>
        <div class="projectCompanyOptions"><?php echo implode(' | ', $options) ?></div>
<?php } // if ?>
      </div>
        
<?php if(is_array($users = $company->getUsersOnProject(active_project())) && count($users)) { ?>
      <div class="projectCompanyUsers">
        <table>
          <tr>
            <th colspan="2"><?php echo lang('company users involved in project', clean($company->getName()), clean(active_project()->getName())) ?>:</th>
          </tr>
<?php foreach($users as $user) { ?>
          <tr>
            <td style="width: 200px">
              <div class="projectUserDisplayName"><a class="internalLink" href="<?php echo $user->getCardUrl() ?>"><?php echo $user->getDisplayName() ?></a></div>
<?php if($user->hasTitle()) { ?>
              <div class="projectUserTitle"><?php echo clean($user->getTitle()) ?></div>
<?php } // if ?>
              <div class="projectUserEmail"><a <?php echo logged_user()->hasMailAccounts() ? 'href="' . get_url('mail', 'add_mail', array('to' => clean($user->getEmail()))) . '"' : 'target="_self" href="mailto:' . clean($user->getEmail()) . '"' ?>><?php echo $user->getEmail() ?></a></div>
            </td>
<?php if(active_project()->canRemoveUserFromProject(logged_user(), $user)) { ?>
            <td><a class="internalLink" href="<?php echo active_project()->getRemoveUserUrl($user) ?>" onclick="return confirm('<?php echo escape_single_quotes(lang('confirm remove user from project')) ?>')"><?php echo lang('remove user from project') ?></a></td>
<?php } // if ?>
          </tr>
<?php } // foreach ?>
        </table>
      </div>
<?php } // if ?>
    </div>

    <div class="clear"></div>
	</fieldset>
  </div>
<?php } // foreach?>
</div>
<?php } // if ?>

<?php if(active_project()->canChangePermissions(logged_user())) { ?>
<div class="hint"><?php echo lang('project permissions form hint', get_url('project', 'permissions')) ?></div>
<?php } // if ?>