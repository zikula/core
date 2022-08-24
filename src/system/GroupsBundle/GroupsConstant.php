<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\GroupsBundle;

class GroupsConstant
{
    /**
     * Guests
     */
    public const GROUP_ID_GUESTS = 0;

    /**
     * Default users group
     */
    public const GROUP_ID_USERS = 1;

    /**
     * Default administrators group
     */
    public const GROUP_ID_ADMIN = 2;

    /**
     * Constant value for core type groups.
     */
    public const GTYPE_CORE = 0;

    /**
     * Constant value for public type groups.
     */
    public const GTYPE_PUBLIC = 1;

    /**
     * Constant value for private type groups.
     */
    public const GTYPE_PRIVATE = 2;

    /**
     * Constant value for groups in the Closed state (not accepting members).
     */
    public const STATE_CLOSED = 0;

    /**
     * Constant value for groups in the Open state (accepting members).
     */
    public const STATE_OPEN = 1;
}
