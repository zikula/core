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
            CoreEvents::INIT => array(
                //array('setupRequest'),
                array('systemPlugins'),
                array('setupAutoloaderForGeneratedCategoryModels'),
                array('setupCsfrProtection'),
                ),
            //'session.require' => array('requireSession'),
            'installer.module.uninstalled' => array('deleteGeneratedCategoryModelsOnModuleRemove'),
            'pageutil.addvar_filter' => array('coreStylesheetOverride'),
//            'theme.init' => array('clickJackProtection'),
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
            if (isset($GLOBALS['__z_old_frontcontroller__'])) {
                //$request->attributes->set('_controller', $controller);
            }

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