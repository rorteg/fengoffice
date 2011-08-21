og.templateParameters = [];
og.templateObjects = [];

og.loadTemplateVars = function(){
	og.templateParameters = [];
	og.templateObjects = [];
};

og.pickObjectForTemplate = function(before) {
	og.ObjectPicker.show(function (objs) {
		if (objs) {
			for (var i=0; i < objs.length; i++) {
				var obj = objs[i].data;
				if (obj.type != 'task' && obj.type != 'milestone') {
					og.msg(lang("error"), lang("object type not supported"), 4, "err");
				} else {
					for(var k=0; k < og.templateObjects.length; k++){
						if(og.templateObjects[k]['id'] == obj.object_id && og.templateObjects[k]['type'] == obj.manager){
							alert(lang('object exists in template'));
							return;
						}
					}
					og.addObjectToTemplate(this, obj);
				}
			}
		}
	}, before, {
		types: {
			'ProjectTasks': true,
			'ProjectMilestones': true
		}
	});
};

og.addObjectToTemplate = function(before, obj) {
	var parent = before.parentNode;
	var count = 0;
	var inputs = parent.getElementsByTagName('input');
	for (var i=0; i < inputs.length; i++) {
		if(inputs[i].className == 'objectID'){
			count++;
		}
	}
	var div = document.createElement('div');
	div.className = "og-add-template-object ico-" + obj.type;
	/*div.onmouseover = og.templateObjectMouseOver;
	div.onmouseout = og.templateObjectMouseOut;*/
	div.innerHTML =
		'<input class="objectID" type="hidden" name="objects[' + count + ']" value="' + obj.manager + ":" + obj.object_id + '" />' +
		'<span class="name">' + og.clean(obj.name) + '</span>' +
		'<a href="#" onclick="og.removeObjectFromTemplate(this.parentNode, ' + obj.object_id + ',\'' + obj.manager + '\')" class="removeDiv">'+lang('remove')+'</a>';
	var editPropDiv = document.createElement('div');
	editPropDiv.id = 'propDiv' + count;
	editPropDiv.style.paddingLeft = '30px';
	editPropDiv.innerHTML = '<a href="#" onclick="og.addTemplateObjectProperty(' + obj.object_id + ', \'' + obj.manager + '\',' + count + ',\'\', \'\')" class="link-ico ico-add">'+ lang('edit object property') + '</a>';
	var objectDiv = document.createElement('div');
	objectDiv.id = 'objectDiv' + count;
	objectDiv.className = (count % 2 ? " odd" : "");
	parent.insertBefore(objectDiv, before);
	objectDiv.appendChild(div);
	objectDiv.appendChild(editPropDiv);
	var newObj = [];
	newObj['id'] = obj.object_id;
	newObj['type'] = obj.manager;
	og.templateObjects.push(newObj);
};

og.removeObjectFromTemplate = function(div, obj_id, manager) {
	var parent = div.parentNode.parentNode;
	var removeId = div.parentNode.id;
	parent.removeChild(div.parentNode);
	var inputs = parent.getElementsByTagName('input');
	var count = 0;
	for (var i=0; i < inputs.length; i++) {
		if(inputs[i].className == 'objectID'){
			inputs[i].name = 'objects[' + count + ']';
			if(removeId == 'objectDiv' + count){
				for(var h = count + 1; h < og.templateObjects.length; h++){
					var objDiv = document.getElementById('objectDiv' + h);
					objDiv.id = 'objectDiv' + (h - 1);
					var propDiv = document.getElementById('propDiv' + h);
					propDiv.id = 'propDiv' + (h - 1);
				}
			}
			count++;
		}
	}
	for(var j=0; j < og.templateObjects.length - 1; j++){
		d = document.getElementById('objectDiv' + j);
		Ext.fly(d).removeClass("odd");
		if (j % 2) {
			Ext.fly(d).addClass("odd");
		}
	}
	for(var k=0; k < og.templateObjects.length; k++){
		if(og.templateObjects[k]['id'] == obj_id && og.templateObjects[k]['type'] == manager){
			og.templateObjects.splice(k,1);
			break;
		}
	}
};

og.templateObjectMouseOver = function() {
	var close = this.firstChild;
	while (close && close.className != 'removeDiv') {
		close = close.nextSibling;
	}
	if (close) {
		close.style.display = 'block';
	}
};

og.templateObjectMouseOut = function() {
	var close = this.firstChild;
	while (close && close.className != 'removeDiv') {
		close = close.nextSibling;
	}
	if (close) {
		close.style.display = 'none';
	}
};

og.addTemplateObjectProperty = function(obj_id, manager, count, property, value){
	var propDiv = document.getElementById('propDiv' + count);
	var parent = propDiv.parentNode;
	var selects = parent.getElementsByTagName('select');
	var total = 0;
	for (var i=0; i < selects.length; i++) {
		if(selects[i].className == 'propertySelect'){
			total++;
		}
	}
	var html = '';
	html += '<table><tr><td id="deletePropTD' + total + '" style="padding-right:5px;"><div class="clico ico-delete" onclick="og.deleteTemplateObjectProperty(' + obj_id + ',\'' + manager + '\',' + total + ')"></div></td>' +
		'<td><select class="propertySelect" style="display:none;" id="objectProperties[' + manager + ':' + obj_id + '][' + total + ']" ' + 
		'name="objectProperties[' + manager + ':' + obj_id + '][' + total + ']" onchange="og.objectPropertyChanged(' + obj_id + ',\'' + manager + '\',' + total + ',\'\')" style="width:150px">' +
		'</select></td><td style="padding-left:10px;" id="propSel[' + manager + ':' + obj_id + '][' + total + ']"></td></tr></table>';
	var newProp = document.createElement('div');
	newProp.id = 'propDiv[' + manager + ':' + obj_id + '][' + total + ']';
	newProp.style.paddingLeft = '30px';
	newProp.style.paddingBottom = '10px';
	newProp.innerHTML += html;
	propDiv.parentNode.insertBefore(newProp, propDiv);

	og.openLink(og.getUrl('template', 'get_object_properties', {object_type: manager}), {
		callback: function(success, data) {
			if (success) {
				var propSelection = '';
				if(data.properties.length > 0){
					var select = document.getElementById('objectProperties[' + manager + ':' + obj_id + '][' + total + ']');
					select.innerHTML = "";
					var option = document.createElement('option');
					option.value = "";
					option.innerHTML = '-- ' + lang('select') + ' --';
					select.appendChild(option);
					for(var j=0; j < data.properties.length; j++){
						var prop = data.properties[j];
						option = document.createElement('option');
						option.className = prop.type;
						option.value = prop.id;
						if (prop.id == property) option.selected = "selected";
						option.innerHTML = prop.name;
						select.appendChild(option);
					}
					select.style.display = '';
					og.objectPropertyChanged(obj_id, manager, total, value);
				}
			}
		}
	});
};

og.deleteTemplateObjectProperty = function(obj_id, manager, count){
	var propDiv = document.getElementById('propDiv[' + manager + ':' + obj_id + '][' + count + ']');
	var parent = propDiv.parentNode;
	propDiv.parentNode.removeChild(propDiv);
	var selects = parent.getElementsByTagName('select');
	var total = 0;
	for (var i=0; i < selects.length; i++) {
		if(selects[i].className == 'propertySelect'){
			total++;
		}
	}
	for(var j=count+1; j <= total; j++){
		propDiv = document.getElementById('propDiv[' + manager + ':' + obj_id + '][' + j + ']');
		propDiv.id = 'propDiv[' + manager + ':' + obj_id + '][' + (j - 1) + ']';
		deletePropTD = document.getElementById('deletePropTD' + j);
		deletePropTD.id = 'deletePropTD' + (j - 1);
		deletePropTD.innerHTML = '<div class="clico ico-delete" onclick="og.deleteTemplateObjectProperty(' + obj_id + ',\'' + manager + '\',' + (j - 1) + ')">';
		var objectPropSel = document.getElementById('objectProperties[' + manager + ':' + obj_id + '][' + j + ']');
		objectPropSel.id = 'objectProperties[' + manager + ':' + obj_id + '][' + (j - 1) + ']';
		objectPropSel.name = 'objectProperties[' + manager + ':' + obj_id + '][' + (j - 1) + ']';
		objectPropSel.attributes["onchange"].value = 'og.objectPropertyChanged(' + obj_id + ',\'' + manager + '\',' + (j - 1) + ',\'\')';
		var propSelTD = document.getElementById('propSel[' + manager + ':' + obj_id + '][' + j + ']');
		if(propSelTD != null){
			propSelTD.id = 'propSel[' + manager + ':' + obj_id + '][' + (j - 1) + ']';
			var datePropType = document.getElementById('datePropType[' + manager + ':' + obj_id + '][' + j + ']');
			if(datePropType != null){
				datePropType.id = 'datePropType[' + manager + ':' + obj_id + '][' + (j - 1) + ']';
				datePropType.attributes["onchange"].value = 'og.datePropertyTypeSel(' + (j - 1) + ',' + obj_id + '\',\'' + manager + '\')';
				var datePropTD = document.getElementById('datePropTD[' + manager + ':' + obj_id + '][' + j + ']');
				if(datePropTD != null){
					datePropTD.id = 'datePropTD[' + manager + ':' + obj_id + '][' + (j - 1) + ']';
				}
			}
		}
		
	}
};

og.objectPropertyChanged = function(obj_id, manager, count, value){
	var propertySel = document.getElementById('objectProperties[' + manager + ':' + obj_id + '][' + count + ']');
	var selectedPropIndex = propertySel.selectedIndex; 
	if(selectedPropIndex != -1){
		var prop = propertySel[selectedPropIndex];
		var propValueTD = document.getElementById('propSel[' + manager + ':' + obj_id + '][' + count + ']');
		var datePropTD = document.getElementById('datePropTD[' + manager + ':' + obj_id + '][' + count + ']');
		if(datePropTD != null){
			datePropTD.innerHTML = '';
		}
		var id = 0;
		var propSel = document.getElementById('objectProperties[' + manager + ':' + obj_id + '][' + id + ']');
		while(propSel != null){
			if(propSel.value == prop.value && selectedPropIndex != 0 && id != count){
				alert(lang('template property already selected'));
				propertySel.selectedIndex = 0;
				propValueTD.innerHTML = '';
				return;
			}
			propSel = document.getElementById('objectProperties[' + manager + ':' + obj_id + '][' + (++id) + ']');
		}
		if(prop.className == 'STRING'){
			propValueTD.innerHTML = '= <input id="propValues[' + manager + ':' + obj_id + '][' + prop.value + ']" name="propValues[' + manager + ':' + obj_id + '][' + prop.value + ']" value="' + value + '" />&nbsp;' +
				'<a href="#" onclick="og.editStringTemplateObjectProperty(' + obj_id + ',\'' + prop.value + '\',\'' + manager + '\')">[' + lang('open property editor') + ']</a>';
		}else if(prop.className == 'DATETIME'){
			propValueTD.innerHTML = '<select id="datePropType[' + manager + ':' + obj_id + '][' + count + ']" onchange="og.datePropertyTypeSel(' + count + ',\'' + obj_id + '\',\'' + manager + '\')">'
				+ '<option value="-1">Select type</option><option value="0">' + lang('fixed date') + '</option><option value="1">' + lang('parametric date') + '</option></select>';
			var newTD = document.createElement('td');
			newTD.id = 'datePropTD[' + manager + ':' + obj_id + '][' + count + ']';
			newTD.style.paddingLeft = '10px';
			propValueTD.parentNode.appendChild(newTD);
			if(value != ''){
				var datePropTypeSel = document.getElementById('datePropType[' + manager + ':' + obj_id + '][' + count + ']');
				if(value.indexOf("+") != -1 || value.indexOf("-") != -1){
					var param = "";
					var operator = "+";
					var posOp = 0;
					if(value.indexOf("+") != -1){
						posOp = value.indexOf("+");
					}
					if (value.indexOf("-") != -1){
						posOp = value.indexOf("-");
						operator = "-";
					}
					param = value.substring(0, posOp);
					amount = value.substring(posOp + 1, value.length - 1);
					unit = value.substring(value.length - 1);
					datePropTypeSel.selectedIndex = 2;
					og.datePropertyTypeSel(count, obj_id, manager);
					var paramSel = document.getElementById('propValueParam[' + manager + ':' + obj_id + '][' + prop.value + ']');
					for(var i=0; i < paramSel.length; i++){
						var paramName = '{' + paramSel.options[i].value + '}';
						if(paramName == param){
							paramSel.selectedIndex = i;
						}
					}
					var opSel = document.getElementById('propValueOperation[' + manager + ':' + obj_id + '][' + prop.value + ']');
					for(i=0; i < opSel.length; i++){
						var op = opSel.options[i].value;
						if(op == operator){
							opSel.selectedIndex = i;
						}
					}
					var amountInput = document.getElementById('propValueAmount[' + manager + ':' + obj_id + '][' + prop.value + ']');
					amountInput.value = amount;
					var unitSel = document.getElementById('propValueUnit[' + manager + ':' + obj_id + '][' + prop.value + ']');
					for(i=0; i < unitSel.length; i++){
						var u = unitSel.options[i].value;
						if(u == unit){
							unitSel.selectedIndex = i;
						}
					}
				}else{
					datePropTypeSel.selectedIndex = 1;
					og.datePropertyTypeSel(count, obj_id, manager);
					var dateProp = document.getElementById('propValues[' + manager + ':' + obj_id + '][' + prop.value + ']');
					dateProp.value = value; 
				}
			}
		}else if(prop.className == 'USER'){
			propValueTD.innerHTML = '<select id="integerPropType[' + manager + ':' + obj_id + '][' + count + ']" onchange="og.integerPropertyTypeSel(' + count + ',\'' + obj_id + '\',\'' + manager + '\')">'
				+ '<option value="-1">Select type</option><option value="0">' + lang('fixed user') + '</option><option value="1">' + lang('parametric user') + '</option></select>';
			var newTD = document.createElement('td');
			newTD.id = 'integerPropTD[' + manager + ':' + obj_id + '][' + count + ']';
			newTD.style.paddingLeft = '10px';
			propValueTD.parentNode.appendChild(newTD);
			if(value != ''){
				var integerPropTypeSel = document.getElementById('integerPropType[' + manager + ':' + obj_id + '][' + count + ']');
				if (value.indexOf("{") != -1) {
					integerPropTypeSel.selectedIndex = 2;
					og.integerPropertyTypeSel(count, obj_id, manager);
					var paramSel = document.getElementById('propValueParam[' + manager + ':' + obj_id + '][' + prop.value + ']');
					for(var i=0; i < paramSel.length; i++){
						var paramName = '{' + paramSel.options[i].value + '}';
						if (paramName == value) {
							paramSel.selectedIndex = i;
						}
					}
				} else {
					integerPropTypeSel.selectedIndex = 1;
					og.integerPropertyTypeSel(count, obj_id, manager, function() {
						var propSel = document.getElementById('propValues[' + manager + ':' + obj_id + '][' + prop.value + ']');
						for(var i=0; i < propSel.length; i++){
							var propVal = propSel.options[i].value;
							if (propVal == value) {
								propSel.selectedIndex = i;
							}
						}
					});
				}
			}
		}else{
			propValueTD.innerHTML = '';
		}
	}
};

og.datePropertyTypeSel = function(count, obj_id, manager){
	var datePropTD = document.getElementById('datePropTD[' + manager + ':' + obj_id + '][' + count + ']');
	var datePropTypeSel = document.getElementById('datePropType[' + manager + ':' + obj_id + '][' + count + ']');
	var selectedPropTypeIndex = datePropTypeSel.selectedIndex;
	datePropTD.innerHTML = '';
	if(selectedPropTypeIndex != -1){
		var propertySel = document.getElementById('objectProperties[' + manager + ':' + obj_id + '][' + count + ']');
		var prop = propertySel[propertySel.selectedIndex].value;
		var type = datePropTypeSel[selectedPropTypeIndex].value;
		if(type == 0){
			var dateProp = new og.DateField({
				renderTo: datePropTD,
				name: 'propValues[' + manager + ':' + obj_id + '][' + prop + ']',
				id: 'propValues[' + manager + ':' + obj_id + '][' + prop + ']'
			});
		}else if(type == 1){
			var selectParam = '';
			if(og.templateParameters.length > 0){
				selectParam = '<select name="propValueParam[' + manager + ':' + obj_id + '][' + prop + ']" id="propValueParam[' + manager + ':' + obj_id + '][' + prop + ']">';
				for(var j=0; j < og.templateParameters.length; j++){
					var item = og.templateParameters[j];
					if(item.type == "date"){
						selectParam += '<option>' + item['name'] + '</option>';
					}
				}
				selectParam += '</select>';
				datePropTD.innerHTML = '= &nbsp;<input type="hidden" name="propValues[' + manager + ':' + obj_id + '][' + prop + ']">' + 
					selectParam + '&nbsp;<select name="propValueOperation[' + manager + ':' + obj_id + '][' + prop + ']" id="propValueOperation[' + manager + ':' + obj_id + '][' + prop + ']">' +
					'<option>+</option><option>-</option></select>&nbsp;' + 
					'<input name="propValueAmount[' + manager + ':' + obj_id + '][' + prop + ']" id="propValueAmount[' + manager + ':' + obj_id + '][' + prop + ']" style="width:30px;" />' +
					'&nbsp;<select name="propValueUnit[' + manager + ':' + obj_id + '][' + prop + ']" id="propValueUnit[' + manager + ':' + obj_id + '][' + prop + ']"><option value="d">'+ lang('days') + '</option>' +
					'<option value="w">'+ lang('weeks') + '</option><option value="m">'+ lang('months') + '</option></select>';
			}
			if(selectParam == ''){
				alert(lang('no parameters in template'));
				datePropTypeSel.selectedIndex = 0;
			}
		}
	}
};

og.integerPropertyTypeSel = function(count, obj_id, manager, callback){
	var integerPropTD = document.getElementById('integerPropTD[' + manager + ':' + obj_id + '][' + count + ']');
	var integerPropTypeSel = document.getElementById('integerPropType[' + manager + ':' + obj_id + '][' + count + ']');
	var selectedPropTypeIndex = integerPropTypeSel.selectedIndex;
	integerPropTD.innerHTML = '';
	if(selectedPropTypeIndex != -1){
		var propertySel = document.getElementById('objectProperties[' + manager + ':' + obj_id + '][' + count + ']');
		var prop = propertySel[propertySel.selectedIndex].value;
		var type = integerPropTypeSel[selectedPropTypeIndex].value;		
		if(type == 0){
			
			og.openLink(og.getUrl('user', 'list_users'), {
				callback: function(success, data) {
					var users = data.users;
					var integerPropTD = document.getElementById('integerPropTD[' + manager + ':' + obj_id + '][' + count + ']');
					var html = "";
					html += '<select name="propValues[' + manager + ':' + obj_id + '][' + prop + ']" id="propValues[' + manager + ':' + obj_id + '][' + prop + ']">';
					if (users.length > 0){
						for(i=0;i < users.length ;i++){
							var usu = users[i];
							html += '<option value="'+ usu.id+'" name="propValue[' + manager + ':' + obj_id + '][' + prop + ']">'+ usu.name + '</option>';
						}
					}else{
						html += '<option value="0" name="propValue[' + manager + ':' + obj_id + '][' + prop + ']">'+ lang ('no users to display') + '</option>';
					}
					html += '</select>';
					integerPropTD.innerHTML = html;
					if (typeof callback == 'function') callback();
				}
			});		
			
			
		}else if(type == 1){
			var selectParam = '';
			if(og.templateParameters.length > 0){
				selectParam = '<input type="hidden" name="propValues[' + manager + ':' + obj_id + '][' + prop + ']" id="propValues[' + manager + ':' + obj_id + '][' + prop + ']" value="" />'
				selectParam += '<select name="propValueParam[' + manager + ':' + obj_id + '][' + prop + ']" id="propValueParam[' + manager + ':' + obj_id + '][' + prop + ']" onChange="og.changeIntegerParam(this)" />';
				for(var j=0; j < og.templateParameters.length; j++){
					var item = og.templateParameters[j];
					if(item.type == "user"){
						// value="{'+ item['name'] +'}" name="propValue[' + manager + ':' + obj_id + '][' + prop + ']"
						selectParam += '<option>' + item['name'] + '</option>';
					}
				}
				selectParam += '</select>';
				integerPropTD.innerHTML = selectParam;
			}
			if(selectParam == ''){
				alert(lang('no parameters in template'));
				integerPropTypeSel.selectedIndex = 0;
			}
		}
	}
};

og.changeIntegerParam = function(select){
	alert('asdasda');
	var id = select.id();
	id = id.repace("propValueParam","propValues");
	hidd = document.getElementById(id);
	if (hidd){
		hidd.value = select.value;
	}
	
}

og.editStringTemplateObjectProperty = function(obj_id, prop, manager){
	var params = [];
	for(var j=0; j < og.templateParameters.length; j++){
		var item = og.templateParameters[j];
		if(item.type == "string"){
			params.push([item['name']]);
		}
	}
	var valueField = document.getElementById('propValues[' + manager + ':' + obj_id + '][' + prop + ']');
	var config = {
			genid: Ext.id(),
			title: lang('edit object property'),
			height: 350,
			width: 350,
			labelWidth: 80,
			ok_fn: function() {
				var value = Ext.getCmp('propValue').getValue();
				valueField.value = value;
				og.ExtendedDialog.hide();
			},
			dialogItems:[
			    {
			    	xtype: 'combo',
			    	fieldLabel: lang('template parameters'),
			    	id: 'paramSel',
			    	mode: 'local',
			    	width: 100,
			    	editable: false,
			    	forceSelection: true,
			    	triggerAction: 'all',
			    	displayField: 'name',
			    	valueField: 'name',
			    	store: new Ext.data.SimpleStore({
						fields: ['name'],
						data : params
					})
			    },
			    {
			    	xtype: 'button',
			    	text: lang('add'),
			    	id: 'addParamterBtn',
			    	iconCls: 'ico-add',
			    	listeners: {
			    		click: function(button, event){
			    			var param = '{' + Ext.getCmp('paramSel').getValue() + '}';
			    			Ext.getCmp('propValue').setValue(Ext.getCmp('propValue').getValue() + param);
			    		}
			    	}
			    },
			    {
			    	xtype: 'textarea',
			    	width: 280,
			    	height: 200,
			    	id: 'propValue',
			    	hideLabel: true,
			    	listeners: {
			    		render: function(textarea){
			    			textarea.setValue(valueField.value);
			    		}
			    	}
			    }
			]
		};
		og.ExtendedDialog.show(config);
};

og.promptAddParameter = function(before, edit, pos) {
	var paramName = document.getElementById('parameters[' + pos + '][name]');
	var paramType = document.getElementById('parameters[' + pos + '][type]');
	var loadName = '';
	var loadType = 'string';
	if(paramName != null){
		loadName = paramName.value;
		loadType = paramType.value;
	}
	var config = {
		genid: Ext.id(),
		title: lang('add parameter'),
		height: 150,
		width: 350,
		labelWidth: 50,
		ok_fn: function() {
			var name = Ext.getCmp('paramName').getValue();
			if(name == ""){
				alert(lang('parameter name empty'));
				return;
			}
			if(og.parameterNameExistsInTemplate(before, name)){
				alert(lang('parameter name exists'));
				return;
			}
			var type = Ext.getCmp('paramType').getValue();
			if(!edit){
				og.addParameterToTemplate(before, name, type);
			}else{
				var oldname = paramName.value;
				for (var i=0; i < og.templateParameters.length; i++) {
					if (og.templateParameters[i].name == oldname) {
						og.templateParameters[i].name = name;
						break;
					}
				}
				paramName.value = name;
				var paramNameSpan = document.getElementById('paramName[' + pos + ']');
				paramNameSpan.innerHTML = '<b>' + name + '</b>&nbsp;(' + lang(type) + ') ';
			}
			og.ExtendedDialog.hide();
		},
		dialogItems:[
		    {
		    	xtype: 'textfield',
		    	fieldLabel: lang('name'),
		    	id: 'paramName',
		    	value: loadName
		    },
		    {
		    	xtype: 'combo',
		    	fieldLabel: lang('type'),
		    	id: 'paramType',
		    	mode: 'local',
		    	width: 100,
		    	editable: false,
		    	disabled: edit,
		    	forceSelection: true,
		    	triggerAction: 'all',
		    	displayField: 'name',
		    	valueField: 'id',
		    	value: loadType,
		    	store: new Ext.data.SimpleStore({
					fields: ['id', 'name'],
					data : [['string', lang('string')],['date', lang('date')],['user', lang('user')]]
				})
		    }
		]
	};
	og.ExtendedDialog.show(config);
};

og.parameterNameExistsInTemplate = function(before, name){
	var parent = before.parentNode;
	var inputs = parent.getElementsByTagName('input');
	for (var i=0; i < inputs.length; i=i+2) {
		if(inputs[i].value == name){
			return true;
		}
	}
	return false;
};

og.addParameterToTemplate = function(params, name, type) {
	var parent = params.parentNode;
	var count = parent.getElementsByTagName('input').length / 2;
	var div = document.createElement('div');
	div.className = "og-add-template-object";
	div.innerHTML =
		'<input type="hidden" name="parameters[' + count + '][name]" id="parameters[' + count + '][name]" value="' + name + '"/>&nbsp;' +
		'<input type="hidden" name="parameters[' + count + '][type]" id="parameters[' + count + '][type]" value="' + type + '"/>&nbsp;' +
		'<span class="name" id="paramName[' + count + ']"><b>' + name + '</b>&nbsp;(' + lang(type) + ') </span>' +
		'<span id="editRemoveParam' + count + '" style="cursor:pointer;line-height:25px;position:absolute;right:0;top:0;"><a href="#" onclick="og.promptAddParameter(this, 1, ' + count + ')" >'+lang('edit')+'</a>' +
		'&nbsp;|&nbsp;<a href="#" onclick="og.removeParameterFromTemplate(this.parentNode.parentNode, \'' + name + '\')" class="removeParamDiv">'+lang('remove')+'</a></span>';
	parent.insertBefore(div, params);
	var param = [];
	param['name'] = name;
	param['type'] = type;
	og.templateParameters.push(param);
};

og.removeParameterFromTemplate = function(div, name) {
	var parent = div.parentNode;
	parent.removeChild(div);
	var inputs = parent.getElementsByTagName('input');
	var count = 0;
	for (var i=0; i < inputs.length; i=i+2) {
		inputs[i].id = 'parameters[' + count + '][name]';
		inputs[i].name = 'parameters[' + count + '][name]';
		inputs[i + 1].id = 'parameters[' + count + '][type]';
		inputs[i + 1].name = 'parameters[' + count + '][type]';
		count++;
	}
	for(var j=0; j < og.templateParameters.length; j++){
		var param = og.templateParameters[j];
		if(param['name'] == name){
			break;
		}
	}
	og.templateParameters.splice(j,1);
	for(var k=j+1; k <= og.templateParameters.length; k++){
		var paramNameSpan = document.getElementById('paramName[' + k + ']');
		paramNameSpan.id = 'paramName[' + (k - 1) + ']';
		paramNameSpan.name = 'paramName[' + (k - 1) + ']';
		var editRemoveParamSpan = document.getElementById('editRemoveParam' + k);
		editRemoveParamSpan.id = 'editRemoveParam' + (k - 1)
		editRemoveParamSpan.innerHTML = '<a href="#" onclick="og.promptAddParameter(this, 1, ' + (k - 1) + ')" >'+lang('edit')+'</a>' +
		'&nbsp;|&nbsp;<a href="#" onclick="og.removeParameterFromTemplate(this.parentNode.parentNode, \'' + name + '\')" class="removeParamDiv">'+lang('remove')+'</a>';
	}
};

og.templateConfirmSubmit = function(genid) {
	var div = document.getElementById(genid + "add_template_objects_div");
	var count = div.getElementsByTagName('input').length;
	if (count == 0) {
		return confirm(lang('confirm template with no objects'));
	}
	return true;
};