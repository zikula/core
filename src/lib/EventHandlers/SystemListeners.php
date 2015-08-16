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

use Zikula\Core\Event\GenericEvent;

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
        $this->addHandlerDefinition('core.init', 'sessionLogging');
        $this->addHandlerDefinition('session.require', 'requireSession');
        $this->addHandlerDefinition('core.init', 'systemPlugins');
        $this->addHandlerDefinition('core.init', 'setupAutoloaderForGeneratedCategoryModels');
        $this->addHandlerDefinition('installer.module.uninstalled', 'deleteGeneratedCategoryModelsOnModuleRemove');
        $this->addHandlerDefinition('pageutil.addvar_filter', 'coreStylesheetOverride');
        $this->addHandlerDefinition('module_dispatch.postexecute', 'addHooksLink');
        $this->addHandlerDefinition('module_dispatch.postexecute', 'addServiceLink');
        $this->addHandlerDefinition('core.preinit', 'initDB');
        $this->addHandlerDefinition('core.init', 'setupCsfrProtection');
        $this->addHandlerDefinition('theme.init', 'clickJackProtection');
        $this->addHandlerDefinition('zikula.link_collector', 'processHookListeners');
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
        try {
            $request = ServiceUtil::get('request');
            $request->setSession($session);
        } catch (Exception $e) {
            // ignore silently (for CLI)
        }

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
     * Implements 'core.preinit' event
     *
     * @param Zikula_Event $event The event handler.
     *
     * @return void
     */
    public function initDB(Zikula_Event $event)
    {
        // Doctrine 1 event
        $this->eventManager->dispatch('doctrine.init_connection', new \Zikula\Core\Event\GenericEvent(null, $event->getArgs()));
        // Doctrine 2 event
        $this->eventManager->dispatch('doctrine.boot', new \Zikula\Core\Event\GenericEvent());
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
        if (!($event['modfunc'][1] == 'getLinks' && $event['type'] == 'admin' && $event['api'] == true)) {
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
            'url' => $this->getContainer()->get('router')->generate('zikulaextensionsmodule_admin_hooks', array('moduleName' => $event['modname'])),
            'text' => __('Hooks'),
            'icon' => 'paperclip'
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
        if (!($event['modfunc'][1] == 'getLinks' && $event['type'] == 'admin' && $event['api'] == true)) {
            return;
        }

        // notify EVENT here to gather any system service links
        $args = array('modname' => $event->getArgument('modname'));
        $localevent = new \Zikula\Core\Event\GenericEvent($event->getSubject(), $args);
        $this->eventManager->dispatch('module_dispatch.service_links', $localevent);
        $sublinks = $localevent->getData();

        if (!empty($sublinks)) {
            $event->data[] = array(
                'url' => $this->getContainer()->get('router')->generate('zikulaextensionsmodule_admin_moduleservices', array('moduleName' => $event['modname'])),
                'text' => __('Services'),
                'icon' => 'cogs',
                'links' => $sublinks);
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
     * @todo Reimplement in response/header objects in 1.5.0 - drak.
     *
     * @param Zikula_Event $event
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
     * Respond to zikula.link_collector events.
     *
     * Create a BC Layer for the zikula.link_collector event to gather Hook-related links.
     *
     * @param GenericEvent $event
     */
    public function processHookListeners(GenericEvent $event)
    {
        $event->setArgument('modname', $event->getSubject());
        $event->setArgument('modfunc', array(1 => 'getLinks'));
        $event->setArgument('api', true);
        $this->addHooksLink($event);
        $this->addServiceLink($event);
    }
}
