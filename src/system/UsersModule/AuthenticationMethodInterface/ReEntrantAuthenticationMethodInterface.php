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

interface ReEntrantAuthenticationMethodInterface extends AuthenticationMethodInterface
{
    /**
     * Return the ID of the user sent by the provider.
     * @return string
     */
    public function getId();

    /**
     * After authentication, this method is used to update the Entity.
     */
    public function getEmail();

    /**
     * After authentication, this method is used to update the Entity.
     */
    public function getUname();
}
