<?php

/* vim: set expandtab tabstop=4 shiftwidth=4: */

/** +----------------------------------------------------------------------+
 * | PHP version 5                                                        |
 * +----------------------------------------------------------------------+
 * | Copyright (c) 2004-2007, Clay Loveless                               |
 * | All rights reserved.                                                 |
 * +----------------------------------------------------------------------+
 * | This LICENSE is in the BSD license style.                            |
 * | http: *www.opensource.org/licenses/bsd-license.php                   |
 * |                                                                      |
 * | Redistribution and use in source and binary forms, with or without   |
 * | modification, are permitted provided that the following conditions   |
 * | are met:                                                             |
 * |                                                                      |
 * |  * Redistributions of source code must retain the above copyright    |
 * |    notice, this list of conditions and the following disclaimer.     |
 * |                                                                      |
 * |  * Redistributions in binary form must reproduce the above           |
 * |    copyright notice, this list of conditions and the following       |
 * |    disclaimer in the documentation and/or other materials provided   |
 * |    with the distribution.                                            |
 * |                                                                      |
 * |  * Neither the name of Clay Loveless nor the names of contributors   |
 * |    may be used to endorse or promote products derived from this      |
 * |    software without specific prior written permission.               |
 * |                                                                      |
 * | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS  |
 * | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT    |
 * | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS    |
 * | FOR A PARTICULAR PURPOSE ARE DISCLAIMED.  IN NO EVENT SHALL THE      |
 * | COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,  |
 * | INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, |
 * | BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;     |
 * | LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER     |
 * | CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT   |
 * | LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN    |
 * | ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE      |
 * | POSSIBILITY OF SUCH DAMAGE.                                          |
 * +----------------------------------------------------------------------+
 * | Author: Clay Loveless <clay@killersoft.com>                          |
 * +----------------------------------------------------------------------+
 *
 * @category VersionControl
 * @package  VersionControl_SVN
 * @author   Clay Loveless <clay@killersoft.com>
 * @author   Michiel Rook <mrook@php.net>
 * @license  http://www.killersoft.com/LICENSE.txt BSD License
 * @version  SVN: $Id: Update.php 314427 2011-08-07 14:33:28Z mrook $
 * @link     http://pear.php.net/package/VersionControl_SVN
 */

/**
 * Subversion Update command manager class
 *
 * Bring changes from the repository into the working copy.
 * 
 * From 'svn update --help':
 *
 * usage: update [PATH...]
 * 
 *   If no revision given, bring working copy up-to-date with HEAD rev.
 *   Else synchronize working copy to revision given by -r.
 * 
 *   For each updated item a line will start with a character reporting the
 *   action taken.  These characters have the following meaning:
 * 
 *     A  Added
 *     D  Deleted
 *     U  Updated
 *     C  Conflict
 *     G  Merged
 * 
 *   A character in the first column signifies an update to the actual file,
 *   while updates to the file's properties are shown in the second column.
 *
 * Conversion of the above usage example to VersionControl_SVN_Switch:
 *
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
 * $switches = array('r' => '30');
 * $args = array('/path/to/working/copy');
 *
 * $svn = VersionControl_SVN::factory(array('update'), $options);
 * print_r($svn->update->run($args));
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
 *  'N'             =>  true|false,
 *                      // operate on single directory only
 *  'non-recursive' =>  true|false,
 *                      // operate on single directory only
 *  'q [quiet]'     =>  true|false,
 *                      // print as little as possible
 *  'diff3-cmd'     =>  'ARG',
 *                      // use ARG as merge command
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
 * @category VersionControl
 * @package  VersionControl_SVN
 * @author   Clay Loveless <clay@killersoft.com>
 * @author   Michiel Rook <mrook@php.net>
 * @license  http://www.killersoft.com/LICENSE.txt BSD License
 * @version  0.4.0
 * @link     http://pear.php.net/package/VersionControl_SVN
 */
class VersionControl_SVN_Update extends VersionControl_SVN
{
    /**
     * Valid switches for svn update
     *
     * @var     array
     */
    public $valid_switches = array('r',
                                'revision',
                                'N',
                                'non-recursive',
                                'non_recursive',
                                'q',
                                'quiet',
                                'diff3-cmd',
                                'username',
                                'password',
                                'ignore-externals',
                                'no-auth-cache',
                                'no_auth_cache',
                                'non-interactive',
                                'non_interactive',
                                'config-dir',
                                'config_dir',
                                'force',
                                'changelist'
                                );

    
    /**
     * Command-line arguments that should be passed 
     * <b>outside</b> of those specified in {@link switches}.
     *
     * @var     array
     */
    public $args = array();
    
    /**
     * Minimum number of args required by this subcommand.
     * See {@link http://svnbook.red-bean.com/svnbook/ Version Control with Subversion}, 
     * Subversion Complete Reference for details on arguments for this subcommand.
     * @var     int
     */
    public $min_args = 0;
    
    /**
     * Switches required by this subcommand.
     * See {@link http://svnbook.red-bean.com/svnbook/ Version Control with Subversion}, 
     * Subversion Complete Reference for details on arguments for this subcommand.
     * @var     array
     */
    public $required_switches = array();
    
    /**
     * Use exec or passthru to get results from command.
     * @var     bool
     */
    public $passthru = false;
    
    /**
     * Prepare the svn subcommand switches.
     *
     * Defaults to non-interactive mode, and will auto-set the 
     * --xml switch (if available) if $fetchmode is set to 
     * VERSIONCONTROL_SVN_FETCHMODE_XML, VERSIONCONTROL_SVN_FETCHMODE_ASSOC
     * or VERSIONCONTROL_SVN_FETCHMODE_OBJECT
     *
     * @return boolean true on success, false on failure. Check PEAR_ErrorStack
     *                 for error details, if any.
     */
    public function prepare()
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
                case 'changelist':
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
                case 'non-recursive':
                case 'non-interactive':
                case 'no-auth-cache':
                case 'ignore-externals':
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
            
            $this->_stack->push(
                VERSIONCONTROL_SVN_NOTICE_INVALID_SWITCH,
                'notice',
                $params
            );
        }
        return true;
    }
    
    // }}}
    // {{{ parseOutput()
    
    /**
     * Handles output parsing of standard and verbose output of command.
     *
     * @param array $out Array of output captured by exec command in {@link run}.
     *
     * @return mixed Returns output requested by fetchmode (if available), 
     *               or raw output if desired fetchmode is not available.
     */
    public function parseOutput($out)
    {
        $fetchmode = $this->fetchmode;
        switch($fetchmode) {
        case VERSIONCONTROL_SVN_FETCHMODE_RAW:
            return join("\n", $out);
            break;
        case VERSIONCONTROL_SVN_FETCHMODE_ARRAY:
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
     * @param array $out Array of output captured by exec command in {@link run}.
     *
     * @return mixed
     * @todo Finish this method! : )
     */
    function parseOutputArray($out)
    {
        $parsed = array();
    }
}

// }}}
?>
