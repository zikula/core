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

namespace Zikula\ExtensionsModule\Entity\RepositoryInterface;

use Doctrine\Common\Collections\Selectable;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ObjectRepository;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;

interface ExtensionRepositoryInterface extends ObjectRepository, Selectable
{
    /**
     * @return ExtensionEntity
     */
    public function get(string $name);

    public function getPagedCollectionBy(
        array $criteria,
        array $orderBy = null,
        int $limit = 0,
        int $offset = 1
    ): Paginator;

    public function getIndexedArrayCollection(string $indexBy): array;

    public function updateName(string $oldName, string $newName): void;

    public function persistAndFlush(ExtensionEntity $entity): void;

    public function removeAndFlush(ExtensionEntity $entity): void;
}
