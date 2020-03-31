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
use Zikula\Bundle\CoreBundle\Doctrine\Paginator;
use Zikula\SearchModule\Entity\RepositoryInterface\SearchStatRepositoryInterface;
use Zikula\SearchModule\Entity\SearchStatEntity;

class MockSearchStatRepository implements SearchStatRepositoryInterface
{
    /**
     * @var SearchStatEntity[]
     */
    private $results = [];

    public function countStats(): int
    {
        return count($this->results);
    }

    public function getStats(array $filters = [], array $sorting = [], int $page = 1, int $pageSize = 25): Paginator
    {
        return $this->results;
    }

    public function persistAndFlush(SearchStatEntity $entity): void
    {
        $this->results[$entity->getSearch()] = $entity;
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
        return $this->results[$criteria['search']] ?? null;
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
