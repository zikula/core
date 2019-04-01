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

interface NonReEntrantAuthenticationMethodInterface extends AuthenticationMethodInterface
{
    /**
     * Provide a FqCN for a Symfony form for login.
     */
    public function getLoginFormClassName(): string;

    /**
     * Provide a path to the required template for login.
     */
    public function getLoginTemplateName(string $type = 'page', string $position = 'left'): string;

    /**
     * Provide a FqCN for a Symfony form for registration.
     */
    public function getRegistrationFormClassName(): string;

    /**
     * Provide a path to the required template for registration.
     */
    public function getRegistrationTemplateName(): string;
}
