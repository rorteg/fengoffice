<div style="font-family: Verdana, Arial, sans-serif; font-size: 12px;">

	<a href="<?php echo $object->getViewUrl() ?>" target="_blank" style="font-size: 18px;"><?php echo $description ?></a><br><br>

	<?php foreach ($properties as $k => $p) { ?>
		<span style="font-family: Verdana, Arial, sans-serif; font-size: 12px;"><?php echo lang($k) ?>: <?php echo $p ?></span><br><br>
	<?php } ?>

	<?php if (isset($links) && is_array($links)) {
		foreach ($links as $link) {
			?><span style="font-family: Verdana, Arial, sans-serif; font-size: 12px;"><?php
			if (isset($link['img']))
				echo '<img src="'.$link['img'].'"/>';
			echo '<a href="'.$link['url'].'" target="_blank">'.$link['text'].'</a>';
			?></span><br><?php
		}
	}		 
	?>
	<br><br>
	
	<?php if (isset($second_properties) && is_array($second_properties)){
			 foreach ($second_properties as $k => $p) { ?>
			<span style="font-family: Verdana, Arial, sans-serif; font-size: 12px;"><?php echo lang($k) ?>: <?php echo $p ?></span><br><br>
		  <?php }  ?>
	<?php }  ?>

	<div style="color: #818283; font-style: italic; border-top: 2px solid #818283; padding-top: 2px; font-family: Verdana, Arial, sans-serif; font-size: 12px;">
	<?php echo lang('system notification email'); ?><br>
	<a href="<?php echo ROOT_URL; ?>" target="_blank" style="font-family: Verdana, Arial, sans-serif; font-size: 12px;"><?php echo ROOT_URL; ?></a>
	</div>

</div>