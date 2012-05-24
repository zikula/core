<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_Session
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Legacy session storage class.
 *
 * This Storage driver couples directly to the old SessionUtil methodology.
 * This will eventually be phased out.
 */
class Zikula_Session_Storage_Legacy implements Zikula_Session_StorageInterface
{
    /**
     * {@inheritdoc}
     */
    public function start()
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


        ini_set('session.name', SessionUtil::getCookieName()); // Name of our cookie
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

        session_set_save_handler(array($this, 'open'), array($this, 'close'), array($this, 'read'), array($this, 'write'), array($this, 'destroy'), array($this, 'gc'));

        // create IP finger print
        $current_ipaddr = '';
        $_REMOTE_ADDR = System::serverGetVar('REMOTE_ADDR');
        $_HTTP_X_FORWARDED_FOR = System::serverGetVar('HTTP_X_FORWARDED_FOR');

        if (System::getVar('sessionipcheck')) {
            // feature for future release
        }

        // create the ip fingerprint
        $current_ipaddr = md5($_REMOTE_ADDR . $_HTTP_X_FORWARDED_FOR);

        // start session check expiry and ip fingerprint if required
        if (session_start() && isset($GLOBALS['_ZSession']['obj']) && $GLOBALS['_ZSession']['obj']) {
            // check if session has expired or not
            $now = time();
            $inactive = ($now - (int)(System::getVar('secinactivemins') * 60));
            $daysold = ($now - (int)(System::getVar('secmeddays') * 86400));
            $lastused = strtotime($GLOBALS['_ZSession']['obj']['lastused']);
            $rememberme = SessionUtil::getVar('rememberme');
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
                        $this->expire();
                    }
                    break;
                case 'High':
                default:
                    // High security - delete session info if user is inactive
                    //if ($rememberme && ($lastused < $inactive)) { // see #427
                    if ($lastused < $inactive) {
                        $this->expire();
                    }
                    break;
            }
        } else {
            // *must* regenerate new session otherwise the default sessid will be
            // taken from any session cookie that was submitted (bad bad bad)
            $this->regenerate(true);
            SessionUtil::_createNew(session_id(), $current_ipaddr);
        }

        if (isset($_SESSION['_ZSession']['obj'])) {
            unset($_SESSION['_ZSession']['obj']);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function expire()
    {
        SessionUtil::expire();
    }

    /**
     * {@inheritdoc}
     */
    public function regenerate($destroy = false)
    {
        SessionUtil::regenerate($destroy);
    }

    /**
     * {@inheritdoc}
     */
    public function open($savePath, $sessionName)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read($sessionId)
    {
        if (System::getVar('sessionstoretofile')) {
            $path = DataUtil::formatForOS(session_save_path());
            if (file_exists("$path/$sessionId")) {
                $result = file_get_contents("$path/$sessionId");
                if ($result) {
                    $result = unserialize($result);
                }
            }
        } else {
            $result = DBUtil::selectObjectByID('session_info', $sessionId, 'sessid');
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
     * {@inheritdoc}
     */
    public function write($sessionId, $vars)
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
                unlink("$path/$sessionId");
            }

            // now write session
            if ($fp = @fopen("$path/$sessionId", "w")) {
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
                    $sessiontable = DBUtil::getTables();
                    $columns = $sessiontable['session_info_column'];
                    $where = "WHERE $columns[sessid] = '" . DataUtil::formatForStore($GLOBALS['_ZSession']['sessid_old']) . "'";
                    $res = DBUtil::updateObject($obj, 'session_info', $where, 'sessid', true, true);
                } else {
                    $res = DBUtil::updateObject($obj, 'session_info', '', 'sessid', true);
                }
            }
        }

        return (bool)$res;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($sessionId)
    {
        if (isset($GLOBALS['_ZSession'])) {
            unset($GLOBALS['_ZSession']);
        }

        // expire the cookie
        setcookie(session_name(), '', 0, ini_get('session.cookie_path'));

        // ensure we delete the stored session (not a regenerated one)
        if (isset($GLOBALS['_ZSession']['regenerated']) && $GLOBALS['_ZSession']['regenerated'] == true) {
            $sessionId = $GLOBALS['_ZSession']['sessid_old'];
        } else {
            $sessionId = session_id();
        }

        if (System::getVar('sessionstoretofile')) {
            $path = DataUtil::formatForOS(session_save_path(), true);

            return unlink("$path/$sessionId");
        } else {
            $res = DBUtil::deleteObjectByID('session_info', $sessionId, 'sessid');

            return (bool)$res;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function gc($lifetime)
    {
        $now = time();
        $inactive = ($now - (int)(System::getVar('secinactivemins') * 60));
        $daysold = ($now - (int)(System::getVar('secmeddays') * 86400));

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
                        if ($lastused < $inactive && !isset($session['rememberme'])) {
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
                        if ($lastused < $inactive && !isset($session['rememberme'])) {
                            unlink($name);
                        } elseif (($lastused < $daysold)) {
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
            $dbtable = DBUtil::getTables();
            $sessioninfocolumn = $dbtable['session_info_column'];
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

            return (bool)$res;
        }
    }
}
