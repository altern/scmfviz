<?php
require_once 'config/app.config.php';
require_once 'src/RepositoryReader.php';
require_once 'src/ErrorCodes.php';
$svnreader = new RepositoryReader;
$svntree = $svnreader->getSvnTree();
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');
echo json_encode($svntree); 
?>
