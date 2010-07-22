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
abstract class Zikula_ErrorHandler_Base
{
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
            case E_STRICT:
                $type = LogUtil::NOTICE;
                break;
            case E_DEPRECATED:
                $type = LogUtil::NOTICE;
                break;
            case LogUtil::ALERT:
                $type = LogUtil::ALERT;
                break;
            case LogUtil::CRIT:
                $type = LogUtil::CRIT;
                break;
            case LogUtil::DEBUG:
            case E_STRICT:
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                $type = LogUtil::DEBUG;
                break;
            case LogUtil::EMERG:
                $type = LogUtil::EMERG;
                break;
            case LogUtil::ERR:
            case E_ERROR:
            case E_USER_ERROR:
                $type = LogUtil::ERR;
                break;
            case LogUtil::INFO:
                $type = LogUtil::INFO;
                break;
            case LogUtil::NOTICE:
            case E_NOTICE:
            case E_USER_NOTICE:
                $type = LogUtil::NOTICE;
                break;
            case LogUtil::WARN:
            case E_WARNING:
            case E_USER_WARNING:
                $type = LogUtil::WARN;
                break;
            default:
                $type = LogUtil::INFO;
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
    abstract public function handler($errno, $errstr, $errfile, $errline, $errcontext);
}
