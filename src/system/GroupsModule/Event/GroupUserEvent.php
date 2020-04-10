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

namespace Zikula\GroupsModule\Event;

use Zikula\GroupsModule\Entity\GroupEntity;
use Zikula\UsersModule\Entity\UserEntity;

class GroupUserEvent extends GroupEntityEvent
{
    /**
     * @var \Zikula\UsersModule\Entity\UserEntity
     */
    private $user;

    public function __construct(GroupEntity $groupEntity, UserEntity $userEntity)
    {
        parent::__construct($groupEntity);
        $this->user = $userEntity;
    }

    public function getUser(): UserEntity
    {
        return $this->user;
    }
}
