<?php $genid = gen_id(); ?>

    <div class="layout-container contact" >
        <div class="left-column-wrapper">
            <div class="left-column view-container">
                <div class="person-view">
                	<div class="close-wrapper" onclick="og.onPersonClose();">
                		<?php echo lang("close");?>
	            		<div class="close" ></div>
	            	</div>
                    <div class="person-information">
                        <div class="picture">
                            <img src="<?php echo $contact->getPictureUrl() ?>" alt="<?php echo clean($contact->getObjectName()) ?> picture" />
                            <?php if ($contact->canEdit(logged_user())):?>
                            	<a class="change-picture" href="<?php echo $contact->getUpdatePictureUrl() ?>">[<?php echo lang("edit picture")?>]</a>
                            <?php endif;?>
                        </div>
                        <div class="basic-info">
                        
                            <h2>
                                <?php echo clean($contact->getObjectName()) ?>
                            </h2>
                            <h3><?php
                            	$jt = clean($contact->getJobTitle());
                            	$cn = $company instanceof Contact && $company->getIsCompany() ? clean($company->getObjectName()) : '';
                            	$sep = ($jt != '' && $cn != '') ? '<span> | </span>' : '';
                            	echo $jt . $sep . $cn; 
                            ?></h3>
                            
                            <h4 class="editable"><?php echo lang ('contact info') ?>
                                <?php if ($contact->canEdit(logged_user())):?>
                            		<a class="edit-link" href="<?php echo $contact->getEditUrl()?>">[<?php echo lang("edit")?>]</a>
                            	<?php endif;?>                        
                            </h4>
                            
                            <ul>
                                <li>
                                	
                                    <span class="mail">
                                    	<?php echo render_mailto($contact->getEmailAddress());?>
                                    </span>
                                    <?php echo ($contact->getPhoneNumber('work',true)) ? '- <strong>' . lang('work') . ' ' . lang('phone') . ':</strong> ' . $contact->getPhoneNumber('work',true) : ''; ?>
                                    <?php echo ($contact->getPhoneNumber('home',true)) ? '- <strong>' . lang('home') . ' ' . lang('phone') . ':</strong> ' . $contact->getPhoneNumber('home',true) : ''; ?>                                    
                                </li>
                            </ul>
                            
                            <?php if ($contact->isUser()) :?>
                            <h4 class="editable"><?php echo lang ('user info') ?>
                            	<?php if ($contact->canEdit(logged_user())):?>
                            		<a class="edit-link" href="<?php echo $contact->getEditProfileUrl()?>">[<?php echo lang("edit")?>]</a>
                            	<?php endif;?>
                            </h4>
                            
                            <ul>
                                <li>
                                    <strong><?php echo lang("username")?>: </strong><span class="username"><?php echo $contact->getUsername()?></span>
                                    <strong><?php echo lang("user type")?>: </strong><span class="username"><?php echo $contact->getUserTypeName()?></span>
                                </li>
                            </ul>
                            <?php endif ;?>
                            
                            <div class="all-info">                            
                                <h4><?php echo ucfirst(lang ('work')) ?></h4>
                                <ul> 
                                	<?php if (($contact->getAddress('work'))):?>                                   
                                    <li>
                                        <?php echo '<strong>' . lang('address') . ':</strong> ' . $contact->getStringAddress('work') . ' [<a class="map-link" href="http://maps.google.com/?q=' . $contact->getStringAddress('work') . '" target="_blank">Map</a>]' ?>
                                    </li>
                                    <?php endif;?>
                                    
                                    <?php if (($contact->getPhoneNumber('work',true))  ):?>
                                    <li>
                                        <?php echo '<strong>' . lang('phone') . ':</strong> ' . $contact->getPhoneNumber('work',true)  ?>
                                    </li>                                    
                                    <?php endif; ?>
                                    
                                    <?php if ($contact->getWebpageUrl('work')):?>
                                    <li>
                                        <?php echo  '<strong>' . lang('webpage') . ':</strong> ' . $contact->getWebpageUrl('work') ?>
                                    </li>   
                                    <?php endif;?>                                 
                                </ul>          
                                                      
                                <h4><?php echo ucfirst(lang ('home')) ?></h4>
                                <ul>
                                	<?php if(($contact->getAddress('home'))):?>
                                    <li>
                                        <?php echo  '<strong>' . lang('address') . ':</strong> ' . $contact->getStringAddress('home') . ' [<a href="http://maps.google.com/?q=' . $contact->getStringAddress('home') . '" target="_blank">Map</a>]'?>
                                    </li>      
                                    <?php endif;?>      
                                    
									<?php if(($contact->getPhoneNumber('home',true))):?>
                                    <li>
                                        <?php echo  '<strong>' . lang('phone') . ':</strong> ' .  $contact->getPhoneNumber('home',true) ; ?>                                    
                                    </li>
                                    <?php endif; ?>
                                    
                                    <?php if(($contact->getWebpageUrl('home'))):?>
                                    <li>
                                        <?php echo  '<strong>' . lang('webpage') . ':</strong> ' . $contact->getWebpageUrl('home');?>
                                    </li>
                                    <?php endif; ?>    
                              
                                </ul>                               
                                
                                <h4><?php echo ucfirst(lang ('other')) ?></h4>  
                                <ul>
                                	<?php if(($contact->getAddress('other'))):?>
                                    <li>
                                        <?php echo '<strong>' . lang('address') . ':</strong> ' . $contact->getStringAddress('other')  . ' [<a href="http://maps.google.com/?q=' . $contact->getStringAddress('other') . '" target="_blank">Map</a>]'?>
                                    </li>
                                    <?php endif; ?> 
                                    
                                    <?php if($contact->getPhoneNumber('other',true)) :?>                               
                                    <li>
                                        <?php echo '<strong>' . lang('phone') . ':</strong> ' .  $contact->getPhoneNumber('other',true); ?>                                    
                                    </li>
                                    <?php endif; ?>
                                    
                                    <?php if(($contact->getWebpageUrl('other'))):?>
                                    <li>
                                        <?php echo '<strong>' . lang('webpage') . ':</strong> ' . $contact->getWebpageUrl('other'); ?>
                                    </li>
                                    <?php endif; ?>                                   
                                </ul>  
                            </div>      
                            <a class="more-info" href="javascript:void();" style="display: none" ><?php echo lang ('more info') ?></a>
                                                  

                        </div>
                    </div>
                    <div class="clear"></div>                    
	                <?php Hook::fire('after_contact_view', $contact, $null); ?>
                </div>
                <?php if ( isset($show_person_activity) ): ?>
                <div class="person-activity">
                    <h2><?php echo lang('related to') ?></h2>
                    <ul>
                    <?php foreach($feeds as $feed): ?>
	                    <?php if ( array_var($feed, 'object_id') != $contact->getId() ) :?>
	                        <li class="<?php echo array_var($feed, 'icon') ?>">
	                            <em class="feed-date"><?php echo ucfirst (array_var($feed, 'type')); ?> - <?php echo array_var($feed, 'dateUpdated');?></em>
	                            - <a href="Javascript:;" onclick="og.openLink('<?php echo array_var($feed, 'url') ?>');"><?php echo array_var($feed, 'name') ?></a>
	                            <?php if(array_var($feed, 'content') != '') ?>
	                                <p><?php echo array_var($feed, 'content'); ?></p>
	                        </li>
	                    <?php endif ;?> 
                    <?php endforeach ?>
                    </ul>
                </div>
                <?php endif ; ?>
            </div>
        </div>	
        <div class="right-column">
            <?php 
                //Add action and properties components to right sidebar.
                tpl_assign("object", $contact);
                tpl_assign("genid", $genid);
                $this->includeTemplate(get_template_path('actions', 'co'));
                $this->includeTemplate(get_template_path('properties', 'co'));                 
            ?>
        </div>
</div>
<div class="clear"></div>

<script>
	$(function(){
	
		$("a.more-info").click(function(){
			var link = this ;
			$('div.all-info').slideToggle('slow',function(){
				if ($(this).is(':visible')) {
					$(link).text(lang("less info"));
				}else{
					$(link).text(lang("more info"));
				}
					
			});
		});
		
		// Remove empty groups
		$(".all-info ul").each(  function() {
		    var elem = $(this);
		    if (elem.children().length == 0) {
			   	elem.prev("h4").remove();
		      	elem.remove();
		    }
		});
		if (!$(".all-info").children().length ){
			$(".more-info").remove();
		}

		$("a.more-info").show();
	});
</script>