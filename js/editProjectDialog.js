YAHOO.namespace("scmfviz.editProjectDialog");
YAHOO.scmfviz.editProjectDialog = function() {
    
    var dialog = null;
    
    var formId = 'editProjectFormId';
    
    var saveProject = function() {
        var data = dialog.getData();
        if(!data.projectName) {
            var handler = function() {
                document.getElementById(formId).projectName.focus();
            }
            YAHOO.scmfviz.alert('Project name cannot be empty', handler);
            return false;
        }
        var url = 'saveproject.php'
        var callback = {
            argument: {}, timeout: 8000,
            success: function(oResponse) {
                var response = JSON.parse(oResponse.responseText);
                if(response.data == 'OK') {
                    YAHOO.scmfviz.project.setURL(data.repositoryURL);
                    YAHOO.scmfviz.project.setName(data.projectName);
                    YAHOO.scmfviz.project.setPublic(data.isPublic);
                    if(YAHOO.scmfviz.project.getStartingVersion() != data.startingVersion[0]) {
                        YAHOO.scmfviz.project.setStartingVersion(data.startingVersion[0]);
                        YAHOO.scmfviz.scmtree.refresh();
                    }
                    YAHOO.scmfviz.svntree.initFooter();
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
//        var formObject = document.getElementById(formId);
//        YAHOO.util.Connect.setForm(formObject);
        if(YAHOO.scmfviz.project.hasStateChanged(data.repositoryURL, data.projectName, data.startingVersion[0], data.isPublic)) {
            var postData = [
                'repositoryURL=' + data.repositoryURL,
                'projectName=' + data.projectName,
                'startingVersion=' + data.startingVersion[0],
                'isPublic=' + (data.isPublic ? 1 : 0)
            ].join('&');
            YAHOO.scmfviz.loading.show();
            YAHOO.util.Connect.asyncRequest('POST', url, callback, postData);
        } else {
            dialog.hide();
        }
        return true;
    }
    
    var init = function() {
        dialog = new YAHOO.widget.Dialog('editProjectDialogDiv', {
            visible:false,
            constraintoviewport:true,
            modal:true,
            hideaftersubmit:false,
            fixedcenter : true,
            close: false,
            buttons:[
            {
                text:'Save',
                handler:saveProject,
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
    }
    
    return {
        init: init,
        show: function() {
            var url = YAHOO.scmfviz.project.getURL();
            var name = YAHOO.scmfviz.project.getName();
            var starting_version = YAHOO.scmfviz.project.getStartingVersion();
            var is_public = YAHOO.scmfviz.project.isPublic();
            if(url) dialog.form.repositoryURL.value = url;
            if(url) document.getElementById('repositoryURLValue').innerHTML = url;
            if(name) dialog.form.projectName.value = name;
            if(starting_version) dialog.form.startingVersion.value = starting_version;
            if(is_public) {
                dialog.form.isPublic.checked = true; 
            } else {
                dialog.form.isPublic.checked = false; 
            }
            
            dialog.show();
        },
        close: function() {
            dialog.hide();
        }
    }
}();