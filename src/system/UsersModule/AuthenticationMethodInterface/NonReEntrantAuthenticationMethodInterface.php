<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\AuthenticationMethodInterface;

interface NonReEntrantAuthenticationMethodInterface extends AuthenticationMethodInterface
{
    /**
     * Provide a FqCN for a Symfony form for login.
     * @return string
     */
    public function getLoginFormClassName();

    /**
     * Provide a path to the required template for login.
     * @return string
     */
    public function getLoginTemplateName();

    /**
     * Provide a FqCN for a Symfony form for registration.
     * @return string
     */
    public function getRegistrationFormClassName();

    /**
     * Provide a path to the required template for registration.
     * @return string
     */
    public function getRegistrationTemplateName();
}
