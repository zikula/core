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
class Zikula_ErrorHandler_Standard extends Zikula_AbstractErrorHandler
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
     * @return boolean
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

        // obey reporing level
        if (abs($this->getType()) > $this->serviceManager['log.display_level']) {
            return false;
        }

        // unless in development mode, exit.
        if (!$this->serviceManager['log.display_template']) {
            return false;
        }

        // if we get this far, display template
        echo ModUtil::func('Errors', 'user', 'system',
                           array('type' => $this->errno,
                                 'message' => $this->errstr,
                                 'file' => $this->errfile,
                                 'line' => $this->errline));
        Zikula_View_Theme::getInstance()->themefooter();
        System::shutDown();
    }
}
