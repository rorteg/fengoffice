<!-- Actions Panel -->

<table style="width:240px;border-collapse:collapse">
	<col width=12/><col width=216/><col width=12/>
	<tr>
		<td class="coViewHeader coViewSmallHeader" colspan=2 rowspan=2><div class="coViewPropertiesHeader"><?php echo lang("actions") ?></div></td>
		<td class="coViewTopRight"></td>
	</tr>
		
	<tr><td class="coViewRight" rowspan=2></td></tr>
	
	<tr>
		<td class="coViewBody" colspan=2> <?php
		if (count(PageActions::instance()->getActions()) > 0 ) { ?>
			<div id="actionsDialog1"> <?php
				$pactions = PageActions::instance()->getActions();
				$shown = 0;
				foreach ($pactions as $action) {
					if ($action->isCommon) {
				 		//if it is a common action sets the style display:block
				 		if ($action->getTarget() != '') { ?>
	   				    	<a id="<?php $atrib = $action->getAttributes(); echo array_var($atrib,'id'); ?>" style="display:block" class="coViewAction <?php echo $action->getName()?>" href="<?php echo $action->getURL()?>" target="<?php echo $action->getTarget()?>"> <?php echo $action->getTitle(); ?></a>
				 		<?php } else { ?>
							<a id="<?php $atrib = $action->getAttributes(); echo array_var($atrib,'id'); ?>" style="display:block" class="<?php $attribs = $action->getAttributes(); echo isset($attribs["download"]) ? '':'internalLink' ?> coViewAction <?php echo $action->getName()?>" href="<?php echo $action->getURL()?>"> <?php echo $action->getTitle(); ?></a>
						<?php }
				 		$shown++;
					} //if
				}//foreach ?>
			</div> <?php
			
			$count = count($pactions);
			$hidden = false;
			foreach ($pactions as $action) {
				if (!$action->isCommon) {
					if (!$hidden && $shown >= 4 && $shown + 1 < $count) {
						// if 4 actions have already been shown and there's more than one action left to show, hide the rest ?>
			 			<div id="otherActions<?php echo $genid ?>" style="display:none"><?php
			 			$hidden = true;
			 		}
			 		
			 		if ($action->getTarget() != '') { ?>
						<a style="display:block" class="coViewAction <?php echo $action->getName()?>" href="<?php echo $action->getURL()?>" target="<?php echo $action->getTarget()?>"> <?php echo $action->getTitle() ?></a>
					<?php } else { ?>
						<a style="display:block" class="<?php $attribs = $action->getAttributes(); echo isset($attribs["download"]) ? '':'internalLink' ?> coViewAction <?php echo $action->getName()?>" href="<?php echo $action->getURL()?>"> <?php echo $action->getTitle() ?></a>
					<?php }
			    	$shown++;
				}
			} // foreach
			if ($hidden) {
				// close the hidden div and show the "More" link ?>
				</div>											
				<a id="moreOption<?php echo $genid; ?>" style="display:block" class="coViewAction" href="javascript: og.showMoreActions('<?php echo $genid ?>')">
			    	<?php echo lang('more').'...' ?>
			    </a> <?php 
			}
		 }
		 PageActions::clearActions(); ?>
		</td>
	</tr>
	<tr>
		<td class="coViewBottomLeft" style="width:12px;">&nbsp;</td>
		<td class="coViewBottom" style="width:216px;"></td>
		<td class="coViewBottomRight" style="width:12px;">&nbsp;&nbsp;</td>
	</tr>
</table>