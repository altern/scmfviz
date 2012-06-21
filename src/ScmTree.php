<?php

class ScmTree {
    
    private $svntree = array();
    private $read_only = true;
    private $deployment_rules = array();
    
    public function __construct($svntree = array()) {
        $this->svntree = $svntree;
    }
    
    public function __toString() {
        return print_r($this->getTree(), true);
    }
    
    public function getJSON() {
        $return = array('tree' => $this->getTree());
//        $return['lastVersion'] = $this->lastVersion;
        return json_encode($return);
    }
        
    public function getDeploymentRules() {
        return $this->deployment_rules;
    }
    
    public function setDeploymentRules($rules) {
        $this->deployment_rules = $rules;
    }
    
    public function setReadOnly($value) {
        $this->read_only = $value;
    }
    
    public function getReadOnly() {
        return $this->read_only;
    }
    
    public function getTree() {
        $scmtree = array();
        $support_branches = $this->getListOfSupportBranches();
        if(!empty($support_branches)) {
            foreach($support_branches as $support_branch) {
                $children = array_merge(
                    $this->getBuildsForSupport($this->svntree['tags']['builds'], $support_branch),
                    $this->getReleaseBranchForSupport($this->svntree['branches']['release'], $support_branch, $this->svntree['tags']['releases']),
                    $this->getExperimentalBranches($support_branch, $this->svntree['branches']['experimental'][$support_branch])
                );
                $treenode = array('label' => $support_branch); 
                if(!$this->read_only) $treenode['actions'] = $this->getSupportBranchActions($support_branch);
//                if(!empty($this->project_root)) {
//                    $treenode['link'] = $this->project_root.'branches/support/'.$support_branch;
//                }
                $treenode['link'] = $this->getDeploymentLink($support_branch);
                if(!empty($children)) {
                    $treenode['children'] = $children;
                }
                $scmtree[] = $treenode;
            }
            $scmtree[count($scmtree) - 1]['isTrunk'] = true;
        }
        return $scmtree;
    }
    
    private function getListOfSupportBranches() {
        $support_branches = array();
        if($this->svntree[0] == 'trunk' || isset($this->svntree['trunk'])) {
            $latest_support_branch = Version::STARTING_SUPPORT_VERSION;
            if(is_array($this->svntree['branches']['support'])) {
                foreach($this->svntree['branches']['support'] as $support_branch) {
                    if(Version::cmp($latest_support_branch, $support_branch) == -1) {
                        $latest_support_branch = $support_branch;
                    }
                }
                $support_branches = $this->svntree['branches']['support'];
                $support_branches[] = Version::incrementVersion($latest_support_branch);
            } else {
                $support_branches[] = $latest_support_branch;
            }
//            print_r($support_branches);
            usort($support_branches, array('Version', 'cmp'));
        }
        return $support_branches;
    }
    
    private function getReleaseBranchForSupport($release_branches, $support_branch, $releases) {
        $scmtree = array();
        if(is_array($release_branches)) {
            foreach($release_branches as $release_branch) {
                if(Version::isParent($support_branch, $release_branch)) {
                    $children = $this->getReleasesForBranch($releases, $release_branch);
                    $treenode = array('label' => $release_branch);
                    if(!$this->read_only) $treenode['actions'] = $this->getReleaseBranchActions();
//                    if(!empty($this->project_root)) {
//                        $treenode['link'] = $this->project_root.'branches/release/'.$release_branch;
//                    }
                    $treenode['link'] = $this->getDeploymentLink($release_branch);
                    if(!empty($children)) {
                        $treenode['children'] = $children;
                    }
                    $scmtree[] = $treenode;
                }
            }
        }
        return $scmtree;
    }
    
    private function getExperimentalBranches($support_branch, $experimental_branches) {
        $scmtree = array();
        if(is_array($experimental_branches)) {
            foreach($experimental_branches as $branch) {
                $treenode = array(
                    'label' => $branch, 
                );
                // TODO: think about links and deployment mapping for experimental branches
//                if(!empty($this->project_root)) {
//                    $treenode['link'] = $this->project_root.'branches/experimental/'.$support_branch.'/'.$branch;
//                }
                $scmtree[] = $treenode;
            }
        }
        return $scmtree;
    }
    
    private function getReleasesForBranch($releases, $release_branch) {
        $scmtree = array();
        if(is_array($releases)) {
            foreach($releases as $maturity => $release_arr) {
                $filtered_releases = array();
                if(is_array($release_arr)) {
                    foreach($release_arr as $release_build) {
                        if(Version::isParent($release_branch, $release_build)) {
                            $filtered_releases[] = $release_build;
                        }
                    }
                }
                if(!empty($filtered_releases)) {
                    $children = $this->getReleases($filtered_releases, $maturity);
                    $treenode = array('label' => $maturity, );
                    if(!empty($children)) {
                        $treenode['children'] = $children;
                    }
                    $scmtree[] = $treenode;
                }
            }
        }
        return $scmtree;
    }
    
    private function getBuildsForSupport($builds, $support_branch) {
        $scmtree = array();
        if(is_array($builds)) {
            uksort($builds, array('Version', 'buildsMaturityCmp'));
            foreach($builds as $maturity => $builds_arr) {
                $filtered_builds = array();
                if(is_array($builds_arr)) {
                    foreach($builds_arr as $build) {
                        if(Version::isParent($support_branch, $build)) {
                            $filtered_builds[] = $build;
                        }
                    }
                }
                if(!empty($filtered_builds)) {
                    $children = $this->getBuilds($filtered_builds, $maturity);
                    $treenode = array('label' => $maturity);
                    if(!empty($children)) {
                        $treenode['children'] = $children;
                    }
                    $scmtree[] = $treenode;
                }
            }
        }
        return $scmtree;
    }
    
    private function getReleases($releases, $maturity) {
        $scmtree = array();
        if(is_array($releases)) {
            foreach($releases as $release) {
                $treenode = array('label' => $release);
                if(!$this->read_only) $treenode['actions'] = $this->getReleaseActions($maturity, $release);
//                if(!empty($this->project_root)) {
//                    $treenode['link'] = $this->project_root."tags/releases/$maturity/$release";
//                }
                $treenode['link'] = $this->getDeploymentLink($release, $maturity);
                $scmtree[] = $treenode;
            }
        }
        return $scmtree;
    }
    
    private function getBuilds($builds, $maturity) {
        $scmtree = array();
        if(is_array($builds)) {
            foreach($builds as $build) {
                $treenode = array('label' => $build); 
                if(!$this->read_only) $treenode['actions'] = $this->getBuildActions($maturity, $build);
//                if(!empty($this->project_root)) {
//                    $treenode['link'] = $this->project_root."tags/releases/$maturity/$build";
//                }
                $treenode['link'] = $this->getDeploymentLink($build, $maturity);
                $scmtree[] = $treenode;
            }
        }
        return $scmtree;
    }
    
    private function getBuildActions($actual_maturity, $actual_build) {
        $actions = array();
        foreach($this->svntree['tags']['builds'] as $builds) {
            if(is_array($builds)) {
                foreach($builds as $build) {
                    if(Version::areSiblings($actual_build, $build) 
                    && Version::cmp($actual_build, $build) == -1) {
                        return $actions;
                    }
                }
            }
        }
        switch($actual_maturity) {
            case Version::PA: 
                $actions = array(
                    array('action' => 'promote to alpha', 
                        'callback' => 'promoteToAlpha', 
                        'message' => 'promoting to alpha'
                    )); 
                break;
            case Version::A: 
                $actions = array(
                    array('action' => 'promote to beta', 
                        'callback' => 'promoteToBeta', 
                        'message' => 'promoting to beta'
                    )); 
                break;
        }
        return $actions;
    }
    
    private function getReleaseActions($actual_maturity, $actual_release) {
        $actions = array();
        foreach($this->svntree['tags']['releases'] as $releases) {
            if(is_array($releases)) {
                foreach($releases as $release) {
                    if(Version::areSiblings($actual_release, $release) 
                        && Version::cmp($actual_release, $release) == -1) {
                        return $actions;
                    }
                }
            }
        }
        switch($actual_maturity) {
            case Version::AR: 
                $actions = array(
                    array('action' => 'promote to beta-release', 
                        'callback' => 'promoteToBetaRelease', 
                        'message' => 'promoting to beta-release'
                    )); 
                break;
            case Version::BR: 
                $actions = array(
                    array('action' => 'promote to release candidate', 
                        'callback' => 'promoteToReleaseCandidate', 
                        'message' => 'promoting to release candidate'
                    )); 
                break;
            case Version::RC: 
                $actions = array(
                    array('action' => 'promote to stable release', 
                        'callback' => 'promoteToStableRelease', 
                        'message' => 'promoting to stable release'
                    )); 
                //array('action' => 'rollback', 'callback' => 'rollback', 'message' => 'rolling back to previous release instead of currently deployed')
                break;
        }
        return $actions;
    }
    
    private function getReleaseBranchActions() {
        $actions = array(array(
            'action' => 'deliver alpha-release', 
            'callback' => 'deliverAlphaRelease', 
            'message' => 'delivering alpha-release'
        ));
        return $actions;
    }
    
    private function getSupportBranchActions($support_branch) {
        $actions = array();
        $support_branches = $this->getListOfSupportBranches();
        $latest_branch = array_slice($support_branches, -1);
        if($latest_branch[0] == $support_branch) {
            $actions = array(
                array('action' => 'create branch', 'callback' => 'createBranch'),
                array('action' => 'deliver', 'callback' => 'deliverPreAlphaBuild', 'message' => 'delivering pre-alpha build from trunk'),
            );
        } else {
            $actions = array(
                array('action' => 'create release branch', 'callback' => 'createReleaseBranch', 'message' => 'creating release branch'),
                array('action' => 'deliver', 'callback' => 'deliverPreAlphaBuild', 'message' => 'delivering pre-alpha build from trunk'),
            );
        }
        return $actions;
    }
    
    public function getDeploymentLink($version, $maturity = '') {
        $link = '';
        if(is_array($this->deployment_rules)) {
            foreach($this->deployment_rules as $rule) {
                $full_version = $maturity ? "$maturity/$version" : $version;
//                print "$full_version, ".$rule['pattern'].": ".Version::correspondsToPattern($full_version, $rule['pattern'])."\n";
                if(Version::correspondsToPattern($full_version, $rule['pattern'])
                && $this->generateDeploymentLink($version, $rule['pattern'], $rule['deployments_limit'])) {
//                    print_r($rule);
                    if($this->isLatestForPattern($version, $rule['pattern'])) {
                        if(strpos($rule['platform'], 'http://') !== false) {
                            $link = $rule['platform'].'/';
                        } else {
                            $link = 'http://'.$rule['platform'].'/';
                        }
                    } else {
                        if(strpos($rule['platform'], 'http://') !== false) {
                            $link = $rule['platform'].'/'.$version;
                        } else {
                            $link = 'http://'.$rule['platform'].'/'.$version;
                        }
                    }
                }
            }
        }
        return $link;
    }
    
    public function isLatestForPattern($version, $pattern) {
        $pattern_versions = $this->getVersionsForPattern($pattern);
        $pattern_arr = explode('/', $pattern);
        if(count($pattern_arr) > 1) {
            list(, $pattern_version_str) = $pattern_arr;
        } else {
            $pattern_version_str = $pattern;
        }
        foreach($pattern_versions as $existing_version) {
            if(Version::correspondsToPattern($existing_version, $pattern_version_str)
                && Version::cmp($existing_version, $version) == 1) { // $existing_version > $version
                    return false;
            }
        }
        return true;
    }
    
    public function getVersionsForPattern($pattern) {
        $versions_arr = array();
        $pattern_arr = explode('/', $pattern);
        if(count($pattern_arr) > 1) {
            list($pattern_maturity, $pattern_version_str) = $pattern_arr;
        } else {
            $pattern_maturity = '';
            $pattern_version_str = $pattern;
        }
        switch($pattern_maturity) {
            case Version::PA:
            case Version::A:
            case Version::B:
                $versions_arr = $this->svntree['tags']['builds'][$pattern_maturity];
                break;
            case Version::AR:
            case Version::BR:
            case Version::RC:
            case Version::ST:
                $versions_arr = $this->svntree['tags']['releases'][$pattern_maturity];
                break;
            default:
                $v = new Version($pattern_version_str);
                if($v->isSupportBranch()) {
                    $versions_arr = $this->svntree['branches']['support'];
                    if(count($versions_arr) > 0) {
                        $versions_arr[] = Version::incrementVersion($versions_arr[count($versions_arr) - 1]);
                    } else {
                        $versions_arr[] = Version::STARTING_SUPPORT_VERSION;
                    }
                }
                if($v->isReleaseBranch()) {
                    $versions_arr = $this->svntree['branches']['release'];
                }
                break;
        }
        return $versions_arr;
    }
    
    private function generateDeploymentLink($version, $pattern, $deployments_limit) {
        $pattern_versions = $this->getVersionsForPattern($pattern);
//        print_r(array(
//            '$version' => $version,
//            '$pattern' => $pattern,
//            '$pattern_versions' => $pattern_versions, 
//            '$deployments_limit' => $deployments_limit));
        foreach($pattern_versions as $pos => $existing_version) {
            if ($pos >= (count($pattern_versions) - $deployments_limit)
                && Version::cmp($version, $existing_version) == 0) { // $version == $existing_version 
//                    print "$version ~ $existing_version; $pos >= ".count($pattern_versions).
//                            " - $deployments_limit (".
//                            (count($pattern_versions) - $deployments_limit).
//                            "): ".($pos >= count($pattern_versions) - $deployments_limit ? 'true' : 'false')."\n";
                    return true;
            }
        }
        return false;
    }
}

?>
