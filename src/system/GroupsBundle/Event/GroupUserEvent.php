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

namespace Zikula\GroupsBundle\Event;

use Zikula\GroupsBundle\Entity\Group;
use Zikula\UsersBundle\Entity\User;

class GroupUserEvent extends GroupEntityEvent
{
    public function __construct(Group $groupEntity, private readonly User $userEntity)
    {
        parent::__construct($groupEntity);
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
