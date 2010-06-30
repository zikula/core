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
 * Zikula ErrorHandler.
 */
class Zikula_ErrorHandler
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

    /**
     * Error logging system setting.
     *
     * @var integer
     */
    protected $errorlog;

    /**
     * Type of log system setting.
     *
     * @var integer
     */
    protected $errorlogtype;

    /**
     * Display to the user flag.
     *
     * @var boolean
     */
    protected $errordisplay;

    /**
     * System writable folder.
     *
     * @var string
     */
    protected $ztemp;


    /**
     * Constructor.
     * 
     * @param Zikula_ServiceManager $serviceManager Servicemanager.
     * @param Zikula_EventManager   $eventManager   Eventmanager.
     */
    public function __construct(Zikula_ServiceManager $serviceManager, Zikula_EventManager $eventManager)
    {
        $this->serviceManager = $serviceManager;
        $this->eventManager = $eventManager;

        $this->errorlog = System::getVar('errorlog');
        $this->errorlogtype = System::getVar('errorlogtype');
        $this->errordisplay = System::getVar('errordisplay');
        $this->ztemp = DataUtil::formatForOS(System::getVar('temp'), true);
    }

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
    public function standardHandler($errno, $errstr, $errfile, $errline, $errcontext)
    {
        $event = new Zikula_Event('systemerror', null, array('errorno' => $errno, 'errstr' => $errstr, 'errfile' => $errfile, 'errline' => $errline, 'errcontext' => $errcontext));
        $this->eventManager->notify($event);

        // check for an @ suppression
        if (error_reporting() == 0 || (defined('E_DEPRECATED') && $errno == E_DEPRECATED || $errno == E_STRICT)) {
            return;
        }

        // What do we want to log?
        // 1 - Log real errors only.
        // 2 - Log everything.
        $logError = ($this->errorlog == 2 || ($this->errorlog == 1 && ($errno != E_WARNING && $errno != E_NOTICE && $errno != E_USER_WARNING && $errno != E_USER_NOTICE)));
        if ($logError == true) {
            // log the error
            $msg = DateUtil::getDatetime() . " Zikula Error: $errstr";
            if (SecurityUtil::checkPermission('::', '::', ACCESS_ADMIN)) {
                $request = System::getCurrentUri();
                $msg .= " in $errfile on line $errline for page $request";
            }
            switch ($this->errorlogtype) {
                case 0:
                    // log to the system log (default php handling....)
                    error_log($msg);
                    break;
                
                case 1:
                    // e-mail the error
                    $toaddress = System::getVar('errormailto');
                    $body = ModUtil::func('Errors', 'user', 'system', array(
                            'type' => $errno,
                            'message' => $errstr,
                            'file' => $errfile,
                            'line' => $errline));
                    ModUtil::apiFunc('Mailer', 'user', 'sendmessage', array(
                            'toaddress' => $toaddress,
                            'toname' => $toaddress,
                            'subject' => __('Error! Oh! Wow! An \'unidentified system error\' has occurred.'),
                            'body' => $body));
                    break;
                case 2:
                    // log a module specific log (based on top level module)
                    $modname = DataUtil::formatForOS(ModUtil::getName());
                    error_log($msg . "\r\n", 3, $this->ztemp . '/error_logs/' . $modname . '.log');
                    break;
                case 3:
                    // log to global error log
                    error_log($msg . "\r\n", 3, $this->ztemp . '/error_logs/error.log');
                    break;
            }
        }

        // should we display the error to the user
        if ($this->errordisplay == 0) {
            return;
        }

        // check if we want to flag up warnings and notices
        if ($this->errordisplay == 1 && ($errno == E_WARNING || $errno == E_NOTICE || $errno == E_USER_WARNING || $errno == E_USER_NOTICE)) {
            return;
        }

        // clear the output buffer
        while (ob_get_level()) {
            ob_end_clean();
        }

        // display the new output and halt the script
        header('HTTP/1.0 500 System Error');

        echo ModUtil::func('Errors', 'user', 'system',
                           array('type' => $errno,
                                 'message' => $errstr,
                                 'file' => $errfile,
                                 'line' => $errline));

        Theme::getInstance()->themefooter();

        System::shutDown();
    }

    /**
     * ErrorHandler for ajax front controller.
     *
     * @param integer $errno      Number of the error.
     * @param string  $errstr     Error message.
     * @param string  $errfile    Filename where the error occurred.
     * @param integer $errline    Line of the error.
     * @param string  $errcontext Context of the error.
     *
     * @return void
     */
    public function ajaxHandler($errno, $errstr, $errfile, $errline, $errcontext)
    {
        
    }
}
