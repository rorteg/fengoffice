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
		
		function passwordError(message) {
			$("<?php echo $jqid ?> .password input,<?php echo $jqid ?> .repeat input").addClass("field-error").val("");
			$("<?php echo $jqid ?> .password input").addClass("field-error").focus().val('');
			
			$("<?php echo $jqid ?> .field-error-msg").remove();
			$("<?php echo $jqid ?> .password").append("<div class='field-error-msg'>"+message+"</div>");		
		}

		function passwordOk(){
			$("<?php echo $jqid ?> .field-error-msg").remove();
			$("<?php echo $jqid ?> .password input, <?php echo $jqid ?> .repeat input").removeClass("field-error");
		}
                
                $("<?php echo $jqid ?>.access-data #create-user").click(function(){
			if ($(this).is(":checked")) {
				$("<?php echo $jqid ?>.access-data .user-data").slideDown();
				$("<?php echo $jqid ?> .password input").focus();
			} else {
				$("<?php echo $jqid ?>.access-data .user-data").slideUp();
			}
		});
                
		
		$("<?php echo $jqid ?>.access-data #create-password").click(function(){
			if ($(this).is(":checked")) {
				$("<?php echo $jqid ?>.access-data .user-data-password").slideDown();
				$("<?php echo $jqid ?> .password input").focus();
			} else {
				$("<?php echo $jqid ?>.access-data .user-data-password").slideUp();
			}
		});


		$("<?php echo $jqid ?> .repeat input").blur(function(){
			if ( $(this).val() != $("<?php echo $jqid ?> .password input").val() ) { 
				passwordError(lang("passwords dont match")); 
			}else{
				passwordOk();
			}	
		});
		$("<?php echo $jqid ?> .password input").blur(function(){
                        if($("<?php echo $jqid ?>.access-data #create-password").is(":checked")){
                            if ($(this).val() == '') {
                                    passwordError(lang("password value missing"));
                            }else if ( $("<?php echo $jqid ?> .repeat input").val() &&  $(this).val() != $("<?php echo $jqid ?> .repeat input").val() ) {
                                    passwordError(lang("passwords dont match")); 
                            }else{
                                    passwordOk();
                            }	
                        }
		});
                
	});

</script>

<div id = "<?php echo $genid ?>" class="access-data"> 
    <label class="checkbox" ><?php echo lang("will this person use feng office?") ?></label><input class="checkbox" type="checkbox" name="contact[user][create-user]" <?php if(!$contact_mail){echo "checked";}?> id="create-user"></input>
    <div class="clear"></div>

    <div class="user-data" <?php if($contact_mail){echo "style='display:none'";}?>>
            <label class="checkbox" ><?php echo lang("specify password?") ?></label><input class="checkbox" type="checkbox" name="contact[user][create-password]" id="create-password" ></input>
            <div class="clear"></div>
            <div class="user-data-password" style="display: none;">
                <div class="field password">
                        <label><?php echo lang("password")?>:</label><input name="contact[user][password]" type="password"></input>
                </div>
                <div class="field repeat">
                        <label><?php echo lang("password again")?>:</label><input type="password" name="contact[user][password_a]"></input>
                </div>
            </div>          
            <div class="clear"></div>
            <div class="field role">
                    <label><?php echo lang("user type")?>:</label>
                            <?php  echo simple_select_box('contact[user][type]', $permission_groups,4)?>
            </div>
    </div>	
</div>


