<?php
/**
 * Copyright 2010 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_Core
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Zikula ErrorHandler base class.
 */
abstract class Zikula_AbstractErrorHandler
{
    const EMERG = 0; // Emergency: system is unusable
    const ALERT = -1; // Alert: action must be taken immediately
    const CRIT = -2; // Critical: critical conditions
    const ERR = -3; // Error: error conditions
    const WARN = -4; // Warning: warning conditions
    const NOTICE = -5; // Notice: normal but significant condition
    const INFO = -6; // Informational: informational messages
    const DEBUG = -7; // Debug: debug messages

    /**
     * ServiceManager instance.
     *
     * @var Zikula_ServiceManager
     */
    protected $serviceManager;

    /**
     * EventManager instance.
     *
     * @var Zikula_EventManager
     */
    protected $eventManager;

    /**
     * The log event instance.
     *
     * @var Zikula_Event
     */
    protected $event;

    /**
     * The PHP or user error number.
     *
     * @var integer
     */
    protected $errno;

    /**
     * The error message.
     *
     * @var string
     */
    protected $errstr;

    /**
     * The name of the file in which the error was raised.
     *
     * @var string
     */
    protected $errfile;

    /**
     * The line number of the file on which the error was raised.
     *
     * @var integer
     */
    protected $errline;

    /**
     * The array that points to the active symbol table at the point the error occurred.
     *
     * In other words, errcontext will contain an array of every variable that existed in the scope the error was
     * triggered in. The error handler must not modify error context.
     *
     * @var array
     */
    protected $errcontext;

    /**
     * The computed error type.
     *
     * Values:
     * <ul>
     *   <li>Zikula_AbstractErrorHandler::EMERG</li>
     *   <li>Zikula_AbstractErrorHandler::ALERT</li>
     *   <li>Zikula_AbstractErrorHandler::CRIT</li>
     *   <li>Zikula_AbstractErrorHandler::ERR</li>
     *   <li>Zikula_AbstractErrorHandler::WARN</li>
     *   <li>Zikula_AbstractErrorHandler::NOTICE</li>
     *   <li>Zikula_AbstractErrorHandler::INFO</li>
     *   <li>Zikula_AbstractErrorHandler::DEBUG</li>
     * </ul>
     *
     * @var integer
     */
    protected $type;

    /**
     * The array of {@link debug_backtrace()} information.
     *
     * @var array
     */
    protected $trace;

    /**
     * Constructor.
     *
     * @param Zikula_ServiceManager $serviceManager Servicemanager.
     */
    public function __construct(Zikula_ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        $this->eventManager = $this->serviceManager->getService('zikula.eventmanager');
        $this->event = new Zikula_Event('log', $this);
    }

    /**
     * Retrieve the computed error type.
     *
     * @return integer The error type.
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Retrieve the {@link debug_backtrace()} information.
     *
     * @return array The backtrace information.
     */
    public function getTrace()
    {
        return $this->trace;
    }

    /**
     * Retrieve the error number.
     *
     * @return integer The error number.
     */
    public function getErrno()
    {
        return $this->errno;
    }

    /**
     * Retrieve the error message.
     *
     * @return string The error message.
     */
    public function getErrstr()
    {
        return $this->errstr;
    }

    /**
     * Retrieve the name of the file in which the error was raised.
     *
     * @return string The file name.
     */
    public function getErrfile()
    {
        return $this->errfile;
    }

    /**
     * Retrieve the line number on which the error was raised.
     *
     * @return integer The line number.
     */
    public function getErrline()
    {
        return $this->errline;
    }

    /**
     * Retrieve the array that points to the active symbol table at the point the error occurred.
     *
     * In other words, errcontext will contain an array of every variable that existed in the scope the error was
     * triggered in. The error handler must not modify error context.
     *
     * @return array The error context information.
     */
    public function getErrcontext()
    {
        return $this->errcontext;
    }

    /**
     * Retrieve the service manager instance.
     *
     * @return Zikula_ServiceManager The service manager instance.
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * Retrieve the event manager instance.
     *
     * @return Zikula_EventManager The event manager instance.
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }

    /**
     * Retrieve the log event instance.
     *
     * @return Zikula_Event The log event instance.
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Indicates whether the PHP error handler should be displayed.
     *
     * Set in config.php or personal_config.php.
     *
     * @return integer|boolean True (or equivalent) to show the PHP error handler.
     */
    public function showPHPErrorHandler()
    {
        return $this->serviceManager['log.show_php_errorhandler'];
    }

    /**
     * Indicates whether the error number represents a PHP error or not.
     *
     * @return bool True if the error number ({@link $errno}) is a PHP error, otherwise false.
     */
    public function isPHPError()
    {
        switch ($this->errno) {
            case E_NOTICE:
            case E_USER_NOTICE:
            case E_WARNING:
            case E_USER_WARNING:
            case E_ERROR:
            case E_USER_ERROR:
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
            case E_STRICT:
            case E_RECOVERABLE_ERROR:
                return true;
            default:
                return false;
        }
    }

    /**
     * Resets the error handler.
     *
     * @return void
     */
    public function resetHandler()
    {
        $this->errno = null;
        $this->errstr = null;
        $this->errfile = null;
        $this->errline = null;
        $this->errcontext = null;
        $this->type = null;
        $this->trace = null;
    }

    /**
     * Sets up the error handler with error information.
     *
     * @param integer $errno      The error number.
     * @param string  $errstr     The error message.
     * @param string  $errfile    The file name in which the error was raised.
     * @param integer $errline    The line number on which the error was raised.
     * @param array   $errcontext The error context information.
     *
     * @return void
     */
    public function setupHandler($errno, $errstr, $errfile='', $errline=0, $errcontext=null)
    {
        $this->errno = $errno;
        $this->errstr = $errstr;
        $this->errfile = $this->decoratePath($errfile);
        $this->errline = $errline;
        $this->errcontext = $errcontext;
        $this->type = $this->decodeError($errno);
        $this->trace = debug_backtrace();
        unset($this->trace[0]);
    }

    /**
     * Indicates whether log.display_template is set in the config.php file (or personal_config.php file).
     *
     * @return boolean True if set, otherwise false.
     */
    public function isDisplayErrorTemplate()
    {
        return (bool)$this->serviceManager['log.display_template'];
    }

    /**
     * Decode the error number given with the error to the internal type.
     *
     * @param integer $errno The error number.
     *
     * @return integer The internal error {@link $type}.
     */
    public function decodeError($errno)
    {
        // decode the error type
        switch ($errno) {
            case self::ALERT:
                $type = self::ALERT;
                break;
            case self::CRIT:
                $type = self::CRIT;
                break;
            case self::DEBUG:
            case E_STRICT:
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                $type = self::DEBUG;
                break;
            case self::EMERG:
                $type = self::EMERG;
                break;
            case self::ERR:
            case E_ERROR:
            case E_USER_ERROR:
                $type = self::ERR;
                break;
            case self::INFO:
                $type = self::INFO;
                break;
            case self::NOTICE:
            case E_NOTICE:
            case E_USER_NOTICE:
                $type = self::NOTICE;
                break;
            case self::WARN:
            case E_WARNING:
            case E_USER_WARNING:
                $type = self::WARN;
                break;
            default:
                $type = self::INFO;
                break;
        }

        return $type;
    }

    /**
     * Adjusts the error file name, removing path information if the system is not in development mode.
     *
     * @param string $errfile The name of the file in which the error was raised.
     *
     * @return The name of the file in which the error was raised, without path information if the system is not in
     *              development mode.
     */
    public function decoratePath($errfile)
    {
        // Remove full path information if not in development mode.
        if (!System::isDevelopmentMode()) {
            $rootpath = realpath('.') . DIRECTORY_SEPARATOR;
            if (strpos($errfile, $rootpath)) {
                $errfile = str_replace($rootpath, '', $errfile);
            } else {
                $errfile = basename($errfile);
            }
        }

        return $errfile;
    }

    /**
     * Translate the error number/type into a human-readable string.
     *
     * @param integer $code The error number/type.
     *
     * @return string The human-readable string corresponding to that number/type.
     */
    public static function translateErrorCode($code)
    {
        switch ($code) {
            case E_NOTICE:
                $word = 'E_NOTICE';
                break;
            case E_USER_NOTICE:
                $word = 'E_USER_NOTICE';
                break;
            case E_WARNING:
                $word = 'E_WARNING';
                break;
            case E_USER_WARNING:
                $word = 'E_USER_WARNING';
                break;
            case E_ERROR:
                $word = 'E_ERROR';
                break;
            case E_USER_ERROR:
                $word = 'E_USER_ERROR';
                break;
            case E_STRICT:
                $word = 'E_STRICT';
                break;
            case E_DEPRECATED:
                $word = 'E_DEPRECATED';
                break;
            case E_USER_DEPRECATED:
                $word = 'E_USER_DEPRECATED';
                break;
            case self::EMERG:
                $word = 'EMERG';
                break;
            case self::ALERT:
                $word = 'ALERT';
                break;
            case self::CRIT:
                $word = 'CRIT';
                break;
            case self::ERR:
                $word = 'ERR';
                break;
            case self::WARN:
                $word = 'WARN';
                break;
            case self::NOTICE:
                $word = 'NOTICE';
                break;
            case self::INFO:
                $word = 'INFO';
                break;
            case self::DEBUG:
                $word = 'DEBUG';
                break;
            default:
                return $code;
                break;
        }

        return $word;
    }

    /**
     * ErrorHandler.
     *
     * @param integer $errno      The error number.
     * @param string  $errstr     The error message.
     * @param string  $errfile    Filename where the error occurred.
     * @param integer $errline    Line number on which the error occurred.
     * @param string  $errcontext Context of the error.
     *
     * @return boolean
     */
    abstract public function handler($errno, $errstr, $errfile='', $errline=0, $errcontext=null);
}
