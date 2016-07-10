<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ZAuthModule\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\OrderBy;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Zikula\Core\Doctrine\WhereFromFilterTrait;
use Zikula\OAuthModule\Entity\MappingEntity;
use Zikula\ZAuthModule\Entity\AuthenticationMappingEntity;
use Zikula\ZAuthModule\Entity\RepositoryInterface\AuthenticationMappingRepositoryInterface;

class AuthenticationMappingRepository extends EntityRepository implements AuthenticationMappingRepositoryInterface
{
    use WhereFromFilterTrait;

    public function persistAndFlush(AuthenticationMappingEntity $entity)
    {
        $this->_em->persist($entity);
        $this->_em->flush($entity);
    }

    public function removeByZikulaId($uid)
    {
        $mapping = parent::findOneBy(['uid' => $uid]);
        if (isset($mapping)) {
            $this->_em->remove($mapping);
            $this->_em->flush();
        }
    }

    public function getByZikulaId($uid)
    {
        return parent::findOneBy(['uid' => $uid]);
    }

    public function setEmailVerification($uid, $value = true)
    {
        $mapping = parent::findOneBy(['uid' => $uid]);
        if (isset($mapping)) {
            $mapping->setVerifiedEmail($value);
            $this->_em->flush($mapping);
        }
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
     * @return \Doctrine\ORM\Tools\Pagination\Paginator|MappingEntity[]
     */
    public function query(array $filter = [], array $sort = [], $limit = 0, $offset = 0, $exprType = 'and')
    {
        $qb = $this->createQueryBuilder('m')
            ->select('m');
        if (!empty($filter)) {
            $where = $this->whereFromFilter($qb, $filter, $exprType, 'm');
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

    /**
     * Construct a QueryBuilder Expr\OrderBy object suitable for use in QueryBuilder->orderBy() from an array.
     * sort = [field => dir, field => dir, ...]
     * @param array $sort
     * @return OrderBy
     */
    private function orderByFromArray(array $sort)
    {
        $orderBy = new OrderBy();
        foreach ($sort as $field => $direction) {
            $orderBy->add("m.$field", $direction);
        }

        return $orderBy;
    }
}
