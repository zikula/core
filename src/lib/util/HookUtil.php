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
 * HookUtil.
 *
 * In the context of Zikula, unfortunately we need to maintain the HookManager
 * since it's not convenient to pass around using dependency injection.
 */
class HookUtil
{
    /**
     * Provider capability key.
     */
    const PROVIDER_CAPABLE = 'hook_provider';

    /**
     * Subscriber capability key.
     */
    const SUBSCRIBER_CAPABLE = 'hook_subscriber';
    /**
     * Constructor.
     */
    private function __construct()
    {

    }

    /**
     * Get list of modules who provide hooks.
     *
     * This means modules that provide hooks that can be attached to other modules.
     *
     * @return array
     */
    public static function getHookProviders()
    {
        return ModUtil::getModulesCapableOf(self::SUBSCRIBER_CAPABLE);
    }

    /**
     * Get list of modules that subscribe to hooks.
     *
     * This means modules that can make use of another modules' hooks.
     *
     * @return array
     */
    public static function getHookSubscribers()
    {
        return ModUtil::getModulesCapableOf(self::SUBSCRIBER_CAPABLE);
    }

    /**
     * Is a module is subscriber capable.
     *
     * @param string $module Module name.
     *
     * @return boolean
     */
    public static function isProviderCapable($module)
    {
        return (bool)ModUtil::isCapable($module, self::PROVIDER_CAPABLE);
    }

    /**
     * Is a module subscriber capable.
     *
     * @param string $module Module name.
     *
     * @return boolean
     */
    public static function isSubscriberCapable($module)
    {
        return ModUtil::isCapable($module, self::SUBSCRIBER_CAPABLE);
    }

    /**
     * Register Provider Hook handlers with persistence layer.
     *
     * @param array $bundles Module's bundles object.
     *
     * @return void
     */
    public static function registerProviderBundles(array $bundles)
    {
        $hookManager = ServiceUtil::getManager()->getService('zikula.hookmanager');
        foreach ($bundles as $bundle) {
            $hookManager->registerProviderBundle($bundle);
        }
    }

    /**
     * Unregister providers by bundle.
     *
     * This cascades to remove all bindings by any subscribers to the providers in these bundles.
     *
     * @param array $bundles Module's bundles object.
     *
     * @return void
     */
    public static function unregisterProviderBundles(array $bundles)
    {
        $hookManager = ServiceUtil::getManager()->getService('zikula.hookmanager');
        foreach ($bundles as $bundle) {
            $hookManager->unregisterProviderBundle($bundle);
        }
    }

    /**
     * Register Subscribers with persistence layer.
     *
     * @param array $bundles Module's bundles object.
     *
     * @return void
     */
    public static function registerSubscriberBundles(array $bundles)
    {
        $hookManager = ServiceUtil::getManager()->getService('zikula.hookmanager');
        foreach ($bundles as $bundle) {
            $hookManager->registerSubscriberBundle($bundle);
        }
    }

    /**
     * Unregister all subscribers from the system.
     *
     * This cascades to remove all event handlers, sorting data and update bindings table.
     *
     * @param array $bundles Module's bundles object.
     *
     * @return void
     */
    public static function unregisterSubscriberBundles(array $bundles)
    {
        $hookManager = ServiceUtil::getManager()->getService('zikula.hookmanager');
        foreach ($bundles as $bundle) {
            $hookManager->unregisterSubscriberBundle($bundle);
            $event = new Zikula_Event('hooks.subscriberbundle.uninstalled', $bundle, array('areaid' => $hookManager->getSubscriberAreaId($bundle->getArea())));
            EventUtil::notify($event);
        }
    }

    public static function getProviderAreasByOwner($moduleName)
    {
        $hookManager = ServiceUtil::getManager()->getService('zikula.hookmanager');
        return $hookManager->getProviderAreasByOwner($moduleName);
    }

    public static function getSubscriberAreasByOwner($moduleName)
    {
        $hookManager = ServiceUtil::getManager()->getService('zikula.hookmanager');
        return $hookManager->getSubscriberAreasByOwner($moduleName);
    }

    public static function getBindingsFor($areaName)
    {
        $hookManager = ServiceUtil::getManager()->getService('zikula.hookmanager');
        return $hookManager->getBindingsFor($areaName);
    }

    public static function setBindOrder($subscriberAreaName, array $providerAreas)
    {
        $hookManager = ServiceUtil::getManager()->getService('zikula.hookmanager');
        return $hookManager->setBindOrder($subscriberAreaName);
    }

    public static function allowBindingBetweenAreas($provider, $subscriber)
    {

    }
}
