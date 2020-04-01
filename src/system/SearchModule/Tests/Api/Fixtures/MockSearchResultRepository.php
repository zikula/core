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
    }

    public function truncateTable(): void
    {
    }

    public function find($id)
    {
    }

    public function findAll()
    {
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
    }

    public function findOneBy(array $criteria)
    {
    }

    public function getClassName()
    {
    }

    public function matching(Criteria $criteria)
    {
    }
}
