<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv3 (or at your option any later version).
 * @package Util
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * SessionUtil
 */
class SessionUtil
{
    /**
     * Initialise session.
     *
     * @return void
     */
    public static function initialize()
    {
    }

    /**
     * Create a new session.
     *
     * @param string $sessid The session ID.
     * @param string $ipaddr The IP address of the host with this session.
     *
     * @return boolean
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
        self::setVar('uid', 0);

        return true;
    }

    /**
     * Get a session variable
     *
     * @param string  $name                 Name of the session variable to get.
     * @param string  $default              The default value to return if the requested session variable is not set.
     * @param string  $path                 Path.
     * @param boolean $autocreate           Whether or not to autocreate the supplied path (optional) (default=true).
     * @param boolean $overwriteExistingVar Whether or not to overwrite existing/set variable entries which the given path requires to be arrays (optional) (default=false).
     *
     * @return string Session variable requested.
     */
    public static function getVar($name, $default = false, $path = '/', $autocreate = true, $overwriteExistingVar = false)
    {
        $session = ServiceUtil::getManager()->getService('session');

        return $session->get($name, $default, $path);
    }

    /**
     * Set a session variable.
     *
     * @param string  $name                 Name of the session variable to set.
     * @param string  $value                Value to set the named session variable.
     * @param string  $path                 Path to traverse to reach the element we wish to return (optional) (default='/').
     * @param boolean $autocreate           Whether or not to autocreate the supplied path (optional) (default=true).
     * @param boolean $overwriteExistingVar Whether or not to overwrite existing/set variable entries which the given path requires to be arrays (optional) (default=false).
     *
     * @return boolean true upon success, false upon failure.
     */
    public static function setVar($name, $value, $path = '/', $autocreate = true, $overwriteExistingVar = false)
    {
        $session = ServiceUtil::getManager()->getService('session');
        if (($name == 'errormsg' || $name == 'statusmsg' || $name == '_ZErrorMsg' || $name == '_ZStatusMsg') && !is_array($value)) {
            if (System::isDevelopmentMode()) {
                LogUtil::log(__("Error! This use of 'SessionUtil::setVar()' is no longer valid. Please use the LogUtil API to manipulate status messages and error messages."));
            }
            if ($name == '_ZErrorMsg' || $name == 'errormsg') {
                return LogUtil::registerError($value);
            }
            if ($name == '_ZStatusMsg' || $name == 'statusmsg') {
                return LogUtil::registerStatus($value);
            }
        }

        if ($name == 'uid') {
            $session->regenerate();
        }

        return $session->set($name, $value, $path);
    }

    /**
     * Delete a session variable.
     *
     * @param string $name    Name of the session variable to delete.
     * @param mixed  $default The default value to return if the requested session variable is not set.
     * @param string $path    Path to traverse to reach the element we wish to return (optional) (default='/').
     *
     * @return mixed The value of the session variable being deleted, or the value provided in $default if the variable is not set.
     */
    public static function delVar($name, $default = false, $path = '/')
    {
        $session = ServiceUtil::getManager()->getService('session');
        $value = $session->get($name, $default, $path);
        $session->del($name, $path);

        return $value;
    }

    /**
     * Traverse the session data structure according to the path given and return a reference to last object in the path.
     *
     * @param string  $path                 Path to traverse to reach the element we wish to return.
     * @param boolean $autocreate           Whether or not to autocreate the supplied path (optional) (default=true).
     * @param boolean $overwriteExistingVar Whether or not to overwrite existing/set variable entries which the given path requires to be arrays (optional) (default=false).
     *
     * @return mixed Array upon successful location/creation of path element(s), false upon failure.
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
     * Session required.
     *
     * Starts a session or terminates loading.
     *
     * @return void
     */
    public static function requireSession()
    {
        $event = new Zikula_Event('session.require');
        EventUtil::getManager()->notify($event);
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
            if (System::getVar('anonymoussessions') == '0' && !session_id()) {
                return;
            }

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
     * @return boolean
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
     * Regerate session id.
     *
     * @param boolean $force Force regeneration, default: false.
     *
     * @return void
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
     * Regenerate session according to probability set by admin.
     *
     * @return void
     */
    public static function random_regenerate()
    {
        if (!System::getVar('sessionrandregenerate')) {
            return;
        }

        $chance = 100 - System::getVar('sessionregeneratefreq');
        $a = mt_rand(0, $chance);
        $b = mt_rand(0, $chance);
        if ($a == $b) {
            self::regenerate();
        }
    }

    /**
     * Define the name of our session cookie.
     *
     * @access private
     * @return string
     */
    public static function getCookieName()
    {
        // Include number of dots in session name such that we use a different session for
        // www.domain.xx and domain.xx. Otherwise we run into problems with both cookies for
        // www.domain.xx as well as domain.xx being sent to www.domain.xx simultaneously!
        $hostNameDotCount = substr_count(System::getHost(), '.');

        return System::getVar('sessionname') . $hostNameDotCount;
    }
}
