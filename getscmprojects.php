<?php
require_once 'config/app.config.php';
require_once 'src/SvnWrapper.php';
require_once 'src/ErrorCodes.php';
require_once 'config/zend.config.php';
require_once 'src/models/Project.php';

$projectModel = new Project();

//$actions = SvnWrapper::singleton()->getScmProjects();
$projects = array();
$project_urls = json_decode($_COOKIE['repositoryURLAutoComplete']);
$db_projects = $projectModel->getPublicProjects();
if(is_array($project_urls)) {
    foreach($project_urls as $url) {
        foreach($db_projects as $project) {
            $url = urldecode($url);
//            print_r(array('db_url' => $project['repository_url'], 'cookies_url' => $url));
            if($project['repository_url'] == $url) {
                $project['password'] = '';
                $projects[] = $project;
            } else {
                $projects[] = array(
                    'name' => '-',
                    'repository_url' => $url,
                    'starting_version' => '0.x.x',
                    'is_public' => false,
                    'username' => '',
                    'password' => '',
                );
            }
        }
    }
}
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');
echo json_encode(array('ResultSet' => array(
    'Result' => $projects,
    "totalResultsAvailable" => count($projects),
    'totalResultsReturned' => count($projects),
    'firstResultPosition' => 1,
    'ResultSetMapUrl' => '',
)));

?>
