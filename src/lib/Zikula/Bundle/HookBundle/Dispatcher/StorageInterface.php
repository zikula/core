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
    public function bindSubscriber($subscriberArea, $providerArea);

    public function unbindSubscriber($subscriberArea, $providerArea);

    public function getBindingsFor($areaName, $type = 'subscriber');

    public function getRuntimeMetaByEventName($eventName);

    public function getRuntimeHandlers();

    public function setBindOrder($subscriberAreaName, array $providerAreas);

    public function getBindingBetweenAreas($subscriberArea, $providerArea);

    public function isAllowedBindingBetweenAreas($subscriberArea, $providerArea);

    public function getBindingsBetweenOwners($subscriberOwner, $providerOwner);
}
