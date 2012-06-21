<?php
require_once 'config/app.config.php';
require_once 'src/SvnWrapper.php';
require_once 'src/ErrorCodes.php';
require_once 'config/zend.config.php';
require_once 'src/models/Project.php';

$projectModel = new Project();

if(!isset($_GET['platform'])) {
    send_response('', NO_URL_ERROR, 'No platform URL has been supplied');
}

$project_url = $projectModel->getProjectUrlByPlatformUrl($_GET['platform']);

send_response($project_url);
?>
