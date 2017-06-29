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

use Zikula\Bundle\HookBundle\Dispatcher\Exception\LogicException;
use Zikula\Bundle\HookBundle\Hook\Hook;

/**
 * Interface HookDispatcherInterface
 */
interface HookDispatcherInterface
{
    /**
     * Get storage driver.
     *
     * @return StorageInterface
     */
    public function getStorage();

    /**
     * Dispatch hook listeners.
     *
     * @param string $name Hook event name
     * @param Hook   $hook Hook instance
     *
     * @return Hook
     */
    public function dispatch($name, Hook $hook);

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
    public function getBindingsFor($areaName, $type = 'subscriber');

    /**
     * Set the bind order of hooks.
     *
     * Used to resort the order providers are invoked for a given
     * area name.
     *
     * @param string $subscriberAreaName
     * @param array  $providerAreas      Array of provider area names
     */
    public function setBindOrder($subscriberAreaName, array $providerAreas);

    /**
     * Get binding between areas.
     *
     * @param string $subscriberArea
     * @param string $providerArea
     *
     * @return array
     */
    public function getBindingBetweenAreas($subscriberArea, $providerArea);

    /**
     * Check if areas may be bound together.
     *
     * @param string $subscriberarea
     * @param string $providerarea
     *
     * @return boolean
     */
    public function isAllowedBindingBetweenAreas($subscriberarea, $providerarea);

    /**
     * Get bindings between two owners.
     *
     * @param string $subscriberName
     * @param string $providerName
     *
     * @return array
     */
    public function getBindingsBetweenOwners($subscriberName, $providerName);

    /**
     * Bind subscriber and provider area together.
     *
     * @param string $subscriberArea
     * @param string $providerArea
     *
     * @throws LogicException
     */
    public function bindSubscriber($subscriberArea, $providerArea);

    /**
     * Unbind subscriber.
     *
     * @param string $subscriberArea
     * @param string $providerArea
     */
    public function unbindSubscriber($subscriberArea, $providerArea);
}
