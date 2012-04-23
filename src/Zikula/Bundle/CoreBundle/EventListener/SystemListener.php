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

namespace Zikula\Bundle\CoreBundle\EventListener;

use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\Core\Event\GenericEvent;
use Zikula\Core\CoreEvents;
use Zikula\Core\Core;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use System;
use SessionUtil;
use SecurityUtil;
use EventUtil;
use PluginUtil;
use ServiceUtil;
use FormUtil;
use ZLanguage;

/**
 * Event handler to override templates.
 */
class SystemListener implements EventSubscriberInterface
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    public function __construct(ContainerBuilder $container)
    {
        $this->container = $container;
        $this->dispatcher = $container->get('event_dispatcher');
    }

    public static function getSubscribedEvents()
    {
        return array(
            'bootstrap.getconfig' => array(
                array('initialHandlerScan', 100),
                ),
//            'setup.errorreporting' => array('defaultErrorReporting'),
            CoreEvents::PREINIT => array('systemCheck'),
            CoreEvents::INIT => array(
                array('setupRequest'),
//                array('sessionLogging'),
                array('systemPlugins'),
//                array('setupDebugToolbar'),
                array('setupAutoloaderForGeneratedCategoryModels'),
                array('setupCsfrProtection'),
                ),
//            'log' => array('errorLog'),
            'session.require' => array('requireSession'),
//            'log.sql' => array('logSqlQueries'),
            'installer.module.uninstalled' => array('deleteGeneratedCategoryModelsOnModuleRemove'),
            'pageutil.addvar_filter' => array('coreStylesheetOverride'),
            'theme.init' => array('clickJackProtection'),
            'module_dispatch.postexecute' => array(
                array('addHooksLink'),
                array('addServiceLink'),
                ),
//            'frontcontroller.predispatch' => array(
//                array('sessionExpired', 3),
//                array('siteOff', 7),
//            ),
        );
    }

    /**
     * Event: 'frontcontroller.predispatch'.
     *
     * @param GenericEvent $event
     *
     * @return void
     */
    public function sessionExpired(GenericEvent $event)
    {
        if (\SessionUtil::hasExpired()) {
            // Session has expired, display warning
            header('HTTP/1.0 403 Access Denied');
            $return = \ModUtil::apiFunc('UsersModule', 'user', 'expiredsession');
            \System::shutdown();
        }
    }

    /**
     * Listens for 'frontcontroller.predispatch'.
     *
     * @param GenericEvent $event
     *
     * @return void
     */
    public function siteOff(GenericEvent $event)
    {
        // Get variables
        $module = FormUtil::getPassedValue('module', '', 'GETPOST', FILTER_SANITIZE_STRING);
        $func = FormUtil::getPassedValue('func', '', 'GETPOST', FILTER_SANITIZE_STRING);

        // Check for site closed
        if (System::getVar('siteoff') && !SecurityUtil::checkPermission('Settings::', 'SiteOff::', ACCESS_ADMIN) && !($module == 'Users' && $func == 'siteofflogin') || (Core::VERSION_NUM != System::getVar('Version_Num'))) {
            if (SecurityUtil::checkPermission('Users::', '::', ACCESS_OVERVIEW) && UserUtil::isLoggedIn()) {
                UserUtil::logout();
            }
            header('HTTP/1.1 503 Service Unavailable');
            require_once System::getSystemErrorTemplate('siteoff.tpl');
            System::shutdown();
        }
    }

    /**
     * Listen for the CoreEvents::INIT event & STAGE_DECODEURLS.
     *
     * This is basically a hack until the routing framework takes over (drak).
     *
     * @param GenericEvent $event Event.
     *
     * @return void
     */
    public function setupRequest(GenericEvent $event)
    {
        if ($event['stage'] & Core::STAGE_DECODEURLS) {

            $request = $event['request'];// \Symfony\Component\HttpFoundation\Request::createFromGlobals();
            $this->container->set('request', $request);

            $module = FormUtil::getPassedValue('module', null, 'GETPOST', FILTER_SANITIZE_STRING);
            $controller = FormUtil::getPassedValue('type', null, 'GETPOST', FILTER_SANITIZE_STRING);
            $action = FormUtil::getPassedValue('func', null, 'GETPOST', FILTER_SANITIZE_STRING);

            $request->attributes->set('_module', $module);
            $request->attributes->set('_controller', $controller);
            $request->attributes->set('_action', $action);
            $request->setLocale(ZLanguage::getLanguageCode());

            $session = $this->container->get('session');
            $request->setSession($session);
        }
    }

    /**
     * Listens for 'bootstrap.getconfig' event.
     *
     * @param GenericEvent $event Event.
     *
     * @return void
     */
    public function initialHandlerScan(GenericEvent $event)
    {
        ServiceUtil::getManager($this->container);
        EventUtil::getManager($this->container->get('event_dispatcher'));
        $event->getSubject()->attachHandlers(ZIKULA_CONFIG_PATH.'/EventHandlers');
    }

    /**
     * Listen on CoreEvents::INIT module.
     *
     * @param GenericEvent $event Event.
     *
     * @return void
     */
    public function setupCsfrProtection(GenericEvent $event)
    {
        if ($event['stage'] & Core::STAGE_MODS) {
            $this->container->setParameter('signing.key', System::getVar('signingkey'));
        }
    }

    /**
     * If enabled and logged in, save login name of user in Apache session variable for Apache logs.
     *
     * Implements CoreEvents::INIT event when Core::STAGE_SESSIONS.
     *
     * @param GenericEvent $event The event handler.
     *
     * @return void
     */
    public function sessionLogging(GenericEvent $event)
    {
        if ($event['stage'] & Core::STAGE_SESSIONS) {
            // If enabled and logged in, save login name of user in Apache session variable for Apache logs
            if (isset($GLOBALS['ZConfig']['Log']['log.apache_uname']) && ($GLOBALS['ZConfig']['Log']['log.apache_uname']) && \UserUtil::isLoggedIn()) {
                if (function_exists('apache_setenv')) {
                    apache_setenv('Zikula-Username', \UserUtil::getVar('uname'));
                }
            }
        }
    }

    /**
     * If enabled and logged in, save login name of user in Apache session variable for Apache logs.
     *
     * Implements 'session.require'.
     *
     * @param GenericEvent $event The event handler.
     *
     * @return void
     */
    public function requireSession(GenericEvent $event)
    {
        $session = $this->container->get('session');
        //$request = $this->container->get('request');
        //$request->setSession($session);

        try {
            if (!$session->start()) {
                throw new \RuntimeException('Failed to start session');
            }
        } catch (\Exception $e) {
            // session initialization failed so display templated error
            header('HTTP/1.1 503 Service Unavailable');
            require_once System::getSystemErrorTemplate('sessionfailed.tpl');
            System::shutdown();
        }
    }

    /**
     * Load system plugins.
     *
     * Implements CoreEvents::INIT event when Core::STAGE_TABLES.
     *
     * @param GenericEvent $event The event handler.
     *
     * @return void
     */
    public function systemPlugins(GenericEvent $event)
    {
        if ($event['stage'] & Core::STAGE_TABLES) {
            if (!System::isInstalling()) {
                ServiceUtil::loadPersistentServices();
                PluginUtil::loadPlugins(ZIKULA_ROOT.'/plugins', "SystemPlugin");
                EventUtil::loadPersistentEvents();
            }
        }
    }

    /**
     * Setup default error reporting.
     *
     * Implements 'setup.errorreporting' event.
     *
     * @param GenericEvent $event The event.
     *
     * @return void
     */
    public function defaultErrorReporting(GenericEvent $event)
    {
        if (!$this->container['log.enabled']) {
            return;
        }

        if ($this->container->has('system.errorreporting')) {
            return;
        }

        $class = 'Zikula\\Framework\\ErrorHandler\\Standard';
        if ($event['stage'] & Core::STAGE_AJAX) {
            $class = 'Zikula\\Framework\\ErrorHandler\\Ajax';
        }

        $errorHandler = new $class($this->container);
        $this->container->set('system.errorreporting', $errorHandler);
        set_error_handler(array($errorHandler, 'handler'));
        $event->stopPropagation();
    }

    /**
     * Establish the necessary instances for logging.
     *
     * Implements CoreEvents::INIT event when Core::STAGE_CONFIG.
     *
     * @param GenericEvent $event The event to log.
     *
     * @return void
     */
    public function setupLoggers(GenericEvent $event)
    {
        if (!($event['stage'] & Core::STAGE_CONFIG)) {
            return;
        }

        if (!$this->container['log.enabled']) {
            return;
        }

        if ($this->container['log.to_display'] || $this->container['log.sql.to_display']) {
            $displayLogger = new \Zend_Log();
            $this->container->set('zend.logger.display', $displayLogger);
            // load writer first because of hard requires in the Zend_Log_Writer_Stream
            $writer = new \Zend_Log_Writer_Stream('php://output');
            $formatter = new \Zend_Log_Formatter_Simple('%priorityName% (%priority%): %message% <br />'.\PHP_EOL);
            $writer->setFormatter($formatter);
            $displayLogger->addWriter($writer);
        }

        if ($this->container['log.to_file'] || $this->container['log.sql.to_file']) {
            $fileLogger = new \Zend_Log();
            $this->container->set('zend.logger.file', $fileLogger);
            $filename = \LogUtil::getLogFileName();
            // load writer first because of hard requires in the Zend_Log_Writer_Stream
            $writer = new \Zend_Log_Writer_Stream($filename);
            $formatter = new \Zend_Log_Formatter_Simple('%timestamp% %priorityName% (%priority%): %message%'.\PHP_EOL);

            $writer->setFormatter($formatter);
            $fileLogger->addWriter($writer);
        }
    }

    /**
     * Log an error.
     *
     * Implements 'log' event.
     *
     * @param GenericEvent $event The log event to log.
     *
     * @throws Zikula_Exception_Fatal Thrown if the handler for the event is an instance of Zikula_ErrorHandler_Ajax.
     *
     * @return void
     */
    public function errorLog(GenericEvent $event)
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
                $message = __f('PHP issued an error at line 0, so reporting entire trace to be more helpful: %1$s: %2$s', array(\Zikula_AbstractErrorHandler::translateErrorCode($event['errno']), $event['errstr']));
                $fullTrace = $event['trace'];
                array_shift($fullTrace); // shift is performed on copy so as not to disturn the event args
                foreach ($fullTrace as $trace) {
                    $file = isset($trace['file']) ? $trace['file'] : null;
                    $line = isset($trace['line']) ? $trace['line'] : null;

                    if ($file && $line) {
                        $message .= ' '.__f('traced in %1$s line %2$s', array($file, $line))."#\n";
                    }
                }
            } else {
                $message = __f('%1$s: %2$s in %3$s line %4$s', array(\Zikula_AbstractErrorHandler::translateErrorCode($event['errno']), $event['errstr'], $event['errfile'], $event['errline']));
            }
        }

        if ($this->container['log.to_display'] && !$handler instanceof \Zikula_ErrorHandler_Ajax) {
            if (abs($handler->getType()) <= $this->container['log.display_level']) {
                $this->container->get('zend.logger.display')->log($message, abs($event['type']));
            }
        }

        if ($this->container['log.to_file']) {
            if (abs($handler->getType()) <= $this->container['log.file_level']) {
                $this->container->get('zend.logger.file')->log($message, abs($event['type']));
            }
        }

        if ($handler instanceof \Zikula_ErrorHandler_Ajax) {
            if (abs($handler->getType()) <= $this->container['log.display_ajax_level']) {
                // autoloaders don't work inside error handlers!
                include_once 'lib/Zikula/Exception.php';
                include_once 'lib/Zikula/Exception/Fatal.php';
                throw new \Zikula_Exception_Fatal($message);
            }
        }
    }

    /**
     * Listener for 'log.sql' events.
     *
     * This listener logs the queries via Zend_Log to file / console.
     *
     * @param GenericEvent $event Event.
     *
     * @return void
     */
    public function logSqlQueries(GenericEvent $event)
    {
        if (!$this->container['log.enabled']) {
            return;
        }

        $message = __f('SQL Query: %s took %s sec', array($event['query'], $event['time']));

        if ($this->container['log.sql.to_display']) {
            $this->container->get('zend.logger.display')->log($message, \Zend_Log::DEBUG);
        }

        if ($this->container['log.sql.to_file']) {
            $this->container->get('zend.logger.file')->log($message, \Zend_Log::DEBUG);
        }
    }

    /**
     * Debug toolbar startup.
     *
     * Implements CoreEvents::INIT event when Core::STAGE_CONFIG in development mode.
     *
     * @param GenericEvent $event Event.
     *
     * @return void
     */
    public function setupDebugToolbar(GenericEvent $event)
    {
        if ($event['stage'] == Core::STAGE_CONFIG && System::isDevelopmentMode() && $event->getSubject()->getContainer()->getParameter('log.to_debug_toolbar')) {
            // autoloaders don't work inside error handlers!
            include_once __DIR__.'/../Zikula/Framework/DebugToolbar/Panel/Log.php';

            // create definitions
            $toolbar = new Definition('Zikula\Framework\DebugToolbar\DebugToolbar',
                    array(new Reference('event_dispatcher')));

            $toolbar->addMethodCall('addPanels', array(
                new Reference('debug.toolbar.panel.version'),
                new Reference('debug.toolbar.panel.config'),
                new Reference('debug.toolbar.panel.memory'),
                new Reference('debug.toolbar.panel.rendertime'),
                new Reference('debug.toolbar.panel.sql'),
                new Reference('debug.toolbar.panel.view'),
                new Reference('debug.toolbar.panel.exec'),
            ));

            $versionPanel = new Definition('Zikula\Framework\DebugToolbar\Panel\Version');
            $configPanel = new Definition('Zikula\Framework\DebugToolbar\Panel\Config');
            $momoryPanel = new Definition('Zikula\Framework\DebugToolbar\Panel\Memory');
            $rendertimePanel = new Definition('Zikula\Framework\DebugToolbar\Panel\RenderTime');
            $sqlPanel = new Definition('Zikula\Framework\DebugToolbar\Panel\SQL');
            $viewPanel = new Definition('Zikula\Framework\DebugToolbar\Panel\View');
            $execPanel = new Definition('Zikula\Framework\DebugToolbar\Panel\Exec');
            $logsPanel = new Definition('Zikula\Framework\DebugToolbar\Panel\Log');

            // save start time (required by rendertime panel)
            $this->container->setParameter('debug.toolbar.panel.rendertime.start', microtime(true));

            // register services
            $this->container->setDefinition('debug.toolbar.panel.version', $versionPanel, true);
            $this->container->setDefinition('debug.toolbar.panel.config', $configPanel, true);
            $this->container->setDefinition('debug.toolbar.panel.memory', $momoryPanel, true);
            $this->container->setDefinition('debug.toolbar.panel.rendertime', $rendertimePanel, true);
            $this->container->setDefinition('debug.toolbar.panel.sql', $sqlPanel, true);
            $this->container->setDefinition('debug.toolbar.panel.view', $viewPanel, true);
            $this->container->setDefinition('debug.toolbar.panel.exec', $execPanel, true);
            $this->container->setDefinition('debug.toolbar.panel.logs', $logsPanel, true);
            $this->container->setDefinition('debug.toolbar', $toolbar, true);

            // setup rendering event listeners
            $this->dispatcher->addListener('theme.prefetch', array($this, 'debugToolbarRendering'));
            $this->dispatcher->addListener('theme.postfetch', array($this, 'debugToolbarRendering'));

            // setup event listeners
            $this->dispatcher->addListenerService('view.init', array('debug.toolbar.panel.view', 'initRenderer'));
            $this->dispatcher->addListenerService('module_dispatch.preexecute', array('debug.toolbar.panel.exec', 'modexecPre'), 20);
            $this->dispatcher->addListenerService('module_dispatch.postexecute', array('debug.toolbar.panel.exec', 'modexecPost'), 20);
            $this->dispatcher->addListenerService('module_dispatch.execute_not_found', array('debug.toolbar.panel.logs', 'logExecNotFound'), 20);
            $this->dispatcher->addListenerService('log', array('debug.toolbar.panel.logs', 'log'));
            $this->dispatcher->addListenerService('log.sql', array('debug.toolbar.panel.sql', 'logSql'));
            $this->dispatcher->addListenerService('controller.method_not_found', array('debug.toolbar.panel.logs', 'logModControllerNotFound'), 20);
            $this->dispatcher->addListenerService('controller_api.method_not_found', array('debug.toolbar.panel.logs', 'logModControllerAPINotFound'), 20);
        }
    }

    /**
     * Debug toolbar rendering (listener for 'theme.prefetch' and 'theme.postfetch' events).
     *
     * @param GenericEvent $event Event.
     *
     * @return void
     */
    public function debugToolbarRendering(GenericEvent $event)
    {
        if (!$event->getSubject() instanceof \Zikula_ErrorHandler_Ajax) {
            if ($event->getName() == 'theme.prefetch') {
                // force object construction (debug toolbar constructor registers javascript and css files via PageUtil)
                $this->container->get('debug.toolbar');
            } else {
                $toolbar = $this->container->get('debug.toolbar');
                $html = $toolbar->getContent()."\n</body>";
                $event->setData(str_replace('</body>', $html, $event->getData()));
            }
        }
    }

    /**
     * Adds an autoloader entry for the cached (generated) doctrine models.
     *
     * Implements CoreEvents::INIT events when Core::STAGE_CONFIG.
     *
     * @param GenericEvent $event Event.
     *
     * @return void
     */
    public function setupAutoloaderForGeneratedCategoryModels(GenericEvent $event)
    {
        if ($event['stage'] == Core::STAGE_CONFIG) {
            \ZLoader::addAutoloader('GeneratedDoctrineModel', \CacheUtil::getLocalDir('doctrinemodels'));
        }
    }

    /**
     * On an module remove hook call this listener deletes all cached (generated) doctrine models for the module.
     *
     * Listens for the 'installer.module.uninstalled' event.
     *
     * @param GenericEvent $event Event.
     *
     * @return void
     */
    public function deleteGeneratedCategoryModelsOnModuleRemove(GenericEvent $event)
    {
        $moduleName = $event['name'];

        // remove generated category models for this record
        $dir = 'doctrinemodels/GeneratedDoctrineModel/'.$moduleName;
        if (file_exists(CacheUtil::getLocalDir($dir))) {
            \CacheUtil::removeLocalDir($dir, true);
        }

        // remove saved data about the record
        $modelsInfo = \ModUtil::getVar('Categories', 'EntityCategorySubclasses', array());
        foreach ($modelsInfo as $class => $info) {
            if ($info['module'] == $moduleName) {
                unset($modelsInfo[$class]);
            }
        }
        \ModUtil::setVar('Categories', 'EntityCategorySubclasses', $modelsInfo);
    }

    /**
     * Core stylesheet override.
     *
     * Implements 'pageutil.addvar_filter' event.
     *
     * @param GenericEvent $event The event handler.
     *
     * @return void
     */
    public function coreStylesheetOverride(GenericEvent $event)
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
     * @param GenericEvent $event The event handler.
     *
     * @return void
     */
    public function addHooksLink(GenericEvent $event)
    {
        // check if this is for this handler
        if (!($event['modfunc'][1] == 'getlinks' && $event['type'] == 'admin' && $event['api'] == true)) {
            return;
        }

        if (!\SecurityUtil::checkPermission($event['modname'].'::Hooks', '::', \ACCESS_ADMIN)) {
            return;
        }

        // return if module is not subscriber or provider capable
        if (!\HookUtil::isSubscriberCapable($event['modname']) && !\HookUtil::isProviderCapable($event['modname'])) {
            return;
        }

        $event->data[] = array(
            'url' => \ModUtil::url($event['modname'], 'admin', 'hooks'),
            'text' => __('Hooks'),
            'class' => 'z-icon-es-hook'
        );
    }

    /**
     * Dynamically add menu links to administration for system services.
     *
     * Listens for 'module_dispatch.postexecute' events.
     *
     * @param GenericEvent $event The event handler.
     *
     * @return void
     */
    public function addServiceLink(GenericEvent $event)
    {
        // check if this is for this handler
        if (!($event['modfunc'][1] == 'getlinks' && $event['type'] == 'admin' && $event['api'] == true)) {
            return;
        }

        // notify EVENT here to gather any system service links
        $args = array('modname' => $event->getArgument('modname'));
        $localevent = new GenericEvent($event->getSubject(), $args);
        $this->dispatcher->dispatch('module_dispatch.service_links', $localevent);
        $sublinks = $localevent->getData();

        if (!empty($sublinks)) {
            $event->data[] = array(
                'url' => \ModUtil::url($event['modname'], 'admin', 'moduleservices'),
                'text' => __('Services'),
                'class' => 'z-icon-es-gears',
                'links' => $sublinks);
        }
    }

    /**
     * Perform some checks that might result in a die() upon failure.
     *
     * Listens on the CoreEvents::PREINIT event.
     *
     * @param GenericEvent $event Event.
     *
     * @return void
     */
    public function systemCheck(GenericEvent $event)
    {
        $die = false;

        if (get_magic_quotes_runtime()) {
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
        $x = explode('.', str_replace('-', '.', phpversion()));
        $phpVersion = "$x[0].$x[1].$x[2]";
        if (version_compare($phpVersion, Core::PHP_MINIMUM_VERSION, '>=') == false) {
            echo __f('Error! Zikula requires PHP version %1$s or greater. Your server seems to be using version %2$s.', array(Core::PHP_MINIMUM_VERSION, $phpVersion));
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

        if ($die) {
            echo __("Please configure your server to meet the Zikula system requirements.");
            exit;
        }

        if (\System::isDevelopmentMode() || \System::isInstalling()) {
            $temp = $this->container->getParameter('temp');
            if (!is_dir($temp) || !is_writable($temp)) {
                echo __f('The temporary directory "%s" and its subfolders must be writable.', $temp).'<br />';
                die(__('Please ensure that the permissions are set correctly on your server.'));
            }

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
                    mkdir($folder, $this->container->getParameter('system.chmod_dir'), true);
                }
                if (!is_writable($folder)) {
                    echo __f("System error! Folder '%s' was not found or is not writable.", $folder).'<br />';
                    $die = true;
                }
            }
        }

        if ($die) {
            echo __('Please ensure that the permissions are set correctly for the mentioned folders.');
            exit;
        }
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
    public function clickJackProtection(GenericEvent $event)
    {
        header('X-Frames-Options: SAMEORIGIN');
        //header("X-Content-Security-Policy: frame-ancestors 'self'");
        header('X-XSS-Protection: 1');
    }

}