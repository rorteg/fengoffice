Ext.BLANK_IMAGE_URL = "s.gif";
// fix node background problem
Ext.override(Ext.tree.TreeEventModel, {
	initEvents : function(){
		var el = this.tree.getTreeEl();
		el.on('click', this.delegateClick, this);
		if(this.tree.trackMouseOver !== false){
			var innerCt = Ext.fly(el.dom.firstChild);
			innerCt.on('mouseover', this.delegateOver, this);
			innerCt.on('mouseout', this.delegateOut, this);
		}
		el.on('dblclick', this.delegateDblClick, this);
		el.on('contextmenu', this.delegateContextMenu, this);
	}
});
// disable keyboard navigation on tree panels
Ext.tree.DefaultSelectionModel.override({
   onKeyDown: Ext.emptyFn
});
/**/
// Uncomment this to support drag and drop in grids
// Also uncomment enableDrag on grids, enableDrop on workspacepanel and ddGroup on both
Ext.grid.CheckboxSelectionModel.override({
    handleMouseDown: Ext.emptyFn   
});
Ext.grid.GridView.override({
	focusCell : function(row, col, hscroll){
		this.syncFocusEl(this.ensureVisible(row, col, hscroll));
		this.focusEl.focus.defer(1, this.focusEl);
	},

    syncFocusEl : function(row, col, hscroll){
        var xy = row;
        if(!Ext.isArray(xy)){
            row = Math.min(row, Math.max(0, this.getRows().length-1));
            //xy = this.getResolvedXY(this.resolveCell(row, col, hscroll));
        }
        this.focusEl.setXY(xy||this.scroller.getXY());
    }
});
Ext.grid.RowSelectionModel.override({
    initEvents : function() {
        if (!this.grid.enableDragDrop && !this.grid.enableDrag) {
            this.grid.on("rowmousedown", this.handleMouseDown, this);
        } else { // allow click to work like normal
            this.grid.on("rowclick", function(grid, rowIndex, e) {
                var target = e.getTarget();                
                if (target.className !== 'x-grid3-row-checker' && e.button === 0 && !e.shiftKey && !e.ctrlKey) {
                    this.selectRow(rowIndex, false);
                    grid.view.focusRow(rowIndex);
                }
            }, this);
        }

        this.rowNav = new Ext.KeyNav(this.grid.getGridEl(), {
            "up" : function(e){
                if(!e.shiftKey){
                    this.selectPrevious(e.shiftKey);
                }else if(this.last !== false && this.lastActive !== false){
                    var last = this.last;
                    this.selectRange(this.last,  this.lastActive-1);
                    this.grid.getView().focusRow(this.lastActive);
                    if(last !== false){
                        this.last = last;
                    }
                }else{
                    this.selectFirstRow();
                }
            },
            "down" : function(e){
                if(!e.shiftKey){
                    this.selectNext(e.shiftKey);
                }else if(this.last !== false && this.lastActive !== false){
                    var last = this.last;
                    this.selectRange(this.last,  this.lastActive+1);
                    this.grid.getView().focusRow(this.lastActive);
                    if(last !== false){
                        this.last = last;
                    }
                }else{
                    this.selectFirstRow();
                }
            },
            scope: this
        });

        var view = this.grid.view;
        view.on("refresh", this.onRefresh, this);
        view.on("rowupdated", this.onRowUpdated, this);
        view.on("rowremoved", this.onRemove, this);        
    }
});



Ext.override(Ext.Element, {
	getAttributeNS : function(ns, name){
		
		if (Ext.isIE) {
			var ieVer = navigator.userAgent.match(/msie (\d+)/i);
			ieVer = ieVer ? parseInt(ieVer[1], 10) : 0;
		}
		
		if (!Ext.isIE || ieVer >= 9) {
			var d = this.dom;
		    return d.getAttributeNS(ns, name) || d.getAttribute(ns+":"+name) || d.getAttribute(name) || d[name];
		} else {
			var d = this.dom;
		    var type = typeof d[ns+":"+name];
		    if(type != 'undefined' && type != 'unknown'){
		        return d[ns+":"+name];
		    }
		    return d[name];
		}
	}
});


// IE 9 does not implement function createContextualFragment for range objects
if (typeof Range != "undefined") {
	if (typeof Range.prototype.createContextualFragment == "undefined") {
	    Range.prototype.createContextualFragment = function (html) {
	        var doc = window.document;
	        var container = doc.createElement("div");
	        container.innerHTML = html;
	        var frag = doc.createDocumentFragment(), n;
	        while ((n = container.firstChild)) {
	            frag.appendChild(n);
	        }
	        return frag;
	    };
	}
}

/**/