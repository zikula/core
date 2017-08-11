<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Zikula\Bundle\HookBundle\Collector\HookCollectorInterface;

/**
 * HookUtil.
 * @deprecated remove at Core-2.0
 *
 * In the context of Zikula, unfortunately we need to maintain the HookDispatcher
 * since it's not convenient to pass around using dependency injection
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
     * Allow to provide to self.
     */
    const SUBSCRIBE_OWN = 'subscribe_own';

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
        @trigger_error('HookUtil is deprecated. please use CapabilityApi instead.', E_USER_DEPRECATED);

        $nonPersistedHookProviders = ServiceUtil::get('zikula_hook_bundle.collector.hook_collector')->getOwnersCapableOf(HookCollectorInterface::HOOK_PROVIDER);
        foreach ($nonPersistedHookProviders as $nonPersistedHookProvider) {
            $nonPersisted[] = ServiceUtil::get('zikula_extensions_module.extension_repository')->findOneBy(['name' => $nonPersistedHookProvider]);
        }

        $persistedHookProviders = ModUtil::getModulesCapableOf(self::PROVIDER_CAPABLE);

        return array_merge($nonPersisted, $persistedHookProviders);
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
        @trigger_error('HookUtil is deprecated. please use CapabilityApi instead.', E_USER_DEPRECATED);

        $nonPersistedHookSubscribers = ServiceUtil::get('zikula_hook_bundle.collector.hook_collector')->getOwnersCapableOf(HookCollectorInterface::HOOK_SUBSCRIBER);
        foreach ($nonPersistedHookSubscribers as $nonPersistedHookSubscriber) {
            $nonPersisted[] = ServiceUtil::get('zikula_extensions_module.extension_repository')->findOneBy(['name' => $nonPersistedHookSubscriber]);
        }

        $persistedHookSubscribers = ModUtil::getModulesCapableOf(self::SUBSCRIBER_CAPABLE);

        return array_merge($nonPersisted, $persistedHookSubscribers);
    }

    /**
     * Is a module is provider capable.
     *
     * @param string $module Module name
     *
     * @return boolean
     */
    public static function isProviderCapable($module)
    {
        @trigger_error('HookUtil is deprecated. please use CapabilityApi instead.', E_USER_DEPRECATED);

        return (bool)ModUtil::isCapable($module, self::PROVIDER_CAPABLE);
    }

    /**
     * Is a module is allowed to subscribe to own provider to itself capable.
     * @see \Zikula\ExtensionsModule\Api\CapabilityApi::isCapable(CapabilityApiInterface::HOOK_SUBSCRIBE_OWN)
     * @see service zikula_extensions_module.api.capability
     *
     * @param string $module Module name
     *
     * @return boolean
     */
    public static function isSubscriberSelfCapable($module)
    {
        @trigger_error('HookUtil is deprecated. please use CapabilityApi instead.', E_USER_DEPRECATED);

        $capabilities = ModUtil::getCapabilitiesOf($module);

        if (isset($capabilities[self::SUBSCRIBER_CAPABLE][self::SUBSCRIBE_OWN])) {
            return $capabilities[self::SUBSCRIBER_CAPABLE][self::SUBSCRIBE_OWN];
        } else {
            return false;
        }
    }

    /**
     * Is a module subscriber capable.
     *
     * @param string $module Module name
     *
     * @return boolean
     */
    public static function isSubscriberCapable($module)
    {
        @trigger_error('HookUtil is deprecated. please use CapabilityApi instead.', E_USER_DEPRECATED);

        return ModUtil::isCapable($module, self::SUBSCRIBER_CAPABLE);
    }

    /**
     * Register Provider Hook handlers with persistence layer.
     * @see \Zikula\Bundle\HookBundle\Api\HookApi::registerProviderBundles
     * @see service zikula_hook_bundle.api.hook
     *
     * @param array $bundles Module's bundles object
     *
     * @return void
     */
    public static function registerProviderBundles(array $bundles)
    {
        @trigger_error('HookUtil is deprecated. please use CapabilityApi instead.', E_USER_DEPRECATED);

        $hookManager = ServiceUtil::getManager()->get('hook_dispatcher');
        foreach ($bundles as $bundle) {
            $hookManager->registerProviderBundle($bundle);
        }
    }

    /**
     * Unregister providers by bundle.
     * @see \Zikula\Bundle\HookBundle\Api\HookApi::unregisterProviderBundles
     * @see service zikula_hook_bundle.api.hook
     *
     * This cascades to remove all bindings by any subscribers to the providers in these bundles
     *
     * @param array $bundles Module's bundles object
     *
     * @return void
     */
    public static function unregisterProviderBundles(array $bundles)
    {
        @trigger_error('HookUtil is deprecated. please use CapabilityApi instead.', E_USER_DEPRECATED);

        $hookManager = ServiceUtil::getManager()->get('hook_dispatcher');
        foreach ($bundles as $bundle) {
            $hookManager->unregisterProviderBundle($bundle);
        }
    }

    /**
     * Register Subscribers with persistence layer.
     * @see \Zikula\Bundle\HookBundle\Api\HookApi::registerSubscriberBundles
     * @see service zikula_hook_bundle.api.hook
     *
     * @param array $bundles Module's bundles object
     *
     * @return void
     */
    public static function registerSubscriberBundles(array $bundles)
    {
        @trigger_error('HookUtil is deprecated. please use CapabilityApi instead.', E_USER_DEPRECATED);

        $hookManager = ServiceUtil::getManager()->get('hook_dispatcher');
        foreach ($bundles as $bundle) {
            $hookManager->registerSubscriberBundle($bundle);
        }
    }

    /**
     * Unregister all subscribers from the system.
     * @see \Zikula\Bundle\HookBundle\Api\HookApi::unregisterSubscriberBundles
     * @see service zikula_hook_bundle.api.hook
     *
     * This cascades to remove all event handlers, sorting data and update bindings table
     *
     * @param array $bundles Module's bundles object
     *
     * @return void
     */
    public static function unregisterSubscriberBundles(array $bundles)
    {
        @trigger_error('HookUtil is deprecated. please use CapabilityApi instead.', E_USER_DEPRECATED);

        $hookManager = ServiceUtil::getManager()->get('hook_dispatcher');
        foreach ($bundles as $bundle) {
            $hookManager->unregisterSubscriberBundle($bundle);
//            $event = new \Zikula\Core\Event\GenericEvent($bundle, ['areaid' => $hookManager->getAreaId($bundle->getArea())]);
//            EventUtil::dispatch('installer.subscriberbundle.uninstalled', $event);
        }
    }

    public static function getProviderAreasByOwner($moduleName)
    {
        @trigger_error('HookUtil is deprecated. please use CapabilityApi instead.', E_USER_DEPRECATED);

        $hookManager = ServiceUtil::getManager()->get('hook_dispatcher');

        return $hookManager->getProviderAreasByOwner($moduleName);
    }

    public static function getSubscriberAreasByOwner($moduleName)
    {
        @trigger_error('HookUtil is deprecated. please use CapabilityApi instead.', E_USER_DEPRECATED);

        $hookManager = ServiceUtil::getManager()->get('hook_dispatcher');

        return $hookManager->getSubscriberAreasByOwner($moduleName);
    }

    public static function getOwnerByArea($areaName)
    {
        @trigger_error('HookUtil is deprecated. please use CapabilityApi instead.', E_USER_DEPRECATED);

        $hookManager = ServiceUtil::getManager()->get('hook_dispatcher');

        return $hookManager->getOwnerByArea($areaName);
    }

    public static function getBindingsFor($areaName)
    {
        @trigger_error('HookUtil is deprecated. please use CapabilityApi instead.', E_USER_DEPRECATED);

        $hookManager = ServiceUtil::getManager()->get('hook_dispatcher');

        return $hookManager->getBindingsFor($areaName);
    }

    public static function setBindOrder($subscriberAreaName, array $providerAreas)
    {
        @trigger_error('HookUtil is deprecated. please use CapabilityApi instead.', E_USER_DEPRECATED);

        $hookManager = ServiceUtil::getManager()->get('hook_dispatcher');

        return $hookManager->setBindOrder($subscriberAreaName, $providerAreas);
    }

    public static function getBindingBetweenAreas($subscriberArea, $providerArea)
    {
        @trigger_error('HookUtil is deprecated. please use CapabilityApi instead.', E_USER_DEPRECATED);

        $hookManager = ServiceUtil::getManager()->get('hook_dispatcher');

        return $hookManager->getBindingBetweenAreas($subscriberArea, $providerArea);
    }

    public static function isAllowedBindingBetweenAreas($subscriberArea, $providerArea)
    {
        @trigger_error('HookUtil is deprecated. please use CapabilityApi instead.', E_USER_DEPRECATED);

        $hookManager = ServiceUtil::getManager()->get('hook_dispatcher');

        return $hookManager->isAllowedBindingBetweenAreas($subscriberArea, $providerArea);
    }

    public static function getBindingsBetweenOwners($subscriberOwner, $providerOwner)
    {
        @trigger_error('HookUtil is deprecated. please use CapabilityApi instead.', E_USER_DEPRECATED);

        $hookManager = ServiceUtil::getManager()->get('hook_dispatcher');

        return $hookManager->getBindingsBetweenOwners($subscriberOwner, $providerOwner);
    }
}
