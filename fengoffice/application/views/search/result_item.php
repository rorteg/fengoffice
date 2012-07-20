	<?php extract($result)?>
	<div class="result-item">
		<div class="title">
			<a href="<?php echo $url?>" ><?php echo $title ?> </a>
		</div>
		<div class="content">
			<p><?php echo $content?></p>
		</div>
		<div class="footer">
			<span class="created_by"><?php echo $updated_by ?></span> -
			<span class="updated_on"><?php echo $updated_on ?></span> -
			<span class="type"><?php echo lang($type) ?></span> 
		</div>
	</div>