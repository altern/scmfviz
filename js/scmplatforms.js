YAHOO.namespace('scmfviz.scmplatforms');

YAHOO.scmfviz.scmplatforms = function () {
    var dataTable = null;
    
    var getDataSource = function() {
        var dataSource = new YAHOO.util.DataSource("getscmplatforms.php?url=" + YAHOO.scmfviz.project.getURL()); 
        dataSource.responseType = YAHOO.util.DataSource.TYPE_JSON;
        dataSource.responseSchema = {
            resultsList: "ResultSet.Result", 
            fields: ["id", "url", /*"project",*/ "deployments_limit", "deployment_method", "remote_directory"]
        };
        return dataSource;
    }    
    var createPlatform = function() {
        var projectName = YAHOO.scmfviz.project.getName();
        if(projectName) {
            YAHOO.scmfviz.editPlatformDialog.show();
        } else {
            YAHOO.scmfviz.alert('You need to edit and save project properties first', function() { fadeOut('editProjectLink'); });
        }
    }
    var init = function() {
        var columns = [
            {key: "url", label: "URL", sortable:true, resizeable:true, formatter: function (cell, rec, col, data) {
                cell.innerHTML = '<a href="#" onclick="YAHOO.scmfviz.platformInfoDialog.show(\'' + data + '\')">' + data + '</a>';    
            }},
//            {key: "project", label: "Project", sortable:true, resizeable:true},
            {key: "deployments_limit", label: "Deployments limit", sortable:true, resizeable:true},
            {key: "deployment_method", label: "Deployment method", sortable:true, resizeable:true},
            {key: "remote_directory", label: "Remote directory", sortable:true, resizeable:true},
            {key: "edit", label: "", sortable:true, resizeable:true, data:'edit', formatter: function (cell, rec, col, data) {
                cell.innerHTML = '<button type="button" id="edit' + rec._nCount + '">Edit</button>';
                YAHOO.util.Event.onContentReady("edit"+rec._nCount, function() {
                    new YAHOO.widget.Button('edit'+rec._nCount);
                });
            }},
            {key: "delete", label: "", sortable:true, resizeable:true, data:'delete', formatter: function (cell, rec, col, data) {
                cell.innerHTML = '<button type="button" id="delete' + rec._nCount + '">Delete</button>';
                YAHOO.util.Event.onContentReady("delete"+rec._nCount, function() {
                    new YAHOO.widget.Button('delete'+rec._nCount);
                });
            }},
        ];
        dataTable = new YAHOO.widget.DataTable("platforms", columns, getDataSource());
        dataTable.subscribe("rowClickEvent", dataTable.onEventSelectRow);
        
        dataTable.subscribe("buttonClickEvent", function(oArgs){ 
            var oRecord = this.getRecord(oArgs.target); 
            var data = oRecord.getData();
            if(oArgs.target.id.indexOf('delete') === 0) {
                var deleteHandler = function() {
                    if(data.id) {
                        var url = 'deleteplatform.php?id=' + data.id;
                        var callback = {
                            success: function(oResponse) {
                                var response = JSON.parse(oResponse.responseText);
                                if(response.data == 'OK') {
                                    YAHOO.scmfviz.scmplatforms.refresh();
                                    YAHOO.scmfviz.scmdeployments.refresh();
                                    YAHOO.scmfviz.scmtree.refresh();
                                } else {
                                    YAHOO.scmfviz.alert(response.error_message);
                                }
                                dlg.hide();
                                YAHOO.scmfviz.loading.hide();
                            },
                            failure: function(oResponse) {
                                YAHOO.log("Failed to process XHR transaction.", "info", "example");
                                if(console) console.log(oResponse);
                                YAHOO.scmfviz.loading.hide();
                            },
                            timeout: 8000
                        }
                        YAHOO.scmfviz.loading.show();
                        YAHOO.util.Connect.asyncRequest('GET', url, callback);
                    }
                }
                var dlg = new YAHOO.widget.SimpleDialog("credentialsConfirm", {
                    width: "400px",
                    fixedcenter: true,
                    visible: false,
                    draggable: true,
                    close: false,
                    text: "Note that if you want to delete platform, all corresponding "
                            + "deployment rules will be deleted too. Do you really want to delete platform <b>"
                            + data.url + "</b>?",
                    icon: YAHOO.widget.SimpleDialog.ICON_HELP,
                    modal: true,
                    constraintoviewport: true,
                    buttons: [ {text:"Delete", handler:deleteHandler, isDefault:true},
                                {text:"Cancel",  handler: function() {this.hide()}} ]
                });
                dlg.render(document.body);
                dlg.show();
            } else if (oArgs.target.id.indexOf('edit') === 0) {
                YAHOO.scmfviz.editPlatformDialog.show(data);
            }
        });
        
//        var oPushButton4 = new YAHOO.widget.Button("pushbutton4"); 
        var div = new YAHOO.util.Element(dataTable.getContainerEl().id);
        var element = new YAHOO.util.Element(document.createElement('button'));
        element.set('type', 'button');
        element.set('id', 'createPlatform');
        element.set('innerHTML', 'Create platform');
        div.appendChild(element);
//        YAHOO.util.Dom.insertAfter('<button type="button" id="createPlatform">Create</button>', dataTable.getContainerEl());
        YAHOO.util.Event.onContentReady("createPlatform", function() {
            new YAHOO.widget.Button('createPlatform');
            YAHOO.util.Event.addListener('createPlatform', 'click', createPlatform);
        });
    }
    
    return {
        init: init,
        refresh: function() {
            if(!dataTable) {
                init();
            } else {
                dataTable.load({'datasource': getDataSource()});
            }
        }
    }
} ();

