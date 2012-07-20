
og.workspaces = {
	init: function() {
		for (x in og.dimension_object_types) {
			if (og.dimension_object_types[x] == 'workspace') {
				og.additional_on_dimension_object_click[x] = 'og.workspaces.onWorkspaceClick(<parameters>);';
			} else if (og.dimension_object_types[x] == 'tag') {
				og.additional_on_dimension_object_click[x] = 'og.workspaces.onTagClick(<parameters>);';
			}
		}
	},
	
	
	onWorkspaceClick: function(member_id) {
		var dimensions_panel = Ext.getCmp('menu-panel');
		dimensions_panel.items.each(function(item, index, length) {
			if (item.dimensionCode == 'workspaces') {
				og.expandCollapseDimensionTree(item);
				var n = item.getNodeById(member_id);
				if (n) {
					if (n.parentNode) item.expandPath(n.parentNode.getPath(), false);
					n.select();
					og.eventManager.fireEvent('member tree node click', n);
				}
			}
		});
	},
	
	onTagClick: function(member_id) {
		var dimensions_panel = Ext.getCmp('menu-panel');
		dimensions_panel.items.each(function(item, index, length) {
			if (item.dimensionCode == 'tags') {
				og.expandCollapseDimensionTree(item);
				var n = item.getNodeById(member_id);
				if (n) {
					if (n.parentNode) item.expandPath(n.parentNode.getPath(), false);
					n.select();
					og.eventManager.fireEvent('member tree node click', n);
				}
			}
		});
	}
};