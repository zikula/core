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
 * Standard class.
 */
class Zikula_ErrorHandler_Standard extends Zikula_ErrorHandler_Base
{

    /**
     * Handles an error.
     *
     * @param integer $errno      Number of the error.
     * @param string  $errstr     Error message.
     * @param string  $errfile    Filename where the error occurred.
     * @param integer $errline    Line of the error.
     * @param string  $errcontext Context of the error.
     *
     * @return void
     */
    public function handler($errno, $errstr, $errfile='', $errline=0, $errcontext=null)
    {
        // Remove full path information if not in development mode.
        if (!System::isDevelopmentMode()) {
            $errfile = str_replace(realpath(dirname(__FILE__) . '/../..') . DIRECTORY_SEPARATOR, '', $errfile);
        }

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

        $trace = debug_backtrace();
        unset($trace[0]);
        
        // Notify all loggers
        $this->eventManager->notify($this->event->setArgs(array('trace' => $trace, 'type' => $type, 'errno' => $errno, 'errstr' => $errstr, 'errfile' => $errfile, 'errline' => $errline, 'errcontext' => $errcontext)));
        if ($this->isPHPError($errno) && System::isDevelopmentMode() && $this->showPHPErrorHandler()) {
            // allow PHP to return error
            return false;
        }
        
        
        if (!$this->isDisplayErrorTemplate()) {
            // prevent PHP from handling the event after we return
            return true;
        }

        // todo..... 

        // clear the output buffer
        while (ob_get_level()) {
            ob_end_clean();
        }

        $output = ModUtil::func('Errors', 'user', 'system',
                           array('type' => $errno,
                                 'message' => $errstr,
                                 'file' => $errfile,
                                 'line' => $errline));

        throw new Zikula_Exception_Fatal($output); // throw back to front controller for clean exit.
    }
}
