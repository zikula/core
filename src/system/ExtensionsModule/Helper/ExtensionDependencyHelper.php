<?php
/**
 * Copyright Zikula Foundation 2016 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\ExtensionsModule\Helper;

use Zikula\ExtensionsModule\Api\ExtensionApi;
use Zikula\ExtensionsModule\Entity\ExtensionDependencyEntity;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\Entity\Repository\ExtensionDependencyRepository;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;

class ExtensionDependencyHelper
{
    /**
     * @var ExtensionDependencyRepository
     */
    private $extensionDependencyRepo;
    /**
     * @var ExtensionRepositoryInterface
     */
    private $extensionEntityRepo;

    /**
     * ExtensionDependencyHelper constructor.
     * @param $extensionDependencyRepo
     * @param $extensionEntityRepo
     */
    public function __construct(ExtensionDependencyRepository $extensionDependencyRepo, ExtensionRepositoryInterface $extensionEntityRepo)
    {
        $this->extensionDependencyRepo = $extensionDependencyRepo;
        $this->extensionEntityRepo = $extensionEntityRepo;
    }

    /**
     * Get an array of ExtensionEntities that are dependent on the $extension.
     * @param ExtensionEntity $extension
     * @return ExtensionEntity[]
     */
    public function getDependentExtensions(ExtensionEntity $extension)
    {
        $requiredDependents = [];
        /** @var ExtensionDependencyEntity[] $requiredDependencies */
        $requiredDependencies = $this->extensionDependencyRepo->findBy([
            'id' => $extension->getId(),
            'status' => \ModUtil::DEPENDENCY_REQUIRED
        ]);
        foreach ($requiredDependencies as $dependent) {
            $foundExtension = $this->extensionEntityRepo->findOneBy([
                'name' => $dependent->getModname(),
                'state' => ExtensionApi::STATE_ACTIVE
            ]);
            if (!is_null($foundExtension)) {
                $requiredDependents[] = $foundExtension;
            }
        }

        return $requiredDependents;
    }
}
