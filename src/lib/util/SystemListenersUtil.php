<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Util
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * System listeners util.
 */
class SystemListenersUtil
{

    /**
     * If enabled and logged in, save login name of user in Apache session variable for Apache logs.
     *
     * @param Zikula_Event $event The event handler.
     *
     * @return void
     */
    public static function sessionLogging(Zikula_Event $event)
    {
        if ($event['stage'] & System::CORE_STAGES_SESSIONS) {
            // If enabled and logged in, save login name of user in Apache session variable for Apache logs
            if (isset($GLOBALS['ZConfig']['Log']['log.apache_uname']) && UserUtil::isLoggedIn()) {
                if (function_exists('apache_setenv')) {
                    apache_setenv('Zikula-Username', UserUtil::getVar('uname'));
                }
            }
        }
    }

    /**
     * Call system hooks.
     *
     * @param Zikula_Event $event The event handler.
     *
     * @return void
     */
    public static function systemHooks(Zikula_Event $event)
    {
        if (!System::isInstalling()) {
            // call system init hooks
            $systeminithooks = FormUtil::getPassedValue('systeminithooks', 'yes', 'GETPOST');
            if (SecurityUtil::checkPermission('::', '::', ACCESS_ADMIN) && (isset($systeminithooks) && $systeminithooks == 'no')) {
                // omit system hooks if requested by an administrator
            } else {
                ModUtil::callHooks('zikula', 'systeminit', 0, array('module' => 'zikula'));
            }
        }
    }

    /**
     * Load system plugins.
     *
     * @param Zikula_Event $event The event handler.
     *
     * @return void
     */
    public static function systemPlugins(Zikula_Event $event)
    {
        if ($event['stage'] & System::CORE_STAGES_LANGS) {
            if (!System::isInstalling()) {
                PluginUtil::loadPlugins(realpath(dirname(__FILE__) . "/../../plugins"), "SystemPlugin");
                EventUtil::loadPersistentEvents();
            }
        }
    }

    /**
     * Setup default error reporting.
     *
     * @param Zikula_Event $event The event.
     *
     * @return void
     */
    public static function defaultErrorReporting(Zikula_Event $event)
    {
        $serviceManager = ServiceUtil::getManager();

        if (!$serviceManager['log.enabled']) {
            return;
        }
        
        if ($serviceManager->hasService('system.errorreporting')) {
            return;
        }

        $class = 'Zikula_ErrorHandler_Standard';
        if ($event['stage'] & System::CORE_STAGES_AJAX) {
            $class = 'Zikula_ErrorHandler_Ajax';
        }
        
        $errorHandler = new $class($serviceManager);
        $serviceManager->attachService('system.errorreporting', $errorHandler);
        set_error_handler(array($errorHandler, 'handler'));
        $event->setNotified();
    }

    public static function setupLoggers(Zikula_Event $event)
    {
        if (!($event['stage'] & System::CORE_STAGES_CONFIG)) {
            return;
        }

        $serviceManager = ServiceUtil::getManager();
        if (!$serviceManager['log.enabled']) {
            return;
        }
        
        if ($serviceManager['log.to_display']) {
            $displayLogger = $serviceManager->attachService('zend.logger.display', new Zend_Log());
            // load writer first because of hard requires in the Zend_Log_Writer_Stream
            $writer = new Zend_Log_Writer_Stream('php://output');
            $formatter = new Zend_Log_Formatter_Simple('%priorityName% (%priority%): %message% <br />' . PHP_EOL);
            $writer->setFormatter($formatter);
            $displayLogger->addWriter($writer);
        }
        if ($serviceManager['log.to_file']) {
            $fileLogger = $serviceManager->attachService('zend.logger.file', new Zend_Log());
            $filename = LogUtil::getLogFileName();
            // load writer first because of hard requires in the Zend_Log_Writer_Stream
            $writer = new Zend_Log_Writer_Stream($filename);
            $formatter = new Zend_Log_Formatter_Simple('%timestamp% %priorityName% (%priority%): %message%' . PHP_EOL);
            
            $writer->setFormatter($formatter);
            $fileLogger->addWriter($writer);
        }
    }

    public static function errorLog(Zikula_Event $event)
    {
        // Check for error supression.  if error @ supression was used.
        // $errno wil still contain the real error that triggered the handler - drak
        if (error_reporting() == 0) {
            return;
        }

        $handler = $event->getSubject();

        // array('trace' => $trace, 'type' => $type, 'errno' => $errno, 'errstr' => $errstr, 'errfile' => $errfile, 'errline' => $errline, 'errcontext' => $errcontext)
        $message = $event['errstr'];
        if (is_string($event['errstr'])) {
            $message = __f("%s: %s in %s line %s", array(Zikula_ErrorHandler::translateErrorCode($event['errno']), $event['errstr'], $event['errfile'], $event['errline']));
        }

        $serviceManager = $event->getSubject()->getServiceManager();

        if ($serviceManager['log.to_display'] && !$handler instanceof Zikula_ErrorHandler_Ajax) {
            if (abs($handler->getType()) <= $serviceManager['log.display_level']) {
                $serviceManager->getService('zend.logger.display')->log($message, abs($event['type']));
            }
        }

        if ($serviceManager['log.to_file']) {
            if (abs($handler->getType()) <= $serviceManager['log.file_level']) {
                $serviceManager->getService('zend.logger.file')->log($message, abs($event['type']));
            }
        }

//        $trace = $event['trace'];
//        unset($trace[0]);
//        foreach ($trace as $key => $var) {
//            if (isset($trace[$key]['object'])) {
//                unset($trace[$key]['object']);
//            }
//            if (isset($trace[$key]['args'])) {
//                unset($trace[$key]['args']);
//            }
//        }

        if ($handler instanceof Zikula_ErrorHandler_Ajax) {
            throw new Zikula_Exception_Fatal($message);
            AjaxUtil::error($message);
        }
    }

}

