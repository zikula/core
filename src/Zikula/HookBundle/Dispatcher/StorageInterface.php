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

/**
 * StorageInterface interface.
 */
interface StorageInterface
{
    public function bindSubscriber(string $subscriberArea, string $providerArea): void;

    public function unbindSubscriber(string $subscriberArea, string $providerArea): void;

    public function getBindingsFor(string $areaName, string $type = 'subscriber'): array;

    public function getRuntimeMetaByEventName(string $eventName);

    public function setBindOrder(string $subscriberAreaName, array $providerAreas): void;

    public function getBindingBetweenAreas(string $subscriberArea, string $providerArea);

    public function isAllowedBindingBetweenAreas(string $subscriberArea, string $providerArea): bool;

    public function getBindingsBetweenOwners(string $subscriberOwner, string $providerOwner): array;
}
