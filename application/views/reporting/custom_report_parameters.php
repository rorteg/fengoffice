<?php
	$genid = gen_id();
	$tiCount = 0;
	$firstId = '';
?>

<form style='height:100%;background-color:white' class="internalForm" action="<?php echo get_url('reporting', 'view_custom_report', array('id' => $id)) ?>" method="get">

<div class="coInputHeader">
	<div class="coInputHeaderUpperRow">
		<div class="coInputTitle"><?php echo $title ?></div>
	</div>
</div>
<div class="coInputSeparator"></div>
<div class="coInputMainBlock">

	<div style="width:600px;padding-bottom:20px"><?php echo $description ?></div>

	<table>
		<tr>
			<td style="padding:0 10px 10px 10px;"><b><?php echo lang('field') ?></b></td>
			<td style="text-align:center;padding:0 10px 10px 10px;"><b><?php echo lang('condition') ?></b></td>
			<td style="text-align:center;padding:0 10px 10px 10px;"><b><?php echo lang('value') ?></b></td>
		</tr>
		
		<?php foreach($conditions as $condition){
			if($condition->getCustomPropertyId() > 0){
				$cp = CustomProperties::getCustomProperty($condition->getCustomPropertyId());
				if (!$cp) continue;
			}
			$tiCount ++;
			?>
			<tr style='height:30px;'>
			<?php if($condition->getCustomPropertyId() > 0){
					$name = clean($cp->getName());
				  }else{
				  	if ($condition->getFieldName()!= 'workspace' && $condition->getFieldName()!= 'tag'){
				  	    $name = lang('field ' . $model . ' ' . $condition->getFieldName());				  	    
				  	}else{				  		
				  		$name = lang($condition->getFieldName());
				  	}	
				  } 
				  $condId = $genid . 'rpcond' . $condition->getId();
				if ($firstId == '')
					$firstId = $condId;
			?>
				<td style="padding:3px 0 0 10px;"><b><?php echo $name ?>&nbsp;</b></td>
				<td style="text-align:center;padding:3px 0 0 0;"><?php echo ($condition->getCondition() != '%' ? $condition->getCondition() : lang('ends with') ) ?>&nbsp;</td>
			<?php if(isset($cp)){ ?>
				<td align='left'>
					<?php if($cp->getType() == 'text' || $cp->getType() == 'numeric'){ ?>
						<input type="text" id="<?php echo $condId; ?>" name="params[<?php echo $condition->getId()."_".clean($cp->getName()) ?>]" tabindex=<?php echo $tiCount?>/>
					<?php }else if($cp->getType() == 'boolean'){  ?>
						<select id="<?php echo $condId; ?>" name="params[<?php echo $condition->getId()."_".clean($cp->getName()) ?>]" tabindex=<?php echo $tiCount?>>
							<option value="1" > <?php echo lang('true') ?>  </option>
							<option value="0" > <?php echo lang('false') ?> </option>
						</select>
					<?php }else if($cp->getType() == 'list'){  ?>
						<select id="<?php echo $condId; ?>" name="params[<?php echo $condition->getId()."_".clean($cp->getName()) ?>]" tabindex=<?php echo $tiCount?>>
						<?php foreach(explode(',', $cp->getValues()) as $value){ ?>
							<option value="<?php echo $value ?>"> <?php echo $value ?>  </option>
						<?php }//foreach ?>
						</select>
					<?php }else if($cp->getType() == 'date'){  ?>
						<?php echo pick_date_widget2("params[".$condition->getId()."_".clean($cp->getName())."]",$genid,$tiCount)?>
					<?php }?>
				</td>
			<?php }else{ ?>
				<td align='left'>
				<?php				
				if($condition->getFieldName() != 'workspace' && $condition->getFieldName() != 'tag' && $condition->getFieldName() != 'state'){
					$model_instance = new $model();
					$col_type = $model_instance->getColumnType($condition->getFieldName());					
					$externalCols = $model_instance->getExternalColumns();
					if(in_array($condition->getFieldName(), $externalCols)){ ?>					
						<select id="<?php echo $condId; ?>" name="params[<?php echo $condition->getId() ?>]" />
					<?php foreach($external_fields[$condition->getFieldName()] as $value){ ?>
							<option value="<?php echo $value['id'] ?>"><?php echo $value['name'] ?></option>
					<?php	}?>
						</select>
					<?php }else{
						if ($col_type == DATA_TYPE_DATE || $col_type == DATA_TYPE_DATETIME) {
							echo pick_date_widget2("params[".$condition->getId()."]");
						} else {
				?>
					<input type="text" id="<?php echo $condId; ?>" name="params[<?php echo $condition->getId() ?>]" />
				<?php 	}
					} ?>
				</td>
			<?php }//if
			else{
				//if is a tag selection or a workspace selection
				$val=$condition->getValue();
				if($condition->getFieldName() == 'workspace'){
					echo select_project2('params[workspace]',isset($val)?$val:0, $genid , true);
				}else if($condition->getFieldName() == 'tag'){					
					echo autocomplete_tags_field("params[tag]", null, null, 40);
				}else if ($condition->getFieldName() == 'state'){					
					?>
					    <select id="<?php echo $condId; ?>" name="params[<?php echo $condition->getId() ?>]" tabindex=<?php echo $tiCount?>>
							<option value="1" > <?php echo lang('completed task') ?>  </option>
							<option value="0" > <?php echo lang('open task') ?> </option>
						</select>
					<?php
				}
			 } ?>
			</tr>
		<?php
			}//else 
			unset($cp);
		}//foreach ?>
	</table>
	
<?php echo submit_button(lang('generate report'),'s',array('tabindex' => $tiCount + 1))?>	
</div>

<script>
var firstCond = Ext.getDom('<?php echo $firstId ?>');
if (firstCond)
	firstCond.focus();
</script>

</form>