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

namespace Zikula\UsersBundle\Repository;

use Doctrine\Common\Collections\Selectable;
use Doctrine\Persistence\ObjectRepository;
use Zikula\UsersBundle\Entity\Group;

interface UserRepositoryInterface extends ObjectRepository, Selectable
{
    public function findUsersByGroup(Group $group): array;

    public function findUsersByGroupId(int $groupId): array;
}
