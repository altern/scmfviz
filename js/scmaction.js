YAHOO.namespace("scmfviz.action");

YAHOO.scmfviz.action = function () {
    var callback = {
        success: function(oResponse) {
            YAHOO.log("XHR transaction was successful.", "info", "example");
            var response = JSON.parse(oResponse.responseText);

            if(response.data) {
                var latestVersion = response.data;
                YAHOO.scmfviz.scmtree.refresh(latestVersion);
                YAHOO.scmfviz.svntree.refresh(latestVersion);
                YAHOO.scmfviz.scmactions.refresh();
            } else if(YAHOO.lang.isString(response.error_message)) {
                YAHOO.scmfviz.alert(response.error_message);
            }
            YAHOO.scmfviz.loading.hide();
        },
        failure: function(oResponse) {
            YAHOO.log("Failed to process XHR transaction.", "info", "example");
            YAHOO.scmfviz.loading.hide();
        },
        argument: {
        },
        timeout: 0
    };
    
    var buildActionURL = function(action, params) {
        YAHOO.scmfviz.loading.show();
        var url = 'performaction.php?action=' + action 
            + '&repositoryURL=' + YAHOO.scmfviz.project.getURL();
        for(var key in params) {
            url += '&params[' + key + ']=' + params[key];
        }
        return url;
    };
    
    return {
        createBranch: function(parentBranch) {
            if(!YAHOO.scmfviz.project.isRepoInitialized()) {
                YAHOO.scmfviz.alert('You cannot perform action until repository structure is initialized properly')
                return false;
            }
            document.getElementsByName('parentBranch')[0].value = parentBranch;
            YAHOO.scmfviz.createBranchDialog.show();
        },
        createReleaseBranch: function(supportBranch) {
            if(!YAHOO.scmfviz.project.isRepoInitialized()) {
                YAHOO.scmfviz.alert('You cannot perform action until repository structure is initialized properly')
                return false;
            }
            var action = 'createReleaseBranch';
            var params = {};
            params.supportBranch = supportBranch;
            YAHOO.util.Connect.asyncRequest('GET', buildActionURL(action, params), callback);
            return false;
        },
        createSupportBranch: function(parentBranch) {
            if(!YAHOO.scmfviz.project.isRepoInitialized()) {
                YAHOO.scmfviz.alert('You cannot perform action until repository structure is initialized properly')
                return false;
            }
            var action = 'createSupportBranch';
            var params = {};
            YAHOO.util.Connect.asyncRequest('GET', buildActionURL(action, params), callback);
            return false;
        },
        deliverPreAlphaBuild: function(supportBranch) {
            if(!YAHOO.scmfviz.project.isRepoInitialized()) {
                YAHOO.scmfviz.alert('You cannot perform action until repository structure is initialized properly')
                return false;
            }
            var action = 'deliverPreAlphaBuild';
            var params = {};
            params.supportBranch = supportBranch;
            YAHOO.util.Connect.asyncRequest('GET', buildActionURL(action, params), callback);
            return false;
        },
        promoteToAlpha: function(build) {
            if(!YAHOO.scmfviz.project.isRepoInitialized()) {
                YAHOO.scmfviz.alert('You cannot perform action until repository structure is initialized properly')
                return false;
            }
            var action = 'promoteToAlpha';
            var params = {};
            params.build = build;
            YAHOO.util.Connect.asyncRequest('GET', buildActionURL(action, params), callback);
            return false;
        },
        promoteToBeta: function(build) {
            if(!YAHOO.scmfviz.project.isRepoInitialized()) {
                YAHOO.scmfviz.alert('You cannot perform action until repository structure is initialized properly')
                return false;
            }
            var action = 'promoteToBeta';
            var params = {};
            params.build = build;
            YAHOO.util.Connect.asyncRequest('GET', buildActionURL(action, params), callback);
            return false;
        },
        deliverAlphaRelease: function(releaseBranch) {
            if(!YAHOO.scmfviz.project.isRepoInitialized()) {
                YAHOO.scmfviz.alert('You cannot perform action until repository structure is initialized properly')
                return false;
            }
            var action = 'deliverAlphaRelease';
            var params = {};
            params.releaseBranch = releaseBranch;
            YAHOO.util.Connect.asyncRequest('GET', buildActionURL(action, params), callback);
            return false;
        },
        promoteToBetaRelease: function(release) {
            if(!YAHOO.scmfviz.project.isRepoInitialized()) {
                YAHOO.scmfviz.alert('You cannot perform action until repository structure is initialized properly')
                return false;
            }
            var action = 'promoteToBetaRelease';
            var params = {};
            params.release = release;
            YAHOO.util.Connect.asyncRequest('GET', buildActionURL(action, params), callback);
            return false;
        },
        promoteToReleaseCandidate: function(release) {
            if(!YAHOO.scmfviz.project.isRepoInitialized()) {
                YAHOO.scmfviz.alert('You cannot perform action until repository structure is initialized properly')
                return false;
            }
            var action = 'promoteToReleaseCandidate';
            var params = {};
            params.release = release;
            YAHOO.util.Connect.asyncRequest('GET', buildActionURL(action, params), callback);
            return false;
        },
        promoteToStableRelease: function(release) {
            if(!YAHOO.scmfviz.project.isRepoInitialized()) {
                YAHOO.scmfviz.alert('You cannot perform action until repository structure is initialized properly')
                return false;
            }
            var action = 'promoteToStableRelease';
            var params = {};
            params.release = release;
            YAHOO.util.Connect.asyncRequest('GET', buildActionURL(action, params), callback);
            return false;
        },
        initRepo: function() {
            var action = 'initRepo';
            var params = {};
            YAHOO.util.Connect.asyncRequest('GET', buildActionURL(action, params), callback);
            return false;
        }
    }
}();

