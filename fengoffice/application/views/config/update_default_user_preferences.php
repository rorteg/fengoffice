<div class="adminConfiguration" style="height:100%;background-color:white">
<form class="internalForm" action="<?php echo $category->getDefaultUpdateUrl() ?>" method="post" onreset="return confirm('<?php echo escape_single_quotes(lang('confirm reset form')) ?>')">
	<div class="adminHeader">
		<div class="adminTitle">
			<table style="width:535px"><tr><td>
				<?php echo $category->getDisplayName() ?>
			</td><td style="text-align:right">
				<?php echo submit_button(lang('save'), 's', array('style' => 'margin-top:0px;')) ?>&nbsp;<button type="reset"><?php echo lang('reset') ?></button>
			</td></tr></table>
		</div>
	</div>
	<div class="adminSeparator"></div>
	<div class="adminMainBlock">

	<?php if(isset($options) && is_array($options) && count($options)) { ?>
			<div id="configCategoryOptions">
				<?php $counter = 0; ?>
				<?php foreach($options as $option) { ?>
					<?php $option->useDefaultValue(); ?>
					<?php $counter++; ?>
					<div class="configCategoryOtpion " style="<?php echo $counter % 2 ? 'background-color:#F4F8F9' : '' ?>" id="configCategoryOption_<?php echo $option->getName() ?>">
						<div class="configOptionInfo">
							<div class="configOptionLabel"><label><?php echo $option->getDisplayName() ?>:</label></div>
						<?php if(trim($option_description = $option->getDisplayDescription())) { ?>
							<div class="configOptionDescription desc"><?php echo $option_description ?></div>
						<?php } // if ?>
						</div>
						<div class="configOptionControl"><?php echo $option->render('options[' . $option->getName() . ']') ?></div>
						<div class="clear"></div>
					</div>
				<?php } // foreach ?>
			</div>
			<?php echo submit_button(lang('save')) ?>&nbsp;<button type="reset"><?php echo lang('reset') ?></button>
	<?php } else { ?>
		<p><?php echo lang('config category is empty') ?></p>
	<?php } // if ?>
	</div>
</form>
</div>
