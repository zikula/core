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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function getResults($filters = [], $sorting = [], $limit = 0, $offset = 0)
    {
        return $this->results;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function persist(SearchResultEntity $entity)
    {
        $this->results[] = $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function flush(SearchResultEntity $entity = null)
    {
        // nothing
    }

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
        // TODO: Implement findOneBy() method.
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
    public function matching(Criteria $criteria)
    {
        // TODO: Implement matching() method.
    }
}
