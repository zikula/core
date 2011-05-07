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

    public function __construct(Zikula_HookManager_StorageInterface $storage, Zikula_EventManager $eventManager)
    {
        $this->storage = $storage;
        $this->eventManager = $eventManager;
    }

    public function getStorage()
    {
        return $this->storage;
    }

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

    public function registerSubscriberBundle(Zikula_HookManager_SubscriberBundle $bundle)
    {
        foreach ($bundle->getHookTypes() as $areaType => $eventName) {
            $this->storage->registerSubscriber($bundle->getOwner(), $bundle->getSubOwner(), $bundle->getArea(), $areaType, $bundle->getCategory(), $eventName);
        }
        $this->reload();
    }

    public function unregisterSubscriberBundle(Zikula_HookManager_SubscriberBundle $bundle)
    {
        $this->storage->unregisterSubscriberByArea($bundle->getArea());
        $this->reload();
    }

    public function registerProviderBundle(Zikula_HookManager_ProviderBundle $bundle)
    {
        foreach ($bundle->getHooks() as $name => $hook) {
            $this->storage->registerProvider($name, $bundle->getOwner(), $bundle->getSubOwner(), $bundle->getArea(), $hook['hooktype'], $bundle->getCategory(), $hook['classname'], $hook['method'], $hook['serviceid']);
        }
        $this->reload();
    }

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

    public function getSubscriberAreasByOwner($owner)
    {
        return $this->storage->getSubscriberAreasByOwner($owner);
    }

    public function getProviderAreasByOwner($owner)
    {
        return $this->storage->getProviderAreasByOwner($owner);
    }

    public function getOwnerByArea($areaName)
    {
        return $this->storage->getOwnerByArea($areaName);
    }

    public function getAreaId($areaName)
    {
        return $this->storage->getAreaId($areaName);
    }

    public function setBindOrder($subscriberAreaName, array $providerAreas)
    {
        $this->storage->setBindOrder($subscriberAreaName, $providerAreas);
        $this->reload();
    }

    public function getBindingBetweenAreas($subscriberArea, $providerArea)
    {
        return $this->storage->getBindingBetweenAreas($subscriberArea, $providerArea);
    }

    public function isAllowedBindingBetweenAreas($subscriberarea, $providerarea)
    {
        return $this->storage->isAllowedBindingBetweenAreas($subscriberarea, $providerarea);
    }

    public function getBindingsBetweenOwners($subscriberName, $providerName)
    {
        return $this->storage->getBindingsBetweenOwners($subscriberName, $providerName);
    }

    public function bindSubscriber($subscriberArea, $providerArea)
    {
        return $this->storage->bindSubscriber($subscriberArea, $providerArea);
    }

    public function unbindSubscriber($subscriberArea, $providerArea)
    {
        return $this->storage->unbindSubscriber($subscriberArea, $providerArea);
    }

    public function loadRuntimeHandlers()
    {
        $handlers = $this->storage->getRuntimeHandlers();
        foreach ($handlers as $handler) {
            if ($handler['serviceid'] && !$this->eventManager->getServiceManager()->hasService($handler['serviceid'])) {
                $definition = new Zikula_ServiceManager_Definition($handler['classname'], array(new Zikula_ServiceManager_Reference('zikula.servicemanager')));
                $this->eventManager->getServiceManager()->registerService($handler['serviceid'], $definition);
            }
            $callable = $this->resolveCallable($handler);
            try {
                $this->eventManager->attach($handler['eventname'], $callable); //, $handler['priority']);
            } catch (InvalidArgumentException $e) {
                throw new RuntimeException("Hook event handler could not be attached because %s", $e->getMessage(), 0, $e);
            }
        }
        return $this;
    }

    private function resolveCallable(array $handler)
    {
        if ($handler['serviceid']) {
            $callable = new Zikula_ServiceHandler($handler['serviceid'], $handler['method']);
        } else {
            $callable = array($handler['classname'], $handler['method']);
        }

        return $callable;
    }

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

    private function reload()
    {
        $this->eventManager->flushHandlers();
        $this->loadRuntimeHandlers();
    }

}
