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

namespace Zikula\UsersModule\Api\ApiInterface;

interface CurrentUserApiInterface
{
    /**
     * Check if current user is logged in.
     */
    public function isLoggedIn(): bool;

    /**
     * Gets a property for the given key.
     *
     * @return mixed
     */
    public function get(string $key);
}
