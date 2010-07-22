<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Util
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * LogUtil
 */
class LogUtil
{
    /**
     * Returns an array of status messages.
     *
     * @param boolean $delete   Whether to delete error messages (optional) (default=true).
     * @param boolean $override Whether to override status messages with error messages (optional) (default=true).
     * @param boolean $reverse  Whether to reverse order of messages (optional) (default=true).
     *
     * @return array of messages.
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
     * Returns a string of the available status messages, separated by the given delimeter.
     *
     * @param string  $delimiter The string to use as the delimeter between the array of messages.
     * @param boolean $delete    True to delete.
     * @param boolean $override  Whether to override status messages with error messages.
     *
     * @return string the generated error message.
     */
    public static function getStatusMessagesText($delimiter = '<br />', $delete = true, $override = true)
    {
        $msgs = self::getStatusMessages($delete, $override);
        return implode($delimiter, $msgs);
    }

    /**
     * Get an array of error messages.
     *
     * @param boolean $delete  True to delete error messages (optional)(default=true).
     * @param boolean $reverse True to reverse error messages (optional)(default=true).
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
     * Get an error message text.
     *
     * @param string  $delimeter The string to use as the delimeter between the array of messages.
     * @param boolean $delete    True to delete.
     *
     * @return string the generated error message.
     */
    public static function getErrorMessagesText($delimeter = '<br />', $delete = true)
    {
        $msgs = self::getErrorMessages($delete);
        return implode($delimeter, $msgs);
    }

    /**
     * get the error type.
     *
     * @return int error type.
     */
    public static function getErrorType()
    {
        return (int)SessionUtil::getVar('_ZErrorMsgType');
    }

    /**
     * check if errors.
     *
     * @return int error type.
     */
    public static function hasErrors()
    {
        $msgs = self::getErrorMessages(false);
        return (bool)!empty($msgs);
    }

    /**
     * Set an error message text.
     *
     * @param string $message String the error message.
     * @param string $url     The url to redirect to (optional) (default=null).
     *
     * @return true, or redirect if url.
     */
    public static function registerStatus($message, $url = null)
    {
        if (empty($message)) {
            return z_exit(__f('Empty [%s] received.', 'message'));
        }

        self::addStatusPopup($message);

        // check if we want to redirect
        if ($url) {
            return System::redirect($url);
        }

        return true;
    }

    public static function addStatusPopup($message)
    {
        $message = empty($message) ? __f('Empty [%s] received.', 'message') : $message;
        self::_addPopup($message, Zikula_ErrorHandler::INFO);
    }

    public static function addErrorPopup($message)
    {
        $message = empty($message) ? __f('Empty [%s] received.', 'message') : $message;
        self::_addPopup($message, E_USER_ERROR);
    }

    private static function _addPopup($message, $type = E_USER_NOTICE)
    {
        self::log($message, Zikula_ErrorHandler::DEBUG);
        
        if ($type === Zikula_ErrorHandler::INFO) {
            $key = '_ZStatusMsg';
        } elseif ($type === E_USER_ERROR) {
            $key = '_ZErrorMsg';
        } else {
            throw new InvalidArgumentException(__f('Invalid type %s for LogUtil::_addPopup', $type));
        }

        $msgs = SessionUtil::getVar($key, array());
        $msgs[] = DataUtil::formatForDisplayHTML($message);
        SessionUtil::setVar($key, $msgs);
    }

    /**
     * Register a failed authid check.
     *
     * This method calls registerError and then redirects back to the specified URL.
     *
     * @param string $url The URL to redirect to (optional) (default=null).
     *
     * @return false.
     */
    public static function registerAuthidError($url = null)
    {
        return self::registerError(self::getErrorMsgAuthid(), null, $url);
    }

    /**
     * Register a failed permission check.
     *
     * This method calls registerError and then logs the failed permission check so that it can be analyzed later.
     *
     * @param string  $url      The URL to redirect to (optional) (default=null).
     * @param boolean $redirect Whether to redirect not logged in users to the login form (default=true).
     *
     * @return false
     */
    public static function registerPermissionError($url = null, $redirect = true)
    {
        /*
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
        */
        $code = 403;
        if (!UserUtil::isLoggedIn() && $redirect) {
            if (is_null($url)) {
                $url = ModUtil::url('Users', 'user', 'loginscreen', array('returnpage' => urlencode(System::getCurrentUri())));
            }
            $code = null;
        }
        return self::registerError(self::getErrorMsgPermission(), $code, $url);
    }

    /**
     * Set an error message text.
     *
     * Also adds method, file and line where the error occured.
     *
     * @param string  $message The error message.
     * @param intiger $type    The type of error (numeric and corresponding to a HTTP status code) (optional) (default=null).
     * @param string  $url     The url to redirect to (optional) (default=null).
     * @param string  $debug   Debug information.
     *
     * @return false or system redirect if url is set.
     */
    public static function registerError($message, $type = null, $url = null, $debug=null)
    {
        if (!isset($message) || empty($message)) {
            return z_exit(__f('Empty [%s] received.', 'message'));
        }

        self::addErrorPopup($message);

        // check if we've got an error type
        if (isset($type) && is_numeric($type)) {
            SessionUtil::setVar('_ZErrorMsgType', $type);
        }

        // check if we want to redirect
        if ($url) {
            return System::redirect($url);
        }

        // since we're registering an error, it makes sense to return false here.
        // This allows the calling code to just return the result of LogUtil::registerError
        // if it wishes to return 'false' (which is what ususally happens).
        return false;
    }

    /**
     * Register a failed method call due to a failed validation on the parameters passed.
     *
     * @param string $url Url to redirect to.
     *
     * @return false.
     */
    public static function registerArgsError($url = null)
    {
        return self::registerError(self::getErrorMsgArgs(), null, $url);
    }

    /**
     * Get the default message for an authid error.
     *
     * @return string error message.
     */
    public static function getErrorMsgAuthid() {
        return __("Sorry! Invalid authorisation key ('authkey'). This is probably either because you pressed the 'Back' button to return to a page which does not allow that, or else because the page's authorisation key expired due to prolonged inactivity. Please refresh the page and try again.");
    }

    /**
     * Get the default message for a permission error.
     *
     * @return string error message.
     */
    public static function getErrorMsgPermission() {
        return __('Sorry! You have not been granted access to this page.');
    }

    /**
     * Get the default message for an argument error.
     *
     * @return string error message.
     */
    public static function getErrorMsgArgs() {
        return __('Error! The action you wanted to perform was not successful for some reason, maybe because of a problem with what you input. Please check and try again.');
    }

    /**
     * Log the given messge under the given level
     *
     * @param string $msg   The message to log.
     * @param string $level The log to log this message under(optional)(default='DEFAULT').
     *
     * @return void
     */
    public static function log($msg, $level = Zikula_ErrorHandler::DEBUG)
    {
        $errorReporting = ServiceUtil::getManager()->getService('system.errorreporting');
        $errorReporting->handler($level, $msg);
    }

    /**
     * Generate the filename of todays log file.
     *
     * @param integer $level Log level.
     *
     * @return the generated filename.
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
}