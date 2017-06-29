<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle\Dispatcher;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Zikula\Bundle\HookBundle\Bundle\SubscriberBundle;
use Zikula\Bundle\HookBundle\Bundle\ProviderBundle;
use Zikula\Bundle\HookBundle\Collector\HookCollector;
use Zikula\Bundle\HookBundle\Dispatcher\Exception\LogicException;
use Zikula\Bundle\HookBundle\Hook\Hook;

/**
 * HookDispatcher class.
 */
class HookDispatcher implements HookDispatcherInterface
{
    /**
     * Storage.
     *
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var HookCollector
     */
    private $hookCollector;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * Constructor.
     *
     * @param StorageInterface $storage
     * @param HookCollector $hookCollector
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        StorageInterface $storage,
        HookCollector $hookCollector,
        EventDispatcherInterface $dispatcher
    ) {
        $this->storage = $storage;
        $this->hookCollector = $hookCollector;
        $this->dispatcher = $dispatcher;
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
     * Dispatch hook listeners.
     *
     * @param string $name Hook event name
     * @param Hook   $hook Hook instance
     *
     * @return Event
     */
    public function dispatch($name, Hook $hook)
    {
        $this->decorateHook($name, $hook);
        if (!$hook->getAreaId()) {
            return $hook;
        }

        return $this->dispatcher->dispatch($name, $hook);
    }

    /**
     * Register a subscriber bundle with persistence.
     * @deprecated no longer required with non-persisted hooks
     *
     * @param SubscriberBundle $bundle
     */
    public function registerSubscriberBundle(SubscriberBundle $bundle)
    {
        foreach ($bundle->getEvents() as $areaType => $eventName) {
            $this->storage->registerSubscriber($bundle->getOwner(), $bundle->getSubOwner(), $bundle->getArea(), $areaType, $bundle->getCategory(), $eventName);
        }
    }

    /**
     * Unregister a subscriber bundle from persistence.
     * @deprecated no longer required with non-persisted hooks
     *
     * @param SubscriberBundle $bundle
     */
    public function unregisterSubscriberBundle(SubscriberBundle $bundle)
    {
        $this->storage->unregisterSubscriberByArea($bundle->getArea());
    }

    /**
     * Register provider bundle with persistence.
     * @deprecated no longer required with non-persisted hooks
     *
     * @param ProviderBundle $bundle
     */
    public function registerProviderBundle(ProviderBundle $bundle)
    {
        foreach ($bundle->getHooks() as $hook) {
            $this->storage->registerProvider($bundle->getOwner(), $bundle->getSubOwner(), $bundle->getArea(), $hook['hooktype'], $bundle->getCategory(), $hook['classname'], $hook['method'], $hook['serviceid']);
        }
    }

    /**
     * Unregister a provider bundle with persistence.
     * @deprecated no longer required with non-persisted hooks
     *
     * @param ProviderBundle $bundle
     */
    public function unregisterProviderBundle(ProviderBundle $bundle)
    {
        $this->storage->unregisterProviderByArea($bundle->getArea());
    }

    /**
     * Return all bindings for a given area.
     *
     * Area names are unique so you can specify subscriber or provider area.
     *
     * @param string $areaName Areaname
     * @param string $type subscriber|provider
     *
     * @return array
     */
    public function getBindingsFor($areaName, $type = 'subscriber')
    {
        return $this->storage->getBindingsFor($areaName, $type);
    }

    /**
     * Get subscriber areas for an owner.
     * @deprecated directly access hookCollector->->getSubscriberAreasByOwner($owner)
     *
     * @param string $owner
     *
     * @return array
     */
    public function getSubscriberAreasByOwner($owner)
    {
        $persistedAreas = $this->storage->getSubscriberAreasByOwner($owner);
        $nonPersistedAreas = $this->hookCollector->getSubscriberAreasByOwner($owner);

        return array_merge($persistedAreas, $nonPersistedAreas);
    }

    /**
     * Get provider areas for an owner.
     * @deprecated directly access hookCollector->->getProviderAreasByOwner($owner)
     *
     * @param string $owner
     *
     * @return array
     */
    public function getProviderAreasByOwner($owner)
    {
        $persistedAreas = $this->storage->getProviderAreasByOwner($owner);
        $nonPersistedAreas = $this->hookCollector->getProviderAreasByOwner($owner);

        return array_merge($persistedAreas, $nonPersistedAreas);
    }

    /**
     * Get owner by area.
     * @deprecated directly access hookCollector->get*($areaName)->getOwner()
     *
     * @param string $areaName
     *
     * @return string
     */
    public function getOwnerByArea($areaName)
    {
        if ($this->hookCollector->hasProvider($areaName)) {
            return $this->hookCollector->getProvider($areaName)->getOwner();
        } else if ($this->hookCollector->hasSubscriber($areaName)) {
            return $this->hookCollector->getSubscriber($areaName)->getOwner();
        } else {
            return $this->storage->getOwnerByArea($areaName); // @deprecated
        }
    }

    /**
     * Set the bind order of hooks.
     *
     * Used to resort the order providers are invoked for a given
     * area name.
     *
     * @param string $subscriberAreaName
     * @param array  $providerAreas      Array of provider area names
     */
    public function setBindOrder($subscriberAreaName, array $providerAreas)
    {
        $this->storage->setBindOrder($subscriberAreaName, $providerAreas);
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
     * Decorate hook with required metadata.
     *
     * @param $name
     * @param Hook $hook
     */
    private function decorateHook($name, Hook $hook)
    {
        $owningSide = $this->storage->getRuntimeMetaByEventName($name);
        if ($owningSide) {
            $hook->setAreaId($owningSide['areaid']);
            if (!$hook->getCaller()) {
                $hook->setCaller($owningSide['owner']);
            }
        }
    }
}
