 <?php set_page_title(lang('login')) ?>
<script>
	showMoreOptions = function() {
		var div = document.getElementById("optionsDiv");
		var more = document.getElementById("optionsLink");
		var hide = document.getElementById("hideOptionsLink");
		div.style.display = "block";
		hide.style.display = "inline";
		more.style.display = "none";
	}
	hideMoreOptions = function() {
		var div = document.getElementById("optionsDiv");
		var more = document.getElementById("optionsLink");
		var hide = document.getElementById("hideOptionsLink");
		div.style.display = "none";
		hide.style.display = "none";
		more.style.display = "inline";
	}
</script>
<form action="<?php echo get_url('access', 'login') ?>" method="post">
	
	<img src="<?php echo get_image_url("layout/loading.gif") ?>" width="1" height="1" style="position:absolute; top:0; left:0 ;display:none"/>
	
<?php tpl_display(get_template_path('form_errors')) ?>

  <div id="loginUsernameDiv">
    <label for="loginUsername"><?php echo lang('email or username') ?>:</label>
    <?php echo text_field('login[username]', array_var($login_data, 'username'), array('id' => 'loginUsername', 'class' => 'medium')) ?>
  </div>
  <div id="loginPasswordDiv">
    <label for="loginPassword"><?php echo lang('password') ?>:</label>
    <?php echo password_field('login[password]', null, array('id' => 'loginPassword', 'class' => 'medium')) ?>
  </div>
  <div class="clean"></div>
  <div style="margin-top: 6px">
    <?php echo checkbox_field('login[remember]', array_var($login_data, 'remember') == 'checked', array('id' => 'loginRememberMe')) ?>
    <label class="checkbox" for="loginRememberMe"><?php echo lang('remember me') ?></label>
  </div>
  
<?php if(isset($login_data) && is_array($login_data) && count($login_data)) { ?>
<?php foreach($login_data as $k => $v) { ?>
<?php if(str_starts_with($k, 'ref_')) { ?>
  <input type="hidden" name="login[<?php echo $k ?>]" value="<?php echo $login_data[$k] ?>" />
<?php } // if ?>
<?php } // foreach ?>
<?php } // if ?>

	<!-- table><tr><td -->
  		<div id="loginSubmit">
  			<?php echo submit_button(lang('login')) ?>
  			<span>(<a class="internalLink" href="<?php echo get_url('access', 'forgot_password') ?>"><?php echo lang('forgot password') ?></a>)</span>
  			<a id="optionsLink" href="javascript:showMoreOptions()"> <?php echo lang('options'); ?></a>
  			<a id="hideOptionsLink" style="display:none" href="javascript:hideMoreOptions()"> <?php echo lang ('hide options'); ?></a>
  		</div>
  	<!-- /td><td -->
  		
  	<!-- /td></tr></table -->
  
  	<div id="optionsDiv" style="display:none">
	<table>
	<tr><td>
		<label><?php echo lang('language')?>:</label>
  		<?php
  			$handler = new LocalizationConfigHandler();
  			echo $handler->render('configOptionSelect', array('text' => lang('last language'), 'value' => 'Default'));
  		?>
  	</td></tr>
	</table>
	</div>    
</form>

<script>
document.getElementById('loginUsername').focus();
</script>

