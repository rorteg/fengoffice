<?php
require_javascript('og/modules/addMessageForm.js'); 
?>

<div class="og-add-subscribers">
<?php
	if (!isset($users) || !is_array($users)) $users = Users::getAll();
	if (!isset($groupUserIds) || !is_array($groupUserIds)) $groupUserIds = array(logged_user()->getId());
	if (!isset($genid)) $genid = gen_id();
?>
<?php
	$grouped = array();
	$allChecked = true;
	foreach($users as $user) {
		if (!in_array($user->getId(), $groupUserIds)) $allChecked = false;
		if(!isset($grouped[$user->getCompanyId()]) || !is_array($grouped[$user->getCompanyId()])) {
			$grouped[$user->getCompanyId()] = array();
		} // if
		$grouped[$user->getCompanyId()][] = $user;
	} // foreach
	$companyUsers = $grouped;
?>
<div id="<?php echo $genid ?>notify_companies">

<?php foreach($companyUsers as $companyId => $users) { ?>

<div id="<?php echo $companyId?>" class="company-users" <?php echo is_array($users) == true? 'style ="margin-bottom: 10px;"' : '' ?>>

	<?php if(is_array($users) && count($users)) { ?>
		<div onclick="og.subscribeCompany(this)"  class="container-div company-name<?php echo $allChecked ? ' checked' : ''?>" onmouseout="og.rollOut(this,true)" onmouseover="og.rollOver(this)">
		<?php $theCompany = Companies::findById($companyId) ?>
			<label for="<?php echo $genid ?>notifyCompany<?php echo $theCompany->getId() ?>" style="background: url('<?php echo $theCompany->getLogoUrl() ?>') no-repeat;">
				<span class="ico-company link-ico"><?php echo clean($theCompany->getName()) ?></span>
			</label>
		</div>
		<div id="<?php echo $genid . $companyId ?>company_users" style="padding-left:10px;">
		<?php foreach($users as $user) { 
				$checked = in_array($user->getId(), $groupUserIds);
				?>
				<div id="div<?php echo $genid ?>inviteUser<?php echo $user->getId() ?>" class="container-div <?php echo $checked==true? 'checked-user':'user-name' ?>" onmouseout="og.rollOut(this,false <?php echo $checked==true? ',true':',false' ?>)" onmouseover="og.rollOver(this)" onclick="og.checkUser(this)">
					<input <?php echo $checked? 'checked="checked"':'' ?> id="<?php echo $genid ?>inviteUser<?php echo $user->getId()?>" type="checkbox" style="display:none" name="<?php echo 'user['.$user->getId() .']' ?>" value="checked" />
					<label for="<?php echo $genid ?>notifyUser<?php echo $user->getId() ?>" style=" width: 120px; overflow:hidden; background:url('<?php echo $user->getAvatarUrl() ?>') no-repeat;">
						<span class="ico-user link-ico"><?php echo clean($user->getDisplayName()) ?></span>
						<br>
						<span style="color:#888888;font-size:90%;font-weight:normal;"> <?php echo $user->getEmail()  ?> </span>
					</label>
				</div>
			
		<?php } // foreach ?>
		<div style="clear:both;"></div>
		</div>
	<?php } // if ?>
</div>	
<?php } // foreach ?>

</div>
</div>