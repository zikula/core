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

use Doctrine\Common\Collections\ArrayCollection;
use Zikula\Bundle\CoreBundle\Doctrine\PaginatorInterface;

class MockPaginator implements PaginatorInterface
{
    /**
     * @var ArrayCollection
     */
    private $results;

    public function __construct(array $results = [])
    {
        $this->results = new ArrayCollection($results);
    }

    public function paginate(int $page = 1): PaginatorInterface
    {
        return $this;
    }

    public function getCurrentPage(): int
    {
        return 1;
    }

    public function getLastPage(): int
    {
        return 2;
    }

    public function getPageSize(): int
    {
        return 1000;
    }

    public function hasPreviousPage(): bool
    {
        return false;
    }

    public function getPreviousPage(): int
    {
        return 1;
    }

    public function hasNextPage(): bool
    {
        return true;
    }

    public function getNextPage(): int
    {
        return 2;
    }

    public function hasToPaginate(): bool
    {
        return true;
    }

    public function getNumResults(): int
    {
        return $this->results->count();
    }

    public function getResults(): \Traversable
    {
        return $this->results;
    }

    public function setRoute(string $route): PaginatorInterface
    {
        return $this;
    }

    public function getRoute(): string
    {
        return '';
    }

    public function setRouteParameters(array $parameters): PaginatorInterface
    {
        return $this;
    }

    public function setRouteParameter(string $name, string $value): void
    {
    }

    public function getRouteParameters(): array
    {
        return [];
    }

    public function setTemplate(string $templateName): void
    {
    }

    public function getTemplate(): string
    {
        return '';
    }
}
