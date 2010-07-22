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
abstract class Zikula_ErrorHandler
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

    protected $event;

    protected $errno;
    protected $errstr;
    protected $errfile;
    protected $errline;
    protected $errcontext;
    protected $type;
    protected $trace;

    /**
     * Constructor.
     *
     * @param Zikula_ServiceManager $serviceManager Servicemanager.
     * @param Zikula_EventManager   $eventManager   Eventmanager.
     */
    public function __construct(Zikula_ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        $this->eventManager = $this->serviceManager->getService('zikula.eventmanager');
        $this->event = new Zikula_Event('log', $this);
    }

    public function getType()
    {
        return $this->type;
    }

    public function getTrace()
    {
        return $this->trace;
    }

    
    public function getErrno()
    {
        return $this->errno;
    }

    public function getErrstr()
    {
        return $this->errstr;
    }

    public function getErrfile()
    {
        return $this->errfile;
    }

    public function getErrline()
    {
        return $this->errline;
    }

    public function getErrcontext()
    {
        return $this->errcontext;
    }

    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    public function getEventManager()
    {
        return $this->eventManager;
    }

    public function getEvent()
    {
        return $this->event;
    }

    public function showPHPErrorHandler()
    {
        return $this->serviceManager['log.show_php_errorhandler'];
    }

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

    public function isDisplayErrorTemplate()
    {
        return (bool)$this->serviceManager['log.display_template'];
    }

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

    public function decoratePath($errfile)
    {
        // Remove full path information if not in development mode.
        if (!System::isDevelopmentMode()) {
            $errfile = str_replace(realpath(dirname(__FILE__) . '/../..') . DIRECTORY_SEPARATOR, '', $errfile);
        }
        return $errfile;
    }

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
     * @param integer $errno      Number of the error.
     * @param string  $errstr     Error message.
     * @param string  $errfile    Filename where the error occurred.
     * @param integer $errline    Line of the error.
     * @param string  $errcontext Context of the error.
     *
     * @return boolean
     */
    abstract public function handler($errno, $errstr, $errfile='', $errline=0, $errcontext=null);
}
