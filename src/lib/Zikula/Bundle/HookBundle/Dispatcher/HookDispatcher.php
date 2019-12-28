<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle\Dispatcher;

use Symfony\Component\EventDispatcher\LegacyEventDispatcherProxy;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
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
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    public function __construct(
        StorageInterface $storage,
        EventDispatcherInterface $dispatcher
    ) {
        $this->storage = $storage;
        $this->dispatcher = LegacyEventDispatcherProxy::decorate($dispatcher);
    }

    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    public function dispatch(string $name, Hook $hook): Event
    {
        $this->decorateHook($name, $hook);
        if (!$hook->getAreaId()) {
            return $hook;
        }

        return $this->dispatcher->dispatch($hook, $name);
    }

    public function getBindingsFor(string $areaName, string $type = 'subscriber'): array
    {
        return $this->storage->getBindingsFor($areaName, $type);
    }

    public function setBindOrder(string $subscriberAreaName, array $providerAreas = []): void
    {
        $this->storage->setBindOrder($subscriberAreaName, $providerAreas);
    }

    public function getBindingBetweenAreas(string $subscriberArea, string $providerArea): array
    {
        return $this->storage->getBindingBetweenAreas($subscriberArea, $providerArea);
    }

    public function isAllowedBindingBetweenAreas(string $subscriberarea, string $providerarea): bool
    {
        return $this->storage->isAllowedBindingBetweenAreas($subscriberarea, $providerarea);
    }

    public function getBindingsBetweenOwners(string $subscriberName, string $providerName): array
    {
        return $this->storage->getBindingsBetweenOwners($subscriberName, $providerName);
    }

    public function bindSubscriber(string $subscriberArea, string $providerArea): void
    {
        $this->storage->bindSubscriber($subscriberArea, $providerArea);
    }

    public function unbindSubscriber(string $subscriberArea, string $providerArea): void
    {
        $this->storage->unbindSubscriber($subscriberArea, $providerArea);
    }

    /**
     * Decorate hook with required metadata.
     */
    private function decorateHook(string $name, Hook $hook): void
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
