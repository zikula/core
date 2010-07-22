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

    public function isPHPError($error)
    {
        switch ($error) {
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

    public function isDisplayErrorTemplate()
    {
        return (bool)$this->serviceManager['log.display_template'];
    }

  
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
