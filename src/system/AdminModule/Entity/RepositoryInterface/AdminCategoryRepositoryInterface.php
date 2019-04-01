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

namespace Zikula\AdminModule\Entity\RepositoryInterface;

use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Zikula\AdminModule\Entity\AdminCategoryEntity;

interface AdminCategoryRepositoryInterface extends ObjectRepository, Selectable
{
    public function countCategories(): int;

    public function getModuleCategory(int $moduleId): ?AdminCategoryEntity;

    public function getIndexedCollection(string $indexBy);

    public function getPagedCategories(array $orderBy = [], int $offset = 0, int $limit = 0): Paginator;
}
