<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package HookDispatcher
 * @subpackage Storage
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
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
