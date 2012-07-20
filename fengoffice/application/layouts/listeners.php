<script>

og.eventManager.addListener('reload member restrictions', 
 	function (genid){ 
		App.modules.addMemberForm.drawDimensionRestrictions(genid, document.getElementById(genid + 'dimension_id').value);
 	}
);

og.eventManager.addListener('reload tab panel', 
 	function (name){
 		if (name) {
			Ext.getCmp(name).reload();
  		}
 	}
);

og.eventManager.addListener('reload member properties', 
 	function (genid){
 		App.modules.addMemberForm.drawDimensionProperties(genid, document.getElementById(genid + 'dimension_id').value);
 	}
);

og.eventManager.addListener('reload dimension tree', 
 	function (dim_id){
 		if (!og.reloadingDimensions){ 
 			og.reloadingDimensions = {} ;
 		}
 		if (!og.reloadingDimensions[dim_id]){
	 		og.reloadingDimensions[dim_id] = true ;
	 		
	 		var tree = Ext.getCmp("dimension-panel-" + dim_id);
	 		if (tree) {
	 			var selection = tree.getSelectionModel().getSelectedNode();
	 			
		 		tree.suspendEvents();
		 		var expanded = [];
		 		tree.root.cascade(function(){
	 				if (this.isExpanded()) expanded.push(this.id);
	 			});
		 		tree.loader.load(tree.getRootNode(), function() {
			 		tree.expanded_once = false;
		 			og.expandCollapseDimensionTree(tree, expanded, selection ? selection.id : null);
			 		og.reloadingDimensions[dim_id] = false;
			 		if (og.select_member_after_reload) {
			 			og.selectDimensionTreeMember(og.select_member_after_reload);
			 			og.select_member_after_reload = null;
			 		}
			 	});
		 		tree.resumeEvents();
	 		}
 		}
 		
 	}
);

og.eventManager.addListener('reset dimension tree', 
 	function (dim_id){
 		if (!og.reloadingDimensions){ 
 			og.reloadingDimensions = {} ;
 		}
 		if (!og.reloadingDimensions[dim_id]){
	 		og.reloadingDimensions[dim_id] = true ;
	 		var tree = Ext.getCmp("dimension-panel-" + dim_id);
	 		if (tree) {
		 		tree.suspendEvents();
 				tree.loader = tree.initialLoader;
		 		tree.loader.load(tree.getRootNode(),function(){
			 		tree.resumeEvents(); 
			 		og.Breadcrumbs.refresh(tree.getRootNode());
			 	});
		 		tree.expandAll();
	 		}
 		}
 	}
);

og.eventManager.addListener('select dimension member', 
	function (data){
		if (og.reloadingDimensions[data.dim_id]) {
		//	og.select_member_after_reload = data;
		} else {
			og.selectDimensionTreeMember(data);
		}
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
		switch (option.name) {
			case 'drag_drop_prompt' :
				og.preferences.drag_drop_prompt = option.value;
				break;	
			case 'localization':
				window.location.reload();
				break;
			default: 
				break;		
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

og.eventManager.addListener('tabs changed',
	function(option) {
		window.location.href = '<?php echo ROOT_URL?>' ;
	}
);
og.eventManager.addListener('logo changed',
	function(option) {
		window.location.href = '<?php echo ROOT_URL?>' ;
	}
);
og.eventManager.addListener('expand menu panel',
	function(option) {
		Ext.getCmp('menu-panel').expand(true);
	}		
);

og.eventManager.addListener('after member save', 
	function (member){
		/*
		member = {
    		dimension_id:"1", 
			member_id:"368", 
			name:"Weekly Planningg", 
			object_type_id:"1", 
			parent_member_id:"8"
		}
		*/

		if (og.dimensions[member.dimension_id]){
			if (!og.dimensions[member.dimension_id][member.member_id]) {
				og.dimensions[member.dimension_id][member.member_id] = {};
				og.dimensions[member.dimension_id][member.member_id].id = member.member_id ;
			}
			og.dimensions[member.dimension_id][member.member_id].name=member.name; 
			og.dimensions[member.dimension_id][member.member_id].ot=member.object_type_id;
		}
	}
);

</script>