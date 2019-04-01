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

interface ReEntrantAuthenticationMethodInterface extends AuthenticationMethodInterface
{
    /**
     * Return the ID of the user sent by the provider.
     */
    public function getId(): string;

    /**
     * After authentication, this method is used to update the user entity.
     */
    public function getEmail(): string;

    /**
     * After authentication, this method is used to update the user entity.
     */
    public function getUname(): string;
}
