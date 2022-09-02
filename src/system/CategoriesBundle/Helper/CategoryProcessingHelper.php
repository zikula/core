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

namespace Zikula\CategoriesBundle\Helper;

use Zikula\CategoriesBundle\Entity\Category;
use Zikula\CategoriesBundle\Repository\CategoryRegistryRepositoryInterface;

class CategoryProcessingHelper
{
    public function __construct(
        private readonly CategoryRegistryRepositoryInterface $categoryRegistryRepository,
        private readonly CategorizableBundleHelper $categorizableBundleHelper
    ) {
    }

    /**
     * Checks whether a category may be deleted or moved.
     * For this all registries are checked to see if the given category is contained in the corresponding subtree.
     * If yes, the mapping table of the corresponding module is checked to see if it contains the given category.
     */
    public function mayCategoryBeDeletedOrMoved(Category $category): bool
    {
        // collect parents
        $isOnTop = false;
        $parentIds = [$category->getId()];
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

            if ($this->categorizableBundleHelper->isCategoryUsedBy($registry->getBundleName(), $category)) {
                return false;
            }
        }

        return true;
    }
}
