<?php
require_once 'config/app.config.php';
require_once 'src/ScmAction.php';
require_once 'src/ErrorCodes.php';

try {
    if(isset($_GET['action'])) {
        $action = $_GET['action'];
        if(method_exists('ScmAction', $action)) {
            try { 
                $scmaction = new ScmAction();
                if(isset($_GET['params'])) {
                    $params = $_GET['params'];
                    if($lastVersion = $scmaction->$action(($params))) send_response($lastVersion);
                    else send_response ('', ACTION_EXECUTION_ERROR, 'Error happened on SVN action perform');
                } else {
                    if($lastVersion = $scmaction->$action()) send_response($lastVersion);
                    else send_response ('', ACTION_EXECUTION_ERROR, 'Error happened on SVN action perform');
                }
            } catch (SvnException $e) {
                send_response ('', ACTION_EXECUTION_ERROR, $e->getMessage());
            }
        } else {
            send_response('', NO_SUCH_ACTION_ERROR, 'No such action: '.$action );
        }
    }
} catch (ScmActionException $e) {
    send_response('', ACTION_EXECUTION_ERROR, 'Action cannot be completed properly because of the following reason: '.$e->getMessage());
}

?>
