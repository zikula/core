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
use Zikula\CategoriesModule\Entity\AbstractCategoryAssignment;
use Zikula\CategoriesModule\Entity\CategoryEntity;
use Zikula\CategoriesModule\Entity\RepositoryInterface\CategoryRegistryRepositoryInterface;
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
     * @var CategoryRegistryRepositoryInterface
     */
    private $categoryRegistryRepository;

    /**
     * @var CapabilityApiInterface
     */
    private $capabilityApi;

    public function __construct(
        EntityManagerInterface $entityManager,
        ZikulaHttpKernelInterface $kernel,
        CategoryRegistryRepositoryInterface $categoryRegistryRepository,
        CapabilityApiInterface $capabilityApi
    ) {
        $this->entityManager = $entityManager;
        $this->kernel = $kernel;
        $this->categoryRegistryRepository = $categoryRegistryRepository;
        $this->capabilityApi = $capabilityApi;
    }

    /**
     * Checks whether a category may be deleted or moved.
     * For this all registries are checked to see if the given category is contained in the corresponding subtree.
     * If yes, the mapping table of the corresponding module is checked to see if it contains the given category.
     */
    public function mayCategoryBeDeletedOrMoved(CategoryEntity $category): bool
    {
        // collect parents
        $isOnTop = false;
        $parentIds = [$category->getId()];
        $directParent = $category;
        while (false === $isOnTop) {
            $directParent = $category->getParent();
            if (null === $directParent) {
                $isOnTop = true;
                break;
            }
            $parentId = $directParent->getId();
            if ($parentId === end($parentIds)) {
                $isOnTop = true;
                break;
            }
            $parentIds[] = $directParent->getId();
        }

        // fetch registries
        $registries = $this->categoryRegistryRepository->findAll();

        // iterate over all registries
        foreach ($registries as $registry) {
            // check if the registry subtree contains our category
            if (!in_array($registry->getCategory()->getId(), $parentIds, true)) {
                continue;
            }

            // get information about responsible module
            if (!$this->kernel->isBundle($registry->getModname())) {
                continue;
            }

            $capabilities = $this->capabilityApi->getCapabilitiesOf($registry->getModname());
            foreach ($capabilities[CapabilityApi::CATEGORIZABLE] as $entityClass) {
                if (!is_subclass_of($entityClass, AbstractCategoryAssignment::class)) {
                    continue;
                }
                // check if this mapping table contains a reference to the given category
                // limit query to one result to avoid wasting performance
                $mappings = $this->entityManager->getRepository($entityClass)
                    ->findBy(['category' => $category], [], 1);
                if (count($mappings) > 0) {
                    // existing reference found
                    return false;
                }
            }
        }

        return true;
    }
}
