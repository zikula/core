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

/**
 * StorageInterface interface.
 */
interface StorageInterface
{
    public function registerSubscriber($owner, $subOwner, $areaName, $areaType, $category, $eventName);

    public function unregisterSubscriberByArea($areaName);

    public function registerProvider($owner, $subOwner, $areaName, $hookType, $category, $className, $method, $serviceId = null);

    public function unregisterProviderByArea($areaName);

    public function bindSubscriber($subscriberArea, $providerArea);

    public function unbindSubscriber($subscriberArea, $providerArea);

    public function getBindingsFor($areaName);

    public function getSubscriberAreasByOwner($owner);

    public function getProviderAreasByOwner($owner);

    public function getRuntimeMetaByEventName($eventName);

    public function getRuntimeHandlers();

    public function getSubscribersByOwner($owner);

    public function getSubscriberByEventName($eventName);

    public function setBindOrder($subscriberAreaName, array $providerAreas);

    public function getBindingBetweenAreas($subscriberArea, $providerArea);

    public function isAllowedBindingBetweenAreas($subscriberArea, $providerArea);

    public function getOwnerByArea($areaName);

    public function getBindingsBetweenOwners($subscriberOwner, $providerOwner);

    public function getAreaId($areaName);
}
