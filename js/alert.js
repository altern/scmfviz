YAHOO.namespace('scmfviz.alert');

YAHOO.scmfviz.alert = function(message, handlerFunc) {
    var dlg = new YAHOO.widget.SimpleDialog("alertDialog", {
        width: "300px",
        fixedcenter: true,
        visible: false,
        draggable: true,
        close: false,
        text: message,
        icon: YAHOO.widget.SimpleDialog.ICON_WARN,
        modal: true,
        constraintoviewport: true,
        buttons: [ {text:"OK",  handler: function() {
                    dlg.hide();
                    if(handlerFunc) handlerFunc.call();
                } } ]
    });
    dlg.render(document.body);
    dlg.show();
} ;