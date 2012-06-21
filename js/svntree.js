YAHOO.namespace("scmfviz.tree");

YAHOO.scmfviz.svntree = function() {
    var tree = null;

    var id_prefix = 'svn_';

    var getTree = function() {
        return tree;
    }

    // Function to initialize the tree:
    var treeInit = function(latestVersion) {
        // Instantiate the tree:
        tree = new YAHOO.widget.TreeView("svntree");

        // Expand and collapse happen prior to the actual expand/collapse,
        // and can be used to cancel the operation
        tree.subscribe("expand", function(node) {
            YAHOO.log(node.index + " was expanded", "info", "example");
        
        });

        tree.subscribe("collapse", function(node) {
            YAHOO.log(node.index + " was collapsed", "info", "example");
        });

        // Trees with TextNodes will fire an event for when the label is clicked:
        tree.subscribe("clickEvent", function(node) {
            YAHOO.util.Event.preventDefault(node.event);
            return false; // return false to prevent node expanding on click
        });
        generateTree(latestVersion);
    }

    var buildTree = function (branch, root) {
        for(var k = 0; k < branch.length; k++) {
            var tempNode = null;
            var data = {'type': 'html'};
            if(!branch[k].name) {
                throw "SCM tree node does not have a label";
            } 
            data.html = '<span id="' + id_prefix + branch[k].name + '">' + branch[k].name + '</span>';
            tempNode = new YAHOO.widget.HTMLNode(data, root, false);
            if (YAHOO.lang.isArray(branch[k].children) && branch[k].children.length > 0) {
                buildTree(branch[k].children, tempNode);
            }
        }
    }

    var generateTree = function(latestVersion) {
        var url = 'checkrepostructure.php';
        
        var callback = {
            success: function(oResponse) {
                YAHOO.log("XHR transaction was successful.", "info", "example");
                var response = JSON.parse(oResponse.responseText);
                if(response.data == 'initialized') {
                    YAHOO.scmfviz.project.setRepoInitialized(true);
                } else if(YAHOO.lang.isString(response.error_message)) {
                    if(response.error_message.indexOf('init repository structure') >= 0) {
                        YAHOO.scmfviz.project.setRepoInitialized(false);
                        initFooter();
                        var linkId = 'initRepoStructureLink';
                        var elem = document.getElementById(linkId);
                        elem.style.backgroundColor = '#ff0';
                        var handler = function() {
                            fadeOut(linkId);
                        }
                        YAHOO.scmfviz.alert(response.error_message, handler);
                    } else {
                        YAHOO.scmfviz.alert(response.error_message);
                    }
                }
                initFooter();
                YAHOO.scmfviz.loading.hide();
            },
            scope: this,
            failure: function() {
                YAHOO.scmfviz.loading.hide();
            }, argument: {}, timeout: 0
        };
        YAHOO.scmfviz.loading.show();
        YAHOO.util.Connect.asyncRequest('GET', url, callback);
        
        var url = 'getsvntree.php';

        var callback = {
            //if our XHR call is successful, we want to make use
            //of the returned data and create child nodes.
            success: function(oResponse) {
                YAHOO.log("XHR transaction was successful.", "info", "example");
                //YAHOO.log(oResponse.responseText);
                var rawData = JSON.parse(oResponse.responseText);
                if(rawData.error_message) {
                    YAHOO.scmfviz.alert(rawData.error_message);
                    return false;
                }
                if (YAHOO.lang.isArray(rawData)) {
                    buildTree(rawData, tree.getRoot());
                    tree.expandAll();
                    tree.render();
                    if(YAHOO.lang.isValue(latestVersion)) {
                        var id = id_prefix + latestVersion;
                        var versionElement = document.getElementById(id);
                        if(versionElement) {
                            versionElement.scrollIntoView();
                            versionElement.style.backgroundColor = '#ff0';
                            fadeOut(id);                    
                        }
                    }
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

        //With our callback object ready, it's now time to
        //make our XHR call using Connection Manager's
        //asyncRequest method:
        YAHOO.scmfviz.loading.show();
        document.getElementById('svntree').innerHTML = '<img src="img/long_loading.gif" class="treeloading"/>';
        YAHOO.util.Connect.asyncRequest('GET', url, callback);
    }
    
    var initFooter = function () {
        var project_url = YAHOO.scmfviz.project.getURL();
        var project_name = YAHOO.scmfviz.project.getName();
        var isRepoInitialized = YAHOO.scmfviz.project.isRepoInitialized();
        
        var svnTreeActions = [];
        if(project_url) {
            svnTreeActions[svnTreeActions.length] = {
                id: 'selectAnotherLink', 
                name: 'select another', 
                action: 'YAHOO.scmfviz.selectProjectDialog.show()'
            },
            svnTreeActions[svnTreeActions.length] = {
                id: 'editProjectLink', 
                name: 'edit', 
                action: 'YAHOO.scmfviz.editProjectDialog.show()'
            }
        } else {
            svnTreeActions[svnTreeActions.length] = {
                id: 'selectAnotherLink', 
                name: 'select project', 
                action: 'YAHOO.scmfviz.selectProjectDialog.show()'
            }
        }
        if(project_url && !isRepoInitialized) {
            svnTreeActions[svnTreeActions.length] = {
                id: 'initRepoStructureLink',
                name: 'init repository structure', 
                action: 'YAHOO.scmfviz.action.initRepo()'
            }; 
        }
        var actionsHTML = '';
        if(project_url) {
            actionsHTML += 'Selected project: ' 
                        + '<a target="_new" href="' + project_url + '">'
                        + (project_name ? project_name : project_url)
                        + '</a>'
                        + (YAHOO.scmfviz.project.isReadOnly() ? ' (readonly)' : '')
        }
        actionsHTML += '<div id="svnTreeActions">';
        for(var elem in svnTreeActions) {
            actionsHTML += '<span id="'
            + svnTreeActions[elem].id + '"><a href="#" onclick="' 
            + svnTreeActions[elem].action + '">'
            + svnTreeActions[elem].name + '</a></span> ';
        }
        actionsHTML += '</div>';
        document.getElementById('rightFooter').innerHTML = actionsHTML;
    }

    return {
        treeInit: treeInit,
        getTree: getTree,
        refresh: function(latestVersion) {
            if(tree) {
                tree.removeChildren(tree.getRoot(), true);
                generateTree(latestVersion);
            } else {
                treeInit(latestVersion);
            }
        }, 
        initFooter: initFooter
    }
} ();