<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage HookManager
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Common\HookManager;

use Zikula\Common\EventManager\EventManager;


/**
 * HookManager class.
 */
class HookManager
{
    /**
     * Storage.
     *
     * @var StorageInterface
     */
    private $storage;

    /**
     * Event Manager.
     *
     * @var EventManager
     */
    private $dispatcher;

    /**
     * Runtime hooks handlers loaded flag.
     *
     * @var boolean
     */
    private $loaded;

    /**
     * Service Factory.
     *
     * @var ServiceFactory
     */
    private $factory;

    /**
     * Constructor.
     *
     * @param StorageInterface $storage
     * @param EventManager     $dispatcher
     * @param ServiceFactory   $factory
     */
    public function __construct(StorageInterface $storage, EventManager $dispatcher, ServiceFactory $factory)
    {
        $this->storage = $storage;
        $this->dispatcher = $dispatcher;
        $this->factory = $factory;
    }

    /**
     * Get storage driver.
     *
     * @return StorageInterface
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * Notify hook handlers.
     *
     * @param Hook $hook Hook instance.
     *
     * @return Hook
     */
    public function notify(Hook $hook)
    {
        if (!$this->loaded) {
            // lazy load handlers for the first time
            $this->loadRuntimeHandlers();
            $this->loaded = true;
        }

        $this->decorateHook($hook);
        if (!$hook->getAreaId()) {
            return $hook;
        }

        $this->dispatcher->notify($hook);
        return $hook;
    }

    /**
     * Register a subscriber bundle with persistence.
     *
     * @param SubscriberBundle $bundle
     */
    public function registerSubscriberBundle(SubscriberBundle $bundle)
    {
        foreach ($bundle->getEvents() as $areaType => $eventName) {
            $this->storage->registerSubscriber($bundle->getOwner(), $bundle->getSubOwner(), $bundle->getArea(), $areaType, $bundle->getCategory(), $eventName);
        }
        $this->reload();
    }

    /**
     * Unregister a subscriber bundle from persistence.
     *
     * @param SubscriberBundle $bundle
     */
    public function unregisterSubscriberBundle(SubscriberBundle $bundle)
    {
        $this->storage->unregisterSubscriberByArea($bundle->getArea());
        $this->reload();
    }

    /**
     * Register provider bundle with persistence.
     *
     * @param ProviderBundle $bundle
     */
    public function registerProviderBundle(ProviderBundle $bundle)
    {
        foreach ($bundle->getHooks() as $hook) {
            $this->storage->registerProvider($bundle->getOwner(), $bundle->getSubOwner(), $bundle->getArea(), $hook['hooktype'], $bundle->getCategory(), $hook['classname'], $hook['method'], $hook['serviceid']);
        }
        $this->reload();
    }

    /**
     * Unregister a provider bundle with persistence.
     *
     * @param ProviderBundle $bundle
     */
    public function unregisterProviderBundle(ProviderBundle $bundle)
    {
        $this->storage->unregisterProviderByArea($bundle->getArea());
        $this->reload();
    }

    /**
     * Return all bindings for a given area.
     *
     * Area names are unique so you can specify subscriber or provider area.
     *
     * @param string $areaName Areaname.
     *
     * @return array
     */
    public function getBindingsFor($areaName)
    {
        return $this->storage->getBindingsFor($areaName);
    }

    /**
     * Get subscriber areas for an owner.
     *
     * @param string $owner
     *
     * @return array
     */
    public function getSubscriberAreasByOwner($owner)
    {
        return $this->storage->getSubscriberAreasByOwner($owner);
    }

    /**
     * Get provider areas for an owner.
     *
     * @param string $owner
     *
     * @return array
     */
    public function getProviderAreasByOwner($owner)
    {
        return $this->storage->getProviderAreasByOwner($owner);
    }

    /**
     * Get owber by area.
     *
     * @param string $areaName
     *
     * @return string
     */
    public function getOwnerByArea($areaName)
    {
        return $this->storage->getOwnerByArea($areaName);
    }

    /**
     * Get area id.
     *
     * @param string $areaName
     *
     * @return integer
     */
    public function getAreaId($areaName)
    {
        return $this->storage->getAreaId($areaName);
    }

    /**
     * Set the bind order of hooks.
     *
     * Used to resort the order providers are invoked for a given
     * area name.
     *
     * @param string $subscriberAreaName
     * @param array $providerAreas Array of provider area names.
     */
    public function setBindOrder($subscriberAreaName, array $providerAreas)
    {
        $this->storage->setBindOrder($subscriberAreaName, $providerAreas);
        $this->reload();
    }

    /**
     * Get binding between areas.
     *
     * @param string $subscriberArea
     * @param string $providerArea
     *
     * @return array
     */
    public function getBindingBetweenAreas($subscriberArea, $providerArea)
    {
        return $this->storage->getBindingBetweenAreas($subscriberArea, $providerArea);
    }

    /**
     * Check if areas may be bound together.
     *
     * @param string $subscriberarea
     * @param string $providerarea
     *
     * @return boolean
     */
    public function isAllowedBindingBetweenAreas($subscriberarea, $providerarea)
    {
        return $this->storage->isAllowedBindingBetweenAreas($subscriberarea, $providerarea);
    }

    /**
     * Get bindings between two owners.
     *
     * @param string $subscriberName
     * @param string $providerName
     *
     * @return array
     */
    public function getBindingsBetweenOwners($subscriberName, $providerName)
    {
        return $this->storage->getBindingsBetweenOwners($subscriberName, $providerName);
    }

    /**
     * Bind subscriber and provider area together.
     *
     * @param string $subscriberArea
     * @param string $providerArea
     *
     * @throws LogicException
     */
    public function bindSubscriber($subscriberArea, $providerArea)
    {
        $this->storage->bindSubscriber($subscriberArea, $providerArea);
    }

    /**
     * Unbind subscriber.
     *
     * @param string $subscriberArea
     * @param string $providerArea
     */
    public function unbindSubscriber($subscriberArea, $providerArea)
    {
        return $this->storage->unbindSubscriber($subscriberArea, $providerArea);
    }

    /**
     * Load runtime hook listeners.
     *
     * @return HookManager
     */
    public function loadRuntimeHandlers()
    {
        $handlers = $this->storage->getRuntimeHandlers();
        foreach ($handlers as $handler) {
            if ($handler['serviceid']) {
                $callable = $this->factory->buildService($handler['serviceid'], $handler['classname'], $handler['method']);
            } else {
                $callable = array($handler['classname'], $handler['method']);
            }

            try {
                $this->dispatcher->attach($handler['eventname'], $callable);
            } catch (\InvalidArgumentException $e) {
                throw new Exception\RuntimeException("Hook event handler could not be attached because %s", $e->getMessage(), 0, $e);
            }
        }
        return $this;
    }

    /**
     * Decorate hook with required metadata.
     *
     * @param Hook $hook
     */
    private function decorateHook(Hook $hook)
    {
        $owningSide = $this->storage->getRuntimeMetaByEventName($hook->getName());
        if ($owningSide) {
            $hook->setAreaId($owningSide['areaid']);
            if (!$hook->getCaller()) {
                $hook->setCaller($owningSide['owner']);
            }
        }
    }

    /**
     * Flush and reload handers.
     */
    private function reload()
    {
        $this->flushHandlers();
        $this->loadRuntimeHandlers();
    }

    /**
     * Flush handlers.
     *
     * Clears all handlers.
     *
     * @return void
     */
    public function flushHandlers()
    {
        foreach ($this->dispatcher->getListeners() as $eventName => $listener) {
            $this->dispatcher->removeListener($eventName, $listener);
        }
    }
}
