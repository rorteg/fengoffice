
og.pickPreviousTask = function(before, genid) {
	og.ObjectPicker.show(function (objs) {
		if (objs && objs.length > 0) {
			var obj = objs[0].data;
			if (obj.type != 'task') {
				og.msg(lang("error"), lang("object type not supported"), 4, "err");
			} else {
				og.addPreviousTask(this, obj, genid);
			}
		}
	}, before, {
		types: {
			'ProjectTasks': true
		}
	});
};

og.addPreviousTask = function(before, obj, genid) {
	var parent = before.parentNode;
	var count = parent.getElementsByTagName('input').length;
	var div = document.createElement('div');
	div.className = "og-add-template-object ico-" + obj.type + (count % 2 ? " odd" : "");
	div.innerHTML =
		'<input type="hidden" name="task[previous]['+og.previousTasksIdx+']" value="' + obj.object_id + '" />' +
		'<span class="name">' + og.clean(obj.name) + '</span>' +
		'<a href="#" onclick="og.removePreviousTask(this.parentNode, \''+genid+'\', '+og.previousTasksIdx+')" class="removeDiv" style="display: block;">'+lang('remove')+'</div>';
	var label = document.getElementById(genid + 'no_previous_selected');
	if (label) label.style.display = 'none';
	parent.insertBefore(div, before);
	og.previousTasks[og.previousTasksIdx] = obj;
	og.previousTasksIdx++;
};

og.removePreviousTask = function(div, genid, index) {
	var parent = div.parentNode;
	parent.removeChild(div);
	og.previousTasks = og.previousTasks.splice(index, 1);
	if (og.previousTasks.length == 0) {
		var label = document.getElementById(genid + 'no_previous_selected');
		if (label) label.style.display = 'inline';
	}
};

og.pickPreviousTaskFromView = function(tid) {
	og.ObjectPicker.show(function (objs) {
		if (objs && objs.length > 0) {
			var obj = objs[0].data;
			if (obj.type != 'task') {
				og.msg(lang("error"), lang("object type not supported"), 4, "err");
			} else {
				og.openLink(og.getUrl('taskdependency', 'add', {pt:obj.object_id, t:tid}));
			}
		}
	}, this, {
		types: {
			'ProjectTasks': true
		}
	});
};