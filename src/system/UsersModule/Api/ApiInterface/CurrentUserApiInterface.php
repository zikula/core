<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Api\ApiInterface;

interface CurrentUserApiInterface
{
    /**
     * Check if current user is logged in.
     * @return boolean
     */
    public function isLoggedIn();

    /**
     * Gets key
     *
     * @param string $key
     *
     * @return string
     */
    public function get($key);
}
