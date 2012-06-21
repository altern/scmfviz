<?php
require_once 'config/app.config.php';
require_once 'src/SvnWrapper.php';
require_once 'src/ErrorCodes.php';

if(isset($_POST['repositoryURL'])) {
    try{
        if(!SvnWrapper::singleton()->checkUrl($_POST['repositoryURL'])) {
            send_response('', NO_REPOSITORY_ERROR, 'Incorrect repository URL specified');
        }
    } catch (SvnException $e) {
        try{
            if(!SvnWrapper::singleton()->checkCredentials(
                    $_POST['repositoryURL'], 
                    $_POST['repositoryUser'], 
                    $_POST['repositoryPass']
                )) {
//                    send_response('', WRONG_CREDENTIALS_ERROR, 'Wrong credentials specified');
                send_response('FALSE');
            } else {
                send_response('TRUE');
            }
        } catch(SvnException $e) {
            send_response('FALSE', WRONG_CREDENTIALS_ERROR, $e->getMessage());
        }
    }
    try{
         if(!isset($_POST['repositoryUser'])) {
            send_response('', NO_USER_ERROR, 'There was no username specified');
        } elseif(!isset($_POST['repositoryPass'])) {
            send_response('', NO_PASSWORD_ERROR, 'There was no password specified');
        } else {
            if(empty($_POST['repositoryUser']) || empty($_POST['repositoryPass'])) {
                send_response('FALSE');
            }
            try{
                if(!SvnWrapper::singleton()->checkCredentials(
                        $_POST['repositoryURL'], 
                        $_POST['repositoryUser'], 
                        $_POST['repositoryPass']
                    )) {
//                    send_response('', WRONG_CREDENTIALS_ERROR, 'Wrong credentials specified');
                    send_response('FALSE');
                } else {
                    send_response('TRUE');
                }
            } catch(SvnException $e) {
                send_response('FALSE', WRONG_CREDENTIALS_ERROR, $e->getMessage());
            }
        }
    } catch (SvnException $e) {
        send_response('', SVN_ACTION_ERROR, $e->getMessage());
    }
}
send_response('', NO_URL_ERROR, 'There was no repository URL specified');
?>
