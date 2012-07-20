<?php if($pagination->hasPrevious) :?>
	<a href="<?php echo $pagination->previousUrl ?>">&lt; <?php echo lang("previous")?></a>
<?php endif?> 

<?php 
	if (count($pagination->links) > 1 ){
		foreach($pagination->links as $page => $url){ ?>
		<a href = "<?php echo $url ?>" >
			<?php 
				if ($pagination->currentPage == $page) echo "<em>";
				echo $page;
				if ($pagination->currentPage == $page) echo "</em>";
			?>
		</a>
<?php 
		}
	}
	else{ 
		 echo "$pagination->currentStart - $pagination->currentEnd ".lang("of")." ".$pagination->total ;		
	}
?>

<?php if($pagination->hasNext) :?>  
	<a href="<?php echo $pagination->nextUrl ?>"><?php echo lang("next")?> &gt;</a>
<?php endif;?>




