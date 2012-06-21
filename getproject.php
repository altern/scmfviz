<?php
require_once 'config/app.config.php';
require_once('src/ErrorCodes.php');
require_once('config/zend.config.php');
require_once('src/models/Project.php');

if(!isset($_GET['url'])) {
    send_response('', NO_URL_ERROR, 'No repository URL');
}
$projectModel = new Project();
$project = $projectModel->getProjectByUrl($_GET['url']);
if($project) {
    send_response($project);
}
?>
