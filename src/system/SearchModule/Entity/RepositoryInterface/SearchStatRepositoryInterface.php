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
use Doctrine\Common\Persistence\ObjectRepository;
use Zikula\SearchModule\Entity\SearchStatEntity;

interface SearchStatRepositoryInterface extends ObjectRepository, Selectable
{
    /**
     * Returns amount of previous search queries.
     */
    public function countStats(): int;

    /**
     * Returns stats for given arguments.
     */
    public function getStats(array $filters = [], array $sorting = [], int $limit = 0, int $offset = 0): array;

    /**
     * Persist and flush a search stat entity.
     */
    public function persistAndFlush(SearchStatEntity $entity): void;
}
