<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_View
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Event handler to override templates.
 */
class SystemListeners extends Zikula_EventHandler
{

    /**
     * Setup handler definitions.
     *
     * @return void
     */
    protected function setupHandlerDefinitions()
    {
        $this->addHandlerDefinition('bootstrap.getconfig', 'initialHandlerScan', -10);
        $this->addHandlerDefinition('bootstrap.getconfig', 'getConfigFile');
        $this->addHandlerDefinition('setup.errorreporting', 'defaultErrorReporting');
        $this->addHandlerDefinition('core.preinit', 'systemCheck');
        $this->addHandlerDefinition('core.preinit', 'setupSessions');
        $this->addHandlerDefinition('core.init', 'setupLoggers');
        $this->addHandlerDefinition('log', 'errorLog');
        $this->addHandlerDefinition('core.init', 'sessionLogging');
        $this->addHandlerDefinition('session.require', 'requireSession');
        $this->addHandlerDefinition('core.init', 'systemPlugins');
        $this->addHandlerDefinition('core.preinit', 'request');
        $this->addHandlerDefinition('core.postinit', 'systemHooks');
        $this->addHandlerDefinition('core.init', 'setupDebugToolbar');
        $this->addHandlerDefinition('log.sql', 'logSqlQueries');
        $this->addHandlerDefinition('core.init', 'setupAutoloaderForGeneratedCategoryModels');
        $this->addHandlerDefinition('installer.module.uninstalled', 'deleteGeneratedCategoryModelsOnModuleRemove');
        $this->addHandlerDefinition('pageutil.addvar_filter', 'coreStylesheetOverride');
        $this->addHandlerDefinition('module_dispatch.postexecute', 'addHooksLink');
        $this->addHandlerDefinition('module_dispatch.postexecute', 'addServiceLink');
        $this->addHandlerDefinition('core.init', 'initDB');
        $this->addHandlerDefinition('core.init', 'setupCsfrProtection');
    }

    /**
     * Listen for the 'core.preinit' event.
     *
     * @param Zikula_Event $event Event.
     *
     * @return void
     */
    public function request(Zikula_Event $event)
    {
        $requestDef = new Zikula_ServiceManager_Definition('Zikula_Request_Http');
        $requestDef->addMethod('setSession', array(new Zikula_ServiceManager_Service('session')));
        $this->serviceManager->registerService(new Zikula_ServiceManager_Service('request', $requestDef));
    }

    /**
     * Listen for the 'core.init' event & STAGE_DECODEURLS.
     *
     * This is basically a hack until the routing framework takes over (drak).
     *
     * @param Zikula_Event $event Event.
     *
     * @return void
     */
    public function setupRequest(Zikula_Event $event)
    {
        $request = $this->serviceManager->getService('request');
        $module = FormUtil::getPassedValue('module', null, 'GETPOST', FILTER_SANITIZE_STRING);
        $controller = FormUtil::getPassedValue('type', null, 'GETPOST', FILTER_SANITIZE_STRING);
        $action = FormUtil::getPassedValue('func', null, 'GETPOST', FILTER_SANITIZE_STRING);
        $request->addRequest($module, $controller, $action);
    }

    /**
     * Listens for 'bootstrap.getconfig' event.
     *
     * @param Zikula_Event $event
     */
    public function initialHandlerScan(Zikula_Event $event)
    {
        $core = $this->serviceManager->getService('zikula');
        ServiceUtil::getManager($core);
        EventUtil::getManager($core);
        $core->attachHandlers('config/EventHandlers');
    }

    /**
     * Start sessions.
     *
     * Implements 'core.preinit' event.
     *
     * @param Zikula_Event $event The event handler.
     *
     * @return void
     */
    public function setupSessions(Zikula_Event $event)
    {
        $storage = new Zikula_ServiceManager_Definition('Zikula_Session_Storage_Legacy');
        $storageService = new Zikula_ServiceManager_Service('session.storage', $storage);
        $this->serviceManager->registerService($storageService);
        $session = new Zikula_ServiceManager_Definition('Zikula_Session', array($storageService));
        $this->serviceManager->registerService(new Zikula_ServiceManager_Service('session', $session));
    }

    /**
     * Listen on 'core.init' module.
     *
     * @param Zikula_Event $event
     */
    public function setupCsfrProtection(Zikula_Event $event)
    {
        if ($event['stage'] & Zikula_Core::STAGE_MODS) {
            $tokenStorageDef = new Zikula_ServiceManager_Definition('Zikula_Token_Storage_Session',
                            array(new Zikula_ServiceManager_Service('session')));
            $this->serviceManager->registerService(new Zikula_ServiceManager_Service('token.storage', $tokenStorageDef));

            $tokenGeneratorDef = new Zikula_ServiceManager_Definition('Zikula_Token_Generator',
                            array(new Zikula_ServiceManager_Service('token.storage'), System::getVar('signingkey')));
            $this->serviceManager->registerService(new Zikula_ServiceManager_Service('token.generator', $tokenGeneratorDef));

            $tokenValidatorDef = new Zikula_ServiceManager_Definition('Zikula_Token_Validate',
                            array(new Zikula_ServiceManager_Service('token.generator')));
            $this->serviceManager->registerService(new Zikula_ServiceManager_Service('token.validator', $tokenValidatorDef));
        }
    }

    /**
     * If enabled and logged in, save login name of user in Apache session variable for Apache logs.
     *
     * Implements 'core.init' event when Zikula_Core::STAGE_SESSIONS.
     *
     * @param Zikula_Event $event The event handler.
     *
     * @return void
     */
    public function sessionLogging(Zikula_Event $event)
    {
        if ($event['stage'] & Zikula_Core::STAGE_SESSIONS) {
            // If enabled and logged in, save login name of user in Apache session variable for Apache logs
            if (isset($GLOBALS['ZConfig']['Log']['log.apache_uname']) && UserUtil::isLoggedIn()) {
                if (function_exists('apache_setenv')) {
                    apache_setenv('Zikula-Username', UserUtil::getVar('uname'));
                }
            }
        }
    }

    /**
     * If enabled and logged in, save login name of user in Apache session variable for Apache logs.
     *
     * Implements 'session.require'.
     *
     * @param Zikula_Event $event The event handler.
     *
     * @return void
     */
    public function requireSession(Zikula_Event $event)
    {
        $session = $this->serviceManager->getService('session');
        try {
            if (!$session->start()) {
                throw new RuntimeException('Failed to start session');
            }
        } catch (Exception $e) {
            // session initialization failed so display templated error
            header('HTTP/1.1 503 Service Unavailable');
            require_once System::getSystemErrorTemplate('sessionfailed.tpl');
            System::shutdown();
        }
    }

    /**
     * Initialise DB connection.
     *
     * Implements 'core.init' event when Zikula_Core::STAGE_DB.
     *
     * @param Zikula_Event $event The event handler.
     *
     * @return void
     */
    public function initDB(Zikula_Event $event)
    {
        if ($event['stage'] & Zikula_Core::STAGE_DB) {
            $dbEvent = new Zikula_Event('doctrine.init_connection');
            $this->eventManager->notify($dbEvent);
        }
    }

    /**
     * Call system hooks.
     *
     * Implements 'core.postinit' event.
     *
     * This is just here for legacy systeminit hooks.
     *
     * @param Zikula_Event $event The event handler.
     *
     * @return void
     */
    public function systemHooks(Zikula_Event $event)
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
     * Implements 'core.init' event when Zikula_Core::STAGE_LANGS.
     *
     * @param Zikula_Event $event The event handler.
     *
     * @return void
     */
    public function systemPlugins(Zikula_Event $event)
    {
        if ($event['stage'] & Zikula_Core::STAGE_LANGS) {
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
     * Implements 'setup.errorreporting' event.
     *
     * @param Zikula_Event $event The event.
     *
     * @return void
     */
    public function defaultErrorReporting(Zikula_Event $event)
    {
        if (!$this->serviceManager['log.enabled']) {
            return;
        }

        if ($this->serviceManager->hasService('system.errorreporting')) {
            return;
        }

        $class = 'Zikula_ErrorHandler_Standard';
        if ($event['stage'] & Zikula_Core::STAGE_AJAX) {
            $class = 'Zikula_ErrorHandler_Ajax';
        }

        $errorHandler = new $class($this->serviceManager);
        $this->serviceManager->attachService('system.errorreporting', $errorHandler);
        set_error_handler(array($errorHandler, 'handler'));
        $event->setNotified();
    }

    /**
     * Establish the necessary instances for logging.
     *
     * Implements 'core.init' event when Zikula_Core::STAGE_CONFIG.
     *
     * @param Zikula_Event $event The event to log.
     *
     * @return void
     */
    public function setupLoggers(Zikula_Event $event)
    {
        if (!($event['stage'] & Zikula_Core::STAGE_CONFIG)) {
            return;
        }

        if (!$this->serviceManager['log.enabled']) {
            return;
        }

        if ($this->serviceManager['log.to_display'] || $this->serviceManager['log.sql.to_display']) {
            $displayLogger = $this->serviceManager->attachService('zend.logger.display', new Zend_Log());
            // load writer first because of hard requires in the Zend_Log_Writer_Stream
            $writer = new Zend_Log_Writer_Stream('php://output');
            $formatter = new Zend_Log_Formatter_Simple('%priorityName% (%priority%): %message% <br />' . PHP_EOL);
            $writer->setFormatter($formatter);
            $displayLogger->addWriter($writer);
        }

        if ($this->serviceManager['log.to_file'] || $this->serviceManager['log.sql.to_file']) {
            $fileLogger = $this->serviceManager->attachService('zend.logger.file', new Zend_Log());
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
     * Implements 'log' event.
     *
     * @param Zikula_Event $event The log event to log.
     *
     * @return void
     *
     * @throws Zikula_Exception_Fatal Thrown if the handler for the event is an instance of Zikula_ErrorHandler_Ajax.
     */
    public function errorLog(Zikula_Event $event)
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

        if ($this->serviceManager['log.to_display'] && !$handler instanceof Zikula_ErrorHandler_Ajax) {
            if (abs($handler->getType()) <= $this->serviceManager['log.display_level']) {
                $this->serviceManager->getService('zend.logger.display')->log($message, abs($event['type']));
            }
        }

        if ($this->serviceManager['log.to_file']) {
            if (abs($handler->getType()) <= $this->serviceManager['log.file_level']) {
                $this->serviceManager->getService('zend.logger.file')->log($message, abs($event['type']));
            }
        }

        if ($handler instanceof Zikula_ErrorHandler_Ajax) {
            if (abs($handler->getType()) <= $this->serviceManager['log.display_ajax_level']) {
                // autoloaders don't work inside error handlers!
                include_once 'lib/Zikula/Exception.php';
                include_once 'lib/Zikula/Exception/Fatal.php';
                throw new Zikula_Exception_Fatal($message);
            }
        }
    }

    /**
     * Listener for 'log.sql' events.
     *
     * This listener logs the queries via Zend_Log to file / console.
     *
     * @param Zikula_Event $event Event.
     *
     * @return void
     */
    public function logSqlQueries(Zikula_Event $event)
    {
        if (!$this->serviceManager['log.enabled']) {
            return;
        }

        $message = __f('SQL Query: %s took %s sec', array($event['query'], $event['time']));

        if ($this->serviceManager['log.sql.to_display']) {
            $this->serviceManager->getService('zend.logger.display')->log($message, Zend_Log::DEBUG);
        }

        if ($this->serviceManager['log.sql.to_file']) {
            $this->serviceManager->getService('zend.logger.file')->log($message, Zend_Log::DEBUG);
        }
    }

    /**
     * Debug toolbar startup.
     *
     * Implements 'core.init' event when Zikula_Core::STAGE_CONFIG in development mode.
     *
     * @param Zikula_Event $event Event.
     *
     * @return void
     */
    public function setupDebugToolbar(Zikula_Event $event)
    {
        if ($event['stage'] == Zikula_Core::STAGE_CONFIG && System::isDevelopmentMode() && $event->getSubject()->getServiceManager()->getArgument('log.to_debug_toolbar')) {
            // autoloaders don't work inside error handlers!
            include_once 'lib/Zikula/DebugToolbar/Panel/Log.php';

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
            $this->serviceManager->setArgument('debug.toolbar.panel.rendertime.start', microtime(true));

            // register services
            $this->serviceManager->registerService(new Zikula_ServiceManager_Service('debug.toolbar.panel.version', $versionPanel, true));
            $this->serviceManager->registerService(new Zikula_ServiceManager_Service('debug.toolbar.panel.config', $configPanel, true));
            $this->serviceManager->registerService(new Zikula_ServiceManager_Service('debug.toolbar.panel.memory', $momoryPanel, true));
            $this->serviceManager->registerService(new Zikula_ServiceManager_Service('debug.toolbar.panel.rendertime', $rendertimePanel, true));
            $this->serviceManager->registerService(new Zikula_ServiceManager_Service('debug.toolbar.panel.sql', $sqlPanel, true));
            $this->serviceManager->registerService(new Zikula_ServiceManager_Service('debug.toolbar.panel.view', $viewPanel, true));
            $this->serviceManager->registerService(new Zikula_ServiceManager_Service('debug.toolbar.panel.exec', $execPanel, true));
            $this->serviceManager->registerService(new Zikula_ServiceManager_Service('debug.toolbar.panel.logs', $logsPanel, true));
            $this->serviceManager->registerService(new Zikula_ServiceManager_Service('debug.toolbar', $toolbar, true));

            // setup rendering event listeners
            $this->eventManager->attach('theme.prefooter', array($this, 'debugToolbarRendering'));
            $this->eventManager->attach('theme.postfooter', array($this, 'debugToolbarRendering'));

            // setup event listeners
            $this->eventManager->attach('view.init', new Zikula_ServiceHandler('debug.toolbar.panel.view', 'initRenderer'));
            $this->eventManager->attach('module_dispatch.preexecute', new Zikula_ServiceHandler('debug.toolbar.panel.exec', 'modexecPre'), 20);
            $this->eventManager->attach('module_dispatch.postexecute', new Zikula_ServiceHandler('debug.toolbar.panel.exec', 'modexecPost'), 20);
            $this->eventManager->attach('module_dispatch.execute_not_found', new Zikula_ServiceHandler('debug.toolbar.panel.logs', 'logExecNotFound'), 20);
            $this->eventManager->attach('log', new Zikula_ServiceHandler('debug.toolbar.panel.logs', 'log'));
            $this->eventManager->attach('log.sql', new Zikula_ServiceHandler('debug.toolbar.panel.sql', 'logSql'));
            $this->eventManager->attach('controller.method_not_found', new Zikula_ServiceHandler('debug.toolbar.panel.logs', 'logModControllerNotFound'), 20);
            $this->eventManager->attach('controller_api.method_not_found', new Zikula_ServiceHandler('debug.toolbar.panel.logs', 'logModControllerAPINotFound'), 20);
        }
    }

    /**
     * Debug toolbar rendering (listener for 'theme.prefooter' and 'theme.postfooter' events).
     *
     * @param Zikula_Event $event Event.
     *
     * @return void
     */
    public function debugToolbarRendering(Zikula_Event $event)
    {
        if (!$event->getSubject() instanceof Zikula_ErrorHandler_Ajax) {
            if ($event->getName() == 'theme.prefooter') {
                // force object construction (debug toolbar constructor registers javascript and css files via PageUtil)
                $this->serviceManager->getService('debug.toolbar');
            } else {
                $toolbar = $this->serviceManager->getService('debug.toolbar');
                $html = $toolbar->asHTML() . "\n</body>";
                $event->setData(str_replace('</body>', $html, $event->getData()));
            }
        }
    }

    /**
     * Adds an autoloader entry for the cached (generated) doctrine models.
     *
     * Implements 'core.init' events when Zikula_Core::STAGE_CONFIG.
     *
     * @param Zikula_Event $event Event.
     *
     * @return void
     */
    public function setupAutoloaderForGeneratedCategoryModels(Zikula_Event $event)
    {
        if ($event['stage'] == Zikula_Core::STAGE_CONFIG) {
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
    public function deleteGeneratedCategoryModelsOnModuleRemove(Zikula_Event $event)
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
     * Implements 'pageutil.addvar_filter' event.
     *
     * @param Zikula_Event $event The event handler.
     *
     * @return void
     */
    public function coreStylesheetOverride(Zikula_Event $event)
    {
        if ($event->getSubject() == 'stylesheet' && ($key = array_search('style/core.css', $event->data)) !== false) {
            if (file_exists('config/style/core.css')) {
                $event->data[$key] = 'config/style/core.css';
            }

            $event->setNotified();
        }
    }

    /**
     * Dynamically add Hooks link to administration.
     *
     * Listens for 'module_dispatch.postexecute' events.
     *
     * @param Zikula_Event $event The event handler.
     *
     * @return void
     */
    public function addHooksLink(Zikula_Event $event)
    {
        // check if this is for this handler
        if (!($event['modfunc'][1] == 'getlinks' && $event['type'] == 'admin' && $event['api'] == true)) {
            return;
        }

        if (!SecurityUtil::checkPermission($event['modname'] . '::Hooks', '::', ACCESS_ADMIN)) {
            return;
        }

        // return if we don't have any hook providers
        $hookproviders = HookUtil::getHookProviders();
        if (empty($hookproviders)) {
            return;
        }

        $event->data[] = array(
                'url' => ModUtil::url($event['modname'], 'admin', 'hooks'),
                'text' => __('Hooks'),
                'class' => 'z-icon-es-attach'
        );
    }

    /**
     * Dynamically add menu links to administration for system services.
     *
     * Listens for 'module_dispatch.postexecute' events.
     *
     * @param Zikula_Event $event The event handler.
     *
     * @return void
     */
    public function addServiceLink(Zikula_Event $event)
    {
        // check if this is for this handler
        if (!($event['modfunc'][1] == 'getlinks' && $event['type'] == 'admin' && $event['api'] == true)) {
            return;
        }

        // notify EVENT here to gather any system service links
        $args = array('modname' => $event->getArg('modname'));
        $localevent = new Zikula_Event('module_dispatch.service_links', $event->getSubject(), $args);
        $this->eventManager->notify($localevent);
        $sublinks = $localevent->getData();

        if (!empty($sublinks)) {
            $event->data[] = array(
                    'url' => ModUtil::url($event['modname'], 'admin', 'moduleservices'),
                    'text' => __('Services'),
                    'class' => 'z-icon-es-exec', //could use z-icon-es-attach
                    'links' => $sublinks);
        }
    }

    /**
     * Listens for 'bootstrap.getconfig'
     * 
     * @param Event $event
     */
    public function getConfigFile(Zikula_Event $event)
    {
        if (is_readable('config/personal_config.php')) {
            include 'config/personal_config.php';
        } else {
            include 'config/config.php';
        }

        if (is_readable('config/multisites_config.php')) {
            include 'config/multisites_config.php';
        }

        foreach ($GLOBALS['ZConfig'] as $config) {
            $event->getSubject()->getServiceManager()->loadArguments($config);
        }

        $event->setNotified();
    }

    /**
     * Perform some checks that might result in a die() upon failure.
     *
     * Listens on the 'core.preinit' event.
     *
     * @param Zikula_Event $event Event.
     * 
     * @return void
     */
    public function systemCheck(Zikula_Event $event)
    {
        $die = false;

        if (get_magic_quotes_runtime ()) {
            echo __('Error! Zikula does not support PHP magic_quotes_runtime - please disable this feature in php.ini.');
            $die = true;
        }

        if (ini_get('magic_quotes_gpc')) {
            echo __('Error! Zikula does not support PHP magic_quotes_gpc = On - please disable this feature in your php.ini file.');
            $die = true;
        }

        if (ini_get('register_globals')) {
            echo __('Error! Zikula does not support PHP register_globals = On - please disable this feature in your php.ini or .htaccess file.');
            $die = true;
        }

        // check PHP version, shouldn't be necessary, but....
        if (version_compare(PHP_VERSION, '5.2.6', '>=') == false) {
            echo __f('Error! Zikula requires PHP version 5.2.6+. Your server seems to be using version %s.', PHP_VERSION);
            $die = true;
        }

        // token_get_all needed for Smarty
        if (!function_exists('token_get_all')) {
            echo __("Error! PHP 'token_get_all()' is required but unavailable.");
            $die = true;
        }

        // mb_string is needed too
        if (!function_exists('mb_get_info')) {
            echo __("Error! PHP must have the mbstring extension loaded.");
            $die = true;
        }

        if (!function_exists('fsockopen')) {
            echo __("Error! The PHP function 'fsockopen()' is needed within the Zikula mailer module, but is not available.");
            $die = true;
        }

        if (System::isDevelopmentMode() || System::isInstalling()) {
            $temp = $this->serviceManager->getArgument('temp');

            $folders = array(
                    $temp,
                    "$temp/error_logs",
                    "$temp/view_compiled",
                    "$temp/view_cache",
                    "$temp/Theme_compiled",
                    "$temp/Theme_cache",
                    "$temp/Theme_Config",
                    "$temp/Theme_cache",
                    "$temp/purifierCache",
                    "$temp/idsTmp"
            );

            foreach ($folders as $folder) {
                if (!is_dir($folder)) {
                    mkdir($folder, $this->serviceManager->getArgument('system.chmod_dir'), true);
                }
                if (!is_writable($folder)) {
                    echo __f("Error! Folder '%s' was not found.", $folder) . '<br />';
                    $die = true;
                }
            }
        }
        if ($die == true) {
            exit;
        }
    }

}