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

namespace Zikula\SearchModule\Entity\RepositoryInterface;

use Doctrine\Common\Collections\Selectable;
use Doctrine\Persistence\ObjectRepository;
use Zikula\Bundle\CoreBundle\Doctrine\PaginatorInterface;
use Zikula\SearchModule\Entity\SearchResultEntity;

interface SearchResultRepositoryInterface extends ObjectRepository, Selectable
{
    /**
     * Returns results for given arguments.
     */
    public function getResults(array $filters = [], array $sorting = [], int $page = 1, int $pageSize = 25): PaginatorInterface;

    /**
     * Deletes all results for the current session.
     */
    public function clearOldResults(string $sessionId = ''): void;

    /**
     * Persist a search result entity.
     */
    public function persist(SearchResultEntity $entity): void;

    /**
     * Save a search result entity.
     */
    public function flush(SearchResultEntity $entity = null): void;

    /**
     * Truncates the table.
     */
    public function truncateTable(): void;
}
