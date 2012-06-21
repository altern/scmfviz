<?php

/**
 *  Class contains methods for the purpose of version manipulations according 
 *  to SCMF principles and approaches
 */

class Version {
    
    const MAJOR_PLACEHOLDER = 'N';
    const MINOR_PLACEHOLDER = 'M';
    const BUILD_PLACEHOLDER = 'K';
    const ANY_PLACEHOLDER = '?';
    const PLACEHOLDER = 'x';
    const SEPARATOR = '.';
    const SUPPORT_BRANCH = 'sup';
    const RELEASE_BRANCH = 'rel';
    const EXPERIMENTAL_BRANCH = 'exp';
    const RELEASE = 'release';
    const BUILD = 'build';
    
    const PA = 'PA';
    const A = 'A';
    const B = 'B';
    const AR = 'AR';
    const BR = 'BR';
    const RC = 'RC';
    const ST = 'ST';
    
    const STARTING_SUPPORT_VERSION = '0.x.x';
    
    public static $REPO_DIRECTORIES = array(
        'trunk' => false,
        'branches' => array(
            'experimental' => false,
            'support' => false,
            'release' => false
        ),
        'tags' => array(
            'builds' => array(
                self::PA => false,
                self::A => false,
                self::B => false
            ),
            'releases' => array(
                self::AR => false,
                self::BR => false,
                self::RC => false,
                self::ST => false
            )
        )
    );
    /**
     * Major version number
     * @var string 
     */
    private $N;
    
    /** 
     * Release version number
     * @var string 
     */
    private $M;
    
    /**
     * Build version number
     * @var string  
     */
    private $K;
    
    public function __construct($version) {
        list($this->N, $this->M, $this->K) = explode(self::SEPARATOR, $version);
    }
    
    public function __toString() {
        return implode(self::SEPARATOR, array($this->N, $this->M, $this->K));
    }
    
    public function __get($name) {
        return $this->$name;
    }
    
    public function __set($name, $value) {
        return $this->$name = $value;
    }
    
    /**
     * Method analyzes version by its compounds for the purpose of detecting 
     * what entity (branches, builds, releases) it represents
     * @return string 
     */
    public function getType() {
        if((is_numeric($this->N) || $this->N == self::MAJOR_PLACEHOLDER) 
            && $this->M == self::PLACEHOLDER 
            && $this->K == self::PLACEHOLDER) 
            return self::SUPPORT_BRANCH;
        if((is_numeric($this->N) || $this->N == self::MAJOR_PLACEHOLDER) 
            && (is_numeric($this->M) || $this->M == self::MINOR_PLACEHOLDER) 
            && $this->K == self::PLACEHOLDER) 
            return self::RELEASE_BRANCH;
        if((is_numeric($this->N) || $this->N == self::MAJOR_PLACEHOLDER) 
            && $this->M == self::PLACEHOLDER 
            && (is_numeric($this->K) || $this->K == self::BUILD_PLACEHOLDER)) 
            return self::BUILD;
        if((is_numeric($this->N) || $this->N == self::MAJOR_PLACEHOLDER)
            && (is_numeric($this->M) || $this->M == self::MINOR_PLACEHOLDER)
            && (is_numeric($this->K) || $this->K == self::BUILD_PLACEHOLDER)) 
            return self::RELEASE;
    }
    
    /**
     * @return boolean
     */
    public function isSupportBranch() {
        return $this->getType() == self::SUPPORT_BRANCH;
    }
    
    /**
     * @return boolean
     */    
    public function isReleaseBranch() {
        return $this->getType() == self::RELEASE_BRANCH;
    }
    
    /**
     * @return boolean
     */
    public function isBuild() {
        return $this->getType() == self::BUILD;
    }
    
    /**
     * @return boolean
     */
    public function isRelease() {
        return $this->getType() == self::RELEASE;
    }
    
    /**
     * Method compares two versions for the purpose of detecting which one is larger
     * 
     * -1 means < (first one is less than the second one)
     * 0 means == (versions are equal)
     * 1 means > (first one is greater than the second one)
     * 
     * @static
     * @param string $version1
     * @param string $version2
     * @return integer
     * @throws VersionComparisonException 
     */
    public static function cmp($version1, $version2) {
        list($N1, $M1, $K1) = explode(self::SEPARATOR, $version1);
        list($N2, $M2, $K2) = explode(self::SEPARATOR, $version2);
        if($N1 == $N2) {
            if($M1 == $M2) {
                if($K1 == $K2) {
                    return 0;   
                } else if(!is_numeric($K1) || !is_numeric($K2)) {
                    throw new VersionComparisonException(sprintf("Versions %s and %s are not comparable", $version1, $version2));
                } else if($K1 < $K2) {
                    return -1;
                } else if($K1 > $K2) {
                    return 1;
                } 
            } else if(!is_numeric($M1) || !is_numeric($M2)) {
                throw new VersionComparisonException(sprintf("Versions %s and %s are not comparable", $version1, $version2));
            } else if($M1 < $M2) {
                return -1;
            } else if($M1 > $M2) {
                return 1;
            } 
        } else if($N1 < $N2) {
            return -1;
        } else if($N1 > $N2) {
            return 1;
        } 
    }
    
    /**
     * Method detects whether versions are sibling (have the same parent branch
     * they were inherited from)
     * 
     * @static
     * @param type $version1
     * @param type $version2
     * @return type 
     */
    public static function areSiblings($version1, $version2) {
        list($N1, $M1, $K1) = explode(self::SEPARATOR, $version1);
        list($N2, $M2, $K2) = explode(self::SEPARATOR, $version2);
        return ($N1 == $N2 && $M1 == $M2);
    }
    
    /**
     * Method compares two versions for the purpose of detecting whether first one
     * can be considered to be parent of the second one
     * 
     * @static
     * @param string $parent
     * @param string $child
     * @return boolean 
     */
    public static function isParent($parent, $child) {
        $parent_version = new Version($parent);
        $child_version = new Version($child);
        $parent_support_condition = $parent_version->N == $child_version->N 
                && $parent_version->isSupportBranch() 
                && ($child_version->isReleaseBranch() || $child_version->isBuild());
        $parent_release_condition = $parent_version->N == $child_version->N 
                && $parent_version->M == $child_version->M 
                && $parent_version->isReleaseBranch() 
                && $child_version->isRelease();
        return ($parent_support_condition || $parent_release_condition);
    }
    
    /**
     * Method gets version of entity which is considered to be parent for 
     * the entity with supplied version
     *  
     * @static
     * @param string $version_str
     * @return string 
     */
    public static function getParent($version_str) {
        $version = new Version($version_str);
        
        if($version->isSupportBranch()) {
            return '';
        } elseif($version->isReleaseBranch() || $version->isBuild()) {
            $parent_version = new Version(implode(Version::SEPARATOR, array($version->N, Version::PLACEHOLDER, Version::PLACEHOLDER)));
            return $parent_version->__toString();
        } elseif($version->isRelease()) {
            $parent_version = new Version(implode(Version::SEPARATOR, array($version->N, $version->M, Version::PLACEHOLDER)));
            return $parent_version->__toString();
        }
    }
    
    /**
     * Method compares two build maturity levels for the purpose of detecting 
     * which one is 'larger'. Used for sorting purposes 
     * 
     * -1 means < (first one is less than the second one)
     * 0 means == (versions are equal)
     * 1 means > (first one is greater than the second one)
     * 
     * @static
     * @param string $maturity1
     * @param string $maturity2
     * @return integer 
     */
    public static function buildsMaturityCmp($maturity1, $maturity2) {
        if($maturity1 == $maturity2) {
            return 0;
        }
        if($maturity1 == self::PA) return -1;
        if($maturity2 == self::B) return 1;
        if($maturity1 == self::B && $maturity2 == self::A) return 1;
        if($maturity1 == self::A && $maturity2 == self::PA) return 1;
    }
    
    /**
     * Method compares two release maturity levels for the purpose of detecting 
     * which one is 'larger'. Used for sorting purposes
     * 
     * @static
     * @param string $maturity1
     * @param string $maturity2
     * @return integer 
     */
    public static function releasesMaturityCmp($maturity1, $maturity2) {
        if($maturity1 == $maturity2) {
            return 0;
        }
        if($maturity1 == self::AR) return -1;
        if($maturity2 == self::ST) return 1;
        if($maturity1 == self::BR && $maturity2 == self::AR) return 1;
        if($maturity1 == self::BR && $maturity2 == self::RC) return -1;
//      if($maturity1 == self::BR && $maturity2 == self::ST) return -1;
        if($maturity1 == self::RC && $maturity2 == self::AR) return 1;
        if($maturity1 == self::RC && $maturity2 == self::BR) return 1;
//      if($maturity1 == self::RC && $maturity2 == self::ST) return 1;
        if($maturity1 == self::ST && $maturity2 == self::AR) return 1;
        if($maturity1 == self::ST && $maturity2 == self::BR) return 1;
        if($maturity1 == self::ST && $maturity2 == self::RC) return 1;
    }
    
    /**
     * Method performs version increment in order to get next version 
     * after the supplied one
     * 
     * @static
     * @param string $v
     * @return string 
     */
    public static function incrementVersion($v) {
        $version = new Version($v);
        if($version->isSupportBranch()) {
            ++$version->N;
        } elseif ($version->isReleaseBranch()) {
            ++$version->M;
        } elseif ($version->isBuild() || $version->isRelease()) {
            ++$version->K;
        }
        return $version->__toString();
    }
    
    public static function createFirstBuildVersion($branch) {
        $version = new Version($branch);
        if($version->isSupportBranch() || $version->isReleaseBranch()) {
            if($version->N == 0 && ($version->M == 0 || $version->M == Version::PLACEHOLDER)) {
                $version->K = 1;
            } else {
                $version->K = 0;
            }
        } 
        return $version->__toString();
    }
    
    public static function createFirstBranchVersion($branch) {
        $version = new Version($branch);
        if($version->isSupportBranch()) {
            if($version->N == 0) {
                $version->M = 1;
            } else {
                $version->M = 0;
            }
        } 
        return $version->__toString();
    }
    
    public static function correspondsToPattern($full_version, $pattern) {
        $version_arr = explode('/', $full_version);
        if(count($version_arr) > 1) {
            list($maturity, $version_str) = $version_arr;
        } else {
            $maturity = '';
            $version_str = $full_version;
        }
        $pattern_arr = explode('/', $pattern);
        if(count($pattern_arr) > 1) {
            list($pattern_maturity, $pattern_version_str) = $pattern_arr;
        } else {
            $pattern_maturity = '';
            $pattern_version_str = $pattern;
        }
        if(!empty($maturity) && $maturity != $pattern_maturity) {
            return false;
        }
        $version = new Version($version_str);
        $pattern_version = new Version($pattern_version_str);
        return (
               is_numeric($version->N) && $pattern_version->N == self::MAJOR_PLACEHOLDER
            && (is_numeric($version->M) && $pattern_version->M == self::MINOR_PLACEHOLDER 
            || $version->M == self::PLACEHOLDER && $pattern_version->M == self::PLACEHOLDER
            || (is_numeric($version->M) || $version->M == self::PLACEHOLDER) && $pattern_version->M == self::ANY_PLACEHOLDER)
            && (is_numeric($version->K) && $pattern_version->K == self::BUILD_PLACEHOLDER 
            || $version->K == self::PLACEHOLDER && $pattern_version->K == self::PLACEHOLDER 
            || (is_numeric($version->K) || $version->K == self::PLACEHOLDER) && $pattern_version->K == self::ANY_PLACEHOLDER)
        );
    }
    
    public static function categorizeVersions($versions) {
        $categories = array();
        if(is_array($versions)) {
            foreach($versions as $version_str) {
                $version = new Version($version_str);
                switch($version->getType()) {
                    case self::SUPPORT_BRANCH: // N.x.x
                        $categories[] = $version_str;
                        break;
                    case self::RELEASE_BRANCH: // N.M.x
                        $category_pattern = $version->N . self::SEPARATOR . self::MINOR_PLACEHOLDER . self::SEPARATOR . self::PLACEHOLDER;
                        if(!isset($categories[$category_pattern])) {
                            $categories[$category_pattern] = array($version_str);
                        } else {
                            $categories[$category_pattern][] = $version_str;
                        }
                        break;
                    case self::BUILD: // N.x.K
                        $category_pattern = $version->N . self::SEPARATOR . self::PLACEHOLDER . self::SEPARATOR . self::BUILD_PLACEHOLDER;
                        if(!isset($categories[$category_pattern])) {
                            $categories[$category_pattern] = array($version_str);
                        } else {
                            $categories[$category_pattern][] = $version_str;
                        }
                        break;
                    case self::RELEASE: // N.M.K
                        $category_pattern = $version->N . self::SEPARATOR . $version->M . self::SEPARATOR . self::PLACEHOLDER;
                        if(!isset($categories[$category_pattern])) {
                            $categories[$category_pattern] = array($version_str);
                        } else {
                            $categories[$category_pattern][] = $version_str;
                        }
                        break;  
                }
            }
        }
        return $categories;
    }
}
?>
