<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle;

/**
 * Interface HookSelfAllowedProviderInterface
 *
 * Create a service that implements this interface and tag it with `zikula.hook_provider`
 * The tag must also include an `areaName` argument.
 *
 * Classes implementing this interface will be allowed to be hooked to its own subscribers.
 */
interface HookSelfAllowedProviderInterface extends HookProviderInterface
{
}
