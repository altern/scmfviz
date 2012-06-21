<?php
require_once 'config/app.config.php';
require_once 'src/SvnWrapper.php';
require_once 'src/ErrorCodes.php';
require_once 'config/zend.config.php';
require_once 'src/models/Platform.php';

$platformModel = new Platform();

if(!isset($_GET['url'])) {
    send_response('', NO_URL_ERROR, 'No repository URL');
}

$platforms = $platformModel->getListByProjectUrl($_GET['url']);

header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');
echo json_encode(array('ResultSet' => array(
    'Result' => $platforms,
    "totalResultsAvailable" => count($platforms),
    'totalResultsReturned' => count($platforms),
    'firstResultPosition' => 1,
    'ResultSetMapUrl' => '',
)));

?>
