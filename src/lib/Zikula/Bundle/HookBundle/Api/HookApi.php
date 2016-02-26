<?php
/**
 * Copyright 2015 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Bundle\HookBundle\Api;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\Bundle\MetaData;
use Zikula\Bundle\HookBundle\Dispatcher\HookDispatcherInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\CapabilityApiInterface;
use Zikula\Core\Event\GenericEvent;

class HookApi
{
    /**
     * Provider capability key.
     * @deprecated
     * @see CapabilityApiInterface::HOOK_PROVIDER
     */
    const PROVIDER_TYPE = 'hook_provider';

    /**
     * Subscriber capability key.
     * @deprecated
     * @see CapabilityApiInterface::HOOK_SUBSCRIBER
     */
    const SUBSCRIBER_TYPE = 'hook_subscriber';

    /**
     * Allow to provide to self.
     * @deprecated
     * @see CapabilityApiInterface::HOOK_SUBSCRIBER_OWN
     */
    const SELF_TYPE = 'subscribe_own';
    /**
     * @var \Zikula\Common\Translator\Translator
     */
    private $translator;
    /**
     * @var HookDispatcherInterface
     */
    private $hookDispatcher;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * HookApi constructor.
     * @param TranslatorInterface $translator
     * @param HookDispatcherInterface $hookDispatcher
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(TranslatorInterface $translator, HookDispatcherInterface $hookDispatcher, EventDispatcherInterface $eventDispatcher)
    {
        $this->translator = $translator;
        $this->hookDispatcher = $hookDispatcher;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Shortcut method to register all provider hooks from extension MetaData.
     * @param MetaData $metaData
     */
    public function installProviderHooks(MetaData $metaData)
    {
        $hookContainer = $this->getHookContainerInstance($metaData, self::PROVIDER_TYPE);
        $this->registerProviderBundles($hookContainer->getHookProviderBundles());
    }

    /**
     * Shortcut method to register all subscriber hooks from extension MetaData.
     * @param MetaData $metaData
     */
    public function installSubscriberHooks(MetaData $metaData)
    {
        $hookContainer = $this->getHookContainerInstance($metaData, self::SUBSCRIBER_TYPE);
        $this->registerSubscriberBundles($hookContainer->getHookSubscriberBundles());
    }

    /**
     * Shortcut method to unregister all provider hooks from extension MetaData.
     * @param MetaData $metaData
     */
    public function uninstallProviderHooks(MetaData $metaData)
    {
        $hookContainer = $this->getHookContainerInstance($metaData, self::PROVIDER_TYPE);
        $this->unregisterProviderBundles($hookContainer->getHookProviderBundles());
    }

    /**
     * Shortcut method to unregister all subscriber hooks from extension MetaData.
     * @param MetaData $metaData
     */
    public function uninstallSubscriberHooks(MetaData $metaData)
    {
        $hookContainer = $this->getHookContainerInstance($metaData, self::SUBSCRIBER_TYPE);
        $this->unregisterSubscriberBundles($hookContainer->getHookSubscriberBundles());
    }

    /**
     * Factory class to create instance of HookContainer class defined in MetaData::capabilities.
     * @param MetaData $metaData
     * @param null $requestedHookType
     * @return null|\Zikula\Bundle\HookBundle\AbstractHookContainer
     */
    public function getHookContainerInstance(MetaData $metaData, $requestedHookType = null)
    {
        foreach ([self::SUBSCRIBER_TYPE, self::PROVIDER_TYPE] as $type) {
            if (isset($metaData->getCapabilities()[$type]['class'])
                && (!isset($requestedHookType) || $type == $requestedHookType)) {
                $hookContainerClassName = $metaData->getCapabilities()[$type]['class'];
                $reflection = new \ReflectionClass($hookContainerClassName);
                if ($reflection->isSubclassOf('Zikula\Bundle\HookBundle\AbstractHookContainer')) {
                    return new $hookContainerClassName($this->translator);
                }
            }
        }

        return null;
    }

    /**
     * Register Provider Hook handlers with persistence layer.
     *
     * @param array $bundles Module's bundles object.
     *
     * @return void
     */
    public function registerProviderBundles(array $bundles)
    {
        foreach ($bundles as $bundle) {
            $this->hookDispatcher->registerProviderBundle($bundle);
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
    public function unregisterProviderBundles(array $bundles)
    {
        foreach ($bundles as $bundle) {
            $this->hookDispatcher->unregisterProviderBundle($bundle);
        }
    }

    /**
     * Register Subscribers with persistence layer.
     *
     * @param array $bundles Module's bundles object.
     *
     * @return void
     */
    public function registerSubscriberBundles(array $bundles)
    {
        foreach ($bundles as $bundle) {
            $this->hookDispatcher->registerSubscriberBundle($bundle);
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
    public function unregisterSubscriberBundles(array $bundles)
    {
        foreach ($bundles as $bundle) {
            $this->hookDispatcher->unregisterSubscriberBundle($bundle);
            $event = new GenericEvent($bundle, array('areaid' => $this->hookDispatcher->getAreaId($bundle->getArea())));
            $this->eventDispatcher->dispatch('installer.subscriberbundle.uninstalled', $event);
        }
    }
}
