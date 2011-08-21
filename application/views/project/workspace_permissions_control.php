<?php 
require_javascript("og/Permissions.js");
if ($project->isNew()) {
	if (active_project() instanceof Project) {
		$permissions = active_project()->getAllPermissions();
	} else {
		$permissions = array(array(
			'wsid' => logged_user()->getId(),
			'pc' => array(1, 1),
			'pr' => array(2, 2, 2, 2, 2, 2, 2, 2, 2),
			'maxPerm' => 2
		));
	}
} else {
	$permissions = $project->getAllPermissions();
}
?>
	<input id="<?php echo $genid ?>hfPerms" type="hidden" value="<?php echo str_replace('"',"'", json_encode($permissions)) ?>"/>
	
	<input id="<?php echo $genid ?>hfPermsSend" name="permissions" type="hidden" value=""/>
<table><tr><td>
  <?php	
  echo select_users_or_groups("", (isset($projects)?$projects:null), null, "workspace-chooser$genid") ?>
  </td><td style="padding-left:20px">
  <div id="<?php echo $genid ?>project_permissions" style="display:none">
  <div id="<?php echo $genid ?>project_name" style="font-weight:bold;font-size:120%;padding-bottom:15px"></div>
  <!-- a href="#" class="internalLink" onclick="og.ogPermApplyToSubworkspaces('<?php echo $genid ?>');return false;"><?php echo lang('apply to all subworkspaces') ?></a>
  <br/><br/-->
  <table>
  	<col align=left/><col align=center/>
  	<tr style="border-bottom:1px solid #888;margin-bottom:5px"><td style="vertical-align:middle">
  		<span class="projectPermission">
			<?php echo checkbox_field($genid . 'pAll', false, array('id' => $genid . 'pAll', 'onclick' => 'og.ogPermAllChecked("' . $genid . '",this.checked)')) ?> <label style="font-weight:bold" for="<?php echo $genid ?>pAll" class="checkbox"><?php echo lang('all') ?></label>   
  		</span>
  	</td>
  	<td align=center style="padding-left:10px;padding-right:10px;width:60px;"><a href="#" class="internalLink" onclick="og.ogPermSetLevel('<?php echo $genid ?>', 2);return false;"><?php echo lang('read and write') ?></a></td>
  	<td align=center style="padding-left:10px;padding-right:10px;width:60px;"><a href="#" class="internalLink" onclick="og.ogPermSetLevel('<?php echo $genid ?>', 1);return false;"><?php echo lang('read only') ?></a></td>
  	<td align=center style="padding-left:10px;padding-right:10px;width:60px;"><a href="#" class="internalLink" onclick="og.ogPermSetLevel('<?php echo $genid ?>', 0);return false;"><?php echo lang('none no bars') ?></a></td></tr>
  	<tr>
  		<td style="padding-right:20px"><?php echo lang('messages') ?></td>
  		<td align=center><?php echo radio_field($genid . 'rg_0',false,array('onchange' => 'og.ogPermValueChanged("' . $genid . '")', 'value' => '2', 'style' => 'width:16px', 'class' => "readWritePermission")) ?></td>
  		<td align=center><?php echo radio_field($genid . 'rg_0',false,array('onchange' => 'og.ogPermValueChanged("' . $genid . '")', 'value' => '1', 'style' => 'width:16px')) ?></td>
  		<td align=center><?php echo radio_field($genid . 'rg_0',false,array('onchange' => 'og.ogPermValueChanged("' . $genid . '")', 'value' => '0', 'style' => 'width:16px')) ?></td>
    </tr>
    <tr style="background-color:#F6F6F6">
  		<td style="padding-right:20px"><?php echo lang('tasks') ?></td>
  		<td align=center><?php echo radio_field($genid . 'rg_1',false,array('onchange' => 'og.ogPermValueChanged("' . $genid . '")', 'value' => '2', 'style' => 'width:16px', 'class' => "readWritePermission")) ?></td>
  		<td align=center><?php echo radio_field($genid . 'rg_1',false,array('onchange' => 'og.ogPermValueChanged("' . $genid . '")', 'value' => '1', 'style' => 'width:16px')) ?></td>
  		<td align=center><?php echo radio_field($genid . 'rg_1',false,array('onchange' => 'og.ogPermValueChanged("' . $genid . '")', 'value' => '0', 'style' => 'width:16px')) ?></td>
    </tr>
    <tr>
  		<td style="padding-right:20px"><?php echo lang('milestones') ?></td>
  		<td align=center><?php echo radio_field($genid . 'rg_2',false,array('onchange' => 'og.ogPermValueChanged("' . $genid . '")', 'value' => '2', 'style' => 'width:16px', 'class' => "readWritePermission")) ?></td>
  		<td align=center><?php echo radio_field($genid . 'rg_2',false,array('onchange' => 'og.ogPermValueChanged("' . $genid . '")', 'value' => '1', 'style' => 'width:16px')) ?></td>
  		<td align=center><?php echo radio_field($genid . 'rg_2',false,array('onchange' => 'og.ogPermValueChanged("' . $genid . '")', 'value' => '0', 'style' => 'width:16px')) ?></td>
    </tr>
    <tr style="background-color:#F6F6F6">
  		<td style="padding-right:20px"><?php echo lang('emails') ?></td>
  		<td align=center><?php echo radio_field($genid . 'rg_3',false,array('onchange' => 'og.ogPermValueChanged("' . $genid . '")', 'value' => '2', 'style' => 'width:16px', 'class' => "readWritePermission")) ?></td>
  		<td align=center><?php echo radio_field($genid . 'rg_3',false,array('onchange' => 'og.ogPermValueChanged("' . $genid . '")', 'value' => '1', 'style' => 'width:16px')) ?></td>
  		<td align=center><?php echo radio_field($genid . 'rg_3',false,array('onchange' => 'og.ogPermValueChanged("' . $genid . '")', 'value' => '0', 'style' => 'width:16px')) ?></td>
    </tr>
    <tr>
  		<td style="padding-right:20px"><?php echo lang('comments') ?></td>
  		<td align=center><?php echo radio_field($genid . 'rg_4',false,array('onchange' => 'og.ogPermValueChanged("' . $genid . '")', 'value' => '2', 'style' => 'width:16px')) ?></td>
  		<td align=center><?php echo radio_field($genid . 'rg_4',false,array('onchange' => 'og.ogPermValueChanged("' . $genid . '")', 'value' => '1', 'style' => 'width:16px')) ?></td>
  		<td align=center><?php echo radio_field($genid . 'rg_4',false,array('onchange' => 'og.ogPermValueChanged("' . $genid . '")', 'value' => '0', 'style' => 'width:16px')) ?></td>
    </tr>
    <tr style="background-color:#F6F6F6">
  		<td style="padding-right:20px"><?php echo lang('contacts') ?></td>
  		<td align=center><?php echo radio_field($genid . 'rg_5',false,array('onchange' => 'og.ogPermValueChanged("' . $genid . '")', 'value' => '2', 'style' => 'width:16px', 'class' => "readWritePermission")) ?></td>
  		<td align=center><?php echo radio_field($genid . 'rg_5',false,array('onchange' => 'og.ogPermValueChanged("' . $genid . '")', 'value' => '1', 'style' => 'width:16px')) ?></td>
  		<td align=center><?php echo radio_field($genid . 'rg_5',false,array('onchange' => 'og.ogPermValueChanged("' . $genid . '")', 'value' => '0', 'style' => 'width:16px')) ?></td>
    </tr>
    <tr>
  		<td style="padding-right:20px"><?php echo lang('weblinks') ?></td>
  		<td align=center><?php echo radio_field($genid . 'rg_6',false,array('onchange' => 'og.ogPermValueChanged("' . $genid . '")', 'value' => '2', 'style' => 'width:16px', 'class' => "readWritePermission")) ?></td>
  		<td align=center><?php echo radio_field($genid . 'rg_6',false,array('onchange' => 'og.ogPermValueChanged("' . $genid . '")', 'value' => '1', 'style' => 'width:16px')) ?></td>
  		<td align=center><?php echo radio_field($genid . 'rg_6',false,array('onchange' => 'og.ogPermValueChanged("' . $genid . '")', 'value' => '0', 'style' => 'width:16px')) ?></td>
    </tr>
    <tr style="background-color:#F6F6F6">
  		<td style="padding-right:20px"><?php echo lang('files') ?></td>
  		<td align=center><?php echo radio_field($genid . 'rg_7',false,array('onchange' => 'og.ogPermValueChanged("' . $genid . '")', 'value' => '2', 'style' => 'width:16px', 'class' => "readWritePermission")) ?></td>
  		<td align=center><?php echo radio_field($genid . 'rg_7',false,array('onchange' => 'og.ogPermValueChanged("' . $genid . '")', 'value' => '1', 'style' => 'width:16px')) ?></td>
  		<td align=center><?php echo radio_field($genid . 'rg_7',false,array('onchange' => 'og.ogPermValueChanged("' . $genid . '")', 'value' => '0', 'style' => 'width:16px')) ?></td>
    </tr>
    <tr>
  		<td style="padding-right:20px"><?php echo lang('events') ?></td>
  		<td align=center><?php echo radio_field($genid . 'rg_8',false,array('onchange' => 'og.ogPermValueChanged("' . $genid . '")', 'value' => '2', 'style' => 'width:16px', 'class' => "readWritePermission")) ?></td>
  		<td align=center><?php echo radio_field($genid . 'rg_8',false,array('onchange' => 'og.ogPermValueChanged("' . $genid . '")', 'value' => '1', 'style' => 'width:16px')) ?></td>
  		<td align=center><?php echo radio_field($genid . 'rg_8',false,array('onchange' => 'og.ogPermValueChanged("' . $genid . '")', 'value' => '0', 'style' => 'width:16px')) ?></td>
    </tr>
    
    </table>
    <br/>
    <?php echo checkbox_field($genid . 'chk_0', false, array('id' => $genid . 'chk_0', 'onclick' => 'og.ogPermValueChanged("' . $genid . '")')) ?> <label style="font-weight:normal" for="<?php echo $genid ?>chk_0" class="checkbox"><?php echo lang('can assign to owners') ?></label>
    <br/><?php echo checkbox_field($genid . 'chk_1', false, array('id' => $genid . 'chk_1', 'onclick' => 'og.ogPermValueChanged("' . $genid . '")')) ?> <label style="font-weight:normal" for="<?php echo $genid ?>chk_1" class="checkbox"><?php echo lang('can assign to other') ?></label>
    </div>
   </td></tr></table>
<script>
	og.ogLoadPermissions('<?php echo $genid ?>'<?php if ($project->isNew()) echo ", true"; ?>);
	var wsch = Ext.getCmp('workspace-chooser<?php echo $genid ?>');
	wsch.on("usercheck", function(arguments) {
		og.ogPermAllChecked('<?php echo $genid ?>', arguments['checked'], arguments['id']);
	}, document);
	wsch.on("userselect", function() {
		og.ogPermSelectedWsChanged('<?php echo $genid ?>');
	}, document);
</script>