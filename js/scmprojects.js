YAHOO.namespace('scmfviz.scmprojects');

YAHOO.scmfviz.scmprojects = function () {
    var dataTable = null;
    
    var dataSource = null;
        
    var init = function() {
        dataSource = new YAHOO.util.DataSource("getscmprojects.php"); 
        dataSource.responseType = YAHOO.util.DataSource.TYPE_JSON;
        dataSource.responseSchema = {
            resultsList: "ResultSet.Result", 
            fields: ["name", "repository_url", "final_platform", "starting_version", "is_public"]
        };

        var columns = [
            { key: "repository_url", label: "URL", sortable:true, resizeable:true},
            { key: "name", label: "Name", sortable:true, resizeable:true},
            { key: "final_platform", label: "Final platform", sortable:true, resizeable:true},
            { key: "starting_version", label: "Starting version", sortable:true, resizeable:true},
            { key: "is_public", label: "Public", sortable:true, formatter: "checkbox", resizeable:true},
        ];
        dataTable = new YAHOO.widget.DataTable("projects", columns, dataSource);
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

