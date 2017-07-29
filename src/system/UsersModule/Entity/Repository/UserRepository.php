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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Zikula\Core\Doctrine\WhereFromFilterTrait;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\UsersModule\Entity\UserEntity;

class UserRepository extends EntityRepository implements UserRepositoryInterface
{
    use WhereFromFilterTrait;

    public function findByUids($uids)
    {
        if (!is_array($uids)) {
            $uids = [$uids];
        }

        return $this->findBy(['uid' => $uids]);
    }

    public function persistAndFlush(UserEntity $user)
    {
        $this->_em->persist($user);
        $this->_em->flush($user);
    }

    public function removeAndFlush(UserEntity $user)
    {
        foreach ($user->getAttributes() as $attribute) {
            // this should be unnecessary because cascade = all but MySQL 5.7 not working with that
            $this->_em->remove($attribute);
        }
        $this->_em->remove($user);
        $this->_em->flush($user);
    }

    /**
     * {@inheritdoc}
     */
    public function setApproved(UserEntity $user, $approvedOn, $approvedBy = null)
    {
        $user->setApproved_Date($approvedOn);
        $approvedBy = isset($approvedBy) ? $approvedBy : $user->getUid();
        $user->setApproved_By($approvedBy);
        $this->_em->flush($user);
    }

    /**
     * {@inheritdoc}
     */
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
                        $filter[$k] = ['operator' => 'like', 'operand' => "%$v%"];
                }
            }
        }

        return $this->query($filter);
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchResults(array $words)
    {
        $qb = $this->createQueryBuilder('u')
            ->andWhere('u.activated <> :activated')
            ->setParameter('activated', UsersConstant::ACTIVATED_PENDING_REG);
        $where = $qb->expr()->orX();
        $i = 1;
        foreach ($words as $word) {
            $subWhere = $qb->expr()->orX();
            $expr = $qb->expr()->like('u.uname', "?$i");
            $subWhere->add($expr);
            $qb->setParameter($i, '%' . $word . '%');
            $i++;
            $where->add($subWhere);
        }
        $qb->andWhere($where);

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function query(array $filter = [], array $sort = [], $limit = 0, $offset = 0, $exprType = 'and')
    {
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
            $paginator = new Paginator($query);

            return $paginator;
        } else {
            return $query->getResult();
        }
    }

    public function count(array $filter = [], $exprType = 'and')
    {
        $qb = $this->createQueryBuilder('u')
            ->select('count(u.uid)');
        if (!empty($filter)) {
            $where = $this->whereFromFilter($qb, $filter, $exprType);
            $qb->andWhere($where);
        }
        $query = $qb->getQuery();

        return $query->getSingleScalarResult();
    }

    /**
     * Construct a QueryBuilder Expr\OrderBy object suitable for use in QueryBuilder->orderBy() from an array.
     * sort = [field => dir, field => dir, ...]
     * @param array $sort
     * @return Expr\OrderBy
     */
    private function orderByFromArray(array $sort)
    {
        $orderBy = new Expr\OrderBy();
        foreach ($sort as $field => $direction) {
            $orderBy->add("u.$field", $direction);
        }

        return $orderBy;
    }

    /**
     * {@inheritdoc}
     */
    public function findAllAsIterable()
    {
        $qb = $this->createQueryBuilder('u');

        return $qb->getQuery()->iterate();
    }

    /**
     * Searches for a user name excluding pending and deleted users.
     *
     * @param array $unameFilter
     * @param int $limit
     * @return UserEntity[]
     */
    public function searchActiveUser(array $unameFilter = [], $limit = 50)
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
