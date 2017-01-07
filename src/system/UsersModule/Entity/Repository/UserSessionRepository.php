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
use Zikula\Bridge\HttpFoundation\ZikulaSessionStorage;
use Zikula\UsersModule\Entity\RepositoryInterface\UserSessionRepositoryInterface;
use Zikula\UsersModule\Entity\UserSessionEntity;

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

    public function persistAndFlush(UserSessionEntity $entity)
    {
        $this->_em->persist($entity);
        $this->_em->flush($entity);
    }

    public function removeAndFlush($id)
    {
        $entity = $this->find($id);
        if ($entity) {
            $this->_em->remove($entity);
            $this->_em->flush($entity);
        }
    }

    public function gc($level, $inactiveMinutes, $days)
    {
        $inactive = new \DateTime();
        $inactive->modify("-$inactiveMinutes minutes");
        $daysOld = new \DateTime();
        $daysOld->modify("-$days days");

        $qb = $this->createQueryBuilder('s')
            ->delete();
        switch ($level) {
            case ZikulaSessionStorage::SECURITY_LEVEL_LOW:
                $qb->where('s.remember = 0')
                    ->andWhere('s.lastused < :inactive')
                    ->setParameter('inactive', $inactive);
                break;
            case ZikulaSessionStorage::SECURITY_LEVEL_MEDIUM:
                $qb->where('s.remember = 0')
                    ->andWhere('s.lastused < :inactive')
                    ->setParameter('inactive', $inactive)
                    ->orWhere('s.lastused < :daysOld')
                    ->setParameter('daysOld', $daysOld)
                    ->orWhere(
                        $qb->where('s.uid = :anonymous')
                            ->setParameter('anonymous', 0) // @todo anonymous user id
                            ->andWhere('s.lastused < :inactive')
                            ->setParameter('inactive', $inactive)
                    );
                break;
            case ZikulaSessionStorage::SECURITY_LEVEL_HIGH:
            default:
                $qb->where('s.lastused < :inactive')
                    ->setParameter('inactive', $inactive);
                break;
        }
        $qb->getQuery()->execute();

        return true;
    }
}
