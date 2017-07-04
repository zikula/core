<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle\Collector;

use Zikula\Bundle\HookBundle\HookProviderInterface;
use Zikula\Bundle\HookBundle\HookSubscriberInterface;

interface HookCollectorInterface
{
    /**
     * Service is capable of subscribing to hooks.
     */
    const HOOK_SUBSCRIBER = 'hook_subscriber';

    /**
     * Service provides hooks to which other extensions may subscribe.
     */
    const HOOK_PROVIDER = 'hook_provider';

    /**
     * Service is capable of providing to its own subscriber hooks.
     */
    const HOOK_SUBSCRIBE_OWN = 'subscribe_own';

    /**
     * Add a HookProviderInterface to the collection.
     * @param string $areaName
     * @param $serviceId
     * @param HookProviderInterface $service
     * @throws \InvalidArgumentException if duplicate areaName
     */
    public function addProvider($areaName, $serviceId, HookProviderInterface $service);

    /**
     * Get a HookProviderInterface from the collection by areaName.
     * @param $areaName
     * @return HookProviderInterface|null
     */
    public function getProvider($areaName);

    /**
     * Has a HookProviderInterface from the collection by areaName?
     * @param $areaName
     * @return bool
     */
    public function hasProvider($areaName);

    /**
     * Get all the HookProviderInterface in the collection.
     * @return HookProviderInterface[]
     */
    public function getProviders();

    /**
     * @return array of all hook provider areas
     */
    public function getProviderAreas();

    /**
     * @param $owner
     * @return array of provider areas for one owner
     */
    public function getProviderAreasByOwner($owner);

    /**
     * Add a HookSubscriberInterface to the collection.
     * @param string $areaName
     * @param HookSubscriberInterface $service
     * @throws \InvalidArgumentException if duplicate areaName
     */
    public function addSubscriber($areaName, HookSubscriberInterface $service);

    /**
     * Get a HookSubscriberInterface from the collection by areaName.
     * @param $areaName
     * @return HookSubscriberInterface|null
     */
    public function getSubscriber($areaName);

    /**
     * Has a HookSubscriberInterface from the collection by areaName?
     * @param $areaName
     * @return bool
     */
    public function hasSubscriber($areaName);

    /**
     * Get all the HookSubscriberInterface in the collection.
     * @return HookSubscriberInterface[]
     */
    public function getSubscribers();

    /**
     * @return array of all hook subscriber areas
     */
    public function getSubscriberAreas();

    /**
     * @param $owner
     * @return array of subscriber areas for one owner
     */
    public function getSubscriberAreasByOwner($owner);

    /**
     * Is moduleName capable of hook type?
     * @param $moduleName
     * @param string $type
     * @return bool
     * @throws \InvalidArgumentException if $type is not a hook type
     */
    public function isCapable($moduleName, $type = self::HOOK_SUBSCRIBER);

    /**
     * Return all owners capable of hook type
     * @param string $type
     * @return array
     * @throws \InvalidArgumentException if $type is not a hook subscriber or provider
     */
    public function getOwnersCapableOf($type = self::HOOK_SUBSCRIBER);
}
