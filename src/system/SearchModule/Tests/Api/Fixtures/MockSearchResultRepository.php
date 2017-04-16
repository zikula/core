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
use Zikula\SearchModule\Entity\RepositoryInterface\SearchResultRepositoryInterface;
use Zikula\SearchModule\Entity\SearchResultEntity;

class MockSearchResultRepository implements SearchResultRepositoryInterface
{
    /**
     * @var SearchResultEntity[]
     */
    private $results = [];

    /**
     * {@inheritDoc}
     */
    public function countResults($sessionId = '')
    {
        $count = 0;
        foreach ($this->results as $k => $result) {
            if ($sessionId == $result->getSesid()) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * {@inheritDoc}
     */
    public function getResults($filters = [], $sorting = [], $limit = 0, $offset = 0)
    {
        return $this->results;
    }

    /**
     * {@inheritDoc}
     */
    public function clearOldResults($sessionId = '')
    {
        foreach ($this->results as $k => $result) {
            if ($sessionId == $result->getSesid()) {
                unset($this->results[$k]);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function persist(SearchResultEntity $entity)
    {
        $this->results[] = $entity;
    }

    /**
     * {@inheritDoc}
     */
    public function flush(SearchResultEntity $entity = null)
    {
        // nothing
    }

    /**
     * {@inheritDoc}
     */
    public function find($id)
    {
        // TODO: Implement find() method.
    }

    /**
     * {@inheritDoc}
     */
    public function findAll()
    {
        // TODO: Implement findAll() method.
    }

    /**
     * {@inheritDoc}
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        // TODO: Implement findBy() method.
    }

    /**
     * {@inheritDoc}
     */
    public function findOneBy(array $criteria)
    {
        // TODO: Implement findOneBy() method.
    }

    /**
     * {@inheritDoc}
     */
    public function getClassName()
    {
        // TODO: Implement getClassName() method.
    }

    /**
     * {@inheritDoc}
     */
    public function matching(Criteria $criteria)
    {
        // TODO: Implement matching() method.
    }

}