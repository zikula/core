<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SearchModule\Tests\Api\Fixtures;

use Doctrine\Common\Collections\Criteria;
use Zikula\SearchModule\Entity\RepositoryInterface\SearchStatRepositoryInterface;
use Zikula\SearchModule\Entity\SearchStatEntity;

class MockSearchStatRepository implements SearchStatRepositoryInterface
{
    private $results = [];

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        // TODO: Implement find() method.
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        // TODO: Implement findAll() method.
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        // TODO: Implement findBy() method.
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria)
    {
        return isset($this->results[$criteria['search']]) ? $this->results[$criteria['search']] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassName()
    {
        // TODO: Implement getClassName() method.
    }

    /**
     * {@inheritdoc}
     */
    public function countStats()
    {
        return count($this->results);
    }

    /**
     * {@inheritdoc}
     */
    public function getStats($filters = [], $sorting = [], $limit = 0, $offset = 0)
    {
        return $this->results;
    }

    /**
     * {@inheritdoc}
     */
    public function persistAndFlush(SearchStatEntity $entity)
    {
        $this->results[$entity->getSearch()] = $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function matching(Criteria $criteria)
    {
        // TODO: Implement matching() method.
    }
}
