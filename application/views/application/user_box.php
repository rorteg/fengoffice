<div id="userbox">
	<?php echo lang('welcome back', clean($_userbox_user->getDisplayName())) ?> (<a target="_self" href="<?php echo get_url('access', 'logout') ?>"><?php echo lang('logout') ?></a>) :
	<?php $first = true; ?>
	<?php foreach ($_userbox_crumbs as $crumb) {
		if (!$first) {
			echo " | ";
		} else {
			$first = false;
		}
		echo '<a';
		if (isset($crumb['target'])) echo ' target="' . $crumb['target'] .'"';
		echo ' href="' . array_var($crumb, 'url', '') . '">';
		echo array_var($crumb, 'text', '');
		echo '</a>';
	} ?> 
</div>