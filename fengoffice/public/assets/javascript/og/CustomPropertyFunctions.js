var cpModified = false;
var selectedObjTypeIndex = -1;

og.loadCustomPropertyFlags = function(){
	cpModified = false;
	selectedObjTypeIndex = -1;
};
  	
og.enterCP = function(id) {
/*  	var deleted = document.getElementById('custom_properties[' + id + '][deleted]');
  	if(deleted && deleted.value == "0"){
		Ext.get('down' + id).setVisible(true);
		Ext.get('up' + id).setVisible(true);
		Ext.get('delete' + id).setVisible(true);
 	}*/
};

og.leaveCP = function(id) {
/*	Ext.get('down' + id).setVisible(false);
	Ext.get('up' + id).setVisible(false);
	Ext.get('delete' + id).setVisible(false);*/
};

og.objectTypeChanged = function(genid){
	var objectTypeSel = document.getElementById('objectTypeSel');
	if(cpModified){
		if(!confirm(lang('confirm discard changes'))){
			objectTypeSel.selectedIndex = selectedObjTypeIndex;
			return;
		}
	}
	cpModified = false;
	selectedObjTypeIndex = objectTypeSel.selectedIndex; 
	if(selectedObjTypeIndex != -1){
		var cpDiv = Ext.getDom(genid);
		while(cpDiv.firstChild){
			cpDiv.removeChild(cpDiv.firstChild);
		}
		var type = objectTypeSel[selectedObjTypeIndex].value;
		if(type != ''){
			og.openLink(og.getUrl('object', 'get_co_types', {object_type: type}), {
				callback: function(success, data) {
					if (success) {
						og.coTypes = data.co_types;
						
						og.openLink(og.getUrl('property', 'get_custom_properties', {object_type: type}), {
							callback: function(success, data) {
								if (success) {
									for(var i=0; i < data.custom_properties.length; i++){
										var property = data.custom_properties[i];
										og.addCustomProperty(genid, property);
									}							
								}
							},
							scope: this
						});
					}
				},
				scope: this
			});
			Ext.getDom('CPactions' + genid).style.display='';
			document.getElementById('objectType').value = type;	
		}else{
			Ext.getDom('CPactions' + genid).style.display='none';
		}
	}
}

og.addCustomProperty = function(genid, property){  	  	
	var cpDiv = Ext.getDom(genid);
	var count = cpDiv.getElementsByTagName('table').length;
	if (count % 2 == 0) {
		var classname = "";
	} else {
		var classname = "odd";
	}
	var types = '<select id="custom_properties[' + count + '][type]" name="custom_properties[' + count + '][type]" onchange="og.fieldTypeChanged(' + count + ')">' +
		'<option value="text"' + (property != null && property.type == 'text' ? 'selected' : '') + '>' + lang('text') + '</option>' + 
		'<option value="numeric"' + (property != null && property.type == 'numeric' ? 'selected' : '') + '>' + lang('numeric') + '</option>' +
		'<option value="boolean"' + (property != null && property.type == 'boolean' ? 'selected' : '') + '>' + lang('boolean') + '</option>' +
		'<option value="list"' + (property != null && property.type == 'list' ? 'selected' : '') + '>' + lang('list') + '</option>' +
		'<option value="date"' + (property != null && property.type == 'date' ? 'selected' : '') + '>' + lang('date') + '</option>' +
		'<option value="memo"' + (property != null && property.type == 'memo' ? 'selected' : '') + '>' + lang('memo') + '</option>' +
		'<option value="table"' + (property != null && property.type == 'table' ? 'selected' : '') + '>' + lang('table') + '</option>' +
		'</select>';
	
	var style = 'style="width:auto;padding-right:10px;"';
	var styleHidden = 'style="width:100px;padding-right:10px;display:none;"';
	
	var table = '<table onmouseover="og.enterCP(' + count + ')" onmouseout="og.leaveCP(' + count + ')"><tr>' +
		'<td style="display:none;"><input id="custom_properties[' + count + '][id]" name="custom_properties[' + count + '][id]" type="hidden" value="{0}"/>' +
		'<input id="custom_properties[' + count + '][deleted]" name="custom_properties[' + count + '][deleted]" type="hidden" value="0"/></td>' +
  		'<td ' + style + '><b>' + lang('name') + '</b>:<br/><input type="text" id="custom_properties[' + count + '][name]" name="custom_properties[' + count + '][name]" value="{1}"/></td>' +
		'<td ' + style + '><b>' + lang('type') + '</b>:<br/>' + types + '</td>' +
		'<td ' + (property != null && (property.type == 'list' || property.type == 'table') && property.values != null ? style : styleHidden) + ' id="tdValues' + count + '">' + 
		
		'<b><span id="tdValues' + count + '_label">' + (property != null && property.type == 'table' ? lang('columns comma separated') : lang('values comma separated')) + '</span>' + 
		'</b>:<br/><input type="text" onchange="javascript:og.fieldValueChanged()" id="custom_properties[' + count + '][values]" name="custom_properties[' + count + '][values]" value="{2}"/></td>' +
		'<td ' + style + '><b>' + lang('description') + 
		'</b>:<br/><input type="text" onchange="javascript:og.fieldValueChanged()" id="custom_properties[' + count + '][description]" name="custom_properties[' + count + '][description]" value="{3}"/></td>' +
		'</tr>' +
		
		'<tr><td ' + style + ' id="tdDefaultValueText' + count + '"><b>' + lang('default value') + 
		'</b>:<br/><input type="text" onchange="javascript:og.fieldValueChanged()" id="custom_properties[' + count + '][default_value]" name="custom_properties[' + count + '][default_value]" value="{4}"/></td>' +
		'<td ' + styleHidden + ' id="tdDefaultValueCheck' + count + '"><b>' + lang('default value') + 
		'</b>:<br/><input type="checkbox" class="checkbox" onchange="javascript:og.fieldValueChanged()" id="custom_properties[' + count + '][default_value_boolean]" name="custom_properties[' + count + '][default_value_boolean]" {4}/>&nbsp;' + lang('checked') + '</td>' +
		'<td ' + style + '><b>' + lang('required') + 
		'</b>:<br/><input class="checkbox" onchange="javascript:og.fieldValueChanged()" type="checkbox" id="custom_properties[' + count + '][required]" name="custom_properties[' + count + '][required]" {5}/></td>' +
		
		'<td ' + style + ' id="tdMultipleValues' + count + '"><b>' + lang('multiple values') + 
		'</b>:<br/><input class="checkbox" onchange="javascript:og.fieldValueChanged();og.checkMValChecked(' + count + ');" type="checkbox" id="custom_properties[' + count + '][multiple_values]" name="custom_properties[' + count + '][multiple_values]" {6}/></td>' +
		
		'<td ' + style + ' id="tdVisibleByDefault' + count + '"><b>' + lang('visible by default') + 
  		'</b>:<br/><input class="checkbox" onchange="javascript:og.fieldValueChanged()" type="checkbox" id="custom_properties[' + count + '][visible_by_default]" name="custom_properties[' + count + '][visible_by_default]" {7}/></td>' +
  			
		'<td style="width:250px;"></td>' +
  		'<td><div id="up' + count + '" class="clico ico-up" onclick="og.moveCustomPropertyUp(' + count + ',\'' + genid + '\')"></div></td>' +
		'<td><div id="down' + count + '" class="clico ico-down" onclick="og.moveCustomPropertyDown(' + count + ',\'' + genid + '\')"></div></td>' +
		'<td><div id="delete' + count + '" class="clico ico-delete" onclick="og.deleteCustomProperty(' + count + ',\'' + genid + '\')"></div></td>' +
		'<tr id="trDelete' + count + '" style="display:none;"><td colspan="6"><b>' + lang('custom property deleted') +
		'</b><a class="internalLink" href="javascript:og.undoDeleteCustomProperty(' + count + ',\'' + genid + '\')">&nbsp;(' + lang('undo') + ')</a></td></tr>' +
  		'</tr></table>';
  	
  	// CO Types
  	if (og.coTypes && og.coTypes.length > 0) {
	  	table += '<div style="margin:4px 0 3px"><b>' + lang('applies to') + '</b>&nbsp;';
	  	var coTypeNames = '';
	  	for (i=0; i<og.coTypes.length; i++) {
	  		var value = 'false';
	  		if(property != null && property.co_types != '') {
	  			var splitted = property.co_types.split(',');
	  			for (k=0; k<splitted.length; k++) {
	  				if (splitted[k] == og.coTypes[i].id) {
	  					value = true;
	  					coTypeNames += (coTypeNames == '' ? '' : ', ') + og.coTypes[i].name;
	  					break;
	  				}
	  			}
	  		} else if (property == null) { //by default all types are selected
	  			value = true;
	  			coTypeNames += (coTypeNames == '' ? '' : ', ') + og.coTypes[i].name;
	  		}
	  		var t = og.coTypes[i];
	  		table = table + '<input type="hidden" value="'+value+'" name="custom_properties[' + count + '][applyto]['+t.id+']" id="custom_properties[' + count + '][applyto]['+t.id+']">';
	  	}
	  	if (coTypeNames == '') coTypeNames = lang('none');
	  	table += '<span class="desc" id="custom_properties[' + count + '][applyto_names]">' + coTypeNames + '</span>';
	  	table += '<a class="ico-edit" style="padding:5px 0 0 16px;margin-left:20px;" href="#" onclick="og.showCoTypeSelector('+count+')">'+lang('edit')+'</a>';
	  	table += '</div>';
  	}
  	
  	if(property != null){
  	  	var defaultValue = (property.type != 'boolean' ? property.default_value : (property.default_value ? 'checked' : ''));
  		table = String.format(table, property.id, property.name, (property.values ? property.values : ''), property.description, defaultValue, property.required == true ? 'checked="checked"' : '', property.multiple_values == true ? 'checked="checked"' : '', property.visible_by_default == true ? 'checked="checked"' : '');
  	}else{
  		table = String.format(table, '', '', '', '', '', '', '');
  	}
	var cp = document.createElement('div');
	cp.id = "CP" + count;
	cp.style.padding = "5px";
	cp.className = classname;
	cp.innerHTML = table;
	cpDiv.appendChild(cp);
	if(property == null) { 
  		cpModified = true;
	} else {
  		if(property.type == 'boolean'){
  			document.getElementById('tdDefaultValueCheck' + count).style.display = '';
  			document.getElementById('tdDefaultValueText' + count).style.display = 'none';
			document.getElementById('tdMultipleValues' + count).style.display = 'none';
  		} else if(property.type == 'table'){
  			var multipleValues = document.getElementById('custom_properties[' + count + '][multiple_values]');
  			multipleValues.checked = true;
  		}
	}
};

og.showCoTypeSelector = function(id) {
	var oldValues = new Array();
	for (i=0; i<og.coTypes.length; i++) {
		var el = document.getElementById('custom_properties[' + id + '][applyto][' + og.coTypes[i].id + ']');
		if (el) {
			oldValues.push({id:og.coTypes[i].id, val:el.value});
		}
	}
	
	var applyAction = function() {
		var str = '';
		for (i=0; i<og.coTypes.length; i++) {
			var el = document.getElementById('custom_properties[' + id + '][applyto][' + og.coTypes[i].id + ']');
			if (el && el.value == 'true') str += (str == '' ? '' : ', ') + og.coTypes[i].name;
		}
		var el = document.getElementById('custom_properties[' + id + '][applyto_names]');
		if (str == '') str = lang('none');
		if (el) el.innerHTML = str;
		og.ExtendedDialog.dialog.destroy();
	};
	
	var cancelAction = function() {
		for (i=0; i<og.coTypes.length; i++) {
			for (j=0; j<oldValues.length; j++) {
				if (og.coTypes[i].id == oldValues[j].id) {
					var el = document.getElementById('custom_properties[' + id + '][applyto][' + oldValues[j].id + ']');
					if (el) el.value = oldValues[j].val;
					break;
				}
			}
		}
		og.ExtendedDialog.dialog.destroy();
	};
	
	var allChecked = true;
	for (i=0; i<og.coTypes.length && allChecked; i++) {
		allChecked = document.getElementById('custom_properties[' + id + '][applyto][' + og.coTypes[i].id + ']').value == 'true';
	}
	var dlgItems = [{
		xtype :'checkbox',
		name :'co_type_all',
		id : 'all',
		boxLabel: lang('all'),
		hideLabel: true,
		checked: allChecked,
		handler: function(checkbox, checked) {
			for (i=0; i<og.coTypes.length; i++) {
				var cotype = og.coTypes[i];
				var el = Ext.getCmp('co_type_' + cotype.id);
				if (el) {
					el.setValue(checked);
					el.setDisabled(checked);
				}
				var domel = document.getElementById('custom_properties[' + id + '][applyto][' + cotype.id + ']');
				if (domel) domel.value = checked;
			}
		}
	}];
	for (i=0; i<og.coTypes.length; i++) {
		var cotype = og.coTypes[i];
		var item = {
			xtype :'checkbox',
			name : cotype.id,
			id : 'co_type_' + cotype.id,
			boxLabel: cotype.name,
			hideLabel: true,
			disabled: allChecked,
			checked: document.getElementById('custom_properties[' + id + '][applyto][' + cotype.id + ']').value == 'true',
			handler: function(checkbox, checked) {
				var el = document.getElementById('custom_properties[' + id + '][applyto][' + checkbox.getName() + ']');
				if (el) el.value = checked;
			}
		};
		dlgItems.push(item);
	}
	
	var config = {
		title: lang('select co types to apply'),
		y :50,
		id :'co_type_selector',
		modal :true,
		height : 110 + dlgItems.length * 26,
		width : 250,
		resizable :false,
		closeAction :'hide',
		closable: false,
		iconCls :'op-ico',
		border :false,
		buttons : [ {
			text :lang('ok'),
			handler :applyAction,
			id :'yes_button',
			scope :this
		}, {
			text :lang('cancel'),
			handler :cancelAction,
			id :'no_button',
			scope :this
		} ],
		dialogItems : dlgItems
	};
	og.ExtendedDialog.show(config);
}

og.checkMValChecked = function(id) {
	var fieldTypeSel = document.getElementById('custom_properties[' + id + '][type]');
	if(fieldTypeSel.selectedIndex != -1){
		var type = fieldTypeSel[fieldTypeSel.selectedIndex].value;
		if (type == 'table') {
			var multipleValues = document.getElementById('custom_properties[' + id + '][multiple_values]');
			multipleValues.checked = true;
		}
	}
}

og.fieldTypeChanged = function(id){
	var fieldTypeSel = document.getElementById('custom_properties[' + id + '][type]');
	if(fieldTypeSel.selectedIndex != -1){
		var type = fieldTypeSel[fieldTypeSel.selectedIndex].value;
		var valuesField = document.getElementById('tdValues' + id);
		var valuesLabel = document.getElementById('tdValues' + id + '_label');
		var defaultValueCheck = document.getElementById('tdDefaultValueCheck' + id);
		var defaultValueText = document.getElementById('tdDefaultValueText' + id);
		var tdMultipleValues = document.getElementById('tdMultipleValues' + id);
		var multipleValues = document.getElementById('custom_properties[' + id + '][multiple_values]');
		if(type == 'list' || type == 'table'){
			valuesField.style.display = '';
		}else{
			valuesField.style.display = 'none';
		}
		if(type == 'boolean'){
			defaultValueCheck.style.display = '';
			defaultValueText.style.display = 'none';
			tdMultipleValues.style.display = 'none';
			multipleValues.checked = false;
			
		}else{
			defaultValueCheck.style.display = 'none';
			defaultValueText.style.display = '';
			tdMultipleValues.style.display = '';
			if (type == 'table') {
				multipleValues.checked = true;
				valuesLabel.innerHTML = lang('columns comma separated');
			} else {
				valuesLabel.innerHTML = lang('values comma separated');
			}
		}
	}
	cpModified = true;
}

og.fieldValueChanged = function(){
	cpModified = true;
};

og.moveCustomPropertyUp = function(id, genid){
	var div = document.getElementById('CP' + id);
	if (div.previousSibling != null) {
		og.swapCPs(id, id - 1, genid);
	}
	og.leaveCP(id);
};

og.moveCustomPropertyDown = function(id, genid){
	var div = document.getElementById('CP' + id);
	if (div.nextSibling != null) {
		og.swapCPs(id + 1, id, genid);
	}
	og.leaveCP(id);
};

og.swapCPs = function(first, second, genid){
	var ids = ['id', 'deleted', 'name', 'type', 'values', 'description', 'default_value'];
  	document.getElementById('tdValues' + first).style.display = '';
	document.getElementById('tdValues' + second).style.display = '';
	for (var i=0; i < ids.length; i++) {
		var o1 = document.getElementById('custom_properties[' + first + '][' + ids[i] + ']');
		var o2 = document.getElementById('custom_properties[' + second + '][' + ids[i] + ']');  
		var value1 = o1.value;
		o1.value = o2.value;
		o2.value = value1;  		
	}  	
	og.fieldTypeChanged(first);
	og.fieldTypeChanged(second);	
	var boolValue1 = document.getElementById('custom_properties[' + first + '][default_value_boolean]');
	var boolValue2 = document.getElementById('custom_properties[' + second + '][default_value_boolean]');
	var checkedBoolValue = boolValue1.checked;
	boolValue1.checked = boolValue2.checked;
	boolValue2.checked = checkedBoolValue;
	var required1 = document.getElementById('custom_properties[' + first + '][required]');
	var required2 = document.getElementById('custom_properties[' + second + '][required]');
	var checkedReq = required1.checked;
	required1.checked = required2.checked;
	required2.checked = checkedReq;
	var multiple1 = document.getElementById('custom_properties[' + first + '][multiple_values]');
	var multiple2 = document.getElementById('custom_properties[' + second + '][multiple_values]');
	var checkedMul = multiple1.checked;
	multiple1.checked = multiple2.checked;
	multiple2.checked = checkedMul;
	
	var vis_default1 = document.getElementById('custom_properties[' + first + '][visible_by_default]');
	var vis_default2 = document.getElementById('custom_properties[' + second + '][visible_by_default]');
	var checkedVisDef = vis_default1.checked;
	vis_default1.checked = vis_default2.checked;
	vis_default2.checked = checkedVisDef;
	
	var applyto_names1 = document.getElementById('custom_properties[' + first + '][applyto_names]');
	var applyto_names2 = document.getElementById('custom_properties[' + second + '][applyto_names]');
	if (applyto_names1 && applyto_names2) {
		var tmp = applyto_names1.innerHTML;
		applyto_names1.innerHTML = applyto_names2.innerHTML;
		applyto_names2.innerHTML = tmp;
	}
	if (og.coTypes) {
		for (k=0; k<og.coTypes.length; k++) {
			var applyto1 = document.getElementById('custom_properties[' + first + '][applyto]['+ og.coTypes[k].id +']');
			var applyto2 = document.getElementById('custom_properties[' + second + '][applyto]['+ og.coTypes[k].id +']');
			var tmp = applyto1.value;
			applyto1.value = applyto2.value;
			applyto2.value = tmp;
		}
	}

	firstCP = document.getElementById('CP' + first);
	secondCP = document.getElementById('CP' + second);
	var parent = firstCP.parentNode;
	parent.removeChild(secondCP);
	parent.insertBefore(secondCP, firstCP);
	var cpDiv = Ext.getDom(genid);
	var count = 0;
	for(i=0; i < cpDiv.childNodes.length; i++){
		var nextCp = cpDiv.childNodes.item(i);
		if(nextCp.id){
			if (count % 2 == 0) {
	  			nextCp.className = "";
	  		} else {
	  			nextCp.className = "odd";
	  		}
	  		nextCp.id = 'CP' + count;
	  		count++;
		}
	}
	cpModified = true;
};

og.deleteCustomProperty = function(id, genid){
  	if(confirm(lang('delete custom property confirmation'))){
		var cp = document.getElementById('CP' + id);
		cp.style.background = '#FFDEAD';
		document.getElementById('trDelete' + id).style.display = '';
		document.getElementById('custom_properties[' + id + '][deleted]').value = 1;
		document.getElementById('down' + id).style.display = 'none';
		document.getElementById('up' + id).style.display = 'none';
		document.getElementById('delete' + id).style.display = 'none';
		cpModified = true;
  	}
};

og.undoDeleteCustomProperty = function(id, genid){
	document.getElementById('trDelete' + id).style.display = 'none';
	document.getElementById('custom_properties[' + id + '][deleted]').value = 0;
	var cpDiv = Ext.getDom(genid);
	for(var i=0; i < cpDiv.childNodes.length; i++){
		var nextCp = cpDiv.childNodes.item(i);
		if(nextCp.id == ('CP' + id)){
			nextCp.style.background = '';
			if (i % 2 == 0) {
	  			nextCp.className = "";
	  		} else {
	  			nextCp.className = "odd";
	  		}
	  		return;
		}
	}
};

og.validateCustomProperties = function(genid){
	var cpDiv = Ext.getDom(genid);
	var cpNames = new Array();
	for(var i=0; i < cpDiv.childNodes.length; i++){
		var deleted = document.getElementById('custom_properties[' + i + '][deleted]').value;
		if(deleted == "0"){
			var name = document.getElementById('custom_properties[' + i + '][name]').value;
			if(name == ''){
				alert(lang('custom property name empty'));
				return false;
			}
			var type = document.getElementById('custom_properties[' + i + '][type]').value;
			var defaultValue = document.getElementById('custom_properties[' + i + '][default_value]').value;
			if(type == 'list'){
				var values = document.getElementById('custom_properties[' + i + '][values]').value;
				if(values == ''){
					alert(lang('custom property values empty', name));
					return false;
				}
				var valuesArray = values.split(',');
				var defaultValueOK = false;
				for(var j=0; j < valuesArray.length; j++){
					valuesArray[j] = valuesArray[j].trim();
					if(valuesArray[j] == defaultValue){
						defaultValueOK = true;
					}
				}
				if(defaultValue != '' && !defaultValueOK){
					alert(lang('custom property wrong default value', name));
					return false;
				}
				
			}else if(type == 'numeric'){
				if(!og.isNumeric(defaultValue)){
					alert(lang('custom property invalid numeric value', name));
					return false;
				}
			}
			for(var k=0; k < cpNames.length; k++){
				if(cpNames[k] == name){
					alert(lang('custom property duplicate name', name));
					return false;
				}
			}
			cpNames.push(name);
		}
	}
	return true;
};

og.isNumeric = function(sText){
   var ValidChars = "0123456789.";
   var IsNumber=true;
   var Char;
 
   for (i = 0; i < sText.length && IsNumber == true; i++){ 
      Char = sText.charAt(i); 
      if (ValidChars.indexOf(Char) == -1){
         IsNumber = false;
      }
   }
   return IsNumber;  	   
 }