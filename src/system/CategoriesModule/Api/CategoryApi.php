<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Api;

use DataUtil;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Zikula\CategoriesModule\Entity\CategoryEntity;
use Zikula\CategoriesModule\Helper\CategoryProcessingHelper;
use Zikula\CategoriesModule\Helper\CategorySortingHelper;
use Zikula\CategoriesModule\Helper\RelativeCategoryPathBuilderHelper;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\PermissionsModule\Api\PermissionApi;
use ZLanguage;

/**
 * CategoryApi
 */
class CategoryApi
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
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var PermissionApi
     */
    private $permissionApi;

    /**
     * @var CategoryProcessingHelper
     */
    private $processingHelper;

    /**
     * @var CategorySortingHelper
     */
    private $sortingHelper;

    /**
     * @var RelativeCategoryPathBuilderHelper
     */
    private $pathBuilder;

    /**
     * CategoryApi constructor.
     *
     * @param TranslatorInterface               $translator       TranslatorInterface service instance
     * @param EntityManager                     $entityManager    EntityManager service instance
     * @param RequestStack                      $requestStack     RequestStack service instance
     * @param PermissionApi                     $permissionApi    PermissionApi service instance
     * @param CategoryProcessingHelper          $processingHelper CategoryProcessingHelper service instance
     * @param CategorySortingHelper             $sortingHelper    CategorySortingHelper service instance
     * @param RelativeCategoryPathBuilderHelper $pathBuilder      RelativeCategoryPathBuilderHelper service instance
     */
    public function __construct(
        TranslatorInterface $translator,
        EntityManager $entityManager,
        RequestStack $requestStack,
        PermissionApi $permissionApi,
        CategoryProcessingHelper $processingHelper,
        CategorySortingHelper $sortingHelper,
        RelativeCategoryPathBuilderHelper $pathBuilder
    ) {
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
        $this->permissionApi = $permissionApi;
        $this->processingHelper = $processingHelper;
        $this->sortingHelper = $sortingHelper;
        $this->pathBuilder = $pathBuilder;
    }

    /**
     * Return a category object by ID
     *
     * @param string $rootPath    The path of the parent category
     * @param string $name        The name of the category
     * @param string $value       The value of the category (optional) (default=null)
     * @param string $displayname The displayname of the category (optional) (default=null, uses $name)
     * @param string $description The description of the category (optional) (default=null, uses $name)
     * @param string $attributes  The attributes array to bind to the category (optional) (default=null)
     *
     * @return array|boolean resulting folder object
     *
     * @throws \InvalidArgumentException Thrown if input arguments are not valid
     */
    public function createCategory($rootPath, $name, $value = null, $displayname = null, $description = null, $attributes = null)
    {
        if (!isset($rootPath) || !$rootPath) {
            throw new \InvalidArgumentException($this->translator->__f("Error! Received invalid parameter '%s'", ['%s' => 'rootPath']));
        }
        if (!isset($name) || !$name) {
            throw new \InvalidArgumentException($this->translator->__f("Error! Received invalid parameter '%s'", ['%s' => 'name']));
        }

        if (!$displayname) {
            $displayname = $name;
        }
        if (!$description) {
            $description = $name;
        }

        /** @var CategoryEntity $rootCat */
        $rootCat = $this->getCategoryByPath($rootPath);
        if (!$rootCat) {
            $this->requestStack->getCurrentRequest()->getSession()->getFlashBag()->add('error', $this->translator->__f("Error! Non-existing root category '%s' received", ['%s' => $rootPath]));

            return false;
        }

        $checkCat = $this->getCategoryByPath("$rootPath/$name");
        if ($checkCat) {
            return false;
        }

        $cat = new CategoryEntity();

        $data = [];
        $data['parent'] = $this->entityManager->getReference('ZikulaCategoriesModule:CategoryEntity', $rootCat['id']);
        $data['name'] = $name;
        $locale = $this->requestStack->getMasterRequest()->getLocale();
        $data['display_name'] = [$locale => $displayname];
        $data['display_desc'] = [$locale => $description];
        if ($value) {
            $data['value'] = $value;
        }

        $data['path'] = "$rootPath/$name";

        $cat->merge($data);
        $this->entityManager->persist($cat);
        $this->entityManager->flush();

        $cat['ipath'] = "$rootCat[ipath]/$cat[id]";
        if ($attributes && is_array($attributes)) {
            foreach ($attributes as $key => $value) {
                $cat->setAttribute($key, $value);
            }
        }

        $this->entityManager->flush();

        return $cat->getId();
    }

    /**
     * Return a category object by ID.
     *
     * @param integer $categoryId The category-ID to retrieve
     *
     * @return array resulting object or empty array if not found
     */
    public function getCategoryById($categoryId)
    {
        if (!$categoryId) {
            return [];
        }

        // get category
        $category = $this->entityManager->find('ZikulaCategoriesModule:CategoryEntity', $categoryId);
        if (!isset($category)) {
            return [];
        }

        // convert to array
        $cat = $category->toArray();

        // set name and description by languages if not set
        $languages = ZLanguage::getInstalledLanguages();
        foreach ($languages as $lang) {
            if (!isset($cat['display_name'][$lang])) {
                $cat['display_name'][$lang] = isset($cat['display_name']['en']) ? $cat['display_name']['en'] : '';
            }
            if (!isset($cat['display_desc'][$lang])) {
                $cat['display_desc'][$lang] = isset($cat['display_desc']['en']) ? $cat['display_desc']['en'] : '';
            }
        }

        // assign parent_id
        // this makes the rootcat's parent 0 as it's stored as null in the database
        $cat['parent_id'] = (null === $cat['parent']) ? null : $category['parent']->getId();

        // get attributes
        $cat['__ATTRIBUTES__'] = [];
        foreach ($cat['attributes'] as $attribute) {
            $cat['__ATTRIBUTES__'][$attribute['name']] = $attribute['value'];
        }

        return $cat;
    }

    /**
     * Return a category object by it's path
     *
     * @param string $apath     The path to retrieve by (simple path or array of paths)
     * @param string $pathField The (path) field we search for (either path or ipath) (optional) (default='path')
     *
     * @return array resulting category object
     */
    public function getCategoryByPath($apath, $pathField = 'path')
    {
        if (!is_array($apath)) {
            $where = "c.$pathField = '" . DataUtil::formatForStore($apath) . "'";
        } else {
            $where = [];
            foreach ($apath as $path) {
                $where[] = "c.$pathField = '" . DataUtil::formatForStore($path) . "'";
            }
            $where = implode(' OR ', $where);
        }
        $cats = $this->getCategories($where);

        if (isset($cats[0]) && is_array($cats[0])) {
            return $cats[0];
        }

        return $cats;
    }

    /**
     * Return an array of categories objects according the specified where-clause and sort criteria.
     *
     * @param string  $where       The where clause to use in the select (optional) (default='')
     * @param string  $sort        The order-by clause to use in the select (optional) (default='')
     * @param string  $assocKey    The field to use as the associated array key (optional) (default='')
     * @param array   $columnArray Array of columns to select (optional) (default=null)
     *
     * @return array resulting category object array
     */
    public function getCategories($where = '', $sort = '', $assocKey = '', $columnArray = null)
    {
        $categories = $this->entityManager->getRepository('ZikulaCategoriesModule:CategoryEntity')->freeSelect($where, $sort, $columnArray);

        $cats = [];
        $languages = ZLanguage::getInstalledLanguages();
        foreach ($categories as $category) {
            $cat = $category->toArray();

            // set name and description by languages if not set
            foreach ($languages as $lang) {
                if (!isset($cat['display_name'][$lang])) {
                    $cat['display_name'][$lang] = isset($cat['display_name']['en']) ? $cat['display_name']['en'] : '';
                }
                if (!isset($cat['display_desc'][$lang])) {
                    $cat['display_desc'][$lang] = isset($cat['display_desc']['en']) ? $cat['display_desc']['en'] : '';
                }
            }

            // this makes the rotocat's parent 0 as it's stored as null in the database
            $cat['parent_id'] = (null === $cat['parent']) ? null : $category['parent']->getId();

            $instance = $category['id'] . ':' . $category['path'] . ':' . $category['ipath'];
            $cat['accessible'] = $this->permissionApi->hasPermission('ZikulaCategoriesModule::Category', $instance, ACCESS_OVERVIEW);

            if (!empty($assocKey)) {
                $cats[$category[$assocKey]] = $cat;
            } else {
                $cats[] = $cat;
            }
        }

        return $cats;
    }

    /**
     * Return an array of categories by the registry info.
     *
     * @param array $registry The category registry info for which categories should be retrieved
     *
     * @return array resulting folder object array
     */
    public function getCategoriesByRegistry($registry)
    {
        if (!$registry || !is_array($registry)) {
            return false;
        }

        $where = [];
        foreach ($registry as $property => $catID) {
            $where[] = "c.id = '" . DataUtil::formatForStore($catID) . "'";
        }
        $where = implode(' OR ', $where);
        $cats = $this->getCategories($where, '', 'id');

        $result = [];
        if ($cats !== false) {
            foreach ($registry as $property => $catID) {
                if (isset($cats[$catID])) {
                    $result[$property] = $cats[$catID];
                }
            }
        }

        return $result;
    }

    /**
     * Return the direct subcategories of the specified category
     *
     * @param integer $id         The folder id to retrieve
     * @param string  $sort       The order-by clause (optional) (default='')
     * @param boolean $relative   Whether or not to also generate relative paths (optional) (default=false)
     * @param boolean $all        Whether or not to return all (or only active) categories (optional) (default=false)
     * @param string  $assocKey   The field to use as the associated array key (optional) (default='')
     * @param array   $attributes The associative array of attribute field names to filter by (optional) (default=null)
     *
     * @return array resulting folder object
     */
    public function getCategoriesByParentId($id, $sort = '', $relative = false, $all = false, $assocKey = '', $attributes = null)
    {
        if (!$id) {
            return false;
        }

        $id = (int)$id;
        $where = "c.parent ='" . DataUtil::formatForStore($id) . "'";

        if (!$all) {
            $where .= " AND c.status = 'A'";
        }

        $cats = $this->getCategories($where, $sort, $assocKey);

        if ($cats && $relative) {
            $category = $this->getCategoryById($id);
            $arraykeys = array_keys($cats);
            foreach ($arraykeys as $key) {
                $this->pathBuilder->buildRelativePathsForCategory($category, $cats[$key], isset($includeRoot) ? $includeRoot : false);
            }
        }

        return $cats;
    }

    /**
     * Return all parent categories starting from id.
     *
     * @param integer        $id       The (leaf) folder id to retrieve
     * @param string|boolean $assocKey Whether or not to return an associative array (optional) (default='id')
     *
     * @return array resulting folder object array
     */
    public function getParentCategories($id, $assocKey = 'id')
    {
        if (!$id) {
            return false;
        }

        $cats = [];
        $cat = $this->entityManager->find('ZikulaCategoriesModule:CategoryEntity', $id);
        if (!$cat) {
            return $cats;
        }

        do {
            $cat = $cat['parent'];
            $cats[$cat[$assocKey]] = $cat->toArray();
        } while (null !== $cat['parent']);

        return $cats;
    }

    /**
     * Return an array of category objects by path without the root category
     *
     * @param string  $apath       The path to retrieve categories by
     * @param string  $sort        The sort field (optional) (default='')
     * @param string  $pathField   The (path) field to use (path or ipath) (optional) (default='ipath')
     * @param boolean $includeLeaf Whether or not to also return leaf nodes (optional) (default=true)
     * @param boolean $all         Whether or not to return all (or only active) categories (optional) (default=false)
     * @param string  $exclPath    The path to exclude from the retrieved categories (optional) (default='')
     * @param string  $assocKey    The field to use to build an associative key (optional) (default='')
     * @param array   $attributes  The associative array of attribute field names to filter by (optional) (default=null)
     * @param array   $columnArray The list of columns to fetch (optional) (default=null)
     *
     * @return array resulting folder object array
     */
    public function getCategoriesByPath($apath, $sort = '', $pathField = 'ipath', $includeLeaf = true, $all = false, $exclPath = '', $assocKey = '', $attributes = null, $columnArray = null)
    {
        $where = "(c.$pathField = '" . DataUtil::formatForStore($apath) . "' OR c.$pathField LIKE '" . DataUtil::formatForStore($apath) . "/%')";

        if ($exclPath) {
            $where .= " AND c.$pathField NOT LIKE '" . DataUtil::formatForStore($exclPath) . "%'";
        }

        if (!$includeLeaf) {
            $where .= " AND c.is_leaf = 0";
        }

        if (!$all) {
            $where .= " AND c.status = 'A'";
        }

        if (!$sort) {
            $sort = "ORDER BY c.sort_value, c.path";
        } else {
            $sort = "ORDER BY c." . $sort;
        }

        $cats = $this->getCategories($where, $sort, $assocKey, $columnArray);

        return $cats;
    }

    /**
     * Return an array of Subcategories for the specified folder
     *
     * @param integer $categoryId  The root-category category-id
     * @param boolean $recurse     Whether or not to generate a recursive subcategory result set (optional) (default=true)
     * @param boolean $relative    Whether or not to generate relative path indexes (optional) (default=true)
     * @param boolean $includeRoot Whether or not to include the root folder in the result set (optional) (default=false)
     * @param boolean $includeLeaf Whether or not to also return leaf nodes (optional) (default=true)
     * @param boolean $all         Whether or not to include all (or only active) folders in the result set (optional) (default=false)
     * @param integer $excludeCid  CategoryID (root folder) to exclude from the result set (optional) (default='')
     * @param string  $assocKey    The field to use as the associated array key (optional) (default='')
     * @param array   $attributes  The associative array of attribute field names to filter by (optional) (default=null)
     * @param string  $sortField   The field to sort the resulting category array by (optional) (default='sort_value')
     * @param array   $columnArray The list of columns to fetch (optional) (default=null)
     *
     * @return array the resulting folder object array
     */
    public function getSubCategories($categoryId, $recurse = true, $relative = true, $includeRoot = false, $includeLeaf = true, $all = false, $excludeCid = '', $assocKey = '', $attributes = null, $sortField = 'sort_value', $columnArray = null)
    {
        if (!$categoryId) {
            return false;
        }

        $rootCat = $this->getCategoryById($categoryId);
        if (!$rootCat) {
            return false;
        }

        $exclCat = '';
        if ($excludeCid) {
            $exclCat = $this->getCategoryById($excludeCid);
        }

        $cats = $this->getSubCategoriesForCategory($rootCat, $recurse, $relative, $includeRoot, $includeLeaf, $all, $exclCat, $assocKey, $attributes, $sortField, $columnArray);

        return $cats;
    }

    /**
     * Return an array of Subcategories for the specified folder
     *
     * @param string  $apath       The path to get categories by
     * @param string  $pathField   The (path) field we match by (either path or ipath) (optional) (default='ipath')
     * @param boolean $recurse     Whether or not to generate a recursive subcategory result set (optional) (default=true)
     * @param boolean $relative    Whether or not to generate relative path indexes (optional) (default=true)
     * @param boolean $includeRoot Whether or not to include the root folder in the result set (optional) (default=false)
     * @param boolean $includeLeaf Whether or not to also return leaf nodes (optional) (default=true)
     * @param boolean $all         Whether or not to include all (or only active) folders in the result set (optional) (default=false)
     * @param integer $excludeCid  CategoryID (root folder) to exclude from the result set (optional) (default='')
     * @param string  $assocKey    The field to use as the associated array key (optional) (default='')
     * @param array   $attributes  The associative array of attribute field names to filter by (optional) (default=null)
     * @param string  $sortField   The field to sort the resulting category array by (optional) (default='sort_value')
     *
     * @return array resulting folder object array
     */
    public function getSubCategoriesByPath($apath, $pathField = 'ipath', $recurse = true, $relative = true, $includeRoot = false, $includeLeaf = true, $all = false, $excludeCid = '', $assocKey = '', $attributes = null, $sortField = 'sort_value')
    {
        if (!$apath) {
            return false;
        }

        $rootCat = $this->getCategoryByPath($apath, $pathField);
        if (!$rootCat) {
            return false;
        }

        $exclCat = '';
        if ($excludeCid) {
            $exclCat = $this->getCategoryById($excludeCid);
        }

        $cats = $this->getSubCategoriesForCategory($rootCat, $recurse, $relative, $includeRoot, $includeLeaf, $all, $exclCat, $assocKey, $attributes, $sortField);

        return $cats;
    }

    /**
     * Return an array of Subcategories by for the given category
     *
     * @param array   $category    The root category to retrieve
     * @param boolean $recurse     Whether or not to recurse (if false, only direct subfolders are retrieved) (optional) (default=true)
     * @param boolean $relative    Whether or not to also generate relative paths (optional) (default=true)
     * @param boolean $includeRoot Whether or not to include the root folder in the result set (optional) (default=false)
     * @param boolean $includeLeaf Whether or not to also return leaf nodes (optional) (default=true)
     * @param boolean $all         Whether or not to return all (or only active) categories (optional) (default=false)
     * @param string  $excludeCat  The root category of the hierarchy to exclude from the result set (optional) (default='')
     * @param string  $assocKey    The field to use as the associated array key (optional) (default='')
     * @param array   $attributes  The associative array of attribute field names to filter by (optional) (default=null)
     * @param string  $sortField   The field to sort the resulting category array by (optional) (default='sort_value')
     * @param array   $columnArray The list of columns to fetch (optional) (default=null)
     *
     * @return array resulting folder object array
     */
    public function getSubCategoriesForCategory($category, $recurse = true, $relative = true, $includeRoot = false, $includeLeaf = true, $all = false, $excludeCat = null, $assocKey = '', $attributes = null, $sortField = 'sort_value', $columnArray = null)
    {
        if (!$category) {
            return false;
        }

        $cats = [];
        $ipath = $category['ipath'];
        if ($recurse) {
            $ipathExcl = ($excludeCat ? $excludeCat['ipath'] : '');
            $cats = $this->getCategoriesByPath($ipath, '', 'ipath', $includeLeaf, $all, $ipathExcl, $assocKey, $attributes, $columnArray);
        } else {
            $cats = $this->getCategoriesByParentId($category['id'], '', $relative, $all, $assocKey, $attributes);
            array_unshift($cats, $category);
        }

        // since array_shift() resets numeric array indexes, we remove the leading element like this
        if (!$includeRoot) {
            foreach ($cats as $k => $v) {
                if (isset($v['ipath']) && $v['ipath'] == $ipath) {
                    unset($cats[$k]);
                }
            }
        }

        if ($cats && $relative) {
            $arraykeys = array_keys($cats);
            foreach ($arraykeys as $key) {
                $this->pathBuilder->buildRelativePathsForCategory($category, $cats[$key], $includeRoot);
            }
        }

        if ($sortField) {
            $cats = $this->sortingHelper->sortCategories($cats, $sortField, $assocKey);
        }

        return $cats;
    }

    /**
     * Delete a category by it's ID
     *
     * @param integer $categoryId The categoryID to delete
     *
     * @return boolean|void
     */
    public function deleteCategoryById($categoryId)
    {
        $category = $this->entityManager->find('ZikulaCategoriesModule:CategoryEntity', $categoryId);
        if (!isset($category)) {
            return;
        }

        if (!$this->processingHelper->mayCategoryBeDeletedOrMoved($category)) {
            $this->requestStack->getCurrentRequest()->getSession()->getFlashBag()->add('error', $this->translator->__f('Error! Category %s can not be deleted, because it is already used.', ['%s' => $category['name']]));

            return false;
        }

        $this->entityManager->remove($category);
        $this->entityManager->flush();
    }

    /**
     * Delete categories by path
     *
     * @param string $path      The path we wish to delete
     * @param string $pathField The (path) field we delete from (either path or ipath) (optional) (default='ipath')
     *
     * @return boolean|void
     */
    public function deleteCategoriesByPath($path, $pathField = 'ipath')
    {
        if (!$apath) {
            return false;
        }

        $categories = $this->entityManager->getRepository('ZikulaCategoriesModule:CategoryEntity')->getIdsInPath($pathField, $apath);

        foreach ($categories as $category) {
            $this->deleteCategoryById($category['id']);
        }
    }
}
