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

use Zikula\UsersModule\Entity\UserEntity;

interface ReEntrantAuthenticationMethodInterface extends AuthenticationMethodInterface
{
    /**
     * Return the ID of the user sent by the provider.
     * @return string
     */
    public function getId();

    /**
     * After authentication, this method is used to update the Entity.
     * properties `email` and `uname` must be updated.
     */
    public function updateUserEntity(UserEntity $userEntity);
}
