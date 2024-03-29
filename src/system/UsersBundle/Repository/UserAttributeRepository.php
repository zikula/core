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
use Zikula\UsersBundle\Entity\UserAttribute;
use Zikula\UsersBundle\UsersConstant;

class UserAttributeRepository extends ServiceEntityRepository implements UserAttributeRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserAttribute::class);
    }

    public function setEmptyValueWhereAttributeNameIn(
        array $attributeNames,
        array $users = [],
        array $forbiddenUsers = [UsersConstant::USER_ID_ADMIN, UsersConstant::USER_ID_ANONYMOUS]
    ): mixed {
        $qb = $this->createQueryBuilder('a')
            ->update()
            ->set('a.value', '\'\'')
            ->where('a.name IN (:attributeNames)')
            ->setParameter('attributeNames', $attributeNames);
        if (!empty($users)) {
            $qb->andWhere('a.user IN (:users)')
                ->setParameter('users', $users);
        }
        if (!empty($forbiddenUsers)) {
            $qb->andWhere('a.user NOT IN (:forbidden_users)')
                ->setParameter('forbidden_users', $forbiddenUsers);
        }

        return $qb->getQuery()->execute();
    }
}
