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

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Zikula\UsersBundle\Entity\Group;
use Zikula\UsersBundle\Entity\User;

class UserRepository extends ServiceEntityRepository implements UserRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findUsersByGroup(Group $group): array
    {
        return $this->createQueryBuilder('u')
            ->innerJoin('u.groups', 'g')
            ->where('g = :group')
            ->setParameter('group', $group)
            ->getQuery()
            ->getResult();
    }

    public function findUsersByGroupId(int $groupId): array
    {
        return $this->createQueryBuilder('u')
            ->innerJoin('u.groups', 'g')
            ->where('g.id = :groupId')
            ->setParameter('groupId', $groupId)
            ->getQuery()
            ->getResult();
    }
}
