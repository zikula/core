<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\Doctrine;

/**
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 * Most of this file is copied from
 *     https://github.com/javiereguiluz/symfony-demo/blob/master/src/Pagination/Paginator.php
 *
 * usage:
 *     in Repository class:
 *         return (new Paginator($qb, $pageSize))->paginate($pageNumber);
 *     in controller:
 *         $latestPosts = $repository->getLatestPosts($criteria, $pageSize);
 *         return $this->render('blog/index.'.$_format.'.twig', [
 *             'paginator' => $latestPosts,
 *         ]);
 *     results in template {% for post in paginator.results %}
 *     include template: {{ include(paginator.template) }}
 */
interface PaginatorInterface
{
    public function paginate(int $page = 1): self;

    public function getCurrentPage(): int;

    public function getLastPage(): int;

    public function getPageSize(): int;

    public function hasPreviousPage(): bool;

    public function getPreviousPage(): int;

    public function hasNextPage(): bool;

    public function getNextPage(): int;

    public function hasToPaginate(): bool;

    public function getNumResults(): int;

    public function getResults(): \Traversable;

    public function setRoute(string $route): self;

    public function getRoute(): string;

    public function setRouteParameters(array $parameters): self;

    public function setRouteParameter(string $name, string $value): void;

    public function getRouteParameters(): array;

    public function setTemplate(string $templateName): void;

    public function getTemplate(): string;
}
