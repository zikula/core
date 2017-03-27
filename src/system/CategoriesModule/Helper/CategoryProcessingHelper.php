<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Helper;

use Doctrine\ORM\EntityManager;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\CategoriesModule\Entity\CategoryEntity;
use Zikula\ExtensionsModule\Api\CapabilityApi;

/**
 * Category processing helper functions for the categories module.
 */
class CategoryProcessingHelper
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    /**
     * @var CapabilityApi
     */
    private $capabilityApi;

    /**
     * CategoryProcessingHelper constructor.
     *
     * @param EntityManager $entityManager EntityManager service instance
     * @param ZikulaHttpKernelInterface $kernel KernelInterface service instance
     */
    public function __construct(
        EntityManager $entityManager,
        ZikulaHttpKernelInterface $kernel,
        CapabilityApi $capabilityApi
    ) {
        $this->entityManager = $entityManager;
        $this->kernel = $kernel;
        $this->capabilityApi = $capabilityApi;
    }

    /**
     * Checks whether a category may be deleted or moved.
     * For this all registries are checked to see if the given category is contained in the corresponding subtree.
     * If yes, the mapping table of the corresponding module is checked to see if it contains the given category.
     *
     * @param CategoryEntity $category The category to process
     *
     * @return boolean true if category may be deleted or moved, false otherwise
     */
    public function mayCategoryBeDeletedOrMoved(CategoryEntity $category)
    {
        // check legacy table first (as this is quickly done)
        // @deprecated remove just these few lines at Core-2.0
        $legacyMappings = $this->entityManager->getRepository('ZikulaCategoriesModule:CategoriesMapobj')
            ->findBy(['categoryId' => $category->getId()]);
        if (count($legacyMappings) > 0) {
            return false;
        }

        // fetch registries
        $registries = $this->entityManager->getRepository('ZikulaCategoriesModule:CategoryRegistryEntity')
            ->findAll();

        // iterate over all registries
        foreach ($registries as $registry) {
            // check if the registry subtree contains our category - iPath is constructed on demand
            $rPath = $registry->getCategory()->getIPath() . '/';
            $cPath = $category->getIPath();
            if (strpos($cPath, $rPath) !== 0) {
                continue;
            }

            // get information about responsible module
            if (!$this->kernel->isBundle($registry->getModname())) {
                continue;
            }

            $capabilities = $this->capabilityApi->getCapabilitiesOf($registry->getModname());
            foreach ($capabilities[CapabilityApi::CATEGORIZABLE] as $entityClass) {
                if (!is_subclass_of($entityClass, 'Zikula\\CategoriesModule\\Entity\\AbstractCategoryAssignment')) {
                    continue;
                }
                // check if this mapping table contains a reference to the given category
                $mappings = $this->entityManager->getRepository($entityClass)
                    ->findBy(['category' => $category]);
                if (count($mappings) > 0) {
                    // existing reference found
                    return false;
                }
            }
        }

        return true;
    }
}
