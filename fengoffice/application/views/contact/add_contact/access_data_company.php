<?php
	$permission_groups = array(); 
	$groups = PermissionGroups::getNonPersonalSameLevelPermissionsGroups('id');
	foreach($groups as $group){
    	$permission_groups[]=array($group->getId(),$group->getName());
    }
    $genid = gen_id();
    $jqid = "#$genid";
?>
<script>
	$(function(){
                $("<?php echo $jqid ?>.access-data input.checkbox").click(function(){
			if ($(this).is(":checked")) {
				$("<?php echo $jqid ?>.access-data .user-data").slideDown();
			} else {
				$("<?php echo $jqid ?>.access-data .user-data").slideUp();
			}
		});
	});

</script>


<div id = "<?php echo $genid ?>" class="access-data">    
    <div class="field role">
            <label><?php echo lang("company")?>:</label>
            <?php echo select_company('contact[user][company_id]',owner_company()->getId(), array('style' => 'width:400px;'))?>
    </div>
    <div class="field role" style="margin-top: 10px; min-height:25px;">
        <label class="checkbox" ><?php echo lang("will this person use feng office?") ?></label><input class="checkbox" type="checkbox" name="contact[user][create-user]" checked ></input>
    </div>
    <div class="user-data" style="margin-bottom: 10px;">            
            <div class="field role">
                    <label><?php echo lang("user type")?>:</label>
                    <?php  echo simple_select_box('contact[user][type]', $permission_groups,4)?>
            </div>
    </div>
</div>


