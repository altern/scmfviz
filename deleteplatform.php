<?php
require_once 'config/app.config.php';
require_once 'config/zend.config.php';
require_once 'src/models/Platform.php';
require_once 'src/ErrorCodes.php';

$platformModel = new Platform();

if(!isset($_GET['id'])) {
    send_response('', NO_PLATFORM_ID, 'No platform id has been specified');
}

try {
    if($result = $platformModel->remove($_GET['id'])) send_response('OK');
    else throw new Exception('Platform has not been deleted. Reason unknown');
} catch(Exception $e) {
    send_response('', PLATFORM_SAVE_ERROR, $e->getMessage());
}
?>
