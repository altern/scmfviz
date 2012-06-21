YAHOO.namespace("scmfviz.selectProjectDialog");
YAHOO.scmfviz.selectProjectDialog = function() {
    
    var dialog = null;
    
    var autoComplete = null;
    
    var formId = 'selectProjectFormId';
    
    var selectProject = function() {
        var data = this.getData();
        if(!data.repositoryURL) {
            var handler = function() {
                document.getElementById(formId).repositoryURL.focus()
            }
            YAHOO.scmfviz.alert('Please select repository to open', handler);
            return false;
        }
        if(document.getElementsByName('repositoryReadonly')[0].checked) {
            YAHOO.scmfviz.project.useRepository(data.repositoryURL);
            dialog.hide();
        } else {
            var url = 'checkrepoaccess.php'

    //        var post_data_arr = [];
    //        for(var field in fields_arr) {
    //            post_data_arr[post_data_arr.length] = fields_arr[field] + '=' + data[fields_arr[field]];
    //        }
    //        var postData = post_data_arr.join('&');
            var callback = {
                argument: {}, timeout: 0,
                success: function(oResponse) {
                    var response = JSON.parse(oResponse.responseText);
                    switch(response.data) {
                        case 'FALSE':
                            var readonlyHandler = function() {
                                YAHOO.scmfviz.project.setReadOnly(true);
                                YAHOO.scmfviz.project.useRepository(data.repositoryURL, data.repositoryUser, data.repositoryPass);
                                this.hide();
                                dialog.hide();
                            }
                            var dlg = new YAHOO.widget.SimpleDialog("credentialsConfirm", {
                                width: "400px",
                                fixedcenter: true,
                                visible: false,
                                draggable: true,
                                close: false,
    //                            text: "Wrong credentials supplied. Do you want to use repository as readonly user or try to enter correct credentials one more time?",
                                text: "Wrong credentials supplied",
                                icon: YAHOO.widget.SimpleDialog.ICON_WARN,
                                modal: true,
                                constraintoviewport: true,
                                buttons: [ /*{text:"Readonly", handler:readonlyHandler, isDefault:true},*/
                                            {text:"Try again",  handler: function() {this.hide()}} ]
                            });
                            dlg.render(document.body);
                            dlg.show();
                            break;
                        case 'TRUE':
                            YAHOO.scmfviz.project.setReadOnly(false);
                            YAHOO.scmfviz.project.useRepository(data.repositoryURL, data.repositoryUser, data.repositoryPass);
                            dialog.hide();
                            break;
                        default:
                            YAHOO.scmfviz.alert(response.error_message);
                    }
                    YAHOO.scmfviz.loading.hide();
                },
                failure: function(oResponse) {
                    YAHOO.log("Failed to process XHR transaction.", "info", "example");
                    if(console) console.log(oResponse);
                    YAHOO.scmfviz.loading.hide();
                }
            }
            var formObject = document.getElementById(formId);
            YAHOO.util.Connect.setForm(formObject);
            YAHOO.scmfviz.loading.show();
            YAHOO.util.Connect.asyncRequest('POST', url, callback/*, postData*/);
        }
    }
    var init = function() {
        dialog = new YAHOO.widget.Dialog('selectProjectDialogDiv', {
//            width: '180px',
            visible:false,
            constraintoviewport:true,
            modal:true,
            hideaftersubmit:false,
            fixedcenter : true,
            close: false,
//            hideaftersubmit: true,
            buttons:[
            {
                text:'OK',
                handler:selectProject,
                isDefault:true
//                type: 'submit'
            },
            {
                text:'Cancel',
                handler: function() {
                    this.cancel();
                }
            }]
            }
        );
//        var form = dialog.getForm();
        var repositoryReadonly = document.getElementsByName('repositoryReadonly')[0];
        repositoryReadonly.onclick = function() {
            document.getElementsByName('repositoryUser')[0].disabled = repositoryReadonly.checked;
            document.getElementsByName('repositoryPass')[0].disabled = repositoryReadonly.checked;
            YAHOO.scmfviz.project.setReadOnly(repositoryReadonly.checked);
        }
//        var oDS = new YAHOO.util.XHRDataSource("assets/php/ysearch_flat.php");
//        // Set the responseType
//        oDS.responseType = YAHOO.util.XHRDataSource.TYPE_TEXT;
//        // Define the schema of the delimited results
//        oDS.responseSchema = {
//            recordDelim: "\n",
//            fieldDelim: "\t"
//        };
//        // Enable caching
//        oDS.maxCacheEntries = 5;
//        var oDS = new YAHOO.util.FunctionDataSource(function(sQuery) {
//            var matches = [];
//            var values = YAHOO.lang.JSON.parse(YAHOO.util.Cookie.get('repositoryURLAutoComplete'));
//            if(values) {
//                for(var i = 0; i < values.length; i++) {
//                    if(values[i].indexOf(unescape(sQuery)) >= 0) {
//                        matches[matches.length] = values[i];
//                    }
//                }
//            }
//            return matches;
//        }); 
//        oDS.responseSchema = {fields : ["repository_url", "name"]};
        var oDS = new YAHOO.util.XHRDataSource("url_autocomplete.php");
        // Set the responseType
        oDS.responseType = YAHOO.util.XHRDataSource.TYPE_TEXT;
        oDS.responseSchema = {
//            fields : ["repository_url"], 
            recordDelim: "\n",
            fieldDelim: "\t"
        };
        oDS.maxCacheEntries = 5; 
        
        autoComplete = new YAHOO.widget.AutoComplete("repositoryURLId","repositoryURLAutoComplete", oDS, {
            'useShadow': false,
            'autoSnapContainer': false
        });
        var pos = YAHOO.util.Dom.getXY('repositoryURLId');
        var region = YAHOO.util.Dom.getRegion('repositoryURLId');
        YAHOO.util.Dom.setXY('repositoryURLAutoComplete', [pos[0] + region.width, pos[1]]);
        autoComplete.prehighlightClassName = "yui-ac-prehighlight"; 
	    autoComplete.useShadow = true;
     
        var fields = YAHOO.util.Cookie.get(formId);
        if(fields != null) {
            fields = fields.split("&")
            for(var i=0;i<fields.length;i++) {
                var name = fields[i].split("=")[0];
                var value = decodeURIComponent(fields[i].split("=")[1]);
                YAHOO.util.Selector.query("input[name='" + name + "']")[0].value = value;
            }
        }
//        var enterListener = new YAHOO.util.KeyListener(document, {keys: [13]}, {
//            fn:selectProject,
//            scope:dialog,
//            correctScope:true
//        });
//        enterListener.enable();
//        var enterListener = new YAHOO.util.KeyListener(document, {keys: [27]}, {
//            fn:dialog.hide,
//            scope:dialog,
//            correctScope:true
//        });
//        enterListener.enable();
//        YAHOO.util.Event.addListener("selectRepositoryFormId", "submit", fnCallback);

        dialog.render(document.body);
    }

    return {
        show: function() {
            dialog.show();
        },
        init: init,
        close: function() {
            dialog.hide();
        },
        getData: function() {
            return this.getData();
        }
    }
}();