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
        $this->setupHandler($errno, $errstr, $errfile, $errline, $errcontext);

        // Notify all loggers
        $this->eventManager->notify($this->event->setArgs(array('trace' => $this->trace, 'type' => $this->type, 'errno' => $this->errno, 'errstr' => $this->errstr, 'errfile' => $this->errfile, 'errline' => $this->errline, 'errcontext' => $this->errcontext)));
        if ($this->isPHPError() && System::isDevelopmentMode() && $this->showPHPErrorHandler()) {
            // allow PHP to return error
            $this->resetHandler();
            return false;
        }
        
        
        if (!$this->isDisplayErrorTemplate()) {
            // prevent PHP from handling the event after we return
            $this->resetHandler();
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
