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

namespace Zikula\Bundle\HookBundle;

/**
 * Interface HookProviderInterface
 *
 * Create a service that implements this interface and tag it with `zikula.hook_provider`
 * The tag must also include an `areaName` argument.
 */
interface HookProviderInterface extends HookInterface
{
    /**
     * Returns an array of hook types this provider wants to listen to.
     *
     * The array keys are hook types and the value can be:
     *  * The method name to call
     *  * An array composed of the method names to call
     *
     * For instance:
     *  * array('hookType' => 'methodName')
     *  * array('hookType' => array('methodName1','methodName2'))
     */
    public function getProviderTypes(): array;
}
