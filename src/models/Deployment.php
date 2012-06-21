<?php

class Deployment extends Zend_Db_Table_Abstract
{
    protected $_name   = 'deployment';
    protected $_primary = 'id';
    protected $_sequence = true;
    
    protected $_referenceMap    = array(
        'platform' => array(
            'columns'           => 'platform_id',
            'refTableClass'     => 'Platform',
            'refColumns'        => 'id'
        )
    );
    
    public function getList(){
        $select = $this->getAdapter()
                       ->select()
                       ->distinct()
                       ->from(array('d' => $this->_name))
                       ->joinLeft(array('p' => 'platform'), 'p.id=d.platform_id', array('platform' => 'url'));
        $result = $this->getAdapter()
                       ->query($select)
                       ->fetchAll();
        
        return $result;
    }
    public function getListByProjectUrl($url){
        $select = $this->getAdapter()
                       ->select()
                       ->distinct()
                       ->from(array('d' => $this->_name))
                       ->joinLeft(array('pl' => 'platform'), 'pl.id=d.platform_id', array('platform' => 'url'))
                       ->joinLeft(array('pr' => 'project'), 'pr.id=pl.project_id', array('project_url' => 'repository_url'))
                       ->where('pr.repository_url = ?', $url);
        $result = $this->getAdapter()
                       ->query($select)
                       ->fetchAll();
        
        return $result;
    }
    
    public function save($data) {
        return $this->insert($data);
    }
    
    public function remove($id) {
        $where = $this->getAdapter()->quoteInto($this->_primary .' = ?', $id);
        return $this->delete($where);
    }
    
    public function getDeploymentRulesByProjectUrl($url) {
        $select = $this->getAdapter()
                       ->select()
                       ->distinct()
                       ->from(array('d' => $this->_name), 'pattern')
                       ->joinLeft(array('pl' => 'platform'), 'pl.id=d.platform_id', array('platform' => 'url', 'deployments_limit'))
                       ->joinLeft(array('pr' => 'project'), 'pr.id=pl.project_id', array())
                       ->where('pr.repository_url = ?', $url);
        $result = $this->getAdapter()
                       ->query($select)
                       ->fetchAll();
        return $result;
    }
    
    public function getPatternByPlatformUrl($url) {
        $select = $this->getAdapter()
                       ->select()
                       ->distinct()
                       ->from(array('d' => $this->_name), 'pattern')
                       ->joinLeft(array('pl' => 'platform'), 'pl.id=d.platform_id', array('platform' => 'url', 'deployments_limit'))
                       ->where('pl.url = ?', $url);
        $result = $this->getAdapter()
                       ->fetchOne($select);
        return $result;
    }
}
?>