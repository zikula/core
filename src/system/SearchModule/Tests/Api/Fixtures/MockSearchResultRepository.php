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

namespace Zikula\SearchModule\Tests\Api\Fixtures;

use Doctrine\Common\Collections\Criteria;
use Zikula\Bundle\CoreBundle\Doctrine\PaginatorInterface;
use Zikula\SearchModule\Entity\RepositoryInterface\SearchResultRepositoryInterface;
use Zikula\SearchModule\Entity\SearchResultEntity;

class MockSearchResultRepository implements SearchResultRepositoryInterface
{
    /**
     * @var SearchResultEntity[]
     */
    private $results = [];

    public function getResults(array $filters = [], array $sorting = [], int $page = 1, int $pageSize = 25): PaginatorInterface
    {
        return new MockPaginator($this->results);
    }

    public function clearOldResults(string $sessionId = ''): void
    {
        foreach ($this->results as $k => $result) {
            if ($sessionId === $result->getSesid()) {
                unset($this->results[$k]);
            }
        }
    }

    public function persist(SearchResultEntity $entity): void
    {
        $this->results[] = $entity;
    }

    public function flush(SearchResultEntity $entity = null): void
    {
        // nothing
    }

    public function truncateTable(): void
    {
        // nothing
    }

    public function find($id)
    {
        // TODO: Implement find() method.
    }

    public function findAll()
    {
        // TODO: Implement findAll() method.
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        // TODO: Implement findBy() method.
    }

    public function findOneBy(array $criteria)
    {
        // TODO: Implement findOneBy() method.
    }

    public function getClassName()
    {
        // TODO: Implement getClassName() method.
    }

    public function matching(Criteria $criteria)
    {
        // TODO: Implement matching() method.
    }
}
