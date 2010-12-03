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
        if ($event['stage'] & System::STAGES_SESSIONS) {
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
        if (!System::isInstalling() && System::isLegacyMode()) {
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
        if ($event['stage'] & System::STAGES_LANGS) {
            if (!System::isInstalling()) {
                ServiceUtil::loadPersistentServices();
                PluginUtil::loadPlugins(realpath(dirname(__FILE__) . "/../../plugins"), "SystemPlugin");
                EventUtil::loadPersistentEvents();
                HookUtil::loadHandlers();
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
        if ($event['stage'] & System::STAGES_AJAX) {
            $class = 'Zikula_ErrorHandler_Ajax';
        }

        $errorHandler = new $class($serviceManager);
        $serviceManager->attachService('system.errorreporting', $errorHandler);
        set_error_handler(array($errorHandler, 'handler'));
        $event->setNotified();
    }

    /**
     * Establish the necessary instances for logging.
     *
     * @param Zikula_Event $event The event to log.
     *
     * @return void
     */
    public static function setupLoggers(Zikula_Event $event)
    {
        if (!($event['stage'] & System::STAGES_CONFIG)) {
            return;
        }

        $serviceManager = ServiceUtil::getManager();
        if (!$serviceManager['log.enabled']) {
            return;
        }

        if ($serviceManager['log.to_display'] || $serviceManager['log.sql.to_display']) {
            $displayLogger = $serviceManager->attachService('zend.logger.display', new Zend_Log());
            // load writer first because of hard requires in the Zend_Log_Writer_Stream
            $writer = new Zend_Log_Writer_Stream('php://output');
            $formatter = new Zend_Log_Formatter_Simple('%priorityName% (%priority%): %message% <br />' . PHP_EOL);
            $writer->setFormatter($formatter);
            $displayLogger->addWriter($writer);
        }

        if ($serviceManager['log.to_file'] || $serviceManager['log.sql.to_file']) {
            $fileLogger = $serviceManager->attachService('zend.logger.file', new Zend_Log());
            $filename = LogUtil::getLogFileName();
            // load writer first because of hard requires in the Zend_Log_Writer_Stream
            $writer = new Zend_Log_Writer_Stream($filename);
            $formatter = new Zend_Log_Formatter_Simple('%timestamp% %priorityName% (%priority%): %message%' . PHP_EOL);

            $writer->setFormatter($formatter);
            $fileLogger->addWriter($writer);
        }
    }

    /**
     * Log an error.
     *
     * @param Zikula_Event $event The log event to log.
     *
     * @return void
     *
     * @throws Zikula_Exception_Fatal Thrown if the handler for the event is an instance of Zikula_ErrorHandler_Ajax.
     */
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
            if ($event['errline'] == 0) {
                $message = __f('PHP issued an error at line 0, so reporting entire trace to be more helpful: %1$s: %2$s', array(Zikula_ErrorHandler::translateErrorCode($event['errno']), $event['errstr']));
                $fullTrace = $event['trace'];
                array_shift($fullTrace); // shift is performed on copy so as not to disturn the event args
                foreach ($fullTrace as $trace) {
                    $file = isset($trace['file']) ? $trace['file'] : null;
                    $line = isset($trace['line']) ? $trace['line'] : null;
                    
                    if ($file && $line) {
                        $message .= ' ' . __f('traced in %1$s line %2$s', array($file, $line)) . "#\n";
                    }
                }
            } else {
                $message = __f('%1$s: %2$s in %3$s line %4$s', array(Zikula_ErrorHandler::translateErrorCode($event['errno']), $event['errstr'], $event['errfile'], $event['errline']));
            }
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

        if ($handler instanceof Zikula_ErrorHandler_Ajax) {
            if (abs($handler->getType()) <= $serviceManager['log.display_ajax_level']) {
                // autoloaders don't work inside error handlers!
                include_once 'lib/Zikula/Exception.php';
                include_once 'lib/Zikula/Exception/Fatal.php';
                throw new Zikula_Exception_Fatal($message);
            }
        }
    }

    /**
     * Listener for log.sql events.
     *
     * This listener logs the queries via Zend_Log to file / console.
     *
     * @param Zikula_Event $event Event.
     *
     * @return void
     */
    public static function logSqlQueries(Zikula_Event $event)
    {
        $serviceManager = ServiceUtil::getManager();
        if (!$serviceManager['log.enabled']) {
            return;
        }

        $message = __f('SQL Query: %s took %s sec', array($event['query'], $event['time']));

        if ($serviceManager['log.sql.to_display']) {
            $serviceManager->getService('zend.logger.display')->log($message, Zend_Log::DEBUG);
        }

        if ($serviceManager['log.sql.to_file']) {
            $serviceManager->getService('zend.logger.file')->log($message, Zend_Log::DEBUG);
        }
    }

    /**
     * Debug toolbar startup.
     *
     * @param Zikula_Event $event Event.
     *
     * @return void
     */
    public static function setupDebugToolbar(Zikula_Event $event)
    {
        if ($event['stage'] == System::STAGES_CONFIG && System::isDevelopmentMode() && $event->getSubject()->getServiceManager()->getArgument('log.to_debug_toolbar')) {
            // autoloaders don't work inside error handlers!
            include_once 'lib/Zikula/DebugToolbar/Panel/Log.php';
            $sm = $event->getSubject()->getServiceManager();

            // create definitions
            $toolbar = new Zikula_ServiceManager_Definition(
                            'Zikula_DebugToolbar',
                            array(),
                            array('addPanels' => array(0 => array(new Zikula_ServiceManager_Service('debug.toolbar.panel.version'),
                                                    new Zikula_ServiceManager_Service('debug.toolbar.panel.config'),
                                                    new Zikula_ServiceManager_Service('debug.toolbar.panel.memory'),
                                                    new Zikula_ServiceManager_Service('debug.toolbar.panel.rendertime'),
                                                    new Zikula_ServiceManager_Service('debug.toolbar.panel.sql'),
                                                    new Zikula_ServiceManager_Service('debug.toolbar.panel.view'),
                                                    new Zikula_ServiceManager_Service('debug.toolbar.panel.exec'),
                                                    new Zikula_ServiceManager_Service('debug.toolbar.panel.logs'))))
            );

            $versionPanel = new Zikula_ServiceManager_Definition('Zikula_DebugToolbar_Panel_Version');
            $configPanel = new Zikula_ServiceManager_Definition('Zikula_DebugToolbar_Panel_Config');
            $momoryPanel = new Zikula_ServiceManager_Definition('Zikula_DebugToolbar_Panel_Memory');
            $rendertimePanel = new Zikula_ServiceManager_Definition('Zikula_DebugToolbar_Panel_RenderTime');
            $sqlPanel = new Zikula_ServiceManager_Definition('Zikula_DebugToolbar_Panel_SQL');
            $viewPanel = new Zikula_ServiceManager_Definition('Zikula_DebugToolbar_Panel_View');
            $execPanel = new Zikula_ServiceManager_Definition('Zikula_DebugToolbar_Panel_Exec');
            $logsPanel = new Zikula_ServiceManager_Definition('Zikula_DebugToolbar_Panel_Log');

            // save start time (required by rendertime panel)
            $sm->setArgument('debug.toolbar.panel.rendertime.start', microtime(true));

            // register services

            $sm->registerService(new Zikula_ServiceManager_Service('debug.toolbar.panel.version', $versionPanel, true));
            $sm->registerService(new Zikula_ServiceManager_Service('debug.toolbar.panel.config', $configPanel, true));
            $sm->registerService(new Zikula_ServiceManager_Service('debug.toolbar.panel.memory', $momoryPanel, true));
            $sm->registerService(new Zikula_ServiceManager_Service('debug.toolbar.panel.rendertime', $rendertimePanel, true));
            $sm->registerService(new Zikula_ServiceManager_Service('debug.toolbar.panel.sql', $sqlPanel, true));
            $sm->registerService(new Zikula_ServiceManager_Service('debug.toolbar.panel.view', $viewPanel, true));
            $sm->registerService(new Zikula_ServiceManager_Service('debug.toolbar.panel.exec', $execPanel, true));
            $sm->registerService(new Zikula_ServiceManager_Service('debug.toolbar.panel.logs', $logsPanel, true));
            $sm->registerService(new Zikula_ServiceManager_Service('debug.toolbar', $toolbar, true));

            $em = $sm->getService('zikula.eventmanager');
            // setup rendering event listeners
            $em->attach('theme.prefooter', array('SystemListenersUtil', 'debugToolbarRendering'));

            // setup event listeners
            $em->attach('view.init', new Zikula_ServiceHandler('debug.toolbar.panel.view', 'initRenderer'));
            $em->attach('module_dispatch.preexecute', new Zikula_ServiceHandler('debug.toolbar.panel.exec', 'modexecPre'));
            $em->attach('module_dispatch.postexecute', new Zikula_ServiceHandler('debug.toolbar.panel.exec', 'modexecPost'));
            $em->attach('module_dispatch.execute_not_found', new Zikula_ServiceHandler('debug.toolbar.panel.logs', 'logExecNotFound'));
            $em->attach('log', new Zikula_ServiceHandler('debug.toolbar.panel.logs', 'log'));
            $em->attach('log.sql', new Zikula_ServiceHandler('debug.toolbar.panel.sql', 'logSql'));
            $em->attach('controller.method_not_found', new Zikula_ServiceHandler('debug.toolbar.panel.logs', 'logModControllerNotFound'));
            $em->attach('controller_api.method_not_found', new Zikula_ServiceHandler('debug.toolbar.panel.logs', 'logModControllerAPINotFound'));
        }
    }

    /**
     * Debug toolbar rendering (listener for theme.prefooter event).
     *
     * @param Zikula_Event $event Event.
     *
     * @return void
     */
    public static function debugToolbarRendering(Zikula_Event $event)
    {
        if (!$event->getSubject() instanceof Zikula_ErrorHandler_Ajax) {
            $toolbar = ServiceUtil::getManager()->getService('debug.toolbar');
            $toolbar->addHTMLToFooter();
        }
    }

    /**
     * Adds an autoloader entry for the cached (generated) doctrine models.
     *
     * @param Zikula_Event $event Event.
     *
     * @return void
     */
    public static function setupAutoloaderForGeneratedCategoryModels(Zikula_Event $event)
    {
        if ($event['stage'] == System::STAGES_CONFIG) {
            ZLoader::addAutoloader('GeneratedDoctrineModel', CacheUtil::getLocalDir('doctrinemodels'));
        }
    }

    /**
     * On an module remove hook call this listener deletes all cached (generated) doctrine models for the module.
     *
     * Listens for the 'installer.module.uninstalled' event.
     *
     * @param Zikula_Event $event Event.
     *
     * @return void
     */
    public static function deleteGeneratedCategoryModelsOnModuleRemove(Zikula_Event $event)
    {
        $moduleName = $event['name'];

        // remove generated category models for this record
        $dir = 'doctrinemodels/GeneratedDoctrineModel/' . $moduleName;
        if (file_exists(CacheUtil::getLocalDir($dir))) {
            CacheUtil::removeLocalDir($dir);
        }

        // remove saved data about the record
        $modelsInfo = ModUtil::getVar('Categories', 'EntityCategorySubclasses', array());
        foreach ($modelsInfo as $class => $info) {
            if ($info['module'] == $moduleName) {
                unset($modelsInfo[$class]);
            }
        }
        ModUtil::setVar('Categories', 'EntityCategorySubclasses', $modelsInfo);
    }

    /**
     * Core stylesheet override.
     *
     * @param Zikula_Event $event The event handler.
     *
     * @return void
     */
    public static function coreStylesheetOverride(Zikula_Event $event)
    {
        if ($event->getSubject() == 'stylesheet' && ($key = array_search('styles/core.css', $event->data)) !== false) {
            if (file_exists('config/styles/core.css')) {
                $event->data[$key] = 'config/styles/core.css';
            }

            $event->setNotified();
        }
    }

    /**
     * Dynamically add menu links to administration for hook providers.
     *
     * Listens for 'module_dispatch.postexecute' events.
     *
     * @param Zikula_Event $event The event handler.
     *
     * @return void
     */
    public static function addHooksAdminLinks(Zikula_Event $event)
    {
        // check if this is for this handler
        if (!($event['modfunc'][1] == 'getlinks' && $event['type'] == 'admin' && $event['api'] == true)) {
            return;
        }

        if (HookUtil::isSubscriberCapable($event['modname'])) {
            $event->data[] = array('url' => ModUtil::url($event['modname'], 'admin', 'hookproviders'), 'text' => __('Hook Providers'));
            return;
        }

        if (HookUtil::isProviderCapable($event['modname'])) {
            $event->data[] = array('url' => ModUtil::url($event['modname'], 'admin', 'hooksubscribers'), 'text' => __('Hook Subscribers'));
            return;
        }
    }

}
