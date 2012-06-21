<?php

class Platform extends Zend_Db_Table_Abstract
{
    protected $_name   = 'platform';
    protected $_primary = 'id';
    protected $_sequence = true;
    protected $_dependentTables = array('deployment');
    
    public function getList(){
        $select = $this->getAdapter()
                       ->select()
                       ->distinct()
                       ->from(array('pl' => $this->_name))
                       ->joinLeft(array('pr' => 'project'), 'pr.id=pl.project_id', array('project_name' => 'name'));
        $result = $this->getAdapter()
                       ->query($select)
                       ->fetchAll();
        
        return $result;
    }
    
    public function getPlatformByProjectId($project_id){
        $select = $this->getAdapter()
                       ->select()
                       ->distinct()
                       ->from(array('pl' => $this->_name))
//                       ->joinLeft(array('pr' => 'Project'), 'pr.id=pl.project_id', array('project' => 'name'))
                       ->where('pl.project_id = ?', $project_id);
        $result = $this->getAdapter()
                       ->query($select)
                       ->fetchAll();
        
        return $result;
    }
    
    public function getListByProjectUrl($url){
        $select = $this->getAdapter()
                       ->select()
                       ->distinct()
                       ->from(array('pl' => $this->_name))
                       ->joinLeft(array('pr' => 'project'), 'pr.id=pl.project_id', array('project' => 'name'))
                       ->where('pr.repository_url = ?', $url);
        $result = $this->getAdapter()
                       ->query($select)
                       ->fetchAll();
        
        return $result;
    }
    
    public function save($data) {
        if(isset($data['project_url'])) {
            $select = $this->getAdapter()
                       ->select()
                       ->distinct()
                       ->from(array('pr' => 'project'), 'id')
                       ->where('pr.repository_url = ?', $data['project_url']);
            $project_id = $this->getAdapter()
                       ->fetchOne($select);
            if($project_id) {
                $data['project_id'] = $project_id;
            }
            unset($data['project_url']);
        }
        if(!isset($data['id'])) {
            $result = $this->insert($data);
        } else {
            $where = $this->getAdapter()->quoteInto($this->_primary .' = ?', $data['id']);
            unset($data['id']);
            $result = $this->update($data, $where);
        }
        return $result;
    }
    
    public function remove($id) {
        $where = $this->getAdapter()->quoteInto($this->_primary .' = ?', $id);
        return $this->delete($where);
    }
}
?>