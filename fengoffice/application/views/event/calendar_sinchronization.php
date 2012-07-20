<?php
set_page_title(lang('calendar sinchronization'));
$genid = gen_id();
?>

<div id="<?php echo $genid ?>sincrhonization" class="adminMailAccounts" style="height: 100%; background-color: white">
    <div class="adminHeader">
        <div class="adminTitle"><?php echo lang('calendar sinchronization') ?></div>
        <div class="adminSeparator"></div>
        <div style="float: left; width: 350px;">
            <form id ="<?php echo $genid ?>submit-sync-form" class="internalForm" action="<?php echo get_url('event', 'add_calendar_user', array('cal_user_id' => array_var($user, 'id'))); ?>" method="post">
                <?php 
                    echo label_tag(lang('account gmail'), $genid . 'auth_user', true);
                    echo text_field('auth_user', array_var($user, 'auth_user'),array('id' => $genid . 'auth_user', 'tabindex' => '1'));

                    echo label_tag(lang('password gmail'), $genid . 'auth_pass', true);
                    echo password_field('auth_pass', array_var($user, 'auth_pass'),array('id' => $genid . 'auth_pass', 'tabindex' => '2'));

                    echo label_tag(lang('sync event feng'), $genid . 'sync');
                    echo checkbox_field('sync', array_var($user, 'sync'), array('id' => $genid.'sync', 'tabindex'=>'3'));
                ?>
                <div style="clear:both"></div> 
                <input id="<?php echo $genid?>related_to" type="hidden" name="related_to" />
                <?php
                  echo button(array_var($user, 'id') ? lang('save changes') : lang('add account'), 's', array('tabindex' => '4', 'onclick' => 'submitCalendar()')); 
                ?>
            </form>
        </div>
        <?php if(count($external_calendars) > 0){?>
        <form class="internalForm" action="<?php echo get_url('event', 'import_calendars'); ?>" method="post">
            <div style="float: left; width: 600px;">
                <fieldset>
                        <legend><?php echo lang('list calendar')?></legend>
                        <div class="mail-account-item">
                            <?php echo lang('list calendar desc')?>
                        </div>
                        <?php foreach ($external_calendars as $e_calendar){?>                        
                        <div class="mail-account-item" style="clear: both;">
                                <?php 
                                    echo checkbox_field('e_calendars['.$e_calendar['title'].']', $e_calendar['sel'], array('tabindex' => '1', 'value' => $e_calendar['user']))." ".$e_calendar['title'];
                                ?>
                        </div>
                        <?php }?>
                        <?php
                          echo submit_button(lang('import calendars'), 's', array('tabindex' => '2')); 
                        ?>
                </fieldset>
            </div>
        </form>
        <?php }?>
        <div style="clear: both;"></div>
        <div style="padding-top:5px;text-align:left;">
            <a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_mail_select_context_div',this)"><?php echo lang('context') ?></a>
        </div>
    </div>
    <div id="<?php echo $genid ?>add_mail_select_context_div" style="display:none" >
        <fieldset>
                <legend><?php echo lang('context') ?></legend>
                <?php
                        if (array_var($user, 'id')) {
                                render_dimension_trees(ProjectEvents::instance()->getObjectTypeId(), $genid, array_var($user, 'related_to'));
                        } else {
                                render_dimension_trees(ProjectEvents::instance()->getObjectTypeId(), $genid, null, array('select_current_context' => true));
                        } 
                ?>
        </fieldset>
    </div>
    <?php if(isset($user) && is_array($user) && count($user)) { ?>
    <div class="adminMainBlock">
        <?php if(isset($calendars) && is_array($calendars) && count($calendars)) { ?>
        <table class="adminListing" style="min-width: 400px; margin-top: 10px;">
                <tr>
                        <th width="90%"><?php echo lang('name calendar') ?></th>
                        <th><?php echo lang('options') ?></th>
                </tr>
                <?php
                $isAlt = true;
                foreach($calendars as $calendar) {
                        $isAlt = !$isAlt;
                ?>
                <tr class="<?php echo $isAlt? 'altRow' : ''?>" id="<?php echo $genid ?>tr_<?php echo $calendar->getId();?>">
                        <td><?php echo $calendar->getCalendarName() ?></td>
                        <?php
                        $options = array();
                        //$options[] = '<a class="internalLink" href="'.get_url('event', 'calendar_sinchronization', array('cal_id' => $calendar->getId())).'">' . lang('edit') . '</a>';
                        $options[] = '<a class="internalLink" href="javascript:og.promptDeleteCalendar(' . $calendar->getId() . ')">' . lang('delete') . '</a>';
                        ?>
                        <td style="font-size: 80%;"><?php echo implode(' | ', $options) ?></td>
                </tr>
                <?php } // foreach ?>
        </table>
        <?php } else { ?> <?php echo lang('no calendars') ?> <?php } // if ?>
    </div>
    <?php }?>
</div>

<script>
	var div = document.getElementById('<?php echo $genid ?>sincrhonization');
	div.parentNode.style.backgroundColor = '#FFFFFF'; 
        
        og.DeleteCalendar = function(cal_id) {        
                                if (confirm(lang('delete calendar'))) {
                                        og.openLink(og.getUrl('event', 'delete_calendar', {
                                                cal_id : cal_id
                                        }));
                                        $('#<?php echo $genid ?>tr_'+cal_id).remove();
                                }
        };
        
        var memberChoosers = Ext.getCmp('<?php echo "$genid-member-chooser-panel-" . ProjectEvents::instance()->getObjectTypeId() ?>').items;
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
                });
            });
        }
        
        function submitCalendar() {
            og.handleMemberChooserSubmit('<?php echo $genid; ?>', <?php echo ProjectEvents::instance()->getObjectTypeId(); ?>);            
            var members = document.getElementById('<?php echo $genid ?>members');
            $('#<?php echo $genid?>related_to').val(members.value);
            $('#<?php echo $genid ?>submit-sync-form').submit();
        };
</script>
