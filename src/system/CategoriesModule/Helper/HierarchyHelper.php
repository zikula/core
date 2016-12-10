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
use Zikula\Common\Translator\TranslatorInterface;

/**
 * Category hierarchy helper functions for the categories module.
 */
class HierarchyHelper
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var CategoryApi
     */
    private $categoryApi;

    /**
     * HierarchyHelper constructor.
     *
     * @param TranslatorInterface $translator    TranslatorInterface service instance
     * @param EntityManager       $entityManager EntityManager service instance
     * @param CategoryApi         $categoryApi   CategoryApi service instance
     */
    public function __construct(TranslatorInterface $translator, EntityManager $entityManager, CategoryApi $categoryApi)
    {
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->categoryApi = $categoryApi;
    }

    /**
     * Check whether a category is a direct subcategory of $rootId.
     *
     * @param array $rootCat The root/parent category
     * @param array $cat     The category we wish to check for subcategory-ness
     *
     * @return boolean
     */
    public function isDirectSubCategory($rootCat, $cat)
    {
        return $cat['parent_id'] == $rootCat['id'];
    }

    /**
     * Check whether a category is a direct subcategory of $rootId.
     *
     * @param integer $rootId     The root/parent ID
     * @param integer $categoryId The categoryID we wish to check for subcategory-ness
     *
     * @return boolean
     */
    public function isDirectSubCategoryById($rootId, $categoryId)
    {
        if (!$categoryId) {
            return false;
        }

        $cat = $this->categoryApi->getCategoryById($categoryId);

        if (isset($cat['parent_id'])) {
            return $cat['parent_id'] == $rootId;
        }

        return false;
    }

    /**
     * Check whether a category is a subcategory of $rootCat.
     *
     * @param array $rootCat The root/parent category
     * @param array $cat     The category we wish to check for subcategory-ness
     *
     * @return boolean
     */
    public function isSubCategory($rootCat, $cat)
    {
        $rPath = $rootCat['ipath'] . '/';
        $cPath = $cat['ipath'];

        return strpos($cPath, $rPath) === 0;
    }

    /**
     * Check whether a category is a subcategory of $rootId.
     *
     * @param integer $rootId     The ID of the root category we wish to check from
     * @param integer $categoryId The category-id we wish to check for subcategory-ness
     *
     * @return boolean
     */
    public function isSubCategoryById($rootId, $categoryId)
    {
        if (!$rootId || !$categoryId) {
            return false;
        }

        $rootCat = $this->categoryApi->getCategoryById($rootId);
        $cat = $this->categoryApi->getCategoryById($categoryId);

        if (!$rootCat || !$cat) {
            return false;
        }

        return $this->isSubCategory($rootCat, $cat);
    }

    /**
     * Check whether a category has subcategories (optional checks for leafe ).
     *
     * @param integer $cid       The parent category
     * @param boolean $countOnly Whether or not to explicitly check for leaf nodes in the subcategories
     * @param boolean $all       Whether or not to return all (or only active) subcategories
     *
     * @return boolean
     */
    public function hasDirectSubcategories($cid, $countOnly = false, $all = true)
    {
        if (!$cid) {
            return false;
        }

        $cats = $this->categoryApi->getCategoriesByParentId($cid, '', false, $all);

        if ($countOnly) {
            return (bool)count($cats);
        }

        foreach ($cats as $cat) {
            if ($cat['is_leaf']) {
                return true;
            }
        }

        return false;
    }
}
