<div class="user-box-actions" style="display: none;">
	<ul>
		<li>
			<img src="<?php echo $_userbox_user->getPictureUrl()?>" align="left" />
			<h2><?php echo clean($_userbox_user->getObjectName()) ?></h2>
			<p></p>
		</li>
		<li class="u-clear"></li>
		<!--
        <div>
			<textarea placeholder="Share your news!"></textarea>
		</div>
        -->
		<li class="line-top"></li>			
		<?php 
		foreach ($_userbox_crumbs as $crumb) {
			echo '<li><a';
			if (isset($crumb['target'])) echo ' target="' . $crumb['target'] .'"';
			echo ' onclick="$(\'div.user-box-actions\').fadeOut(\'fast\')" href="' . array_var($crumb, 'url', '') . '">';
			echo array_var($crumb, 'text', '');
			echo '</a></li>';
		} 
		?>
		
		<?php if (can_manage_configuration(logged_user())) : ?>
		<li>
            <a href="Javascript:;" onclick="$('.theme-color-picker-wrapper').slideToggle();if($('input.color-picker').val()=='#undefined')$('input.color-picker').val('#')"><?php echo lang('brand colors')?></a>
        </li>    
		<li class="theme-color-picker-wrapper" style="display: none;">
            <div class="theme-color-picker">
                <form action="">
                    <label><?php echo lang('head color')?></label>
                    <input type="text" class="color-picker back-color-value" value="" /><br />
                    <label><?php echo lang('tabs color')?></label>
                    <input type="text" class="color-picker front-color-value" value="" /><br /> 
                    <label><?php echo lang('font color')?></label>
                    <input type="text" class="color-picker face-font-color-value" value="" /><br />
                    <label><?php echo lang('title color')?></label>
                    <input type="text" class="color-picker title-font-color-value" value="" /><br /><br />
                    <input type="button" value="<?php echo lang('save colors')?>" onclick="saveBrandColors(this);" />               
                    <input type="button" onclick="$('.theme-color-picker-wrapper').slideUp();" value="<?php echo lang('cancel')?>" />   
                </form>
            </div>
        </li>
        <?php endif; ?>
		<li class="line-top">
			<a href="#" target="_self" onclick="window.location.href='<?php echo get_url('access', 'logout') ?>'"><?php echo lang('logout') ?></a>
		</li>
	</ul>
</div>