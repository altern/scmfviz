<?php
require_once 'config/app.config.php';
require_once 'config/zend.config.php';
require_once 'src/models/Project.php';
require_once 'src/ErrorCodes.php';

$projectModel = new Project();

$data = array();
if(isset($_POST['repositoryURL'])) $data['repository_url'] = $_POST['repositoryURL'];
if(isset($_POST['projectName'])) $data['name'] = $_POST['projectName'];
if(isset($_POST['startingVersion'])) $data['starting_version'] = $_POST['startingVersion'];
if(isset($_POST['isPublic'])) $data['is_public'] = $_POST['isPublic'];

try {
    if($result = $projectModel->save($data)) send_response('OK');
    else throw new Exception('Project has not been saved. Reason unknown');
} catch(Exception $e) {
    send_response('', PROJECT_SAVE_ERROR, $e->getMessage());
}
?>
