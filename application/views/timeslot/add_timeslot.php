<?php $genid = gen_id(); ?>

<form style="height:100%;background-color:white" class="internalForm" action="<?php echo $timeslot->getEditUrl() ?>" method="post">

<div class="timeslot">
<div class="coInputHeader">
<div class="coInputHeaderUpperRow">
	<div class="coInputTitle"><table style="width:535px"><tr><td><?php echo $timeslot->isNew() ? lang('new timeslot') : lang('edit timeslot') ?>
	</td><td style="text-align:right"><?php echo submit_button($timeslot->isNew() ? lang('add timeslot') : lang('save changes'),'s',array('style'=>'margin-top:0px;margin-left:10px')) ?></td></tr></table>
	</div>
	
	</div>
</div>
<div class="coInputSeparator"></div>
<div class="coInputMainBlock">
  <div class="formAddTimeslotDescription">
    <?php echo label_tag(lang('description'), 'addTimeslotDescription', false) ?>
    <?php echo textarea_field("timeslot[description]", array_var($timeslot_data, 'description'), array('class' => 'short', 'id' => 'addTimeslotDescription', 'tabindex' => '10')) ?>
  </div>
	<table>
		<tr>
			<td><b><?php echo lang("start date") ?>:&nbsp;</b></td>
			<td align='left'><?php 
				$start_time = new DateTimeValue($timeslot->getStartTime()->getTimestamp() + logged_user()->getTimezone() * 3600) ;
				echo pick_date_widget2('timeslot[start_value]',$start_time, $genid, 20);
			?></td>
		</tr>
		
		<tr>
			<td><b><?php echo lang("start time") ?>:&nbsp;</b></td>
			<td align='left'><select name="timeslot[start_hour]" size="1" tabindex="30">
			<?php
			for($i = 0; $i < 24; $i++) {
					echo "<option value=\"$i\"";
					if($start_time->getHour() == $i) echo ' selected="selected"';
					echo ">$i</option>\n";
				}
			?>
			</select> <b>:</b> <select name="timeslot[start_minute]" size="1">
			<?php
			$minute = $start_time->getMinute();
			for($i = 0; $i < 60; $i++) {
				echo "<option value='$i'";
				if($minute == $i) echo ' selected="selected"';
				echo sprintf(">%02d</option>\n", $i);
			}
			?>
			</select></td>
		</tr><tr><td>&nbsp;</td></tr>
		<tr>
			<td ><b><?php echo lang("end date") ?>:&nbsp;</b></td>
			<td align='left'><?php 
				if ($timeslot->getEndTime() == null){
					$dt = DateTimeValueLib::now();
					$end_time = new DateTimeValue($dt->getTimestamp() + logged_user()->getTimezone() * 3600);
				} else
					$end_time = new DateTimeValue($timeslot->getEndTime()->getTimestamp() + logged_user()->getTimezone() * 3600) ;
			echo pick_date_widget2('timeslot[end_value]',$end_time, $genid, 40);
			?></td>
		</tr>
		
		<tr>
			<td><b><?php echo lang("end time") ?>:&nbsp;</b></td>
			<td align='left'><select name="timeslot[end_hour]" size="1" tabindex="50">
			<?php
			for($i = 0; $i < 24; $i++) {
					echo "<option value=\"$i\"";
					if($end_time->getHour() == $i) echo ' selected="selected"';
					echo ">$i</option>\n";
				}
			?>
			</select> <b>:</b> <select name="timeslot[end_minute]" size="1" tabindex="60">
			<?php
			$minute = $end_time->getMinute();
			for($i = 0; $i < 60; $i++) {
				echo "<option value='$i'";
				if($minute == $i) echo ' selected="selected"';
				echo sprintf(">%02d</option>\n", $i);
			}
			?>
			</select></td>
		</tr><tr><td>&nbsp;</td></tr>
		<tr>
			<td ><b><?php echo lang("total pause time") ?>:&nbsp;</b></td>
			<td align='left'><b><?php 
				$totalSeconds = $timeslot->getSubtract();
				$seconds = $totalSeconds % 60;
				$minutes = (($totalSeconds - $seconds) / 60) % 60;
				$hours = (($totalSeconds - $seconds - ($minutes * 60)) / 3600);
				
			?><input type="text" style="width:40px;margin-right:3px" name="timeslot[subtract_hours]" value="<?php echo($hours); ?>"/><?php echo lang('hours') ?>,&nbsp;
			</b><select name="timeslot[subtract_minutes]" size="1" tabindex="70">
			<?php
			for($i = 0; $i < 60; $i++) {
				echo "<option value='$i'";
				if($minutes == $i) echo ' selected="selected"';
				echo sprintf(">%02d</option>\n", $i);
			}
			?>
			</select><?php echo lang('minutes') ?>,&nbsp;
			<select name="timeslot[subtract_seconds]" size="1" tabindex="80">
			<?php
			for($i = 0; $i < 60; $i++) {
				echo "<option value='$i'";
				if($seconds == $i) echo ' selected="selected"';
				echo sprintf(">%02d</option>\n", $i);
			}
			?>
			</select><?php echo lang('seconds') ?></td>
		</tr>
	</table>

	<?php if ($show_billing) {?>
		<br/>
		<?php echo radio_field('timeslot[is_fixed_billing]',!$timeslot_data['is_fixed_billing'],array('onchange' => 'og.showAndHide("' . $genid. 'hbilling",["' . $genid. 'fbilling"])', 
			'value' => '0', 'style' => 'width:16px')); echo '<b>' . lang('hourly billing') . '</b>'; ?>
		<?php echo radio_field('timeslot[is_fixed_billing]',$timeslot_data['is_fixed_billing'],array('onchange' => 'og.showAndHide("' . $genid. 'fbilling",["' . $genid. 'hbilling"])', 
		'value' => '1', 'style' => 'width:16px')); echo '<b>' . lang('fixed billing') . '</b>'; ?>
	  	<div id="<?php echo $genid ?>hbilling" style="<?php echo $timeslot_data['is_fixed_billing']?'display:none':'' ?>">
	    	<?php echo label_tag(lang('hourly rates'), 'addTimeslotHourlyBilling', false) ?>
	  		<?php echo text_field('timeslot[hourly_billing]',array_var($timeslot_data, 'hourly_billing'), array('id' => 'addTimeslotHourlyBilling')) ?>
	  	</div>
	  	<div id="<?php echo $genid ?>fbilling" style="<?php echo $timeslot_data['is_fixed_billing']?'':'display:none' ?>">
	    	<?php echo label_tag(lang('billing amount'), 'addTimeslotFixedBilling', false) ?>
	  		<?php echo text_field('timeslot[fixed_billing]',array_var($timeslot_data, 'fixed_billing'), array('id' => 'addTimeslotFixedBilling')) ?>
	  	</div>
  	<?php } ?>

    <?php echo submit_button($timeslot->isNew() ? lang('add timeslot') : lang('save changes'), 's', array('tabindex' => '80')); ?>
</div>
</div>

</form>