<?php
require_once 'config/app.config.php';
require_once 'src/RepositoryReader.php';
require_once 'src/ScmTree.php';
require_once 'src/ErrorCodes.php';
require_once 'config/zend.config.php';
require_once 'src/models/Deployment.php';

$deploymentModel = new Deployment();

if(!isset($_GET['url'])) {
    send_response('', NO_URL_ERROR, 'No platfrom URL specified');
}

$svnreader = new RepositoryReader;
$svntree = $svnreader->readRepoStructure();
$scmtree = new ScmTree($svntree);
$scmtree->setReadOnly(!isset($_GET['readonly']) || $_GET['readonly'] == 'true');
$rules = $deploymentModel->getDeploymentRulesByProjectUrl(SvnWrapper::singleton()->getURL());
$scmtree->setDeploymentRules($rules);

$pattern = $deploymentModel->getPatternByPlatformUrl($_GET['url']);
$versions = Version::categorizeVersions($scmtree->getVersionsForPattern($pattern), $pattern);
function convertVersions($node, $scmtree, $pattern) {
    $convertedNode = array();
    foreach($node as $key => $leaf) {
        if(is_array($leaf)) {
            $convertedNode[] = array('label' => $key, 'children' => convertVersions($leaf, $scmtree, $pattern));
        } else {
            $convertedNode[] = array('label' => $leaf, 'children' => array());
            if($link = $scmtree->getDeploymentLink($leaf)) {
                $convertedNode[count($convertedNode) - 1]['link'] = $link;
            }
            if($scmtree->isLatestForPattern($leaf, $pattern)) {
                $convertedNode[count($convertedNode) - 1]['latest'] = true;
            }
        }
    }
    return $convertedNode;
}
$versions = convertVersions($versions, $scmtree, $pattern);
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');
send_response(array('pattern' => $pattern, 'versions' => $versions));

?>
