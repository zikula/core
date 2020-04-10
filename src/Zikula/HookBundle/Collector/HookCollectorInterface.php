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

namespace Zikula\Bundle\HookBundle\Collector;

use InvalidArgumentException;
use Zikula\Bundle\HookBundle\HookProviderInterface;
use Zikula\Bundle\HookBundle\HookSubscriberInterface;

interface HookCollectorInterface
{
    /**
     * Service is capable of subscribing to hooks.
     */
    public const HOOK_SUBSCRIBER = 'hook_subscriber';

    /**
     * Service provides hooks to which other extensions may subscribe.
     */
    public const HOOK_PROVIDER = 'hook_provider';

    /**
     * Service is capable of providing to its own subscriber hooks.
     */
    public const HOOK_SUBSCRIBE_OWN = 'subscribe_own';

    /**
     * Add a HookProviderInterface to the collection.
     *
     * @throws InvalidArgumentException if duplicate areaName
     */
    public function addProvider(HookProviderInterface $service): void;

    /**
     * Get a HookProviderInterface from the collection by areaName.
     */
    public function getProvider(string $areaName): ?HookProviderInterface;

    /**
     * Has a HookProviderInterface from the collection by areaName?
     */
    public function hasProvider(string $areaName): bool;

    /**
     * Get all the HookProviderInterface in the collection.
     *
     * @return HookProviderInterface[]
     */
    public function getProviders(): iterable;

    /**
     * @return string[] array of all hook provider areas
     */
    public function getProviderAreas(): array;

    /**
     * @return string[] array of provider areas for one owner
     */
    public function getProviderAreasByOwner(string $owner): array;

    /**
     * Add a HookSubscriberInterface to the collection.
     *
     * @throws InvalidArgumentException if duplicate areaName
     */
    public function addSubscriber(HookSubscriberInterface $service): void;

    /**
     * Get a HookSubscriberInterface from the collection by areaName.
     */
    public function getSubscriber(string $areaName): ?HookSubscriberInterface;

    /**
     * Has a HookSubscriberInterface from the collection by areaName?
     */
    public function hasSubscriber(string $areaName): bool;

    /**
     * Get all the HookSubscriberInterface in the collection.
     *
     * @return HookSubscriberInterface[]
     */
    public function getSubscribers(): iterable;

    /**
     * @return string[] array of all hook subscriber areas
     */
    public function getSubscriberAreas(): array;

    /**
     * @return string[] array of subscriber areas for one owner
     */
    public function getSubscriberAreasByOwner(string $owner): array;

    /**
     * Is moduleName capable of hook type?
     *
     * @throws InvalidArgumentException if $type is not a hook type
     */
    public function isCapable(string $moduleName, string $type = self::HOOK_SUBSCRIBER): bool;

    /**
     * Return all owners capable of hook type
     *
     * @return string[]
     * @throws InvalidArgumentException if $type is not a hook subscriber or provider
     */
    public function getOwnersCapableOf(string $type = self::HOOK_SUBSCRIBER): array;
}
