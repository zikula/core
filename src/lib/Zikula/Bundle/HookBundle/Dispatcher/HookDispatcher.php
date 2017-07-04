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
use Zikula\Bundle\HookBundle\Collector\HookCollectorInterface;
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
     * @var HookCollectorInterface
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
     * @param HookCollectorInterface $hookCollector
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        StorageInterface $storage,
        HookCollectorInterface $hookCollector,
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
