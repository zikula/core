<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * LogUtil
 *
 * @package Zikula_Core
 * @subpackage LogUtil
 */
class LogUtil
{
    /**
     * Returns an array of status messages
     *
     * @param $override whether to override status messages with error messages
     * @return array of messages
     */
    public static function getStatusMessages($delete = true, $override = true, $reverse = true)
    {
        $msgs = SessionUtil::getVar('_ZStatusMsg', array());
        $errs = SessionUtil::getVar('_ZErrorMsg', array());
        if (!empty($errs) && $override) {
            $msgs = $errs;
        }

        if ($delete) {
            SessionUtil::delVar('_ZErrorMsg');
            SessionUtil::delVar('_ZErrorMsgType');
            SessionUtil::delVar('_ZStatusMsg');
            SessionUtil::delVar('_ZStatusMsgType');
        }

        if ($reverse) {
            $msgs = array_reverse($msgs, true);
        }

        return $msgs;
    }

    /**
     * Returns a string of the available status messages, separated by the given delimeter
     *
     * @param $delimeter The string to use as the delimeter between the array of messages
     * @param $override whether to override status messages with error messages
     * @return string the generated error message
     */
    public static function getStatusMessagesText($delimiter = '<br />', $delete = true, $override = true)
    {
        $msgs = self::getStatusMessages($delete, $override);
        return implode($delimiter, $msgs);
    }

    /**
     * Get an array of error messages
     *
     * @return array of messages
     */
    public static function getErrorMessages($delete = true, $reverse = true)
    {
        $msgs = SessionUtil::getVar('_ZErrorMsg', array());

        if ($delete) {
            SessionUtil::delVar('_ZErrorMsg');
            SessionUtil::delVar('_ZErrorMsgType');
        }

        if ($reverse) {
            $msgs = array_reverse($msgs, true);
        }

        return $msgs;
    }

    /**
     * Get an error message text
     *
     * @param $delimeter The string to use as the delimeter between the array of messages
     * @return string the generated error message
     */
    public static function getErrorMessagesText($delimeter = '<br />', $delete = true)
    {
        $msgs = self::getErrorMessages($delete);
        return implode($delimeter, $msgs);
    }

    /**
     * get the error type
     *
     * @return int error type
     */
    public static function getErrorType()
    {
        return (int) SessionUtil::getVar('_ZErrorMsgType');
    }

    /**
     * check if errors
     *
     * @return int error type
     */
    public static function hasErrors()
    {
        $msgs = self::getErrorMessages(false);
        return (bool) !empty($msgs);
    }

    /**
     * Set an error message text
     *
     * @param $message string the error message
     * @param $url the url to redirect to (optional) (default=null)
     * @return true
     */
    public static function registerStatus($message, $url = null)
    {
        if (empty($message)) {
            return pn_exit(__f('Empty %s received.', 'message'));
        }

        $msgs = SessionUtil::getVar('_ZStatusMsg', array());
        if (is_array($message)) {
            $msgs = array_merge($msgs, $message);
        } else {
            $msgs[] = DataUtil::formatForDisplayHTML($message);
        }
        SessionUtil::setVar('_ZStatusMsg', $msgs);

        // check if we want to redirect
        if ($url) {
            return pnRedirect($url);
        }

        return true;
    }

    /**
     * Register a failed authid check. This method calls registerError and
     * then redirects back to the specified URL.
     *
     * @param $url       The URL to redirect to (optional) (default=null)
     * @return false
     */
    public static function registerAuthidError($url = null)
    {
        return self::registerError(__("Sorry! Invalid authorisation key ('authkey'). This is probably either because you pressed the 'Back' button to return to a page which does not allow that, or else because the page's authorisation key expired due to prolonged inactivity. Please refresh the page and try again."), null, $url);
    }

    /**
     * Register a failed permission check. This method calls registerError and
     * then logs the failed permission check so that it can be analyzed later.
     *
     * @param $url       The URL to redirect to (optional) (default=null)
     * @return false
     */
    public static function registerPermissionError($url = null)
    {
        static $strLevels = array();
        if (!$strLevels) {
            $strLevels[ACCESS_INVALID] = 'INVALID';
            $strLevels[ACCESS_NONE] = 'NONE';
            $strLevels[ACCESS_OVERVIEW] = 'OVERVIEW';
            $strLevels[ACCESS_READ] = 'READ';
            $strLevels[ACCESS_COMMENT] = 'COMMENT';
            $strLevels[ACCESS_MODERATE] = 'MODERATE';
            $strLevels[ACCESS_EDIT] = 'EDIT';
            $strLevels[ACCESS_ADD] = 'ADD';
            $strLevels[ACCESS_DELETE] = 'DELETE';
            $strLevels[ACCESS_ADMIN] = 'ADMIN';
        }

        global $ZRuntime;
        $obj = array();
        $obj['component'] = 'PERMISSION';
        $obj['sec_component'] = $ZRuntime['security']['last_failed_check']['component'];
        $obj['sec_instance'] = $ZRuntime['security']['last_failed_check']['instance'];
        $obj['sec_permission'] = $strLevels[$ZRuntime['security']['last_failed_check']['level']];

        self::_write(__('Sorry! You have not been granted access to this page.'), 'PERMISSION', $obj);
        if (!pnUserLoggedIn()) {
            if (is_null($url)) {
                $url = pnModURL('Users', 'user', 'loginscreen', array('returnpage' => urlencode(pnGetCurrentURI())));
            }
            return self::registerStatus(__('Sorry! You have not been granted access to this page.'), $url);
        }
        return self::registerError(__('Sorry! You have not been granted access to this page.'), 403, $url);
    }

    /**
     * Set an error message text. Also adds method, file and line where the error occured
     *
     * @param $message string the error message
     * @param $type type of error (numeric and corresponding to a HTTP status code) (optional) (default=null)
     * @param $url the url to redirect to (optional) (default=null)
     * @return false
     */
    public static function registerError($message, $type = null, $url = null)
    {
        if (empty($message)) {
            return pn_exit(__f('Empty %s received.', 'message'));
        }

        global $ZConfig;

        $showDetailInfo = (defined('_ZINSTALLVER') || ($ZConfig['System']['development'] && SecurityUtil::checkPermission('.*', '.*', ACCESS_ADMIN)));

        if ($showDetailInfo) {
            $bt = debug_backtrace();

            $cf0 = $bt[0];
            $cf1 = isset($bt[1]) ? $bt[1] : array('function' => '', 'args' => '');
            $file = $cf0['file'];
            $line = $cf0['line'];
            $func = !empty($cf1['function']) ? $cf1['function'] : '';
            $class = !empty($cf1['class']) ? $cf1['class'] : '';
            $args = $cf1['args'];
        } else {
            $func = '';
        }

        if (!$showDetailInfo) {
            $msg = $message;
        } else {
            // TODO A [do we need to have HTML sanitization] (drak)
            $func = ((!empty($class)) ? "$class::$func" : $func);
            $msg = __f('%1$s The origin of this message was \'%2$s\' at line %3$s in file \'%4$s\'.', array($message, $func, $line, $file));
            //
            if ($ZConfig['System']['development']) {
                $msg .= '<br />';
                $msg .= _prayer(debug_backtrace());
            }
        }

        $msgs = SessionUtil::getVar('_ZErrorMsg', array());
        // no html encoding should be used here - not htmlentities nor DataUtil methods
        // as the message *may* contain pre-formatted html
        if (is_array($message)) {
            $msgs = array_merge($msgs, $message);
        } else {
            $msgs[] = $msg;
        }
        // note for bug #4439 - we dont want to pass messages through HTML tag
        // filter, only ensure the HTML is valid since this is system generated
        SessionUtil::setVar('_ZErrorMsg', $msgs);

        // check if we've got an error type
        if (isset($type) && is_numeric($type)) {
            SessionUtil::setVar('_ZErrorMsgType', $type);
        }

        // check if we want to redirect
        if ($url) {
            return pnRedirect($url);
        }

        // since we're registering an error, it makes sense to return false here.
        // This allows the calling code to just return the result of pnRegisterError
        // if it wishes to return 'false' (which is what ususally happens).
        return false;
    }

    /**
     * Register a failed method call due to a failed validation on the parameters passed
     *
     * @returns false
     */
    public static function registerArgsError($url = null)
    {
        return self::registerError(__('Error! The action you wanted to perform was not successful for some reason, maybe because of a problem with what you input. Please check and try again.'), null, $url);
    }

    /**
     * Log the given messge under the given level
     *
     * @param msg      The message to log
     * @param level    The log to log this message under
     * @returns nothing
     */
    public static function log($msg, $level = 'DEFAULT')
    {
        global $ZConfig;
        $haveConfig = count($ZConfig['Log']) > 0;
        $logLevels = $ZConfig['Log']['log_levels'];
        $showErrors = $ZConfig['Log']['log_show_errors'];
        $logUser = $ZConfig['Log']['log_user'];
        $suid = SessionUtil::getVar('uid', 0);

        if ($logUser && $logUser != $suid) {
            return;
        }

        if (!$haveConfig) {
            print "<p><strong>".__("Logging configuration can't be loaded .... logging is disabled")."</strong></p>";
        } elseif ($level == "ALL" && $showErrors == true) {
            print "<p><strong>".__("You should not add an event log with log_level 'ALL'")."</strong></p>";
        } elseif (in_array($level, $logLevels) || in_array("ALL", $logLevels)) {
            self::_write($msg, $level);
        }
    }

    /**
     * Generate the filename of todays log file
     *
     * @returns the generated filename
     */
    public static function getLogFileName($level = null)
    {
        global $ZConfig;
        $logfileSpec = $ZConfig['Log']['log_file'];
        $dateFormat = $ZConfig['Log']['log_file_date_format'];

        if ($level && isset($ZConfig['Log']['log_level_files'][$level]) && $ZConfig['Log']['log_level_files'][$level]) {
            $logfileSpec = $ZConfig['Log']['log_level_files'][$level];
        }

        if (strpos($logfileSpec, "%s") !== false) {
            if ($ZConfig['Log']['log_file_uid']) {
                $perc = strpos($logfileSpec, '%s');
                $start = substr($logfileSpec, 0, $perc + 2);
                $end = substr($logfileSpec, $perc + 2);
                $uid = SessionUtil::getVar('uid', 0);

                $logfileSpec = $start . '-%d' . $end;
                $logfile = sprintf($logfileSpec, date($dateFormat), $uid);
            } else {
                $logfile = sprintf($logfileSpec, date($dateFormat));
            }
        } else {
            $logfile = $logfileSpec;
        }

        return $logfile;
    }

    /**
     * Write the error message to the log file.
     *
     * Prints log file full error (if $log_show_errors is true)
     *
     * @param msg      The message to log
     * @param level    The log level to log this message under
     *
     * @returns Logging file write error (file or directory unwritable) (if $log_show_errors is true)
     */
    public static function _write($msg, $level = 'DEFAULT', $securityInfo = null)
    {
        global $ZConfig;
        $logEnabled = $ZConfig['Log']['log_enabled'];
        if (!$logEnabled) {
            return;
        }

        $logShowErr = $ZConfig['Log']['log_show_errors'];
        $logDateFmt = $ZConfig['Log']['log_date_format'];
        $logDest = $ZConfig['Log']['log_dest'];
        $uid = SessionUtil::getVar('uid', 1);
        $module = pnModGetName();
        $type = FormUtil::getPassedValue('type', 'user', 'GETPOST');
        $func = FormUtil::getPassedValue('func', 'main', 'GETPOST');

        if ($level && isset($ZConfig['Log']['log_level_dest'][$level]) && $ZConfig['Log']['log_level_dest'][$level]) {
            $logDest = $ZConfig['Log']['log_level_dest'][$level];
        }

        // permission to be logged to DB or FILE
        if ($level == 'PERMISSION' && ($logDest != 'DB' && $logDest != 'FILE')) {
            $logDest = 'DB';
        }

        $logDest = strtoupper($logDest);

        $logline = '';
        if ($logDest == 'FILE') {
            $title = date($logDateFmt) . ", level=$level, uid=$uid, module=$module, type=$type, func=$func\n";
            if ($securityInfo)
                $title .= "++ sec_component=$securityInfo[sec_component], sec_instance=$securityInfo[sec_instance], sec_permission=$securityInfo[sec_permission]\n";
            $logline = '+ ' . $title;
        }
        $logline .= "$msg\n\n";

        if ($logDest == 'FILE') {
            static $logfile = '';
            if (!$logfile) {
                $logfile = self::getLogFileName($level);
            }

            $logfileOK = self::_checkLogFile($logfile, $level, $reason);
            if ($logfileOK) {
                $fp = fopen($logfile, 'a');
                fwrite($fp, $logline, strlen($logline));
                fclose($fp);
            } elseif ($logShowErr) {
                if ($reason == 'NOWRITE') {
                    print "<p><strong>".__f('Logging Disabled. Log file (%s) is not writable.', $logfile)."</strong></p>";
                } elseif ($reason == 'TOOBIG') {
                    print "<p><strong>".__f("Log file (%s) is full.", $logfile)."</strong></p>";
                }
            }
        } elseif ($logDest == 'PRINT') {
            print '<div class="z-sub" style="text-align:left;">' . $logline . '</div>';
            //print $msg;
        } elseif ($logDest == 'MAIL') {
            $title = date($logDateFmt) . ", level=$level, uid=$uid\n";
            $adminmail = pnConfigGetVar('adminmail');

            $args = array();
            $args['fromname'] = 'Zikula ' . pnConfigGetVar('slogan', 'Site Slogan');
            $args['fromaddress'] = $adminmail;
            $args['toname'] = 'Site Administrator';
            $args['toaddress'] = $adminmail;
            $args['subject'] = "Log Message: level=$level, uid=$uid";
            $args['body'] = $logline;

            $rc = pnModFunc('Mailer', 'userapi', 'sendmessage', $args);
        } elseif ($logDest == 'DB') {
            $obj = array();
            $obj['date'] = date($logDateFmt);
            $obj['uid'] = $uid;
            $obj['component'] = $level;
            $obj['module'] = $module;
            $obj['type'] = $type;
            $obj['function'] = $func;
            $obj['message'] = $msg;

            if ($securityInfo && is_array($securityInfo)) {
                $obj = array_merge($obj, $securityInfo);
            }

            if (pnModDBInfoLoad('SecurityCenter')) {
                if (!DBUtil::insertObject($obj, 'sc_logevent')) {
                    print '<div class="z-sub" style="text-align:left;">';
                    print __('Failed to insert log record into log_event table').'<br />';
                    prayer($obj);
                    print '</div>';
                }
            } else {
                print __('Failed to load logging table definition from SecurityCenter module').'<br />';
            }
        } else {
            print __f('Unknown log destination [%s].', $logDest);
        }
    }

    /**
     * Check the log file is writable and not full.
     *
     * returns unwritable The file or directory cannot be written to
     * returns toobig The log file size is bigger than $log_length in logging.conf.php.
     *
     * @returns boolean Whether or not the file is ready for writing
     */
    public static function _checkLogFile($logfile, $level, &$reason)
    {
        global $ZConfig;
        $logSize = $ZConfig['Log']['log_maxsize'];

        if (!$logfile) {
            $logfile = self::getLogFileName($level);
        }

        $size = 0;
        $rc = false;

        if (file_exists($logfile)) {
            $size = filesize($logfile) / 1024 / 1024;
        }

        if (file_exists($logfile) && is_writable($logfile)) {
            $rc = true;
        } elseif (!file_exists($logfile)) {
            @touch($logfile);
            if (file_exists($logfile)) {
                chmod($logfile, 0755);
                $rc = true;
            } else {
                SessionUtil::setVar('_ZStatusMsg', __f('Unable to create log file [%s].', $logfile));
                $reason = 'NOWRITE';
            }
        } elseif ($logSize && $size > $logSize) {
            SessionUtil::setVar('_ZStatusMsg', __f('Logfile [%s] size [%s] exceeds [%s].', array($logfile, $size, $logSize)));
            $reason = 'TOOBIG';
        }

        return $rc;
    }

    /**
     * Cleans up unneeded old log files
     */
    public static function _cleanLogFiles()
    {
        if (defined('_ZINSTALLVER')) {
            return;
        }

        global $ZConfig;

        $oneday = 24 * 60 * 60;
        $log_keep_days = $ZConfig['Log']['log_keep_days'];
        if (!$log_keep_days)
            $log_keep_days = 30; // temporary default value for migration


        $log_keep_seconds = $log_keep_days * $oneday;
        $lastcheck = pnConfigGetVar('log_last_rotate');
        $currenttime = time();

        if (time() - $lastcheck > $oneday) {
            // check once a day
            Loader::loadClass('FileUtil');
            $logfilepath = $ZConfig['Log']['log_dir'];
            $logfiles = FileUtil::getFiles($logfilepath, false, false);
            foreach ($logfiles as $logfile) {
                if ($currenttime - filemtime($logfile) > $log_keep_seconds) {
                    unlink($logfile);
                }
            }
            pnConfigSetVar('log_last_rotate', $currenttime);
        }
    }
}
