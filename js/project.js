YAHOO.namespace('scmfviz.project');

YAHOO.scmfviz.project = function() {
    
    var repositoryURL = '';
    
    var projectName = '';
    
    var starting_version = '0.x.x';
    
    var is_public = false;
    
    var is_repo_initialized = false;
    
    var read_only_access = true;
    
    var formId = 'selectProjectFormId';
    
    var addUrlToCookies = function(url) {
        var array_search = function(needle, haystack) {
            for (var key in haystack) {
                if (haystack[key] == needle) {
                    return key;
                }
            }
            return false;
        } 
        var repoURLs = YAHOO.lang.JSON.parse(YAHOO.util.Cookie.get('repositoryURLAutoComplete'));    
        if(!repoURLs) {
            repoURLs = [url];
        } else if( YAHOO.lang.isArray(repoURLs) && array_search(url, repoURLs) === false) {
            repoURLs[repoURLs.length] = url;
        }
        YAHOO.util.Cookie.set(
            'repositoryURLAutoComplete', 
            YAHOO.lang.JSON.stringify(repoURLs), 
            {expires: new Date("January 12, 2025"), path: location.pathname}
        );    
    }
    
    var useRepository = function(repository_url, user, pass) {
        var url = 'getproject.php?url=' + repository_url;
        var callback = {
            argument: {}, timeout: 8000,
            success: function(oResponse) {
                addUrlToCookies(repository_url);
                var fields = YAHOO.util.Connect.setForm(document.getElementById(formId));
                YAHOO.util.Cookie.set(formId, fields, {expires: new Date("January 12, 2025"), path:location.pathname});
                YAHOO.util.Cookie.set('repositoryURL', repository_url, {expires: new Date("January 12, 2025"), path:location.pathname});
                if(user) YAHOO.util.Cookie.set('repositoryUser', user, {expires: new Date("January 12, 2025"), path:location.pathname});
                if(pass) YAHOO.util.Cookie.set('repositoryPass', pass, {expires: new Date("January 12, 2025"), path:location.pathname});
                YAHOO.scmfviz.project.setURL(repository_url);
                YAHOO.scmfviz.project.setName(''); 
                YAHOO.scmfviz.project.setStartingVersion('0.x.x'); 
                YAHOO.scmfviz.project.setPublic(false); 
                if(oResponse.responseText) {
                    var response = JSON.parse(oResponse.responseText);
                    if(response.data.name) {
                        YAHOO.scmfviz.project.setName(response.data.name);
                    }
                    if(response.data.starting_version) {
                        YAHOO.scmfviz.project.setStartingVersion(response.data.starting_version);
                    }
                    if(response.data.is_public != undefined) {
                        YAHOO.scmfviz.project.setPublic(response.data.is_public);
                    }
                }
                var params = getUriParams();
                if(params.version) {
                    YAHOO.scmfviz.scmtree.refresh(params.version);
                    YAHOO.scmfviz.svntree.refresh(params.version);
                } else {
                    YAHOO.scmfviz.scmtree.refresh();
                    YAHOO.scmfviz.svntree.refresh();
                }
                YAHOO.scmfviz.svntree.initFooter();
                YAHOO.scmfviz.scmactions.refresh();
                YAHOO.scmfviz.scmplatforms.refresh();
                YAHOO.scmfviz.scmdeployments.refresh();
                YAHOO.scmfviz.loading.hide();
            },
            failure: function(oResponse) {
                YAHOO.log("Failed to process XHR transaction.", "info", "example");
                if(console) console.log(oResponse);
                YAHOO.scmfviz.loading.hide();
            }
        };
        YAHOO.scmfviz.loading.show();
        YAHOO.util.Connect.asyncRequest('GET', url, callback);
    }
    
    return {
        getURL: function() {
            return repositoryURL;
        },
        setURL: function( url ){
            repositoryURL = url;
        },
        getName: function( ){
            return projectName;
        },
        setName: function( name ){
            projectName = name;
        },
        getStartingVersion: function () {
            return starting_version;
        },
        setStartingVersion: function(version) {
            starting_version = version;
        },
        isPublic: function() {
            return is_public;
        },
        setPublic: function(value) {
            is_public = value;
        },
        isReadOnly: function() {
            return read_only_access;
        },
        setReadOnly: function(value) {
            read_only_access = value;
        },
        isRepoInitialized: function() {
            return is_repo_initialized;
        },
        setRepoInitialized: function(value) {
            is_repo_initialized = value;
        },
        hasStateChanged: function(url, name, version, visibility) {
            return repositoryURL != url 
                || projectName != name 
                || starting_version != version 
                || is_public != visibility;
        },
        useRepository: useRepository
    }
} ();