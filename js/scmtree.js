YAHOO.namespace("scmfviz.tree");

YAHOO.scmfviz.scmtree = function() {
    var tree = null;
    
    var id_prefix = 'scm_';
    
    var getTree = function() {
        return tree;
    }

    // Function to initialize the tree:
    var treeInit = function(latestVersion) {
        // Instantiate the tree:
        tree = new YAHOO.widget.TreeView("scmtree");
        // Expand and collapse happen prior to the actual expand/collapse,
        // and can be used to cancel the operation
        tree.subscribe("expand", function(node) {
            YAHOO.log(node.index + " was expanded", "info", "example");
        // return false; // return false to cancel the expand
        });

        tree.subscribe("collapse", function(node) {
            YAHOO.log(node.index + " was collapsed", "info", "example");
        });

        // Trees with TextNodes will fire an event for when the label is clicked:
        tree.subscribe("clickEvent", function(node) {
            YAHOO.util.Event.preventDefault(node.event);
            return false;
        });
        
        tree.subscribe("enterKeyPressed", function(node) {
            YAHOO.util.Event.preventDefault(node.event);
            console.log(node);
            return false;
        });
        
        generateTree(latestVersion);
    }

    var buildTree = function (branch, root) {
        for(var k = 0; k < branch.length; k++) {
            var data = {'type': 'html'};
            var tempNode = null, label = '';
            if(!branch[k].label) {
                throw "SCM tree node does not have a label";
            } 
            label = '<span id="' + id_prefix + branch[k].label + '">' + branch[k].label + '</span>';
            if(branch[k].link) {
                data.html = '<a href="' + branch[k].link + '" onclick="window.open(\'' + branch[k].link + '\')">' + label + '</a>';
            } else {
                data.html = label;
            }
            if('isTrunk' in branch[k]) {
                data.html += ' (trunk)';
            }
            if(YAHOO.lang.isArray(branch[k].actions)) {
                var actions = branch[k].actions;
                for(var i = 0; i < actions.length; i++) {
                    if(actions[i].callback && (typeof YAHOO.scmfviz.action[actions[i].callback] === 'function')) {
                        data.html += ' <a href="#" class="scmf_action" onkeypressed="return false;" onclick="YAHOO.scmfviz.action.' + actions[i].callback + '(\'' + branch[k].label + '\')">' + actions[i].action + '</a>';
                    } else if (actions[i].message) {
                        data.html += ' <a href="#" class="scmf_action" onclick="YAHOO.scmfviz.alert(\'' + actions[i].message + '\')">' + actions[i].action + '</a>';
                    } else {
                        data.html += ' <a href="#" class="scmf_action">' + actions[i].action + '</a>';
                    }
                }
            }
            tempNode = new YAHOO.widget.HTMLNode(data, root, false);
            if (YAHOO.lang.isArray(branch[k].children)) {
                buildTree(branch[k].children, tempNode);
            }
        }
    }

    var generateTree = function(latestVersion) {
        var url = 'getscmtree.php?readonly=' 
            + YAHOO.scmfviz.project.isReadOnly();
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
                if (YAHOO.lang.isValue(rawData.tree)) {
                    buildTree(rawData.tree, tree.getRoot());
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

            //if our XHR call is not successful, we want to
            //fire the TreeView callback and let the Tree
            //proceed with its business.
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
        document.getElementById('scmtree').innerHTML = '<img src="img/long_loading.gif" class="treeloading"/>';
        YAHOO.util.Connect.asyncRequest('GET', url, callback);
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
        }
    }
} ();
