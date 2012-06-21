<?php
require_once('SvnWrapper.php');
require_once('Version.php');
require_once('RepositoryReader.php');

class ScmActionException extends Exception {}

class ScmAction {
    
    const SCM_ACTION_LABEL = ':by scmfviz:';
    
    public function deliverPreAlphaBuild($params = array()) {
        if(!isset($params['supportBranch'])) return false;
        $support_branch = $params['supportBranch'];
        $svnreader = new RepositoryReader;
        $svntree = $svnreader->readRepoStructure();
        $filtered_builds = array();
        foreach($svntree['tags']['builds'] as $maturity => $builds) {
            if(is_array($builds)) {
                foreach($builds as $build) {
                    if(Version::isParent($support_branch, $build)) {
                        $filtered_builds[] = $build;
                    }
                }
            }
        }
        usort($filtered_builds, array('Version', 'cmp'));
        if(!empty($filtered_builds)) {
            $latestVersion = array_slice($filtered_builds, -1);
            $latestVersion = $latestVersion[0];
            $nextBuildVersion = Version::incrementVersion($latestVersion);
        } else {
            $nextBuildVersion = Version::createFirstBuildVersion($support_branch);
        }
        $svntree = $svnreader->readRepoStructure();
        $message = "Created pre-alpha build $nextBuildVersion ".self::SCM_ACTION_LABEL; 
        if(is_array($svntree['branches']['support']) 
           && in_array($support_branch, $svntree['branches']['support'])) {
            SvnWrapper::singleton()->cp(
                '/branches/support/'.$support_branch,
                '/tags/builds/PA/'.$nextBuildVersion,
                $message
            );
        } else {
            SvnWrapper::singleton()->cp(
                '/trunk',
                '/tags/builds/PA/'.$nextBuildVersion,
                $message
            );
        }
        return $nextBuildVersion;
    }
    
    public function promoteToAlpha($params = array()) {
        if(!isset($params['build'])) return false;
        $build = $params['build'];
        $nextBuildVersion = Version::incrementVersion($build);
        $svnreader = new RepositoryReader;
        $svntree = $svnreader->readRepoStructure();
        $message = "Created alpha build $nextBuildVersion ".self::SCM_ACTION_LABEL; 
        if(is_array($svntree['branches']['support']) 
           && in_array(Version::getParent($nextBuildVersion), $svntree['branches']['support'])) {
            SvnWrapper::singleton()->cp(
                '/branches/support/'.Version::getParent($nextBuildVersion),
                '/tags/builds/A/'.$nextBuildVersion,
                $message
            );
        } else {
            SvnWrapper::singleton()->cp(
                '/trunk',
                '/tags/builds/A/'.$nextBuildVersion,
                $message
            );
        }
        return $nextBuildVersion;
    }
    
    public function promoteToBeta($params = array()) {
        if(!isset($params['build'])) return false;
        $build = $params['build'];
        $nextBuildVersion = Version::incrementVersion($build);
        $svnreader = new RepositoryReader;
        $svntree = $svnreader->readRepoStructure();
        $message = "Created beta build $nextBuildVersion ".self::SCM_ACTION_LABEL; 
        if(is_array($svntree['branches']['support']) 
           && in_array(Version::getParent($nextBuildVersion), $svntree['branches']['support'])) {
            SvnWrapper::singleton()->cp(
                '/branches/support/'.Version::getParent($nextBuildVersion),
                '/tags/builds/B/'.$nextBuildVersion,
                $message    
            );
        } else {
            SvnWrapper::singleton()->cp(
                '/trunk',
                '/tags/builds/B/'.$nextBuildVersion,
                $message    
            );
        }
        return $nextBuildVersion;
    }
    
    public function deliverAlphaRelease($params = array()) {
        if(!isset($params['releaseBranch'])) return false;
        $release_branch = $params['releaseBranch'];
        $svnreader = new RepositoryReader;
        $svntree = $svnreader->readRepoStructure();
        $filtered_releases = array();
        foreach($svntree['tags']['releases'] as $maturity => $releases) {
            if(is_array($releases)) {
                foreach($releases as $release) {
                    if(Version::isParent($release_branch, $release)) {
                        $filtered_releases[] = $release;
                    }
                }
            }
        }
        usort($filtered_releases, array('Version', 'cmp'));
        if(!empty($filtered_releases)) {
            $latestVersion = array_slice($filtered_releases, -1);
            $latestVersion = $latestVersion[0];
            $nextReleaseVersion = Version::incrementVersion($latestVersion);
        } else {
            $nextReleaseVersion = Version::createFirstBuildVersion($release_branch);
        }
        $message = "Created alpha-release $nextReleaseVersion ".self::SCM_ACTION_LABEL; 
        SvnWrapper::singleton()->cp(
            '/branches/release/'.$release_branch,
            '/tags/releases/AR/'.$nextReleaseVersion,
            $message
        );
        return $nextReleaseVersion;
    }
    
    public function promoteToBetaRelease($params = array()) {
        if(!isset($params['release'])) return false;
        $release = $params['release'];
        $nextReleaseVersion = Version::incrementVersion($release);
        $message = "Created beta-release $nextReleaseVersion ".self::SCM_ACTION_LABEL; 
        SvnWrapper::singleton()->cp(
            '/branches/release/'.Version::getParent($nextReleaseVersion),
            '/tags/releases/BR/'.$nextReleaseVersion,
            $message
        );
        return $nextReleaseVersion;
    }
    
    public function promoteToReleaseCandidate($params = array()) {
        if(!isset($params['release'])) return false;
        $release = $params['release'];
        $nextReleaseVersion = Version::incrementVersion($release);
        $message = "Created release-candidate $nextReleaseVersion ".self::SCM_ACTION_LABEL; 
        SvnWrapper::singleton()->cp(
            '/branches/release/'.Version::getParent($nextReleaseVersion),
            '/tags/releases/RC/'.$nextReleaseVersion,
            $message
        );
        return $nextReleaseVersion;
    }
    
    public function promoteToStableRelease($params = array()) {
        if(!isset($params['release'])) return false;
        $release = $params['release'];
        $nextReleaseVersion = Version::incrementVersion($release);
        $message = "Created stable release $nextReleaseVersion ".self::SCM_ACTION_LABEL; 
        SvnWrapper::singleton()->cp(
            '/branches/release/'.Version::getParent($nextReleaseVersion),
            '/tags/releases/ST/'.$nextReleaseVersion,
            $message
        );
        return $nextReleaseVersion;
    }
    
    public function createSupportBranch($params = array()) {
        $svnreader = new RepositoryReader;
        $svntree = $svnreader->readRepoStructure();
        $support_branches = $svntree['branches']['support'];
        if(!empty($support_branches)) {
            usort($support_branches, array('Version', 'cmp'));
            $latestVersion = array_slice($support_branches, -1);
            $latestVersion = $latestVersion[0];
            $nextVersion = Version::incrementVersion($latestVersion);
        } else {
            $nextVersion = Version::STARTING_SUPPORT_VERSION;
        }
        $message = "Created support branch $nextVersion ".self::SCM_ACTION_LABEL; 
        SvnWrapper::singleton()->cp(
            '/trunk',
            '/branches/support/'.$nextVersion,
            $message
        );
        return $nextVersion;
    }
    
    public function createReleaseBranch($params = array()) {
        if(!isset($params['supportBranch'])) return false;
        $support_branch = $params['supportBranch'];
        $svnreader = new RepositoryReader;
        $svntree = $svnreader->readRepoStructure();
        $filtered_branches= array();
        if(is_array($svntree['branches']['release'])) {
            foreach($svntree['branches']['release'] as $release_branch) {
                if(Version::isParent($support_branch, $release_branch)) {
                    $filtered_branches[] = $release_branch;
                }
            }
        }
        if(!empty($filtered_branches)) {
            usort($filtered_branches, array('Version', 'cmp'));
            $latestVersion = array_slice($filtered_branches, -1);
            $latestVersion = $latestVersion[0];
            $nextVersion = Version::incrementVersion($latestVersion);
        } else {
            $nextVersion = Version::createFirstBranchVersion($support_branch);
        }
        $message = "Created release branch $nextVersion ".self::SCM_ACTION_LABEL; 
        if(is_array($svntree['branches']['support']) 
           && in_array($support_branch, $svntree['branches']['support'])) {
            SvnWrapper::singleton()->cp(
                '/branches/support/'.$support_branch,
                '/branches/release/'.$nextVersion,
                $message
            );
        } else {
            SvnWrapper::singleton()->cp(
                '/trunk',
                '/branches/release/'.$nextVersion,
                $message
            );
        }
        return $nextVersion;
    }
    
    public function createExperimentalBranch($params = array()) {
        if(!isset($params['supportBranch'])) return false;
        if(!isset($params['experimentalName'])) return false;
        $support_branch = $params['supportBranch'];
        $experimental_name = $params['experimentalName'];
        $svnreader = new RepositoryReader;
        $svntree = $svnreader->readRepoStructure();
        $message = 'Created parent directory for experimental branch';
        if(!isset($svntree['branches']['experimental'][$support_branch])) {
            SvnWrapper::singleton()->mkdir('/branches/experimental/'.$support_branch, $message);
        }
        $message = "Created experimental branch $experimental_name from $support_branch ".self::SCM_ACTION_LABEL; 
        if(is_array($svntree['branches']['support']) 
            && in_array($support_branch, $svntree['branches']['support'])) {
            SvnWrapper::singleton()->cp(
                '/branches/support/'.$support_branch,
                '/branches/experimental/'.$support_branch.'/'.$experimental_name,
                $message
            );
        } else {
            SvnWrapper::singleton()->cp(
                '/trunk',
                '/branches/experimental/'.$support_branch.'/'.$experimental_name,
                $message
            );
        }
        return $experimental_name;
    }
    
    private function convertStatusesToPaths($statuses) {
        $paths = array();
        foreach($statuses as $path => $status) {
            if(is_array($status)) {
                $child_paths = $this->convertStatusesToPaths($status);
                $paths[] = $path;
                foreach($child_paths as $child_path) {
                    $paths[] = "$path/$child_path";
                }
            } elseif (!$status) {
                $paths[] = $path;
            }
        }
        return $paths;
    }
    
    public function initRepo() {
        $reader = new RepositoryReader();
        $directoriesStatus = $reader->getDirectoriesStatus();
        $paths = $this->convertStatusesToPaths($directoriesStatus);
        if(is_array($paths)) {
            foreach($paths as $path) {
                $path = $this->url.'/'.rtrim($path, '/\ ');
                try {
                    SvnWrapper::singleton()->mkdir($path, "Intializing repository structure, ". $path. ' directory '.self::SCM_ACTION_LABEL);
                } catch (SvnException $e) {
                    $errors = $e->getMessage();
                    if(strpos($errors, 'already exists') === false 
                       && strpos($errors, 'Server sent unexpected return value (405 Method Not Allowed) in response to MKCOL request') === false) {
                        throw new SvnException($errors);
                    }
                }
                
            }
        } 
        return 'OK';
    }    
}

?>
