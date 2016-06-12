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

interface ReEntrantAuthenticationmethodInterface extends AuthenticationMethodInterface
{
    /**
     * An array of user data sent by the provider.
     * must include keys 'email' and 'nickname'
     * @return array
     */
    public function getUserData();
}
