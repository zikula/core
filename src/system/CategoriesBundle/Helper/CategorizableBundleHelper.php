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
use Zikula\Bundle\CoreBundle\Composer\MetaData;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\CategoriesBundle\Entity\AbstractCategoryAssignment;
use Zikula\CategoriesBundle\Entity\CategoryEntity;

class CategorizableBundleHelper
{
    private const CAPABILITY_NAME = 'categorizable';

    public function __construct(
        private readonly ZikulaHttpKernelInterface $kernel,
        private readonly EntityManagerInterface $entityManager
    ) {
    }


    public function getCategorizableBundleNames(): array
    {
        $result = [];
        $extensions = $this->kernel->getModules();
        foreach ($extensions as $extension) {
            if (isset($extension->getMetaData()->getCapabilities()[self::CAPABILITY_NAME])) {
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
        if (!$this->kernel->isBundle($bundleName)) {
            return $data;
        }

        $bundle = $this->kernel->getBundle($bundleName);
        $entityClasses = $this->getCategorizableEntityClasses($bundle->getMetaData());
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

    public function isCategoryUsedBy(string $bundleName, CategoryEntity $category): bool
    {
        // get information about responsible module
        if (!$this->kernel->isBundle($bundleName)) {
            return false;
        }

        $bundle = $this->kernel->getBundle($bundleName);
        $entityClasses = $this->getCategorizableEntityClasses($bundle->getMetaData());
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

    private function getCategorizableEntityClasses(MetaData $metaData): array
    {
        return $metaData->getCapabilities()[self::CAPABILITY_NAME] ?? [];
    }
}
