<?php
require_once 'config/app.config.php';
require_once 'config/zend.config.php';
require_once 'src/models/Platform.php';
require_once 'src/ErrorCodes.php';

$platformModel = new Platform();

$data = array();
// fields: ["id", "url", "remote_directory", "deployments_limit", "deployment_method"]
// platformURL, remoteDirectory, deploymentMethod, deploymentsLimit
if(isset($_POST['id']) && $_POST['id']) $data['id'] = $_POST['id'];
if(isset($_POST['platformURL'])) $data['url'] = $_POST['platformURL'];
if(isset($_POST['projectURL'])) $data['project_url'] = $_POST['projectURL'];
if(isset($_POST['remoteDirectory'])) $data['remote_directory'] = $_POST['remoteDirectory'];
if(isset($_POST['deploymentsLimit'])) $data['deployments_limit'] = $_POST['deploymentsLimit'];
if(isset($_POST['deploymentMethod'])) $data['deployment_method'] = $_POST['deploymentMethod'];

try {
    if($result = $platformModel->save($data)) send_response('OK');
    else throw new Exception('Platform has not been saved. Reason unknown');
} catch(Exception $e) {
    send_response('', PLATFORM_SAVE_ERROR, $e->getMessage());
}
?>
