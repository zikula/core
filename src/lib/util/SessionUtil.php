<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * SessionUtil
 *
 * @package Zikula_Core
 * @subpackage SessionUtil
 */
class SessionUtil
{
    /**
     * Set up session handling
     * Set all PHP options for Zikula session handling
     *
     * @return void
     */
    public static function _setup()
    {
        $path = System::getBaseUri();
        if (empty($path)) {
            $path = '/';
        } elseif (substr($path, -1, 1) != '/') {
            $path .= '/';
        }

        $host = System::serverGetVar('HTTP_HOST');

        if (($pos = strpos($host, ':')) !== false) {
            $host = substr($host, 0, $pos);
        }

        // PHP configuration variables
        ini_set('session.use_trans_sid', 0); // Stop adding SID to URLs
        @ini_set('url_rewriter.tags', ''); // some environments dont allow this value to be set causing an error that prevents installation
        ini_set('session.serialize_handler', 'php'); // How to store data
        ini_set('session.use_cookies', 1); // Use cookie to store the session ID
        ini_set('session.auto_start', 1); // Auto-start session


        ini_set('session.name', self::getCookieName()); // Name of our cookie


        // Set lifetime of session cookie
        $seclevel = System::getVar('seclevel');
        switch ($seclevel) {
            case 'High':
                // Session lasts duration of browser
                $lifetime = 0;
                // Referer check
                // ini_set('session.referer_check', $host.$path);
                ini_set('session.referer_check', $host);
                break;
            case 'Medium':
                // Session lasts set number of days
                $lifetime = System::getVar('secmeddays') * 86400;
                break;
            case 'Low':
            default:
                // Session lasts unlimited number of days (well, lots, anyway)
                // (Currently set to 25 years)
                $lifetime = 788940000;
                break;
        }
        ini_set('session.cookie_lifetime', $lifetime);

        // domain and path settings for session cookie
        // if (System::getVar('intranet') == false) {
        // Cookie path
        ini_set('session.cookie_path', $path);

        // Garbage collection
        ini_set('session.gc_probability', System::getVar('gc_probability'));
        ini_set('session.gc_divisor', 10000);
        ini_set('session.gc_maxlifetime', System::getVar('secinactivemins') * 60); // Inactivity timeout for user sessions

        ini_set('session.hash_function', 1);

        // Set custom session handlers
        ini_set('session.save_handler', 'user');
        if (System::getVar('sessionstoretofile')) {
            ini_set('session.save_path', System::getVar('sessionsavepath'));
        }
        // PHP 5.2 workaround
        if (version_compare(phpversion(), '5.2.0', '>=')) {
            register_shutdown_function('session_write_close');
        }
        // Do not call any of these functions directly.  Marked as private with _
        session_set_save_handler('_SessionUtil__Start', '_SessionUtil__Close', '_SessionUtil__Read', '_SessionUtil__Write',   /* use session_write_close(); */
                                 '_SessionUtil__Destroy', /* use session_destroy(); */
                                 '_SessionUtil__GC');
    }

    /**
     * Initialise session
     *
     * @return bool
     */
    public static function initialize()
    {
        self::_setup();

        // First thing we do is ensure that there is no attempted pollution
        // of the session namespace
        if (ini_get('register_globals')) {
            foreach ($GLOBALS as $k => $v) {
                if (substr($k, 0, 4) == 'ZSV') {
                    return false;
                }
            }
        }

        // create IP finger print
        $current_ipaddr = '';
        $_REMOTE_ADDR = System::serverGetVar('REMOTE_ADDR');
        $_HTTP_X_FORWARDED_FOR = System::serverGetVar('HTTP_X_FORWARDED_FOR');

        if (System::getVar('sessionipcheck')) {
            /* -- feature for after 0.8 release - drak
            // todo - add dropdown option for sessionipcheckmask for /32, /24, /16 CIDR

            $ipmask = System::getVar('sessionipcheckmask');
            if ($ipmask <> 32) {
                // since we're not a /32 we need to handle in case multiple ips returned
                if ($_HTTP_X_FORWARDED_FOR && strstr($_HTTP_X_FORWARDED_FOR, ', ')) {
                    $_ips = explode(', ', $_HTTP_X_FORWARDED_FOR);
                    $_HTTP_X_FORWARDED_FOR = $_ips[0];
                }

                // apply CIDR mask to allow IP checks on clients assigned
                // dynamic IP addresses - e.g. A O *cough* L
                if ($ipmask == 24) {
                    $_REMOTE_ADDR = preg_replace('/[^.]+.$/', '*', $_REMOTE_ADDR);
                    $_HTTP_X_FORWARDED_FOR = ($_HTTP_X_FORWARDED_FOR ? preg_replace('/[^.]+.$/', '*', $_HTTP_X_FORWARDED_FOR) : '');
                } else if ($ipmask == 16) {
                    $_REMOTE_ADDR = preg_replace('/[0-9]*.\.[^.]+.$/', '*', $_REMOTE_ADDR);
                    $_HTTP_X_FORWARDED_FOR = ($_HTTP_X_FORWARDED_FOR ? preg_replace('/[0-9]*.\.[^.]+.$/', '*', $fullhost) : '');
                } else { // must be a /32 CIDR
                    null; // nothing to do
                }
            }
            */
        }

        // create the ip fingerprint
        $current_ipaddr = md5($_REMOTE_ADDR . $_HTTP_X_FORWARDED_FOR);

        // start session check expiry and ip fingerprint if required
        if (session_start() && isset($GLOBALS['_ZSession']['obj']) && $GLOBALS['_ZSession']['obj']) {
            // check if session has expired or not
            $now = time();
            $inactive = ($now - (int) (System::getVar('secinactivemins') * 60));
            $daysold = ($now - (int) (System::getVar('secmeddays') * 86400));
            $lastused = strtotime($GLOBALS['_ZSession']['obj']['lastused']);
            $rememberme = self::getVar('rememberme');
            $uid = $GLOBALS['_ZSession']['obj']['uid'];
            $ipaddr = $GLOBALS['_ZSession']['obj']['ipaddr'];

            // IP check
            if (System::getVar('sessionipcheck', false)) {
                if ($ipaddr !== $current_ipaddr) {
                    session_destroy();
                    return false;
                }
            }

            switch (System::getVar('seclevel')) {
                case 'Low':
                    // Low security - users stay logged in permanently
                    //                no special check necessary
                    break;
                case 'Medium':
                    // Medium security - delete session info if session cookie has
                    // expired or user decided not to remember themself and inactivity timeout
                    // OR max number of days have elapsed without logging back in
                    if ((!$rememberme && $lastused < $inactive) || ($lastused < $daysold) || ($uid == '0' && $lastused < $inactive)) {
                        self::expire();
                    }
                    break;
                case 'High':
                default:
                    // High security - delete session info if user is inactive
                    //if ($rememberme && ($lastused < $inactive)) { // see #427
                    if ($lastused < $inactive) {
                        self::expire();
                    }
                    break;
            }
        } else {
            // *must* regenerate new session otherwise the default sessid will be
            // taken from any session cookie that was submitted (bad bad bad)
            self::regenerate(true);
            self::_createNew(session_id(), $current_ipaddr);
        }

        if (isset($_SESSION['_ZSession']['obj'])) {
            unset($_SESSION['_ZSession']['obj']);
        }

        return true;
    }

    /**
     * Create a new session
     *
     * @access private
     * @param sessid $ the session ID
     * @param ipaddr $ the IP address of the host with this session
     *
     * @return bool
     */
    public static function _createNew($sessid, $ipaddr)
    {
        $now = date('Y-m-d H:i:s', time());
        $obj = array('sessid' => $sessid, 'ipaddr' => $ipaddr, 'uid' => 0, 'lastused' => $now);
        $GLOBALS['_ZSession']['obj'] = $obj;
        $GLOBALS['_ZSession']['new'] = true;
        // Generate a random number, used for some authentication (using prime numer bounds)
        //self::setVar('rand', RandomUtil::getString(32, 40, false, true, true, false, true, true, true));
        // Initialize the array of random values for modules authentication
        self::setVar('rand', array());
        // write hash of useragent into the session for later validation
        self::setVar('useragent', sha1(System::serverGetVar('HTTP_USER_AGENT')));

        // init status & error message arrays
        self::setVar('_ZErrorMsg', array());
        self::setVar('_ZStatusMsg', array());

        return true;
    }

    /**
     * Get a session variable
     *
     * @param sring $name of the session variable to get
     * @param string $default the default value to return if the requested session variable is not set
     * @param autocreate $autocreate whether or not to autocreate the supplied path (optional) (default=true)
     * @param overwriteExistingVar $overwriteExistingVar whether or not to overwrite existing/set variable entries which the given path requires to be arrays (optional) (default=false)
     * @return string session variable requested
     */
    public static function getVar($name, $default = false, $path = '/', $autocreate = true, $overwriteExistingVar = false)
    {
        /* Legacy Handling
         * $lang in session has deprecated and code should use ZLanguage::getLanguageCodeLegacy();
         * if you need the current language code use ZLanguage::getLanguageCode();
         */
        if ($name == 'lang') {
            return ZLanguage::getLanguageCodeLegacy();
        }

        if ($path == '/' || $path === '') {
            if (isset($_SESSION['ZSV' . $name])) {
                return $_SESSION['ZSV' . $name];
            }
        } else {
            $parent = & self::_resolvePath($path, $autocreate, $overwriteExistingVar);
            if ($parent === false) { // path + autocreate or overwriteExistingVar Error
                return false;
            }

            if (isset($parent[$name])) {
                return $parent[$name];
            } else if ($autocreate) {
                $parent[$name] = $default;
            }
        }

        return $default;
    }

    /**
     * Set a session variable
     *
     * @param string $name of the session variable to set
     * @param value $value to set the named session variable
     * @param path $path to traverse to reach the element we wish to return (optional) (default='/')
     * @param autocreate $autocreate whether or not to autocreate the supplied path (optional) (default=true)
     * @param overwriteExistingVar $overwriteExistingVar whether or not to overwrite existing/set variable entries which the given path requires to be arrays (optional) (default=false)
     * @return bool true upon success, false upon failure
     */
    public static function setVar($name, $value, $path = '/', $autocreate = true, $overwriteExistingVar = false)
    {
        global $ZConfig;

        if (($name == 'errormsg' || $name == 'statusmsg' || $name == '_ZErrorMsg' || $name == '_ZStatusMsg') && !is_array($value)) {
            if ($ZConfig['System']['development']) {
                LogUtil::log(__("Error! This use of 'SessionUtil::setVar()' is no longer valid. Please use the LogUtil API to manipulate status messages and error messages."));
            }
            if ($name == '_ZErrorMsg' || $name == 'errormsg') {
                return LogUtil::registerError($value);
            }
            if ($name == '_ZStatusMsg' || $name == 'statusmsg') {
                return LogUtil::registerStatus($value);
            }
        }

        // temporary fix for bug #3770
        // $value = str_replace('\\', '/', $value);


        // cause session on regeration on uid change
        if ($name == 'uid') {
            self::regenerate();
        }

        if ($path == '/' || $path === '') {
            $_SESSION['ZSV' . $name] = $value;
        } else {
            $parent = & self::_resolvePath($path, $autocreate, $overwriteExistingVar);
            if ($parent === false) { // path + autocreate or overwriteExistingVar Error
                return false;
            }

            $parent[$name] = $value;
        }

        return true;
    }

    /**
     * Delete a session variable
     *
     * @param string $name of the session variable to delete
     * @param string $default the default value to return if the requested session variable is not set
     * @param path $path to traverse to reach the element we wish to return (optional) (default='/')
     * @return bool true
     */
    public static function delVar($name, $default = false, $path = '/')
    {
        $value = false;

        if ($path == '/' || $path === '') {
            if (isset($_SESSION['ZSV' . $name])) {
                $value = $_SESSION['ZSV' . $name];
                unset($_SESSION['ZSV' . $name]);
            } else {
                $value = $default;
            }
        } else {
            $parent = & self::_resolvePath($path, false, false);
            if ($parent === false) { // path + autocreate or overwriteExistingVar Error
                return false;
            }

            if (isset($parent[$name])) {
                $value = $parent[$name];
                unset($parent[$name]);
            } else {
                $value = $default;
            }
        }

        // unset if registerglobals are on
        unset($GLOBALS['ZSV' . $name]);

        return $value;
    }

    /**
     * Traverse the session data structure according to the path given and return a reference to last object in the path
     *
     * @access private
     * @param path $path to traverse to reach the element we wish to return
     * @param autocreate $autocreate whether or not to autocreate the supplied path (optional) (default=true)
     * @param overwriteExistingVar $overwriteExistingVar whether or not to overwrite existing/set variable entries which the given path requires to be arrays (optional) (default=false)
     * @return mixed array upon successful location/creation of path element(s), false upon failure
     */
    public static function &_resolvePath($path, $autocreate = true, $overwriteExistingVar = false)
    {
        // now traverse down the path and set the var
        if ($path == '/' || !$path) {
            return LogUtil::registerError(__f('Error! Invalid [%s] received.', 'path'));
        }

        // remove leading '/' so that explode doesn't deliver an empty 1st element
        if (strpos($path, '/') === 0) {
            $path = substr($path, 1);
        }

        $c = 0;
        $parent = & $_SESSION;
        $paths = explode('/', $path);
        foreach ($paths as $p) {
            $pFixed = ($c == 0 ? 'ZSV' . $p : $p);
            if (!isset($parent[$pFixed])) {
                if ($autocreate) {
                    $parent[$pFixed] = array();
                    $parent = & $parent[$pFixed];
                } else {
                    $false = false;
                    return $false;
                }
            } else {
                if (!is_array($parent[$pFixed])) {
                    if ($overwriteExistingVar) {
                        $parent[$pFixed] = array();
                    } else {
                        $false = false;
                        return $false;
                    }
                }
                $parent = & $parent[$pFixed];
            }
            $c++;
        }

        return $parent;
    }

    /**
     * Session required
     * Starts a session or terminates loading.
     *
     */
    public static function requireSession()
    {
        // TODO A [make an API to process these fatal errors] (drak)
        // check if we need to create a session
        if (!session_id()) {
            // Start session
            if (!self::initialize()) {
                // session initialization failed so display templated error
                header('HTTP/1.1 503 Service Unavailable');
                if (file_exists('config/templates/sessionfailed.htm')) {
                    require_once 'config/templates/sessionfailed.htm';
                } else {
                    require_once 'lib/templates/sessionfailed.htm';
                }
                // terminate execution
                System::shutdown();
            }
        }
    }

    /**
     * Let session expire nicely
     *
     * @return void
     */
    public static function expire()
    {
        if (self::getVar('uid') == '0') {
            // no need to do anything for guests without sessions
            if (System::getVar('anonymoussessions') == '0')
                return;

            // no need to display expiry for anon users with sessions since it's invisible anyway
            // handle expired sessions differently
            self::_createNew(session_id(), $GLOBALS['_ZSession']['obj']['ipaddr']);
            // session is not new, remove flag
            unset($GLOBALS['_ZSession']['new']);
            self::regenerate(true);
            return;
        }

        // for all logged in users with session destroy session and set flag
        session_destroy();
        $GLOBALS['_ZSession']['expired'] = true;
    }

    /**
     * Check if a session has expired or not
     *
     * @return bool
     */
    public static function hasExpired()
    {
        if (isset($GLOBALS['_ZSession']['expired']) && $GLOBALS['_ZSession']['expired']) {
            unset($GLOBALS['_ZSession']);
            return true;
        }

        return false;
    }

    /**
     * regerate session id
     *
     * @param bool $force default false force regeneration
     * @return void
     *
     */
    public static function regenerate($force = false)
    {
        // only regenerate if set in admin
        if ($force == false) {
            if (!System::getVar('sessionregenerate') || System::getVar('sessionregenerate') == 0) {
                // there is no point changing a newly generated session.
                if (isset($GLOBALS['_ZSession']['new']) && $GLOBALS['_ZSession']['new'] == true) {
                    return;
                }
                return;
            }
        }

        // dont allow multiple regerations
        if (isset($GLOBALS['_ZSession']['regenerated']) && $GLOBALS['_ZSession']['regenerated'] == true) {
            return;
        }

        $GLOBALS['_ZSession']['sessid_old'] = session_id(); // save old session id

        session_regenerate_id();

        $GLOBALS['_ZSession']['obj']['sessid'] = session_id(); // commit new sessid
        $GLOBALS['_ZSession']['regenerated'] = true; // flag regeneration
        return;
    }

    /**
     * Regenerate session according to probability set by admin
     *
     */
    public static function random_regenerate()
    {
        if (!System::getVar('sessionrandregenerate')) {
            return;
        }

        $chance = 100 - System::getVar('sessionregeneratefreq');
        $a = rand(0, $chance);
        $b = rand(0, $chance);
        if ($a == $b) {
            self::regenerate();
        }
    }

    /**
     * Define the name of our session cookie
     *
     * @access private
     */
    public static function getCookieName()
    {
        // Include number of dots in session name such that we use a different session for
        // www.domain.xx and domain.xx. Otherwise we run into problems with both cookies for
        // www.domain.xx as well as domain.xx being sent to www.domain.xx simultaneously!
        $hostNameDotCount = substr_count(pnGetHost(), '.');
        return System::getVar('sessionname') . $hostNameDotCount;
    }
}

/* Following _Session__* API are for internal class use.  Do not call directly */

/**
 * PHP function to start the session
 *
 * @access private
 * @return bool true
 */
function _SessionUtil__Start($path, $name)
{
    // Nothing to do
    return true;
}

/**
 * PHP function to close the session
 *
 * @access private
 * @return bool true
 */
function _SessionUtil__Close()
{
    // nothing to do
    return true;
}

/**
 * PHP function to read a set of session variables
 *
 * @access private
 * @param string $sessid session id
 * @return mixed bool of false or string session variable if true
 */
function _SessionUtil__Read($sessid)
{
    // if (System::getVar('anonymoussessions') == '0') {
    if (System::getVar('sessionstoretofile')) {
        $path = DataUtil::formatForOS(session_save_path());
        if (file_exists("$path/$sessid")) {
            $result = file_get_contents("$path/$sessid");
            if ($result) {
                $result = unserialize($result);
            }
        }
    } else {
        $result = DBUtil::selectObjectByID('session_info', $sessid, 'sessid');
        if (!$result) {
            return false;
        }
    }

    if (is_array($result) && isset($result['sessid'])) {
        $GLOBALS['_ZSession']['obj'] = array('sessid' => $result['sessid'], 'ipaddr' => $result['ipaddr'], 'uid' => $result['uid'], 'lastused' => $result['lastused']);
    }

    // security feature to change session id's periodically
    SessionUtil::random_regenerate();

    return (isset($result['vars']) ? $result['vars'] : '');
}

/**
 * PHP function to write a set of session variables
 *
 * DO NOT CALL THIS DIRECTLY use session_write_close()
 *
 * @access private
 * @param string $sessid session id
 * @param string $vars session variables
 * @return bool
 */
function _SessionUtil__Write($sessid, $vars)
{
    $obj = $GLOBALS['_ZSession']['obj'];
    $obj['vars'] = $vars;
    $obj['remember'] = (SessionUtil::getVar('rememberme') ? SessionUtil::getVar('rememberme') : 0);
    $obj['uid'] = (SessionUtil::getVar('uid') ? SessionUtil::getVar('uid') : 0);
    $obj['lastused'] = date('Y-m-d H:i:s', time());

    if (System::getVar('sessionstoretofile')) {
        $path = DataUtil::formatForOS(session_save_path());

        // if session was regenerate, delete it first
        if (isset($GLOBALS['_ZSession']['regenerated']) && $GLOBALS['_ZSession']['regenerated'] == true) {
            unlink("$path/$sessid");
        }

        // now write session
        if ($fp = @fopen("$path/$sessid", "w")) {
            $res = fwrite($fp, serialize($obj));
            fclose($fp);
        } else {
            return false;
        }
    } else {
        if (isset($GLOBALS['_ZSession']['new']) && $GLOBALS['_ZSession']['new'] == true) {
            $res = DBUtil::insertObject($obj, 'session_info', 'sessid', true);
            unset($GLOBALS['_ZSession']['new']);
        } else {
            // check for regenerated session and update ID in database
            if (isset($GLOBALS['_ZSession']['regenerated']) && $GLOBALS['_ZSession']['regenerated'] == true) {
                $sessiontable = System::dbGetTables();
                $columns = $sessiontable['session_info_column'];
                $where = "WHERE $columns[sessid] = '" . DataUtil::formatForStore($GLOBALS['_ZSession']['sessid_old']) . "'";
                $res = DBUtil::updateObject($obj, 'session_info', $where, 'sessid', true, true);
            } else {
                $res = DBUtil::updateObject($obj, 'session_info', '', 'sessid', true);
            }
        }
    }

    return (bool) $res;
}

/**
 * PHP function to destroy a session
 *
 * DO NOT CALL THIS FUNCTION DIRECTLY use session_destory();
 *
 * @access private
 * @param string $sessid session id
 * @return bool
 */
function _SessionUtil__Destroy($sessid)
{
    if (isset($GLOBALS['_ZSession'])) {
        unset($GLOBALS['_ZSession']);
    }

    // expire the cookie
    setcookie(session_name(), '', 0, ini_get('session.cookie_path'));

    // can exit if anon user and anon session disabled
    if (System::getVar('anonymoussessions') == '0' && SessionUtil::getVar('uid') == '0') {
        return true;
    }

    // ensure we delete the stored session (not a regenerated one)
    if (isset($GLOBALS['_ZSession']['regenerated']) && $GLOBALS['_ZSession']['regenerated'] == true) {
        $sessid = $GLOBALS['_ZSession']['sessid_old'];
    } else {
        $sessid = session_id();
    }

    if (System::getVar('sessionstoretofile')) {
        $path = DataUtil::formatForOS(session_save_path(), true);
        return unlink("$path/$sessid");
    } else {
        $res = DBUtil::deleteObjectByID('session_info', $sessid, 'sessid');
        return (bool) $res;
    }
}

/**
 * PHP function to garbage collect session information
 *
 * @access private
 * @param int $maxlifetime maxlifetime of the session
 * @return bool
 */
function _SessionUtil__GC($maxlifetime)
{
    $now = time();
    $inactive = ($now - (int) (System::getVar('secinactivemins') * 60));
    $daysold = ($now - (int) (System::getVar('secmeddays') * 86400));

    // find the hash length dynamically
    $hash = ini_get('session.hash_function');
    if (empty($hash) || $hash == 0) {
        $sessionlength = 32;
    } else {
        $sessionlength = 40;
    }

    if (System::getVar('sessionstoretofile')) {
        // file based GC
        $path = DataUtil::formatForOS(session_save_path(), true);
        // get files
        $files = array();
        if ($handle = opendir($path)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != '.' && $file != '..' && strlen($file) == $sessionlength) {
                    // filename, created, last modified
                    $file = "$path/$file";
                    $files[] = array('name' => $file, 'lastused' => filemtime($file));
                }
            }
        }

        // check we have something to do
        if (count($files) == 0) {
            return true;
        }

        // do GC
        switch (System::getVar('seclevel')) {
            case 'Low':
                // Low security - delete session info if user decided not to
                //                remember themself and session is inactive
                foreach ($files as $file) {
                    $name = $file['name'];
                    $lastused = $file['lastused'];
                    $session = unserialize(file_get_contents($name));
                    if ($lastused < $inactive && !isset($session['ZSVrememberme'])) {
                        unlink($name);
                    }
                }
                break;
            case 'Medium':
                // Medium security - delete session info if session cookie has
                // expired or user decided not to remember themself and inactivity timeout
                // OR max number of days have elapsed without logging back in
                foreach ($files as $file) {
                    $name = $file['name'];
                    $lastused = $file['lastused'];
                    $session = unserialize(file_get_contents($name));
                    if ($lastused < $inactive && !isset($session['ZSVrememberme'])) {
                        unlink($name);
                    } else if (($lastused < $daysold)) {
                        unlink($name);
                    }
                }
                break;
            case 'High':
                // High security - delete session info if user is inactive
                foreach ($files as $file) {
                    $name = $file['name'];
                    $lastused = $file['lastused'];
                    if ($lastused < $inactive) {
                        unlink($name);
                    }
                }
                break;
        }
        return true;

    } else {
        // DB based GC
        $pntable = System::dbGetTables();
        $sessioninfocolumn = $pntable['session_info_column'];
        $inactive = DataUtil::formatForStore(date('Y-m-d H:i:s', $inactive));
        $daysold = DataUtil::formatForStore(date('Y-m-d H:i:s', $daysold));

        switch (System::getVar('seclevel')) {
            case 'Low':
                // Low security - delete session info if user decided not to
                //                remember themself and inactivity timeout
                $where = "WHERE $sessioninfocolumn[remember] = 0
                          AND $sessioninfocolumn[lastused] < '$inactive'";
                break;
            case 'Medium':
                // Medium security - delete session info if session cookie has
                // expired or user decided not to remember themself and inactivity timeout
                // OR max number of days have elapsed without logging back in
                $where = "WHERE ($sessioninfocolumn[remember] = 0
                          AND $sessioninfocolumn[lastused] < '$inactive')
                          OR ($sessioninfocolumn[lastused] < '$daysold')
                          OR ($sessioninfocolumn[uid] = 0 AND $sessioninfocolumn[lastused] < '$inactive')";
                break;
            case 'High':
            default:
                // High security - delete session info if user is inactive
                $where = "WHERE $sessioninfocolumn[lastused] < '$inactive'";
                break;
        }

        $res = DBUtil::deleteWhere('session_info', $where);
        return (bool) $res;
    }
}


