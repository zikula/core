<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\AdminModule\Helper;

use Zikula\AdminModule\Entity\AdminModuleEntity;
use Zikula\AdminModule\Entity\Repository\AdminModuleRepository;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;

class AdminModuleHelper
{
    /**
     * @var AdminModuleRepository
     */
    private $adminModuleRepository;

    /**
     * AdminModuleHelper constructor.
     * @param AdminModuleRepository $adminModuleRepository
     */
    public function __construct(AdminModuleRepository $adminModuleRepository)
    {
        $this->adminModuleRepository = $adminModuleRepository;
    }

    public function setAdminModuleCategory(ExtensionEntity $module, $categoryId)
    {
        $adminModule = $this->adminModuleRepository->findOneBy(['mid' => $module->getId()]);
        if (!isset($adminModule)) {
            $adminModule = new AdminModuleEntity();
        }
        $adminModule->setMid($module->getId());
        $adminModule->setCid((int)$categoryId);
        $adminModule->setSortorder($this->adminModuleRepository->countModulesByCategory($categoryId));
        $this->adminModuleRepository->persistAndFlush($adminModule);
    }
}
