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

use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Zikula\CategoriesBundle\Entity\AbstractCategoryAssignment;
use Zikula\CategoriesBundle\Entity\Category;
use Zikula\CoreBundle\Bundle\MetaData\MetaDataAwareBundleInterface;

class CategorizableBundleHelper
{
    public function __construct(
        private readonly KernelInterface $kernel,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function getCategorizableBundleNames(): array
    {
        $result = [];
        $bundles = $this->kernel->getBundles();
        foreach ($bundles as $bundle) {
            $entityClasses = $this->getCategorizableEntityClasses($bundle);
            if (!empty($entityClasses)) {
                $bundleName = $bundle->getName();
                $result[$bundleName] = $bundleName;
            }
        }

        return $result;
    }

    /**
     * Build an array suitable for a choice form type.
     */
    public function buildEntityChoiceListFor(string $bundleName): array
    {
        $data = [];
        try {
            $bundle = $this->kernel->getBundle($bundleName);
        } catch (InvalidArgumentException) {
            return $data;
        }

        $entityClasses = $this->getCategorizableEntityClasses($bundle);
        if (empty($entityClasses)) {
            return $data;
        }

        $keys = array_keys($entityClasses);
        $entityList = is_int($keys[0]) ? $entityClasses : $entityClasses[$keys[0]];
        foreach ($entityList as $fullyQualifiedEntityName) {
            $nameParts = explode('\\', $fullyQualifiedEntityName);
            $entityName = array_pop($nameParts);
            $data[$entityName] = $entityName;
        }

        return $data;
    }

    public function isCategoryUsedBy(string $bundleName, Category $category): bool
    {
        try {
            $bundle = $this->kernel->getBundle($bundleName);
        } catch (InvalidArgumentException) {
            return false;
        }

        $entityClasses = $this->getCategorizableEntityClasses($bundle);
        if (empty($entityClasses)) {
            return false;
        }

        foreach ($entityClasses as $entityClass) {
            if (!is_subclass_of($entityClass, AbstractCategoryAssignment::class)) {
                continue;
            }

            // check if this mapping table contains a reference to the given category
            // limit query to one result to avoid wasting performance
            $mappings = $this->entityManager->getRepository($entityClass)
                ->findBy(['category' => $category], [], 1);
            if (0 < count($mappings)) {
                // existing reference found
                return true;
            }
        }

        return false;
    }

    private function getCategorizableEntityClasses(BundleInterface $bundle): ?array
    {
        if (!($bundle instanceof MetaDataAwareBundleInterface)) {
            return null;
        }

        return $bundle->getMetaData()->getCategorizableEntityClasses();
    }
}
