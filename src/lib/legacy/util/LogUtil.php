<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Monolog\Logger as Log;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * LogUtil.
 * @deprecated remove at Core-2.0
 */
class LogUtil
{
    /**
     * Returns an array of status messages.
     *
     * @param boolean $delete   Whether to delete error messages (optional) (default=true)
     * @param boolean $override Whether to override status messages with error messages (optional) (default=true)
     * @param boolean $reverse  Whether to reverse order of messages (optional) (default=true)
     *
     * @return array of messages
     */
    public static function getStatusMessages($delete = true, $override = true, $reverse = true)
    {
        $session = ServiceUtil::getManager()->get('session');
        $msgs = $session->getFlashBag()->peek(Zikula_Session::MESSAGE_STATUS);
        $errs = $session->getFlashBag()->peek(Zikula_Session::MESSAGE_ERROR);

        if (!empty($errs) && $override) {
            $msgs = $errs;
        }

        if ($delete) {
            $session->clearMessages(Zikula_Session::MESSAGE_STATUS);
            SessionUtil::delVar('_ZErrorMsgType');
            $session->clearMessages(Zikula_Session::MESSAGE_ERROR);
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
     * @param string  $delimiter The string to use as the delimeter between the array of messages
     * @param boolean $delete    True to delete
     * @param boolean $override  Whether to override status messages with error messages
     *
     * @return string the generated error message
     */
    public static function getStatusMessagesText($delimiter = '<br />', $delete = true, $override = true)
    {
        $msgs = self::getStatusMessages($delete, $override);

        return implode($delimiter, $msgs);
    }

    /**
     * Returns an array of warning messages.
     *
     * @param boolean $delete   Whether to delete warning messages (optional) (default=true)
     * @param boolean $override Whether to override warning messages with error messages (optional) (default=true)
     * @param boolean $reverse  Whether to reverse order of messages (optional) (default=true)
     *
     * @return array of messages
     */
    public static function getWarningMessages($delete = true, $override = true, $reverse = true)
    {
        $session = ServiceUtil::getManager()->get('session');
        $warns = $session->getFlashBag()->peek(Zikula_Session::MESSAGE_WARNING);
        $errs =  $session->getFlashBag()->peek(Zikula_Session::MESSAGE_ERROR);

        if (!empty($errs) && $override) {
            $warns = $errs;
        }

        if ($delete) {
            $session->clearMessages(Zikula_Session::MESSAGE_WARNING);
            SessionUtil::delVar('_ZWarningMsgType');
        }

        if ($reverse) {
            $warns = array_reverse($warns, true);
        }

        return $warns;
    }

    /**
     * Returns a string of the available warning messages, separated by the given delimeter.
     *
     * @param string  $delimiter The string to use as the delimeter between the array of messages
     * @param boolean $delete    True to delete
     * @param boolean $override  Whether to override warning messages with error messages
     *
     * @return string the generated error message
     */
    public static function getWarningMessagesText($delimiter = '<br />', $delete = true, $override = true)
    {
        $msgs = self::getWarningMessages($delete, $override);

        return implode($delimiter, $msgs);
    }

    /**
     * Get an array of error messages.
     *
     * @param boolean $delete  True to delete error messages (optional)(default=true)
     * @param boolean $reverse True to reverse error messages (optional)(default=true)
     *
     * @return array of messages
     */
    public static function getErrorMessages($delete = true, $reverse = true)
    {
        $session = ServiceUtil::getManager()->get('session');
        $msgs = $session->getFlashBag()->peek(Zikula_Session::MESSAGE_ERROR);

        if ($delete) {
            $session->clearMessages(Zikula_Session::MESSAGE_ERROR);
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
     * @param string  $delimeter The string to use as the delimeter between the array of messages
     * @param boolean $delete    True to delete
     *
     * @return string the generated error message
     */
    public static function getErrorMessagesText($delimeter = '<br />', $delete = true)
    {
        $msgs = self::getErrorMessages($delete);

        return implode($delimeter, $msgs);
    }

    /**
     * get the error type.
     *
     * @return int error type
     */
    public static function getErrorType()
    {
        return (int)SessionUtil::getVar('_ZErrorMsgType');
    }

    /**
     * check if errors.
     *
     * @return int error type
     */
    public static function hasErrors()
    {
        $msgs = self::getErrorMessages(false);

        return (bool)!empty($msgs);
    }

    /**
     * Set an status message text.
     *
     * @param string $message String the error message
     * @param string $url     The url to redirect to (optional) (default=null)
     *
     * @return true, or redirect if url
     */
    public static function registerStatus($message, $url = null)
    {
        $message = empty($message) ? __f('Empty [%s] received.', 'message') : $message;

        self::addStatusPopup($message);

        // check if we want to redirect
        if ($url) {
            return new RedirectResponse($url, 302);
        }

        return true;
    }

    /**
     * Set a warning message text.
     *
     * @param string $message String the warning message
     * @param string $url     The url to redirect to (optional) (default=null)
     *
     * @return true, or redirect if url
     */
    public static function registerWarning($message, $url = null)
    {
        $message = empty($message) ? __f('Empty [%s] received.', 'message') : $message;

        self::addWarningPopup($message);

        // check if we want to redirect
        if ($url) {
            return new RedirectResponse($url, 302);
        }

        return true;
    }

    /**
     * Add a popup status message.
     *
     * @param string $message The status message
     *
     * @return void
     *
     * @throws InvalidArgumentException Thrown if the type provided to the internal function _addPopup is invalid
     */
    public static function addStatusPopup($message)
    {
        $message = empty($message) ? __f('Empty [%s] received.', 'message') : $message;
        self::_addPopup($message, Log::INFO);
    }

    /**
     * Add a popup warning message.
     *
     * @param string $message The warning message
     *
     * @return void
     *
     * @throws InvalidArgumentException Thrown if the type provided to the internal function _addPopup is invalid
     */
    public static function addWarningPopup($message)
    {
        $message = empty($message) ? __f('Empty [%s] received.', 'message') : $message;
        self::_addPopup($message, Log::WARNING);
    }

    /**
     * Add a popup error message.
     *
     * @param string $message The error message
     *
     * @return void
     *
     * @throws InvalidArgumentException Thrown if the type provided to the internal function _addPopup is invalid
     */
    public static function addErrorPopup($message)
    {
        $message = empty($message) ? __f('Empty [%s] received.', 'message') : $message;
        self::_addPopup($message, E_USER_ERROR);
    }

    /**
     * Add popup message to the status or error messages.
     *
     * @param string  $message The message
     * @param integer $type    The message type
     *
     * @throws InvalidArgumentException Thrown if the $type is invalid
     *
     * @return void
     */
    private static function _addPopup($message, $type = E_USER_NOTICE)
    {
        self::log($message, Log::DEBUG);
        $session = ServiceUtil::getManager()->get('session');

        if ($type === Log::INFO) {
            $session->addMessage(Zikula_Session::MESSAGE_STATUS, DataUtil::formatForDisplayHTML($message));
        } elseif ($type === Log::WARNING) {
            $session->addMessage(Zikula_Session::MESSAGE_WARNING, DataUtil::formatForDisplayHTML($message));
        } elseif ($type === E_USER_ERROR) {
            $session->addMessage(Zikula_Session::MESSAGE_ERROR, DataUtil::formatForDisplayHTML($message));
        } else {
            throw new InvalidArgumentException(__f('Invalid type %s for LogUtil::_addPopup', $type));
        }
    }

    /**
     * Register a failed authid check.
     *
     * This method calls registerError and then redirects back to the specified URL.
     *
     * @param string $url The URL to redirect to (optional) (default=null)
     *
     * @return false
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
     * @param string  $url      The URL to redirect to (optional) (default=null)
     * @param boolean $redirect Whether to redirect not logged in users to the login form (default=true)
     *
     * @return false
     */
    public static function registerPermissionError($url = null, $redirect = true)
    {
        $code = 403;
        if (!UserUtil::isLoggedIn() && $redirect) {
            if (is_null($url)) {
                $request = ServiceUtil::get('request');

                $loginArgs = [];
                if ($request->isMethod('GET')) {
                    $loginArgs['returnpage'] = urlencode(System::getCurrentUri());
                }
                $url = ModUtil::url('ZikulaUsersModule', 'user', 'login', $loginArgs);
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
     * @param string  $message The error message
     * @param integer $type    The type of error (numeric and corresponding to a HTTP status code) (optional) (default=null)
     * @param string  $url     The url to redirect to (optional) (default=null)
     *
     * @return false or system redirect if url is set
     */
    public static function registerError($message, $type = 500, $url = null)
    {
        self::log(__f('Deprecated call %s - depending on where this is called you may need to throw an exception instead', __METHOD__), Log::INFO);
        $message = empty($message) ? __f('Empty [%s] received.', 'message') : $message;

        self::addErrorPopup($message);

        // check if we want to redirect
        if ($url) {
            return new RedirectResponse($url, 302);
        }

        // since we're registering an error, it makes sense to return false here.
        // This allows the calling code to just return the result of LogUtil::registerError
        // if it wishes to return 'false' (which is what usually happens).
        return false;
    }

    /**
     * Register a failed method call due to a failed validation on the parameters passed.
     *
     * @param string $url Url to redirect to
     *
     * @return false
     */
    public static function registerArgsError($url = null)
    {
        self::log(__f('Deprecated call %s - throw an exception instead', __METHOD__), Log::INFO);

        return self::registerError(self::getErrorMsgArgs(), null, $url);
    }

    /**
     * Get the default message for an authid error.
     *
     * @return string error message
     */
    public static function getErrorMsgAuthid()
    {
        return __("Sorry! Invalid authorisation key ('authkey'). This is probably either because you pressed the 'Back' button to return to a page which does not allow that, or else because the page's authorisation key expired due to prolonged inactivity. Please refresh the page and try again.");
    }

    /**
     * Get the default message for a permission error.
     *
     * @return string error message
     */
    public static function getErrorMsgPermission()
    {
        return __('Sorry! You have not been granted access to this page.');
    }

    /**
     * Get the default message for an argument error.
     *
     * @return string error message
     */
    public static function getErrorMsgArgs()
    {
        return __('Error! The action you wanted to perform was not successful for some reason, maybe because of a problem with what you input. Please check and try again.');
    }

    /**
     * Log the given message under the given level
     *
     * @param string $msg   The message to log
     * @param string $level The log to log this message under(optional)(default='DEFAULT')
     *
     * @return void
     */
    public static function log($msg, $level = Log::DEBUG)
    {
        if (System::isInstalling()) {
            return;
        }

        $serviceManager = ServiceUtil::getManager();
        if (!$serviceManager->has('logger')) {
            return;
        }

        // @todo remove in 1.5.0 this is a BC hack - drak
        if ($level === E_USER_DEPRECATED) {
            $level = Log::DEBUG;
        }

        /** @var Log $logger */
        $logger = $serviceManager->get('logger');
        $logger->log($level, $msg);
    }
}
