YAHOO.namespace('scmfviz.scmdeployments');

YAHOO.scmfviz.scmdeployments = function () {
    var dataTable = null;
    
    var createDeploymentRule = function() {
        var projectName = YAHOO.scmfviz.project.getName();
        if(projectName) {
            YAHOO.scmfviz.editDeploymentRuleDialog.show();
        } else {
            YAHOO.scmfviz.alert('You need to edit and save project properties first', function() { fadeOut('editProjectLink'); });
        }
    }
    
    var getDataSource = function() {
        var dataSource = new YAHOO.util.DataSource("getscmdeployments.php?url=" + YAHOO.scmfviz.project.getURL()); 
        dataSource.responseType = YAHOO.util.DataSource.TYPE_JSON;
        dataSource.responseSchema = {
            resultsList: "ResultSet.Result", 
            fields: ["id", "pattern", "platform"]
        };
        return dataSource;
    }
    var init = function() {
        
        var columns = [
            {key: "pattern", label: "Pattern", sortable:true, resizeable:true},
            {key: "platform", label: "Platform", sortable:true, resizeable:true, formatter: function (cell, rec, col, data) {
                cell.innerHTML = '<a href="#" onclick="YAHOO.scmfviz.platformInfoDialog.show(\'' + data + '\')">' + data + '</a>';
            }},
            {key: "delete", label: "", sortable:true, resizeable:true, formatter: function (cell, rec, col, data) {
                cell.innerHTML = '<button type="button" id="delete' + rec._nCount + '">Delete</button>';
                YAHOO.util.Event.onContentReady("delete"+rec._nCount, function() {
                    new YAHOO.widget.Button('delete'+rec._nCount);
                });
            }},
        ];
        dataTable = new YAHOO.widget.DataTable("deployments", columns, getDataSource());
        dataTable.subscribe("rowClickEvent", dataTable.onEventSelectRow);
        
        var div = new YAHOO.util.Element(dataTable.getContainerEl().id);
        var element = new YAHOO.util.Element(document.createElement('button'));
        element.set('type', 'button');
        element.set('id', 'createDeploymentRule');
        element.set('innerHTML', 'Create deployment rule');
        div.appendChild(element);
//        YAHOO.util.Dom.insertAfter('<button type="button" id="createPlatform">Create</button>', dataTable.getContainerEl());
        YAHOO.util.Event.onContentReady("createDeploymentRule", function() {
            new YAHOO.widget.Button('createDeploymentRule');
            YAHOO.util.Event.addListener('createDeploymentRule', 'click', createDeploymentRule);
        });
        dataTable.subscribe("buttonClickEvent", function(oArgs){ 
            var oRecord = this.getRecord(oArgs.target); 
            var data = oRecord.getData();
            var deleteHandler = function() {
                if(data.id) {
                    var url = 'deletedeploymentrule.php?id=' + data.id;
                    var callback = {
                        success: function(oResponse) {
                            var response = JSON.parse(oResponse.responseText);
                            if(response.data == 'OK') {
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
                text: "Do you really want to delete deployment rule?",
                icon: YAHOO.widget.SimpleDialog.ICON_HELP,
                modal: true,
                constraintoviewport: true,
                buttons: [ {text:"Delete", handler:deleteHandler, isDefault:true},
                            {text:"Cancel",  handler: function() {this.hide()}} ]
            });
            dlg.render(document.body);
            dlg.show();
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

