<?php
require_javascript('og/modules/addMessageForm.js'); 
require_javascript('og/EventRelatedPopUp.js');
$genid = gen_id(); 
?>
<script>
	var genid = '<?php echo $genid ?>';

	og.cal_hide = function(id) {
		document.getElementById(id).style.display = "none";
	}

	og.cal_show = function(id) {
		document.getElementById(id).style.display = "block";
	}
	
	og.toggleDiv = function(div_id){
		var theDiv = document.getElementById(div_id);
		dis = !theDiv.disabled;
	    var theFields = theDiv.getElementsByTagName('*');
	    for (var i=0; i < theFields.length;i++) theFields[i].disabled=dis;
	    theDiv.disabled=dis;
	}
	
	og.changeRepeat = function() {
		og.cal_hide("cal_extra1");
		og.cal_hide("cal_extra2");
		og.cal_hide("cal_extra3");
		og.cal_hide("<?php echo $genid?>add_reminders_warning");
		if(document.getElementById("daily").selected){
			document.getElementById("word").innerHTML = '<?php echo escape_single_quotes(lang("days"))?>';
			og.cal_show("cal_extra1");
			og.cal_show("cal_extra2");
			og.cal_show("<?php echo $genid?>add_reminders_warning");
		} else if(document.getElementById("weekly").selected){
			document.getElementById("word").innerHTML =  '<?php echo escape_single_quotes(lang("weeks"))?>';
			og.cal_show("cal_extra1");
			og.cal_show("cal_extra2");
			og.cal_show("<?php echo $genid?>add_reminders_warning");
		} else if(document.getElementById("monthly").selected){
			document.getElementById("word").innerHTML =  '<?php echo escape_single_quotes(lang("months"))?>';
			og.cal_show("cal_extra1");
			og.cal_show("cal_extra2");
			og.cal_show("<?php echo $genid?>add_reminders_warning");
		} else if(document.getElementById("yearly").selected){
			document.getElementById("word").innerHTML =  '<?php echo escape_single_quotes(lang("years"))?>';
			og.cal_show("cal_extra1");
			og.cal_show("cal_extra2");
			og.cal_show("<?php echo $genid?>add_reminders_warning");
		} else if(document.getElementById("holiday").selected){
			og.cal_show("cal_extra3");
			og.cal_show("<?php echo $genid?>add_reminders_warning");
		}
	}
	
	og.confirmEditRepEvent = function(ev_id, is_repetitive) {
		if (is_repetitive) {
			return confirm(lang('confirm repeating event edition'));
		}
		return true;
	}
	
	og.updateEventStartDate = function() {
		var picker = Ext.getCmp(genid + 'event[start_value]Cmp');
		var old_date = picker.getValue();
		var r_dow = Ext.get(genid + 'event[repeat_dow]').getValue();
		var r_wnum = Ext.get(genid + 'event[repeat_wnum]').getValue();
		
		var date = new Date();
		date.setMonth(old_date.getMonth());
		date.setFullYear(old_date.getFullYear());
		for (i=1; i<=7; i++) {
			date.setDate(i);
			if (date.getDay() + 1 == r_dow) break;
		}
		for (i = 1; i < r_wnum; i++) date.setDate(date.getDate() + 7);
		picker.setValue(date);
	}
	
	og.updateRepeatHParams = function() {
		var picker = Ext.getCmp(genid + 'event[start_value]Cmp');
		var orig_date = picker.getValue();
		var r_dow = document.getElementById(genid + 'event[repeat_dow]');
		var r_wnum = document.getElementById(genid + 'event[repeat_wnum]');
		if (r_dow && r_wnum) {
			var date = new Date();
			date.setMonth(orig_date.getMonth());
			date.setFullYear(orig_date.getFullYear());
			date.setDate(1);
			var first_dow = date.getDay();
			var wnum = date.getDay() == 0 ? -1 : 0;
			for (i=1; i<=orig_date.getDate(); i++) {
				date.setDate(i);
				if (date.getDay() == first_dow) wnum++;
			}
			if (wnum > 4) wnum = 4;
			
			r_dow.selectedIndex = orig_date.getDay();
			r_wnum.selectedIndex = wnum - 1;
		}
	}
	
</script>


<?php
/*
	Copyright (c) Reece Pegues
	sitetheory.com

    Reece PHP Calendar is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or 
	any later version if you wish.

    You should have received a copy of the GNU General Public License
    along with this file; if not, write to the Free Software
    Foundation Inc, 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
$object = $event;

$day =  array_var($event_data, 'day');
$month =  array_var($event_data, 'month');
$year =  array_var($event_data, 'year');
$all = true;
if (active_project()!= null)
	$all = false;	
$filter_user = isset($_GET['user_id']) ? $_GET['user_id'] : logged_user()->getId();

$use_24_hours = user_config_option('time_format_use_24');

// get dates
$setlastweek='';
$pm = 0;
if($event->isNew()) { 
	$username = '';
	$desc = '';
	
	// if adding event to today, make the time current time.  Else just make it 6PM (you can change that)
	if( "$year-$month-$day" == date("Y-m-d") ) $hour = date('G') + 1;
	else $hour = 18;
	// organize time by 24-hour or 12-hour clock.
	$pm = 0;
	if(!$use_24_hours) {
		if($hour >= 12) {
			$hour = $hour - 12;
			$pm = 1;
		}
	}
	// set default minute and duration times.
	$minute = 0;
	$durhr = 1;
	$durday = 0;
	$durmin = 0;
	// set other defaults
	$rjump = 1;
	// set type of event to default of 1 (nothing)
	$typeofevent = 1;
}

$form_on_submit = "return og.handleMemberChooserSubmit('$genid', ".$event->manager()->getObjectTypeId().");";

if($event->isNew()) {
	$form_view_url = get_url('event', 'add')."&view=". array_var($_GET, 'view','month');
} else {
	$form_view_url = $event->getEditUrl()."&view=". array_var($_GET, 'view','month');
} 

$visible_cps = CustomProperties::countVisibleCustomPropertiesByObjectType($object->getObjectTypeId());

?>
	<form id="<?php echo $genid ?>submit-edit-form" class="add-event" style="height:100%;background-color:white" class="internalForm" action="<?php echo $form_view_url; ?>" method="post" onsubmit="<?php echo $form_on_submit ?>">
	<input type="hidden" id="event[pm]" name="event[pm]" value="<?php echo $pm?>">
        <input id="<?php echo $genid?>view_related" type="hidden" name="view_related" value="<?php echo $event_related ?>" />
        <input id="<?php echo $genid?>type_related" type="hidden" name="type_related" value="only" />
	<div class="event">	
	<div class="coInputHeader">
		<div class="coInputHeaderUpperRow">
			<div class="coInputTitle">
				<table style="width:535px">
				<tr>
					<td>
					<?php echo $event->isNew() ? lang('new event') : lang('edit event') ?></td>
					<td style="text-align:right">
					<?php
						$is_repetitive = $event->isRepetitive() ? 'true' : 'false'; 
						echo submit_button($event->isNew() ? lang('add event') : lang('save changes'),'e',array('style'=>'margin-top:0px;margin-left:10px', 'tabindex' => 200, 'onclick' => (!$event->isNew() ? "javascript:if(!og.confirmEditRepEvent('".$event->getId()."',$is_repetitive)) return false;" : '')));
					?>
					</td>
				</tr>
				</table>
			</div>		
		</div>
		<div style="text-align:left;"><?php echo label_tag(lang('subject'), 'taskListFormName', true) . text_field('event[name]', array_var($event_data, 'name'), 
	    		array('class' => 'title', 'id' => 'eventSubject', 'tabindex' => '1', 'maxlength' => '100', 'tabindex' => '10')) ?>
	    </div>
	 
	 	<?php $categories = array(); Hook::fire('object_edit_categories', $object, $categories); ?>
	 	
	 	<div style="padding-top:5px;text-align:left;">
		<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_event_select_context_div',this)" <?php echo ($event->isNew() ? '' : '')?>><?php echo lang('context') ?></a> -
		<a href='#' class='option' onclick="og.ToggleTrap('trap3', 'fs3');og.toggleAndBolden('<?php echo $genid ?>add_event_description_div', this)"><?php echo lang('description')?></a> - 
		<a href='#' class='option' onclick="og.ToggleTrap('trap4', 'fs4');og.toggleAndBolden('<?php echo $genid ?>event_repeat_options_div', this)"><?php echo lang('CAL_REPEATING_EVENT')?></a> -
		<a href='#' class='option' onclick="og.ToggleTrap('trap5', 'fs5');og.toggleAndBolden('<?php echo $genid ?>add_reminders_div', this)"><?php echo lang('object reminders')?></a> - 
		<a href='#' class='option <?php echo $visible_cps>0 ? 'bold' : ''?>' onclick="og.ToggleTrap('trap6', 'fs6');og.toggleAndBolden('<?php echo $genid ?>add_custom_properties_div', this)"><?php echo lang('custom properties') ?></a> - 
		<a href="#" class="option" onclick="og.ToggleTrap('trap7', 'fs7');og.toggleAndBolden('<?php echo $genid ?>add_subscribers_div',this)"><?php echo lang('object subscribers') ?></a> - 
		<a href="#" class="option" onclick="og.ToggleTrap('trap8', 'fs8');og.toggleAndBolden('<?php echo $genid ?>add_linked_objects_div',this)"><?php echo lang('linked objects') ?></a> - 
		<a href="#" class="option" onclick="og.ToggleTrap('trap9', 'fs9');og.toggleAndBolden('<?php echo $genid ?>add_event_invitation_div', this);"><?php echo lang('event invitations') ?></a>
		<?php foreach ($categories as $category) { ?>
			- <a href="#" class="option" <?php if ($category['visible']) echo 'style="font-weight: bold"'; ?> onclick="og.toggleAndBolden('<?php echo $genid . $category['name'] ?>', this)"><?php echo lang($category['name'])?></a>
		<?php } ?>
		</div>
		</div>
	
		<div class="coInputSeparator"></div>
		<div class="coInputMainBlock">	
			<input id="<?php echo $genid?>updated-on-hidden" type="hidden" name="updatedon" value="<?php echo $event->isNew() ? '' : $event->getUpdatedOn()->getTimestamp() ?>">
			<input id="<?php echo $genid?>merge-changes-hidden" type="hidden" name="merge-changes" value="" >
			<input id="<?php echo $genid?>genid" type="hidden" name="genid" value="<?php echo $genid ?>" >
		
		<div id="<?php echo $genid ?>add_event_select_context_div" style="display:none" >
			<fieldset>
				<legend><?php echo lang('context') ?></legend>
				<?php
					if ($event->isNew()) {
						render_dimension_trees($event->manager()->getObjectTypeId(), $genid, null, array('select_current_context' => true));
					} else {
						render_dimension_trees($event->manager()->getObjectTypeId(), $genid, $event->getMemberIds());
					} 
				?>
			</fieldset>
		</div>
		<div id="trap1"><fieldset id="fs1" style="height:0px;border:0px;padding:0px;display:none"><span style="color:#FFFFFF;"></span></fieldset></div>
		
		<div id="<?php echo $genid ?>add_event_description_div" style="display:none">
			<fieldset>
				<legend><?php echo lang('description')?></legend>
				<?php echo textarea_field('event[description]',array_var($event_data, 'description'), array('id' => 'descriptionFormText', 'tabindex' => '30'));?>
			</fieldset>
		</div>
		<div id="trap3"><fieldset id="fs3" style="height:0px;border:0px;padding:0px;display:none"><span style="color:#FFFFFF;"></span></fieldset></div>
		
<?php 
	$occ = array_var($event_data, 'occ'); 
	$rsel1 = array_var($event_data, 'rsel1'); 
	$rsel2 = array_var($event_data, 'rsel2'); 
	$rsel3 = array_var($event_data, 'rsel3'); 
	$rnum = array_var($event_data, 'rnum'); 
	$rend = array_var($event_data, 'rend');?>
		
	<div id="<?php echo $genid ?>event_repeat_options_div" style="display:none">
		<fieldset>
			<legend><?php echo lang('CAL_REPEATING_EVENT')?></legend>
			<?php
			// calculate what is visible given the repeating options
			$hide = '';
			$hide2 = (isset($occ) && $occ == 6)? '' : "display: none;";
			if((!isset($occ)) OR $occ == 1 OR $occ=="6" OR $occ=="") $hide = "display: none;";
			// print out repeating options for daily/weekly/monthly/yearly repeating.
			if(!isset($rsel1)) $rsel1=true;
			if(!isset($rsel2)) $rsel2="";
			if(!isset($rsel3)) $rsel3="";
			if(!isset($rnum) || $rsel2=='') $rnum="";
			if(!isset($rend) || $rsel3=='') $rend="";
			if(!isset($hide2) ) $hide2="";?>
			
			<table border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td align="left" valign="top" style="padding-bottom:6px">
						<table>
							<tr>
								<td align="left" valign="top" style="padding-bottom:6px">
									<?php echo lang('CAL_REPEAT')?> 
										<select name="event[occurance]" onChange="og.changeRepeat()" tabindex="40">
											<option value="1" id="today"<?php if(isset($occ) && $occ == 1) echo ' selected="selected"'?>><?php echo lang('CAL_ONLY_TODAY')?></option>
											<option value="2" id="daily"<?php if(isset($occ) && $occ == 2) echo ' selected="selected"'?>><?php echo lang('CAL_DAILY_EVENT')?></option>
											<option value="3" id="weekly"<?php if(isset($occ) && $occ == 3) echo ' selected="selected"'?>><?php echo lang('CAL_WEEKLY_EVENT')?></option>
											<option value="4" id="monthly"<?php if(isset($occ) && $occ == 4) echo ' selected="selected"'?>><?php echo lang('CAL_MONTHLY_EVENT') ?></option>
											<option value="5" id="yearly"<?php if(isset($occ) && $occ == 5) echo  ' selected="selected"'?>><?php echo lang('CAL_YEARLY_EVENT') ?></option>
											<option value="6" id="holiday"<?php if(isset($occ) && $occ == 6)  echo ' selected="selected"'?>><?php echo lang('CAL_HOLIDAY_EVENT') ?></option>
										</select>
									<?php if (isset($occ) && $occ > 1 && $occ < 6){ ?>
									<script>
										og.changeRepeat();
									</script>
									<?php } ?>
								</td>
							</tr>
						</table>
					</td>
					</tr><tr>
					<td>
						<div id="cal_extra2" style="width: 400px; align: center; text-align: left; <?php echo $hide ?>">
							<div id="cal_extra1" style="<?php echo $hide ?>">
								<?php echo lang('CAL_EVERY') ."&nbsp;". text_field('event[occurance_jump]',array_var($event_data, 'rjump', '1'), array('class' => 'title','size' => '2', 'tabindex' => '50', 'maxlength' => '100', 'style'=>'width:25px')) ?>
								<span id="word"></span>
							</div>
							<table>
                                                                <script type="text/javascript">
                                                                        og.selectRepeatMode = function(mode) {
                                                                                var id = '';
                                                                                if (mode == 1) id = 'repeat_opt_forever';
                                                                                else if (mode == 2) id = 'repeat_opt_times';
                                                                                else if (mode == 3) id = 'repeat_opt_until';
                                                                                if (id != '') {
                                                                                        el = document.getElementById('<?php echo $genid ?>'+id);
                                                                                        if (el) el.checked = true;
                                                                                } 
                                                                        }
                                                                </script>
								<tr><td colspan="2" style="vertical-align:middle; height: 22px;">
									<?php echo radio_field('event[repeat_option]',$rsel1,array('id' => $genid.'repeat_opt_forever','value' => '1', 'tabindex' => '60')) ."&nbsp;". lang('CAL_REPEAT_FOREVER')?>
								</td></tr>
								<tr><td colspan="2" style="vertical-align:middle">
									<?php echo radio_field('event[repeat_option]',$rsel2,array('id' => $genid.'repeat_opt_times','value' => '2', 'tabindex' => '70')) ."&nbsp;". lang('CAL_REPEAT');
									echo "&nbsp;" . text_field('event[repeat_num]', $rnum, array('size' => '3', 'id' => 'repeat_num', 'maxlength' => '3', 'style'=>'width:25px', 'tabindex' => '80', 'onchange' => 'og.selectRepeatMode(2);')) ."&nbsp;" . lang('CAL_TIMES') ?>
								</td></tr>
								<tr><td style="vertical-align:middle">
									<?php echo radio_field('event[repeat_option]',$rsel3,array('id' => $genid.'repeat_opt_until','value' => '3', 'tabindex' => '90')) ."&nbsp;". lang('CAL_REPEAT_UNTIL');?>
								</td><td style="padding-left:8px;">
									<?php echo pick_date_widget2('event[repeat_end]', $rend, $genid, 95);?>
								</td></tr>
							</table>
						</div>
						<div id="cal_extra3" style="width: 400px; align: center; text-align: left; <?php echo $hide2 ?>'">
							<?php
								echo lang('CAL_REPEAT') . "&nbsp;";
								$options = array(
									option_tag(lang('1st'), 1, array_var($event_data, 'repeat_wnum') == 1 ? array("selected" => "selected") : null),
									option_tag(lang('2nd'), 2, array_var($event_data, 'repeat_wnum') == 2 ? array("selected" => "selected") : null),
									option_tag(lang('3rd'), 3, array_var($event_data, 'repeat_wnum') == 3 ? array("selected" => "selected") : null),
									option_tag(lang('4th'), 4, array_var($event_data, 'repeat_wnum') == 4 ? array("selected" => "selected") : null),
								);
								echo select_box('event[repeat_wnum]', $options, array("id" => $genid."event[repeat_wnum]", "onchange" => "og.updateEventStartDate();"));
								
								$options = array(
									option_tag(lang('sunday'), 1, array_var($event_data, 'repeat_dow') == 1 ? array("selected" => "selected") : null),
									option_tag(lang('monday'), 2, array_var($event_data, 'repeat_dow') == 2 ? array("selected" => "selected") : null),
									option_tag(lang('tuesday'), 3, array_var($event_data, 'repeat_dow') == 3 ? array("selected" => "selected") : null),
									option_tag(lang('wednesday'), 4, array_var($event_data, 'repeat_dow') == 4 ? array("selected" => "selected") : null),
									option_tag(lang('thursday'), 5, array_var($event_data, 'repeat_dow') == 5 ? array("selected" => "selected") : null),
									option_tag(lang('friday'), 6, array_var($event_data, 'repeat_dow') == 6 ? array("selected" => "selected") : null),
									option_tag(lang('saturday'), 7, array_var($event_data, 'repeat_dow') == 7 ? array("selected" => "selected") : null),
								);
								echo select_box('event[repeat_dow]', $options, array("id" => $genid."event[repeat_dow]", "onchange" => "og.updateEventStartDate();"));
								echo "&nbsp;" . lang('every') . "&nbsp;";
								$options = array();
								for ($i=1; $i<=12; $i++) {
									$options[] = option_tag("$i", $i, array_var($event_data, 'repeat_mjump') == $i ? array("selected" => "selected") : null);
								}
								echo select_box('event[repeat_mjump]', $options, array("id" => $genid."event[repeat_mjump]"));
								echo "&nbsp;" . lang('months');
							?>
						</div>
					</td>
				</tr>
                                <tr id="<?php echo $genid ?>repeat_days">
                                    <td>
                                        <table>
                                            <tr>
                                                <td>
                                                    <input class="checkbox" type="checkbox" value="1" name="event[repeat_saturdays]"/>
                                                    <?php echo lang('repeat on saturdays')?>                                                
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <input class="checkbox" type="checkbox" value="1" name="event[repeat_sundays]"/>
                                                    <?php echo lang('repeat on sundays')?>                                                
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <input class="checkbox" type="checkbox" value="1" name="event[working_days]"/>
                                                    <?php echo lang('repeat working days')?>                                                
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
			</table>
		</fieldset>
	</div>
	<div id="trap4"><fieldset id="fs4" style="height:0px;border:0px;padding:0px;display:none"><span style="color:#FFFFFF;"></span></fieldset></div>

	<div id="<?php echo $genid ?>add_reminders_div" style="display:none">
	<fieldset>
	<legend><?php echo lang('object reminders')?></legend>
		<div id="<?php echo $genid ?>add_reminders_warning" class="desc" style="display:none;">
			<?php echo lang('reminders will not apply to repeating events') ?>
		</div>
		<?php echo render_add_reminders($object, "start", null, null, "event");?>
	</fieldset>
	</div>
	<div id="trap5"><fieldset id="fs5" style="height:0px;border:0px;padding:0px;display:none"><span style="color:#FFFFFF;"></span></fieldset></div>

	
	<div id="<?php echo $genid ?>add_custom_properties_div" style="<?php echo ($visible_cps > 0 ? "" : "display:none") ?>">
	<fieldset>
	<legend><?php echo lang('custom properties')?></legend>
		<?php echo render_object_custom_properties($object, false) ?>
		<?php //echo render_add_custom_properties($object);?>
	</fieldset>
	</div>
	<div id="trap6"><fieldset id="fs6" style="height:0px;border:0px;padding:0px;display:none"><span style="color:#FFFFFF;"></span></fieldset></div>

	<div id="<?php echo $genid ?>add_subscribers_div" style="display:none">
		<fieldset>
		<legend><?php echo lang('object subscribers') ?></legend>
		<div id="<?php echo $genid ?>add_subscribers_content">
			<?php echo render_add_subscribers($object, $genid); ?>
		</div>
		</fieldset>
	</div>
	
	<div id="trap7"><fieldset id="fs7" style="height:0px;border:0px;padding:0px;display:none"><span style="color:#FFFFFF;"></span></fieldset></div>

	<div style="display:none" id="<?php echo $genid ?>add_linked_objects_div">
	<fieldset>
		<legend><?php echo lang('linked objects') ?></legend>
		<?php echo render_object_link_form($object) ?>
	</fieldset>	
	</div>
	<div id="trap8"><fieldset id="fs8" style="height:0px;border:0px;padding:0px;display:none"><span style="color:#FFFFFF;"></span></fieldset></div>
	
	<div id="<?php echo $genid ?>add_event_invitation_div" style="display:none" class="og-add-subscribers">
	<fieldset id="emailNotification">
		<legend><?php echo lang('event invitations') ?></legend>
		<?php // ComboBox for Assistance confirmation 
			if (!$event->isNew()) {
				$event_invs = $event->getInvitations();
				if (isset($event_invs[$filter_user])) {
					$event_inv_state = $event_invs[$filter_user]->getInvitationState();
				} else {
					$event_inv_state = -1;
				}
				
				if ($event_inv_state != -1) {
					$options = array(
						option_tag(lang('yes'), 1, ($event_inv_state == 1)?array('selected' => 'selected'):null),
						option_tag(lang('no'), 2, ($event_inv_state == 2)?array('selected' => 'selected'):null),
						option_tag(lang('maybe'), 3, ($event_inv_state == 3)?array('selected' => 'selected'):null)
					);
					if ($event_inv_state == 0) {
						$options[] = option_tag(lang('decide later'), 0, ($event_inv_state == 0) ? array('selected' => 'selected'):null);
					}
					?>
					<table><tr><td style="padding-right: 6px;"><label for="eventFormComboAttendance" class="combobox"><?php echo lang('confirm attendance') ?></label></td><td>
					<?php echo select_box('event[confirmAttendance]', $options, array('id' => 'eventFormComboAttendance', 'tabindex' => '100'));?>
					</td></tr></table>	
			<?php	} //if			
			} // if ?>

			<p><?php echo lang('event invitations desc') ?></p>
			<p><?php echo checkbox_field('event[send_notification]', array_var($event_data, 'send_notification', $event->isNew()), array('id' => $genid . 'eventFormSendNotification', 'tabindex' => '110')) ?>
			<label for="<?php echo $genid ?>eventFormSendNotification" class="checkbox"><?php echo lang('send new event notification') ?></label></p>
			<p><?php echo checkbox_field('event[subscribe_invited]', ($event->isNew())?true:array_var($event_data, 'subscribe_invited', false), array('id' => $genid . 'eventFormSubscribeInvited', 'tabindex' => '111')) ?>
			<label for="<?php echo $genid ?>eventFormSubscribeInvited" class="checkbox"><?php echo lang('subscribe invited users') ?></label></p>
			
	</fieldset>
	</div>	
	<div id="trap9"><fieldset id="fs9" style="height:0px;border:0px;padding:0px;display:none"><span style="color:#FFFFFF;"></span></fieldset></div>

<div>
<fieldset><legend><?php echo lang('CAL_TIME_AND_DURATION') ?></legend>
<table>
	<tr style="padding-bottom:4px">
		<td align="right" style="padding-right:10px;padding-bottom:6px;padding-top:2px"><?php echo lang('CAL_DATE') ?></td>
		<td align='left'><?php
				$tmph = array_var($event_data, 'hour') == -1 ? 0 : array_var($event_data, 'hour');
				$tmpm = array_var($event_data, 'minute') == -1 ? 0 : array_var($event_data, 'minute');
				$dv_start = DateTimeValueLib::make($tmph, $tmpm, 0, $month, $day, $year);
				$event->setStart($dv_start);
				echo pick_date_widget2('event[start_value]', $event->getStart(), $genid, 120); ?>
		</td>
	</tr>

	<tr style="padding-bottom:4px">
		<td align="right" style="padding-right:10px;padding-bottom:6px;padding-top:2px">
			<?php echo lang('CAL_TIME') ?>
		</td>
		<td>
		<?php
			$hr = array_var($event_data, 'hour');
		 	$minute = array_var($event_data, 'minute');
			$is_pm = array_var($event_data, 'pm');
			$time_val = "$hr:" . str_pad($minute, 2, '0') . ($use_24_hours ? '' : ' '.($is_pm ? 'PM' : 'AM'));
			echo pick_time_widget2('event[start_time]', $time_val, $genid, 130);
		?>
		</td>
	</tr>
	<!--   begin printing the duration options-->
	<tr>
		<td align="right" style="padding-right:10px;padding-bottom:6px;padding-top:2px"><?php echo lang('CAL_DURATION') ?></td>
		<td align="left">
		<div id="<?php echo $genid ?>ev_duration_div">
			<select name="event[durationhour]" size="1" tabindex="150">
			<?php
			for($i = 0; $i < 24; $i++) {
				echo "<option value='$i'";
				if(array_var($event_data, 'durationhour')== $i) echo ' selected="selected"';
				echo ">$i</option>\n";
			}
			?>
			</select> <?php echo lang('CAL_HOURS') ?> <select
				name="event[durationmin]" size="1" tabindex="160">
				<?php
				// print out the duration minutes drop down
				$durmin = array_var($event_data, 'durationmin');
				for($i = 0; $i <= 59; $i = $i + 15) {
					echo "<option value='$i'";
					if($durmin >= $i && $i > $durmin - 15) echo ' selected="selected"';
					echo sprintf(">%02d</option>\n", $i);
				}
				?>
			</select> 
		</div>
		</td>
	</tr>
	<tr style="padding-bottom:4px">
		<td align="right" style="padding-right:10px;padding-bottom:6px;padding-top:2px">&nbsp;</td>
		<td align='left'>
			<?php
			echo checkbox_field('event[type_id]',array_var($event_data, 'typeofevent') == 2, array('id' => 'format_html','value' => '2', 'tabindex' => '170', 'onchange' => 'og.toggleDiv(\''.$genid.'event[start_time]\'); og.toggleDiv(\''.$genid.'ev_duration_div\');'));
			echo lang('CAL_FULL_DAY');
			?>
		</td>
	</tr>

	<!--   print extra time options-->
	
</table>
</fieldset>
</div>

<?php foreach ($categories as $category) { ?>
	<div <?php if (!$category['visible']) echo 'style="display:none"' ?> id="<?php echo $genid . $category['name'] ?>">
	<fieldset>
		<legend><?php echo lang($category['name'])?><?php if ($category['required']) echo ' <span class="label_required">*</span>'; ?></legend>
		<?php echo $category['content'] ?>
	</fieldset>
	</div>
	<?php } ?>

	<input type="hidden" name="cal_origday" value="<?php echo $day?>">
	<input type="hidden" name="cal_origmonth" value="<?php echo $month?>">
	<input type="hidden" name="cal_origyear" value="<?php echo $year?>">


	<?php echo submit_button($event->isNew() ? lang('add event') : lang('save changes'),'e',array('style'=>'margin-top:0px;margin-left:10px', 'tabindex' => 180, 'onclick' => (!$event->isNew() ? "javascript:if(!og.confirmEditRepEvent('".$event->getId()."',$is_repetitive)) return false;" : ''))); ?>
	</div></div>
</form>

<script>

og.eventInvitationsUserFilter = '<?php echo $filter_user ?>';

og.drawInnerHtml = function(companies) {
	var htmlStr = '';
	var script = "";
	var genid = Ext.id();
	htmlStr += '<div id="' + genid + 'invite_companies"></div>';
	htmlStr += '&nbsp;';
	script += 'var div = Ext.getDom(genid + \'invite_companies\');';
	script += 'div.invite_companies = {};';
	script += 'var cos = div.invite_companies;';
	htmlStr += '<div class="company-users">';
	if (companies != null) {
		for (i = 0; i < companies.length; i++) {
			comp_id = companies[i].object_id;
			comp_name = companies[i].name;
			comp_img = companies[i].logo_url;			
			script += 'cos.company_' + comp_id + ' = {id:\'' + genid + 'inviteCompany' + comp_id + '\', checkbox_id : \'inviteCompany' + comp_id + '\',users : []};';
			htmlStr += '<div onclick="App.modules.addMessageForm.emailNotifyClickCompany('+comp_id+',\'' + genid + '\',\'invite_companies\', \'invitation\')" class="company-name container-div" onmouseover="og.rollOver(this)" onmouseout="og.rollOut(this,true ,true)" >';
					htmlStr += '<input type="checkbox" style="display:none;" name="event[invite_company_'+comp_id+']" id="' + genid + 'inviteCompany'+comp_id+'" ></input>';
					htmlStr += '<label style="background: transparent url('+comp_img+') no-repeat; scroll 0% -5px;" ><span class="link-ico ico-company">'+og.clean(comp_name)+'</span></label>';
			htmlStr += '</div>';
			
			htmlStr += '<div class="company-users" style="padding-left:10px;">';
						
					for (j = 0; j < companies[i].users.length; j++) {

						usr = companies[i].users[j];
						htmlStr += '<div id="div' + genid + 'inviteUser'+usr.id+'" class="container-div user-name" style="margin-left:5px;" onmouseover="og.rollOver(this)" onmouseout="og.rollOut(this,false ,true)" onclick="og.checkUser(this)">'
						htmlStr += '<input style="display:none;" type="checkbox" class="checkbox" name="event[invite_user_'+usr.id+']" id="' + genid + 'inviteUser'+usr.id+'" value="checked"></input>';
						htmlStr += '<label style="overflow:hidden; background: transparent url('+usr.avatar_url+') no-repeat;" ><span class="link-ico ico-user" >'+og.clean(usr.name)+'</span> <br> <span style="color:#888888;font-size:90%;font-weight:normal;">'+ usr.mail+ ' </span></label>';
						script += 'cos.company_' + comp_id + '.users.push({ id:'+usr.id+', checkbox_id : \'inviteUser' + usr.id + '\'});';
						if (usr.invited)
							script += 'og.checkUser(document.getElementById(\'div' + genid + 'inviteUser'+usr.id+'\'));'
						htmlStr += '</div>';
	
					}
				htmlStr += '</div>';
			
		}
		htmlStr += '</div>';
	}
	Ext.lib.Event.onAvailable(genid + 'invite_companies', function() {
		eval(script);
	});
	return htmlStr;
};

og.drawUserList = function(success, data) {
	var companies = data.companies;

	var inv_div = Ext.get('<?php echo $genid ?>inv_companies_div');
	if (inv_div != null) inv_div.remove();
	inv_div = Ext.get('emailNotification');
	
	if (inv_div != null) {
		inv_div.insertHtml('beforeEnd', '<div id="<?php echo $genid ?>inv_companies_div">' + og.drawInnerHtml(companies) + '</div>');	
		if (Ext.isIE) inv_div.update(Ext.getDom("emailNotification").innerHTML, true);
	}
};

og.redrawUserList = function(context){
	og.openLink(og.getUrl('event', 'allowed_users_view_events', {context:context, user:og.eventInvitationsUserFilter, evid:<?php echo $event->isNew() ? 0 : $event->getId()?>}), {callback:og.drawUserList});
};
og.redrawUserList("");

Ext.getCmp(genid + 'event[start_value]Cmp').on({
	change: og.updateRepeatHParams
});

var memberChoosers = Ext.getCmp('<?php echo "$genid-member-chooser-panel-".$event->manager()->getObjectTypeId()?>').items;

if (memberChoosers) {
	memberChoosers.each(function(item, index, length) {
		item.on('all trees updated', function() {
			var dimensionMembers = {};
			memberChoosers.each(function(it, ix, l) {
				dim_id = this.dimensionId;
				dimensionMembers[dim_id] = [];
				var checked = it.getChecked("id");
				for (var j = 0 ; j < checked.length ; j++ ) {
					dimensionMembers[dim_id].push(checked[j]);
				}
			});

			var uids = App.modules.addMessageForm.getCheckedUsers('<?php echo $genid ?>');
			Ext.get('<?php echo $genid ?>add_subscribers_content').load({
				url: og.getUrl('object', 'render_add_subscribers', {
					context: Ext.util.JSON.encode(dimensionMembers),
					users: uids,
					genid: '<?php echo $genid ?>',
					otype: '<?php echo $event->manager()->getObjectTypeId()?>'
				}),
				scripts: true
			});
			og.redrawUserList(Ext.util.JSON.encode(dimensionMembers));
		});
	});
}

Ext.get('eventSubject').focus();
<?php if (array_var($event_data, 'typeofevent') == 2) echo 'og.toggleDiv(\''.$genid.'event[start_time]\'); og.toggleDiv(\''.$genid.'ev_duration_div\');'; ?>

Ext.extend(og.EventRelatedPopUp, Ext.Window, {
        accept: function() {
                this.close();
        }
});

$(document).ready(function() {
    if($("#<?php echo $genid?>view_related").val()){
        this.dialog = new og.EventRelatedPopUp();
        this.dialog.setTitle(lang('events related'));
        this.dialog.show();      
    }
});

function selectEventRelated(val){
    $("#<?php echo $genid?>type_related").val(val);
}
</script>