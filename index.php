<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" 
    "http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <link rel="shortcut icon" href="favicon.ico" />
        <!-- Combo-handled YUI CSS files: -->
        <link rel="stylesheet" type="text/css"href="js/yui2/build/reset-fonts-grids/reset-fonts-grids.css">
        <link rel="stylesheet" type="text/css" href="js/yui2/build/assets/skins/sam/resize.css">
        <link rel="stylesheet" type="text/css" href="js/yui2/build/assets/skins/sam/layout.css">
        <link rel="stylesheet" type="text/css" href="js/yui2/build/assets/skins/sam/button.css">
        <link rel="stylesheet" type="text/css" href="js/yui2/build/logger/assets/skins/sam/logger.css">
        <link rel="stylesheet" type="text/css" href="js/yui2/build/assets/skins/sam/container.css">
        <link rel="stylesheet" type="text/css" href="js/yui2/build/treeview/assets/skins/sam/treeview.css">
        <link rel="stylesheet" type="text/css" href="js/yui2/build/autocomplete/assets/skins/sam/autocomplete.css">
        <link rel="stylesheet" type="text/css" href="js/yui2/build/datatable/assets/skins/sam/datatable.css">
        <link rel="stylesheet" type="text/css" href="js/yui2/build/tabview/assets/skins/sam/tabview.css">
        <link rel="stylesheet" type="text/css" href="css/style.css">
        <!-- Combo-handled YUI JS files: -->
        <script type="text/javascript" src="js/yui2/build/yahoo/yahoo-min.js"></script>
        <script type="text/javascript" src="js/yui2/build/yahoo-dom-event/yahoo-dom-event.js"></script>
        <script type="text/javascript" src="js/yui2/build/cookie/cookie-min.js"></script>
        <script type="text/javascript" src="js/yui2/build/selector/selector-min.js"></script>
        <script type="text/javascript" src="js/yui2/build/event/event-min.js"></script>
        <script type="text/javascript" src="js/yui2/build/dom/dom-min.js"></script>
        <script type="text/javascript" src="js/yui2/build/element/element-min.js"></script>
        <script type="text/javascript" src="js/yui2/build/dragdrop/dragdrop-min.js"></script>
        <script type="text/javascript" src="js/yui2/build/resize/resize-min.js"></script>
        <script type="text/javascript" src="js/yui2/build/animation/animation-min.js"></script>
        <script type="text/javascript" src="js/yui2/build/layout/layout-min.js"></script>
        <script type="text/javascript" src="js/yui2/build/treeview/treeview.js"></script>
        <script type="text/javascript" src="js/yui2/build/logger/logger-min.js"></script>
        <script type="text/javascript" src="js/yui2/build/button/button-min.js"></script>
        <script type="text/javascript" src="js/yui2/build/container/container-min.js"></script>
        <script type="text/javascript" src="js/yui2/build/connection/connection_core-min.js"></script>
        <script type="text/javascript" src="js/yui2/build/connection/connection.js"></script>
        <script type="text/javascript" src="js/yui2/build/json/json-min.js"></script>
        <script type="text/javascript" src="js/yui2/build/datasource/datasource-min.js"></script>
        <script type="text/javascript" src="js/yui2/build/autocomplete/autocomplete-min.js"></script>
        <script type="text/javascript" src="js/yui2/build/datatable/datatable-min.js"></script>
        <script type="text/javascript" src="js/yui2/build/tabview/tabview-min.js"></script>
        <!-- SCMFVIZ JS files: -->
        <script type="text/javascript" src="js/util.js"></script>
        <script type="text/javascript" src="js/layout.js"></script>
        <script type="text/javascript" src="js/alert.js"></script>
        <script type="text/javascript" src="js/selectProjectDialog.js"></script>
        <script type="text/javascript" src="js/editProjectDialog.js"></script>
        <script type="text/javascript" src="js/editPlatformDialog.js"></script>
        <script type="text/javascript" src="js/editDeploymentRuleDialog.js"></script>
        <script type="text/javascript" src="js/platformInfoDialog.js"></script>
        <script type="text/javascript" src="js/createBranchDialog.js"></script>
        <script type="text/javascript" src="js/scmaction.js"></script>
        <script type="text/javascript" src="js/scmplatforms.js"></script>
        <script type="text/javascript" src="js/scmdeployments.js"></script>
        <script type="text/javascript" src="js/scmactions.js"></script>
        <script type="text/javascript" src="js/scmtree.js"></script>
        <script type="text/javascript" src="js/svntree.js"></script>
        <script type="text/javascript" src="js/project.js"></script>
        <script type="text/javascript" src="js/loading.js"></script>
        <script type="text/javascript"> 
            (function() {
                YAHOO.util.Event.onDOMReady(function() { 
                    YAHOO.scmfviz.selectProjectDialog.init();
                    YAHOO.scmfviz.editProjectDialog.init();
                    YAHOO.scmfviz.editPlatformDialog.init();
                    YAHOO.scmfviz.editDeploymentRuleDialog.init();
                    YAHOO.scmfviz.platformInfoDialog.init();
                    YAHOO.scmfviz.createBranchDialog.init();
                    YAHOO.scmfviz.svntree.initFooter();
                    YAHOO.scmfviz.loading.init();
                    var tabView = new YAHOO.widget.TabView('center');
                    var actionsTab = tabView.getTab(0);
                    actionsTab.addListener('click', function(event) {
                        YAHOO.scmfviz.scmactions.refresh();
                    });
                    var deploymentsTab = tabView.getTab(1);
                    deploymentsTab.addListener('click', function(event) {
                        YAHOO.scmfviz.scmdeployments.refresh();
                    });
                    var platformsTab = tabView.getTab(2);
                    platformsTab.addListener('click', function(event) {
                        YAHOO.scmfviz.scmplatforms.refresh();
                    });
                    
                    var params = getUriParams();
                    if(params.platform) {
                        var url = 'selectproject.php?platform=' + params.platform;
                        var callback = {
                            timeout: 8000, argument: {},
                            success: function(oResponse) {
                                var response = JSON.parse(oResponse.responseText);
                                if(response.data) {
                                    YAHOO.scmfviz.project.setReadOnly(true);
                                    YAHOO.scmfviz.project.useRepository(response.data, 'readonlyuser', 'read123');
                                    YAHOO.scmfviz.platformInfoDialog.show(params.platform);
                                } else {
                                    alert('There was no project found corresponding to platform ' + params.platform);
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
                        YAHOO.util.Connect.asyncRequest('GET', url, callback);
                    } else {
                        YAHOO.scmfviz.selectProjectDialog.show();
                    }
//                    YAHOO.scmfviz.scmactions.init();
                } );
            })();
        </script>
    </head>
    <body class="yui-skin-sam">
        <div id="description">
            <img src="img/scmf.png"/>
            <p>SCMFViz application implements main principles of <a href="docs/SCMF_specification.pdf" alt="download" target="_blank">Software Configuration Management Framework</a> <b>(SCMF)</b> 
                - <a href="http://www.slideshare.net/altern/agile-software-configuration-management-10042528" target="_blank">application versions numbering approach</a> 
                and <a href="http://altern.kiev.ua/images/scm/branches_naming_A3.en.v1.0.0.jpg" target="_blank">repository structuring approach</a>. Checkout <a href="docs/SCMFViz_manual.pdf" target="_blank">SCMFViz manual</a> in order to get acquainted with SCMFViz features and functionality.
            </p>
        </div>
        <div id="bottom">
            <?php
            $appData = parse_ini_file('app.ini');
            ?>
            <div id="version">Version: <a target="_blank" href="<?php echo $appData['scmfviz_url']."?version=".$appData['version'].'&platform='.$appData['platform']?>"><?php echo $appData['version']?></a></div>
            <div id="logos">
                <a href="http://developer.yahoo.com/yui/2/"><img src="img/yui-logo.gif" style="top: -10px;"/></a>
                <a href="http://framework.zend.com/"><img src="img/zf-logo.png" style="position:relative; top: 1px; "/></a>
                <a href="http://www.w3.org/Style/CSS/"><img src="img/w3c_css.png" style="position:relative; top: -8px;"/></a>
                <a href="http://www.w3.org/TR/xhtml1/"><img src="img/w3c_xhtml.png" style="position:relative; top: -8px; "/></a>
                <a href="http://php.net"><img src="img/php5.png" style="position:relative; top: -8px; "/></a>
                <a href="http://pear.php.net/"><img src="img/pear-logo.png" style="position:relative; top: -8px; "/></a>
            </div>
            <div id="author">Concept and implementation: <a href="http://altern.kiev.ua" target="_new">altern</a></div>
        </div>
        
<!--        <div id="validator">
            <div id="version_input">
                <input type="text" value="" />
                <input type="button" value="Check" />
            </div>
            <div id="logger"></div>
            <div id="validation_output"></div>
            
        </div>-->
        <div id="left">
            <div id="scmtree"></div>
        </div>
        <div id="right">
            <div id="svntree"></div>
        </div>
        <div id="rightFooter"></div>
        <div id="center" class="yui-navset">
            <ul class="yui-nav">
<!--                <li><a href="#projects"><em>Projects</em></a></li>-->
                <li class="selected"><a href="#actions"><em>Actions</em></a></li>
                <li><a href="#deployments"><em>Deployment rules</em></a></li>
                <li><a href="#platforms"><em>Platforms</em></a></li>
            </ul>            
            <div class="yui-content">
<!--                <div id="projects">
                    <p>Projects</p>
                </div>-->
                <div id="actions">
<!--                    <p>Actions</p>-->
                </div>
                <div id="deployments">
<!--                    <p>Deployments</p>-->
                </div>
                <div id="platforms">
<!--                    <p>Platforms</p>-->
                </div>
            </div>
        </div>
        
        <div id="selectProjectDialogDiv" style="visibility: hidden">
            <div class="hd">Select project</div>
            <div class="bd">
                <form name="selectProjectForm" id="selectProjectFormId" method="POST" action="checkrepository.php">
                    <table>
                        <tr>
                            <td align="right">Repository URL:</td><td> 
                                <input name="repositoryURL" type="text" value="" id="repositoryURLId"/> 
                                <div id="repositoryURLAutoComplete"></div>
                            </td>
                        </tr>
                        <tr>
                            <td align="right">Readonly:</td>
                            <td align="left">
                                <input name="repositoryReadonly" type="checkbox" selected="false"/> 
                            </td>
                        </tr>
                        <tr>
                            <td align="right">Username:</td>
                            <td><input name="repositoryUser" type="text" /> </td>
                        </tr>
                        <tr>
                            <td align="right">Password:</td><td> <input name="repositoryPass" type="password" /> </td>
                        </tr>
                    </table>
                </form>
            </div>
            <div class="ft"></div>
        </div>
        <div id="editProjectDialogDiv" style="visibility: hidden">
            <div class="hd">Edit project</div>
            <div class="bd">
                <form name="editProjectForm" id="editProjectFormId" method="POST">
                    <table>
                        <tr>
                            <td align="right">Repository URL:</td>
                            <td align="left"> 
                                <span id="repositoryURLValue"></span>
<!--                                <input name="repositoryURL" type="text" value="" id="repositoryURL" /> -->
<!--                                <input name="repositoryURL" type="text" value="" id="repositoryURL" disabled="true"/> -->
                                <input name="repositoryURL" type="hidden" value="" id="repositoryURL" /> 
                            </td>
                        </tr>
                        <tr>
                            <td align="right">Project name:</td>
                            <td align="left"><input name="projectName" type="text" value="" /> </td>
                        </tr>
                        <tr>
                            <td align="right">Starting version:</td>
                            <td align="left">
                                <select name="startingVersion" disabled="true">
                                    <option value="0.x.x">0.x.x</option>
                                    <option value="1.x.x">1.x.x</option>
                                </select> 
                            </td>
                        </tr>
                        <tr>
                            <td align="right">Public:</td>
                            <td align="left"><input name="isPublic" type="checkbox" value="false" /> </td>
                        </tr>
<!--                        <tr>
                            <td align="right">Username:</td><td><input name="repositoryUser" type="text" value="" /> </td>
                        </tr>
                        <tr>
                            <td align="right">Password:</td><td> <input name="repositoryPass" type="password" value="" /> </td>
                        </tr>-->
                    </table>
                </form>
            </div>
            <div class="ft"></div>
        </div>
        <div id="editPlatformDialogDiv" style="visibility: hidden; float: left ">
            <div class="hd"></div>
            <div class="bd">
                <form name="editPlatformForm" id="editPlatformFormId" method="POST">
                    <table>
                        <tr>
                            <td align="right">URL:</td>
                            <td align="left"> 
                                <input name="platformURL" type="text" value="" id="platformURL" /> 
                                <input name="platformId" type="hidden" value="" id="platformId" /> 
                                <input name="projectURL" type="hidden" value="" id="projectURL" /> 
                            </td>
                        </tr>
                        <tr>
                            <td align="right">Remote directory:</td>
                            <td align="left"><input name="remoteDirectory" type="text" value="" /> </td>
                        </tr>
                        <tr>
                            <td align="right">Deployment method:</td>
                            <td align="left">
                                <select name="deploymentMethod">
                                    <option value="ftp" selected="selected">ftp</option>
                                    <option value="ssh">ssh</option>
                                    <option value="sftp">sftp</option>
                                    <option value="scp">scp</option>
                                    <option value="copy">copy</option>
                                    <option value="none">none</option>
                                </select> 
                            </td>
                        </tr>
                        <tr>
                            <td align="right">Deployments limit:</td>
                            <td align="left"><input name="deploymentsLimit" type="text" value="" size="5"/> </td>
                        </tr>
<!--                        <tr>
                            <td align="right">Username:</td><td><input name="repositoryUser" type="text" value="" /> </td>
                        </tr>
                        <tr>
                            <td align="right">Password:</td><td> <input name="repositoryPass" type="password" value="" /> </td>
                        </tr>-->
                    </table>
                </form>
            </div>
            <div class="ft"></div>
        </div>
        <div id="editDeploymentRuleDialogDiv" style="visibility: hidden; float: left">
            <div class="hd"></div>
            <div class="bd">
                <form name="editDeploymentRuleForm" id="editDeploymentRuleFormId" method="POST">
                    <table>
                        <tr>
                            <td align="right">Platform:</td>
                            <td align="left"> 
                                <select name="platformId" id="platformId">
                                    <option value=""></option>
                                </select> 
<!--                                <input name="projectURL" type="hidden" value="" id="projectURL" /> -->
                            </td>
                        </tr>
                        <tr>
                            <td align="right">Version pattern:</td>
                            <td align="left"> 
                                <select name="pattern" id="pattern" >
                                    <option value="N.x.x">N.x.x</option>
                                    <option value="N.M.x">N.M.x</option>
                                    <option value="PA/N.x.K">PA/N.x.K</option>
                                    <option value="A/N.x.K">A/N.x.K</option>
                                    <option value="B/N.x.K">B/N.x.K</option>
                                    <option value="AR/N.M.K">AR/N.M.K</option>
                                    <option value="BR/N.M.K">BR/N.M.K</option>
                                    <option value="RC/N.M.K">RC/N.M.K</option>
                                    <option value="ST/N.M.K">ST/N.M.K</option>
                                </select> 
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
            <div class="ft"></div>
        </div>
        <div id="createBranchDialogDiv" style="visibility: hidden; float: left">
            <div class="hd">Create branch</div>
            <div class="bd">
                <form name="createBranchForm">
                    <div style="text-align: left">
                        <input name="branchType" type="radio" value="support" checked="checked" />Support<br />
                        <input name="branchType" type="radio" value="release" />Release<br />
                        <input name="branchType" type="radio" value="experimental" />Experimental<br />
                        <input name="experimentalName" type="text" value="" disabled/>
                        <input name="parentBranch" type="hidden" value=""/>
                    </div>
                </form>
            </div>
            <div class="ft"></div>
        </div>
        <div id="platformInfoDialogDiv">
            <div class="hd">Platform info</div>
            <div class="bd">
                <form name="platformInfoForm">
                    <table id="platformInfoTable">
                        <tr>
                            <td align="right">URL:</td>
                            <td align="left" id="platformURL"> 
                                
                            </td>
                        </tr>
                        <tr>
                            <td align="right">Pattern:</td>
                            <td align="left" id="platformPattern"> 
                                
                            </td>
                        </tr>
                        <tr>
                            <td align="right" style="vertical-align: top">Platform versions:</td>
                            <td align="left" id="platformVersions"> 
                                
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
            <div class="ft"></div>
        </div>
        <div id="loading"></div>
    </body>
</html>