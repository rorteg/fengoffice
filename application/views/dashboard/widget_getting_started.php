<script>
<?php 

	/**
	 * Deteminate the events to attach
	 */
	if(!isset($_SESSION['wizard']))
	{
		if(can_manage_workspaces(logged_user()))
			$canAddWS = "1";
		else 
			$canAddWS = "0";
		
		echo
	   	"Ext.query('#addNewProject')[0].onclick = function(){og.openLink(og.getUrl('project', 'add', {wizard:'newproject'}), {caller:'project', iswizard:'true'});}; 
	   	 Ext.query('#addNewCustomer')[0].onclick = function(){og.openLink(og.getUrl('project', 'add', {wizard:'newcustomer'}), {caller:'project'});};
		 var wsCount = Ext.getCmp('workspace-panel').getWsList();
		 if(wsCount.length > 2 || !$canAddWS)
		 {
			if(Ext.query('div.dashGettingStarted'))
				Ext.get(Ext.query('div.dashGettingStarted')).remove();
		 }else
		     var element = Ext.get(Ext.query('div.dashGettingStarted'));
		     if(element)
		     	element.slideIn('t', {useDisplay:true});
		";
	}else if($_SESSION['wizard'] == 'newcustomer')
	{
		echo
	    "Ext.query('#addNewCustomerProject')[0].onclick = function(){og.openLink(og.getUrl('project', 'add', {wizard:'newproject'}), {caller:'project', iswizard:'true'});}; 
	     Ext.query('#addNewCustomerTask')[0].onclick = function(){
	     	Ext.getCmp('tabs-panel').setActiveTab(og.panels.tasks);
			setTimeout('showQuickTask()', 1500);	     	
		 };
	     var element = Ext.get(Ext.query('div.dashGettingStarted'));
	     element.slideIn('t', {useDisplay:true});
		";
		
	}else if($_SESSION['wizard'] == 'newproject')
	{
		echo
	    "Ext.query('#addNewProjectTask')[0].onclick = function(){
	     	Ext.getCmp('tabs-panel').setActiveTab(og.panels.tasks);
			setTimeout('showQuickTask()', 1500);
		 };
	     Ext.get(Ext.query('div.dashGettingStarted')).setStyle('display', 'block');	    
	    ";		
	}

?>
	/**
 	* Show quick task form 
 	*@void 
	*/	
	function showQuickTask()
	{
		document.getElementById('rx__no_tasks_info').style.display='none'; 
		document.getElementById('rx__hidden_group').style.display='block'; 
		ogTasks.drawAddNewTaskForm('unclassified');
	}

	//Attach event to done action
	if(Ext.query('#wizardDoneAction').length > 0)
	{
		Ext.query('#wizardDoneAction')[0].onclick = function()
			{
				Ext.get(Ext.query('div.dashGettingStarted')).remove();
			};
	}
</script>
<div class="wizard">
    <h2><?php echo lang ('wizard title') ?></h2>
    <p class="intro">
    	<?php 
        	if(isset($_SESSION['wizard']) && $_SESSION['wizard'] == 'newproject')
        	{
        		echo lang('wizard intro project');        						    		
        	}elseif(isset($_SESSION['wizard']) && $_SESSION['wizard'] == 'newcustomer'){
        		echo lang('wizard intro customer');        		
        	}else{
        		echo lang('wizard intro');
        	}
    	?>
    </p>
    
    <div class="actionsContainer">
        
		<?php 
        if(!isset($_SESSION['wizard']))
        {
        ?>
        <!-- Start Wizard -->
        <div class="action" id="newCustomerAction">
            <div class="icon">
                <img src="public/assets/themes/default/images/wizard/customer.gif" alt="" />
            </div>
            <div class="text">
                <a href="Javascript:;" id="addNewCustomer"><?php echo lang('wizard new customer title'); ?></a>
                <p>
                    <?php echo lang('wizard new customer')?>
                </p>
            </div>
        </div>
        
        <div class="clear"></div>
    
        <div class="action" id="newProjectAction">
            <div class="icon">
                <img src="public/assets/themes/default/images/wizard/project.gif" alt="" />
            </div>
            <div class="text">
                <a href="Javascript:;" id="addNewProject"><?php echo lang('wizard new project title'); ?></a>
                <p>
                    <?php echo lang('wizard new project')?>
                </p>
            </div>
        </div>
        <?php 
        }
        ?>
        
        <?php 
        if(isset($_SESSION['wizard']) && $_SESSION['wizard'] == 'newcustomer')
        {
			$_SESSION['wizard_task'] = true;        	
        ?>        
        <div class="action" id="newCustomerProjectAction">
            <div class="icon">
                <img src="public/assets/themes/default/images/wizard/project.gif" alt="" />
            </div>
            <div class="text">
                <a href="Javascript:;" id="addNewCustomerProject"><?php echo lang('wizard new project title'); ?></a>
                <p>
                    <?php echo lang('wizard new customer project')?>                    
                </p>
            </div>
        </div>        
        
        <div class="clear"></div>
        
        <div class="action" id="newUSerAction">
            <div class="icon">
                <img src="public/assets/themes/default/images/wizard/user.gif" alt="" />
            </div>
            <div class="text">
                <a href="index.php?c=user&amp;a=add&amp;company_id=1" target="administration">
                	<?php echo lang('wizard new user title')?>  
                </a>
                <p>
 					<?php echo lang('wizard new user')?>  
                </p>
            </div>
        </div>       
        
        <div class="clear"></div>
    
        <div class="action" id="newTaskAction">
            <div class="icon">
                <img src="public/assets/themes/default/images/wizard/tasks.gif" alt="" />
            </div>
            <div class="text">
                <a href="Javascript:;"  id="addNewCustomerTask">
                	<?php echo lang('wizard new customer task title'); ?>
                </a>
                <p>
                    <?php echo lang('wizard new customer task')?>                
                </p>
            </div>
        </div>
        <?php 
        }
        
        ?>

        <?php 
        if(isset($_SESSION['wizard']) && $_SESSION['wizard'] == 'newproject')
        {
        	$_SESSION['wizard_task'] = true;
        ?>         
        <div class="action" id="newUSerAction">
            <div class="icon">
                <img src="public/assets/themes/default/images/wizard/user.gif" alt="" />
            </div>
            <div class="text">
                <a href="index.php?c=user&amp;a=add&amp;company_id=1" target="administration"><?php echo lang('wizard new user title')?></a>
                <p>
					<?php echo lang('wizard new user')?>
                </p>
            </div>
        </div>
        <div class="clear"></div>
        <div class="action" id="newTaskAction">
            <div class="icon">
                <img src="public/assets/themes/default/images/wizard/tasks.gif" alt="" />
            </div>
            <div class="text">
                <a href="Javascript:;" id="addNewProjectTask"><?php echo lang('wizard new project task title'); ?></a>
                <p>
					<?php echo lang('wizard new project task')?>
                </p>
            </div>
        </div>
        <?php 
        }
        ?>  
    
    </div>    
    
    <div class="clear"></div>
    
    <div class="buttons">
        <div class="action">
        	<a href="Javascript:;" id="wizardDoneAction" class="
	        	<?php
		        	if(Localization::instance()->getLocale()=="es_la" || Localization::instance()->getLocale()=="es_es"){
		        		echo"closeActionEs";
		        	}else{
		        		echo"closeActionEn";
		        	}    	 
	        	?>">
        	</a>
        </div>
        <div class="message">
        	<p><?php echo lang('wizard close action') ?></p>
        </div>
    </div>
    
    <?php 
    	//Remove stored session for wizard
    	if(isset($_SESSION['wizard']))
    		unset($_SESSION['wizard'])
    ?>

</div>