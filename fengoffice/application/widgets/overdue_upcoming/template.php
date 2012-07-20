<?php /* @var Project $project */
	$genid = gen_id();
?>



<div class="late-objects-widget widget">

	<div class="widget-header" onclick="og.dashExpand('<?php echo $genid?>');">
		<?php echo lang('late tasks and upcoming tasks'); ?>
		<div class="dash-expander ico-dash-expanded" id="<?php echo $genid; ?>expander"></div>
	</div>
	
	<div class="widget-body" id="<?php echo $genid; ?>_widget_body">
	<?php if (isset($overdue_upcoming_objects) && count($overdue_upcoming_objects)) : ?>
		<table id="dashTableMS" style="width:100%; margin-bottom: 10px ;">

		<?php
			$today = DateTimeValueLib::now()->beginningOfDay();
			$tomorrow = DateTimeValueLib::now()->beginningOfDay()->add('d', 1);
			$c = 0;
			
			foreach($overdue_upcoming_objects as $object):
				$c++;
				if ($object->getCompletedById() > 0) {
					$days_str = lang('completed');
					$cls = 'completed';
				} else {
					if ($object->getDueDate()){
						if ($object->getDueDate()->getTimestamp() < $today->getTimestamp()) {
							$days_str = lang('days late', $object->getLateInDays());
							$cls = 'late';
						} else if ($object->getDueDate()->getTimestamp() >= $today->getTimestamp() && $object->getDueDate()->getTimestamp() < $tomorrow->getTimestamp()) {
							$days_str = lang('today');
							$cls = 'today';
						} else {
							$days_str = lang('days left', $object->getLeftInDays());
							$cls = 'future';
						}
					}
				}?>
		    <tr class="<?php echo ' ' . ($c > 55? 'noDispLM':''); ?>" style="<?php echo $c > 55? 'display:none':'' ?>">
			    
			    <td class="date-col nobr">
			    	<div class="<?php echo isset($cls)?$cls:'';?>-row">
			    		<?php echo isset($days_str)?($days_str):lang("no due date"); ?>
			    	</div>
			    </td>
			    
			    <td class="db-ico">
			    	<div class="db-ico <?php echo $object->getIconClass()?>"></div>
			    </td>
			    
			    <td style="padding-left:5px;padding-bottom:2px;overflow:hidden;max-width:10px;vertical-align: middle;">
			    	<div class="nobr">
			    	
			    		<?php
			    			$crumbOptions = json_encode($object->getMembersToDisplayPath());
							$crumbJs = " og.getCrumbHtml($crumbOptions) "; 
			    		?>
			    		<span id="object_crumb_<?php echo $object->getId()?>"></span>
			    		<script>
							var crumbHtml = <?php echo $crumbJs?> ;
							$("#object_crumb_<?php echo $object->getId()?>").html(crumbHtml);
						</script>
			    	
						<a class="internalLink" href="<?php echo $object->getViewUrl() ?>" title="<?php echo clean($object->getObjectName()) ?>">
							<?php if ($object instanceof ProjectTask && $object->getAssignedToContactId() > 0) echo "<span class='bold'>". clean($object->getAssignedToName()).": </span>"; ?>
							<?php echo clean($object->getObjectName()) ?>
						</a>
						<?php if ($object instanceof ProjectTask) :
							$text = strlen_utf($object->getText()) > 100 ? substr_utf(html_to_text($object->getText()), 0, 100) . "..." : strip_tags($object->getText());
								if (strlen_utf($text) > 0) :
						?>
								&nbsp;-&nbsp;<span class="desc nobr"><?php echo clean($text) ?></span>
							<?php endif;?>
						<?php endif;?>
					</div>
				</td>
				
			</tr>
		<?php endforeach; ?>
		</table>
		
		<div class="">
			<a href="#" onmousedown="og.openLink(og.getUrl('task', 'new_list_tasks'), {caller:'tasks-panel'});" onclick="Ext.getCmp('tabs-panel').activate('tasks-panel');">
				<?php if ($show_more) echo lang('see all');?>
			</a>
		</div>
		
		
	<?php else:?>
		<div class="empty">
			<?php echo lang("no data to show") ?>
		</div>
	<?php endif; ?>
	
	<?php if ($render_add) : ?>
		<div class="new-task">
		
			<div class="field name">
				<label class="label"><?php echo lang("add task")?></label>
				<input type="text" class="task-name" maxlength='255'  />
			</div>

			<div class="field assigned ">
				<label><?php echo lang('assigned to')?>:</label>
				<?php echo simple_select_box('task[assigned_to_contact_id]',$users, null, array('class'=> 'assigned-to' ) ) ?>
				
			</div>
			
			
			<div class="field due">
				<label><?php echo lang('due date') ?>:</label>
				<?php echo pick_date_widget2('task[task_due_date]', null, $genid, 70, false, $genid.'due_date') ?>
			</div>
			
			<?php if (config_option('use_time_in_task_dates')) { ?>
			<div class="field due">
				<label>&nbsp;</label>
				<?php echo pick_time_widget2('task[task_due_time]', null, $genid, 75); ?>
			</div>
			<?php } ?>
			<div class="x-clear"></div>
			
			<button class="submit-task" ><?php echo lang('add task')?></button>	
			<a class= "task-more-details" href="#"><?php echo lang("more")?>>></a>
			<div class="x-clear"></div>
			
		</div>
	<?php endif;?>
	</div>
</div>


<script>
	$(function(){
		$("button.submit-task").click(function(){
			var container = $(this).closest(".widget-body") ;
			container.closest(".widget-body").addClass("loading");
			
			var name = $(container).find("input.task-name").val();
			var due_date =  $(container).find('input[name="task[task_due_date]"]').val();
			var due_time =  $(container).find('input[name="task[task_due_time]"]').val();
			var assigned_to =  $(container).find("select.assigned-to option:selected").val();


			if (name) {
				
				og.quickAddTask({
					due_date: due_date,
					due_time: due_time,
					name: name,
					assigned_to: assigned_to
				},function(){
					og.customDashboard('dashboard', 'main_dashboard',{},true);
				});
				
			}else{
				alert('<?php echo lang('name required')?>');
				$(container).find("input.add-project-field").focus();
				container.removeClass("loading");
			}	
			
		});


		$(".late-objects-widget .task-name").keypress(function(e){
			if(e.keyCode == 13){
				$("button.submit-task").click();
     		}
		});


		$(".late-objects-widget a.task-more-details").click(function(){
			var container = $(this).closest(".widget-body");
			var name = $(container).find("input.task-name").val();
			var due_date =  $(container).find('input[name="task[task_due_date]"]').val();
			var due_time =  $(container).find('input[name="task[task_due_time]"]').val();
			var assigned_to =  $(container).find("select.assigned-to option:selected").val();
			
			og.openLink(og.getUrl('task','add_task'),{
				post: {
					'name': name,
					'assigned_to_contact_id': assigned_to,
					'task_due_date': due_date,
					'task_due_time': due_time
				}
			});
		});

	});
</script>
