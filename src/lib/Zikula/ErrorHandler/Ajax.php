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
 * Ajax class.
 */
class Zikula_ErrorHandler_Ajax extends Zikula_AbstractErrorHandler
{
    /**
     * ErrorHandler for ajax front controller.
     *
     * @param integer $errno      Number of the error.
     * @param string  $errstr     Error message.
     * @param string  $errfile    Filename where the error occurred.
     * @param integer $errline    Line of the error.
     * @param string  $errcontext Context of the error.
     *
     * @return boolean True.
     */
    public function handler($errno, $errstr, $errfile='', $errline=0, $errcontext=null)
    {
        $this->setupHandler($errno, $errstr, $errfile, $errline, $errcontext);

        // Notify all loggers.
        $this->eventManager->notify($this->event->setArgs(array('trace' => $this->trace, 'type' => $this->type, 'errno' => $this->errno, 'errstr' => $this->errstr, 'errfile' => $this->errfile, 'errline' => $this->errline, 'errcontext' => $this->errcontext)));
        // prevent PHP handler showing here.
        $this->resetHandler();

        return true;
    }
}
