<script>
//some event handlers
og.eventManager.addListener('tag changed', 
 	function (tag){ 
 		if (Ext.getCmp('tabs-panel').getActiveTab().id == 'tasks-panel') {
 			og.openLink('<?php echo get_url('task','new_list_tasks')?>',
 				{caller:'tasks-panel',
 				get:{tag:tag}}
 			);
 		}
 	}
);
og.eventManager.addListener('workspace changed', 
 	function (ws){ 
 	}
);

og.eventManager.addListener('company added', 
 	function (company) {
 		var elems = document.getElementsByName("contact[company_id]");
 		for (var i=0; i < elems.length; i++) {
 			if (elems[i].tagName == 'SELECT') {
	 			var opt = document.createElement('option');
	        	opt.value = company.id;
		        opt.innerHTML = company.name;
	 			elems[i].appendChild(opt);
 			}
 		}
 	}
);

og.eventManager.addListener('contact added from mail', 
	function (obj) {
		var hf_contacts = document.getElementById(obj.hf_contacts);
		if (hf_contacts) hf_contacts.value += (hf_contacts != '' ? "," : "") + obj.combo_val;
		var div = Ext.get(obj.div_id);
 		if (div) div.remove();
 	}
);

og.eventManager.addListener('draft mail autosaved', 
	function (obj) {
		var hf_id = document.getElementById(obj.hf_id);
		if (hf_id) hf_id.value = obj.id;
 	}
);

og.eventManager.addListener('popup',
	function (args) {
		og.msg(args.title, args.message, 0, args.type, args.sound);
	}
);

og.eventManager.addListener('user preference changed',
	function(option) {
		// experimental (not developed): dynamically change localization
		if (option.name == 'localization') {
			og.loadScripts([og.getUrl('access', 'get_javascript_translation')], {
				callback: function() {
					var spans = document.getElementsByName('og-lang');
					for (var i=0; i < spans.length; i++) {
						var key = spans[i].id.substring(8);
						spans[i].innerHTML = lang(key);
					}
				}
			});
		}
	}
);

og.eventManager.addListener('download document',
	function(args) {
		if(args.reloadDocs){
			//og.openLink(og.getUrl('files', 'list_files'));
			og.panels.documents.reload();
		}	
		location.href = og.getUrl('files', 'download_file', {id: args.id, validate:0});
	}
);

og.eventManager.addListener('config option changed',
	function(option) {
		og.config[option.name] = option.value;
	}
);

og.eventManager.addListener('user preference changed',
	function(option) {
		og.preferences[option.name] = option.value;
	}
);

og.eventManager.addListener('select workspace', 
    function (ws){ 
        Ext.getCmp('workspace-panel').select(ws);
    }
);

og.eventManager.addListener('back_to_wizard', 
	    function (ws){ 
			Ext.getCmp('tabs-panel').setActiveTab(og.panels.overview);
	    }
);

og.eventManager.addListener('refresh_assignees', 
	    function (assign){
			assigned_user = assign; 
			og.openLink(og.getUrl('task', 'allowed_users_to_assign', {ws_id:wsVal}), {callback:og.drawUserLists});
	    }
);

</script>