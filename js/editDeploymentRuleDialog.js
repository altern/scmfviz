YAHOO.namespace('scmfviz.editDeploymentRuleDialog');

YAHOO.scmfviz.editDeploymentRuleDialog = function () {
    
    var formId = 'editDeploymentRuleFormId';
    
    var dialog = null;
    
    var saveDeploymentRule = function() {
        var data = dialog.getData();
        if(!data.platformId[0]) {
            var handler = function() {
                document.getElementById(formId).platformId.focus();
            }
            YAHOO.scmfviz.alert('You should select the platform to create deployment rule', handler);
            return false;
        }
        var url = 'savedeploymentrule.php';
        var callback = {
            argument: {}, timeout: 8000,
            success: function(oResponse) {
                var response = JSON.parse(oResponse.responseText);
                if(response.data == 'OK') {
                    YAHOO.scmfviz.scmdeployments.refresh();
                    YAHOO.scmfviz.scmtree.refresh();
                    dialog.hide();
                } else {
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
        var postData = [
            'platformId=' + data.platformId[0],
//            'projectURL=' + data.projectURL,
            'pattern=' + data.pattern[0],
        ].join('&');
        YAHOO.scmfviz.loading.show();
//        var formObject = document.getElementById(formId);
//        YAHOO.util.Connect.setForm(formObject);
        YAHOO.util.Connect.asyncRequest('POST', url, callback, postData);
    }
    
    var init = function() {
         dialog = new YAHOO.widget.Dialog('editDeploymentRuleDialogDiv', {
            visible:false,
            constraintoviewport:true,
            modal:true,
            hideaftersubmit:false,
            fixedcenter : true,
            close: false,
            buttons:[
            {
                text:'Save',
                handler:saveDeploymentRule,
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
        var enterListener = new YAHOO.util.KeyListener(document, {keys: [27]}, {
            fn:dialog.hide,
            scope:dialog,
            correctScope:true
        });
        enterListener.enable();
        dialog.render(document.body);
    }
    
    return {
        show: function(data) {
            if(!dialog) {
                init();
            }
            if(!data) {
                dialog.setHeader('Create deployment rule');
            } else {
                dialog.setHeader('Edit deployment rule');
            }
            var url = 'getscmplatforms.php?url=' + YAHOO.scmfviz.project.getURL();
            var callback = {
                argument: {}, timeout: 8000,
                success: function(oResponse) {
                    var response = JSON.parse(oResponse.responseText);
                    var data = response.ResultSet.Result;
                    if(data) {
                        var selectbox = dialog.form.platformId;
                        var i;
                        for( i = selectbox.options.length - 1; i >= 0; i--) {
                            selectbox.remove(i);
                        }
                        var addOption = function (combo, text, value) {
                            var newOption = document.createElement('option');
                            newOption.text = text;
                            if (value) {
                                newOption.value = value;
                            }
                            combo.add(newOption, null);
                        };
                        addOption(selectbox, '', '');
                        for(var i = 0; i < data.length; i++) {
                            addOption(selectbox, data[i].url, data[i].id);
                        }
                    } else {
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
            YAHOO.scmfviz.loading.show();
            YAHOO.util.Connect.asyncRequest('GET', url, callback/*, postData*/);
            dialog.show();
        },
        close: function() {
            dialog.hide();
        },
        init: init
    }
} ();