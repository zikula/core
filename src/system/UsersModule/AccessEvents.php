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

namespace Zikula\UsersModule;

/**
 * Class AccessEvents
 */
class AccessEvents
{
    /**
     * Occurs right after a successful logout.
     * The event's subject contains the user's UserEntity
     * Args contain array of `['authentication_method' => $authenticationMethod,
     * 'uid'=> $uid];`
     */
    public const LOGOUT_SUCCESS = 'module.users.ui.logout.succeeded';
}
