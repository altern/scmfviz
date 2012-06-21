<?php

class Project extends Zend_Db_Table_Abstract
{
    protected $_name   = 'project';
    protected $_primary = 'id';
    protected $_sequence = true;

    
    public function getList(){
        $select = $this->getAdapter()
                       ->select()
                       ->distinct()
                       ->from(array('p' => $this->_name));
        $result = $this->getAdapter()
                       ->query($select)
                       ->fetchAll();
        
        return $result;
    }
    
    public function getPublicProjects(){
        $select = $this->getAdapter()
                       ->select()
                       ->distinct()
                       ->from(array('p' => $this->_name))
                       ->where('p.is_public = true');
        $result = $this->getAdapter()
                       ->query($select)
                       ->fetchAll();
        return $result;
    }
    
    public function getProjectByUrl($url) {
        $select = $this->getAdapter()
                       ->select()
                       ->distinct()
                       ->from(array('p' => $this->_name))
                       ->where('p.repository_url = ?', $url);
        $result = $this->getAdapter()
                       ->query($select)
                       ->fetch();
        return $result;
    }
    
    public function save($data) {
        $select = $this->getAdapter()
                       ->select()
                       ->distinct()
                       ->from(array('p' => $this->_name))
                       ->where('p.repository_url = ?', $data['repository_url']);
        $rowCount = $this->getAdapter()
                       ->query($select)
                       ->rowCount();
        if($rowCount == 0) {
            $result = $this->insert($data);
        } else {
            $where = $this->getAdapter()->quoteInto('repository_url = ?', $data['repository_url']);
            $result = $this->update($data, $where);
        }
//        if(!$result) {
//            echo $profiler->getLastQueryProfile()->getQuery().' '.print_r($profiler->getLastQueryProfile()->getQueryParams(), true);
//        }
//        $profiler->setEnabled(true);
        return $result;
    }
    
    public function getProjectUrlByPlatformUrl($url) {
        $select = $this->getAdapter()
                       ->select()
                       ->distinct()
                       ->from(array('pr' => $this->_name), 'repository_url')
                       ->joinLeft(array('pl' => 'platform'), 'pr.id=pl.project_id')
                       ->where('pl.url = ?', $url);
//        print $select->getAdapter()->getProfiler()->getLastQueryProfile()->getQuery();
        return $this->getAdapter()
                       ->fetchOne($select);
    }
}
?>