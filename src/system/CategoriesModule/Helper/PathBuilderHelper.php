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
use Zikula\CategoriesModule\Api\CategoryApi;
use Zikula\CategoriesModule\Entity\CategoryEntity;

/**
 * Path building helper functions for the categories module.
 */
class PathBuilderHelper
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var CategoryApi
     */
    private $categoryApi;

    /**
     * @var RelativeCategoryPathBuilderHelper
     */
    private $relativeHelper;

    /**
     * PathBuilderHelper constructor.
     *
     * @param EntityManager                     $entityManager  EntityManager service instance
     * @param CategoryApi                       $categoryApi    CategoryApi service instance
     * @param RelativeCategoryPathBuilderHelper $relativeHelper RelativeCategoryPathBuilderHelper service instance
     */
    public function __construct(EntityManager $entityManager, CategoryApi $categoryApi, RelativeCategoryPathBuilderHelper $relativeHelper)
    {
        $this->entityManager = $entityManager;
        $this->categoryApi = $categoryApi;
        $this->relativeHelper = $relativeHelper;
    }

    /**
     * Builds relative paths.
     *
     * Given an array of categories (with the Property-Names being
     * the keys of the array) and it corresponding Parent categories (indexed
     * with the Property-Names too), return an (identically indexed) array
     * of category-paths based on the given field (name or id make sense).
     *
     * @param array   $rootCatIDs  The root/parent categories ID
     * @param array   &$cats       The associative categories object array
     * @param boolean $includeRoot If true, the root portion of the path is preserved
     *
     * @return The resulting folder path array (which is also altered in place)
     */
    public function buildRelativePaths($rootCatIDs, &$cats, $includeRoot = false)
    {
        if (!$rootCatIDs) {
            return false;
        }

        foreach ($cats as $prop => $catID) {
            if (!isset($rootCatIDs[$prop]) || !$rootCatIDs[$prop]) {
                continue;
            }
            $rootCat = $this->categoryApi->getCategoryById($rootCatIDs[$prop]);
            $this->relativeHelper->buildRelativePathsForCategory($rootCat, $cats[$prop], $includeRoot);
        }

        return;
    }

    /**
     * Builds paths.
     *
     * Given an array of categories (with the category-IDs being
     * the keys of the array), return an (idenically indexed) array
     * of category-paths based on the given field (name or id make sense).
     *
     * @param array  $cats  The associative categories object array
     * @param string $field Which field to use the building the path (optional) (default='name')
     *
     * @return The resulting folder path array
     */
    public function buildPaths($cats, $field = 'name')
    {
        if (!$cats) {
            return false;
        }

        $paths = [];

        foreach ($cats as $k => $v) {
            $path = $v[$field];
            $pid = (null !== $v['parent']) ? $v['parent']->getId() : null;

            while ($pid > 0) {
                $pcat = $cats[$pid];
                $path = $pcat[$field] . '/' . $path;
                $pid = (null !== $pcat['parent']) ? $pcat['parent']->getId() : null;
            }

            $paths[$k] = '/' . $path;
        }

        return $paths;
    }

    /**
     * Rebuild the path field for all categories in the database.
     *
     * Note that field and sourceField go in pairs (that is, if you want sensical results)!.
     *
     * @param string  $pathField   The field which we wish to populate (optional) (default='path')
     * @param string  $sourceField The field we use to build the path with (optional) (default='name')
     * @param integer $leafId      The leaf-category category-id (ie: we'll rebuild the path of this category and all it's parents) (optional) (default=0)
     *
     * @return void
     */
    public function rebuildPaths($pathField = 'path', $sourceField = 'name', $leafId = 0)
    {
        if ($leafId > 0) {
            $cats = $this->categoryApi->getParentCategories($leafId, 'id');
        } else {
            $cats = $this->categoryApi->getCategories('', '', 'id');
        }

        $paths = $this->buildPaths($cats, $sourceField);

        if (!$cats || !$paths) {
            return;
        }

        $categoryRepository = $this->entityManager->getRepository('ZikulaCategoriesModule:CategoryEntity');

        foreach ($cats as $k => $v) {
            if (isset($v[$pathField]) && isset($paths[$k]) && ($v[$pathField] != $paths[$k])) {
                $categoryRepository->updatePath($k, $pathField, $paths[$k]);
            }
        }
    }
}
