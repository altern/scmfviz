<?php
require_once 'config/app.config.php';
require_once 'src/SvnWrapper.php';
require_once 'src/ErrorCodes.php';

$actions = SvnWrapper::singleton()->getScmActions();
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');
echo json_encode(array('ResultSet' => array(
    'Result' => $actions,
    "totalResultsAvailable" => count($actions),
    'totalResultsReturned' => count($actions),
    'firstResultPosition' => 1,
    'ResultSetMapUrl' => '',
)));

?>
