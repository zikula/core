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

interface AdminCategoryRepositoryInterface extends ObjectRepository, Selectable
{
    public function countCategories();

    public function getModuleCategory($moduleId);

    public function getIndexedCollection($indexBy);

    public function getPagedCategories($orderBy = [], $offset = 0, $limit = 0);
}
