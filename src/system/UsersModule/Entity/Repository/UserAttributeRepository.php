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

namespace Zikula\UsersModule\Entity\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Zikula\UsersModule\Constant;
use Zikula\UsersModule\Entity\RepositoryInterface\UserAttributeRepositoryInterface;
use Zikula\UsersModule\Entity\UserAttributeEntity;

class UserAttributeRepository extends ServiceEntityRepository implements UserAttributeRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserAttributeEntity::class);
    }

    /**
     * @param array $attributeNames
     * @param array $users
     * @param array $forbiddenUsers
     * @return mixed
     */
    public function setEmptyValueWhereAttributeNameIn(
        array $attributeNames,
        array $users = [],
        array $forbiddenUsers = [Constant::USER_ID_ADMIN, Constant::USER_ID_ANONYMOUS]
    ) {
        $qb = $this->createQueryBuilder('a')
            ->update()
            ->set('a.value', '\'\'')
            ->where('a.name IN (:attributeNames)')
            ->setParameter('attributeNames', $attributeNames);
        if (!empty($users)) {
            $qb->andWhere('a.user IN (:users)')
                ->setParameter('users', $users);
        }
        $qb->andWhere('a.user NOT IN (:forbidden_users)')
            ->setParameter('forbidden_users', $forbiddenUsers);

        return $qb->getQuery()->execute();
    }
}
