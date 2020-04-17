<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle\Dispatcher;

use Zikula\Bundle\HookBundle\Exception\LogicException;
use Zikula\Bundle\HookBundle\Hook\Hook;

/**
 * Interface HookDispatcherInterface
 */
interface HookDispatcherInterface
{
    /**
     * Get storage driver.
     */
    public function getStorage(): StorageInterface;

    /**
     * Dispatch hook listeners.
     */
    public function dispatch(string $eventName, Hook $hook): Hook;

    /**
     * Return all bindings for a given area.
     * Area names are unique so you can specify subscriber or provider area.
     */
    public function getBindingsFor(string $areaName, string $type = 'subscriber'): array;

    /**
     * Set the bind order of hooks.
     * Used to resort the order providers are invoked for a given area name.
     */
    public function setBindOrder(string $subscriberAreaName, array $providerAreas = []): void;

    /**
     * Get binding between areas.
     */
    public function getBindingBetweenAreas(string $subscriberArea, string $providerArea): array;

    /**
     * Check if areas may be bound together.
     */
    public function isAllowedBindingBetweenAreas(string $subscriberarea, string $providerarea): bool;

    /**
     * Get bindings between two owners.
     */
    public function getBindingsBetweenOwners(string $subscriberName, string $providerName): array;

    /**
     * Bind subscriber and provider area together.
     *
     * @throws \Zikula\Bundle\HookBundle\Exception\LogicException
     */
    public function bindSubscriber(string $subscriberArea, string $providerArea): void;

    /**
     * Unbind subscriber.
     */
    public function unbindSubscriber(string $subscriberArea, string $providerArea): void;
}
