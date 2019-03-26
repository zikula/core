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

namespace Zikula\UsersModule\AuthenticationMethodInterface;

interface AuthenticationMethodInterface
{
    /**
     * An unique alias for this authentication method.
     * @return string
     */
    public function getAlias();

    /**
     * A displayable title.
     * @return string
     */
    public function getDisplayName();

    /**
     * Describe the nature of this method.
     * @return string
     */
    public function getDescription();

    /**
     * Authenticate the user from the provided data and return the associated native uid.
     * @param array $data
     * @return integer|null
     */
    public function authenticate(array $data = []);

    /**
     * Register a new user from the provided data and map authorization to a Zikula UID.
     * MUST return boolean TRUE on success.
     * @param array $data
     * @return boolean
     */
    public function register(array $data);
}
