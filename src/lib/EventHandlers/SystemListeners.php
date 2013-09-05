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

use Doctrine\Common\Annotations\AnnotationRegistry;
use Zikula_Request_Http as Request;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\Response;

/**
 * Event handler to override templates.
 */
class SystemListeners extends Zikula_AbstractEventHandler
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
        $this->addHandlerDefinition('core.init', 'setupLoggers');
        $this->addHandlerDefinition('log', 'errorLog');
        $this->addHandlerDefinition('core.init', 'sessionLogging');
        $this->addHandlerDefinition('session.require', 'requireSession');
        $this->addHandlerDefinition('core.init', 'systemPlugins');
        $this->addHandlerDefinition('core.init', 'setupRequest');
        $this->addHandlerDefinition('core.preinit', 'request');
        $this->addHandlerDefinition('core.postinit', 'systemHooks');
        $this->addHandlerDefinition('core.init', 'setupDebugToolbar');
        $this->addHandlerDefinition('log.sql', 'logSqlQueries');
        $this->addHandlerDefinition('core.init', 'setupAutoloaderForGeneratedCategoryModels');
        $this->addHandlerDefinition('installer.module.uninstalled', 'deleteGeneratedCategoryModelsOnModuleRemove');
        $this->addHandlerDefinition('pageutil.addvar_filter', 'coreStylesheetOverride');
        $this->addHandlerDefinition('module_dispatch.postexecute', 'addHooksLink');
        $this->addHandlerDefinition('module_dispatch.postexecute', 'addServiceLink');
        $this->addHandlerDefinition('core.preinit', 'initDB');
        $this->addHandlerDefinition('core.init', 'setupCsfrProtection');
        $this->addHandlerDefinition('theme.init', 'clickJackProtection');
        $this->addHandlerDefinition('frontcontroller.predispatch', 'sessionExpired', 3);
        $this->addHandlerDefinition('frontcontroller.predispatch', 'siteOff', 7);
        $this->addHandlerDefinition('core.postinit', 'doctrineExtensions');
    }

    /**
     * Event: 'frontcontroller.predispatch'.
     *
     * @param Zikula_Event $event
     *
     * @return void
     */
    public function sessionExpired(Zikula_Event $event)
    {
        if (SessionUtil::hasExpired()) {
            // Session has expired, display warning
            $response = new Response(ModUtil::apiFunc('ZikulaUsersModule', 'user', 'expiredsession', 403));
            $response = Zikula_View_Theme::getInstance()->themefooter($response);
            $response->send();
            System::shutdown();
        }
    }

    /**
     * Listens for 'frontcontroller.predispatch'.
     *
     * @param Zikula_Event $event
     *
     * @return void
     */
    public function siteOff(Zikula_Event $event)
    {
        // Get variables
        $module = FormUtil::getPassedValue('module', '', 'GETPOST', FILTER_SANITIZE_STRING);
        $func = FormUtil::getPassedValue('func', '', 'GETPOST', FILTER_SANITIZE_STRING);

        // Check for site closed
        if (System::getVar('siteoff') && !SecurityUtil::checkPermission('ZikulaSettingsModule::', 'SiteOff::', ACCESS_ADMIN) && !($module == 'Users' && $func == 'siteOffLogin') || (Zikula_Core::VERSION_NUM != System::getVar('Version_Num'))) {
            if (SecurityUtil::checkPermission('ZikulaUsersModule::', '::', ACCESS_OVERVIEW) && UserUtil::isLoggedIn()) {
                UserUtil::logout();
            }
            header('HTTP/1.1 503 Service Unavailable');
            require_once System::getSystemErrorTemplate('siteoff.tpl');
            System::shutdown();
        }
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
        return;
        $requestDef = new Definition('Zikula_Request_Http');
        $requestDef->addMethod('setSession', array(new Reference('session')));
        $this->serviceManager->setDefinition('request', $requestDef);
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
        if ($event['stage'] & Zikula_Core::STAGE_DECODEURLS) {
            $request = $this->serviceManager->get('request');
            // temporary workaround: reinitialize request information after having decoded short urls

            $module = FormUtil::getPassedValue('module', null, 'GETPOST', FILTER_SANITIZE_STRING);
            $controller = FormUtil::getPassedValue('type', null, 'GETPOST', FILTER_SANITIZE_STRING);
            $action = FormUtil::getPassedValue('func', null, 'GETPOST', FILTER_SANITIZE_STRING);
            //$request->addRequest($module, $controller, $action);
        }
    }

    /**
     * Listens for 'bootstrap.getconfig' event.
     *
     * @param Zikula_Event $event Event.
     *
     * @return void
     */
    public function initialHandlerScan(Zikula_Event $event)
    {
        $core = $this->serviceManager->get('zikula');
        ServiceUtil::getManager($core);
        EventUtil::getManager($core);
        $core->attachHandlers('config/EventHandlers');
    }

    /**
     * Listen on 'core.init' module.
     *
     * @param Zikula_Event $event Event.
     *
     * @return void
     */
    public function setupCsfrProtection(Zikula_Event $event)
    {
        if ($event['stage'] & Zikula_Core::STAGE_MODS) {
            // todo - handle this in DIC later
            // inject secret
            $def = $this->serviceManager->get('token.generator');
            $def->setSecret(System::getVar('signingkey'));
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
            if (isset($GLOBALS['ZConfig']['Log']['log.apache_uname']) && ($GLOBALS['ZConfig']['Log']['log.apache_uname']) && UserUtil::isLoggedIn()) {
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
        $session = $this->serviceManager->get('session');
        $request = $this->serviceManager->get('request');
        $request->setSession($session);
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
        $this->eventManager->dispatch('doctrine.init_connection', new \Zikula\Core\Event\GenericEvent(null, $event->getArgs()));
        $this->eventManager->dispatch('doctrine.boot', new \Zikula\Core\Event\GenericEvent());
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
     * Implements 'core.init' event when Zikula_Core::STAGE_TABLES.
     *
     * @param Zikula_Event $event The event handler.
     *
     * @return void
     */
    public function systemPlugins(Zikula_Event $event)
    {
        if ($event['stage'] & Zikula_Core::STAGE_TABLES) {
            if (!System::isInstalling()) {
                ServiceUtil::loadPersistentServices();
                PluginUtil::loadPlugins(realpath(realpath('.').'/plugins'), "SystemPlugin");
                EventUtil::loadPersistentEvents();
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

        if ($this->serviceManager->has('system.errorreporting')) {
            return;
        }

        $class = 'Zikula_ErrorHandler_Standard';
        if ($event['stage'] & Zikula_Core::STAGE_AJAX) {
            $class = 'Zikula_ErrorHandler_Ajax';
        }

        $errorHandler = new $class($this->serviceManager);
        $this->serviceManager->set('system.errorreporting', $errorHandler);
        set_error_handler(array($errorHandler, 'handler'));
        $event->stopPropagation();
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
            $this->serviceManager->set('zend.logger.display', $displayLogger = new Monolog\Logger('logger'));
            // load writer first because of hard requires in the Zend_Log_Writer_Stream
            $handler = new Monolog\Handler\StreamHandler('php://output');
            $formatter = new Monolog\Formatter\LineFormatter();
            $handler->setFormatter($formatter);
            $displayLogger->pushHandler($handler);
        }

        if ($this->serviceManager['log.to_file'] || $this->serviceManager['log.sql.to_file']) {
            $this->serviceManager->set('zend.logger.file', $fileLogger = new Monolog\Logger('logger.file'));
            $filename = LogUtil::getLogFileName();
            // load writer first because of hard requires in the Zend_Log_Writer_Stream
            $handler = new Monolog\Handler\StreamHandler($filename);
            $formatter = new Monolog\Formatter\LineFormatter();
            $handler->setFormatter($formatter);
            $fileLogger->pushHandler($handler);
        }
    }

    /**
     * Log an error.
     *
     * Implements 'log' event.
     *
     * @param Zikula_Event $event The log event to log.
     *
     * @throws Zikula_Exception_Fatal Thrown if the handler for the event is an instance of Zikula_ErrorHandler_Ajax.
     *
     * @return void
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
                $message = __f('PHP issued an error at line 0, so reporting entire trace to be more helpful: %1$s: %2$s', array(Zikula_AbstractErrorHandler::translateErrorCode($event['errno']), $event['errstr']));
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
                $message = __f('%1$s: %2$s in %3$s line %4$s', array(Zikula_AbstractErrorHandler::translateErrorCode($event['errno']), $event['errstr'], $event['errfile'], $event['errline']));
            }
        }

        $type = Zikula_AbstractErrorHandler::$configConversion[abs($handler->getType())];
        if ($this->serviceManager['log.to_display'] && !$handler instanceof Zikula_ErrorHandler_Ajax) {
            if (abs($handler->getType()) <= $this->serviceManager['log.display_level']) {
                $this->serviceManager->get('zend.logger.display')->log(abs($event['type']), $message);
            }
        }

        if ($this->serviceManager['log.to_file']) {
            if ($type <= $this->serviceManager['log.file_level']) {
                $this->serviceManager->get('zend.logger.file')->log(abs($event['type']), $message);
            }
        }

        if ($handler instanceof Zikula_ErrorHandler_Ajax) {
            if ($type <= $this->serviceManager['log.display_ajax_level']) {
                // autoloaders don't work inside error handlers!
                include_once 'lib/legacy/Zikula/Exception.php';
                include_once 'lib/legacy/Zikula/Exception/Fatal.php';
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
            $this->serviceManager->get('zend.logger.display')->debug($message);
        }

        if ($this->serviceManager['log.sql.to_file']) {
            $this->serviceManager->get('zend.logger.file')->debug($message);
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
        if ($event['stage'] == Zikula_Core::STAGE_CONFIG && System::isDevelopmentMode() && $event->getSubject()->getContainer()->getParameter('log.to_debug_toolbar')) {
            // autoloaders don't work inside error handlers!
            include_once 'lib/legacy/Zikula/DebugToolbar/Panel/Log.php';

            // create definitions
            $toolbar = new Definition('Zikula_DebugToolbar',  array(new Reference('event_dispatcher')));
            $toolbar->addMethodCall('addPanel', array(new Reference('debug.toolbar.panel.version')));
            $toolbar->addMethodCall('addPanel', array(new Reference('debug.toolbar.panel.config')));
            $toolbar->addMethodCall('addPanel', array(new Reference('debug.toolbar.panel.memory')));
            $toolbar->addMethodCall('addPanel', array(new Reference('debug.toolbar.panel.rendertime')));
            $toolbar->addMethodCall('addPanel', array(new Reference('debug.toolbar.panel.sql')));
            $toolbar->addMethodCall('addPanel', array(new Reference('debug.toolbar.panel.view')));
            $toolbar->addMethodCall('addPanel', array(new Reference('debug.toolbar.panel.exec')));
            $toolbar->addMethodCall('addPanel', array(new Reference('debug.toolbar.panel.logs')));

            $versionPanel = new Definition('Zikula_DebugToolbar_Panel_Version');
            $configPanel = new Definition('Zikula_DebugToolbar_Panel_Config');
            $momoryPanel = new Definition('Zikula_DebugToolbar_Panel_Memory');
            $rendertimePanel = new Definition('Zikula_DebugToolbar_Panel_RenderTime');
            $sqlPanel = new Definition('Zikula_DebugToolbar_Panel_SQL');
            $viewPanel = new Definition('Zikula_DebugToolbar_Panel_View');
            $execPanel = new Definition('Zikula_DebugToolbar_Panel_Exec');
            $logsPanel = new Definition('Zikula_DebugToolbar_Panel_Log');

            // save start time (required by rendertime panel)
            $this->serviceManager->setParameter('debug.toolbar.panel.rendertime.start', microtime(true));

            // register services
            $this->serviceManager->setDefinition('debug.toolbar.panel.version', $versionPanel);
            $this->serviceManager->setDefinition('debug.toolbar.panel.config', $configPanel);
            $this->serviceManager->setDefinition('debug.toolbar.panel.memory', $momoryPanel);
            $this->serviceManager->setDefinition('debug.toolbar.panel.rendertime', $rendertimePanel);
            $this->serviceManager->setDefinition('debug.toolbar.panel.sql', $sqlPanel);
            $this->serviceManager->setDefinition('debug.toolbar.panel.view', $viewPanel);
            $this->serviceManager->setDefinition('debug.toolbar.panel.exec', $execPanel);
            $this->serviceManager->setDefinition('debug.toolbar.panel.logs', $logsPanel);
            $this->serviceManager->setDefinition('debug.toolbar', $toolbar);

            // setup rendering event listeners
            $this->eventManager->addListener('theme.prefetch', array($this, 'debugToolbarRendering'));
            $this->eventManager->addListener('theme.postfetch', array($this, 'debugToolbarRendering'));

            // setup event listeners
            $this->eventManager->addListenerService('view.init', array('debug.toolbar.panel.view', 'initRenderer'));
            $this->eventManager->addListenerService('module_dispatch.preexecute', array('debug.toolbar.panel.exec', 'modexecPre'), -20);
            $this->eventManager->addListenerService('module_dispatch.postexecute', array('debug.toolbar.panel.exec', 'modexecPost'), -20);
            $this->eventManager->addListenerService('module_dispatch.execute_not_found', array('debug.toolbar.panel.logs', 'logExecNotFound'), -20);
            $this->eventManager->addListenerService('log', array('debug.toolbar.panel.logs', 'log'));
            $this->eventManager->addListenerService('log.sql', array('debug.toolbar.panel.sql', 'logSql'));
            $this->eventManager->addListenerService('controller.method_not_found', array('debug.toolbar.panel.logs', 'logModControllerNotFound'), -20);
            $this->eventManager->addListenerService('controller_api.method_not_found', array('debug.toolbar.panel.logs', 'logModControllerAPINotFound'),- 20);
        }
    }

    /**
     * Debug toolbar rendering (listener for 'theme.prefetch' and 'theme.postfetch' events).
     *
     * @param Zikula_Event $event Event.
     *
     * @return void
     */
    public function debugToolbarRendering(Zikula_Event $event)
    {
        if (!$event->getSubject() instanceof Zikula_ErrorHandler_Ajax) {
            if ($event->getName() == 'theme.prefetch') {
                // force object construction (debug toolbar constructor registers javascript and css files via PageUtil)
                $this->serviceManager->get('debug.toolbar');
            } else {
                $toolbar = $this->serviceManager->get('debug.toolbar');
                $html = $toolbar->getContent() . "\n</body>";
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
            CacheUtil::removeLocalDir($dir, true);
        }

        // remove saved data about the record
        $modelsInfo = ModUtil::getVar('ZikulaCategoriesModule', 'EntityCategorySubclasses', array());
        foreach ($modelsInfo as $class => $info) {
            if ($info['module'] == $moduleName) {
                unset($modelsInfo[$class]);
            }
        }
        ModUtil::setVar('ZikulaCategoriesModule', 'EntityCategorySubclasses', $modelsInfo);
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
        if ($event->getSubject() == 'stylesheet' && ($key = array_search('style/core.css', (array)$event->data)) !== false) {
            if (file_exists('config/style/core.css')) {
                $event->data[$key] = 'config/style/core.css';
            }

            $event->stopPropagation();
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

        // return if module is not subscriber or provider capable
        if (!HookUtil::isSubscriberCapable($event['modname']) && !HookUtil::isProviderCapable($event['modname'])) {
            return;
        }

        $event->data[] = array(
                'url' => ModUtil::url($event['modname'], 'admin', 'hooks'),
                'text' => __('Hooks'),
                'class' => 'smallicon smallicon-hook'
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
        $args = array('modname' => $event->getArgument('modname'));
        $localevent = new \Zikula\Core\Event\GenericEvent($event->getSubject(), $args);
        $this->eventManager->dispatch('module_dispatch.service_links', $localevent);
        $sublinks = $localevent->getData();

        if (!empty($sublinks)) {
            $event->data[] = array(
                    'url' => ModUtil::url($event['modname'], 'admin', 'moduleservices'),
                    'text' => __('Services'),
                    'class' => 'smallicon smallicon-gears',
                    'links' => $sublinks);
        }
    }

    /**
     * Listens for 'bootstrap.getconfig'
     *
     * @param Zikula_Event $event Event.
     *
     * @return void
     */
    public function getConfigFile(Zikula_Event $event)
    {
        foreach ($GLOBALS['ZConfig'] as $config) {
            $event->getSubject()->getContainer()->loadArguments($config);
        }

        $event->stopPropagation();
    }

    /**
     * Respond to theme.init events.
     *
     * Issues anti-clickjack headers.
     *
     * @link http://www.owasp.org/images/0/0e/OWASP_AppSec_Research_2010_Busting_Frame_Busting_by_Rydstedt.pdf
     * @link http://www.contextis.co.uk/resources/white-papers/clickjacking/Context-Clickjacking_white_paper.pdf
     *
     * @todo Reimplement in response/header objects in 1.4.0 - drak.
     *
     * @param Zikula $event
     *
     * @return void
     */
    public function clickJackProtection(Zikula_Event $event)
    {
        header('X-Frames-Options: SAMEORIGIN');
        //header("X-Content-Security-Policy: frame-ancestors 'self'");
        header('X-XSS-Protection: 1');
    }

    /**
     * Adds Doctrine extensions.
     *
     * Implements 'core.postinit' event.
     *
     * @param Zikula_Event $event The event handler.
     *
     * @deprecated since 1.3.6
     * @todo remove in 1.4.0
     *
     * @return void
     */
    public function doctrineExtensions(Zikula_Event $event)
    {
        $definition = new Definition('Doctrine\Common\Annotations\AnnotationReader');
        $this->serviceManager->setDefinition('doctrine.annotation_reader', $definition);

        $definition = new Definition('Doctrine\ORM\Mapping\Driver\AnnotationDriver', array(new Reference('doctrine.annotation_reader')));
        $this->serviceManager->setDefinition('doctrine.annotation_driver', $definition);

        $definition = new Definition('Doctrine\ORM\Mapping\Driver\DriverChain');
        $this->serviceManager->setDefinition('doctrine.driver_chain', $definition);

        $definition = new Definition('Zikula\Core\Doctrine\ExtensionsManager', array(new Reference('doctrine.eventmanager'), new Reference('service_container')));
        $this->serviceManager->setDefinition('doctrine_extensions', $definition);

        $types = array('Blameable', 'Exception', 'Loggable', 'Mapping', 'SoftDeleteable', 'Uploadable', 'Sluggable', 'Timestampable', 'Translatable', 'Tree', 'Sortable');
        foreach ($types as $type) {
            $definition = new Definition("Gedmo\\$type\\{$type}Listener");
            $this->serviceManager->setDefinition(strtolower("doctrine_extensions.listener.$type"), $definition);
        }
    }

}
