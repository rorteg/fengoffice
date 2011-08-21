
	<!-- permissions -->
		<?php $field_tab = 30; ?>
		<?php if (isset($companies) && is_array($companies) && count($companies)) { ?>
			<div id="projectCompaniesContainer"><div id="projectCompanies">
			<?php foreach ($companies as $company) { ?>
				<?php if ($company->countUsers() > 0) { ?>
				<fieldset>
				<legend><?php echo clean($company->getName()) ?></legend>
					<div class="projectCompany" style="border:0">
					<div class="projectCompanyLogo"><img src="<?php echo $company->getLogoUrl() ?>" alt="<?php echo clean($company->getName()) ?>" /></div>
					<div class="projectCompanyMeta">
						<div class="projectCompanyTitle">
					<?php //if($company->isOwner()) { ?>
						<!-- <label><?php //echo clean($compangetName()) ?></label>-->
							<!-- input type="hidden" name="project_company_<?php echo $company->getId() ?>" value="checked" / -->
					<?php //} else {
						$has_checked_users = false;
						foreach ($company->getUsers() as $user) {
							if ($user->isProjectUser($project)) {
								$has_checked_users = true;
								break;
							}
						}
						echo checkbox_field('project_company_' . $company->getId(), $has_checked_users, array('id' => $genid . 'project_company_' . $company->getId(), 'tabindex' => $field_tab++, 'onclick' => "App.modules.updatePermissionsForm.companyCheckboxClick(" . $company->getId() . ",'".$genid."')")) ?> <label for="<?php echo 'project_company_' . $company->getId() ?>" class="checkbox" onclick="og.showHide('<?php echo $genid ?>project_company_users_<?php echo $company->getId() ?>')"><?php echo clean($company->getName()) ?></label>
					<?php //} // if ?>
						</div>
						<div class="projectCompanyUsers" id="<?php echo $genid ?>project_company_users_<?php echo $company->getId() ?>">
							<table class="blank">
					<?php if ($users = $company->getUsers()) { ?>
<?php						 foreach ($users as $user) { ?>
								<tr class="user">
									<td>
								<?php echo checkbox_field('project_user_' . $user->getId(), $user->isProjectUser($project), array('id' => $genid . 'project_user_' . $user->getId(), 'tabindex' => $field_tab++, 'onclick' => "App.modules.updatePermissionsForm.userCheckboxClick(" . $user->getId() . ", " . $company->getId() . ",'".$genid."')")) ?> <label class="checkbox" for="<?php echo 'project_user_' . $user->getId() ?>" onclick="og.showHide('<?php echo $genid ."user_".$user->getId() ?>_permissions')"><?php echo clean($user->getDisplayName()) ?></label>
<?php //							 } // if ?>
<?php							 if($user->isAdministrator()) {?> 
										<span class="desc">(<?php echo lang('administrator') ?>)</span>
<?php							 } // if ?>
									</td>
									<td>
							<?php //if(!$company->isOwner()) { ?>
										<div class="projectUserPermissions" id="<?php echo $genid ."user_".$user->getId() ?>_permissions">
										<div><?php echo checkbox_field('project_user_' . $user->getId() . '_all', $user->hasAllProjectPermissions($project), array('id' => $genid . 'project_user_' . $user->getId() . '_all', 'tabindex' => $field_tab++, 'onclick' => "App.modules.updatePermissionsForm.userPermissionAllCheckboxClick(" . $user->getId() . ",'".$genid."')")) ?> <label for="<?php echo 'project_user_' . $user->getId() . '_all' ?>" class="checkbox" style="font-weight: bolder"><?php echo lang('all permissions') ?></label></div>
								<?php foreach ($permissions as $permission_id => $permission_text) { ?>						
										<div><?php echo checkbox_field('project_user_' . $user->getId() . "_$permission_id", $user->hasProjectPermission($project, $permission_id), array('id' => $genid . 'project_user_' . $user->getId() . "_$permission_id", 'tabindex' => $field_tab++, 'onclick' => "App.modules.updatePermissionsForm.userPermissionCheckboxClick(" . $user->getId() . ",'".$genid."')")) ?> <label for="<?php echo 'project_user_' . $user->getId() . "_$permission_id" ?>" class="checkbox normal"><?php echo $permission_text ?></label></div>
								<?php } // foreach ?>
										</div>
									<script>
										if (!document.getElementById( '<?php echo $genid ?>project_user_<?php echo $user->getId() ?>').checked) {
											document.getElementById( '<?php echo $genid ?>user_<?php echo $user->getId() ?>_permissions').style.display = 'none';
										} // if
									</script>
							<?php //} // if ?>
									</td>
								</tr>
						<?php } // foreach ?>
					<?php } else { ?>
								<tr>
								<td colspan="2"><?php echo lang('no users in company') ?></td>
								</tr>
					<?php } // if ?>
							</table>
						</div>
						<div class="clear"></div>
					</div>
					</div>
					<?php //if (!$company->isOwner()) { ?>
						<script>
							if(!document.getElementById( '<?php echo $genid ?>project_company_<?php echo $company->getId() ?>').checked) {
								document.getElementById( '<?php echo $genid ?>project_company_users_<?php echo $company->getId() ?>').style.display = 'none';
							} // if
						</script>
					<?php //} // if ?>
				</fieldset>
				<?php } // if ?>
			<?php } // foreach ?>
			</div></div>
		<?php } // if ?>
	
	<!-- /permissions -->
	
