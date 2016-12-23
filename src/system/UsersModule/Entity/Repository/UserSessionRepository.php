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
use Zikula\UsersModule\Entity\RepositoryInterface\UserSessionRepositoryInterface;

class UserSessionRepository extends EntityRepository implements UserSessionRepositoryInterface
{
    public function getUsersSince(\DateTime $dateTime)
    {
        $query = $this->createQueryBuilder('s')
            ->select('DISTINCT s.uid')
            ->where('s.lastused > :activetime')
            ->setParameter('activetime', $dateTime)
            ->andWhere('s.uid != 0')
            ->getQuery();
        $users = $query->getArrayResult();
        $result = [];
        foreach ($users as $user) {
            $result[] = $user['uid'];
        }

        return $result;
    }

    public function countUsersSince(\DateTime $dateTime)
    {
        $query = $this->createQueryBuilder('s')
            ->select('COUNT(s.uid)')
            ->where('s.lastused > :activetime')
            ->setParameter('activetime', $dateTime)
            ->andWhere('s.uid != 0')
            ->getQuery();

        return (int)$query->getSingleScalarResult();
    }

    public function countGuestsSince(\DateTime $dateTime)
    {
        $query = $this->createQueryBuilder('s')
            ->select('COUNT(s.uid)')
            ->where('s.lastused > :activetime')
            ->setParameter('activetime', $dateTime)
            ->andWhere('s.uid = 0')
            ->getQuery();

        return (int)$query->getSingleScalarResult();
    }
}
