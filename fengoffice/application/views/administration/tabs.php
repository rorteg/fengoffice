<div class ="page tab-manager" >
	<h1><?php echo lang("tabs")?></h1>
	<form method = "POST" action="<?php echo get_url("administration" , "tabs_submit" )?>" >
		<table>
			<tr>
				<th>ID</th>
				<th><?php echo lang("title")?></th>
				<th><?php echo lang("order")?></th>
				<th><?php echo lang("enabled")?></th>
				
			</tr>
			<?php foreach ( $tabs as $tab ) : /* @var $tab TabPanel */ ?>
			<tr class="<?php echo ($tab->getEnabled())?'enabled':'disabled'?> ">
				<td><?php echo $tab->getId()?></td>
				<td><input type="text" class="disabled" readonly="readonly" required name="tabs[<?php echo $tab->getId()?>][title]" value="<?php echo $tab->getTitle()?>"></td>
				<td><input type="number" name="tabs[<?php echo $tab->getId()?>][ordering]" value="<?php echo $tab->getOrdering()?>"></input></td>
				<td>
					<input 
						type="checkbox" 
						name="tabs[<?php echo $tab->getId()?>][enabled]" 
						<?php echo ( $tab->getEnabled() ) ? "checked='checked'" : "" ?>/>
						
						
				</td>
			</tr>
			<?php endforeach;?>
		</table>
		<input class="submit" type="submit" value="Save changes"></input>
	</form>
	
</div>


