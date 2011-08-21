/**
 *  Permissions
 *
 * Author: Carlos Palma (chonwil@gmail.com)
 */

var permissionsList = new Array();


//--------------------------------------------------
//			PERMISSIONS CLASS
//--------------------------------------------------


/**
 * This is the main class representing a workspace permission.
 * 		wsid = the workspace id
 * 		pc = an array containing the checkbox permissions, states: [0,1]
 * 		pr = an array containing the radio permissions, states: [0,1,2]
 *		isModified = returns true if the workspace permission was modified from its original value
 */
og.ogPermission = function(workspace_id, radio_permissions, checkbox_permissions){
	this.isModified = false;
	this.wsid = workspace_id;
	this.pr = radio_permissions;
	this.pc = checkbox_permissions;
}


//Returns a copy of this permissions object
og.ogPermission.prototype.clone = function() {
	var radio_permissions = [];
	for (var i = 0; i < this.pr.length; i++)
		radio_permissions[radio_permissions.length] = this.pr[i];
		
	var checkbox_permissions = [];
	for (var i = 0; i < this.pc.length; i++)
		checkbox_permissions[checkbox_permissions.length] = this.pc[i];
	
	var result = new og.ogPermission(this.wsid, radio_permissions, checkbox_permissions);
	result.isModified = this.isModified;
	
	return result;
}
 
 
//Returns true if the permission has any permission set to a value other than 0
og.ogPermHasAnyPermission = function(){
	var allCheckedFalse = true;

	//Checkboxes
	for (var i = 0; i < this.pc.length; i++)
		allCheckedFalse = allCheckedFalse && (this.pc[i] == 0);
		
	//Radio buttons
	for (var i = 0; i < this.pr.length; i++)
		allCheckedFalse = allCheckedFalse && (this.pr[i] == 0);
	
	return !allCheckedFalse;
}


//Returns true if the permission has all permissions set to their highest value
og.ogPermHasAllPermissions = function(genid){
	var allCheckedTrue = true;

	//Checkboxes
	for (var i = 0; i < this.pc.length; i++)
		allCheckedTrue = allCheckedTrue && (this.pc[i] == 1);
		
	//Radio buttons
	for (var i = 0; i < this.pr.length; i++)
		allCheckedTrue = allCheckedTrue && (i == 4 ? this.pr[i] >= 2 : this.pr[i] >= og.ogPermMax(genid));
	
	return allCheckedTrue;
}

og.ogPermission.prototype.hasAnyPermission = og.ogPermHasAnyPermission;
og.ogPermission.prototype.hasAllPermissions = og.ogPermHasAllPermissions;



//--------------------------------------------------
//				FUNCTIONS
//--------------------------------------------------
 
 
//-------------------------------------------------- DATA LOAD
 
//	Loads the permission info from a hidden field. 
//	The name of the hidden field must be of the form <genid> + 'hfPerms'
og.ogLoadPermissions = function(genid, isNew){
	var hf = document.getElementById(genid + 'hfPerms');
	if (hf && hf.value != ''){
	 	var dec = Ext.util.JSON.decode(hf.value);	
	 	og.ogLoadPermissionsFromArray(genid, dec, isNew);
	} else {
		permissionsList[genid] = [];
	}
}

og.ogLoadPermissionsFromArray = function(genid, dec, isNew) {
	var tree = Ext.getCmp('workspace-chooser' + genid);
	tree.uncheckAll();
 	
	var permarray = [];
 	for (var i = 0; i < dec.length; i++){
 		var perm = new og.ogPermission(dec[i].wsid, dec[i].pr, dec[i].pc);
 		if (isNew) perm.isModified = true;
 		permarray[dec[i].wsid] = perm;
 		var node = tree.getNodeById(tree.nodeId(dec[i].wsid));
 		if (node){
			node.suspendEvents();
			node.ui.toggleCheck(perm.hasAnyPermission());
			node.attributes.checked = perm.hasAnyPermission();
			node.resumeEvents();
		}
 	}
 	permissionsList[genid] = permarray;
}


//	Sets the permission information to send inside a hidden field. 
//	The id of the hidden field must be of the form: <genid> + 'hfPermsSend'
og.ogPermPrepareSendData = function(genid){
	var result = new Array();
	var permissions = permissionsList[genid];
	var i;
	for (i in permissions){
		if (permissions[i].isModified)
			result[result.length] = {'wsid':permissions[i].wsid, 'pr':permissions[i].pr, 'pc':permissions[i].pc};
	}
	
	var hf = document.getElementById(genid + 'hfPermsSend');
	if (hf) {
		hf.value = Ext.util.JSON.encode(result);
	}
		
	return true;
}
 
 
//-------------------------------------------------- ACTIONS

//	Applies the current workspace permission settings to all subworkspaces
og.ogPermApplyToSubworkspaces = function(genid){
	var ws = og.ogPermGetSelectedWs(genid);
	var permission = permissionsList[genid][ws.id];
	if (!permission){
		permission = new og.ogPermission(ws.id, [0,0,0,0,0,0,0,0,0], [0,0]);
		permissionsList[genid][ws.id] = permission;
	}
	var tree = Ext.getCmp('workspace-chooser' + genid);
	var node = tree.getNodeById(tree.nodeId(ws.id));
	var ids = og.ogPermGetSubWsIdsFromNode(node);
	
	// holds the nodes that that were expanded once, to avoid expanding again the same node.
	// 1 expansion per node is needed to fix a view issue when checking collapsed child nodes.
	var already_expanded_once = []; 
	
	var i;
	var hasPerm = permission.hasAnyPermission();
	for (i in ids){
		if (typeof(ids[i]) == 'number'){
		 	var permissionCopy = permission.clone();
			permissionCopy.wsid = ids[i];
		 	permissionCopy.isModified = true;
		 	permissionsList[genid][ids[i]] = permissionCopy;
		 	
		 	//update the treenode 'checked' attribute
		 	var node2 = tree.getNodeById(tree.nodeId(ids[i]));
			if (node2){
			
				var parent_expanded = false;
				for (i=0; i<already_expanded_once.length && !parent_expanded; i++)
					parent_expanded = already_expanded_once[i] == node2.ws.p;
				// if parent was expanded before then dont do anything, otherwise expand it and add it to 'once expanded nodes' array.
				if (!parent_expanded) {
					var parent = tree.getNodeById(tree.nodeId(node2.ws.p));
					if (parent && !parent.expanded) {
						parent.expand();
						parent.collapse();
					}
					already_expanded_once[already_expanded_once.length] = node2.ws.p;
				}
				
				node2.suspendEvents();
				node2.ui.toggleCheck(hasPerm);
				node2.attributes.checked = true;
				node2.resumeEvents();
			}
		}
	}
}


//	Action to execute when the value of an element of the displayed permission changes
og.ogPermValueChanged = function(genid){
	var ws = og.ogPermGetSelectedWs(genid);
	var permission = permissionsList[genid][ws.id];
	if (!permission){
		permission = new og.ogPermission(ws.id, [0,0,0,0,0,0,0,0,0], [0,0]);
		permissionsList[genid][ws.id] = permission;
	}
	og.ogSavePermissions(genid,permission);
	
	//Update the tree checkbox if there are any permissions
	var tree = Ext.getCmp('workspace-chooser' + genid);
	var node = tree.getNodeById(tree.nodeId(ws.id));
	if (node){
		node.suspendEvents();
		node.ui.toggleCheck(permission.hasAnyPermission());
		node.resumeEvents();
	}
	
	//Update the 'All' checkbox if all permissions are set
	var chk = document.getElementById(genid + 'pAll');
	if (chk)
		chk.checked = permission.hasAllPermissions(genid);
}


//	Action to execute when the selected workspace changes
og.ogPermSelectedWsChanged = function(genid){
	var ws = og.ogPermGetSelectedWs(genid);
	var permission = permissionsList[genid][ws.id];
	if (!permission){
		permission = new og.ogPermission(ws.id, [0,0,0,0,0,0,0,0,0], [0,0]);
	}
	og.ogPopulatePermissions(genid,permission);
	
	var titleDiv = document.getElementById(genid + 'project_name').innerHTML = ws.n;
	document.getElementById(genid + 'project_permissions').style.display="block";
}

// allow up to read-only permission
og.ogPermReadOnly = function(genid, setReadOnly) {
	var x = setReadOnly ? 1 : 2;
	Ext.getDom(genid + "hfPerms").maxPermValue = x;
	
	var ws = og.ogPermGetSelectedWs(genid);
	var permission = permissionsList[genid][ws.id];
	if (permission) {
		og.ogPopulatePermissions(genid,permission);
	}
};

og.ogPermMax = function(genid) {
	var ws = og.ogPermGetSelectedWs(genid);
	return Ext.getDom(genid + "hfPerms").maxPermValue || (ws.g ? 1 : 2);
};

//	Action to execute when the 'All' checkbox is checked or unchecked
og.ogPermAllChecked = function(genid,value,wsid){
	if (!wsid){
		var ws = og.ogPermGetSelectedWs(genid);
		wsid = ws.id;
	}
	var permission;
	if (value) {
		var x = og.ogPermMax(genid);
		permission = new og.ogPermission(wsid, [x,x,x,x,2,x,x,x,x], [1,1]);
	} else {
		permission = new og.ogPermission(wsid, [0,0,0,0,0,0,0,0,0], [0,0]);
	}
	
	permission.isModified = true;
	permissionsList[genid][wsid] = permission;
	
	var ws = og.ogPermGetSelectedWs(genid);
	if (ws.id == wsid)
		og.ogPopulatePermissions(genid,permission);
		
	var tree = Ext.getCmp('workspace-chooser' + genid);
	var node = tree.getNodeById(tree.nodeId(wsid));
	node.suspendEvents();
	node.ui.toggleCheck(value);
	node.resumeEvents();
}

 
//-------------------------------------------------- UTILITIES

//	Returns the subworkspace ids from a given tree node
og.ogPermGetSubWsIdsFromNode = function(node){
	var result = new Array();
	if (node && node.firstChild){
		var children = node.childNodes;
		for (var i = 0; i < children.length; i++){
			result[result.length] = children[i].ws.id;
			result = result.concat(og.ogPermGetSubWsIdsFromNode(children[i]));
		}
	}
	return result;
}


//	Returns the selected workspace from the tree control
og.ogPermGetSelectedWs = function(genid){
	var tree = Ext.getCmp('workspace-chooser' + genid);
	var ws = tree.getSelected();
	return ws;
}


//	Sets all radio permissions to a specific level for a given workspace
og.ogPermSetLevel = function(genid,level){
	var ws = og.ogPermGetSelectedWs(genid);
	var permission = permissionsList[genid][ws.id];
	var x = Math.min(level, og.ogPermMax(genid));
	if (!permission){
		permission = new og.ogPermission(ws.id, [x,x,x,x,level,x,x,x,x], [0,0]);
	} else {
		permission.pr = [x,x,x,x,level,x,x,x,x];
		permission.isModified = true;
	}
	
	permissionsList[genid][ws.id] = permission;
	og.ogPopulatePermissions(genid,permission);
	
	var tree = Ext.getCmp('workspace-chooser' + genid);
	var node = tree.getNodeById(tree.nodeId(ws.id));
	node.suspendEvents();
	node.ui.toggleCheck(permission.hasAnyPermission());
	node.resumeEvents();
}


//	Displays the permission values
og.ogPopulatePermissions = function(genid, permission){
	//Checkboxes
	for (var i = 0; i < permission.pc.length; i++)
		document.getElementById(genid + "chk_" + i).checked = (permission.pc[i] == 1);
		
	//Radio buttons
	var x = og.ogPermMax(genid);
	for (var i = 0; i < permission.pr.length; i++)
		og.ogSetCheckedValue(document.getElementsByName(genid + "rg_" + i), i == 4 ? permission.pr[i] : Math.min(x, permission.pr[i]));
	
	Ext.get(genid + "project_permissions").select('.readWritePermission').setDisplayed(x >= 2);
	
	var chk = document.getElementById(genid + 'pAll');
	if (chk) 
		chk.checked = permission.hasAllPermissions(genid);
}


//	Gets the values from the displayed permission and saves them to the permission object.
og.ogSavePermissions = function(genid,permission){
	//Checkboxes
	for (var i = 0; i < permission.pc.length; i++)
		permission.pc[i] = document.getElementById(genid + "chk_" + i).checked ? 1 : 0;
		
	//Radio buttons
	for (var i = 0; i < permission.pr.length; i++)
		permission.pr[i] = og.ogGetCheckedValue(document.getElementsByName(genid + "rg_" + i));
		
	permission.isModified = true;
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