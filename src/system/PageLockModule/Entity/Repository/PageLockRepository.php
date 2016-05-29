<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\PageLockModule\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use InvalidArgumentException;

/**
 * Repository class used to implement own convenience methods for performing certain DQL queries.
 *
 * This is the repository class for pagelock entities.
 */
class PageLockRepository extends EntityRepository
{
    /**
     * Returns amount of active locks.
     *
     * @param string $lockName  Name of lock.
     * @param string $sessionId Identifier of current session.
     *
     * @return integer
     *
     * @throws InvalidArgumentException Thrown if invalid parameters are received
     */
    public function getActiveLockAmount($lockName, $sessionId)
    {
        // check parameters
        if ($lockName == '' || $sessionId == '') {
            throw new \InvalidArgumentException('Invalid parameter received.');
        }

        $qb = $this->createQueryBuilder('tbl')
            ->select('COUNT(tbl.id)');
        $qb = $this->addCommonFilters($qb, $lockName, $sessionId);

        $query = $qb->getQuery();

        $count = (int)$query->getSingleScalarResult();

        return $count;
    }

    /**
     * Returns active locks.
     *
     * @param string $lockName  Name of lock.
     * @param string $sessionId Identifier of current session.
     *
     * @return array
     *
     * @throws InvalidArgumentException Thrown if invalid parameters are received
     */
    public function getActiveLocks($lockName, $sessionId)
    {
        // check parameters
        if ($lockName == '' || $sessionId == '') {
            throw new \InvalidArgumentException('Invalid parameter received.');
        }

        $qb = $this->createQueryBuilder('tbl')
            ->select('tbl');
        $qb = $this->addCommonFilters($qb, $lockName, $sessionId);

        $query = $qb->getQuery();

        $locks = $query->getArrayResult();

        // now flush to database
        $this->getEntityManager()->flush();

        return $locks;
    }

    /**
     * Updates the expire date of affected lock.
     *
     * @param string    $lockName   Name of lock to be updated.
     * @param string    $sessionId  Identifier of current session.
     * @param \DateTime $expireDate The new expire date.
     *
     * @return void
     *
     * @throws InvalidArgumentException Thrown if invalid parameters are received
     */
    public function updateExpireDate($lockName, $sessionId, \DateTime $expireDate)
    {
        // check parameters
        if ($lockName == '' || $sessionId == '') {
            throw new \InvalidArgumentException('Invalid parameter received.');
        }

        $qb = $this->createQueryBuilder('tbl')
            ->update('Zikula\PageLockModule\Entity\PageLockEntity', 'tbl')
            ->set('tbl.edate', $expireDate);
        $qb = $this->addCommonFilters($qb, $lockName, $sessionId);

        $query = $qb->getQuery();
        $query->execute();
    }

    /**
     * Deletes all locks which expired.
     *
     * @return void
     */
    public function deleteExpiredLocks()
    {
        $qb = $this->createQueryBuilder('tbl')
            ->delete('Zikula\PageLockModule\Entity\PageLockEntity', 'tbl')
            ->where('tbl.edate < :now')
            ->setParameter('now', time());
        $query = $qb->getQuery();

        $query->execute();
    }

    /**
     * Deletes a lock for a given name.
     *
     * @param string $lockName  Name of lock to be deleted.
     * @param string $sessionId Identifier of current session.
     *
     * @return void
     *
     * @throws InvalidArgumentException Thrown if invalid parameters are received
     */
    public function deleteByLockName($lockName, $sessionId)
    {
        // check parameters
        if ($lockName == '' || $sessionId == '') {
            throw new \InvalidArgumentException('Invalid parameter received.');
        }

        $qb = $this->createQueryBuilder('tbl')
            ->delete('Zikula\PageLockModule\Entity\PageLockEntity', 'tbl');
        $qb = $this->addCommonFilters($qb, $lockName, $sessionId);

        $query = $qb->getQuery();

        $query->execute();

        // now flush to database
        $this->getEntityManager()->flush();
    }

    /**
     * Adds common filters to the given query builder.
     *
     * @param QueryBuilder $qb The current query builder instance.
     * @param string $lockName  Name of lock.
     * @param string $sessionId Identifier of current session.
     *
     * @return QueryBuilder The enriched query builder.
     */
    private function addCommonFilters(QueryBuilder $qb, $lockName, $sessionId)
    {
        $qb
           ->where('tbl.name = :lockName')
           ->setParameter('lockName', $lockName)
           ->andWhere('tbl.session = :sessionId')
           ->setParameter('sessionId', $sessionId);

        return $qb;
    }
}
