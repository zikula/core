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

namespace Zikula\CategoriesModule\Helper;

use Doctrine\ORM\EntityManagerInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\CategoriesModule\Entity\CategoryEntity;
use Zikula\ExtensionsModule\Api\ApiInterface\CapabilityApiInterface;
use Zikula\ExtensionsModule\Api\CapabilityApi;

/**
 * Category processing helper functions for the categories module.
 */
class CategoryProcessingHelper
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    /**
     * @var CapabilityApiInterface
     */
    private $capabilityApi;

    /**
     * CategoryProcessingHelper constructor.
     *
     * @param EntityManagerInterface $entityManager EntityManager service instance
     * @param ZikulaHttpKernelInterface $kernel KernelInterface service instance
     * @param CapabilityApiInterface $capabilityApi
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ZikulaHttpKernelInterface $kernel,
        CapabilityApiInterface $capabilityApi
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
        // TODO #3920
        return true;

        // collect parents
        $isOnTop = false;
        $parentIds = [$category->getId()];
        $directParent = $category;
        while (false === $isOnTop) {
            $directParent = $category->getParent();
            if (null === $directParent) {
                $isOnTop = true;
            } else {
                $parentIds[] = $directParent->getId();
            }
        }

        // fetch registries
        $registries = $this->entityManager->getRepository('ZikulaCategoriesModule:CategoryRegistryEntity')
            ->findAll();

        // iterate over all registries
        foreach ($registries as $registry) {
            // check if the registry subtree contains our category
            if (!in_array($registry->getCategory()->getId(), $parentIds)) {
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
