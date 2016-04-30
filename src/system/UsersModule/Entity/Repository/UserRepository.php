<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Entity\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\UsersModule\Constant as UsersConstant;

class UserRepository extends EntityRepository implements UserRepositoryInterface
{
    public function findByUids($uids)
    {
        if (!is_array($uids)) {
            $uids = [$uids];
        }

        return parent::findBy(['uid' => $uids]);
    }

    public function persistAndFlush(UserEntity $user)
    {
        $this->_em->persist($user);
        $this->_em->flush($user);
    }

    public function removeAndFlush(UserEntity $user)
    {
        $this->_em->remove($user);
        $this->_em->flush($user);
    }

    public function removeArray(array $userIds)
    {
        $users = $this->query(['uid' => ['operator' => 'in', 'operand' => $userIds]]);
        foreach ($users as $user) {
            $this->_em->remove($user);
        }
        $this->_em->flush();
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
    public function find($id, $lockMode = null, $lockVersion = null)
    {
        return parent::find($id, $lockMode, $lockVersion);
    }

    /**
     * @param array $formData
     * @return Paginator
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
     * Fetch a collection of users. Optionally filter, sort, limit, offset results.
     *   filter = [field => value, field => value, field => ['operator' => '!=', 'operand' => value], ...]
     *   when value is not an array, operator is assumed to be '='
     *
     * @param array $filter
     * @param array $sort
     * @param int $limit
     * @param int $offset
     * @param string $exprType
     * @return \Doctrine\ORM\Tools\Pagination\Paginator|UserEntity[]
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
        $where = $this->whereFromFilter($qb, $filter, $exprType);
        $qb->andWhere($where);
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
     * Construct a QueryBuilder Expr object suitable for use in QueryBuilder->where(Expr).
     * filter = [field => value, field => value, field => ['operator' => '!=', 'operand' => value], ...]
     * when value is not an array, operator is assumed to be '='
     *
     * @param QueryBuilder $qb
     * @param array $filter The filter, see getAll() and countAll().
     * @param string $exprType default 'and'
     * @return Expr\Composite
     */
    private function whereFromFilter(QueryBuilder $qb, array $filter, $exprType = 'and')
    {
        $exprType = in_array($exprType, ['and', 'or']) ? $exprType : 'and';
        $exprMethod = strtolower($exprType) . "X";
        /** @var \Doctrine\ORM\Query\Expr\Composite $expr */
        $expr = $qb->expr()->$exprMethod();
        $i = 1; // parameter counter
        $alias = 'u';
        foreach ($filter as $field => $value) {
            if ($field == 'groups') {
                $field = 'gid';
                $alias = 'g';
            }
            if (!is_array($value)) {
                $value = [
                    'operator' => '=',
                    'operand' => $value,
                ];
            }
            if (preg_match('/^IS (NOT )?NULL/i', $value['operator'], $matches)) {
                $method = isset($matches[1]) ? 'isNotNull' : 'isNull';
                $expr->add($qb->expr()->$method($alias . '.' . $field));
            } else {
                if (is_bool($value['operand'])) {
                    $dbValue = $value['operand'] ? '1' : '0';
                } elseif (is_int($value['operand']) || is_array($value['operand']) || ($value['operand'] instanceof \DateTime)) {
                    $dbValue = $value['operand'];
                } else {
                    $dbValue = "{$value['operand']}";
                }
                $methodMap = [
                    '=' => 'eq',
                    '>' => 'gt',
                    '<' => 'lt',
                    '>=' => 'gte',
                    '<=' => 'lte',
                    '<>' => 'neq',
                    '!=' => 'neq',
                    'like' => 'like',
                    'notLike' => 'notLike',
                    'in' => 'in',
                    'notIn' => 'notIn',
                ];
                $method = $methodMap[$value['operator']];

                $expr->add($qb->expr()->$method($alias . '.' . $field, '?' . $i));
                $qb->setParameter($i, $dbValue);
            }
            $i++;
        }

        return $expr;
    }
}
