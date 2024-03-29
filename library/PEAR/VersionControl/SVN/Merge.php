<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP version 5                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 2004-2007, Clay Loveless                               |
// | All rights reserved.                                                 |
// +----------------------------------------------------------------------+
// | This LICENSE is in the BSD license style.                            |
// | http://www.opensource.org/licenses/bsd-license.php                   |
// |                                                                      |
// | Redistribution and use in source and binary forms, with or without   |
// | modification, are permitted provided that the following conditions   |
// | are met:                                                             |
// |                                                                      |
// |  * Redistributions of source code must retain the above copyright    |
// |    notice, this list of conditions and the following disclaimer.     |
// |                                                                      |
// |  * Redistributions in binary form must reproduce the above           |
// |    copyright notice, this list of conditions and the following       |
// |    disclaimer in the documentation and/or other materials provided   |
// |    with the distribution.                                            |
// |                                                                      |
// |  * Neither the name of Clay Loveless nor the names of contributors   |
// |    may be used to endorse or promote products derived from this      |
// |    software without specific prior written permission.               |
// |                                                                      |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS  |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT    |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS    |
// | FOR A PARTICULAR PURPOSE ARE DISCLAIMED.  IN NO EVENT SHALL THE      |
// | COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,  |
// | INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, |
// | BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;     |
// | LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER     |
// | CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT   |
// | LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN    |
// | ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE      |
// | POSSIBILITY OF SUCH DAMAGE.                                          |
// +----------------------------------------------------------------------+
// | Author: Clay Loveless <clay@killersoft.com>                          |
// +----------------------------------------------------------------------+
//
// $Id: Merge.php 286753 2009-08-03 19:37:03Z mrook $
//

/**
 * @package     VersionControl_SVN
 * @category    VersionControl
 * @author      Clay Loveless <clay@killersoft.com>
 */

/**
 * Subversion Merge command manager class
 *
 * Apply the differences between two sources to a working copy path.
 * 
 * From 'svn merge --help':
 *
 * usage: 1. merge sourceURL1[@N] sourceURL2[@M] [WCPATH]
 *        2. merge sourceWCPATH1@N sourceWCPATH2@M [WCPATH]
 *        3. merge -r N:M SOURCE [WCPATH]
 * 
 *   1. In the first form, the source URLs are specified at revisions
 *      N and M.  These are the two sources to be compared.  The revisions
 *      default to HEAD if omitted.
 * 
 *   2. In the second form, the URLs corresponding to the source working
 *      copy paths define the sources to be compared.  The revisions must
 *      be specified.
 * 
 *   3. In the third form, SOURCE can be a URL, or working copy item
 *      in which case the corresponding URL is used.  This URL, at
 *      revisions N and M, defines the two sources to be compared.
 * 
 *   WCPATH is the working copy path that will receive the changes.
 *   If WCPATH is omitted, a default value of '.' is assumed, unless
 *   the sources have identical basenames that match a file within '.':
 *   in which case, the differences will be applied to that file.
 *
 * Conversion of the above usage examples to VersionControl_SVN_Merge:
 *
 * Example 1:
 * <code>
 * <?php
 * require_once 'VersionControl/SVN.php';
 *
 * // Setup error handling -- always a good idea!
 * $svnstack = &PEAR_ErrorStack::singleton('VersionControl_SVN');
 *
 * // Set up runtime options. Will be passed to all 
 * // subclasses.
 * $options = array('fetchmode' => VERSIONCONTROL_SVN_FETCHMODE_RAW);
 *
 * $args = array(
 *  'svn://svn.example.com/repos/TestProj/trunk/example.php@4',   // sourceurl1
 *  'svn://svn.example.com/repos/TestProj/branch/example.php@15', // sourceurl2
 *  '/path/to/working/copy'                                       // wcpath
 * );
 *
 * $svn = VersionControl_SVN::factory(array('merge'), $options);
 * print_r($svn->merge->run($args));
 *
 * if (count($errs = $svnstack->getErrors())) { 
 *     foreach ($errs as $err) {
 *         echo '<br />'.$err['message']."<br />\n";
 *         echo "Command used: " . $err['params']['cmd'];
 *     }
 * }
 * ?>
 * </code>
 *
 * Example 2:
 * <code>
 * <?php
 * require_once 'VersionControl/SVN.php';
 *
 * // Setup error handling -- always a good idea!
 * $svnstack = &PEAR_ErrorStack::singleton('VersionControl_SVN');
 *
 * // Set up runtime options. Will be passed to all 
 * // subclasses.
 * $options = array('fetchmode' => VERSIONCONTROL_SVN_FETCHMODE_RAW);
 *
 * $args = array(
 *  '/path/to/working/copy/trunk/example.php@4',    // wcpath1
 *  '/path/to/working/copy/branch/example.php@15'   // wcpath2
 * );
 *
 * $svn = VersionControl_SVN::factory(array('merge'), $options);
 * print_r($svn->merge->run($args));
 *
 * if (count($errs = $svnstack->getErrors())) { 
 *     foreach ($errs as $err) {
 *         echo '<br />'.$err['message']."<br />\n";
 *         echo "Command used: " . $err['params']['cmd'];
 *     }
 * }
 * ?>
 * </code>
 *
 * Example 3:
 * <code>
 * <?php
 * require_once 'VersionControl/SVN.php';
 *
 * // Setup error handling -- always a good idea!
 * $svnstack = &PEAR_ErrorStack::singleton('VersionControl_SVN');
 *
 * // Set up runtime options. Will be passed to all 
 * // subclasses.
 * $options = array('fetchmode' => VERSIONCONTROL_SVN_FETCHMODE_RAW);
 *
 * $switches = array('r' => '5:8');
 * $args = array('svn://svn.example.com/repos/TestProj/trunk/example.php');
 *
 * $svn = VersionControl_SVN::factory(array('merge'), $options);
 * print_r($svn->merge->run($args, $switches));
 *
 * if (count($errs = $svnstack->getErrors())) { 
 *     foreach ($errs as $err) {
 *         echo '<br />'.$err['message']."<br />\n";
 *         echo "Command used: " . $err['params']['cmd'];
 *     }
 * }
 * ?>
 * </code>
 *
 * $switches is an array containing one or more command line options
 * defined by the following associative keys:
 *
 * <code>
 *
 * $switches = array(
 *  'r [revision]'  =>  'ARG (some commands also take ARG1:ARG2 range)
 *                        A revision argument can be one of:
 *                           NUMBER       revision number
 *                           "{" DATE "}" revision at start of the date
 *                           "HEAD"       latest in repository
 *                           "BASE"       base rev of item's working copy
 *                           "COMMITTED"  last commit at or before BASE
 *                           "PREV"       revision just before COMMITTED',
 *                      // either 'r' or 'revision' may be used
 *  'q [quiet]'     =>  true|false,
 *                      // print as little as possible
 *  'dry-run'       =>  true|false,
 *                      // try operation but make no changes
 *  'force'         =>  true|false,
 *                      // force operation to run
 *  'N'             =>  true|false,
 *                      // operate on single directory only
 *  'non-recursive' =>  true|false,
 *                      // operate on single directory only
 *  'diff3-cmd'     =>  'ARG',
 *                      // use ARG as merge command
 *  'ignore-ancestry' => true|false,
 *                      // ignore ancestry when calculating merges
 *  'username'      =>  'Subversion repository login',
 *  'password'      =>  'Subversion repository password',
 *  'no-auth-cache' =>  true|false,
 *                      // Do not cache authentication tokens
 *  'config-dir'    =>  'Path to a Subversion configuration directory'
 * );
 *
 * </code>
 *
 * Note: Subversion does not offer an XML output option for this subcommand
 *
 * The non-interactive option available on the command-line 
 * svn client may also be set (true|false), but it is set to true by default.
 *
 *
 * @package  VersionControl_SVN
 * @version  0.4.0
 * @category SCM
 * @author   Clay Loveless <clay@killersoft.com>
 */
class VersionControl_SVN_Merge extends VersionControl_SVN
{
    /**
     * Valid switches for svn merge
     *
     * @var     array
     * @access  public
     */
    var $valid_switches = array('r',
                                'revision',
                                'N',
                                'non-recursive',
                                'non_recursive',
                                'q',
                                'quiet',
                                'force',
                                'dry-run',
                                'dry_run',
                                'diff3-cmd',
                                'ignore-ancestry',
                                'ignore_ancestry',
                                'username',
                                'password',
                                'no-auth-cache',
                                'no_auth_cache',
                                'non-interactive',
                                'non_interactive',
                                'config-dir',
                                'config_dir'
                                );

    
    /**
     * Command-line arguments that should be passed 
     * <b>outside</b> of those specified in {@link switches}.
     *
     * @var     array
     * @access  public
     */
    var $args = array();
    
    /**
     * Minimum number of args required by this subcommand.
     * See {@link http://svnbook.red-bean.com/svnbook/ Version Control with Subversion}, 
     * Subversion Complete Reference for details on arguments for this subcommand.
     * @var     int
     * @access  public
     */
    var $min_args = 1;
    
    /**
     * Switches required by this subcommand.
     * See {@link http://svnbook.red-bean.com/svnbook/ Version Control with Subversion}, 
     * Subversion Complete Reference for details on arguments for this subcommand.
     * @var     array
     * @access  public
     */
    var $required_switches = array();
    
    /**
     * Use exec or passthru to get results from command.
     * @var     bool
     * @access  public
     */
    var $passthru = false;
    
    /**
     * Prepare the svn subcommand switches.
     *
     * Defaults to non-interactive mode, and will auto-set the 
     * --xml switch (if available) if $fetchmode is set to VERSIONCONTROL_SVN_FETCHMODE_XML,
     * VERSIONCONTROL_SVN_FETCHMODE_ASSOC or VERSIONCONTROL_SVN_FETCHMODE_OBJECT
     *
     * @param   void
     * @return  int    true on success, false on failure. Check PEAR_ErrorStack
     *                 for error details, if any.
     */
    function prepare()
    {
        $meets_requirements = $this->checkCommandRequirements();
        if (!$meets_requirements) {
            return false;
        }
        
        $valid_switches     = $this->valid_switches;
        $switches           = $this->switches;
        $args               = $this->args;
        $fetchmode          = $this->fetchmode;
        $invalid_switches   = array();
        $_switches          = '';
        
        foreach ($switches as $switch => $val) {
            if (in_array($switch, $valid_switches)) {
                $switch = str_replace('_', '-', $switch);
                switch ($switch) {
                    case 'revision':
                    case 'username':
                    case 'password':
                    case 'diff3-cmd':
                    case 'config-dir':
                        $_switches .= "--$switch $val ";
                        break;
                    case 'r':
                        $_switches .= "-$switch $val ";
                        break;
                    case 'N':
                    case 'q':
                        if ($val === true) {
                            $_switches .= "-$switch ";
                        }
                        break;
                    case 'force':
                    case 'dry-run':
                    case 'non-recursive':
                    case 'non-interactive':
                    case 'ignore-ancestry':
                    case 'no-auth-cache':
                        if ($val === true) {
                            $_switches .= "--$switch ";
                        }
                        break;
                    default:
                        // that's all, folks!
                        break;
                }
            } else {
                $invalid_switches[] = $switch;
            }
        }
        // We don't want interactive mode
        if (strpos($_switches, 'non-interactive') === false) {
            $_switches .= '--non-interactive ';
        }
        $_switches = trim($_switches);
        $this->_switches = $_switches;

        $cmd = "$this->svn_path $this->_svn_cmd $_switches";
        if (!empty($args)) {
            $cmd .= ' '. join(' ', $args);
        }

        $this->_prepped_cmd = $cmd;
        $this->prepared = true;

        $invalid = count($invalid_switches);
        if ($invalid > 0) {
            $params['was'] = 'was';
            $params['is_invalid_switch'] = 'is an invalid switch';
            if ($invalid > 1) {
                $params['was'] = 'were';
                $params['is_invalid_switch'] = 'are invalid switches';
            }
            $params['list'] = $invalid_switches;
            $params['switches'] = $switches;
            $params['_svn_cmd'] = ucfirst($this->_svn_cmd);
            $this->_stack->push(VERSIONCONTROL_SVN_NOTICE_INVALID_SWITCH, 'notice', $params);
        }
        return true;
    }
    
    // }}}
    // {{{ parseOutput()
    
    /**
     * Handles output parsing of standard and verbose output of command.
     *
     * @param   array   $out    Array of output captured by exec command in {@link run}.
     * @return  mixed   Returns output requested by fetchmode (if available), or raw output
     *                  if desired fetchmode is not available.
     * @access  public
     */
    function parseOutput($out)
    {
        $fetchmode = $this->fetchmode;
        switch($fetchmode) {
            case VERSIONCONTROL_SVN_FETCHMODE_RAW:
                return join("\n", $out);
                break;
            case VERSIONCONTROL_SVN_FETCHMODE_ASSOC:
                // Temporary, see parseOutputArray below
                return join("\n", $out);
                break;
            case VERSIONCONTROL_SVN_FETCHMODE_OBJECT:
                // Temporary, will return object-ified array from
                // parseOutputArray
                return join("\n", $out);
                break;
            case VERSIONCONTROL_SVN_FETCHMODE_XML:
                // Temporary, will eventually build an XML string
                // with XML_Util or XML_Tree
                return join("\n", $out);
                break;
            default:
                // What you get with VERSIONCONTROL_SVN_FETCHMODE_DEFAULT
                return join("\n", $out);
                break;
        }
    }
    
    /**
     * Helper method for parseOutput that parses output into an associative array
     *
     * @todo Finish this method! : )
     */
    function parseOutputArray($out)
    {
        $parsed = array();
    }
}

// }}}
?>