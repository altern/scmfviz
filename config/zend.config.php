<?
$PROJECT_ROOT = dirname('..');
set_include_path(''
    . PATH_SEPARATOR . "$PROJECT_ROOT/library"
    . PATH_SEPARATOR . "$PROJECT_ROOT/src/models"
    . PATH_SEPARATOR . "$PROJECT_ROOT/src/views"
    . PATH_SEPARATOR . "$PROJECT_ROOT/src/controllers"
    . PATH_SEPARATOR . get_include_path()
);
include_once('Zend/Loader/Autoloader.php');
$loader = Zend_Loader_Autoloader::getInstance();
$loader->setFallbackAutoloader(true);
$loader->suppressNotFoundWarnings(false);

$options = array(
    'host'     => 'localhost',
    'username' => 'root',
    'password' => '',
    'dbname'   => 'SCMFViz',
    'adapterNamespace' => 'Db'
);

//$db = Zend_Db::factory('Mysqli', $options);
$db = new Zend_Db_Adapter_Mysqli($options);
Zend_Db_Table_Abstract::setDefaultAdapter($db);
Zend_Registry::set('db', $db);

$locale = new Zend_Locale('en_US');
Zend_Registry::set('Zend_Locale', $locale);

//Set logConfig
$logConfig = new Zend_Config_Ini($PROJECT_ROOT."/config/log.config.ini");
Zend_Registry ::set("logConfig", $logConfig);

?>