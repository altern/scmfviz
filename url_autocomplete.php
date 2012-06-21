<?php
require_once 'config/app.config.php';
require_once('src/ErrorCodes.php');
require_once('config/zend.config.php');
require_once('src/models/Project.php');

$projectModel = new Project();
$projects = $projectModel->getPublicProjects();
$project_urls = json_decode(urldecode($_COOKIE['repositoryURLAutoComplete']));
foreach($projects as $project) {
    if(is_array($project_urls)) {
        if( !in_array($project['repository_url'], $project_urls)) {
            $project_urls[] = $project['repository_url'];
        }
    } else {
        $project_urls[] = $project['repository_url'];
    }
}
foreach($project_urls as $url) {
    if(strpos($url, $_GET['query']) > -1) {
        print "$url\n";
    }
}
?>
