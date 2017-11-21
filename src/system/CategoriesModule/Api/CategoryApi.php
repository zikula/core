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
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\SettingsModule\Api\ApiInterface\LocaleApiInterface;

/**
 * CategoryApi
 * @deprecated
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
     * @var PermissionApiInterface
     */
    private $permissionApi;

    /**
     * @var CategoryProcessingHelper
     */
    private $processingHelper;

    /**
     * @var LocaleApiInterface
     */
    private $localeApi;

    /**
     * CategoryApi constructor.
     *
     * @param TranslatorInterface $translator TranslatorInterface service instance
     * @param EntityManager $entityManager EntityManager service instance
     * @param RequestStack $requestStack RequestStack service instance
     * @param PermissionApiInterface $permissionApi PermissionApi service instance
     * @param CategoryProcessingHelper $processingHelper CategoryProcessingHelper service instance
     * @param LocaleApiInterface $localeApi
     */
    public function __construct(
        TranslatorInterface $translator,
        EntityManager $entityManager,
        RequestStack $requestStack,
        PermissionApiInterface $permissionApi,
        CategoryProcessingHelper $processingHelper,
        LocaleApiInterface $localeApi
    ) {
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
        $this->permissionApi = $permissionApi;
        $this->processingHelper = $processingHelper;
        $this->localeApi = $localeApi;
    }

    /**
     * Return a category object by ID
     * @deprecated
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
        @trigger_error('CategoriesApi is deprecated. Please use the CategoryRepository instead.', E_USER_DEPRECATED);

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
        $data['value'] = $value ? $value : '';

        $cat->merge($data);
        $this->entityManager->persist($cat);
        $this->entityManager->flush();

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
     * @deprecated
     *
     * @param integer $categoryId The category-ID to retrieve
     *
     * @return array resulting object or empty array if not found
     */
    public function getCategoryById($categoryId)
    {
        @trigger_error('CategoriesApi is deprecated. Please use the CategoryRepository instead.', E_USER_DEPRECATED);

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
        // add BC
        $cat['path'] = $category->getPath();
        $cat['ipath'] = $category->getIPath();

        // set name and description by languages if not set
        $languages = $this->localeApi->getSupportedLocales();
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
     * @deprecated
     *
     * @param string  $apath       The path to retrieve by (simple path or array of paths)
     * @param string  $pathField   The (path) field we search for (either path or ipath) (optional) (default='path')
     * @param string  $sort        The sort field (optional) (default='')
     * @param boolean $includeLeaf Whether or not to also return leaf nodes (optional) (default=true)
     * @param boolean $all         Whether or not to return all (or only active) categories (optional) (default=false)
     *
     * @return array|CategoryEntity resulting category object
     */
    public function getCategoryByPath($apath, $pathField = 'path', $sort = '', $includeLeaf = true, $all = false)
    {
        @trigger_error('CategoriesApi is deprecated. Please use the CategoryRepository instead.', E_USER_DEPRECATED);

        $repo = $this->entityManager->getRepository('ZikulaCategoriesModule:CategoryEntity');
        $fieldMap = ['path' => 'name', 'ipath' => 'id'];
        if (!is_array($apath)) {
            $apath = [$apath];
        }
        $values = [];
        foreach ($apath as $path) {
            $parts = explode('/', $path);

            if ('path' == $pathField) {
                $parts = array_values(array_filter($parts));

                if (!empty($parts)) {
                    $last = count($parts) - 1;

                    foreach ($parts as $part_key => $part_value) {
                        if (0 == $part_key) {
                            $parent = $repo->findOneBy(['name' => $parts[$part_key]])->getID();
                        } elseif ($part_key != $last) {
                            $parent = $repo->findOneBy(['name' => $parts[$part_key], 'parent' => $parent])->getID();
                        }
                    }
                }
            }

            $values[] = array_pop($parts);
        }
        if (count($values) > 1) {
            $method = 'findBy';
        } else {
            $method = 'findOneBy';
            $values = array_pop($values);
        }
        $criteria = [$fieldMap[$pathField] => $values];
        if ('path' == $pathField) {
            $criteria['parent'] = $parent;
        }
        if (!$includeLeaf) {
            $criteria['is_leaf'] = false;
        }
        if (!$all) {
            $criteria['status'] = 'A';
        }
        if (!empty($sort)) {
            $sort = [$sort => 'ASC'];
        } else {
            $sort = null;
        }
        $categories = $repo->$method($criteria, $sort);
        if (!$categories) {
            return $categories;
        }
        if ('ipath' == $pathField) {
            return $categories;
        }
        $result = [];
        if (!is_array($categories)) {
            $categories = [$categories];
        }
        foreach ($categories as $category) {
            $path = $category->getPath();
            if (in_array($path, $apath)) {
                $result[] = $category;
            }
        }

        return count($result) > 1 ? $result : array_pop($result);
    }

    /**
     * Return an array of categories objects according the specified where-clause and sort criteria.
     * @deprecated
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
        @trigger_error('CategoriesApi is deprecated. Please use the CategoryRepository instead.', E_USER_DEPRECATED);

        $categories = $this->entityManager->getRepository('ZikulaCategoriesModule:CategoryEntity')->freeSelect($where, $sort, $columnArray);

        $cats = [];
        $languages = $this->localeApi->getSupportedLocales();
        /** @var CategoryEntity[] $categories */
        foreach ($categories as $category) {
            $cat = $category->toArray();
            // add BC
            $cat['path'] = $category->getPath();
            $cat['ipath'] = $category->getIPath();

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

            $instance = $category->getId() . ':' . $category->getPath() . ':' . $category->getIPath();
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
     * @deprecated
     *
     * @param array $registry The category registry info for which categories should be retrieved
     *
     * @return array resulting folder object array
     */
    public function getCategoriesByRegistry($registry)
    {
        @trigger_error('CategoriesApi is deprecated. Please use the CategoryRegistryRepository instead.', E_USER_DEPRECATED);

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
        if (false !== $cats) {
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
     * @deprecated
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
        @trigger_error('CategoriesApi is deprecated. Please use the CategoryRepository instead.', E_USER_DEPRECATED);

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
            @trigger_error('CategoriesApi::getCategoriesByParentId cannot return relative paths any longer.', E_USER_DEPRECATED);
        }

        return $cats;
    }

    /**
     * Return all parent categories starting from id.
     * @deprecated
     *
     * @param integer        $id       The (leaf) folder id to retrieve
     * @param string|boolean $assocKey Whether or not to return an associative array (optional) (default='id')
     *
     * @return array resulting folder object array
     */
    public function getParentCategories($id, $assocKey = 'id')
    {
        @trigger_error('CategoriesApi is deprecated. Please use the CategoryRepository instead.', E_USER_DEPRECATED);

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
     * @deprecated
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
        @trigger_error('CategoriesApi is deprecated. Please use the CategoryRepository instead.', E_USER_DEPRECATED);
        if (!empty($exclPath) || !empty($assocKey) || !empty($attributes) || !empty($columnArray)) {
            @trigger_error('The arguments "exclPath", "assocKey", "attributes" and "columnArray" no longer affect the query.', E_USER_DEPRECATED);
        }

        return [$this->getCategoryByPath($apath, $pathField, $sort, $includeLeaf, $all)];
    }

    /**
     * Return an array of Subcategories for the specified folder
     * @deprecated
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
        @trigger_error('CategoriesApi is deprecated. Please use the CategoryRepository instead.', E_USER_DEPRECATED);

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
     * @deprecated
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
        @trigger_error('CategoriesApi is deprecated. Please use the CategoryRepository instead.', E_USER_DEPRECATED);

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
     * @deprecated
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
        @trigger_error('CategoriesApi is deprecated. Please use the CategoryRepository instead.', E_USER_DEPRECATED);

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
            @trigger_error('CategoriesApi::getSubCategoriesForCategory cannot return relative paths any longer.', E_USER_DEPRECATED);
        }

        if ($sortField) {
            @trigger_error('CategoriesApi::getSubCategoriesForCategory cannot sort fields any longer.', E_USER_DEPRECATED);
        }

        return $cats;
    }

    /**
     * Delete a category by it's ID
     * @deprecated
     *
     * @param integer $categoryId The categoryID to delete
     *
     * @return boolean|void
     */
    public function deleteCategoryById($categoryId)
    {
        @trigger_error('CategoriesApi is deprecated. Please use the CategoryRepository instead.', E_USER_DEPRECATED);

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
     * @deprecated
     *
     * @param string $apath      The path we wish to delete
     * @param string $pathField The (path) field we delete from (either path or ipath) (optional) (default='ipath')
     *
     * @return boolean|void
     */
    public function deleteCategoriesByPath($apath, $pathField = 'ipath')
    {
        @trigger_error('CategoriesApi is deprecated. Please use the CategoryRepository instead.', E_USER_DEPRECATED);

        if (!$apath) {
            return false;
        }

        $categories = $this->entityManager->getRepository('ZikulaCategoriesModule:CategoryEntity')->getIdsInPath($pathField, $apath);

        foreach ($categories as $category) {
            $this->deleteCategoryById($category['id']);
        }
    }
}
