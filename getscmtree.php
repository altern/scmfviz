<?php
require_once 'config/app.config.php';
require_once 'config/zend.config.php';
require_once 'src/RepositoryReader.php';
require_once 'src/SvnWrapper.php';
require_once 'src/ErrorCodes.php';
require_once 'src/models/Deployment.php';

if(isset($_GET['url'])) {
    SvnWrapper::singleton()->setURL($_GET['url']);
}
$svnreader = new RepositoryReader;
$svntree = $svnreader->readRepoStructure();
$scmtree = new ScmTree($svntree);
$deploymentModel = new Deployment();
$scmtree->setReadOnly(!isset($_GET['readonly']) || $_GET['readonly'] == 'true');
$rules = $deploymentModel->getDeploymentRulesByProjectUrl(SvnWrapper::singleton()->getURL());
$scmtree->setDeploymentRules($rules);
if($scmtree) {
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    echo $scmtree->getJSON(); 
}
?>
