<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ExtensionsModule;

class Constant
{
    const STATE_UNINITIALISED = 1;
    const STATE_INACTIVE = 2;
    const STATE_ACTIVE = 3;
    const STATE_MISSING = 4;
    const STATE_UPGRADED = 5;
    const STATE_NOTALLOWED = 6;
    const STATE_INVALID = -1;
    const STATE_TRANSITIONAL = 7; // installing or uninstalling
    const INCOMPATIBLE_CORE_SHIFT = 20;
}
