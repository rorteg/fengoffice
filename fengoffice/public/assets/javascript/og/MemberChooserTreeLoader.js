
// ***** tree loader ***** //
og.MemberChooserTreeLoader = function(config) {
	og.MemberChooserTreeLoader.superclass.constructor.call(this, config);
	if (this.ownerTree) {		
		this.ownerTree.totalNodes = 0 ;
	}
};

Ext.extend(og.MemberChooserTreeLoader , Ext.tree.TreeLoader, {
		
	ownerTree: null ,
	
	createNode: function (attr) {
		
		if (  Ext.type(this.ownerTree ) ){	
			if (this.ownerTree.totalNodes) {
				this.ownerTree.totalNodes++ ;
			}else{
				this.ownerTree.totalNodes = 1;
			}
		}else{
			alert("MemberChooserTreeLoader.js - TREE NOT DEFINED  ! ! ! "+ attr.text) ;
		}
		
        // apply baseAttrs, nice idea Corey!
        if(this.baseAttrs){
            Ext.applyIf(attr, this.baseAttrs);
        }
        if(this.applyLoader !== false){
            attr.loader = this;
        }
        if(typeof attr.uiProvider == 'string'){
           attr.uiProvider = this.uiProviders[attr.uiProvider] || eval(attr.uiProvider);
        }
        if(attr.nodeType){

            var node =  Ext.tree.TreePanel.nodeTypes[attr.nodeType](attr);
        }else{
        	
            var node = attr.leaf ?
	            new Ext.tree.TreeNode(attr) :
	            new Ext.tree.AsyncTreeNode(attr);
                       
        }
		node.object_id = attr.object_id ;
		node.options = attr.options ;
		node.object_controller = attr.object_controller ;
		node.object_type_id = attr.object_type_id ;
		node.allow_childs = attr.allow_childs ;
        
		if (attr.actions){
			node.actions = attr.actions ;
		}
        
        return node ;            
        
	},
	
	processResponse:function(response, node, callback) {
		if (  Ext.type(this.ownerTree ) ){
			this.ownerTree.totalNodes = 1 ;
		}
		var json = response.responseText;
		try {
			var o = eval("("+json+")");
			o = o.dimension_members;

			for(var i = 0, len = o.length; i < len; i++){
				var n = this.createNode(o[i]);
				n.object_id = o[i].object_id ;
				n.options = o[i].options ;
				n.object_controller = o[i].object_controller ;
				n.allow_childs = o[i].allow_childs ;

				if(n){
					node.appendChild(n);
				}
			}
			node.endUpdate();
			if(typeof callback == "function"){
				callback(this, node);
			}
			this.ownerTree.expanded_once = false;
		}catch(e){
			this.handleFailure(response);
		}
	}
	
	
}); 
