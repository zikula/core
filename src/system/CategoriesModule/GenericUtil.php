<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule;

use Symfony\Component\Finder\Finder;
use Zikula\CategoriesModule\Entity\CategoryEntity;

/**
 * Helper functions for the categories module.
 */
class GenericUtil
{
    /**
     * Validate the data for a category
     *
     * @param array $data The data for the category
     *
     * @return boolean true/false Whether the provided data is valid
     *
     * @throws \InvalidArgumentException Thrown if no category name is provided or
     *                                          if no parent is defined for the category
     * @throws \RuntimeException Thrown if a category of the same anme already exists under the parent
     */
    public static function validateCategoryData($data)
    {
        if (empty($data['name'])) {
            throw new \InvalidArgumentException(__('Error! You did not enter a name for the category.'));
        }

        if (empty($data['parent_id'])) {
            throw new \InvalidArgumentException(__('Error! You did not provide a parent for the category.'));
        }

        // get entity manager
        $entityManager = \ServiceUtil::get('doctrine.orm.default_entity_manager');

        // process name
        $data['name'] = self::processCategoryName($data['name']);

        // check that we don't have another category with the same name
        // on the same level
        $qb = $entityManager->createQueryBuilder();
        $qb->select('COUNT(c.id)')
           ->from('ZikulaCategoriesModule:CategoryEntity', 'c')
           ->where('c.name = :name')
           ->andWhere('c.parent = :parentid')
           ->setParameter('name', $data['name'])
           ->setParameter('parentid', $data['parent_id']);

        if (isset($data['id']) && is_numeric($data['id'])) {
            $qb->andWhere('c.id != :id')
               ->setParameter('id', $data['id']);
        }

        $query = $qb->getQuery();
        $exists = (int)$query->getSingleScalarResult();
        if ($exists > 0) {
            throw new \RuntimeException(__f('Category %s must be unique under parent', $data['name']));
        }

        return true;
    }

    /**
     * Process the name of a category
     *
     * @param array $name The name of the category
     *
     * @return string the processed name
     */
    public static function processCategoryName($name)
    {
        // encode slash in name
        return $name = str_replace('/', '&#47;', $name);
    }

    /**
     * Process the parent of a category
     *
     * @param integer $parent_id The parent_id of the category
     *
     * @return CategoryEntity the parent entity
     */
    public static function processCategoryParent($parent_id)
    {
        $entityManager = \ServiceUtil::get('doctrine.orm.default_entity_manager');

        return $entityManager->getReference('ZikulaCategoriesModule:CategoryEntity', $parent_id);
    }

    /**
     * Process the display name of a category
     *
     * @param array $displayname The display name of the category
     * @param array $name        The name of the category
     *
     * @return array the processed display name
     */
    public static function processCategoryDisplayName($displayname, $name)
    {
        $languages = \ZLanguage::getInstalledLanguages();
        foreach ($languages as $lang) {
            if (!isset($displayname[$lang]) || !$displayname[$lang]) {
                $displayname[$lang] = $name;
            }
        }

        return $displayname;
    }

    /**
     * Process the path of a category
     *
     * @param string $parent_path   The path of the parent category
     * @param string $category_name The name of the category
     *
     * @return string the category path
     */
    public static function processCategoryPath($parent_path, $category_name)
    {
        return $parent_path . '/' . $category_name;
    }

    /**
     * Process the ipath of a category
     *
     * @param string $parent_ipath  The ipath of the parent category
     * @param string $category_id   The id of the category
     *
     * @return string the category path
     */
    public static function processCategoryIPath($parent_ipath, $category_id)
    {
        return $parent_ipath . '/' . $category_id;
    }

    /**
     * Process the attributes of a category
     *
     * @param CategoryEntity $category      The category to set the attributes for
     * @param array          $attrib_names  The attribute names
     * @param array          $attrib_values The attribute values
     *
     * @return void
     */
    public static function processCategoryAttributes($category, $attrib_names, $attrib_values)
    {
        // delete attributes
        if (isset($category['attributes'])) {
            foreach ($category['attributes'] as $attribute) {
                if (!in_array($attribute['name'], $attrib_names)) {
                    $category->delAttribute($attribute['name']);
                }
            }
        }

        // add/update attributes
        foreach ($attrib_names as $attrib_key => $attrib_name) {
            if (!empty($attrib_name)) {
                $category->setAttribute($attrib_name, $attrib_values[$attrib_key]);
            }
        }
    }

    /**
     * Checks whether a category may be deleted or moved.
     * For this all registries are checked for if the given category is contained in the corresponding subtree.
     * If yes, the mapping table of the corresponding module is checked for if it contains the given category.
     *
     * @param CategoryEntity $category The category to process
     *
     * @return boolean true if category may be deleted or moved, false otherwise
     */
    public static function mayCategoryBeDeletedOrMoved($category)
    {
        // get entity manager
        $entityManager = \ServiceUtil::get('doctrine.orm.default_entity_manager');

        // check legacy table first (as this is quickly done)
        $legacyMappings = $entityManager->getRepository('ZikulaCategoriesModule:CategoriesMapobj')
            ->findBy(['categoryId' => $category['id']]);
        if (count($legacyMappings) > 0) {
            return false;
        }

        // fetch registries
        $registries = $entityManager->getRepository('ZikulaCategoriesModule:CategoryRegistryEntity')
            ->findAll();

        $kernel = \ServiceUtil::get('kernel');

        // iterate over all registries
        foreach ($registries as $registry) {
            // check if the registry subtree contains our category
            $isContained = \CategoryUtil::isSubCategory($registry['category_id'], $category);
            if (!$isContained) {
                continue;
            }

            // get information about responsible module
            $module = $kernel->getModule($registry['modname']);
            $moduleClass = get_class($module);
            $moduleClassLevels = explode('\\', get_class($module));
            unset($moduleClassLevels[count($moduleClassLevels) - 1]);
            $moduleNamespace = implode('\\', $moduleClassLevels);

            // collect module entities
            $entityPath = $module->getRelativePath() . '/Entity/';
            $finder = new Finder();
            $finder->files()->name('*.php')->in($entityPath);
            foreach ($finder as $file) {
                // check if this entity implements category assignments
                include_once $file;
                $entityName = basename($file->getRelativePathname(), '.php');
                $entityClass = $moduleNamespace . '\\Entity\\' . $entityName;
                if (!is_subclass_of($entityClass, 'Zikula\\CategoriesModule\\Entity\\AbstractCategoryAssignment')) {
                    continue;
                }

                // check if this mapping table contains a reference to the given category
                $mappings = $entityManager->getRepository($registry['modname'] . ':' . $entityName)
                    ->findBy(['category' => $category['id']]);
                if (count($mappings) > 0) {
                    // existing reference found
                    return false;
                }
            }
        }

        return true;
    }
}
