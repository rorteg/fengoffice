/**
 *  Permissions
 *
 * Author: Alvaro Torterola (alvaro.torterola@fengoffice.com)
 */


/******************************************************
 * Functions for member selector
******************************************************/

//	Loads the permission info from a hidden field. 
//	The name of the hidden field must be of the form <genid> + 'hfPerms'
og.permissionInfo = [];

og.ogLoadPermissions = function(genid, isNew){
	var hf = document.getElementById(genid + 'hfPerms');
	if (hf && hf.value != ''){
		var hf_ot = document.getElementById(genid + 'hfAllowedOT');
		var hf_ot_mem = document.getElementById(genid + 'hfAllowedOTbyMemType');
		var hf_memt = document.getElementById(genid + 'hfMemTypes');

		og.permissionInfo[genid] = {
			permissions: Ext.util.JSON.decode(hf.value),
			allowedOt: Ext.util.JSON.decode(hf_ot.value),
			allowedOtByMemType: Ext.util.JSON.decode(hf_ot_mem.value),
			member_types: Ext.util.JSON.decode(hf_memt.value)
		}

		Ext.removeNode(hf);
		Ext.removeNode(hf_ot);
		Ext.removeNode(hf_ot_mem);
		Ext.removeNode(hf_memt);
	} else {
		og.permissionInfo[genid] = {};
	}
}

og.getPermissionsForMember = function(genid, member_id) {
	if (!og.permissionInfo[genid].permissions[member_id]) 
		og.permissionInfo[genid].permissions[member_id] = [];
	return og.permissionInfo[genid].permissions[member_id];
}

og.addPermissionsForMember = function(genid, member_id, perm) {
	og.permissionInfo[genid].permissions[member_id].push(perm);
}

og.deletePermissionsForMember = function(genid, member_id) {
	og.permissionInfo[genid].permissions[member_id] = [];
}

og.canEditPermissionObjType = function(genid, member_id, obj_type) {
	var mem_type = og.permissionInfo[genid].member_types[member_id];
	if (!mem_type) return false;
	var allowed = og.permissionInfo[genid].allowedOtByMemType[mem_type];
	for (var i=0; i<allowed.length; i++) {
		if (allowed[i] == obj_type) return true;
	}
	return false;
}

og.setReadOnlyObjectTypeRow = function(genid, dim_id, obj_type, readonly) {
	for (var i=0; i<4; i++) {
		var radio = Ext.get(genid + 'rg_'+ i +'_' + dim_id + '_' + obj_type).dom;
		if (radio) radio.disabled = readonly;
	}
	var label = Ext.get(genid + 'obj_type_label' + dim_id + '_' + obj_type);
	if (readonly) label.addClass('desc');
	else label.removeClass('desc');
}

og.loadMemberPermissions = function(genid, dim_id, member_id) {
	var allowed_ot = og.permissionInfo[genid].allowedOt;
	var member_perms = og.getPermissionsForMember(genid, member_id);
	
	for (var i=0; i < allowed_ot[dim_id].length; i++) {
		var val = 0;
		var found = false;
		for (var j=0; j<member_perms.length; j++) {
			var perm = member_perms[j];
			if (!perm) continue;
			if (perm.o == allowed_ot[dim_id][i]) {
				val = perm.w == 1 && perm.d == 1 ? 3 : (perm.w == 1 ? 2 : (perm.r ? 1 : 0));
				found = true;
				break;
			}
		}
		if (!found) {
			og.permissionInfo[genid].permissions[member_id].push({o: allowed_ot[dim_id][i], d:0 , w:0, r:0});
		}
		og.ogSetCheckedValue(document.getElementsByName(genid + "rg_" + dim_id + "_" + allowed_ot[dim_id][i]), val);
		
		og.setReadOnlyObjectTypeRow(genid, dim_id, allowed_ot[dim_id][i], !og.canEditPermissionObjType(genid, member_id, allowed_ot[dim_id][i]));
	}

	//Update the 'All' checkbox if all permissions are set
	var chk = document.getElementById(genid + dim_id + 'pAll');
	if (chk)
		chk.checked = og.hasAllPermissions(genid, member_id, member_perms);
}

//Action to execute when the value of an element of the displayed permission changes
og.ogPermValueChanged = function(genid, dim_id, obj_type){
	var member_id = og.permissionInfo[genid].selectedMember;
	var member_perms = og.getPermissionsForMember(genid, member_id);

	for (var i=0; i<member_perms.length; i++) {
		var tmp = member_perms[i];
		if (tmp.o == obj_type) {
			perm = tmp;
			break;
		}
	}
	if (!perm) return;
		
	var value = og.ogGetCheckedValue(document.getElementsByName(genid + "rg_" + dim_id + "_" + obj_type));

	perm.modified = true;
	perm.d = (value == 3);
	perm.w = (value >= 2);
	perm.r = (value >= 1);

	if (perm.r) {
		module_check = document.getElementById(genid + 'mod_perm['+perm.o+']');
		if(module_check && !module_check.checked) module_check.checked = true; 
	}

	og.markMemberPermissionModified(genid, dim_id, member_id);

	//Update the 'All' checkbox if all permissions are set
	var chk = document.getElementById(genid + dim_id + 'pAll');
	if (chk)
		chk.checked = og.hasAllPermissions(genid, member_id, member_perms);
}

og.hasAllPermissions = function(genid, member_id, member_permissions) {
	for (var i=0; i<member_permissions.length; i++) {
		if (!member_permissions[i] || !og.canEditPermissionObjType(genid, member_id, member_permissions[i].o)) continue;
		if (!(member_permissions[i].d && member_permissions[i].w && member_permissions[i].r)) return false;
	}
	return true;
}

//Sets all radio permissions to a specific level for a given member
og.ogPermSetLevel = function(genid, dim_id, level){
	var member_id = og.permissionInfo[genid].selectedMember;
	var member_perms = og.getPermissionsForMember(genid, member_id);

	for (var i=0; i<member_perms.length; i++) {
		//if (!og.canEditPermissionObjType(genid, member_id, member_perms[i].o)) continue;
		if (!member_perms[i]) {
			member_perms[i] = {o: og.permissionInfo[genid].allowedOt[dim_id][i], d: 0, w: 0, r: 0};
			og.addPermissionsForMember(genid, member_id, perms[i]);
		}
		
		member_perms[i].d = (level == 3);
		member_perms[i].w = (level >= 2);
		member_perms[i].r = (level >= 1);
		member_perms[i].modified = true;

		og.ogSetCheckedValue(document.getElementsByName(genid + "rg_" + dim_id + "_" + member_perms[i].o), level);

		if (member_perms[i].r) {
			module_check = document.getElementById(genid + 'mod_perm['+member_perms[i].o+']');
			if(module_check && !module_check.checked) module_check.checked = true; 
		}
	}

	og.markMemberPermissionModified(genid, dim_id, member_id);
	
	//Update the 'All' checkbox if all permissions are set
	var chk = document.getElementById(genid + dim_id + 'pAll');
	if (chk)
		chk.checked = level == 3;
}

//Action to execute when the 'All' checkbox is checked or unchecked
og.ogPermAllChecked = function(genid, dim_id, value){
	var level = value ? 3 : 0;
	og.ogPermSetLevel(genid, dim_id, level);
}

//Applies the current member permission settings to all submembers
og.ogPermApplyToSubmembers = function(genid, dim_id, from_root_node){
	var member_id = og.permissionInfo[genid].selectedMember;
	var member_perms = og.getPermissionsForMember(genid, member_id);
	
	var tree = Ext.getCmp(genid + '-member-chooser-panel-' + dim_id + '-tree');
	if (from_root_node) {
		var node = tree.getRootNode();
	} else {
		var node = tree.getNodeById(member_id);
	}
	if (!node) return;
	var ids = og.ogPermGetSubMemberIdsFromNode(node);

	for (var i=0; i<ids.length; i++) {
		//var old_member_perms = og.getPermissionsForMember(genid, ids[i]);
		og.deletePermissionsForMember(genid, ids[i]);
		
		for (var j=0; j<member_perms.length; j++) {
			if (!member_perms[j]) {
				member_perms[j] = {o: og.permissionInfo[genid].allowedOt[dim_id][j].o, d: 0, w: 0, r: 0};
				og.addPermissionsForMember(genid, member_id, member_perms[j]);
			}
			var radio = Ext.get(genid + 'rg_3_' + dim_id + '_' + member_perms[j].o);
			
		//	if (!og.canEditPermissionObjType(genid, ids[i], member_perms[j].o)) {
		//		var perm = {o: member_perms[j].o, d: 0, w: 0, r: 0};
		//	} else {
				var perm = {o: member_perms[j].o, d: member_perms[j].d, w: member_perms[j].w, r: member_perms[j].r, modified:true};
		//	}
			og.addPermissionsForMember(genid, ids[i], perm);
			if (member_perms[j].r) {
				module_check = document.getElementById(genid + 'mod_perm['+member_perms[j].o+']');
				if(module_check && !module_check.checked) module_check.checked = true; 
			}
		}
		og.markMemberPermissionModified(genid, dim_id, ids[i]);
	}
}

//Applies the current member permission settings to all dimension members
og.ogPermApplyToAllMembers = function(genid, dim_id){
	og.ogPermApplyToSubmembers(genid, dim_id, true);
}

og.ogPermGetSubMemberIdsFromNode = function(node){
	var result = new Array();
	if (node && node.firstChild){
		var children = node.childNodes;
		for (var i = 0; i < children.length; i++){
			result[result.length] = children[i].id;
			result = result.concat(og.ogPermGetSubMemberIdsFromNode(children[i]));
		}
	}
	return result;
}

og.markMemberPermissionModified = function(genid, dim_id, member_id) {
	var tree = Ext.getCmp(genid + '-member-chooser-panel-' + dim_id + '-tree');
	var node = tree.getNodeById(member_id);
	node.getUI().addClass('tree-node-modified');
}

//Sets the permission information to send inside a hidden field. 
//The id of the hidden field must be of the form: <genid> + 'hfPermsSend'
og.ogPermPrepareSendData = function(genid){
	var result = new Array();
	var permissions = og.permissionInfo[genid].permissions;
	for (i in permissions){
		for (var j = 0; j < permissions[i].length; j++){
			var p = permissions[i][j];
			if (p && p.modified) {
				result[result.length] = {'m':i, 'o':p.o, 'd':p.d, 'w':p.w, 'r':p.r};
			}
		}
	}
	
	var hf = document.getElementById(genid + 'hfPermsSend');
	if (hf) {
		hf.value = Ext.util.JSON.encode(result);
	}
		
	return true;
}

og.removeAllPermissionsForObjType = function(genid, obj_type) {
	for (member_id in og.permissionInfo[genid].permissions) {
		for (var i=0; i<og.permissionInfo[genid].permissions[member_id].length; i++) {
			var perm = og.permissionInfo[genid].permissions[member_id][i];
			if (perm.o == obj_type) {
				perm.r = 0;
				perm.w = 0;
				perm.d = 0;
				break;
			}
		}
	}
	for (var i=0; i<og.permissionDimensions.length; i++) {
		var radio = document.getElementsByName(genid + "rg_" + og.permissionDimensions[i] + "_" + obj_type)
		if (radio) og.ogSetCheckedValue(radio, 0);
	}
}

//	Returns the value of the radio button that is checked
og.ogGetCheckedValue = function(radioObj) {
	if(!radioObj)
		return "";
	var radioLength = radioObj.length;
	if(radioLength == undefined)
		if(radioObj.checked)
			return radioObj.value;
		else
			return "";
	for(var i = 0; i < radioLength; i++) {
		if(radioObj[i].checked) {
			return radioObj[i].value;
		}
	}
	return "";
}


//	Sets the radio button with the given value as being checked
og.ogSetCheckedValue = function(radioObj, newValue) {
	if(!radioObj)
		return;
	var radioLength = radioObj.length;
	if(radioLength == undefined) {
		radioObj.checked = (radioObj.value == newValue.toString());
		return;
	}
	for(var i = 0; i < radioLength; i++) {
		radioObj[i].checked = false;
		if(radioObj[i].value == newValue.toString()) {
			radioObj[i].checked = true;
		}
	}
}


/******************************************************
 * Functions for user selector
******************************************************/

og.userPermissions = {};
og.userPermissions.permissionInfo = [];

og.userPermissions.loadPermissions = function (genid, selector_id) {
	var hf = document.getElementById(genid + 'hfPerms');
	if (hf && hf.value != ''){
		var hf_ot = document.getElementById(genid + 'hfAllowedOT');

		og.userPermissions.permissionInfo[genid] = {
			permissions: Ext.util.JSON.decode(hf.value),
			allowedOt: Ext.util.JSON.decode(hf_ot.value)		
		}

		if (selector_id) {
			og.userPermissions.permissionInfo[genid].selectorId = selector_id;
			for (pg_id in og.userPermissions.permissionInfo[genid].permissions) {
				og.userPermissions.setCheckedPG(genid, pg_id);
			}
		}
		
		Ext.removeNode(hf);
		Ext.removeNode(hf_ot);
	} else {
		og.userPermissions.permissionInfo[genid] = {};
	}
}

og.userPermissions.setCheckedPG = function(genid, pg_id) {
	var selector = Ext.getCmp(genid + og.userPermissions.permissionInfo[genid].selectorId);
	if (!selector) return;
	var node = selector.getNodeById(selector.nodeId(pg_id));
	if (node) {
		node.ensureVisible();
		node.suspendEvents();
		var checked = og.userPermissions.hasAnyPermissions(genid, pg_id);
		node.ui.toggleCheck(checked);
		node.user.checked = checked;
		node.resumeEvents();
	}
}

og.userPermissions.getPermissionsForPG = function(genid, pg_id) {
	if (!og.userPermissions.permissionInfo[genid].permissions[pg_id]) {
		og.userPermissions.permissionInfo[genid].permissions[pg_id] = [];
	}
	return og.userPermissions.permissionInfo[genid].permissions[pg_id];
}

og.userPermissions.loadPGPermissions = function(genid, pg_id) {
	var allowed_ot = og.userPermissions.permissionInfo[genid].allowedOt;
	var permissions = og.userPermissions.getPermissionsForPG(genid, pg_id);
	
	for (var i=0; i < allowed_ot.length; i++) {
		var val = 0;
		var found = false;
		for (var j=0; j<permissions.length; j++) {
			var perm = permissions[j];
			if (perm.o == allowed_ot[i]) {
				val = perm.w == 1 && perm.d == 1 ? 3 : (perm.w == 1 ? 2 : (perm.r ? 1 : 0));
				found = true;
				break;
			}
		}
		if (!found) {
			og.userPermissions.permissionInfo[genid].permissions[pg_id].push({o: allowed_ot[i], d:0 , w:0, r:0});
		}
		document.getElementById(genid + 'rg_' + val + '_' + allowed_ot[i]).checked = 1;
	}

	//Update the 'All' checkbox if all permissions are set
	var chk = document.getElementById(genid + 'pAll');
	if (chk)
		chk.checked = og.userPermissions.hasAllPermissions(genid, pg_id);
}

og.userPermissions.hasAllPermissions = function(genid, pg_id) {
	var permissions = og.userPermissions.getPermissionsForPG(genid, pg_id);
	for (var i=0; i<permissions.length; i++) {
		if (!(permissions[i].d && permissions[i].w && permissions[i].r)) return false;
	}
	return true;
}

og.userPermissions.hasAnyPermissions = function(genid, pg_id) {
	var permissions = og.userPermissions.getPermissionsForPG(genid, pg_id);
	for (var i=0; i<permissions.length; i++) {
		if (permissions[i].d || permissions[i].w || permissions[i].r) return true;
	}
	return false;
}

//Sets all radio permissions to a specific level for a given member
og.userPermissions.ogPermSetLevel = function(genid, level){
	var pg_id = og.userPermissions.permissionInfo[genid].selectedPG;
	var permissions = og.userPermissions.getPermissionsForPG(genid, pg_id);

	for (var i=0; i<permissions.length; i++) {
		
		permissions[i].d = (level == 3);
		permissions[i].w = (level >= 2);
		permissions[i].r = (level >= 1);
		permissions[i].modified = true;

		og.ogSetCheckedValue(document.getElementsByName(genid + "rg_" + permissions[i].o), level);
	}

	og.userPermissions.setCheckedPG(genid, pg_id);
	
	//Update the 'All' checkbox if all permissions are set
	var chk = document.getElementById(genid + 'pAll');
	if (chk)
		chk.checked = level == 3;
}

//Action to execute when the value of an element of the displayed permission changes
og.userPermissions.ogPermValueChanged = function(genid, obj_type){
	var pg_id = og.userPermissions.permissionInfo[genid].selectedPG;
	var permissions = og.userPermissions.getPermissionsForPG(genid, pg_id);

	var perm = null;
	for (var i=0; i<permissions.length; i++) {
		var tmp = permissions[i];
		if (tmp.o == obj_type) {
			perm = tmp;
			break;
		}
	}
	if (perm == null) return;
		
	var value = og.ogGetCheckedValue(document.getElementsByName(genid + "rg_" + obj_type));

	perm.modified = true;
	perm.d = (value == 3);
	perm.w = (value >= 2);
	perm.r = (value >= 1);

	og.userPermissions.setCheckedPG(genid, pg_id);

	//Update the 'All' checkbox if all permissions are set
	var chk = document.getElementById(genid + 'pAll');
	if (chk)
		chk.checked = og.userPermissions.hasAllPermissions(genid, pg_id);
}

//Action to execute when the 'All' checkbox is checked or unchecked
og.userPermissions.ogPermAllChecked = function(genid, value){
	var level = value ? 3 : 0;
	og.userPermissions.ogPermSetLevel(genid, level);
}

og.userPermissions.ogPermPrepareSendData = function(genid){
	var result = new Array();
	var permissions = og.userPermissions.permissionInfo[genid].permissions;
	for (i in permissions){
		for (var j = 0; j < permissions[i].length; j++){
			var p = permissions[i][j];
			if (p && p.modified) {
				result[result.length] = {'pg':i, 'o':p.o, 'd':p.d, 'w':p.w, 'r':p.r};
			}
		}
	}
	
	var hf = document.getElementById(genid + 'hfPermsSend');
	if (hf) {
		hf.value = Ext.util.JSON.encode(result);
	}

	return true;
}


og.showHideNonGuestPermissionOptions = function (guest_selected) {
	if (guest_selected) {
		$('.radio_3').hide();
		$('.radio_2').hide();
		$('.radio-title-3').hide();
		$('.radio-title-2').hide();
		$('.perm_all_checkbox_container').hide();
	} else {
		$('.radio_3').show();
		$('.radio_2').show();
		$('.radio-title-3').show();
		$('.radio-title-2').show();
		$('.perm_all_checkbox_container').show();
	}
}