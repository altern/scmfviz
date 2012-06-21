YAHOO.namespace('scmfviz.loading');
YAHOO.scmfviz.loading = function() {
    var panel = null;
    var init = function() {
        panel = new YAHOO.widget.Panel("loading",  
            {width:"240px", 
                fixedcenter:true, 
                close:false, 
                draggable:false, 
                zindex:4,
                modal:true,
                visible:false
            } 
        );

        panel.setHeader("Loading, please wait...");
        panel.setBody('<img src="img/long_loading.gif" />');
        
        var enterListener = new YAHOO.util.KeyListener(document, {keys: [27]}, {
            fn:panel.hide,
            scope:panel,
            correctScope:true
        });
        enterListener.enable();
        
        panel.render(document.body);
    }
    
    return {
        init: init,
        show: function() {
            panel.show();
        },
        hide: function() {
            panel.hide();
        }
    }
} ();