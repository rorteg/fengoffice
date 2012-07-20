<?php
	require_javascript('og/tasks/main.js');
	require_javascript('og/time/main.js');
	require_javascript('og/time/drawing.js');

	// FIXME: para cuando se haga el billing habilitar $show_billing
	$show_billing = false;//can_manage_billing(logged_user());
	
	$genid = gen_id();
	$tasks_array = array();
	$timeslots_array = array();
	$users_array = array();
	$all_users_array = array();
	$companies_array = array();
	if (isset($tasks)) {
		foreach($tasks as $task) {
			$tasks_array[] = $task->getArrayInfo();
		}
	}
	if (isset($timeslots)) {
		foreach($timeslots as $timeslot) {
			/* @var $timeslot Timeslot */
			$timeslots_array[] = $timeslot->getArrayInfo($show_billing);
		}
	}
	if (isset($users)) {
		foreach($users as $user) {
			$info = $user->getArrayInfo();
			if ($user->getId() == logged_user()->getId()) $info['isCurrent'] = true;
			$users_array[] = $info;
		}
	}
	if (isset($all_users)) {
		foreach($all_users as $user) {
			$info = $user->getArrayInfo();
			if ($user->getId() == logged_user()->getId()) $info['isCurrent'] = true;
			$users_array[] = $info;
		}
	}
	if (isset($companies)) {
		foreach($companies as $company) {
			$companies_array[] = $company->getArrayInfo();
		}
	}

?>

<style>

</style>

<div id="timePanel" class="ogContentPanel" style="background-color:#F0F0F0;height:100%;">
<div style="padding:7px;">
<input type="hidden" id="<?php echo $genid ?>hfTasks" value="<?php echo clean(json_encode($tasks_array)) ?>"/>
<input type="hidden" id="<?php echo $genid ?>hfTimeslots" value="<?php echo clean(json_encode($timeslots_array)) ?>"/>
<input type="hidden" id="<?php echo $genid ?>hfUsers" value="<?php echo clean(json_encode($users_array)) ?>"/>
<input type="hidden" id="<?php echo $genid ?>hfAllUsers" value="<?php echo clean(json_encode($all_users_array)) ?>"/>
<input type="hidden" id="<?php echo $genid ?>hfCompanies" value="<?php echo clean(json_encode($companies_array)) ?>"/>
<input type="hidden" id="<?php echo $genid ?>hfDrawInputs" value="<?php echo $draw_inputs ? "1" : "0" ?>"/>

<table style="width:100%; display:none;" id="<?php echo $genid ?>active_tasks_table">
	<tr>
		<td colspan=2 class="TMActiveTasksHeader">
			<?php echo lang('all active tasks') ?>
		</td>
		<td class="coViewTopRight">&nbsp;</td>
	</tr>
	<tr>
		<td colspan=2 class="coViewBody" style="background-color:white;">
			<div id="<?php echo $genid ?>TMActiveTasksContents" class="TMActiveTasksContents">
			
			</div>
		</td>
		<td class="coViewRight"></td>
	</tr>
	<tr>
		<td class="coViewBottomLeft"></td>
		<td class="coViewBottom">&nbsp;</td>
		<td class="coViewBottomRight"></td>
	</tr>
</table>



<table style="width:100%;" class="general-timeslots">

<tr>
	<td style="width:12px;height:1px;overflow:hidden;line-height:0px;"></td>
	<td style="height:0px;overflow:hidden;line-height:0px;">&nbsp;</td>
	<td style="width:12px;height:1px;overflow:hidden;line-height:0px;"></td>
</tr>
<tr>
	<td colspan="2" rowspan="2">
	<div id="<?php echo $genid ?>TMTimespanHeader" class="TMTimespanHeader" style="width:100%;">
		<div style="padding:3px 7px">
		<table style="width:100%;"><tr>
			<td>
				<?php echo lang('time timeslots') ?>
			</td>
			<td align="right" style="font-size:80%;font-weight:normal">
				<a href="<?php echo get_url("reporting",'total_task_times_p', array('type' => '1', 'ws' => active_project() instanceof Project ? active_project()->getId() : 0)) ?>" class="internalLink coViewAction ico-print" style="color:white;font-weight:bold"><?php echo lang('generate report') ?></a>
			</td>
		</tr></table>
		</div>
	</div>
	
	<script type="text/javascript">
	//submit the form when the user press enter
	og.checkEnterPress = function (e,genid)
	{
		var characterCode;
		if(e && e.which){
			characterCode = e.which;
		}
		else{
			e = event;
			characterCode = e.keyCode;
		}
		if(characterCode == 13){
			ogTimeManager.SubmitNewTimeslot(genid);
			return false;
		}
	}
	</script>
	<div id="<?php echo $genid ?>TMTimespanAddNew" class="TMTimespanAddNew" <?php echo ($draw_inputs ? "" : 'style="display:none;"') ?>>
		<input type="hidden" id="<?php echo $genid ?>tsId" name="timeslot[id]" value=""/>
		<div style="padding:7px;">
			<table style="width:100%;">
				<tr>
					<td style="padding-right: 10px; width:140px;vertical-align:bottom">
						<?php echo label_tag(lang('date')) ?>
					</td>
					
					<td style="padding-right: 10px; width:140px;vertical-align:bottom">
						<?php echo label_tag(lang('user')) ?>
					</td>
					<td style="padding-right: 10px; width:140px;vertical-align:bottom">
						<?php echo label_tag(lang('hours')) ?>
					</td>
                                        <td style="padding-right: 10px; width:140px;vertical-align:bottom">
						<?php echo label_tag(lang('minutes')) ?>
					</td>
					<td style="padding-right: 10px; width:95%; margin-top: 0px;vertical-align:bottom">
						<?php echo label_tag(lang('description')) ?>
					</td>
					<td style="padding-left: 10px;text-align:right; vertical-align: middle;">
					</td>
				</tr>
				<tr>
					<td style="padding-right: 10px; width:140px;">
						<?php echo pick_date_widget2('timeslot[date]', DateTimeValueLib::now(), $genid, 100, false) ?>
					</td>
		
					<td style="padding-right: 10px; width:140px;">
						<?php
							$options = array();
							foreach ($users as $user) {
								$options[] = option_tag($user->getObjectName(), $user->getId(), $selected_user == $user->getId() ? array("selected" => "selected") : null);
							}
							echo select_box("timeslot[contact_id]", $options, array('id' => $genid . 'tsUser', 'tabindex' => '150')); 
						?>
					</td>
				<td style="padding-right: 10px; width: 140px;"><?php echo text_field('timeslot[hours]', 0, 
				array('style' => 'width:28px', 'tabindex' => '200', 'id' => $genid . 'tsHours','onkeypress'=>'og.checkEnterPress(event,\''.$genid.'\')')) ?>
				</td>
				<td style="padding-right: 10px; width: 140px;">
					<select name="timeslot[minutes]" size="1" tabindex="220" id="<?php echo $genid . 'tsMinutes'?>">
					<?php
						$minuteOptions = array(0,5,10,15,20,25,30,35,40,45,50,55);
						for($i = 0; $i < 12; $i++) {
							echo "<option value=\"" . $minuteOptions[$i] . "\"";
							echo ">" . $minuteOptions[$i] . "</option>\n";
						}
					?>
					</select>
				</td>
				<td style="padding-right: 10px; width:95%; margin-top: 0px;">
						<?php echo textarea_field('timeslot[description]', '', array('class' => 'short', 'style' => 'height:30px;width:100%;min-width:200px', 'tabindex' => '250', 'id' => $genid . 'tsDesc')) ?>
					</td>
					<td style="padding-left: 10px;text-align:right; vertical-align: top">
						<div id="<?php echo $genid ?>TMTimespanSubmitAdd"><?php echo submit_button(lang('add'),'s',array('style'=>'margin-top:0px;margin-left:0px', 'tabindex' => '300', 'onclick' => 'ogTimeManager.SubmitNewTimeslot(\'' .$genid . '\');return false;')) ?></div>
						<div id="<?php echo $genid ?>TMTimespanSubmitEdit" style="display:none">
							<?php echo submit_button(lang('save'),'s',array('style'=>'margin-top:0px;margin-left:0px', 
								'tabindex' => '310', 'onclick' => 'ogTimeManager.SubmitNewTimeslot(\'' .$genid . '\');return false;')) ?><br/>
							<?php echo submit_button(lang('cancel'),'c',array('style'=>'margin-top:0px;margin-left:0px', 
								'tabindex' => '320', 'onclick' => 'ogTimeManager.CancelEdit();return false;')) ?>
						</div>
					</td>
				</tr>
			</table>
			
			<div class="context-switcher">
				<div class="context-header">
					<label><?php echo lang("related to")?></label>
				</div>
				<div class="context-body" style="display:none">
					<?php render_dimension_trees(Timeslots::instance()->getObjectTypeId(), $genid, array());	?>
				</div>
			</div>
			
		</div>
	</div>
	<div id="<?php echo $genid ?>TMTimespanAddNew" class="TMTimespanAddNew" style="padding: 6px 0;<?php echo (!$draw_inputs ? "" : 'display:none;') ?>">
		<?php
			$names = array();
			$context = active_context();
			foreach ($context as $dimension) {
				$names[] = $dimension->getName();
			} 
		?>
		<span class="desc" style="padding: 0 12px;">* <?php echo lang('select member to add timeslots', implode(", ", $names))?></span>
	</div>

	</td>
	<td class="coViewTopRight">&nbsp;</td>
</tr>
<tr><td colspan="2" class="coViewRight"></td></tr>
<tr>
	<td colspan="2" class="coViewBody">
		<div id="<?php echo $genid ?>TMTimespanContents" style="width:100%" class="TMTimespanContents">
		<div style="padding:7px">
			<table style="width:100%" id="<?php echo $genid ?>TMTimespanTable">
			<tr>
				<td width='70px'><span class="bold"><?php echo lang('date') ?></span></td>
				<td width='15%'><span class="bold"><?php echo lang('user') ?></span></td>
				<td width='20%'><span class="bold"><?php echo lang('related to') ?></span></td>
				<td width='180px'><span class="bold"><?php echo lang('last updated by') ?></span></td>
				<td width='60px'><span class="bold"><?php echo lang('time') ?></span></td>
				<?php if ($show_billing) { ?>
					<td width="100px"><span class="bold"><?php echo lang('billing') ?></span></td>
				<?php } ?>
				<td><span class="bold"><?php echo lang('description') ?></span></td>
				<td></td>
			</tr>
			</table>
		</div>
		</div>
	</td>
	<td class="coViewRight"></td>
</tr>
<tr>
	<td colspan="2" class="coViewBody">
	<?php if ($total > 0) {
		$page = intval($start / $limit);
		$totalPages = ceil($total / $limit);
		if ($totalPages > 1) {
			$a_nav = array(
				'<span class="x-tbar-page-first" style="padding-left:16px"/>', 
				'<span class="x-tbar-page-prev" style="padding-left:16px"/>', 
				'<span class="x-tbar-page-next" style="padding-left:16px"/>', 
				'<span class="x-tbar-page-last" style="padding-left:16px"/>'
			);
			$nav = '';
			if ($page != 0) { ?>
				<a class="internalLink" href="<?php echo get_url('time', 'index', array('start' => '0', 'limit' => $limit)) ?>"><span class="x-tbar-page-first db-ico" style="padding-left:16px">&nbsp;</span></a>
				<a class="internalLink" href="<?php  echo get_url('time', 'index', array('start' => $start - $limit, 'limit' => $limit)) ?>"><span class="x-tbar-page-prev db-ico" style="padding-left:16px">&nbsp;</span></a>&nbsp;
			<?php } else { ?>
				<span class="og-disabled x-tbar-page-first db-ico" style="padding-left:16px">&nbsp;</span>
				<span class="og-disabled x-tbar-page-prev db-ico" style="padding-left:16px">&nbsp;</span>&nbsp;
			<?php }
			for ($i = 1; $i < $totalPages + 1; $i++) {
				$off = $limit * ($i - 1);
				if(($i != $page + 1) && abs($i - 1 - $page) <= 2 ) { ?>
					<a class="internalLink" href="<?php echo get_url('time', 'index', array('start' => $off, 'limit' => $limit)) ?>"><?php echo $i ?></a>&nbsp;
				<?php } else if($i == $page + 1) { ?>
					<span class="bold"><?php echo $i ?></span>&nbsp;
				<?php }
			}
			if ($page < $totalPages - 1) {
				$off = $start + $limit; ?>
				<a class="internalLink" href="<?php echo get_url('time', 'index', array('start' => $off, 'limit' => $limit)) ?>"><span class="x-tbar-page-next db-ico" style="padding-left:16px">&nbsp;</span></a>
				<?php $off = $limit * ($totalPages - 1); ?>
				<a class="internalLink" href="<?php echo get_url('time', 'index', array('start' => $off, 'limit' => $limit)) ?>"><span class="x-tbar-page-last db-ico" style="padding-left:16px">&nbsp;</span></a>
			<?php } else { ?>
				<span class="og-disabled x-tbar-page-next db-ico" style="padding-left:16px">&nbsp;</span>
				<span class="og-disabled x-tbar-page-last db-ico" style="padding-left:16px">&nbsp;</span>
			<?php } ?>
			<br/><span class='desc'>&nbsp;<?php echo lang('total') . ": " . $totalPages . " " . lang('pages') ?></span>
		<?php }
	} ?>
	</td>
	<td class="coViewRight"></td>
</tr>
<tr>
	<td class="coViewBottomLeft"></td>
	<td class="coViewBottom">&nbsp;</td>
	<td class="coViewBottomRight"></td>
</tr>
</table>
</div>

<script>
	ogTimeManager.loadDataFromHF('<?php echo $genid ?>');
	ogTimeManager.drawTasks('<?php echo $genid ?>');
	ogTimeManager.drawTimespans('<?php echo $genid ?>');	
	Ext.getCmp("<?php echo $genid ?>timeslot[date]Cmp").focus();
	$('.context-header').click(function(){
		$('.context-body').slideToggle();
	});
</script>
</div>
