<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Zikula\UsersModule\Constant;
use Zikula\UsersModule\Entity\RepositoryInterface\UserAttributeRepositoryInterface;

class UserAttributeRepository extends EntityRepository implements UserAttributeRepositoryInterface
{
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
        $d = $qb->getDQL();

        return $qb->getQuery()->execute();
    }
}
