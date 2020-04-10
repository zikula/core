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

namespace Zikula\AdminModule\Entity\RepositoryInterface;

use Doctrine\Common\Collections\Selectable;
use Doctrine\Persistence\ObjectRepository;
use Zikula\AdminModule\Entity\AdminCategoryEntity;
use Zikula\AdminModule\Entity\AdminModuleEntity;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;

interface AdminModuleRepositoryInterface extends ObjectRepository, Selectable
{
    public function persistAndFlush(AdminModuleEntity $entity): void;

    public function countModulesByCategory(int $cid): int;

    public function setModuleCategory(ExtensionEntity $moduleEntity, AdminCategoryEntity $adminCategoryEntity): void;

    public function changeCategory(int $oldCategory, int $newCategory): void;
}
