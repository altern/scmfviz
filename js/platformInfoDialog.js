YAHOO.namespace('scmfviz.platformInfoDialog');

YAHOO.scmfviz.platformInfoDialog = function() {
    var dialog = null;
    
    var init = function () {
        dialog = new YAHOO.widget.Dialog('platformInfoDialogDiv', {
            visible:false,
            constraintoviewport:true,
            modal:true,
            hideaftersubmit:false,
            fixedcenter : true,
            close: false,
//            width: '300px',
            buttons:[
//            {
//                text:'OK',
//                handler:saveDeploymentRule,
//                isDefault:true
////                type: 'submit'
//            },
            {
                text:'Cancel',
                handler: function() {
                    this.cancel();
                }
            }]
        });
        dialog.render(document.body);
        
    }
    
    return {
        show: function(url) {
            var requestUrl = 'getplatforminfo.php?url=' + url;
            var callback = {
                argument: {}, timeout: 0,
                success: function(oResponse) {
                    var response = JSON.parse(oResponse.responseText);
                    if(response.data) {
                        document.getElementById('platformURL').innerHTML = '<b>' + url + '</b>';
                        document.getElementById('platformPattern').innerHTML = '<b>' + response.data.pattern + '</b>';
                        var versionsHTML = '<div class="platformVersions">';
                        var classValue = "";
                        for(var i in response.data.versions) {
                            if(YAHOO.lang.isArray(response.data.versions[i].children) 
                                && response.data.versions[i].children.length > 0) {
                                versionsHTML += '<div class="versionCategory"><b>' + response.data.versions[i].label + '</b><ul>'; 
                                for(var j in response.data.versions[i].children) {
                                    classValue = "";
                                    if(response.data.versions[i].children[j].latest) {
                                        classValue += "latestVersion "; 
                                    }
                                    if(response.data.versions[i].children[j].link) {
                                        classValue += "hasLink "; 
                                        versionsHTML += '<li class="' + classValue + '" id="platform'
                                            + response.data.versions[i].children[j].label + '"><a href="' 
                                            + response.data.versions[i].children[j].link
                                            + '">' +  response.data.versions[i].children[j].label + '</a></li>';
                                    } else {
                                        versionsHTML += '<li class="' + classValue + '">' +  response.data.versions[i].children[j].label + '</li>';
                                    }
                                }
                                versionsHTML += '</ul></div>'; 
                            } else {
                                classValue = "";
                                if(response.data.versions[i].latest) {
                                    classValue += "latestVersion "; 
                                }
                                if(response.data.versions[i].link) {
                                    classValue += "hasLink "; 
                                    versionsHTML += '<li class="' + classValue + '" id="platform'
                                        + response.data.versions[i].label + '"><a href="' 
                                        + response.data.versions[i].link + '">' 
                                        +  response.data.versions[i].label + '</a></li>';
                                } else {
                                    versionsHTML += '<li class="' + classValue + '">' +  response.data.versions[i].label + '</li>';
                                }
                            }
                        }
                        versionsHTML += '</div>';
                        document.getElementById('platformVersions').innerHTML = versionsHTML;
                        dialog.show();
                        var params = getUriParams();
                        if(params.version) {
                            var id = 'platform' + params.version;
                            var versionElement = document.getElementById(id);
                            var animation1 = new YAHOO.util.ColorAnim(
                                versionElement, 
                                {backgroundColor: {from:'#ddd', to:"#ff0"}}, 0.5
                            );
                            var animation2 = new YAHOO.util.ColorAnim(
                                versionElement, 
                                {backgroundColor: {from:'#ff0', to: '#ddd'}}, 0.5
                            );
                            animation1.onComplete.subscribe(function() {
                                animation2.animate();
                            })
                            animation2.onComplete.subscribe(function() {
                                animation1.animate();
                            })
                            YAHOO.util.Event.onContentReady('platformInfoDialogDiv', function() {
                                animation1.animate();
                            });
                        }
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
            YAHOO.util.Connect.asyncRequest('GET', requestUrl, callback);
        },
        init: init,
    }
}();