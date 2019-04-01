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

namespace Zikula\ExtensionsModule;

class Constant
{
    public const STATE_UNINITIALISED = 1;

    public const STATE_INACTIVE = 2;

    public const STATE_ACTIVE = 3;

    public const STATE_MISSING = 4;

    public const STATE_UPGRADED = 5;

    public const STATE_NOTALLOWED = 6;

    public const STATE_INVALID = -1;

    public const STATE_TRANSITIONAL = 7; // installing or uninstalling

    public const INCOMPATIBLE_CORE_SHIFT = 20;
}
