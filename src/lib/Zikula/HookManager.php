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

/**
 * HookManager class.
 */
class Zikula_HookManager
{
    /**
     * Storage.
     *
     * @var Zikula_HookManager_StorageInterface
     */
    private $storage;

    /**
     * Event Manager.
     *
     * @var Zikula_EventManager
     */
    private $eventManager;

    /**
     * Runtime hooks handlers loaded flag.
     *
     * @var boolean
     */
    private $loaded;

    /**
     * Service Factory.
     *
     * @var Zikula_HookManager_ServiceFactory
     */
    private $factory;

    /**
     * Constructor.
     *
     * @param Zikula_HookManager_StorageInterface $storage
     * @param Zikula_EventManager                 $eventManager
     * @param Zikula_HookManager_ServiceFactory   $factory
     */
    public function __construct(Zikula_HookManager_StorageInterface $storage, Zikula_EventManager $eventManager, Zikula_HookManager_ServiceFactory $factory)
    {
        $this->storage = $storage;
        $this->eventManager = $eventManager;
        $this->factory = $factory;
    }

    /**
     * Get storage driver.
     *
     * @return Zikula_HookManager_StorageInterface
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * Notify hook handlers.
     *
     * @param Zikula_HookInterface $hook Hook instance.
     *
     * @return Zikula_HookInterface
     */
    public function notify(Zikula_HookInterface $hook)
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

        $this->eventManager->notify($hook);

        return $hook;
    }

    /**
     * Register a subscriber bundle with persistence.
     *
     * @param Zikula_HookManager_SubscriberBundle $bundle
     */
    public function registerSubscriberBundle(Zikula_HookManager_SubscriberBundle $bundle)
    {
        foreach ($bundle->getEvents() as $areaType => $eventName) {
            $this->storage->registerSubscriber($bundle->getOwner(), $bundle->getSubOwner(), $bundle->getArea(), $areaType, $bundle->getCategory(), $eventName);
        }
        $this->reload();
    }

    /**
     * Unregister a subscriber bundle from persistence.
     *
     * @param Zikula_HookManager_SubscriberBundle $bundle
     */
    public function unregisterSubscriberBundle(Zikula_HookManager_SubscriberBundle $bundle)
    {
        $this->storage->unregisterSubscriberByArea($bundle->getArea());
        $this->reload();
    }

    /**
     * Register provider bundle with persistence.
     *
     * @param Zikula_HookManager_ProviderBundle $bundle
     */
    public function registerProviderBundle(Zikula_HookManager_ProviderBundle $bundle)
    {
        foreach ($bundle->getHooks() as $hook) {
            $this->storage->registerProvider($bundle->getOwner(), $bundle->getSubOwner(), $bundle->getArea(), $hook['hooktype'], $bundle->getCategory(), $hook['classname'], $hook['method'], $hook['serviceid']);
        }
        $this->reload();
    }

    /**
     * Unregister a provider bundle with persistence.
     *
     * @param Zikula_HookManager_ProviderBundle $bundle
     */
    public function unregisterProviderBundle(Zikula_HookManager_ProviderBundle $bundle)
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
     * @param array  $providerAreas      Array of provider area names.
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
     * @return Zikula_HookManager
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
                $this->eventManager->attach($handler['eventname'], $callable);
            } catch (InvalidArgumentException $e) {
                throw new Zikula_HookManager_Exception_RuntimeException("Hook event handler could not be attached because " . $e->getMessage());
            }
        }

        return $this;
    }

    /**
     * Decorate hook with required metadata.
     *
     * @param Zikula_HookInterface $hook
     */
    private function decorateHook(Zikula_HookInterface $hook)
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
        $this->eventManager->flushHandlers();
        $this->loadRuntimeHandlers();
    }

}
