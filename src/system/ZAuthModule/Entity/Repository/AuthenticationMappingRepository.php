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

namespace Zikula\ZAuthModule\Entity\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\OrderBy;
use Doctrine\Persistence\ManagerRegistry;
use Zikula\Bundle\CoreBundle\Doctrine\Paginator;
use Zikula\Bundle\CoreBundle\Doctrine\WhereFromFilterTrait;
use Zikula\ZAuthModule\Entity\AuthenticationMappingEntity;
use Zikula\ZAuthModule\Entity\RepositoryInterface\AuthenticationMappingRepositoryInterface;

class AuthenticationMappingRepository extends ServiceEntityRepository implements AuthenticationMappingRepositoryInterface
{
    use WhereFromFilterTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuthenticationMappingEntity::class);
    }

    public function persistAndFlush(AuthenticationMappingEntity $entity): void
    {
        $this->_em->persist($entity);
        $this->_em->flush();
    }

    public function removeByZikulaId(int $userId): void
    {
        $mapping = $this->findOneBy(['uid' => $userId]);
        if (isset($mapping)) {
            $this->_em->remove($mapping);
            $this->_em->flush();
        }
    }

    public function getByZikulaId(int $userId): AuthenticationMappingEntity
    {
        return $this->findOneBy(['uid' => $userId]);
    }

    public function setEmailVerification(int $userId, bool $value = true): void
    {
        $mapping = $this->findOneBy(['uid' => $userId]);
        if (isset($mapping)) {
            $mapping->setVerifiedEmail($value);
            $this->_em->flush();
        }
    }

    /**
     * Fetch a collection of users. Optionally filter, sort results.
     *   filter = [field => value, field => value, field => ['operator' => '!=', 'operand' => value], ...]
     *   when value is not an array, operator is assumed to be '='
     */
    public function query(
        array $filter = [],
        array $sort = [],
        string $exprType = 'and',
        int $page = 1,
        int $pageSize = 25
    ): Paginator {
        $qb = $this->createQueryBuilder('m')
            ->select('m');
        if (!empty($filter)) {
            $where = $this->whereFromFilter($qb, $filter, $exprType, 'm');
            $qb->andWhere($where);
        }
        if (!empty($sort)) {
            $qb->orderBy($this->orderByFromArray($sort));
        }
        return (new Paginator($qb, $pageSize))->paginate($page);
    }

    public function getByExpiredPasswords()
    {
        $qb = $this->createQueryBuilder('m')
            ->select('m')
            ->where('m.pass NOT LIKE :param')
            ->setParameter('param', '$%');

        return $qb->getQuery()->getResult();
    }

    /**
     * Construct a QueryBuilder Expr\OrderBy object suitable for use in QueryBuilder->orderBy() from an array.
     * sort = [field => dir, field => dir, ...]
     */
    private function orderByFromArray(array $sort = []): OrderBy
    {
        $orderBy = new OrderBy();
        foreach ($sort as $field => $direction) {
            $orderBy->add('m.' . $field, $direction);
        }

        return $orderBy;
    }
}
