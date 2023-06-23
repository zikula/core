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

namespace Zikula\UsersBundle;

/**
 * Users bundle-wide constants.
 */
class UsersConstant
{
    /**
     * The UID of the 'anonymous' user
     */
    public const USER_ID_ANONYMOUS = 1;

    /**
     * The UID of the default/generated admin user
     */
    public const USER_ID_ADMIN = 2;

    /**
     * Pending registration (not able to log in).
     * Moderation and/or e-mail verification are in use in the registration process, and one or more of the required steps has not yet
     * been completed.
     */
    public const ACTIVATED_PENDING_REG = -32768;

    /**
     * User 'activated' state of 'inactive'--not able to log in.
     * This state may be set by the site administrator to prevent any attempt to log in with this account.
     */
    public const ACTIVATED_INACTIVE = 0;

    /**
     * User 'activated' state of 'active'--able to log in.
     */
    public const ACTIVATED_ACTIVE = 1;

    /**
     * User 'activated' state of 'marked for deletion'--soft delete (FUTURE USE)
     * Similar to inactive, but with the expectation that the account could be removed at any time. This state can also be used to
     * simulate deletion without actually deleting the account.
     */
    public const ACTIVATED_PENDING_DELETE = 16384;

    // TODO move to enum
    public const ACTIVATED_OPTIONS = [
        self::ACTIVATED_ACTIVE,
        self::ACTIVATED_INACTIVE,
        self::ACTIVATED_PENDING_DELETE,
        self::ACTIVATED_PENDING_REG
    ];
}
