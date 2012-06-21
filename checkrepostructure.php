<?php
require_once 'config/app.config.php';
require_once 'src/RepositoryReader.php';
require_once 'src/ErrorCodes.php';

$svnreader = new RepositoryReader();
if($svnreader->checkMissingNodes()) {
    send_response('', 
        REPOSITORY_UNINITIALIZED_ERROR, 
        'You have opened repository without proper inner structure. You need to initialize it using \'init repository structure\' button at the \'Repository structure panel\''
    );
}
send_response('initialized');
?>
