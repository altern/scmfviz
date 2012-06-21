<?php
require_once 'config/app.config.php';
require_once 'config/zend.config.php';
require_once 'src/models/Deployment.php';
require_once 'src/ErrorCodes.php';

$deploymentModel = new Deployment();

if(!isset($_GET['id'])) {
    send_response('', NO_DEPLOYMENTRULE_ID_ERROR, 'No deployment rule id has been specified');
}

try {
    if($result = $deploymentModel->remove($_GET['id'])) send_response('OK');
    else throw new Exception('Deployment rule has not been deleted. Reason unknown');
} catch(Exception $e) {
    send_response('', DEPLOYMENT_SAVE_ERROR, $e->getMessage());
}
?>
