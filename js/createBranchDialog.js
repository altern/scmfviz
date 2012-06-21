YAHOO.namespace("scmfviz.createBranchDialog");
YAHOO.scmfviz.createBranchDialog = function() {
    
    var dialog = null;
    
    var createBranch = function() {
        var data = this.getData();
        var action = '';
        var params = {};
        if(!YAHOO.lang.isValue(data.branchType)) return false;
        switch(data.branchType) {
            case 'release': action = 'createReleaseBranch'; 
                params.supportBranch = data.parentBranch;
                break;
            case 'support': 
                action = 'createSupportBranch'; 
                break;
            case 'experimental': 
                action = 'createExperimentalBranch'; 
                params.supportBranch = data.parentBranch;
                if(data.experimentalName == '') {
                    YAHOO.scmfviz.alert('Branch name cannot be empty');
                    return false;
                }
                params.experimentalName = data.experimentalName; 
                break;
        }
        if(!YAHOO.lang.isValue(data.parentBranch)) return false;
        var url = 'performaction.php?action=' + action
            + '&repositoryURL=' + YAHOO.scmfviz.project.getURL();
        for(var key in params) {
            url += '&params[' + key + ']=' + params[key];
        }
        var callback = {
            success: function(oResponse) {
                YAHOO.log("XHR transaction was successful.", "info", "example");
                //YAHOO.log(oResponse.responseText);
                var response = JSON.parse(oResponse.responseText);

                if(YAHOO.lang.isString(response.data)) {
                    var latestVersion = response.data;
                    YAHOO.scmfviz.scmtree.refresh(latestVersion);
                    YAHOO.scmfviz.svntree.refresh(latestVersion);
                    YAHOO.scmfviz.scmactions.refresh();
                    dialog.hide();
                    YAHOO.scmfviz.loading.hide();
                } else {
                    if(YAHOO.lang.isString(response.error_message)) {
                        YAHOO.scmfviz.alert(response.error_message);
                    }
                }
            },
            failure: function(oResponse) {
                YAHOO.log("Failed to process XHR transaction.", "info", "example");
                YAHOO.scmfviz.loading.hide();
            },
            argument: {
            },
            timeout: 0
        };
        YAHOO.scmfviz.loading.show();
        YAHOO.util.Connect.asyncRequest('GET', url, callback);
        return false;
    }
    var init = function() {
        dialog = new YAHOO.widget.Dialog('createBranchDialogDiv', {
            visible:false,
            constraintoviewport:true,
            modal:true,
            hideaftersubmit:false,
            fixedcenter : true,
            close: false,
            buttons:[
            {
                text:'Create',
                handler:createBranch,
                isDefault:true
            },
            {
                text:'Cancel',
                handler: function() {
                    this.cancel();
                }
            }]
            }
        );
        
        dialog.render(document.body);
        var disableInput = function () {
            document.getElementsByName('experimentalName')[0].disabled = true;
        }
        var enableInput = function() {
            document.getElementsByName('experimentalName')[0].disabled = false;
        }
        var branchTypeOptions = document.getElementsByName('branchType');
        for (var i = 0; i < branchTypeOptions.length; i++) {
            if(branchTypeOptions[i].value != 'experimental') {
               branchTypeOptions[i].onclick = disableInput; 
            } else {
               branchTypeOptions[i].onclick = enableInput; 
                
            }
        }
//        var enterListener = new YAHOO.util.KeyListener(document, {keys: [13]}, {
//            fn:createBranch,
//            scope:dialog,
//            correctScope:true
//        });
//        enterListener.enable();
        var enterListener = new YAHOO.util.KeyListener(document, {keys: [27]}, {
            fn:dialog.hide,
            scope:dialog,
            correctScope:true
        });
        enterListener.enable();
    }

    return {
        show: function() {
            dialog.show();
        },
        init: init,
        close: function() {
            dialog.hide();
        }
    }
}();