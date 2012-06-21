YAHOO.namespace('scmfviz.scmactions');

YAHOO.scmfviz.scmactions = function () {
    var dataTable = null;
    
    var dataSource = null;
        
    var init = function() {
        dataSource = new YAHOO.util.DataSource("getscmactions.php"); 
        dataSource.responseType = YAHOO.util.DataSource.TYPE_JSON;
        var data = [
            {message:"Created beta build 0.x.2",revision:"42"},
            {message:"Created alpha build 0.x.1",revision:"41"},
            {message:"Created pre-alpha build 0.x.0",revision:"40"}
        ];
//        dataSource = new YAHOO.util.DataSource(data); 
//        dataSource.responseType = YAHOO.util.DataSource.TYPE_JSARRAY;
        dataSource.responseSchema = {
            resultsList: "ResultSet.Result", 
            fields: ["revision", "message"]
        };

        var columns = [
            { key: "revision", label: "Revision", sortable:true, resizeable:true},
            { key: "message", label: "Action", sortable:true, resizeable:true}
        ];
        dataTable = new YAHOO.widget.DataTable("actions", columns, dataSource);
        dataTable.subscribe("rowClickEvent", dataTable.onEventSelectRow);
    }
    
    return {
        init: init,
        refresh: function() {
            if(!dataTable) {
                init();
            } else {
                dataTable.load();
            }
        }
    }
} ();

