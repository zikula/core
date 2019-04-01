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
     */
    public function getAlias(): string;

    /**
     * A displayable title.
     */
    public function getDisplayName(): string;

    /**
     * Describe the nature of this method.
     */
    public function getDescription(): string;

    /**
     * Authenticate the user from the provided data and return the associated native uid.
     */
    public function authenticate(array $data = []): ?int;

    /**
     * Register a new user from the provided data and map authorization to a Zikula UID.
     * MUST return boolean TRUE on success.
     */
    public function register(array $data = []): bool;
}
