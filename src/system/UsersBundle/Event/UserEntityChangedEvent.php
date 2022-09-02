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

namespace Zikula\UsersBundle\Event;

use Zikula\UsersBundle\Entity\User;

/**
 * An UserEntityEvent that adds an oldUser to reflect changed data.
 */
class UserEntityChangedEvent extends UserEntityEvent
{
    private User $oldUser;

    public function __construct(User $user, User $oldUser)
    {
        parent::__construct($user);
        $this->oldUser = $oldUser;
    }

    public function getOldUser(): User
    {
        return $this->oldUser;
    }
}
