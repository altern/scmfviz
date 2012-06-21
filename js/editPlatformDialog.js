YAHOO.namespace('scmfviz.editPlatformDialog');

YAHOO.scmfviz.editPlatformDialog = function () {
    
    var formId = 'editPlatformFormId';
    
    var dialog = null;
    
    var savePlatform = function() {
        var data = dialog.getData();
        if(!data.platformURL) {
            var handler = function() {
                document.getElementById(formId).platformURL.focus();
            }
            YAHOO.scmfviz.alert('Platform URL cannot be empty', handler);
            return false;
        }
        var url = 'saveplatform.php';
        var callback = {
            argument: {}, timeout: 8000,
            success: function(oResponse) {
                var response = JSON.parse(oResponse.responseText);
                if(response.data == 'OK') {
                    YAHOO.scmfviz.scmplatforms.refresh();
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
            'id=' + data.platformId,
            'platformURL=' + data.platformURL,
            'projectURL=' + data.projectURL,
            'remoteDirectory=' + data.remoteDirectory,
            'deploymentsLimit=' + data.deploymentsLimit,
            'deploymentMethod=' + data.deploymentMethod,
        ].join('&');
// fields: ["url", "project", "deployments_limit", "deployment_method"]
            // platformURL, remoteDirectory, deploymentMethod, deploymentsLimit
        YAHOO.scmfviz.loading.show();
//        var formObject = document.getElementById(formId);
//        YAHOO.util.Connect.setForm(formObject);
        YAHOO.util.Connect.asyncRequest('POST', url, callback, postData);
    }
    
    var init = function() {
         dialog = new YAHOO.widget.Dialog('editPlatformDialogDiv', {
            visible:false,
            constraintoviewport:true,
            modal:true,
            hideaftersubmit:false,
            fixedcenter : true,
            close: false,
            buttons:[
            {
                text:'Save',
                handler:savePlatform,
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
//        var enterListener = new YAHOO.util.KeyListener(document, {keys: [13]}, {
//            fn:dialog.hide,
//            scope:dialog,
//            correctScope:true
//        });
//        enterListener.enable();
        dialog.render(document.body);
    }
    
    return {
        show: function(data) {
            if(!dialog) {
                init();
            }
            if(!data) {
                dialog.setHeader('Create platform');
            } else {
                dialog.setHeader('Edit platform');
            }
            // fields: ["url", "project", "deployments_limit", "deployment_method"]
            // platformURL, remoteDirectory, deploymentMethod, deploymentsLimit
            dialog.form.platformId.value = (data && data.id ? data.id : ''); 
            dialog.form.projectURL.value = YAHOO.scmfviz.project.getURL(); 
            dialog.form.platformURL.value = (data && data.url ? data.url : '');
            dialog.form.remoteDirectory.value = (data && data.remote_directory ? data.remote_directory : '');
            dialog.form.deploymentsLimit.value = (data && data.deployments_limit ? data.deployments_limit : '');
            dialog.form.deploymentMethod.value = (data && data.deployment_method ? data.deployment_method : '');
            dialog.show();
        },
        close: function() {
            dialog.hide();
        },
        init: init
    }
} ();