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
use Zikula\Bridge\HttpFoundation\ZikulaSessionStorage;
use Zikula\UsersModule\Constant;
use Zikula\UsersModule\Entity\RepositoryInterface\UserSessionRepositoryInterface;
use Zikula\UsersModule\Entity\UserSessionEntity;

class UserSessionRepository extends ServiceEntityRepository implements UserSessionRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserSessionEntity::class);
    }

    public function getUsersSince(\DateTime $dateTime)
    {
        $query = $this->createQueryBuilder('s')
            ->select('DISTINCT s.uid')
            ->where('s.lastused > :activetime')
            ->setParameter('activetime', $dateTime)
            ->andWhere('s.uid != :guestUser')
            ->setParameter('guestUser', Constant::USER_ID_ANONYMOUS)
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
            ->andWhere('s.uid != :guestUser')
            ->setParameter('guestUser', Constant::USER_ID_ANONYMOUS)
            ->getQuery();

        return (int)$query->getSingleScalarResult();
    }

    public function countGuestsSince(\DateTime $dateTime)
    {
        $query = $this->createQueryBuilder('s')
            ->select('COUNT(s.uid)')
            ->where('s.lastused > :activetime')
            ->setParameter('activetime', $dateTime)
            ->andWhere('s.uid = :guestUser')
            ->setParameter('guestUser', Constant::USER_ID_ANONYMOUS)
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
        $inactive->modify("-${inactiveMinutes} minutes");
        $daysOld = new \DateTime();
        $daysOld->modify("-${days} days");

        $qb = $this->createQueryBuilder('s')
            ->delete();
        switch ($level) {
            case ZikulaSessionStorage::SECURITY_LEVEL_LOW:
                $qb->where($qb->expr()->andX(
                    $qb->expr()->eq('s.remember', 0),
                    $qb->expr()->lt('s.lastused', '?1'))
                )->setParameter(1, $inactive);
                break;
            case ZikulaSessionStorage::SECURITY_LEVEL_MEDIUM:
                $qb->where(
                    $qb->expr()->andX(
                        $qb->expr()->eq('s.remember', 0),
                        $qb->expr()->lt('s.lastused', '?1'))
                    )->setParameter(1, $inactive)
                    ->orWhere($qb->expr()->lt('s.lastused', '?2'))->setParameter(2, $daysOld)
                    ->orWhere($qb->expr()->andX(
                        $qb->expr()->eq('s.uid', Constant::USER_ID_ANONYMOUS),
                        $qb->expr()->lt('s.lastused', '?3')
                    ))->setParameter(3, $inactive);
                break;
            case ZikulaSessionStorage::SECURITY_LEVEL_HIGH:
            default:
                $qb->where($qb->expr()->lt('s.lastused', '?1'))->setParameter(1, $inactive);
                break;
        }
        $qb->getQuery()->execute();

        return true;
    }
}
