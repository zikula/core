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

namespace Zikula\Component\HookDispatcher;

/**
 * StorageInterface interface.
 */
interface StorageInterface
{
    function registerSubscriber($owner, $subOwner, $areaName, $areaType, $category, $eventName);
    function unregisterSubscriberByArea($areaName);
    function registerProvider($owner, $subOwner, $areaName, $hookType, $category, $className, $method, $serviceId=null);
    function unregisterProviderByArea($areaName);
    function bindSubscriber($subscriberArea, $providerArea);
    function unbindSubscriber($subscriberArea, $providerArea);
    function getBindingsFor($areaName);
    function getSubscriberAreasByOwner($owner);
    function getProviderAreasByOwner($owner);
    function getRuntimeMetaByEventName($eventName);
    function getRuntimeHandlers();
    function getSubscribersByOwner($owner);
    function getSubscriberByEventName($eventName);
    function setBindOrder($subscriberAreaName, array $providerAreas);
    function getBindingBetweenAreas($subscriberArea, $providerArea);
    function isAllowedBindingBetweenAreas($subscriberArea, $providerArea);
    function getOwnerByArea($areaName);
    function getBindingsBetweenOwners($subscriberOwner, $providerOwner);
    function getAreaId($areaName);
}

