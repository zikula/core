<?php
/**
 * Created by PhpStorm.
 * User: craig
 * Date: 4/16/17
 * Time: 4:45 PM
 */

namespace Zikula\SearchModule\Tests\Api\Fixtures;

use Doctrine\Common\Collections\Criteria;
use Zikula\SearchModule\Entity\RepositoryInterface\SearchStatRepositoryInterface;
use Zikula\SearchModule\Entity\SearchStatEntity;

class MockSearchStatRepository implements SearchStatRepositoryInterface
{
    private $results = [];

    /**
     * @inheritDoc
     */
    public function find($id)
    {
        // TODO: Implement find() method.
    }

    /**
     * @inheritDoc
     */
    public function findAll()
    {
        // TODO: Implement findAll() method.
    }

    /**
     * @inheritDoc
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        // TODO: Implement findBy() method.
    }

    /**
     * @inheritDoc
     */
    public function findOneBy(array $criteria)
    {
        return isset($this->results[$criteria['search']]) ? $this->results[$criteria['search']] : null;
    }

    /**
     * @inheritDoc
     */
    public function getClassName()
    {
        // TODO: Implement getClassName() method.
    }

    /**
     * @inheritDoc
     */
    public function countStats()
    {
        return count($this->results);
    }

    /**
     * @inheritDoc
     */
    public function getStats($filters = [], $sorting = [], $limit = 0, $offset = 0)
    {
        return $this->results;
    }

    /**
     * @inheritDoc
     */
    public function persistAndFlush(SearchStatEntity $entity)
    {
        $this->results[$entity->getSearch()] = $entity;
    }

    /**
     * @inheritDoc
     */
    public function matching(Criteria $criteria)
    {
        // TODO: Implement matching() method.
    }

}