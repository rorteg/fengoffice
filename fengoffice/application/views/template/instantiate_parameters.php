<?php $genid = gen_id() ?>
<form style='height:100%;background-color:white' class="internalForm" action="<?php echo get_url('template', 'instantiate_parameters', array('id' => $id, 'back' => '1')) ?>" method="post">

<div class="template">
<div class="coInputHeader">
	<div class="coInputHeaderUpperRow">
		<div class="coInputTitle"><?php echo lang('template parameters')?></div>
	</div>

</div>
<div class="coInputMainBlock">
	
	<div>
		<table><tbody>
		<?php foreach($parameters as $parameter) {?>
			<tr style='height:30px;'>
				<td style="padding:3px 10px 0 10px;"><b><?php echo $parameter['name']; ?></b></td>
				<td align="left">
					<?php if($parameter['type'] == 'string'){ ?>
						<input id="parameterValues[<?php echo $parameter['name'] ?>]" name="parameterValues[<?php echo $parameter['name'] ?>]" />
					<?php }else if($parameter['type'] == 'date'){ ?>
						<?php echo pick_date_widget2('parameterValues['.$parameter['name'].']')?>
					<?php }else{ ?>
						<select name="<?php echo 'parameterValues['.$parameter['name'].']'; ?>">
						<?php
							$companies  = allowed_users_to_assign(active_context());
							foreach ($companies as $c) {?>
								<option value="<?php echo $c['id']; ?>"> <?php echo $c['name']; ?></option>
							<?php 
								$users = $c['users'];
								if ( count($users) ) {
									foreach ($users as $usr) {?>																
										<option value="<?php echo $usr['id'] ?>"> <?php echo $usr['name'] ?></option>
										
								<?php }
								}
							}	
							 
						?>
						</select>
					<?php } ?>
				</td>
			</tr>
		<?php }//foreach ?>
		</tbody></table>
	</div>
	<br/>
	<div>
	<?php echo submit_button(lang('instantiate'),'s',	array('style'=>'margin-top:0px', 'tabindex' => '3')) ?>
	</div>	
</div>
</div>
</form>