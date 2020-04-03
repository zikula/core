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

namespace Zikula\UsersModule\Event;

use Zikula\UsersModule\Entity\UserEntity;

/**
 * Occurs right after a successful logout.
 */
class UserPostLogoutSuccessEvent extends UserEntityEvent implements AuthMethodAwareInterface
{
    use AuthMethodTrait;

    public function __construct(UserEntity $userEntity, string $authenticationMethod)
    {
        parent::__construct($userEntity);
        $this->authenticationMethod = $authenticationMethod;
    }
}
