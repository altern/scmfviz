<?php
require_once 'config/app.config.php';
require_once 'config/zend.config.php';
require_once 'src/models/Deployment.php';
require_once 'src/ErrorCodes.php';

$deploymentModel = new Deployment();

$data = array();
// fields: ["id", "platform_id", "pattern"]
// projectURL, platformId, pattern
if(isset($_POST['platformId'])) $data['platform_id'] = $_POST['platformId'];
if(isset($_POST['projectURL'])) $data['project_url'] = $_POST['projectURL'];
if(isset($_POST['pattern'])) $data['pattern'] = $_POST['pattern'];

try {
    if($result = $deploymentModel->save($data)) send_response('OK');
    else throw new Exception('Deployment rule has not been saved. Reason unknown');
} catch(Exception $e) {
    send_response('', PLATFORM_SAVE_ERROR, $e->getMessage());
}
?>
