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

use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Doctrine\ORM\Query\Expr\OrderBy;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Zikula\Core\Doctrine\WhereFromFilterTrait;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\UsersModule\Entity\UserAttributeEntity;

class UserRepository extends ServiceEntityRepository implements UserRepositoryInterface
{
    use WhereFromFilterTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserEntity::class);
    }

    public function findByUids(array $userIds = []): array
    {
        return $this->findBy(['uid' => $userIds]);
    }

    public function persistAndFlush(UserEntity $user): void
    {
        $this->_em->persist($user);
        $this->_em->flush($user);
    }

    public function removeAndFlush(UserEntity $user): void
    {
        // the following process should be unnecessary because cascade = all but MySQL 5.7 not working with that (#3726)
        $qb = $this->_em->createQueryBuilder();
        $qb->delete(UserAttributeEntity::class, 'a')
           ->where('a.user = :userId')
           ->setParameter('userId', $user->getUid());
        $query = $qb->getQuery();
        $query->execute();
        // end of theoretically unrequired process

        $user->setAttributes(new ArrayCollection());
        $this->_em->remove($user);
        $this->_em->flush($user);
    }

    public function setApproved(UserEntity $user, DateTime $approvedOn, int $approvedBy = null): void
    {
        $user->setApproved_Date($approvedOn);
        $user->setApproved_By($approvedBy ?? $user->getUid());
        $this->_em->flush($user);
    }

    public function queryBySearchForm(array $formData = [])
    {
        $filter = ['activated' => ['operator' => '!=', 'operand' => UsersConstant::ACTIVATED_PENDING_REG]];
        foreach ($formData as $k => $v) {
            if (!empty($v)) {
                switch ($k) {
                    case 'registered_before':
                        $filter['user_regdate'] = ['operator' => '<=', 'operand' => $v];
                        break;
                    case 'registered_after':
                        $filter['user_regdate'] = ['operator' => '>=', 'operand' => $v];
                        break;
                    case 'groups':
                        /** @var ArrayCollection $v */
                        if (!$v->isEmpty()) {
                            $filter['groups'] = ['operator' => 'in', 'operand' => $v->getValues()];
                        }
                        break;
                    default:
                        $filter[$k] = ['operator' => 'like', 'operand' => "%${v}%"];
                }
            }
        }

        return $this->query($filter);
    }

    public function getSearchResults(array $words = [])
    {
        $qb = $this->createQueryBuilder('u')
            ->andWhere('u.activated != :activated')
            ->setParameter('activated', UsersConstant::ACTIVATED_PENDING_REG);
        $where = $qb->expr()->orX();
        $i = 1;
        foreach ($words as $word) {
            $subWhere = $qb->expr()->orX();
            $expr = $qb->expr()->like('u.uname', "?${i}");
            $subWhere->add($expr);
            $qb->setParameter($i, '%' . $word . '%');
            $i++;
            $where->add($subWhere);
        }
        $qb->andWhere($where);

        return $qb->getQuery()->getResult();
    }

    public function query(
        array $filter = [],
        array $sort = [],
        int $limit = 0,
        int $offset = 0,
        string $exprType = 'and'
    ) {
        $qb = $this->createQueryBuilder('u')
            ->select('u');
        if (!empty($filter['groups'])) {
            $qb->join('u.groups', 'g');
        }
        if (!empty($filter)) {
            $where = $this->whereFromFilter($qb, $filter, $exprType);
            $qb->andWhere($where);
        }
        if (!empty($sort)) {
            $qb->orderBy($this->orderByFromArray($sort));
        }
        $query = $qb->getQuery();

        if ($limit > 0) {
            $query->setMaxResults($limit);
            $query->setFirstResult($offset);

            return new Paginator($query);
        }

        return $query->getResult();
    }

    public function count(array $filter = [], string $exprType = 'and'): int
    {
        $qb = $this->createQueryBuilder('u')
            ->select('count(u.uid)');
        if (!empty($filter)) {
            $where = $this->whereFromFilter($qb, $filter, $exprType);
            $qb->andWhere($where);
        }
        $query = $qb->getQuery();

        return (int)$query->getSingleScalarResult();
    }

    /**
     * Construct a QueryBuilder Expr\OrderBy object suitable for use in QueryBuilder->orderBy() from an array.
     * sort = [field => dir, field => dir, ...]
     */
    private function orderByFromArray(array $sort = []): OrderBy
    {
        $orderBy = new OrderBy();
        foreach ($sort as $field => $direction) {
            $orderBy->add('u.' . $field, $direction);
        }

        return $orderBy;
    }

    public function findAllAsIterable(): IterableResult
    {
        $qb = $this->createQueryBuilder('u');

        return $qb->getQuery()->iterate();
    }

    public function searchActiveUser(array $unameFilter = [], int $limit = 50)
    {
        if (!count($unameFilter)) {
            return [];
        }

        $filter = [
            'activated' => ['operator' => 'notIn', 'operand' => [
                UsersConstant::ACTIVATED_PENDING_REG,
                UsersConstant::ACTIVATED_PENDING_DELETE
            ]],
            'uname' => $unameFilter
        ];

        return $this->query($filter, ['uname' => 'asc'], $limit);
    }
}
