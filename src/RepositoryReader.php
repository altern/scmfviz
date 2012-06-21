<?php
require_once 'SvnWrapper.php';
require_once 'ScmTree.php';
require_once 'Version.php';

class VersionComparisonException extends Exception {}

/**
 *  Class contains methods for reading svn repository structure and converting
 *  data into the convenient form for subsequent repository structure analysis
 */
class RepositoryReader {

    /**
     * Method converts flat array containing repository paths into the nested tree
     * 
     * @param array $paths - flat array containing repository paths
     * @param integer $nested_level - current nested level used for building nested tree recursively
     * @return array - nested tree containing parent and child repository directories
     */
    public function buildTreeFromFlatList($paths, $nested_level = 0) {
        $tree = array();
        $chunk_start = 0;
        foreach($paths as $key => $path) {
            $path_arr = split('/', $path['name']);
            $path_arr_count = count($path_arr);
            if($path_arr_count <= ($nested_level + 1) ) {
                if(is_array($tree[count($tree) - 1]) && key_exists('children', $tree[count($tree) - 1])) {
                    $tree[count($tree) - 1]['children'] = $this->buildTreeFromFlatList(
                        array_slice($paths, $chunk_start, $key - $chunk_start), 
                        $nested_level + 1
                    );
                }
                $tree[] = array(
                    'name' => $path_arr[$nested_level], 
                    'children' => array()
                );
                $chunk_start = $key + 1;
            }
            
        }
        if(is_array($tree[count($tree) - 1]) 
            && key_exists('children', $tree[count($tree) - 1]) 
            && $tree[count($tree) - 1]['name'] != 'trunk') {
            $tree[count($tree) - 1]['children'] = $this->buildTreeFromFlatList(
                array_slice($paths, $chunk_start, $key - $chunk_start + 1), 
                $nested_level + 1
            );
        }
        return $tree;
    }
   
    /**
     * Method uses subversion credentials and repository path for the purpose of getting repository structure
     * 
     * @return array - flat array containing repository paths
     */
    public function readRepoStructure() {
        $tree = array();
        $entries_list = SvnWrapper::singleton()->ls();
        if(is_array($entries_list)) {
//            $svntree = $this->buildTreeFromFlatList($entries_list);
            $svntree = $this->buildTreeRecursively('/');
            $tree = $this->svnTreeTransform($svntree);
        }
        return $tree;
    }
    
    public function getSvnTree() {
        $tree = array();
//        $entries_list = SvnWrapper::singleton()->ls();
//        if(is_array($entries_list)) {
//            $tree = $this->buildTreeFromFlatList($entries_list);
//        }
        $tree = $this->buildTreeRecursively('/');
        return $tree;
    }
    
    public function buildTreeRecursively($path) {
        $ls = SvnWrapper::singleton()->ls($path);
        $tree = array();
//        print_r($tree);
        if(is_array($ls)) {
            foreach($ls as $repo_path) {
                if($repo_path['type'] == 'D') {
                    $tree[] = array('name' => $repo_path['name'], 'children' => array());
                    if(!$this->disallowTreeTraversal($path.'/'.$repo_path['name']) ) { 
                        $children = $this->buildTreeRecursively($path.'/'.$repo_path['name']);
                        if(!empty($children)) { 
                            $tree[count($tree) - 1]['children'] = $children;
                        } 
                    }
                }
            }
        }
        return $tree;
    }
    
    private function disallowTreeTraversal($path) {
        $return = preg_match('|/trunk|', $path)
                || preg_match('|/branches/experimental/(.*)/(.*)|', $path)
                || preg_match('|/branches/support/(.*)|', $path)
                || preg_match('|/branches/release/(.*)|', $path)
                || preg_match('|/tags/(.*)/(.*)/(.*)|', $path);
        return $return;
    }
    /**
     * Method converts array from nested tree format array('label' => 'label', 'children' => array()) 
     * into the key-values format array('label' => array(child1, child2, ... childN))
     * 
     * @param array $tree - nested tree containing parent and child repository directories 
     * @return array 
     */
    public function svnTreeTransform($tree) {
        $transformedTree = array();
        foreach($tree as $leaf) {
            $children = $this->svnTreeTransform($leaf['children']);
            if(!empty($children)) {
                $transformedTree[$leaf['name']] = $children;
            } else {
                $transformedTree[] = $leaf['name'];
            }
        }
        return $transformedTree;
    }
    
    public function checkMissingNodes() {
        return $this->areDirectoriesMissing($this->getDirectoriesStatus());
    }
    
    public function getDirectoriesStatus() {
        $svntree = $this->getSvnTree();
        $actualDirectoriesStatus = $this->checkDirectoriesStatus(
            $svntree, 
            Version::$REPO_DIRECTORIES
        );
        return $actualDirectoriesStatus;
    }
    
    private function areDirectoriesMissing($status) {
        $result = false;
        foreach($status as $stat) {
            if(is_array($stat)) {
                $result = $result || $this->areDirectoriesMissing($stat);
            } else {
                $result = $result || !$stat; 
            }
        }
        return $result;
    }
    
    private function checkDirectoriesStatus($svntree, $treeStatus) {
        foreach($svntree as $node) {
            foreach($treeStatus as $expectedNode => $status) {
                if($node['name'] == $expectedNode) {
                    if(!is_array($status)) {
                        $treeStatus[$expectedNode] = true;
                    } else {
                        $treeStatus[$expectedNode] = $this->checkDirectoriesStatus($node['children'], $status);
                    } 
                } 
            }
        }
        return $treeStatus;
    }
}

?>
