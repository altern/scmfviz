<?php
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(dirname(__FILE__)).'/library/PEAR/');
require_once 'VersionControl/SVN.php';
require_once 'ScmAction.php';

class SvnException extends Exception {};

class SvnWrapper {
    
    private static $instance = null;
    
    private $url = '';
    private $username = '';
    private $password = '';
    private $svnactions = array();
    private $svnoptions = array('fetchmode' => VERSIONCONTROL_SVN_FETCHMODE_ARRAY);
    private $svnerror = null;
    
    private function __construct() {
        $this->svnerror = &PEAR_ErrorStack::singleton('VersionControl_SVN');
    }
    
    public static function singleton() {
        if(self::$instance == null) {
            $wrapper = new SvnWrapper;
            if(isset($_COOKIE['repositoryURL'])) {
                $wrapper->setURL($_COOKIE['repositoryURL']);
            }
            if(isset($_COOKIE['repositoryUser'])) {
                $wrapper->setUsername($_COOKIE['repositoryUser']);
            }
            if(isset($_COOKIE['repositoryPass'])) {
                $wrapper->setPassword($_COOKIE['repositoryPass']);
            }
            self::$instance = $wrapper;
        }
        return self::$instance;
    }
    
    public function setURL($url) {
        $this->url = $url;
    }
    
    public function getURL() {
        return $this->url;
    }
    
    public function setUsername($user) {
        $this->username = $user;
    }
    
    public function setPassword($password) {
        $this->password = $password;
    }
    
    public function getErrors() {
        $all_errors = '';
        if (count($errs = $this->svnerror->getErrors())) {
            foreach ($errs as $err) {
                $all_errors .= $err['message']."\n";
            }
        }
        return $all_errors;
    }
    
    private function getAction($action) {
        if(!isset($this->svnactions[$action])) {
            $this->svnactions[$action] = VersionControl_SVN::factory($action, $this->svnoptions);
        }
        return $this->svnactions[$action];
    }
    
    private function getSwitches() {
        $switches = array();
        if($this->username) $switches['username'] = $this->username;
        if($this->password) $switches['password'] = $this->password;
        return $switches;
    }
    
    public function ls($relPath = '') {
        $action = $this->getAction('list');
        $switches = $this->getSwitches();
        $switches['recursive'] = false;
//        $switches['recursive'] = true;
        $relPath = rtrim($relPath, '/\ ');
        $list = $action->run(array($this->url.'/'.$relPath), $switches);
        if($errors = $this->getErrors()) {
            throw new SvnException($errors);
        } 
        return $list;
    }
    
    public function cp($from, $to, $message) {
        $action = $this->getAction('copy');
        $switches = $this->getSwitches();
        $switches['message'] = $message;
        $from = rtrim($from, '/\ ');
        $to = rtrim($to, '/\ ');
        $action->run(array(
            'from' => $this->url.'/'.$from, 
            'to' => $this->url.'/'.$to
        ), $switches);
        if($errors = $this->getErrors()) {
            throw new SvnException($errors);
        } 
        return true;
    }
    
    public function rm($relPath, $message) {
        $action = $this->getAction('delete');
        $switches = $this->getSwitches();
        $switches['message'] = $message;
        $action->run(array($this->url.'/'.$relPath), $switches);
        if($errors = $this->getErrors()) {
            throw new SvnException($errors);
        }  
        return true;
    }
    
    public function mkdir($relPath, $message) {
        $action = $this->getAction('mkdir');
        $switches = $this->getSwitches();
        $switches['message'] = $message;        
        $action->run(array($this->url.'/'.rtrim($relPath, '/\ ')), $switches);
        if($errors = $this->getErrors()) {
            throw new SvnException($errors);
        }  
        return true;
    }
    
    private function validateUrl($url) {
        return preg_match('~^(http(s)?://|svn://|file:///)[a-z0-9-\.]+([a-z0-9-]+)*(:[0-9]+)?(/.*)?$~i', $url);
    }
    public function checkUrl($url) {
        if(!$this->validateUrl($url)) {
            throw new SvnException('Repository URL is not valid');
        }
        $action = $this->getAction('list');
        $action->run(array($url), array());
        if($errors = $this->getErrors()) {
//            if(strpos($errors, 'Unable to connect') !== false ) {
//                return false;   
//            } else {
                throw new SvnException($errors);
//            }
        }
        
        $url = trim($url, '/');
        $parent_url = substr($url, 0, strrpos($url, '/'));
        if($this->validateUrl($parent_url)) {
            $action->run(array($parent_url), array());
            if(!$this->getErrors()) {
                throw new SvnException('Incorrect repository URL specified. Repository root URL should be used instead of subdirectory URL.');
            }
        }
        return true;
    }
    
    public function checkCredentials($url, $user, $password) {
        while($this->svnerror->hasErrors()) {
            $this->svnerror->pop(); 
        }
        $switches = array(
            'username' => $user, 
            'password' => $password, 
            'message' => ''
        );
        $action = $this->getAction('mkdir');
        $url = rtrim($url, '/\ ');
        $action->run(array($url.'/test'), $switches);
        $action = $this->getAction('delete');
        $action->run(array($url.'/test'), $switches);
        if($errors = $this->getErrors()) {
            throw new SvnException($errors);
        }
        return true;
    }
    
    public function getScmActions() {
        $logs = array();
        $action = $this->getAction('log');
        $log_messages = $action->run(array($this->url), $this->getSwitches());
        if(is_array($log_messages)) {
            foreach($log_messages as $log_message) {
                $label_position = strpos($log_message['MSG'], ScmAction::SCM_ACTION_LABEL);
                if( $label_position !== false) {
                    $logs[] = array(
                        'message' => trim(substr($log_message['MSG'], 0, $label_position)),
                        'revision' => $log_message['REVISION']
                    );
                }
            }
        }
        return $logs;
    }
}
?>
