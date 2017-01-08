<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\AdminModule\Entity\RepositoryInterface;

use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\Persistence\ObjectRepository;
use Zikula\AdminModule\Entity\AdminCategoryEntity;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;

interface AdminModuleRepositoryInterface extends ObjectRepository, Selectable
{
    public function persistAndFlush($entity);

    public function countModulesByCategory($cid);

    /**
     * @param ExtensionEntity $moduleEntity
     * @param AdminCategoryEntity $adminCategoryEntity
     */
    public function setModuleCategory(ExtensionEntity $moduleEntity, AdminCategoryEntity $adminCategoryEntity);

    /**
     * @param int $oldCategory
     * @param int $newCategory
     */
    public function changeCategory($oldCategory, $newCategory);
}
