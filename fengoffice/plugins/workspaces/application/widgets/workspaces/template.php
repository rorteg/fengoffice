<?php 
$members = implode (',', active_context_members(false));
$ws_dim = Dimensions::findByCode('workspaces');
?>

<div class="ws-widget widget">

	<div class="widget-header" onclick="og.dashExpand('<?php echo $genid?>');">
		<?php echo lang('workspaces')?>
		<div class="dash-expander ico-dash-expanded" id="<?php echo $genid; ?>expander"></div>
	</div>
	
	<div class="widget-body" id="<?php echo $genid; ?>_widget_body" >
	
		<div class="project-list">
		<?php foreach($data_ws as $ws):?>
			<div class="project-row-container">
                                    
				<a class="internalLink" href="javascript:void(0);" onclick="og.workspaces.onWorkspaceClick(<?php echo $ws->getId() ?>);">
					<img class="ico-color<?php echo $ws->getMemberColor() ?>" unselectable="on" src="s.gif"/>
					<?php echo $ws->getName() ?>
				</a>		
			</div>
			<div class="x-clear"></div>
		<?php endforeach;?>
		</div>
		
		<?php if ($total < count ($workspaces)) : ?>
			<a href="javascript:og.customDashboard('member', 'init', {},true)" ><?php echo lang('see all');?></a>
		<?php endif ;?>
				
		<label><?php echo lang('add project')?></label>
		<input type="text" class="ws-name" />
		<button class="submit-ws" ><?php echo lang('add')?></button>
		<a class= "ws-more-details" href="#" onclick="return false;" ><?php echo lang("more")?>>></a>
		<div class="x-clear"></div>		

	</div>
</div>


<script>
	$(function(){
		$("button.submit-ws").click(function(){
			var container = $(this).closest(".widget-body") ;
			container.closest(".widget-body").addClass("loading");
			
			var name = $(container).find("input.ws-name").val();

			if (name) {
				og.quickAddWs({
					name: name,
					parent: '<?php echo $members?>',
					dim_id: '<?php echo $ws_dim->getId()?>',
					ot_id: '<?php echo Workspaces::instance()->getObjectTypeId()?>'
				},function(){
					og.customDashboard('dashboard', 'main_dashboard',{},true);
				});
				
			}else{
				alert('<?php echo lang('name required')?>');
				$(container).find("input.add-project-field").focus();
				container.removeClass("loading");
			}	
			
		});


		$(".ws-widget .ws-name").keypress(function(e){
			if(e.keyCode == 13){
				$("button.submit-ws").click();
     		}
		});


		$(".ws-widget a.ws-more-details").click(function(){
			var container = $(this).closest(".widget-body");
			var name = $(container).find("input.ws-name").val();
			
			og.openLink(og.getUrl('member','add'),{
				get: {
					'name': name,
					'dim_id': '<?php echo $ws_dim->getId()?>',
					'parent': '<?php echo $members?>'
				}
			});
		});

	});
</script>
