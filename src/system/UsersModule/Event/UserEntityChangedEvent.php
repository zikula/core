<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Event;

use Zikula\UsersModule\Entity\UserEntity;

/**
 * An UserEntityEvent that adds an oldUser to reflect changed data.
 */
class UserEntityChangedEvent extends UserEntityEvent
{
    /**
     * @var UserEntity
     */
    private $oldUser;

    public function __construct(
        UserEntity $user,
        UserEntity $oldUser
    ) {
        parent::__construct($user);
        $this->oldUser = $oldUser;
    }

    public function getOldUser(): UserEntity
    {
        return $this->oldUser;
    }
}
