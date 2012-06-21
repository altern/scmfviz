<?php
define('NO_SUCH_ACTION_ERROR', 1);
define('ACTION_EXECUTION_ERROR', 2);
define('NO_REPOSITORY_ERROR', 3);
define('NO_URL_ERROR', 4);
define('NO_USER_ERROR', 5);
define('NO_PASSWORD_ERROR', 6);
define('WRONG_CREDENTIALS_ERROR', 7);
define('SVN_ACTION_ERROR', 8);
define('PROJECT_SAVE_ERROR', 9);
define('PLATFORM_SAVE_ERROR', 10);
define('NO_DEPLOYMENTRULE_ID_ERROR', 11);
define('NO_PLATFORM_ID_ERROR', 12);
define('DEPLOYMENT_SAVE_ERROR', 13);

function send_response($data, $error_code=0, $error_message='') {
    header('Content-type: application/json');
    $r = array('data' => $data,
               'error_code' => $error_code,
               'error_message' => $error_message);
    print json_encode($r);
    exit();
}
?>
